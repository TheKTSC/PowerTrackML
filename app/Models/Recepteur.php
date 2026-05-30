<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recepteur extends Model {
    protected $fillable = [
        'user_id','nom','type_equipement','puissance_nominale',
        'est_moteur','rendement','puissance_absorbee','anciennete',
        'heures_par_jour','jours_par_mois','cout_kwh',
        'usage_ge','cout_kwh_ge','notes',
    ];
    protected $casts = ['est_moteur' => 'boolean', 'usage_ge' => 'boolean'];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function saisies(): HasMany  { return $this->hasMany(Saisie::class); }

    public function getPuissanceEffective(): float {
        if ($this->est_moteur && $this->rendement > 0) {
            return $this->puissance_nominale / ($this->rendement / 100);
        }
        return $this->puissance_nominale;
    }

    public function getCoutEffectif(User $user): float {
        if ($this->usage_ge && $this->cout_kwh_ge) return $this->cout_kwh_ge;
        return $this->cout_kwh ?? $user->cout_kwh;
    }
}