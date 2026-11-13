<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Movement;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    // Panel principal: resumen, formulario, categorías e historial (solo del usuario logueado)
    public function index()
    {
        $userId = auth()->id();

        $ingresos = Movement::where('user_id', $userId)->where('type', 'ingreso')->sum('amount');
        $gastos   = Movement::where('user_id', $userId)->where('type', 'gasto')->sum('amount');
        $balance  = $ingresos - $gastos;

        // Categorías disponibles para el formulario
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        // Nombres de todas las categorías (para mostrar en historial y resumen)
        $categoryNames = Category::pluck('name', 'id');

        // Gastos del usuario agrupados por categoría
        $gastosPorCategoria = Movement::where('user_id', $userId)
            ->where('type', 'gasto')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        // Historial del usuario
        $movements = Movement::where('user_id', $userId)
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('movements.index', compact(
            'ingresos', 'gastos', 'balance',
            'categories', 'categoryNames',
            'gastosPorCategoria', 'movements'
        ));
    }

    // Guarda un movimiento a nombre del usuario logueado
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'          => 'required|in:ingreso,gasto',
            'amount'        => 'required|numeric|min:0.01',
            'category_id'   => 'required|exists:categories,id',
            'movement_date' => 'required|date',
            'description'   => 'nullable|string|max:255',
        ]);

        $movement = new Movement();
        $movement->user_id       = auth()->id();   // siempre el usuario actual, nunca viene del formulario
        $movement->type          = $data['type'];
        $movement->amount        = $data['amount'];
        $movement->category_id   = $data['category_id'];
        $movement->movement_date = $data['movement_date'];
        $movement->description   = $data['description'] ?? null;
        $movement->source        = 'manual';
        $movement->save();

        return redirect(route('movements.index') . '#historial')
            ->with('success', 'Movimiento registrado correctamente.');
    }
}
