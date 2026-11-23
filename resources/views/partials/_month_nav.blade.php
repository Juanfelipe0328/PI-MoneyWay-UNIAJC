{{--
    Selector de mes: flechas para ir al mes anterior/siguiente y un selector para saltar a cualquier mes.
    Parametros: $ruta (nombre de la ruta), $mes (YYYY-MM), $mesPrev, $mesNext, $mesNombre
--}}
<form method="GET" action="{{ route($ruta) }}" class="d-flex align-items-center gap-1 flex-wrap">
    <a href="{{ route($ruta, ['mes' => $mesPrev]) }}" class="btn btn-outline-secondary btn-sm" title="Mes anterior" aria-label="Mes anterior">&lsaquo;</a>
    <input type="month" name="mes" value="{{ $mes }}" class="form-control form-control-sm" style="width: 10.5rem;"
           aria-label="Elegir mes" onchange="this.form.submit()">
    <a href="{{ route($ruta, ['mes' => $mesNext]) }}" class="btn btn-outline-secondary btn-sm" title="Mes siguiente" aria-label="Mes siguiente">&rsaquo;</a>
    @if ($mes !== now('America/Bogota')->format('Y-m'))
        <a href="{{ route($ruta) }}" class="btn btn-link btn-sm text-decoration-none">Ir a este mes</a>
    @endif
</form>
