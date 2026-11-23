@extends('layouts.app')

@section('title', 'Extracto - MoneyWay')

@push('styles')
<style>
    .extracto { background: var(--bs-body-bg); border-radius: 1rem; box-shadow: var(--bs-box-shadow-sm); overflow: hidden; }
    .extracto-head { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--bs-border-color); }
    .resumen-box { background: var(--bs-tertiary-bg); border-radius: .75rem; padding: .85rem 1rem; height: 100%; }
    .resumen-box small { color: var(--bs-secondary-color); display: block; }
    .resumen-box strong { font-size: 1.15rem; }
    .num { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
    .extracto table thead th { font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; color: var(--bs-secondary-color); background: var(--bs-tertiary-bg); }
    .fila-saldo td { background: var(--bs-tertiary-bg); font-weight: 600; }
    .pendiente td { color: var(--bs-secondary-color); font-style: italic; }
</style>
@endpush

@section('content')
@php
    $peso  = fn ($n) => '$' . number_format(abs($n), 0, ',', '.');
    $firma = fn ($n) => ($n < 0 ? '-' : '') . $peso($n);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 fw-bold mb-0">Extracto de {{ $mesNombre }}</h1>
        <div class="text-secondary small">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @include('partials._month_nav', ['ruta' => 'statement.index', 'mesNombre' => $mesNombre])
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">Imprimir</button>
    </div>
</div>

<div class="extracto">
    <div class="extracto-head">
        <div class="row g-3">
            <div class="col-6 col-lg-3"><div class="resumen-box"><small>Saldo anterior</small><strong>{{ $firma($saldoAnterior) }}</strong></div></div>
            <div class="col-6 col-lg-3"><div class="resumen-box"><small>Total ingresos (+)</small><strong class="text-success">{{ $peso($totalIngresos) }}</strong></div></div>
            <div class="col-6 col-lg-3"><div class="resumen-box"><small>Total gastos (-)</small><strong class="text-danger">{{ $peso($totalGastos) }}</strong></div></div>
            <div class="col-6 col-lg-3"><div class="resumen-box"><small>Saldo final</small><strong class="{{ $saldoFinal < 0 ? 'text-danger' : '' }}">{{ $firma($saldoFinal) }}</strong></div></div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th class="text-end">Ingreso</th>
                    <th class="text-end">Gasto</th>
                    <th class="text-end">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr class="fila-saldo">
                    <td colspan="5">Saldo anterior</td>
                    <td class="num">{{ $firma($saldoAnterior) }}</td>
                </tr>

                @forelse ($filas as $f)
                    <tr>
                        <td class="text-nowrap">{{ $f['fecha']->format('d/m/Y') }}</td>
                        <td>
                            {{ $f['descripcion'] }}
                            @if ($f['fijo']) <span class="badge bg-primary-subtle text-primary-emphasis ms-1">Fijo</span> @endif
                        </td>
                        <td class="text-secondary">{{ $f['categoria'] }}</td>
                        <td class="num text-success">{{ $f['tipo'] === 'ingreso' ? '+' . $peso($f['valor']) : '' }}</td>
                        <td class="num text-danger">{{ $f['tipo'] === 'gasto' ? '-' . $peso($f['valor']) : '' }}</td>
                        <td class="num fw-semibold {{ $f['saldo'] < 0 ? 'text-danger' : '' }}">{{ $firma($f['saldo']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary py-4">No hay movimientos en este mes.</td></tr>
                @endforelse

                <tr class="fila-saldo">
                    <td colspan="3">Totales del mes / Saldo final</td>
                    <td class="num text-success">+{{ $peso($totalIngresos) }}</td>
                    <td class="num text-danger">-{{ $peso($totalGastos) }}</td>
                    <td class="num">{{ $firma($saldoFinal) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@if (count($pendientes))
    <div class="extracto mt-3">
        <div class="extracto-head pb-2">
            <h2 class="h6 fw-bold mb-1">Próximos fijos de este mes</h2>
            <div class="small text-secondary">Todavía no caen, por eso no están en tu saldo. Se registran solos el día que les toca.</div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <tbody>
                    @foreach ($pendientes as $p)
                        <tr class="pendiente">
                            <td class="text-nowrap">{{ $p['fecha']->format('d/m/Y') }}</td>
                            <td>{{ $p['descripcion'] }}</td>
                            <td class="num">{{ $p['tipo'] === 'ingreso' ? '+' : '-' }}{{ $peso($p['valor']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="mt-3 small text-secondary">
    ¿Falta algo? <a href="{{ route('movements.index') }}">Registra un movimiento</a> o administra tu
    <a href="{{ route('recurring.index') }}">sueldo y gastos fijos</a>.
</div>
@endsection
