<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $movementsOfMonth = Movement::query()
            ->where('user_id', $userId)
            ->whereBetween('movement_date', [$monthStart, $monthEnd]);

        $income = (clone $movementsOfMonth)
            ->whereIn('type', ['ingreso', 'income'])
            ->sum('amount');

        $expenses = (clone $movementsOfMonth)
            ->whereIn('type', ['gasto', 'expense'])
            ->sum('amount');

        $recentMovements = Movement::query()
            ->with('category')
            ->where('user_id', $userId)
            ->latest('movement_date')
            ->latest('id')
            ->take(5)
            ->get();

         $topExpenseCategory = Movement::query()
         ->join('categories', 'movements.category_id', '=', 'categories.id')
         ->where('movements.user_id', $userId)
         ->whereBetween('movements.movement_date', [$monthStart, $monthEnd])
         ->whereIn('movements.type', ['gasto', 'expense'])
         ->select(
        'categories.name',
        DB::raw('SUM(movements.amount) as total')
        )
        ->groupBy('categories.id', 'categories.name')
        ->orderByDesc('total')
        ->first();

        return view('dashboard', [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $income - $expenses,
            'recentMovements' => $recentMovements,
            'monthLabel' => ucfirst(now()->locale('es')->translatedFormat('F Y')),
            'topExpenseCategory' => $topExpenseCategory,
        ]);
    }
}