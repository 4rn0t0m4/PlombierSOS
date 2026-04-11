<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $this->autoMigrate();
    }

    /**
     * Run pending migrations once after deployment.
     * Uses a hash file to detect new deployments.
     */
    private function autoMigrate(): void
    {
        $hashFile = storage_path('app/deploy_hash');
        $currentHash = $this->getDeployHash();

        if (file_exists($hashFile) && file_get_contents($hashFile) === $currentHash) {
            return;
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            file_put_contents($hashFile, $currentHash);
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    private function getDeployHash(): string
    {
        $composerLock = base_path('composer.lock');

        return md5_file($composerLock) ?: md5((string) filemtime(base_path()));
    }
}
