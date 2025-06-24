<?php

namespace App\Http\Controllers\web\backend\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalKategori;
use App\Models\PortalKategoriSub;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KategoriSubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $uuid_tags_enc)
    {
        // auth
        $auth = Auth::user();
        $uuid_kategori = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);

        if ($request->ajax()) {
            $data = PortalKategoriSub::where("uuid_kategori", $uuid_kategori)->orderBy("nama", "ASC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('status', function ($data) use ($auth) {
                    $uuid = Helper::encode($data->uuid);
                    if ($data->status == "1") {
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
                                <input class="form-check-input" type="checkbox" role="switch" id="status_' . $data->uuid . '" data-onclick="ubah-status" data-status="' . $uuid . '" data-status-value="' . $data->status . '" ' . $toogle . '>
                                <label class="form-check-label" for="status_' . $data->uuid . '">' . $text . '</label>
                            </div>
                        ';
                    } else {
                        $status = '<label class="form-check-label" for="status">' . $text . '</label>';
                    }
                    return $status;
                })
                ->addColumn('aksi', function ($data) use ($auth, $uuid_tags_enc) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit = route('prt.apps.mst.tags.sub.edit', [$uuid_tags_enc, $uuid_enc]);
                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin") {
                        $aksi = '
                            <div class="d-flex">
                                <a href="' . $edit . '" class="btn btn-primary shadow btn-xs sharp me-1"><i class="fas fa-pencil-alt"></i></a>
                                <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp" data-delete="' . $uuid_enc . '"><i class="fa fa-trash"></i></a>
                            </div>
                        ';
                    } else {
                        if ($data->uuid_created == $auth->uuid) {
                            $aksi = '
                                <div class="d-flex">
                                    <a href="' . $edit . '" class="btn btn-primary shadow btn-xs sharp me-1"><i class="fas fa-pencil-alt"></i></a>
                                    <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp" data-delete="' . $uuid_enc . '"><i class="fa fa-trash"></i></a>
                                </div>
                            ';
                        } else {
                            $aksi = '
                                <div class="d-flex">
                                    <a href="javascript:void(0);" class="btn btn-primary shadow btn-xs sharp me-1 disabled"><i class="fas fa-pencil-alt"></i></a>
                                    <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp disabled"><i class="fa fa-trash"></i></a>
                                </div>
                            ';
                        }
                    }
                    return $aksi;
                })
                ->escapeColumns([''])
                ->make(true);
        }

        return view('pages.admin.portal_apps.kategori_sub.index', compact(
            'uuid_tags_enc',
            'master_kategori'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($uuid_tags_enc)
    {
        $uuid_kategori = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);
        $title = "Tambah Data Master Kategori Sub: " . $master_kategori->nama;
        $submit = "Simpan";
        return view('pages.admin.portal_apps.kategori_sub.create_edit', compact(
            'uuid_tags_enc',
            'master_kategori',
            'title',
            'submit'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $uuid_tags_enc)
    {
        // auth
        $auth = Auth::user();
        $uuid_kategori = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);

        //validate
        $request->validate([
            "nama" => "required|string|max:100",
        ]);

        //uuid
        $uuid = Str::uuid();
        $nama = $request->nama;

        // cek kategori
        $cekKategori = PortalKategoriSub::whereNama($nama)->where("uuid_kategori", $uuid_kategori)->first();
        if ($cekKategori !== null) {
            // ada data
            alert()->error('Error!', 'Nama Kategori Sudah Ada!');
            return \back()->withInput($request->all());
        }

        // value
        $value_1 = [
            "uuid" => $uuid,
            "uuid_kategori" => $uuid_kategori,
            "nama" => $nama,
            "slug" => Str::slug($nama),
        ];

        // save
        $save_1 = PortalKategoriSub::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori_sub"],
                "uuid" => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menambahkan Data Master Kategori Sub: " . $nama . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return \redirect()->route('prt.apps.mst.tags.sub.index', [$uuid_tags_enc]);
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
    public function edit($uuid_tags_enc, $uuid_enc)
    {
        // uuid
        $uuid_kategori = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);
        $uuid = Helper::decode($uuid_enc);
        $data = PortalKategoriSub::findOrFail($uuid);
        $title = "Edit Data Master Kategori Sub: " . $master_kategori->nama;
        $submit = "Simpan";
        return view('pages.admin.portal_apps.kategori_sub.create_edit', compact(
            'uuid_tags_enc',
            'uuid_enc',
            'title',
            'submit',
            'data'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_tags_enc, $uuid_enc)
    {
        // auth
        $auth = Auth::user();
        $uuid_kategori = Helper::decode($uuid_tags_enc);
        $master_kategori = PortalKategori::findOrFail($uuid_kategori);

        //validate
        $request->validate([
            "nama" => "required|string|max:100",
        ]);

        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalKategoriSub::findOrFail($uuid);
        $nama = $request->nama;

        if ($nama != $data->nama) {
            // cek kategori
            $cekKategori = PortalKategoriSub::whereNama($nama)->where("uuid_kategori", $uuid_kategori)->first();
            if ($cekKategori !== null) {
                // ada data
                alert()->error('Error!', 'Nama Kategori Sudah Ada!');
                return \back()->withInput($request->all());
            }
        }

        // value
        $value_1 = [
            "nama" => $nama,
            "slug" => Str::slug($nama),
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori_sub"],
                "uuid" => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Data Master Kategori Sub: " . $nama . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Mengubah Data!");
            return \redirect()->route('prt.apps.mst.tags.sub.index', [$uuid_tags_enc]);
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
        // auth
        $auth = Auth::user();

        // uuid
        $uuid = Helper::decode($request->uuid);

        // data
        $data = PortalKategoriSub::findOrFail($uuid);

        // save
        $save_1 = $data->delete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori_sub"],
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menghapus Data Master Kategori Sub: " . $data->nama . " - " . $uuid,
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
        $data = PortalKategoriSub::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kategori_sub"],
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Status Master Kategori Sub: " . $data->nama . " - " . $uuid,
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