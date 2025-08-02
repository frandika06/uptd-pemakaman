<?php
namespace App\Http\Controllers\web\backend\tpu;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TpuLahan;
use App\Models\TpuMakam;
use App\Models\TpuRefStatusMakam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class TpuMakamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = Auth::user();

        // Inisialisasi filter dari session atau default
        $filter_tpu    = $request->session()->get('filter_makam_tpu', 'Semua TPU');
        $filter_lahan  = $request->session()->get('filter_makam_lahan', 'Semua Lahan');
        $filter_status = $request->session()->get('filter_makam_status', 'Semua Status');

        if ($request->ajax()) {
            $query = TpuMakam::query()->with(['Lahan.Tpu', 'StatusMakam']);

            // Filter berdasarkan role
            if (in_array($auth->role, ['Admin TPU', 'Petugas TPU'])) {
                $query->whereHas('Lahan.Tpu', function ($q) use ($auth) {
                    $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                });
            }

            // Filter berdasarkan TPU
            if ($request->filled('filter.tpu') && $request->input('filter.tpu') !== 'Semua TPU' && ! in_array($auth->role, ['Admin TPU', 'Petugas TPU'])) {
                $query->whereHas('Lahan.Tpu', function ($q) use ($request) {
                    $q->where('nama', $request->input('filter.tpu'));
                });
                $request->session()->put('filter_makam_tpu', $request->input('filter.tpu'));
            } elseif ($request->input('filter.tpu') === 'Semua TPU') {
                $request->session()->put('filter_makam_tpu', 'Semua TPU');
            }

            // Filter berdasarkan Lahan
            if ($request->filled('filter.lahan') && $request->input('filter.lahan') !== 'Semua Lahan') {
                $query->whereHas('Lahan', function ($q) use ($request) {
                    $q->where('kode_lahan', $request->input('filter.lahan'));
                });
                $request->session()->put('filter_makam_lahan', $request->input('filter.lahan'));
            } elseif ($request->input('filter.lahan') === 'Semua Lahan') {
                $request->session()->put('filter_makam_lahan', 'Semua Lahan');
            }

            // Filter berdasarkan Status
            if ($request->filled('filter.status') && $request->input('filter.status') !== 'Semua Status') {
                $query->where('status_makam', $request->input('filter.status'));
                $request->session()->put('filter_makam_status', $request->input('filter.status'));
            } elseif ($request->input('filter.status') === 'Semua Status') {
                $request->session()->put('filter_makam_status', 'Semua Status');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('action', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $editUrl  = route('tpu.makam.edit', $uuid_enc);
                    $role     = $auth->role;

                    // Logika untuk aksi buttons
                    $canEdit   = false;
                    $canDelete = false;

                    if ($role == 'Super Admin' || $role == 'Admin') {
                        $canEdit   = true;
                        $canDelete = true;
                    } elseif ($role == 'Admin TPU') {
                        // Admin TPU bisa edit dan delete makam di TPU mereka
                        $isSameTPU = ($auth->RelPetugasTpu && $data->Lahan && $data->Lahan->Tpu && $auth->RelPetugasTpu->uuid_tpu === $data->Lahan->Tpu->uuid);
                        $canEdit   = $isSameTPU;
                        $canDelete = $isSameTPU;
                    } elseif ($role == 'Petugas TPU') {
                        // Petugas TPU bisa edit tapi tidak bisa delete
                        $isSameTPU = ($auth->RelPetugasTpu && $data->Lahan && $data->Lahan->Tpu && $auth->RelPetugasTpu->uuid_tpu === $data->Lahan->Tpu->uuid);
                        $canEdit   = $isSameTPU;
                        $canDelete = false;
                    }

                    $actions = '<div class="d-flex align-items-center">';

                    // Edit button
                    if ($canEdit) {
                        $actions .= '<a href="' . $editUrl . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                <i class="ki-outline ki-pencil fs-5"></i>
                            </a>';
                    } else {
                        $actions .= '<span class="btn btn-icon btn-bg-light btn-sm me-1 disabled" data-bs-toggle="tooltip" title="Edit (Tidak diizinkan)">
                                <i class="ki-outline ki-pencil fs-5 text-muted"></i>
                            </span>';
                    }

                    // Delete button
                    if ($canDelete) {
                        $actions .= '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete"
                                data-kt-delete-url="' . route('tpu.makam.destroy') . '"
                                data-kt-delete-id="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                <i class="ki-outline ki-trash fs-5"></i>
                            </button>';
                    } else {
                        $actions .= '<span class="btn btn-icon btn-bg-light btn-sm disabled" data-bs-toggle="tooltip" title="Hapus (Tidak diizinkan)">
                                <i class="ki-outline ki-trash fs-5 text-muted"></i>
                            </span>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->addColumn('lahan_info', function ($data) {
                    if ($data->Lahan) {
                        $jenis_tpu = $data->Lahan->Tpu ? $data->Lahan->Tpu->jenis_tpu : '';
                        switch ($jenis_tpu) {
                            case 'muslim':
                                $jenis_color = 'primary';
                                break;
                            case 'non_muslim':
                                $jenis_color = 'warning';
                                break;
                            case 'gabungan':
                                $jenis_color = 'success';
                                break;
                            default:
                                $jenis_color = 'secondary';
                        }

                        return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 fw-bold fs-6">' . $data->Lahan->kode_lahan . '</span>
                        <span class="text-muted fw-semibold fs-7">' . ($data->Lahan->Tpu ? $data->Lahan->Tpu->nama : '-') . '</span>
                        <span class="badge badge-light-' . $jenis_color . ' fw-bold fs-8 mt-1">' . ucfirst(str_replace('_', ' ', $jenis_tpu)) . '</span>
                    </div>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('kategori_makam', function ($data) {
                    $badges = [
                        'muslim'     => '<span class="badge badge-light-primary">Muslim</span>',
                        'non_muslim' => '<span class="badge badge-light-warning">Non Muslim</span>',
                    ];
                    return $badges[$data->kategori_makam] ?? '<span class="badge badge-light-secondary">Undefined</span>';
                })
                ->addColumn('dimensi', function ($data) {
                    return '
                <div class="d-flex flex-column">
                    <span class="text-gray-800 fw-semibold fs-6">' . number_format($data->panjang_m, 2) . ' × ' . number_format($data->lebar_m, 2) . ' m</span>
                    <span class="text-muted fw-semibold fs-7">Luas: ' . number_format($data->luas_m2, 2) . ' m²</span>
                </div>';
                })
                ->addColumn('kapasitas', function ($data) {
                    $percentage    = $data->kapasitas > 0 ? round(($data->makam_terisi / $data->kapasitas) * 100, 1) : 0;
                    $progressClass = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');

                    return '
                <div class="d-flex flex-column">
                    <div class="d-flex align-items-center mb-1">
                        <div class="progress h-6px w-100 me-2">
                            <div class="progress-bar bg-' . $progressClass . '" style="width: ' . $percentage . '%"></div>
                        </div>
                        <span class="text-muted fs-7">' . $percentage . '%</span>
                    </div>
                    <span class="text-gray-600 fw-semibold fs-6">' . number_format($data->makam_terisi) . ' / ' . number_format($data->kapasitas) . ' jenazah</span>
                    <span class="text-muted fw-semibold fs-7">Sisa: ' . number_format($data->sisa_kapasitas) . '</span>
                </div>';
                })
                ->addColumn('status_makam', function ($data) {
                    switch ($data->status_makam) {
                        case 'Kosong':
                            $statusColor = 'success';
                            break;
                        case 'Terisi Sebagian':
                            $statusColor = 'primary';
                            break;
                        case 'Penuh':
                            $statusColor = 'danger';
                            break;
                        case 'Cadangan':
                            $statusColor = 'warning';
                            break;
                        case 'Blokir':
                            $statusColor = 'dark';
                            break;
                        case 'Rusak':
                            $statusColor = 'danger';
                            break;
                        case 'Renovasi':
                            $statusColor = 'info';
                            break;
                        case 'Dalam Peralihan':
                            $statusColor = 'secondary';
                            break;
                        default:
                            $statusColor = 'secondary';
                    }

                    return '<span class="badge badge-light-' . $statusColor . ' fw-bold fs-7 px-3 py-2">' . ($data->status_makam ?: 'Tidak Diketahui') . '</span>';
                })
                ->addColumn('keterangan', function ($data) {
                    if ($data->keterangan) {
                        $keterangan = Str::limit($data->keterangan, 50);
                        return '<span class="text-gray-600 fw-semibold d-block fs-7" title="' . htmlspecialchars($data->keterangan) . '">' . $keterangan . '</span>';
                    }
                    return '<span class="text-muted fs-7">-</span>';
                })
                ->rawColumns(['action', 'lahan_info', 'kategori_makam', 'dimensi', 'kapasitas', 'status_makam', 'keterangan'])
                ->make(true);
        }

        // Mengambil data tpus dan lahans berdasarkan role
        $tpus   = collect();
        $lahans = collect();

        if (in_array($auth->role, ['Admin TPU', 'Petugas TPU'])) {
            $tpus = TpuLahan::with('Tpu')
                ->whereHas('Tpu', function ($q) use ($auth) {
                    $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                })
                ->get()
                ->sortBy(function ($item) {
                    return $item->Tpu->nama;
                })
                ->pluck('Tpu')
                ->unique();

            $lahans = TpuLahan::whereHas('Tpu', function ($q) use ($auth) {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            })
                ->get(['uuid', 'kode_lahan']);
        } else {
            $tpus = TpuLahan::with('Tpu')->get()->sortBy(function ($item) {
                return $item->Tpu->nama;
            })->pluck('Tpu')->unique();
            $lahans = TpuLahan::get(['uuid', 'kode_lahan']);
        }

        $stsmakam = TpuRefStatusMakam::where('status', '1')->orderBy('nama', 'ASC')->get();

        $data = [
            'title'           => 'Data Makam',
            'tpus'            => $tpus,
            'lahans'          => $lahans,
            'stsmakam'        => $stsmakam,
            'user_role'       => $auth->role,
            'hide_tpu_filter' => in_array($auth->role, ['Admin TPU', 'Petugas TPU']),
            'filter_tpu'      => $filter_tpu,
            'filter_lahan'    => $filter_lahan,
            'filter_status'   => $filter_status,
        ];

        return view('admin.tpu.makam.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth = Auth::user();

        // Petugas TPU tidak diizinkan mengakses create (tetap diblokir untuk create)
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Anda tidak memiliki izin untuk menambah data makam.');
            return redirect()->route('tpu.makam.index');
        }

        // Get available lahan
        $lahans = TpuLahan::with('Tpu');

        if ($auth->role === 'Admin TPU') {
            $lahans->whereHas('Tpu', function ($q) use ($auth) {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            });
        }

        // Get status makam
        $statusMakam = TpuRefStatusMakam::where('status', '1')->get();

        $view_data = [
            'title'       => 'Tambah Data Makam',
            'submit'      => 'Simpan',
            'lahans'      => $lahans->get(),
            'statusMakam' => $statusMakam,
        ];

        return view('admin.tpu.makam.create_edit', $view_data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = Auth::user();

        // Petugas TPU tidak diizinkan menyimpan data baru (tetap diblokir untuk create)
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Anda tidak memiliki izin untuk menambah data makam.');
            return redirect()->route('tpu.makam.index');
        }

        // Get lahan untuk validasi kategori
        $lahan = TpuLahan::with('Tpu')->find($request->uuid_lahan);
        if (! $lahan || ! $lahan->Tpu) {
            alert()->error('Error', 'Lahan atau TPU tidak valid.');
            return back()->withInput();
        }

        // Tentukan kategori_makam berdasarkan jenis TPU
        $kategori_makam = null;
        $jenis_tpu      = $lahan->Tpu->jenis_tpu;

        switch ($jenis_tpu) {
            case 'muslim':
                $kategori_makam = 'muslim';
                break;
            case 'non_muslim':
                $kategori_makam = 'non_muslim';
                break;
            case 'gabungan':
                // Untuk TPU gabungan, kategori harus dipilih user
                $kategori_makam = $request->kategori_makam;
                if (! in_array($kategori_makam, ['muslim', 'non_muslim'])) {
                    alert()->error('Error', 'Kategori makam harus dipilih untuk TPU gabungan.');
                    return back()->withInput();
                }
                break;
            default:
                alert()->error('Error', 'Jenis TPU tidak valid.');
                return back()->withInput();
        }

        // Basic validation (tanpa kategori_makam karena sudah dihandle di atas)
        $request->validate([
            'uuid_lahan'   => 'required|exists:tpu_lahans,uuid',
            'panjang_m'    => 'required|numeric|min:0.1|max:10',
            'lebar_m'      => 'required|numeric|min:0.1|max:10',
            'kapasitas'    => 'required|integer|min:1',
            'makam_terisi' => 'required|integer|min:0',
            'status_makam' => 'required|string|max:100',
            'keterangan'   => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Validasi bisnis rules dengan kategori yang sudah ditentukan
            if (! TpuMakam::canAddMakamToLahan($request->uuid_lahan, $kategori_makam)) {
                alert()->error('Error', 'Tidak dapat menambahkan makam dengan kategori ini pada lahan yang dipilih. Periksa aturan TPU dan kategori makam.');
                return back()->withInput();
            }

            // Validasi makam_terisi tidak boleh lebih dari kapasitas
            if ($request->makam_terisi > $request->kapasitas) {
                alert()->error('Error', 'Jumlah makam terisi tidak boleh lebih dari kapasitas total.');
                return back()->withInput();
            }

            // Calculate luas_m2
            $luas_m2        = $request->panjang_m * $request->lebar_m;
            $sisa_kapasitas = $request->kapasitas - $request->makam_terisi;

            // UUID
            $uuid = Str::uuid();

            $value_makam = [
                'uuid'           => $uuid,
                'uuid_lahan'     => $request->uuid_lahan,
                'kategori_makam' => $kategori_makam, // Use calculated kategori
                'panjang_m'      => $request->panjang_m,
                'lebar_m'        => $request->lebar_m,
                'luas_m2'        => $luas_m2,
                'kapasitas'      => $request->kapasitas,
                'makam_terisi'   => $request->makam_terisi,
                'sisa_kapasitas' => $sisa_kapasitas,
                'status_makam'   => $request->status_makam,
                'keterangan'     => $request->keterangan,
                'uuid_created'   => $auth->uuid,
                'uuid_updated'   => $auth->uuid,
            ];

            $save = TpuMakam::create($value_makam);

            if ($save) {
                DB::commit();
                alert()->success('Success', 'Data Makam berhasil ditambahkan!');
                return redirect()->route('tpu.makam.index');
            } else {
                DB::rollback();
                alert()->error('Error', 'Gagal Menambahkan Data Makam!');
                return back()->withInput();
            }
        } catch (\Exception $e) {
            DB::rollback();
            alert()->error('Error', 'Terjadi kesalahan: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        $auth = Auth::user();

        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuMakam::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

        // Check permission untuk Admin TPU dan Petugas TPU
        if ($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') {
            if (! $auth->RelPetugasTpu || ! $data->Lahan || ! $data->Lahan->Tpu || $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Anda tidak memiliki izin untuk mengedit makam ini.');
                return redirect()->route('tpu.makam.index');
            }
        }

        // Get available lahan
        $lahans = TpuLahan::with('Tpu');

        if ($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') {
            $lahans->whereHas('Tpu', function ($q) use ($auth) {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            });
        }

        // Get status makam
        $statusMakam = TpuRefStatusMakam::where('status', '1')->get();

        $view_data = [
            'title'       => 'Edit Data Makam',
            'submit'      => 'Simpan',
            'data'        => $data,
            'uuid_enc'    => $uuid_enc,
            'lahans'      => $lahans->get(),
            'statusMakam' => $statusMakam,
        ];

        return view('admin.tpu.makam.create_edit', $view_data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        $auth = Auth::user();

        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuMakam::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

        // Check permission untuk Admin TPU dan Petugas TPU
        if ($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') {
            if (! $auth->RelPetugasTpu || ! $data->Lahan || ! $data->Lahan->Tpu || $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Anda tidak memiliki izin untuk mengedit makam ini.');
                return redirect()->route('tpu.makam.index');
            }
        }

        // Basic validation (tidak termasuk kategori_makam karena tidak bisa diubah saat edit)
        $request->validate([
            'uuid_lahan'   => 'required|exists:tpu_lahans,uuid',
            'panjang_m'    => 'required|numeric|min:0.1|max:10',
            'lebar_m'      => 'required|numeric|min:0.1|max:10',
            'kapasitas'    => 'required|integer|min:1',
            'makam_terisi' => 'required|integer|min:0',
            'status_makam' => 'required|string|max:100',
            'keterangan'   => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Validasi makam_terisi tidak boleh lebih dari kapasitas
            if ($request->makam_terisi > $request->kapasitas) {
                alert()->error('Error', 'Jumlah makam terisi tidak boleh lebih dari kapasitas total.');
                return back()->withInput();
            }

            // Calculate luas_m2
            $luas_m2        = $request->panjang_m * $request->lebar_m;
            $sisa_kapasitas = $request->kapasitas - $request->makam_terisi;

            // For update, we don't change lahan and kategori - only update other fields
            $value_makam = [
                'panjang_m'      => $request->panjang_m,
                'lebar_m'        => $request->lebar_m,
                'luas_m2'        => $luas_m2,
                'kapasitas'      => $request->kapasitas,
                'makam_terisi'   => $request->makam_terisi,
                'sisa_kapasitas' => $sisa_kapasitas,
                'status_makam'   => $request->status_makam,
                'keterangan'     => $request->keterangan,
                'uuid_updated'   => $auth->uuid,
            ];

            $update = $data->update($value_makam);

            if ($update) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_makams'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value_makam],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Mengubah Data Makam: ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Data Makam berhasil diperbarui!');
                return redirect()->route('tpu.makam.index');
            } else {
                DB::rollback();
                alert()->error('Error', 'Gagal Memperbarui Data Makam!');
                return back()->withInput();
            }
        } catch (\Exception $e) {
            DB::rollback();
            alert()->error('Error', 'Terjadi kesalahan: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $auth = Auth::user();

        // Petugas TPU tidak diizinkan menghapus (tetap diblokir untuk delete)
        if ($auth->role === 'Petugas TPU') {
            return response()->json([
                'status'  => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus data makam.',
            ], 403);
        }

        $request->validate([
            'uuid' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $uuid_dec = Helper::decode($request->uuid);
            $data     = TpuMakam::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

            // Check permission untuk Admin TPU
            if ($auth->role === 'Admin TPU') {
                if (! $auth->RelPetugasTpu || ! $data->Lahan || ! $data->Lahan->Tpu || $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Anda tidak memiliki izin untuk menghapus makam ini.',
                    ], 403);
                }
            }

            $value = $data->toArray();
            $save  = $data->delete();

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_makams'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menghapus Data Makam: ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                return response()->json([
                    'status'  => true,
                    'message' => 'Data Makam Berhasil Dihapus!',
                ], 200);
            } else {
                DB::rollback();
                return response()->json([
                    'status'  => false,
                    'message' => 'Data Makam Gagal Dihapus!',
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $auth = Auth::user();

        // Petugas TPU tidak diizinkan menghapus
        if ($auth->role === 'Petugas TPU') {
            return response()->json([
                'status'  => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus data makam.',
            ], 403);
        }

        $request->validate([
            'uuids'   => 'required|array|min:1',
            'uuids.*' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $decodedUuids = $request->uuids;
            $deletedCount = 0;
            $failedItems  = [];

            foreach ($decodedUuids as $index => $uuid) {
                try {
                    $data = TpuMakam::with(['Lahan.Tpu'])->findOrFail($uuid);

                    // Check permission untuk Admin TPU
                    if ($auth->role === 'Admin TPU') {
                        if (! $auth->RelPetugasTpu || (! $data->Lahan || ! $data->Lahan->Tpu || $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu)) {
                            $failedItems[] = 'Tidak memiliki izin untuk menghapus makam ID: ' . $request->uuids[$index];
                            continue;
                        }
                    }

                    $value = $data->toArray();
                    if ($data->delete()) {
                        $deletedCount++;

                        // Create log per item
                        $aktifitas = [
                            'tabel' => ['tpu_makams'],
                            'uuid'  => [$uuid],
                            'value' => [$value],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Menghapus Data Makam (Bulk): ' . $uuid,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = 'Gagal menghapus makam ID: ' . $request->uuids[$index];
                    }
                } catch (\Exception $e) {
                    $failedItems[] = 'Error pada ID ' . $request->uuids[$index] . ': ' . $e->getMessage();
                    continue;
                }
            }

            $message = 'Berhasil menghapus ' . $deletedCount . ' makam';
            if (! empty($failedItems)) {
                $message .= '. Gagal menghapus ' . count($failedItems) . ' item';
            }

            // Create summary log
            $summaryLog = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Bulk Delete Data Makam - Berhasil: ' . $deletedCount . ', Gagal: ' . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['tpu_makams'],
                    'total_request' => count($request->uuids),
                    'total_deleted' => $deletedCount,
                    'total_failed'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            DB::commit();
            return response()->json([
                'status'        => true,
                'message'       => $message,
                'deleted_count' => $deletedCount,
                'failed_count'  => count($failedItems),
                'failed_items'  => $failedItems,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX method untuk mendapatkan detail lahan dan kategori yang tersedia
     */
    public function getLahanDetails(Request $request)
    {
        try {
            $uuid_lahan = $request->uuid_lahan;

            $lahan = TpuLahan::with('Tpu')->find($uuid_lahan);
            if (! $lahan || ! $lahan->Tpu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lahan atau TPU tidak ditemukan',
                ]);
            }

            $available_kategori   = TpuMakam::getAvailableKategoriForLahan($uuid_lahan);
            $existing_makam_count = TpuMakam::where('uuid_lahan', $uuid_lahan)->count();

            return response()->json([
                'success' => true,
                'data'    => [
                    'lahan'                => [
                        'kode_lahan' => $lahan->kode_lahan,
                        'luas_m2'    => $lahan->luas_m2,
                    ],
                    'tpu'                  => [
                        'nama'      => $lahan->Tpu->nama,
                        'jenis_tpu' => $lahan->Tpu->jenis_tpu,
                    ],
                    'available_kategori'   => $available_kategori,
                    'existing_makam_count' => $existing_makam_count,
                    'max_makam_per_lahan'  => $lahan->Tpu->jenis_tpu === 'gabungan' ? 2 : 1,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX method untuk mendapatkan kategori makam yang tersedia untuk lahan
     */
    public function getAvailableKategoriForLahan(Request $request)
    {
        try {
            $uuid_lahan         = $request->uuid_lahan;
            $available_kategori = TpuMakam::getAvailableKategoriForLahan($uuid_lahan);

            return response()->json([
                'success' => true,
                'data'    => $available_kategori,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate kapasitas based on jenis TPU, luas makam, and kategori makam
     */
    private function calculateKapasitas($lahan, $luas_makam, $kategori_makam = null)
    {
        if (! $lahan || ! $lahan->Tpu || $luas_makam <= 0) {
            return 0;
        }

        $tpu          = $lahan->Tpu;
        $luas_lahan   = $lahan->luas_m2;
        $luas_efektif = max(0, $luas_lahan - 200); // Minimal 200 m² untuk sarana prasarana

        if ($luas_efektif <= 0 || $luas_makam <= 0) {
            return 0;
        }

        // Perhitungan kapasitas dasar
        $kapasitas_dasar = floor($luas_efektif / $luas_makam);

        // Jika tidak ada kategori_makam (backward compatibility), gunakan perhitungan lama
        if (! $kategori_makam) {
            return max(0, $kapasitas_dasar);
        }

        // Penyesuaian berdasarkan jenis TPU dan kategori makam
        switch ($tpu->jenis_tpu) {
            case 'muslim':
                $kapasitas = $kategori_makam == 'muslim' ? $kapasitas_dasar : 0;
                break;
            case 'non_muslim':
                $kapasitas = $kategori_makam == 'non_muslim' ? $kapasitas_dasar : 0;
                break;
            case 'gabungan':
                // Untuk TPU gabungan, bagi berdasarkan persentase
                // 70% Muslim, 30% Non Muslim (sesuaikan dengan regulasi)
                if ($kategori_makam == 'muslim') {
                    $kapasitas = floor($kapasitas_dasar * 0.7);
                } else {
                    $kapasitas = floor($kapasitas_dasar * 0.3);
                }
                break;
            default:
                $kapasitas = 0;
        }

        return max(0, $kapasitas);
    }

    /**
     * AJAX method untuk menghitung kapasitas berdasarkan lahan dan kategori
     */
    public function calculateKapasitasAjax(Request $request)
    {
        try {
            $request->validate([
                'uuid_lahan'     => 'required|exists:tpu_lahans,uuid',
                'kategori_makam' => 'required|in:muslim,non_muslim',
                'panjang_m'      => 'required|numeric|min:0.1',
                'lebar_m'        => 'required|numeric|min:0.1',
            ]);

            $lahan = TpuLahan::with('Tpu')->find($request->uuid_lahan);
            if (! $lahan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lahan tidak ditemukan',
                ]);
            }

            $luas_makam = $request->panjang_m * $request->lebar_m;
            $kapasitas  = $this->calculateKapasitas($lahan, $luas_makam, $request->kategori_makam);

            // Tambahan informasi perhitungan
            $luas_lahan   = $lahan->luas_m2;
            $luas_efektif = max(0, $luas_lahan - 200);
            $jenis_tpu    = $lahan->Tpu ? $lahan->Tpu->jenis_tpu : '';

            return response()->json([
                'success' => true,
                'data'    => [
                    'kapasitas'        => $kapasitas,
                    'luas_m2'          => number_format($luas_makam, 2),
                    'luas_lahan'       => $luas_lahan,
                    'luas_efektif'     => $luas_efektif,
                    'jenis_tpu'        => $jenis_tpu,
                    'kategori_makam'   => $request->kategori_makam,
                    'calculation_info' => $this->getCalculationInfo($jenis_tpu, $luas_lahan, $luas_efektif, $luas_makam, $kapasitas, $request->kategori_makam),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan dalam perhitungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get calculation information text with kategori makam
     */
    private function getCalculationInfo($jenis_tpu, $luas_lahan, $luas_efektif, $luas_makam, $kapasitas, $kategori_makam)
    {
        $jenis_display    = ucfirst(str_replace('_', ' ', $jenis_tpu));
        $kategori_display = $kategori_makam == 'muslim' ? 'Muslim' : 'Non Muslim';

        $info = "Perhitungan untuk TPU {$jenis_display} - Kategori {$kategori_display}:\n";
        $info .= "• Luas lahan: " . number_format($luas_lahan, 2) . " m²\n";
        $info .= "• Dikurangi sarana prasarana: 200 m²\n";
        $info .= "• Luas efektif: " . number_format($luas_efektif, 2) . " m²\n";
        $info .= "• Luas per makam: " . number_format($luas_makam, 2) . " m²\n";

        if ($jenis_tpu === 'gabungan') {
            $persentase = $kategori_makam == 'muslim' ? '70%' : '30%';
            $info .= "• Alokasi untuk {$kategori_display}: {$persentase} dari luas efektif\n";
            $luas_alokasi = $kategori_makam == 'muslim' ? ($luas_efektif * 0.7) : ($luas_efektif * 0.3);
            $info .= "• Luas alokasi: " . number_format($luas_alokasi, 2) . " m²\n";
            $info .= "• Kapasitas: " . number_format($luas_alokasi, 2) . " ÷ " . number_format($luas_makam, 2) . " = {$kapasitas} makam";
        } else {
            $info .= "• Kapasitas: " . number_format($luas_efektif, 2) . " ÷ " . number_format($luas_makam, 2) . " = {$kapasitas} makam";
        }

        return $info;
    }

    /**
     * Get lahan by tpu
     */
    public function getLahanByTpu(Request $request)
    {
        $auth = Auth::user();

        $lahans = TpuLahan::whereHas('Tpu', function ($q) use ($request, $auth) {
            if ($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            } else {
                $q->where('nama', $request->tpu);
            }
        })->get(['uuid', 'kode_lahan']);

        return response()->json(['status' => true, 'data' => $lahans]);
    }
}