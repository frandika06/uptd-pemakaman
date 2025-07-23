<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TpuSarpras extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_sarpras";
    protected $primaryKey     = "uuid";
    protected $keyType        = 'string';
    protected $cascadeDeletes = ['DokumensWithTrashed'];
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

    public function Lahan()
    {
        return $this->belongsTo('App\Models\TpuLahan', 'uuid_lahan', 'uuid')->withTrashed();
    }

    public function JenisSarpras()
    {
        return $this->belongsTo('App\Models\TpuRefJenisSarpras', 'jenis_sarpras', 'nama')->withTrashed();
    }

    public function Dokumens()
    {
        return $this->hasMany('App\Models\TpuDokumen', 'uuid_modul', 'uuid')->where('nama_modul', 'Sarpras');
    }

    public function DokumensWithTrashed()
    {
        return $this->hasMany('App\Models\TpuDokumen', 'uuid_modul', 'uuid')->where('nama_modul', 'Sarpras')->withTrashed();
    }
}