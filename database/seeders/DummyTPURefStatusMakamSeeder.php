<?php
namespace Database\Seeders;

use App\Models\TpuRefStatusMakam;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyTPURefStatusMakamSeeder extends Seeder
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

        // Truncate tabel TpuRefStatusMakam untuk memulai dengan data baru
        TpuRefStatusMakam::truncate();

        // Definisi data status makam
        $statusMakam = [
            [
                'nama'      => 'Kosong',
                'deskripsi' => 'Lahan makam tersedia dan belum digunakan',
            ],
            [
                'nama'      => 'Terisi',
                'deskripsi' => 'Lahan makam sudah terisi (jenazah sudah dimakamkan)',
            ],
            [
                'nama'      => 'Cadangan',
                'deskripsi' => 'Disiapkan untuk pengguna tertentu, belum dimakamkan',
            ],
            [
                'nama'      => 'Blokir',
                'deskripsi' => 'Tidak dapat digunakan (zona rawan, sengketa, genangan, atau teknis)',
            ],
            [
                'nama'      => 'Rusak',
                'deskripsi' => 'Tidak layak pakai (terendam, longsor, atau digali ulang)',
            ],
            [
                'nama'      => 'Renovasi',
                'deskripsi' => 'Sementara tidak digunakan karena ada perbaikan',
            ],
            [
                'nama'      => 'Dalam Peralihan',
                'deskripsi' => 'Sedang dalam proses penggantian/pengosongan',
            ],
        ];

        // Hitung total item untuk progress bar
        $totalItems = count($statusMakam);

        // Mulai progress bar
        $this->command->getOutput()->progressStart($totalItems);

        foreach ($statusMakam as $status) {
            // Validasi data sebelum proses
            if (empty(trim($status['nama']))) {
                $this->command->warn("Nama status kosong untuk: {$status['nama']}. Dilewati.");
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            // Cek apakah status sudah ada
            $cek_status = TpuRefStatusMakam::where('nama', $status['nama'])->first();
            if (! $cek_status) {
                // Jika status belum ada, buat yang baru
                $value_status = [
                    'uuid'         => Str::uuid(),
                    'nama'         => trim($status['nama']),
                    'status'       => '1', // Aktif
                    'uuid_created' => $user->uuid,
                    'uuid_updated' => $user->uuid,
                ];
                TpuRefStatusMakam::create($value_status);
            }
            // Update progress bar setelah setiap status diproses
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();

        $this->command->info('Seeder TpuRefStatusMakam selesai dijalankan.');
    }
}