<?php

namespace App\Services;

use App\Models\Plombier;

class RatingService
{
    public function recalculate(Plombier $plombier): void
    {
        $avis = $plombier->approvedAvis;

        if ($avis->isEmpty()) {
            $plombier->update(['moyenne' => 0, 'nb_avis' => 0]);

            return;
        }

        $total = $avis->sum(fn ($a) => $a->moyenne);
        $moyenne = round($total / $avis->count(), 1);

        $plombier->update([
            'moyenne' => $moyenne,
            'nb_avis' => $avis->count(),
        ]);
    }
}
