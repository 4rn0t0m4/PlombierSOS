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
    try {
        if (@opcache_reset()) {
            $output .= "OPcache reset.\n";
        } else {
            $output .= "OPcache reset skipped (restricted or disabled).\n";
        }
    } catch (\Throwable $e) {
        $output .= 'OPcache reset skipped: '.$e->getMessage()."\n";
    }
}

// Invalidate OPcache per-file for key files (often works when reset is blocked).
if (function_exists('opcache_invalidate')) {
    $targets = [
        __DIR__.'/../app/Http/Controllers/ChatbotController.php',
        __DIR__.'/../app/Http/Controllers',
    ];
    foreach ($targets as $path) {
        if (is_dir($path)) {
            foreach (glob($path.'/*.php') ?: [] as $file) {
                try {
                    @opcache_invalidate($file, true);
                } catch (\Throwable $e) {
                    // Ignore per-file failures.
                }
            }
            $output .= "OPcache invalidated for directory: $path\n";
        } elseif (is_file($path)) {
            try {
                $ok = @opcache_invalidate($path, true);
                $mtime = date('Y-m-d H:i:s', filemtime($path));
                $sha = substr(sha1_file($path), 0, 10);
                $output .= "OPcache invalidate $path: ".($ok ? 'OK' : 'FAILED')." (mtime=$mtime sha=$sha)\n";
            } catch (\Throwable $e) {
                $output .= "OPcache invalidate $path: EX ".$e->getMessage()."\n";
            }
        }
    }
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
