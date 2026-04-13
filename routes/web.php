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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Deploy helpers (to remove after setup)
Route::get('/deploy/{action}/{token}', function (string $action, string $token) {
    if ($token !== 'psos-2026-setup') {
        abort(404);
    }
    if ($action === 'import-villes') {
        DB::unprepared(file_get_contents(database_path('seeders/data.sql')));

        return '<pre>Départements et villes importés.</pre>';
    }
    match ($action) {
        'migrate' => Artisan::call('migrate', ['--force' => true]),
        'import-plombiers' => Artisan::call('import:google-places', ['--limit' => 20]),
        'import-reviews' => Artisan::call('import:google-reviews', ['--limit' => 50]),
        'scrape-emails' => Artisan::call('scrape:emails', ['--limit' => 50]),
        'seo-plumbers' => Artisan::call('seo:generate', ['type' => 'plumber', '--limit' => 50]),
        'seo-plumbers-force' => Artisan::call('seo:generate', ['type' => 'plumber', '--limit' => 50, '--force' => true]),
        'seo-departments' => Artisan::call('seo:generate', ['type' => 'department', '--limit' => 102]),
        'seo-departments-force' => Artisan::call('seo:generate', ['type' => 'department', '--limit' => 102, '--force' => true]),
        'seo-cities' => Artisan::call('seo:generate', ['type' => 'city', '--limit' => 50]),
        'seo-cities-force' => Artisan::call('seo:generate', ['type' => 'city', '--limit' => 50, '--force' => true]),
        'create-admin' => Artisan::call('make:admin'),
        default => abort(404),
    };

    return '<pre>'.Artisan::output().'</pre>';
});

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Search
Route::get('/recherche', [RechercheController::class, 'index'])->name('recherche');

// Urgence
Route::get('/urgence', [UrgenceController::class, 'index'])->name('urgence');

// Demande d'intervention
Route::get('/demande', [DemandeController::class, 'create'])->name('demande.create');
Route::post('/demande', [DemandeController::class, 'store'])->middleware('throttle:5,1')->name('demande.store');

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

// Admin (must be before catch-all routes)
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

// Hierarchical pages: /{department}/{city}/{plumber}
// These must be last to avoid catching other routes
$reserved = '^(?!admin$|ajax$|deploy$|avis$|connexion$|inscription$|deconnexion$|recherche$|urgence$|demande$|mentions-legales$|confidentialite$|mot-de-passe-oublie$|reinitialiser-mot-de-passe$|sitemap\.xml$|up$)[^/]+$';
Route::get('/{deptSlug}', [DepartementController::class, 'show'])->name('departement.show')->where('deptSlug', $reserved);
Route::get('/{deptSlug}/{villeSlug}', [VilleController::class, 'show'])->name('ville.show')->where('deptSlug', $reserved);
Route::get('/{deptSlug}/{villeSlug}/{plombierSlug}', [PlombierController::class, 'show'])->name('plombier.show')->where('deptSlug', $reserved);
