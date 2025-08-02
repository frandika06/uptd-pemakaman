<?php
namespace App\Http\Controllers\web\backend\dashboard;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TpuDatas;
use App\Models\TpuLahan;
use App\Models\TpuMakam;
use App\Models\TpuPetugas;
use App\Models\TpuSarpras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardTpuController extends Controller
{
    /**
     * Display TPU Dashboard
     */
    public function index(Request $request)
    {
        try {
            $auth = Auth::user();
            $role = $auth->role;

            // Ambil data berdasarkan role
            $data = $this->getDashboardData($auth);

            // Ambil data TPU untuk filter hanya jika Super Admin atau Admin
            $tpuList = [];
            if (in_array($role, ['Super Admin', 'Admin'])) {
                $tpuList = TpuDatas::select('uuid', 'nama')
                    ->whereNotIn('status', ['Tidak Aktif'])
                    ->orderBy('nama', 'ASC')
                    ->get();
            }

            return view('admin.tpu.home.index', compact('data', 'auth', 'role', 'tpuList'));
        } catch (\Exception $e) {
            return view('admin.tpu.home.index', [
                'data'    => $this->getEmptyData(),
                'auth'    => Auth::user(),
                'role'    => Auth::user()->role,
                'tpuList' => [],
                'error'   => 'Terjadi kesalahan saat memuat dashboard.',
            ]);
        }
    }

    /**
     * Get dashboard data berdasarkan role user
     */
    private function getDashboardData($auth)
    {
        $role = $auth->role;

        try {
            // Base query berdasarkan role
            if (in_array($role, ['Admin TPU', 'Petugas TPU'])) {
                // Filter hanya TPU yang terkait dengan user
                if ($auth->RelPetugasTpu && $auth->RelPetugasTpu->uuid_tpu) {
                    $uuid_tpu = $auth->RelPetugasTpu->uuid_tpu;
                    return $this->getFilteredTpuData($uuid_tpu);
                } else {
                    // Jika tidak ada relasi TPU, return data kosong
                    return $this->getEmptyData();
                }
            } else {
                // Super Admin dan Admin bisa melihat semua data
                return $this->getAllTpuData();
            }
        } catch (\Exception $e) {
            return $this->getEmptyData();
        }
    }

    /**
     * Get data untuk TPU tertentu (Admin TPU/Petugas TPU)
     */
    private function getFilteredTpuData($uuid_tpu)
    {
        try {
            // Data TPU
            $tpu      = TpuDatas::where('uuid', $uuid_tpu)->first();
            $totalTpu = TpuDatas::where('uuid', $uuid_tpu)->count();
            $tpuAktif = TpuDatas::where('uuid', $uuid_tpu)->where('status', 'Aktif')->count();

            // Statistik Utama
            $totalLahan = TpuLahan::where('uuid_tpu', $uuid_tpu)->count();

            $totalMakam = TpuMakam::whereHas('Lahan', function ($q) use ($uuid_tpu) {
                $q->where('uuid_tpu', $uuid_tpu);
            })->count();

            $totalPetugas = TpuPetugas::where('uuid_tpu', $uuid_tpu)->count();

            $totalSarpras = TpuSarpras::whereHas('Lahan', function ($q) use ($uuid_tpu) {
                $q->where('uuid_tpu', $uuid_tpu);
            })->count();

            // Kapasitas Makam - Sesuai dengan field di database
            $kapasitasData = TpuMakam::whereHas('Lahan', function ($q) use ($uuid_tpu) {
                $q->where('uuid_tpu', $uuid_tpu);
            })->selectRaw('
                COALESCE(SUM(kapasitas), 0) as total_kapasitas,
                COALESCE(SUM(makam_terisi), 0) as makam_terisi,
                COALESCE(SUM(sisa_kapasitas), 0) as sisa_kapasitas
            ')->first();

            $totalKapasitas = $kapasitasData->total_kapasitas ?? 0;
            $makamTerisi    = $kapasitasData->makam_terisi ?? 0;
            $sisaKapasitas  = $kapasitasData->sisa_kapasitas ?? 0;

            // Hitung ulang sisa kapasitas jika tidak ada data sisa_kapasitas
            if ($sisaKapasitas == 0 && $totalKapasitas > 0) {
                $sisaKapasitas = $totalKapasitas - $makamTerisi;
            }

            $persentaseKapasitas = $totalKapasitas > 0 ? round(($makamTerisi / $totalKapasitas) * 100, 1) : 0;

            // Status Makam - Sesuai dengan field status_makam (string)
            $statusMakam = TpuMakam::select('status_makam', DB::raw('count(*) as total'))
                ->whereHas('Lahan', function ($q) use ($uuid_tpu) {
                    $q->where('uuid_tpu', $uuid_tpu);
                })
                ->whereNotNull('status_makam')
                ->where('status_makam', '!=', '')
                ->groupBy('status_makam')
                ->get();

            // Recent Activities (5 data terbaru)
            $recentMakam = TpuMakam::whereHas('Lahan', function ($q) use ($uuid_tpu) {
                $q->where('uuid_tpu', $uuid_tpu);
            })
                ->with(['Lahan' => function ($q) {
                    $q->select('uuid', 'uuid_tpu', 'kode_lahan');
                }])
                ->select('uuid', 'uuid_lahan', 'luas_m2', 'kapasitas', 'makam_terisi', 'status_makam', 'created_at')
                ->latest()
                ->limit(5)
                ->get();

            return [
                'tpu'          => $tpu,
                'overview'     => [
                    'total_tpu'     => $totalTpu,
                    'tpu_aktif'     => $tpuAktif,
                    'total_lahan'   => $totalLahan,
                    'total_makam'   => $totalMakam,
                    'total_petugas' => $totalPetugas,
                    'total_sarpras' => $totalSarpras,
                ],
                'kapasitas'    => [
                    'total_kapasitas'      => $totalKapasitas,
                    'makam_terisi'         => $makamTerisi,
                    'sisa_kapasitas'       => $sisaKapasitas,
                    'persentase_kapasitas' => $persentaseKapasitas,
                ],
                'status_makam' => $statusMakam,
                'recent_makam' => $recentMakam,
                'is_filtered'  => true,
            ];
        } catch (\Exception $e) {
            return $this->getEmptyData();
        }
    }

    /**
     * Get semua data TPU (Super Admin/Admin)
     */
    private function getAllTpuData()
    {
        try {
            // Statistik TPU
            $totalTpu = TpuDatas::count();
            $tpuAktif = TpuDatas::where('status', 'Aktif')->count();

            // Statistik Utama
            $totalLahan   = TpuLahan::count();
            $totalMakam   = TpuMakam::count();
            $totalPetugas = TpuPetugas::count();
            $totalSarpras = TpuSarpras::count();

            // Kapasitas Total - Sesuai dengan field di database
            $kapasitasData = TpuMakam::selectRaw('
                COALESCE(SUM(kapasitas), 0) as total_kapasitas,
                COALESCE(SUM(makam_terisi), 0) as makam_terisi,
                COALESCE(SUM(sisa_kapasitas), 0) as sisa_kapasitas
            ')->first();

            $totalKapasitas = $kapasitasData->total_kapasitas ?? 0;
            $makamTerisi    = $kapasitasData->makam_terisi ?? 0;
            $sisaKapasitas  = $kapasitasData->sisa_kapasitas ?? 0;

            // Hitung ulang sisa kapasitas jika tidak ada data sisa_kapasitas
            if ($sisaKapasitas == 0 && $totalKapasitas > 0) {
                $sisaKapasitas = $totalKapasitas - $makamTerisi;
            }

            $persentaseKapasitas = $totalKapasitas > 0 ? round(($makamTerisi / $totalKapasitas) * 100, 1) : 0;

            // Status Makam - Sesuai dengan field status_makam (string)
            $statusMakam = TpuMakam::select('status_makam', DB::raw('count(*) as total'))
                ->whereNotNull('status_makam')
                ->where('status_makam', '!=', '')
                ->groupBy('status_makam')
                ->get();

            // TPU per Jenis - Sesuai dengan enum jenis_tpu
            $tpuPerJenis = TpuDatas::select('jenis_tpu', DB::raw('count(*) as total'))
                ->whereNotNull('jenis_tpu')
                ->groupBy('jenis_tpu')
                ->get();

            // Recent Activities (5 data terbaru)
            $recentMakam = TpuMakam::with([
                'Lahan' => function ($q) {
                    $q->select('uuid', 'uuid_tpu', 'kode_lahan')
                        ->with(['Tpu' => function ($tq) {
                            $tq->select('uuid', 'nama');
                        }]);
                },
            ])
                ->select('uuid', 'uuid_lahan', 'luas_m2', 'kapasitas', 'makam_terisi', 'status_makam', 'created_at')
                ->latest()
                ->limit(5)
                ->get();

            return [
                'overview'      => [
                    'total_tpu'     => $totalTpu,
                    'tpu_aktif'     => $tpuAktif,
                    'total_lahan'   => $totalLahan,
                    'total_makam'   => $totalMakam,
                    'total_petugas' => $totalPetugas,
                    'total_sarpras' => $totalSarpras,
                ],
                'kapasitas'     => [
                    'total_kapasitas'      => $totalKapasitas,
                    'makam_terisi'         => $makamTerisi,
                    'sisa_kapasitas'       => $sisaKapasitas,
                    'persentase_kapasitas' => $persentaseKapasitas,
                ],
                'status_makam'  => $statusMakam,
                'tpu_per_jenis' => $tpuPerJenis,
                'recent_makam'  => $recentMakam,
                'is_filtered'   => false,
            ];
        } catch (\Exception $e) {
            return $this->getEmptyData();
        }
    }

    /**
     * Get empty data jika tidak ada relasi TPU atau terjadi error
     */
    private function getEmptyData()
    {
        return [
            'overview'      => [
                'total_tpu'     => 0,
                'tpu_aktif'     => 0,
                'total_lahan'   => 0,
                'total_makam'   => 0,
                'total_petugas' => 0,
                'total_sarpras' => 0,
            ],
            'kapasitas'     => [
                'total_kapasitas'      => 0,
                'makam_terisi'         => 0,
                'sisa_kapasitas'       => 0,
                'persentase_kapasitas' => 0,
            ],
            'status_makam'  => collect([]),
            'tpu_per_jenis' => collect([]),
            'recent_makam'  => collect([]),
            'is_filtered'   => true,
            'no_tpu_access' => true,
        ];
    }

    /**
     * Format recent activities untuk response
     */
    private function formatRecentActivities($recentMakam, $isFiltered = false, $tpu = null, $role = null)
    {
        return $recentMakam->map(function ($makam) use ($isFiltered, $tpu, $role) {
            $statusBadge = $this->getStatusBadgeClass($makam->status_makam);

            return [
                'uuid'            => $makam->uuid,
                'tpu_nama'        => $isFiltered ? ($tpu->nama ?? 'N/A') : ($makam->Lahan->Tpu->nama ?? 'N/A'),
                'lahan_nama'      => $makam->Lahan->kode_lahan ?? 'N/A',
                'status'          => $makam->status_makam ?? 'Unknown',
                'status_badge'    => $statusBadge,
                'luas_m2'         => number_format($makam->luas_m2 ?? 0, 2),
                'kapasitas'       => $makam->kapasitas ?? 0,
                'terisi'          => $makam->makam_terisi ?? 0,
                'created_at'      => \Carbon\Carbon::parse($makam->created_at)->format('d/m/Y H:i'),
                'edit_url'        => route('tpu.makam.edit', Helper::encode($makam->uuid)),
                'show_tpu_column' => in_array($role, ['Super Admin', 'Admin']),
            ];
        })->toArray();
    }

    /**
     * Get badge class untuk status makam
     */
    private function getStatusBadgeClass($status)
    {
        switch (strtolower($status)) {
            case 'kosong':
                return 'success';
            case 'terisi':
            case 'terisi sebagian':
                return 'warning';
            case 'penuh':
                return 'danger';
            case 'rusak':
                return 'dark';
            default:
                return 'primary';
        }
    }

    /**
     * API untuk mendapatkan statistik untuk AJAX refresh
     */
    public function getStatistics(Request $request)
    {
        try {
            $auth = Auth::user();
            $data = $this->getDashboardData($auth);

            return response()->json([
                'success'   => true,
                'overview'  => $data['overview'] ?? [],
                'kapasitas' => $data['kapasitas'] ?? [],
                'timestamp' => now()->toISOString(),
                'message'   => 'Statistik berhasil dimuat.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Terjadi kesalahan saat memuat statistik.',
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan aktivitas terbaru
     */
    public function getRecentActivities(Request $request)
    {
        try {
            $auth  = Auth::user();
            $limit = $request->input('limit', 5);

            // Validasi limit
            if ($limit > 20) {
                $limit = 20;
            }

            // Base query berdasarkan role
            $query = TpuMakam::with([
                'Lahan' => function ($q) {
                    $q->select('uuid', 'uuid_tpu', 'kode_lahan')
                        ->with(['Tpu' => function ($tq) {
                            $tq->select('uuid', 'nama');
                        }]);
                },
            ])->select('uuid', 'uuid_lahan', 'luas_m2', 'kapasitas', 'makam_terisi', 'status_makam', 'created_at');

            if (in_array($auth->role, ['Admin TPU', 'Petugas TPU'])) {
                if ($auth->RelPetugasTpu && $auth->RelPetugasTpu->uuid_tpu) {
                    $query->whereHas('Lahan', function ($q) use ($auth) {
                        $q->where('uuid_tpu', $auth->RelPetugasTpu->uuid_tpu);
                    });
                } else {
                    // User tidak memiliki akses TPU
                    return response()->json([
                        'success'    => true,
                        'activities' => [],
                        'message'    => 'Tidak ada akses TPU.',
                        'timestamp'  => now()->toISOString(),
                    ]);
                }
            }

            $recentMakam = $query->latest()->limit($limit)->get();

            $activities = $this->formatRecentActivities($recentMakam, false, null, $auth->role);

            return response()->json([
                'success'    => true,
                'activities' => $activities,
                'total'      => count($activities),
                'timestamp'  => now()->toISOString(),
                'message'    => 'Aktivitas terbaru berhasil dimuat.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Terjadi kesalahan saat memuat aktivitas terbaru.',
            ], 500);
        }
    }
}