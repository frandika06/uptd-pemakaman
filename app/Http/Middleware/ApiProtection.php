<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiProtection
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 🔹 API Key yang diizinkan
        $allowedToken = env('API_SECRET_KEY', 'GQGR6UFFH7iDBtgKeKAeZBTGKGbOr5TydQ9FWXQoVio9Ja21fOAQJ4R4CuxjYVTR');

        // 🔹 Daftar domain frontend yang diizinkan tanpa API Key
        $allowedOrigins = [
            'http://localhost',
            'http://127.0.0.1',
        ];

        // 🔹 Wildcard domain yang diizinkan (*.kemdikbud.go.id, *.dikdasmen.go.id, *.kemendikdasmen.go.id)
        $wildcardOrigins = [
            'https://*.codingers.id',
        ];

        // 🔹 Ambil informasi dari request
        $origin        = $request->header('Origin');
        $referrer      = $request->header('Referer');
        $path          = $request->path();
        $isValidOrigin = false;

        // ✅ **Tambahkan log untuk debugging origin**
        // \Log::info("🔥 API Request Origin: " . ($origin ?? 'null') . " - Path: " . $path);

        // ✅ **Pengecualian API tertentu yang boleh diakses langsung tanpa proteksi ketat**
        $isExemptedApi = preg_match('/^api\/(esertifikat|unduhan|galeri|infografis|ebook|emagazine)\//', $path);

        // ✅ **Jika API masuk pengecualian, langsung lanjutkan request tanpa validasi ketat**
        if ($isExemptedApi) {
            return $next($request);
        }

        // ✅ **Pastikan request berasal dari XMLHttpRequest (AJAX)**
        if (! $request->header('X-Requested-With') || $request->header('X-Requested-With') !== 'XMLHttpRequest') {
            \Log::warning("🚨 Unauthorized API Access: Invalid X-Requested-With", [
                'path'       => $path,
                'origin'     => $origin,
                'ip'         => $request->ip(),
                'user-agent' => $request->header('User-Agent'),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // ✅ **Cek apakah Origin termasuk domain yang diizinkan tanpa API Key**
        if ($origin) {
            if (in_array($origin, $allowedOrigins)) {
                $isValidOrigin = true;
            } else {
                foreach ($wildcardOrigins as $pattern) {
                    $regex = '/^' . str_replace(['.', '*'], ['\.', '.*'], $pattern) . '$/';
                    if (preg_match($regex, $origin)) {
                        $isValidOrigin = true;
                        break;
                    }
                }
            }
        }

        // ✅ **Proteksi tambahan berdasarkan Referrer**
        if ($isValidOrigin && $referrer && ! str_starts_with($referrer, $origin)) {
            \Log::warning("🚨 Unauthorized API Access: Invalid Referrer", [
                'path'       => $path,
                'origin'     => $origin,
                'referrer'   => $referrer,
                'ip'         => $request->ip(),
                'user-agent' => $request->header('User-Agent'),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // ✅ **Jika request bukan dari frontend resmi, wajib pakai API Key**
        $authHeader = $request->header('Authorization');
        if (! $isValidOrigin && (! $authHeader || $authHeader !== "Bearer " . $allowedToken)) {
            \Log::warning("🚨 Unauthorized API Access: Missing or Invalid API Key", [
                'path'       => $path,
                'origin'     => $origin,
                'ip'         => $request->ip(),
                'user-agent' => $request->header('User-Agent'),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // ✅ **Tambahkan CORS hanya jika Origin valid**
        $response = $next($request);

        if ($isValidOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Vary', 'Origin');
        }

        // ✅ **Jika request adalah OPTIONS, langsung return 204 (tanpa API Key)**
        if ($request->isMethod('OPTIONS')) {
            return response('', 204);
        }

        // ✅ **Tambahkan proteksi tambahan untuk file statis (PDF, Gambar, Video)**
        if ($request->is('flip/*') || $request->is('storage/*') || $request->is('media/*')) {
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');
        }

        return $response;
    }
}