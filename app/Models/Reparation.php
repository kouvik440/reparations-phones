<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reparation extends Model
{
    protected $fillable = [
        'client_id',
        'code',
        'marque',
        'modele',
        'probleme',
        'statut',
        'date_reception',
        'date_depot' // Ajouté
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
