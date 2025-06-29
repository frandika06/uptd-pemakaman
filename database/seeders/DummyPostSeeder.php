<?php
namespace Database\Seeders;

use App\Models\PortalKategori;
use App\Models\PortalPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        // Check if posts already exist
        if (PortalPost::count() > 0) {
            $this->command->info("Posts already exist. Skipping seeder.");
            return;
        }

        $this->command->info("Starting DummyPostSeeder...");

        DB::transaction(function () {
            // Mendapatkan data user
            $user = $this->getUser();
            if (! $user) {
                return;
            }

            // Get or create categories
            $categories = $this->getOrCreateCategories();

            // Define post data untuk UPTD Pemakaman
            $postData = $this->getPostData();

            // Calculate total posts
            $totalPosts = array_sum(array_map('count', $postData));

            $progressBar = $this->command->getOutput()->createProgressBar($totalPosts);
            $progressBar->start();

            $postsCreated = [];
            $batchSize    = 5; // Insert in batches

            // Iterate over categories and posts
            foreach ($postData as $category => $posts) {
                $this->command->newLine();
                $this->command->info("Creating posts for category: {$category}");

                foreach ($posts as $index => $postTitle) {
                    try {
                        $postRecord     = $this->createPostRecord($category, $postTitle, $user, $index);
                        $postsCreated[] = $postRecord;

                        // Insert in batches for better performance
                        if (count($postsCreated) >= $batchSize) {
                            PortalPost::insert($postsCreated);
                            $postsCreated = [];
                        }

                        $progressBar->advance();

                    } catch (\Exception $e) {
                        $this->command->error("Failed to create post '{$postTitle}': " . $e->getMessage());
                        $progressBar->advance();
                        continue;
                    }
                }
            }

            // Insert remaining records
            if (! empty($postsCreated)) {
                PortalPost::insert($postsCreated);
            }

            $progressBar->finish();
            $this->command->newLine();
            $this->command->info("DummyPostSeeder completed successfully! Created {$totalPosts} posts.");
        });
    }

    /**
     * Get user for seeding
     */
    private function getUser()
    {
        $user = User::where('username', 'frandika.septa@gmail.com')->first();
        if (! $user) {
            $this->command->error('No suitable user found for seeding. Please run ActorSeeder first.');
            return null;
        }

        return $user;
    }

    /**
     * Get or create post categories
     */
    private function getOrCreateCategories(): array
    {
        $categoryNames = ['Berita', 'Pengumuman', 'Artikel', 'Layanan', 'Informasi'];
        $categories    = [];

        foreach ($categoryNames as $name) {
            $category = PortalKategori::firstOrCreate([
                'nama' => $name,
                'slug' => Str::slug($name),
                'type' => 'Post',
            ], [
                'uuid'   => Str::uuid()->toString(),
                'nama'   => $name,
                'slug'   => Str::slug($name),
                'type'   => 'Post',
                'status' => '1',
            ]);

            $categories[] = $category->nama;
        }

        return $categories;
    }

    /**
     * Get post data structure
     */
    private function getPostData(): array
    {
        return [
            'Berita'     => [
                'Pembukaan Tempat Pemakaman Umum Baru di Wilayah Kabupaten Tangerang',
                'Sosialisasi Prosedur Pemakaman Sesuai Protokol Kesehatan',
                'Peningkatan Fasilitas Makam di TPU Wilayah Tangerang Selatan',
                'Program Digitalisasi Data Pemakaman UPTD Tahun 2025',
                'Kerjasama UPTD dengan Rumah Sakit untuk Koordinasi Pemakaman',
                'Renovasi TPU Utama Kabupaten Tangerang',
                'Launching Website Resmi UPTD Pemakaman',
                'Program CSR untuk Pembangunan Fasilitas TPU',
            ],
            'Pengumuman' => [
                'Jadwal Operasional UPTD Pemakaman Selama Bulan Ramadhan',
                'Persyaratan Baru Pengurusan Surat Izin Pemakaman',
                'Penutupan Sementara TPU Zona A untuk Renovasi',
                'Tarif Retribusi Pemakaman Tahun 2025',
                'Pengumuman Lokasi Pemakaman COVID-19',
                'Jadwal Libur Nasional UPTD Pemakaman',
                'Sistem Antrian Online Mulai Berlaku',
            ],
            'Artikel'    => [
                'Layanan Ambulan Jenazah Gratis 24 Jam',
                'Panduan Pendaftaran Pemakaman Online',
                'Konsultasi Gratis Prosedur Pemakaman',
                'Layanan Pemakaman Khusus Protokol Kesehatan',
                'Bantuan Pemakaman untuk Keluarga Tidak Mampu',
                'Tips Memilih Lokasi Pemakaman yang Tepat',
                'Prosedur Pemindahan Makam',
            ],
            'Layanan'    => [
                'Layanan Pemakaman 24 Jam Non-Stop',
                'Konsultasi Gratis dengan Tim Ahli',
                'Bantuan Transportasi Jenazah',
                'Layanan Administrasi Terpadu',
                'Program Pemakaman Ramah Lingkungan',
            ],
            'Informasi'  => [
                'Daftar Lokasi TPU di Kabupaten Tangerang',
                'Persyaratan Dokumen Pemakaman',
                'Kontak Darurat UPTD Pemakaman',
                'FAQ Layanan Pemakaman',
                'Panduan Protokol Kesehatan di TPU',
            ],
        ];
    }

    /**
     * Create individual post record
     */
    private function createPostRecord(string $category, string $postTitle, $user, int $index): array
    {
        // Generate UUID and paths
        $uuid          = Str::uuid()->toString();
        $thumbnailPath = $this->generateThumbnailPath($uuid, $postTitle);

        // Generate slug
        $slug = $this->generateUniqueSlug($postTitle);

        // Generate random date dalam 6 bulan terakhir
        $randomDate = Carbon::now()->subDays(rand(1, 180));

        // Random status distribution
        $statusDistribution = [
            'Published'      => 80,
            'Draft'          => 10,
            'Pending Review' => 7,
            'Scheduled'      => 2,
            'Archived'       => 1,
        ];
        $status = $this->getRandomStatus($statusDistribution);

        // If scheduled, set future date
        if ($status === 'Scheduled') {
            $randomDate = Carbon::now()->addDays(rand(1, 30));
        }

        return [
            'uuid'         => $uuid,
            'judul'        => $postTitle,
            'slug'         => $slug,
            'deskripsi'    => $this->generateDeskripsi($category, $postTitle),
            'post'         => $this->generateKonten($category, $postTitle),
            'thumbnails'   => $thumbnailPath,
            'tanggal'      => $randomDate->format('Y-m-d H:i:s'),
            'views'        => rand(10, 2500),
            'kategori'     => $category,
            'status'       => $status,
            'uuid_created' => $user->uuid,
            'uuid_updated' => $user->uuid,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug     = $baseSlug;
        $counter  = 1;

        while (PortalPost::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate thumbnail path
     */
    private function generateThumbnailPath(string $uuid, string $title): string
    {
        $year          = date('Y');
        $filename      = Str::slug($title) . '.jpg';
        $path          = "post/{$year}/{$uuid}/{$filename}";
        $dummyImageUrl = "https://dummyimage.com/600x400/000/fff&text=" . urlencode($filename);

        // Download and save image to storage
        try {
            $imageContent = Http::get($dummyImageUrl)->body();
            Storage::disk('public')->put($path, $imageContent);
        } catch (\Exception $e) {
            $this->command->error("Failed to download image for {$filename}: " . $e->getMessage());
        }

        return $path;
    }

    /**
     * Get random status based on distribution
     */
    private function getRandomStatus(array $distribution): string
    {
        $rand       = rand(1, 100);
        $cumulative = 0;

        foreach ($distribution as $status => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'Published'; // fallback
    }

    /**
     * Generate deskripsi berdasarkan kategori dan judul
     */
    private function generateDeskripsi(string $kategori, string $judul): string
    {
        $deskripsiTemplates = [
            'Berita'     => "Berita terbaru dari UPTD Pemakaman Kabupaten Tangerang mengenai {$judul}. Informasi lengkap dan akurat untuk masyarakat.",
            'Pengumuman' => "Pengumuman resmi dari UPTD Pemakaman Kabupaten Tangerang terkait {$judul}. Harap diperhatikan oleh seluruh masyarakat.",
            'Artikel'    => "Informasi layanan UPTD Pemakaman: {$judul}. Layanan profesional dan terpercaya untuk membantu masyarakat.",
            'Layanan'    => "Layanan UPTD Pemakaman: {$judul}. Pelayanan berkualitas dan terpercaya untuk masyarakat Kabupaten Tangerang.",
            'Informasi'  => "Informasi penting dari UPTD Pemakaman: {$judul}. Panduan lengkap untuk kemudahan masyarakat.",
        ];

        return $deskripsiTemplates[$kategori] ?? "Informasi dari UPTD Pemakaman Kabupaten Tangerang mengenai {$judul}.";
    }

    /**
     * Generate konten detail berdasarkan kategori
     */
    private function generateKonten(string $kategori, string $judul): string
    {
        $baseKonten = "<h2>{$judul}</h2>";

        switch ($kategori) {
            case 'Berita':
                return $baseKonten . "
                <p>UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang menyampaikan informasi terbaru kepada masyarakat terkait <strong>{$judul}</strong>.</p>

                <p>Dalam upaya meningkatkan pelayanan kepada masyarakat, UPTD Pemakaman terus berkomitmen untuk memberikan layanan yang berkualitas dan sesuai dengan standar yang telah ditetapkan.</p>

                <h3>Detail Informasi:</h3>
                <ul>
                <li>Informasi ini berlaku untuk seluruh wilayah Kabupaten Tangerang</li>
                <li>Koordinasi dengan pihak terkait telah dilakukan</li>
                <li>Masyarakat dapat menghubungi UPTD untuk informasi lebih lanjut</li>
                <li>Update informasi akan disampaikan melalui website resmi</li>
                </ul>

                <p>Untuk informasi lebih lanjut, masyarakat dapat menghubungi kantor UPTD Pemakaman di nomor (021) 1234-5678 atau mengunjungi website resmi kami.</p>";

            case 'Pengumuman':
                return $baseKonten . "
                <p>Kepada seluruh masyarakat Kabupaten Tangerang, dengan ini kami sampaikan pengumuman resmi terkait <strong>{$judul}</strong>.</p>

                <h3>Hal yang perlu diperhatikan:</h3>
                <ul>
                <li>Pengumuman ini berlaku efektif sejak tanggal diterbitkan</li>
                <li>Masyarakat diharapkan untuk mematuhi ketentuan yang berlaku</li>
                <li>Informasi lebih lanjut dapat diperoleh di kantor UPTD</li>
                <li>Pengumuman ini dapat berubah sewaktu-waktu sesuai kebutuhan</li>
                </ul>

                <h3>Kontak Informasi:</h3>
                <p><strong>Telp:</strong> (021) 1234-5678<br>
                <strong>Email:</strong> uptd.pemakaman@tangerangkab.go.id<br>
                <strong>Website:</strong> www.uptdpemakaman-tangerang.go.id</p>

                <p>Demikian pengumuman ini kami sampaikan untuk dapat diketahui dan dipatuhi bersama. Terima kasih atas perhatian dan kerjasamanya.</p>";

            case 'Artikel':
                return $baseKonten . "
                <p>UPTD Pemakaman Kabupaten Tangerang dengan bangga mempersembahkan informasi mengenai <strong>{$judul}</strong> untuk kemudahan dan kenyamanan masyarakat.</p>

                <h3>Keunggulan Layanan:</h3>
                <ul>
                <li>Pelayanan profesional dan berpengalaman</li>
                <li>Tersedia 24 jam sehari, 7 hari seminggu</li>
                <li>Sesuai dengan protokol kesehatan yang berlaku</li>
                <li>Menghormati adat dan kepercayaan yang berbeda</li>
                <li>Tim yang terlatih dan bersertifikat</li>
                </ul>

                <h3>Cara Mengakses Informasi:</h3>
                <ol>
                <li>Hubungi nomor layanan: (021) 1234-5678</li>
                <li>Kunjungi kantor UPTD Pemakaman</li>
                <li>Akses melalui website resmi</li>
                <li>Konsultasi langsung dengan petugas</li>
                </ol>

                <p>Tim kami siap membantu Anda dengan sepenuh hati dalam memberikan informasi yang Anda butuhkan.</p>";

            case 'Layanan':
                return $baseKonten . "
                <p>UPTD Pemakaman Kabupaten Tangerang menyediakan <strong>{$judul}</strong> sebagai bentuk komitmen kami dalam melayani masyarakat.</p>

                <h3>Fitur Layanan:</h3>
                <ul>
                <li>Layanan 24/7 tanpa libur</li>
                <li>Petugas terlatih dan profesional</li>
                <li>Fasilitas modern dan lengkap</li>
                <li>Prosedur yang mudah dan transparan</li>
                <li>Biaya terjangkau dan kompetitif</li>
                </ul>

                <h3>Prosedur Penggunaan Layanan:</h3>
                <ol>
                <li>Hubungi nomor layanan darurat</li>
                <li>Sampaikan kebutuhan Anda</li>
                <li>Tim kami akan segera merespons</li>
                <li>Layanan akan diberikan sesuai kebutuhan</li>
                </ol>

                <p>Kami berkomitmen memberikan layanan terbaik untuk masyarakat Kabupaten Tangerang.</p>";

            case 'Informasi':
                return $baseKonten . "
                <p>Berikut adalah informasi penting mengenai <strong>{$judul}</strong> yang perlu diketahui oleh masyarakat Kabupaten Tangerang.</p>

                <h3>Informasi Penting:</h3>
                <ul>
                <li>Informasi selalu diupdate secara berkala</li>
                <li>Tersedia bantuan konsultasi gratis</li>
                <li>Panduan lengkap tersedia di website</li>
                <li>Tim customer service siap membantu</li>
                </ul>

                <h3>Cara Mendapatkan Informasi Lebih Lanjut:</h3>
                <ol>
                <li>Kunjungi website resmi UPTD Pemakaman</li>
                <li>Hubungi customer service</li>
                <li>Datang langsung ke kantor UPTD</li>
                <li>Follow media sosial resmi kami</li>
                </ol>

                <p>Informasi ini disusun untuk memudahkan masyarakat dalam mengakses layanan UPTD Pemakaman Kabupaten Tangerang.</p>";

            default:
                return $baseKonten . "
                <p>Informasi dari UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang mengenai <strong>{$judul}</strong>.</p>

                <p>Kami berkomitmen untuk memberikan pelayanan terbaik kepada masyarakat dalam bidang pemakaman dan pengurusan jenazah sesuai dengan standar pelayanan publik yang berlaku.</p>

                <h3>Hubungi Kami:</h3>
                <p><strong>Alamat:</strong> Jl. Pemakaman No. 123, Kabupaten Tangerang<br>
                <strong>Telp:</strong> (021) 1234-5678<br>
                <strong>Email:</strong> uptd.pemakaman@tangerangkab.go.id</p>

                <p>Untuk informasi lebih lanjut, silakan hubungi kami melalui kontak yang tersedia.</p>";
        }
    }
}