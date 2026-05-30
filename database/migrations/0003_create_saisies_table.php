<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('saisies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepteur_id')->constrained()->onDelete('cascade');
            $table->enum('periode', ['jour','semaine','mois']);
            $table->string('date_saisie', 20);
            $table->float('kwh');
            $table->float('heures')->nullable();
            $table->enum('mode_saisie', ['auto','manuel'])->default('manuel');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('saisies'); }
};