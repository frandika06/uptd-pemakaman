<?php
namespace App\Http\Controllers\web\auth;

use App\Http\Controllers\Controller;
use App\Models\SysFailedLogin;
use App\Models\SysLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // sudah login atau belum
        if (Auth::check()) {
            return \redirect()->route('auth.home');
        } else {
            return \view('admin.auth.login');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            "email"    => "required|email|max:100",
            "password" => "required|string|max:100",
            'captcha'  => 'required|captcha',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'captcha.required'  => 'Captcha Harus Diisi',
            'captcha.captcha'   => 'Captcha Tidak Valid',
        ]);

        // Gunakan hanya lowercase email untuk pencocokan
        $email    = strtolower($request->input('email'));
        $password = $request->input('password');
        $remember = $request->has('remember_me');

        // Cari user berdasarkan email (pastikan kolom username = email)
        $user = \App\Models\User::where('username', $email)->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            if ($user->status != "1") {
                Auth::logout();

                SysFailedLogin::create([
                    'username' => $email,
                    'ip'       => $request->ip(),
                    'agent'    => $request->userAgent(),
                    'status'   => 'Gagal Login, Akun User Sudah Di Non-Aktifkan!',
                    'device'   => 'web',
                ]);

                alert()->error('Gagal Login!', 'Akun Anda Sudah Di Non-Aktifkan!');
                return back()->withInput($request->only('email'));
            }

            if ($user->trashed()) {
                Auth::logout();

                SysFailedLogin::create([
                    'username' => $email,
                    'ip'       => $request->ip(),
                    'agent'    => $request->userAgent(),
                    'status'   => 'Gagal Login, Akun User Sudah Dihapus!',
                    'device'   => 'web',
                ]);

                alert()->error('Gagal Login!', 'Email/Password Yang Anda Masukkan Salah!');
                return back()->withInput($request->only('email'));
            }

            // Login berhasil
            Auth::login($user, $remember);

            SysLogin::create([
                'uuid_profile' => $user->uuid,
                'ip'           => $request->ip(),
                'agent'        => $request->userAgent(),
                'status'       => 'Akun ' . $email . ' Login ke Aplikasi melalui Website',
                'device'       => 'web',
            ]);

            return redirect()->route('auth.home');
        }

        // Login gagal
        SysFailedLogin::create([
            'username' => $email,
            'ip'       => $request->ip(),
            'agent'    => $request->userAgent(),
            'status'   => 'Gagal Login, Email/Password Salah!',
            'device'   => 'web',
        ]);

        alert()->error('Gagal Login!', 'Email/Password Yang Anda Masukkan Salah!');
        return back()->withInput($request->only('email'));
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
     */
    // logout
    public function logout(Request $request)
    {
        $user = Auth::user();
        //Success Logout
        $SuccessLogout = [
            'uuid_profile' => $user->uuid,
            'ip'           => $request->ip(),
            "agent"        => $request->header('user-agent'),
            "status"       => "Akun " . $user->username . " Logout dari Aplikasi melalui Website",
            "device"       => "web",
        ];
        SysLogin::create($SuccessLogout);
        Auth::logout();
        alert()->success('Success!', 'Anda Berhasil Logout!');
        return \redirect()->route('prt.home.index');
    }

    /*
    |--------------------------------------------------------------------------
    | GOOGLE AUTH
    |--------------------------------------------------------------------------
     */
    // redirectToGoogle
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user     = Socialite::driver('google')->user();
            $finduser = User::where('username', $user->email)->first();
            if ($finduser) {
                Auth::login($finduser);
                alert()->success('Success!', 'Anda Berhasil Login!');
                return \redirect()->route('auth.home');
            } else {
                alert()->error('Gagal Login!', 'Email Tidak Ditemukan!');
                return \redirect()->route('prt.home.index');
            }
        } catch (Exception $e) {
            // dd($e->getMessage());
            alert()->error('Gagal Login!', 'Error: ' . $e->getMessage());
            return \redirect()->route('prt.home.index');
        }
    }
}