<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type_alerte', ['global','recepteur']);
            $table->unsignedBigInteger('recepteur_id')->nullable();
            $table->string('nom_recepteur')->nullable();
            $table->float('valeur');
            $table->float('seuil');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('alertes'); }
};