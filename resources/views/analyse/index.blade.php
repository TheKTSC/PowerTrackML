@extends('layouts.app')
@section('title', 'Analyse & Bilans')
@section('content')
<div class="space-y-5 pt-4">

    {{-- Onglets --}}
    <div class="flex gap-1 border-b border-gray-100" id="onglets">
        @foreach(['Évolution','Comparaison','Répartition','Pics'] as $i => $o)
        <button onclick="showOnglet({{ $i }})" id="tab-{{ $i }}"
            class="px-4 py-2 text-sm font-semibold rounded-t-lg border-b-2 -mb-px transition-all {{ $i===0 ? 'border-[#1A3A5C] text-[#1A3A5C] bg-blue-50/50' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
            {{ $o }}
        </button>
        @endforeach
    </div>

    {{-- Évolution --}}
    <div id="panel-0">
        <div class="card">
            <p class="text-sm font-bold text-gray-900 mb-4">Consommation globale — 6 derniers mois</p>
            @if(count($evolution) > 0)
            <canvas id="chartEvol" height="180"></canvas>
            <table class="w-full mt-4 text-sm border-t border-gray-100">
                <thead><tr class="text-xs text-gray-400 uppercase">
                    <th class="text-left py-2">Mois</th><th class="text-right py-2">kWh</th><th class="text-right py-2">Coût</th>
                </tr></thead>
                <tbody>
                    @foreach($evolution as $e)
                    <tr class="border-b border-gray-50">
                        <td class="py-1.5 text-gray-700">{{ $e['label'] }}</td>
                        <td class="py-1.5 text-right font-bold text-gray-900">{{ $e['kwh'] }} kWh</td>
                        <td class="py-1.5 text-right text-gray-400">{{ number_format($e['cout'],0) }} {{ auth()->user()->devise }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else <p class="text-sm text-gray-400 text-center py-10">Aucune donnée</p> @endif
        </div>
    </div>

    {{-- Comparaison --}}
    <div id="panel-1" class="hidden">
        <div class="card">
            <p class="text-sm font-bold text-gray-900 mb-4">Par récepteur — mois en cours</p>
            @if(count($bilanMois) > 0)
            <canvas id="chartBar" height="220"></canvas>
            <table class="w-full mt-4 text-sm border-t border-gray-100">
                <thead><tr class="text-xs text-gray-400 uppercase">
                    <th class="text-left py-2">Récepteur</th><th class="text-right py-2">kWh</th><th class="text-right py-2">Coût</th><th class="text-right py-2">Part</th>
                </tr></thead>
                <tbody>
                    @foreach($bilanMois as $b)
                    <tr class="border-b border-gray-50">
                        <td class="py-1.5 text-gray-700">{{ $b['nom'] }}</td>
                        <td class="py-1.5 text-right font-bold text-gray-900">{{ $b['kwh'] }} kWh</td>
                        <td class="py-1.5 text-right text-gray-400">{{ number_format($b['cout'],0) }} {{ auth()->user()->devise }}</td>
                        <td class="py-1.5 text-right text-gray-400">{{ $b['pourcentage'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else <p class="text-sm text-gray-400 text-center py-10">Aucune donnée ce mois-ci</p> @endif
        </div>
    </div>

    {{-- Répartition --}}
    <div id="panel-2" class="hidden">
        <div class="card flex flex-col items-center">
            <p class="text-sm font-bold text-gray-900 mb-4 self-start">Répartition — mois en cours</p>
            @if(count($bilanMois) > 0)
            <canvas id="chartPie" style="max-width:320px;max-height:320px"></canvas>
            @else <p class="text-sm text-gray-400 py-10">Aucune donnée ce mois-ci</p> @endif
        </div>
    </div>

    {{-- Pics --}}
    <div id="panel-3" class="hidden">
        <div class="card">
            <p class="text-sm font-bold text-gray-900 mb-4">Pics de consommation — mois en cours (Top 5)</p>
            @if(count($pics) > 0)
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-gray-400 uppercase border-b border-gray-100">
                    <th class="text-left py-2">Rang</th><th class="text-left py-2">Date</th><th class="text-right py-2">kWh</th>
                </tr></thead>
                <tbody>
                    @foreach($pics as $i => $p)
                    <tr class="border-b border-gray-50 {{ $i===0?'bg-red-50/50':'' }}">
                        <td class="py-2 font-extrabold text-gray-400">#{{ $i+1 }}</td>
                        <td class="py-2 text-gray-700">{{ $p['date'] }}</td>
                        <td class="py-2 text-right font-bold {{ $i===0?'text-red-500':'text-gray-900' }}">{{ $p['kwh'] }} kWh</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else <p class="text-sm text-gray-400 text-center py-10">Saisissez des données journalières pour identifier les pics</p> @endif
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function showOnglet(n) {
    [0,1,2,3].forEach(i => {
        document.getElementById('panel-'+i).classList.toggle('hidden', i!==n);
        const tab = document.getElementById('tab-'+i);
        if (i===n) { tab.classList.add('border-[#1A3A5C]','text-[#1A3A5C]','bg-blue-50/50'); tab.classList.remove('border-transparent','text-gray-400'); }
        else { tab.classList.remove('border-[#1A3A5C]','text-[#1A3A5C]','bg-blue-50/50'); tab.classList.add('border-transparent','text-gray-400'); }
    });
}
const COLORS = ['#1A3A5C','#F39C12','#1E8449','#E74C3C','#8E44AD','#2980B9','#17A589','#E67E22'];
const evol   = @json($evolution);
const bilan  = @json($bilanMois);
const pics   = @json($pics);

if (evol.length > 0) {
    new Chart(document.getElementById('chartEvol'),{
        type:'line',
        data:{ labels:evol.map(e=>e.label), datasets:[{ label:'kWh', data:evol.map(e=>e.kwh),
            borderColor:'#1A3A5C', backgroundColor:'rgba(26,58,92,.08)', borderWidth:2.5, tension:0.4, fill:true, pointRadius:4 }] },
        options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
    });
}
if (bilan.length > 0) {
    new Chart(document.getElementById('chartBar'),{
        type:'bar',
        data:{ labels:bilan.map(b=>b.nom.substring(0,12)),
            datasets:[{ data:bilan.map(b=>b.kwh), backgroundColor:COLORS, borderRadius:6 }] },
        options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
    });
    new Chart(document.getElementById('chartPie'),{
        type:'doughnut',
        data:{ labels:bilan.map(b=>b.nom), datasets:[{ data:bilan.map(b=>b.kwh), backgroundColor:COLORS, borderWidth:2 }] },
        options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}
    });
}
</script>
@endsection