@extends('layouts.app')

@section('title', 'Editar fijo - MoneyWay')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Editar sueldo / gasto fijo</h1>
    <p class="text-secondary mb-0">Los cambios aplican a los meses que aún no se han registrado; lo anterior no se modifica.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('recurring.update', $item->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                @include('recurring._form', ['item' => $item])
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">Guardar cambios</button>
                    <a href="{{ route('recurring.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
