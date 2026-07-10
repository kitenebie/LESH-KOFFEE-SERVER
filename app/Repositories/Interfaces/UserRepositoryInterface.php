<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function getProfile(int $userId);

    public function updateProfile(int $userId, array $data);

    public function getAddresses(int $userId);

    public function addAddress(int $userId, array $data);
}
