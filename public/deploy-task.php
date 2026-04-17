<?php

if (($_GET['token'] ?? '') !== 'psos-2026-setup') {
    http_response_code(404);
    exit('404');
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$action = $_GET['action'] ?? '';
$output = '';

if (function_exists('opcache_reset')) {
    opcache_reset();
    $output .= "OPcache reset.\n";
}

Illuminate\Support\Facades\Artisan::call('cache:clear');
$output .= Illuminate\Support\Facades\Artisan::output();
Illuminate\Support\Facades\Artisan::call('route:clear');
$output .= Illuminate\Support\Facades\Artisan::output();
Illuminate\Support\Facades\Artisan::call('view:clear');
$output .= Illuminate\Support\Facades\Artisan::output();

if ($action === 'review-summary') {
    Illuminate\Support\Facades\Artisan::call('seo:generate', ['type' => 'review-summary', '--limit' => 50]);
    $output .= Illuminate\Support\Facades\Artisan::output();
} elseif ($action) {
    $output .= "Action inconnue : $action\n";
} else {
    $output .= "Cache vidé. Actions disponibles : ?action=review-summary\n";
}

header('Content-Type: text/plain');
echo $output;
