<?php

namespace App\Services;

use App\Models\Movement;
use App\Models\RecurringItem;
use Carbon\Carbon;

/**
 * Convierte las plantillas de sueldo / gastos fijos en movimientos reales.
 *
 * Se ejecuta cada vez que el usuario abre el dashboard, el extracto o sus movimientos:
 * por cada plantilla activa crea el movimiento de cada mes (desde start_month hasta el mes actual)
 * que todavia no exista. Es seguro llamarlo muchas veces: la llave unica
 * (recurring_id, recurring_period) evita duplicados.
 */
class RecurringService
{
    // Maximo de meses hacia atras que se rellenan de una sola vez
    private const MAX_MESES_ATRAS = 24;

    public function sync(int $userId, Carbon $hoy): void
    {
        $items = RecurringItem::where('user_id', $userId)->where('is_active', true)->get();
        if ($items->isEmpty()) {
            return;
        }

        $mesActual = $hoy->copy()->startOfMonth();
        $limiteAtras = $mesActual->copy()->subMonthsNoOverflow(self::MAX_MESES_ATRAS);
        $ahora = now();

        foreach ($items as $item) {
            $desde = Carbon::createFromFormat('!Y-m', $item->start_month, 'America/Bogota');
            if ($desde->lt($limiteAtras)) {
                $desde = $limiteAtras->copy();
            }

            $hasta = $mesActual->copy();
            if ($item->end_month) {
                $fin = Carbon::createFromFormat('!Y-m', $item->end_month, 'America/Bogota');
                if ($fin->lt($hasta)) {
                    $hasta = $fin;
                }
            }

            $yaCreados = Movement::where('recurring_id', $item->id)->pluck('recurring_period')->all();

            $filas = [];
            for ($mes = $desde->copy(); $mes->lte($hasta); $mes->addMonthNoOverflow()) {
                $periodo = $mes->format('Y-m');
                if (in_array($periodo, $yaCreados, true)) {
                    continue;
                }

                // Si el dia es 31 y el mes tiene 30 (o febrero), cae el ultimo dia del mes
                $dia = min((int) $item->day_of_month, $mes->daysInMonth);

                // En el mes actual solo se registra cuando llega el dia (como un banco:
                // el sueldo no cuenta en tu saldo antes de que te lo paguen).
                if ($periodo === $mesActual->format('Y-m') && $dia > $hoy->day) {
                    continue;
                }

                $filas[] = [
                    'user_id'          => $item->user_id,
                    'category_id'      => $item->category_id,
                    'type'             => $item->type,
                    'amount'           => $item->amount,
                    'movement_date'    => $mes->copy()->day($dia)->toDateString(),
                    'description'      => $item->description,
                    'custom_category'  => $item->custom_category,
                    'source'           => 'fijo',
                    'recurring_id'     => $item->id,
                    'recurring_period' => $periodo,
                    'created_at'       => $ahora,
                    'updated_at'       => $ahora,
                ];
            }

            if ($filas) {
                // insertOrIgnore: si dos pestañas lo ejecutan a la vez, la llave unica evita duplicados
                Movement::insertOrIgnore($filas);
            }
        }
    }

    /**
     * Fijos del mes actual que todavia no han caido (para mostrarlos como "proximos").
     *
     * @return array<int,array{fecha:Carbon,descripcion:string,tipo:string,valor:float}>
     */
    public function pendientes(int $userId, Carbon $hoy): array
    {
        $periodo = $hoy->format('Y-m');
        $items = RecurringItem::where('user_id', $userId)->where('is_active', true)
            ->where('start_month', '<=', $periodo)
            ->where(fn ($q) => $q->whereNull('end_month')->orWhere('end_month', '>=', $periodo))
            ->get();

        $hechos = Movement::where('user_id', $userId)->where('recurring_period', $periodo)
            ->pluck('recurring_id')->all();

        $r = [];
        foreach ($items as $item) {
            if (in_array((int) $item->id, array_map("intval", $hechos), true)) {
                continue;
            }
            $dia = min((int) $item->day_of_month, $hoy->daysInMonth);
            if ($dia <= $hoy->day) {
                continue;
            }
            $r[] = [
                'fecha'       => $hoy->copy()->day($dia),
                'descripcion' => $item->description,
                'tipo'        => $item->type,
                'valor'       => (float) $item->amount,
            ];
        }
        usort($r, fn ($a, $b) => $a['fecha'] <=> $b['fecha']);

        return $r;
    }
}
