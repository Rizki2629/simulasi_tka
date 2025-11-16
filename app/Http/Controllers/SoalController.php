<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Soal;
use App\Models\PilihanJawaban;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SoalController extends Controller
{
    public function index()
    {
        // Get all soal from database with relationships and count simulasi usage
        $soals = Soal::with(['mataPelajaran', 'pilihanJawaban'])
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

        // Check if we have any soal data
        $hasSoalData = false;
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'jenis_soal_') === 0) {
                $hasSoalData = true;
                break;
            }
        }

        if (!$hasSoalData) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Silakan tambahkan minimal 1 soal');
        }

        DB::beginTransaction();
        
        try {
            // Get or create mata pelajaran
            $mataPelajaran = MataPelajaran::firstOrCreate(
                ['nama' => $request->mata_pelajaran],
                [
                    'kode' => strtoupper(substr($request->mata_pelajaran, 0, 3)),
                    'is_active' => true
                ]
            );
            
            $savedCount = 0;
            
            // Loop through all soal submissions
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'jenis_soal_') === 0) {
                    // Extract soal ID from field name
                    $soalId = str_replace('jenis_soal_', '', $key);
                    $jenisSoal = $value;
                    
                    // Get pertanyaan
                    $pertanyaanKey = 'pertanyaan_' . $soalId;
                    if (!$request->has($pertanyaanKey)) {
                        continue;
                    }
                    
                    $pertanyaan = $request->input($pertanyaanKey);
                    
                    // Generate kode soal
                    $kode = strtoupper(substr($request->mata_pelajaran, 0, 3)) . '-' . date('Ymd-His') . '-' . $soalId;
                    
                    // Handle image upload for soal
                    $gambarPath = null;
                    $gambarSoalKey = 'gambar_soal_' . $soalId;
                    if ($request->hasFile($gambarSoalKey)) {
                        $gambarPath = $request->file($gambarSoalKey)->store('soal', 'public');
                    }
                    
                    // Create soal
                    $soal = Soal::create([
                        'kode_soal' => $kode,
                        'mata_pelajaran_id' => $mataPelajaran->id,
                        'jenis_soal' => $jenisSoal,
                        'pertanyaan' => $pertanyaan,
                        'gambar_pertanyaan' => $gambarPath,
                        'bobot' => 1,
                        'created_by' => 1, // TODO: Get from auth user
                    ]);
                    
                    // Save pilihan jawaban for pilihan_ganda, benar_salah, mcma
                    if (in_array($jenisSoal, ['pilihan_ganda', 'benar_salah', 'mcma'])) {
                        $labels = ['a', 'b', 'c', 'd', 'e'];
                        $jawabanBenar = [];
                        
                        foreach ($labels as $label) {
                            $pilihanKey = 'pilihan_' . $soalId . '_' . $label;
                            
                            if ($request->filled($pilihanKey)) {
                                $gambarJawabanPath = null;
                                $gambarPilihanKey = 'gambar_pilihan_' . $soalId . '_' . $label;
                                if ($request->hasFile($gambarPilihanKey)) {
                                    $gambarJawabanPath = $request->file($gambarPilihanKey)->store('jawaban', 'public');
                                }
                                
                                $isBenar = false;
                                $kunciJawabanKey = 'kunci_jawaban_' . $soalId;
                                
                                if ($jenisSoal === 'pilihan_ganda' || $jenisSoal === 'benar_salah') {
                                    $isBenar = (strtolower($request->input($kunciJawabanKey)) === $label);
                                } elseif ($jenisSoal === 'mcma') {
                                    $kunciArray = $request->input($kunciJawabanKey, []);
                                    $isBenar = is_array($kunciArray) && in_array($label, array_map('strtolower', $kunciArray));
                                }
                                
                                PilihanJawaban::create([
                                    'soal_id' => $soal->id,
                                    'label' => strtoupper($label),
                                    'teks_jawaban' => $request->input($pilihanKey),
                                    'gambar_jawaban' => $gambarJawabanPath,
                                    'is_benar' => $isBenar,
                                ]);
                                
                                if ($isBenar) {
                                    $jawabanBenar[] = strtoupper($label);
                                }
                            }
                        }
                        
                        // Update jawaban_benar field in soal
                        $soal->update(['jawaban_benar' => implode(',', $jawabanBenar)]);
                    } 
                    // For isian and uraian
                    else {
                        $jawabanIsianKey = 'jawaban_isian_' . $soalId;
                        $soal->update(['jawaban_benar' => $request->input($jawabanIsianKey, '')]);
                    }
                    
                    $savedCount++;
                }
            }
            
            DB::commit();
            
            return redirect()->route('soal.index')->with('success', $savedCount . ' soal berhasil disimpan');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error saving soal: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal menyimpan soal: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $soal = Soal::with(['mataPelajaran', 'pilihanJawaban', 'creator'])->findOrFail($id);
        return view('soal.show', compact('soal'));
    }

    public function edit($id)
    {
        $soal = Soal::with(['mataPelajaran', 'pilihanJawaban'])->findOrFail($id);
        $mataPelajaran = MataPelajaran::where('is_active', true)->get();
        return view('soal.edit', compact('soal', 'mataPelajaran'));
    }

    public function update(Request $request, $id)
    {
        // Log incoming request for debugging
        \Log::info('Update Soal Request', [
            'soal_id' => $id,
            'all_data' => $request->all(),
            'files' => $request->allFiles()
        ]);
        
        $soal = Soal::findOrFail($id);
        
        // Check if using new format (from edit with _1 suffix)
        $soalId = null;
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'jenis_soal_') === 0) {
                $soalId = str_replace('jenis_soal_', '', $key);
                break;
            }
        }
        
        // If new format, remap fields
        if ($soalId) {
            $jenisSoalKey = 'jenis_soal_' . $soalId;
            $pertanyaanKey = 'pertanyaan_' . $soalId;
            $pembahasanKey = 'pembahasan_' . $soalId;
            $gambarPertanyaanKey = 'gambar_pertanyaan_' . $soalId;
            
            $request->merge([
                'jenis_soal' => $request->input($jenisSoalKey),
                'pertanyaan' => $request->input($pertanyaanKey),
                'pembahasan' => $request->input($pembahasanKey),
            ]);
            
            // Handle new format kunci_jawaban
            $kunciJawabanKey = 'kunci_jawaban_' . $soalId;
            if ($request->has($kunciJawabanKey)) {
                $kunciValue = $request->input($kunciJawabanKey);
                if (is_array($kunciValue)) {
                    $request->merge(['jawaban_benar_mcma' => $kunciValue]);
                } else {
                    $request->merge(['jawaban_benar' => $kunciValue]);
                }
            }
        }
        
        $request->validate([
            'pertanyaan' => 'required|string',
            'jenis_soal' => 'required|in:pilihan_ganda,benar_salah,mcma,isian,uraian',
        ]);

        DB::beginTransaction();
        
        try {
            // Get mata pelajaran ID from name
            $mataPelajaran = MataPelajaran::where('nama', $request->mata_pelajaran)->first();
            if (!$mataPelajaran) {
                throw new \Exception('Mata pelajaran tidak ditemukan');
            }
            
            // Update soal
            $soal->mata_pelajaran_id = $mataPelajaran->id;
            $soal->pertanyaan = $request->pertanyaan;
            $soal->jenis_soal = $request->jenis_soal;
            $soal->pembahasan = $request->pembahasan;
            
            // Handle gambar pertanyaan upload
            $gambarPertanyaanKey = $soalId ? 'gambar_soal_' . $soalId : 'gambar_pertanyaan';
            if ($request->hasFile($gambarPertanyaanKey)) {
                // Delete old image
                if ($soal->gambar_pertanyaan) {
                    Storage::disk('public')->delete($soal->gambar_pertanyaan);
                }
                $soal->gambar_pertanyaan = $request->file($gambarPertanyaanKey)->store('soal', 'public');
            }
            
            $soal->save();
            
            // Update pilihan jawaban if applicable
            if (in_array($request->jenis_soal, ['pilihan_ganda', 'mcma'])) {
                // Delete old pilihan jawaban and their images
                $oldPilihan = $soal->pilihanJawaban;
                foreach ($oldPilihan as $pil) {
                    if ($pil->gambar_jawaban) {
                        Storage::disk('public')->delete($pil->gambar_jawaban);
                    }
                }
                $soal->pilihanJawaban()->delete();
                
                // Create new pilihan jawaban
                $labels = ['a', 'b', 'c', 'd'];
                $jawabanBenar = [];
                
                foreach ($labels as $label) {
                    // Field name format: pilihan_${soalId}_a (not pilihan_a_${soalId})
                    $pilihanField = $soalId ? 'pilihan_' . $soalId . '_' . $label : 'pilihan_' . $label;
                    
                    if ($request->filled($pilihanField)) {
                        $gambarPath = null;
                        // Field name format: gambar_pilihan_${soalId}_a
                        $gambarField = $soalId ? 'gambar_pilihan_' . $soalId . '_' . $label : 'gambar_pilihan_' . $label;
                        
                        if ($request->hasFile($gambarField)) {
                            $gambarPath = $request->file($gambarField)->store('jawaban', 'public');
                        }
                        
                        $isBenar = false;
                        if ($request->jenis_soal == 'pilihan_ganda') {
                            $jawabanBenarValue = $request->jawaban_benar ?? '';
                            $isBenar = (strtolower($jawabanBenarValue) === $label);
                        } elseif ($request->jenis_soal == 'mcma') {
                            $jawabanMcma = $request->jawaban_benar_mcma ?? [];
                            $isBenar = is_array($jawabanMcma) && in_array($label, array_map('strtolower', $jawabanMcma));
                        }
                        
                        PilihanJawaban::create([
                            'soal_id' => $soal->id,
                            'label' => strtoupper($label),
                            'teks_jawaban' => $request->input($pilihanField),
                            'gambar_jawaban' => $gambarPath,
                            'is_benar' => $isBenar,
                        ]);
                        
                        if ($isBenar) {
                            $jawabanBenar[] = strtoupper($label);
                        }
                    }
                }
                
                $soal->update(['jawaban_benar' => implode(',', $jawabanBenar)]);
            } elseif ($request->jenis_soal == 'benar_salah') {
                // Delete old pilihan jawaban
                $soal->pilihanJawaban()->delete();
                
                // Create Benar/Salah options
                PilihanJawaban::create([
                    'soal_id' => $soal->id,
                    'label' => 'Benar',
                    'teks_jawaban' => 'Benar',
                    'is_benar' => ($request->jawaban_benar === 'Benar'),
                ]);
                
                PilihanJawaban::create([
                    'soal_id' => $soal->id,
                    'label' => 'Salah',
                    'teks_jawaban' => 'Salah',
                    'is_benar' => ($request->jawaban_benar === 'Salah'),
                ]);
            } elseif (in_array($request->jenis_soal, ['isian', 'uraian'])) {
                // Delete pilihan jawaban if changing from PG/BS/MCMA
                $soal->pilihanJawaban()->delete();
                
                // Get kunci_jawaban with proper field name
                $kunciJawabanValue = '';
                if ($soalId) {
                    $kunciJawabanKey = 'kunci_jawaban_' . $soalId;
                    $kunciJawabanValue = $request->input($kunciJawabanKey, '');
                } else {
                    $kunciJawabanValue = $request->input('kunci_jawaban', '');
                }
                
                $soal->jawaban_benar = $kunciJawabanValue;
                $soal->kunci_jawaban = $kunciJawabanValue;
            }
            
            $soal->save();
            
            DB::commit();
            
            return redirect()->route('soal.index')->with('success', 'Soal berhasil diupdate');
            
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
}
