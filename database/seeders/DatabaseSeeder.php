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
        // komparasi
        $this->call(KomparasiKategoriGlobalSeeder::class);
        // konten dummy
        $this->call(DummyBannerSeeder::class);
        $this->call(DummyPesanSeeder::class);
        $this->call(DummyGaleriSeeder::class);
        $this->call(DummyPageSeeder::class);
        $this->call(DummyPostSeeder::class);
        $this->call(DummyFaqSeeder::class);
        $this->call(DummySosmedSeeder::class);
        $this->call(DummySurveySeeder::class);
        $this->call(DummyVideoSeeder::class);
        $this->call(DummyUnduhanSeeder::class);
        $this->call(DummySetupSeeder::class);
        // TPU
        $this->call(DummyTPUKategoriDokumenSeeder::class);
        $this->call(DummyTPURefStatusMakamSeeder::class);
        $this->call(DummyTPURefJenisSarprasSeeder::class);
        $this->call(TpuKategoriDokumenSeeder::class);
        $this->call(DummyTpuDatasSeeder::class);
        $this->call(DummyTpuPetugasSeeder::class);
        $this->call(DummyTpuLahanSeeder::class);
        $this->call(DummyTpuMakamSeeder::class);
        $this->call(DummyTpuSarprasSeeder::class);
    }
}