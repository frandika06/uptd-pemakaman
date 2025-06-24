<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TanosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        $isDetailRequest = $request->routeIs('api.tanos.show');

        return [
            'uuid'                 => $this->uuid,
            'judul'                => $this->judul,
            'slug'                 => $this->slug,
            'thumbnails'           => [
                '_original'  => Helper::urlImg($this->thumbnails),
                '_thumbnail' => Helper::thumbnail($this->thumbnails),
            ],
            'bidang_lomba'         => $this->RelKategori->nama,
            'kategori_penghargaan' => $this->RelKategoriSub->nama,
            'nama_group'           => $this->nama_group,
            'tanggal'              => $this->tanggal,
            'tahun'                => date('Y', strtotime($this->tanggal)),
            'sumber'               => $this->sumber,
            'url'                  => Helper::urlImg($this->url),
            'tipe'                 => Str::upper($this->tipe),
            'size'                 => Helper::SizeDisk($this->size),
            'views'                => Helper::toDot($this->views),
            'jumlah'               => Helper::toDot(\count($this->RelTanosListApi)),
            'author'               => $this->Penulis ?? null,
            'publisher'            => $this->Publisher ?? null,
            'created_at'           => $this->created_at->toDateTimeString(),
            'updated_at'           => $this->updated_at->toDateTimeString(),

            // Menggunakan array map untuk memformat list_data jika ini adalah request detail
            'list_data'            => $isDetailRequest
            ? $this->RelTanosListApi->map(function ($item) {
                return [
                    'uuid'         => $item->uuid,
                    'uuid_tanos'   => $item->uuid_tanos,
                    'no_urut'      => $item->no_urut,
                    'nama_anggota' => $item->nama_anggota,
                    'asal_sekolah' => $item->asal_sekolah,
                    'author'       => $item->Penulis ?? null,
                    'publisher'    => $item->Publisher ?? null,
                    'created_at'   => $item->created_at->toDateTimeString(),
                    'updated_at'   => $item->updated_at->toDateTimeString(),
                ];
            })
            : null,

            // Tambahkan detail hanya jika ini adalah request detail
            'deskripsi'            => $isDetailRequest ? Helper::updateImageUrls($this->deskripsi ?? '') : null,
        ];
    }
}