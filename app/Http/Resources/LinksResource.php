<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class LinksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'no_urut' => $this->no_urut,
            'judul' => $this->judul,
            'url' => $this->url,
            'kategori' => $this->kategori,
            'author' => $this->Penulis ?? null,
            'publisher' => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
