<?php
namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\PortalPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DummyPostSeeder extends Seeder
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
        PortalPost::whereIn("kategori", ["Profile", "TOS", "Kebijakan"])->forceDelete();

        // Define categories
        $categories = [
            'Profile' => [
                'Visi dan Misi',
                'Struktur Organisasi',
                'Tugas dan Fungsi',
                'Regulasi dan SOP',
                'Laporan & Monitoring',
            ],
        ];

        // Iterate over categories and services
        foreach ($categories as $category => $services) {
            foreach ($services as $service) {
                // Generate UUID and file path
                $uuid          = Str::uuid();
                $filename      = Str::slug($service) . ".png";
                $path          = "halaman/" . date('Y') . "/" . $uuid . "/" . $filename;
                $dummyImageUrl = "https://dummyimage.com/600x400/000/fff&text=" . urlencode($service);

                // Download and save image to storage
                try {
                    $imageContent = Http::get($dummyImageUrl)->body();
                    Storage::disk('public')->put($path, $imageContent);
                } catch (\Exception $e) {
                    $this->command->error("Failed to download image for {$service}: " . $e->getMessage());
                    continue;
                }

                // slug
                $slug      = \Str::slug($service);
                $cekslug   = PortalPost::whereSlug($slug)->count();
                $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;

                // Insert data into the database
                PortalPost::create([
                    'uuid'         => $uuid,
                    'judul'        => $service,
                    'slug'         => $inputslug,
                    'deskripsi'    => "{$service} - Lorem Ipsum is simply dummy text of the printing and typesetting industry",
                    'post'         => "{$service} - Lorem Ipsum is simply dummy text of the printing and typesetting industry",
                    'thumbnails'   => $path,
                    'tanggal'      => Carbon::now(),
                    'views'        => mt_rand(0, 10000),
                    'kategori'     => 'Berita',
                    'status'       => 'Published',
                    'uuid_created' => $user->uuid,
                    'uuid_updated' => $user->uuid,
                ]);
            }
        }

        $this->command->info('Dummy data untuk halaman berhasil dibuat.');
    }
}