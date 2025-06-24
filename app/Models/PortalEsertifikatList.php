<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortalEsertifikatList extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $incrementing = false;
    protected $table = "portal_esertifikat_list";
    protected $primaryKey = "uuid";
    protected $keyType = 'string';
    protected $guarded = [];
    protected $hidden = [
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

    // Automatically increment the no_urut based on uuid_esertifikat
    protected static function booted()
    {
        static::creating(function ($galeriList) {
            // Get the last no_urut for the same uuid_esertifikat
            $lastNoUrut = static::where('uuid_esertifikat', $galeriList->uuid_esertifikat)
                ->max('no_urut');

            // Set no_urut to last no_urut + 1
            $galeriList->no_urut = $lastNoUrut ? $lastNoUrut + 1 : 1;
        });
    }

    public function Penulis()
    {
        return $this->belongsTo('App\Models\PortalActor', 'uuid_created', 'uuid_user')->withTrashed();
    }
    public function Publisher()
    {
        return $this->belongsTo('App\Models\PortalActor', 'uuid_updated', 'uuid_user')->withTrashed();
    }
    public function RelEsertifikat()
    {
        return $this->belongsTo('App\Models\PortalEsertifikat', 'uuid_esertifikat', 'uuid')->withTrashed();
    }
}