<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhoneController;
use App\Http\Controllers\PlombierController;
use App\Http\Controllers\RechercheController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UrgenceController;
use App\Http\Controllers\VilleAutocompleteController;
use App\Http\Controllers\VilleController;
use Illuminate\Support\Facades\Route;

// Deploy helpers (to remove after setup)
Route::get('/deploy/{action}/{token}', function (string $action, string $token) {
    if ($token !== 'psos-2026-setup') {
        abort(404);
    }
    match ($action) {
        'migrate' => Artisan::call('migrate', ['--force' => true]),
        'import-villes' => Artisan::call('import:villes'),
        'import-plombiers' => Artisan::call('import:google-places', ['--limit' => 20]),
        default => abort(404),
    };
    return '<pre>' . Artisan::output() . '</pre>';
});

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Plombier detail pages
Route::get('/plombier/{slug}.html', [PlombierController::class, 'show'])->defaults('type', 0)->name('plombier.show');
Route::get('/chauffagiste/{slug}.html', [PlombierController::class, 'show'])->defaults('type', 1)->name('chauffagiste.show');
Route::get('/plombier-chauffagiste/{slug}.html', [PlombierController::class, 'show'])->defaults('type', 2)->name('plombier-chauffagiste.show');
Route::get('/depanneur-urgence/{slug}.html', [PlombierController::class, 'show'])->defaults('type', 3)->name('depanneur-urgence.show');

// Department & city pages
Route::get('/departement-{slug}.html', [DepartementController::class, 'show'])->name('departement.show');
Route::get('/plombier-a-{slug}.html', [VilleController::class, 'show'])->name('ville.show');

// Search
Route::get('/recherche.html', [RechercheController::class, 'index'])->name('recherche');

// Urgence
Route::get('/urgence', [UrgenceController::class, 'index'])->name('urgence');

// Demande d'intervention
Route::get('/demande-intervention', [DemandeController::class, 'create'])->name('demande.create');
Route::post('/demande-intervention', [DemandeController::class, 'store'])->middleware('throttle:5,1')->name('demande.store');

// Autocomplete
Route::get('/ajax/villes', VilleAutocompleteController::class)->name('villes.autocomplete');

// Phone reveal
Route::post('/ajax/phone', [PhoneController::class, 'reveal'])->name('phone.reveal');

// Reviews
Route::post('/avis', [AvisController::class, 'store'])->middleware('throttle:5,1')->name('avis.store');
Route::get('/avis/confirmer/{token}', [AvisController::class, 'confirmerEmail'])->name('avis.confirmer');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Static pages
Route::view('/mentions-legales', 'pages.mentions-legales')->name('mentions-legales');
Route::view('/confidentialite', 'pages.confidentialite')->name('confidentialite');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/mot-de-passe-oublie', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::get('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
