<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'model_label',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    /**
     * Get the short model name (e.g. "Order" from "App\Models\Order").
     */
    public function getModelNameAttribute(): string
    {
        return class_basename($this->model_type);
    }

    /**
     * Get changed fields summary for display.
     */
    public function getChangesSummaryAttribute(): string
    {
        if ($this->action === 'created') {
            return 'Record created';
        }

        if ($this->action === 'deleted') {
            return 'Record deleted';
        }

        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];
        $changes = [];

        foreach ($new as $key => $value) {
            $oldVal = $old[$key] ?? '—';
            $changes[] = "{$key}: {$oldVal} → {$value}";
        }

        return implode(', ', array_slice($changes, 0, 3)) . (count($changes) > 3 ? '...' : '');
    }
}
