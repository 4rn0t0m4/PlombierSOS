<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Models\City;
use App\Models\Plumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ChatbotController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'messages' => 'required|array|min:1|max:20',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:2000',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:5',
            'session_id' => 'nullable|string|max:64',
            'page_url' => 'nullable|string|max:500',
        ]);

        $ip = $request->ip();
        $key = "chatbot:$ip";

        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['error' => 'Trop de requêtes, réessayez dans quelques minutes.'], 429);
        }
        RateLimiter::hit($key, 60);

        $messages = $request->input('messages');
        $city = $request->input('city');
        $postalCode = $request->input('postal_code');

        // Try to detect city/postal code from all user messages
        if (! $postalCode || ! $city) {
            $allUserText = collect($messages)
                ->where('role', 'user')
                ->pluck('content')
                ->implode(' ');

            // Detect postal code
            if (! $postalCode && preg_match('/\b(\d{5})\b/', $allUserText, $cpMatch)) {
                $postalCode = $cpMatch[1];
            }

            // Detect city name from database
            if (! $city && ! $postalCode) {
                // Normalize text: replace dashes/apostrophes with spaces
                $normalizedText = str_replace(['-', "'", "\xe2\x80\x99"], ' ', mb_strtolower($allUserText));
                $words = preg_split('/[\s,.!?]+/', $normalizedText);
                $words = array_values(array_filter($words, fn ($w) => mb_strlen($w) >= 2));

                // Common French words that should never trigger a city match.
                // Without this, prepositions like "sous" wrongly match "Sous-Parsat" via prefix.
                $stopwords = [
                    'sous', 'sans', 'dans', 'avec', 'dont', 'hors', 'vers', 'sauf', 'chez', 'pour',
                    'mais', 'donc', 'alors', 'aussi', 'plus', 'moins', 'bien', 'mieux', 'encore',
                    'avant', 'apres', 'comme', 'ainsi', 'meme', 'juste', 'tres', 'trop',
                    'nous', 'vous', 'elles', 'leur', 'leurs', 'mien', 'tien', 'sien',
                    'avoir', 'etre', 'fait', 'dire', 'aller', 'venir', 'faire',
                    'peut', 'doit', 'faut', 'vais', 'vient', 'donne', 'prend', 'mettre', 'etait', 'etais', 'sera',
                    'fuite', 'evier', 'robinet', 'tuyau', 'tuyaux', 'joint', 'siphon', 'conduit',
                    'urgence', 'urgent', 'active', 'goutte', 'gouttes', 'arret', 'maison',
                    'cuisine', 'salle', 'bain', 'douche', 'toilettes', 'chauffe', 'chaudiere',
                    'plomberie', 'plombier', 'depannage', 'devis',
                    'bonjour', 'merci', 'voici', 'voila', 'oui', 'non', 'vraiment',
                ];
                $words = array_values(array_filter($words, fn ($w) => ! in_array($w, $stopwords, true)));

                // Try multi-word combinations first (longest match wins), then single words
                for ($len = min(4, count($words)); $len >= 1; $len--) {
                    for ($i = count($words) - $len; $i >= 0; $i--) {
                        $candidate = implode(' ', array_slice($words, $i, $len));
                        if (mb_strlen($candidate) < 4) {
                            continue;
                        }
                        // First: exact matches only
                        $exactMatches = City::whereRaw("LOWER(REPLACE(REPLACE(name, '-', ' '), \"'\", ' ')) = ?", [$candidate])
                            ->orderByDesc('population')
                            ->limit(5)
                            ->get();
                        if ($exactMatches->count() === 1) {
                            $city = $exactMatches->first()->name;
                            $postalCode = $exactMatches->first()->postal_code;
                            break 2;
                        } elseif ($exactMatches->count() > 1) {
                            $ambiguousCities = $exactMatches->map(fn ($c) => "{$c->name} ({$c->postal_code})")->implode(', ');
                            break 2;
                        }
                    }
                }
                // If exact match found, check if there are also "word-prefix" matches (e.g. "Hérouville" + "Hérouville Saint Clair")
                // but exclude false positives like "Nice" matching "Nicey" (different word root)
                if ($city) {
                    $wordPrefixMatches = City::whereRaw("LOWER(REPLACE(REPLACE(name, '-', ' '), \"'\", ' ')) LIKE ?", [mb_strtolower($city).' %'])
                        ->orderByDesc('population')
                        ->limit(4)
                        ->get();
                    if ($wordPrefixMatches->isNotEmpty()) {
                        // There are compound names starting with the same word — ask to clarify
                        $allMatches = collect([$city." ({$postalCode})"])
                            ->merge($wordPrefixMatches->map(fn ($c) => "{$c->name} ({$c->postal_code})"));
                        $ambiguousCities = $allMatches->implode(', ');
                        $city = null;
                        $postalCode = null;
                    }
                }
                // If no exact match at all, try prefix match
                if (! $city && empty($ambiguousCities)) {
                    foreach (array_reverse($words) as $candidate) {
                        if (mb_strlen($candidate) < 4) {
                            continue;
                        }
                        $prefixMatches = City::whereRaw("LOWER(REPLACE(REPLACE(name, '-', ' '), \"'\", ' ')) LIKE ?", [$candidate.' %'])
                            ->orderByDesc('population')
                            ->limit(5)
                            ->get();
                        if ($prefixMatches->count() === 1) {
                            $city = $prefixMatches->first()->name;
                            $postalCode = $prefixMatches->first()->postal_code;
                            break;
                        } elseif ($prefixMatches->count() > 1) {
                            $ambiguousCities = $prefixMatches->map(fn ($c) => "{$c->name} ({$c->postal_code})")->implode(', ');
                            break;
                        }
                    }
                }
            }
        }

        // Find relevant plumbers if location is provided
        $plumbersContext = '';
        if ($city || $postalCode) {
            $query = Plumber::active();
            if ($postalCode) {
                $deptPrefix = substr($postalCode, 0, 2);
                if (in_array($deptPrefix, ['97', '98'])) {
                    $deptPrefix = substr($postalCode, 0, 3);
                }
                $query->where(function ($q) use ($deptPrefix, $city) {
                    $q->where('department', $deptPrefix);
                    if ($city) {
                        $q->orWhere('city', 'LIKE', "$city%");
                    }
                });
            } elseif ($city) {
                $query->where('city', 'LIKE', "$city%");
            }

            $debugInfo = ['city' => $city, 'postal_code' => $postalCode, 'deptPrefix' => $deptPrefix ?? null];
            Log::info('Chatbot search', $debugInfo);

            $plumbers = $query->orderByDesc('google_rating')
                ->limit(5)
                ->get(['title', 'slug', 'city', 'postal_code', 'phone', 'google_rating', 'google_reviews_count', 'emergency_24h', 'free_quote', 'type', 'department', 'city_id']);

            $debugInfo['plumbers_found'] = $plumbers->count();
            Log::info('Chatbot results', $debugInfo);

            // Build a link to the city/department page
            $cityModel = City::where('name', $city)->where('postal_code', $postalCode)->first();
            $deptModel = $cityModel?->departmentRelation;
            $pageUrl = '';
            if ($deptModel && $cityModel) {
                $pageUrl = url("/{$deptModel->slug}/{$cityModel->slug}");
            } elseif ($deptModel) {
                $pageUrl = url("/{$deptModel->slug}");
            }

            if ($plumbers->isNotEmpty()) {
                $plumbersContext = "\n\nPlombiers disponibles dans la zone :\n";
                foreach ($plumbers as $p) {
                    $plumbersContext .= "- {$p->title} à {$p->city} ({$p->postal_code})";
                    $plumbersContext .= " — Note Google : ".($p->google_rating ?? 'N/A')."/5";
                    $plumbersContext .= $p->emergency_24h ? ' — Urgence 24h' : '';
                    $plumbersContext .= $p->free_quote ? ' — Devis gratuit' : '';
                    $plumbersContext .= " — URL : {$p->url}";
                    $plumbersContext .= "\n";
                }
                if ($pageUrl) {
                    $plumbersContext .= "\nPage complète des plombiers : {$pageUrl}\n";
                }
            } elseif ($pageUrl) {
                $plumbersContext .= "\n\nAucun plombier trouvé dans la base, mais propose ce lien vers la page de la ville : {$pageUrl}\n";
            }
        }

        if (! empty($ambiguousCities)) {
            $locationInfo = "L'utilisateur a mentionné une ville ambiguë. Plusieurs communes correspondent : {$ambiguousCities}. Demande-lui de préciser laquelle.";
        } elseif ($city) {
            $locationInfo = "Localisation détectée : {$city}".($postalCode ? " ({$postalCode})" : '');
        } else {
            $locationInfo = 'Localisation non détectée';
        }

        $hasPlumbers = $plumbersContext !== '';

        if ($hasPlumbers) {
            $recommendationBlock = <<<BLOCK
## Plombiers disponibles

Tu DOIS recommander UNIQUEMENT les plombiers listés ci-dessous. Format obligatoire : [Nom du plombier](URL).
{$plumbersContext}
BLOCK;
        } else {
            $recommendationBlock = <<<BLOCK
## AUCUN PLOMBIER DISPONIBLE POUR LE MOMENT

INTERDICTIONS ABSOLUES tant que l'utilisateur n'a pas donné sa ville ou son code postal :
- NE cite AUCUN nom de plombier (même en exemple)
- NE cite AUCUN nom de ville, village ou commune
- NE cite AUCUN nom de département (ni Creuse, ni Paris, ni AUCUN autre)
- NE cite AUCUN code postal
- N'INVENTE JAMAIS de plombier. Aucune liste ne t'a été fournie, donc tu ne connais AUCUN plombier.

À la place : pose UNE question pour avancer le diagnostic OU demande la ville/code postal de l'utilisateur.
BLOCK;
        }

        $system = <<<SYSTEM
Tu es l'assistant de Plombier SOS (www.plombier-sos.fr), un annuaire en ligne de plombiers en France.

Ton rôle :
1. Aider l'utilisateur à diagnostiquer son problème de plomberie
2. Évaluer l'urgence de la situation (fuite active = urgent, robinet qui goutte = pas urgent)
3. Donner des conseils de premiers gestes (couper l'eau, etc.)
4. Recommander les plombiers disponibles dans sa zone

{$locationInfo}

Règles générales :
- Réponds en français, de manière concise (2-4 phrases max par réponse)
- Pose UNE question à la fois pour comprendre le problème
- Ne donne JAMAIS de diagnostic technique définitif, tu n'es pas sur place
- En cas d'urgence (fuite importante, inondation, odeur de gaz), conseille d'abord de couper l'arrivée d'eau/gaz et d'appeler les urgences si nécessaire
- Sois chaleureux mais professionnel
- Ne dis pas que tu as des plombiers dans un autre département que celui demandé

{$recommendationBlock}
SYSTEM;

        $apiKey = config('services.anthropic.key', '');
        if (! $apiKey) {
            Log::error('Chatbot: ANTHROPIC_API_KEY not found');

            return response()->json(['error' => 'Service temporairement indisponible.'], 503);
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 300,
                'system' => $system,
                'messages' => $messages,
            ]);

            if ($response->ok()) {
                $text = $response->json('content.0.text');

                // Server-side guard: if no plumbers were provided but the model still mentioned a
                // department/city, override the response. This protects against LLM hallucination
                // even if the system prompt is somehow ignored.
                $guardTriggered = false;
                if (! $hasPlumbers && $text) {
                    $forbiddenPatterns = '/(creuse|guéret|gueret|bonnat|budelière|budeliere|en\s+(?:[a-zà-ü]+\s+){0,3}département)/i';
                    if (preg_match($forbiddenPatterns, $text)) {
                        $text = "Pour pouvoir vous recommander un plombier disponible dans votre secteur, j'ai d'abord besoin de savoir où vous vous trouvez. Pouvez-vous me donner votre ville ou votre code postal ?";
                        $guardTriggered = true;
                    }
                }

                // Save conversation
                $allMessages = array_merge($messages, [['role' => 'assistant', 'content' => $text]]);
                $sessionId = $request->input('session_id') ?: ($request->hasSession() ? session()->getId() : md5($request->ip().date('Y-m-d')));

                try {
                    ChatbotConversation::updateOrCreate(
                        ['session_id' => $sessionId],
                        [
                            'ip' => $request->ip(),
                            'city' => $city,
                            'postal_code' => $postalCode,
                            'messages' => $allMessages,
                            'message_count' => count($allMessages),
                            'page_url' => $request->input('page_url'),
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning('Chatbot conversation save failed: '.$e->getMessage());
                }

                return response()->json([
                    'message' => $text,
                    'city' => $city,
                    'postal_code' => $postalCode,
                    'session_id' => $sessionId,
                    'debug' => [
                        'has_plumbers' => $hasPlumbers,
                        'prompt_sha' => substr(sha1($system), 0, 8),
                        'guard_triggered' => $guardTriggered,
                    ],
                ]);
            }

            Log::warning('Chatbot API error: '.$response->status().' '.$response->body());

            return response()->json(['error' => 'Erreur du service, réessayez.'], 500);
        } catch (\Exception $e) {
            Log::warning('Chatbot exception: '.$e->getMessage());

            return response()->json(['error' => 'Service temporairement indisponible.'], 503);
        }
    }
}
