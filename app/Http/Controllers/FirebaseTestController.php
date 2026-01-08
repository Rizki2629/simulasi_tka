<?php

namespace App\Http\Controllers;

use App\Services\FirestoreRestClient;
use Illuminate\Http\Request;

class FirebaseTestController extends Controller
{
    public function __construct(
        protected FirestoreRestClient $firestore,
    ) {}

    /**
     * Test koneksi dan operasi dasar Firebase
     */
    public function testFirebase()
    {
        try {
            // Test CREATE
            $testData = [
                'nama' => 'Test Data',
                'created_at' => now()->toDateTimeString(),
                'status' => 'active'
            ];

            $docId = 'test_' . now()->format('Ymd_His') . '_' . uniqid();
            $this->firestore->setDocument('test_collection', $docId, $testData);
            
            // Test READ
            $readData = $this->firestore->getDocument('test_collection', $docId);
            
            // Test UPDATE
            $this->firestore->setDocument('test_collection', $docId, array_merge($testData, ['status' => 'updated']));
            
            // Test READ ALL
            $allData = $this->firestore->listCollection('test_collection');
            
            // Test QUERY
            $queryResult = array_values(array_filter($allData, function ($row) {
                return ($row['status'] ?? null) === 'updated';
            }));
            
            // Test DELETE
            $this->firestore->deleteDocument('test_collection', $docId);
            
            return response()->json([
                'success' => true,
                'message' => 'Firebase connection successful!',
                'test_results' => [
                    'created_id' => $docId,
                    'read_data' => $readData,
                    'all_data_count' => is_array($allData) ? count($allData) : 0,
                    'query_result_count' => is_array($queryResult) ? count($queryResult) : 0,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Contoh: Simpan soal ke Firestore
     */
    public function storeSoal(Request $request)
    {
        $data = [
            'teks_soal' => $request->teks_soal,
            'tipe_soal' => $request->tipe_soal,
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'created_at' => now()->toDateTimeString(),
        ];

        $id = 'soal_' . now()->format('Ymd_His') . '_' . uniqid();
        $this->firestore->setDocument('soal', $id, $data);
        
        return response()->json([
            'success' => true,
            'soal_id' => $id
        ]);
    }

    /**
     * Contoh: Ambil semua soal
     */
    public function getSoal()
    {
        $soal = $this->firestore->listCollection('soal');
        
        return response()->json([
            'success' => true,
            'data' => $soal
        ]);
    }

    /**
     * Contoh: Ambil soal berdasarkan mata pelajaran
     */
    public function getSoalByMapel($mapelId)
    {
        $rows = $this->firestore->listCollection('soal');
        $soal = array_values(array_filter($rows, function ($row) use ($mapelId) {
            return (int) ($row['mata_pelajaran_id'] ?? 0) === (int) $mapelId;
        }));
        
        return response()->json([
            'success' => true,
            'data' => $soal
        ]);
    }
}
