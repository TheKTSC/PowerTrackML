@extends('layouts.app')
@section('title', 'Récepteurs')
@section('content')
<div class="pt-4">
    <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-gray-500">{{ $recs->count() }} récepteur{{ $recs->count()!==1?'s':'' }}</p>
        <a href="/recepteurs/create" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau récepteur
        </a>
    </div>

    @if($recs->isEmpty())
    <div class="card flex flex-col items-center justify-center py-16">
        <svg class="w-16 h-16 text-gray-200 mb-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
        <p class="text-base font-bold text-gray-500 mb-2">Aucun récepteur</p>
        <p class="text-sm text-gray-400 mb-6">Ajoutez vos équipements pour commencer le suivi.</p>
        <a href="/recepteurs/create" class="btn-primary">Ajouter un récepteur</a>
    </div>
    @else
    <div class="card overflow-x-auto p-0">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100">
                <tr class="text-xs font-bold text-gray-400 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Nom</th>
                    <th class="text-left px-4 py-3">Type</th>
                    <th class="text-right px-4 py-3">Pa (W)</th>
                    <th class="text-right px-4 py-3">Conso mois</th>
                    <th class="text-right px-4 py-3">Coût</th>
                    <th class="text-center px-4 py-3">Niveau</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recs as $r)
                @php
                    $kwh   = $kwhs[$r->id] ?? 0;
                    $cout  = $kwh * $r->getCoutEffectif($user);
                    $badge = $kwh===0 ? ['neutral','Inactif'] : ($kwh<30 ? ['success','Faible'] : ($kwh<100 ? ['warning','Modéré'] : ['danger','Élevé']));
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3">
                        <a href="/recepteurs/{{ $r->id }}" class="font-bold text-gray-900 hover:text-[#1A3A5C] flex items-center gap-1">
                            {{ $r->nom }}
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @if($r->est_moteur)<span class="badge-info ml-1">Moteur</span>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 capitalize">{{ str_replace('_',' ',$r->type_equipement) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-700">{{ number_format($r->getPuissanceEffective(),0) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ number_format($kwh,2) }} kWh</td>
                    <td class="px-4 py-3 text-right text-gray-400">{{ number_format($cout,0) }} {{ $user->devise }}</td>
                    <td class="px-4 py-3 text-center"><span class="badge-{{ $badge[0] }}">{{ $badge[1] }}</span></td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="/recepteurs/{{ $r->id }}/edit" class="p-1.5 text-gray-400 hover:text-[#1A3A5C] hover:bg-blue-50 rounded-lg transition-all" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="/recepteurs/{{ $r->id }}" onsubmit="return confirm('Supprimer {{ $r->nom }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection