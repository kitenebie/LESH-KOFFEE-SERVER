<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Add this trait to any model to automatically log create/update/delete events.
 *
 * Optionally override `getAuditLabel()` to customize the display label.
 * Optionally set `$auditExclude` to hide sensitive fields from logs.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('created', [], $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            $excluded = $model->getAuditExclude();

            // Filter out excluded fields and timestamps
            $filteredDirty = array_diff_key($dirty, array_flip($excluded), array_flip(['updated_at']));
            if (empty($filteredDirty)) return;

            $oldValues = [];
            $newValues = [];
            foreach ($filteredDirty as $key => $value) {
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $value;
            }

            $model->logAudit('updated', $oldValues, $newValues);
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getAuditableAttributes(), []);
        });
    }

    /**
     * Write the audit log entry.
     */
    protected function logAudit(string $action, array $oldValues, array $newValues): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'System',
                'action' => $action,
                'model_type' => get_class($this),
                'model_id' => $this->getKey(),
                'model_label' => $this->getAuditLabel(),
                'old_values' => !empty($oldValues) ? $oldValues : null,
                'new_values' => !empty($newValues) ? $newValues : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silent — audit logging should never break the app
            \Log::warning('[Audit] Failed to log', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a human-readable label for the model instance.
     * Override in your model for custom labels.
     */
    public function getAuditLabel(): string
    {
        // Try common identifier fields
        if (isset($this->order_number)) return "Order #{$this->order_number}";
        if (isset($this->name)) return $this->name;
        if (isset($this->code)) return $this->code;
        if (isset($this->title)) return $this->title;
        if (isset($this->email)) return $this->email;

        return class_basename($this) . " #{$this->getKey()}";
    }

    /**
     * Get attributes suitable for audit (exclude sensitive/large fields).
     */
    protected function getAuditableAttributes(): array
    {
        $excluded = $this->getAuditExclude();
        $attributes = $this->attributesToArray();

        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Fields to exclude from audit logs.
     * Override in model: protected array $auditExclude = ['password', 'remember_token'];
     */
    protected function getAuditExclude(): array
    {
        return property_exists($this, 'auditExclude')
            ? $this->auditExclude
            : ['password', 'remember_token', 'lesh_cvv', 'updated_at', 'created_at'];
    }
}
