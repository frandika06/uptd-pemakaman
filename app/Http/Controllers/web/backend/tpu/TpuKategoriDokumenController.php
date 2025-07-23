<?php
namespace App\Http\Controllers\web\backend\tpu;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TpuKategoriDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class TpuKategoriDokumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // Cek filter
        if ($request->session()->exists('filter_type_kategori_dokumen')) {
            $type = $request->session()->get('filter_type_kategori_dokumen');
        } else {
            $request->session()->put('filter_type_kategori_dokumen', 'Semua Data');
            $type = 'Semua Data';
        }

        if ($request->ajax()) {
            if (isset($_GET['filter']) && isset($_GET['filter']['type'])) {
                $type = $_GET['filter']['type'];
                $request->session()->put('filter_type_kategori_dokumen', $type);
            }

            // Cek type
            if ($type == 'Semua Data') {
                $data = TpuKategoriDokumen::orderBy('tipe', 'ASC')->orderBy('nama', 'ASC')->get();
            } else {
                $data = TpuKategoriDokumen::where('tipe', $type)->orderBy('tipe', 'ASC')->orderBy('nama', 'ASC')->get();
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('tpu.kategori-dokumen.edit', $uuid_enc);
                    return '
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $data->nama . '</a>
                                <span class="text-muted fw-semibold d-block fs-7">' . Str::slug($data->nama) . '</span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('tipe', function ($data) {
                    $colors = [
                        'foto'        => 'primary',
                        'dokumen-tpu' => 'success',
                        // 'dokumen-iptm' => 'warning', // Tambahkan jika diperlukan nanti
                    ];

                    $color = $colors[$data->tipe] ?? 'secondary';

                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $data->tipe . '</span>';
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
                ->addColumn('aksi', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('tpu.kategori-dokumen.edit', $uuid_enc);

                    $role = $auth->role;
                    if ($role == 'Super Admin' || $role == 'Admin') {
                        $aksi = '
                            <div class="d-flex justify-content-center">
                                <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="ki-outline ki-pencil fs-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                    <i class="ki-outline ki-trash fs-2"></i>
                                </a>
                            </div>
                        ';
                    } else {
                        if (isset($data->uuid_created) && $data->uuid_created == $auth->uuid) {
                            $aksi = '
                                <div class="d-flex justify-content-center">
                                    <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                        <i class="ki-outline ki-pencil fs-2"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                        <i class="ki-outline ki-trash fs-2"></i>
                                    </a>
                                </div>
                            ';
                        } else {
                            $aksi = '
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
                    return $aksi;
                })
                ->escapeColumns([])
                ->make(true);
        }

        // Get all types
        $getType = TpuKategoriDokumen::select('tipe')->groupBy('tipe')->orderBy('tipe', 'ASC')->get();
        return view('admin.tpu.master.kategori_dokumen.index', compact(
            'type',
            'getType'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title  = 'Tambah Data Master Kategori Dokumen';
        $submit = 'Simpan';
        return view('admin.tpu.master.kategori_dokumen.create_edit', compact(
            'title',
            'submit'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // Validate
        $request->validate([
            'nama' => 'required|string|max:100',
            'tipe' => 'required|string|max:100',
        ]);

        // UUID
        $uuid = Str::uuid();
        $nama = $request->nama;
        $tipe = $request->tipe;

        // Cek kategori
        $cekKategori = TpuKategoriDokumen::where('nama', $nama)->where('tipe', $tipe)->first();
        if ($cekKategori !== null) {
            alert()->error('Error!', 'Nama Kategori Dokumen Sudah Ada!');
            return \back()->withInput($request->all());
        }

        // Value
        $value = [
            'uuid'         => $uuid,
            'nama'         => $nama,
            'tipe'         => $tipe,
            'status'       => '1',
            'uuid_created' => $auth->uuid,
            'uuid_updated' => $auth->uuid,
        ];

        // Save
        $save = TpuKategoriDokumen::create($value);
        if ($save) {
            // Create log
            $aktifitas = [
                'tabel' => ['tpu_kategori_dokumens'],
                'uuid'  => [$uuid],
                'value' => [$value],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Menambahkan Data Master Kategori Dokumen: ' . $nama . ' - ' . $uuid,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            // Alert success
            alert()->success('Success', 'Berhasil Menambahkan Data!');
            return \redirect()->route('tpu.kategori-dokumen.index');
        } else {
            alert()->error('Error', 'Gagal Menambahkan Data!');
            return \back()->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($uuid_enc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        // UUID
        $uuid   = Helper::decode($uuid_enc);
        $data   = TpuKategoriDokumen::findOrFail($uuid);
        $title  = 'Edit Data Master Kategori Dokumen';
        $submit = 'Simpan';
        return view('admin.tpu.master.kategori_dokumen.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'data'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        // Auth
        $auth = Auth::user();

        // Validate
        $request->validate([
            'nama' => 'required|string|max:100',
            'tipe' => 'required|string|max:100',
        ]);

        // UUID
        $uuid = Helper::decode($uuid_enc);
        $data = TpuKategoriDokumen::findOrFail($uuid);
        $nama = $request->nama;
        $tipe = $request->tipe;

        if ($nama != $data->nama || $tipe != $data->tipe) {
            // Cek kategori
            $cekKategori = TpuKategoriDokumen::where('nama', $nama)->where('tipe', $tipe)->first();
            if ($cekKategori !== null) {
                alert()->error('Error!', 'Nama Kategori Dokumen Sudah Ada!');
                return \back()->withInput($request->all());
            }
        }

        // Value
        $value = [
            'nama'         => $nama,
            'tipe'         => $tipe,
            'uuid_updated' => $auth->uuid,
        ];

        // Save
        $save = $data->update($value);
        if ($save) {
            // Create log
            $aktifitas = [
                'tabel' => ['tpu_kategori_dokumens'],
                'uuid'  => [$uuid],
                'value' => [$value],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Mengubah Data Master Kategori Dokumen: ' . $nama . ' - ' . $uuid,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            // Alert success
            alert()->success('Success', 'Berhasil Mengubah Data!');
            return \redirect()->route('tpu.kategori-dokumen.index');
        } else {
            alert()->error('Error', 'Gagal Mengubah Data!');
            return \back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // UUID
        $uuid = Helper::decode($request->uuid);

        // Data
        $data = TpuKategoriDokumen::findOrFail($uuid);

        // Save
        $save = $data->delete();
        if ($save) {
            // Create log
            $aktifitas = [
                'tabel' => ['tpu_kategori_dokumens'],
                'uuid'  => [$uuid],
                'value' => [$data->toArray()],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Menghapus Data Master Kategori Dokumen: ' . $data->nama . ' - ' . $uuid,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            // Alert success
            $msg      = 'Data Berhasil Dihapus!';
            $response = [
                'status'  => true,
                'message' => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // Success
            $msg      = 'Data Gagal Dihapus!';
            $response = [
                'status'  => false,
                'message' => $msg,
            ];
            return response()->json($response, 422);
        }
    }

    /**
     * Status Aktif
     */
    public function status(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // UUID
        $uuid   = Helper::decode($request->uuid);
        $status = $request->status;
        if ($status == '0') {
            $status_update = '1';
        } else {
            $status_update = '0';
        }

        // Data
        $data = TpuKategoriDokumen::findOrFail($uuid);

        // Value
        $value = [
            'status'       => $status_update,
            'uuid_updated' => $auth->uuid,
        ];

        // Save
        $save = $data->update($value);
        if ($save) {
            // Create log
            $aktifitas = [
                'tabel' => ['tpu_kategori_dokumens'],
                'uuid'  => [$uuid],
                'value' => [$value],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Mengubah Status Master Kategori Dokumen: ' . $data->nama . ' - ' . $uuid,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            // Alert success
            $msg      = 'Status Berhasil Diubah!';
            $response = [
                'status'  => true,
                'message' => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // Success
            $msg      = 'Status Gagal Diubah!';
            $response = [
                'status'  => false,
                'message' => $msg,
            ];
            return response()->json($response, 422);
        }
    }

    /**
     * Bulk delete categories
     */
    public function bulkDestroy(Request $request)
    {
        try {
            // Auth
            $auth = Auth::user();

            // Validate request
            $request->validate([
                'uuids'   => 'required|array|min:1',
                'uuids.*' => 'required|string',
            ]);

            $uuids        = $request->uuids;
            $deletedCount = 0;
            $failedItems  = [];

            // Loop through each UUID and delete
            foreach ($uuids as $uuid_enc) {
                try {
                    $uuid = $uuid_enc;
                    $data = TpuKategoriDokumen::findOrFail($uuid);

                    $role      = $auth->role;
                    $canDelete = ($role == 'Super Admin' || $role == 'Admin');

                    if (! $canDelete && isset($data->uuid_created)) {
                        $canDelete = ($data->uuid_created == $auth->uuid);
                    }

                    if (! $canDelete) {
                        $failedItems[] = 'Tidak memiliki izin untuk menghapus: ' . $data->nama;
                        continue;
                    }

                    if ($data->delete()) {
                        $deletedCount++;

                        $aktifitas = [
                            'tabel' => ['tpu_kategori_dokumens'],
                            'uuid'  => [$uuid],
                            'value' => [$data->toArray()],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Menghapus Data Master Kategori Dokumen (Bulk): ' . $data->nama . ' - ' . $uuid,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = 'Gagal menghapus: ' . $data->nama;
                    }
                } catch (\Exception $e) {
                    $failedItems[] = 'Error pada ID ' . $uuid_enc . ': ' . $e->getMessage();
                    continue;
                }
            }

            $message = 'Berhasil menghapus ' . $deletedCount . ' kategori';
            if (! empty($failedItems)) {
                $message .= '. Gagal menghapus ' . count($failedItems) . ' item';
            }

            $summaryLog = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Bulk Delete Master Kategori Dokumen - Berhasil: ' . $deletedCount . ', Gagal: ' . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['tpu_kategori_dokumens'],
                    'total_request' => count($uuids),
                    'total_deleted' => $deletedCount,
                    'total_failed'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            $response = [
                'status'        => true,
                'message'       => $message,
                'deleted_count' => $deletedCount,
                'failed_count'  => count($failedItems),
                'failed_items'  => $failedItems,
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            \Log::error('Bulk Delete Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $response = [
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Bulk status update
     */
    public function bulkStatus(Request $request)
    {
        try {
            // Auth
            $auth = Auth::user();

            // Validate request
            $request->validate([
                'uuids'   => 'required|array|min:1',
                'uuids.*' => 'required|string',
                'status'  => 'required|in:0,1',
            ]);

            $uuids        = $request->uuids;
            $newStatus    = $request->status;
            $updatedCount = 0;
            $failedItems  = [];

            // Loop through each UUID and update status
            foreach ($uuids as $uuid_enc) {
                try {
                    $uuid = $uuid_enc;
                    $data = TpuKategoriDokumen::findOrFail($uuid);

                    $role      = $auth->role;
                    $canUpdate = ($role == 'Super Admin' || $role == 'Admin');

                    if (! $canUpdate && isset($data->uuid_created)) {
                        $canUpdate = ($data->uuid_created == $auth->uuid);
                    }

                    if (! $canUpdate) {
                        $failedItems[] = 'Tidak memiliki izin untuk mengubah: ' . $data->nama;
                        continue;
                    }

                    $value = ['status' => $newStatus, 'uuid_updated' => $auth->uuid];
                    if ($data->update($value)) {
                        $updatedCount++;

                        $aktifitas = [
                            'tabel' => ['tpu_kategori_dokumens'],
                            'uuid'  => [$uuid],
                            'value' => [$value],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Bulk Update Status Master Kategori Dokumen: ' . $data->nama . ' - ' . $uuid,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = 'Gagal mengubah status: ' . $data->nama;
                    }
                } catch (\Exception $e) {
                    $failedItems[] = 'Error pada ID ' . $uuid_enc . ': ' . $e->getMessage();
                    continue;
                }
            }

            $statusText = $newStatus == '1' ? 'mengaktifkan' : 'menonaktifkan';
            $message    = 'Berhasil ' . $statusText . ' ' . $updatedCount . ' kategori';
            if (! empty($failedItems)) {
                $message .= '. Gagal ' . $statusText . ' ' . count($failedItems) . ' item';
            }

            $response = [
                'status'        => true,
                'message'       => $message,
                'updated_count' => $updatedCount,
                'failed_count'  => count($failedItems),
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status'  => false,
                'message' => 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }
}