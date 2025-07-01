<?php
namespace App\Http\Controllers\web\backend\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();

        // cek filter
        if ($request->session()->exists('filter_type_kategori')) {
            $type = $request->session()->get('filter_type_kategori');
        } else {
            $request->session()->put('filter_type_kategori', 'Semua Data');
            $type = "Semua Data";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $type = $_GET['filter']['type'];
                $request->session()->put('filter_type_kategori', $type);
            }
            // cek type
            if ($type == "Semua Data") {
                $data = PortalKategori::orderBy("type", "ASC")->orderBy("nama", "ASC")->get();
            } else {
                $data = PortalKategori::whereType($type)->orderBy("type", "ASC")->orderBy("nama", "ASC")->get();
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.mst.tags.edit', $uuid_enc);
                    return '
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $data->nama . '</a>
                                <span class="text-muted fw-semibold d-block fs-7">' . Str::slug($data->nama) . '</span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('type', function ($data) {
                    $colors = [
                        'Postingan' => 'primary',
                        'Halaman'   => 'success',
                        'Banner'    => 'warning',
                        'Galeri'    => 'info',
                        'Video'     => 'danger',
                        'Unduhan'   => 'dark',
                        'FAQ'       => 'secondary',
                    ];

                    $color = $colors[$data->type] ?? 'primary';

                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $data->type . '</span>';
                })
                ->addColumn('status', function ($data) use ($auth) {
                    $uuid = Helper::encode($data->uuid);
                    if ($data->status == "1") {
                        $checked = "checked";
                        $text    = "Aktif";
                        $color   = "success";
                    } else {
                        $checked = "";
                        $text    = "Tidak Aktif";
                        $color   = "danger";
                    }

                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin") {
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
                ->addColumn('kategori_sub', function ($data) {
                    if (count($data->RelKategoriSub ?? []) > 0) {
                        $jumlah = Helper::toDot($data->GetJumlahKetegoriSub());
                        $color  = "primary";
                    } else {
                        $jumlah = 0;
                        $color  = "secondary";
                    }

                    return '
                        <div class="d-flex align-items-center justify-content-center">
                            <span class="badge badge-light-' . $color . ' fs-base fs-3">' . $jumlah . '</span>
                        </div>
                    ';
                })
                ->addColumn('aksi', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.mst.tags.edit', $uuid_enc);
                    $sub_url  = route('prt.apps.mst.tags.sub.index', $uuid_enc);

                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin") {
                        $aksi = '
                            <div class="d-flex justify-content-center">
                                <a href="' . $sub_url . '" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1" data-bs-toggle="tooltip" title="Sub Kategori">
                                    <i class="ki-outline ki-category fs-2"></i>
                                </a>
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
                                    <a href="' . $sub_url . '" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1" data-bs-toggle="tooltip" title="Sub Kategori">
                                        <i class="ki-outline ki-category fs-2"></i>
                                    </a>
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
                                    <a href="' . $sub_url . '" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1" data-bs-toggle="tooltip" title="Sub Kategori">
                                        <i class="ki-outline ki-category fs-2"></i>
                                    </a>
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
                ->escapeColumns([''])
                ->make(true);
        }

        // get all type
        $getType = PortalKategori::select('type')->groupBy('type')->orderBy("type", "ASC")->get();
        return view('admin.cms.master.kategori.index', compact(
            'type',
            'getType'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title  = "Tambah Data Master Kategori";
        $submit = "Simpan";
        return view('admin.cms.master.kategori.create_edit', compact(
            'title',
            'submit'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // auth
        $auth = Auth::user();

        //validate
        $request->validate([
            "nama" => "required|string|max:100",
            "type" => "required|string|max:100",
        ]);

        //uuid
        $uuid = Str::uuid();
        $nama = $request->nama;
        $type = $request->type;

        // cek kategori
        $cekKategori = PortalKategori::whereNama($nama)->whereType($type)->first();
        if ($cekKategori !== null) {
            // ada data
            alert()->error('Error!', 'Nama Kategori Sudah Ada!');
            return \back()->withInput($request->all());
        }

        // value
        $value_1 = [
            "uuid" => $uuid,
            "nama" => $nama,
            "slug" => Str::slug($nama),
            "type" => $type,
        ];

        // save
        $save_1 = PortalKategori::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Master Kategori: " . $nama . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return \redirect()->route('prt.apps.mst.tags.index');
        } else {
            alert()->error('Error', "Gagal Menambahkan Data!");
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
        // uuid
        $uuid   = Helper::decode($uuid_enc);
        $data   = PortalKategori::findOrFail($uuid);
        $title  = "Edit Data Master Kategori";
        $submit = "Simpan";
        return view('admin.cms.master.kategori.create_edit', compact(
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
        // auth
        $auth = Auth::user();

        //validate
        $request->validate([
            "nama" => "required|string|max:100",
            "type" => "required|string|max:100",
        ]);

        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalKategori::findOrFail($uuid);
        $nama = $request->nama;
        $type = $request->type;

        if ($nama != $data->nama || $type != $data->type) {
            // cek kategori
            $cekKategori = PortalKategori::whereNama($nama)->whereType($type)->first();
            if ($cekKategori !== null) {
                // ada data
                alert()->error('Error!', 'Nama Kategori Sudah Ada!');
                return \back()->withInput($request->all());
            }
        }

        // value
        $value_1 = [
            "nama" => $nama,
            "slug" => Str::slug($nama),
            "type" => $type,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Master Kategori: " . $nama . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Mengubah Data!");
            return \redirect()->route('prt.apps.mst.tags.index');
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return \back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // auth
        $auth = Auth::user();

        // uuid
        $uuid = Helper::decode($request->uuid);

        // data
        $data = PortalKategori::findOrFail($uuid);

        // save
        $save_1 = $data->delete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Master Kategori: " . $data->nama . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            $msg      = "Data Berhasil Dihapus!";
            $response = [
                "status"  => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // success
            $msg      = "Data Gagal Dihapus!";
            $response = [
                "status"  => false,
                "message" => $msg,
            ];
            return response()->json($response, 422);
        }
    }

    /**
     * Status Aktif
     */
    public function status(Request $request)
    {
        // auth
        $auth = Auth::user();

        // uuid
        $uuid   = Helper::decode($request->uuid);
        $status = $request->status;
        if ($status == "0") {
            $status_update = "1";
        } else {
            $status_update = "0";
        }

        // data
        $data = PortalKategori::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Master Kategori: " . $data->nama . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            $msg      = "Status Berhasil Diubah!";
            $response = [
                "status"  => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // success
            $msg      = "Status Gagal Diubah!";
            $response = [
                "status"  => false,
                "message" => $msg,
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
            // auth
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
                    // Find data
                    $data = PortalKategori::find($uuid_enc);

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid_enc} tidak ditemukan";
                        continue;
                    }

                    // Check permission (if needed)
                    $role      = $auth->role;
                    $canDelete = ($role == "Super Admin" || $role == "Admin");

                    // If not admin, check ownership
                    if (! $canDelete && isset($data->uuid_created)) {
                        $canDelete = ($data->uuid_created == $auth->uuid);
                    }

                    if (! $canDelete) {
                        $failedItems[] = "Tidak memiliki izin untuk menghapus: {$data->nama}";
                        continue;
                    }

                    // Delete the data
                    if ($data->delete()) {
                        $deletedCount++;

                        // Create log for each deleted item
                        $aktifitas = [
                            "tabel" => ["portal_kategori"],
                            "uuid"  => [$uuid_enc],
                            "value" => [$data->toArray()],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Menghapus Data Master Kategori (Bulk): " . $data->nama . " - " . $uuid_enc,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = "Gagal menghapus: {$data->nama}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid_enc}: " . $e->getMessage();
                    continue;
                }
            }

            // Prepare response message
            $message = "Berhasil menghapus {$deletedCount} kategori";

            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Delete Master Kategori - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_kategori"],
                    "total_request" => count($uuids),
                    "total_deleted" => $deletedCount,
                    "total_failed"  => count($failedItems),
                    "failed_items"  => $failedItems,
                ],
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            $response = [
                "status"        => true,
                "message"       => $message,
                "deleted_count" => $deletedCount,
                "failed_count"  => count($failedItems),
                "failed_items"  => $failedItems,
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Bulk Delete Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $response = [
                "status"  => false,
                "message" => "Terjadi kesalahan saat menghapus data: " . $e->getMessage(),
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
            // auth
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
                    // Find data
                    $data = PortalKategori::find($uuid_enc);

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid_enc} tidak ditemukan";
                        continue;
                    }

                    // Check permission
                    $role      = $auth->role;
                    $canUpdate = ($role == "Super Admin" || $role == "Admin");

                    if (! $canUpdate && isset($data->uuid_created)) {
                        $canUpdate = ($data->uuid_created == $auth->uuid);
                    }

                    if (! $canUpdate) {
                        $failedItems[] = "Tidak memiliki izin untuk mengubah: {$data->nama}";
                        continue;
                    }

                    // Update status
                    if ($data->update(['status' => $newStatus])) {
                        $updatedCount++;

                        // Create log
                        $aktifitas = [
                            "tabel" => ["portal_kategori"],
                            "uuid"  => [$uuid_enc],
                            "value" => [['status' => $newStatus]],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Bulk Update Status Master Kategori: " . $data->nama . " - " . $uuid_enc,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = "Gagal mengubah status: {$data->nama}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid_enc}: " . $e->getMessage();
                    continue;
                }
            }

            $statusText = $newStatus == '1' ? 'mengaktifkan' : 'menonaktifkan';
            $message    = "Berhasil {$statusText} {$updatedCount} kategori";

            if (! empty($failedItems)) {
                $message .= ". Gagal {$statusText} " . count($failedItems) . " item";
            }

            $response = [
                "status"        => true,
                "message"       => $message,
                "updated_count" => $updatedCount,
                "failed_count"  => count($failedItems),
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                "status"  => false,
                "message" => "Terjadi kesalahan saat mengubah status: " . $e->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }
}