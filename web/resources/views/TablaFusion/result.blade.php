@extends('layouts.app')

@section('title', 'Resultado — Motor de Fusiones FTTH')

@section('content')

<div class="page-header">
    <div class="label">Resultado</div>
    <h1>Fusiones generadas</h1>
    <p>
        El recorrido se completó correctamente.
        Se han insertado {{ $total ?? 0 }} fusiones en la tabla de resultados.
    </p>
</div>

<div class="alert alert-success">
    ✓ Recorrido completado — {{ $total ?? 0 }} registros generados
</div>

@if(!empty($fusiones) && count($fusiones) > 0)
<div class="card" style="padding: 0;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Cable origen</th>
                    <th>Fil. origen</th>
                    <th>Fil. destino</th>
                    <th>Cable destino</th>
                    <th>TP origen</th>
                    <th>TP destino</th>
                    <th>Splitter</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fusiones as $fila)
                <tr>
                    <td>{{ $fila->cod_tramo_origen ?? '—' }}</td>
                    <td>{{ $fila->filamento_origen ?? '—' }}</td>
                    <td>{{ $fila->filamento_destino ?? '—' }}</td>
                    <td>{{ $fila->cod_tramo_destino ?? '—' }}</td>
                    <td>{{ $fila->cod_objeto_origen ?? '—' }}</td>
                    <td>{{ $fila->cod_objeto_destino ?? '—' }}</td>
                    <td>
                        @if($fila->splitter === 'div1')
                            <span class="badge badge-div1">div1</span>
                        @elseif($fila->splitter === 'div2')
                            <span class="badge badge-div2">div2</span>
                        @else
                            <span class="badge-empty">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
    <a href="{{ route('fusion.distribucion.form') }}"
       style="font-family: var(--mono); font-size: 0.78rem; color: var(--muted);
              text-decoration: none; border: 1px solid var(--border); padding: 0.6rem 1.2rem;
              border-radius: 3px; transition: color 0.15s, border-color 0.15s;"
       onmouseover="this.style.color='var(--accent)';this.style.borderColor='var(--accent)'"
       onmouseout="this.style.color='var(--muted)';this.style.borderColor='var(--border)'">
        ← Nuevo recorrido
    </a>
</div>

@endsection
