<?php
namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\TpuDatas;
use App\Models\TpuPetugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyTpuPetugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai DummyTpuPetugasSeeder...');

        // Mendapatkan data user admin
        $this->command->info('Memeriksa user admin...');
        $adminUser = User::whereUsername('admin@mail.com')->first();
        if (! $adminUser) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Mendapatkan semua data TPU
        $this->command->info('Mengambil data TPU dari tabel tpu_datas...');
        $tpuList = TpuDatas::all();
        if ($tpuList->isEmpty()) {
            $this->command->error('Tidak ada data TPU ditemukan di tabel tpu_datas.');
            return;
        }

        // Truncate tabel TpuPetugas dan hapus user terkait
        $this->command->info('Menghapus data lama di tabel tpu_petugas dan user terkait...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TpuPetugas::truncate();                                    // Truncate terlebih dahulu untuk menghindari masalah foreign key
        $petugasUuids = TpuPetugas::pluck('uuid_user')->toArray(); // Diperbarui setelah truncate
        User::whereIn('uuid', $petugasUuids)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Inisialisasi data
        $usersData   = [];
        $petugasData = [];
        $totalItems  = $tpuList->count() * 4; // 1 Admin + 3 Petugas per TPU
        $index       = 1;

        // Mulai progress bar
        $this->command->info("Memulai proses seeding untuk {$totalItems} data petugas...");
        $progressBar = $this->command->getOutput()->createProgressBar($totalItems);
        $progressBar->start();

        DB::transaction(function () use ($tpuList, $adminUser, &$usersData, &$petugasData, &$index, $progressBar) {
            foreach ($tpuList as $tpu) {
                // Reset nomor urutan untuk email per TPU
                $emailCounter = 1;

                // Data untuk 1 Admin TPU
                $adminRoleData = [
                    'role'          => 'Admin TPU',
                    'nama_lengkap'  => 'Admin ' . $tpu->nama,
                    'tpu_uuid'      => $tpu->uuid,
                    'email_counter' => $emailCounter++,
                ];
                $userData      = $this->createUserData($adminRoleData, $index);
                $petugasData[] = $this->createPetugasData($adminRoleData, $userData['uuid'], $adminUser->uuid, $index);
                $usersData[]   = $userData;
                $index++;
                $progressBar->advance();

                // Data untuk 3 Petugas TPU
                for ($i = 1; $i <= 3; $i++) {
                    $petugasRoleData = [
                        'role'          => 'Petugas TPU',
                        'nama_lengkap'  => "Petugas {$i} " . $tpu->nama,
                        'tpu_uuid'      => $tpu->uuid,
                        'email_counter' => $emailCounter++,
                    ];
                    $userData      = $this->createUserData($petugasRoleData, $index);
                    $petugasData[] = $this->createPetugasData($petugasRoleData, $userData['uuid'], $adminUser->uuid, $index);
                    $usersData[]   = $userData;
                    $index++;
                    $progressBar->advance();
                }
            }

            // Bulk insert untuk performa
            User::insert($usersData);
            TpuPetugas::insert($petugasData);
        });

        // Selesaikan progress bar
        $progressBar->finish();
        $this->command->info("\nDummyTpuPetugasSeeder selesai dijalankan. {$totalItems} data petugas telah dibuat.");
    }

    /**
     * Create user data array
     */
    private function createUserData(array $roleData, int $index): array
    {
        $uuid  = Str::uuid()->toString();
        $email = $this->generateEmail($roleData['role'], $roleData['email_counter']);

        // Cek apakah email sudah ada untuk mencegah duplikasi
        if (User::where('username', $email)->exists()) {
            $existingUser = User::where('username', $email)->first();
            return $existingUser->toArray();
        }

        return [
            'uuid'         => $uuid,
            'username'     => $email,
            'password'     => Hash::make(config('app.default_password', '@qwerty4321#')),
            'role'         => $roleData['role'],
            'uuid_created' => $uuid,
            'uuid_updated' => $uuid,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }

    /**
     * Create petugas data array
     */
    private function createPetugasData(array $roleData, string $userUuid, string $adminUuid, int $index): array
    {
        $email = $this->generateEmail($roleData['role'], $roleData['email_counter']);

        return [
            'uuid'          => Str::uuid()->toString(),
            'uuid_tpu'      => $roleData['tpu_uuid'],
            'uuid_user'     => $userUuid,
            'nip'           => $this->generateNip($index),
            'nama_lengkap'  => $roleData['nama_lengkap'],
            'jenis_kelamin' => rand(0, 1) ? 'L' : 'P',
            'kontak'        => $this->generatePhoneNumber(),
            'email'         => $email,
            'jabatan'       => $roleData['role'] === 'Admin TPU' ? 'Administrator TPU' : 'Petugas Operasional TPU',
            'foto'          => 'logo/logo-tangkab.png', // Ganti asset() dengan path statis
            'uuid_created'  => $adminUuid,
            'uuid_updated'  => $adminUuid,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }

    /**
     * Generate email based on role and counter
     */
    private function generateEmail(string $role, int $counter): string
    {
        $prefix = $role === 'Admin TPU' ? 'admin' : 'petugas';
        $rand   = Helper::gencode(4);
        return "{$prefix}.{$rand}@email.com";
    }

    /**
     * Generate NIP with better format
     */
    private function generateNip(int $index): string
    {
        $year     = date('Y');
        $month    = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day      = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $sequence = str_pad($index, 6, '0', STR_PAD_LEFT);

        return "{$year}{$month}{$day}{$sequence}";
    }

    /**
     * Generate phone number with consistent format
     */
    private function generatePhoneNumber(): string
    {
        return '0812' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    }
}