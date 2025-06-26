<?php
namespace App\Http\Controllers\web\backend\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalKategori;
use App\Models\PortalKategoriSub;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KategoriSubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $uuid_tags_enc)
    {
        // auth
        $auth            = Auth::user();
        $uuid_kategori   = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);

        if ($request->ajax()) {
            $data = PortalKategoriSub::where("uuid_kategori", $uuid_kategori)->orderBy("nama", "ASC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('status', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    if ($data->status == "1") {
                        $toggle       = "checked";
                        $text         = "Aktif";
                        $badge_class  = "badge-light-success";
                        $status_value = "1";
                    } else {
                        $toggle       = "";
                        $text         = "Tidak Aktif";
                        $badge_class  = "badge-light-danger";
                        $status_value = "0";
                    }

                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin") {
                        $status = '
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="status_' . $data->uuid . '"
                                       data-status="' . $uuid_enc . '"
                                       data-status-value="' . $status_value . '"
                                       ' . $toggle . '>
                                <label class="form-check-label fw-semibold text-gray-400 ms-3" for="status_' . $data->uuid . '">' . $text . '</label>
                            </div>
                        ';
                    } else {
                        $status = '<span class="badge ' . $badge_class . ' fs-7 fw-bold">' . $text . '</span>';
                    }
                    return $status;
                })
                ->addColumn('aksi', function ($data) use ($auth, $uuid_tags_enc) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.mst.tags.sub.edit', [$uuid_tags_enc, $uuid_enc]);

                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin") {
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
                        // Check ownership for non-admin users
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
                ->escapeColumns([''])
                ->make(true);
        }

        return view('admin.cms.master.kategori_sub.index', compact(
            'uuid_tags_enc',
            'master_kategori'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($uuid_tags_enc)
    {
        $uuid_kategori   = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);
        $title           = "Tambah Data Master Kategori Sub: " . $master_kategori->nama;
        $submit          = "Simpan";
        return view('admin.cms.master.kategori_sub.create_edit', compact(
            'uuid_tags_enc',
            'master_kategori',
            'title',
            'submit'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $uuid_tags_enc)
    {
        // auth
        $auth            = Auth::user();
        $uuid_kategori   = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);

        //validate
        $request->validate([
            "nama" => "required|string|max:100",
        ]);

        //uuid
        $uuid = Str::uuid();
        $nama = $request->nama;

        // cek kategori
        $cekKategori = PortalKategoriSub::whereNama($nama)->where("uuid_kategori", $uuid_kategori)->first();
        if ($cekKategori !== null) {
            // ada data
            alert()->error('Error!', 'Nama Kategori Sub Sudah Ada!');
            return \back()->withInput($request->all());
        }

        // value
        $value_1 = [
            "uuid"          => $uuid,
            "uuid_kategori" => $uuid_kategori,
            "nama"          => $nama,
            "slug"          => Str::slug($nama),
            "uuid_created"  => $auth->uuid,
        ];

        // save
        $save_1 = PortalKategoriSub::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori_sub"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Master Kategori Sub: " . $nama . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return \redirect()->route('prt.apps.mst.tags.sub.index', [$uuid_tags_enc]);
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
    public function edit($uuid_tags_enc, $uuid_enc)
    {
        // uuid
        $uuid_kategori   = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);
        $uuid            = Helper::decode($uuid_enc);
        $data            = PortalKategoriSub::findOrFail($uuid);
        $title           = "Edit Data Master Kategori Sub: " . $master_kategori->nama;
        $submit          = "Simpan";
        return view('admin.cms.master.kategori_sub.create_edit', compact(
            'uuid_tags_enc',
            'uuid_enc',
            'title',
            'submit',
            'data',
            'master_kategori'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_tags_enc, $uuid_enc)
    {
        // auth
        $auth            = Auth::user();
        $uuid_kategori   = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);

        //validate
        $request->validate([
            "nama" => "required|string|max:100",
        ]);

        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalKategoriSub::findOrFail($uuid);
        $nama = $request->nama;

        if ($nama != $data->nama) {
            // cek kategori
            $cekKategori = PortalKategoriSub::whereNama($nama)->where("uuid_kategori", $uuid_kategori)->first();
            if ($cekKategori !== null) {
                // ada data
                alert()->error('Error!', 'Nama Kategori Sub Sudah Ada!');
                return \back()->withInput($request->all());
            }
        }

        // value
        $value_1 = [
            "nama" => $nama,
            "slug" => Str::slug($nama),
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori_sub"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Master Kategori Sub: " . $nama . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Mengubah Data!");
            return \redirect()->route('prt.apps.mst.tags.sub.index', [$uuid_tags_enc]);
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
        $data = PortalKategoriSub::findOrFail($uuid);

        // save
        $save_1 = $data->delete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori_sub"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Master Kategori Sub: " . $data->nama . " - " . $uuid,
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
        $data = PortalKategoriSub::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori_sub"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Master Kategori Sub: " . $data->nama . " - " . $uuid,
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
     * Bulk delete sub categories
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
                    $data = PortalKategoriSub::find($uuid_enc);

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
                            "tabel" => ["portal_kategori_sub"],
                            "uuid"  => [$uuid_enc],
                            "value" => [$data->toArray()],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Menghapus Data Master Kategori Sub (Bulk): " . $data->nama . " - " . $uuid_enc,
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
            $message = "Berhasil menghapus {$deletedCount} sub kategori";

            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Delete Master Kategori Sub - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_kategori_sub"],
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
            Log::error('Bulk Delete Sub Kategori Error', [
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
                    $data = PortalKategoriSub::find($uuid_enc);

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
                            "tabel" => ["portal_kategori_sub"],
                            "uuid"  => [$uuid_enc],
                            "value" => [['status' => $newStatus]],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Bulk Update Status Master Kategori Sub: " . $data->nama . " - " . $uuid_enc,
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
            $message    = "Berhasil {$statusText} {$updatedCount} sub kategori";

            if (! empty($failedItems)) {
                $message .= ". Gagal {$statusText} " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Update Status Master Kategori Sub - Berhasil: {$updatedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_kategori_sub"],
                    "total_request" => count($uuids),
                    "total_updated" => $updatedCount,
                    "total_failed"  => count($failedItems),
                    "new_status"    => $newStatus,
                    "failed_items"  => $failedItems,
                ],
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            $response = [
                "status"        => true,
                "message"       => $message,
                "updated_count" => $updatedCount,
                "failed_count"  => count($failedItems),
                "failed_items"  => $failedItems,
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Bulk Status Update Sub Kategori Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $response = [
                "status"  => false,
                "message" => "Terjadi kesalahan saat mengubah status: " . $e->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }
}