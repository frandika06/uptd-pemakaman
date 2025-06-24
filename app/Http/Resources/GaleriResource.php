<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class GaleriResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        $isDetailRequest = $request->routeIs('api.galeri.show');

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
            'jumlah'     => Helper::toDot(\count($this->RelGaleriListApi)),
            'author'     => $this->Penulis ?? null,
            'publisher'  => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Menggunakan array map untuk memformat list_data jika ini adalah request detail
            'list_data'  => $isDetailRequest
            ? $this->RelGaleriListApi->map(function ($item) {
                return [
                    'uuid'        => $item->uuid,
                    'uuid_galeri' => $item->uuid_galeri,
                    'no_urut'     => $item->no_urut,
                    'judul'       => $item->judul,
                    'url'         => Helper::urlImg($item->url),
                    'thumbnails'  => [
                        '_original'  => Helper::urlImg($item->url),
                        '_thumbnail' => Helper::thumbnail($item->url),
                    ],
                    'tipe'        => Str::upper($item->tipe),
                    'size'        => Helper::SizeDisk($item->size),
                    'author'      => $item->Penulis ?? null,
                    'publisher'   => $item->Publisher ?? null,
                    'created_at'  => $item->created_at->toDateTimeString(),
                    'updated_at'  => $item->updated_at->toDateTimeString(),
                ];
            })
            : null,
        ];
    }
}