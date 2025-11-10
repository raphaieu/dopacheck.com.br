<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Invalidar cache relacionado a um usuário
     */
    public static function invalidateUserCache(int $userId): void
    {
        Cache::forget("user_quick_stats_{$userId}");
        Cache::forget("recommended_challenges_user_{$userId}");
        Cache::forget("user_active_challenge_{$userId}");
    }

    /**
     * Invalidar cache relacionado a um desafio
     */
    public static function invalidateChallengeCache(int $challengeId): void
    {
        Cache::forget("challenge_stats_{$challengeId}");
        Cache::forget("challenge_participants_{$challengeId}");
        
        // Invalidar cache de recomendações (pode incluir este desafio)
        Cache::flush(); // Por enquanto, invalidar tudo. Futuro: usar tags
    }

    /**
     * Invalidar cache relacionado a um user challenge
     */
    public static function invalidateUserChallengeCache(int $userChallengeId, ?int $userId = null, ?int $challengeId = null): void
    {
        if ($userId) {
            self::invalidateUserCache($userId);
        }
        
        if ($challengeId) {
            self::invalidateChallengeCache($challengeId);
        }
    }

    /**
     * Invalidar todos os caches relacionados a check-ins
     */
    public static function invalidateCheckinCache(int $userId, int $challengeId): void
    {
        self::invalidateUserCache($userId);
        self::invalidateChallengeCache($challengeId);
    }
}

