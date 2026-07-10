<?php

namespace App\Repositories\Interfaces;

interface NotificationRepositoryInterface
{
    public function getByUser(int $userId);

    public function markAsRead(int $id);
}
