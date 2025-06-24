<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortalFAQList extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing = false;
    protected $table = "portal_faq_list";
    protected $primaryKey = "uuid";
    protected $keyType = 'string';
    protected $dates = ['deleted_at'];
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

    // Automatically increment the no_urut based on uuid_portal_faq
    protected static function booted()
    {
        static::creating(function ($faqList) {
            // Get the last no_urut for the same uuid_portal_faq
            $lastNoUrut = static::where('uuid_portal_faq', $faqList->uuid_portal_faq)
                ->max('no_urut');

            // Set no_urut to last no_urut + 1
            $faqList->no_urut = $lastNoUrut ? $lastNoUrut + 1 : 1;
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
    public function RelFAQ()
    {
        return $this->belongsTo('App\Models\PortalFAQ', 'uuid_portal_faq', 'uuid')->withTrashed();
    }
}