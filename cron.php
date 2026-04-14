<?php

// Log start time
echo date('Y-m-d H:i:s')." - Cron démarré\n";
echo "Dir: ".__DIR__."\n";

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('import:google-places', ['--limit' => 7]);

echo $kernel->output();

echo date('Y-m-d H:i:s')." - Cron terminé\n";
