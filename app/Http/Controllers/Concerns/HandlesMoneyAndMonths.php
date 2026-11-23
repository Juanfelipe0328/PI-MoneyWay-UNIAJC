<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Category;
use Carbon\Carbon;

/**
 * Utilidades compartidas: zona horaria de Colombia, filtro por mes,
 * limpieza de valores como "10.000" y categorias base.
 */
trait HandlesMoneyAndMonths
{
    protected function hoy(): Carbon
    {
        return Carbon::now('America/Bogota')->startOfDay()->locale('es');
    }

    /**
     * Devuelve el primer dia del mes pedido (?mes=2026-11).
     * Si no viene o es invalido, usa el mes actual.
     */
    protected function parseMonth(?string $mes): Carbon
    {
        if ($mes && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            return Carbon::createFromFormat('!Y-m', $mes, 'America/Bogota')->locale('es');
        }

        return $this->hoy()->startOfMonth();
    }

    /**
     * Convierte lo que escribe el usuario ("10.000", "10.000,50", "$ 10000") en un numero ("10000", "10000.50").
     * El punto es separador de miles y la coma es el decimal (formato colombiano).
     */
    protected function cleanMoney($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = preg_replace('/[^\d,]/', '', (string) $value);
        if ($v === '') {
            return '';
        }

        return str_replace(',', '.', $v);
    }

    // IDs de las categorias que se llaman "Otro" / "Otros"
    protected function otherCategoryIds($categories): array
    {
        return $categories
            ->filter(fn ($c) => in_array(mb_strtolower(trim($c->name)), ['otro', 'otros'], true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    // Asegura que existan las categorias base y devuelve todas ordenadas
    protected function loadCategories()
    {
        $this->ensureDefaultCategories();

        return Category::orderBy('sort_order')->orderBy('id')->get();
    }

    // Crea (solo si faltan) Sueldo y otros tipos de ingreso, y "Otro" para ingresos y gastos
    protected function ensureDefaultCategories(): void
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
            // Categorias tipicas de gastos fijos (solo se crean si no existen)
            ['Vivienda',              'gasto',   10, ['vivienda', 'arriendo', 'hogar']],
            ['Servicios',             'gasto',   11, ['servicios', 'servicios publicos']],
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
}
