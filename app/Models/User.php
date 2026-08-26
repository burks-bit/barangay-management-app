<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'announcements_seen_at' => 'datetime',
        ];
    }

    /**
     * Number of published announcements posted after the user last viewed
     * the Announcements page. Used for the sidebar badge (no polling).
     */
    public function newAnnouncementsCount(): int
    {
        return Announcement::where('status', 'published')
            ->when($this->announcements_seen_at, fn ($q, $seen) => $q->where('published_at', '>', $seen))
            ->count();
    }

    public function markAnnouncementsSeen(): void
    {
        $this->forceFill(['announcements_seen_at' => now()])->save();
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function hasMemberProfile(): bool
    {
        return $this->memberProfile()->exists();
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->roles->first()?->name;
    }

    public function getPermissionsListAttribute(): array
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }
}