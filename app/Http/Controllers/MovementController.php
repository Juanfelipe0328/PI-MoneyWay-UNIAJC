<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MovementController extends Controller
{
    // Panel principal: resumen, formulario, categorias e historial (solo del usuario logueado)
    public function index()
    {
        $userId = auth()->id();

        $ingresos = Movement::where('user_id', $userId)->where('type', 'ingreso')->sum('amount');
        $gastos   = Movement::where('user_id', $userId)->where('type', 'gasto')->sum('amount');
        $balance  = $ingresos - $gastos;

        $allCategories = $this->loadCategories();
        $categories    = $allCategories->where('is_active', true)->values();
        $categoryNames = $allCategories->pluck('name', 'id');
        $otherIds      = $this->otherCategoryIds($allCategories);

        // Gastos del usuario agrupados por categoria.
        // Las categorias "Otro" se separan por el nombre que escribio el usuario.
        $gastosPorCategoria = Movement::where('user_id', $userId)
            ->where('type', 'gasto')
            ->selectRaw('category_id, custom_category, SUM(amount) as total')
            ->groupBy('category_id', 'custom_category')
            ->orderByDesc('total')
            ->get();

        // Historial del usuario
        $movements = Movement::where('user_id', $userId)
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('movements.index', compact(
            'ingresos', 'gastos', 'balance',
            'categories', 'categoryNames', 'otherIds',
            'gastosPorCategoria', 'movements'
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

        return redirect(route('movements.index') . '#historial')
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

        return redirect(route('movements.index') . '#historial')
            ->with('success', 'Movimiento actualizado correctamente.');
    }

    // Elimina un movimiento (solo si es del usuario logueado)
    public function destroy($id)
    {
        $movement = Movement::where('user_id', auth()->id())->findOrFail($id);
        $movement->delete();

        return redirect(route('movements.index') . '#historial')
            ->with('success', 'Movimiento eliminado correctamente.');
    }

    // ------------------------------------------------------------------
    // Auxiliares
    // ------------------------------------------------------------------

    // Validacion compartida por store() y update()
    private function validateMovement(Request $request): array
    {
        $otherIds = $this->otherCategoryIds(Category::all(['id', 'name']));
        $isOther  = in_array((int) $request->input('category_id'), $otherIds, true);

        $data = $request->validate([
            'type'   => 'required|in:ingreso,gasto',
            'amount' => 'required|numeric|min:0.01',
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

    // Asegura que existan las categorias base y devuelve todas ordenadas
    private function loadCategories()
    {
        $this->ensureDefaultCategories();

        return Category::orderBy('sort_order')->orderBy('id')->get();
    }

    // Crea (solo si faltan) Sueldo y otros tipos de ingreso, y "Otro" para ingresos y gastos
    private function ensureDefaultCategories(): void
    {
        $existing = Category::all(['name', 'type']);

        // [nombre, tipo, orden, nombres equivalentes que ya cuentan como existentes]
        $defaults = [
            ['Sueldo',                'ingreso', 1,  ['sueldo', 'salario']],
            ['Trabajo independiente', 'ingreso', 2,  ['trabajo independiente', 'freelance']],
            ['Ventas',                'ingreso', 3,  ['ventas', 'venta']],
            ['Arriendos',             'ingreso', 4,  ['arriendos', 'arriendo']],
            ['Otro',                  'ingreso', 98, ['otro', 'otros']],
            ['Otro',                  'gasto',   99, ['otro', 'otros']],
        ];

        foreach ($defaults as [$name, $type, $order, $aliases]) {
            $found = $existing->first(function ($c) use ($type, $aliases) {
                return $c->type === $type
                    && in_array(mb_strtolower(trim($c->name)), $aliases, true);
            });

            if ($found) {
                continue;
            }

            $category = new Category();
            $category->name       = $name;
            $category->type       = $type;
            $category->is_active  = true;
            $category->sort_order = $order;
            $category->save();
        }
    }

    // IDs de las categorias que se llaman "Otro" / "Otros"
    private function otherCategoryIds($categories): array
    {
        return $categories
            ->filter(fn ($c) => in_array(mb_strtolower(trim($c->name)), ['otro', 'otros'], true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
