<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seuil extends Model {
    protected $fillable = ['user_id','type_seuil','recepteur_id','valeur','unite'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}