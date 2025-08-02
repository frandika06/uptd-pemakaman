<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TpuMakam extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_makams";
    protected $primaryKey     = "uuid";
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

    // Auto calculate luas_m2 when panjang_m or lebar_m changes
    public function setLuasM2Attribute($value)
    {
        $this->attributes['luas_m2'] = $value;
    }

    public function getPanjangMAttribute($value)
    {
        return number_format($value, 2);
    }

    public function getLebarMAttribute($value)
    {
        return number_format($value, 2);
    }

    public function getLuasM2Attribute($value)
    {
        return number_format($value, 2);
    }

    // Progress bar untuk kapasitas
    public function getKapasitasProgressAttribute()
    {
        if ($this->kapasitas == 0) {
            return '<span class="text-muted">Tidak Ada Kapasitas</span>';
        }

        $percentage    = round(($this->makam_terisi / $this->kapasitas) * 100, 1);
        $progressClass = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');

        return '
            <div class="d-flex align-items-center">
                <div class="progress h-6px w-100 me-2">
                    <div class="progress-bar bg-' . $progressClass . '" style="width: ' . $percentage . '%"></div>
                </div>
                <span class="text-muted fs-7">' . $this->makam_terisi . '/' . $this->kapasitas . '</span>
            </div>
            <div class="fs-8 text-muted">' . $percentage . '% terisi</div>
        ';
    }

    // Badge untuk kategori makam
    public function getKategoriMakamBadgeAttribute()
    {
        $badges = [
            'muslim'     => '<span class="badge badge-light-primary">Muslim</span>',
            'non_muslim' => '<span class="badge badge-light-warning">Non Muslim</span>',
        ];

        return $badges[$this->kategori_makam] ?? '<span class="badge badge-light-secondary">Undefined</span>';
    }

    // Mutator untuk auto-calculate sisa kapasitas
    public function setMakamTerisiAttribute($value)
    {
        $this->attributes['makam_terisi'] = $value;

        // Auto calculate sisa kapasitas
        if (isset($this->attributes['kapasitas'])) {
            $this->attributes['sisa_kapasitas'] = $this->attributes['kapasitas'] - $value;
        }
    }

    public function setKapasitasAttribute($value)
    {
        $this->attributes['kapasitas'] = $value;

        // Auto calculate sisa kapasitas
        if (isset($this->attributes['makam_terisi'])) {
            $this->attributes['sisa_kapasitas'] = $value - $this->attributes['makam_terisi'];
        }
    }

    // Relations
    public function Lahan()
    {
        return $this->belongsTo('App\Models\TpuLahan', 'uuid_lahan', 'uuid')->withTrashed();
    }

    public function StatusMakam()
    {
        return $this->belongsTo('App\Models\TpuRefStatusMakam', 'status_makam', 'nama')->withTrashed();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status_makam', 'Tersedia');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status_makam', 'Terpakai');
    }

    public function scopeHasCapacity($query)
    {
        return $query->where('sisa_kapasitas', '>', 0);
    }

    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori_makam', $kategori);
    }

    // Helper method untuk menghitung kapasitas berdasarkan jenis TPU
    public static function calculateKapasitasByTpu($lahan, $luas_makam, $kategori_makam)
    {
        if (! $lahan || ! $lahan->Tpu || $luas_makam <= 0) {
            return 0;
        }

        $tpu          = $lahan->Tpu;
        $luas_lahan   = $lahan->luas_m2;
        $luas_efektif = max(0, $luas_lahan - 200); // Minimal 200 m² untuk sarana prasarana

        if ($luas_efektif <= 0 || $luas_makam <= 0) {
            return 0;
        }

        // Perhitungan kapasitas dasar
        $kapasitas_dasar = floor($luas_efektif / $luas_makam);

        // Penyesuaian berdasarkan jenis TPU dan kategori makam
        switch ($tpu->jenis_tpu) {
            case 'muslim':
                $kapasitas = $kategori_makam == 'muslim' ? $kapasitas_dasar : 0;
                break;
            case 'non_muslim':
                $kapasitas = $kategori_makam == 'non_muslim' ? $kapasitas_dasar : 0;
                break;
            case 'gabungan':
                // Untuk TPU gabungan, bagi berdasarkan persentase
                // 70% Muslim, 30% Non Muslim (sesuaikan dengan regulasi)
                if ($kategori_makam == 'muslim') {
                    $kapasitas = floor($kapasitas_dasar * 0.7);
                } else {
                    $kapasitas = floor($kapasitas_dasar * 0.3);
                }
                break;
            default:
                $kapasitas = 0;
        }

        return max(0, $kapasitas);
    }

    // Method untuk mengecek apakah lahan bisa menambah makam dengan kategori tertentu
    public static function canAddMakamToLahan($uuid_lahan, $kategori_makam)
    {
        $lahan = \App\Models\TpuLahan::with('Tpu')->find($uuid_lahan);
        if (! $lahan || ! $lahan->Tpu) {
            return false;
        }

        $existing_makam = self::where('uuid_lahan', $uuid_lahan)->count();

        // Aturan: maksimal 2 makam per lahan
        if ($existing_makam >= 2) {
            return false;
        }

        // Untuk jenis TPU selain gabungan, hanya boleh 1 makam
        if ($lahan->Tpu->jenis_tpu !== 'gabungan' && $existing_makam >= 1) {
            return false;
        }

        // Untuk TPU gabungan, cek apakah kategori sudah ada
        if ($lahan->Tpu->jenis_tpu === 'gabungan') {
            $existing_kategori = self::where('uuid_lahan', $uuid_lahan)
                ->where('kategori_makam', $kategori_makam)
                ->exists();
            return ! $existing_kategori;
        }

        // Cek kesesuaian kategori dengan jenis TPU
        if ($lahan->Tpu->jenis_tpu === 'muslim' && $kategori_makam !== 'muslim') {
            return false;
        }
        if ($lahan->Tpu->jenis_tpu === 'non_muslim' && $kategori_makam !== 'non_muslim') {
            return false;
        }

        return true;
    }

    // Method untuk mendapatkan kategori yang masih bisa ditambahkan untuk lahan tertentu
    public static function getAvailableKategoriForLahan($uuid_lahan)
    {
        $lahan = \App\Models\TpuLahan::with('Tpu')->find($uuid_lahan);
        if (! $lahan || ! $lahan->Tpu) {
            return [];
        }

        $existing_makam = self::where('uuid_lahan', $uuid_lahan)->count();

        // Jika sudah ada 2 makam, tidak bisa tambah lagi
        if ($existing_makam >= 2) {
            return [];
        }

        switch ($lahan->Tpu->jenis_tpu) {
            case 'muslim':
                return $existing_makam > 0 ? [] : ['muslim'];
            case 'non_muslim':
                return $existing_makam > 0 ? [] : ['non_muslim'];
            case 'gabungan':
                $existing_kategori = self::where('uuid_lahan', $uuid_lahan)
                    ->pluck('kategori_makam')
                    ->toArray();
                $all_kategori = ['muslim', 'non_muslim'];
                return array_diff($all_kategori, $existing_kategori);
            default:
                return [];
        }
    }
}