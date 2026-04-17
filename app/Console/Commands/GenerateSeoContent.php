<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Department;
use App\Models\Plumber;
use App\Services\SeoContentService;
use Illuminate\Console\Command;

class GenerateSeoContent extends Command
{
    protected $signature = 'seo:generate
        {type : Type de contenu (plumber, department, city, review-summary)}
        {--limit=10 : Nombre d\'éléments à traiter}
        {--force : Regénérer même si du contenu existe déjà}';

    protected $description = 'Génère du contenu SEO via Claude AI';

    public function handle(SeoContentService $seo): int
    {
        $type = $this->argument('type');
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        return match ($type) {
            'plumber' => $this->generatePlumbers($seo, $limit, $force),
            'department' => $this->generateDepartments($seo, $limit, $force),
            'city' => $this->generateCities($seo, $limit, $force),
            'review-summary' => $this->generateReviewsSummaries($seo, $limit, $force),
            default => $this->error("Type invalide : {$type}") ?? self::FAILURE,
        };
    }

    private function generatePlumbers(SeoContentService $seo, int $limit, bool $force): int
    {
        $query = Plumber::active();
        if (! $force) {
            $query->whereNull('seo_content');
        }

        $plumbers = $query->limit($limit)->get();
        $this->info("{$plumbers->count()} plombier(s) à traiter.");

        foreach ($plumbers as $plumber) {
            $this->line("  {$plumber->title} ({$plumber->city})...");

            $content = $seo->generateForPlumber([
                'title' => $plumber->title,
                'type' => $plumber->type_label,
                'city' => $plumber->city,
                'postal_code' => $plumber->postal_code,
                'department' => $plumber->department,
                'emergency_24h' => $plumber->emergency_24h,
                'free_quote' => $plumber->free_quote,
                'google_rating' => $plumber->google_rating,
                'google_reviews_count' => $plumber->google_reviews_count,
                'description' => $plumber->description,
            ]);

            if ($content) {
                $plumber->update(['seo_content' => $content]);
                $this->info('    OK');
            } else {
                $this->warn('    Erreur de génération');
            }

            usleep(500000); // 0.5s entre chaque appel
        }

        return self::SUCCESS;
    }

    private function generateDepartments(SeoContentService $seo, int $limit, bool $force): int
    {
        $query = Department::query();
        if (! $force) {
            $query->whereNull('seo_content');
        }

        $departments = $query->limit($limit)->get();
        $this->info("{$departments->count()} département(s) à traiter.");

        foreach ($departments as $dept) {
            $this->line("  {$dept->number} - {$dept->name}...");

            $topCities = $dept->cities()->orderByDesc('population')->limit(5)->pluck('name')->implode(', ');

            $content = $seo->generateForDepartment([
                'name' => $dept->name,
                'region' => $dept->region,
                'cities' => $topCities,
            ]);

            if ($content) {
                $dept->update(['seo_content' => $content]);
                $this->info('    OK');
            } else {
                $this->warn('    Erreur de génération');
            }

            usleep(500000);
        }

        return self::SUCCESS;
    }

    private function generateReviewsSummaries(SeoContentService $seo, int $limit, bool $force): int
    {
        $query = Plumber::active()
            ->whereNotNull('google_reviews')
            ->where('google_reviews', '!=', '[]');
        if (! $force) {
            $query->whereNull('reviews_summary');
        }

        $plumbers = $query->limit($limit)->get();
        $this->info("{$plumbers->count()} plombier(s) avec avis à traiter.");

        foreach ($plumbers as $plumber) {
            $reviews = $plumber->google_reviews;
            if (empty($reviews)) {
                continue;
            }

            $this->line("  {$plumber->title} ({$plumber->city}) — {$plumber->google_reviews_count} avis...");

            $summary = $seo->generateReviewsSummary([
                'title' => $plumber->title,
                'city' => $plumber->city,
                'rating' => $plumber->google_rating,
                'reviews' => $reviews,
            ]);

            if ($summary) {
                $plumber->update(['reviews_summary' => $summary]);
                $this->info("    OK : $summary");
            } else {
                $this->warn('    Erreur de génération');
            }

            usleep(500000);
        }

        return self::SUCCESS;
    }

    private function generateCities(SeoContentService $seo, int $limit, bool $force): int
    {
        $query = City::has('plumbers')->with('departmentRelation');
        if (! $force) {
            $query->whereNull('seo_content');
        }

        $cities = $query->orderByDesc('population')->limit($limit)->get();
        $this->info("{$cities->count()} ville(s) à traiter.");

        foreach ($cities as $city) {
            $this->line("  {$city->name} ({$city->postal_code})...");

            $content = $seo->generateForCity([
                'name' => $city->name,
                'postal_code' => $city->postal_code,
                'department' => $city->departmentRelation?->name ?? '',
            ]);

            if ($content) {
                $city->update(['seo_content' => $content]);
                $this->info('    OK');
            } else {
                $this->warn('    Erreur de génération');
            }

            usleep(500000);
        }

        return self::SUCCESS;
    }
}
