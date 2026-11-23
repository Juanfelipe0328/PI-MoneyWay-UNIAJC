<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoneyAndMonths;
use App\Models\Category;
use App\Models\Movement;
use App\Services\RecurringService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MovementController extends Controller
{
    use HandlesMoneyAndMonths;

    // Panel principal: resumen del mes, formulario, categorias e historial (solo del usuario logueado)
    public function index(Request $request, RecurringService $recurrentes)
    {
        $userId = auth()->id();
        $recurrentes->sync($userId, $this->hoy());

        // Mes elegido (?mes=2026-10). Si no viene, el mes actual.
        $inicioMes = $this->parseMonth($request->query('mes'));
        $finMes    = $inicioMes->copy()->endOfMonth();
        $rango     = [$inicioMes->toDateString(), $finMes->toDateString()];

        $mes       = $inicioMes->format('Y-m');
        $mesNombre = ucfirst($inicioMes->translatedFormat('F Y'));
        $mesPrev   = $inicioMes->copy()->subMonthNoOverflow()->format('Y-m');
        $mesNext   = $inicioMes->copy()->addMonthNoOverflow()->format('Y-m');

        $ingresos = (float) Movement::where('user_id', $userId)->where('type', 'ingreso')->whereBetween('movement_date', $rango)->sum('amount');
        $gastos   = (float) Movement::where('user_id', $userId)->where('type', 'gasto')->whereBetween('movement_date', $rango)->sum('amount');
        $balance  = $ingresos - $gastos;

        $allCategories = $this->loadCategories();
        $categories    = $allCategories->where('is_active', true)->values();
        $categoryNames = $allCategories->pluck('name', 'id');
        $otherIds      = $this->otherCategoryIds($allCategories);

        // Gastos del mes agrupados por categoria.
        // Las categorias "Otro" se separan por el nombre que escribio el usuario.
        $gastosPorCategoria = Movement::where('user_id', $userId)
            ->where('type', 'gasto')
            ->whereBetween('movement_date', $rango)
            ->selectRaw('category_id, custom_category, SUM(amount) as total')
            ->groupBy('category_id', 'custom_category')
            ->orderByDesc('total')
            ->get();

        // Historial del mes
        $movements = Movement::where('user_id', $userId)
            ->whereBetween('movement_date', $rango)
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('movements.index', compact(
            'ingresos', 'gastos', 'balance',
            'categories', 'categoryNames', 'otherIds',
            'gastosPorCategoria', 'movements',
            'mes', 'mesNombre', 'mesPrev', 'mesNext'
        ));
    }

    // Guarda un movimiento a nombre del usuario logueado
    public function store(Request $request)
    {
        $data = $this->validateMovement($request);

        $movement = new Movement();
        $movement->user_id = auth()->id();   // siempre el usuario actual, nunca viene del formulario
        $this->fillMovement($movement, $data);
        $movement->source = 'manual';
        $movement->save();

        return redirect(route('movements.index', ['mes' => substr($data['movement_date'], 0, 7)]) . '#historial')
            ->with('success', 'Movimiento registrado correctamente.');
    }

    // Formulario para editar un movimiento (solo si es del usuario logueado)
    public function edit($id)
    {
        $movement = Movement::where('user_id', auth()->id())->findOrFail($id);

        $allCategories = $this->loadCategories();
        $categories    = $allCategories->where('is_active', true)->values();
        $otherIds      = $this->otherCategoryIds($allCategories);

        return view('movements.edit', compact('movement', 'categories', 'otherIds'));
    }

    // Actualiza un movimiento (solo si es del usuario logueado)
    public function update(Request $request, $id)
    {
        $movement = Movement::where('user_id', auth()->id())->findOrFail($id);

        $data = $this->validateMovement($request);
        $this->fillMovement($movement, $data);
        $movement->save();

        return redirect(route('movements.index', ['mes' => substr($data['movement_date'], 0, 7)]) . '#historial')
            ->with('success', 'Movimiento actualizado correctamente.');
    }

    // Elimina un movimiento (solo si es del usuario logueado)
    public function destroy($id)
    {
        $movement = Movement::where('user_id', auth()->id())->findOrFail($id);
        $mes = substr((string) $movement->movement_date, 0, 7);

        // Si lo borraran, el fijo lo volveria a crear. Se maneja desde "Fijos".
        if ($movement->recurring_id !== null) {
            return redirect(route('movements.index', ['mes' => $mes]) . '#historial')
                ->with('error', 'Este movimiento viene de un fijo y no se borra desde aqui. Pausalo o ponle mes final en "Fijos". Si solo cambio el valor este mes, usa Editar.');
        }

        $movement->delete();

        return redirect(route('movements.index', ['mes' => $mes]) . '#historial')
            ->with('success', 'Movimiento eliminado correctamente.');
    }

    // ------------------------------------------------------------------
    // Auxiliares
    // ------------------------------------------------------------------

    // Validacion compartida por store() y update()
    private function validateMovement(Request $request): array
    {
        // El valor llega como "10.000": se limpia antes de validar
        $request->merge(['amount' => $this->cleanMoney($request->input('amount'))]);

        $otherIds = $this->otherCategoryIds(Category::all(['id', 'name']));
        $isOther  = in_array((int) $request->input('category_id'), $otherIds, true);

        $data = $request->validate([
            'type'   => 'required|in:ingreso,gasto',
            'amount' => 'required|numeric|min:0.01|max:9999999999',
            // La categoria debe estar activa y ser del mismo tipo que el movimiento
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($q) use ($request) {
                    $q->where('is_active', true)->where('type', $request->input('type'));
                }),
            ],
            'movement_date' => 'required|date',
            'description'   => 'nullable|string|max:255',
            // Solo es obligatoria cuando se elige "Otro"
            'custom_category' => [Rule::requiredIf($isOther), 'nullable', 'string', 'max:60'],
        ], [
            'amount.required'          => 'Escribe el valor.',
            'amount.numeric'           => 'El valor debe ser un numero.',
            'amount.min'               => 'El valor debe ser mayor a cero.',
            'amount.max'               => 'El valor es demasiado grande.',
            'custom_category.required' => 'Escribe cual es la categoria.',
        ]);

        // Se guarda en un formato uniforme para que "mascotas" y "Mascotas" se agrupen igual
        $data['custom_category'] = $isOther
            ? Str::ucfirst(Str::lower(trim($data['custom_category'])))
            : null;

        return $data;
    }

    private function fillMovement(Movement $movement, array $data): void
    {
        $movement->type            = $data['type'];
        $movement->amount          = $data['amount'];
        $movement->category_id     = $data['category_id'];
        $movement->custom_category = $data['custom_category'];
        $movement->movement_date   = $data['movement_date'];
        $movement->description     = $data['description'] ?? null;
    }
}
