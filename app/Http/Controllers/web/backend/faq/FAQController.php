<?php
namespace App\Http\Controllers\web\backend\faq;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalFAQ;
use App\Models\PortalFAQList;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FAQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;

        if ($request->ajax()) {
            // cek role
            if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                $data = PortalFAQ::orderBy("created_at", "DESC")->get();
            } else {
                $data = PortalFAQ::whereUuidCreated($auth->uuid)
                    ->orderBy("created_at", "DESC")
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
                    $edit_url = route('prt.apps.faq.edit', $uuid_enc);

                                                                                   // Thumbnail processing
                    $thumbnail_url = asset('assets/media/misc/question-mark.jpg'); // default image
                    if (! empty($data->thumbnails)) {
                        $thumbnail_url = Helper::urlImg($data->thumbnails);
                    }

                    // Title processing
                    if (empty($data->judul)) {
                        $judul_display = '[Draft] - Lanjutkan atau hapus';
                        $class         = 'text-muted fst-italic';
                    } else {
                        $judul_display = Str::limit($data->judul, 50, "...");
                        $class         = 'text-gray-800 text-hover-primary fw-bold';
                    }

                    // Description processing
                    $deskripsi_display = ! empty($data->deskripsi)
                    ? Str::limit($data->deskripsi, 80, "...")
                    : 'Tidak ada deskripsi';

                    return '
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-3">
                            <img src="' . $thumbnail_url . '" class="h-50px w-50px" alt="FAQ Thumbnail" style="object-fit: cover; border-radius: 8px;" />
                        </div>
                        <div class="d-flex flex-column">
                            <a href="' . $edit_url . '" class="' . $class . ' mb-1 fs-6">' . $judul_display . '</a>
                            <span class="text-muted fw-semibold d-block fs-7">' . $deskripsi_display . '</span>
                        </div>
                    </div>
                ';
                })
                ->addColumn('jumlah', function ($data) {
                    $jumlah = $data->GetJumlahFAQ();
                    if ($jumlah > 0) {
                        $color = "primary";
                    } else {
                        $color = "secondary";
                    }
                    return '
                        <div class="d-flex align-items-center justify-content-center">
                            <span class="badge badge-light-' . $color . ' fs-base fs-3">' . $jumlah . '</span>
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
                    if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
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
                ->addColumn('aksi', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.faq.edit', $uuid_enc);

                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                        $aksi = '
                        <div class="d-flex justify-content-center">
                            <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit FAQ">
                                <i class="ki-outline ki-pencil fs-2"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus FAQ">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </a>
                        </div>
                    ';
                    } else {
                        if (isset($data->uuid_created) && $data->uuid_created == $auth->uuid) {
                            $aksi = '
                            <div class="d-flex justify-content-center">
                                <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit FAQ">
                                    <i class="ki-outline ki-pencil fs-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus FAQ">
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

        return view('admin.cms.konten.text.faq.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // auth
        $auth   = Auth::user();
        $title  = "Tambah Data FAQ";
        $submit = "Simpan";

        return view('admin.cms.konten.text.faq.create_edit', compact(
            'auth',
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

        // Validasi input
        $request->validate([
            "judul"         => "required|string|max:300",
            "deskripsi"     => "required|string|max:160",
            "thumbnails"    => "required|image|mimes:png,jpg,jpeg|max:2048",
            "tanggal"       => "required|date",
            'status'        => 'required|in:0,1',
            'pertanyaan.*'  => 'required|string|max:150',
            'status_list.*' => 'required|in:0,1',
            "jawaban.*"     => "required|string",
        ], [
            'judul.required'        => 'Judul FAQ wajib diisi',
            'judul.max'             => 'Judul FAQ maksimal 300 karakter',
            'deskripsi.required'    => 'Deskripsi wajib diisi',
            'deskripsi.max'         => 'Deskripsi maksimal 160 karakter',
            'thumbnails.required'   => 'Thumbnail wajib diupload',
            'thumbnails.image'      => 'Thumbnail harus berupa gambar',
            'thumbnails.mimes'      => 'Format thumbnail harus PNG, JPG, atau JPEG',
            'thumbnails.max'        => 'Ukuran thumbnail maksimal 2MB',
            'pertanyaan.*.required' => 'Pertanyaan FAQ wajib diisi',
            'pertanyaan.*.max'      => 'Pertanyaan FAQ maksimal 150 karakter',
            'jawaban.*.required'    => 'Jawaban FAQ wajib diisi',
        ]);

        // Generate UUID dan slug
        $uuid = Str::uuid();
        $slug = Str::slug($request->judul);

        // Cek slug duplikat
        $cekslug   = PortalFAQ::whereSlug($slug)->count();
        $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;

        // Path untuk file
        $tahun = date("Y");
        $path  = "faq/" . $tahun . "/" . $uuid;

        // Upload thumbnail
        $thumbnails = null;
        if ($request->hasFile('thumbnails')) {
            $thumbnails = Helper::UpThumbnails($request, "thumbnails", $path);
            if ($thumbnails == "0") {
                alert()->error('Error!', 'Gagal menyimpan thumbnail, format tidak sesuai!');
                return back()->withInput();
            }
        }

        // Prepare main FAQ data
        $tanggal = Carbon::parse($request->tanggal);
        $value_1 = [
            "uuid"       => $uuid,
            "judul"      => $request->judul,
            "slug"       => $inputslug,
            "deskripsi"  => $request->deskripsi,
            "thumbnails" => $thumbnails,
            "tanggal"    => $tanggal,
            "status"     => $request->status,
        ];

        // Save main FAQ
        $save_1 = PortalFAQ::create($value_1);

        if ($save_1) {
            // Save FAQ List items
            if ($request->has('pertanyaan')) {
                foreach ($request->pertanyaan as $index => $pertanyaan) {
                    if (! empty($pertanyaan) && ! empty($request->jawaban[$index])) {
                        PortalFAQList::create([
                            'uuid'            => Str::uuid(),
                            'uuid_portal_faq' => $uuid,
                            'pertanyaan'      => $pertanyaan,
                            'jawaban'         => Helper::UpdateImgIndexing($request->jawaban[$index], $path),
                            'status'          => $request->status_list[$index] ?? '1',
                        ]);
                    }
                }
            }

            // Create activity log
            $aktifitas = [
                "tabel" => ["portal_faq"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data FAQ: " . $request->judul . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);

            alert()->success('Success', "Berhasil menambahkan FAQ!");
            return redirect()->route('prt.apps.faq.index');
        } else {
            alert()->error('Error', "Gagal menambahkan FAQ!");
            return back()->withInput();
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

        // Decode UUID dan ambil data
        $uuid     = Helper::decode($uuid_enc);
        $data     = PortalFAQ::findOrFail($uuid);
        $list_faq = $data->RelFAQList;

        $title  = "Edit Data FAQ";
        $submit = "Simpan Perubahan";

        return view('admin.cms.konten.text.faq.create_edit', compact(
            'auth',
            'uuid_enc',
            'title',
            'submit',
            'data',
            'list_faq'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        // auth
        $auth = Auth::user();

        // Decode UUID dan ambil data existing
        $uuid_faq = Helper::decode($uuid_enc);
        $data     = PortalFAQ::findOrFail($uuid_faq);

        // Validasi input
        $thumbnailsRules = empty($data->thumbnails) ? 'required|' : 'sometimes|';
        $request->validate([
            "judul"         => "required|string|max:300",
            "deskripsi"     => "required|string|max:160",
            "thumbnails"    => $thumbnailsRules . "image|mimes:png,jpg,jpeg|max:2048",
            "tanggal"       => "required|date",
            'status'        => 'required|in:0,1',
            'pertanyaan.*'  => 'required|string|max:150',
            'status_list.*' => 'required|in:0,1',
            "jawaban.*"     => "required|string",
        ], [
            'judul.required'        => 'Judul FAQ wajib diisi',
            'judul.max'             => 'Judul FAQ maksimal 300 karakter',
            'deskripsi.required'    => 'Deskripsi wajib diisi',
            'deskripsi.max'         => 'Deskripsi maksimal 160 karakter',
            'thumbnails.required'   => 'Thumbnail wajib diupload',
            'thumbnails.image'      => 'Thumbnail harus berupa gambar',
            'thumbnails.mimes'      => 'Format thumbnail harus PNG, JPG, atau JPEG',
            'thumbnails.max'        => 'Ukuran thumbnail maksimal 2MB',
            'pertanyaan.*.required' => 'Pertanyaan FAQ wajib diisi',
            'pertanyaan.*.max'      => 'Pertanyaan FAQ maksimal 150 karakter',
            'jawaban.*.required'    => 'Jawaban FAQ wajib diisi',
        ]);

        // Generate slug jika judul berubah
        if ($data->judul !== $request->judul) {
            $slug      = Str::slug($request->judul);
            $cekslug   = PortalFAQ::where('uuid', '!=', $uuid_faq)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // Path untuk file
        $tahun = date("Y", strtotime($data->created_at));
        $path  = "faq/" . $tahun . "/" . $uuid_faq;

        // Prepare update data
        $tanggal = Carbon::parse($request->tanggal);
        $value_1 = [
            'judul'     => $request->judul,
            'slug'      => $inputslug,
            "deskripsi" => $request->deskripsi,
            "tanggal"   => $tanggal,
            'status'    => $request->status,
        ];

        // Handle thumbnail upload
        if ($request->hasFile('thumbnails')) {
            // Delete old thumbnail if exists
            if (! empty($data->thumbnails) && Storage::disk('public')->exists($data->thumbnails)) {
                Storage::disk('public')->delete($data->thumbnails);

                // Delete thumbnail version too
                $thumbnailPath = str_replace('.', '_thumbnail.', $data->thumbnails);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            // Upload new thumbnail
            $thumbnails = Helper::UpThumbnails($request, "thumbnails", $path);
            if ($thumbnails == "0") {
                alert()->error('Error!', 'Gagal menyimpan thumbnail, format tidak sesuai!');
                return back()->withInput();
            }
            $value_1['thumbnails'] = $thumbnails;
        }

        // Update main FAQ data
        $save_1 = $data->update($value_1);

        if ($save_1) {
            // Delete existing FAQ list items and recreate
            PortalFAQList::where("uuid_portal_faq", $uuid_faq)->forceDelete();

            // Save new FAQ List items
            if ($request->has('pertanyaan')) {
                foreach ($request->pertanyaan as $index => $pertanyaan) {
                    if (! empty($pertanyaan) && ! empty($request->jawaban[$index])) {
                        PortalFAQList::create([
                            'uuid'            => Str::uuid(),
                            'uuid_portal_faq' => $uuid_faq,
                            'pertanyaan'      => $pertanyaan,
                            'jawaban'         => Helper::UpdateImgIndexing($request->jawaban[$index], $path),
                            'status'          => $request->status_list[$index] ?? '1',
                        ]);
                    }
                }
            }

            // Create activity log
            $aktifitas = [
                "tabel" => ["portal_faq"],
                "uuid"  => [$uuid_faq],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data FAQ: " . $request->judul . " - " . $uuid_faq,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);

            alert()->success('Success', "Berhasil mengubah FAQ!");
            return redirect()->route('prt.apps.faq.index');
        } else {
            alert()->error('Error', "Gagal mengubah FAQ!");
            return back()->withInput();
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
        $data = PortalFAQ::findOrFail($uuid);

        // save
        // drop path
        $tahun = Carbon::parse($data->tanggal)->year;
        $path  = "faq/{$tahun}/{$data->uuid}";
        Helper::deleteFolderIfExists("directory", $path);
        $save_1 = $data->forceDelete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_faq"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data FAQ: " . $data->judul . " - " . $uuid,
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
        $data = PortalFAQ::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_faq"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status FAQ: " . $data->judul . " - " . $uuid,
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
     * Bulk delete FAQ
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
                    $data = PortalFAQ::find($uuid_enc);

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid_enc} tidak ditemukan";
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
                        $judul         = $data->judul ?: '[Draft FAQ]';
                        $failedItems[] = "Tidak memiliki izin untuk menghapus: {$judul}";
                        continue;
                    }

                    // Delete the data
                    // drop path
                    $tahun = Carbon::parse($data->tanggal)->year;
                    $path  = "faq/{$tahun}/{$data->uuid}";
                    Helper::deleteFolderIfExists("directory", $path);
                    $save_1 = $data->forceDelete();
                    if ($save_1) {
                        $deletedCount++;

                        // Create log for each deleted item
                        $aktifitas = [
                            "tabel" => ["portal_faq"],
                            "uuid"  => [$uuid_enc],
                            "value" => [$data->toArray()],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Menghapus Data FAQ (Bulk): " . ($data->judul ?: '[Draft FAQ]') . " - " . $uuid_enc,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $judul         = $data->judul ?: '[Draft FAQ]';
                        $failedItems[] = "Gagal menghapus: {$judul}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid_enc}: " . $e->getMessage();
                    continue;
                }
            }

            // Prepare response message
            $message = "Berhasil menghapus {$deletedCount} FAQ";

            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            // Create summary log
            $summaryLog = [
                "apps"      => "Portal Apps",
                "subjek"    => "Bulk Delete FAQ - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                "aktifitas" => [
                    "tabel"         => ["portal_faq"],
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
                    $data = PortalFAQ::find($uuid_enc);

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid_enc} tidak ditemukan";
                        continue;
                    }

                    // Check permission
                    $role      = $auth->role;
                    $canUpdate = ($role == "Super Admin" || $role == "Admin" || $role == "Editor");

                    if (! $canUpdate && isset($data->uuid_created)) {
                        $canUpdate = ($data->uuid_created == $auth->uuid);
                    }

                    if (! $canUpdate) {
                        $judul         = $data->judul ?: '[Draft FAQ]';
                        $failedItems[] = "Tidak memiliki izin untuk mengubah: {$judul}";
                        continue;
                    }

                    // Update status
                    if ($data->update(['status' => $newStatus])) {
                        $updatedCount++;

                        // Create log
                        $aktifitas = [
                            "tabel" => ["portal_faq"],
                            "uuid"  => [$uuid_enc],
                            "value" => [['status' => $newStatus]],
                        ];
                        $log = [
                            "apps"      => "Portal Apps",
                            "subjek"    => "Mengubah Status FAQ: " . ($data->judul ?: '[Draft FAQ]') . " - " . $uuid_enc,
                            "aktifitas" => $aktifitas,
                            "device"    => "web",
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $judul         = $data->judul ?: '[Draft FAQ]';
                        $failedItems[] = "Gagal mengubah status: {$judul}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid_enc}: " . $e->getMessage();
                    continue;
                }
            }

            $statusText = $newStatus == '1' ? 'mengaktifkan' : 'menonaktifkan';
            $message    = "Berhasil {$statusText} {$updatedCount} FAQ";

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