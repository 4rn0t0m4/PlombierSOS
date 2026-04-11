<?php

namespace App\Services;

use Illuminate\Support\Str;

class SlugService
{
    public static function generate(string $text): string
    {
        $text = str_replace(['œ', 'Œ', 'æ', 'Æ'], ['oe', 'OE', 'ae', 'AE'], $text);

        return Str::slug($text);
    }
}
