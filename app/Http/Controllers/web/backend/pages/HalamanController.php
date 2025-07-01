<?php
namespace App\Http\Controllers\web\backend\pages;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalPage;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HalamanController extends Controller
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

        // cek filter
        if ($request->session()->exists('filter_status_halaman_' . $tags)) {
            $status = $request->session()->get('filter_status_halaman_' . $tags);
        } else {
            $request->session()->put('filter_status_halaman_' . $tags, 'Published');
            $status = "Published";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $status = $_GET['filter']['status'];
                $request->session()->put('filter_status_halaman_' . $tags, $status);
            }

            // cek role
            if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                if ($status == "Draft") {
                    $data = PortalPage::whereStatus($status)
                        ->whereUuidCreated($auth->uuid)
                        ->whereKategori($kategori)
                        ->orderBy("no_urut", "ASC")
                        ->get();
                } else {
                    $data = PortalPage::whereStatus($status)
                        ->whereKategori($kategori)
                        ->orderBy("no_urut", "ASC")
                        ->get();
                }
            } else {
                $data = PortalPage::whereStatus($status)
                    ->whereKategori($kategori)
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
                    $edit_url = route('prt.apps.page.edit', [$tags, $uuid_enc]);
                    return '
                        <div class="d-flex align-items-center">
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
                ->addColumn('views', function ($data) {
                    $views = Helper::toDot($data->views);
                    return '<div class="text-center"><span class="fw-bold text-gray-800">' . $views . '</span></div>';
                })
                ->addColumn('penulis', function ($data) {
                    $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                    return '<span class="text-gray-600 fw-semibold">' . $penulis . '</span>';
                })
                ->addColumn('publisher', function ($data) {
                    if ($data->status == "Published") {
                        $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                        return '<span class="text-success fw-semibold">' . $publisher . '</span>';
                    } else {
                        return '<span class="text-muted">-</span>';
                    }
                })
                ->addColumn('status', function ($data) {
                    $colors = [
                        'Draft'          => 'warning',
                        'Pending Review' => 'info',
                        'Published'      => 'success',
                        'Scheduled'      => 'primary',
                        'Archived'       => 'dark',
                    ];

                    $color = $colors[$data->status] ?? 'secondary';
                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $data->status . '</span>';
                })
                ->addColumn('tanggal', function ($data) {
                    $tanggal = Helper::TglSimple($data->tanggal);
                    return '<span class="text-gray-600 fw-semibold">' . $tanggal . '</span>';
                })
                ->addColumn('aksi', function ($data) use ($tags, $auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.page.edit', [$tags, $uuid_enc]);

                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                        $actions = '
                            <div class="d-flex justify-content-center">
                                <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="ki-outline ki-pencil fs-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-kt-page-table-filter="delete_row" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
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
                                    <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-kt-page-table-filter="delete_row" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
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
                ->escapeColumns([''])
                ->make(true);
        }

        return view('admin.cms.konten.internal.pages.index', compact(
            'tags',
            'kategori',
            'status'
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
        $title    = "Tambah Data Halaman " . $kategori;
        $submit   = "Simpan";
        return view('admin.cms.konten.internal.pages.create_edit', compact(
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
            "no_urut"    => "required|numeric|min:1",
            "judul"      => "required|string|max:300",
            "deskripsi"  => "required|string|max:160",
            "post"       => "required",
            "thumbnails" => "required|image|mimes:png,jpg,jpeg|max:2048",
            "tanggal"    => "required",
        ]);

        // Validasi status menggunakan helper
        if (! Helper::validateStatus($auth->role, $request->status)) {
            alert()->error('Error!', 'Status tidak valid untuk peran Anda!');
            return back()->withInput($request->all());
        }

        // slug
        $slug      = \Str::slug($request->judul);
        $cekslug   = PortalPage::whereSlug($slug)->count();
        $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;

        // value
        $uuid    = Str::uuid();
        $path    = "halaman/" . date('Y') . "/" . $uuid;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            "uuid"      => $uuid,
            "no_urut"   => $request->no_urut,
            "judul"     => $request->judul,
            "kategori"  => $kategori,
            "slug"      => $inputslug,
            "deskripsi" => $request->deskripsi,
            "tanggal"   => $tanggal,
            "status"    => $request->status,
        ];

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            $img = Helper::UpThumbnails($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return back();
            }
            $value_1['thumbnails'] = $img;
        }

        // post
        $imgpost         = Helper::processTinyMCEBase64Images($request, "post", $path);
        $value_1['post'] = $imgpost;

        // save
        $save_1 = PortalPage::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_page"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Halaman: " . $request->judul . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return \redirect()->route('prt.apps.page.index', [$tags]);
        } else {
            alert()->error('Error', "Gagal Menambahkan Data!");
            return \back()->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($tags, $uuid_enc)
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
        // uuid
        $uuid     = Helper::decode($uuid_enc);
        $data     = PortalPage::findOrFail($uuid);
        $kategori = Helper::decode($tags);
        $title    = "Edit Data Halaman " . $kategori;
        $submit   = "Simpan";
        return view('admin.cms.konten.internal.pages.create_edit', compact(
            'auth',
            'uuid_enc',
            'title',
            'submit',
            'data',
            'tags',
            'kategori'
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

        // uuid
        $uuid     = Helper::decode($uuid_enc);
        $data     = PortalPage::findOrFail($uuid);
        $kategori = Helper::decode($tags);

        // Validasi input
        $request->validate([
            "no_urut"   => "required|numeric|min:1",
            "judul"     => "required|string|max:300",
            "deskripsi" => "required|string|max:160",
            "post"      => "required",
            "tanggal"   => "required",
        ]);

        // Validasi status menggunakan helper
        if (! Helper::validateStatus($role, $request->status)) {
            alert()->error('Error!', 'Status tidak valid untuk peran Anda!');
            return back()->withInput($request->all());
        }

        // Validasi jika Penulis atau Kontributor
        if ($role == 'Penulis' || $role == 'Kontributor') {
            if ($data->status != "Draft") {
                alert()->error('Error!', 'Konten Sudah Tidak Bisa Diubah!');
                return back()->withInput($request->all());
            }
        }

        // slug
        if ($data->judul !== $request->judul) {
            $slug      = \Str::slug($request->judul);
            $cekslug   = PortalPage::where('uuid', '!=', $uuid)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value
        $thn     = date("Y", \strtotime($data->created_at));
        $path    = "halaman/" . $thn . "/" . $uuid;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            "no_urut"   => $request->no_urut,
            "judul"     => $request->judul,
            "kategori"  => $kategori,
            "slug"      => $inputslug,
            "deskripsi" => $request->deskripsi,
            "tanggal"   => $tanggal,
            "status"    => $request->status,
        ];

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            if (! empty($data->thumbnails) && Storage::disk('public')->exists($data->thumbnails)) {
                Storage::disk('public')->delete($data->thumbnails);
                $thumbnailPath = str_replace('.', '_thumbnail.', $data->thumbnails);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            $img = Helper::UpThumbnails($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return back();
            }
            $value_1['thumbnails'] = $img;
        }

        // post
        $imgpost         = Helper::processTinyMCEBase64Images($request, "post", $path);
        $value_1['post'] = $imgpost;

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_page"],
                "uuid"  => [$uuid],
                "value" => [$request->judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Halaman: " . $request->judul . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return \redirect()->route('prt.apps.page.index', [$tags]);
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return \back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $tags)
    {
        // auth
        $auth = Auth::user();

        // uuid
        $uuid = Helper::decode($request->uuid);

        // data
        $data     = PortalPage::findOrFail($uuid);
        $kategori = Helper::decode($tags);

        // Lakukan soft delete
        if ($data->status == "Draft" || $data->status == "Pending Review") {
            // drop path
            $tahun = Carbon::parse($data->tanggal)->year;
            $path  = "halaman/{$tahun}/{$data->uuid}";
            Helper::deleteFolderIfExists("directory", $path);
            $save_1 = $data->forceDelete();
        } else {
            // Update uuid_deleted dan status sebelum melakukan soft delete
            $data->update([
                'uuid_deleted' => $auth->uuid,
                'status'       => 'Deleted',
            ]);
            $save_1 = $data->delete();
        }
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_page"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Halaman " . $kategori . ": " . $data->judul . " - " . $uuid,
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
     * Bulk delete pages
     */
    public function bulkDestroy(Request $request, $tags)
    {
        try {
            // auth
            $auth     = Auth::user();
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
            foreach ($uuids as $uuid) {
                try {
                    // Find data
                    $data = PortalPage::find($uuid);

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid} tidak ditemukan";
                        continue;
                    }

                    // Check permission (if needed)
                    $role      = $auth->role;
                    $canDelete = ($role == "Super Admin" || $role == "Admin" || $role == "Editor");

                    // If not admin, check ownership
                    if (! $canDelete && isset($data->uuid_created)) {
                        $canDelete = ($data->uuid_created == $auth->uuid);
                    }

                    if (! $canDelete) {
                        $failedItems[] = "Tidak memiliki izin untuk menghapus: {$data->judul}";
                        continue;
                    }

                    // Delete the data
                    // Lakukan soft delete
                    if ($data->status == "Draft" || $data->status == "Pending Review") {
                        // drop path
                        $tahun = Carbon::parse($data->tanggal)->year;
                        $path  = "halaman/{$tahun}/{$data->uuid}";
                        Helper::deleteFolderIfExists("directory", $path);
                        $save_1 = $data->forceDelete();
                    } else {
                        // Update uuid_deleted dan status sebelum melakukan soft delete
                        $data->update([
                            'uuid_deleted' => $auth->uuid,
                            'status'       => 'Deleted',
                        ]);
                        $save_1 = $data->delete();
                    }

                    if ($save_1) {
                        $deletedCount++;

                        // Create log for each deleted item
                        $aktifitas = [
                            "tabel" => ["portal_page"],
                            "uuid"  => [$uuid],
                            "value" => [$data->toArray()],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Menghapus Data Halaman " . $kategori . " (Bulk): " . $data->judul . " - " . $uuid,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = "Gagal menghapus: {$data->judul}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid}: " . $e->getMessage();
                    continue;
                }
            }

            // Prepare response message
            $message = "Berhasil menghapus {$deletedCount} halaman";

            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Delete Halaman " . $kategori . " - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_page"],
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
}