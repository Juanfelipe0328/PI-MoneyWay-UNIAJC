<?php

namespace App\Services;

/**
 * Genera recomendaciones de ahorro a partir de los numeros del mes.
 * Son reglas simples y no consulta la base de datos: recibe todo ya calculado.
 */
class RecommendationService
{
    // Orden en que se muestran (primero lo mas urgente)
    private const ORDEN = ['danger' => 0, 'warning' => 1, 'info' => 2, 'success' => 3];

    /**
     * @param array{
     *   mes: string,
     *   ingresos: float,
     *   gastos: float,
     *   gastosFijos?: float,          // parte de los gastos que viene de gastos fijos
     *   esMesActual?: bool, diaActual?: int, diasMes?: int,
     *   gastosMesPasado: float,       // mismo periodo del mes anterior (hasta el mismo dia)
     *   categorias: array<string,float>, // gastos del mes por categoria, de mayor a menor
     *   presupuestos: array<int,array{nombre:string,limite:float,gastado:float,pct:float}>,
     *   hayMovimientos: bool
     * } $d
     * @return array<int,array{nivel:string,titulo:string,mensaje:string}>
     */
    public function generate(array $d): array
    {
        if (! $d['hayMovimientos']) {
            return [[
                'nivel'   => 'info',
                'titulo'  => 'Empieza a registrar',
                'mensaje' => "Aun no tienes movimientos en {$d['mes']}. Anota tus ingresos y gastos para recibir recomendaciones personalizadas.",
            ]];
        }

        $r = [];
        $ingresos = $d['ingresos'];
        $gastos   = $d['gastos'];
        $fijos     = (float) ($d['gastosFijos'] ?? 0);
        $topNombre = array_key_first($d['categorias']);
        $topValor  = $topNombre !== null ? $d['categorias'][$topNombre] : 0;

        // 1. Presupuestos por categoria
        foreach ($d['presupuestos'] as $p) {
            if ($p['pct'] >= 100) {
                $r[] = $this->item('danger', 'Presupuesto superado',
                    "Superaste el presupuesto de {$p['nombre']}: llevas {$this->money($p['gastado'])} de {$this->money($p['limite'])}.");
            } elseif ($p['pct'] >= 80) {
                $r[] = $this->item('warning', 'Cerca del limite',
                    'Ya usaste el ' . round($p['pct']) . "% del presupuesto de {$p['nombre']} ({$this->money($p['gastado'])} de {$this->money($p['limite'])}).");
            }
        }

        // 2. Ahorro del mes
        if ($ingresos <= 0 && $gastos > 0) {
            $r[] = $this->item('warning', 'Falta registrar ingresos',
                'Tienes gastos registrados pero ningun ingreso este mes. Registra tus ingresos para calcular cuanto ahorras.');
        } elseif ($gastos > $ingresos) {
            $r[] = $this->item('danger', 'Gastas mas de lo que ganas',
                "Tus gastos ({$this->money($gastos)}) superan tus ingresos ({$this->money($ingresos)}) este mes. Revisa los gastos que puedas aplazar o reducir.");
        } elseif ($ingresos > 0) {
            $tasa = (($ingresos - $gastos) / $ingresos) * 100;
            if ($tasa >= 20) {
                $r[] = $this->item('success', 'Buen ritmo de ahorro',
                    'Estas ahorrando el ' . round($tasa) . '% de tus ingresos este mes. Mantenerlo es la mejor estrategia.');
            } else {
                $falta = max(0.0, $ingresos * 0.20 - ($ingresos - $gastos));
                $extra = $topNombre !== null ? " Una buena opcion es recortar en {$topNombre}." : '';
                $r[] = $this->item('info', 'Puedes ahorrar mas',
                    'Estas ahorrando el ' . round(max($tasa, 0)) . '% de tus ingresos. Para llegar al 20% recomendado te faltan ' . $this->money($falta) . ' este mes.' . $extra);
            }
        }

        // 3. Una categoria concentra demasiado gasto
        if ($gastos > 0 && $topNombre !== null) {
            $share = ($topValor / $gastos) * 100;
            if ($share >= 40) {
                $r[] = $this->item('info', 'Gasto concentrado',
                    "{$topNombre} representa el " . round($share) . "% de tus gastos del mes ({$this->money($topValor)}). Es la categoria con mas margen para ajustar.");
            }
        }

        // 3b. Gastos fijos muy pesados frente al ingreso
        if ($ingresos > 0 && $fijos > 0) {
            $pesoFijos = ($fijos / $ingresos) * 100;
            if ($pesoFijos >= 50) {
                $r[] = $this->item('warning', 'Tus gastos fijos pesan mucho',
                    'Tus gastos fijos son el ' . round($pesoFijos) . '% de tus ingresos ('
                    . $this->money($fijos) . '). Lo ideal es que no pasen del 50%: revisa si alguno se puede renegociar o cancelar.');
            }
        }

        // 3c. Proyeccion al cierre del mes (solo mes en curso)
        if (! empty($d['esMesActual']) && ($d['diaActual'] ?? 0) >= 5 && ($d['diasMes'] ?? 0) > 0 && $gastos <= $ingresos && $ingresos > 0) {
            $variable   = max(0.0, $gastos - $fijos);
            $proyectado = $fijos + ($variable / $d['diaActual']) * $d['diasMes'];
            if ($proyectado > $ingresos) {
                $r[] = $this->item('warning', 'Vas a cerrar el mes en rojo',
                    'A este ritmo tus gastos llegarian a ' . $this->money($proyectado)
                    . ' al final del mes, por encima de tus ingresos (' . $this->money($ingresos) . '). Baja el gasto diario para evitarlo.');
            }
        }

        // 4. Comparacion con el mismo periodo del mes pasado
        if ($d['gastosMesPasado'] > 0) {
            $cambio = (($gastos - $d['gastosMesPasado']) / $d['gastosMesPasado']) * 100;
            if ($cambio >= 20) {
                $r[] = $this->item('warning', 'Tus gastos suben',
                    'Llevas un ' . round($cambio) . '% mas de gasto que en el mismo periodo del mes anterior. Revisa en que categoria subio.');
            } elseif ($cambio <= -10) {
                $r[] = $this->item('success', 'Estas gastando menos',
                    'Llevas un ' . round(abs($cambio)) . '% menos de gasto que en el mismo periodo del mes pasado.');
            }
        }

        if (! $r) {
            $r[] = $this->item('success', 'Todo en orden',
                'Tus finanzas de este mes se ven estables. Sigue registrando tus movimientos para mantener el control.');
        }

        usort($r, fn ($a, $b) => self::ORDEN[$a['nivel']] <=> self::ORDEN[$b['nivel']]);

        return array_slice($r, 0, 6);
    }

    private function item(string $nivel, string $titulo, string $mensaje): array
    {
        return ['nivel' => $nivel, 'titulo' => $titulo, 'mensaje' => $mensaje];
    }

    private function money(float $n): string
    {
        return '$' . number_format($n, 0, ',', '.');
    }
}
