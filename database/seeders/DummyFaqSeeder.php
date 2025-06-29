<?php
namespace Database\Seeders;

use App\Models\PortalFAQ;
use App\Models\PortalFAQList;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class DummyFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Mendapatkan data user
        $user = User::whereUsername("admin@mail.com")->firstOrFail();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        $faker       = Faker::create();
        $output      = new ConsoleOutput();
        $progressBar = new ProgressBar($output, 10);

        $progressBar->start();

        // membersihkan FAQ
        PortalFAQList::truncate();
        PortalFAQ::truncate();
        for ($i = 0; $i < 10; $i++) {
            $faqUuid       = Str::uuid();
            $deskripsi     = "Deskripsi FAQ ke-$i yang mencakup.";
            $judul         = $faker->sentence(6);
            $filename      = Str::slug($judul) . ".png";
            $path          = "faq/" . date('Y') . "/" . $faqUuid . "/" . $filename;
            $dummyImageUrl = "https://dummyimage.com/600x400/000/fff&text=" . urlencode($judul);

            // Download and save image to storage
            try {
                $imageContent = Http::get($dummyImageUrl)->body();
                Storage::disk('public')->put($path, $imageContent);
            } catch (\Exception $e) {
                $this->command->error("Failed to download image for {$judul}: " . $e->getMessage());
                continue;
            }

            $faq = PortalFAQ::create([
                'uuid'         => $faqUuid,
                'judul'        => $judul,
                'slug'         => Str::slug($judul),
                'deskripsi'    => $deskripsi,
                'thumbnails'   => $path,
                'tanggal'      => Carbon::now(),
                'status'       => "1",
                'created_at'   => now(),
                'updated_at'   => now(),
                'uuid_created' => $user->uuid,
                'uuid_updated' => $user->uuid,
            ]);

            for ($j = 0; $j < 10; $j++) {
                PortalFAQList::create([
                    'uuid'            => Str::uuid(),
                    'uuid_portal_faq' => $faqUuid,
                    'pertanyaan'      => $faker->sentence(10),
                    'jawaban'         => $faker->paragraph(4),
                    'status'          => "1",
                    'created_at'      => now(),
                    'updated_at'      => now(),
                    'uuid_created'    => $user->uuid,
                    'uuid_updated'    => $user->uuid,
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("\nDummy FAQ data has been seeded successfully.");
    }

    /**
     * Fetch a random image URL from an API.
     *
     * @return string|null
     */
    private function fetchRandomImage()
    {
        try {
            $response = Http::get('https://api.unsplash.com/photos/random', [
                'client_id'   => 'CvUbgfvrVjq90VG-3dVw7FCAuxGN_bIEVQXVO_pH5XE', // Masukkan API Key Unsplash Anda
                'query'       => 'human activities',                            // Kata kunci gambar
                'orientation' => 'landscape',
            ]);

            if ($response->successful()) {
                return $response->json()['urls']['regular'] ?? null;
            }
        } catch (\Exception $e) {
            $this->command->error('Gagal mengambil gambar dari Unsplash: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Save an image to local storage.
     *
     * @param string $imageUrl
     * @param string $localPath
     */
    private function saveImageToLocal($imageUrl, $localPath)
    {
        try {
            $imageContent = Http::get($imageUrl)->body();
            Storage::disk('public')->put($localPath, $imageContent);
        } catch (\Exception $e) {
            $this->command->error('Gagal menyimpan gambar ke storage: ' . $e->getMessage());
        }
    }
}