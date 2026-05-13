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
    private const MAX_TOOL_ITERATIONS = 4;

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'messages' => 'required|array|min:1|max:20',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:64',
            'page_url' => 'nullable|string|max:500',
        ]);

        $ip = $request->ip();
        $key = "chatbot:$ip";

        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['error' => 'Trop de requêtes, réessayez dans quelques minutes.'], 429);
        }
        RateLimiter::hit($key, 60);

        $apiKey = config('services.anthropic.key', '');
        if (! $apiKey) {
            Log::error('Chatbot: ANTHROPIC_API_KEY not found');

            return response()->json(['error' => 'Service temporairement indisponible.'], 503);
        }

        $messages = $request->input('messages');

        $system = <<<'SYSTEM'
Tu es l'assistant de Plombier SOS (www.plombier-sos.fr), un annuaire en ligne de plombiers en France.

Ton rôle :
1. Aider l'utilisateur à diagnostiquer son problème de plomberie
2. Évaluer l'urgence de la situation (fuite active = urgent, robinet qui goutte = pas urgent)
3. Donner des conseils de premiers gestes (couper l'eau, etc.)
4. Recommander un plombier près de chez lui via l'outil `search_plumbers`

Règles générales :
- Réponds en français, de manière concise (2-4 phrases max par réponse)
- Pose UNE question à la fois pour comprendre le problème
- En cas d'urgence (fuite importante, inondation, odeur de gaz), conseille d'abord de couper l'arrivée d'eau/gaz
- Ne donne JAMAIS de diagnostic technique définitif, tu n'es pas sur place
- Sois chaleureux mais professionnel

Recommandations de plombiers — RÈGLES STRICTES :
- Tu n'as AUCUNE connaissance préalable des plombiers, villes ou départements de France. Toute information sur un plombier doit venir EXCLUSIVEMENT de l'outil `search_plumbers`.
- N'INVENTE JAMAIS un nom de plombier, de ville, de commune, de département ou de code postal.
- Tant que l'utilisateur n'a pas explicitement donné sa ville OU son code postal, NE cite aucune ville et NE recommande aucun plombier. Demande-lui poliment où il se trouve.
- Dès qu'il fournit sa ville ou son code postal, appelle `search_plumbers` avec ces informations.
- N'appelle l'outil que si l'utilisateur a clairement énoncé une localisation (ex : "à Caen", "14000", "Paris 12"). Une mention indirecte comme "sous l'évier" ou "dans la salle de bain" n'est PAS une localisation.
- Quand tu cites un plombier, utilise UNIQUEMENT les noms et URLs retournés par l'outil, au format Markdown : [Nom du plombier](URL).
- Si l'outil retourne 0 plombier, dis-le honnêtement et propose à l'utilisateur de consulter www.plombier-sos.fr.
SYSTEM;

        $tools = [
            [
                'name' => 'search_plumbers',
                'description' => "Recherche les plombiers actifs en France pour une ville et/ou un code postal donnés. Retourne jusqu'à 5 plombiers triés par note Google. N'utilise cet outil QUE quand l'utilisateur a explicitement fourni sa ville ou son code postal.",
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'city' => [
                            'type' => 'string',
                            'description' => "Nom de la ville mentionnée par l'utilisateur (ex: 'Caen', 'Paris', 'Lyon'). Optionnel si postal_code est fourni.",
                        ],
                        'postal_code' => [
                            'type' => 'string',
                            'description' => "Code postal à 5 chiffres (ex: '14000'). Optionnel si city est fourni.",
                        ],
                    ],
                ],
            ],
        ];

        $apiMessages = $messages;
        $finalCity = null;
        $finalPostalCode = null;
        $finalText = null;
        $toolCallsDebug = [];

        for ($iter = 0; $iter < self::MAX_TOOL_ITERATIONS; $iter++) {
            try {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 500,
                    'system' => $system,
                    'tools' => $tools,
                    'messages' => $apiMessages,
                ]);
            } catch (\Exception $e) {
                Log::warning('Chatbot exception: '.$e->getMessage());

                return response()->json(['error' => 'Service temporairement indisponible.'], 503);
            }

            if (! $response->ok()) {
                Log::warning('Chatbot API error: '.$response->status().' '.$response->body());

                return response()->json(['error' => 'Erreur du service, réessayez.'], 500);
            }

            $payload = $response->json();
            $stopReason = $payload['stop_reason'] ?? null;
            $content = $payload['content'] ?? [];

            if ($stopReason === 'tool_use') {
                $apiMessages[] = ['role' => 'assistant', 'content' => $content];

                $toolResults = [];
                foreach ($content as $block) {
                    if (($block['type'] ?? null) !== 'tool_use') {
                        continue;
                    }
                    $toolUseId = $block['id'] ?? '';
                    $toolName = $block['name'] ?? '';
                    $toolInput = $block['input'] ?? [];

                    if ($toolName === 'search_plumbers') {
                        $result = $this->searchPlumbers(
                            trim((string) ($toolInput['city'] ?? '')),
                            trim((string) ($toolInput['postal_code'] ?? ''))
                        );
                        if ($result['city']) {
                            $finalCity = $result['city'];
                        }
                        if ($result['postal_code']) {
                            $finalPostalCode = $result['postal_code'];
                        }
                        $toolCallsDebug[] = [
                            'input' => $toolInput,
                            'resolved_city' => $result['city'],
                            'resolved_postal_code' => $result['postal_code'],
                            'count' => $result['count'],
                        ];
                        $toolResults[] = [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolUseId,
                            'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                        ];
                    } else {
                        $toolResults[] = [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolUseId,
                            'content' => json_encode(['error' => "Outil inconnu : $toolName"]),
                            'is_error' => true,
                        ];
                    }
                }

                $apiMessages[] = ['role' => 'user', 'content' => $toolResults];

                continue;
            }

            $textParts = [];
            foreach ($content as $block) {
                if (($block['type'] ?? null) === 'text') {
                    $textParts[] = $block['text'] ?? '';
                }
            }
            $finalText = trim(implode("\n", $textParts));
            break;
        }

        if ($finalText === null || $finalText === '') {
            Log::warning('Chatbot: empty final response after '.self::MAX_TOOL_ITERATIONS.' iterations');

            return response()->json(['error' => 'Service temporairement indisponible.'], 503);
        }

        $allMessages = array_merge($messages, [['role' => 'assistant', 'content' => $finalText]]);
        $sessionId = $request->input('session_id') ?: ($request->hasSession() ? session()->getId() : md5($request->ip().date('Y-m-d')));

        try {
            ChatbotConversation::updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'ip' => $request->ip(),
                    'city' => $finalCity,
                    'postal_code' => $finalPostalCode,
                    'messages' => $allMessages,
                    'message_count' => count($allMessages),
                    'page_url' => $request->input('page_url'),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Chatbot conversation save failed: '.$e->getMessage());
        }

        return response()->json([
            'message' => $finalText,
            'city' => $finalCity,
            'postal_code' => $finalPostalCode,
            'session_id' => $sessionId,
            'debug' => [
                'tool_calls' => $toolCallsDebug,
                'iterations' => $iter + 1,
            ],
        ]);
    }

    private function searchPlumbers(string $cityInput, string $postalCodeInput): array
    {
        $city = null;
        $postalCode = null;

        if (preg_match('/^\d{5}$/', $postalCodeInput)) {
            $postalCode = $postalCodeInput;
        }

        if ($cityInput !== '') {
            $normalized = mb_strtolower(str_replace(['-', "'", "\xe2\x80\x99"], ' ', $cityInput));
            $cityModel = City::whereRaw(
                "LOWER(REPLACE(REPLACE(name, '-', ' '), \"'\", ' ')) = ?",
                [$normalized]
            )->orderByDesc('population')->first();

            if ($cityModel) {
                $city = $cityModel->name;
                if (! $postalCode) {
                    $postalCode = $cityModel->postal_code;
                }
            } else {
                $city = $cityInput;
            }
        }

        if (! $city && ! $postalCode) {
            return [
                'city' => null,
                'postal_code' => null,
                'plumbers' => [],
                'page_url' => null,
                'count' => 0,
                'message' => 'Aucune localisation valide fournie.',
            ];
        }

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

        $plumbers = $query->orderByDesc('google_rating')
            ->limit(5)
            ->get(['title', 'slug', 'city', 'postal_code', 'phone', 'google_rating', 'emergency_24h', 'free_quote', 'type', 'department', 'city_id']);

        $cityModelForUrl = ($city && $postalCode)
            ? City::where('name', $city)->where('postal_code', $postalCode)->first()
            : null;
        $deptModel = $cityModelForUrl?->departmentRelation;
        $pageUrl = null;
        if ($deptModel && $cityModelForUrl) {
            $pageUrl = url("/{$deptModel->slug}/{$cityModelForUrl->slug}");
        } elseif ($deptModel) {
            $pageUrl = url("/{$deptModel->slug}");
        }

        Log::info('Chatbot tool search', [
            'city' => $city,
            'postal_code' => $postalCode,
            'count' => $plumbers->count(),
        ]);

        return [
            'city' => $city,
            'postal_code' => $postalCode,
            'count' => $plumbers->count(),
            'plumbers' => $plumbers->map(fn ($p) => [
                'name' => $p->title,
                'city' => $p->city,
                'postal_code' => $p->postal_code,
                'rating' => $p->google_rating,
                'emergency_24h' => (bool) $p->emergency_24h,
                'free_quote' => (bool) $p->free_quote,
                'url' => $p->url,
            ])->all(),
            'page_url' => $pageUrl,
        ];
    }
}
