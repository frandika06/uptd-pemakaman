<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
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
            $role = $request->user()->role;
            // roles array
            $roles = [
                "Super Admin"
            ];
            if (in_array($role, $roles)) {
                return $next($request);
            } else {
                $response = [
                    "status" => false,
                    "message" => "Anda Tidak Memiliki Hak Akses!",
                ];
                return response()->json($response, 403);
            }
        } else {
            // pengguna mengakses route web
            if (Auth::check()) {
                $role = Auth::user()->role;
                // roles array
                $roles = [
                    "Super Admin"
                ];
                if (in_array($role, $roles)) {
                    return $next($request);
                } else {
                    alert()->error('Error', "Anda Tidak Memiliki Hak Akses!");
                    return \redirect()->route('auth.home');
                }
            } else {
                alert()->error('Error', "Anda Tidak Memiliki Hak Akses!");
                return \redirect()->route('auth.home');
            }
        }
    }
}