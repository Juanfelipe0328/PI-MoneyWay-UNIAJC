<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoneyAndMonths;
use App\Models\Category;
use App\Models\Movement;
use App\Services\RecurringService;
use Illuminate\Http\Request;

/**
 * Extracto mensual parecido al de un banco:
 * saldo anterior, cada movimiento con su saldo acumulado, totales y saldo final.
 */
class StatementController extends Controller
{
    use HandlesMoneyAndMonths;

    public function index(Request $request, RecurringService $recurrentes)
    {
        $userId = auth()->id();
        $hoy    = $this->hoy();
        $recurrentes->sync($userId, $hoy);

        $inicioMes = $this->parseMonth($request->query('mes'));
        $finMes    = $inicioMes->copy()->endOfMonth();
        $mes       = $inicioMes->format('Y-m');

        // Lo que habia antes de que empezara el mes
        $saldoAnterior =
              (float) Movement::where('user_id', $userId)->where('type', 'ingreso')
                  ->where('movement_date', '<', $inicioMes->toDateString())->sum('amount')
            - (float) Movement::where('user_id', $userId)->where('type', 'gasto')
                  ->where('movement_date', '<', $inicioMes->toDateString())->sum('amount');

        $nombres = Category::pluck('name', 'id');

        $movimientos = Movement::where('user_id', $userId)
            ->whereBetween('movement_date', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->orderBy('movement_date')
            ->orderBy('id')
            ->get();

        // Saldo acumulado linea por linea
        $saldo = $saldoAnterior;
        $totalIngresos = $totalGastos = 0.0;
        $filas = [];

        foreach ($movimientos as $m) {
            $valor = (float) $m->amount;

            if ($m->type === 'ingreso') {
                $saldo += $valor;
                $totalIngresos += $valor;
            } else {
                $saldo -= $valor;
                $totalGastos += $valor;
            }

            $cat = $nombres[$m->category_id] ?? 'Sin categoria';
            if ($m->custom_category) {
                $cat .= ': ' . $m->custom_category;
            }

            $filas[] = [
                'id'          => $m->id,
                'fecha'       => \Carbon\Carbon::parse($m->movement_date),
                'descripcion' => $m->description ?: $cat,
                'categoria'   => $cat,
                'tipo'        => $m->type,
                'valor'       => $valor,
                'saldo'       => $saldo,
                'fijo'        => $m->recurring_id !== null,
                'futuro'      => \Carbon\Carbon::parse($m->movement_date)->gt($hoy),
            ];
        }

        $saldoFinal = $saldo;

        // Fijos del mes en curso que todavia no han caido
        $pendientes = $inicioMes->format('Y-m') === $hoy->format('Y-m')
            ? $recurrentes->pendientes($userId, $hoy)
            : [];

        return view('statement.index', [
            'pendientes'    => $pendientes,
            'mes'           => $mes,
            'mesNombre'     => ucfirst($inicioMes->translatedFormat('F Y')),
            'mesPrev'       => $inicioMes->copy()->subMonthNoOverflow()->format('Y-m'),
            'mesNext'       => $inicioMes->copy()->addMonthNoOverflow()->format('Y-m'),
            'desde'         => $inicioMes,
            'hasta'         => $finMes,
            'filas'         => $filas,
            'saldoAnterior' => $saldoAnterior,
            'totalIngresos' => $totalIngresos,
            'totalGastos'   => $totalGastos,
            'saldoFinal'    => $saldoFinal,
            'hoy'           => $hoy,
        ]);
    }
}
