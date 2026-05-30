<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recepteurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->string('type_equipement')->default('autre');
            $table->float('puissance_nominale');
            $table->boolean('est_moteur')->default(false);
            $table->float('rendement')->nullable();
            $table->float('puissance_absorbee')->nullable();
            $table->integer('anciennete')->default(0);
            $table->float('heures_par_jour')->nullable();
            $table->integer('jours_par_mois')->default(30);
            $table->float('cout_kwh')->nullable();
            $table->boolean('usage_ge')->default(false);
            $table->float('cout_kwh_ge')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('recepteurs'); }
};