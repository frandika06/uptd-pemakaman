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
        // Mendapatkan data user admin
        $this->command->info('Memeriksa user admin...');
        $user = User::whereUsername("admin@mail.com")->first();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Truncate tabel TpuMakam untuk memulai dengan data baru
        $this->command->info('Menghapus data lama di tabel tpu_makams...');
        TpuMakam::truncate();

        // Mendapatkan semua lahan yang aktif
        $this->command->info('Mengambil data lahan aktif...');
        $lahans = TpuLahan::with('Tpu')->get();
        if ($lahans->isEmpty()) {
            $this->command->error('Tidak ada data lahan aktif yang ditemukan.');
            return;
        }

        // Mendapatkan status makam yang aktif
        $this->command->info('Mengambil data status makam aktif...');
        $statusMakam = TpuRefStatusMakam::where('status', '1')->pluck('nama')->toArray();
        if (empty($statusMakam)) {
            $this->command->error('Tidak ada data status makam aktif yang ditemukan.');
            return;
        }

        $makamData  = [];
        $totalItems = 0;

        // Iterasi setiap lahan untuk membuat 1 hingga 3 makam
        $this->command->info('Mengumpulkan data makam untuk setiap lahan...');
        foreach ($lahans as $lahan) {
            $this->command->info("Memproses lahan: {$lahan->kode_lahan}...");
            // Tentukan jumlah makam secara acak (1-3)
            $jumlahMakam = rand(1, 3);

            for ($i = 1; $i <= $jumlahMakam; $i++) {
                $panjang_m      = round(rand(150, 300) / 100, 2); // Panjang antara 1.5m - 3m
                $lebar_m        = round(rand(80, 150) / 100, 2);  // Lebar antara 0.8m - 1.5m
                $luas_m2        = $panjang_m * $lebar_m;
                $kapasitas      = $this->calculateKapasitas($lahan, $luas_m2);
                $makam_terisi   = rand(0, 100);
                $sisa_kapasitas = $kapasitas - $makam_terisi;
                $status         = $statusMakam[array_rand($statusMakam)]; // Pilih status secara acak
                $keterangan     = "Makam {$i} untuk lahan {$lahan->kode_lahan}";

                $makamData[] = [
                    'uuid_lahan'     => $lahan->uuid,
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

        // Mulai progress bar
        $this->command->info("Memulai proses seeding untuk {$totalItems} data makam...");
        $this->command->getOutput()->progressStart($totalItems);

        // Proses penyimpanan data
        foreach ($makamData as $index => $makam) {
            // Validasi data sebelum proses
            if (empty($makam['uuid_lahan']) || empty($makam['status_makam'])) {
                $this->command->warn("Data makam tidak valid untuk index: {$index}. Dilewati.");
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            // Cek apakah makam sudah ada berdasarkan uuid_lahan dan keterangan
            $cek_makam = TpuMakam::where('uuid_lahan', $makam['uuid_lahan'])
                ->where('keterangan', $makam['keterangan'])
                ->first();
            if (! $cek_makam) {
                // Jika makam belum ada, buat yang baru
                $value_makam = [
                    'uuid'           => Str::uuid(),
                    'uuid_lahan'     => $makam['uuid_lahan'],
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
                ];
                TpuMakam::create($value_makam);
                $this->command->info("Data makam '{$makam['keterangan']}' berhasil dibuat.");
            } else {
                $this->command->warn("Data makam '{$makam['keterangan']}' sudah ada, dilewati.");
            }

            // Update progress bar
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();

        $this->command->info("Seeder TpuMakam selesai dijalankan. {$totalItems} data makam telah diproses.");
    }

    /**
     * Calculate kapasitas based on jenis TPU and luas makam
     */
    private function calculateKapasitas($lahan, $luas_makam)
    {
        if (! $lahan || ! $lahan->Tpu || $luas_makam <= 0) {
            return 0;
        }

        $luas_lahan   = $lahan->luas_m2;
        $luas_efektif = max(0, $luas_lahan - 200); // Minimal 200 m² untuk sarana prasarana

        if ($luas_efektif <= 0 || $luas_makam <= 0) {
            return 0;
        }

        // Perhitungan kapasitas
        $kapasitas = floor($luas_efektif / $luas_makam);

        return max(0, $kapasitas);
    }
}