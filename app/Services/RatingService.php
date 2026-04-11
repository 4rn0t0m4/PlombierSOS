<?php

namespace App\Services;

use App\Models\Plumber;

class RatingService
{
    public function recalculate(Plumber $plumber): void
    {
        $reviews = $plumber->approvedReviews;

        if ($reviews->isEmpty()) {
            $plumber->update(['average_rating' => 0, 'reviews_count' => 0]);

            return;
        }

        $total = $reviews->sum(fn ($review) => $review->average_rating);
        $avg = round($total / $reviews->count(), 1);

        $plumber->update([
            'average_rating' => $avg,
            'reviews_count' => $reviews->count(),
        ]);
    }
}
