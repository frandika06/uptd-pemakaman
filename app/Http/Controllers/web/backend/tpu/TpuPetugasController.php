<?php
namespace App\Http\Controllers\web\backend\tpu;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TpuDatas;
use App\Models\TpuPetugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\DataTables;

class TpuPetugasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = Auth::user();

        if ($request->ajax()) {
            $query = TpuPetugas::query()->with(['Tpu', 'User']);

            // Filter berdasarkan role
            if ($auth->role === 'Admin TPU') {
                $query->whereHas('Tpu', function ($q) use ($auth) {
                    $q->where('uuid_created', $auth->uuid);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama_petugas', function ($data) {
                    $foto = Helper::pp($data->foto);
                    return '<div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-5">
                                <img src="' . $foto . '" alt="avatar">
                            </div>
                            <div class="d-flex flex-column">
                                <a href="#" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $data->nama_lengkap . '</a>
                                <span class="text-muted fw-semibold d-block fs-7">' . $data->jabatan . '</span>
                            </div>
                        </div>';
                })
                ->addColumn('nama_tpu', function ($data) {
                    return '<span class="text-gray-600 fw-semibold d-block fs-7">' . ($data->Tpu ? $data->Tpu->nama : '-') . '</span>';
                })
                ->addColumn('kontak', function ($data) {
                    return '<span class="text-gray-600 fw-semibold d-block fs-7">' . ($data->kontak ?: '-') . '</span>';
                })
                ->addColumn('email', function ($data) {
                    return '<span class="text-gray-600 fw-semibold d-block fs-7">' . ($data->email ?: '-') . '</span>';
                })
                ->addColumn('status', function ($data) {
                    $status   = $data->User ? ($data->User->status == 1 ? 'Aktif' : 'Nonaktif') : 'Nonaktif';
                    $color    = $data->User && $data->User->status == 1 ? 'success' : 'danger';
                    $uuid_enc = Helper::encode($data->uuid);
                    $checked  = $data->User && $data->User->status == 1 ? 'checked' : '';
                    $disabled = ($data->uuid_user == Auth::user()->uuid && Auth::user()->role == 'Admin TPU') ? 'disabled' : '';
                    return '<div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input toggle-status" type="checkbox" data-status="' . $uuid_enc . '" ' . $checked . ' ' . $disabled . '>
                        </div>';
                })
                ->addColumn('aksi', function ($data) use ($auth) {
                    $uuid_enc      = Helper::encode($data->uuid);
                    $edit_url      = route('tpu.petugas.edit', $uuid_enc);
                    $isOwnAccount  = ($data->uuid_user == $auth->uuid && $auth->role == 'Admin TPU');
                    $canEditDelete = ($auth->role == 'Super Admin' || $auth->role == 'Admin' || ($auth->role == 'Admin TPU' && ! $isOwnAccount));

                    if ($canEditDelete) {
                        return '<div class="d-flex justify-content-center">
                                <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="ki-outline ki-pencil fs-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-btn" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                    <i class="ki-outline ki-trash fs-2"></i>
                                </a>
                            </div>';
                    } else {
                        return '<div class="d-flex justify-content-center">
                                <span class="btn btn-icon btn-bg-light btn-sm me-1 disabled" data-bs-toggle="tooltip" title="Edit (Tidak diizinkan)">
                                    <i class="ki-outline ki-pencil fs-2 text-muted"></i>
                                </span>
                                <span class="btn btn-icon btn-bg-light btn-sm disabled" data-bs-toggle="tooltip" title="Hapus (Tidak diizinkan)">
                                    <i class="ki-outline ki-trash fs-2 text-muted"></i>
                                </span>
                            </div>';
                    }
                })
                ->escapeColumns([])
                ->make(true);
        }

        return view('admin.tpu.petugas.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth   = Auth::user();
        $title  = 'Tambah Data Petugas';
        $submit = 'Simpan';
        $tpus   = [];
        $users  = User::whereIn('role', ['Admin TPU', 'Petugas TPU'])->select('uuid', 'username')->get();
        $roles  = ['Admin TPU', 'Petugas TPU'];

        // Jika Super Admin atau Admin, ambil semua TPU
        if ($auth->role === 'Super Admin' || $auth->role === 'Admin') {
            $tpus = TpuDatas::select('uuid', 'nama')->get();
        }

        // Jika Admin TPU, ambil uuid_tpu dari data terkait
        $uuid_tpu = null;
        if ($auth->role === 'Admin TPU') {
            $tpu = TpuDatas::where('uuid_created', $auth->uuid)->first();
            if ($tpu) {
                $uuid_tpu = $tpu->uuid;
            } else {
                alert()->error('Error!', 'Tidak ada TPU yang terkait dengan akun Anda!');
                return redirect()->route('tpu.petugas.index');
            }
        }

        return view('admin.tpu.petugas.create_edit', compact('title', 'submit', 'tpus', 'users', 'uuid_tpu', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = Auth::user();

        // Validasi input
        $request->validate([
            'nip'           => 'required|string|max:50',
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kontak'        => 'required|numeric|digits_between:10,15',
            'email'         => 'required|email|unique:tpu_petugas,email|max:100',
            'jabatan'       => 'required|string|max:100',
            'uuid_tpu'      => 'required|exists:tpu_datas,uuid',
            'role'          => 'required|in:Admin TPU,Petugas TPU',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password'      => [
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

        // Cek hak akses
        if ($auth->role === 'Admin TPU') {
            $tpu = TpuDatas::where('uuid', $request->uuid_tpu)->where('uuid_created', $auth->uuid)->first();
            if (! $tpu) {
                alert()->error('Error!', 'Anda tidak memiliki izin untuk menambahkan petugas pada TPU ini!');
                return back()->withInput($request->all());
            }
        }

        // Data untuk tabel users
        $uuid_user = Str::uuid()->toString();
        $userData  = [
            'uuid'         => $uuid_user,
            'username'     => Str::lower($request->email),
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'uuid_created' => $auth->uuid,
            'uuid_updated' => $auth->uuid,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        // Data untuk tabel tpu_petugas
        $uuid_petugas = Str::uuid()->toString();
        $petugasData  = [
            'uuid'          => $uuid_petugas,
            'uuid_tpu'      => $request->uuid_tpu,
            'uuid_user'     => $uuid_user,
            'nip'           => $request->nip,
            'nama_lengkap'  => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
            'kontak'        => $request->kontak,
            'email'         => Str::lower($request->email),
            'jabatan'       => $request->jabatan,
            'uuid_created'  => $auth->uuid,
            'uuid_updated'  => $auth->uuid,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        // Penanganan foto
        $path = "tpu_petugas/" . $uuid_petugas;
        if ($request->hasFile('foto')) {
            $foto = Helper::UpFoto($request, 'foto', $path);
            if ($foto === '0') {
                alert()->error('Error!', 'Gagal Menyimpan Data, Foto Tidak Sesuai Format!');
                return back()->withInput($request->all());
            }
            $petugasData['foto'] = $foto;
        } else {
            $petugasData['foto'] = 'logo/logo-tangkab.png'; // Default foto
        }

        // Simpan data
        DB::beginTransaction();
        try {
            $saveUser    = User::create($userData);
            $savepetugas = TpuPetugas::create($petugasData);

            if ($saveUser && $savepetugas) {
                // Log aktivitas
                $aktifitas = [
                    'tabel' => ['users', 'tpu_petugas'],
                    'uuid'  => [$uuid_user, $uuid_petugas],
                    'value' => [$userData, $petugasData],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menambahkan Data Petugas: ' . $request->nama_lengkap . ' - ' . $uuid_user,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Menambahkan Data Petugas!');
                return redirect()->route('tpu.petugas.index');
            } else {
                throw new \Exception('Gagal menyimpan data');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing petugas: ' . $e->getMessage());
            alert()->error('Error', 'Gagal Menambahkan Data petugas!');
            return back()->withInput($request->all());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        $auth = Auth::user();
        $uuid = Helper::decode($uuid_enc);
        $data = TpuPetugas::findOrFail($uuid);

        // Cek hak akses
        if ($auth->role !== 'Super Admin' && $auth->role !== 'Admin') {
            $tpu = TpuDatas::where('uuid', $data->uuid_tpu)->where('uuid_created', $auth->uuid)->first();
            if (! $tpu || ($auth->role == 'Admin TPU' && $data->uuid_user == $auth->uuid)) {
                alert()->error('Error!', 'Anda tidak memiliki izin untuk mengedit petugas ini!');
                return redirect()->route('tpu.petugas.index');
            }
        }

        $title  = 'Edit Data Petugas';
        $submit = 'Simpan';
        $tpus   = [];
        $users  = User::whereIn('role', ['Admin TPU', 'Petugas TPU'])->select('uuid', 'username')->get();

        // Jika Super Admin atau Admin, ambil semua TPU
        if ($auth->role === 'Super Admin' || $auth->role === 'Admin') {
            $tpus = TpuDatas::select('uuid', 'nama')->get();
        }

        // Jika Admin TPU, ambil uuid_tpu dari data petugas yang diedit
        $uuid_tpu = null;
        if ($auth->role === 'Admin TPU') {
            $uuid_tpu = $data->uuid_tpu;
        }

        return view('admin.tpu.petugas.create_edit', compact('title', 'submit', 'data', 'tpus', 'users', 'uuid_tpu', 'uuid_enc'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        $auth = Auth::user();
        $uuid = Helper::decode($uuid_enc);
        $data = TpuPetugas::findOrFail($uuid);

        // Cek hak akses
        if ($auth->role !== 'Super Admin' && $auth->role !== 'Admin') {
            $tpu = TpuDatas::where('uuid', $data->uuid_tpu)->where('uuid_created', $auth->uuid)->first();
            if (! $tpu || ($auth->role == 'Admin TPU' && $data->uuid_user == $auth->uuid)) {
                alert()->error('Error!', 'Anda tidak memiliki izin untuk mengedit petugas ini!');
                return redirect()->route('tpu.petugas.index');
            }
        }

        // Validasi input
        $request->validate([
            'nip'           => 'required|string|max:50',
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kontak'        => 'required|numeric|digits_between:10,15',
            'email'         => [
                'required',
                'email',
                'max:100',
                Rule::unique('tpu_petugas', 'email')->ignore($data->uuid, 'uuid'),   // Cek unik email, kecuali untuk data yang sedang diedit
                Rule::unique('users', 'username')->ignore($data->uuid_user, 'uuid'), // Cek unik username di tabel users
            ],
            'jabatan'       => 'required|string|max:100',
            'uuid_tpu'      => 'required|exists:tpu_datas,uuid', // Validasi UUID TPU ada di tabel tpu_datas
            'role'          => 'required|in:Admin TPU,Petugas TPU',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password'      => [
                'nullable',
                'string',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        // Data untuk tabel users
        $uuid_user = $data->uuid_user;
        $userData  = [
            'username'     => Str::lower($request->email), // Update username sesuai email
            'uuid_updated' => $auth->uuid,
        ];
        if ($request->filled('password')) {
            if (Hash::check($request->password, $data->User->password)) {
                alert()->error('Error', 'Password tidak boleh sama dengan sebelumnya!');
                return back()->withInput($request->all());
            }
            $userData['password'] = Hash::make($request->password);
        }
        // Update role jika berbeda
        if ($data->User->role !== $request->role) {
            $userData['role'] = $request->role;
        }

        // Data untuk tabel tpu_petugas
        $petugasData = [
            'uuid_tpu'      => $request->uuid_tpu,
            'nip'           => $request->nip,
            'nama_lengkap'  => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
            'kontak'        => $request->kontak,
            'email'         => Str::lower($request->email),
            'jabatan'       => $request->jabatan,
            'uuid_updated'  => $auth->uuid,
            'updated_at'    => now(),
        ];

        // Penanganan foto
        $path = "tpu_petugas/" . $uuid;
        if ($request->hasFile('foto')) {
            if (! empty($data->foto) && Storage::disk('public')->exists($data->foto)) {
                Storage::disk('public')->delete($data->foto);
            }
            $foto = Helper::UpFoto($request, 'foto', $path);
            if ($foto === '0') {
                alert()->error('Error!', 'Gagal Menyimpan Data, Foto Tidak Sesuai Format!');
                return back();
            }
            $petugasData['foto'] = $foto;
        }

        // Simpan data
        DB::beginTransaction();
        try {
            $saveUser    = User::where('uuid', $uuid_user)->update($userData);
            $savePetugas = $data->update($petugasData);

            if ($saveUser && $savePetugas) {
                // Log aktivitas
                $aktifitas = [
                    'tabel' => ['users', 'tpu_petugas'],
                    'uuid'  => [$uuid_user, $uuid],
                    'value' => [$userData, $petugasData],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Mengubah Data petugas: ' . $request->nama_lengkap . ' - ' . $uuid_user,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Mengubah Data petugas!');
                return redirect()->route('tpu.petugas.index');
            } else {
                throw new \Exception('Gagal menyimpan data');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating petugas: ' . $e->getMessage());
            alert()->error('Error', 'Gagal Mengubah Data petugas!');
            return back()->withInput($request->all());
        }
    }

    /**
     * Update the status of the specified resource.
     */

    public function status(Request $request)
    {
        $auth = Auth::user();
        $uuid = Helper::decode($request->uuid);
        $data = TpuPetugas::findOrFail($uuid);

        // Cek hak akses
        if ($auth->role !== 'Super Admin' && $auth->role !== 'Admin') {
            $tpu = TpuDatas::where('uuid', $data->uuid_tpu)->where('uuid_created', $auth->uuid)->first();
            if (! $tpu || ($auth->role == 'Admin TPU' && $data->uuid_user == $auth->uuid)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Anda tidak memiliki izin untuk mengubah status petugas ini!',
                ], 403);
            }
        }

        $user      = User::whereUuid($data->uuid_user)->firstOrFail();
        $newStatus = $request->has('status') ? $request->status : ($user->status == 1 ? 0 : 1);
        $save      = $user->update(['status' => $newStatus]);

        if ($save) {
            $aktifitas = [
                'tabel' => ['users'],
                'uuid'  => [$user->uuid],
                'value' => ['status' => $newStatus],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Mengubah Status Petugas: ' . $data->nama_lengkap . ' - ' . $newStatus,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            return response()->json([
                'status'     => true,
                'message'    => 'Status petugas berhasil diubah!',
                'new_status' => $newStatus,
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengubah status petugas!',
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // UUID
        $uuid = Helper::decode($request->uuid);
        $data = TpuPetugas::findOrFail($uuid);

        // Cek hak akses
        if ($auth->role !== 'Super Admin' && $auth->role !== 'Admin') {
            $tpu = TpuDatas::where('uuid', $data->uuid_tpu)->where('uuid_created', $auth->uuid)->first();
            if (! $tpu || ($auth->role == 'Admin TPU' && $data->uuid_user == $auth->uuid)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus petugas ini!',
                ], 403);
            }
        }

        // Save
        $save = $data->delete();
        if ($save) {
            // Create log
            $aktifitas = [
                'tabel' => ['tpu_petugas'],
                'uuid'  => [$uuid],
                'value' => [$data->toArray()],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Menghapus Data Petugas: ' . $data->nama_lengkap . ' - ' . $uuid,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            return response()->json([
                'status'  => true,
                'message' => 'Data petugas berhasil dihapus!',
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus data petugas!',
            ], 422);
        }
    }

    /**
     * Bulk delete petugas data
     */
    public function bulkDestroy(Request $request)
    {
        try {
            // Auth
            $auth = Auth::user();

            // Validasi
            $request->validate([
                'uuids'   => 'required|array|min:1',
                'uuids.*' => 'required|string',
            ]);

            $uuids        = $request->uuids;
            $deletedCount = 0;
            $failedItems  = [];

            // Loop through each UUID
            foreach ($uuids as $index => $uuid) {
                try {
                    $data = TpuPetugas::findOrFail($uuid);

                    // Cek hak akses
                    $canDelete = ($auth->role == 'Super Admin' || $auth->role == 'Admin');
                    if (! $canDelete) {
                        $tpu       = TpuDatas::where('uuid', $data->uuid_tpu)->where('uuid_created', $auth->uuid)->first();
                        $canDelete = $tpu && ! ($auth->role == 'Admin TPU' && $data->uuid_user == $auth->uuid);
                    }

                    if (! $canDelete) {
                        $failedItems[] = 'Tidak memiliki izin untuk menghapus: ' . $data->nama_lengkap;
                        continue;
                    }

                    if ($data->delete()) {
                        $deletedCount++;
                        $aktifitas = [
                            'tabel' => ['tpu_petugas'],
                            'uuid'  => [$uuid],
                            'value' => [$data->toArray()],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Menghapus Data Petugas (Bulk): ' . $data->nama_lengkap . ' - ' . $uuid,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = 'Gagal menghapus: ' . $data->nama_lengkap;
                    }
                } catch (\Exception $e) {
                    $failedItems[] = 'Error pada ID ' . $uuids[$index] . ': ' . $e->getMessage();
                    continue;
                }
            }

            $message = 'Berhasil menghapus ' . $deletedCount . ' data petugas';
            if (! empty($failedItems)) {
                $message .= '. Gagal menghapus ' . count($failedItems) . ' item';
            }

            $summaryLog = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Bulk Delete Data Petugas - Berhasil: ' . $deletedCount . ', Gagal: ' . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['tpu_petugas'],
                    'total_request' => count($uuids),
                    'total_deleted' => $deletedCount,
                    'total_failed'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            return response()->json([
                'status'        => true,
                'message'       => $message,
                'deleted_count' => $deletedCount,
                'failed_count'  => count($failedItems),
                'failed_items'  => $failedItems,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Bulk Delete Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk update status petugas
     */
    public function bulkStatus(Request $request)
    {
        try {
            // Auth
            $auth = Auth::user();

            // Validasi
            $request->validate([
                'uuids'   => 'required|array|min:1',
                'uuids.*' => 'required|string',
                'status'  => 'required|in:0,1',
            ]);

            $uuids        = $request->uuids;
            $updatedCount = 0;
            $failedItems  = [];

            // Loop through each UUID
            foreach ($uuids as $index => $uuid) {
                try {
                    $data = TpuPetugas::findOrFail($uuid);

                    // Cek hak akses
                    $canUpdate = ($auth->role == 'Super Admin' || $auth->role == 'Admin');
                    if (! $canUpdate) {
                        $tpu       = TpuDatas::where('uuid', $data->uuid_tpu)->where('uuid_created', $auth->uuid)->first();
                        $canUpdate = $tpu && ! ($auth->role == 'Admin TPU' && $data->uuid_user == $auth->uuid);
                    }

                    if (! $canUpdate) {
                        $failedItems[] = 'Tidak memiliki izin untuk mengubah status: ' . $data->nama_lengkap;
                        continue;
                    }

                    $user = User::whereUuid($data->uuid_user)->firstOrFail();
                    if ($user->update(['status' => $request->status])) {
                        $updatedCount++;
                        $aktifitas = [
                            'tabel' => ['users'],
                            'uuid'  => [$user->uuid],
                            'value' => ['status' => $request->status],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Mengubah Status Petugas (Bulk): ' . $data->nama_lengkap . ' - ' . $request->status,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = 'Gagal mengubah status: ' . $data->nama_lengkap;
                    }
                } catch (\Exception $e) {
                    $failedItems[] = 'Error pada ID ' . $uuids[$index] . ': ' . $e->getMessage();
                    continue;
                }
            }

            $message = 'Berhasil mengubah status ' . $updatedCount . ' petugas';
            if (! empty($failedItems)) {
                $message .= '. Gagal mengubah ' . count($failedItems) . ' item';
            }

            $summaryLog = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Bulk Update Status Petugas - Berhasil: ' . $updatedCount . ', Gagal: ' . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['users'],
                    'total_request' => count($uuids),
                    'total_updated' => $updatedCount,
                    'total_failed'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            return response()->json([
                'status'        => true,
                'message'       => $message,
                'updated_count' => $updatedCount,
                'failed_count'  => count($failedItems),
                'failed_items'  => $failedItems,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Bulk Status Update Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage(),
            ], 500);
        }
    }
}