<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasTeams;
use Laravel\Cashier\Billable;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Billable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'plan',
        'whatsapp_number',
        'phone',
        'subscription_ends_at',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'profile_photo_url',
        'is_pro',
        'public_profile_url',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    /**
     * Corrige URLs absolutas (ex: avatar do Google) salvas em profile_photo_path.
     * O Jetstream assume "path do storage" e prefixa via Storage::url(), o que quebra
     * quando o valor já é uma URL completa.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            $path = $this->profile_photo_path;

            if (is_string($path) && $path !== '' && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))) {
                return $path;
            }

            if (! $path) {
                return $this->defaultProfilePhotoUrl();
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk($this->profilePhotoDisk());

            return $disk->url($path);
        });
    }

    // ========================================
    // DOPA CHECK SPECIFIC METHODS
    // ========================================

    /**
     * Check if user has PRO plan
     */
    public function getIsProAttribute(): bool
    {
        return $this->plan === 'pro' && 
               $this->subscription_ends_at && 
               $this->subscription_ends_at->isFuture();
    }

    /**
     * Get public profile URL
     */
    public function getPublicProfileUrlAttribute(): string
    {
        // Correção temporária até implementarmos as rotas
        try {
            return route('profile.public', $this->username ?: $this->id);
        } catch (\Exception $e) {
            // Fallback se a rota não existir ainda
            return url('/u/' . ($this->username ?: $this->id));
        }
    }

    /**
     * Get user's OAuth connections
     */
    public function oauthConnections(): HasMany
    {
        return $this->hasMany(OauthConnection::class);
    }

    /**
     * Get active challenges for the user
     */
    public function activeChallenges(): HasMany
    {
        return $this->userChallenges()->where('status', 'active');
    }
    
    /**
     * Get user's challenge participations
     */
    public function userChallenges(): HasMany
    {
        return $this->hasMany(UserChallenge::class);
    }

    /**
     * Get user's check-ins (through user challenges)
     */
    public function checkins(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            Checkin::class,
            UserChallenge::class,
            'user_id', // Foreign key on UserChallenge table
            'user_challenge_id', // Foreign key on Checkin table
            'id', // Local key on User table
            'id' // Local key on UserChallenge table
        );
    }

    /**
     * Get WhatsApp session
     */
    public function whatsappSession(): HasOne
    {
        return $this->hasOne(WhatsAppSession::class);
    }

    /**
     * Get challenges created by user
     */
    public function createdChallenges(): HasMany
    {
        return $this->hasMany(Challenge::class, 'created_by');
    }

    /**
     * Get today's check-ins
     */
    public function todayCheckins(): HasMany
    {
        return $this->checkins()
            ->whereDate('checked_at', today())
            ->with('task');
    }

    /**
     * Check if user can create more challenges
     */
    public function canCreateChallenge(): bool
    {
        if ($this->is_pro) {
            return true;
        }

        // Free users: 1 active challenge only
        return $this->activeChallenges()->count() < 1;
    }

    /**
     * Get user's current active challenge
     */
    public function currentChallenge(): ?UserChallenge
    {
        return $this->activeChallenges()->with(['challenge.tasks'])->first();
    }

    /**
     * Calculate user's overall stats
     */
    public function calculateStats(): array
    {
        $totalChallenges = $this->userChallenges()->count();
        $completedChallenges = $this->userChallenges()->where('status', 'completed')->count();
        $totalCheckins = $this->checkins()->count();
        $currentStreak = $this->calculateCurrentStreak();

        return [
            'total_challenges' => $totalChallenges,
            'completed_challenges' => $completedChallenges,
            'completion_rate' => $totalChallenges > 0 ? round(($completedChallenges / $totalChallenges) * 100, 2) : 0,
            'total_checkins' => $totalCheckins,
            'current_streak' => $currentStreak,
            'best_streak' => $this->userChallenges()->max('best_streak') ?? 0,
        ];
    }

    /**
     * Calculate current streak across all challenges
     */
    private function calculateCurrentStreak(): int
    {
        $checkins = $this->checkins()
            ->where('checked_at', '>=', now()->subDays(365)->startOfDay())
            ->orderBy('checked_at', 'desc')
            ->get()
            ->groupBy(function ($checkin) {
                return $checkin->checked_at instanceof \Carbon\Carbon
                    ? $checkin->checked_at->toDateString()
                    : \Carbon\Carbon::parse($checkin->checked_at)->toDateString();
            });

        $currentStreak = 0;
        $date = today();
        $maxDays = 366; // Limite de segurança

        while (isset($checkins[$date->toDateString()]) && $maxDays-- > 0) {
            $currentStreak++;
            $date->subDay();
        }

        return $currentStreak;
    }

    /**
     * Scope for PRO users
     */
    public function scopePro($query)
    {
        return $query->where('plan', 'pro')
                     ->where('subscription_ends_at', '>', now());
    }

    /**
     * Scope for active users (with recent activity)
     */
    public function scopeActive($query)
    {
        return $query->whereHas('checkins', function ($query) {
            $query->where('checked_at', '>=', now()->subDays(7));
        });
    }
}
