<?php
namespace Database\Seeders;

use App\Models\TpuDatas;
use App\Models\TpuLahan;
use App\Models\TpuMakam;
use App\Models\TpuStatistikKapasitas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DummyTpuStatistikKapasitasSeeder extends Seeder
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

        // Truncate tabel TpuStatistikKapasitas
        TpuStatistikKapasitas::truncate();

        // Mendapatkan semua TPU yang aktif
        $tpuList = TpuDatas::where('status', 'Aktif')->get();
        if ($tpuList->isEmpty()) {
            $this->command->error('Tidak ada data TPU aktif yang ditemukan.');
            return;
        }

        // Inisialisasi data
        $statistikData = [];
        $totalItems    = 0;

        // Mengumpulkan data statistik untuk 12 bulan terakhir
        foreach ($tpuList as $tpu) {
            // Hitung total lahan untuk TPU ini
            $uuid       = $tpu->uuid;
            $totalLahan = TpuLahan::where('uuid_tpu', $uuid)->count();
            if ($totalLahan == 0) {
                $this->command->warn("TPU '{$tpu->nama}' tidak memiliki lahan, dilewati.");
                continue;
            }

            // Hitung total kapasitas berdasarkan makam
            $totalKapasitas = TpuMakam::whereHas('Lahan', function ($q) use ($tpu) {
                $q->where('uuid_tpu', $tpu->uuid);
            })->sum('kapasitas');

            // Jika tidak ada makam, generate kapasitas estimasi
            if ($totalKapasitas == 0) {
                $totalKapasitas = $totalLahan * rand(50, 200);
            }

            // Generate data untuk 12 bulan terakhir
            for ($i = 11; $i >= 0; $i--) {
                $bulan = Carbon::now()->subMonths($i)->startOfMonth();

                // Simulasi penggunaan kapasitas
                $penggunaanPersentase = min(95, 10 + ($i * 5) + rand(0, 20));
                $kapasitasTerpakai    = round($totalKapasitas * ($penggunaanPersentase / 100));
                $sisaKapasitas        = max(0, $totalKapasitas - $kapasitasTerpakai);

                // Variasi seasonal
                $bulanAngka = $bulan->month;
                if (in_array($bulanAngka, [6, 7, 12])) {
                    $penggunaanTambahan = rand(5, 15);
                    $kapasitasTerpakai  = min($totalKapasitas, $kapasitasTerpakai + round($totalKapasitas * ($penggunaanTambahan / 100)));
                    $sisaKapasitas      = max(0, $totalKapasitas - $kapasitasTerpakai);
                }

                $statistikData[] = [
                    'uuid'            => Str::uuid(),
                    'uuid_tpu'        => $tpu->uuid,
                    'bulan'           => $bulan->format('Y-m-d'),
                    'total_lahan'     => $totalLahan,
                    'total_kapasitas' => $totalKapasitas,
                    'sisa_kapasitas'  => $sisaKapasitas,
                    'uuid_created'    => $user->uuid,
                    'uuid_updated'    => $user->uuid,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
                $totalItems++;
            }
        }

        // Mulai progress bar
        $this->command->getOutput()->progressStart($totalItems);

        // Proses penyimpanan data
        foreach ($statistikData as $statistik) {
            // Validasi data
            if (empty($statistik['uuid_tpu']) || empty($statistik['bulan'])) {
                $tpu     = TpuDatas::find($statistik['uuid_tpu']);
                $tpuNama = $tpu ? $tpu->nama : 'Unknown';
                $this->command->warn("Data statistik untuk TPU '{$tpuNama}' - " . date('F Y', strtotime($statistik['bulan'])) . " tidak valid, dilewati.");
                $this->command->getOutput()->progressAdvance();
                continue;
            }

            // Cek apakah data sudah ada
            $existing = TpuStatistikKapasitas::where('uuid_tpu', $statistik['uuid_tpu'])
                ->whereRaw('DATE_FORMAT(bulan, "%Y-%m") = ?', [date('Y-m', strtotime($statistik['bulan']))])
                ->first();

            if (! $existing) {
                TpuStatistikKapasitas::create($statistik);
            }

            // Update progress bar
            $this->command->getOutput()->progressAdvance();
        }

        // Selesaikan progress bar
        $this->command->getOutput()->progressFinish();
    }
}