<?php

echo date('Y-m-d H:i:s')." - Cron démarré\n";

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('import:google-places');

echo $kernel->output();

echo date('Y-m-d H:i:s')." - Cron terminé\n";
