<?php
namespace App\Http\Controllers;

use App\Models\Saisie;
use Services\CalculService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {

    public function index() {
        $user     = Auth::user();
        $now      = now();
        $prefixe  = $now->format('Y-m');

        $recepteurs = $user->recepteurs;
        $rIds       = $recepteurs->pluck('id')->toArray();

        // Consommation mois courant
        $saisiesMois = Saisie::whereIn('recepteur_id', $rIds)
            ->where('date_saisie', 'like', "$prefixe%")
            ->get();
        $bilanMois   = CalculService::bilanParRecepteur(
            $saisiesMois->all(), $recepteurs, $user
        );
        $totalKwh    = array_sum(array_column($bilanMois, 'kwh'));
        $totalCout   = array_sum(array_column($bilanMois, 'cout'));

        // Mois précédent
        $moisPrec   = $now->copy()->subMonth()->format('Y-m');
        $saisiesPrec= Saisie::whereIn('recepteur_id', $rIds)
            ->where('date_saisie', 'like', "$moisPrec%")
            ->get();
        $totalPrec  = $saisiesPrec->sum('kwh');
        $variation  = $totalPrec > 0
            ? round(($totalKwh - $totalPrec) / $totalPrec * 100, 1)
            : null;

        // Évolution 6 mois
        $evolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $m      = $now->copy()->subMonths($i);
            $pref   = $m->format('Y-m');
            $kwh    = Saisie::whereIn('recepteur_id', $rIds)
                ->where('date_saisie', 'like', "$pref%")
                ->sum('kwh');
            $evolution[] = ['label' => $m->format('m/y'), 'kwh' => round($kwh, 2)];
        }

        // Alertes récentes
        $alertes = $user->alertes()
            ->orderBy('created_at','desc')
            ->limit(3)->get();

        return view('dashboard.index', compact(
            'user','totalKwh','totalCout','variation',
            'bilanMois','evolution','alertes','recepteurs'
        ));
    }
}