<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TpuDatas extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_datas";
    protected $primaryKey     = "uuid";
    protected $keyType        = 'string';
    protected $cascadeDeletes = ['LahansWithTrashed', 'PetugasWithTrashed', 'StatistikKapasitasWithTrashed', 'DokumensWithTrashed'];
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

    public function Lahans()
    {
        return $this->hasMany('App\Models\TpuLahan', 'uuid_tpu', 'uuid');
    }

    public function LahansWithTrashed()
    {
        return $this->hasMany('App\Models\TpuLahan', 'uuid_tpu', 'uuid')->withTrashed();
    }

    public function Petugas()
    {
        return $this->hasMany('App\Models\TpuPetugas', 'uuid_tpu', 'uuid');
    }

    public function PetugasWithTrashed()
    {
        return $this->hasMany('App\Models\TpuPetugas', 'uuid_tpu', 'uuid')->withTrashed();
    }

    public function StatistikKapasitas()
    {
        return $this->hasMany('App\Models\TpuStatistikKapasitas', 'uuid_tpu', 'uuid');
    }

    public function StatistikKapasitasWithTrashed()
    {
        return $this->hasMany('App\Models\TpuStatistikKapasitas', 'uuid_tpu', 'uuid')->withTrashed();
    }

    public function Dokumens()
    {
        return $this->hasMany('App\Models\TpuDokumen', 'uuid_modul', 'uuid')->where('nama_modul', 'TPU');
    }

    public function DokumensWithTrashed()
    {
        return $this->hasMany('App\Models\TpuDokumen', 'uuid_modul', 'uuid')->where('nama_modul', 'TPU')->withTrashed();
    }
}