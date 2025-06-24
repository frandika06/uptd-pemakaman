<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomCors
{
    public function handle(Request $request, Closure $next): Response
    {
        // 🚫 Skip jika ini adalah permintaan API
        if ($request->is('api/*')) {
            return $next($request);
        }

        // 🔹 Ambil APP_URL dan normalisasi
        $baseAppUrl  = rtrim(config('app.url'), '/');
        $baseAppHost = parse_url($baseAppUrl, PHP_URL_HOST);

        $allowedOrigins = [
            'localhost',
            'portalsma.test',
            '127.0.0.1',
            '10.35.4.60',
            'sma.kemdikbud.go.id',
            'sma.dikdasmen.go.id',
            'sma.kemendikdasmen.go.id',
            'ditsma.codingers.id',
        ];

        $wildcardOrigins = [
            'kemdikbud.go.id',
            'dikdasmen.go.id',
            'kemendikdasmen.go.id',
            'codingers.id',
        ];

        $origin        = $request->headers->get('Origin');
        $allowedOrigin = null;

        // 🎯 IPX internal fetch biasanya tidak membawa Origin, izinkan berdasarkan path
        if (! $origin && $request->is('cms-sma/storage/*')) {
            $allowedOrigin = '*';
        }

        if ($origin) {
            $originHost = parse_url($origin, PHP_URL_HOST);

            if (in_array($originHost, $allowedOrigins) || $originHost === $baseAppHost) {
                $allowedOrigin = $origin;
            } else {
                foreach ($wildcardOrigins as $pattern) {
                    if (str_ends_with($originHost, $pattern)) {
                        $allowedOrigin = $origin;
                        break;
                    }
                }
            }

            // ❌ Jika origin ada tapi tidak diizinkan → blokir (khusus produksi)
            if (! $allowedOrigin && app()->environment('production')) {
                return response()->json(['error' => 'Forbidden: Invalid Origin'], 403);
            }
        }

        // ✅ Tangani Preflight Request (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            return response()->json('', 204, [
                'Access-Control-Allow-Origin'      => $allowedOrigin ?? '*',
                'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Origin, Accept',
                'Access-Control-Allow-Credentials' => 'true',
            ]);
        }

        $response = $next($request);

        // ✅ Tambahkan CORS header jika origin ada dan valid
        if ($allowedOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Origin, Accept');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');

            // File statis
            if ($request->is('flip/*') || $request->is('storage/*') || $request->is('media/*')) {
                $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');
            }
        }

        return $response;
    }
}