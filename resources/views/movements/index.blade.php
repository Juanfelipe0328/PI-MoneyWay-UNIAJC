@extends('layouts.app')

@section('title', 'Mis finanzas - MoneyWay')

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Hola, {{ auth()->user()->name }}</h1>
    <p class="text-secondary mb-0">Este es el resumen de tus finanzas.</p>
</div>

{{-- Resumen --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-success-subtle text-success-emphasis h-100">
            <div class="card-body">
                <div class="small">Ingresos totales</div>
                <div class="fs-3 fw-semibold">${{ number_format($ingresos, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-danger-subtle text-danger-emphasis h-100">
            <div class="card-body">
                <div class="small">Gastos totales</div>
                <div class="fs-3 fw-semibold">${{ number_format($gastos, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-secondary-subtle text-body-emphasis h-100">
            <div class="card-body">
                <div class="small">Balance neto</div>
                <div class="fs-3 fw-semibold">
                    {{ $balance < 0 ? '-' : '' }}${{ number_format(abs($balance), 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pestanas --}}
<ul class="nav nav-underline nav-fill mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#agregar" type="button" role="tab">Agregar transaccion</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#categorias" type="button" role="tab">Categorias</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">Historial</button>
    </li>
</ul>

<div class="tab-content">

    {{-- AGREGAR --}}
    <div class="tab-pane fade show active" id="agregar" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Agregar nueva transaccion</h2>

                <form action="{{ route('movements.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">

                        @include('movements._fields', ['movement' => null])

                        <div class="col-12">
                            <button type="submit" class="btn btn-dark">Registrar movimiento</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- CATEGORIAS --}}
    <div class="tab-pane fade" id="categorias" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Gastos por categoria</h2>

                @forelse ($gastosPorCategoria as $fila)
                    @php
                        $porcentaje = $gastos > 0 ? ($fila->total / $gastos) * 100 : 0;
                        $nombreBase = $categoryNames[$fila->category_id] ?? 'Sin categoria';
                        $nombre     = $fila->custom_category ? $nombreBase . ': ' . $fila->custom_category : $nombreBase;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $nombre }}</span>
                            <span class="text-secondary">
                                ${{ number_format($fila->total, 0, ',', '.') }} - {{ number_format($porcentaje, 0) }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-secondary" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-secondary mb-0">Aun no tienes gastos registrados.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- HISTORIAL --}}
    <div class="tab-pane fade" id="historial" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Historial de transacciones</h2>

                @if ($movements->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Categoria</th>
                                    <th>Descripcion</th>
                                    <th class="text-end">Monto</th>
                                    <th>Origen</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movements as $m)
                                    @php
                                        $nombreCat = $categoryNames[$m->category_id] ?? '-';
                                        if ($m->custom_category) {
                                            $nombreCat = $nombreCat . ': ' . $m->custom_category;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($m->movement_date)->format('d/m/Y') }}</td>
                                        <td>
                                            @if ($m->type === 'ingreso')
                                                <span class="badge bg-success-subtle text-success-emphasis">Ingreso</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger-emphasis">Gasto</span>
                                            @endif
                                        </td>
                                        <td>{{ $nombreCat }}</td>
                                        <td class="text-secondary">{{ $m->description ?: '-' }}</td>
                                        <td class="text-end">
                                            {{ $m->type === 'ingreso' ? '+' : '-' }}${{ number_format($m->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-secondary">{{ ucfirst($m->source) }}</td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('movements.edit', $m->id) }}"
                                               class="btn btn-outline-secondary btn-sm">Editar</a>

                                            <form action="{{ route('movements.destroy', $m->id) }}" method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Seguro que quieres eliminar este movimiento?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $movements->fragment('historial')->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <p class="text-secondary mb-0">Aun no tienes movimientos registrados.</p>
                @endif
            </div>
        </div>
    </div>

</div>

<script>
    // Abrir la pestana indicada en la URL (#historial, #categorias)
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash;
        if (!hash) return;
        const btn = document.querySelector('[data-bs-target="' + hash + '"]');
        if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
    });
</script>
@endsection
