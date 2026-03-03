@extends('layouts.app')

@section('title', 'Inicio — Motor de Fusiones FTTH')

@section('content')

<div class="page-header">
    <div class="label">Sistema de telecomunicaciones</div>
    <h1>Motor de Fusiones FTTH</h1>
    <p>
        Automatización del proceso de fusión de fibra óptica entre cables y
        Telecom Premises (TP). Selecciona el tipo de recorrido para comenzar.
    </p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">

    <div class="card">
        <div style="font-family: var(--mono); font-size: 0.7rem; color: var(--accent);
                    letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 0.8rem;">
            01 / Distribución
        </div>
        <p style="font-size: 0.88rem; color: var(--muted); line-height: 1.6; margin-bottom: 1.4rem;">
            Recorre automáticamente la red de distribución desde el primer cable
            hasta el splitter final, generando todas las fusiones del trayecto.
        </p>
        <a href="{{ route('fusion.distribucion.form') }}"
           style="display: inline-block; background: var(--accent); color: #0d0f12;
                  padding: 0.6rem 1.2rem; border-radius: 3px; font-family: var(--mono);
                  font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;
                  text-decoration: none; font-weight: 500;">
            Iniciar →
        </a>
    </div>

    <div class="card">
        <div style="font-family: var(--mono); font-size: 0.7rem; color: var(--accent);
                    letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 0.8rem;">
            02 / Alimentación
        </div>
        <p style="font-size: 0.88rem; color: var(--muted); line-height: 1.6; margin-bottom: 1.4rem;">
            Recorre el tramo de cable de alimentación desde la cabecera hasta
            el primer punto de distribución de la red.
        </p>
        <a href="{{ route('fusion.alimentacion.form') }}"
           style="display: inline-block; background: var(--accent); color: #0d0f12;
                  padding: 0.6rem 1.2rem; border-radius: 3px; font-family: var(--mono);
                  font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;
                  text-decoration: none; font-weight: 500;">
            Iniciar →
        </a>
    </div>

</div>

@endsection
