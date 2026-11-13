<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ---------------- LOGIN ----------------

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Solo entran cuentas activas
        if (Auth::attempt([...$credentials, 'status' => 'activa'])) {
            $request->session()->regenerate();

            return redirect('/registrar');
        }

        return back()
            ->withErrors(['email' => 'El correo o la contraseña son incorrectos.'])
            ->onlyInput('email');
    }

    // ---------------- REGISTRO ----------------

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'terms'    => 'accepted',
        ], [
            'name.required'      => 'Escribe tu nombre.',
            'email.required'     => 'Escribe tu correo electrónico.',
            'email.email'        => 'Escribe un correo válido.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.required'  => 'Escribe una contraseña.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'terms.accepted'     => 'Debes aceptar el tratamiento de datos para continuar.',
        ]);

        $user = new User();
        $user->name                = $data['name'];
        $user->email               = $data['email'];
        $user->password            = Hash::make($data['password']);
        $user->role                = 'user';      // todo registro nuevo es usuario normal
        $user->status              = 'activa';
        $user->consent_accepted_at = now();
        $user->save();

        // Inicia sesión automáticamente
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/registrar');
    }

    // ---------------- LOGOUT ----------------

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
