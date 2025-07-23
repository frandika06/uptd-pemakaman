<?php
namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\TpuDatas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyTpuDatasSeeder extends Seeder
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

        // Truncate tabel TpuDatas untuk memulai dengan data baru
        $this->command->info('Menghapus data lama di tabel tpu_datas...');
        TpuDatas::truncate();

        // Mendapatkan daftar kecamatan
        $this->command->info('Mengambil daftar kecamatan untuk Kabupaten Tangerang (kode: 3603)...');
        $kecamatanList = Helper::getKecamatanList(3603);
        if (! $kecamatanList['status'] || empty($kecamatanList['data'])) {
            $this->command->error('Gagal mengambil daftar kecamatan.');
            return;
        }

        $tpuData    = [];
        $totalItems = 0;

        // Daftar status dan jenis TPU yang mungkin
        $statusOptions   = ['Aktif', 'Tidak Aktif', 'Penuh'];
        $jenisTpuOptions = ['muslim', 'non_muslim', 'gabungan'];

        // Iterasi setiap kecamatan untuk mengumpulkan data
        $this->command->info('Mengumpulkan data kelurahan dan membuat data TPU...');
        foreach ($kecamatanList['data'] as $kecamatan) {
            $this->command->info("Memproses kecamatan: {$kecamatan['name']}...");
            // Mendapatkan daftar kelurahan untuk kecamatan ini
            $desaList = Helper::getDesaList($kecamatan['id']);
            if (! $desaList['status'] || empty($desaList['data'])) {
                $this->command->warn("Gagal mengambil daftar kelurahan untuk kecamatan {$kecamatan['name']}.");
                continue;
            }

            // Membuat data TPU dummy untuk setiap kelurahan
            foreach ($desaList['data'] as $desa) {
                $tpuData[] = [
                    'nama'         => 'TPU ' . $desa['name'],
                    'alamat'       => 'Jl. ' . $desa['name'] . ' Raya, ' . $desa['name'],
                    'kecamatan_id' => $kecamatan['id'],
                    'kelurahan_id' => $desa['id'],
                    'kecamatan'    => $kecamatan['name'],
                    'kelurahan'    => $desa['name'],
                    'jenis_tpu'    => $jenisTpuOptions[array_rand($jenisTpuOptions)],
                    'latitude'     => -6.1 - (rand(0, 1000) / 10000),  // Random latitude di sekitar Kab. Tangerang
                    'longitude'    => 106.4 + (rand(0, 1000) / 10000), // Random longitude di sekitar Kab. Tangerang
                    'status'       => $statusOptions[array_rand($statusOptions)],
                ];
                $totalItems++;
            }
        }

        // Mulai progress bar
        $this->command->info("Memulai proses seeding untuk {$totalItems} data TPU...");
        $this->command->getOutput()->progressStart($totalItems);

        // Proses penyimpanan data
        foreach ($tpuData as $index => $tpu) {
            // Validasi data sebelum proses
            if (empty(trim($tpu['nama']))) {
                $this->command->warn("Nama TPU kosong untuk: {$tpu['nama']}. Dilewati.");
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            // Cek apakah TPU sudah ada berdasarkan nama
            $cek_tpu = TpuDatas::where('nama', $tpu['nama'])->first();
            if (! $cek_tpu) {
                // Jika TPU belum ada, buat yang baru
                $value_tpu = [
                    'uuid'         => Str::uuid(),
                    'nama'         => trim($tpu['nama']),
                    'alamat'       => $tpu['alamat'],
                    'kecamatan_id' => $tpu['kecamatan_id'],
                    'kelurahan_id' => $tpu['kelurahan_id'],
                    'kecamatan'    => $tpu['kecamatan'],
                    'kelurahan'    => $tpu['kelurahan'],
                    'jenis_tpu'    => $tpu['jenis_tpu'],
                    'latitude'     => $tpu['latitude'],
                    'longitude'    => $tpu['longitude'],
                    'status'       => $tpu['status'],
                    'uuid_created' => $user->uuid,
                    'uuid_updated' => $user->uuid,
                ];
                TpuDatas::create($value_tpu);
                $this->command->info("Data TPU '{$tpu['nama']}' berhasil dibuat.");
            } else {
                $this->command->warn("Data TPU '{$tpu['nama']}' sudah ada, dilewati.");
            }

            // Update progress bar
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();

        $this->command->info("Seeder TpuDatas selesai dijalankan. {$totalItems} data TPU telah diproses.");
    }
}