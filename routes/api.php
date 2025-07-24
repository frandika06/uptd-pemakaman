<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::get('/proxy-nominatim', function (Request $request) {
    $query = $request->input('q');
    $url   = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($query) . "&bounded=1&viewbox=106.0,-6.5,107.0,-6.0&limit=5";

    try {
        $response = Http::withHeaders(['User-Agent' => 'UPTD-Pemakaman-Tangerang/1.0'])->timeout(10)->get($url);
        \Log::info('Nominatim Request URL: ' . $url);
        \Log::info('Nominatim Response Status: ' . $response->status());
        \Log::info('Nominatim Response Body: ' . $response->body());

        if ($response->successful()) {
            $data = $response->json();
            if (empty($data)) {
                return response()->json(['error' => 'Tidak ada hasil ditemukan untuk pencarian ini.'], 404);
            }
            return $data;
        } else {
            return response()->json(['error' => 'Gagal mengambil data dari Nominatim: Status ' . $response->status()], $response->status());
        }
    } catch (\Exception $e) {
        \Log::error('Nominatim Exception: ' . $e->getMessage());
        return response()->json(['error' => 'Error koneksi ke Nominatim: ' . $e->getMessage()], 500);
    }
})->name('api.proxy-nominatim');
