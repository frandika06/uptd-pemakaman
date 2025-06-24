<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysResetPassword extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $table = "password_reset_tokens";
    protected $primaryKey = "uuid";
    protected $keyType = 'string';
    protected $guarded = [];
}
