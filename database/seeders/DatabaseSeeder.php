<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // users & actors
        $this->call(ActorSeeder::class);
        // konten dummy
        $this->call(DummyPesanSeeder::class);
        $this->call(DummyGaleriSeeder::class);
        $this->call(DummyPageSeeder::class);
        $this->call(DummyPostSeeder::class);
        $this->call(DummyFaqSeeder::class);
        $this->call(DummySosmedSeeder::class);
        $this->call(DummySurveySeeder::class);
        $this->call(DummySetupSeeder::class);
        // komparasi
        $this->call(KomparasiKategoriGlobalSeeder::class);
    }
}