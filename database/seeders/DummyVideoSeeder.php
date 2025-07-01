<?php
namespace Database\Seeders;

use App\Models\PortalKategori;
use App\Models\PortalVideo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DummyVideoSeeder extends Seeder
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

        // Mendapatkan kategori video
        $kategoriList = PortalKategori::whereType("Video")->whereStatus("1")->pluck('nama')->toArray();
        if (empty($kategoriList)) {
            $this->command->error('Tidak ada kategori video yang aktif ditemukan.');
            return;
        }

        // Hapus data sebelumnya
        PortalVideo::truncate();

        // Daftar judul video dummy
        $videoTitles = [
            'Panduan Prosedur Pemakaman Umum',
            'Layanan Pemakaman Khusus',
            'Fasilitas Pemakaman Modern',
            'Sejarah Pemakaman Tangerang',
            'Tips Memilih Lokasi Makam',
            'Prosedur Pendaftaran Pemakaman',
            'Layanan Konsultasi Pemakaman',
            'Pemeliharaan Makam Berkualitas',
            'Kegiatan Sosial di Pemakaman',
            'Peringatan Hari Besar di Pemakaman',
            'Tutorial Pengurusan Dokumen Pemakaman',
            'Fasilitas Pendukung Pemakaman',
            'Layanan Pemakaman Ramah Lingkungan',
            'Pengelolaan Makam Digital',
            'Kisah Inspiratif dari Pemakaman',
            'Prosedur Pemakaman Darurat',
            'Layanan Doa Bersama',
            'Pemeliharaan Taman Makam',
            'Informasi Tarif Pemakaman 2025',
            'Panduan Keamanan Pemakaman',
            'Layanan Transportasi Jenazah',
            'Dokumentasi Upacara Pemakaman',
            'Pengelolaan Data Pemakaman',
            'Layanan Konseling Keluarga',
            'Pameran Fasilitas Pemakaman',
            'Prosedur Pemakaman Multi Agama',
            'Pengenalan Staf UPTD Pemakaman',
            'Layanan Pemakaman Online',
            'Pemeliharaan Infrastruktur Pemakaman',
            'Kegiatan Amal di Pemakaman',
            'Panduan Ziarah Makam',
            'Layanan Dokumentasi Video Pemakaman',
            'Pengenalan Teknologi Pemakaman',
            'Layanan Pemakaman untuk Lansia',
            'Pemeliharaan Kebersihan Makam',
            'Kegiatan Relawan Pemakaman',
            'Panduan Pemakaman Syariah',
            'Layanan Dukungan Psikologis',
            'Pengenalan Program Pemakaman',
            'Layanan Pemakaman Publik',
            'Tutorial Penggunaan Aplikasi Pemakaman',
            'Pemeliharaan Monumen Makam',
            'Kegiatan Edukasi Pemakaman',
            'Layanan Pemakaman Inklusif',
            'Panduan Administrasi Pemakaman',
            'Layanan Konsultasi Online',
            'Pemeliharaan Fasilitas Pemakaman',
            'Kegiatan Komunitas Pemakaman',
            'Panduan Pemakaman Tradisional',
            'Layanan Informasi Pemakaman',
        ];

        // Status yang mungkin
        $statuses = ['Draft', 'Pending Review', 'Published', 'Scheduled', 'Archived'];

        // Sumber yang mungkin
        $sources = ['YouTube', 'Upload'];

        // Generate 50 videos
        foreach ($videoTitles as $index => $title) {
            $uuid      = Str::uuid();
            $tahun     = Carbon::now()->year;
            $slug      = Str::slug($title);
            $cekslug   = PortalVideo::whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . '-' . $this->gencode(4) : $slug;
            $status    = $statuses[array_rand($statuses)];
            $sumber    = $sources[array_rand($sources)];
            $kategori  = $kategoriList[array_rand($kategoriList)];
            $path      = "video/{$tahun}/{$uuid}";
            $tanggal   = Carbon::now()->subDays(rand(0, 365));

            // Data dasar
            $value = [
                'uuid'         => $uuid,
                'judul'        => $title,
                'slug'         => $inputslug,
                'deskripsi'    => Str::limit("{$title} - Lorem ipsum dolor sit amet, consectetur adipiscing elit.", 160),
                'sumber'       => $sumber,
                'kategori'     => $kategori,
                'status'       => $status,
                'tanggal'      => $tanggal,
                'views'        => $status === 'Published' ? rand(100, 10000) : 0,
                'uuid_created' => $user->uuid,
                'uuid_updated' => $user->uuid,
            ];

            // Handle sumber
            if ($sumber === 'YouTube') {
                $value['url'] = "https://www.youtube.com/watch?v=" . $this->gencode(11);
            } else {
                $filename          = Str::slug($title) . ".mp4";
                $thumbnailFilename = Str::slug($title) . ".jpg";
                $videoPath         = "{$path}/{$filename}";
                $thumbnailPath     = "{$path}/{$thumbnailFilename}";
                $dummyImageUrl     = "https://dummyimage.com/1280x720/000/fff&text=" . urlencode($title);

                // Simulasi file video (hanya path, tidak benar-benar upload file besar)
                $value['url']  = $videoPath;
                $value['tipe'] = 'mp4';
                $value['size'] = rand(10000, 200000); // Ukuran dalam KB (10MB-200MB)

                // Download dan simpan thumbnail
                try {
                    $imageContent = Http::get($dummyImageUrl)->body();
                    Storage::disk('public')->put($thumbnailPath, $imageContent);
                    $value['thumbnails'] = $thumbnailPath;
                } catch (\Exception $e) {
                    $this->command->error("Failed to download thumbnail for {$title}: " . $e->getMessage());
                    continue;
                }
            }

            // Post (opsional, 50% kemungkinan ada post)
            if (rand(0, 1)) {
                $postFilename = Str::slug($title) . "_post.jpg";
                $postPath     = "{$path}/{$postFilename}";
                $dummyPostUrl = "https://dummyimage.com/600x400/000/fff&text=" . urlencode($title . " Post");
                try {
                    $imageContent = Http::get($dummyPostUrl)->body();
                    Storage::disk('public')->put($postPath, $imageContent);
                    $value['post'] = $postPath;
                } catch (\Exception $e) {
                    $this->command->error("Failed to download post image for {$title}: " . $e->getMessage());
                }
            }

            // Insert data
            try {
                PortalVideo::create($value);
            } catch (\Exception $e) {
                $this->command->error("Failed to create video {$title}: " . $e->getMessage());
                continue;
            }
        }

        $this->command->info('Dummy data untuk video berhasil dibuat.');
    }

    /**
     * Generate random code (mimicking Helper::gencode)
     */
    private function gencode($length)
    {
        $characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}