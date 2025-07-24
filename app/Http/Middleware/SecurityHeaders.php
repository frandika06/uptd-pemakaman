<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HTTP Strict Transport Security (HSTS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // ✅ CSP diatur agar YouTube & CKEditor tetap bisa embed
        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.ckeditor.com https://trusted-scripts.com https://cdn.datatables.net https://unpkg.com https://speedcf.cloudflareaccess.com https://nominatim.openstreetmap.org; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.datatables.net https://unpkg.com https://speedcf.cloudflareaccess.com; "
            . "font-src 'self' https://fonts.gstatic.com data:; "
            . "img-src 'self' data: blob: https: *.tile.openstreetmap.org; "
            . "connect-src 'self' blob: http://127.0.0.1 http://upt-pemakaman.test https://cdn.datatables.net https://cdn.ckeditor.com https://nominatim.openstreetmap.org *.tile.openstreetmap.org; "
            . "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com; "
            . "frame-ancestors 'self'; "
            . "object-src 'none'; "
            . "base-uri 'self'; "
            . "form-action 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        // Anti Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-sniffing protection
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=(), usb=()");

        // ❌ Hapus atau nonaktifkan COEP agar YouTube iframe bisa ditampilkan
        // $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');

        // Expect-CT
        $response->headers->set('Expect-CT', 'max-age=86400, enforce');

        // Flash / Silverlight cross-domain block
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        return $response;
    }
}
