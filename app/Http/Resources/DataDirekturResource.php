<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataDirekturResource extends JsonResource
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
            'no_urut' => $this->no_urut,
            'nama_lengkap' => $this->nama_lengkap,
            'foto' => [
                '_original' => Helper::urlImg($this->foto),
                '_avatar' => Helper::pp($this->foto),
            ],
            'jabatan' => $this->jabatan,
            'masa_jabatan' => $this->masa_jabatan,
            'author' => $this->Penulis ?? null,
            'publisher' => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
