<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hosting extends Model
{
    protected $table = 'hosting';
    protected $primaryKey = 'hosting_id';

    protected $fillable = [
        'hosting_name',
        'hosting_provider',
        'plan',
        'expiry_date',
        'cost',
        'auto_renew',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'cost'        => 'decimal:2',
            'auto_renew'  => 'boolean',
        ];
    }

    /**
     * The user who created this record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Days remaining until expiry.
     * Positive = days left, negative = days overdue.
     */
    public function getDaysRemainingAttribute(): int
    {
        return now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    /**
     * Status label: active, warn, danger, expired.
     */
    public function getStatusAttribute(): string
    {
        $days = $this->days_remaining;

        if ($days < 0)   return 'expired';
        if ($days <= 7)  return 'danger';
        if ($days <= 30) return 'warn';
        return 'active';
    }
}