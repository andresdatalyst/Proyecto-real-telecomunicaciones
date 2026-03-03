<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de resultados para fusiones de cables de alimentación.
     * Misma estructura que tabla_fusion_laravel pero para el tipo
     * de cable de alimentación (tramo desde cabecera hasta primer splitter).
     */
    public function up(): void
    {
        Schema::create('tabla_fusion_alimentacion', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cable_origen')->nullable();
            $table->string('filamento_origen', 20)->nullable();
            $table->string('filamento_destino', 20)->nullable();
            $table->integer('id_cable_destino')->nullable();
            $table->string('cod_tramo_origen', 50)->nullable();
            $table->string('cod_tramo_destino', 50)->nullable();
            $table->string('splitter', 10)->nullable();
            $table->integer('id_objeto_origen')->nullable();
            $table->integer('id_objeto_destino')->nullable();
            $table->string('cod_objeto_origen', 50)->nullable();
            $table->string('cod_objeto_destino', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabla_fusion_alimentacion');
    }
};
