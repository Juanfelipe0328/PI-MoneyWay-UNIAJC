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
        // 1. Validar que los campos estén llenos
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Iniciar sesión solo si la cuenta está activa
        if (Auth::attempt([...$credentials, 'status' => 'activa'])) {
            $request->session()->regenerate();

            return redirect('/registrar');
        }

        // 3. Credenciales incorrectas (o cuenta suspendida/eliminada)
        return back()
            ->withErrors(['email' => 'El correo o la contraseña son incorrectos.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}