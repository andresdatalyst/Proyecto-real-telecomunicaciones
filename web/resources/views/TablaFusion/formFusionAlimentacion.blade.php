@extends('layouts.app')

@section('title', 'Fusión Alimentación — Motor de Fusiones FTTH')

@section('content')

<div class="page-header">
    <div class="label">Recorrido / Alimentación</div>
    <h1>Fusión de cables de alimentación</h1>
    <p>
        Introduce el ID del cable de alimentación inicial y la ciudad.
        El sistema recorrerá el tramo hasta el punto de distribución.
    </p>
</div>

<div class="card">
    <form method="POST" action="{{ route('fusionAlimentacion') }}">
        @csrf

        <div class="form-group">
            <label for="id_cable_origen">ID cable origen</label>
            <input
                type="number"
                id="id_cable_origen"
                name="id_cable_origen"
                placeholder="ej: 777"
                value="{{ old('id_cable_origen') }}"
                required
            >
            <span class="hint">objectid del cable de alimentación inicial</span>
        </div>

        <div class="form-group">
            <label for="city">Ciudad</label>
            <input
                type="text"
                id="city"
                name="city"
                placeholder="ej: VEGAS DE ALMENARA"
                value="{{ old('city') }}"
                required
            >
            <span class="hint">Filtra cables y TPs por proyecto de red</span>
        </div>

        <button type="submit" class="btn">
            ▶ Ejecutar recorrido
        </button>
    </form>
</div>

@endsection
