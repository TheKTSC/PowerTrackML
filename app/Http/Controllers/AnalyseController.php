<?php
namespace App\Http\Controllers;

use App\Models\Saisie;
use Services\CalculService;
use Illuminate\Support\Facades\Auth;

class AnalyseController extends Controller {

    public function index() {
        $user       = Auth::user();
        $recepteurs = $user->recepteurs;
        $rIds       = $recepteurs->pluck('id')->toArray();
        $now        = now();

        // Bilan mois courant
        $pref        = $now->format('Y-m');
        $saisiesMois = Saisie::whereIn('recepteur_id', $rIds)
            ->where('date_saisie', 'like', "$pref%")->get();
        $bilanMois   = CalculService::bilanParRecepteur(
            $saisiesMois->all(), $recepteurs, $user
        );
        $totalKwh    = array_sum(array_column($bilanMois, 'kwh'));
        $totalCout   = array_sum(array_column($bilanMois, 'cout'));

        // Évolution 6 mois
        $evolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $m    = $now->copy()->subMonths($i);
            $pref2= $m->format('Y-m');
            $kwh  = Saisie::whereIn('recepteur_id', $rIds)
                ->where('date_saisie', 'like', "$pref2%")->sum('kwh');
            $s    = Saisie::whereIn('recepteur_id', $rIds)
                ->where('date_saisie', 'like', "$pref2%")->get();
            $b    = CalculService::bilanParRecepteur($s->all(), $recepteurs, $user);
            $cout = array_sum(array_column($b, 'cout'));
            $evolution[] = [
                'label' => $m->format('m/y'),
                'kwh'   => round($kwh, 2),
                'cout'  => round($cout, 2),
            ];
        }

        // Pics journaliers mois courant
        $parJour = [];
        $saisiesJour = Saisie::whereIn('recepteur_id', $rIds)
            ->where('date_saisie', 'like', $now->format('Y-m').'-%')
            ->where('periode', 'jour')->get();
        foreach ($saisiesJour as $s) {
            $parJour[$s->date_saisie] = ($parJour[$s->date_saisie] ?? 0) + $s->kwh;
        }
        $serie = array_map(fn($d,$k) => ['date'=>$d,'kwh'=>round($k,4)],
            array_keys($parJour), array_values($parJour));
        $pics  = CalculService::identifierPics($serie);

        return view('analyse.index', compact(
            'bilanMois','totalKwh','totalCout','evolution','pics','user'
        ));
    }
}