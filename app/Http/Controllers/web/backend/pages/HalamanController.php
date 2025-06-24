<?php
namespace App\Http\Controllers\web\backend\pages;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalPage;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
        if ($request->session()->exists('filter_status_halaman')) {
            $status = $request->session()->get('filter_status_halaman');
        } else {
            $request->session()->put('filter_status_halaman', 'Published');
            $status = "Published";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $status = $_GET['filter']['status'];
                $request->session()->put('filter_status_halaman', $status);
            }
            // cek role
            if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                if ($status == "Draft") {
                    $data = PortalPage::whereStatus($status)
                        ->whereKategori($kategori)
                        ->whereUuidCreated($auth->uuid)
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
                ->addColumn('judul', function ($data) use ($tags) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.page.edit', [$tags, $uuid_enc]);
                    $judul    = '<a class="text-underline" href="' . $edit . '">' . Str::limit($data->judul, 50, "...") . '</a>';
                    return $judul;
                })
                ->addColumn('views', function ($data) {
                    $views = Helper::toDot($data->views);
                    return $views;
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
                ->addColumn('aksi', function ($data) use ($tags) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.page.edit', [$tags, $uuid_enc]);
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
        return view('pages.admin.portal_apps.halaman.index', compact(
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
        return view('pages.admin.portal_apps.halaman.create_edit', compact(
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
            "uuid"     => $uuid,
            "no_urut"  => $request->no_urut,
            "judul"    => $request->judul,
            "kategori" => $kategori,
            "slug"     => $inputslug,
            "tanggal"  => $tanggal,
            "status"   => $request->status,
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
        $imgpost         = Helper::UpImgPostWithCompress($request, "post", $path);
        $value_1['post'] = $imgpost;

        // deskripsi
        $value_1['deskripsi'] = $request->filled('deskripsi') ? $request->deskripsi : Helper::generateDescription($imgpost);

        // save
        $save_1 = PortalPage::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_page"],
                "uuid"  => [$uuid],
                "value" => [$request->judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Halaman UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return redirect()->route('prt.apps.page.index', [$tags]);
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
        $data   = PortalPage::findOrFail($uuid);
        $title  = "Edit Data Halaman " . $kategori;
        $submit = "Simpan";
        return view('pages.admin.portal_apps.halaman.create_edit', compact(
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
            "no_urut"    => "required|numeric|min:1",
            "judul"      => "required|string|max:300",
            "deskripsi"  => "required|string|max:160",
            "post"       => "required",
            "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "tanggal"    => "required",
        ]);

        // $uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalPage::findOrFail($uuid);

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
        $imgpost         = Helper::UpdateImgPostWithCompress($request, "post", $path);
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
                "subjek"    => "Mengubah Data Halaman UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.page.index', [$tags]);
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
        $data  = PortalPage::findOrFail($uuid);
        $judul = $data->judul;

        // Lakukan soft delete
        if ($data->status == "Draft" || $data->status == "Pending Review") {
            // drop path
            $tahun = Carbon::parse($data->tanggal)->year;
            $path  = "halaman/{$tahun}/{$data->uuid}";
            Helper::deleteFolderIfExists("directory", $path);
            $save_1 = $data->forceDelete();
        } else {
            // Update uuid_deleted dan status sebelum melakukan soft delete
            $save_1 = $data->delete();
        }

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_page"],
                "uuid"  => [$uuid],
                "value" => [$judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Halaman UUID= " . $uuid,
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
