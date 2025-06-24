<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class EsertifikatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        $isDetailRequest = $request->routeIs('api.esertifikat.search');

        if ($isDetailRequest) {
            return [
                'uuid'           => $this->uuid,
                'judul'          => $this->judul,
                'slug'           => $this->slug,
                'deskripsi'      => $this->deskripsi,
                'thumbnails'     => [
                    '_original'  => Helper::urlImg($this->thumbnails),
                    '_thumbnail' => Helper::thumbnail($this->thumbnails),
                ],
                'tanggal'        => $this->tanggal,
                'tipe_publikasi' => $this->tipe_publikasi,
                'kategori'       => $this->kategori,
                'jumlah'         => Helper::toDot($this->GetJumlahEsertifikat()),
                'created_at'     => $this->created_at->toDateTimeString(),
                'updated_at'     => $this->updated_at->toDateTimeString(),

                // Menggunakan array map untuk memformat list_data jika ini adalah request detail
                'list_data'      => $isDetailRequest
                ? $this->RelEsertifikatList->map(function ($item) {
                    return [
                        'uuid'             => $item->uuid,
                        'uuid_esertifikat' => $item->uuid_esertifikat,
                        'no_urut'          => $item->no_urut,
                        'nama_lengkap'     => $item,
                        'instansi'         => $item->instansi,
                        'url'              => Helper::urlImg($item->url),
                        'tipe'             => Str::upper($item->tipe),
                        'size'             => Helper::SizeDisk($item->size),
                        'views'            => Helper::toDot($item->views),
                        'downloads'        => Helper::toDot($item->downloads),
                        'author'           => $item->Penulis ?? null,
                        'publisher'        => $item->Publisher ?? null,
                        'created_at'       => $item->created_at->toDateTimeString(),
                        'updated_at'       => $item->updated_at->toDateTimeString(),
                    ];
                })
                : null,
            ];
        } else {
            return [
                'uuid'           => $this->uuid,
                'judul'          => $this->judul,
                'slug'           => $this->slug,
                'deskripsi'      => $this->deskripsi,
                'thumbnails'     => [
                    '_original'  => Helper::urlImg($this->thumbnails),
                    '_thumbnail' => Helper::thumbnail($this->thumbnails),
                ],
                'tanggal'        => $this->tanggal,
                'tipe_publikasi' => $this->tipe_publikasi,
                'kategori'       => $this->kategori,
                'jumlah'         => Helper::toDot($this->GetJumlahEsertifikat()),
                'author'         => $this->Penulis ?? null,
                'publisher'      => $this->Publisher ?? null,
                'created_at'     => $this->created_at->toDateTimeString(),
                'updated_at'     => $this->updated_at->toDateTimeString(),
            ];
        }
    }
}