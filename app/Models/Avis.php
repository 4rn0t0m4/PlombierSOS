<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avis extends Model
{
    protected $fillable = [
        'plombier_id', 'user_id', 'pseudo_auteur', 'email_auteur',
        'token_validation', 'email_verified_at',
        'titre', 'contenu', 'ip', 'valide', 'refus', 'reponse', 'reponse_date',
        'note_ponctualite', 'note_qualite', 'note_prix', 'note_proprete', 'note_conseil',
        'type_intervention',
    ];

    protected function casts(): array
    {
        return [
            'valide' => 'boolean',
            'refus' => 'boolean',
            'reponse_date' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    public function getAuteurNameAttribute(): string
    {
        return $this->user?->pseudo ?? $this->pseudo_auteur ?? 'Anonyme';
    }

    public function getMoyenneAttribute(): float
    {
        return round(($this->note_ponctualite + $this->note_qualite + $this->note_prix + $this->note_proprete + $this->note_conseil) / 5, 1);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('valide', true)->where('refus', false);
    }

    public function plombier(): BelongsTo
    {
        return $this->belongsTo(Plombier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
