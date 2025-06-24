<?php
namespace Database\Seeders;

use App\Models\PortalSetup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummySetupSeeder extends Seeder
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

        // Hapus data sebelumnya
        PortalSetup::truncate();

        // Data setup
        $setups = [
            [
                'nama'     => 'footer_phone',
                'value'    => '(021) 1234 5678',
                'kategori' => 'Footer Section',
            ],
            [
                'nama'     => 'footer_map',
                'value'    => 'Citra Raya, Kabupaten Tangerang',
                'kategori' => 'Footer Section',
            ],
            [
                'nama'     => 'footer_email',
                'value'    => 'uptdpemakaman@gmail.com',
                'kategori' => 'Footer Section',
            ],
        ];

        // Iterasi data simpan ke database
        foreach ($setups as $setuped) {
            PortalSetup::create([
                'uuid'             => Str::uuid(),
                "nama_pengaturan"  => $setuped['nama'],
                "value_pengaturan" => $setuped['value'],
                "kategori"         => $setuped['kategori'],
                "sites"            => "Portal",
                "status"           => "1",
                'uuid_created'     => $user->uuid,
                'uuid_updated'     => $user->uuid,
            ]);
        }

        $this->command->info('Dummy data untuk portal setup berhasil dibuat.');
    }
}