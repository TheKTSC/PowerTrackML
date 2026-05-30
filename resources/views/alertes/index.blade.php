@extends('layouts.app')
@section('title', 'Alertes & Seuils')
@section('content')
<div class="space-y-5 pt-4" x-data="{onglet:0}">

    {{-- Onglets --}}
    <div class="flex gap-1 border-b border-gray-100">
        @foreach(['Configuration','Historique'] as $i => $o)
        <button onclick="showPanel({{ $i }})" id="atab-{{ $i }}"
            class="px-4 py-2 text-sm font-semibold rounded-t-lg border-b-2 -mb-px transition-all {{ $i===0?'border-[#1A3A5C] text-[#1A3A5C] bg-blue-50/50':'border-transparent text-gray-400' }}">
            {{ $o }}
            @if($i===1 && $historique->count()>0)
            <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $historique->count() }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- Config --}}
    <div id="apanel-0">
        <form method="POST" action="/alertes/seuils" class="space-y-4 max-w-2xl">
            @csrf
            {{-- Seuil global --}}
            <div class="card">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Seuil global mensuel</p>
                <p class="text-xs text-gray-400 mb-3">Alerte si la consommation totale dépasse ce seuil.</p>
                @php $seuilGlobal = $seuils->firstWhere('type_seuil','global'); @endphp
                <div class="flex gap-3">
                    <input type="number" name="seuils[0][valeur]" value="{{ $seuilGlobal?->valeur }}"
                        class="input-field flex-1" placeholder="Ex : 300" step="0.01" min="0">
                    <select name="seuils[0][unite]" class="input-field w-28">
                        <option value="kwh" {{ ($seuilGlobal?->unite === 'kwh' || !$seuilGlobal) ? 'selected' : '' }}>kWh</option>
                        <option value="cout" {{ $seuilGlobal?->unite === 'cout' ? 'selected' : '' }}>Coût</option>
                    </select>
                    <input type="hidden" name="seuils[0][type_seuil]" value="global">
                </div>
            </div>

            {{-- Seuils par récepteur --}}
            <div class="card">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Seuils par récepteur</p>
                @foreach($recepteurs as $idx => $r)
                @php $sr = $seuils->where('type_seuil','recepteur')->firstWhere('recepteur_id',$r->id); $i = $idx+1; @endphp
                <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                    <span class="flex-1 text-sm font-semibold text-gray-700">{{ $r->nom }}</span>
                    <input type="number" name="seuils[{{ $i }}][valeur]" value="{{ $sr?->valeur }}"
                        class="input-field w-32" placeholder="Seuil" step="0.01" min="0">
                    <select name="seuils[{{ $i }}][unite]" class="input-field w-24">
                        <option value="kwh" {{ (!$sr || $sr->unite==='kwh') ? 'selected' : '' }}>kWh</option>
                        <option value="cout" {{ $sr?->unite==='cout' ? 'selected' : '' }}>Coût</option>
                    </select>
                    <input type="hidden" name="seuils[{{ $i }}][type_seuil]" value="recepteur">
                    <input type="hidden" name="seuils[{{ $i }}][recepteur_id]" value="{{ $r->id }}">
                </div>
                @endforeach
            </div>

            <button type="submit" class="btn-primary w-full">Enregistrer les seuils</button>
        </form>
    </div>

    {{-- Historique --}}
    <div id="apanel-1" class="hidden">
        @if($historique->isEmpty())
        <div class="card flex flex-col items-center py-16 text-center">
            <p class="text-base font-bold text-gray-500 mb-2">Aucune alerte</p>
            <p class="text-sm text-gray-400">Les alertes apparaîtront ici lors de dépassements de seuils.</p>
        </div>
        @else
        <div class="flex justify-end mb-3">
            <form method="POST" action="/alertes/historique" onsubmit="return confirm('Effacer tout l\'historique ?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-gray-400 hover:text-red-500 font-semibold transition-colors">
                    Effacer l'historique
                </button>
            </form>
        </div>
        <div class="space-y-2">
            @foreach($historique as $a)
            <div class="card border-l-4 border-red-400 pl-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-bold text-gray-900">
                            {{ $a->type_alerte === 'global' ? 'Consommation globale dépassée' : $a->nom_recepteur.' — seuil dépassé' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $a->valeur }} (seuil : {{ $a->seuil }})
                        </p>
                    </div>
                    <span class="text-xs text-gray-300">{{ $a->created_at->format('d/m H:i') }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
@section('scripts')
<script>
function showPanel(n) {
    [0,1].forEach(i => {
        document.getElementById('apanel-'+i).classList.toggle('hidden', i!==n);
        const t = document.getElementById('atab-'+i);
        if (i===n) { t.classList.add('border-[#1A3A5C]','text-[#1A3A5C]','bg-blue-50/50'); t.classList.remove('border-transparent','text-gray-400'); }
        else { t.classList.remove('border-[#1A3A5C]','text-[#1A3A5C]','bg-blue-50/50'); t.classList.add('border-transparent','text-gray-400'); }
    });
}
</script>
@endsection