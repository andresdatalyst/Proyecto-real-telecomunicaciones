<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de resultados del motor de fusiones.
     * Cada registro representa la fusión de un par de filamentos entre dos puntos.
     *
     * CAMPOS CLAVE:
     *   filamento_origen/destino → número de filamento fusionado en cada extremo
     *   splitter                 → 'div1', 'div2' o null (fusión directa cable-cable)
     *   id_objeto_origen/destino → objectid_1 del TP en cada extremo
     */
    public function up(): void
    {
        Schema::create('tabla_fusion_laravel', function (Blueprint $table) {
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
        Schema::dropIfExists('tabla_fusion_laravel');
    }
};
