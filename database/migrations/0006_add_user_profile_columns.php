<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nom')) {
                $table->string('nom')->after('id');
            }
            if (!Schema::hasColumn('users', 'type_compte')) {
                $table->enum('type_compte', ['Particulier','Entreprise'])->default('Particulier')->after('password');
            }
            if (!Schema::hasColumn('users', 'nombre_utilisateurs')) {
                $table->integer('nombre_utilisateurs')->default(1)->after('type_compte');
            }
            if (!Schema::hasColumn('users', 'cout_kwh')) {
                $table->float('cout_kwh')->default(100.0)->after('nombre_utilisateurs');
            }
            if (!Schema::hasColumn('users', 'devise')) {
                $table->string('devise', 10)->default('FCFA')->after('cout_kwh');
            }
            if (!Schema::hasColumn('users', 'notifications_actives')) {
                $table->boolean('notifications_actives')->default(true)->after('devise');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'notifications_actives')) {
                $table->dropColumn('notifications_actives');
            }
            if (Schema::hasColumn('users', 'devise')) {
                $table->dropColumn('devise');
            }
            if (Schema::hasColumn('users', 'cout_kwh')) {
                $table->dropColumn('cout_kwh');
            }
            if (Schema::hasColumn('users', 'nombre_utilisateurs')) {
                $table->dropColumn('nombre_utilisateurs');
            }
            if (Schema::hasColumn('users', 'type_compte')) {
                $table->dropColumn('type_compte');
            }
            if (Schema::hasColumn('users', 'nom')) {
                $table->dropColumn('nom');
            }
        });
    }
};
