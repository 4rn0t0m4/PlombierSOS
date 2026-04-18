<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\ClaimController;
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

    $actions = [
        'debug-plumbers' => function () {
            $dept = request()->query('dept', '14');
            $q = request()->query('q', '');
            if ($q) {
                $cities = \App\Models\City::where('name', 'LIKE', "%{$q}%")->limit(10)->get(['name', 'postal_code', 'department', 'population']);
                echo "Cities matching '{$q}': ".json_encode($cities, JSON_UNESCAPED_UNICODE)."\n";
                return;
            }
            $count = \App\Models\Plumber::active()->where('department', $dept)->count();
            $sample = \App\Models\Plumber::active()->where('department', $dept)->limit(3)->get(['id', 'title', 'city', 'department', 'postal_code']);
            echo "Dept {$dept}: {$count} plombiers actifs\n";
            echo "Sample: ".json_encode($sample, JSON_UNESCAPED_UNICODE)."\n";
        },
        'migrate' => fn () => Artisan::call('migrate', ['--force' => true]),
        'import-plombiers' => fn () => Artisan::call('import:google-places'),
        'import-reviews' => fn () => Artisan::call('import:google-reviews', ['--limit' => 50]),
        'scrape-emails' => fn () => Artisan::call('scrape:emails', ['--limit' => 50]),
        'seo-plumbers' => fn () => Artisan::call('seo:generate', ['type' => 'plumber', '--limit' => 50]),
        'seo-plumbers-force' => fn () => Artisan::call('seo:generate', ['type' => 'plumber', '--limit' => 50, '--force' => true]),
        'seo-departments' => fn () => Artisan::call('seo:generate', ['type' => 'department', '--limit' => 102]),
        'seo-departments-force' => fn () => Artisan::call('seo:generate', ['type' => 'department', '--limit' => 102, '--force' => true]),
        'seo-cities' => fn () => Artisan::call('seo:generate', ['type' => 'city', '--limit' => 50]),
        'seo-cities-force' => fn () => Artisan::call('seo:generate', ['type' => 'city', '--limit' => 50, '--force' => true]),
        'review-summary' => fn () => Artisan::call('seo:generate', ['type' => 'review-summary', '--limit' => 5]),
        'review-summary-force' => fn () => Artisan::call('seo:generate', ['type' => 'review-summary', '--limit' => 5, '--force' => true]),
        'cache-clear' => function () {
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
        },
        'create-admin' => fn () => Artisan::call('make:admin'),
    ];

    if (! isset($actions[$action])) {
        abort(404);
    }

    $actions[$action]();

    return '<pre>'.Artisan::output().'</pre>';
});

// Deploy helper for review summaries (separate route to bypass cache)
Route::get('/deploy-run/{token}', function (string $token) {
    if ($token !== 'psos-2026-setup') {
        abort(404);
    }
    $output = '';

    Artisan::call('cache:clear');
    $output .= Artisan::output();
    Artisan::call('route:clear');
    $output .= Artisan::output();
    Artisan::call('view:clear');
    $output .= Artisan::output();

    if (function_exists('opcache_reset')) {
        opcache_reset();
        $output .= "OPcache reset.\n";
    }

    Artisan::call('seo:generate', ['type' => 'review-summary', '--limit' => 50]);
    $output .= Artisan::output();

    return '<pre>'.$output.'</pre>';
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

// Chatbot
Route::post('/ajax/chatbot', [\App\Http\Controllers\ChatbotController::class, 'chat'])->middleware('throttle:30,1')->name('chatbot.chat');

// Claim listing
Route::post('/ajax/claim', [ClaimController::class, 'store'])->middleware('throttle:3,1')->name('claim.store');

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

// Espace Pro
Route::middleware(['auth', \App\Http\Middleware\ProMiddleware::class])
    ->prefix('pro')
    ->name('pro.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\ProController::class, 'dashboard'])->name('dashboard');
        Route::get('/{plumber}/edit', [\App\Http\Controllers\ProController::class, 'edit'])->name('edit');
        Route::put('/{plumber}', [\App\Http\Controllers\ProController::class, 'update'])->name('update');
    });

// Admin (must be before catch-all routes)
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

// 301 redirects for old URLs with index.html
Route::get('/{slug}/index.html', fn (string $slug) => redirect("/$slug", 301))->where('slug', '[^/]+');

// Hierarchical pages: /{department}/{city}/{plumber}
// These must be last to avoid catching other routes
$reserved = '^(?!admin$|ajax$|deploy$|pro$|avis$|connexion$|inscription$|deconnexion$|recherche$|urgence$|demande$|mentions-legales$|confidentialite$|mot-de-passe-oublie$|reinitialiser-mot-de-passe$|sitemap\.xml$|up$)[^/]+$';
Route::get('/{deptSlug}', [DepartementController::class, 'show'])->name('departement.show')->where('deptSlug', $reserved);
Route::get('/{deptSlug}/{villeSlug}', [VilleController::class, 'show'])->name('ville.show')->where('deptSlug', $reserved);
Route::get('/{deptSlug}/{villeSlug}/{plombierSlug}', [PlombierController::class, 'show'])->name('plombier.show')->where('deptSlug', $reserved);
