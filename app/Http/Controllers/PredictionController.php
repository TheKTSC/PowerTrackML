<?php
namespace App\Http\Controllers;

use App\Models\Saisie;
use Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller {

    public function index() {
        $user      = Auth::user();
        $apiOk     = PredictionService::apiDisponible();
        $moisCible = now()->month === 12 ? 1 : now()->month + 1;
        return view('prediction.index', compact('user','apiOk','moisCible'));
    }

    public function predict(Request $request) {
        $user       = Auth::user();
        $recepteurs = $user->recepteurs;
        $moisCible  = now()->month === 12 ? 1 : now()->month + 1;
        $rIds       = $recepteurs->pluck('id')->toArray();

        // Historique 3 mois par récepteur
        $listeFeatures = [];
        foreach ($recepteurs as $r) {
            $historique = [];
            for ($i = 2; $i >= 0; $i--) {
                $pref = now()->subMonths($i)->format('Y-m');
                $historique[] = Saisie::where('recepteur_id', $r->id)
                    ->where('date_saisie', 'like', "$pref%")->sum('kwh');
            }
            $listeFeatures[] = PredictionService::preparerFeatures(
                $r, $user, $historique, $moisCible
            );
        }

        $resultat = PredictionService::predireTotal($listeFeatures);
        return view('prediction.index', [
            'user'       => $user,
            'apiOk'      => PredictionService::apiDisponible(),
            'moisCible'  => $moisCible,
            'prediction' => $resultat['data'],
            'mode'       => $resultat['mode'],
        ]);
    }
}