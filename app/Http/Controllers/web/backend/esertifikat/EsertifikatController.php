<?php
namespace App\Http\Controllers\web\backend\esertifikat;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalEsertifikat;
use App\Models\PortalEsertifikatList;
use App\Models\PortalKategori;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EsertifikatController extends Controller
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
        if ($request->session()->exists('filter_status_esertifikat')) {
            $status = $request->session()->get('filter_status_esertifikat');
        } else {
            $request->session()->put('filter_status_esertifikat', 'Published');
            $status = "Published";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $status = $_GET['filter']['status'];
                $request->session()->put('filter_status_esertifikat', $status);
            }
            // cek role
            if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                if ($status == "Draft") {
                    $data = PortalEsertifikat::whereStatus($status)
                        ->whereUuidCreated($auth->uuid)
                        ->orderBy("tanggal", "DESC")
                        ->get();
                } else {
                    $data = PortalEsertifikat::whereStatus($status)
                        ->orderBy("tanggal", "DESC")
                        ->get();
                }
            } else {
                $data = PortalEsertifikat::whereStatus($status)
                    ->whereUuidCreated($auth->uuid)
                    ->orderBy("tanggal", "DESC")
                    ->get();
            }
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('judul', function ($data) {
                    $thumbnails = Helper::thumbnail($data->thumbnails);
                    $uuid_enc   = Helper::encode($data->uuid);
                    $edit       = route('prt.apps.esertifikat.edit', $uuid_enc);
                    $judulText  = $data->judul ? Str::limit($data->judul, 40, "...") : "[draft] - lanjutkan atau hapus";
                    $judul      = '
                    <div class="trans-list">
                        <img src="' . $thumbnails . '" alt="" class="rounded avatar avatar-xl me-3" draggable="false">
                        <h4><a class="text-underline" href="' . $edit . '">' . $judulText . '</a></h4>
                    </div>';
                    return $judul;
                })
                ->addColumn('jumlah', function ($data) {
                    if (count($data->RelEsertifikatList) > 0) {
                        $jumlah = Helper::toDot($data->GetJumlahEsertifikat());
                    } else {
                        $jumlah = 0;
                    }
                    return $jumlah;
                })
                ->addColumn('penulis', function ($data) {
                    $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                    return $penulis;
                })
                ->addColumn('publisher', function ($data) {
                    if ($data->status == "Published") {
                        $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                    } else {
                        $publisher = '';
                    }
                    return $publisher;
                })
                ->addColumn('tanggal', function ($data) {
                    $tanggal = Helper::TglSimple($data->created_at);
                    return $tanggal;
                })
                ->addColumn('aksi', function ($data) use ($role) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.esertifikat.edit', $uuid_enc);
                    $aksi     = '
                        <div class="d-flex">
                        <a href="' . $edit . '" class="btn btn-primary shadow btn-xs sharp me-1"><i class="fas fa-pencil-alt"></i></a>
                        <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp" data-delete="' . $uuid_enc . '"><i class="fa fa-trash"></i></a>
                        </div>
                    ';
                    return $aksi;
                })
                ->escapeColumns([''])
                ->make(true);
        }
        return view('pages.admin.portal_apps.esertifikat.index', compact(
            'status'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // auth
        $auth = Auth::user();
        // buat draft Esertifikat
        $uuid_esertifikat     = Str::uuid();
        $uuid_esertifikat_enc = Helper::encode($uuid_esertifikat);
        $value                = [
            "uuid"   => $uuid_esertifikat,
            "judul"  => "",
            "status" => "Draft",
        ];
        // save
        try {
            $save = PortalEsertifikat::create($value);
            if ($save) {
                // create log
                $aktifitas = [
                    "tabel" => ["portal_esertifikat"],
                    "uuid"  => [$uuid_esertifikat],
                    "value" => [$value],
                ];
                $log = [
                    "apps"      => "Portal Apps",
                    "subjek"    => "Menambahkan Data Draft Esertifikat" . $uuid_esertifikat,
                    "aktifitas" => $aktifitas,
                    "device"    => "web",
                ];
                Helper::addToLogAktifitas($request, $log);
                return redirect()->route('prt.apps.esertifikat.edit', [$uuid_esertifikat_enc]);
            }
            return abort(500, 'Gagal Membuat Esertifikat, Silahkan Coba Lagi!.');
        } catch (Exception $e) {
            return abort(500, 'Gagal Membuat Esertifikat, Silahkan Coba Lagi!.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        // get kategori
        $kategoriList = PortalKategori::whereType("Esertifikat")->whereStatus("1")->orderBy("nama")->get();
        // uuid
        $uuid   = Helper::decode($uuid_enc);
        $data   = PortalEsertifikat::findOrFail($uuid);
        $title  = "Edit Data Esertifikat";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.esertifikat.create_edit', compact(
            'auth',
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
        $role = $auth->role;

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "judul"      => "required|string|max:100",
            "deskripsi"  => "required|string|max:160",
            "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "tanggal"    => "required",
            "kategori"   => "required|string|max:100",
            "status"     => "required|string|max:100",
            "password"   => "sometimes|nullable|string|max:100",
        ]);

        // $uuid_esertifikat
        $uuid_esertifikat = Helper::decode($uuid_enc);
        $data             = PortalEsertifikat::findOrFail($uuid_esertifikat);
        if ($data->thumbnails === null) {
            $request->validate([
                "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            ]);
        } else {
            $request->validate([
                "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            ]);
        }

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
            $cekslug   = PortalEsertifikat::where('uuid', '!=', $uuid_esertifikat)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value
        $thn     = date("Y", \strtotime($data->created_at));
        $path    = "esertifikat/" . $thn . "/" . $uuid_esertifikat;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            "judul"     => $request->judul,
            "slug"      => $inputslug,
            "deskripsi" => $request->deskripsi,
            "tanggal"   => $tanggal,
            "kategori"  => $request->kategori,
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
                "tabel" => ["portal_esertifikat"],
                "uuid"  => [$uuid_esertifikat],
                "value" => [$request->judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Esertifikat UUID= " . $uuid_esertifikat,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.esertifikat.index');
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
        $auth = Auth::user();
        $tags = Helper::decode($request->tags);

        // Jika bulk delete
        if ($request->filled('uuids') && is_array($request->uuids)) {
            $uuidList = array_map(function ($u) {
                return Helper::decode($u);
            }, $request->uuids);

            switch ($tags) {
                case 'list_esertifikat':
                    return $this->bulkDelListEsertifikat($request, $auth, $uuidList);
            }
        }

        // Delete tunggal (default)
        $uuid = Helper::decode($request->uuid);
        switch ($tags) {
            case 'esertifikat':
                return $this->delEsertifikat($request, $auth, $uuid);
                break;
            case 'list_esertifikat':
                return $this->delListEsertifikat($request, $auth, $uuid);
                break;
            default:
                return response()->json(['status' => false, 'message' => 'Tag tidak valid.'], 422);
        }
    }
    private function delEsertifikat($request, $auth, $uuid)
    {
        // Dapatkan data dari database
        $data  = PortalEsertifikat::findOrFail($uuid);
        $judul = $data->judul;

        // Lakukan soft delete
        if ($data->status == "Draft" || $data->status == "Pending Review") {
            // drop path
            $tahun = Carbon::parse($data->tanggal)->year;
            $path  = "esertifikat/{$tahun}/{$data->uuid}";
            Helper::deleteFolderIfExists("directory", $path);
            $save_1 = $data->forceDelete();
        } else {
            // Update uuid_deleted dan status sebelum melakukan soft delete
            $save_1 = $data->delete();
        }

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_esertifikat"],
                "uuid"  => [$uuid],
                "value" => [$judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Esertifikat UUID= " . $uuid,
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
    private function delListEsertifikat($request, $auth, $uuid)
    {
        // Dapatkan data dari database
        $data  = PortalEsertifikatList::findOrFail($uuid);
        $judul = $data->judul;

        // drop path
        if (! empty($data->url) && Storage::disk('public')->exists($data->url)) {
            Storage::disk('public')->delete($data->url);
        }
        $save_1 = $data->forceDelete();
        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_esertifikat_list"],
                "uuid"  => [$uuid],
                "value" => [$judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data List Esertifikat UUID= " . $uuid,
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
    private function bulkDelListEsertifikat($request, $auth, array $uuids)
    {
        $deleted = 0;
        $failed  = 0;

        foreach ($uuids as $uuid) {
            try {
                $data  = PortalEsertifikatList::findOrFail($uuid);
                $judul = $data->judul;

                if (! empty($data->url) && Storage::disk('public')->exists($data->url)) {
                    Storage::disk('public')->delete($data->url);
                }

                $data->forceDelete();
                $deleted++;

                // Logging per item
                $aktifitas = [
                    "tabel" => ["portal_esertifikat_list"],
                    "uuid"  => [$uuid],
                    "value" => [$judul],
                ];
                Helper::addToLogAktifitas($request, [
                    "apps"      => "Portal Apps",
                    "subjek"    => "Menghapus Massal List Esertifikat UUID = $uuid",
                    "aktifitas" => $aktifitas,
                    "device"    => "web",
                ]);
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'status'  => true,
            'message' => "$deleted data berhasil dihapus, $failed gagal.",
        ]);
    }

    /**
     * UPLOAD FILE E-SERTIFIKAT
     */
    public function upload(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // Validasi
        $request->validate([
            'file'             => 'required|mimes:pdf|max:51200', // maksimal 50MB dan format pdf
            'uuid_esertifikat' => 'required',
        ]);

        // cek esertifikat
        $uuid_esertifikat   = Helper::decode($request->uuid_esertifikat);
        $portal_esertifikat = PortalEsertifikat::findOrFail($uuid_esertifikat);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if ($file->isValid()) {
                // Validasi tipe file
                $filename          = $file->getClientOriginalName();
                $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
                $allowedExtensions = ['pdf'];
                $fileExtension     = strtolower($file->getClientOriginalExtension());

                if (! in_array($fileExtension, $allowedExtensions)) {
                    return "0"; // File tidak diizinkan
                }
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $fileSize     = $file->getSize();

                // Ekstrak nama dan instansi dari nama file
                $nameParts = explode('-', $originalName);
                if (count($nameParts) < 2) {
                    // Gagal
                    $msg      = "Format nama file salah. Gunakan format nama_lengkap-instansi.pdf";
                    $response = [
                        "status"  => false,
                        "message" => $msg,
                    ];
                    return response()->json($response, 400);
                }

                // Konversi nama dan instansi menjadi ucwords setelah mengganti underscore dengan spasi
                // $nama_lengkap = ucwords(str_replace('_', ' ', $nameParts[0]));
                // $instansi = ucwords(str_replace('_', ' ', $nameParts[1]));
                $nama_lengkap = $nameParts[0];
                $instansi     = $nameParts[1];

                // cek data
                $cekFileEsertifikat = PortalEsertifikatList::where("nama_lengkap", $nama_lengkap)
                    ->where("instansi", $instansi)
                    ->where("uuid_esertifikat", $uuid_esertifikat)
                    ->first();
                if ($cekFileEsertifikat) {
                    // Gagal
                    $msg      = "Gagal Upload E-Sertifikat " . $name . ", File Sudah Ada!";
                    $response = [
                        "status"  => false,
                        "message" => $msg,
                    ];
                    return response()->json($response, 400);
                }

                // Generate UUID dan simpan file
                $uuid      = Str::uuid();
                $tahun     = date('Y', strtotime($portal_esertifikat->tanggal));
                $fileName  = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
                $path      = "esertifikat/{$tahun}/{$uuid_esertifikat}";
                $file_save = $path . "/" . $fileName;
                if (! is_dir(storage_path('app/public/' . $path))) {
                    Storage::disk('public')->makeDirectory($path);
                }
                Storage::disk('public')->putFileAs($path, $file, $fileName);

                // Value untuk disimpan ke database
                $value_1 = [
                    'uuid'             => $uuid,
                    'uuid_esertifikat' => $uuid_esertifikat,
                    'nama_lengkap'     => $nama_lengkap,
                    'instansi'         => $instansi,
                    'url'              => $file_save,
                    'tipe'             => $fileExtension,
                    'size'             => $fileSize,
                ];

                // Simpan ke database
                $save_1 = PortalEsertifikatList::create($value_1);
                if ($save_1) {
                    // Buat log aktivitas
                    $aktifitas = [
                        "tabel" => ["portal_esertifikat_list"],
                        "uuid"  => [$uuid],
                        "value" => [$value_1],
                    ];
                    $log = [
                        "apps"      => "Portal Apps",
                        "subjek"    => "Berhasil Upload File E-Sertifikat: " . $name . " - " . $uuid,
                        "aktifitas" => $aktifitas,
                        "device"    => "web",
                    ];
                    Helper::addToLogAktifitas($request, $log);

                    // Alert sukses
                    $msg      = "Berhasil Upload E-Sertifikat " . $name . "!";
                    $response = [
                        "status"  => true,
                        "message" => $msg,
                    ];
                    return response()->json($response, 200);
                } else {
                    // Gagal
                    $msg      = "Gagal Upload E-Sertifikat " . $name . "!";
                    $response = [
                        "status"  => false,
                        "message" => $msg,
                    ];
                    return response()->json($response, 400);
                }
            } else {
                // Jika tidak ada file
                return response()->json(['status' => false, 'message' => $name . ' file tidak falid.'], 400);
            }
        }

        // Jika tidak ada file
        return response()->json(['status' => false, 'message' => $name . ' file tidak ditemukan.'], 400);
    }
}
