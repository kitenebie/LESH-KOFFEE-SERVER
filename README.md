# Lesh Kaffe & Pasalubong — Backend Server (LeshServer)

> A Laravel 13 backend API + Filament 5 admin panel for the **Lesh Kaffe & Pasalubong** coffee shop app. Provides RESTful APIs for the mobile app and a full CMS dashboard for store management.

---

## 📋 Project Overview

| Field | Value |
|-------|-------|
| **Framework** | Laravel 13 |
| **PHP Version** | 8.3+ |
| **Admin Panel** | Filament 5 |
| **Frontend** | Livewire 4 + Blaze |
| **Database** | SQLite (local dev) |
| **Payment Gateway** | BUX.ph (Philippines) |
| **API Base** | `http://s1102464823.onlinehome.us/api` |
| **Architecture** | Repository → Service → Controller pattern |

---

## 🏗️ Architecture

### Design Pattern: Repository-Service-Controller

```
[Routes] → [Controllers] → [Services] → [Repositories] → [Models/DB]
```

- **Controllers**: Handle HTTP request/response (thin layer)
- **Services**: Business logic (validation, orchestration, calculations)
- **Repositories**: Database queries (isolated from business logic)
- **Repository Interfaces**: Contracts for dependency injection

### Authentication

- **Custom header-based auth** (no Laravel Sanctum/Passport tokens)
- The mobile app sends `X-User-Id` header with every request
- `IdentifyUser` middleware reads the header and calls `Auth::setUser()` so `Auth::id()` works throughout the request lifecycle

```php
// App\Http\Middleware\IdentifyUser
$userId = $request->header('X-User-Id');
$user = User::find($userId);
Auth::setUser($user);
```

---

## 📁 Folder Structure

```
LeshServer/
├── app/
│   ├── Filament/
│   │   └── Resources/         # Filament admin panel resources (15 resources)
│   │       ├── CategoryResource.php
│   │       ├── DeliveryTrackingResource.php
│   │       ├── LeshWalletResource.php
│   │       ├── LoyaltyTransactionResource.php
│   │       ├── NotificationResource.php
│   │       ├── OrderResource.php
│   │       ├── ProductResource.php
│   │       ├── PromoResource.php
│   │       ├── StampAchievementResource.php
│   │       ├── StoreResource.php
│   │       ├── SubscriptionResource.php
│   │       ├── UserResource.php
│   │       ├── UserVoucherResource.php
│   │       ├── VoucherResource.php
│   │       └── WalletTransactionResource.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/           # REST API controllers (15 controllers)
│   │   │       ├── AuthController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── LoyaltyController.php
│   │   │       ├── NotificationController.php
│   │   │       ├── OrderController.php
│   │   │       ├── PaymentController.php
│   │   │       ├── ProductController.php
│   │   │       ├── PromoController.php
│   │   │       ├── RatingController.php
│   │   │       ├── StampController.php
│   │   │       ├── StoreController.php
│   │   │       ├── SubscriptionController.php
│   │   │       ├── UserController.php
│   │   │       ├── VoucherController.php
│   │   │       └── WalletController.php
│   │   └── Middleware/
│   │       └── IdentifyUser.php   # X-User-Id → Auth::setUser()
│   ├── Models/                # Eloquent models (22 models)
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── ProductCustomization.php
│   │   ├── ProductRating.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── LeshWallet.php
│   │   ├── WalletTransaction.php
│   │   ├── LeshPoints.php
│   │   ├── LoyaltyTransaction.php
│   │   ├── Notification.php
│   │   ├── StampAchievement.php
│   │   ├── StampHistory.php
│   │   ├── Voucher.php
│   │   ├── UserVoucher.php
│   │   ├── Promo.php
│   │   ├── Subscription.php
│   │   ├── UserAddress.php
│   │   ├── DeliveryTracking.php
│   │   ├── Store.php
│   │   └── SpotlightCustomer.php
│   ├── Providers/             # Service providers
│   ├── Repositories/          # Data access layer (13 repositories + interfaces)
│   │   ├── Interfaces/        # Repository contracts
│   │   ├── CategoryRepository.php
│   │   ├── LoyaltyRepository.php
│   │   ├── NotificationRepository.php
│   │   ├── OrderRepository.php
│   │   ├── ProductRepository.php
│   │   ├── PromoRepository.php
│   │   ├── RatingRepository.php
│   │   ├── StampRepository.php
│   │   ├── StoreRepository.php
│   │   ├── SubscriptionRepository.php
│   │   ├── UserRepository.php
│   │   ├── VoucherRepository.php
│   │   └── WalletRepository.php
│   └── Services/              # Business logic layer (13 services)
│       ├── CategoryService.php
│       ├── LoyaltyService.php
│       ├── NotificationService.php
│       ├── OrderService.php
│       ├── ProductService.php
│       ├── PromoService.php
│       ├── RatingService.php
│       ├── StampService.php
│       ├── StoreService.php
│       ├── SubscriptionService.php
│       ├── UserService.php
│       ├── VoucherService.php
│       └── WalletService.php
├── database/
│   ├── database.sqlite        # SQLite database file
│   ├── migrations/            # 26 migration files
│   ├── factories/             # Model factories
│   └── seeders/               # Database seeders
├── routes/
│   ├── api.php                # REST API routes
│   ├── web.php                # Web routes (Filament admin)
│   └── console.php            # Artisan commands
├── config/                    # Laravel configuration
├── resources/                 # Views, Blade templates
├── public/                    # Public assets
├── storage/                   # Logs, cache, uploads
├── tests/                     # PHPUnit tests
├── composer.json
├── package.json
├── vite.config.js
├── phpunit.xml
├── phpstan.neon               # Static analysis config
└── pint.json                  # Code style config
```

---

## 🗃️ Database Schema (22 Models)

### Model Relationships

```
User
├── hasOne → LeshWallet
├── hasOne → LeshPoints
├── hasMany → UserAddress
├── hasMany → Order
├── hasMany → WalletTransaction
├── hasMany → LoyaltyTransaction
├── hasMany → Notification
├── hasMany → StampAchievement
├── hasMany → UserVoucher
├── hasMany → DeliveryTracking
├── hasMany → ProductRating
└── belongsTo → Subscription (active_subscription_id)

Product
├── belongsTo → Category
├── hasOne → ProductCustomization
├── hasMany → OrderItem
└── hasMany → StampHistory

Order
├── belongsTo → User
├── hasMany → OrderItem
└── hasOne → DeliveryTracking

LeshWallet
├── belongsTo → User
└── hasMany → WalletTransaction

LeshPoints
├── belongsTo → User
└── hasMany → LoyaltyTransaction

Voucher
├── hasMany → UserVoucher
└── hasOne → Promo

Store
└── hasOne → SpotlightCustomer
```

### User Model Fields

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Full name |
| `first_name` | string | First name |
| `email` | string | Email address |
| `phone` | string | Phone number |
| `password` | string (hidden) | Hashed password |
| `avatar` | string | Avatar URL |
| `member_level` | string | Silver, Gold, Platinum |
| `member_level_label` | string | Display label |
| `wallet_balance` | decimal(2) | Current wallet balance |
| `loyalty_points` | integer | Current loyalty points |
| `stamps_collected` | integer | Total stamps collected |
| `stamps_required` | integer | Stamps needed for reward |
| `subscription_balance` | integer | Remaining subscription drinks |
| `active_subscription_id` | FK | Current subscription plan |
| `joined_date` | date | Registration date |
| `latitude` | decimal(8) | User location lat |
| `longitude` | decimal(8) | User location lng |

---

## 🌐 API Endpoints

### Authentication
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| POST | `/api/auth/login` | AuthController@login | Login |
| POST | `/api/auth/register` | AuthController@register | Register new user |
| POST | `/api/auth/logout` | AuthController@logout | Logout |

### Products & Categories
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/products` | ProductController@index | List all products |
| GET | `/api/products/{id}` | ProductController@show | Get product details |
| GET | `/api/categories` | CategoryController@index | List categories |

### Orders
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/orders` | OrderController@index | User's orders (active + past) |
| POST | `/api/orders` | OrderController@store | Place a new order |

### Wallet
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/wallet` | WalletController@index | Get wallet balance & transactions |
| POST | `/api/wallet/topup` | WalletController@topUp | Top up wallet |
| POST | `/api/wallet/debit` | WalletController@debit | Debit wallet |

### Loyalty Points
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/loyalty/transactions` | LoyaltyController@index | List loyalty transactions |
| GET | `/api/loyalty/points` | LoyaltyController@points | Get current points balance |
| POST | `/api/loyalty/earn` | LoyaltyController@earn | Earn points |
| POST | `/api/loyalty/redeem` | LoyaltyController@redeem | Redeem points |
| POST | `/api/loyalty/recalculate` | LoyaltyController@recalculate | Recalculate balance |

### Notifications
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/notifications` | NotificationController@index | List notifications |
| PATCH | `/api/notifications/{id}/read` | NotificationController@markAsRead | Mark as read |

### Stamps
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/stamps` | StampController@index | Get stamp achievements & history |

### Promos & Vouchers
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/promos` | PromoController@index | List active promos |
| GET | `/api/vouchers` | VoucherController@index | List all vouchers |
| GET | `/api/vouchers/unclaimed` | VoucherController@unclaimed | Unclaimed vouchers |
| GET | `/api/vouchers/claimed` | VoucherController@claimed | Claimed vouchers |
| POST | `/api/vouchers/{id}/claim` | VoucherController@claim | Claim a voucher |

### Subscriptions & Store
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/subscriptions` | SubscriptionController@index | List subscription plans |
| GET | `/api/store` | StoreController@index | Store info & spotlight customer |

### User Profile
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/user/profile` | UserController@profile | Get user profile |
| PUT | `/api/user/profile` | UserController@updateProfile | Update profile |
| GET | `/api/user/addresses` | UserController@addresses | List addresses |
| POST | `/api/user/addresses` | UserController@addAddress | Add new address |

### Payments (BUX.ph)
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| POST | `/api/payments/checkout` | PaymentController@checkout | Create payment request |
| POST | `/api/payments/webhook` | PaymentController@webhook | Payment webhook callback |
| GET | `/api/payments/success` | PaymentController@success | Payment success redirect |
| GET | `/api/payments/status/{reqId}` | PaymentController@status | Check payment status |

### Ratings
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| POST | `/api/ratings` | RatingController@store | Rate a product |
| POST | `/api/ratings/order` | RatingController@rateOrder | Rate all items in an order |
| GET | `/api/ratings/product/{id}` | RatingController@productRatings | Get product ratings |
| GET | `/api/ratings/order/{orderId}` | RatingController@orderRatings | Get order ratings |

---

## 🖥️ Filament Admin Panel (15 Resources)

The admin panel at `/admin` provides full CRUD management:

| Resource | Description |
|----------|-------------|
| **UserResource** | Manage users, view profiles |
| **CategoryResource** | Product categories |
| **ProductResource** | Products with customization options |
| **OrderResource** | View and manage orders |
| **LeshWalletResource** | User wallets |
| **WalletTransactionResource** | Wallet transaction history |
| **LoyaltyTransactionResource** | Loyalty points history |
| **NotificationResource** | Manage notifications |
| **StampAchievementResource** | Stamp card configurations |
| **PromoResource** | Promotional campaigns |
| **VoucherResource** | Voucher codes |
| **UserVoucherResource** | User-claimed vouchers |
| **SubscriptionResource** | Subscription plans |
| **StoreResource** | Store settings |
| **DeliveryTrackingResource** | Delivery tracking entries |

---

## 🗄️ Database Migrations (26 files)

| Migration | Table |
|-----------|-------|
| `0001_01_01_000000` | `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001` | `cache`, `cache_locks` |
| `0001_01_01_000002` | `jobs`, `job_batches`, `failed_jobs` |
| `2026_07_07_000001` | `subscriptions` |
| `2026_07_07_000002` | `user_addresses` |
| `2026_07_07_000003` | `categories` |
| `2026_07_07_000004` | `products` |
| `2026_07_07_000005` | `product_customizations` |
| `2026_07_07_000006` | `orders` |
| `2026_07_07_000007` | `lesh_wallets`, `order_items` |
| `2026_07_07_000008` | `wallet_transactions` |
| `2026_07_07_000009` | `loyalty_transactions` |
| `2026_07_07_000010` | `user_notifications` |
| `2026_07_07_000011` | `stamp_achievements` |
| `2026_07_07_000012` | `stamp_histories` |
| `2026_07_07_000013` | `vouchers` |
| `2026_07_07_000014` | `user_vouchers` |
| `2026_07_07_000015` | `promos` |
| `2026_07_07_000016` | `delivery_trackings` |
| `2026_07_07_000017` | `stores` |
| `2026_07_07_000018` | `spotlight_customers` |
| `2026_07_07_000020` | Add `active_subscription_id` FK to users |
| `2026_07_08_000001` | `lesh_points` |
| `2026_07_09_000001` | Add payment fields to orders |
| `2026_07_09_000002` | `product_ratings` |

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 18+ & npm
- SQLite (or configure MySQL/PostgreSQL in `.env`)

### Install & Setup

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Install frontend dependencies
npm install
npm run build

# Or use the setup script:
composer setup
```

### Development Server

```bash
# Start all services (server + queue + vite)
composer dev

# Or individually:
php artisan serve          # API server on :8000
php artisan queue:listen   # Queue worker
npm run dev                # Vite dev server
```

### Admin Panel

Access the Filament admin at: `http://localhost:8000/admin`

---

## 🧪 Testing & Quality

```bash
# Run all tests
composer test

# Lint (Laravel Pint)
composer lint

# Static analysis (PHPStan)
composer types:check

# CI check (lint + types + tests)
composer ci:check
```

---

## 📦 Key Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^13.17 | Laravel core |
| `filament/filament` | ~5.0 | Admin panel & resources |
| `livewire/livewire` | ^4.1 | Reactive UI components |
| `livewire/blaze` | ^1.0 | Livewire starter kit |
| `laravel/tinker` | ^3.0 | REPL for debugging |
| `larastan/larastan` | ^3.9 | Static analysis |
| `laravel/pint` | ^1.27 | Code style fixer |
| `phpunit/phpunit` | ^12.5 | Testing |
| `laravel/sail` | ^1.53 | Docker dev environment |

---

## 💳 Payment Integration (BUX.ph)

The app integrates with **BUX.ph** for wallet top-ups:

1. App calls `POST /api/payments/checkout` with amount
2. Server creates a BUX.ph payment request and returns a redirect URL
3. User completes payment on BUX.ph
4. BUX.ph calls `POST /api/payments/webhook` on success
5. Server credits the user's Lesh Wallet
6. App polls `GET /api/payments/status/{reqId}` for confirmation

---

## 📝 Notes

- The server uses SQLite for development. Configure MySQL/PostgreSQL in `.env` for production.
- All API routes are in `routes/api.php` (no auth middleware gates — relies on `X-User-Id` header).
- The Filament admin panel provides a complete dashboard for store operators.
- Repository interfaces are in `app/Repositories/Interfaces/` for testability and DI.
