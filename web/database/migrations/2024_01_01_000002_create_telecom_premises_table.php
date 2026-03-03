<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de puntos de conexión de la red (CTOs, splitters, armarios).
     * Un Telecom Premise (TP) es el punto físico donde se realizan las fusiones.
     *
     * CAMPOS CLAVE:
     *   objectid_1 → identificador principal usado en las relaciones con cable
     *   codigo     → nombre del punto (ej: 'CTO-001'), usado para navegación
     *   ciudad     → filtra TPs por proyecto de red
     */
    public function up(): void
    {
        Schema::create('telecom_premises', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('objectid_1')->unique();
            $table->string('codigo', 50)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telecom_premises');
    }
};
