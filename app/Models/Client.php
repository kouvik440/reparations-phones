<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    // Définir les colonnes qui peuvent être insérées ou modifiées
    protected $fillable = ['prenom', 'nom', 'telephone', 'piece_identite'];

    // Relation : un client a plusieurs réparations
    public function reparations()
    {
        return $this->hasMany(Reparation::class);
    }
}
