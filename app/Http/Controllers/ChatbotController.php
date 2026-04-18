<?php

namespace App\Http\Controllers;

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

                // Try multi-word combinations first (longest match wins), then single words
                for ($len = min(4, count($words)); $len >= 1; $len--) {
                    for ($i = count($words) - $len; $i >= 0; $i--) {
                        $candidate = implode(' ', array_slice($words, $i, $len));
                        if (mb_strlen($candidate) < 3) {
                            continue;
                        }
                        // Search exact match + prefix matches together
                        $matches = City::whereRaw("LOWER(REPLACE(REPLACE(name, '-', ' '), \"'\", ' ')) LIKE ?", [$candidate.'%'])
                            ->orderByDesc('population')
                            ->limit(5)
                            ->get();
                        if ($matches->count() === 1) {
                            $city = $matches->first()->name;
                            $postalCode = $matches->first()->postal_code;
                            break 2;
                        } elseif ($matches->count() > 1) {
                            $ambiguousCities = $matches->map(fn ($c) => "{$c->name} ({$c->postal_code})")->implode(', ');
                            break 2;
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

        $system = <<<SYSTEM
Tu es l'assistant de Plombier SOS (www.plombier-sos.fr), un annuaire en ligne de plombiers en France.

Ton rôle :
1. Aider l'utilisateur à diagnostiquer son problème de plomberie
2. Évaluer l'urgence de la situation (fuite active = urgent, robinet qui goutte = pas urgent)
3. Donner des conseils de premiers gestes (couper l'eau, etc.)
4. Recommander les plombiers disponibles dans sa zone

{$locationInfo}

Règles :
- Réponds en français, de manière concise (2-4 phrases max par réponse)
- Pose UNE question à la fois pour comprendre le problème
- Si l'utilisateur n'a pas donné sa ville ou son code postal, demande-le
- Quand tu recommandes un plombier, donne son nom et un lien cliquable au format [Nom du plombier](URL)
- Ne donne JAMAIS de diagnostic technique définitif, tu n'es pas sur place
- En cas d'urgence (fuite importante, inondation, odeur de gaz), conseille d'abord de couper l'arrivée d'eau/gaz et d'appeler les urgences si nécessaire
- Sois chaleureux mais professionnel
- N'invente JAMAIS de plombier. Utilise UNIQUEMENT les plombiers listés ci-dessous. Si aucun plombier n'est listé, propose à l'utilisateur de chercher sur le site www.plombier-sos.fr
- Ne dis pas que tu as des plombiers dans un autre département que celui demandé
{$plumbersContext}
SYSTEM;

        $apiKey = env('ANTHROPIC_API_KEY', '');
        if (! $apiKey) {
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

                return response()->json([
                    'message' => $text,
                    'city' => $city,
                    'postal_code' => $postalCode,
                    'debug' => $debugInfo ?? null,
                ]);
            }

            Log::warning('Chatbot API error: '.$response->body());

            return response()->json(['error' => 'Erreur du service, réessayez.'], 500);
        } catch (\Exception $e) {
            Log::warning('Chatbot exception: '.$e->getMessage());

            return response()->json(['error' => 'Service temporairement indisponible.'], 503);
        }
    }
}
