<?php

namespace Modules\Authorization\Models\Passport;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Models\User;

class UserToken extends Token
{
    protected $table = 'oauth_access_tokens';

    protected static function booted(): void
    {
        static::addGlobalScope('user_tokens', function (Builder $query) {
            $query->whereNotNull('user_id');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
