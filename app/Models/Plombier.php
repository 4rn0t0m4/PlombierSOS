<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plombier extends Model
{
    const TYPE_SLUGS = [
        0 => 'plombier',
        1 => 'chauffagiste',
        2 => 'plombier-chauffagiste',
        3 => 'depanneur-urgence',
    ];

    const TYPE_LABELS = [
        0 => 'Plombier',
        1 => 'Chauffagiste',
        2 => 'Plombier-Chauffagiste',
        3 => 'Dépanneur urgence',
    ];

    protected $fillable = [
        'type', 'titre', 'slug', 'place_id',
        'email', 'telephone', 'portable', 'site_web', 'google_maps_url',
        'adresse', 'cp', 'ville', 'dept', 'ville_id', 'latitude', 'longitude',
        'rayon_intervention', 'description', 'horaires', 'tarifs', 'siret', 'photo',
        'urgence_24h', 'devis_gratuit', 'agree_rge', 'specialites',
        'moyenne', 'nb_avis', 'google_rating', 'google_nb_avis',
        'valide', 'classement_ville',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'valide' => 'boolean',
            'urgence_24h' => 'boolean',
            'devis_gratuit' => 'boolean',
            'agree_rge' => 'boolean',
            'specialites' => 'array',
            'moyenne' => 'decimal:1',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    // --- Accessors ---

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? 'Plombier';
    }

    public function getTypeSlugAttribute(): string
    {
        return self::TYPE_SLUGS[$this->type] ?? 'plombier';
    }

    public function getUrlAttribute(): string
    {
        return '/' . $this->type_slug . '/' . $this->slug . '.html';
    }

    public function getStatutOuvertureAttribute(): string
    {
        $jourSemaine = now()->dayOfWeekIso;
        $horaire = $this->horairesRelation->firstWhere('jour', $jourSemaine);

        if (! $horaire) {
            return 'inconnu';
        }

        return $horaire->statut;
    }

    public function getProchaineOuvertureAttribute(): ?string
    {
        $now = now();
        $jourActuel = $now->dayOfWeekIso;
        $minutes = $now->hour * 60 + $now->minute;

        for ($i = 0; $i < 7; $i++) {
            $jour = (($jourActuel - 1 + $i) % 7) + 1;
            $horaire = $this->horairesRelation->firstWhere('jour', $jour);

            if (! $horaire || $horaire->ferme) {
                continue;
            }

            $openTime = $horaire->matin_ouverture;
            if (! $openTime) {
                continue;
            }

            $parts = explode(':', $openTime);
            $openMinutes = ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
            $heureFormatee = substr($openTime, 0, 5);

            if ($i === 0 && $openMinutes > $minutes) {
                return "Ouvre à $heureFormatee";
            }

            if ($i > 0) {
                return 'Ouvre ' . Horaire::JOURS[$jour] . " à $heureFormatee";
            }
        }

        return null;
    }

    // --- Scopes ---

    public function scopeValide(Builder $query): Builder
    {
        return $query->where('valide', true);
    }

    public function scopeUrgence(Builder $query): Builder
    {
        return $query->where('urgence_24h', true);
    }

    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm = 10): Builder
    {
        return $query->selectRaw(
            '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
            [$lat, $lng, $lat]
        )->having('distance', '<', $radiusKm)->orderBy('distance');
    }

    // --- Relations ---

    public function villeRelation(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public function horairesRelation(): HasMany
    {
        return $this->hasMany(Horaire::class)->orderBy('jour');
    }

    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }

    public function approvedAvis(): HasMany
    {
        return $this->hasMany(Avis::class)->where('valide', true)->where('refus', false);
    }

    public function administrateurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class);
    }
}
