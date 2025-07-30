<?php
namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TpuMakam extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    public $incrementing      = false;
    protected $table          = "tpu_makams";
    protected $primaryKey     = "uuid";
    protected $keyType        = 'string';
    protected $cascadeDeletes = [];
    protected $dates          = ['deleted_at'];

    // Update fillable untuk mengizinkan mass assignment
    protected $fillable = [
        'uuid',
        'uuid_lahan',
        'panjang_m',
        'lebar_m',
        'luas_m2',
        'kapasitas',
        'makam_terisi',
        'sisa_kapasitas',
        'status_makam',
        'keterangan',
        'uuid_created',
        'uuid_updated',
        'uuid_deleted',
    ];

    protected $hidden = [
        "uuid_created",
        "uuid_updated",
        "uuid_deleted",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    // Accessor untuk status makam dengan warna
    public function getStatusMakamBadgeAttribute()
    {
        if (! $this->StatusMakam) {
            return '<span class="badge badge-light-secondary">Tidak Diketahui</span>';
        }

        $badges = [
            'Tersedia'        => '<span class="badge badge-light-success">Tersedia</span>',
            'Terpakai'        => '<span class="badge badge-light-danger">Terpakai</span>',
            'Dalam Perawatan' => '<span class="badge badge-light-warning">Dalam Perawatan</span>',
            'Reservasi'       => '<span class="badge badge-light-info">Reservasi</span>',
        ];

        return $badges[$this->status_makam] ?? '<span class="badge badge-light-secondary">' . $this->status_makam . '</span>';
    }

    // Accessor untuk kapasitas dengan progress bar
    public function getKapasitasProgressAttribute()
    {
        if (! $this->kapasitas || $this->kapasitas == 0) {
            return '<div class="text-muted">Tidak diketahui</div>';
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
}