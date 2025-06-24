<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroSetupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'uuid'               => $this->uuid,
            "nama_pengaturan"    => $this->nama_pengaturan,
            "heading_1"          => $this->heading_1,
            "heading_2"          => $this->heading_2,
            "deskripsi"          => $this->deskripsi,
            "judul_tombol_aksi"  => $this->judul_tombol_aksi,
            "url_tombol_aksi"    => $this->url_tombol_aksi,
            "judul_tombol_video" => $this->judul_tombol_video,
            "url_tombol_video"   => $this->url_tombol_video,
            "background_gambar"  => Helper::backgroundGambarHero($this->nama_pengaturan, $this->background_gambar),
            "background_video"   => Helper::backgroundVideoHero($this->background_video),
            "illustration"       => Helper::illustrationHero($this->nama_pengaturan, $this->illustration),
            'author'             => $this->Penulis ?? null,
            'publisher'          => $this->Publisher ?? null,
            'created_at'         => $this->created_at->toDateTimeString(),
            'updated_at'         => $this->updated_at->toDateTimeString(),
        ];
    }
}