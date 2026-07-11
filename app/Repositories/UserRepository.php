<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserAddress;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function getProfile(int $userId)
    {
        return User::with(['activeSubscription', 'leshWallet', 'leshPoints'])->findOrFail($userId);
    }

    public function updateProfile(int $userId, array $data)
    {
        $user = User::findOrFail($userId);
        $user->update($data);

        return $user->fresh(['activeSubscription', 'leshWallet', 'leshPoints']);
    }

    public function getAddresses(int $userId)
    {
        return UserAddress::where('user_id', $userId)
            ->orderBy('is_default', 'desc')
            ->get();
    }

    public function addAddress(int $userId, array $data)
    {
        return UserAddress::create(array_merge($data, ['user_id' => $userId]));
    }
}
