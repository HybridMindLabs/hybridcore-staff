<?php

namespace Hybridcore\Staff\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffMember extends Model
{
    protected $fillable = [
        'user_id', 'name', 'role_label', 'group', 'bio',
        'avatar', 'color', 'discord', 'steam', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Avatar to display: the linked user's, else the stored one. */
    public function displayAvatar(): ?string
    {
        return $this->user?->avatar ?? $this->avatar;
    }

    /** Public profile URL for the linked user, if any. */
    public function profileUrl(): ?string
    {
        return $this->user?->username ? route('profile.show', $this->user->username) : null;
    }
}
