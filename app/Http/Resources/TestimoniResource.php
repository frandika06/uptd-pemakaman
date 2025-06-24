<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimoniResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        // $isDetailRequest = $request->routeIs('api.testimoni.show');

        return [
            'uuid' => $this->uuid,
            'judul' => $this->judul,
            'slug' => $this->slug,
            'nama_lengkap' => $this,
            'jabatan' => $this->jabatan,
            'ringkasan' => $this->ringkasan,
            'foto' => [
                '_original' => Helper::urlImg($this->foto),
                '_avatar' => Helper::pp($this->foto),
            ],
            'views' => Helper::toDot($this->views),
            'kategori' => $this->kategori,
            'author' => $this->Penulis ?? null,
            'publisher' => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Tambahkan detail hanya jika ini adalah request detail
            // 'detail' => $isDetailRequest ? Helper::updateImageUrls($this->detail ?? '') : null,
        ];
    }
}
