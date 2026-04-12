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
        $prompt = "Tu es un rédacteur SEO spécialisé en plomberie. Rédige un texte de présentation unique (150-200 mots) pour ce professionnel. Le texte doit être naturel, informatif et optimisé pour le référencement local. N'invente pas de services non mentionnés. Utilise des paragraphes HTML (<p>).

Informations :
- Nom : {$data['title']}
- Type : {$data['type']}
- Ville : {$data['city']} ({$data['postal_code']})
- Département : {$data['department']}
- Urgence 24h : " . ($data['emergency_24h'] ? 'Oui' : 'Non') . "
- Devis gratuit : " . ($data['free_quote'] ? 'Oui' : 'Non') . "
- Note Google : " . ($data['google_rating'] ?? 'Non noté') . "/5 ({$data['google_reviews_count']} avis)
- Description existante : " . ($data['description'] ?? 'Aucune') . "

Réponds UNIQUEMENT avec le HTML (balises <p> uniquement, pas de <h1> ni de titre).";

        return $this->call($prompt);
    }

    public function generateForDepartment(array $data): ?string
    {
        $prompt = "Tu es un rédacteur SEO spécialisé en plomberie. Rédige un texte (100-150 mots) pour la page listant les plombiers du département {$data['name']} (région {$data['region']}). Le texte doit encourager les visiteurs à trouver un plombier dans ce département. Mentionne les grandes villes si possible. Utilise des paragraphes HTML (<p>).

Réponds UNIQUEMENT avec le HTML (balises <p> uniquement).";

        return $this->call($prompt);
    }

    public function generateForCity(array $data): ?string
    {
        $prompt = "Tu es un rédacteur SEO spécialisé en plomberie. Rédige un texte (100-150 mots) pour la page listant les plombiers de {$data['name']} ({$data['postal_code']}, département {$data['department']}). Le texte doit être optimisé pour le SEO local (\"plombier à {$data['name']}\"). Mentionne les services courants (dépannage, installation, entretien). Utilise des paragraphes HTML (<p>).

Réponds UNIQUEMENT avec le HTML (balises <p> uniquement).";

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
                // Remove markdown code fences if present
                $text = preg_replace('/^```html\s*/', '', $text);
                $text = preg_replace('/\s*```$/', '', $text);

                return trim($text);
            }

            Log::warning('Anthropic API error: ' . $response->body());
        } catch (\Exception $e) {
            Log::warning('Anthropic API exception: ' . $e->getMessage());
        }

        return null;
    }
}
