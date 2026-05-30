<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {

    public function showLogin()    { return view('auth.login');    }
    public function showRegister() { return view('auth.register'); }

    public function register(Request $request) {
        $request->validate([
            'nom'                 => 'required|string|max:100',
            'email'               => 'required|email|unique:users',
            'password'            => 'required|min:6|confirmed',
            'type_compte'         => 'required|in:Particulier,Entreprise',
            'nombre_utilisateurs' => 'required|integer|min:1',
            'cout_kwh'            => 'nullable|numeric|min:0',
            'devise'              => 'nullable|in:FCFA,EUR,USD',
        ]);

        $user = User::create([
            'nom'                 => $request->nom,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'type_compte'         => $request->type_compte,
            'nombre_utilisateurs' => $request->nombre_utilisateurs,
            'cout_kwh'            => $request->cout_kwh   ?? 100,
            'devise'              => $request->devise      ?? 'FCFA',
        ]);

        Auth::login($user);
        return redirect('/')->with('success', 'Bienvenue sur PowerTrack !');
    }

    public function login(Request $request) {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email','password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Email ou mot de passe incorrect.']);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}