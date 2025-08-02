<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    // Event untuk mengisi kolom uuid_created, uuid_updated, dan uuid_deleted
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $auth = Auth::user();
            if ($auth) {
                $model->uuid_created = $auth->uuid;
                $model->uuid_updated = $auth->uuid;
            }
        });

        static::updating(function ($model) {
            $auth = Auth::user();
            if ($auth) {
                $model->uuid_updated = $auth->uuid;
            }
        });

        static::deleting(function ($model) {
            $auth = Auth::user();
            if ($auth) {
                $model->uuid_deleted = $auth->uuid;
                DB::table($model->getTable())
                    ->where($model->getKeyName(), $model->getKey())
                    ->update(['uuid_deleted' => $model->uuid_deleted]);
            }
        });
    }

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
