<?php

namespace Modules\Authorization\Models\Passport;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientToken extends Token
{
    protected $table = 'oauth_access_tokens';

    protected static function booted(): void
    {
        static::addGlobalScope('client_tokens', function (Builder $query) {
            $query->whereNull('user_id');
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function isRevoked(): bool
    {
        return (bool) $this->revoked;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return !$this->isRevoked() && !$this->isExpired();
    }
}
