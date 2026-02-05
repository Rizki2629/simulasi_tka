<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Simulasi;
use App\Models\SimulasiPeserta;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RekapNilaiController extends Controller
{
    private function normalizeDetailJawaban(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            if (is_string($decoded) && $decoded !== '') {
                $decoded2 = json_decode($decoded, true);
                if (is_array($decoded2)) {
                    return $decoded2;
                }
            }
        }

        return [];
    }

    public function index(Request $request)
    {
        // Show list of simulasi with statistics
        $query = Simulasi::with(['mataPelajaran', 'creator'])
            ->withCount('nilai');

        // Order by latest first
        $simulasiList = $query->orderBy('created_at', 'desc')->paginate(12);

        $simulasiIds = $simulasiList->getCollection()->pluck('id')->filter()->values();
        if ($simulasiIds->isNotEmpty()) {
            $statsBySimulasiId = Nilai::query()
                ->whereIn('simulasi_id', $simulasiIds)
                ->selectRaw('simulasi_id, COUNT(*) as total_peserta, AVG(nilai_total) as rata_rata, MAX(nilai_total) as nilai_tertinggi, MIN(nilai_total) as nilai_terendah')
                ->groupBy('simulasi_id')
                ->get()
                ->keyBy('simulasi_id');

            $driver = (string) DB::connection()->getDriverName();
            $topBySimulasiId = collect();
            $bottomBySimulasiId = collect();

            if ($driver === 'pgsql') {
                $topPairs = DB::table('nilai')
                    ->selectRaw('DISTINCT ON (simulasi_id) id, simulasi_id')
                    ->whereIn('simulasi_id', $simulasiIds)
                    ->orderBy('simulasi_id')
                    ->orderByDesc('nilai_total')
                    ->orderBy('id')
                    ->get();

                $bottomPairs = DB::table('nilai')
                    ->selectRaw('DISTINCT ON (simulasi_id) id, simulasi_id')
                    ->whereIn('simulasi_id', $simulasiIds)
                    ->orderBy('simulasi_id')
                    ->orderBy('nilai_total')
                    ->orderBy('id')
                    ->get();

                $allNilaiIds = collect($topPairs)->pluck('id')
                    ->merge(collect($bottomPairs)->pluck('id'))
                    ->filter()
                    ->unique()
                    ->values();

                $nilaiById = collect();
                if ($allNilaiIds->isNotEmpty()) {
                    $nilaiById = Nilai::with(['user:id,name,rombongan_belajar'])
                        ->whereIn('id', $allNilaiIds)
                        ->get()
                        ->keyBy('id');
                }

                foreach ($topPairs as $p) {
                    $nilai = $nilaiById->get($p->id);
                    if ($nilai) {
                        $topBySimulasiId->put((int) $p->simulasi_id, $nilai);
                    }
                }

                foreach ($bottomPairs as $p) {
                    $nilai = $nilaiById->get($p->id);
                    if ($nilai) {
                        $bottomBySimulasiId->put((int) $p->simulasi_id, $nilai);
                    }
                }
            }

            foreach ($simulasiList as $simulasi) {
                $stats = $statsBySimulasiId->get($simulasi->id);

                $simulasi->total_peserta = (int) ($stats->total_peserta ?? 0);
                $simulasi->rata_rata = (float) ($stats->rata_rata ?? 0);
                $simulasi->nilai_tertinggi = (float) ($stats->nilai_tertinggi ?? 0);
                $simulasi->nilai_terendah = (float) ($stats->nilai_terendah ?? 0);

                $simulasi->top_nilai = $topBySimulasiId->get($simulasi->id);
                $simulasi->bottom_nilai = $bottomBySimulasiId->get($simulasi->id);
            }
        } else {
            foreach ($simulasiList as $simulasi) {
                $simulasi->total_peserta = 0;
                $simulasi->rata_rata = 0;
                $simulasi->nilai_tertinggi = 0;
                $simulasi->nilai_terendah = 0;
                $simulasi->top_nilai = null;
                $simulasi->bottom_nilai = null;
            }
        }

        return view('rekap-nilai.index', compact('simulasiList'));
    }

    public function show($simulasiId, Request $request)
    {
        $simulasi = Simulasi::with([
            'mataPelajaran',
            'creator',
            'simulasiSoal.soal' => function ($q) {
                $q->withCount('subSoal');
            },
        ])->findOrFail($simulasiId);

        $query = Nilai::with(['user'])
            ->where('simulasi_id', $simulasiId);

        // Filter by class if provided
        if ($request->has('class_filter') && $request->class_filter != '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('rombongan_belajar', $request->class_filter);
            });
        }

        // Order by score descending
        $nilaiList = $query->orderBy('nilai_total', 'desc')->paginate(20);

        // Compute total soal for this simulasi (paket counts as its sub-soal count)
        $totalSoalSimulasi = 0;
        foreach (($simulasi->simulasiSoal ?? []) as $ref) {
            $soal = $ref->soal ?? null;
            if (!$soal) {
                continue;
            }
            $subCount = (int) ($soal->sub_soal_count ?? 0);
            $totalSoalSimulasi += max(1, $subCount);
        }

        // Fetch peserta rows for stable waktu_mulai/waktu_selesai display
        $userIds = $nilaiList->getCollection()->pluck('user_id')->filter()->unique()->values();
        $pesertaByUserId = collect();
        if ($userIds->isNotEmpty()) {
            $pesertaByUserId = SimulasiPeserta::query()
                ->where('simulasi_id', (int) $simulasiId)
                ->whereIn('user_id', $userIds)
                ->get()
                ->keyBy('user_id');
        }

        // Get unique classes for filter
        $classes = User::where('role', 'siswa')
                    ->whereNotNull('rombongan_belajar')
                    ->distinct()
                    ->pluck('rombongan_belajar')
                    ->sort()
                    ->values();

        return view('rekap-nilai.show', compact('simulasi', 'nilaiList', 'classes', 'totalSoalSimulasi', 'pesertaByUserId'));
    }

    public function export(Request $request)
    {
        // TODO: Export to Excel functionality
        return response()->json(['message' => 'Export feature coming soon']);
    }

    public function review($nilaiId)
    {
        $nilai = Nilai::with(['user', 'simulasi.mataPelajaran'])->findOrFail($nilaiId);
        
        // Normalize detail_jawaban
        $detailJawaban = $this->normalizeDetailJawaban($nilai->detail_jawaban ?? []);
        
        // Get soal IDs from detail_jawaban
        $soalIds = collect($detailJawaban)
            ->pluck('soal_id')
            ->filter(fn ($v) => (int) $v > 0)
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
        
        // Load soals
        $soals = empty($soalIds)
            ? collect()
            : Soal::with(['subSoal.pilihanJawaban', 'pilihanJawaban'])
                ->whereIn('id', $soalIds)
                ->get();
        
        // Prepare hasil data
        $hasil = [
            'nilai_total' => (float) ($nilai->nilai_total ?? 0),
            'jumlah_benar' => (int) ($nilai->jumlah_benar ?? 0),
            'jumlah_salah' => (int) ($nilai->jumlah_salah ?? 0),
            'jumlah_soal' => (int) ($nilai->jumlah_soal ?? 0),
            'detail_jawaban' => $detailJawaban,
        ];
        
        $student = $nilai->user;
        $reviewData = [
            'simulasi' => [
                'nama' => $nilai->simulasi->nama_simulasi ?? '-',
                'mata_pelajaran' => $nilai->simulasi->mataPelajaran->nama_mata_pelajaran ?? '-',
            ],
        ];
        
        // Compute score metrics
        $skorPoin = 0;
        $skorMaksimal = 0;
        foreach ($detailJawaban as $item) {
            $skorPoin += (float) ($item['nilai'] ?? 0);
            $skorMaksimal += (float) ($item['maksimal'] ?? 1);
        }
        
        $nilaiAkhir = $skorMaksimal > 0 ? round(($skorPoin / $skorMaksimal) * 100) : 0;
        
        // Use same review view as students
        return view('simulasi.review', compact('soals', 'hasil', 'reviewData', 'skorPoin', 'skorMaksimal', 'student', 'nilaiAkhir', 'nilai'))
            ->with('jawaban', [])
            ->with('isAdminView', true);
    }

    public function download($nilaiId)
    {
        $nilai = Nilai::with(['user', 'simulasi.mataPelajaran'])->findOrFail($nilaiId);
        
        // Normalize detail_jawaban
        $detailJawaban = $this->normalizeDetailJawaban($nilai->detail_jawaban ?? []);
        
        // Get soal IDs from detail_jawaban
        $soalIds = collect($detailJawaban)
            ->pluck('soal_id')
            ->filter(fn ($v) => (int) $v > 0)
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
        
        // Load soals
        $soals = empty($soalIds)
            ? collect()
            : Soal::with(['subSoal.pilihanJawaban', 'pilihanJawaban'])
                ->whereIn('id', $soalIds)
                ->get();
        
        // Prepare hasil data
        $hasil = [
            'nilai_total' => (float) ($nilai->nilai_total ?? 0),
            'jumlah_benar' => (int) ($nilai->jumlah_benar ?? 0),
            'jumlah_salah' => (int) ($nilai->jumlah_salah ?? 0),
            'jumlah_soal' => (int) ($nilai->jumlah_soal ?? 0),
            'detail_jawaban' => $detailJawaban,
        ];
        
        $student = $nilai->user;
        $reviewData = [
            'simulasi' => [
                'nama' => $nilai->simulasi->nama_simulasi ?? '-',
                'mata_pelajaran' => $nilai->simulasi->mataPelajaran->nama_mata_pelajaran ?? '-',
            ],
        ];
        
        // Compute score metrics
        $skorPoin = 0;
        $skorMaksimal = 0;
        foreach ($detailJawaban as $item) {
            $skorPoin += (float) ($item['nilai'] ?? 0);
            $skorMaksimal += (float) ($item['maksimal'] ?? 1);
        }
        
        $nilaiAkhir = $skorMaksimal > 0 ? round(($skorPoin / $skorMaksimal) * 100) : 0;
        
        // Generate PDF
        $pdf = Pdf::loadView('rekap-nilai.review-pdf', compact('soals', 'hasil', 'reviewData', 'nilai', 'skorPoin', 'skorMaksimal', 'student'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
        
        $filename = 'Review_' . $student->name . '_' . ($reviewData['simulasi']['nama'] ?? 'Simulasi') . '_' . now()->format('YmdHis') . '.pdf';
        
        return $pdf->download($filename);
    }
}
