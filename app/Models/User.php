<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Les attributs pouvant être remplis.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Les attributs cachés lors de la sérialisation.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs castés automatiquement.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Vérifie si l'utilisateur est un administrateur.
     *
     * @return bool
     */

      public function admin()
        {
            return view('admin.index'); // ou autre
        }


    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

}
