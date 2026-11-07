<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyWay - Registrar Movimiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-teal-700 mb-6">Registrar manual</h2>

        <!-- Mensaje de éxito tras guardar -->
        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('movements.store') }}" method="POST">
            @csrf <!-- Token de seguridad de Laravel -->
            
            <div class="mb-4 flex space-x-4">
                <div class="w-1/2">
                    <label class="block font-medium mb-1 text-sm">Tipo</label>
                    <select name="type" class="w-full border border-slate-300 rounded px-3 py-2" required>
                        <option value="gasto">Gasto</option>
                        <option value="ingreso">Ingreso</option>
                    </select>
                </div>
                <div class="w-1/2">
                    <label class="block font-medium mb-1 text-sm">Valor (COP)</label>
                    <input type="number" name="amount" class="w-full border border-slate-300 rounded px-3 py-2" placeholder="Ej. 45000" required>
                </div>
            </div>

            <div class="mb-4 flex space-x-4">
                <div class="w-1/2">
                    <label class="block font-medium mb-1 text-sm">Categoría</label>
                    <select name="category_id" class="w-full border border-slate-300 rounded px-3 py-2" required>
                        <!-- Inyecta dinámicamente las categorías desde la base de datos -->
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-1/2">
                    <label class="block font-medium mb-1 text-sm">Fecha</label>
                    <input type="date" name="movement_date" class="w-full border border-slate-300 rounded px-3 py-2" required>
                </div>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-1 text-sm">Descripción (opcional)</label>
                <input type="text" name="description" class="w-full border border-slate-300 rounded px-3 py-2" placeholder="Ej. Almuerzo con el equipo">
            </div>

            <button type="submit" class="bg-teal-700 text-white px-4 py-2 rounded hover:bg-teal-800">
                Guardar
            </button>
        </form>
    </div>
</body>
</html>