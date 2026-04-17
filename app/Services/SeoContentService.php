<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeoContentService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('ANTHROPIC_API_KEY', '');
    }

    public function generateForPlumber(array $data): ?string
    {
        $prompt = <<<PROMPT
Rédige une présentation SEO (150-200 mots, en HTML avec des balises <p> uniquement) pour un professionnel de la plomberie.

Règles strictes :
- Parle du professionnel à la 3e personne ("l'entreprise", "ce plombier")
- Décris ses services concrets : dépannage, installation, réparation, entretien
- Mentionne sa zone d'intervention (ville + alentours)
- Si note Google élevée (>4), mentionne la satisfaction client
- Si urgence 24h, insiste sur la disponibilité
- Si devis gratuit, le mentionner
- N'invente RIEN qui n'est pas dans les données
- Pas de titre, pas de h1/h2, juste des <p>
- Ton professionnel et factuel, pas commercial/racoleur

Données :
- Nom : {$data['title']}
- Métier : {$data['type']}
- Ville : {$data['city']} ({$data['postal_code']})
- Urgence 24h : URGENCE
- Devis gratuit : DEVIS
- Note Google : NOTE/5 (AVIS avis)
PROMPT;

        $prompt = str_replace('URGENCE', $data['emergency_24h'] ? 'Oui' : 'Non', $prompt);
        $prompt = str_replace('DEVIS', $data['free_quote'] ? 'Oui' : 'Non', $prompt);
        $prompt = str_replace('NOTE', $data['google_rating'] ?? '-', $prompt);
        $prompt = str_replace('AVIS', $data['google_reviews_count'] ?? 0, $prompt);

        return $this->call($prompt);
    }

    public function generateForDepartment(array $data): ?string
    {
        $cities = $data['cities'] ?? '';

        $prompt = <<<PROMPT
Rédige un texte SEO (80-120 mots, balises <p> uniquement) pour une page qui liste les plombiers disponibles dans le département {$data['name']}.

Règles strictes :
- Explique qu'on peut trouver un plombier qualifié dans ce département
- Mentionne les types de services : dépannage urgent, installation sanitaire, chauffage, entretien
- Si des villes sont listées, cite les 3-4 principales comme zones couvertes
- Pas de formule vide type "votre département de confiance" ou "n'hésitez pas"
- Ton informatif et direct
- Pas de titre, juste des <p>

Principales villes du département : {$cities}
PROMPT;

        return $this->call($prompt);
    }

    public function generateForCity(array $data): ?string
    {
        $prompt = <<<PROMPT
Rédige un texte SEO (80-120 mots, balises <p> uniquement) pour une page listant les plombiers à {$data['name']} ({$data['postal_code']}).

Règles strictes :
- Commence par "À {$data['name']}" ou "Les plombiers à {$data['name']}"
- Décris les services disponibles : dépannage urgent, fuite d'eau, débouchage, installation, chaudière
- Mentionne que les professionnels interviennent à {$data['name']} et ses environs
- Pas de formule creuse ("n'hésitez pas", "faites confiance")
- Ton informatif et concret
- Pas de titre, juste des <p>
PROMPT;

        return $this->call($prompt);
    }

    public function generateReviewsSummary(array $data): ?string
    {
        $reviews = $data['reviews'];
        $reviewsText = collect($reviews)->map(fn ($r) => "- {$r['author']} ({$r['rating']}/5) : {$r['text']}")->implode("\n");

        $prompt = <<<PROMPT
Rédige un résumé concis (2-3 phrases, texte brut sans HTML) des avis clients pour ce professionnel de la plomberie.

Règles strictes :
- Synthétise les points forts et faibles mentionnés par les clients
- Mentionne la tendance générale (satisfaction, qualité, ponctualité, tarifs)
- Si tous les avis sont positifs, dis-le simplement sans exagérer
- Si des critiques reviennent, mentionne-les honnêtement
- N'invente rien, base-toi uniquement sur les avis fournis
- Pas de formule type "en résumé" ou "globalement"
- Commence directement par le constat

Professionnel : {$data['title']} ({$data['city']})
Note Google : {$data['rating']}/5

Avis :
{$reviewsText}
PROMPT;

        return $this->call($prompt);
    }

    private function call(string $prompt): ?string
    {
        if (! $this->apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 500,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if ($response->ok()) {
                $text = $response->json('content.0.text');
                $text = preg_replace('/^```html\s*/s', '', $text);
                $text = preg_replace('/\s*```$/s', '', $text);

                return trim($text);
            }

            Log::warning('Anthropic API error: '.$response->body());
        } catch (\Exception $e) {
            Log::warning('Anthropic API exception: '.$e->getMessage());
        }

        return null;
    }
}
