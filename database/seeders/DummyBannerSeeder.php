<?php
namespace Database\Seeders;

use App\Models\PortalBanner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DummyBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Mendapatkan data user
        $user = User::whereUsername("admin@mail.com")->firstOrFail();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Hapus data sebelumnya berdasarkan kategori
        PortalBanner::whereIn("kategori", ["Content", "Event", "Widget", "Zona Integritas", "Footer"])->forceDelete();

        // Define categories and sample banner titles
        $categories = [
            'Content'         => [
                'Promo Layanan Pemakaman',
                'Informasi Tarif Pemakaman',
                'Panduan Prosedur Pemakaman',
            ],
            'Event'           => [
                'Peringatan Hari Besar Keagamaan',
                'Kegiatan Sosial Pemakaman',
                'Pameran Layanan Pemakaman',
            ],
            'Widget'          => [
                'Banner Iklan Samping',
                'Widget Informasi Kontak',
                'Banner Promosi Layanan',
            ],
            'Zona Integritas' => [
                'Kampanye Anti Korupsi',
                'Transparansi Layanan',
                'Integritas Pelayanan Publik',
            ],
            'Footer'          => [
                'Link Media Sosial',
                'Informasi Kontak UPTD',
                'Peta Lokasi Pemakaman',
            ],
        ];

        // Iterate over categories and banners
        foreach ($categories as $category => $banners) {
            foreach ($banners as $banner) {
                // Generate UUID and file path
                $uuid          = Str::uuid();
                $filename      = Str::slug($banner) . ".png";
                $path          = "banner/" . date('Y') . "/" . $uuid . "/" . $filename;
                $dummyImageUrl = "https://dummyimage.com/600x400/000/fff&text=" . urlencode($banner);

                // Download and save image to storage
                try {
                    $imageContent = Http::get($dummyImageUrl)->body();
                    Storage::disk('public')->put($path, $imageContent);
                } catch (\Exception $e) {
                    $this->command->error("Failed to download image for {$banner}: " . $e->getMessage());
                    continue;
                }

                // Insert data into the database
                PortalBanner::create([
                    'uuid'         => $uuid,
                    'judul'        => $banner,
                    'url'          => 'https://example.com/' . Str::slug($banner),
                    'deskripsi'    => "{$banner} - Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                    'thumbnails'   => $path,
                    'tanggal'      => Carbon::now(),
                    'warna_text'   => '#FFFFFF',
                    'kategori'     => $category,
                    'status'       => '1', // Published
                    'uuid_created' => $user->uuid,
                    'uuid_updated' => $user->uuid,
                ]);
            }
        }

        $this->command->info('Dummy data untuk banner berhasil dibuat.');
    }
}