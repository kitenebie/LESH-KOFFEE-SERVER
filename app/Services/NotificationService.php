<?php

namespace App\Services;

use App\Repositories\Interfaces\NotificationRepositoryInterface;

class NotificationService
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function getUserNotifications(int $userId)
    {
        return $this->notificationRepository->getByUser($userId);
    }

    public function markAsRead(int $id)
    {
        return $this->notificationRepository->markAsRead($id);
    }
}
