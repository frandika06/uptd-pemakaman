<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppsCounter extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $table = "apps_counter";
    protected $primaryKey = "uuid";
    protected $keyType = 'string';
    protected $guarded = [];
    protected $hidden = [
        "created_at",
        "updated_at",
    ];
}
