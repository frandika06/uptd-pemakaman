<?php
namespace App\Http\Controllers\web\backend\unduhan;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalKategori;
use App\Models\PortalUnduhan;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UnduhanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;

        // cek filter
        if ($request->session()->exists('filter_status_unduhan')) {
            $status = $request->session()->get('filter_status_unduhan');
        } else {
            $request->session()->put('filter_status_unduhan', 'Published');
            $status = "Published";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $status = $_GET['filter']['status'];
                $request->session()->put('filter_status_unduhan', $status);
            }

            // cek role
            if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                if ($status == "Draft") {
                    $data = PortalUnduhan::whereStatus($status)
                        ->whereUuidCreated($auth->uuid)
                        ->orderBy("tanggal", "DESC")
                        ->get();
                } else {
                    $data = PortalUnduhan::whereStatus($status)
                        ->orderBy("tanggal", "DESC")
                        ->get();
                }
            } else {
                $data = PortalUnduhan::whereStatus($status)
                    ->whereUuidCreated($auth->uuid)
                    ->orderBy("tanggal", "DESC")
                    ->get();
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('checkbox', function ($data) {
                    return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input row-checkbox" type="checkbox" value="' . $data->uuid . '" /></div>';
                })
                ->addColumn('judul', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.unduhan.edit', $uuid_enc);

                                                                                   // Generate thumbnail URL
                    $thumbnail_url = asset('be/media/misc/image-placeholder.svg'); // Default placeholder
                    if (! empty($data->thumbnails)) {
                        $thumbnail_url = Helper::thumbnailUnduhan($data->thumbnails, $data->tipe);
                    }

                    return '
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-50px me-5">
                                <img src="' . $thumbnail_url . '" class="h-75 align-self-end" alt="Thumbnail" style="object-fit: cover; border-radius: 8px;" />
                            </div>
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . Str::limit($data->judul, 60, "...") . '</a>
                                <span class="text-muted fw-semibold d-block fs-7">' . $data->slug . '</span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('kategori', function ($data) {
                    $categories = explode(',', $data->kategori);
                    $badges     = '';
                    foreach ($categories as $category) {
                        $badges .= '<span class="badge badge-light-primary fw-bold fs-7 px-3 py-2 me-1">' . trim($category) . '</span>';
                    }
                    return $badges;
                })
                ->addColumn('views', function ($data) {
                    $views = Helper::toDot($data->views);
                    return '<div class="text-center"><span class="fw-bold text-gray-800">' . $views . '</span></div>';
                })
                ->addColumn('size', function ($data) {
                    $size = isset($data->size) ? Helper::SizeDisk($data->size) : '-';
                    return '<div class="text-center"><span class="fw-bold text-gray-800">' . $size . '</span></div>';
                })
                ->addColumn('downloads', function ($data) {
                    $downloads = Helper::toDot($data->downloads);
                    return '<div class="text-center"><span class="fw-bold text-gray-800">' . $downloads . '</span></div>';
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
                ->addColumn('aksi', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.unduhan.edit', $uuid_enc);

                    // role
                    $role = $auth->role;
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
                ->escapeColumns([''])
                ->make(true);
        }

        return view('admin.cms.konten.media.unduhan.index', compact(
            'status'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // auth
        $auth = Auth::user();
        // get kategori
        $kategoriList = PortalKategori::whereType("Unduhan")->whereStatus("1")->orderBy("nama")->get();
        $title        = "Tambah Data Unduhan";
        $submit       = "Simpan";
        return view('admin.cms.konten.media.unduhan.create_edit', compact(
            'title',
            'submit',
            'auth',
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

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "judul"      => "required|string|max:300",
            "deskripsi"  => "required|string|max:160",
            "post"       => "sometimes|nullable",
            "kategori"   => "required",
            "thumbnails" => "required|image|mimes:png,jpg,jpeg|max:2048",
            "sumber"     => "required|string|max:100",
            "tanggal"    => "required",
            "password"   => "sometimes|nullable|string|max:100",
        ]);

        // Validasi status menggunakan helper
        if (! Helper::validateStatus($auth->role, $request->status)) {
            alert()->error('Error!', 'Status tidak valid untuk peran Anda!');
            return back()->withInput($request->all());
        }

        // slug
        $slug      = \Str::slug($request->judul);
        $cekslug   = PortalUnduhan::whereSlug($slug)->count();
        $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;

        // value
        $uuid    = Str::uuid();
        $path    = "unduhan/" . date('Y') . "/" . $uuid;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            "uuid"     => $uuid,
            "sumber"   => $request->sumber,
            "judul"    => $request->judul,
            "slug"     => $inputslug,
            "tanggal"  => $tanggal,
            "kategori" => $request->kategori,
            "status"   => $request->status,
        ];

        // cek sumber Unduhan
        $sumber = $request->sumber;
        if ($sumber == "Link") {
            // SUMBER = Link
            $request->validate([
                "url" => "required|url",
            ]);
            $value_1['url'] = $request->url;
        } else {
            // SUMBER = UPLOAD
            $request->validate([
                'file_unduhan' => 'required|file|mimes:jpg,jpeg,png,gif,bmp,svg,tiff,webp,' .
                'doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,rtf,pdf,txt,csv,xml,json,md,' .
                'mp3,wav,ogg,m4a,flac,aac,' .
                'mp4,mkv,avi,mov,wmv,flv,webm,3gp,mpeg,' .
                'zip,rar,tar,gz,7z,bz2,xz,iso|max:204800', // 200 MB
            ]);
            // file_unduhan
            if ($request->hasFile('file_unduhan')) {
                $img = Helper::UpFileUnduhan($request, "file_unduhan", $path);
                if ($img == "0") {
                    alert()->error('Error!', 'Gagal Menyimpan Data, File Unduhan Tidak Sesuai Format!');
                    return back();
                }
                $value_1['url']  = $img['url'];
                $value_1['tipe'] = $img['tipe'];
                $value_1['size'] = $img['size'];
            }
        }

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            $img = Helper::UpInfografis($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return back();
            }
            // $value_1['thumbnails'] = $img;
            $value_1['thumbnails'] = $img['url'];
        }

        // post
        if ($request->filled('post')) {
            $imgpost         = Helper::UpImgPostWithCompress($request, "post", $path);
            $value_1['post'] = $imgpost;
        }

        // deskripsi
        $value_1['deskripsi'] = $request->filled('deskripsi') ? $request->deskripsi : Helper::generateDescription($imgpost);

        // password
        if ($request->filled('password')) {
            $value_1['tipe_publikasi'] = "Private";
            $value_1['password']       = $request->password;
        } else {
            $value_1['tipe_publikasi'] = "Public";
            $value_1['password']       = null;
        }

        // save
        $save_1 = PortalUnduhan::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_unduhan"],
                "uuid"  => [$uuid],
                "value" => [$request->judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Unduhan UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return redirect()->route('prt.apps.unduhan.index');
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
    public function edit($uuid_enc)
    {
        // auth
        $auth = Auth::user();
        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalUnduhan::findOrFail($uuid);
        // get kategori
        $kategoriList = PortalKategori::whereType("Unduhan")->whereStatus("1")->orderBy("nama")->get();
        $title        = "Edit Data Unduhan";
        $submit       = "Simpan";
        return view('admin.cms.konten.media.unduhan.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'auth',
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
        $role = $auth->role;

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "judul"      => "required|string|max:300",
            "deskripsi"  => "required|string|max:160",
            "post"       => "sometimes|nullable",
            "kategori"   => "required",
            "thumbnails" => "sometimes|nullable|mimes:png,jpg,jpeg|max:2048",
            "sumber"     => "required|string|max:100",
            "tanggal"    => "required",
            "password"   => "sometimes|nullable|string|max:100",
        ]);

        // $uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalUnduhan::findOrFail($uuid);

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
            $cekslug   = PortalUnduhan::where('uuid', '!=', $uuid)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value
        $thn     = date("Y", \strtotime($data->created_at));
        $path    = "unduhan/" . $thn . "/" . $uuid;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            "judul"    => $request->judul,
            "slug"     => $inputslug,
            "tanggal"  => $tanggal,
            "kategori" => $request->kategori,
            "status"   => $request->status,
        ];

        // cek sumber Unduhan
        $sumber = $data->sumber;
        if ($sumber == "Link") {
            // SUMBER = Link
            $request->validate([
                "url" => "required|url",
            ]);
            $value_1['url'] = $request->url;
        } else {
            // SUMBER = UPLOAD
            $request->validate([
                'file_unduhan' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,bmp,svg,tiff,webp,' .
                'doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,rtf,pdf,txt,csv,xml,json,md,' .
                'mp3,wav,ogg,m4a,flac,aac,' .
                'mp4,mkv,avi,mov,wmv,flv,webm,3gp,mpeg,' .
                'zip,rar,tar,gz,7z,bz2,xz,iso|max:204800', // 200 MB
            ]);
            // file_unduhan
            if ($request->hasFile('file_unduhan')) {
                if (! empty($data->url) && Storage::disk('public')->exists($data->url)) {
                    Storage::disk('public')->delete($data->url);
                }
                $img = Helper::UpFileUnduhan($request, "file_unduhan", $path);
                if ($img == "0") {
                    alert()->error('Error!', 'Gagal Menyimpan Data, File Unduhan Tidak Sesuai Format!');
                    return back();
                }
                $value_1['url']  = $img['url'];
                $value_1['tipe'] = $img['tipe'];
                $value_1['size'] = $img['size'];
            }
        }

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            if (! empty($data->thumbnails) && Storage::disk('public')->exists($data->thumbnails)) {
                Storage::disk('public')->delete($data->thumbnails);
                $thumbnailPath = str_replace('.', '_thumbnail.', $data->thumbnails);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }
            $img = Helper::UpInfografis($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return back();
            }
            // $value_1['thumbnails'] = $img;
            $value_1['thumbnails'] = $img['url'];
        }

        // post
        if ($request->filled('post')) {
            $imgpost         = Helper::UpdateImgPostWithCompress($request, "post", $path);
            $value_1['post'] = $imgpost;
        }

        // deskripsi
        $value_1['deskripsi'] = $request->filled('deskripsi') ? $request->deskripsi : Helper::generateDescription($imgpost);

        // password
        if ($request->filled('password')) {
            $value_1['tipe_publikasi'] = "Private";
            $value_1['password']       = $request->password;
        } else {
            $value_1['tipe_publikasi'] = "Public";
            $value_1['password']       = null;
        }

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_unduhan"],
                "uuid"  => [$uuid],
                "value" => [$request->judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Unduhan UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.unduhan.index');
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Auth user
        $auth = Auth::user();

        // Decode UUID dari request
        $uuid = Helper::decode($request->uuid);

        // Dapatkan data dari database
        $data  = PortalUnduhan::findOrFail($uuid);
        $judul = $data->judul;

        // Lakukan soft delete
        if ($data->status == "Draft" || $data->status == "Pending Review") {
            // drop path
            $tahun = Carbon::parse($data->tanggal)->year;
            $path  = "unduhan/{$tahun}/{$data->uuid}";
            Helper::deleteFolderIfExists("directory", $path);
            $save_1 = $data->forceDelete();
        } else {
            $save_1 = $data->delete();
        }

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_unduhan"],
                "uuid"  => [$uuid],
                "value" => [$judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Unduhan UUID= " . $uuid,
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
     * Bulk delete unduhan
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
            foreach ($uuids as $uuid) {
                try {
                    // Find data
                    $data = PortalUnduhan::find($uuid);

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid} tidak ditemukan";
                        continue;
                    }

                    // Check permission
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
                    if ($data->status == "Draft" || $data->status == "Pending Review") {
                        // drop path
                        $tahun = Carbon::parse($data->tanggal)->year;
                        $path  = "unduhan/{$tahun}/{$data->uuid}";
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
                            "tabel" => ["portal_unduhan"],
                            "uuid"  => [$uuid],
                            "value" => [$data->toArray()],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Menghapus Data Unduhan (Bulk): " . $data->judul . " - " . $uuid,
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
            $message = "Berhasil menghapus {$deletedCount} unduhan";

            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Delete Unduhan - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_unduhan"],
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
            Log::error('Bulk Delete Unduhan Error', [
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