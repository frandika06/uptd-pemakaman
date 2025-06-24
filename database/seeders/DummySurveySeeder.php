<?php
namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\PortalLinks;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummySurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Mendapatkan data user admin
        $user = User::whereUsername("admin@mail.com")->first();
        if (! $user) {
            $this->command->error('User dengan username "admin@mail.com" tidak ditemukan.');
            return;
        }

        // Hapus data sebelumnya berdasarkan kategori Survey
        PortalLinks::where("kategori", "Survey")->forceDelete();

        // Data survey dengan judul dan URL
        $surveys = [
            [
                'judul' => 'Survey Kepuasan Pelanggan',
                'url'   => 'https://forms.gle/MsktsAeDVKHsng6Y6',
            ],
            [
                'judul' => 'Survey Persepsi Korupsi',
                'url'   => 'https://forms.gle/ZQFot3pV7s8oCfHY7',
            ],
            [
                'judul' => 'Survey Pendengar Podcast Cerita SMA',
                'url'   => 'https://bit.ly/DraftSurveyPodcastCSMA',
            ],
            [
                'judul' => 'Survey BOS dan DAK',
                'url'   => 'https://bit.ly/DraftSurveyBOSDAK',
            ],
            [
                'judul' => 'Survey Jaringan Virtual Ekosistem Jarkom SMA',
                'url'   => 'https://bit.ly/DraftSurveyVirtualEkosistem',
            ],
            [
                'judul' => 'Hasil Survey',
                'url'   => 'https://sma.dikdasmen.go.id/ziwbkhasilsurvey',
            ],
        ];

        // Iterasi data survey dan simpan ke database
        foreach ($surveys as $survey) {
            PortalLinks::create([
                'uuid'         => Str::uuid(),
                'no_urut'      => Helper::GetNoUrutLinks(Helper::encode('Survey')),
                'judul'        => $survey['judul'],
                'url'          => $survey['url'],
                'kategori'     => 'Survey',
                'status'       => '1',
                'uuid_created' => $user->uuid,
                'uuid_updated' => $user->uuid,
            ]);
        }

        $this->command->info('Dummy data untuk link survey berhasil dibuat.');
    }
}