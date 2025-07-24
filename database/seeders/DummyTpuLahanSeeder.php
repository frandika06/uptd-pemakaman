<?php
namespace Database\Seeders;

use App\Models\TpuDatas;
use App\Models\TpuLahan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyTpuLahanSeeder extends Seeder
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

        // Truncate tabel TpuLahan untuk memulai dengan data baru
        $this->command->info('Menghapus data lama di tabel tpu_lahans...');
        TpuLahan::truncate();

        // Mendapatkan semua TPU yang aktif
        $this->command->info('Mengambil data TPU aktif...');
        $tpuList = TpuDatas::all();
        if ($tpuList->isEmpty()) {
            $this->command->error('Tidak ada data TPU aktif yang ditemukan.');
            return;
        }

        $lahanData  = [];
        $totalItems = 0;

        // Iterasi setiap TPU untuk membuat 1 atau 2 lahan
        $this->command->info('Mengumpulkan data lahan untuk setiap TPU...');
        foreach ($tpuList as $tpu) {
            $this->command->info("Memproses TPU: {$tpu->nama}...");
            // Tentukan jumlah lahan secara acak (1 atau 2)
            $jumlahLahan = rand(1, 2);

            for ($i = 1; $i <= $jumlahLahan; $i++) {
                $kodeLahan   = 'LH-' . strtoupper(Str::random(5)) . '-' . $tpu->nama;
                $lahanData[] = [
                    'uuid_tpu'   => $tpu->uuid,
                    'kode_lahan' => $kodeLahan,
                    'luas_m2'    => rand(100, 1000),                             // Luas lahan antara 100-1000 m²
                    'latitude'   => $tpu->latitude + (rand(-100, 100) / 10000),  // Sedikit offset dari TPU
                    'longitude'  => $tpu->longitude + (rand(-100, 100) / 10000), // Sedikit offset dari TPU
                    'catatan'    => 'Lahan ' . $i . ' untuk TPU ' . $tpu->nama,
                ];
                $totalItems++;
            }
        }

        // Mulai progress bar
        $this->command->info("Memulai proses seeding untuk {$totalItems} data lahan...");
        $this->command->getOutput()->progressStart($totalItems);

        // Proses penyimpanan data
        foreach ($lahanData as $index => $lahan) {
            // Validasi data sebelum proses
            if (empty(trim($lahan['kode_lahan']))) {
                $this->command->warn("Kode lahan kosong untuk: {$lahan['kode_lahan']}. Dilewati.");
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            // Cek apakah lahan sudah ada berdasarkan kode_lahan
            $cek_lahan = TpuLahan::where('kode_lahan', $lahan['kode_lahan'])->first();
            if (! $cek_lahan) {
                // Jika lahan belum ada, buat yang baru
                $value_lahan = [
                    'uuid'         => Str::uuid(),
                    'uuid_tpu'     => $lahan['uuid_tpu'],
                    'kode_lahan'   => trim($lahan['kode_lahan']),
                    'luas_m2'      => $lahan['luas_m2'],
                    'latitude'     => $lahan['latitude'],
                    'longitude'    => $lahan['longitude'],
                    'catatan'      => $lahan['catatan'],
                    'uuid_created' => $user->uuid,
                    'uuid_updated' => $user->uuid,
                ];
                TpuLahan::create($value_lahan);
                $this->command->info("Data lahan '{$lahan['kode_lahan']}' berhasil dibuat.");
            } else {
                $this->command->warn("Data lahan '{$lahan['kode_lahan']}' sudah ada, dilewati.");
            }

            // Update progress bar
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();

        $this->command->info("Seeder TpuLahan selesai dijalankan. {$totalItems} data lahan telah diproses.");
    }
}
