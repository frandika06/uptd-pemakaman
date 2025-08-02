<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TpuPetugas extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_petugas";
    protected $primaryKey     = "uuid";
    protected $keyType        = 'string';
    protected $cascadeDeletes = ['User'];
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

    public function Tpu()
    {
        return $this->belongsTo('App\Models\TpuDatas', 'uuid_tpu', 'uuid')->withTrashed();
    }

    public function User()
    {
        return $this->belongsTo('App\Models\User', 'uuid_user', 'uuid')->withTrashed();
    }
}
