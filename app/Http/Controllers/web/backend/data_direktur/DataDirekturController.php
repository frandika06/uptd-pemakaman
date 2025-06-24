<?php

namespace App\Http\Controllers\web\backend\data_direktur;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalDataDirektur;
use App\Models\PortalKategori;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DataDirekturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();

        if ($request->ajax()) {
            $data = PortalDataDirektur::orderBy("no_urut", "DESC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama_lengkap', function ($data) {
                    $foto = Helper::thumbnail($data->foto);
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit = route('prt.apps.data.direktur.edit', [$uuid_enc]);
                    $nama_lengkap = '
                    <div class="trans-list">
                        <img src="' . $foto . '" alt="" class="avatar avatar-sm me-3" draggable="false">
                        <h4><a class="text-underline" href="' . $edit . '">' . $data->nama_lengkap . '</a></h4>
                    </div>';
                    return $nama_lengkap;
                })
                ->addColumn('penulis', function ($data) {
                    $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                    return $penulis;
                })
                ->addColumn('publisher', function ($data) {
                    $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                    return $publisher;
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
                ->addColumn('aksi', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit = route('prt.apps.data.direktur.edit', [$uuid_enc]);
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
        return view('pages.admin.portal_apps.data_direktur.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // auth
        $auth = Auth::user();
        $title = "Tambah Data Direktur";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.data_direktur.create_edit', compact(
            'title',
            'submit',
            'auth',
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input tanpa memvalidasi status
        $request->validate([
            "no_urut"  => "required|numeric|min:1",
            "nama_lengkap"  => "required|string|max:100",
            "foto" => "required|image|mimes:png,jpg,jpeg|max:2048",
            "jabatan"  => "required|string|max:100",
            "masa_jabatan"  => "required|string|max:100",
        ]);

        // value
        $uuid = Str::uuid();
        $path = "data_direktur/" . $uuid;
        $value_1 = [
            "uuid" => $uuid,
            "no_urut" => $request->no_urut,
            "nama_lengkap" => $request->nama_lengkap,
            "jabatan" => $request->jabatan,
            "masa_jabatan" => $request->masa_jabatan,
            "status" => "0",
        ];

        // foto
        if ($request->hasFile('foto')) {
            $img = Helper::UpFotoDirektur($request, "foto", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, File Data Direktur Tidak Sesuai Format!');
                return back();
            }
            $value_1['foto'] = $img;
        }

        // save
        $save_1 = PortalDataDirektur::create($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_data_direktur"],
                "uuid" => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menambahkan Data Direktur UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return redirect()->route('prt.apps.data.direktur.index');
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
        $data = PortalDataDirektur::findOrFail($uuid);
        $title = "Edit Data Direktur";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.data_direktur.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'auth',
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

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "no_urut"  => "required|numeric|min:1",
            "nama_lengkap"  => "required|string|max:100",
            "foto" => "sometimes|nullable|image|mimes:png,jpg,jpeg|max:2048",
            "jabatan"  => "required|string|max:100",
            "masa_jabatan"  => "required|string|max:100",
        ]);

        // $uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalDataDirektur::findOrFail($uuid);

        // value
        $path = "data_direktur/" . $uuid;
        $value_1 = [
            "no_urut" => $request->no_urut,
            "nama_lengkap" => $request->nama_lengkap,
            "jabatan" => $request->jabatan,
            "masa_jabatan" => $request->masa_jabatan,
        ];

        // foto
        if ($request->hasFile('foto')) {
            if (!empty($data->foto) && Storage::disk('public')->exists($data->foto)) {
                Storage::disk('public')->delete($data->foto);
                $thumbnailPath = str_replace('.', '_thumbnail.', $data->foto);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            $img = Helper::UpFotoDirektur($request, "foto", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, File Data Direktur Tidak Sesuai Format!');
                return back();
            }
            $value_1['foto'] = $img;
        }

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_data_direktur"],
                "uuid" => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Data Direktur UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.data.direktur.index');
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
        // Decode UUID dari request
        $uuid = Helper::decode($request->uuid);

        // Dapatkan data dari database
        $data = PortalDataDirektur::findOrFail($uuid);

        // Lakukan soft delete
        $save_1 = $data->delete();

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_data_direktur"],
                "uuid" => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Menghapus Data Direktur UUID= " . $uuid,
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
    public function status(Request $request)
    {
        // uuid
        $uuid = Helper::decode($request->uuid);
        $status = $request->status;
        if ($status == "0") {
            $status_update = "1";
        } else {
            $status_update = "0";
        }

        // data
        $data = PortalDataDirektur::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_data_direktur"],
                "uuid" => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Status Data Direktur: " . $data->nama_lengkap . " - " . $uuid,
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