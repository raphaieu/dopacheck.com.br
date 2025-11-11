<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheHelper
{
    /**
     * Invalidar cache relacionado a um usuário
     */
    public static function invalidateUserCache(int $userId): void
    {
        try {
            Cache::forget("user_quick_stats_{$userId}");
            Cache::forget("recommended_challenges_user_{$userId}");
            Cache::forget("user_active_challenge_{$userId}");
        } catch (\Exception $e) {
            // Log do erro mas não falhar - cache é opcional
            Log::warning('Erro ao invalidar cache do usuário', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invalidar cache relacionado a um desafio
     */
    public static function invalidateChallengeCache(int $challengeId): void
    {
        try {
            Cache::forget("challenge_stats_{$challengeId}");
            Cache::forget("challenge_participants_{$challengeId}");
            Cache::forget("challenge_{$challengeId}");
            
            // Invalidar cache de recomendações específicas (não usar flush para evitar problemas)
            // Futuro: usar tags de cache quando disponível
        } catch (\Exception $e) {
            // Log do erro mas não falhar - cache é opcional
            Log::warning('Erro ao invalidar cache do desafio', [
                'challenge_id' => $challengeId,
                'error' => $e->getMessage()
            ]);
        }
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
        try {
            self::invalidateUserCache($userId);
            self::invalidateChallengeCache($challengeId);
        } catch (\Exception $e) {
            // Log do erro mas não falhar - cache é opcional
            Log::warning('Erro ao invalidar cache de check-in', [
                'user_id' => $userId,
                'challenge_id' => $challengeId,
                'error' => $e->getMessage()
            ]);
        }
    }
}

