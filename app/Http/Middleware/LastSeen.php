<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            // pengguna mengakses route API
            $user = $request->user();
            if ($user) {
                // cek status aktif
                if ($user->status == "0") {
                    // Revoke
                    $result = $request->user()->token()->revoke();
                    if ($result) {
                        $response = [
                            "status" => false,
                            "message" => "User is Non Active!",
                        ];
                        return response()->json($response, 401);
                    }
                }

                // Update Last Seen
                $tgl_sekarang = \date('Y-m-d H:i:s');
                $expiresAt = \date('Y-m-d H:i:s', strtotime($tgl_sekarang . ' +5 minutes')); // keep online for 1 min
                Cache::put('user-is-online-' . $user->uuid, true, $expiresAt);
                // last seen
                User::where('uuid', $user->uuid)->update(['last_seen' => $tgl_sekarang]);
            }
        } else {
            // pengguna mengakses route web
            if (Auth::check()) {
                // cek status aktif
                $user = Auth::user();
                if ($user->status == "0") {
                    // Revoke
                    $result = Auth::logout();
                    if ($result) {
                        alert()->error('Error', "Akun Anda Sudah Di Non-Aktifkan!");
                        return \redirect()->route('auth.index');
                    }
                }

                // Update Last Seen
                $tgl_sekarang = \date('Y-m-d H:i:s');
                $expiresAt = \date('Y-m-d H:i:s', strtotime($tgl_sekarang . ' +5 minutes')); // keep online for 1 min
                Cache::put('user-is-online-' . $user->uuid, true, $expiresAt);
                // last seen
                User::where('uuid', $user->uuid)->update(['last_seen' => $tgl_sekarang]);
            }
        }
        return $next($request);
    }
}