<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Soal;
use App\Models\SubSoal;
use App\Models\PilihanJawaban;
use App\Models\SubPilihanJawaban;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\SoalFirestoreRepository;
use App\Services\SoalViewAdapter;
use App\Services\SoalFirestoreSyncService;

class SoalController extends Controller
{
    public function index()
    {
        try {
            $rows = app(SoalFirestoreRepository::class)->listAll();
            if (!empty($rows)) {
                $soals = app(SoalViewAdapter::class)->hydrateSoalList($rows);
                return view('soal.index', compact('soals'));
            }
        } catch (\Throwable $e) {
            \Log::warning('Firestore soal.index fallback to DB: ' . $e->getMessage());

            $soals = Soal::with(['mataPelajaran', 'pilihanJawaban', 'creator'])
                        ->withCount('simulasiSoal')
                        ->orderBy('created_at', 'desc')
                        ->get();

            return view('soal.index', compact('soals'));
        }

        $soals = Soal::with(['mataPelajaran', 'pilihanJawaban', 'creator'])
                    ->withCount('simulasiSoal')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('soal.index', compact('soals'));
    }

    public function create()
    {
        // Get all mata pelajaran for dropdown
        $mataPelajaran = MataPelajaran::where('is_active', true)->get();
        
        return view('soal.create', compact('mataPelajaran'));
    }

    public function store(Request $request)
    {
        // Debug: Log all request data
        \Log::info('Form submitted', $request->all());
        
        // Validation - handle dynamic field names
        $request->validate([
            'mata_pelajaran' => 'required|string',
        ]);

        $formIds = $this->extractSoalFormIds($request);
        if (empty($formIds)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Silakan tambahkan minimal 1 soal');
        }

        DB::beginTransaction();
        
        try {
            $mataPelajaran = MataPelajaran::firstOrCreate(
                ['nama' => $request->mata_pelajaran],
                [
                    'kode' => strtoupper(substr($request->mata_pelajaran, 0, 3)),
                    'is_active' => true,
                ]
            );

            // Generate Kode Soal for the Bundle/Packet
            $kodePrefix = strtoupper(substr($mataPelajaran->kode ?? '', 0, 3));
            $kodePrefix = $kodePrefix ?: strtoupper(substr($mataPelajaran->nama, 0, 3));
            $kodeSoal = $kodePrefix . '-' . now()->format('Ymd-His');

            // Create Parent Soal (Packet)
            // We use 'paket' as jenis_soal to indicate it's a container
            // The actual questions are in sub_soal
            $soal = Soal::create([
                'kode_soal' => $kodeSoal,
                'mata_pelajaran_id' => $mataPelajaran->id,
                'jenis_soal' => 'paket', 
                'pertanyaan' => 'Paket Soal ' . $mataPelajaran->nama,
                'pembahasan' => null,
                'gambar_pertanyaan' => null,
                'bobot' => count($formIds), // Total questions as weight? Or just default.
                'jawaban_benar' => null,
                'created_by' => Auth::id() ?? 1,
            ]);

            $nomorUrut = 1;
            foreach ($formIds as $formId) {
                $this->createSubSoalFromForm($request, $soal, $formId, $nomorUrut);
                $nomorUrut++;
            }

            DB::commit();

            try {
                app(SoalFirestoreSyncService::class)->sync($soal);
            } catch (\Throwable $e) {
                \Log::warning('Firestore sync after soal.store failed: ' . $e->getMessage());
            }

            return redirect()->route('soal.index')->with('success', 'Paket soal berhasil ditambahkan dengan ' . count($formIds) . ' pertanyaan');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing soal: ' . $e->getMessage()); // Added logging

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal menyimpan soal: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $id = (int) $id;

        try {
            $data = app(SoalFirestoreRepository::class)->find($id);
            if ($data) {
                $soal = app(SoalViewAdapter::class)->hydrateSoal($data);
                return view('soal.show', compact('soal'));
            }
        } catch (\Throwable $e) {
            \Log::warning('Firestore soal.show fallback to DB: ' . $e->getMessage());
        }

        $soal = Soal::with(['mataPelajaran', 'pilihanJawaban', 'creator', 'subSoal.pilihanJawaban'])->findOrFail($id);
        return view('soal.show', compact('soal'));
    }

    public function edit($id)
    {
        $soal = Soal::with(['mataPelajaran', 'pilihanJawaban', 'subSoal.pilihanJawaban'])->findOrFail($id);
        $mataPelajaran = MataPelajaran::where('is_active', true)->get();
        return view('soal.edit', compact('soal', 'mataPelajaran'));
    }

    public function update(Request $request, $id)
    {
        \Log::info('Update Soal Request', [
            'soal_id' => $id,
            'payload' => $request->all(),
        ]);

        $formIds = $this->extractSoalFormIds($request);
        if (empty($formIds)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Silakan lengkapi minimal 1 soal');
        }

        DB::beginTransaction();

        try {
            $soal = Soal::findOrFail($id);

            $mataPelajaran = MataPelajaran::firstOrCreate(
                ['nama' => $request->mata_pelajaran],
                [
                    'kode' => strtoupper(substr($request->mata_pelajaran, 0, 3)),
                    'is_active' => true,
                ]
            );

            // Update Parent Soal
            $soal->mata_pelajaran_id = $mataPelajaran->id;
            // If the soal is already a 'paket', we keep it as 'paket'
            // If it was a single question, we might be converting it, but for now we assume structure consistency or upgrade
            if ($soal->jenis_soal !== 'paket' && count($formIds) > 1) {
                $soal->jenis_soal = 'paket';
                $soal->pertanyaan = 'Paket Soal ' . $mataPelajaran->nama;
            } elseif ($soal->jenis_soal === 'paket') {
                $soal->pertanyaan = 'Paket Soal ' . $mataPelajaran->nama;
            }
            // If it's a single question updated to be single, updateSoalFromForm logic would apply, 
            // but the current controller logic uses subSoal approach for updates in this block.
            // My previous reading showed strict usage of subSoal logic in update()
            // So we enforce Packet structure for uniformity in this refactor.
            if ($soal->jenis_soal !== 'paket') {
                 // For backward compatibility or single question edits, 
                 // if the original code supported single question edits without subsoal, it would be different.
                 // But the code I read (lines 129-140) blindly deletes subsoals and recreates them.
                 // If the original soal was "single" (no subsoals), the subsoal loop does nothing, 
                 // and then it adds new subsoals.
                 // So effectively it converts everything to subsoals?
                 // Let's check if the index view handles "paket" containing only 1 subsoal?
                 // Yes, likely.
                 $soal->jenis_soal = 'paket';
                 $soal->pertanyaan = 'Paket Soal ' . $mataPelajaran->nama;
            }

            $soal->bobot = count($formIds);
            $soal->save();

            // Hapus sub-soal lama
            $soal->subSoal()->each(function($sub) {
                foreach ($sub->pilihanJawaban as $pil) {
                    if ($pil->gambar_jawaban) {
                        Storage::disk('public')->delete($pil->gambar_jawaban);
                    }
                }
                if ($sub->gambar_pertanyaan) {
                    Storage::disk('public')->delete($sub->gambar_pertanyaan);
                }
                $sub->delete();
            });

            // Simpan semua sub-soal
            $nomorUrut = 1;
            foreach ($formIds as $formId) {
                $this->createSubSoalFromForm($request, $soal, $formId, $nomorUrut);
                $nomorUrut++;
            }

            DB::commit();

            try {
                app(SoalFirestoreSyncService::class)->sync($soal);
            } catch (\Throwable $e) {
                \Log::warning('Firestore sync after soal.update failed: ' . $e->getMessage());
            }

            $totalSoal = count($formIds);
            return redirect()->route('soal.index')->with('success', "Soal berhasil diupdate dengan {$totalSoal} pertanyaan");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal mengupdate soal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $soal = Soal::findOrFail($id);
            
            // Delete associated images
            if ($soal->gambar_pertanyaan) {
                Storage::disk('public')->delete($soal->gambar_pertanyaan);
            }
            
            // Delete pilihan jawaban images
            foreach ($soal->pilihanJawaban as $pilihan) {
                if ($pilihan->gambar_jawaban) {
                    Storage::disk('public')->delete($pilihan->gambar_jawaban);
                }
            }
            
            // Delete soal (cascade will delete pilihan_jawaban)
            $soal->delete();

            try {
                app(SoalFirestoreRepository::class)->delete((int) $id);
            } catch (\Throwable $e) {
                \Log::warning('Firestore delete after soal.destroy failed: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil daftar ID form soal yang valid dari request.
     */
    private function extractSoalFormIds(Request $request): array
    {
        $ids = [];

        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'jenis_soal_') !== 0) {
                continue;
            }

            $formId = str_replace('jenis_soal_', '', $key);
            if ($request->filled('pertanyaan_' . $formId)) {
                $ids[] = $formId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Buat satu entri soal lengkap berdasarkan data form dinamis.
     */
    private function createSoalFromForm(Request $request, string $formId, MataPelajaran $mataPelajaran): bool
    {
        $jenisSoal = $request->input('jenis_soal_' . $formId);
        $pertanyaanKey = 'pertanyaan_' . $formId;

        if (!$jenisSoal || !$request->filled($pertanyaanKey)) {
            return false;
        }

        $kode = strtoupper(substr($mataPelajaran->kode ?? '', 0, 3));
        $kode = $kode ?: strtoupper(substr($mataPelajaran->nama, 0, 3));
        $kode .= '-' . now()->format('Ymd-His') . '-' . $formId;

        $gambarPath = null;
        $gambarField = 'gambar_soal_' . $formId;
        
        // Check if image uploaded via file input
        if ($request->hasFile($gambarField)) {
            $gambarPath = $request->file($gambarField)->store('soal', 'public');
        }
        // Check if image pasted (stored as path in hidden input)
        elseif ($request->filled($gambarField)) {
            $gambarPath = $request->input($gambarField);
        }

        $soal = Soal::create([
            'kode_soal' => $kode,
            'mata_pelajaran_id' => $mataPelajaran->id,
            'jenis_soal' => $jenisSoal,
            'pertanyaan' => $request->input($pertanyaanKey),
            'pembahasan' => $request->input('pembahasan_' . $formId),
            'gambar_pertanyaan' => $gambarPath,
            'bobot' => 1,
            'jawaban_benar' => null,
            'created_by' => Auth::id() ?? 1,
        ]);

        switch ($jenisSoal) {
            case 'pilihan_ganda':
                $this->storePilihanGanda($request, $soal, $formId);
                break;
            case 'benar_salah':
                $this->syncPernyataanJawaban($request, $soal, $formId, 'benar_salah');
                break;
            case 'mcma':
                $this->syncPernyataanJawaban($request, $soal, $formId, 'mcma');
                break;
            case 'isian':
            case 'uraian':
                $kunci = $request->input('kunci_jawaban_' . $formId, '');
                $soal->jawaban_benar = $kunci;
                $soal->kunci_jawaban = $kunci;
                $soal->save();
                break;
        }

        return true;
    }

    /**
     * Perbarui soal yang sudah ada menggunakan struktur form dinamis.
     */
    private function updateSoalFromForm(Request $request, Soal $soal, string $formId, MataPelajaran $mataPelajaran): void
    {
        $jenisSoal = $request->input('jenis_soal_' . $formId);
        $pertanyaanKey = 'pertanyaan_' . $formId;

        if (!$jenisSoal || !$request->filled($pertanyaanKey)) {
            throw new \Exception('Data soal utama tidak lengkap.');
        }

        $soal->mata_pelajaran_id = $mataPelajaran->id;
        $soal->jenis_soal = $jenisSoal;
        $soal->pertanyaan = $request->input($pertanyaanKey);
        $soal->pembahasan = $request->input('pembahasan_' . $formId);

        $gambarField = 'gambar_soal_' . $formId;
        
        // Check if image uploaded via file input
        if ($request->hasFile($gambarField)) {
            if ($soal->gambar_pertanyaan) {
                Storage::disk('public')->delete($soal->gambar_pertanyaan);
            }
            $soal->gambar_pertanyaan = $request->file($gambarField)->store('soal', 'public');
        }
        // Check if image pasted (stored as path in hidden input)
        elseif ($request->filled($gambarField)) {
            // If different from current, delete old and use new
            $newPath = $request->input($gambarField);
            if ($soal->gambar_pertanyaan !== $newPath) {
                if ($soal->gambar_pertanyaan) {
                    Storage::disk('public')->delete($soal->gambar_pertanyaan);
                }
                $soal->gambar_pertanyaan = $newPath;
            }
        }

        $soal->save();

        switch ($jenisSoal) {
            case 'pilihan_ganda':
                $this->clearPilihanJawaban($soal);
                $this->storePilihanGanda($request, $soal, $formId);
                break;
            case 'benar_salah':
                $this->clearPilihanJawaban($soal);
                $this->syncPernyataanJawaban($request, $soal, $formId, 'benar_salah');
                break;
            case 'mcma':
                $this->clearPilihanJawaban($soal);
                $this->syncPernyataanJawaban($request, $soal, $formId, 'mcma');
                break;
            case 'isian':
            case 'uraian':
                $this->clearPilihanJawaban($soal);
                $kunci = $request->input('kunci_jawaban_' . $formId, '');
                $soal->jawaban_benar = $kunci;
                $soal->kunci_jawaban = $kunci;
                $soal->save();
                break;
        }
    }

    private function storePilihanGanda(Request $request, Soal $soal, string $formId): void
    {
        $labels = ['a', 'b', 'c', 'd', 'e'];
        $jawabanBenar = [];

        foreach ($labels as $label) {
            $pilihanKey = 'pilihan_' . $formId . '_' . $label;
            if (!$request->filled($pilihanKey)) {
                continue;
            }

            $gambarPath = null;
            $gambarKey = 'gambar_pilihan_' . $formId . '_' . $label;
            
            // Check if image uploaded via file input
            if ($request->hasFile($gambarKey)) {
                $gambarPath = $request->file($gambarKey)->store('jawaban', 'public');
            }
            // Check if image pasted (stored as path in hidden input)
            elseif ($request->filled($gambarKey)) {
                $gambarPath = $request->input($gambarKey);
            }

            $kunciKey = 'kunci_jawaban_' . $formId;
            $isBenar = strtolower($request->input($kunciKey, '')) === $label;

            PilihanJawaban::create([
                'soal_id' => $soal->id,
                'label' => strtoupper($label),
                'teks_jawaban' => $request->input($pilihanKey),
                'gambar_jawaban' => $gambarPath,
                'is_benar' => $isBenar,
            ]);

            if ($isBenar) {
                $jawabanBenar[] = strtoupper($label);
            }
        }

        $soal->jawaban_benar = implode(',', $jawabanBenar);
        $soal->save();
    }

    /**
     * Sinkronisasi daftar pernyataan untuk soal benar/salah maupun MCMA.
     */
    private function syncPernyataanJawaban(Request $request, Soal $soal, ?string $soalId, string $tipe): void
    {
        $pernyataanField = $soalId ? 'pernyataan_' . $soalId : 'pernyataan';
        $pernyataanList = $request->input($pernyataanField, []);

        if (!is_array($pernyataanList)) {
            $pernyataanList = [];
        }

        $pernyataanList = array_values(array_filter($pernyataanList, function ($value) {
            return trim((string) $value) !== '';
        }));

        if (empty($pernyataanList)) {
            throw new \Exception('Minimal harus ada satu pernyataan untuk jenis soal ini');
        }

        $soal->pilihanJawaban()->delete();

        $jawabanBenar = [];
        foreach ($pernyataanList as $index => $teks) {
            $label = 'P' . ($index + 1);
            $pernyataanNumber = $index + 1;

            if ($tipe === 'benar_salah') {
                $kunciField = ($soalId ? 'kunci_' . $soalId . '_' : 'kunci_') . $pernyataanNumber;
                $isBenar = strtolower($request->input($kunciField, 'salah')) === 'benar';
            } else {
                $kunciField = ($soalId ? 'kunci_' . $soalId . '_' : 'kunci_') . $pernyataanNumber . '_benar';
                $isBenar = $request->has($kunciField);
            }

            // Handle gambar pernyataan (file upload or pasted image)
            $gambarPernyataan = null;
            $gambarField = $soalId ? "gambar_pernyataan_{$soalId}_{$pernyataanNumber}" : "gambar_pernyataan_{$pernyataanNumber}";
            
            // Check if image uploaded via file input
            if ($request->hasFile($gambarField)) {
                $gambarPernyataan = $request->file($gambarField)->store('pernyataan', 'public');
            }
            // Check if image pasted (stored as path in hidden input)
            elseif ($request->filled($gambarField)) {
                $gambarPernyataan = $request->input($gambarField);
            }

            PilihanJawaban::create([
                'soal_id' => $soal->id,
                'label' => $label,
                'teks_jawaban' => $teks,
                'gambar_jawaban' => $gambarPernyataan,
                'is_benar' => $isBenar,
            ]);

            if ($isBenar) {
                $jawabanBenar[] = $label;
            }
        }

        $soal->jawaban_benar = implode(',', $jawabanBenar);
    }

    /**
     * Hapus pilihan jawaban beserta gambar lama secara aman.
     */
    private function clearPilihanJawaban(Soal $soal): void
    {
        foreach ($soal->pilihanJawaban as $pil) {
            if ($pil->gambar_jawaban) {
                Storage::disk('public')->delete($pil->gambar_jawaban);
            }
        }

        $soal->pilihanJawaban()->delete();
    }

    /**
     * Buat sub-soal dari form dinamis.
     */
    private function createSubSoalFromForm(Request $request, Soal $soal, string $formId, int $nomorUrut): void
    {
        $jenisSoal = $request->input('jenis_soal_' . $formId);
        $pertanyaanKey = 'pertanyaan_' . $formId;

        if (!$jenisSoal || !$request->filled($pertanyaanKey)) {
            return;
        }

        $gambarPath = null;
        $gambarField = 'gambar_soal_' . $formId;
        if ($request->hasFile($gambarField)) {
            $gambarPath = $request->file($gambarField)->store('soal', 'public');
        } elseif ($request->filled($gambarField)) {
            // Paste image path
            $gambarPath = $request->input($gambarField);
        }

        $subSoal = $soal->subSoal()->create([
            'nomor_urut' => $nomorUrut,
            'jenis_soal' => $jenisSoal,
            'pertanyaan' => $request->input($pertanyaanKey),
            'pembahasan' => $request->input('pembahasan_' . $formId),
            'gambar_pertanyaan' => $gambarPath,
            'jawaban_benar' => null,
        ]);

        // Simpan pilihan jawaban untuk sub-soal
        switch ($jenisSoal) {
            case 'pilihan_ganda':
                $this->storeSubPilihanGanda($request, $subSoal, $formId);
                break;
            case 'benar_salah':
                $this->syncSubPernyataanJawaban($request, $subSoal, $formId, 'benar_salah');
                break;
            case 'mcma':
                $this->syncSubPernyataanJawaban($request, $subSoal, $formId, 'mcma');
                break;
            case 'isian':
            case 'uraian':
                $kunci = $request->input('kunci_jawaban_' . $formId, '');
                $subSoal->jawaban_benar = $kunci;
                $subSoal->kunci_jawaban = $kunci;
                $subSoal->save();
                break;
        }
    }

    private function storeSubPilihanGanda(Request $request, SubSoal $subSoal, string $formId): void
    {
        $labels = ['a', 'b', 'c', 'd', 'e'];
        $jawabanBenar = [];

        foreach ($labels as $label) {
            $pilihanKey = 'pilihan_' . $formId . '_' . $label;
            if (!$request->filled($pilihanKey)) {
                continue;
            }

            $gambarPath = null;
            $gambarKey = 'gambar_pilihan_' . $formId . '_' . $label;
            if ($request->hasFile($gambarKey)) {
                $gambarPath = $request->file($gambarKey)->store('jawaban', 'public');
            } elseif ($request->filled($gambarKey)) {
                // Paste image path
                $gambarPath = $request->input($gambarKey);
            }

            $kunciKey = 'kunci_jawaban_' . $formId;
            $isBenar = strtolower($request->input($kunciKey, '')) === $label;

            $subSoal->pilihanJawaban()->create([
                'label' => strtoupper($label),
                'teks_jawaban' => $request->input($pilihanKey),
                'gambar_jawaban' => $gambarPath,
                'is_benar' => $isBenar,
            ]);

            if ($isBenar) {
                $jawabanBenar[] = strtoupper($label);
            }
        }

        $subSoal->jawaban_benar = implode(',', $jawabanBenar);
        $subSoal->save();
    }

    private function syncSubPernyataanJawaban(Request $request, SubSoal $subSoal, string $formId, string $tipe): void
    {
        $pernyataanField = 'pernyataan_' . $formId;
        $pernyataanList = $request->input($pernyataanField, []);

        if (!is_array($pernyataanList)) {
            $pernyataanList = [];
        }

        $pernyataanList = array_values(array_filter($pernyataanList, function ($value) {
            return trim((string) $value) !== '';
        }));

        if (empty($pernyataanList)) {
            return;
        }

        $jawabanBenar = [];
        foreach ($pernyataanList as $index => $teks) {
            $label = 'P' . ($index + 1);

            if ($tipe === 'benar_salah') {
                $kunciField = 'kunci_' . $formId . '_' . ($index + 1);
                $isBenar = strtolower($request->input($kunciField, 'salah')) === 'benar';
            } else {
                $kunciField = 'kunci_' . $formId . '_' . ($index + 1) . '_benar';
                $isBenar = $request->has($kunciField);
            }

            // Handle gambar pernyataan (file upload or paste image)
            $gambarPath = null;
            $gambarKey = 'gambar_pernyataan_' . $formId . '_' . ($index + 1);
            if ($request->hasFile($gambarKey)) {
                $gambarPath = $request->file($gambarKey)->store('jawaban', 'public');
            } elseif ($request->filled($gambarKey)) {
                // Paste image path
                $gambarPath = $request->input($gambarKey);
            }

            $subSoal->pilihanJawaban()->create([
                'label' => $label,
                'teks_jawaban' => $teks,
                'gambar_jawaban' => $gambarPath,
                'is_benar' => $isBenar,
            ]);

            if ($isBenar) {
                $jawabanBenar[] = $label;
            }
        }

        $subSoal->jawaban_benar = implode(',', $jawabanBenar);
        $subSoal->save();
    }

    /**
     * Upload image from paste (clipboard)
     */
    public function uploadPasteImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|string', // Base64 string
            ]);

            // Get base64 image data
            $imageData = $request->input('image');
            
            // Remove data:image/...;base64, prefix if exists
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $imageType = $matches[1]; // png, jpg, jpeg, gif, etc.
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Format gambar tidak valid'
                ], 400);
            }

            // Decode base64
            $imageData = base64_decode($imageData);
            
            if ($imageData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal decode gambar'
                ], 400);
            }

            // Generate unique filename
            $filename = 'paste_' . time() . '_' . uniqid() . '.' . $imageType;
            $path = 'soal_images/' . $filename;

            // Save to storage
            Storage::disk('public')->put($path, $imageData);

            // Return URL
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path,
                'message' => 'Gambar berhasil diupload'
            ]);

        } catch (\Exception $e) {
            \Log::error('Paste upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat upload gambar: ' . $e->getMessage()
            ], 500);
        }
    }
}
