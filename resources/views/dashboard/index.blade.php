@extends('layouts.app')

@section('title', 'Dashboard - MoneyWay')

@push('styles')
<style>
    /* Colores tomados de Bootstrap: se ven bien en el tema claro del resto de la app */
    .mw-card {
        background: var(--bs-body-bg);
        border-radius: 1rem;
        padding: 1.25rem 1.4rem;
        height: 100%;
        box-shadow: var(--bs-box-shadow-sm);
    }
    .mw-card h2 { font-size: 1.05rem; font-weight: 600; margin: 0 0 1rem; }

    /* Tarjetas de resumen */
    .stat-card {
        position: relative; overflow: hidden; border-radius: 1rem; padding: 1.25rem 1.4rem;
        color: #fff; height: 100%;
    }
    .stat-green  { background: #3aa76d; }
    .stat-orange { background: #ee9345; }
    .stat-blue   { background: #4a86d8; }
    .stat-icon {
        width: 44px; height: 44px; border-radius: 50%; background: rgba(255, 255, 255, .22);
        display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 700;
        flex-shrink: 0;
    }
    .stat-label { font-size: .95rem; opacity: .95; }
    .stat-value { font-size: 1.85rem; font-weight: 700; line-height: 1.15; }

    .chart-box { position: relative; height: 270px; }
    .donut-wrap { position: relative; height: 240px; width: 240px; flex-shrink: 0; }
    .donut-center {
        position: absolute; inset: 0; display: flex; flex-direction: column;
        align-items: center; justify-content: center; text-align: center; pointer-events: none;
    }
    .donut-center small { color: var(--bs-secondary-color); font-size: .72rem; }
    .donut-center strong { font-size: 1.05rem; }
    .dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; flex-shrink: 0; }

    .mov-table { --bs-table-bg: transparent; }
    .mov-table th { color: var(--bs-secondary-color); font-weight: 600; font-size: .85rem; border-bottom: 1px solid var(--bs-border-color); }
    .mov-table td { border-bottom: 1px solid var(--bs-border-color); }
    .mov-table tr:last-child td { border-bottom: 0; }

    .progress { border-radius: 999px; }
    .progress-bar.ok     { background: #3aa76d; }
    .progress-bar.near   { background: #ee9345; }
    .progress-bar.over   { background: #dc4c4c; }

    .rec {
        display: flex; gap: .85rem; align-items: flex-start; height: 100%;
        background: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color);
        border-left: 4px solid var(--c); border-radius: .75rem; padding: .9rem 1rem;
    }
    .rec-danger  { --c: #dc3545; }
    .rec-warning { --c: #e08a00; }
    .rec-info    { --c: #0d6efd; }
    .rec-success { --c: #198754; }
    .rec-icon {
        width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; display: flex;
        align-items: center; justify-content: center; font-weight: 700; font-size: .9rem;
        color: var(--c); background: color-mix(in srgb, var(--c) 15%, transparent);
    }
    .rec-title { font-weight: 600; margin-bottom: .15rem; }
    .rec-text  { color: var(--bs-secondary-color); font-size: .9rem; margin: 0; }

    /* Las opciones de los desplegables siempre con los colores del tema (se leen en claro y en oscuro) */
    select option { background-color: var(--bs-body-bg); color: var(--bs-body-color); }
</style>
@endpush

@section('content')
@php
    $money = fn ($n) => '$' . number_format(abs($n), 0, ',', '.');
@endphp

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 fw-bold mb-0">Dashboard financiero - {{ $mesNombre }}</h1>
    <a href="{{ route('movements.index') }}" class="btn btn-success btn-sm">+ Registrar movimiento</a>
</div>

{{-- Tarjetas de resumen --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon">&uarr;</span>
                <div>
                    <div class="stat-label">Ingresos del mes</div>
                    <div class="stat-value">+{{ $money($ingresos) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-orange">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon">&darr;</span>
                <div>
                    <div class="stat-label">Gastos del mes</div>
                    <div class="stat-value">-{{ $money($gastos) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon">$</span>
                <div>
                    <div class="stat-label">Saldo disponible</div>
                    <div class="stat-value">{{ $saldo < 0 ? '-' : '' }}{{ $money($saldo) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Graficas --}}
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="mw-card">
            <h2>Gastos por categoría</h2>
            @if ($gastos > 0)
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-4">
                    <div class="donut-wrap">
                        <canvas id="chartDona"></canvas>
                        <div class="donut-center">
                            <small>Total del mes</small>
                            <strong>{{ $money($gastos) }}</strong>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        @foreach ($dona as $fila)
                            <li class="d-flex align-items-center gap-2 mb-2">
                                <span class="dot" style="background: {{ $fila['color'] }}"></span>
                                <span>{{ $fila['pct'] }}% {{ $fila['nombre'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p class="text-secondary mb-0">Aún no tienes gastos este mes. Cuando registres uno, aquí verás en qué se va tu dinero.</p>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="mw-card">
            <h2>Evolución de ingresos y gastos (últimos 7 días)</h2>
            <div class="chart-box"><canvas id="chartSemana"></canvas></div>
        </div>
    </div>
</div>

{{-- Movimientos y presupuesto --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="mw-card">
            <h2>Últimos movimientos</h2>
            @if ($ultimos->count())
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mov-table mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Categoría / descripción</th>
                                <th>Tipo</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ultimos as $m)
                                <tr>
                                    <td class="text-nowrap">{{ $m['fecha'] }}</td>
                                    <td>
                                        {{ $m['titulo'] }}
                                        @if ($m['detalle'])<div class="small text-secondary">{{ $m['detalle'] }}</div>@endif
                                    </td>
                                    <td>{{ $m['tipo'] === 'ingreso' ? 'Ingreso' : 'Gasto' }}</td>
                                    <td class="text-end text-nowrap fw-semibold {{ $m['tipo'] === 'ingreso' ? 'text-success' : '' }}">
                                        {{ $m['tipo'] === 'ingreso' ? '+' : '-' }}{{ $money($m['monto']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('movements.index') }}#historial" class="btn btn-outline-secondary btn-sm">Ver todo el historial</a>
                </div>
            @else
                <p class="text-secondary mb-0">Aún no hay movimientos. Registra el primero desde "Registrar movimiento".</p>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="mw-card">
            <h2>Progreso de presupuesto mensual</h2>

            @if (count($presupuestos))
                @php
                    $claseTotal = $pctTotal >= 100 ? 'over' : ($pctTotal >= 80 ? 'near' : 'ok');
                @endphp
                <div class="progress mb-2" style="height: 16px;">
                    <div class="progress-bar {{ $claseTotal }}" style="width: {{ min($pctTotal, 100) }}%"></div>
                </div>
                <p class="text-center mb-3">
                    Has gastado <strong>{{ $money($gastadoTotal) }}</strong> de <strong>{{ $money($limiteTotal) }}</strong>
                    ({{ round($pctTotal) }}%).
                </p>

                @foreach ($presupuestos as $p)
                    @php $clase = $p['pct'] >= 100 ? 'over' : ($p['pct'] >= 80 ? 'near' : 'ok'); @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $p['nombre'] }}</span>
                            <div class="text-secondary d-flex align-items-center">
                                {{ $money($p['gastado']) }} / {{ $money($p['limite']) }}
                                <form action="{{ route('budgets.destroy', $p['id']) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Quitar este presupuesto?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 ms-2 text-secondary" title="Quitar">&times;</button>
                                </form>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $clase }}" style="width: {{ min($p['pct'], 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-secondary">Todavía no defines presupuestos para este mes. Ponle un límite a una categoría y aquí verás cuánto llevas.</p>
            @endif

            <form action="{{ route('budgets.store') }}" method="POST" class="row g-2 mt-2">
                @csrf
                <div class="col-sm-5">
                    <select name="category_id" class="form-select form-select-sm" required>
                        <option value="" disabled selected>Categoría</option>
                        @foreach ($categoriasGasto as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <input type="number" name="amount_limit" class="form-control form-control-sm"
                           placeholder="Límite $" min="1" step="1" required>
                </div>
                <div class="col-sm-3 d-grid">
                    <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Recomendaciones --}}
<div class="mw-card mb-2">
    <h2>Recomendaciones para ti</h2>
    <div class="row g-3">
        @foreach ($recomendaciones as $r)
            @php $icono = ['danger' => '!', 'warning' => '!', 'info' => 'i', 'success' => '✓'][$r['nivel']]; @endphp
            <div class="col-md-6 col-xl-4">
                <div class="rec rec-{{ $r['nivel'] }}">
                    <span class="rec-icon">{{ $icono }}</span>
                    <div>
                        <div class="rec-title">{{ $r['titulo'] }}</div>
                        <p class="rec-text">{{ $r['mensaje'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.js"></script>
<script>
    const etiquetas     = @json($etiquetas);
    const serieIngresos = @json($serieIngresos);
    const serieGastos   = @json($serieGastos);
    const dona          = @json($dona);

    const peso = n => '$' + new Intl.NumberFormat('es-CO').format(Math.round(n));
    const hayDatos = [...serieIngresos, ...serieGastos].some(v => v > 0);
    // Colores segun el tema de la pagina (claro por defecto)
    const css     = getComputedStyle(document.documentElement);
    const texto   = css.getPropertyValue('--bs-secondary-color').trim() || '#6c757d';
    const rejilla = css.getPropertyValue('--bs-border-color').trim() || '#dee2e6';
    const fondo   = css.getPropertyValue('--bs-body-bg').trim() || '#ffffff';

    Chart.defaults.color = texto;
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;

    // Dona: gastos por categoria
    const elDona = document.getElementById('chartDona');
    if (elDona) {
        new Chart(elDona, {
            type: 'doughnut',
            data: { labels: dona.map(d => d.nombre),
                    datasets: [{ data: dona.map(d => d.total), backgroundColor: dona.map(d => d.color),
                                 borderColor: fondo, borderWidth: 3 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%',
                       plugins: { legend: { display: false },
                                  tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + peso(c.parsed) } } } }
        });
    }

    // Evolucion de los ultimos 7 dias
    new Chart(document.getElementById('chartSemana'), {
        type: 'line',
        data: { labels: etiquetas, datasets: [
            { label: 'Ingresos', data: serieIngresos, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.18)',
              fill: true, cubicInterpolationMode: 'monotone', pointBackgroundColor: '#22c55e', pointRadius: 4 },
            { label: 'Gastos', data: serieGastos, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.18)',
              fill: true, cubicInterpolationMode: 'monotone', pointBackgroundColor: '#f59e0b', pointRadius: 4 }
        ] },
        options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                   plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8 } },
                              tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + peso(c.parsed.y) } } },
                   scales: { x: { grid: { display: false } },
                             y: { beginAtZero: true, suggestedMax: hayDatos ? undefined : 100000, grid: { color: rejilla },
                                  ticks: { callback: v => peso(v), maxTicksLimit: 6 } } } }
    });
</script>
@endpush
