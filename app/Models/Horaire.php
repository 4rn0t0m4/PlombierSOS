<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horaire extends Model
{
    const JOURS = [
        1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi',
        5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',
    ];

    protected $fillable = [
        'plombier_id', 'jour', 'matin_ouverture', 'matin_fermeture',
        'aprem_ouverture', 'aprem_fermeture', 'ferme',
    ];

    protected function casts(): array
    {
        return ['ferme' => 'boolean'];
    }

    public function getJourLabelAttribute(): string
    {
        return self::JOURS[$this->jour] ?? '';
    }

    public function getStatutAttribute(): string
    {
        if ($this->ferme) {
            return 'ferme';
        }

        $minutes = now()->hour * 60 + now()->minute;
        $plages = [];

        if ($this->matin_ouverture && $this->matin_fermeture) {
            $plages[] = ['open' => $this->timeToMinutes($this->matin_ouverture), 'close' => $this->timeToMinutes($this->matin_fermeture)];
        }
        if ($this->aprem_ouverture && $this->aprem_fermeture) {
            $plages[] = ['open' => $this->timeToMinutes($this->aprem_ouverture), 'close' => $this->timeToMinutes($this->aprem_fermeture)];
        }

        if (empty($plages)) {
            return 'ferme';
        }

        foreach ($plages as $plage) {
            if ($minutes >= $plage['open'] && $minutes < $plage['close']) {
                return ($plage['close'] - $minutes <= 30) ? 'ferme_bientot' : 'ouvert';
            }
        }

        foreach ($plages as $plage) {
            if ($plage['open'] > $minutes && $plage['open'] - $minutes <= 30) {
                return 'ouvre_bientot';
            }
        }

        return 'ferme';
    }

    private function timeToMinutes(?string $time): int
    {
        if (! $time) {
            return 0;
        }
        $parts = explode(':', $time);

        return ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
    }

    public function plombier(): BelongsTo
    {
        return $this->belongsTo(Plombier::class);
    }
}
