<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Reparation extends Model
{
    protected function casts(): array
    {
        return ['status_version' => 'integer'];
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(RepairStatusChange::class)->orderByDesc('version');
    }

    protected $fillable = [
        'device_brand_id',
        'device_model_id',
        'prix',
        'client_id',
        'code',
        'marque',
        'modele',
        'probleme',
        'statut',
        'date_reception',
        'date_depot', // Ajouté
    ];

    // Génère un code unique automatiquement
    protected static function booted()
    {
        static::creating(function ($reparation) {
            do {
                $code = strtoupper(Str::random(8));
            } while (self::where('code', $code)->exists());
            $reparation->code = $code;
        });

    }

    // Une réparation appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
