<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Soal;
use App\Models\Simulasi;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\DB;

class FixMataPelajaranData extends Command
{
    protected $signature = 'fix:mata-pelajaran';
    protected $description = 'Fix mata_pelajaran_id on soal and simulasi records based on kode_soal prefix';

    public function handle()
    {
        $this->info('Fixing mata_pelajaran_id based on kode_soal prefix...');

        // Get all mata pelajaran keyed by kode
        $mapelByKode = MataPelajaran::all()->keyBy(function ($m) {
            return strtoupper($m->kode);
        });

        $this->info("Found {$mapelByKode->count()} mata pelajaran records:");
        foreach ($mapelByKode as $kode => $m) {
            $this->line("  [{$kode}] {$m->nama} (id: {$m->id})");
        }

        // Fix soal records
        $soals = Soal::where('jenis_soal', 'paket')->get();
        $fixedSoal = 0;

        foreach ($soals as $soal) {
            $prefix = strtoupper(explode('-', $soal->kode_soal)[0] ?? '');
            if ($prefix && $mapelByKode->has($prefix)) {
                $correctMapelId = $mapelByKode[$prefix]->id;
                if ($soal->mata_pelajaran_id != $correctMapelId) {
                    $oldId = $soal->mata_pelajaran_id;
                    $soal->mata_pelajaran_id = $correctMapelId;
                    $soal->save();
                    $fixedSoal++;
                    $this->warn("  Fixed soal [{$soal->kode_soal}]: mata_pelajaran_id {$oldId} -> {$correctMapelId} ({$mapelByKode[$prefix]->nama})");
                }
            }
        }

        // Fix simulasi records based on their linked soal paket
        $simulasis = Simulasi::with('simulasiSoal.soal')->get();
        $fixedSimulasi = 0;

        foreach ($simulasis as $sim) {
            $paketSoal = $sim->simulasiSoal->first()?->soal;
            if ($paketSoal) {
                $prefix = strtoupper(explode('-', $paketSoal->kode_soal)[0] ?? '');
                if ($prefix && $mapelByKode->has($prefix)) {
                    $correctMapelId = $mapelByKode[$prefix]->id;
                    if ($sim->mata_pelajaran_id != $correctMapelId) {
                        $oldId = $sim->mata_pelajaran_id;
                        $sim->mata_pelajaran_id = $correctMapelId;
                        $sim->save();
                        $fixedSimulasi++;
                        $this->warn("  Fixed simulasi [{$sim->nama_simulasi}]: mata_pelajaran_id {$oldId} -> {$correctMapelId} ({$mapelByKode[$prefix]->nama})");
                    }
                }
            }
        }

        // Clear cache so changes are reflected immediately
        \Illuminate\Support\Facades\Cache::forget('simulasi_active_list');
        \Illuminate\Support\Facades\Cache::forget('generate_paket_soal');

        $this->info("Done! Fixed {$fixedSoal} soal records and {$fixedSimulasi} simulasi records.");

        return 0;
    }
}
