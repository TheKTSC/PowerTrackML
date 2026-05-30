<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('seuils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type_seuil', ['global','recepteur'])->default('global');
            $table->unsignedBigInteger('recepteur_id')->nullable();
            $table->float('valeur');
            $table->enum('unite', ['kwh','cout'])->default('kwh');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('seuils'); }
};