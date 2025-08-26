<?php

namespace Litepie\Integration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class IntegrationSecret extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'integration_id',
        'name',
        'secret_key',
        'status',
        'last_used_at',
        'expires_at',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'secret_key',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (IntegrationSecret $secret) {
            if (empty($secret->secret_key)) {
                $secret->secret_key = static::generateSecretKey();
            }

            if (empty($secret->status)) {
                $secret->status = 'active';
            }

            if (empty($secret->name)) {
                $secret->name = 'Secret Key ' . (static::where('integration_id', $secret->integration_id)->count() + 1);
            }
        });
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('integration.table_names.integration_secrets', 'integration_secrets');
    }

    /**
     * Get the integration that owns this secret.
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * Scope to filter by active status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by inactive status.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope to filter non-expired secrets.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if the secret is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the secret is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the secret is valid (active and not expired).
     */
    public function isValid(): bool
    {
        return $this->isActive() && !$this->isExpired();
    }

    /**
     * Activate the secret.
     */
    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Deactivate the secret.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => 'inactive']);
    }

    /**
     * Mark the secret as used.
     */
    public function markAsUsed(): bool
    {
        return $this->update(['last_used_at' => now()]);
    }

    /**
     * Set expiration date.
     */
    public function setExpiration(\DateTimeInterface $date): bool
    {
        return $this->update(['expires_at' => $date]);
    }

    /**
     * Remove expiration date.
     */
    public function removeExpiration(): bool
    {
        return $this->update(['expires_at' => null]);
    }

    /**
     * Generate a secret key.
     */
    protected static function generateSecretKey(): string
    {
        $length = config('integration.client.secret_length', 80);
        return Str::random($length);
    }

    /**
     * Get a masked version of the secret for display.
     */
    public function getMaskedSecretAttribute(): string
    {
        $secret = $this->secret_key;
        $visibleChars = 8;
        
        if (strlen($secret) <= $visibleChars) {
            return str_repeat('*', strlen($secret));
        }

        return substr($secret, 0, 4) . str_repeat('*', strlen($secret) - $visibleChars) . substr($secret, -4);
    }
}
