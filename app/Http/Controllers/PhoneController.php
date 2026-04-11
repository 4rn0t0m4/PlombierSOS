<?php

namespace App\Http\Controllers;

use App\Models\Plumber;
use App\Services\AudiotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function reveal(Request $request, AudiotelService $audiotel): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'plumber_id' => 'required|integer',
        ]);

        $phone = AudiotelService::decode($request->input('phone'));
        $plumber = Plumber::findOrFail($request->input('plumber_id'));
        $isMobile = preg_match('/Mobile|Android|iPhone|iPad/i', $request->userAgent() ?? '');

        if (AudiotelService::isCrawler($request->userAgent())) {
            return response()->json(['phone' => AudiotelService::format($phone), 'premium' => false]);
        }

        $result = $audiotel->getEphemeralNumber($phone, $plumber->id, $plumber->url, $request->ip());
        $formatted = AudiotelService::format($result['numero']);

        return response()->json([
            'phone' => $formatted,
            'tel' => preg_replace('/[^0-9+]/', '', $result['numero']),
            'code' => $result['code'] ?? null,
            'premium' => $result['premium'],
            'tarif' => $result['tarif'] ?? '',
            'mobile' => (bool) $isMobile,
        ]);
    }
}
