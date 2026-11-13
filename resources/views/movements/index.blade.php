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

{{-- Pestañas --}}
<ul class="nav nav-underline nav-fill mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#agregar" type="button" role="tab">Agregar transacción</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#categorias" type="button" role="tab">Categorías</button>
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
                <h2 class="h5 fw-bold mb-3">Agregar nueva transacción</h2>

                <form action="{{ route('movements.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label for="amount" class="form-label">Valor (COP)</label>
                            <input type="number" step="0.01" min="0" name="amount" id="amount"
                                   value="{{ old('amount') }}" placeholder="Ej. 45000"
                                   class="form-control @error('amount') is-invalid @enderror" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="type" class="form-label">Tipo</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="" disabled {{ old('type') ? '' : 'selected' }}>Seleccionar tipo</option>
                                <option value="gasto" @selected(old('type') === 'gasto')>Gasto</option>
                                <option value="ingreso" @selected(old('type') === 'ingreso')>Ingreso</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="category_id" class="form-label">Categoría</label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Seleccionar categoría</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-type="{{ $category->type }}"
                                            @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="movement_date" class="form-label">Fecha</label>
                            <input type="date" name="movement_date" id="movement_date"
                                   value="{{ old('movement_date', now()->toDateString()) }}"
                                   class="form-control @error('movement_date') is-invalid @enderror" required>
                            @error('movement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label for="description" class="form-label">Descripción (opcional)</label>
                            <input type="text" name="description" id="description"
                                   value="{{ old('description') }}" placeholder="Ej. Almuerzo con el equipo"
                                   class="form-control @error('description') is-invalid @enderror">
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-dark">Registrar movimiento</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- CATEGORÍAS --}}
    <div class="tab-pane fade" id="categorias" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Gastos por categoría</h2>

                @forelse ($gastosPorCategoria as $fila)
                    @php $porcentaje = $gastos > 0 ? ($fila->total / $gastos) * 100 : 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $categoryNames[$fila->category_id] ?? 'Sin categoría' }}</span>
                            <span class="text-secondary">
                                ${{ number_format($fila->total, 0, ',', '.') }} · {{ number_format($porcentaje, 0) }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-secondary" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-secondary mb-0">Aún no tienes gastos registrados.</p>
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
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Monto</th>
                                    <th>Origen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movements as $m)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($m->movement_date)->format('d/m/Y') }}</td>
                                        <td>
                                            @if ($m->type === 'ingreso')
                                                <span class="badge bg-success-subtle text-success-emphasis">Ingreso</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger-emphasis">Gasto</span>
                                            @endif
                                        </td>
                                        <td>{{ $categoryNames[$m->category_id] ?? '-' }}</td>
                                        <td class="text-secondary">{{ $m->description ?: '-' }}</td>
                                        <td class="text-end">
                                            {{ $m->type === 'ingreso' ? '+' : '-' }}${{ number_format($m->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-secondary">{{ ucfirst($m->source) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $movements->fragment('historial')->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <p class="text-secondary mb-0">Aún no tienes movimientos registrados.</p>
                @endif
            </div>
        </div>
    </div>

</div>

<script>
    // Mostrar solo las categorías que corresponden al tipo elegido
    const typeSel = document.getElementById('type');
    const catSel = document.getElementById('category_id');

    function filtrarCategorias() {
        const t = typeSel.value;
        [...catSel.options].forEach(o => {
            if (!o.value) return;
            const ok = !t || !o.dataset.type || o.dataset.type === t;
            o.hidden = !ok;
            o.disabled = !ok;
        });
        if (catSel.selectedOptions[0] && catSel.selectedOptions[0].disabled) {
            catSel.value = '';
        }
    }
    typeSel.addEventListener('change', filtrarCategorias);
    filtrarCategorias();

    // Abrir la pestaña indicada en la URL (#historial, #categorias)
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash;
        if (!hash) return;
        const btn = document.querySelector('[data-bs-target="' + hash + '"]');
        if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
    });
</script>
@endsection
