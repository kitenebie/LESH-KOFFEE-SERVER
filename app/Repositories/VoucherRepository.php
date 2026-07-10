<?php

namespace App\Repositories;

use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Repositories\Interfaces\VoucherRepositoryInterface;

class VoucherRepository implements VoucherRepositoryInterface
{
    public function getAll()
    {
        return Voucher::where('is_active', true)->get();
    }

    public function getByUser(int $userId)
    {
        return UserVoucher::with('voucher')
            ->where('user_id', $userId)
            ->where('is_used', false)
            ->get();
    }
}
