<?php
namespace App\Http\Controllers;

use App\Models\Alerte;
use App\Models\Seuil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlerteController extends Controller {

    public function index() {
        $user      = Auth::user();
        $seuils    = $user->seuils;
        $historique= $user->alertes()->orderBy('created_at','desc')->limit(100)->get();
        $recepteurs= $user->recepteurs;
        return view('alertes.index', compact('seuils','historique','recepteurs','user'));
    }

    public function updateSeuils(Request $request) {
        $user = Auth::user();
        $request->validate([
            'seuils'              => 'nullable|array',
            'seuils.*.type_seuil' => 'required|in:global,recepteur',
            'seuils.*.valeur'     => 'required|numeric|min:0',
            'seuils.*.unite'      => 'required|in:kwh,cout',
        ]);

        Seuil::where('user_id', $user->id)->delete();
        foreach ($request->seuils ?? [] as $s) {
            if (empty($s['valeur'])) continue;
            Seuil::create([
                'user_id'      => $user->id,
                'type_seuil'   => $s['type_seuil'],
                'recepteur_id' => $s['recepteur_id'] ?? null,
                'valeur'       => (float)$s['valeur'],
                'unite'        => $s['unite'],
            ]);
        }
        return redirect('/alertes')->with('success', 'Seuils enregistrés.');
    }

    public function effacerHistorique() {
        Alerte::where('user_id', Auth::id())->delete();
        return redirect('/alertes')->with('success', 'Historique effacé.');
    }
}