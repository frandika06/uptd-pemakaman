<?php
namespace App\Http\Controllers\web\backend\ebook;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalEbook;
use App\Models\PortalKategori;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EbookController extends Controller
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
        if ($request->session()->exists('filter_status_ebook')) {
            $status = $request->session()->get('filter_status_ebook');
        } else {
            $request->session()->put('filter_status_ebook', 'Published');
            $status = "Published";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $status = $_GET['filter']['status'];
                $request->session()->put('filter_status_ebook', $status);
            } else {
                $status = $request->session()->get('filter_status_ebook');
            }
            // cek role
            if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                if ($status == "Draft") {
                    $data = PortalEbook::whereStatus($status)
                        ->whereUuidCreated($auth->uuid)
                        ->orderBy("tanggal", "DESC")
                        ->get();
                } else {
                    $data = PortalEbook::whereStatus($status)
                        ->orderBy("tanggal", "DESC")
                        ->get();
                }
            } else {
                $data = PortalEbook::whereStatus($status)
                    ->whereUuidCreated($auth->uuid)
                    ->orderBy("tanggal", "DESC")
                    ->get();
            }
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('judul', function ($data) {
                    $thumbnails = Helper::thumbnail($data->thumbnails, $data->tipe);
                    $uuid_enc   = Helper::encode($data->uuid);
                    $edit       = route('prt.apps.ebook.edit', $uuid_enc);
                    $judul      = '
                    <div class="trans-list">
                        <img src="' . $thumbnails . '" alt="" class="rounded avatar avatar-xl me-3" draggable="false">
                        <h4><a class="text-underline" href="' . $edit . '">' . Str::limit($data->judul, 40, "...") . '</a></h4>
                    </div>';
                    return $judul;
                })
                ->addColumn('views', function ($data) {
                    $views = Helper::toDot($data->views);
                    return $views;
                })
                ->addColumn('size', function ($data) {
                    $size = isset($data->size) ? Helper::SizeDisk($data->size) : '-';
                    return $size;
                })
                ->addColumn('downloads', function ($data) {
                    $downloads = Helper::toDot($data->downloads);
                    return $downloads;
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
                    $tanggal = Helper::TglSimple($data->tanggal);
                    return $tanggal;
                })
                ->addColumn('aksi', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.ebook.edit', [$uuid_enc]);
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
        return view('pages.admin.portal_apps.ebook.index', compact(
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
        $kategoriList = PortalKategori::whereType("Ebook")->whereStatus("1")->orderBy("nama")->get();
        $title        = "Tambah Data Ebook";
        $submit       = "Simpan";
        return view('pages.admin.portal_apps.ebook.create_edit', compact(
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
            "tanggal"    => "required",
            'file_ebook' => 'required|file|mimes:pdf|max:204800', // 200 MB
        ]);

        // Validasi status menggunakan helper
        if (! Helper::validateStatus($auth->role, $request->status)) {
            alert()->error('Error!', 'Status tidak valid untuk peran Anda!');
            return back()->withInput($request->all());
        }

        // slug
        $slug      = \Str::slug($request->judul);
        $cekslug   = PortalEbook::whereSlug($slug)->count();
        $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;

        // value
        $uuid    = Str::uuid();
        $path    = "ebook/" . date('Y') . "/" . $uuid;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            "uuid"      => $uuid,
            "judul"     => $request->judul,
            "slug"      => $inputslug,
            "tanggal"   => $tanggal,
            "kategori"  => $request->kategori,
            "deskripsi" => $request->deskripsi,
            "status"    => $request->status,
        ];

        // file_ebook
        if ($request->hasFile('file_ebook')) {
            $img = Helper::UpFilePdf($request, "file_ebook", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, File Ebook Tidak Sesuai Format!');
                return back();
            }
            $value_1['url']  = $img['url'];
            $value_1['tipe'] = $img['tipe'];
            $value_1['size'] = $img['size'];
        }

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            $img = Helper::UpThumbnailsEbook($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return back();
            }
            $value_1['thumbnails'] = $img;
        }

        // save
        $save_1 = PortalEbook::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_ebook"],
                "uuid"  => [$uuid],
                "value" => [$request->judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Ebook UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return redirect()->route('prt.apps.ebook.index');
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
        $data = PortalEbook::findOrFail($uuid);
        // get kategori
        $kategoriList = PortalKategori::whereType("Ebook")->whereStatus("1")->orderBy("nama")->get();
        $title        = "Edit Data Ebook";
        $submit       = "Simpan";
        return view('pages.admin.portal_apps.ebook.create_edit', compact(
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
            "tanggal"    => "required",
            'file_ebook' => 'sometimes|nullable|file|mimes:pdf|max:204800', // 200 MB
        ]);

        // $uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalEbook::findOrFail($uuid);

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
            $cekslug   = PortalEbook::where('uuid', '!=', $uuid)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value
        $thn     = date("Y", \strtotime($data->created_at));
        $path    = "ebook/" . $thn . "/" . $uuid;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            "judul"     => $request->judul,
            "slug"      => $inputslug,
            "tanggal"   => $tanggal,
            "kategori"  => $request->kategori,
            "deskripsi" => $request->deskripsi,
            "status"    => $request->status,
        ];

        // file_ebook
        if ($request->hasFile('file_ebook')) {
            if (! empty($data->url) && Storage::disk('public')->exists($data->url)) {
                Storage::disk('public')->delete($data->url);
            }
            $img = Helper::UpFilePdf($request, "file_ebook", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, File Ebook Tidak Sesuai Format!');
                return back();
            }
            $value_1['url']  = $img['url'];
            $value_1['tipe'] = $img['tipe'];
            $value_1['size'] = $img['size'];
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
            $img = Helper::UpThumbnailsEbook($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return back();
            }
            $value_1['thumbnails'] = $img;
        }

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_ebook"],
                "uuid"  => [$uuid],
                "value" => [$request->judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Ebook UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.ebook.index');
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
        $data  = PortalEbook::findOrFail($uuid);
        $judul = $data->judul;

        // Lakukan soft delete
        if ($data->status == "Draft" || $data->status == "Pending Review") {
            // drop path
            $tahun = Carbon::parse($data->tanggal)->year;
            $path  = "ebook/{$tahun}/{$data->uuid}";
            Helper::deleteFolderIfExists("directory", $path);
            $save_1 = $data->forceDelete();
        } else {
            // Update uuid_deleted dan status sebelum melakukan soft delete
            $save_1 = $data->delete();
        }

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_ebook"],
                "uuid"  => [$uuid],
                "value" => [$judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Ebook UUID= " . $uuid,
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
}
