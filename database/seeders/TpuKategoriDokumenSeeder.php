<?php
namespace Database\Seeders;

use App\Models\TpuKategoriDokumen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TpuKategoriDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Mendapatkan data user admin
        $user = User::whereUsername("admin@mail.com")->first();
        if (! $user) {
            return;
        }

        // Truncate tabel TpuKategoriDokumen untuk memulai dengan data baru
        TpuKategoriDokumen::truncate();

        // Definisi kategori dokumen berdasarkan tipe
        $kategoriDokumen = [
            'foto'            => [
                'Foto TPU',
                'Foto Gerbang TPU',
                'Foto Fasilitas TPU',
                'Foto Lahan',
                'Foto Sarpras',
            ],
            'dokumen-tpu'     => [
                'Surat Izin Mendirikan Bangunan (IMB)',
                'Sertifikat Tanah',
                'Surat Persetujuan Lingkungan',
                'AMDAL (Analisis Mengenai Dampak Lingkungan)',
                'UKL-UPL (Upaya Pengelolaan Lingkungan)',
                'Surat Keterangan Domisili',
                'Izin Usaha dari Dinkes',
                'Akta Pendirian (untuk TPU Swasta)',
                'NPWP',
                'Laporan Keuangan',
                'SOP (Standard Operating Procedure)',
                'Denah Lokasi TPU',
            ],
            'dokumen-lahan'   => [
                'Sertifikat Lahan',
                'Denah Lahan',
                'Surat Ukur Tanah',
                'Peta Lahan',
            ],
            'dokumen-sarpras' => [
                'Spesifikasi Sarpras',
                'Manual Penggunaan',
                'Sertifikat Sarpras',
                'Jadwal Pemeliharaan',
            ],
        ];

        // Hitung total item untuk progress bar
        $totalItems = array_sum(array_map('count', $kategoriDokumen));

        // Mulai progress bar
        $this->command->getOutput()->progressStart($totalItems);

        foreach ($kategoriDokumen as $tipe => $kategoris) {
            foreach ($kategoris as $nama) {
                // Validasi data sebelum proses
                if (empty(trim($nama)) || empty(trim($tipe))) {
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
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];

                    try {
                        TpuKategoriDokumen::create($value_kategori);
                    } catch (\Exception $e) {
                        // Skip jika gagal
                    }
                }

                // Update progress bar
                $this->command->getOutput()->progressAdvance();
            }
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();
    }
}