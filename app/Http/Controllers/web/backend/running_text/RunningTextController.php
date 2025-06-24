<?php

namespace App\Http\Controllers\web\backend\running_text;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalRunningText;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RunningTextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();

        if ($request->ajax()) {
            $data = PortalRunningText::orderBy("created_at", "DESC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('judul', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit = route('prt.apps.runningtext.edit', $uuid_enc);
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
                    $edit = route('prt.apps.runningtext.edit', [$uuid_enc]);
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
        return view('pages.admin.portal_apps.running_text.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // variable
        $title = "Tambah Data Running Text";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.running_text.create_edit', compact(
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

        // Validasi input sesuai kolom form
        $request->validate([
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string|max:500',
        ]);

        // value untuk PortalRunningText
        $uuid_running_text = Str::uuid();
        $value = [
            'uuid' => $uuid_running_text,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'status' => "0",
        ];

        // Save ke database
        $save = PortalRunningText::create($value);
        if ($save) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_running_text"],
                "uuid" => [$uuid_running_text],
                "value" => [$value],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menambahkan Data Running Text: " . $request->judul . " - " . $uuid_running_text,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return redirect()->route('prt.apps.runningtext.index');
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
        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalRunningText::findOrFail($uuid);
        $title = "Edit Data Running Text";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.running_text.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
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

        // Decode UUID dan dapatkan data yang akan diupdate
        $uuid_running_text = Helper::decode($uuid_enc);
        $data = PortalRunningText::findOrFail($uuid_running_text);

        // Validasi input sesuai kolom form
        $request->validate([
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string|max:500',
        ]);

        // value untuk update PortalRunningText
        $value = [
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ];

        // Save update ke database
        $save = $data->update($value);
        if ($save) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_running_text"],
                "uuid" => [$uuid_running_text],
                "value" => [$value],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Data Running Text: " . $request->judul . " - " . $uuid_running_text,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.runningtext.index');
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
        // auth
        $auth = Auth::user();

        // uuid
        $uuid = Helper::decode($request->uuid);

        // data
        $data = PortalRunningText::findOrFail($uuid);

        // save
        $save_1 = $data->delete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_running_text"],
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menghapus Data Running Text: " . $data->judul . " - " . $uuid,
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
        $data = PortalRunningText::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_running_text"],
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Status Running Text: " . $data->judul . " - " . $uuid,
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