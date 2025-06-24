<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'tanggal' => $this->tanggal,
            'warna_text' => $this->warna_text,
            'url' => $this->url,
            'thumbnails' => [
                '_original' => Helper::urlImg($this->thumbnails),
                '_thumbnail' => Helper::thumbnail($this->thumbnails),
            ],
            'kategori' => $this->kategori,
            'author' => $this->Penulis ?? null,
            'publisher' => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
