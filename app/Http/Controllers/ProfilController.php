<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller {

    public function index() {
        return view('profil.index', ['user' => Auth::user()]);
    }

    public function update(Request $request) {
        $user = Auth::user();
        $request->validate([
            'nom'                 => 'required|string|max:100',
            'type_compte'         => 'required|in:Particulier,Entreprise',
            'nombre_utilisateurs' => 'required|integer|min:1',
        ]);
        $user->update($request->only('nom','type_compte','nombre_utilisateurs'));
        return redirect('/profil')->with('success', 'Profil mis à jour.');
    }

    public function updateParametres(Request $request) {
        $user = Auth::user();
        $request->validate([
            'cout_kwh'              => 'required|numeric|min:0',
            'devise'                => 'required|in:FCFA,EUR,USD',
            'notifications_actives' => 'nullable|boolean',
        ]);
        $user->update([
            'cout_kwh'              => $request->cout_kwh,
            'devise'                => $request->devise,
            'notifications_actives' => $request->boolean('notifications_actives'),
        ]);
        return redirect('/profil')->with('success', 'Paramètres enregistrés.');
    }
}