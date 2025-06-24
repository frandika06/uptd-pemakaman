<?php
namespace Database\Seeders;

use App\Models\PortalSosmed;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummySosmedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Mendapatkan data user admin
        $user = User::whereUsername("admin@mail.com")->first();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Hapus data sebelumnya berdasarkan kategori sosmed
        PortalSosmed::truncate();

        // Data sosmed dengan judul dan URL
        $sosmeds = [
            [
                'sosmed' => 'Facebook',
                'url'    => 'https://www.facebook.com/',
            ],
            [
                'sosmed' => 'Twitter',
                'url'    => 'https://x.com/',
            ],
            [
                'sosmed' => 'TikTok',
                'url'    => 'https://www.tiktok.com/',
            ],
            [
                'sosmed' => 'Instagram',
                'url'    => 'https://www.instagram.com/',
            ],
            [
                'sosmed' => 'YouTube',
                'url'    => 'https://www.youtube.com/',
            ],
        ];

        // Iterasi data sosmed dan simpan ke database
        foreach ($sosmeds as $sosmed) {
            PortalSosmed::create([
                'uuid'         => Str::uuid(),
                'sosmed'       => $sosmed['sosmed'],
                'url'          => $sosmed['url'],
                'uuid_created' => $user->uuid,
                'uuid_updated' => $user->uuid,
            ]);
        }

        $this->command->info('Dummy data untuk link sosmed berhasil dibuat.');
    }
}
