<?php
namespace Database\Seeders;

use App\Models\PortalSetup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyPortalSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mendapatkan data user admin
        $user = User::where('username', 'admin@mail.com')->first();
        if (! $user) {
            return;
        }

        // Setup progress bar
        $bar = $this->command->getOutput()->createProgressBar(2);
        $bar->start();

        // Hapus data sebelumnya
        PortalSetup::truncate();
        $bar->advance();

        // Data setup pengaturan portal berdasarkan analisa template & kebutuhan UPTD Pemakaman
        $setupData = [
            // HEADER SECTION
            [
                'nama_pengaturan'  => 'header_logo',
                'value_pengaturan' => 'logo/logo-color.png',
                'kategori'         => 'Header',
                'keterangan'       => 'Logo yang tampil di header website portal',
            ],
            [
                'nama_pengaturan'  => 'header_xlabel_alt',
                'value_pengaturan' => 'UPTD Pemakaman Dinas Perkim Kabupaten Tangerang',
                'kategori'         => 'Header',
                'keterangan'       => 'Alt text untuk logo header (accessibility & SEO)',
            ],

            // FOOTER SECTION - Contact Info
            [
                'nama_pengaturan'  => 'footer_phone',
                'value_pengaturan' => '(021) 5962-7890',
                'kategori'         => 'Footer',
                'keterangan'       => 'Nomor telepon kantor untuk footer',
            ],
            [
                'nama_pengaturan'  => 'footer_alamat',
                'value_pengaturan' => 'Citra Raya, Kabupaten Tangerang',
                'kategori'         => 'Footer',
                'keterangan'       => 'Alamat kantor yang tampil di footer',
            ],
            [
                'nama_pengaturan'  => 'footer_email',
                'value_pengaturan' => 'uptdpemakaman@tangerangkab.go.id',
                'kategori'         => 'Footer',
                'keterangan'       => 'Email resmi yang tampil di footer',
            ],

            // FOOTER - Logo & Organization Info
            [
                'nama_pengaturan'  => 'footer_logo',
                'value_pengaturan' => 'logo/logo-color.png',
                'kategori'         => 'Footer',
                'keterangan'       => 'Logo yang tampil di footer',
            ],
            [
                'nama_pengaturan'  => 'footer_description',
                'value_pengaturan' => 'Website Resmi dari UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang',
                'kategori'         => 'Footer',
                'keterangan'       => 'Deskripsi organisasi di footer',
            ],

            // FOOTER - External Links (Pranala)
            [
                'nama_pengaturan'  => 'footer_link_web_terpadu',
                'value_pengaturan' => 'https://tangerangkab.go.id',
                'kategori'         => 'Footer',
                'keterangan'       => 'Link ke Web Terpadu Kabupaten Tangerang',
            ],
            [
                'nama_pengaturan'  => 'footer_link_dppp',
                'value_pengaturan' => 'https://dppp.tangerangkab.go.id',
                'kategori'         => 'Footer',
                'keterangan'       => 'Link ke website DPPP',
            ],
            [
                'nama_pengaturan'  => 'footer_link_simapan',
                'value_pengaturan' => 'https://simapan.tangerangkab.go.id',
                'kategori'         => 'Footer',
                'keterangan'       => 'Link ke sistem SIMAPAN',
            ],

            // FOOTER - Copyright
            [
                'nama_pengaturan'  => 'footer_copyright',
                'value_pengaturan' => 'UPTD Pemakaman &#x25CF; DPPP Kabupaten Tangerang',
                'kategori'         => 'Footer',
                'keterangan'       => 'Teks copyright di footer',
            ],

            // SEO & META SECTION
            [
                'nama_pengaturan'  => 'seo_title',
                'value_pengaturan' => 'Website UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang',
                'kategori'         => 'SEO',
                'keterangan'       => 'Default title tag untuk halaman',
            ],
            [
                'nama_pengaturan'  => 'seo_description',
                'value_pengaturan' => 'Selamat Datang di Website UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang, Pusat Informasi Tentang Tempat Pemakaman Umum (TPU) dan Layanan Mobil Ambulan Jenazah.',
                'kategori'         => 'SEO',
                'keterangan'       => 'Default meta description untuk SEO',
            ],
            [
                'nama_pengaturan'  => 'seo_keywords',
                'value_pengaturan' => 'UPTD Pemakaman, Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang, TPU Pemakaman Kabupaten Tangerang',
                'kategori'         => 'SEO',
                'keterangan'       => 'Meta keywords untuk SEO',
            ],

            // HERO SECTION (Homepage)
            [
                'nama_pengaturan'  => 'hero_background',
                'value_pengaturan' => 'fe/img/custom/bg-beranda.png',
                'kategori'         => 'Hero',
                'keterangan'       => 'Background image untuk hero section homepage',
            ],
            [
                'nama_pengaturan'  => 'hero_greeting',
                'value_pengaturan' => 'Selamat Datang di Website',
                'kategori'         => 'Hero',
                'keterangan'       => 'Teks pembuka di hero section',
            ],
            [
                'nama_pengaturan'  => 'hero_title',
                'value_pengaturan' => 'UPTD PEMAKAMAN',
                'kategori'         => 'Hero',
                'keterangan'       => 'Judul utama di hero section',
            ],
            [
                'nama_pengaturan'  => 'hero_subtitle_1',
                'value_pengaturan' => 'Dinas Perumahan Permukiman dan Pemakaman',
                'kategori'         => 'Hero',
                'keterangan'       => 'Sub judul pertama di hero section',
            ],
            [
                'nama_pengaturan'  => 'hero_subtitle_2',
                'value_pengaturan' => 'Kabupaten Tangerang',
                'kategori'         => 'Hero',
                'keterangan'       => 'Sub judul kedua di hero section',
            ],
            [
                'nama_pengaturan'  => 'hero_cta_text',
                'value_pengaturan' => 'Lihat Data TPU',
                'kategori'         => 'Hero',
                'keterangan'       => 'Teks tombol Call-to-Action di hero',
            ],

            // CONTACT & SERVICE INFO
            [
                'nama_pengaturan'  => 'contact_whatsapp',
                'value_pengaturan' => '6285123456789',
                'kategori'         => 'Kontak',
                'keterangan'       => 'Nomor WhatsApp untuk kontak (format internasional tanpa +)',
            ],
            [
                'nama_pengaturan'  => 'contact_jam_operasional',
                'value_pengaturan' => 'Senin - Jumat: 08:00 - 16:00 WIB',
                'kategori'         => 'Kontak',
                'keterangan'       => 'Jam operasional kantor',
            ],
            [
                'nama_pengaturan'  => 'contact_layanan_darurat',
                'value_pengaturan' => 'Layanan Darurat 24 Jam',
                'kategori'         => 'Kontak',
                'keterangan'       => 'Info layanan darurat pemakaman',
            ],

            // ORGANIZATION INFO
            [
                'nama_pengaturan'  => 'org_nama_lengkap',
                'value_pengaturan' => 'Unit Pelaksana Teknis Daerah Pemakaman',
                'kategori'         => 'Organisasi',
                'keterangan'       => 'Nama lengkap organisasi',
            ],
            [
                'nama_pengaturan'  => 'org_dinas_induk',
                'value_pengaturan' => 'Dinas Perumahan Permukiman dan Pemakaman',
                'kategori'         => 'Organisasi',
                'keterangan'       => 'Nama dinas induk',
            ],
            [
                'nama_pengaturan'  => 'org_wilayah',
                'value_pengaturan' => 'Kabupaten Tangerang',
                'kategori'         => 'Organisasi',
                'keterangan'       => 'Wilayah kerja organisasi',
            ],

            // SERVICE FEATURES
            [
                'nama_pengaturan'  => 'layanan_mobil_jenazah',
                'value_pengaturan' => '1',
                'kategori'         => 'Layanan',
                'keterangan'       => 'Status layanan mobil jenazah (1=Aktif, 0=Tidak)',
            ],
            [
                'nama_pengaturan'  => 'layanan_info_tpu',
                'value_pengaturan' => '1',
                'kategori'         => 'Layanan',
                'keterangan'       => 'Status layanan informasi TPU (1=Aktif, 0=Tidak)',
            ],
            [
                'nama_pengaturan'  => 'layanan_konsultasi',
                'value_pengaturan' => '1',
                'kategori'         => 'Layanan',
                'keterangan'       => 'Status layanan konsultasi (1=Aktif, 0=Tidak)',
            ],
        ];

        // Iterasi dan simpan data ke database
        $totalRecords = count($setupData);
        $bar->setMaxSteps($totalRecords + 1); // +1 untuk truncate

        foreach ($setupData as $setup) {
            PortalSetup::create([
                'uuid'             => Str::uuid(),
                'nama_pengaturan'  => $setup['nama_pengaturan'],
                'value_pengaturan' => $setup['value_pengaturan'],
                'kategori'         => $setup['kategori'],
                'sites'            => 'portal',
                'status'           => '1',
                'keterangan'       => $setup['keterangan'] ?? null,
                'uuid_created'     => $user->uuid,
                'uuid_updated'     => $user->uuid,
            ]);
            $bar->advance();
        }

        $bar->finish();
    }
}
