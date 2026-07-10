<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function getProfile(int $userId)
    {
        return $this->userRepository->getProfile($userId);
    }

    public function updateProfile(int $userId, array $data)
    {
        $allowedFields = ['name', 'first_name', 'email', 'phone', 'avatar'];
        $filteredData = array_intersect_key($data, array_flip($allowedFields));

        return $this->userRepository->updateProfile($userId, $filteredData);
    }

    public function getAddresses(int $userId)
    {
        return $this->userRepository->getAddresses($userId);
    }

    public function addAddress(int $userId, array $data)
    {
        return $this->userRepository->addAddress($userId, $data);
    }
}
