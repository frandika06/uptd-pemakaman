<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TpuDokumen extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_dokumens";
    protected $primaryKey     = "uuid";
    protected $keyType        = 'string';
    protected $cascadeDeletes = [];
    protected $dates          = ['deleted_at'];
    protected $guarded        = [];
    protected $hidden         = [
        "uuid_created",
        "uuid_updated",
        "uuid_deleted",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    public function Kategori()
    {
        return $this->belongsTo('App\Models\TpuKategoriDokumen', 'kategori', 'uuid')->withTrashed();
    }

    public function Tpu()
    {
        return $this->belongsTo('App\Models\TpuDatas', 'uuid_modul', 'uuid')->where('nama_modul', 'TPU')->withTrashed();
    }

    public function Lahan()
    {
        return $this->belongsTo('App\Models\TpuLahan', 'uuid_modul', 'uuid')->where('nama_modul', 'Lahan')->withTrashed();
    }

    public function Sarpras()
    {
        return $this->belongsTo('App\Models\TpuSarpras', 'uuid_modul', 'uuid')->where('nama_modul', 'Sarpras')->withTrashed();
    }
}