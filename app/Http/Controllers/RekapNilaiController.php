<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Simulasi;
use App\Models\User;
use Illuminate\Http\Request;

class RekapNilaiController extends Controller
{
    public function index(Request $request)
    {
        // Show list of simulasi with statistics
        $query = Simulasi::with(['mataPelajaran', 'creator'])
            ->withCount('nilai');

        // Order by latest first
        $simulasiList = $query->orderBy('created_at', 'desc')->paginate(12);

        // Add statistics for each simulasi
        foreach ($simulasiList as $simulasi) {
            $stats = Nilai::where('simulasi_id', $simulasi->id)
                ->selectRaw('COUNT(*) as total_peserta, AVG(nilai_total) as rata_rata, MAX(nilai_total) as nilai_tertinggi, MIN(nilai_total) as nilai_terendah')
                ->first();

            $simulasi->total_peserta = (int) ($stats->total_peserta ?? 0);
            $simulasi->rata_rata = (float) ($stats->rata_rata ?? 0);
            $simulasi->nilai_tertinggi = (float) ($stats->nilai_tertinggi ?? 0);
            $simulasi->nilai_terendah = (float) ($stats->nilai_terendah ?? 0);

            // Detail nilai tertinggi/terendah (nama siswa)
            $top = null;
            $bottom = null;
            if ($simulasi->total_peserta > 0) {
                $top = Nilai::with(['user:id,name,rombongan_belajar'])
                    ->where('simulasi_id', $simulasi->id)
                    ->orderByDesc('nilai_total')
                    ->orderBy('id')
                    ->first();

                $bottom = Nilai::with(['user:id,name,rombongan_belajar'])
                    ->where('simulasi_id', $simulasi->id)
                    ->orderBy('nilai_total')
                    ->orderBy('id')
                    ->first();
            }

            $simulasi->top_nilai = $top;
            $simulasi->bottom_nilai = $bottom;
        }

        return view('rekap-nilai.index', compact('simulasiList'));
    }

    public function show($simulasiId, Request $request)
    {
        $simulasi = Simulasi::with(['mataPelajaran', 'creator'])->findOrFail($simulasiId);

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

        // Get unique classes for filter
        $classes = User::where('role', 'siswa')
                    ->whereNotNull('rombongan_belajar')
                    ->distinct()
                    ->pluck('rombongan_belajar')
                    ->sort()
                    ->values();

        return view('rekap-nilai.show', compact('simulasi', 'nilaiList', 'classes'));
    }

    public function export(Request $request)
    {
        // TODO: Export to Excel functionality
        return response()->json(['message' => 'Export feature coming soon']);
    }
}
