<?php
namespace Database\Seeders;

use App\Models\TpuKategoriDokumen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyTPUKategoriDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Mendapatkan data user admin
        $user = User::whereUsername("admin@mail.com")->firstOrFail();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Truncate tabel TpuKategoriDokumen untuk memulai dengan data baru
        TpuKategoriDokumen::truncate();

        // Definisi kategori dokumen berdasarkan tipe
        $kategoriDokumen = [
            'foto'        => [
                'Foto Pemakaman',
                'Foto Kegiatan',
                'Foto Fasilitas',
            ],
            'dokumen-tpu' => [
                'Izin Pemakaian Tanah Makam',
                'Laporan Kegiatan Pemakaman',
                'Data Inventarisasi Makam',
                'Regulasi Pemakaman',
                'SOP Pemakaman',
            ],
            // 'dokumen-iptm' => [
            //     'Formulir Permohonan IPTM',
            //     'Dokumen Persetujuan IPTM',
            //     'Laporan Retribusi IPTM',
            //     'Dokumen Administrasi IPTM',
            // ],
        ];

        // Hitung total item untuk progress bar
        $totalItems = 0;
        foreach ($kategoriDokumen as $tipe => $kategoris) {
            $totalItems += count($kategoris); // Hitung total kategori
        }

        // Mulai progress bar
        $this->command->getOutput()->progressStart($totalItems);

        foreach ($kategoriDokumen as $tipe => $kategoris) {
            foreach ($kategoris as $nama) {
                // Validasi data sebelum proses
                if (empty(trim($nama)) || empty(trim($tipe))) {
                    $this->command->warn("Nama atau tipe kosong untuk kategori: {$nama} - {$tipe}. Dilewati.");
                    $this->command->getOutput()->progressAdvance();
                    continue;
                }

                // Cek apakah kategori sudah ada
                $cek_kategori = TpuKategoriDokumen::where('nama', $nama)->where('tipe', $tipe)->first();
                if (! $cek_kategori) {
                    // Jika kategori belum ada, buat yang baru
                    $value_kategori = [
                        'uuid'         => Str::uuid(),
                        'nama'         => trim($nama),
                        'tipe'         => trim($tipe),
                        'status'       => '1', // Aktif
                        'uuid_created' => $user->uuid,
                        'uuid_updated' => $user->uuid,
                    ];
                    TpuKategoriDokumen::create($value_kategori);
                }
                // Update progress bar setelah setiap kategori diproses
                $this->command->getOutput()->progressAdvance();
            }
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();

        $this->command->info('Seeder TpuKategoriDokumen selesai dijalankan.');
    }
}
