<?php
namespace App\Http\Controllers\web\backend\tpu;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TpuDatas;
use App\Models\TpuLahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class TpuLahanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = Auth::user();

        if ($request->ajax()) {
            $query = TpuLahan::query()->with(['Tpu']);

            // Filter berdasarkan role
            if ($auth->role === 'Admin TPU') {
                $query->whereHas('Tpu', function ($q) use ($auth) {
                    $q->where('uuid_created', $auth->uuid);
                });
            }

            // Filter berdasarkan TPU
            if (isset($_GET['filter']['tpu']) && $_GET['filter']['tpu'] !== 'Semua TPU') {
                $query->whereHas('Tpu', function ($q) use ($request) {
                    $q->where('nama', $request->input('filter.tpu'));
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('kode_lahan', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('tpu.lahan.edit', $uuid_enc);
                    return '
                    <div class="d-flex align-items-center">
                        <div class="d-flex flex-column">
                            <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $data->kode_lahan . '</a>
                            <span class="text-muted fw-semibold d-block fs-7">' . Str::slug($data->kode_lahan) . '</span>
                        </div>
                    </div>
                ';
                })
                ->addColumn('nama_tpu', function ($data) {
                    return '<span class="badge badge-light-primary fw-bold fs-7 px-3 py-2">' . ($data->Tpu ? $data->Tpu->nama : '-') . '</span>';
                })
                ->addColumn('luas_m2', function ($data) {
                    return '<span class="text-gray-600 fw-semibold d-block fs-7">' . number_format($data->luas_m2, 2) . ' m²</span>';
                })
                ->addColumn('koordinat', function ($data) {
                    if ($data->latitude && $data->longitude) {
                        return '<span class="text-gray-600 fw-semibold d-block fs-7">' . $data->latitude . ', ' . $data->longitude . '</span>';
                    }
                    return '<span class="text-muted fs-7">-</span>';
                })
                ->addColumn('total_makam', function ($data) {
                    $total = $data->Makams()->count();
                    return '<span class="badge badge-light-success fw-bold fs-7 px-3 py-2">' . $total . ' Makam</span>';
                })
                ->addColumn('status', function ($data) use ($auth) {
                    $uuid = Helper::encode($data->uuid);
                    if ($data->status == '1') {
                        $checked = 'checked';
                        $text    = 'Aktif';
                        $color   = 'success';
                    } else {
                        $checked = '';
                        $text    = 'Tidak Aktif';
                        $color   = 'danger';
                    }

                    $role = $auth->role;
                    if ($role == 'Super Admin' || $role == 'Admin') {
                        $status = '
                        <div class="form-check form-switch form-check-custom form-check-success">
                            <input class="form-check-input" type="checkbox" role="switch"
                                id="status_' . $data->uuid . '"
                                data-status="' . $uuid . '"
                                data-status-value="' . $data->status . '" ' . $checked . '>
                            <label class="form-check-label fw-semibold text-' . $color . ' ms-3"
                                for="status_' . $data->uuid . '">' . $text . '</label>
                        </div>
                    ';
                    } else {
                        $status = '<span class="badge badge-light-' . $color . ' fw-bold">' . $text . '</span>';
                    }
                    return $status;
                })
                ->addColumn('actions', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('tpu.lahan.edit', $uuid_enc);

                    $role = $auth->role;
                    if ($role == 'Super Admin' || $role == 'Admin') {
                        $actions = '
                        <div class="d-flex justify-content-center">
                            <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                <i class="ki-outline ki-pencil fs-2"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete" data-uuid="' . $uuid_enc . '" data-name="' . $data->kode_lahan . '" data-bs-toggle="tooltip" title="Hapus">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </a>
                        </div>
                    ';
                    } else {
                        if (isset($data->Tpu->uuid_created) && $data->Tpu->uuid_created == $auth->uuid) {
                            $actions = '
                            <div class="d-flex justify-content-center">
                                <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="ki-outline ki-pencil fs-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete" data-uuid="' . $uuid_enc . '" data-name="' . $data->kode_lahan . '" data-bs-toggle="tooltip" title="Hapus">
                                    <i class="ki-outline ki-trash fs-2"></i>
                                </a>
                            </div>
                        ';
                        } else {
                            $actions = '
                            <div class="d-flex justify-content-center">
                                <span class="btn btn-icon btn-bg-light btn-sm me-1 disabled" data-bs-toggle="tooltip" title="Edit (Tidak diizinkan)">
                                    <i class="ki-outline ki-pencil fs-2 text-muted"></i>
                                </span>
                                <span class="btn btn-icon btn-bg-light btn-sm disabled" data-bs-toggle="tooltip" title="Hapus (Tidak diizinkan)">
                                    <i class="ki-outline ki-trash fs-2 text-muted"></i>
                                </span>
                            </div>
                        ';
                        }
                    }
                    return $actions;
                })
                ->rawColumns(['kode_lahan', 'nama_tpu', 'luas_m2', 'koordinat', 'total_makam', 'status', 'actions'])
                ->make(true);
        }

        $data = [
            'title' => 'Data Lahan',
            'tpus'  => TpuDatas::where('status', 'Aktif')->get(),
        ];

        return view('admin.tpu.lahan.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth = Auth::user();
        $tpus = TpuDatas::where('status', 'Aktif');

        if ($auth->role === 'Admin TPU') {
            $tpus->where('uuid_created', $auth->uuid);
        }

        $data = [
            'title'  => 'Tambah Data Lahan',
            'submit' => 'Simpan',
            'tpus'   => $tpus->get(),
        ];

        return view('admin.tpu.lahan.create_edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'uuid_tpu'   => 'required|exists:tpu_datas,uuid',
            'kode_lahan' => 'required|string|max:255|unique:tpu_lahans,kode_lahan',
            'luas_m2'    => 'required|numeric|min:0',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
            'catatan'    => 'nullable|string',
        ], [
            'uuid_tpu.required'   => 'TPU harus dipilih',
            'uuid_tpu.exists'     => 'TPU yang dipilih tidak valid',
            'kode_lahan.required' => 'Kode lahan harus diisi',
            'kode_lahan.unique'   => 'Kode lahan sudah digunakan',
            'luas_m2.required'    => 'Luas lahan harus diisi',
            'luas_m2.numeric'     => 'Luas lahan harus berupa angka',
            'luas_m2.min'         => 'Luas lahan tidak boleh kurang dari 0',
            'latitude.between'    => 'Latitude harus antara -90 dan 90',
            'longitude.between'   => 'Longitude harus antara -180 dan 180',
        ]);

        try {
            DB::beginTransaction();

            $uuid  = Str::uuid();
            $value = [
                'uuid'         => $uuid,
                'uuid_tpu'     => $request->uuid_tpu,
                'kode_lahan'   => $request->kode_lahan,
                'luas_m2'      => $request->luas_m2,
                'latitude'     => $request->latitude,
                'longitude'    => $request->longitude,
                'catatan'      => $request->catatan,
                'uuid_created' => $auth->uuid,
                'uuid_updated' => $auth->uuid,
            ];

            $save = TpuLahan::create($value);

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_lahans'],
                    'uuid'  => [$uuid],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menambahkan Data Lahan: ' . $request->kode_lahan . ' - ' . $uuid,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Menambahkan Data Lahan!');
                return redirect()->route('tpu.lahan.index');
            } else {
                DB::rollback();
                alert()->error('Error', 'Gagal Menambahkan Data Lahan!');
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
        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuLahan::findOrFail($uuid_dec);

        $auth = Auth::user();

        // Check permission
        if ($auth->role === 'Admin TPU' && $data->Tpu->uuid_created !== $auth->uuid) {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.lahan.index');
        }

        $tpus = TpuDatas::where('status', 'Aktif');

        if ($auth->role === 'Admin TPU') {
            $tpus->where('uuid_created', $auth->uuid);
        }

        $view_data = [
            'title'    => 'Edit Data Lahan',
            'submit'   => 'Simpan',
            'data'     => $data,
            'uuid_enc' => $uuid_enc,
            'tpus'     => $tpus->get(),
        ];

        return view('admin.tpu.lahan.create_edit', $view_data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        $auth     = Auth::user();
        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuLahan::findOrFail($uuid_dec);

        // Check permission
        if ($auth->role === 'Admin TPU' && $data->Tpu->uuid_created !== $auth->uuid) {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.lahan.index');
        }

        $request->validate([
            'uuid_tpu'   => 'required|exists:tpu_datas,uuid',
            'kode_lahan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tpu_lahans', 'kode_lahan')->ignore($data->uuid, 'uuid'),
            ],
            'luas_m2'    => 'required|numeric|min:0',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
            'catatan'    => 'nullable|string',
        ], [
            'uuid_tpu.required'   => 'TPU harus dipilih',
            'uuid_tpu.exists'     => 'TPU yang dipilih tidak valid',
            'kode_lahan.required' => 'Kode lahan harus diisi',
            'kode_lahan.unique'   => 'Kode lahan sudah digunakan',
            'luas_m2.required'    => 'Luas lahan harus diisi',
            'luas_m2.numeric'     => 'Luas lahan harus berupa angka',
            'luas_m2.min'         => 'Luas lahan tidak boleh kurang dari 0',
            'latitude.between'    => 'Latitude harus antara -90 dan 90',
            'longitude.between'   => 'Longitude harus antara -180 dan 180',
        ]);

        try {
            DB::beginTransaction();

            $value = [
                'uuid_tpu'     => $request->uuid_tpu,
                'kode_lahan'   => $request->kode_lahan,
                'luas_m2'      => $request->luas_m2,
                'latitude'     => $request->latitude,
                'longitude'    => $request->longitude,
                'catatan'      => $request->catatan,
                'uuid_updated' => $auth->uuid,
            ];

            $save = $data->update($value);

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_lahans'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Mengubah Data Lahan: ' . $request->kode_lahan . ' - ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Mengubah Data Lahan!');
                return redirect()->route('tpu.lahan.index');
            } else {
                DB::rollback();
                alert()->error('Error', 'Gagal Mengubah Data Lahan!');
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

        $request->validate([
            'uuid' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $uuid_dec = Helper::decode($request->uuid);
            $data     = TpuLahan::findOrFail($uuid_dec);

            // Check permission
            if ($auth->role === 'Admin TPU' && $data->Tpu->uuid_created !== $auth->uuid) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }

            // Check if there are related makam
            if ($data->Makams()->count() > 0) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tidak dapat menghapus lahan yang memiliki data makam.',
                ], 422);
            }

            $value = $data->toArray();
            $save  = $data->delete();

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_lahans'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menghapus Data Lahan: ' . $data->kode_lahan . ' - ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                return response()->json([
                    'status'  => true,
                    'message' => 'Data Lahan Berhasil Dihapus!',
                ], 200);
            } else {
                DB::rollback();
                return response()->json([
                    'status'  => false,
                    'message' => 'Data Lahan Gagal Dihapus!',
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

        $request->validate([
            'uuids'   => 'required|array|min:1',
            'uuids.*' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            $failedItems  = [];

            foreach ($request->uuids as $uuid_enc) {
                try {
                    $uuid_dec = $uuid_enc;
                    $data     = TpuLahan::findOrFail($uuid_dec);

                    // Check permission
                    if ($auth->role === 'Admin TPU' && $data->Tpu->uuid_created !== $auth->uuid) {
                        $failedItems[] = 'Tidak memiliki izin untuk menghapus: ' . $data->kode_lahan;
                        continue;
                    }

                    // Check if there are related makam
                    if ($data->Makams()->count() > 0) {
                        $failedItems[] = 'Lahan ' . $data->kode_lahan . ' memiliki data makam dan tidak dapat dihapus';
                        continue;
                    }

                    $value = $data->toArray();
                    if ($data->delete()) {
                        $deletedCount++;

                        // Create log
                        $aktifitas = [
                            'tabel' => ['tpu_lahans'],
                            'uuid'  => [$uuid_dec],
                            'value' => [$value],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Menghapus Data Lahan (Bulk): ' . $data->kode_lahan . ' - ' . $uuid_dec,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = 'Gagal menghapus: ' . $data->kode_lahan;
                    }
                } catch (\Exception $e) {
                    $failedItems[] = 'Error pada ID ' . $uuid_enc . ': ' . $e->getMessage();
                    continue;
                }
            }

            $message = 'Berhasil menghapus ' . $deletedCount . ' lahan';
            if (! empty($failedItems)) {
                $message .= '. Gagal menghapus ' . count($failedItems) . ' item';
            }

            // Create summary log
            $summaryLog = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Bulk Delete Data Lahan - Berhasil: ' . $deletedCount . ', Gagal: ' . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['tpu_lahans'],
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
}