{{-- Campos del formulario de movimiento. Lo usan index.blade.php (crear) y edit.blade.php (editar) --}}
@php
    $tipoActual      = old('type', $movement->type ?? '');
    $categoriaActual = old('category_id', $movement->category_id ?? '');
    $customActual    = old('custom_category', $movement->custom_category ?? '');
    $fechaActual     = old(
        'movement_date',
        $movement ? \Carbon\Carbon::parse($movement->movement_date)->format('Y-m-d') : now()->toDateString()
    );
@endphp

<div class="col-md-4">
    <label for="amount" class="form-label">Valor (COP)</label>
    <input type="number" step="0.01" min="0" name="amount" id="amount"
           value="{{ old('amount', $movement->amount ?? '') }}" placeholder="Ej. 45000"
           class="form-control @error('amount') is-invalid @enderror" required>
    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label for="type" class="form-label">Tipo</label>
    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
        <option value="" disabled {{ $tipoActual ? '' : 'selected' }}>Seleccionar tipo</option>
        <option value="gasto" @selected($tipoActual === 'gasto')>Gasto</option>
        <option value="ingreso" @selected($tipoActual === 'ingreso')>Ingreso</option>
    </select>
    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label for="category_id" class="form-label" id="cat-label">Categoria</label>
    <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
        <option value="" disabled {{ $categoriaActual ? '' : 'selected' }}>Seleccionar</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" data-type="{{ $category->type }}"
                    @selected((string) $categoriaActual === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- Solo se muestra cuando se elige "Otro" --}}
<div class="col-12 d-none" id="custom-wrap">
    <label for="custom_category" class="form-label" id="custom-label">Cual categoria?</label>
    <input type="text" name="custom_category" id="custom_category" maxlength="60"
           value="{{ $customActual }}" placeholder="Ej. Mascotas"
           class="form-control @error('custom_category') is-invalid @enderror">
    @error('custom_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label for="movement_date" class="form-label">Fecha</label>
    <input type="date" name="movement_date" id="movement_date"
           value="{{ $fechaActual }}"
           class="form-control @error('movement_date') is-invalid @enderror" required>
    @error('movement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-8">
    <label for="description" class="form-label">Descripcion (opcional)</label>
    <input type="text" name="description" id="description"
           value="{{ old('description', $movement->description ?? '') }}" placeholder="Ej. Almuerzo con el equipo"
           class="form-control @error('description') is-invalid @enderror">
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<script>
    (function () {
        // IDs de las categorias llamadas "Otro"
        const otherIds = @json($otherIds);

        const typeSel  = document.getElementById('type');
        const catSel   = document.getElementById('category_id');
        const catLabel = document.getElementById('cat-label');
        const wrap     = document.getElementById('custom-wrap');
        const label    = document.getElementById('custom-label');
        const input    = document.getElementById('custom_category');

        // Mostrar solo las categorias que corresponden al tipo elegido
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
            catLabel.textContent = t === 'ingreso' ? 'Tipo de ingreso' : 'Categoria';
        }

        // Mostrar el campo de texto libre solo si la categoria elegida es "Otro"
        function actualizarOtro() {
            const esOtro = otherIds.includes(parseInt(catSel.value, 10));
            const esIngreso = typeSel.value === 'ingreso';

            wrap.classList.toggle('d-none', !esOtro);
            input.required = esOtro;
            label.textContent = esIngreso ? 'Cual tipo de ingreso?' : 'Cual categoria?';
            input.placeholder = esIngreso ? 'Ej. Venta de ropa' : 'Ej. Mascotas';
            if (!esOtro) input.value = '';
        }

        typeSel.addEventListener('change', function () {
            filtrarCategorias();
            actualizarOtro();
        });
        catSel.addEventListener('change', actualizarOtro);

        filtrarCategorias();
        actualizarOtro();
    })();
</script>
