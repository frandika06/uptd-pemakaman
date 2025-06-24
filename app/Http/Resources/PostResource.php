<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        $isDetailRequest = $request->routeIs('api.postingan.show');

        return [
            'uuid'       => $this->uuid,
            'judul'      => $this->judul,
            'slug'       => $this->slug,
            'deskripsi'  => $this->deskripsi,
            'thumbnails' => [
                '_original'  => Helper::urlImg($this->thumbnails),
                '_thumbnail' => Helper::thumbnail($this->thumbnails),
            ],
            'tanggal'    => $this->tanggal,
            'views'      => Helper::toDot($this->views),
            'kategori'   => $this->kategori,
            'author'     => $this->Penulis ?? null,
            'publisher'  => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Tambahkan post hanya jika ini adalah request detail
            'post'       => $isDetailRequest ? Helper::updateImageUrls($this->post ?? '') : null,
        ];
    }
}