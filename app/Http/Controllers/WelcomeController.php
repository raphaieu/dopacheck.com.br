<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Challenge;
use App\Models\User;
use App\Models\UserChallenge;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

final class WelcomeController extends Controller
{
    public function home(): Response
    {
        // Buscar estatísticas reais com cache (5 minutos)
        $stats = Cache::remember('welcome_stats', 300, function () {
            $totalCheckins = Checkin::whereNull('deleted_at')->count();
            $totalUsers = User::whereNotNull('email_verified_at')->count();
                $totalChallenges = Challenge::where('visibility', Challenge::VISIBILITY_GLOBAL)->count();
            
            // Calcular taxa de conclusão média
            $completedChallenges = UserChallenge::where('status', 'completed')->count();
            $totalParticipations = UserChallenge::count();
            $completionRate = $totalParticipations > 0 
                ? round(($completedChallenges / $totalParticipations) * 100) 
                : 0;
            
            return [
                'completion_rate' => $completionRate,
                'total_checkins' => $totalCheckins,
                'total_users' => $totalUsers,
                'total_challenges' => $totalChallenges,
            ];
        });

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'stats' => $stats,
            'seo' => [
                'title' => 'Início',
                'description' => 'DOPA Check é o Strava dos hábitos e da mente. Um sistema de check-ins que transforma disciplina em algo visual, social e viciante - no bom sentido.',
                'keywords' => 'DOPA Check, hábitos, tracker de hábitos, desafios, check-in, rotina, produtividade, bem-estar, metas, streak, comunidade, WhatsApp, dashboard',
            ],
        ]);
    }
}
