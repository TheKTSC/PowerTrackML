@extends('layouts.app')
@section('title', 'Profil')
@section('content')
<div class="max-w-2xl mx-auto pt-4 space-y-5">

    {{-- Avatar --}}
    <div class="card flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-[#1A3A5C] flex items-center justify-center flex-shrink-0">
            <span class="text-2xl font-extrabold text-white">{{ strtoupper(substr($user->nom,0,1)) }}</span>
        </div>
        <div>
            <p class="text-xl font-extrabold text-gray-900">{{ $user->nom }}</p>
            <p class="text-sm text-gray-400">{{ $user->email }}</p>
            <p class="text-xs text-gray-300 mt-0.5">Membre depuis {{ $user->created_at->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- Infos personnelles --}}
    <form method="POST" action="/profil" class="card space-y-4">
        @csrf @method('PUT')
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Informations du compte</p>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nom complet</label>
            <input type="text" name="nom" value="{{ old('nom',$user->nom) }}" required class="input-field">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Type de compte</label>
                <select name="type_compte" class="input-field">
                    <option value="Particulier" {{ $user->type_compte==='Particulier'?'selected':'' }}>Particulier</option>
                    <option value="Entreprise"  {{ $user->type_compte==='Entreprise'?'selected':'' }}>Entreprise</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    {{ $user->type_compte === 'Entreprise' ? "Nb d'employés" : 'Nb personnes' }}
                </label>
                <input type="number" name="nombre_utilisateurs" value="{{ old('nombre_utilisateurs',$user->nombre_utilisateurs) }}" min="1" class="input-field">
            </div>
        </div>
        <button type="submit" class="btn-primary w-full">Enregistrer le profil</button>
    </form>

    {{-- Paramètres facturation --}}
    <form method="POST" action="/profil/parametres" class="card space-y-4">
        @csrf @method('PUT')
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Paramètres de facturation</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Coût du kWh (tarif global)</label>
                <input type="number" name="cout_kwh" value="{{ old('cout_kwh',$user->cout_kwh) }}" step="0.01" min="0" required class="input-field" placeholder="100">
                <p class="text-xs text-gray-400 mt-1">Utilisé pour tous les récepteurs sans tarif spécifique</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Devise</label>
                <select name="devise" class="input-field">
                    @foreach(['FCFA'=>'FCFA (F)','EUR'=>'Euro (€)','USD'=>'Dollar ($)'] as $v => $l)
                    <option value="{{ $v }}" {{ $user->devise===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <input type="checkbox" name="notifications_actives" value="1" id="notifOn"
                {{ $user->notifications_actives ? 'checked' : '' }} class="w-4 h-4 rounded text-[#1A3A5C]">
            <label for="notifOn" class="text-sm font-semibold text-gray-700">Alertes visuelles actives</label>
        </div>
        <button type="submit" class="btn-primary w-full">Enregistrer les paramètres</button>
    </form>

    {{-- À propos --}}
    <div class="card">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">À propos de PowerTrack</p>
        @foreach(['Version'=>'1.0.0','Backend'=>'Laravel 10 + MySQL','Frontend'=>'Blade + TailwindCSS CDN','Graphiques'=>'Chart.js CDN','Prédiction ML'=>'XGBoost via API Flask'] as $k => $v)
        <div class="flex justify-between py-2 border-b border-gray-50 text-sm">
            <span class="text-gray-400">{{ $k }}</span>
            <span class="font-semibold text-gray-700">{{ $v }}</span>
        </div>
        @endforeach
    </div>
</div>
@endsection