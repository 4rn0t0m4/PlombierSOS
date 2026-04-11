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
0 = Plombier         → /plombier/{slug}.html
1 = Chauffagiste     → /chauffagiste/{slug}.html
2 = Plombier-Chauffagiste → /plombier-chauffagiste/{slug}.html
3 = Dépanneur urgence → /depanneur-urgence/{slug}.html
```

### Models clés

**Plombier** : Types, scopes `valide()`, `nearby()`, `urgence()`. Champs spéciaux : `urgence_24h`, `devis_gratuit`, `agree_rge`, `specialites` (JSON), `google_rating`, `place_id`.

**Avis** : 5 critères (ponctualité, qualité, prix, propreté, conseil). Support anonyme avec confirmation email.

**Demande** : Mise en relation / demande d'intervention. Niveaux d'urgence : normale, urgente, très urgente.

### Services

- **GooglePlacesService** : Recherche textuelle et nearby via Google Places API (New)
- **GeoSearchService** : Recherche par proximité Haversine SQL
- **RatingService** : Recalcul moyenne/nb_avis
- **SlugService** : Slugification URL-safe
- **AudiotelService** : Masquage numéros + service mise en relation

### Import Google Places

- Planifié toutes les heures via scheduler (`routes/console.php`)
- 10 plombiers par exécution (dans le quota gratuit Google : 5000 req/mois)
- Parcourt les départements séquentiellement
- Détecte automatiquement : type (plombier/chauffagiste), urgence 24h, devis gratuit
- Importe : nom, adresse, téléphone, site web, coordonnées GPS, note Google, horaires

## Conventions

- Texte UI en **français**
- URLs SEO avec suffixe `.html`
- Thème : bleu-900 (header), rouge-600 (urgence/CTA)
- Composants : `<x-plombier-card>`, `<x-star-rating>`, `<x-phone-reveal>`, `<x-statut-ouverture>`
