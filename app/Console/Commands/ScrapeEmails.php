<?php

namespace App\Console\Commands;

use App\Models\Plumber;
use App\Services\EmailScraperService;
use Illuminate\Console\Command;

class ScrapeEmails extends Command
{
    protected $signature = 'scrape:emails
        {--limit=10 : Nombre de plombiers à traiter}
        {--force : Re-scraper même si un email existe déjà}';

    protected $description = 'Scrape les sites web des plombiers pour trouver leurs emails';

    public function handle(EmailScraperService $scraper): int
    {
        $query = Plumber::whereNotNull('website')->where('website', '!=', '');

        if (! $this->option('force')) {
            $query->whereNull('email');
        }

        $plumbers = $query->limit((int) $this->option('limit'))->get();

        if ($plumbers->isEmpty()) {
            $this->info('Aucun plombier à traiter.');

            return self::SUCCESS;
        }

        $this->info("{$plumbers->count()} site(s) à scraper.");
        $found = 0;

        foreach ($plumbers as $plumber) {
            $this->line("  {$plumber->title} → {$plumber->website}");

            $email = $scraper->findEmail($plumber->website);

            if ($email) {
                $plumber->update(['email' => $email]);
                $this->info("    Trouvé : {$email}");
                $found++;
            } else {
                // Mark as scraped with empty string to avoid re-processing
                $plumber->update(['email' => '']);
                $this->line('    Aucun email trouvé.');
            }

            usleep(500000); // 0.5s entre chaque requête
        }

        $this->info("{$found} email(s) trouvé(s) sur {$plumbers->count()} site(s).");

        return self::SUCCESS;
    }
}
