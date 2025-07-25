<?php
namespace Database\Seeders;

use App\Models\TpuLahan;
use App\Models\TpuRefJenisSarpras;
use App\Models\TpuSarpras;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyTpuSarprasSeeder extends Seeder
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

        // Truncate tabel TpuSarpras untuk memulai dengan data baru
        $this->command->info('Menghapus data lama di tabel tpu_sarpras...');
        TpuSarpras::truncate();

        // Mendapatkan semua lahan yang aktif
        $this->command->info('Mengambil data lahan aktif...');
        $lahanList = TpuLahan::all();
        if ($lahanList->isEmpty()) {
            $this->command->error('Tidak ada data lahan aktif yang ditemukan.');
            return;
        }

        // Mendapatkan semua jenis sarpras yang aktif (status = 1)
        $this->command->info('Mengambil data jenis sarpras aktif...');
        $jenisSarprasList = TpuRefJenisSarpras::where('status', '1')->get();
        if ($jenisSarprasList->isEmpty()) {
            $this->command->error('Tidak ada data jenis sarpras aktif yang ditemukan.');
            return;
        }

        $sarprasData = [];
        $totalItems  = 0;

        // Iterasi setiap lahan untuk membuat data sarpras berdasarkan semua jenis sarpras
        $this->command->info('Mengumpulkan data sarpras untuk setiap lahan...');
        foreach ($lahanList as $lahan) {
            $this->command->info("Memproses lahan: {$lahan->kode_lahan}...");
            foreach ($jenisSarprasList as $jenisSarpras) {
                $namaSarpras   = "{$jenisSarpras->nama} - {$lahan->Tpu->nama}";
                $sarprasData[] = [
                    'uuid_lahan'    => $lahan->uuid,
                    'nama'          => $namaSarpras,
                    'jenis_sarpras' => $jenisSarpras->nama,
                    'luas_m2'       => rand(10, 100) + (rand(0, 99) / 100), // Luas antara 10-100 m² dengan 2 desimal
                    'deskripsi'     => "Sarana dan prasarana {$jenisSarpras->nama} untuk lahan {$lahan->kode_lahan}",
                ];
                $totalItems++;
            }
        }

        // Mulai progress bar
        $this->command->info("Memulai proses seeding untuk {$totalItems} data sarpras...");
        $this->command->getOutput()->progressStart($totalItems);

        // Proses penyimpanan data
        foreach ($sarprasData as $index => $sarpras) {
            // Validasi data sebelum proses
            if (empty(trim($sarpras['nama']))) {
                $this->command->warn("Nama sarpras kosong untuk: {$sarpras['nama']}. Dilewati.");
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            // Cek apakah sarpras sudah ada berdasarkan nama dan uuid_lahan
            $cek_sarpras = TpuSarpras::where('nama', $sarpras['nama'])
                ->where('uuid_lahan', $sarpras['uuid_lahan'])
                ->first();
            if (! $cek_sarpras) {
                // Jika sarpras belum ada, buat yang baru
                $value_sarpras = [
                    'uuid'          => Str::uuid(),
                    'uuid_lahan'    => $sarpras['uuid_lahan'],
                    'nama'          => trim($sarpras['nama']),
                    'jenis_sarpras' => $sarpras['jenis_sarpras'],
                    'luas_m2'       => $sarpras['luas_m2'],
                    'deskripsi'     => $sarpras['deskripsi'],
                    'uuid_created'  => $user->uuid,
                    'uuid_updated'  => $user->uuid,
                ];
                TpuSarpras::create($value_sarpras);
                // $this->command->info("Data sarpras '{$sarpras['nama']}' berhasil dibuat.");
            } else {
                $this->command->warn("Data sarpras '{$sarpras['nama']}' sudah ada, dilewati.");
            }

            // Update progress bar
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();

        $this->command->info("Seeder TpuSarpras selesai dijalankan. {$totalItems} data sarpras telah diproses.");
    }
}