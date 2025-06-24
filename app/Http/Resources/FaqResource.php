<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        $isDetailRequest = $request->routeIs('api.faq.show');

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
            'jumlah'     => Helper::toDot(\count($this->RelFAQListApi)),
            'author'     => $this->Penulis ?? null,
            'publisher'  => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Menggunakan array map untuk memformat list_data jika ini adalah request detail
            'list_data'  => $isDetailRequest
            ? $this->RelFAQListApi->map(function ($item) {
                return [
                    'uuid'            => $item->uuid,
                    'uuid_portal_faq' => $item->uuid_portal_faq,
                    'no_urut'         => $item->no_urut,
                    'pertanyaan'      => $item->pertanyaan,
                    'jawaban'         => Helper::updateImageUrls($item->jawaban),
                    'author'          => $item->Penulis ?? null,
                    'publisher'       => $item->Publisher ?? null,
                    'created_at'      => $item->created_at->toDateTimeString(),
                    'updated_at'      => $item->updated_at->toDateTimeString(),
                ];
            })
            : null,
        ];
    }
}