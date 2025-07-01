<?php
namespace App\Http\Controllers\web\backend\banner;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalBanner;
use App\Models\PortalKategori;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();

        // get kategori
        $kategoriList = PortalKategori::whereType("Banner")->whereStatus("1")->orderBy("nama")->get();

        // cek filter
        if ($request->session()->exists('filter_kategori_banner')) {
            $kategori = $request->session()->get('filter_kategori_banner');
        } else {
            $request->session()->put('filter_kategori_banner', 'Semua Data');
            $kategori = "Semua Data";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $kategori = $_GET['filter']['kategori'];
                $request->session()->put('filter_kategori_banner', $kategori);
            } else {
                $kategori = $request->session()->get('filter_kategori_banner');
            }

            // Query data berdasarkan filter kategori
            $query = PortalBanner::query();
            if ($kategori !== 'Semua Data') {
                $query->whereKategori($kategori);
            }
            $data = $query->orderBy("tanggal", "DESC")->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('checkbox', function ($data) {
                    return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input row-checkbox" type="checkbox" value="' . $data->uuid . '" /></div>';
                })
                ->addColumn('judul', function ($data) {
                    $uuid_enc      = Helper::encode($data->uuid);
                    $edit_url      = route('prt.apps.banner.edit', $uuid_enc);
                    $thumbnail_url = asset('be/media/misc/image-placeholder.svg');
                    if (! empty($data->thumbnails)) {
                        $thumbnail_url = Helper::urlImg($data->thumbnails);
                    }
                    return '
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-50px me-5">
                                <img src="' . $thumbnail_url . '" class="h-75 align-self-end" alt="Thumbnail" style="object-fit: cover; border-radius: 8px;" />
                            </div>
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . Str::limit($data->judul, 60, "...") . '</a>
                                <span class="text-muted fw-semibold d-block fs-7">' . Str::slug($data->judul) . '</span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('kategori', function ($data) {
                    return '<span class="badge badge-light-primary fw-bold fs-7 px-3 py-2">' . $data->kategori . '</span>';
                })
                ->addColumn('penulis', function ($data) {
                    $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                    return '<span class="text-gray-600 fw-semibold">' . $penulis . '</span>';
                })
                ->addColumn('publisher', function ($data) {
                    if ($data->status == "1") {
                        $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                        return '<span class="text-success fw-semibold">' . $publisher . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('tanggal', function ($data) {
                    $tanggal = Helper::TglSimple($data->tanggal);
                    return '<span class="text-gray-600 fw-semibold">' . $tanggal . '</span>';
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
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                        $status = '
                            <div class="form-check form-switch form-check-custom form-check-' . $color . '">
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
                    $edit_url = route('prt.apps.banner.edit', $uuid_enc);
                    $role     = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                        $actions = '
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
                            $actions = '
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
                ->escapeColumns([])
                ->make(true);
        }

        return view('admin.cms.konten.media.banner.index', compact(
            'kategori',
            'kategoriList'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // get kategori
        $kategoriList = PortalKategori::whereType("Banner")->whereStatus("1")->orderBy("nama")->get();
        $title        = "Tambah Data Banner";
        $submit       = "Simpan";
        return view('admin.cms.konten.media.banner.create_edit', compact(
            'title',
            'submit',
            'kategoriList'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // auth
        $auth = Auth::user();

        // validate
        $request->validate([
            "judul"      => "required|string|max:300",
            "thumbnails" => "required|image|mimes:png,jpg,jpeg|max:2048",
            "url"        => "sometimes|nullable|url",
            "deskripsi"  => "sometimes|nullable|string|max:500",
            "warna_text" => "required",
            "kategori"   => "required|string|max:100",
            'status'     => 'required|in:0,1',
        ]);

        // value
        $uuid    = Str::uuid();
        $path    = "banner/" . date('Y') . "/" . $uuid;
        $value_1 = [
            "uuid"         => $uuid,
            "judul"        => $request->judul,
            "url"          => $request->url,
            "deskripsi"    => $request->deskripsi,
            "tanggal"      => Carbon::now(),
            "warna_text"   => $request->warna_text,
            "kategori"     => $request->kategori,
            'status'       => $request->status,
            'uuid_created' => $auth->uuid,
        ];

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            $img = Helper::UpImg($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return \back();
            }
            $value_1['thumbnails'] = $img;
        }

        // save
        $save_1 = PortalBanner::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_banner"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Banner UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return \redirect()->route('prt.apps.banner.index');
        } else {
            alert()->error('Error', "Gagal Menambahkan Data!");
            return \back()->withInput($request->all());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalBanner::findOrFail($uuid);
        // get kategori
        $kategoriList = PortalKategori::whereType("Banner")->whereStatus("1")->orderBy("nama")->get();
        $title        = "Edit Data Banner";
        $submit       = "Simpan";
        return view('admin.cms.konten.media.banner.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'kategoriList',
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

        // validate
        $request->validate([
            "judul"      => "required|string|max:300",
            "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "url"        => "sometimes|nullable|url",
            "deskripsi"  => "sometimes|nullable|string|max:500",
            "warna_text" => "required",
            "kategori"   => "required|string|max:100",
            'status'     => 'required|in:0,1',
        ]);

        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalBanner::findOrFail($uuid);

        // value
        $thn     = date("Y", \strtotime($data->created_at));
        $path    = "banner/" . $thn . "/" . $uuid;
        $value_1 = [
            "judul"        => $request->judul,
            "deskripsi"    => $request->deskripsi,
            "warna_text"   => $request->warna_text,
            "url"          => $request->url,
            "kategori"     => $request->kategori,
            'status'       => $request->status,
            "uuid_updated" => $auth->uuid,
        ];

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            $img = Helper::UpImg($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return \back();
            }
            $value_1['thumbnails'] = $img;
        }

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_banner"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Banner UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Mengubah Data!");
            return \redirect()->route('prt.apps.banner.index');
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
        try {
            // auth
            $auth = Auth::user();

            // validate
            $request->validate([
                'uuid' => 'required|string',
            ]);

            // uuid
            $uuid = Helper::decode($request->uuid);
            $data = PortalBanner::findOrFail($uuid);

            // Check permission
            $role      = $auth->role;
            $canDelete = ($role == "Super Admin" || $role == "Admin" || $role == "Editor");
            if (! $canDelete && isset($data->uuid_created)) {
                $canDelete = ($data->uuid_created == $auth->uuid);
            }

            if (! $canDelete) {
                return response()->json([
                    "status"  => false,
                    "message" => "Tidak memiliki izin untuk menghapus banner: " . $data->judul,
                ], 403);
            }

            // Delete the data
            $tahun = Carbon::parse($data->tanggal)->year;
            $path  = "banner/{$tahun}/{$data->uuid}";
            if ($data->status == "0") {
                // Hard delete for non-published banners
                Helper::deleteFolderIfExists("directory", $path);
                $save_1 = $data->forceDelete();
            } else {
                // Soft delete for published banners
                $data->update([
                    'uuid_deleted' => $auth->uuid,
                ]);
                $save_1 = $data->delete();
            }

            if ($save_1) {
                // create log
                $aktifitas = [
                    "tabel" => ["portal_banner"],
                    "uuid"  => [$uuid],
                    "value" => [$data->toArray()],
                ];
                $log = [
                    "apps"      => "Portal Apps",
                    "subjek"    => "Menghapus Data Banner: " . $data->judul . " - " . $uuid,
                    "aktifitas" => $aktifitas,
                    "device"    => "web",
                ];
                Helper::addToLogAktifitas($request, $log);

                return response()->json([
                    "status"  => true,
                    "message" => "Data Berhasil Dihapus!",
                ], 200);
            } else {
                return response()->json([
                    "status"  => false,
                    "message" => "Data Gagal Dihapus!",
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('Delete Banner Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                "status"  => false,
                "message" => "Terjadi kesalahan saat menghapus data: " . $e->getMessage(),
            ], 500);
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
        $data = PortalBanner::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_banner"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Banner: " . $data->judul . " - " . $uuid,
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
     * Bulk delete banners
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
            foreach ($uuids as $index => $uuid) {
                try {
                    // Find data
                    $data = PortalBanner::find($uuid);

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuids[$index]} tidak ditemukan";
                        continue;
                    }

                    // Check permission
                    $role      = $auth->role;
                    $canDelete = ($role == "Super Admin" || $role == "Admin" || $role == "Editor");
                    if (! $canDelete && isset($data->uuid_created)) {
                        $canDelete = ($data->uuid_created == $auth->uuid);
                    }

                    if (! $canDelete) {
                        $failedItems[] = "Tidak memiliki izin untuk menghapus: {$data->judul}";
                        continue;
                    }

                    // Delete the data
                    $tahun = Carbon::parse($data->tanggal)->year;
                    $path  = "banner/{$tahun}/{$data->uuid}";
                    if ($data->status == "0") {
                        // Hard delete for non-published banners
                        Helper::deleteFolderIfExists("directory", $path);
                        $save_1 = $data->forceDelete();
                    } else {
                        // Soft delete for published banners
                        $data->update([
                            'uuid_deleted' => $auth->uuid,
                        ]);
                        $save_1 = $data->delete();
                    }

                    if ($save_1) {
                        $deletedCount++;

                        // Create log for each deleted item
                        $aktifitas = [
                            "tabel" => ["portal_banner"],
                            "uuid"  => [$uuid],
                            "value" => [$data->toArray()],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Menghapus Data Banner (Bulk): " . $data->judul . " - " . $uuid,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = "Gagal menghapus: {$data->judul}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuids[$index]}: " . $e->getMessage();
                    continue;
                }
            }

            // Prepare response message
            $message = "Berhasil menghapus {$deletedCount} banner";
            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Delete Banner - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_banner"],
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
            Log::error('Bulk Delete Banner Error', [
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
            foreach ($uuids as $index => $uuid) {
                try {
                    // Find data
                    $data = PortalBanner::find($uuid);

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuids[$index]} tidak ditemukan";
                        continue;
                    }

                    // Check permission
                    $role      = $auth->role;
                    $canUpdate = ($role == "Super Admin" || $role == "Admin" || $role == "Editor");
                    if (! $canUpdate && isset($data->uuid_created)) {
                        $canUpdate = ($data->uuid_created == $auth->uuid);
                    }

                    if (! $canUpdate) {
                        $failedItems[] = "Tidak memiliki izin untuk mengubah status: {$data->judul}";
                        continue;
                    }

                    // Update status
                    if ($data->update([
                        'status'       => $newStatus,
                        'uuid_updated' => $auth->uuid,
                    ])) {
                        $updatedCount++;

                        // Create log
                        $aktifitas = [
                            "tabel" => ["portal_banner"],
                            "uuid"  => [$uuid],
                            "value" => [['status' => $newStatus]],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Bulk Update Status Banner: " . $data->judul . " - " . $uuid,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = "Gagal mengubah status: {$data->judul}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuids[$index]}: " . $e->getMessage();
                    continue;
                }
            }

            $statusText = $newStatus == '1' ? 'mengaktifkan' : 'menonaktifkan';
            $message    = "Berhasil {$statusText} {$updatedCount} banner";
            if (! empty($failedItems)) {
                $message .= ". Gagal {$statusText} " . count($failedItems) . " item";
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Update Status Banner - Berhasil: {$updatedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_banner"],
                    "total_request" => count($uuids),
                    "total_updated" => $updatedCount,
                    "total_failed"  => count($failedItems),
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
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Bulk Status Update Banner Error', [
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