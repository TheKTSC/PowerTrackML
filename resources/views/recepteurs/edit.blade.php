@extends('layouts.app')
@section('title', 'Modifier — '.$r->nom)
@section('content')
<div class="max-w-2xl mx-auto pt-4">
    <a href="/recepteurs/{{ $r->id }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Retour au détail
    </a>

    <form method="POST" action="/recepteurs/{{ $r->id }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="card">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Informations générales</p>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="nom" value="{{ old('nom',$r->nom) }}" required class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Type</label>
                    <select name="type_equipement" class="input-field">
                        @foreach($types as $t)
                        <option value="{{ $t }}" {{ (old('type_equipement',$r->type_equipement)===$t)?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Ancienneté (ans)</label>
                    <input type="number" name="anciennete" value="{{ old('anciennete',$r->anciennete) }}" min="0" class="input-field">
                </div>
            </div>
        </div>

        <div class="card">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Puissance</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Puissance nominale (W) *</label>
                    <input type="number" name="puissance_nominale" value="{{ old('puissance_nominale',$r->puissance_nominale) }}" required step="0.1" class="input-field" id="puissanceNom">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Rendement η (%)</label>
                    <input type="number" name="rendement" value="{{ old('rendement',$r->rendement) }}" step="0.1" min="1" max="100" class="input-field" id="rendement">
                </div>
            </div>
            <div class="flex items-center gap-3 mt-3">
                <input type="checkbox" name="est_moteur" id="estMoteur" value="1" {{ (old('est_moteur',$r->est_moteur))?'checked':'' }} class="w-4 h-4 rounded">
                <label for="estMoteur" class="text-sm font-semibold text-gray-700">Équipement moteur</label>
            </div>
            <div id="calcPa" class="mt-3 bg-blue-50 rounded-xl p-3 {{ $r->est_moteur ? '' : 'hidden' }}">
                <p class="text-xs text-blue-600 font-semibold">Puissance absorbée (Pa) :</p>
                <p class="text-lg font-extrabold text-[#1A3A5C]" id="paResult">{{ $r->getPuissanceEffective() }} W</p>
            </div>
        </div>

        <div class="card">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Utilisation & Coût</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Heures / jour</label>
                    <input type="number" name="heures_par_jour" value="{{ old('heures_par_jour',$r->heures_par_jour) }}" step="0.1" min="0" max="24" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jours / mois</label>
                    <input type="number" name="jours_par_mois" value="{{ old('jours_par_mois',$r->jours_par_mois) }}" min="1" max="31" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tarif kWh spécifique</label>
                    <input type="number" name="cout_kwh" value="{{ old('cout_kwh',$r->cout_kwh) }}" step="0.01" min="0" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tarif GE</label>
                    <input type="number" name="cout_kwh_ge" value="{{ old('cout_kwh_ge',$r->cout_kwh_ge) }}" step="0.01" min="0" class="input-field">
                </div>
            </div>
            <div class="flex items-center gap-3 mt-2">
                <input type="checkbox" name="usage_ge" value="1" {{ (old('usage_ge',$r->usage_ge))?'checked':'' }} class="w-4 h-4 rounded">
                <label class="text-sm font-semibold text-gray-700">Usage groupe électrogène</label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary flex-1">Enregistrer</button>
            <a href="/recepteurs/{{ $r->id }}" class="btn-secondary text-center flex-1">Annuler</a>
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
    if (moteur && p>0 && r>0) {
        document.getElementById('paResult').textContent = (p/(r/100)).toFixed(1)+' W';
        div.classList.remove('hidden');
    } else { div.classList.add('hidden'); }
}
['puissanceNom','rendement'].forEach(id => document.getElementById(id).addEventListener('input',calcPa));
document.getElementById('estMoteur').addEventListener('change',calcPa);
</script>
@endsection