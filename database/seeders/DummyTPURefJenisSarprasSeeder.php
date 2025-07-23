<?php
namespace Database\Seeders;

use App\Models\TpuRefJenisSarpras;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyTPURefJenisSarprasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Mendapatkan data user admin
        $user = User::whereUsername("admin@mail.com")->firstOrFail();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Truncate tabel TpuRefJenisSarpras untuk memulai dengan data baru
        TpuRefJenisSarpras::truncate();

        // Definisi data jenis sarpras
        $jenisSarpras = [
            [
                'nama'      => 'Jalan Akses',
                'deskripsi' => 'Jalan utama dan jalan blok dalam area TPU',
            ],
            [
                'nama'      => 'Pagar / Pembatas',
                'deskripsi' => 'Pagar keliling, tembok batas, atau kawat berduri',
            ],
            [
                'nama'      => 'Gerbang Utama',
                'deskripsi' => 'Gapura atau pintu masuk TPU',
            ],
            [
                'nama'      => 'Pos Jaga',
                'deskripsi' => 'Tempat penjaga TPU bertugas',
            ],
            [
                'nama'      => 'Tempat Sampah',
                'deskripsi' => 'Kontainer atau fasilitas pengelolaan sampah',
            ],
            [
                'nama'      => 'Saluran Air',
                'deskripsi' => 'Drainase, got, atau parit',
            ],
            [
                'nama'      => 'Kamar Mandi / WC',
                'deskripsi' => 'Fasilitas toilet umum di area TPU',
            ],
            [
                'nama'      => 'Mushola / Tempat Ibadah',
                'deskripsi' => 'Area ibadah kecil di sekitar TPU',
            ],
            [
                'nama'      => 'Tempat Parkir',
                'deskripsi' => 'Area parkir kendaraan pengunjung atau petugas',
            ],
            [
                'nama'      => 'Gudang / Tempat Alat',
                'deskripsi' => 'Menyimpan alat gali, semprotan, papan nama, dll',
            ],
            [
                'nama'      => 'Penerangan',
                'deskripsi' => 'Tiang lampu, panel listrik',
            ],
            [
                'nama'      => 'Tempat Duduk / Istirahat',
                'deskripsi' => 'Bangku taman, saung, gazebo',
            ],
            [
                'nama'      => 'Bangunan Administrasi',
                'deskripsi' => 'Kantor pengelola atau ruang pengarsipan IPTM',
            ],
        ];

        // Hitung total item untuk progress bar
        $totalItems = count($jenisSarpras);

        // Mulai progress bar
        $this->command->getOutput()->progressStart($totalItems);

        foreach ($jenisSarpras as $sarpras) {
            // Validasi data sebelum proses
            if (empty(trim($sarpras['nama']))) {
                $this->command->warn("Nama sarpras kosong untuk: {$sarpras['nama']}. Dilewati.");
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            // Cek apakah jenis sarpras sudah ada
            $cek_sarpras = TpuRefJenisSarpras::where('nama', $sarpras['nama'])->first();
            if (! $cek_sarpras) {
                // Jika jenis sarpras belum ada, buat yang baru
                $value_sarpras = [
                    'uuid'         => Str::uuid(),
                    'nama'         => trim($sarpras['nama']),
                    'status'       => '1', // Aktif
                    'uuid_created' => $user->uuid,
                    'uuid_updated' => $user->uuid,
                ];
                TpuRefJenisSarpras::create($value_sarpras);
            }
            // Update progress bar setelah setiap sarpras diproses
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();

        $this->command->info('Seeder TpuRefJenisSarpras selesai dijalankan.');
    }
}