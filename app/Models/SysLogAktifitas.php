<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysLogAktifitas extends Model
{
    use HasFactory;
    protected $table = "sys_log_aktifitas";
    protected $guarded = [];

    public function RelPortalActor()
    {
        return $this->belongsTo('App\Models\PortalActor', 'uuid', 'uuid_user')->withTrashed();
    }
}
