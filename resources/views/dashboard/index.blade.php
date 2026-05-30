@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="space-y-6 pt-4">

    {{-- Alerte active --}}
    @if($alertes->count() > 0)
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm font-semibold px-4 py-3 rounded-xl">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        {{ $alertes->count() }} alerte{{ $alertes->count() > 1 ? 's' : '' }} active{{ $alertes->count() > 1 ? 's' : '' }} —
        <a href="/alertes" class="underline">Voir les alertes</a>
    </div>
    @endif

    {{-- Bienvenue --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900">Bonjour, {{ explode(' ',$user->nom)[0] }} 👋</h2>
            <p class="text-sm text-gray-400 mt-0.5">{{ now()->isoFormat('dddd D MMMM YYYY') }}</p>
        </div>
        <a href="/saisie" class="flex items-center gap-2 bg-amber-400 text-white px-4 py-2 rounded-xl text-sm font-bold hover:opacity-90 transition-opacity shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Saisir aujourd'hui
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $kpiData = [
                ['titre'=>'Consommation mois','valeur'=>number_format($totalKwh,1).' kWh','sous'=>($variation !== null ? ($variation>0?'+':'').$variation.'% vs mois dernier' : null),'color'=>($variation>0?'text-red-500':'text-green-600'),'bg'=>($variation>0?'bg-red-50':'bg-green-50')],
                ['titre'=>'Coût estimé','valeur'=>number_format($totalCout,0).' '.$user->devise,'sous'=>$user->cout_kwh.' '.$user->devise.'/kWh','color'=>'text-amber-500','bg'=>'bg-amber-50'],
                ['titre'=>'Récepteurs','valeur'=>$recepteurs->count(),'sous'=>'équipements suivis','color'=>'text-blue-700','bg'=>'bg-blue-50'],
                ['titre'=>'Utilisateurs','valeur'=>$user->nombre_utilisateurs,'sous'=>$user->type_compte,'color'=>'text-indigo-600','bg'=>'bg-indigo-50'],
            ];
        @endphp
        @foreach($kpiData as $k)
        <div class="card">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">{{ $k['titre'] }}</p>
            <p class="text-2xl font-extrabold mt-1 {{ $k['color'] }}">{{ $k['valeur'] }}</p>
            @if($k['sous'])<p class="text-xs text-gray-400 mt-0.5">{{ $k['sous'] }}</p>@endif
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Graphique évolution --}}
        <div class="card">
            <p class="text-sm font-bold text-gray-900 mb-4">Évolution — 6 derniers mois</p>
            <canvas id="chartEvolution" height="200"></canvas>
        </div>

        {{-- Top récepteurs --}}
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-bold text-gray-900">Top récepteurs — mois en cours</p>
                <a href="/recepteurs" class="text-xs text-[#1A3A5C] font-semibold hover:underline">Voir tous →</a>
            </div>
            @forelse(array_slice($bilanMois,0,5) as $i => $b)
            <div class="flex items-center gap-3 mb-3">
                <span class="w-5 text-xs font-bold text-gray-400">#{{ $i+1 }}</span>
                <div class="flex-1">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-semibold text-gray-700">{{ $b['nom'] }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ $b['kwh'] }} kWh</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-[#1A3A5C] rounded-full h-1.5" style="width:{{ $b['pourcentage'] }}%"></div>
                    </div>
                </div>
                <span class="text-xs text-gray-400 w-8 text-right">{{ $b['pourcentage'] }}%</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">Aucune saisie ce mois-ci</p>
            @endforelse
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([['Saisir','/saisie','#F39C12'],['Analyse','/analyse','#1E8449'],['Prédiction','/prediction','#2980B9'],['Alertes','/alertes','#E74C3C']] as [$label,$path,$bg])
        <a href="{{ $path }}" style="background:{{ $bg }}" class="text-white rounded-xl p-4 flex flex-col items-center gap-2 hover:opacity-90 transition-opacity text-sm font-bold">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>
@endsection
@section('scripts')
<script>
new Chart(document.getElementById('chartEvolution'), {
    type: 'line',
    data: {
        labels: @json(array_column($evolution,'label')),
        datasets: [{
            label: 'kWh',
            data:  @json(array_column($evolution,'kwh')),
            borderColor: '#1A3A5C', backgroundColor: 'rgba(26,58,92,.08)',
            borderWidth: 2.5, tension: 0.4, fill: true,
            pointBackgroundColor: '#1A3A5C', pointRadius: 4,
        }]
    },
    options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});
</script>
@endsection