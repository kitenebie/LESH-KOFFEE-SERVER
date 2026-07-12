<?php

namespace App\Services;

use App\Models\LeshPoints;
use App\Models\Product;
use App\Models\StampQuotaCategory;
use App\Models\StampQuotaRequirement;
use App\Models\User;
use App\Models\UserStampProgress;
use Illuminate\Support\Facades\Log;

class StampQuotaService
{
    /**
     * Process stamps for an order's items.
     * Called after order is placed successfully.
     *
     * @param int $userId
     * @param array $items [['product_id' => int, 'quantity' => int], ...]
     */
    public function processOrderStamps(int $userId, array $items): void
    {
        $user = User::find($userId);
        if (!$user) return;

        // Get user's current tier (or assign starting tier if none)
        $currentTier = $user->stamp_quota_category_id
            ? StampQuotaCategory::find($user->stamp_quota_category_id)
            : StampQuotaCategory::getStartingTier();

        if (!$currentTier) return; // No tiers configured

        // If user has no tier assigned, assign the starting one
        if (!$user->stamp_quota_category_id) {
            $user->update(['stamp_quota_category_id' => $currentTier->id]);
        }

        // Get product categories for ordered items
        $productIds = array_column($items, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Group quantities by product category
        $categoryQuantities = [];
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) continue;

            $catId = $product->category_id;
            $qty = (int) ($item['quantity'] ?? 1);
            $categoryQuantities[$catId] = ($categoryQuantities[$catId] ?? 0) + $qty;
        }

        if (empty($categoryQuantities)) return;

        // Get requirements for user's current tier
        $requirements = StampQuotaRequirement::where('stamp_quota_category_id', $currentTier->id)
            ->whereIn('category_id', array_keys($categoryQuantities))
            ->get();

        $totalPointsEarned = 0;

        foreach ($requirements as $requirement) {
            $catId = $requirement->category_id;
            $qty = $categoryQuantities[$catId] ?? 0;
            if ($qty <= 0) continue;

            // Get or create progress record
            $progress = UserStampProgress::firstOrCreate([
                'user_id' => $userId,
                'stamp_quota_requirement_id' => $requirement->id,
            ], [
                'stamp_quota_category_id' => $currentTier->id,
                'current_count' => 0,
                'is_completed' => false,
            ]);

            // Skip if already completed
            if ($progress->is_completed) continue;

            // Increment count (cap at required)
            $newCount = min($progress->current_count + $qty, $requirement->required_count);
            $stampsAdded = $newCount - $progress->current_count;

            $progress->update(['current_count' => $newCount]);

            // Award points per stamp
            if ($stampsAdded > 0 && $requirement->points_per_stamp > 0) {
                $pointsForStamps = $stampsAdded * $requirement->points_per_stamp;
                $totalPointsEarned += $pointsForStamps;
            }

            // Check if this requirement is now completed
            if ($newCount >= $requirement->required_count) {
                $progress->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);
            }
        }

        // Award per-stamp points
        if ($totalPointsEarned > 0) {
            $leshPoints = LeshPoints::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'is_active' => true]
            );
            $leshPoints->earn($totalPointsEarned, "Stamp quota progress (+{$totalPointsEarned} pts)");
        }

        // Check if ALL requirements for current tier are completed → promote
        $this->checkTierPromotion($userId, $currentTier);
    }

    /**
     * Check if user has completed all requirements for their tier and promote them.
     */
    private function checkTierPromotion(int $userId, StampQuotaCategory $currentTier): void
    {
        $allRequirements = StampQuotaRequirement::where('stamp_quota_category_id', $currentTier->id)->count();
        
        if ($allRequirements === 0) return;

        $completedCount = UserStampProgress::where('user_id', $userId)
            ->where('stamp_quota_category_id', $currentTier->id)
            ->where('is_completed', true)
            ->count();

        if ($completedCount < $allRequirements) return;

        // All requirements met! Award tier completion points
        if ($currentTier->reward_points > 0) {
            $leshPoints = LeshPoints::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'is_active' => true]
            );
            $leshPoints->earn(
                $currentTier->reward_points,
                "🏆 Completed {$currentTier->name} tier! (+{$currentTier->reward_points} pts)"
            );
        }

        // Promote to next tier
        $nextTier = $currentTier->getNextTier();
        if ($nextTier) {
            User::where('id', $userId)->update(['stamp_quota_category_id' => $nextTier->id]);
            Log::info("[StampQuota] User promoted!", [
                'user_id' => $userId,
                'from' => $currentTier->name,
                'to' => $nextTier->name,
            ]);
        } else {
            Log::info("[StampQuota] User completed highest tier!", [
                'user_id' => $userId,
                'tier' => $currentTier->name,
            ]);
        }
    }

    /**
     * Get user's stamp progress for their current tier.
     */
    public function getUserProgress(int $userId): array
    {
        $user = User::find($userId);
        if (!$user || !$user->stamp_quota_category_id) {
            $startingTier = StampQuotaCategory::getStartingTier();
            if (!$startingTier) return ['tier' => null, 'requirements' => []];

            return [
                'tier' => [
                    'id' => $startingTier->id,
                    'name' => $startingTier->name,
                    'slug' => $startingTier->slug,
                    'color' => $startingTier->color,
                    'rank' => $startingTier->rank,
                    'reward_points' => $startingTier->reward_points,
                ],
                'requirements' => $this->getRequirementsProgress($userId, $startingTier->id),
            ];
        }

        $tier = StampQuotaCategory::find($user->stamp_quota_category_id);

        return [
            'tier' => [
                'id' => $tier->id,
                'name' => $tier->name,
                'slug' => $tier->slug,
                'color' => $tier->color,
                'rank' => $tier->rank,
                'reward_points' => $tier->reward_points,
            ],
            'requirements' => $this->getRequirementsProgress($userId, $tier->id),
        ];
    }

    private function getRequirementsProgress(int $userId, int $tierId): array
    {
        $requirements = StampQuotaRequirement::with('productCategory')
            ->where('stamp_quota_category_id', $tierId)
            ->get();

        return $requirements->map(function ($req) use ($userId, $tierId) {
            $progress = UserStampProgress::where('user_id', $userId)
                ->where('stamp_quota_requirement_id', $req->id)
                ->first();

            return [
                'id' => $req->id,
                'category_name' => $req->productCategory->name ?? 'Unknown',
                'category_id' => $req->category_id,
                'required_count' => $req->required_count,
                'current_count' => $progress?->current_count ?? 0,
                'is_completed' => $progress?->is_completed ?? false,
                'points_per_stamp' => $req->points_per_stamp,
            ];
        })->toArray();
    }
}
