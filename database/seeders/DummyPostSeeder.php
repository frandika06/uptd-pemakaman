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
        $user = User::whereUsername("admin@mail.com")->first();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Hapus data sebelumnya berdasarkan kategori
        PortalPost::whereIn("kategori", ["Berita", "Pengumuman", "Artikel"])->forceDelete();

        // Define post categories dan data untuk UPTD Pemakaman
        $postData = [
            'Berita'     => [
                'Pembukaan Tempat Pemakaman Umum Baru di Wilayah Kabupaten Tangerang',
                'Sosialisasi Prosedur Pemakaman Sesuai Protokol Kesehatan',
                'Peningkatan Fasilitas Makam di TPU Wilayah Tangerang Selatan',
                'Program Digitalisasi Data Pemakaman UPTD Tahun 2025',
                'Kerjasama UPTD dengan Rumah Sakit untuk Koordinasi Pemakaman',
            ],
            'Pengumuman' => [
                'Jadwal Operasional UPTD Pemakaman Selama Bulan Ramadhan',
                'Persyaratan Baru Pengurusan Surat Izin Pemakaman',
                'Penutupan Sementara TPU Zona A untuk Renovasi',
                'Tarif Retribusi Pemakaman Tahun 2025',
                'Pengumuman Lokasi Pemakaman COVID-19',
            ],
            'Artikel'    => [
                'Layanan Mobile Jenazah Gratis 24 Jam',
                'Pendaftaran Online Tempat Pemakaman',
                'Konsultasi Gratis Prosedur Pemakaman',
                'Layanan Pemakaman Khusus Protokol Kesehatan',
                'Bantuan Pemakaman untuk Keluarga Tidak Mampu',
            ],
        ];

        $this->command->info('Mulai membuat dummy data postingan untuk UPTD Pemakaman...');

        // Iterate over categories and posts
        foreach ($postData as $category => $posts) {
            $this->command->info("Membuat postingan kategori: {$category}");

            foreach ($posts as $index => $postTitle) {
                // Generate UUID and file path
                $uuid     = Str::uuid();
                $filename = Str::slug($postTitle) . ".jpg";
                $path     = "post/" . date('Y') . "/" . $uuid . "/" . $filename;

                // URL gambar dummy yang relevan dengan konten pemakaman
                $imageKeywords = [
                    'Berita'     => 'cemetery+news',
                    'Pengumuman' => 'announcement+board',
                    'Artikel'    => 'funeral+service',
                ];

                $keyword       = $imageKeywords[$category] ?? 'cemetery';
                $dummyImageUrl = "https://dummyimage.com/800x600/2563eb/ffffff&text=" . urlencode($category);

                // Download and save image to storage
                try {
                    $imageContent = Http::timeout(30)->get($dummyImageUrl)->body();
                    Storage::disk('public')->put($path, $imageContent);
                } catch (\Exception $e) {
                    $this->command->warn("Failed to download image for {$postTitle}: " . $e->getMessage());
                    // Set path kosong jika gagal download
                    $path = null;
                }

                // Generate slug
                $slug      = Str::slug($postTitle);
                $cekslug   = PortalPost::whereSlug($slug)->count();
                $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;

                // Generate random date dalam 3 bulan terakhir
                $randomDate = Carbon::now()->subDays(rand(1, 90));

                // Generate konten yang relevan berdasarkan kategori
                $konten = $this->generateKonten($category, $postTitle);

                // Insert data into the database
                PortalPost::create([
                    'uuid'         => $uuid,
                    'judul'        => $postTitle,
                    'slug'         => $inputslug,
                    'deskripsi'    => $this->generateDeskripsi($category, $postTitle),
                    'post'         => $konten,
                    'thumbnails'   => $path,
                    'tanggal'      => $randomDate,
                    'views'        => mt_rand(50, 2500),
                    'kategori'     => $category,
                    'status'       => 'Published',
                    'uuid_created' => $user->uuid,
                    'uuid_updated' => $user->uuid,
                ]);

                $this->command->info("- Berhasil membuat: {$postTitle}");
            }
        }

        $this->command->info('Dummy data postingan UPTD Pemakaman berhasil dibuat.');
    }

    /**
     * Generate deskripsi berdasarkan kategori dan judul
     */
    private function generateDeskripsi($kategori, $judul)
    {
        $deskripsiTemplates = [
            'Berita'     => "Berita terbaru dari UPTD Pemakaman Kabupaten Tangerang mengenai {$judul}. Informasi lengkap dan akurat untuk masyarakat.",
            'Pengumuman' => "Pengumuman resmi dari UPTD Pemakaman Kabupaten Tangerang terkait {$judul}. Harap diperhatikan oleh seluruh masyarakat.",
            'Artikel'    => "Informasi layanan UPTD Pemakaman: {$judul}. Layanan profesional dan terpercaya untuk membantu masyarakat.",
        ];

        return $deskripsiTemplates[$kategori] ?? "Informasi dari UPTD Pemakaman Kabupaten Tangerang mengenai {$judul}.";
    }

    /**
     * Generate konten detail berdasarkan kategori
     */
    private function generateKonten($kategori, $judul)
    {
        $baseKonten = "<p><strong>{$judul}</strong></p>";

        switch ($kategori) {
            case 'Berita':
                return $baseKonten . "
                <p>UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang menyampaikan informasi terbaru kepada masyarakat terkait {$judul}.</p>

                <p>Dalam upaya meningkatkan pelayanan kepada masyarakat, UPTD Pemakaman terus berkomitmen untuk memberikan layanan yang berkualitas dan sesuai dengan standar yang telah ditetapkan.</p>

                <p><strong>Detail Informasi:</strong></p>
                <ul>
                <li>Informasi ini berlaku untuk seluruh wilayah Kabupaten Tangerang</li>
                <li>Koordinasi dengan pihak terkait telah dilakukan</li>
                <li>Masyarakat dapat menghubungi UPTD untuk informasi lebih lanjut</li>
                </ul>

                <p>Untuk informasi lebih lanjut, masyarakat dapat menghubungi kantor UPTD Pemakaman atau mengunjungi website resmi kami.</p>";

            case 'Pengumuman':
                return $baseKonten . "
                <p>Kepada seluruh masyarakat Kabupaten Tangerang, dengan ini kami sampaikan pengumuman terkait {$judul}.</p>

                <p><strong>Hal yang perlu diperhatikan:</strong></p>
                <ul>
                <li>Pengumuman ini berlaku efektif sejak tanggal diterbitkan</li>
                <li>Masyarakat diharapkan untuk mematuhi ketentuan yang berlaku</li>
                <li>Informasi lebih lanjut dapat diperoleh di kantor UPTD</li>
                </ul>

                <p><strong>Kontak Informasi:</strong><br>
                Telp: (021) 123-4567<br>
                Email: uptd.pemakaman@tangerangkab.go.id</p>

                <p>Demikian pengumuman ini kami sampaikan untuk dapat diketahui dan dipatuhi bersama.</p>";

            case 'Artikel':
                return $baseKonten . "
                <p>UPTD Pemakaman Kabupaten Tangerang dengan bangga mempersembahkan layanan {$judul} untuk kemudahan dan kenyamanan masyarakat.</p>

                <p><strong>Keunggulan Layanan:</strong></p>
                <ul>
                <li>Pelayanan profesional dan berpengalaman</li>
                <li>Tersedia 24 jam sehari, 7 hari seminggu</li>
                <li>Sesuai dengan protokol kesehatan yang berlaku</li>
                <li>Menghormati adat dan kepercayaan yang berbeda</li>
                </ul>

                <p><strong>Cara Mengakses Layanan:</strong></p>
                <ol>
                <li>Hubungi nomor layanan darurat: (021) 123-4567</li>
                <li>Kunjungi kantor UPTD Pemakaman</li>
                <li>Akses melalui website resmi</li>
                </ol>

                <p>Tim kami siap membantu Anda dengan sepenuh hati dalam situasi yang sulit ini.</p>";

            default:
                return $baseKonten . "
                <p>Informasi dari UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang.</p>

                <p>Kami berkomitmen untuk memberikan pelayanan terbaik kepada masyarakat dalam bidang pemakaman dan pengurusan jenazah.</p>

                <p>Untuk informasi lebih lanjut, silakan hubungi kami melalui kontak yang tersedia.</p>";
        }
    }
}