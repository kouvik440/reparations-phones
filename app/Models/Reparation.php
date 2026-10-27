<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Relations\HasMany;
>>>>>>> version1
use Illuminate\Support\Str;

class Reparation extends Model
{
<<<<<<< HEAD
    protected $fillable = [
=======
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
>>>>>>> version1
        'client_id',
        'code',
        'marque',
        'modele',
        'probleme',
        'statut',
        'date_reception',
<<<<<<< HEAD
        'date_depot' // Ajouté
=======
        'date_depot', // Ajouté
>>>>>>> version1
    ];

    // Génère un code unique automatiquement
    protected static function booted()
    {
        static::creating(function ($reparation) {
<<<<<<< HEAD
        do { 
            $code = strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());
        $reparation->code = $code;
    });
=======
            do {
                $code = strtoupper(Str::random(8));
            } while (self::where('code', $code)->exists());
            $reparation->code = $code;
        });
>>>>>>> version1

    }

    // Une réparation appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
