<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoneyAndMonths;
use App\Models\Category;
use App\Models\RecurringItem;
use App\Services\RecurringService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Sueldo y gastos fijos. Aqui se definen una vez y MoneyWay los registra solo cada mes.
 */
class RecurringController extends Controller
{
    use HandlesMoneyAndMonths;

    public function index(RecurringService $recurrentes)
    {
        $userId = auth()->id();
        $recurrentes->sync($userId, $this->hoy());

        $allCategories = $this->loadCategories();
        $categories    = $allCategories->where('is_active', true)->values();
        $categoryNames = $allCategories->pluck('name', 'id');
        $otherIds      = $this->otherCategoryIds($allCategories);

        $items = RecurringItem::where('user_id', $userId)
            ->orderByDesc('is_active')->orderBy('type')->orderBy('day_of_month')->get();

        $totalIngresos = (float) $items->where('is_active', true)->where('type', 'ingreso')->sum('amount');
        $totalGastos   = (float) $items->where('is_active', true)->where('type', 'gasto')->sum('amount');

        return view('recurring.index', [
            'item'          => null,
            'items'         => $items,
            'categories'    => $categories,
            'categoryNames' => $categoryNames,
            'otherIds'      => $otherIds,
            'totalIngresos' => $totalIngresos,
            'totalGastos'   => $totalGastos,
            'mesActual'     => $this->hoy()->format('Y-m'),
        ]);
    }

    public function store(Request $request, RecurringService $recurrentes)
    {
        $data = $this->validateItem($request);

        $item = new RecurringItem($data);
        $item->user_id   = auth()->id();   // nunca viene del formulario
        $item->is_active = true;
        $item->save();

        // Se refleja de inmediato en el dashboard, los movimientos y el extracto
        $recurrentes->sync(auth()->id(), $this->hoy());

        return redirect()->route('recurring.index')->with('success', 'Guardado. Ya aparece en tus movimientos y se repetira cada mes.');
    }

    public function edit($id)
    {
        $item = RecurringItem::where('user_id', auth()->id())->findOrFail($id);

        $allCategories = $this->loadCategories();

        return view('recurring.edit', [
            'item'       => $item,
            'categories' => $allCategories->where('is_active', true)->values(),
            'otherIds'   => $this->otherCategoryIds($allCategories),
        ]);
    }

    public function update(Request $request, $id, RecurringService $recurrentes)
    {
        $item = RecurringItem::where('user_id', auth()->id())->findOrFail($id);

        $item->fill($this->validateItem($request))->save();
        $recurrentes->sync(auth()->id(), $this->hoy());

        return redirect()->route('recurring.index')
            ->with('success', 'Actualizado. Los cambios aplican desde los proximos meses; lo ya registrado no se modifica.');
    }

    // Activa / pausa sin borrar
    public function toggle($id)
    {
        $item = RecurringItem::where('user_id', auth()->id())->findOrFail($id);
        $item->is_active = ! $item->is_active;
        $item->save();

        return redirect()->route('recurring.index')
            ->with('success', $item->is_active ? 'Activado.' : 'Pausado: no se registrara en los proximos meses.');
    }

    // Borra la plantilla. Los movimientos ya generados se conservan en el historial.
    public function destroy($id)
    {
        RecurringItem::where('user_id', auth()->id())->findOrFail($id)->delete();

        return redirect()->route('recurring.index')
            ->with('success', 'Eliminado. Lo que ya se registro en meses anteriores se conserva.');
    }

    private function validateItem(Request $request): array
    {
        $request->merge(['amount' => $this->cleanMoney($request->input('amount'))]);

        $otherIds = $this->otherCategoryIds(Category::all(['id', 'name']));
        $isOther  = in_array((int) $request->input('category_id'), $otherIds, true);

        $data = $request->validate([
            'type'         => 'required|in:ingreso,gasto',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:1|max:9999999999',
            'category_id'  => [
                'required',
                Rule::exists('categories', 'id')->where(function ($q) use ($request) {
                    $q->where('is_active', true)->where('type', $request->input('type'));
                }),
            ],
            'custom_category' => [Rule::requiredIf($isOther), 'nullable', 'string', 'max:60'],
            'day_of_month'    => 'required|integer|min:1|max:31',
            'start_month'     => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'end_month'       => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ], [
            'description.required'     => 'Ponle un nombre (ej. Sueldo, Arriendo, Internet).',
            'amount.required'          => 'Escribe el valor.',
            'amount.numeric'           => 'El valor debe ser un numero.',
            'amount.min'               => 'El valor debe ser mayor a cero.',
            'custom_category.required' => 'Escribe cual es la categoria.',
            'day_of_month.required'    => 'Indica el dia del mes en que cae.',
            'day_of_month.min'         => 'El dia debe estar entre 1 y 31.',
            'day_of_month.max'         => 'El dia debe estar entre 1 y 31.',
            'start_month.required'     => 'Indica desde que mes aplica.',
            'start_month.regex'        => 'El mes de inicio no es valido.',
            'end_month.regex'          => 'El mes final no es valido.',
        ]);

        $data['custom_category'] = $isOther
            ? Str::ucfirst(Str::lower(trim($data['custom_category'])))
            : null;
        $data['end_month'] = $data['end_month'] ?: null;

        // "YYYY-MM" se puede comparar como texto
        if ($data['end_month'] && $data['end_month'] < $data['start_month']) {
            throw ValidationException::withMessages([
                'end_month' => 'El mes final no puede ser anterior al inicial.',
            ]);
        }

        return $data;
    }
}
