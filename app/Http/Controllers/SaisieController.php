<?php
namespace App\Http\Controllers;

use App\Models\Recepteur;
use App\Models\Saisie;
use Services\CalculService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaisieController extends Controller {

    public function index() {
        $user       = Auth::user();
        $recepteurs = $user->recepteurs;
        return view('saisie.index', compact('recepteurs','user'));
    }

    public function store(Request $request) {
        $user = Auth::user();
        $request->validate([
            'periode'     => 'required|in:jour,semaine,mois',
            'date_saisie' => 'required|string',
            'saisies'     => 'required|array',
        ]);

        $recepteurs = $user->recepteurs;
        $rIds       = $recepteurs->pluck('id')->toArray();

        foreach ($request->saisies as $rId => $val) {
            if (!in_array((int)$rId, $rIds)) continue;

            $r   = $recepteurs->firstWhere('id', (int)$rId);
            $kwh = 0;
            $h   = null;
            $mode= 'manuel';

            if (!empty($val['heures'])) {
                $h    = (float)$val['heures'];
                $kwh  = CalculService::consommationKwh($r->getPuissanceEffective(), $h);
                $mode = 'auto';
            } elseif (!empty($val['kwh'])) {
                $kwh = (float)$val['kwh'];
            }

            if ($kwh <= 0) continue;

            // Upsert
            Saisie::updateOrCreate(
                [
                    'recepteur_id' => (int)$rId,
                    'date_saisie'  => $request->date_saisie,
                    'periode'      => $request->periode,
                ],
                ['kwh' => round($kwh,4), 'heures' => $h, 'mode_saisie' => $mode]
            );
        }

        // Vérification des seuils
        $pref        = substr($request->date_saisie, 0, 7);
        $saisiesMois = Saisie::whereIn('recepteur_id', $rIds)
            ->where('date_saisie', 'like', "$pref%")->get();
        $bilan       = CalculService::bilanParRecepteur($saisiesMois->all(), $recepteurs, $user);
        $totalKwh    = array_sum(array_column($bilan, 'kwh'));
        CalculService::verifierSeuils($user, $bilan, $totalKwh);

        return redirect('/saisie')->with('success', 'Consommations enregistrées avec succès.');
    }

    public function destroy(int $id) {
        $user  = Auth::user();
        $rIds  = $user->recepteurs->pluck('id')->toArray();
        $saisie= Saisie::whereIn('recepteur_id', $rIds)->findOrFail($id);
        $saisie->delete();
        return redirect('/saisie')->with('success', 'Saisie supprimée.');
    }
}