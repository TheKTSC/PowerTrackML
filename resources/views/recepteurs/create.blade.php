@extends('layouts.app')
@section('title', 'Nouveau récepteur')
@section('content')
<div class="max-w-2xl mx-auto pt-4">
    <a href="/recepteurs" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Retour aux récepteurs
    </a>

    <form method="POST" action="/recepteurs" class="space-y-5">
        @csrf

        {{-- Général --}}
        <div class="card">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Informations générales</p>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nom du récepteur *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" required class="input-field" placeholder="Ex : Climatiseur salon">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Type d'équipement</label>
                    <select name="type_equipement" class="input-field">
                        @foreach($types as $t)
                        <option value="{{ $t }}" {{ old('type_equipement')===$t?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Ancienneté (ans)</label>
                    <input type="number" name="anciennete" value="{{ old('anciennete',0) }}" min="0" class="input-field" placeholder="0">
                    <p class="text-xs text-gray-400 mt-1">+2% de consommation par an</p>
                </div>
            </div>
        </div>

        {{-- Puissance --}}
        <div class="card">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Puissance</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Puissance nominale (W) *</label>
                    <input type="number" name="puissance_nominale" value="{{ old('puissance_nominale') }}" required step="0.1" min="0.1" class="input-field" placeholder="Ex : 1500" id="puissanceNom">
                    <p class="text-xs text-gray-400 mt-1">Valeur sur l'étiquette de l'appareil</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Rendement η (%)</label>
                    <input type="number" name="rendement" value="{{ old('rendement') }}" step="0.1" min="1" max="100" class="input-field" placeholder="Ex : 85" id="rendement">
                    <p class="text-xs text-gray-400 mt-1">Pour les moteurs uniquement</p>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-3">
                <input type="checkbox" name="est_moteur" id="estMoteur" value="1" {{ old('est_moteur')?'checked':'' }} class="w-4 h-4 rounded text-[#1A3A5C]">
                <label for="estMoteur" class="text-sm font-semibold text-gray-700">Équipement moteur — active Pa = P_utile ÷ η</label>
            </div>
            <div id="calcPa" class="mt-3 bg-blue-50 rounded-xl p-3 hidden">
                <p class="text-xs text-blue-600 font-semibold">Puissance absorbée (Pa) calculée :</p>
                <p class="text-lg font-extrabold text-[#1A3A5C] mt-0.5" id="paResult">—</p>
                <p class="text-xs text-gray-400 mt-0.5 italic" id="paFormule">Pa = P ÷ η</p>
            </div>
        </div>

        {{-- Usage --}}
        <div class="card">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Habitudes d'utilisation</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Heures / jour</label>
                    <input type="number" name="heures_par_jour" value="{{ old('heures_par_jour') }}" step="0.1" min="0" max="24" class="input-field" placeholder="Ex : 8">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jours / mois</label>
                    <input type="number" name="jours_par_mois" value="{{ old('jours_par_mois',30) }}" min="1" max="31" class="input-field" placeholder="30">
                </div>
            </div>
        </div>

        {{-- Coût --}}
        <div class="card">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Coût & Facturation</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tarif kWh spécifique</label>
                    <input type="number" name="cout_kwh" value="{{ old('cout_kwh') }}" step="0.01" min="0" class="input-field" placeholder="Laissez vide → tarif global">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tarif groupe électrogène</label>
                    <input type="number" name="cout_kwh_ge" value="{{ old('cout_kwh_ge') }}" step="0.01" min="0" class="input-field" placeholder="Si usage GE">
                </div>
            </div>
            <div class="flex items-center gap-3 mt-2">
                <input type="checkbox" name="usage_ge" id="usageGe" value="1" {{ old('usage_ge')?'checked':'' }} class="w-4 h-4 rounded">
                <label for="usageGe" class="text-sm font-semibold text-gray-700">Usage sur groupe électrogène</label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary flex-1">Créer le récepteur</button>
            <a href="/recepteurs" class="btn-secondary text-center flex-1">Annuler</a>
        </div>
    </form>
</div>
@endsection
@section('scripts')
<script>
function calcPa() {
    const p = parseFloat(document.getElementById('puissanceNom').value);
    const r = parseFloat(document.getElementById('rendement').value);
    const moteur = document.getElementById('estMoteur').checked;
    const div = document.getElementById('calcPa');
    if (moteur && p > 0 && r > 0) {
        const pa = (p / (r / 100)).toFixed(1);
        document.getElementById('paResult').textContent = pa + ' W (' + (pa/1000).toFixed(3) + ' kW)';
        document.getElementById('paFormule').textContent = 'Pa = ' + p + 'W ÷ ' + r + '% = ' + pa + ' W';
        div.classList.remove('hidden');
    } else { div.classList.add('hidden'); }
}
document.getElementById('puissanceNom').addEventListener('input', calcPa);
document.getElementById('rendement').addEventListener('input', calcPa);
document.getElementById('estMoteur').addEventListener('change', calcPa);
</script>
@endsection