<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TpuStatistikKapasitas extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_statistik_kapasitas";
    protected $primaryKey     = "id";
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

    public function Tpu()
    {
        return $this->belongsTo('App\Models\TpuDatas', 'uuid_tpu', 'uuid')->withTrashed();
    }
}