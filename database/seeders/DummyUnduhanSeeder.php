<?php
namespace Database\Seeders;

use App\Models\PortalKategori;
use App\Models\PortalUnduhan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DummyUnduhanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Mendapatkan data user
        $user = User::whereUsername("admin@mail.com")->first();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Mendapatkan kategori unduhan
        $kategoriList = PortalKategori::whereType("Unduhan")->whereStatus("1")->pluck('nama')->toArray();
        if (empty($kategoriList)) {
            $this->command->error('Tidak ada kategori unduhan yang aktif ditemukan.');
            return;
        }

        // Hapus data sebelumnya
        PortalUnduhan::truncate();

        // Daftar judul unduhan dummy
        $unduhanTitles = [
            'Panduan Prosedur Pendaftaran Pemakaman',
            'Formulir Pendaftaran Makam',
            'Regulasi Pemakaman Kabupaten Tangerang',
            'Laporan Tahunan UPTD Pemakaman 2024',
            'Brosur Layanan Pemakaman',
            'Panduan Ziarah Makam Aman',
            'Dokumen Hukum Pemakaman',
            'Infografis Fasilitas Pemakaman',
            'SOP Pemeliharaan Makam',
            'Panduan Pemakaman Syariah',
            'Tarif Layanan Pemakaman 2025',
            'Formulir Permohonan Pemindahan Makam',
            'Laporan Keuangan Pemakaman 2023',
            'Panduan Keamanan Pemakaman',
            'Dokumen Persyaratan Pemakaman',
            'Infografis Prosedur Pemakaman',
            'Panduan Layanan Konseling Keluarga',
            'Regulasi Pemakaman Multi Agama',
            'Laporan Kegiatan Komunitas Pemakaman',
            'Formulir Pemesanan Makam',
            'Panduan Pemeliharaan Taman Makam',
            'Dokumen SOP Transportasi Jenazah',
            'Infografis Statistik Pemakaman',
            'Panduan Administrasi Pemakaman',
            'Laporan Audit Fasilitas Pemakaman',
            'Formulir Izin Ziarah Kelompok',
            'Regulasi Pemakaman Ramah Lingkungan',
            'Panduan Penggunaan Aplikasi Pemakaman',
            'Dokumen Kebijakan Pemakaman',
            'Infografis Layanan Pemakaman Online',
            'Laporan Kegiatan Amal Pemakaman',
            'Formulir Konsultasi Pemakaman',
            'Panduan Pemakaman Inklusif',
            'Dokumen Prosedur Pemakaman Darurat',
            'Infografis Pemeliharaan Infrastruktur',
            'Laporan Evaluasi Layanan Pemakaman',
            'Formulir Pendaftaran Relawan Pemakaman',
            'Panduan Pemakaman Tradisional',
            'Dokumen Kebijakan Kebersihan Makam',
            'Infografis Program Edukasi Pemakaman',
            'Laporan Kegiatan Sosial Pemakaman',
            'Formulir Permohonan Monumen Makam',
            'Panduan Layanan Doa Bersama',
            'Dokumen SOP Keamanan Pemakaman',
            'Infografis Tarif Pemakaman',
            'Laporan Pemeliharaan Makam 2024',
            'Formulir Konsultasi Online',
            'Panduan Pengelolaan Data Pemakaman',
            'Dokumen Regulasi Ziarah Makam',
            'Infografis Kegiatan Relawan Pemakaman',
        ];

        // Status yang mungkin
        $statuses = ['Draft', 'Pending Review', 'Published', 'Scheduled', 'Archived'];

        // Sumber yang mungkin
        $sources = ['Link', 'Upload'];

        // Inisialisasi progress bar
        $totalRecords = count($unduhanTitles);
        $this->command->getOutput()->progressStart($totalRecords);

        // Generate 50 unduhan
        foreach ($unduhanTitles as $index => $title) {
            $uuid               = Str::uuid();
            $tahun              = Carbon::now()->year;
            $slug               = Str::slug($title);
            $cekslug            = PortalUnduhan::whereSlug($slug)->count();
            $inputslug          = $cekslug > 0 ? $slug . '-' . $this->gencode(4) : $slug;
            $status             = $statuses[array_rand($statuses)];
            $sumber             = $sources[array_rand($sources)];
            $selectedCategories = (array) array_rand(array_flip($kategoriList), rand(1, 3));
            $kategori           = implode(',', $selectedCategories);
            $tanggal            = Carbon::now()->subDays(rand(0, 365));
            $isPrivate          = rand(0, 1); // 50% chance for private publication

            // Data dasar
            $value = [
                'uuid'           => $uuid,
                'judul'          => $title,
                'slug'           => $inputslug,
                'deskripsi'      => Str::limit("{$title} - Lorem ipsum dolor sit amet, consectetur adipiscing elit.", 160),
                'sumber'         => $sumber,
                'kategori'       => $kategori,
                'status'         => $status,
                'tanggal'        => $tanggal,
                'views'          => $status === 'Published' ? rand(100, 10000) : 0,
                'downloads'      => $status === 'Published' ? rand(50, 5000) : 0,
                'uuid_created'   => $user->uuid,
                'uuid_updated'   => $user->uuid,
                'tipe_publikasi' => $isPrivate ? 'Private' : 'Public',
                'password'       => $isPrivate ? Str::random(8) : null,
            ];

            // Handle sumber
            if ($sumber === 'Link') {
                $value['url']  = "https://example.com/dokumen/" . Str::slug($title) . ".pdf";
                $value['tipe'] = null;
                $value['size'] = null;
            } else {
                $filename = Str::slug($title) . ".pdf";
                $filePath = "unduhan/{$tahun}/{$uuid}/{$filename}";
                // Simulasi file unduhan
                Storage::disk('public')->put($filePath, 'Dummy PDF content for seeding');
                $value['url']  = $filePath;
                $value['tipe'] = 'pdf';
                $value['size'] = rand(100000, 5000000); // Ukuran dalam bytes (100KB-5MB)
            }

            // Thumbnails (required)
            $thumbnailFilename = Str::slug($title) . ".jpg";
            $thumbnailPath     = "unduhan/{$tahun}/{$uuid}/{$thumbnailFilename}";
            $dummyImageUrl     = "https://dummyimage.com/1280x720/000/fff&text=" . urlencode($title);
            try {
                $imageContent = Http::get($dummyImageUrl)->body();
                Storage::disk('public')->put($thumbnailPath, $imageContent);
                $value['thumbnails'] = $thumbnailPath;
            } catch (\Exception $e) {
                $this->command->error("Failed to download thumbnail for {$title}: " . $e->getMessage());
                $value['thumbnails'] = null;
            }

            // Post (optional, 50% chance)
            if (rand(0, 1)) {
                $postFilename = Str::slug($title) . "_post.jpg";
                $postPath     = "unduhan/{$tahun}/{$uuid}/{$postFilename}";
                $dummyPostUrl = "https://dummyimage.com/600x400/000/fff&text=" . urlencode($title . " Post");
                try {
                    $imageContent = Http::get($dummyPostUrl)->body();
                    Storage::disk('public')->put($postPath, $imageContent);
                    $value['post'] = $postPath;
                } catch (\Exception $e) {
                    $this->command->error("Failed to download post image for {$title}: " . $e->getMessage());
                    $value['post'] = null;
                }
            }

            // Insert data
            try {
                PortalUnduhan::create($value);
            } catch (\Exception $e) {
                $this->command->error("Failed to create unduhan {$title}: " . $e->getMessage());
                continue;
            }

            // Update progress bar
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();
        $this->command->info("Successfully seeded {$totalRecords} dummy unduhan records.");
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