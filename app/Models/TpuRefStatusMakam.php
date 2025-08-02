<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TpuRefStatusMakam extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_ref_status_makam";
    protected $primaryKey     = "uuid";
    protected $keyType        = 'string';
    protected $cascadeDeletes = ['MakamsWithTrashed'];
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

    public function Makams()
    {
        return $this->hasMany('App\Models\TpuMakam', 'status_makam', 'nama');
    }

    public function MakamsWithTrashed()
    {
        return $this->hasMany('App\Models\TpuMakam', 'status_makam', 'nama')->withTrashed();
    }
}