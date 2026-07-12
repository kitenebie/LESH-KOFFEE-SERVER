<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipCard;
use App\Models\StampQuotaCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MembershipCardController extends Controller
{
    /**
     * GET /api/membership-card
     * 
     * Returns the user's current membership card with tier info
     * and all available tier colors from stamp_quota_categories.
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get user's membership card
        $card = MembershipCard::where('user_id', $userId)->first();

        // Get all tier colors from stamp_quota_categories
        $tiers = StampQuotaCategory::where('is_active', true)
            ->orderBy('rank', 'asc')
            ->get(['id', 'name', 'slug', 'rank', 'color', 'icon', 'reward_points']);

        return response()->json([
            'success' => true,
            'data' => [
                'card' => $card ? [
                    'id' => $card->id,
                    'card_tier' => $card->card_tier,
                    'card_number' => $card->card_number,
                    'card_exp' => $card->card_exp,
                    'is_active' => $card->is_active,
                    'tier_label' => $card->tier_label,
                ] : null,
                'tiers' => $tiers->map(fn($tier) => [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'slug' => $tier->slug,
                    'rank' => $tier->rank,
                    'color' => $tier->color,
                    'icon' => $tier->icon,
                    'reward_points' => $tier->reward_points,
                ]),
            ],
        ]);
    }
}
