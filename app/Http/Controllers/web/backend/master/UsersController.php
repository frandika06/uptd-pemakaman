<?php
namespace App\Http\Controllers\web\backend\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalActor;
use App\Models\User;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $tags)
    {
        // auth
        $auth = Auth::user();
        // tags
        $role = Helper::decode($tags);
        if ($auth->role !== "Super Admin" && $role == "Admin") {
            alert()->error('Error', "Anda Tidak Memiliki Hak Akses!");
            return \redirect()->route('auth.home');
        }

        if ($request->ajax()) {
            $data = PortalActor::join("users", "portal_actor.uuid_user", "=", "users.uuid")
                ->select("portal_actor.*", "users.status")
                ->where("users.role", $role)
                ->where("users.is_api_user", "0")
                ->orderBy("portal_actor.nama_lengkap", "ASC")
                ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama_lengkap', function ($data) use ($tags) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.mst.users.edit', [$tags, $uuid_enc]);
                    $foto     = Helper::pp($data->foto);
                    return '
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px symbol-circle me-3">
                                <img src="' . $foto . '" alt="' . $data->nama_lengkap . '" class="object-fit-cover" />
                            </div>
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $data->nama_lengkap . '</a>
                                <span class="text-muted fw-semibold d-block fs-7">' . $data->jabatan . '</span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('kontak', function ($data) {
                    return '<span class="fw-semibold">' . $data->kontak . '</span>';
                })
                ->addColumn('email', function ($data) {
                    return '<span class="fw-semibold">' . $data->email . '</span>';
                })
                ->addColumn('jabatan', function ($data) {
                    return '<span class="badge badge-light-primary fw-bold fs-7 px-3 py-2">' . $data->jabatan . '</span>';
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
                    if ($auth->role == "Super Admin" || $auth->role == "Admin") {
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
                ->addColumn('aksi', function ($data) use ($auth, $tags) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.mst.users.edit', [$tags, $uuid_enc]);
                    if ($auth->role == "Super Admin" || $auth->role == "Admin") {
                        $aksi = '
                            <div class="d-flex justify-content-center">
                                <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="ki-outline ki-pencil fs-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                    <i class="ki-outline ki-trash fs-2"></i>
                                </a>
                            </div>
                        ';
                    } else {
                        if (isset($data->uuid_created) && $data->uuid_created == $auth->uuid) {
                            $aksi = '
                                <div class="d-flex justify-content-center">
                                    <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                        <i class="ki-outline ki-pencil fs-2"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
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

        return view('admin.setup.master.users.index', compact(
            'tags',
            'role'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($tags)
    {
        // tags
        $role   = Helper::decode($tags);
        $title  = "Tambah Data Master Users " . $role;
        $submit = "Simpan";
        return view('admin.setup.master.users.create_edit', compact(
            'tags',
            'role',
            'title',
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
        $role = Helper::decode($tags);

        // Validasi input sesuai kolom tabel
        $request->validate([
            "nip"           => 'required|string|max:20',
            'nama_lengkap'  => 'required|string|max:100',
            'jenis_kelamin' => 'required|string',
            'kontak'        => 'required|numeric',
            'email'         => 'required|email|unique:portal_actor,email',
            "jabatan"       => 'required|string|max:100',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password'      => [
                'required',
                'string',
                Password::min(8)   // Minimal 8 karakter
                    ->letters()        // Harus ada huruf
                    ->mixedCase()      // Harus ada huruf besar dan kecil
                    ->numbers()        // Harus ada angka
                    ->symbols()        // Harus ada simbol
                    ->uncompromised(), // Cek apakah password sudah bocor
            ],
        ]);

        // value 1
        $email     = Str::lower($request->email);
        $uuid_user = Str::uuid();
        $value_1   = [
            "uuid"     => $uuid_user,
            'username' => $email,
            'password' => bcrypt($request->password),
            "role"     => $role,
        ];

        // value 2
        $uuid_actor = Str::uuid();
        $value_2    = [
            "uuid"          => $uuid_actor,
            "uuid_user"     => $uuid_user,
            'nip'           => $request->nip,
            'nama_lengkap'  => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
            'kontak'        => $request->kontak,
            'email'         => $email,
            'jabatan'       => $request->jabatan,
        ];

        // foto
        $path = "actor/" . $uuid_actor;
        if ($request->hasFile("foto")) {
            $foto = Helper::UpFoto($request, "foto", $path);
            if ($foto == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Foto Tidak Sesuai Format!');
                return back();
            }
            $value_2['foto'] = $foto;
        }

        // save
        $save_1 = User::create($value_1);
        $save_2 = PortalActor::create($value_2);
        if ($save_1 && $save_2) {
            // create log
            $aktifitas = [
                "tabel" => ["users", "portal_actor"],
                "uuid"  => [$uuid_user, $uuid_actor],
                "value" => [$value_1, $value_2],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Master Users: " . $request->nama_lengkap . " - " . $uuid_user,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return \redirect()->route('prt.apps.mst.users.index', [$tags]);
        } else {
            alert()->error('Error', "Gagal Menambahkan Data!");
            return \back()->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid_enc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($tags, $uuid_enc)
    {
        // tags
        $role = Helper::decode($tags);
        // uuid
        $uuid   = Helper::decode($uuid_enc);
        $data   = PortalActor::findOrFail($uuid);
        $title  = "Edit Data Master Users";
        $submit = "Simpan";
        return view('admin.setup.master.users.create_edit', compact(
            'tags',
            'role',
            'uuid_enc',
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
        // tags
        $role = Helper::decode($tags);
        // uuid
        $uuid_actor = Helper::decode($uuid_enc);
        $data       = PortalActor::findOrFail($uuid_actor);

        // Validasi input sesuai kolom tabel
        $request->validate([
            "nip"           => 'required|string|max:20',
            'nama_lengkap'  => 'required|string|max:100',
            'jenis_kelamin' => 'required|string',
            'kontak'        => 'required|numeric',
            "email"         => "required|string|max:100",
            "jabatan"       => 'required|string|max:100',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $email = $request->email;
        // cek email
        if ($email != $data->email) {
            $cekEmail = PortalActor::whereEmail($email)->first();
            if ($cekEmail !== null) {
                // ada actor
                alert()->error('Gagal Simpan!', 'Email Sudah Digunakan Oleh User Lain, Mohon Cek Kembali Alamat Email!');
                return back()->withInput($request->all());
            }
        }

        // value 1
        $email     = Str::lower($request->email);
        $uuid_user = $data->uuid_user;
        $value_1   = [
            "uuid_updated" => $auth->uuid,
        ];

        // Cek apakah field password diisi
        if ($request->filled('password')) {
            // Validasi password
            $request->validate([
                'password' => [
                    'required',
                    'string',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(),
                ],
            ]);

            // Pastikan password baru tidak sama dengan yang lama
            if (password_verify($request->password, $data->RelUser->password)) {
                alert()->error('Error', "Password tidak boleh sama dengan sebelumnya!");
                return back()->withInput($request->all());
            }

            $value_1['password'] = bcrypt($request->password);
        }

        // value 2
        $uuid_actor = $data->uuid;
        $value_2    = [
            'nip'           => $request->nip,
            'nama_lengkap'  => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
            'kontak'        => $request->kontak,
            'email'         => $email,
            'jabatan'       => $request->jabatan,
        ];

        // foto
        $path = "actor/" . $uuid_actor;
        if ($request->hasFile('foto')) {
            if (! empty($data->foto) && Storage::disk('public')->exists($data->foto)) {
                Storage::disk('public')->delete($data->foto);
                $avatarPath = str_replace('.', '_avatar.', $data->foto);
                if (Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }
            }
            $img = Helper::UpFoto($request, "foto", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Foto Tidak Sesuai Format!');
                return \back();
            }
            $value_2['foto'] = $img;
        }

        // save
        $save_1 = User::whereUuid($uuid_user)->update($value_1);
        $save_2 = $data->update($value_2);
        if ($save_1 && $save_2) {
            // create log
            $aktifitas = [
                "tabel" => ["users", "portal_actor"],
                "uuid"  => [$uuid_user, $uuid_actor],
                "value" => [$value_1, $value_2],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Master Users: " . $request->nama_lengkap . " - " . $uuid_user,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Mengubah Data!");
            return \redirect()->route('prt.apps.mst.users.index', [$tags]);
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return \back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $tags)
    {
        // auth
        $auth = Auth::user();

        // uuid
        $uuid = Helper::decode($request->uuid);

        // data
        $data      = PortalActor::findOrFail($uuid);
        $uuid_user = $data->uuid_user;

        // save
        $save_1 = $data->delete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_actor"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Master Users: " . $data->nama_lengkap . " - " . $uuid_user,
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
    public function status(Request $request, $tags)
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
        $data      = PortalActor::findOrFail($uuid);
        $uuid_user = $data->uuid_user;

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = User::whereUuid($uuid_user)->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["users"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Master Users: " . $data->nama_lengkap . " - " . $uuid_user,
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