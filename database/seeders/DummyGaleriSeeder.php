<?php

namespace Database\Seeders;

use App\Models\PortalGaleri;
use App\Models\PortalGaleriList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DummyGaleriSeeder extends Seeder
{
    public function run()
    {
        // Mendapatkan data user
        $user = User::whereUsername("admin@mail.com")->firstOrFail();
        if (!$user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Hapus data sebelumnya
        PortalGaleri::truncate();
        PortalGaleriList::truncate();

        // Kategori galeri
        $categories = ['Berita', 'Event', 'Kegiatan', 'Seminar'];

        // Status default
        $statusOptions = ['Published'];

        // Minimal 9 galeri utama
        $totalGaleri = 9;
        $photosPerGaleri = 8;

        for ($i = 1; $i <= $totalGaleri; $i++) {
            $uuidGaleri = Str::uuid();
            $judulGaleri = "Galeri Kegiatan " . $i;
            $slug = Str::slug($judulGaleri);
            $kategori = $categories[array_rand($categories)];
            $status = $statusOptions[array_rand($statusOptions)];
            $deskripsi = "Deskripsi galeri kegiatan ke-$i yang mencakup $kategori.";
            $path = "galeri/" . date('Y') . "/" . $uuidGaleri;

            // Fetch random thumbnail
            $thumbnailUrl = $this->fetchRandomImage();
            $thumbnailPath = "$path/thumbnails.png";

            // Save thumbnail to local storage
            if ($thumbnailUrl) {
                $this->saveImageToLocal($thumbnailUrl, $thumbnailPath);
            }

            // Simpan data galeri utama
            PortalGaleri::create([
                'uuid' => $uuidGaleri,
                'judul' => $judulGaleri,
                'slug' => $slug,
                'deskripsi' => $deskripsi,
                'thumbnails' => $thumbnailPath,
                'tanggal' => Carbon::now(),
                'views' => rand(0, 1000),
                'kategori' => $kategori,
                'status' => $status,
                "uuid_created" => $user->uuid,
                "uuid_updated" => $user->uuid,
            ]);

            // Buat foto-foto untuk galeri
            for ($j = 1; $j <= $photosPerGaleri; $j++) {
                $uuidFoto = Str::uuid();
                $judulFoto = "Foto Kegiatan {$i}-{$j}";
                $photoUrl = $this->fetchRandomImage();
                $photoPath = "$path/photos/photo-$j.png";

                // Save photo to local storage
                if ($photoUrl) {
                    $this->saveImageToLocal($photoUrl, $photoPath);
                }

                // Simpan data list foto
                PortalGaleriList::create([
                    'uuid' => $uuidFoto,
                    'uuid_galeri' => $uuidGaleri,
                    'no_urut' => $j,
                    'judul' => $judulFoto,
                    'url' => $photoPath,
                    'tipe' => 'PNG',
                    'size' => rand(500, 2000), // Ukuran file dummy
                    'status' => '1',
                    "uuid_created" => $user->uuid,
                    "uuid_updated" => $user->uuid,
                ]);
            }
        }

        $this->command->info('Dummy data galeri dan foto berhasil dibuat.');
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
                'client_id' => 'CvUbgfvrVjq90VG-3dVw7FCAuxGN_bIEVQXVO_pH5XE', // Masukkan API Key Unsplash Anda
                'query' => 'school activities', // Kata kunci gambar
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
