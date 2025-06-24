<?php
namespace Database\Seeders;

use App\Models\PortalActor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ActorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if data already exists
        if (User::exists()) {
            $this->command->info("Users already exist. Skipping seeder.");
            return;
        }

        $this->command->info("Starting Actor Seeder...");

        DB::transaction(function () {
            $roles       = $this->getRolesData();
            $progressBar = $this->command->getOutput()->createProgressBar(count($roles));
            $progressBar->start();

            $usersData  = [];
            $actorsData = [];

            foreach ($roles as $index => $roleData) {
                $userData  = $this->createUserData($roleData, $index + 1);
                $actorData = $this->createActorData($roleData, $userData['uuid'], $index + 1);

                $usersData[]  = $userData;
                $actorsData[] = $actorData;

                $progressBar->advance();
            }

            // Bulk insert for better performance
            User::insert($usersData);
            PortalActor::insert($actorsData);

            $progressBar->finish();
            $this->command->info("\nActor Seeder Completed Successfully!");
        });
    }

    /**
     * Get roles configuration data
     */
    private function getRolesData(): array
    {
        return [
            [
                'email'        => 'frandika.septa@gmail.com',
                'role'         => 'Super Admin',
                'nama_lengkap' => 'Frandika Septa',
                'is_admin'     => true,
            ],
            [
                'role'         => 'Admin',
                'nama_lengkap' => 'Admin User',
            ],
            [
                'role'         => 'Editor',
                'nama_lengkap' => 'Editor User',
            ],
            [
                'role'         => 'Penulis',
                'nama_lengkap' => 'Penulis User',
            ],
            [
                'role'         => 'Kontributor',
                'nama_lengkap' => 'Kontributor User',
            ],
            [
                'role'         => 'Operator',
                'nama_lengkap' => 'Operator User',
            ],
            [
                'role'         => 'Pimpinan',
                'nama_lengkap' => 'Pimpinan User',
            ],
        ];
    }

    /**
     * Create user data array
     */
    private function createUserData(array $roleData, int $index): array
    {
        $uuid  = Str::uuid()->toString();
        $email = $roleData['email'] ?? $this->generateEmail($roleData['role']);

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
     * Create actor data array
     */
    private function createActorData(array $roleData, string $userUuid, int $index): array
    {
        $email = $roleData['email'] ?? $this->generateEmail($roleData['role']);

        return [
            'uuid'          => Str::uuid()->toString(),
            'uuid_user'     => $userUuid,
            'nip'           => $this->generateNip($index),
            'nama_lengkap'  => $roleData['nama_lengkap'],
            'jenis_kelamin' => 'L',
            'kontak'        => $this->generatePhoneNumber(),
            'email'         => $email,
            'jabatan'       => 'Pegawai',
            'foto'          => asset('logo/logo-tangkab.png'),
            'uuid_created'  => $userUuid,
            'uuid_updated'  => $userUuid,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }

    /**
     * Generate email from role name
     */
    private function generateEmail(string $role): string
    {
        $cleanRole = Str::lower(str_replace(' ', '.', $role));
        return "{$cleanRole}@mail.com";
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