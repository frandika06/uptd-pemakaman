<?php
namespace Database\Seeders;

use App\Models\PortalGreeting;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class DummyGreetingSeeder extends Seeder
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
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        $faker       = Faker::create();
        $output      = new ConsoleOutput();
        $progressBar = new ProgressBar($output, 10);

        $progressBar->start();

        // Truncate existing data
        PortalGreeting::truncate();

        for ($i = 0; $i < 10; $i++) {
            // Generate UUID and file path
            $uuid          = Str::uuid();
            $penulis       = $faker->name();
            $filename      = Str::slug($penulis) . ".png";
            $path          = "greeting/" . date('Y') . "/" . $uuid . "/" . $filename;
            $dummyImageUrl = "https://dummyimage.com/300x300/000/fff&text=" . urlencode($penulis);

            // Download and save image to storage
            try {
                $imageContent = Http::get($dummyImageUrl)->body();
                Storage::disk('public')->put($path, $imageContent);
            } catch (\Exception $e) {
                $this->command->error("Failed to download image for {$penulis}: " . $e->getMessage());
                continue;
            }
            PortalGreeting::create([
                'uuid'         => $uuid,
                'penulis'      => $penulis,
                'kutipan'      => $faker->sentence(10),
                'foto'         => $path,
                'status'       => "1",
                'uuid_created' => $user->uuid,
                'uuid_updated' => $user->uuid,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("\nDummy Greeting data has been seeded successfully.");
    }
}