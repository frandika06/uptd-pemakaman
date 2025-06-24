<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        $isDetailRequest = $request->routeIs('api.video.show');
        return [
            'uuid' => $this->uuid,
            'sumber' => $this->sumber,
            'judul' => $this->judul,
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi,
            'thumbnails' => [
                '_youtube' => Helper::getYouTubeThumbnailUrl($this->url, "mqdefault"),
                '_original' => Helper::urlImg($this->thumbnails),
                '_thumbnail' => Helper::thumbnail($this->thumbnails),
            ],
            'tanggal' => $this->tanggal,
            'url' => $this->sumber == "YouTube" ? Helper::getYouTubeVideoID($this->url) : Helper::urlImg($this->url),
            'tipe' => Str::upper($this->tipe),
            'size' => Helper::SizeDisk($this->size),
            'views' => Helper::toDot($this->views),
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
