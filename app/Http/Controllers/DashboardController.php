<?php

namespace App\Http\Controllers;

use App\Models\Movement;

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

        return view('dashboard', [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $income - $expenses,
            'recentMovements' => $recentMovements,
            'monthLabel' => ucfirst(now()->locale('es')->translatedFormat('F Y')),
        ]);
    }
}