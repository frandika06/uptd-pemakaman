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
            $request->session()->put('filter_kategori_banner', 'Hero');
            $kategori = "Hero";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $kategori = $_GET['filter']['kategori'];
                $request->session()->put('filter_kategori_banner', $kategori);
            } else {
                $kategori = $request->session()->get('filter_kategori_banner');
            }

            $data = PortalBanner::whereKategori($kategori)->orderBy("created_at", "DESC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('judul', function ($data) {
                    $thumbnails = Helper::thumbnail($data->thumbnails);
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit = route('prt.apps.banner.edit', $uuid_enc);
                    $judul = '
                    <div class="trans-list">
                        <img src="' . $thumbnails . '" alt="" class="rounded avatar avatar-xl me-3" draggable="false">
                        <h4><a class="text-underline" href="' . $edit . '">' . Str::limit($data->judul, 30, "...") . '</a></h4>
                    </div>';
                    return $judul;
                })
                ->addColumn('penulis', function ($data) {
                    $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                    return $penulis;
                })
                ->addColumn('publisher', function ($data) {
                    if ($data->status == "1") {
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
                ->addColumn('tanggal', function ($data) {
                    $tanggal = Helper::TglSimple($data->tanggal);
                    return $tanggal;
                })
                ->addColumn('status', function ($data) use ($auth) {
                    $uuid = Helper::encode($data->uuid);
                    $status = $data->status;
                    if ($status == "1") {
                        $toogle = "checked";
                        $text = "Aktif";
                    } else {
                        $toogle = "";
                        $text = "Tidak Aktif";
                    }
                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                        $status = '
                            <div class="form-check form-switch form-switch-custom form-switch-primary mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="status_' . $data->uuid . '" data-onclick="ubah-status" data-status="' . $uuid . '" data-status-value="' . $status . '" ' . $toogle . '>
                                <label class="form-check-label" for="status_' . $data->uuid . '">' . $text . '</label>
                            </div>
                        ';
                    } else {
                        $status = '<label class="form-check-label" for="status">' . $text . '</label>';
                    }
                    return $status;
                })
                ->addColumn('aksi', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit = route('prt.apps.banner.edit', [$uuid_enc]);
                    $aksi = '
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
        return view('pages.admin.portal_apps.banner.index', compact(
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
        $title = "Tambah Data Banner";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.banner.create_edit', compact(
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

        //validate
        $request->validate([
            "judul" => "required|string|max:300",
            "thumbnails" => "required|image|mimes:png,jpg,jpeg|max:2048",
            "url" => "sometimes|nullable|url",
            "deskripsi" => "sometimes|nullable|string|max:500",
            "warna_text" => "required",
            "kategori" => "required|string|max:100",
        ]);

        // value
        $uuid = Str::uuid();
        $path = "banner/" . date('Y') . "/" . $uuid;
        $value_1 = [
            "uuid" => $uuid,
            "judul" => $request->judul,
            "url" => $request->url,
            "deskripsi" => $request->deskripsi,
            "tanggal" => Carbon::now(),
            "warna_text" => $request->warna_text,
            "kategori" => $request->kategori,
            'status' => "0",
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
                "tabel" => array("portal_banner"),
                "uuid" => array($uuid),
                "value" => array($value_1),
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menambahkan Data Banner UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
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
        $uuid = Helper::decode($uuid_enc);
        $data = PortalBanner::findOrFail($uuid);
        // get kategori
        $kategoriList = PortalKategori::whereType("Banner")->whereStatus("1")->orderBy("nama")->get();
        $title = "Edit Data Banner";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.banner.create_edit', compact(
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

        //validate
        $request->validate([
            "judul" => "required|string|max:300",
            "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "url" => "sometimes|nullable|url",
            "deskripsi" => "sometimes|nullable|string|max:500",
            "warna_text" => "required",
            "kategori" => "required|string|max:100",
        ]);

        // $uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalBanner::findOrFail($uuid);

        // value
        $thn = date("Y", \strtotime($data->created_at));
        $path = "banner/" . $thn . "/" . $uuid;
        $value_1 = [
            "judul" => $request->judul,
            "deskripsi" => $request->deskripsi,
            "warna_text" => $request->warna_text,
            "url" => $request->url,
            "kategori" => $request->kategori,
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
                "tabel" => array("portal_banner"),
                "uuid" => array($uuid),
                "value" => array($value_1),
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Data Banner UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
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
        // uuid
        $uuid = Helper::decode($request->uuid);

        // data
        $data = PortalBanner::findOrFail($uuid);

        // save
        $save_1 = $data->delete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => array("portal_banner"),
                "uuid" => array($uuid),
                "value" => array($data),
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menghapus Data Banner UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            $msg = "Data Berhasil Dihapus!";
            $response = [
                "status" => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // success
            $msg = "Data Gagal Dihapus!";
            $response = [
                "status" => false,
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
        $uuid = Helper::decode($request->uuid);
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
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Status Banner: " . $data->judul . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            $msg = "Status Berhasil Diubah!";
            $response = [
                "status" => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // success
            $msg = "Status Gagal Diubah!";
            $response = [
                "status" => false,
                "message" => $msg,
            ];
            return response()->json($response, 422);
        }
    }
}
