<?php
namespace Database\Seeders;

use App\Models\PortalKategori;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KomparasiKategoriGlobalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mendapatkan data user
        $user = User::whereUsername("admin@mail.com")->firstOrFail();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // truncate PortalKategori
        PortalKategori::truncate();

        // kategori dan subkategori
        $kategori = [
            'Banner'    => ['Content', 'Event', 'Widget', 'Zona Integritas', 'Layanan Internal', 'Layanan Eksternal', 'Footer'],
            'Galeri'    => ['Berita', 'Kegiatan', 'Seminar', 'Event'],
            'Post'      => ['Berita', 'Artikel'],
            'Video'     => ['Umum'],
            'Testimoni' => ['Umum'],
        ];

        // Hitung total item untuk progress bar (kategori + subkategori)
        $totalItems = 0;
        foreach ($kategori as $subcategories) {
            $totalItems += count($subcategories); // Hitung total subkategori
        }

        // Mulai progress bar
        $this->command->getOutput()->progressStart($totalItems);

        foreach ($kategori as $type => $subcategories) {
            foreach ($subcategories as $nama) {
                // Cek apakah kategori sudah ada
                $cek_kategori = PortalKategori::where('nama', $nama)->where('type', $type)->first();
                if (! $cek_kategori) {
                    // Jika kategori belum ada, buat yang baru
                    $value_kategori = [
                        "uuid"         => Str::uuid(),
                        "nama"         => $nama, // Nama subkategori (Umum, Edisi, Event)
                        "slug"         => Str::slug($nama),
                        "type"         => $type, // Type (Infografis, Ebook, Emagazine)
                        "uuid_created" => $user->uuid,
                        "uuid_updated" => $user->uuid,
                    ];
                    PortalKategori::create($value_kategori);
                }
                // Update progress bar setelah setiap kategori diproses
                $this->command->getOutput()->progressAdvance();
            }
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();
    }
}