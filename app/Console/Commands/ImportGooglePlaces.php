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
        {--departement= : Forcer un département spécifique}
        {--dry-run : Simuler sans insérer en base}';

    protected $description = 'Importe des plombiers depuis Google Places API — 1 requête par appel, jamais de doublon';

    private string $apiKey;

    private const FIELD_MASK = 'places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.internationalPhoneNumber,places.websiteUri,places.googleMapsUri,places.regularOpeningHours,places.rating,places.userRatingCount,places.reviews,places.types,places.location,places.editorialSummary,places.businessStatus,places.addressComponents';

    private const SEARCH_QUERIES = [
        'plombier',
        'plombier chauffagiste',
        'dépannage plomberie urgence',
        'chauffagiste',
    ];

    private const ALLOWED_TYPES = [
        'plumber',
        'electrician',
        'roofing_contractor',
        'general_contractor',
        'contractor',
        'hvac_contractor',
    ];

    private const EXCLUDED_TYPES = [
        'hospital',
        'doctor',
        'dentist',
        'pharmacy',
        'health',
        'veterinary_care',
        'school',
        'university',
        'church',
        'restaurant',
        'cafe',
        'bar',
        'lodging',
        'hotel',
        'car_repair',
        'car_dealer',
        'gas_station',
        'bank',
        'atm',
        'insurance_agency',
        'real_estate_agency',
        'lawyer',
        'accounting',
        'beauty_salon',
        'hair_care',
        'spa',
        'gym',
        'supermarket',
        'grocery_or_supermarket',
        'home_improvement_store',
        'hardware_store',
        'department_store',
        'furniture_store',
        'store',
        'shopping_mall',
        'home_goods_store',
        'electronics_store',
    ];

    private const CITIES_PER_BATCH = 10;

    public function handle(): int
    {
        $this->apiKey = config('services.google_places.api_key');
        if (! $this->apiKey) {
            $this->error('GOOGLE_PLACES_API_KEY non configurée.');

            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');

        $dept = $this->getNextDepartment();

        if (! $dept) {
            $this->info('Tous les départements sont entièrement traités.');

            return self::SUCCESS;
        }

        $progress = DB::table('google_import_progress')->where('department', $dept->number)->first();
        $cityOffset = $progress->city_offset ?? 0;
        $queryOffset = $progress->query_offset ?? 0;

        // Get cities for current batch
        $allCitiesCount = City::where('department', $dept->number)->count();
        $cities = City::where('department', $dept->number)
            ->orderByDesc('population')
            ->offset($cityOffset)
            ->limit(self::CITIES_PER_BATCH)
            ->pluck('name')
            ->toArray();

        if (empty($cities)) {
            // No more cities: department is definitively done
            $this->info("{$dept->name} : plus aucune ville à chercher. Département terminé définitivement.");
            DB::table('google_import_progress')->updateOrInsert(
                ['department' => $dept->number],
                ['completed' => true, 'last_run_at' => now(), 'updated_at' => now()]
            );

            return self::SUCCESS;
        }

        // Build all queries for this batch
        $allQueries = [];
        foreach ($cities as $city) {
            foreach (self::SEARCH_QUERIES as $searchType) {
                $allQueries[] = "$searchType $city";
            }
        }

        $totalQueries = count($allQueries);

        // All queries in this batch done: advance to next batch of cities
        if ($queryOffset >= $totalQueries) {
            $newCityOffset = $cityOffset + self::CITIES_PER_BATCH;
            $this->info("{$dept->name} : batch villes " . ($cityOffset + 1) . '-' . ($cityOffset + count($cities)) . " terminé. Passage aux villes suivantes.");
            DB::table('google_import_progress')->updateOrInsert(
                ['department' => $dept->number],
                [
                    'completed' => false,
                    'city_offset' => $newCityOffset,
                    'query_offset' => 0,
                    'last_run_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return self::SUCCESS;
        }

        $query = $allQueries[$queryOffset];
        $cityRange = ($cityOffset + 1) . '-' . ($cityOffset + count($cities)) . "/$allCitiesCount";
        $this->info("Département : {$dept->number} - {$dept->name} (villes $cityRange, requête " . ($queryOffset + 1) . "/$totalQueries)");
        $this->line("  Recherche : $query");

        // Execute single API call
        $places = $this->searchPlaces($query);
        $imported = 0;

        foreach ($places as $place) {
            $placeId = $place['id'] ?? '';
            if (! $placeId) {
                continue;
            }

            $placeTypes = $place['types'] ?? [];
            if ($this->isExcludedType($placeTypes)) {
                $this->line("    IGNORÉ (type non-plombier) : ".($place['displayName']['text'] ?? 'inconnu'));

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

        // Advance query_offset
        $totalImported = ($progress->total_imported ?? 0) + $imported;
        DB::table('google_import_progress')->updateOrInsert(
            ['department' => $dept->number],
            [
                'completed' => false,
                'query_offset' => $queryOffset + 1,
                'total_imported' => $totalImported,
                'last_run_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Cap at 100 imports per department
        if ($totalImported >= 100) {
            $this->info("$imported importé(s). $totalImported au total → limite de 100 atteinte, département terminé.");
            DB::table('google_import_progress')->where('department', $dept->number)
                ->update(['completed' => true, 'updated_at' => now()]);
        } else {
            $this->info("$imported importé(s) ($totalImported au total). Prochain appel → même département, requête suivante.");
        }

        return self::SUCCESS;
    }

    private function getNextDepartment(): ?Department
    {
        if ($this->option('departement')) {
            return Department::where('number', $this->option('departement'))->first();
        }

        $completed = DB::table('google_import_progress')
            ->where('completed', true)
            ->pluck('department');

        return Department::whereNotIn('number', $completed)
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
            'includedType' => 'plumber',
            'languageCode' => 'fr',
            'regionCode' => 'FR',
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

        $type = 0;
        $nomLower = strtolower($nom);
        if (str_contains($nomLower, 'chauffag') && str_contains($nomLower, 'plomb')) {
            $type = 2;
        } elseif (str_contains($nomLower, 'chauffag')) {
            $type = 1;
        } elseif (str_contains($nomLower, 'urgence') || str_contains($nomLower, 'dépann') || str_contains($nomLower, 'sos')) {
            $type = 3;
        }

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
        $country = $this->extractComponent($place, 'country');

        if ($country && $country !== 'France') {
            return null;
        }

        if ($cp && ! preg_match('/^\d{5}$/', $cp)) {
            return null;
        }

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
            'google_reviews' => $this->formatGoogleReviews($place['reviews'] ?? []),
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

    private function formatGoogleReviews(array $reviews): ?string
    {
        if (empty($reviews)) {
            return null;
        }

        $formatted = collect($reviews)->map(fn ($r) => [
            'author' => $r['authorAttribution']['displayName'] ?? 'Anonyme',
            'rating' => $r['rating'] ?? 0,
            'text' => $r['text']['text'] ?? '',
            'date' => $r['publishTime'] ?? null,
            'photo' => $r['authorAttribution']['photoUri'] ?? null,
        ])->filter(fn ($r) => ! empty($r['text']))->values()->all();

        return ! empty($formatted) ? json_encode($formatted, JSON_UNESCAPED_UNICODE) : null;
    }

    private function isExcludedType(array $placeTypes): bool
    {
        // If the place has at least one allowed type, keep it
        foreach ($placeTypes as $type) {
            if (in_array($type, self::ALLOWED_TYPES)) {
                return false;
            }
        }

        // If the place has any excluded type, reject it
        foreach ($placeTypes as $type) {
            if (in_array($type, self::EXCLUDED_TYPES)) {
                return true;
            }
        }

        // No allowed type found, but no excluded type either — keep it (generic business)
        return false;
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
