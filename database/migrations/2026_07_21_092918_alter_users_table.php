<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'nom');
            $table->string('prenom')->after('nom');
            $table->string('role')->default('freelance')->after('password');
            $table->string('telephone', 20)->nullable()->after('role');
            $table->string('statut')->default('actif')->after('telephone');
            $table->decimal('taux_horaire', 8, 2)->nullable()->after('statut');
            $table->dropColumn('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
            $table->dropColumn(['prenom', 'role', 'telephone', 'statut', 'taux_horaire']);
            $table->timestamp('email_verified_at')->nullable();
        });
    }
};
