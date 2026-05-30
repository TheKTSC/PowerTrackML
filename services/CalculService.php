<?php
namespace Services;

use App\Models\Recepteur;
use App\Models\User;

class CalculService {

    public static function puissanceAbsorbee(float $puissanceNominale, ?float $rendement): float {
        if (!$rendement || $rendement <= 0) return $puissanceNominale;
        return $puissanceNominale / ($rendement / 100);
    }

    public static function consommationKwh(float $puissanceW, float $heures): float {
        return ($puissanceW / 1000) * $heures;
    }

    public static function coefficientDegradation(int $anciennete): float {
        return 1 + 0.02 * $anciennete;
    }

    public static function consommationTheorique(Recepteur $r): float {
        $pa    = $r->getPuissanceEffective();
        $h     = $r->heures_par_jour  ?? 0;
        $j     = $r->jours_par_mois   ?? 30;
        $degrad= self::coefficientDegradation($r->anciennete ?? 0);
        return ($pa / 1000) * $h * $j * $degrad;
    }

    public static function calculerCout(float $kwh, float $coutParKwh): float {
        return $kwh * $coutParKwh;
    }

    /**
     * Retourne le bilan de consommation par récepteur pour un mois donné
     */
    public static function bilanParRecepteur(
        array $saisies,
        \Illuminate\Support\Collection $recepteurs,
        User $user
    ): array {
        $totaux = [];
        foreach ($saisies as $s) {
            $totaux[$s->recepteur_id] = ($totaux[$s->recepteur_id] ?? 0) + $s->kwh;
        }
        $totalKwh = array_sum($totaux);
        $bilan    = [];

        foreach ($recepteurs as $r) {
            $kwh  = $totaux[$r->id] ?? 0;
            $cout = self::calculerCout($kwh, $r->getCoutEffectif($user));
            $bilan[] = [
                'recepteur_id' => $r->id,
                'nom'          => $r->nom,
                'type'         => $r->type_equipement,
                'kwh'          => round($kwh, 4),
                'cout'         => round($cout, 2),
                'pourcentage'  => $totalKwh > 0 ? round($kwh / $totalKwh * 100, 1) : 0,
            ];
        }

        usort($bilan, fn($a, $b) => $b['kwh'] <=> $a['kwh']);
        return $bilan;
    }

    public static function identifierPics(array $serieJournaliere, int $topN = 5): array {
        usort($serieJournaliere, fn($a, $b) => $b['kwh'] <=> $a['kwh']);
        return array_slice($serieJournaliere, 0, $topN);
    }

    public static function verifierSeuils(
        User $user,
        array $bilanRecepteurs,
        float $totalKwh
    ): array {
        $seuils          = $user->seuils;
        $nouvelles       = [];

        foreach ($seuils as $seuil) {
            if ($seuil->type_seuil === 'global') {
                $valeurTest = $seuil->unite === 'cout'
                    ? $totalKwh * $user->cout_kwh
                    : $totalKwh;
                if ($valeurTest >= $seuil->valeur) {
                    $alerte = \App\Models\Alerte::create([
                        'user_id'    => $user->id,
                        'type_alerte'=> 'global',
                        'valeur'     => round($valeurTest, 2),
                        'seuil'      => $seuil->valeur,
                    ]);
                    $nouvelles[] = $alerte;
                }
            } elseif ($seuil->type_seuil === 'recepteur' && $seuil->recepteur_id) {
                $b = collect($bilanRecepteurs)
                    ->firstWhere('recepteur_id', $seuil->recepteur_id);
                if ($b) {
                    $valeurTest = $seuil->unite === 'cout' ? $b['cout'] : $b['kwh'];
                    if ($valeurTest >= $seuil->valeur) {
                        $alerte = \App\Models\Alerte::create([
                            'user_id'      => $user->id,
                            'type_alerte'  => 'recepteur',
                            'recepteur_id' => $seuil->recepteur_id,
                            'nom_recepteur'=> $b['nom'],
                            'valeur'       => round($valeurTest, 2),
                            'seuil'        => $seuil->valeur,
                        ]);
                        $nouvelles[] = $alerte;
                    }
                }
            }
        }
        return $nouvelles;
    }
}