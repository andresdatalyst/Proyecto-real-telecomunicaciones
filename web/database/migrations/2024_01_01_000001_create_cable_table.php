<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de tramos de cable de fibra óptica.
     * Cada registro representa un tramo entre dos Telecom Premises (TP).
     *
     * CAMPOS CLAVE:
     *   fibras_act → pares de fibras activas en formato texto: '1-2,3-4,5-6'
     *   fibras_res → pares de fibras de reserva en el mismo formato
     *   origen     → código del TP de inicio del tramo
     *   destino    → código del TP de fin del tramo
     *   ciudad     → filtra cables por proyecto de red (evita colisiones de códigos)
     *
     * NOTA: El formato de texto de fibras_act/fibras_res es heredado del sistema GIS
     * de producción. Ver /sql/improved/ para el diseño normalizado alternativo.
     */
    public function up(): void
    {
        Schema::create('cable', function (Blueprint $table) {
            $table->increments('objectid');
            $table->string('codigo', 50)->nullable();
            $table->string('origen', 50)->nullable();
            $table->string('destino', 50)->nullable();
            $table->text('fibras_act')->nullable();
            $table->text('fibras_res')->nullable();
            $table->text('fibras_totales')->nullable();
            $table->text('fi_x')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cable');
    }
};
