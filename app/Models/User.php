<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "uuid",
        "username",
        "password",
        "two_factor_secret",
        "remember_token",
        "role",
        "status",
        "socialite",
        "is_api_user",
        "uuid_created",
        "uuid_updated",
        "uuid_deleted",
        "last_seen",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        "uuid_created",
        "uuid_updated",
        "uuid_deleted",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

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

    // Buat secret untuk 2FA
    public function generateTwoFactorSecret()
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    public function getTwoFactorQRCodeUrl()
    {
        $google2fa = new Google2FA();
        return $google2fa->getQRCodeUrl(
            config('app.name'),     // Nama aplikasi Anda
            $this->username,        // Identitas pengguna
            $this->two_factor_secret// Secret yang sudah di-generate
        );
    }

    public function RelPortalActor()
    {
        return $this->belongsTo('App\Models\PortalActor', 'uuid', 'uuid_user')->withTrashed();
    }

    public function RelPertugasTpu()
    {
        return $this->belongsTo('App\Models\TpuPetugas', 'uuid', 'uuid_user')->withTrashed();
    }
}