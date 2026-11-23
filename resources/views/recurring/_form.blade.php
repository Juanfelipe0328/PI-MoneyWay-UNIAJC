{{-- Campos de sueldo / gasto fijo. Los usan index (crear) y edit (editar). Espera $item (o null), $categories, $otherIds --}}
@php
    $tipoActual  = old('type', $item->type ?? 'ingreso');
    $catActual   = old('category_id', $item->category_id ?? '');
    $customAct   = old('custom_category', $item->custom_category ?? '');
    $valorAct    = old('amount', $item->amount ?? '');
    $valorAct    = $valorAct !== '' ? number_format((float) $valorAct, 0, ',', '.') : '';
    $inicioAct   = old('start_month', $item->start_month ?? now('America/Bogota')->format('Y-m'));
    $finAct      = old('end_month', $item->end_month ?? '');
    $diaAct      = old('day_of_month', $item->day_of_month ?? 1);
    $uid         = ($item->id ?? 'new');
@endphp

<div class="col-md-4">
    <label class="form-label">Qué es</label>
    <select name="type" id="r-type-{{ $uid }}" class="form-select" required>
        <option value="ingreso" @selected($tipoActual === 'ingreso')>Sueldo / ingreso fijo</option>
        <option value="gasto" @selected($tipoActual === 'gasto')>Gasto fijo</option>
    </select>
    @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Nombre</label>
    <input type="text" name="description" value="{{ old('description', $item->description ?? '') }}" maxlength="255"
           placeholder="Ej. Sueldo, Arriendo, Internet" class="form-control @error('description') is-invalid @enderror" required>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Valor (COP)</label>
    <div class="input-group">
        <span class="input-group-text">$</span>
        <input type="text" name="amount" data-money value="{{ $valorAct }}" placeholder="Ej. 1.800.000"
               class="form-control @error('amount') is-invalid @enderror" required>
    </div>
    @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Categoría</label>
    <select name="category_id" id="r-cat-{{ $uid }}" class="form-select @error('category_id') is-invalid @enderror" required>
        <option value="" disabled {{ $catActual ? '' : 'selected' }}>Seleccionar</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" data-type="{{ $c->type }}" @selected((string) $catActual === (string) $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4 d-none" id="r-custom-wrap-{{ $uid }}">
    <label class="form-label">¿Cuál categoría?</label>
    <input type="text" name="custom_category" id="r-custom-{{ $uid }}" maxlength="60" value="{{ $customAct }}"
           class="form-control @error('custom_category') is-invalid @enderror" placeholder="Ej. Gimnasio">
    @error('custom_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-6 col-md-2">
    <label class="form-label">Día del mes</label>
    <input type="number" name="day_of_month" min="1" max="31" value="{{ $diaAct }}"
           class="form-control @error('day_of_month') is-invalid @enderror" required>
    @error('day_of_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-6 col-md-3">
    <label class="form-label">Desde el mes</label>
    <input type="month" name="start_month" value="{{ $inicioAct }}"
           class="form-control @error('start_month') is-invalid @enderror" required>
    @error('start_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-12 col-md-3">
    <label class="form-label">Hasta el mes <span class="text-secondary">(opcional)</span></label>
    <input type="month" name="end_month" value="{{ $finAct }}"
           class="form-control @error('end_month') is-invalid @enderror">
    @error('end_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<script>
    (function () {
        const otherIds = @json($otherIds);
        const typeSel = document.getElementById('r-type-{{ $uid }}');
        const catSel  = document.getElementById('r-cat-{{ $uid }}');
        const wrap    = document.getElementById('r-custom-wrap-{{ $uid }}');
        const input   = document.getElementById('r-custom-{{ $uid }}');

        function filtrar() {
            [...catSel.options].forEach(o => {
                if (!o.value) return;
                const ok = o.dataset.type === typeSel.value;
                o.hidden = !ok; o.disabled = !ok;
            });
            if (catSel.selectedOptions[0] && catSel.selectedOptions[0].disabled) catSel.value = '';
        }
        function otro() {
            const esOtro = otherIds.includes(parseInt(catSel.value, 10));
            wrap.classList.toggle('d-none', !esOtro);
            input.required = esOtro;
            if (!esOtro) input.value = '';
        }
        typeSel.addEventListener('change', () => { filtrar(); otro(); });
        catSel.addEventListener('change', otro);
        filtrar(); otro();
    })();
</script>
