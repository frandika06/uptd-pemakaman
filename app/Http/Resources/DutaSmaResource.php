<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class DutaSmaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Tentukan apakah ini adalah request koleksi atau detail berdasarkan route
        $isDetailRequest = $request->routeIs('api.duta-sma.show');

        return [
            'uuid'            => $this->uuid,
            'judul'           => $this->judul,
            'slug'            => $this->slug,
            'thumbnails'      => [
                '_original'  => Helper::urlImg($this->thumbnails),
                '_thumbnail' => Helper::thumbnail($this->thumbnails),
            ],
            'avatar_1'        => [
                '_original' => Helper::urlImg($this->avatar_1),
                '_avatar'   => Helper::pp($this->avatar_1),
            ],
            'avatar_2'        => [
                '_original' => Helper::urlImg($this->avatar_2),
                '_avatar'   => Helper::pp($this->avatar_2),
            ],
            'tanggal'         => $this->created_at,
            "nama_peserta_1"  => $this->nama_peserta_1,
            "nama_peserta_2"  => $this->nama_peserta_2,
            "nama_sekolah_1"  => $this->nama_sekolah_1,
            "nama_sekolah_2"  => $this->nama_sekolah_2,
            "predikat_1"      => $this->predikat_1,
            "predikat_2"      => $this->predikat_2,
            "tahun"           => $this->tahun,
            'views'           => Helper::toDot($this->views),
            'kategori'        => $this->kategori,
            "link_ig_1"       => $this->link_ig_1,
            "link_ig_2"       => $this->link_ig_2,
            "link_fb_1"       => $this->link_fb_1,
            "link_fb_2"       => $this->link_fb_2,
            "link_tiktok_1"   => $this->link_tiktok_1,
            "link_tiktok_2"   => $this->link_tiktok_2,
            "link_twitter_1"  => $this->link_twitter_1,
            "link_twitter_2"  => $this->link_twitter_2,
            "link_youtube_1"  => $this->link_youtube_1,
            "link_youtube_2"  => $this->link_youtube_2,
            "link_linkedin_1" => $this->link_linkedin_1,
            "link_linkedin_2" => $this->link_linkedin_2,
            'author'          => $this->Penulis ?? null,
            'publisher'       => $this->Publisher ?? null,
            'created_at'      => $this->created_at->toDateTimeString(),
            'updated_at'      => $this->updated_at->toDateTimeString(),

            // Tambahkan deskripsi hanya jika ini adalah request detail
            'deskripsi_1'     => $isDetailRequest ? Helper::updateImageUrls($this->deskripsi_1 ?? '') : null,
            'deskripsi_2'     => $isDetailRequest ? Helper::updateImageUrls($this->deskripsi_2 ?? '') : null,
        ];

    }
}
