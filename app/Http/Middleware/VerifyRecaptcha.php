<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class VerifyRecaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $recaptchaResponse = $request->input('captcha');
        $secretKey = env('RECAPTCHA_SECRET_KEY');

        if (!$recaptchaResponse) {
            return response()->json([
                'status' => false,
                'message' => 'Middleware: CAPTCHA tidak valid.',
            ], 422);
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
        ]);

        $responseData = $response->json();

        if (!$responseData['success']) {
            return response()->json([
                'status' => false,
                'message' => 'Middleware: CAPTCHA tidak valid.',
            ], 422);
        }

        return $next($request);
    }
}