@extends('layouts.app')
@section('title', 'Saisie de consommation')
@section('content')
<div class="max-w-3xl mx-auto pt-4 space-y-4">

    @if($recepteurs->isEmpty())
    <div class="card flex flex-col items-center py-16 text-center">
        <p class="text-base font-bold text-gray-500 mb-2">Aucun récepteur</p>
        <p class="text-sm text-gray-400 mb-6">Créez d'abord vos récepteurs pour saisir leur consommation.</p>
        <a href="/recepteurs/create" class="btn-primary">Créer un récepteur</a>
    </div>
    @else
    <form method="POST" action="/saisie">
        @csrf
        {{-- Options --}}
        <div class="card mb-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Période</label>
                    <select name="periode" class="input-field">
                        <option value="jour">Journalière</option>
                        <option value="semaine">Hebdomadaire</option>
                        <option value="mois">Mensuelle</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                    <input type="date" name="date_saisie" value="{{ now()->format('Y-m-d') }}" class="input-field">
                </div>
            </div>
        </div>

        {{-- Tableau de saisie --}}
        <div class="space-y-3" id="lignesSaisie">
            @foreach($recepteurs as $r)
            @php $pa = $r->getPuissanceEffective(); @endphp
            <div class="card">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-900">{{ $r->nom }}</p>
                        <p class="text-xs text-gray-400">{{ number_format($pa,0) }} W effective</p>
                        @if($r->est_moteur)<span class="badge-info text-xs">Moteur η {{ $r->rendement }}%</span>@endif
                    </div>
                    <div class="flex items-start gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Heures (auto)</label>
                            <input type="number" name="saisies[{{ $r->id }}][heures]"
                                step="0.1" min="0" max="24" placeholder="h"
                                class="input-field w-24 text-center auto-heures"
                                data-pa="{{ $pa }}"
                                data-target="kwh-{{ $r->id }}">
                        </div>
                        <div class="flex items-center pt-5 text-gray-300 text-lg">ou</div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">kWh (manuel)</label>
                            <input type="number" name="saisies[{{ $r->id }}][kwh]"
                                id="kwh-{{ $r->id }}"
                                step="0.0001" min="0" placeholder="kWh"
                                class="input-field w-32 text-center">
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-blue-500 font-semibold hidden eq-result" id="eq-{{ $r->id }}"></div>
            </div>
            @endforeach
        </div>

        {{-- Résumé --}}
        <div class="card mt-4 bg-[#0D1F33] text-white flex items-center justify-between">
            <div>
                <p class="text-white/60 text-sm">Total saisi</p>
                <p class="text-2xl font-extrabold" id="totalKwh">0.000 kWh</p>
                <p class="text-amber-400 font-semibold text-sm" id="totalCout">0 {{ $user->devise }}</p>
            </div>
            <button type="submit" class="bg-amber-400 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition-opacity flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Enregistrer
            </button>
        </div>
    </form>
    @endif
</div>
@endsection
@section('scripts')
<script>
const COUT_KWH = {{ $user->cout_kwh }};

function updateTotal() {
    let total = 0;
    document.querySelectorAll('[id^="kwh-"]').forEach(el => {
        total += parseFloat(el.value)||0;
    });
    document.getElementById('totalKwh').textContent = total.toFixed(3)+' kWh';
    document.getElementById('totalCout').textContent = (total*COUT_KWH).toFixed(0)+' {{ $user->devise }}';
}

document.querySelectorAll('.auto-heures').forEach(el => {
    el.addEventListener('input', function() {
        const pa = parseFloat(this.dataset.pa)||0;
        const h  = parseFloat(this.value)||0;
        const kwh= (pa/1000)*h;
        const targetId = this.dataset.target;
        document.getElementById(targetId).value = kwh > 0 ? kwh.toFixed(4) : '';
        const eq = document.getElementById('eq-'+targetId.replace('kwh-',''));
        if (kwh > 0) {
            eq.textContent = `E = (${pa.toFixed(0)}W ÷ 1000) × ${h}h = ${kwh.toFixed(4)} kWh`;
            eq.classList.remove('hidden');
        } else { eq.classList.add('hidden'); }
        updateTotal();
    });
});

document.querySelectorAll('[id^="kwh-"]').forEach(el => {
    el.addEventListener('input', updateTotal);
});
</script>
@endsection