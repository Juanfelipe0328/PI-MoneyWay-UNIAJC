<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    
    public function showLoginForm()
    {
        return view('auth.login');
    }

    
   public function login(Request $request)
    {
        // 1. Valida Campos llenos
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. iniciar sesión
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Ingreso correctamente
            return redirect('/registrar'); 
        }

        // 3. Ingreso Incorrectamnte
        return back()->withErrors([
            'email' => 'El correo o la contraseña son incorrectos.',
        ]);
    }
}