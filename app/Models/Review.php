<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'plumber_id', 'user_id', 'author_username', 'author_email',
        'validation_token', 'email_verified_at',
        'title', 'content', 'ip', 'is_approved', 'is_rejected', 'response', 'response_date',
        'punctuality_rating', 'quality_rating', 'price_rating', 'cleanliness_rating', 'advice_rating',
        'intervention_type',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'is_rejected' => 'boolean',
            'response_date' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->user?->username ?? $this->author_username ?? 'Anonyme';
    }

    public function getAverageRatingAttribute(): float
    {
        return round(($this->punctuality_rating + $this->quality_rating + $this->price_rating + $this->cleanliness_rating + $this->advice_rating) / 5, 1);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true)->where('is_rejected', false);
    }

    public function plumber(): BelongsTo
    {
        return $this->belongsTo(Plumber::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
