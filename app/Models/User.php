<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable {
    protected $fillable = [
        'nom','email','password','type_compte',
        'nombre_utilisateurs','cout_kwh','devise','notifications_actives',
    ];
    protected $hidden = ['password'];
    protected $casts  = ['notifications_actives' => 'boolean'];

    public function recepteurs(): HasMany {
        return $this->hasMany(Recepteur::class);
    }
    public function alertes(): HasMany {
        return $this->hasMany(Alerte::class);
    }
    public function seuils(): HasMany {
        return $this->hasMany(Seuil::class);
    }
}