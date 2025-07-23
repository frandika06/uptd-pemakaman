<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TpuLahan extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_lahans";
    protected $primaryKey     = "uuid";
    protected $keyType        = 'string';
    protected $cascadeDeletes = ['MakamsWithTrashed', 'SarprasWithTrashed', 'DokumensWithTrashed'];
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

    public function Tpu()
    {
        return $this->belongsTo('App\Models\TpuDatas', 'uuid_tpu', 'uuid')->withTrashed();
    }

    public function Makams()
    {
        return $this->hasMany('App\Models\TpuMakam', 'uuid_lahan', 'uuid');
    }

    public function MakamsWithTrashed()
    {
        return $this->hasMany('App\Models\TpuMakam', 'uuid_lahan', 'uuid')->withTrashed();
    }

    public function Sarpras()
    {
        return $this->hasMany('App\Models\TpuSarpras', 'uuid_lahan', 'uuid');
    }

    public function SarprasWithTrashed()
    {
        return $this->hasMany('App\Models\TpuSarpras', 'uuid_lahan', 'uuid')->withTrashed();
    }

    public function Dokumens()
    {
        return $this->hasMany('App\Models\TpuDokumen', 'uuid_modul', 'uuid')->where('nama_modul', 'Lahan');
    }

    public function DokumensWithTrashed()
    {
        return $this->hasMany('App\Models\TpuDokumen', 'uuid_modul', 'uuid')->where('nama_modul', 'Lahan')->withTrashed();
    }
}
