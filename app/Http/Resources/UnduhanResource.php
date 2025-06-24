<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UnduhanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        $isDetailRequest = $request->routeIs('api.unduhan.show');

        return [
            'uuid' => $this->uuid,
            'sumber' => $this->sumber,
            'judul' => $this->judul,
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi,
            'thumbnails' => [
                '_original' => Helper::urlImg($this->thumbnails),
                '_thumbnail' => Helper::thumbnailUnduhan($this->thumbnails, $this->tipe),
            ],
            'tanggal' => $this->tanggal,
            'url' => $this->sumber === "Link" ? $this->url : "",
            'tipe' => Str::upper($this->tipe),
            'size' => Helper::SizeDisk($this->size),
            'views' => Helper::toDot($this->views),
            'downloads' => Helper::toDot($this->downloads),
            'tipe_publikasi' => $this->tipe_publikasi,
            'kategori' => $this->kategori,
            'author' => $this->Penulis ?? null,
            'publisher' => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Tambahkan post hanya jika ini adalah request detail
            'post' => $isDetailRequest ? Helper::updateImageUrls($this->post ?? '') : null,
        ];
    }
}