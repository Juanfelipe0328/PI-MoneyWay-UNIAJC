@extends('layouts.app')

@section('title', 'Editar movimiento - MoneyWay')

@section('content')

<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Editar movimiento</h1>
    <p class="text-secondary mb-0">Corrige los datos y guarda los cambios.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('movements.update', $movement->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                @include('movements._fields', ['movement' => $movement])

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">Guardar cambios</button>
                    <a href="{{ route('movements.index') }}#historial" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
