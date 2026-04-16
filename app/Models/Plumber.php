<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plumber extends Model
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
        'type', 'title', 'slug', 'place_id',
        'email', 'phone', 'mobile_phone', 'website', 'google_maps_url',
        'address', 'postal_code', 'city', 'department', 'city_id', 'latitude', 'longitude',
        'service_radius', 'description', 'seo_content', 'opening_hours', 'pricing', 'siret', 'photo',
        'emergency_24h', 'free_quote', 'rge_certified', 'specialties',
        'average_rating', 'reviews_count', 'google_rating', 'google_reviews_count', 'google_reviews',
        'is_active', 'city_ranking',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'is_active' => 'boolean',
            'emergency_24h' => 'boolean',
            'free_quote' => 'boolean',
            'rge_certified' => 'boolean',
            'specialties' => 'array',
            'google_reviews' => 'array',
            'average_rating' => 'decimal:1',
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
        $city = $this->cityRelation;
        $dept = $city?->departmentRelation;

        // Try to build full URL from relations
        if ($dept && $city) {
            return '/'.$dept->slug.'/'.$city->slug.'/'.$this->slug;
        }

        // Fallback: find department and city by raw fields
        if (! $dept && $this->department) {
            $dept = Department::where('number', $this->department)->first();
        }

        if (! $city && $this->postal_code) {
            $city = City::where('postal_code', $this->postal_code)->first();
        }

        if ($dept && $city) {
            return '/'.$dept->slug.'/'.$city->slug.'/'.$this->slug;
        }

        if ($dept) {
            return '/'.$dept->slug;
        }

        return '/';
    }

    public function getOpeningStatusAttribute(): string
    {
        $dayOfWeek = now()->dayOfWeekIso;
        $hour = $this->schedules->firstWhere('day_of_week', $dayOfWeek);

        if (! $hour) {
            return 'unknown';
        }

        return $hour->status;
    }

    public function getNextOpeningAttribute(): ?string
    {
        $now = now();
        $currentDay = $now->dayOfWeekIso;
        $minutes = $now->hour * 60 + $now->minute;

        for ($i = 0; $i < 7; $i++) {
            $day = (($currentDay - 1 + $i) % 7) + 1;
            $hour = $this->schedules->firstWhere('day_of_week', $day);

            if (! $hour || $hour->is_closed) {
                continue;
            }

            $openTime = $hour->morning_open;
            if (! $openTime) {
                continue;
            }

            $parts = explode(':', $openTime);
            $openMinutes = ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
            $formatted = substr($openTime, 0, 5);

            if ($i === 0 && $openMinutes > $minutes) {
                return "Ouvre à $formatted";
            }

            if ($i > 0) {
                return 'Ouvre '.OpeningHour::DAYS[$day]." à $formatted";
            }
        }

        return null;
    }

    // --- Scopes ---

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeEmergency(Builder $query): Builder
    {
        return $query->where('emergency_24h', true);
    }

    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm = 10): Builder
    {
        return $query->selectRaw(
            '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
            [$lat, $lng, $lat]
        )->having('distance', '<', $radiusKm)->orderBy('distance');
    }

    // --- Relations ---

    public function cityRelation(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(OpeningHour::class)->orderBy('day_of_week');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->where('is_rejected', false);
    }

    public function administrators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'plumber_user')->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
