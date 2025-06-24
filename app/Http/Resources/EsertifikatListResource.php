<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class EsertifikatListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'uuid'             => $this->uuid,
            'uuid_esertifikat' => $this->uuid_esertifikat,
            'no_urut'          => $this->no_urut,
            'nama_lengkap'     => $this->nama_lengkap,
            'instansi'         => $this->instansi,
            'tipe_publikasi'   => $this->RelEsertifikat->tipe_publikasi,
            'tipe'             => Str::upper($this->tipe),
            'size'             => Helper::SizeDisk($this->size),
            'views'            => Helper::toDot($this->views),
            'downloads'        => Helper::toDot($this->downloads),
            'author'           => $this->Penulis ?? null,
            'publisher'        => $this->Publisher ?? null,
            'created_at'       => $this->created_at->toDateTimeString(),
            'updated_at'       => $this->updated_at->toDateTimeString(),
        ];
    }
}