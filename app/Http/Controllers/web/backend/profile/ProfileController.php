<?php
namespace App\Http\Controllers\web\backend\profile;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalActor;
use App\Models\TpuPetugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display profile based on user role
     */
    public function index()
    {
        $auth = Auth::user();
        $role = $auth->role;

        // Tentukan view dan data berdasarkan role
        if (in_array($role, ['Admin TPU', 'Petugas TPU'])) {
            return $this->getTPUProfileView($auth);
        } else {
            return $this->getPortalProfileView($auth);
        }
    }

    /**
     * Update profile based on user role
     */
    public function update(Request $request)
    {
        $auth = Auth::user();
        $role = $auth->role;

        // Proses update berdasarkan role
        if (in_array($role, ['Admin TPU', 'Petugas TPU'])) {
            return $this->updateTPUProfile($request, $auth);
        } else {
            return $this->updatePortalProfile($request, $auth);
        }
    }

    /**
     * Get profile view for Portal users (Super Admin, Admin, Editor, etc)
     */
    private function getPortalProfileView($auth)
    {
        $uuid_user = $auth->uuid;
        $data      = PortalActor::whereUuidUser($uuid_user)->firstOrFail();
        $title     = "Edit Profile";
        $submit    = "Simpan";

        return view('admin.setup.profile.create_edit', compact(
            'title',
            'submit',
            'data'
        ));
    }

    /**
     * Get profile view for TPU users (Admin TPU, Petugas TPU)
     */
    private function getTPUProfileView($auth)
    {
        $uuid_user = $auth->uuid;
        $data      = TpuPetugas::whereUuidUser($uuid_user)->with(['Tpu', 'User'])->firstOrFail();

        $title  = "Edit Profile TPU";
        $submit = "Simpan";

        // Get TPU data for reference (read-only in profile)
        $tpu_data = $data->Tpu;

        return view('admin.setup.profile.create_edit_tpu', compact(
            'title',
            'submit',
            'data',
            'tpu_data'
        ));
    }

    /**
     * Update profile for Portal users
     */
    private function updatePortalProfile(Request $request, $auth)
    {
        $uuid_user = $auth->uuid;
        $data      = PortalActor::whereUuidUser($uuid_user)->firstOrFail();

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
        $email   = Str::lower($request->email);
        $value_1 = [
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
        $uuid_actor = Str::uuid();
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
        if ($save_1 || $save_2) {
            // create log
            $aktifitas = [
                "tabel" => ["users", "portal_actor"],
                "uuid"  => [$uuid_user, $uuid_actor],
                "value" => [$value_1, $value_2],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Profile: " . $request->nama_lengkap . " - " . $uuid_user,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            alert()->success('Success', "Berhasil Mengubah Data!");
            return \redirect()->route('prt.apps.profile.index');
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return \back()->withInput($request->all());
        }
    }

    /**
     * Update profile for TPU users
     */
    private function updateTPUProfile(Request $request, $auth)
    {
        $uuid_user = $auth->uuid;
        $data      = TpuPetugas::whereUuidUser($uuid_user)->firstOrFail();

        // Validasi input sesuai kolom tabel tpu_petugas
        $request->validate([
            'nip'           => 'required|string|max:50',
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kontak'        => 'required|numeric|digits_between:10,15',
            'email'         => 'required|email|max:100',
            'jabatan'       => 'required|string|max:100',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $email = Str::lower($request->email);

        // Cek email jika berbeda
        if ($email != $data->email) {
            $cekEmail = TpuPetugas::whereEmail($email)->where('uuid', '!=', $data->uuid)->first();
            if ($cekEmail !== null) {
                alert()->error('Gagal Simpan!', 'Email Sudah Digunakan Oleh User Lain, Mohon Cek Kembali Alamat Email!');
                return back()->withInput($request->all());
            }
        }

        // value untuk update user table
        $value_user = [
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
            if (password_verify($request->password, $data->User->password)) {
                alert()->error('Error', "Password tidak boleh sama dengan sebelumnya!");
                return back()->withInput($request->all());
            }

            $value_user['password'] = bcrypt($request->password);
        }

        // value untuk update tpu_petugas table
        $value_petugas = [
            'nip'           => $request->nip,
            'nama_lengkap'  => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
            'kontak'        => $request->kontak,
            'email'         => $email,
            'jabatan'       => $request->jabatan,
        ];

        // Handle foto upload
        $path = "petugas/" . $data->uuid;
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
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
                return back();
            }
            $value_petugas['foto'] = $img;
        }

        // Handle avatar remove
        if ($request->filled('avatar_remove') && $request->avatar_remove == '1') {
            if (! empty($data->foto) && Storage::disk('public')->exists($data->foto)) {
                Storage::disk('public')->delete($data->foto);
                $avatarPath = str_replace('.', '_avatar.', $data->foto);
                if (Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }
            }
            $value_petugas['foto'] = null;
        }

        // Save data
        $save_user    = User::whereUuid($uuid_user)->update($value_user);
        $save_petugas = $data->update($value_petugas);

        if ($save_user || $save_petugas) {
            // Create log
            $aktifitas = [
                "tabel" => ["users", "tpu_petugas"],
                "uuid"  => [$uuid_user, $data->uuid],
                "value" => [$value_user, $value_petugas],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Profile TPU: " . $request->nama_lengkap . " - " . $uuid_user,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);

            alert()->success('Success', "Berhasil Mengubah Data Profile!");
            return redirect()->route('prt.apps.profile.index');
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return back()->withInput($request->all());
        }
    }
}