<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Department;
use App\Models\Plumber;
use App\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportGooglePlaces extends Command
{
    protected $signature = 'import:google-places
        {--limit=10 : Nombre de plombiers à importer par exécution}
        {--departement= : Forcer un département spécifique}
        {--dry-run : Simuler sans insérer en base}';

    protected $description = 'Importe des plombiers depuis Google Places API (10/heure par défaut)';

    private string $apiKey;

    private const FIELD_MASK = 'places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.internationalPhoneNumber,places.websiteUri,places.googleMapsUri,places.regularOpeningHours,places.rating,places.userRatingCount,places.types,places.location,places.editorialSummary,places.businessStatus,places.addressComponents';

    private const SEARCH_QUERIES = [
        'plombier',
        'plombier chauffagiste',
        'dépannage plomberie urgence',
        'chauffagiste',
    ];

    public function handle(): int
    {
        $this->apiKey = config('services.google_places.api_key');
        if (! $this->apiKey) {
            $this->error('GOOGLE_PLACES_API_KEY non configurée.');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $imported = 0;

        $dept = $this->getNextDepartment();
        if (! $dept) {
            $this->info('Tous les départements traités. Reset.');
            DB::table('google_import_progress')->update(['completed' => false]);
            $dept = $this->getNextDepartment();
        }

        $this->info("Département : {$dept->number} - {$dept->name}");

        $cities = City::where('department', $dept->number)
            ->orderByDesc('population')
            ->limit(5)
            ->pluck('name')
            ->toArray();

        if (empty($cities)) {
            $cities = [$dept->name];
        }

        foreach (self::SEARCH_QUERIES as $searchType) {
            if ($imported >= $limit) {
                break;
            }

            foreach ($cities as $city) {
                if ($imported >= $limit) {
                    break;
                }

                $query = "$searchType $city";
                $this->line("  Recherche : $query");

                $places = $this->searchPlaces($query);

                foreach ($places as $place) {
                    if ($imported >= $limit) {
                        break;
                    }

                    $placeId = $place['id'] ?? '';
                    if (! $placeId) {
                        continue;
                    }

                    if (DB::table('google_imports')->where('place_id', $placeId)->exists()) {
                        continue;
                    }

                    $nom = $place['displayName']['text'] ?? '';
                    $cp = $this->extractComponent($place, 'postal_code');

                    if (Plumber::where('title', $nom)->where('postal_code', $cp)->exists()) {
                        DB::table('google_imports')->insertOrIgnore([
                            'place_id' => $placeId,
                            'status' => 'duplicate',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        continue;
                    }

                    if ($dryRun) {
                        $cityName = $this->extractComponent($place, 'locality');
                        $this->info("    [DRY-RUN] $nom - $cityName ($cp)");
                        $imported++;

                        continue;
                    }

                    $plumberId = $this->createPlumber($place);

                    if ($plumberId) {
                        DB::table('google_imports')->insertOrIgnore([
                            'place_id' => $placeId,
                            'plumber_id' => $plumberId,
                            'status' => 'imported',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $imported++;
                        $cityName = $this->extractComponent($place, 'locality');
                        $this->info("    IMPORTÉ : $nom - $cityName ($cp)");
                    }
                }
            }
        }

        DB::table('google_import_progress')->updateOrInsert(
            ['department' => $dept->number],
            [
                'completed' => true,
                'total_imported' => DB::raw("total_imported + $imported"),
                'last_run_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->info("$imported plombier(s) importé(s) dans le {$dept->number} - {$dept->name}.");

        return self::SUCCESS;
    }

    private function getNextDepartment(): ?Department
    {
        if ($this->option('departement')) {
            return Department::where('number', $this->option('departement'))->first();
        }

        $processed = DB::table('google_import_progress')
            ->where('completed', true)
            ->pluck('department');

        return Department::whereNotIn('number', $processed)
            ->orderBy('number')
            ->first();
    }

    private function searchPlaces(string $query): array
    {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $this->apiKey,
            'X-Goog-FieldMask' => self::FIELD_MASK,
        ])->timeout(15)->post('https://places.googleapis.com/v1/places:searchText', [
            'textQuery' => $query,
            'languageCode' => 'fr',
            'maxResultCount' => 20,
        ]);

        if (! $response->ok()) {
            $this->warn('    API Error: '.$response->json('error.message', 'Inconnue'));

            return [];
        }

        usleep(100000);

        return $response->json('places', []);
    }

    private function createPlumber(array $place): ?int
    {
        $nom = $place['displayName']['text'] ?? '';
        $location = $place['location'] ?? [];
        $hours = $place['regularOpeningHours'] ?? [];
        $types = $place['types'] ?? [];

        // Déterminer le type
        $type = 0; // plombier par défaut
        $nomLower = strtolower($nom);
        if (str_contains($nomLower, 'chauffag') && str_contains($nomLower, 'plomb')) {
            $type = 2;
        } elseif (str_contains($nomLower, 'chauffag')) {
            $type = 1;
        } elseif (str_contains($nomLower, 'urgence') || str_contains($nomLower, 'dépann') || str_contains($nomLower, 'sos')) {
            $type = 3;
        }

        // Détecter urgence 24h
        $emergency24h = false;
        if (str_contains($nomLower, '24h') || str_contains($nomLower, '24/24') || str_contains($nomLower, 'urgence')) {
            $emergency24h = true;
        }
        if (! empty($hours['periods'])) {
            $jours = collect($hours['periods'])->pluck('open.day')->unique()->count();
            if ($jours >= 7) {
                $emergency24h = true;
            }
        }

        $cp = $this->extractComponent($place, 'postal_code');
        $cityName = $this->extractComponent($place, 'locality');
        $rue = $this->extractComponent($place, 'route');
        $numero = $this->extractComponent($place, 'street_number');

        $address = trim(($numero ? "$numero " : '').$rue);

        $city = null;
        if ($cp) {
            $city = City::where('postal_code', $cp)
                ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$cityName.'%'])
                ->first();
        }

        $deptNum = $city?->department;
        if (! $deptNum && $cp && strlen($cp) >= 2) {
            $deptNum = substr($cp, 0, 2);
        }

        $slug = SlugService::generate($nom.' '.$cityName.' '.$cp);
        $baseSlug = $slug;
        $i = 1;
        while (Plumber::where('slug', $slug)->exists()) {
            $slug = "$baseSlug-$i";
            $i++;
        }

        $telephone = $place['nationalPhoneNumber'] ?? '';
        $telephone = substr(preg_replace('/[^0-9+]/', '', $telephone), 0, 20);

        $openingHoursText = '';
        if (! empty($hours['weekdayDescriptions'])) {
            $openingHoursText = implode("\n", $hours['weekdayDescriptions']);
        }

        $plumberId = DB::table('plumbers')->insertGetId([
            'type' => $type,
            'title' => $nom,
            'slug' => $slug,
            'place_id' => $place['id'],
            'email' => null,
            'phone' => $telephone ?: null,
            'website' => $place['websiteUri'] ?? null,
            'google_maps_url' => $place['googleMapsUri'] ?? null,
            'address' => $address ?: null,
            'postal_code' => $cp ?: null,
            'city' => $cityName ?: null,
            'department' => $deptNum ?: null,
            'city_id' => $city?->id,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'service_radius' => 20,
            'description' => $place['editorialSummary']['text'] ?? null,
            'opening_hours' => $openingHoursText ?: null,
            'emergency_24h' => $emergency24h,
            'free_quote' => str_contains($nomLower, 'devis gratuit'),
            'is_active' => true,
            'average_rating' => 0,
            'reviews_count' => 0,
            'google_rating' => $place['rating'] ?? null,
            'google_reviews_count' => $place['userRatingCount'] ?? 0,
            'city_ranking' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! empty($hours['periods'])) {
            $this->importOpeningHours($plumberId, $hours['periods']);
        }

        return $plumberId;
    }

    private function importOpeningHours(int $plumberId, array $periods): void
    {
        $grouped = [];
        foreach ($periods as $period) {
            $day = $period['open']['day'] ?? null;
            if ($day === null) {
                continue;
            }
            $dayOfWeek = $day === 0 ? 7 : $day;
            $grouped[$dayOfWeek][] = $period;
        }

        $format = fn ($p, $key) => str_pad($p[$key]['hour'] ?? 0, 2, '0', STR_PAD_LEFT)
            .':'.str_pad($p[$key]['minute'] ?? 0, 2, '0', STR_PAD_LEFT).':00';

        foreach ($grouped as $dayOfWeek => $dayPeriods) {
            usort($dayPeriods, fn ($a, $b) => ($a['open']['hour'] ?? 0) - ($b['open']['hour'] ?? 0));

            $first = $dayPeriods[0];
            $second = $dayPeriods[1] ?? null;

            DB::table('opening_hours')->insertOrIgnore([
                'plumber_id' => $plumberId,
                'day_of_week' => $dayOfWeek,
                'morning_open' => $format($first, 'open'),
                'morning_close' => $format($first, 'close'),
                'afternoon_open' => $second ? $format($second, 'open') : null,
                'afternoon_close' => $second ? $format($second, 'close') : null,
                'is_closed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function extractComponent(array $place, string $type): string
    {
        foreach ($place['addressComponents'] ?? [] as $comp) {
            if (in_array($type, $comp['types'] ?? [])) {
                return $comp['longText'] ?? ($comp['shortText'] ?? '');
            }
        }

        return '';
    }
}
