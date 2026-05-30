<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecepteurController;
use App\Http\Controllers\SaisieController;
use App\Http\Controllers\AnalyseController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\ProfilController;

// Auth
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register']);
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// App (authentifié)
Route::middleware('auth')->group(function () {
    Route::get('/',                     [DashboardController::class,  'index'])->name('dashboard');
    Route::get('/recepteurs',           [RecepteurController::class,  'index'])->name('recepteurs.index');
    Route::get('/recepteurs/create',    [RecepteurController::class,  'create'])->name('recepteurs.create');
    Route::post('/recepteurs',          [RecepteurController::class,  'store'])->name('recepteurs.store');
    Route::get('/recepteurs/{id}',      [RecepteurController::class,  'show'])->name('recepteurs.show');
    Route::get('/recepteurs/{id}/edit', [RecepteurController::class,  'edit'])->name('recepteurs.edit');
    Route::put('/recepteurs/{id}',      [RecepteurController::class,  'update'])->name('recepteurs.update');
    Route::delete('/recepteurs/{id}',   [RecepteurController::class,  'destroy'])->name('recepteurs.destroy');

    Route::get('/saisie',               [SaisieController::class,     'index'])->name('saisie.index');
    Route::post('/saisie',              [SaisieController::class,     'store'])->name('saisie.store');
    Route::delete('/saisie/{id}',       [SaisieController::class,     'destroy'])->name('saisie.destroy');

    Route::get('/analyse',              [AnalyseController::class,    'index'])->name('analyse.index');

    Route::get('/prediction',           [PredictionController::class, 'index'])->name('prediction.index');
    Route::post('/prediction',          [PredictionController::class, 'predict'])->name('prediction.predict');

    Route::get('/alertes',              [AlerteController::class,     'index'])->name('alertes.index');
    Route::post('/alertes/seuils',      [AlerteController::class,     'updateSeuils'])->name('alertes.seuils');
    Route::delete('/alertes/historique',[AlerteController::class,     'effacerHistorique'])->name('alertes.effacer');

    Route::get('/profil',               [ProfilController::class,     'index'])->name('profil.index');
    Route::put('/profil',               [ProfilController::class,     'update'])->name('profil.update');
    Route::put('/profil/parametres',    [ProfilController::class,     'updateParametres'])->name('profil.parametres');
});