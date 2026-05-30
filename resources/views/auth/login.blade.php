<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerTrack — Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex bg-gray-50">
    <!-- Panneau gauche -->
    <div class="hidden lg:flex w-1/2 bg-[#0D1F33] flex-col justify-center items-center p-12">
        <div class="flex items-center gap-3 mb-8">
            <svg class="w-12 h-12 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
            <span class="text-4xl font-extrabold text-white">PowerTrack</span>
        </div>
        <p class="text-white/60 text-center text-lg max-w-sm leading-relaxed">
            Suivez, analysez et prédisez votre consommation électrique grâce au Machine Learning.
        </p>
        <div class="mt-10 grid grid-cols-2 gap-4 w-full max-w-sm">
            @foreach(['Récepteurs' => 'Gérez vos équipements','Prédiction ML' => 'Anticipez vos factures','Bilans graphiques' => 'Visualisez vos données','Alertes' => 'Ne dépassez plus les seuils'] as $titre => $desc)
            <div class="bg-white/8 rounded-xl p-3">
                <p class="text-white font-semibold text-sm">{{ $titre }}</p>
                <p class="text-white/50 text-xs mt-0.5">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Formulaire -->
    <div class="flex-1 flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex items-center gap-2 mb-8">
                <svg class="w-8 h-8 text-[#1A3A5C]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                <span class="text-2xl font-extrabold text-[#1A3A5C]">PowerTrack</span>
            </div>
            <h2 class="text-2xl font-extrabold text-gray-900 mb-1">Connexion</h2>
            <p class="text-gray-400 text-sm mb-8">Accédez à votre espace de suivi</p>

            @if($errors->any())
                <div class="bg-red-50 text-red-600 text-sm font-semibold px-4 py-3 rounded-xl mb-4">
                    @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#1A3A5C] focus:ring-2 focus:ring-[#1A3A5C]/20"
                        placeholder="votre@email.com">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mot de passe</label>
                    <input type="password" name="password" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#1A3A5C] focus:ring-2 focus:ring-[#1A3A5C]/20"
                        placeholder="••••••••">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded">
                    <label for="remember" class="text-sm text-gray-500">Se souvenir de moi</label>
                </div>
                <button type="submit" class="w-full bg-[#1A3A5C] text-white py-2.5 rounded-xl font-semibold text-sm hover:opacity-90 transition-opacity mt-2">
                    Se connecter
                </button>
            </form>
            <p class="text-center text-sm text-gray-500 mt-6">
                Pas encore de compte ?
                <a href="/register" class="text-[#1A3A5C] font-bold hover:underline">S'inscrire</a>
            </p>
        </div>
    </div>
</body>
</html>