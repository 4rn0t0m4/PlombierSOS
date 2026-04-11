# CLAUDE.md

## Commandes

```bash
# Serveur dev
php artisan serve
npm run dev

# Build
npm run build

# Base de données
php artisan migrate
php artisan migrate:fresh

# Import villes/départements (depuis TopInstitut)
php artisan import:villes

# Import Google Places (10 plombiers/heure)
php artisan import:google-places
php artisan import:google-places --limit=20
php artisan import:google-places --departement=75
php artisan import:google-places --dry-run

# Cache
php artisan cache:clear && php artisan view:clear && php artisan route:clear

# Tests
php artisan test

# Formatage
./vendor/bin/pint
```

## Architecture

**Stack** : Laravel 13 / PHP 8.5+ / MySQL / Tailwind CSS v4 / Alpine.js / Vite

**Auth** : Custom `AuthController`. Session-based. Admin via `is_admin` boolean + `AdminMiddleware`.

### Routes

| Groupe | Fichier | Préfixe | Usage |
|--------|---------|---------|-------|
| Web | `routes/web.php` | `/` | Pages publiques, auth |
| Admin | `routes/admin.php` | `/admin` | Back-office |

### Types de plombier

```
0 = Plombier         → /plombier/{slug}
1 = Chauffagiste     → /chauffagiste/{slug}
2 = Plombier-Chauffagiste → /plombier-chauffagiste/{slug}
3 = Dépanneur urgence → /depanneur-urgence/{slug}
```

### Models

| Model | Table | Description |
|-------|-------|-------------|
| `Plumber` | `plumbers` | Types, scopes `active()`, `nearby()`, `emergency()`. Champs : `emergency_24h`, `free_quote`, `rge_certified`, `specialties` (JSON), `google_rating`, `place_id` |
| `Review` | `reviews` | 5 critères (`punctuality_rating`, `quality_rating`, `price_rating`, `cleanliness_rating`, `advice_rating`). Support anonyme avec confirmation email |
| `ServiceRequest` | `requests` | Mise en relation / demande d'intervention. Urgency : `normal`, `urgent`, `very_urgent`. Status : `new`, `sent`, `accepted`, `refused`, `completed` |
| `OpeningHour` | `opening_hours` | Horaires par jour (`day_of_week`, `morning_open/close`, `afternoon_open/close`, `is_closed`) |
| `Department` | `departments` | Primary key : `number`. Champs : `name`, `slug`, `region`, `article` |
| `City` | `cities` | Champs : `name`, `postal_code`, `slug`, `department`, `population` |
| `Message` | `messages` | Messages de contact directs |
| `User` | `users` | Champs : `username`, `last_name`, `first_name`, `phone`, `postal_code`, `city` |

### Services

- **GooglePlacesService** : Recherche textuelle et nearby via Google Places API (New)
- **GeoSearchService** : Recherche par proximité Haversine SQL
- **RatingService** : Recalcul `average_rating`/`reviews_count`
- **SlugService** : Slugification URL-safe
- **AudiotelService** : Masquage numéros + service mise en relation

### Import Google Places

- Planifié toutes les heures via scheduler (`routes/console.php`)
- 10 plombiers par exécution (dans le quota gratuit Google : 5000 req/mois)
- Parcourt les départements séquentiellement
- Détecte automatiquement : type (plombier/chauffagiste), urgence 24h, devis gratuit
- Importe : nom, adresse, téléphone, site web, coordonnées GPS, note Google, horaires

## Conventions

- Texte UI en **français**, code/DB en **anglais**
- URLs propres sans extension (ex: `/plombier/mon-slug`, `/departement/ain`, `/ville/lyon`)
- Thème : bleu-900 (header), rouge-600 (urgence/CTA)
- Composants : `<x-plombier-card>`, `<x-star-rating>`, `<x-phone-reveal>`, `<x-statut-ouverture>`
