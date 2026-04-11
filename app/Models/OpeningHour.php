<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningHour extends Model
{
    protected $table = 'opening_hours';

    const DAYS = [
        1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi',
        5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',
    ];

    protected $fillable = [
        'plumber_id', 'day_of_week', 'morning_open', 'morning_close',
        'afternoon_open', 'afternoon_close', 'is_closed',
    ];

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }

    public function getDayLabelAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? '';
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_closed) {
            return 'closed';
        }

        $minutes = now()->hour * 60 + now()->minute;
        $slots = [];

        if ($this->morning_open && $this->morning_close) {
            $slots[] = ['open' => $this->timeToMinutes($this->morning_open), 'close' => $this->timeToMinutes($this->morning_close)];
        }
        if ($this->afternoon_open && $this->afternoon_close) {
            $slots[] = ['open' => $this->timeToMinutes($this->afternoon_open), 'close' => $this->timeToMinutes($this->afternoon_close)];
        }

        if (empty($slots)) {
            return 'closed';
        }

        foreach ($slots as $slot) {
            if ($minutes >= $slot['open'] && $minutes < $slot['close']) {
                return ($slot['close'] - $minutes <= 30) ? 'closing_soon' : 'open';
            }
        }

        foreach ($slots as $slot) {
            if ($slot['open'] > $minutes && $slot['open'] - $minutes <= 30) {
                return 'opening_soon';
            }
        }

        return 'closed';
    }

    private function timeToMinutes(?string $time): int
    {
        if (! $time) {
            return 0;
        }
        $parts = explode(':', $time);

        return ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
    }

    public function plumber(): BelongsTo
    {
        return $this->belongsTo(Plumber::class);
    }
}
