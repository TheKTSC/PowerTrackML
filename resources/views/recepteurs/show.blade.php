@extends('layouts.app')
@section('title', $r->nom)
@section('content')
<div class="space-y-5 pt-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/recepteurs" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-4.5 h-4.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-extrabold text-gray-900">{{ $r->nom }}</h2>
                <p class="text-sm text-gray-400 capitalize">{{ str_replace('_',' ',$r->type_equipement) }}</p>
            </div>
            @if($r->est_moteur)<span class="badge-info">Moteur</span>@endif
            @if($r->usage_ge)<span class="badge-warning ml-1">Groupe élec.</span>@endif
        </div>
        <div class="flex gap-2">
            <a href="/recepteurs/{{ $r->id }}/edit" class="btn-secondary text-sm flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Modifier
            </a>
            <form method="POST" action="/recepteurs/{{ $r->id }}" onsubmit="return confirm('Supprimer {{ $r->nom }} ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger text-sm">Supprimer</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Stats --}}
        <div class="space-y-4">
            <div class="card">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Conso mois en cours</p>
                <p class="text-3xl font-extrabold text-[#1A3A5C]">{{ number_format($kwhMois,2) }} kWh</p>
                <p class="text-sm text-gray-400 mt-1">{{ number_format($coutMois,0) }} {{ $user->devise }}</p>
                @php $badge = $kwhMois===0.0?['neutral','Inactif']:($kwhMois<30?['success','Faible']:($kwhMois<100?['warning','Modéré']:['danger','Élevé'])); @endphp
                <span class="badge-{{ $badge[0] }} mt-2">{{ $badge[1] }}</span>
            </div>
            <div class="card">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Caractéristiques</p>
                <div class="space-y-2 text-sm">
                    @foreach([
                        ['Puissance nominale', $r->puissance_nominale.' W'],
                        $r->est_moteur ? ['Rendement (η)', $r->rendement.' %'] : null,
                        ['Puissance absorbée (Pa)', number_format($r->getPuissanceEffective(),1).' W', true],
                        $r->anciennete > 0 ? ['Ancienneté', $r->anciennete.' an'.($r->anciennete>1?'s':'')] : null,
                        $r->anciennete > 0 ? ['Dégradation estimée', '+'.(int)($r->anciennete*2).' %', true] : null,
                        $r->heures_par_jour ? ['Usage typique', $r->heures_par_jour.'h/j × '.$r->jours_par_mois.'j/mois'] : null,
                        ['Conso théorique/mois', number_format($theoriqueM,3).' kWh', true],
                    ] as $ligne)
                    @if($ligne)
                    <div class="flex justify-between py-1.5 border-b border-gray-50 {{ isset($ligne[2]) && $ligne[2] ? 'bg-blue-50/60 -mx-1 px-1 rounded' : '' }}">
                        <span class="text-gray-500">{{ $ligne[0] }}</span>
                        <span class="font-bold {{ isset($ligne[2]) && $ligne[2] ? 'text-[#1A3A5C]' : 'text-gray-900' }}">{{ $ligne[1] }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Graphique --}}
        <div class="card lg:col-span-2">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Évolution 6 mois</p>
            <canvas id="chartRec" height="180"></canvas>
            <table class="w-full mt-4 text-sm border-t border-gray-100 pt-2">
                <thead><tr class="text-xs text-gray-400 uppercase">
                    <th class="text-left py-2">Mois</th>
                    <th class="text-right py-2">kWh</th>
                    <th class="text-right py-2">Coût</th>
                </tr></thead>
                <tbody>
                    @foreach($historique as $h)
                    <tr class="border-b border-gray-50">
                        <td class="py-1.5 text-gray-600">{{ $h['label'] }}</td>
                        <td class="py-1.5 text-right font-bold text-gray-900">{{ $h['kwh'] }} kWh</td>
                        <td class="py-1.5 text-right text-gray-400">{{ number_format($h['kwh']*$r->getCoutEffectif($user),0) }} {{ $user->devise }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
new Chart(document.getElementById('chartRec'),{
    type:'line',
    data:{
        labels: @json(array_column($historique,'label')),
        datasets:[{ label:'kWh', data: @json(array_column($historique,'kwh')),
            borderColor:'#1A3A5C', backgroundColor:'rgba(26,58,92,.08)',
            borderWidth:2.5, tension:0.4, fill:true, pointRadius:4, pointBackgroundColor:'#1A3A5C'
        }]
    },
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
});
</script>
@endsection