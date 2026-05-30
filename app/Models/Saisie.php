<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Saisie extends Model {
    protected $fillable = ['recepteur_id','periode','date_saisie','kwh','heures','mode_saisie'];

    public function recepteur(): BelongsTo { return $this->belongsTo(Recepteur::class); }
}