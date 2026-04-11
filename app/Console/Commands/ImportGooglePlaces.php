<?php

namespace App\Console\Commands;

use App\Models\Departement;
use App\Models\Plombier;
use App\Models\Ville;
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

        $dept = $this->getNextDepartement();
        if (! $dept) {
            $this->info('Tous les départements traités. Reset.');
            DB::table('google_import_progress')->update(['completed' => false]);
            $dept = $this->getNextDepartement();
        }

        $this->info("Département : {$dept->numero} - {$dept->departement}");

        $villes = Ville::where('departement', $dept->numero)
            ->orderByDesc('habitants')
            ->limit(5)
            ->pluck('nom_ville')
            ->toArray();

        if (empty($villes)) {
            $villes = [$dept->departement];
        }

        foreach (self::SEARCH_QUERIES as $searchType) {
            if ($imported >= $limit) {
                break;
            }

            foreach ($villes as $ville) {
                if ($imported >= $limit) {
                    break;
                }

                $query = "$searchType $ville";
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

                    if (Plombier::where('titre', $nom)->where('cp', $cp)->exists()) {
                        DB::table('google_imports')->insertOrIgnore([
                            'place_id' => $placeId,
                            'statut' => 'doublon',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        continue;
                    }

                    if ($dryRun) {
                        $villeNom = $this->extractComponent($place, 'locality');
                        $this->info("    [DRY-RUN] $nom - $villeNom ($cp)");
                        $imported++;

                        continue;
                    }

                    $plombierId = $this->createPlombier($place);

                    if ($plombierId) {
                        DB::table('google_imports')->insertOrIgnore([
                            'place_id' => $placeId,
                            'plombier_id' => $plombierId,
                            'statut' => 'importe',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $imported++;
                        $villeNom = $this->extractComponent($place, 'locality');
                        $this->info("    IMPORTÉ : $nom - $villeNom ($cp)");
                    }
                }
            }
        }

        DB::table('google_import_progress')->updateOrInsert(
            ['departement' => $dept->numero],
            [
                'completed' => true,
                'total_imported' => DB::raw("total_imported + $imported"),
                'last_run_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->info("$imported plombier(s) importé(s) dans le {$dept->numero} - {$dept->departement}.");

        return self::SUCCESS;
    }

    private function getNextDepartement(): ?Departement
    {
        if ($this->option('departement')) {
            return Departement::where('numero', $this->option('departement'))->first();
        }

        $processed = DB::table('google_import_progress')
            ->where('completed', true)
            ->pluck('departement');

        return Departement::whereNotIn('numero', $processed)
            ->orderBy('numero')
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
            $this->warn('    API Error: ' . $response->json('error.message', 'Inconnue'));

            return [];
        }

        usleep(100000);

        return $response->json('places', []);
    }

    private function createPlombier(array $place): ?int
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
        $urgence24h = false;
        if (str_contains($nomLower, '24h') || str_contains($nomLower, '24/24') || str_contains($nomLower, 'urgence')) {
            $urgence24h = true;
        }
        if (! empty($hours['periods'])) {
            $jours = collect($hours['periods'])->pluck('open.day')->unique()->count();
            if ($jours >= 7) {
                $urgence24h = true;
            }
        }

        $cp = $this->extractComponent($place, 'postal_code');
        $villeNom = $this->extractComponent($place, 'locality');
        $rue = $this->extractComponent($place, 'route');
        $numero = $this->extractComponent($place, 'street_number');

        $adresse = trim(($numero ? "$numero " : '') . $rue);

        $ville = null;
        if ($cp) {
            $ville = Ville::where('code_postal', $cp)
                ->orderByRaw("CASE WHEN nom_ville LIKE ? THEN 0 ELSE 1 END", [$villeNom . '%'])
                ->first();
        }

        $deptNum = $ville?->departement;
        if (! $deptNum && $cp && strlen($cp) >= 2) {
            $deptNum = substr($cp, 0, 2);
        }

        $slug = SlugService::generate($nom . ' ' . $villeNom . ' ' . $cp);
        $baseSlug = $slug;
        $i = 1;
        while (Plombier::where('slug', $slug)->exists()) {
            $slug = "$baseSlug-$i";
            $i++;
        }

        $telephone = $place['nationalPhoneNumber'] ?? '';
        $telephone = substr(preg_replace('/[^0-9+]/', '', $telephone), 0, 20);

        $horairesText = '';
        if (! empty($hours['weekdayDescriptions'])) {
            $horairesText = implode("\n", $hours['weekdayDescriptions']);
        }

        $plombierId = DB::table('plombiers')->insertGetId([
            'type' => $type,
            'titre' => $nom,
            'slug' => $slug,
            'place_id' => $place['id'],
            'email' => null,
            'telephone' => $telephone ?: null,
            'site_web' => $place['websiteUri'] ?? null,
            'google_maps_url' => $place['googleMapsUri'] ?? null,
            'adresse' => $adresse ?: null,
            'cp' => $cp ?: null,
            'ville' => $villeNom ?: null,
            'dept' => $deptNum ?: null,
            'ville_id' => $ville?->id,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'rayon_intervention' => 20,
            'description' => $place['editorialSummary']['text'] ?? null,
            'horaires' => $horairesText ?: null,
            'urgence_24h' => $urgence24h,
            'devis_gratuit' => str_contains($nomLower, 'devis gratuit'),
            'valide' => true,
            'moyenne' => 0,
            'nb_avis' => 0,
            'google_rating' => $place['rating'] ?? null,
            'google_nb_avis' => $place['userRatingCount'] ?? 0,
            'classement_ville' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! empty($hours['periods'])) {
            $this->importHoraires($plombierId, $hours['periods']);
        }

        return $plombierId;
    }

    private function importHoraires(int $plombierId, array $periods): void
    {
        $grouped = [];
        foreach ($periods as $period) {
            $day = $period['open']['day'] ?? null;
            if ($day === null) {
                continue;
            }
            $jour = $day === 0 ? 7 : $day;
            $grouped[$jour][] = $period;
        }

        $format = fn ($p, $key) => str_pad($p[$key]['hour'] ?? 0, 2, '0', STR_PAD_LEFT)
            . ':' . str_pad($p[$key]['minute'] ?? 0, 2, '0', STR_PAD_LEFT) . ':00';

        foreach ($grouped as $jour => $dayPeriods) {
            usort($dayPeriods, fn ($a, $b) => ($a['open']['hour'] ?? 0) - ($b['open']['hour'] ?? 0));

            $first = $dayPeriods[0];
            $second = $dayPeriods[1] ?? null;

            DB::table('horaires')->insertOrIgnore([
                'plombier_id' => $plombierId,
                'jour' => $jour,
                'matin_ouverture' => $format($first, 'open'),
                'matin_fermeture' => $format($first, 'close'),
                'aprem_ouverture' => $second ? $format($second, 'open') : null,
                'aprem_fermeture' => $second ? $format($second, 'close') : null,
                'ferme' => false,
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
