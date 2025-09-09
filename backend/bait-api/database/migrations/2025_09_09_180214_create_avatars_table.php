<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Corregido: Nombre de la tabla a plural.
        Schema::create('avatars', function (Blueprint $table) {
            $table->id();
            // Corregido: Nombre de la columna a plural.
            $table->string('url_avatars');
            $table->timestamps();
        });

        // IMPORTANTE: Añade la relación a la tabla 'users'.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('avatar_id')->nullable()->constrained('avatars')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['avatar_id']);
            $table->dropColumn('avatar_id');
        });

        Schema::dropIfExists('avatars');
    }
};