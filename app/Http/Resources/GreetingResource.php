<?php
namespace App\Http\Resources;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GreetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'       => $this->uuid,
            'penulis'    => $this->penulis,
            'kutipan'    => $this->kutipan,
            'foto'       => [
                '_original' => Helper::urlImg($this->foto),
                '_avatar'   => Helper::pp($this->foto),
            ],
            'author'     => $this->Penulis ?? null,
            'publisher'  => $this->Publisher ?? null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
