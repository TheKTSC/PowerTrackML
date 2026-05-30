<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerte extends Model {
    protected $fillable = ['user_id','type_alerte','recepteur_id','nom_recepteur','valeur','seuil'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}