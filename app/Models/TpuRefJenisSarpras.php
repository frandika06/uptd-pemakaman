<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TpuRefJenisSarpras extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_ref_jenis_sarpras";
    protected $primaryKey     = "uuid";
    protected $keyType        = 'string';
    protected $cascadeDeletes = ['SarprasWithTrashed'];
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

    public function Sarpras()
    {
        return $this->hasMany('App\Models\TpuSarpras', 'jenis_sarpras', 'nama');
    }

    public function SarprasWithTrashed()
    {
        return $this->hasMany('App\Models\TpuSarpras', 'jenis_sarpras', 'nama')->withTrashed();
    }
}