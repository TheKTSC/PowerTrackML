@extends('layouts.app')
@section('title', 'Prédiction')
@section('content')
<div class="max-w-3xl mx-auto pt-4 space-y-5">

    {{-- Statut API --}}
    <div class="flex items-center gap-2">
        @if($apiOk)
            <span class="badge-success">● API connectée</span>
        @else
            <span class="badge-danger">● API hors ligne</span>
        @endif
        <span class="text-sm text-gray-400">— Prédiction pour le mois {{ $moisCible }}</span>
    </div>

    @if(!$apiOk)
    <div class="bg-amber-50 border border-amber-200 text-amber-700 text-sm px-4 py-3 rounded-xl font-semibold">
        L'API Flask est inaccessible. Le mode simulation (formule physique) sera utilisé automatiquement.
    </div>
    @endif

    {{-- Résultat --}}
    @isset($prediction)
    @if(isset($mode) && $mode === 'simulation')
    <div class="badge-warning">Mode simulation — calcul par formule physique (E = Pa × h × j × coeff. dégradation)</div>
    @endif

    <div class="card bg-[#0D1F33] text-center py-8">
        <p class="text-white/60 text-sm mb-2">Consommation totale prévue — mois {{ $moisCible }}</p>
        <p class="text-4xl font-extrabold text-white">{{ number_format($prediction['total_kwh'],2) }} kWh</p>
        <p class="text-amber-400 font-semibold mt-1 text-lg">
            {{ number_format($prediction['total_kwh'] * auth()->user()->cout_kwh, 0) }} {{ auth()->user()->devise }}
        </p>
        @if($prediction['variation'] !== null)
        <div class="flex items-center justify-center gap-2 mt-3">
            @if($prediction['variation'] > 0)
                <span class="text-red-400 font-bold">↑ +{{ $prediction['variation'] }}% vs mois actuel</span>
            @else
                <span class="text-green-400 font-bold">↓ {{ $prediction['variation'] }}% vs mois actuel</span>
            @endif
        </div>
        @endif
    </div>

    <div class="space-y-3">
        @foreach($prediction['recepteurs'] as $r)
        <div class="card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $r['nom'] }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ str_replace('_',' ',$r['type']) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-base font-extrabold text-[#1A3A5C]">{{ $r['kwh_predit'] }} kWh</p>
                    <p class="text-xs text-gray-400">{{ number_format($r['kwh_predit'] * auth()->user()->cout_kwh, 0) }} {{ auth()->user()->devise }}</p>
                </div>
            </div>
            @if(!empty($r['interpretation']))
            <p class="text-xs text-blue-500 italic mt-2 pt-2 border-t border-gray-50">{{ $r['interpretation'] }}</p>
            @endif
        </div>
        @endforeach
    </div>
    @endisset

    @if(!isset($prediction))
    <div class="card flex flex-col items-center py-16 text-center">
        <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <p class="text-base font-bold text-gray-500 mb-2">Aucune prédiction</p>
        <p class="text-sm text-gray-400">Cliquez sur le bouton pour lancer la prédiction du mois suivant.</p>
    </div>
    @endif

    <form method="POST" action="/prediction">
        @csrf
        <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 py-3 text-base">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Lancer la prédiction — mois {{ $moisCible }}
        </button>
    </form>
</div>
@endsection