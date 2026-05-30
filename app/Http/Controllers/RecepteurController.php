<?php
namespace App\Http\Controllers;

use App\Models\Recepteur;
use App\Models\Saisie;
use Services\CalculService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecepteurController extends Controller {

    private array $types = [
        'climatiseur','refrigerateur','televiseur','ordinateur','lave_linge',
        'chauffe_eau','eclairage','ventilateur','four','pompe','moteur','autre'
    ];

    public function index() {
        $user  = Auth::user();
        $recs  = $user->recepteurs;
        $pref  = now()->format('Y-m');
        $kwhs  = [];
        foreach ($recs as $r) {
            $kwhs[$r->id] = Saisie::where('recepteur_id', $r->id)
                ->where('date_saisie', 'like', "$pref%")->sum('kwh');
        }
        return view('recepteurs.index', compact('recs','kwhs','user'));
    }

    public function create() {
        return view('recepteurs.create', ['types' => $this->types]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'nom'               => 'required|string|max:100',
            'type_equipement'   => 'required|string',
            'puissance_nominale'=> 'required|numeric|min:0.1',
            'est_moteur'        => 'nullable|boolean',
            'rendement'         => 'nullable|numeric|min:1|max:100',
            'anciennete'        => 'nullable|integer|min:0',
            'heures_par_jour'   => 'nullable|numeric|min:0|max:24',
            'jours_par_mois'    => 'nullable|integer|min:1|max:31',
            'cout_kwh'          => 'nullable|numeric|min:0',
            'usage_ge'          => 'nullable|boolean',
            'cout_kwh_ge'       => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:500',
        ]);

        $data['user_id']           = Auth::id();
        $data['est_moteur']        = $request->boolean('est_moteur');
        $data['usage_ge']          = $request->boolean('usage_ge');
        $data['puissance_absorbee']= CalculService::puissanceAbsorbee(
            $data['puissance_nominale'],
            $data['est_moteur'] ? ($data['rendement'] ?? null) : null
        );

        Recepteur::create($data);
        return redirect('/recepteurs')->with('success', 'Récepteur créé avec succès.');
    }

    public function show(int $id) {
        $user = Auth::user();
        $r    = Recepteur::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        // Historique 6 mois
        $historique = [];
        for ($i = 5; $i >= 0; $i--) {
            $m   = now()->subMonths($i);
            $pref= $m->format('Y-m');
            $kwh = Saisie::where('recepteur_id', $id)
                ->where('date_saisie', 'like', "$pref%")->sum('kwh');
            $historique[] = ['label' => $m->format('m/y'), 'kwh' => round($kwh, 4)];
        }

        $kwhMois    = Saisie::where('recepteur_id', $id)
            ->where('date_saisie', 'like', now()->format('Y-m').'%')->sum('kwh');
        $coutMois   = $kwhMois * $r->getCoutEffectif($user);
        $theoriqueM = CalculService::consommationTheorique($r);

        return view('recepteurs.show', compact('r','historique','kwhMois','coutMois','theoriqueM','user'));
    }

    public function edit(int $id) {
        $r = Recepteur::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('recepteurs.edit', ['r' => $r, 'types' => $this->types]);
    }

    public function update(Request $request, int $id) {
        $r = Recepteur::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $data = $request->validate([
            'nom'               => 'required|string|max:100',
            'type_equipement'   => 'required|string',
            'puissance_nominale'=> 'required|numeric|min:0.1',
            'est_moteur'        => 'nullable|boolean',
            'rendement'         => 'nullable|numeric|min:1|max:100',
            'anciennete'        => 'nullable|integer|min:0',
            'heures_par_jour'   => 'nullable|numeric|min:0|max:24',
            'jours_par_mois'    => 'nullable|integer|min:1|max:31',
            'cout_kwh'          => 'nullable|numeric|min:0',
            'usage_ge'          => 'nullable|boolean',
            'cout_kwh_ge'       => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:500',
        ]);

        $data['est_moteur']         = $request->boolean('est_moteur');
        $data['usage_ge']           = $request->boolean('usage_ge');
        $data['puissance_absorbee'] = CalculService::puissanceAbsorbee(
            $data['puissance_nominale'],
            $data['est_moteur'] ? ($data['rendement'] ?? null) : null
        );
        $r->update($data);
        return redirect('/recepteurs')->with('success', 'Récepteur modifié.');
    }

    public function destroy(int $id) {
        Recepteur::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();
        return redirect('/recepteurs')->with('success', 'Récepteur supprimé.');
    }
}