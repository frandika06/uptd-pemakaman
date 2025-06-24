<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysLogin extends Model
{
    use HasFactory;
    protected $table = "sys_login";
    protected $guarded = [];

    public function RelPortalActor()
    {
        return $this->belongsTo('App\Models\PortalActor', 'uuid', 'uuid_user')->withTrashed();
    }
}
