<?php
namespace App\Http\Controllers\web\backend\links;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalLinks;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LinksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $tags)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;
        // tags
        $kategori = Helper::decode($tags);

        if ($request->ajax()) {
            // cek role
            if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                $data = PortalLinks::whereKategori($kategori)
                    ->orderBy("no_urut", "ASC")
                    ->get();
            } else {
                $data = PortalLinks::whereKategori($kategori)
                    ->whereUuidCreated($auth->uuid)
                    ->orderBy("no_urut", "ASC")
                    ->get();
            }
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('checkbox', function ($data) {
                    return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input row-checkbox" type="checkbox" value="' . $data->uuid . '" /></div>';
                })
                ->addColumn('judul', function ($data) use ($tags) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.links.edit', [$tags, $uuid_enc]);
                    return '
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . Str::limit($data->judul, 60, "...") . '</a>
                                <a href="' . $data->url . '" target="_blank" class="text-muted fw-semibold d-block fs-7 text-hover-primary">
                                    <i class="ki-outline ki-external-link fs-8 me-1"></i>' . Str::limit($data->url, 80, "...") . '
                                </a>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('penulis', function ($data) {
                    $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                    return '<span class="text-gray-600 fw-semibold">' . $penulis . '</span>';
                })
                ->addColumn('publisher', function ($data) {
                    if ($data->status == "1") {
                        $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                        return '<span class="text-success fw-semibold">' . $publisher . '</span>';
                    } else {
                        return '<span class="text-muted">-</span>';
                    }
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
                ->addColumn('tanggal', function ($data) {
                    $tanggal = Helper::TglSimple($data->created_at);
                    return '<span class="text-gray-600 fw-semibold">' . $tanggal . '</span>';
                })
                ->addColumn('aksi', function ($data) use ($tags, $auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.links.edit', [$tags, $uuid_enc]);

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
        return view('admin.cms.konten.internal.survey.index', compact(
            'tags',
            'kategori'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($tags)
    {
        // auth
        $auth = Auth::user();
        // tags
        $kategori = Helper::decode($tags);
        $title    = "Tambah Data Links " . $kategori;
        $submit   = "Simpan";
        return view('admin.cms.konten.internal.survey.create_edit', compact(
            'auth',
            'title',
            'tags',
            'kategori',
            'submit'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $tags)
    {
        // auth
        $auth = Auth::user();
        // tags
        $kategori = Helper::decode($tags);

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "no_urut" => "required|numeric|min:1",
            "judul"   => "required|string|max:300",
            "url"     => "required|url|max:300",
        ]);

        // value
        $uuid    = Str::uuid();
        $value_1 = [
            "uuid"     => $uuid,
            "no_urut"  => $request->no_urut,
            "judul"    => $request->judul,
            "url"      => $request->url,
            "kategori" => $kategori,
            "status"   => "1",
        ];

        // save
        $save_1 = PortalLinks::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_links"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Links UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return redirect()->route('prt.apps.links.index', [$tags]);
        } else {
            alert()->error('Error', "Gagal Menambahkan Data!");
            return back()->withInput($request->all());
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
    public function edit($tags, $uuid_enc)
    {
        // auth
        $auth = Auth::user();
        // tags
        $kategori = Helper::decode($tags);
        // uuid
        $uuid   = Helper::decode($uuid_enc);
        $data   = PortalLinks::findOrFail($uuid);
        $title  = "Edit Data Links " . $kategori;
        $submit = "Simpan";
        return view('admin.cms.konten.internal.survey.create_edit', compact(
            'auth',
            'uuid_enc',
            'tags',
            'kategori',
            'title',
            'submit',
            'data'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $tags, $uuid_enc)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;
        // tags
        $kategori = Helper::decode($tags);

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "no_urut" => "required|numeric|min:1",
            "judul"   => "required|string|max:300",
            "url"     => "required|url|max:300",
        ]);

        // $uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalLinks::findOrFail($uuid);

        // value
        $value_1 = [
            "no_urut"  => $request->no_urut,
            "judul"    => $request->judul,
            "url"      => $request->url,
            "kategori" => $kategori,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_links"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Links UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.links.index', [$tags]);
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $tags)
    {
        // Auth user
        $auth = Auth::user();

        // Decode UUID dari request
        $uuid = Helper::decode($request->uuid);

        // Dapatkan data dari database
        $data  = PortalLinks::findOrFail($uuid);
        $judul = $data->judul;

        // Update uuid_deleted dan status sebelum melakukan soft delete
        $save_1 = $data->delete();

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_links"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Links UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);

            // Return response success
            $msg      = "Data Berhasil Dihapus!";
            $response = [
                "status"  => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // Return response gagal
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
    public function status(Request $request, $tags)
    {
        // auth
        $auth = Auth::user();
        // tags
        $kategori = Helper::decode($tags);

        // uuid
        $uuid   = Helper::decode($request->uuid);
        $status = $request->status;
        if ($status == "0") {
            $status_update = "1";
        } else {
            $status_update = "0";
        }

        // data
        $data = PortalLinks::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_links"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Links {$kategori}: " . $data->judul . " - " . $uuid,
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
    public function bulkDestroy(Request $request, $tags)
    {
        try {
            // auth
            $auth = Auth::user();
            // tags
            $kategori = Helper::decode($tags);

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
                    $data = PortalLinks::find($uuid_enc);

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
                        $failedItems[] = "Tidak memiliki izin untuk menghapus: {$data->judul}";
                        continue;
                    }

                    // Delete the data
                    if ($data->delete()) {
                        $deletedCount++;

                        // Create log for each deleted item
                        $aktifitas = [
                            "tabel" => ["portal_links"],
                            "uuid"  => [$uuid_enc],
                            "value" => [$data->toArray()],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Menghapus Data Links {$kategori} (Bulk): " . $data->judul . " - " . $uuid_enc,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = "Gagal menghapus: {$data->judul}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid_enc}: " . $e->getMessage();
                    continue;
                }
            }

            // Prepare response message
            $message = "Berhasil menghapus {$deletedCount} Links {$kategori}";

            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Delete Links - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_links"],
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
    public function bulkStatus(Request $request, $tags)
    {
        try {
            // auth
            $auth = Auth::user();
            // tags
            $kategori = Helper::decode($tags);

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
                    $data = PortalLinks::find($uuid_enc);

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
                        $failedItems[] = "Tidak memiliki izin untuk mengubah: {$data->judul}";
                        continue;
                    }

                    // Update status
                    if ($data->update(['status' => $newStatus])) {
                        $updatedCount++;

                        // Create log
                        $aktifitas = [
                            "tabel" => ["portal_links"],
                            "uuid"  => [$uuid_enc],
                            "value" => [['status' => $newStatus]],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Mengubah Status Links " . $kategori . ": " . $data->judul . " - " . $uuid_enc,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = "Gagal mengubah status: {$data->judul}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid_enc}: " . $e->getMessage();
                    continue;
                }
            }

            $statusText = $newStatus == '1' ? 'mengaktifkan' : 'menonaktifkan';
            $message    = "Berhasil {$statusText} {$updatedCount} Links {$kategori}";

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