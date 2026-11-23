<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoneyAndMonths;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Movement;
use App\Services\RecommendationService;
use App\Services\RecurringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    use HandlesMoneyAndMonths;

    // Colores de la dona (mismo orden en la leyenda y en la grafica)
    private const PALETTE = ['#22c55e', '#3b82f6', '#a855f7', '#f59e0b', '#ef4444', '#94a3b8'];

    public function index(Request $request, RecommendationService $recomendador, RecurringService $recurrentes)
    {
        $userId = auth()->id();

        $hoy = $this->hoy();
        // Sueldo y gastos fijos del mes se registran solos antes de calcular nada
        $recurrentes->sync($userId, $hoy);

        // Mes elegido (?mes=2026-10). Si no viene, el mes actual.
        $inicioMes   = $this->parseMonth($request->query('mes'));
        $finMes      = $inicioMes->copy()->endOfMonth();
        $mes         = $inicioMes->format('Y-m');
        $esMesActual = $mes === $hoy->format('Y-m');
        $mesNombre   = ucfirst($inicioMes->translatedFormat('F Y'));

        $nombres = Category::pluck('name', 'id');

        // ---- Movimientos del mes (todo se calcula desde aqui) ----
        $delMes = Movement::where('user_id', $userId)
            ->whereBetween('movement_date', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->get();

        $ingresos = (float) $delMes->where('type', 'ingreso')->sum('amount');
        $gastos   = (float) $delMes->where('type', 'gasto')->sum('amount');

        // Saldo al cierre del mes elegido = todo lo que habia entrado menos lo que habia salido hasta esa fecha
        $saldo = (float) Movement::where('user_id', $userId)->where('type', 'ingreso')
                    ->where('movement_date', '<=', $finMes->toDateString())->sum('amount')
               - (float) Movement::where('user_id', $userId)->where('type', 'gasto')
                    ->where('movement_date', '<=', $finMes->toDateString())->sum('amount');

        $gastosFijos = (float) $delMes->where('type', 'gasto')->whereNotNull('recurring_id')->sum('amount');

        // ---- Gastos por categoria (las "Otro" se separan por el nombre que escribio el usuario) ----
        $porCategoria = $delMes->where('type', 'gasto')
            ->groupBy(function ($m) use ($nombres) {
                $base = $nombres[$m->category_id] ?? 'Sin categoria';
                return $m->custom_category ? $base . ': ' . $m->custom_category : $base;
            })
            ->map(fn ($grupo) => (float) $grupo->sum('amount'))
            ->sortDesc();

        $categoriasTop = $porCategoria->take(5);
        if ($porCategoria->count() > 6) {
            $categoriasTop->put('Demas categorias', (float) $porCategoria->slice(5)->sum());
        } else {
            $categoriasTop = $porCategoria->take(6);
        }

        $dona = $categoriasTop->values()->map(function ($total, $i) use ($categoriasTop, $gastos) {
            return [
                'nombre' => $categoriasTop->keys()[$i],
                'total'  => $total,
                'pct'    => $gastos > 0 ? round(($total / $gastos) * 100) : 0,
                'color'  => self::PALETTE[$i % count(self::PALETTE)],
            ];
        })->all();

        // ---- Ultimos 7 dias del mes elegido (ingresos vs gastos) ----
        $fin   = $esMesActual ? $hoy->copy() : $finMes->copy()->startOfDay();
        $desde = $fin->copy()->subDays(6);
        $semana = Movement::where('user_id', $userId)
            ->whereBetween('movement_date', [$desde->toDateString(), $fin->toDateString()])
            ->get()
            ->groupBy(fn ($m) => Carbon::parse($m->movement_date)->toDateString());

        $etiquetas = $serieIngresos = $serieGastos = [];
        for ($i = 0; $i < 7; $i++) {
            $dia   = $desde->copy()->addDays($i);
            $filas = $semana->get($dia->toDateString(), collect());
            $ing   = (float) $filas->where('type', 'ingreso')->sum('amount');
            $gas   = (float) $filas->where('type', 'gasto')->sum('amount');

            $etiquetas[]     = $dia->translatedFormat('d M');
            $serieIngresos[] = $ing;
            $serieGastos[]   = $gas;
        }

        // ---- Ultimos movimientos ----
        $ultimos = Movement::where('user_id', $userId)
            ->whereBetween('movement_date', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($m) use ($nombres) {
                $cat = $nombres[$m->category_id] ?? 'Sin categoria';
                if ($m->custom_category) {
                    $cat .= ': ' . $m->custom_category;
                }
                return [
                    'fecha'    => Carbon::parse($m->movement_date)->locale('es')->translatedFormat('d M'),
                    'titulo'   => $m->description ?: $cat,
                    'detalle'  => $m->description ? $cat : null,
                    'tipo'     => $m->type,
                    'monto'    => (float) $m->amount,
                ];
            });

        // ---- Presupuestos del mes ----
        $mesClave = $mes;
        $gastadoPorCategoria = $delMes->where('type', 'gasto')->groupBy('category_id')
            ->map(fn ($g) => (float) $g->sum('amount'));

        $presupuestos = Budget::where('user_id', $userId)
            ->where('month', $mesClave)
            ->get()
            ->map(function ($b) use ($nombres, $gastadoPorCategoria) {
                $limite  = (float) $b->amount_limit;
                $gastado = (float) ($gastadoPorCategoria[$b->category_id] ?? 0);
                return [
                    'id'      => $b->id,
                    'nombre'  => $nombres[$b->category_id] ?? 'Categoria',
                    'limite'  => $limite,
                    'gastado' => $gastado,
                    'pct'     => $limite > 0 ? ($gastado / $limite) * 100 : 0,
                ];
            })
            ->sortByDesc('pct')
            ->values()
            ->all();

        $limiteTotal  = array_sum(array_column($presupuestos, 'limite'));
        $gastadoTotal = array_sum(array_column($presupuestos, 'gastado'));
        $pctTotal     = $limiteTotal > 0 ? ($gastadoTotal / $limiteTotal) * 100 : 0;

        // Categorias de gasto disponibles para definir presupuesto
        $categoriasGasto = Category::where('type', 'gasto')->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')->get(['id', 'name']);

        // ---- Recomendaciones ----
        // Mes actual: se compara con el MISMO periodo del mes anterior (hasta el mismo dia).
        // Mes pasado: se compara el mes completo contra el anterior.
        $diaCorte   = $esMesActual ? $hoy->day : $finMes->day;
        $inicioPrev = $inicioMes->copy()->subMonthNoOverflow()->startOfMonth();
        $corte      = $inicioPrev->copy()->addDays($diaCorte - 1);
        $finPrev    = $inicioPrev->copy()->endOfMonth();
        if ($corte->gt($finPrev)) {
            $corte = $finPrev;
        }
        $gastosMesPasado = (float) Movement::where('user_id', $userId)->where('type', 'gasto')
            ->whereBetween('movement_date', [$inicioPrev->toDateString(), $corte->toDateString()])
            ->sum('amount');

        $recomendaciones = $recomendador->generate([
            'mes'             => $mesNombre,
            'ingresos'        => $ingresos,
            'gastos'          => $gastos,
            'gastosFijos'     => $gastosFijos,
            'gastosMesPasado' => $gastosMesPasado,
            'categorias'      => $porCategoria->all(),
            'presupuestos'    => $presupuestos,
            'hayMovimientos'  => $delMes->isNotEmpty(),
            'esMesActual'     => $esMesActual,
            'diaActual'       => $diaCorte,
            'diasMes'         => $inicioMes->daysInMonth,
        ]);

        $mesPrev = $inicioMes->copy()->subMonthNoOverflow()->format('Y-m');
        $mesNext = $inicioMes->copy()->addMonthNoOverflow()->format('Y-m');

        return view('dashboard.index', compact(
            'mesNombre', 'ingresos', 'gastos', 'saldo',
            'dona', 'etiquetas', 'serieIngresos', 'serieGastos',
            'ultimos', 'presupuestos', 'limiteTotal', 'gastadoTotal', 'pctTotal',
            'categoriasGasto', 'recomendaciones',
            'mes', 'mesPrev', 'mesNext', 'esMesActual'
        ));
    }

    // Crea o actualiza el presupuesto del mes que se esta viendo para una categoria
    public function storeBudget(Request $request)
    {
        $request->merge(['amount_limit' => $this->cleanMoney($request->input('amount_limit'))]);
        $mes = $this->parseMonth($request->input('mes'))->format('Y-m');

        $data = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('is_active', true)->where('type', 'gasto')),
            ],
            'amount_limit' => 'required|numeric|min:1|max:9999999999',
        ], [
            'category_id.required'  => 'Elige una categoria para el presupuesto.',
            'category_id.exists'    => 'Esa categoria no es valida.',
            'amount_limit.required' => 'Escribe el limite del presupuesto.',
            'amount_limit.numeric'  => 'El limite debe ser un numero.',
            'amount_limit.min'      => 'El limite debe ser mayor a cero.',
            'amount_limit.max'      => 'El limite es demasiado grande.',
        ]);

        Budget::updateOrCreate(
            [
                'user_id'     => auth()->id(),
                'category_id' => $data['category_id'],
                'month'       => $mes,
            ],
            ['amount_limit' => $data['amount_limit']]
        );

        return redirect()->route('dashboard', ['mes' => $mes])->with('success', 'Presupuesto guardado.');
    }

    // Quita un presupuesto (solo si es del usuario logueado)
    public function destroyBudget(Request $request, $id)
    {
        $budget = Budget::where('user_id', auth()->id())->findOrFail($id);
        $mes = $budget->month;
        $budget->delete();

        return redirect()->route('dashboard', ['mes' => $mes])->with('success', 'Presupuesto eliminado.');
    }
}
