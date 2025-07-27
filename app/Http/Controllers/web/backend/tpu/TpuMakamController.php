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
                    $uuid_enc   = Helper::encode($data->uuid);
                    $editUrl    = route('tpu.makam.edit', $uuid_enc);
                    $isReadOnly = $auth->role === 'Petugas TPU';

                    return '
                <div class="d-flex align-items-center">
                    <a href="' . $editUrl . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1' . ($isReadOnly ? ' disabled' : '') . '">
                        <i class="ki-outline ki-pencil fs-5"></i>
                    </a>
                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete' . ($isReadOnly ? ' disabled' : '') . '"
                            data-kt-delete-url="' . route('tpu.makam.destroy') . '"
                            data-kt-delete-id="' . $uuid_enc . '">
                        <i class="ki-outline ki-trash fs-5"></i>
                    </button>
                </div>';
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
                ->addColumn('dimensi', function ($data) {
                    return '
                <div class="d-flex flex-column">
                    <span class="text-gray-800 fw-semibold fs-6">' . number_format($data->panjang_m, 2) . ' × ' . number_format($data->lebar_m, 2) . ' m</span>
                    <span class="text-muted fw-semibold fs-7">Luas: ' . number_format($data->luas_m2, 2) . ' m²</span>
                </div>';
                })
                ->addColumn('kapasitas', function ($data) {
                    if ($data->kapasitas) {
                        return '<span class="text-gray-600 fw-semibold d-block fs-7">' . number_format($data->kapasitas) . ' jenazah</span>';
                    }
                    return '<span class="text-muted fs-7">-</span>';
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
                ->rawColumns(['action', 'lahan_info', 'dimensi', 'kapasitas', 'status_makam', 'keterangan'])
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
                ->pluck('Tpu')
                ->unique();

            $lahans = TpuLahan::whereHas('Tpu', function ($q) use ($auth) {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            })
                ->get(['uuid', 'kode_lahan']);
        } else {
            $tpus   = TpuLahan::with('Tpu')->get()->pluck('Tpu')->unique();
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

        // Cek akses untuk Petugas TPU
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Unauthorized action.');
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

        $data = [
            'title'       => 'Tambah Data Makam',
            'submit'      => 'Simpan',
            'lahans'      => $lahans->get(),
            'statusMakam' => $statusMakam,
        ];

        return view('admin.tpu.makam.create_edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = Auth::user();

        // Cek akses untuk Petugas TPU
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.makam.index');
        }

        $request->validate([
            'uuid_lahan'   => 'required|exists:tpu_lahans,uuid',
            'panjang_m'    => 'required|numeric|min:0.01',
            'lebar_m'      => 'required|numeric|min:0.01',
            'kapasitas'    => 'nullable|integer|min:1',
            'status_makam' => 'required|exists:tpu_ref_status_makam,nama',
            'keterangan'   => 'nullable|string|max:1000',
        ], [
            'uuid_lahan.required'   => 'Lahan harus dipilih',
            'uuid_lahan.exists'     => 'Lahan yang dipilih tidak valid',
            'panjang_m.required'    => 'Panjang makam harus diisi',
            'panjang_m.numeric'     => 'Panjang makam harus berupa angka',
            'panjang_m.min'         => 'Panjang makam minimal 0.01 meter',
            'lebar_m.required'      => 'Lebar makam harus diisi',
            'lebar_m.numeric'       => 'Lebar makam harus berupa angka',
            'lebar_m.min'           => 'Lebar makam minimal 0.01 meter',
            'kapasitas.integer'     => 'Kapasitas harus berupa angka',
            'kapasitas.min'         => 'Kapasitas minimal 1 jenazah',
            'status_makam.required' => 'Status makam harus dipilih',
            'status_makam.exists'   => 'Status makam yang dipilih tidak valid',
            'keterangan.max'        => 'Keterangan maksimal 1000 karakter',
        ]);

        try {
            DB::beginTransaction();

            // Check permission for lahan
            $lahan = TpuLahan::with('Tpu')->findOrFail($request->uuid_lahan);
            if ($auth->role === 'Admin TPU' && $lahan->Tpu && $lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Unauthorized action.');
                return back()->withInput();
            }

            $uuid    = Str::uuid();
            $luas_m2 = $request->panjang_m * $request->lebar_m;

            // Calculate kapasitas if not provided
            $kapasitas = $request->kapasitas;
            if (! $kapasitas) {
                $kapasitas = $this->calculateKapasitas($lahan, $luas_m2);
            }

            $value = [
                'uuid'         => $uuid,
                'uuid_lahan'   => $request->uuid_lahan,
                'panjang_m'    => $request->panjang_m,
                'lebar_m'      => $request->lebar_m,
                'luas_m2'      => $luas_m2,
                'kapasitas'    => $kapasitas,
                'status_makam' => $request->status_makam,
                'keterangan'   => $request->keterangan,
                'uuid_created' => $auth->uuid,
                'uuid_updated' => $auth->uuid,
            ];

            $save = TpuMakam::create($value);

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_makams'],
                    'uuid'  => [$uuid],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menambahkan Data Makam: ' . $uuid,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Menambahkan Data Makam!');
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

        // Cek akses untuk Petugas TPU
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.makam.index');
        }

        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuMakam::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

        // Check permission
        if ($auth->role === 'Admin TPU' && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
            alert()->error('Error', 'Unauthorized action.');
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

        // Cek akses untuk Petugas TPU
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.makam.index');
        }

        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuMakam::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

        // Check permission
        if ($auth->role === 'Admin TPU' && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
            alert()->error('Error', 'Unauthorized action.');
            return back()->withInput();
        }

        $request->validate([
            'uuid_lahan'   => 'required|exists:tpu_lahans,uuid',
            'panjang_m'    => 'required|numeric|min:0.01',
            'lebar_m'      => 'required|numeric|min:0.01',
            'kapasitas'    => 'nullable|integer|min:1',
            'status_makam' => 'required|exists:tpu_ref_status_makam,nama',
            'keterangan'   => 'nullable|string|max:1000',
        ], [
            'uuid_lahan.required'   => 'Lahan harus dipilih',
            'uuid_lahan.exists'     => 'Lahan yang dipilih tidak valid',
            'panjang_m.required'    => 'Panjang makam harus diisi',
            'panjang_m.numeric'     => 'Panjang makam harus berupa angka',
            'panjang_m.min'         => 'Panjang makam minimal 0.01 meter',
            'lebar_m.required'      => 'Lebar makam harus diisi',
            'lebar_m.numeric'       => 'Lebar makam harus berupa angka',
            'lebar_m.min'           => 'Lebar makam minimal 0.01 meter',
            'kapasitas.integer'     => 'Kapasitas harus berupa angka',
            'kapasitas.min'         => 'Kapasitas minimal 1 jenazah',
            'status_makam.required' => 'Status makam harus dipilih',
            'status_makam.exists'   => 'Status makam yang dipilih tidak valid',
            'keterangan.max'        => 'Keterangan maksimal 1000 karakter',
        ]);

        try {
            DB::beginTransaction();

            // Check permission for new lahan if changed
            if ($request->uuid_lahan !== $data->uuid_lahan) {
                $lahan = TpuLahan::with('Tpu')->findOrFail($request->uuid_lahan);
                if ($auth->role === 'Admin TPU' && $lahan->Tpu && $lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                    alert()->error('Error', 'Unauthorized action.');
                    return back()->withInput();
                }
            } else {
                $lahan = $data->Lahan;
            }

            $luas_m2 = $request->panjang_m * $request->lebar_m;

            // Calculate kapasitas if not provided or if dimensions changed
            $kapasitas = $request->kapasitas;
            if (! $kapasitas ||
                $data->panjang_m != $request->panjang_m ||
                $data->lebar_m != $request->lebar_m ||
                $data->uuid_lahan !== $request->uuid_lahan) {
                $kapasitas = $this->calculateKapasitas($lahan, $luas_m2);
            }

            $value = [
                'uuid_lahan'   => $request->uuid_lahan,
                'panjang_m'    => $request->panjang_m,
                'lebar_m'      => $request->lebar_m,
                'luas_m2'      => $luas_m2,
                'kapasitas'    => $kapasitas,
                'status_makam' => $request->status_makam,
                'keterangan'   => $request->keterangan,
                'uuid_updated' => $auth->uuid,
            ];

            $save = $data->update($value);

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_makams'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Mengubah Data Makam: ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Mengubah Data Makam!');
                return redirect()->route('tpu.makam.index');
            } else {
                DB::rollback();
                alert()->error('Error', 'Gagal Mengubah Data Makam!');
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

        // Cek akses untuk Petugas TPU
        if ($auth->role === 'Petugas TPU') {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $request->validate([
            'uuid' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $uuid_dec = Helper::decode($request->uuid);
            $data     = TpuMakam::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

            // Check permission
            if ($auth->role === 'Admin TPU' && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized action.',
                ], 403);
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
                        if (! $auth->RelPetugasTpu || ($data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu)) {
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
     * Calculate kapasitas based on jenis TPU and luas makam
     */
    private function calculateKapasitas($lahan, $luas_makam)
    {
        if (! $lahan || ! $lahan->Tpu || $luas_makam <= 0) {
            return 0;
        }

        $luas_lahan = $lahan->luas_m2;
        $jenis_tpu  = $lahan->Tpu->jenis_tpu;

        // Minimal 200 m² untuk sarana prasarana
        $luas_efektif = max(0, $luas_lahan - 200);

        if ($luas_efektif <= 0 || $luas_makam <= 0) {
            return 0;
        }

        // Perhitungan sama untuk semua jenis TPU
        // Karena untuk gabungan: 70% + 30% = 100% dari luas efektif
        $kapasitas = floor($luas_efektif / $luas_makam);

        return max(0, $kapasitas);
    }

    /**
     * Calculate kapasitas via AJAX
     */
    public function calculateKapasitasAjax(Request $request)
    {
        try {
            $lahan      = TpuLahan::with('Tpu')->findOrFail($request->uuid_lahan);
            $panjang    = (float) $request->panjang_m;
            $lebar      = (float) $request->lebar_m;
            $luas_makam = $panjang * $lebar;

            $kapasitas = $this->calculateKapasitas($lahan, $luas_makam);

            $luas_lahan   = $lahan->luas_m2;
            $luas_efektif = max(0, $luas_lahan - 200);
            $jenis_tpu    = $lahan->Tpu ? $lahan->Tpu->jenis_tpu : '';

            return response()->json([
                'status' => true,
                'data'   => [
                    'kapasitas'         => $kapasitas,
                    'luas_makam'        => $luas_makam,
                    'luas_lahan'        => $luas_lahan,
                    'luas_efektif'      => $luas_efektif,
                    'jenis_tpu'         => $jenis_tpu,
                    'jenis_tpu_display' => ucfirst(str_replace('_', ' ', $jenis_tpu)),
                    'calculation_info'  => $this->getCalculationInfo($jenis_tpu, $luas_lahan, $luas_efektif, $luas_makam, $kapasitas),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan dalam perhitungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get calculation information text
     */
    private function getCalculationInfo($jenis_tpu, $luas_lahan, $luas_efektif, $luas_makam, $kapasitas)
    {
        $jenis_display = ucfirst(str_replace('_', ' ', $jenis_tpu));

        $info = "Perhitungan untuk TPU {$jenis_display}:\n";
        $info .= "• Luas lahan: " . number_format($luas_lahan, 2) . " m²\n";
        $info .= "• Dikurangi sarana prasarana: 200 m²\n";
        $info .= "• Luas efektif: " . number_format($luas_efektif, 2) . " m²\n";
        $info .= "• Luas per makam: " . number_format($luas_makam, 2) . " m²\n";

        if ($jenis_tpu === 'gabungan') {
            $info .= "• Pembagian: 70% muslim + 30% non muslim = 100%\n";
        }

        $info .= "• Kapasitas: " . number_format($luas_efektif, 2) . " ÷ " . number_format($luas_makam, 2) . " = {$kapasitas} makam";

        return $info;
    }

    /**
     * Get lahan details for calculation
     */
    public function getLahanDetails(Request $request)
    {
        try {
            $lahan = TpuLahan::with('Tpu')->findOrFail($request->uuid_lahan);

            return response()->json([
                'status' => true,
                'data'   => [
                    'lahan'      => $lahan,
                    'tpu'        => $lahan->Tpu,
                    'jenis_tpu'  => $lahan->Tpu ? $lahan->Tpu->jenis_tpu : null,
                    'luas_lahan' => $lahan->luas_m2,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Lahan tidak ditemukan',
            ], 404);
        }
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
