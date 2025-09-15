<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crea la tabla 'banners' (plural)
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            // La columna es 'url_banners' (plural) como espera el Modelo
            $table->string('url_banners');
            $table->timestamps();
        });

        // Modifica la tabla 'users' para agregar la relación
        Schema::table('users', function (Blueprint $table) {
            // Asegúrate de que la columna se añade después de 'avatar_id' si existe
            $table->unsignedBigInteger('banner_id')->nullable()->after('id'); 
            $table->foreign('banner_id')->references('id')->on('banners')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // El proceso de rollback debe ser el inverso a la creación
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banner_id']);
            $table->dropColumn('banner_id');
        });
        
        Schema::dropIfExists('banners');
    }
};