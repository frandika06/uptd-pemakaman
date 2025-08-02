<?php
namespace Database\Seeders;

use App\Models\TpuLahan;
use App\Models\TpuMakam;
use App\Models\TpuRefStatusMakam;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyTpuMakamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Validasi user admin
        $user = User::where('username', 'admin@mail.com')->first();
        if (! $user) {
            return;
        }

        // Truncate tabel TpuMakam
        TpuMakam::truncate();

        // Ambil data lahan aktif
        $lahans = TpuLahan::with('Tpu')->get();
        if ($lahans->isEmpty()) {
            return;
        }

        // Ambil status makam aktif
        $statusMakam = TpuRefStatusMakam::where('status', '1')->pluck('nama')->toArray();
        if (empty($statusMakam)) {
            return;
        }

        $makamData  = [];
        $totalItems = 0;

        // Iterasi setiap lahan untuk membuat makam
        foreach ($lahans as $lahan) {
            if (! $lahan->Tpu) {
                continue;
            }

            $tpu           = $lahan->Tpu;
            $kategoriMakam = $this->getKategoriMakam($tpu->jenis_tpu);
            if (empty($kategoriMakam)) {
                continue;
            }

            foreach ($kategoriMakam as $kategori) {
                $panjang_m        = round(rand(150, 300) / 100, 2);
                $lebar_m          = round(rand(80, 150) / 100, 2);
                $luas_m2          = $panjang_m * $lebar_m;
                $kapasitas        = $this->calculateKapasitas($lahan, $luas_m2, $kategori);
                $makam_terisi     = rand(0, min($kapasitas, 50));
                $sisa_kapasitas   = $kapasitas - $makam_terisi;
                $status           = $statusMakam[array_rand($statusMakam)];
                $kategori_display = $kategori == 'muslim' ? 'Muslim' : 'Non Muslim';
                $keterangan       = "Makam {$kategori_display} untuk lahan {$lahan->kode_lahan}";

                $makamData[] = [
                    'uuid_lahan'     => $lahan->uuid,
                    'kategori_makam' => $kategori,
                    'panjang_m'      => $panjang_m,
                    'lebar_m'        => $lebar_m,
                    'luas_m2'        => $luas_m2,
                    'kapasitas'      => $kapasitas,
                    'makam_terisi'   => $makam_terisi,
                    'sisa_kapasitas' => $sisa_kapasitas,
                    'status_makam'   => $status,
                    'keterangan'     => $keterangan,
                ];
                $totalItems++;
            }
        }

        // Proses seeding dengan progress bar
        $this->command->getOutput()->progressStart($totalItems);

        foreach ($makamData as $makam) {
            if (empty($makam['uuid_lahan']) || empty($makam['status_makam']) || empty($makam['kategori_makam'])) {
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            // Cek duplikasi makam
            $cekMakam = TpuMakam::where('uuid_lahan', $makam['uuid_lahan'])
                ->where('kategori_makam', $makam['kategori_makam'])
                ->first();

            if (! $cekMakam) {
                TpuMakam::create([
                    'uuid'           => Str::uuid(),
                    'uuid_lahan'     => $makam['uuid_lahan'],
                    'kategori_makam' => $makam['kategori_makam'],
                    'panjang_m'      => $makam['panjang_m'],
                    'lebar_m'        => $makam['lebar_m'],
                    'luas_m2'        => $makam['luas_m2'],
                    'kapasitas'      => $makam['kapasitas'],
                    'makam_terisi'   => $makam['makam_terisi'],
                    'sisa_kapasitas' => $makam['sisa_kapasitas'],
                    'status_makam'   => $makam['status_makam'],
                    'keterangan'     => $makam['keterangan'],
                    'uuid_created'   => $user->uuid,
                    'uuid_updated'   => $user->uuid,
                ]);
            }

            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
    }

    /**
     * Determine kategori makam based on jenis TPU.
     *
     * @param string $jenisTpu
     * @return array
     */
    private function getKategoriMakam($jenisTpu)
    {
        if ($jenisTpu == 'muslim') {
            return ['muslim'];
        } elseif ($jenisTpu == 'non_muslim') {
            return ['non_muslim'];
        } elseif ($jenisTpu == 'gabungan') {
            return ['muslim', 'non_muslim'];
        }
        return [];
    }

    /**
     * Calculate kapasitas based on jenis TPU, luas makam, and kategori makam.
     *
     * @param object $lahan
     * @param float $luasMakam
     * @param string $kategoriMakam
     * @return int
     */
    private function calculateKapasitas($lahan, $luasMakam, $kategoriMakam)
    {
        if (! $lahan || ! $lahan->Tpu || $luasMakam <= 0) {
            return 0;
        }

        $luasEfektif = max(0, $lahan->luas_m2 - 200);
        if ($luasEfektif <= 0) {
            return 0;
        }

        $kapasitasDasar = floor($luasEfektif / $luasMakam);

        if ($lahan->Tpu->jenis_tpu == 'muslim' || $lahan->Tpu->jenis_tpu == 'non_muslim') {
            return $kapasitasDasar;
        } elseif ($lahan->Tpu->jenis_tpu == 'gabungan') {
            if ($kategoriMakam == 'muslim') {
                return floor($kapasitasDasar * 0.7);
            } else {
                return floor($kapasitasDasar * 0.3);
            }
        }
        return 0;
    }
}