<?php
namespace App\Http\Controllers\web\backend\testimoni;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalKategori;
use App\Models\PortalTestimoni;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestimoniController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();

        if ($request->ajax()) {
            $data = PortalTestimoni::orderBy("created_at", "DESC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama_lengkap', function ($data) {
                    $foto         = Helper::pp($data->foto);
                    $nama_lengkap = '
                    <div class="trans-list">
                        <img src="' . $foto . '" alt="" class="avatar avatar-sm me-3" draggable="false">
                        <h4>' . $data->nama_lengkap . '</h4>
                    </div>';
                    return $nama_lengkap;
                })
                ->addColumn('judul', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.testimoni.edit', $uuid_enc);
                    $judul    = '<a class="text-underline" href="' . $edit . '">' . Str::limit($data->judul, 25, "...") . '</a>';
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
                    $uuid   = Helper::encode($data->uuid);
                    $status = $data->status;
                    if ($status == "1") {
                        $toogle = "checked";
                        $text   = "Aktif";
                    } else {
                        $toogle = "";
                        $text   = "Tidak Aktif";
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
                    $edit     = route('prt.apps.testimoni.edit', [$uuid_enc]);
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
        return view('pages.admin.portal_apps.testimoni.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // variable
        $title  = "Tambah Data Testimoni";
        $submit = "Simpan";
        // ketegori
        $kategori = PortalKategori::whereType("Testimoni")->whereStatus("1")->orderBy("nama")->get();
        return view('pages.admin.portal_apps.testimoni.create_edit', compact(
            'title',
            'submit',
            'kategori'
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
            'nama_lengkap' => 'required|string|max:100',
            'jabatan'      => 'required|string|max:100',
            'kategori'     => 'required|string|max:100',
            'judul'        => 'required|string|max:300',
            'ringkasan'    => 'required|string|max:500',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // value untuk PortalTestimoni
        $uuid_testimoni = Str::uuid();

        // slug
        $slug      = \Str::slug($request->judul);
        $cekslug   = PortalTestimoni::whereSlug($slug)->count();
        $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;

        // value 1
        $value_1 = [
            'uuid'         => $uuid_testimoni,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan'      => $request->jabatan,
            'kategori'     => $request->kategori,
            'judul'        => $request->judul,
            'slug'         => $inputslug,
            'ringkasan'    => $request->ringkasan,
            'status'       => "0",
        ];

        // Handling foto
        $path = "testimoni/" . date('Y') . "/" . $uuid_testimoni;
        if ($request->hasFile('foto')) {
            $foto = Helper::UpFoto($request, "foto", $path);
            if ($foto == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Foto Tidak Sesuai Format!');
                return back()->withInput($request->all());
            }
            $value_1['foto'] = $foto;
        }

        // Save ke database
        $save = PortalTestimoni::create($value_1);
        if ($save) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_testimoni"],
                "uuid"  => [$uuid_testimoni],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Testimoni: " . $request->nama_lengkap . " - " . $uuid_testimoni,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return redirect()->route('prt.apps.testimoni.index');
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
        $uuid   = Helper::decode($uuid_enc);
        $data   = PortalTestimoni::findOrFail($uuid);
        $title  = "Edit Data Testimoni";
        $submit = "Simpan";
        // ketegori
        $kategori = PortalKategori::whereType("Testimoni")->whereStatus("1")->orderBy("nama")->get();
        return view('pages.admin.portal_apps.testimoni.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'kategori',
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
        $uuid_testimoni = Helper::decode($uuid_enc);
        $data           = PortalTestimoni::findOrFail($uuid_testimoni);

        // Validasi input sesuai kolom form
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'jabatan'      => 'required|string|max:100',
            'kategori'     => 'required|string|max:100',
            'judul'        => 'required|string|max:300',
            'ringkasan'    => 'required|string|max:500',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // slug
        if ($data->judul !== $request->judul) {
            $slug      = \Str::slug($request->judul);
            $cekslug   = PortalTestimoni::where('uuid', '!=', $uuid_testimoni)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value untuk update PortalTestimoni
        $value_1 = [
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan'      => $request->jabatan,
            'kategori'     => $request->kategori,
            'judul'        => $request->judul,
            'slug'         => $inputslug,
            'ringkasan'    => $request->ringkasan,
        ];

        // Handling foto
        $thn  = date("Y", \strtotime($data->created_at));
        $path = "testimoni/" . $thn . "/" . $uuid_testimoni;
        if ($request->hasFile('foto')) {
            if (! empty($data->foto) && Storage::disk('public')->exists($data->foto)) {
                Storage::disk('public')->delete($data->foto);
                $avatarPath = str_replace('.', '_avatar.', $data->foto);
                if (Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }
            }
            $foto = Helper::UpFoto($request, "foto", $path);
            if ($foto == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Foto Tidak Sesuai Format!');
                return back()->withInput($request->all());
            }
            $value_1['foto'] = $foto;
        }

        // Save update ke database
        $save = $data->update($value_1);
        if ($save) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_testimoni"],
                "uuid"  => [$uuid_testimoni],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Testimoni: " . $request->nama_lengkap . " - " . $uuid_testimoni,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.testimoni.index');
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
        $data = PortalTestimoni::findOrFail($uuid);

        // save
        $save_1 = $data->delete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_testimoni"],
                "uuid"  => [$uuid],
                "value" => [],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Testimoni: " . $data->nama_lengkap . " - " . $uuid,
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
        $data = PortalTestimoni::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_testimoni"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Testimoni: " . $data->nama_lengkap . " - " . $uuid,
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
}
