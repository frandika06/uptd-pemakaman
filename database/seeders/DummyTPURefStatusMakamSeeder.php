<?php
namespace Database\Seeders;

use App\Models\TpuRefStatusMakam;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyTPURefStatusMakamSeeder extends Seeder
{
    public function run()
    {
        $user = User::whereUsername("admin@mail.com")->firstOrFail();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        TpuRefStatusMakam::truncate();

        $statusMakam = [
            [
                'nama'      => 'Kosong',
                'deskripsi' => 'Lahan makam tersedia dan belum digunakan',
            ],
            [
                'nama'      => 'Terisi Sebagian',
                'deskripsi' => 'Lahan makam sudah terisi sebagian, belum mencapai kapasitas maksimal',
            ],
            [
                'nama'      => 'Penuh',
                'deskripsi' => 'Lahan makam sudah terisi penuh sesuai kapasitas',
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

        $this->command->getOutput()->progressStart(count($statusMakam));

        foreach ($statusMakam as $status) {
            if (empty(trim($status['nama']))) {
                $this->command->warn("Nama status kosong. Dilewati.");
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            $cek_status = TpuRefStatusMakam::where('nama', $status['nama'])->first();
            if (! $cek_status) {
                TpuRefStatusMakam::create([
                    'uuid'         => Str::uuid(),
                    'nama'         => trim($status['nama']),
                    'status'       => '1',
                    'uuid_created' => $user->uuid,
                    'uuid_updated' => $user->uuid,
                ]);
            }

            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Seeder TpuRefStatusMakam selesai dijalankan.');
    }
}