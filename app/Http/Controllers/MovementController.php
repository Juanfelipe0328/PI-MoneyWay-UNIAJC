<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movement;
use App\Models\Category;

class MovementController extends Controller
{
    // Muestra el formulario en pantalla
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('movements.create', compact('categories'));
    }

    // Guarda los datos en la base de datos
    public function store(Request $request)
    {
        Movement::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'type' => $request->type,
            'amount' => $request->amount,
            'movement_date' => $request->movement_date,
            'description' => $request->description,
            'source' => 'manual',
        ]);

        return redirect()->back()->with('success', 'Movimiento registrado correctamente');
    }
}