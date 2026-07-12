<?php

namespace App\Filament\Pages;

use App\Models\LeshWallet;
use App\Models\Order;
use App\Models\UserSubscription;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Rule;

class QRScanner extends Page
{
    protected string $view = 'filament.pages.qr-scanner';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-qr-code';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Operations';
    }

    public static function getNavigationLabel(): string
    {
        return 'QR Scanner';
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'qr-scanner';
    }

    #[Rule('required|string')]
    public string $qrData = '';

    public ?array $lastResult = null;

    public function processRedemption(): void
    {
        $this->validate();

        // Parse the QR JSON data
        $data = json_decode($this->qrData, true);

        if (!$data || !is_array($data)) {
            Notification::make()
                ->title('Invalid QR Data')
                ->body('Could not parse QR code data. Please ensure valid JSON is provided.')
                ->danger()
                ->send();
            return;
        }

        // Validate required fields
        if (($data['type'] ?? '') !== 'subscription_redeem') {
            Notification::make()
                ->title('Invalid QR Type')
                ->body('This QR code is not a subscription redemption code.')
                ->danger()
                ->send();
            return;
        }

        $userId = $data['user_id'] ?? null;
        $subscriptionId = $data['subscription_id'] ?? null;
        $userSubscriptionId = $data['user_subscription_id'] ?? null;

        if (!$userId || !$subscriptionId || !$userSubscriptionId) {
            Notification::make()
                ->title('Missing Data')
                ->body('QR code is missing required fields (user_id, subscription_id, user_subscription_id).')
                ->danger()
                ->send();
            return;
        }

        // Find the UserSubscription record
        $userSub = UserSubscription::with('subscription')
            ->where('id', $userSubscriptionId)
            ->where('user_id', $userId)
            ->where('subscription_id', $subscriptionId)
            ->first();

        if (!$userSub) {
            Notification::make()
                ->title('Not Found')
                ->body('Subscription record not found. Please verify the QR code.')
                ->danger()
                ->send();
            return;
        }

        // Verify it's active and usable
        if (!$userSub->is_usable) {
            $reason = $userSub->is_expired
                ? 'This subscription has expired.'
                : ($userSub->drinks_remaining <= 0
                    ? 'No drinks remaining on this subscription.'
                    : 'This subscription is not active.');

            Notification::make()
                ->title('Cannot Redeem')
                ->body($reason)
                ->warning()
                ->send();
            return;
        }

        // Decrement drinks_remaining
        $redeemed = $userSub->useDrink('Subscription drink redeemed via QR scanner');

        if (!$redeemed) {
            Notification::make()
                ->title('Redemption Failed')
                ->body('Failed to redeem drink. Please try again.')
                ->danger()
                ->send();
            return;
        }

        // Create an Order record
        $subscriptionName = $userSub->subscription->name ?? 'Subscription';
        $orderNumber = str_replace(' ', '', $subscriptionName) . '-' . now()->format('Ymd') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $order = Order::create([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i'),
            'status' => 'Completed',
            'total' => 0,
            'subtotal' => 0,
            'delivery_fee' => 0,
            'discount' => 0,
            'payment_method' => 'subscription',
        ]);

        // Create a WalletTransaction for audit trail
        $wallet = LeshWallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'currency' => 'PHP', 'is_active' => true]
        );

        $wallet->transactions()->create([
            'user_id' => $userId,
            'type' => 'debit',
            'amount' => 0,
            'description' => "Subscription redemption: {$subscriptionName}",
            'transaction_date' => now()->toDateString(),
        ]);

        // Store result for display
        $this->lastResult = [
            'order_number' => $order->order_number,
            'subscription_name' => $subscriptionName,
            'drinks_remaining' => $userSub->fresh()->drinks_remaining,
            'drinks_used' => $userSub->fresh()->drinks_used,
            'user_id' => $userId,
        ];

        // Clear input
        $this->qrData = '';

        Notification::make()
            ->title('✅ Drink Redeemed!')
            ->body("Order: {$order->order_number} — {$subscriptionName} ({$this->lastResult['drinks_remaining']} drinks remaining)")
            ->success()
            ->duration(8000)
            ->send();
    }
}
