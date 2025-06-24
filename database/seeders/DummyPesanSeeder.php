<?php

namespace Database\Seeders;

use App\Models\PortalPesan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyPesanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate tabel PortalPesan sebelum seeding
        PortalPesan::truncate();

        // Data pesan dummy
        $dataPesan = [
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Ahmad Setiawan",
                "no_telp" => "081234567890",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "PT Teknologi Indonesia",
                "subjek" => "Permintaan Informasi Kursus",
                "pesan" => "Saya ingin mengetahui lebih lanjut tentang kursus yang tersedia.",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Rina Kartika",
                "no_telp" => "082345678901",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "SMK Negeri 1 Jakarta",
                "subjek" => "Kerjasama Pelatihan",
                "pesan" => "Apakah ada kesempatan untuk bekerjasama dalam pelatihan?",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Budi Santoso",
                "no_telp" => "083456789012",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "Universitas Indonesia",
                "subjek" => "Pertanyaan Mengenai Sertifikasi",
                "pesan" => "Apakah setiap kursus dilengkapi dengan sertifikasi?",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Siti Aminah",
                "no_telp" => "084567890123",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "PT Digital Nusantara",
                "subjek" => "Info Jadwal Kelas",
                "pesan" => "Kapan jadwal kelas terdekat untuk pelatihan pemrograman?",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Andi Wirawan",
                "no_telp" => "085678901234",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "Institut Teknologi Bandung",
                "subjek" => "Permintaan Materi Pelatihan",
                "pesan" => "Dapatkah saya memperoleh materi pelatihan dalam bentuk PDF?",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Dewi Lestari",
                "no_telp" => "086789012345",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "Politeknik Negeri Bandung",
                "subjek" => "Apakah Kursus Gratis?",
                "pesan" => "Apakah ada kursus yang disediakan secara gratis?",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Farhan Aulia",
                "no_telp" => "087890123456",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "Sekolah Tinggi Teknologi",
                "subjek" => "Syarat Pendaftaran Kursus",
                "pesan" => "Apa saja syarat yang diperlukan untuk mengikuti kursus ini?",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Mega Sari",
                "no_telp" => "088901234567",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "SMK Informatika",
                "subjek" => "Apakah Ada Program Magang?",
                "pesan" => "Apakah ada program magang yang bisa diikuti setelah kursus?",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Ridho Hakim",
                "no_telp" => "089012345678",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "Universitas Gadjah Mada",
                "subjek" => "Harga Kursus",
                "pesan" => "Berapa biaya untuk mengikuti kursus di lembaga ini?",
                "balasan" => null,
                "status" => "Pending",
            ],
            [
                "uuid" => Str::uuid(),
                "nama_lengkap" => "Mila Andriana",
                "no_telp" => "081012345679",
                "email" => "frandika.septa@gmail.com",
                "instansi" => "PT Industri Kreatif",
                "subjek" => "Materi Kursus Desain",
                "pesan" => "Apakah ada kursus desain yang tersedia?",
                "balasan" => null,
                "status" => "Pending",
            ],
        ];

        // Tambahkan progress bar
        $this->command->getOutput()->progressStart(count($dataPesan));

        // Insert data menggunakan loop dengan progress bar
        foreach ($dataPesan as $data) {
            PortalPesan::create($data);
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();

        // Informasi sukses
        $this->command->info("10 pesan dummy berhasil dimasukkan ke dalam database.");
    }
}