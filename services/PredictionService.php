<?php
namespace Services;

use Illuminate\Support\Facades\Http;
use App\Models\Recepteur;
use App\Models\User;

class PredictionService {

    private static function apiUrl(): string {
        return rtrim(env('FLASK_API_URL', 'http://127.0.0.1:5000'), '/');
    }

    public static function apiDisponible(): bool {
        try {
            $res = Http::timeout(3)->get(self::apiUrl() . '/health');
            return $res->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function preparerFeatures(
        Recepteur $r,
        User $user,
        array $historiqueKwh,
        int $moisCible
    ): array {
        $moy3 = count($historiqueKwh) > 0
            ? array_sum($historiqueKwh) / count($historiqueKwh)
            : 0;
        return [
            'id'                        => $r->id,
            'nom'                       => $r->nom,
            'type_equipement'           => $r->type_equipement,
            'puissance_w'               => $r->getPuissanceEffective(),
            'heures_utilisation_jour'   => $r->heures_par_jour   ?? 0,
            'jours_utilisation_mois'    => $r->jours_par_mois    ?? 30,
            'anciennete_equipement_ans' => $r->anciennete        ?? 0,
            'type_utilisateur'          => $user->type_compte,
            'nombre_utilisateurs'       => $user->nombre_utilisateurs,
            'mois'                      => $moisCible,
            'conso_mois_precedent_kwh'  => end($historiqueKwh) ?: 0,
            'conso_moyenne_3mois_kwh'   => $moy3,
        ];
    }

    public static function predireTotal(array $listeFeatures): array {
        try {
            $res = Http::timeout(10)
                ->post(self::apiUrl() . '/predict/total', [
                    'recepteurs' => $listeFeatures,
                ]);
            if ($res->successful()) {
                $json = $res->json();
                $data = $json['data'] ?? $json;

                if (isset($data['recepteurs']) && is_array($data['recepteurs'])) {
                    foreach ($data['recepteurs'] as $idx => $r) {
                        if (!array_key_exists('interpretation', $r)) {
                            $data['recepteurs'][$idx]['interpretation'] = self::genererInterpretation(
                                $listeFeatures[$idx] ?? [],
                                floatval($r['kwh_predit'] ?? 0)
                            );
                        }
                    }
                }

                return ['succes' => true, 'data' => $data, 'mode' => 'modele'];
            }
            throw new \Exception('Réponse API invalide');
        } catch (\Exception $e) {
            // Mode simulation : calcul physique de secours
            return self::simulerTotal($listeFeatures);
        }
    }

    private static function simulerTotal(array $listeFeatures): array {
        $resultats   = [];
        $totalKwh    = 0;
        $totalActuel = 0;

        foreach ($listeFeatures as $f) {
            $pa        = ($f['puissance_w'] ?? 0) / 1000;
            $h         = $f['heures_utilisation_jour']   ?? 0;
            $j         = $f['jours_utilisation_mois']    ?? 30;
            $anciennete= $f['anciennete_equipement_ans'] ?? 0;
            $degrad    = 1 + 0.02 * $anciennete;
            $kwh       = round($pa * $h * $j * $degrad, 4);

            $resultats[] = [
                'id'            => $f['id'],
                'nom'           => $f['nom'],
                'type'          => $f['type_equipement'],
                'kwh_predit'    => $kwh,
                'interpretation'=> self::genererInterpretation($f, $kwh),
            ];
            $totalKwh    += $kwh;
            $totalActuel += $f['conso_mois_precedent_kwh'] ?? 0;
        }

        $variation = $totalActuel > 0
            ? round(($totalKwh - $totalActuel) / $totalActuel * 100, 2)
            : null;

        return [
            'succes'        => true,
            'data'          => [
                'total_kwh'     => round($totalKwh, 4),
                'variation'     => $variation,
                'recepteurs'    => $resultats,
                'nb_recepteurs' => count($resultats),
            ],
            'mode' => 'simulation',
        ];
    }

    private static function genererInterpretation(array $f, float $kwh): string {
        $mois      = (int)($f['mois']                      ?? 1);
        $type      = $f['type_equipement']                 ?? '';
        $anciennete= (float)($f['anciennete_equipement_ans']?? 0);
        $actuel    = (float)($f['conso_mois_precedent_kwh'] ?? 0);
        $msgs      = [];

        if (in_array($mois, [6,7,8,9]) && in_array($type, ['climatiseur','ventilateur']))
            $msgs[] = 'Hausse attendue liée à la saison chaude.';
        if (in_array($mois, [12,1,2]) && $type === 'chauffe_eau')
            $msgs[] = 'Usage plus intense en saison froide.';
        if ($anciennete >= 5)
            $msgs[] = "Dégradation estimée : +{$anciennete}% (appareil de {$anciennete} ans).";
        if ($actuel > 0) {
            $var = ($kwh - $actuel) / $actuel * 100;
            if ($var > 10)       $msgs[] = sprintf('Hausse prévue de %.1f%% vs mois actuel.', $var);
            elseif ($var < -10)  $msgs[] = sprintf('Baisse prévue de %.1f%% vs mois actuel.', abs($var));
        }
        return implode(' ', $msgs) ?: 'Consommation stable prévue.';
    }
}