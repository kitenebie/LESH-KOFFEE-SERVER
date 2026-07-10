<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Interfaces
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Repositories\Interfaces\LoyaltyRepositoryInterface;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\StampRepositoryInterface;
use App\Repositories\Interfaces\PromoRepositoryInterface;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Repositories\Interfaces\StoreRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Repositories\Interfaces\RatingRepositoryInterface;

// Implementations
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\WalletRepository;
use App\Repositories\LoyaltyRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\StampRepository;
use App\Repositories\PromoRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use App\Repositories\VoucherRepository;
use App\Repositories\RatingRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(LoyaltyRepositoryInterface::class, LoyaltyRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(StampRepositoryInterface::class, StampRepository::class);
        $this->app->bind(PromoRepositoryInterface::class, PromoRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(VoucherRepositoryInterface::class, VoucherRepository::class);
        $this->app->bind(RatingRepositoryInterface::class, RatingRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
