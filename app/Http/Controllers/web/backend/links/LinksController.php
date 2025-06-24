<?php

namespace App\Http\Controllers\web\backend\links;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalLinks;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
                ->addColumn('judul', function ($data) use ($tags) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit = route('prt.apps.links.edit', [$tags, $uuid_enc]);
                    $judul = '<a class="text-underline" href="' . $edit . '">' . Str::limit($data->judul, 50, "...") . '</a>';
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
                    if ($role == "Super Admin" || $role == "Admin") {
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
                ->addColumn('aksi', function ($data) use ($tags) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit = route('prt.apps.links.edit', [$tags, $uuid_enc]);
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
        return view('pages.admin.portal_apps.links.index', compact(
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
        $title = "Tambah Data Links " . $kategori;
        $submit = "Simpan";
        return view('pages.admin.portal_apps.links.create_edit', compact(
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
            "no_urut"  => "required|numeric|min:1",
            "judul" => "required|string|max:300",
            "url" => "required|url|max:300",
        ]);

        // value
        $uuid = Str::uuid();
        $value_1 = [
            "uuid" => $uuid,
            "no_urut" => $request->no_urut,
            "judul" => $request->judul,
            "url" => $request->url,
            "kategori" => $kategori,
            "status" => "1",
        ];

        // save
        $save_1 = PortalLinks::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_links"],
                "uuid" => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menambahkan Data Links UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
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
        $uuid = Helper::decode($uuid_enc);
        $data = PortalLinks::findOrFail($uuid);
        $title = "Edit Data Links " . $kategori;
        $submit = "Simpan";
        return view('pages.admin.portal_apps.links.create_edit', compact(
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
            "no_urut"  => "required|numeric|min:1",
            "judul" => "required|string|max:300",
            "url" => "required|url|max:300",
        ]);

        // $uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalLinks::findOrFail($uuid);

        // value
        $value_1 = [
            "no_urut" => $request->no_urut,
            "judul" => $request->judul,
            "url" => $request->url,
            "kategori" => $kategori,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_links"],
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Data Links UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
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
        $data = PortalLinks::findOrFail($uuid);
        $judul = $data->judul;

        // Update uuid_deleted dan status sebelum melakukan soft delete
        $save_1 = $data->delete();

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_links"],
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menghapus Data Links UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);

            // Return response success
            $msg = "Data Berhasil Dihapus!";
            $response = [
                "status" => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // Return response gagal
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
    public function status(Request $request, $tags)
    {
        // auth
        $auth = Auth::user();
        // tags
        $kategori = Helper::decode($tags);

        // uuid
        $uuid = Helper::decode($request->uuid);
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
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Status Links {$kategori}: " . $data->judul . " - " . $uuid,
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
