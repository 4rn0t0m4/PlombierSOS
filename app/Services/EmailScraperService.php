<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailScraperService
{
    private const IGNORED_EMAILS = [
        'example.com', 'example.org', 'test.com', 'sentry.io',
        'wixpress.com', 'wordpress.com', 'googleapis.com',
    ];

    private const IGNORED_PREFIXES = [
        'noreply', 'no-reply', 'webmaster', 'admin@wordpress',
        'support@wix', 'info@example',
    ];

    /**
     * Scrape a website to find an email address.
     */
    public function findEmail(string $url): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; PlombierSOS/1.0)'])
                ->get($url);

            if (! $response->ok()) {
                return null;
            }

            $html = $response->body();

            // 1. Check mailto: links first (most reliable)
            preg_match_all('/mailto:([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i', $html, $mailtoMatches);

            // 2. Find all email patterns in the HTML
            preg_match_all('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $html, $allMatches);

            // Prioritize mailto links, then all matches
            $candidates = array_merge($mailtoMatches[1] ?? [], $allMatches[0] ?? []);
            $candidates = array_unique(array_map('strtolower', $candidates));

            foreach ($candidates as $email) {
                if ($this->isValidEmail($email)) {
                    return $email;
                }
            }
        } catch (\Exception $e) {
            Log::debug('Email scraping failed for '.$url.': '.$e->getMessage());
        }

        return null;
    }

    private function isValidEmail(string $email): bool
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = substr($email, strpos($email, '@') + 1);

        foreach (self::IGNORED_EMAILS as $ignored) {
            if (str_contains($domain, $ignored)) {
                return false;
            }
        }

        foreach (self::IGNORED_PREFIXES as $prefix) {
            if (str_starts_with($email, $prefix)) {
                return false;
            }
        }

        // Skip image file extensions used as fake emails
        if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|css|js)$/i', $email)) {
            return false;
        }

        return true;
    }
}
