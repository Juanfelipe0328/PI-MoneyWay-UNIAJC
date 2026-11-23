@extends('layouts.app')

@section('title', 'Sueldo y fijos - MoneyWay')

@section('content')
@php $peso = fn ($n) => '$' . number_format(abs($n), 0, ',', '.'); @endphp

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Sueldo y gastos fijos</h1>
    <p class="text-secondary mb-0">Regístralos una sola vez. MoneyWay los anota solo cada mes, el día que les toca, en tu dashboard, movimientos y extracto.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 bg-success-subtle text-success-emphasis h-100"><div class="card-body">
        <div class="small">Ingresos fijos al mes</div><div class="fs-4 fw-semibold">{{ $peso($totalIngresos) }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 bg-danger-subtle text-danger-emphasis h-100"><div class="card-body">
        <div class="small">Gastos fijos al mes</div><div class="fs-4 fw-semibold">{{ $peso($totalGastos) }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 bg-secondary-subtle h-100"><div class="card-body">
        <div class="small">Te queda libre después de fijos</div>
        <div class="fs-4 fw-semibold">{{ ($totalIngresos - $totalGastos) < 0 ? '-' : '' }}{{ $peso($totalIngresos - $totalGastos) }}</div></div></div></div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-3">Agregar sueldo o gasto fijo</h2>
        <form action="{{ route('recurring.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                @include('recurring._form', ['item' => null])
                <div class="col-12"><button type="submit" class="btn btn-dark">Guardar</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-3">Tus fijos</h2>
        @if ($items->count())
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Nombre</th><th>Tipo</th><th>Categoría</th><th>Día</th><th>Vigencia</th><th class="text-end">Valor</th><th class="text-end">Acciones</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $it)
                            @php
                                $cat = $categoryNames[$it->category_id] ?? '-';
                                if ($it->custom_category) $cat .= ': ' . $it->custom_category;
                            @endphp
                            <tr class="{{ $it->is_active ? '' : 'text-secondary' }}">
                                <td>{{ $it->description }} @unless ($it->is_active)<span class="badge bg-secondary-subtle text-secondary-emphasis">Pausado</span>@endunless</td>
                                <td>
                                    @if ($it->type === 'ingreso') <span class="badge bg-success-subtle text-success-emphasis">Ingreso</span>
                                    @else <span class="badge bg-danger-subtle text-danger-emphasis">Gasto</span> @endif
                                </td>
                                <td>{{ $cat }}</td>
                                <td>{{ $it->day_of_month }}</td>
                                <td class="small">{{ $it->start_month }} &rarr; {{ $it->end_month ?: 'sin fin' }}</td>
                                <td class="text-end text-nowrap">{{ $it->type === 'ingreso' ? '+' : '-' }}{{ $peso($it->amount) }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('recurring.edit', $it->id) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
                                    <form action="{{ route('recurring.toggle', $it->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-outline-secondary btn-sm">{{ $it->is_active ? 'Pausar' : 'Activar' }}</button>
                                    </form>
                                    <form action="{{ route('recurring.destroy', $it->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este fijo? Lo ya registrado se conserva.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-secondary mb-0">Aún no tienes fijos. Empieza agregando tu sueldo y tu arriendo.</p>
        @endif
    </div>
</div>
@endsection
