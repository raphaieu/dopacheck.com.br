<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\CacheHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Normaliza preferências para o formato esperado pelo frontend.
     * Isso também corrige dados legados corrompidos (ex.: array_merge_recursive
     * gerava arrays onde deveriam ser booleans).
     */
    private function normalizePreferences(?array $preferences): array
    {
        $preferences = $preferences ?? [];

        $getBool = function (mixed $value, bool $default): bool {
            // Corrige legado: se vier array (ex.: [true,false]), pega o último escalar.
            if (is_array($value)) {
                $value = end($value);
            }

            if (is_bool($value)) {
                return $value;
            }

            if (is_int($value)) {
                return $value === 1;
            }

            if (is_string($value)) {
                $v = strtolower(trim($value));
                if (in_array($v, ['1', 'true', 'on', 'yes'], true)) {
                    return true;
                }
                if (in_array($v, ['0', 'false', 'off', 'no'], true)) {
                    return false;
                }
            }

            return $default;
        };

        $privacy = $preferences['privacy'] ?? [];
        $notifications = $preferences['notifications'] ?? [];

        return [
            'privacy' => [
                'public_profile' => $getBool($privacy['public_profile'] ?? null, true),
                'show_progress' => $getBool($privacy['show_progress'] ?? null, true),
            ],
            'notifications' => [
                'email' => $getBool($notifications['email'] ?? null, true),
                'whatsapp' => $getBool($notifications['whatsapp'] ?? null, false),
                'daily_reminder' => $getBool($notifications['daily_reminder'] ?? null, false),
            ],
        ];
    }

    /**
     * Show user's public profile
     */
    public function public(string $username): Response
    {
        // Try to find user by username first, then by ID
        $user = User::where('username', $username)->first();
        
        if (!$user && is_numeric($username)) {
            $user = User::find($username);
        }
        
        if (!$user) {
            abort(404, 'Usuário não encontrado');
        }
        
        // Check if user allows public profile
        $preferences = $user->preferences ?? [];
        if (!($preferences['privacy']['public_profile'] ?? true)) {
            abort(403, 'Perfil privado');
        }
        
        // Get user's completed challenges
        $completedChallenges = $user->userChallenges()
            ->completed()
            ->with(['challenge'])
            ->latest('completed_at')
            ->get();
        
        // Get current active challenge (if user allows showing progress)
        $currentChallenge = null;
        if ($preferences['privacy']['show_progress'] ?? true) {
            $currentChallenge = $user->currentChallenge();
        }
        
        // Get recent checkins with images
        $recentCheckins = $user->checkins()
            ->where('checkins.status', 'approved')
            ->withImage()
            ->with(['task', 'userChallenge.challenge'])
            ->latest('checked_at')
            ->limit(9) // Grid 3x3
            ->get();
        
        // Calculate public stats
        $stats = $user->calculateStats();
        
        $appUrl = rtrim((string) config('app.url', 'https://dopacheck.com.br'), '/');
        $displayUsername = $user->username ? '@' . $user->username : null;
        $title = trim($user->name . ($displayUsername ? ' (' . $displayUsername . ')' : '')) . ' | DOPA Check';
        $description = Str::of("Veja o perfil público de {$user->name} no DOPA Check e acompanhe desafios e check-ins.")
            ->squish()
            ->limit(180)
            ->toString();

        $ogImageUrl = route('og.user', ['username' => $user->username ?: (string) $user->id]);

        return Inertia::render('Profile/Public', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->profile_photo_url,
                'created_at' => $user->created_at,
            ],
            'completedChallenges' => $completedChallenges,
            'currentChallenge' => $currentChallenge ? [
                'id' => $currentChallenge->id,
                'challenge' => $currentChallenge->challenge,
                'current_day' => $currentChallenge->current_day,
                'progress_percentage' => $currentChallenge->progress_percentage,
                'started_at' => $currentChallenge->started_at,
            ] : null,
            'recentCheckins' => $recentCheckins,
            'stats' => [
                'total_challenges' => $stats['total_challenges'],
                'completed_challenges' => $stats['completed_challenges'],
                'completion_rate' => $stats['completion_rate'],
                'total_checkins' => $stats['total_checkins'],
                'current_streak' => $stats['current_streak'],
                'best_streak' => $stats['best_streak'],
            ],
        ])->withViewData([
            'seo' => [
                'type' => 'profile',
                'title' => $title,
                'description' => $description,
                'image' => $ogImageUrl,
                'image_type' => 'image/jpeg',
                'image_width' => '1200',
                'image_height' => '630',
                'image_alt' => 'Foto de perfil de ' . $user->name,
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'ProfilePage',
                    'name' => $user->name,
                    'description' => $description,
                    'url' => url()->current(),
                    'image' => $ogImageUrl,
                    'mainEntity' => [
                        '@type' => 'Person',
                        'name' => $user->name,
                        'identifier' => $user->username ?: (string) $user->id,
                        'url' => $appUrl . '/u/' . ($user->username ?: (string) $user->id),
                        'image' => $ogImageUrl,
                    ],
                ],
            ],
        ]);
    }
    
    /**
     * Show profile settings
     */
    public function settings(Request $request): Response
    {
        $user = $request->user();
        $preferences = $this->normalizePreferences($user->preferences);
        
        return Inertia::render('Profile/Settings', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'phone' => $user->phone,
                'plan' => $user->plan,
                'is_pro' => $user->is_pro,
                'subscription_ends_at' => $user->subscription_ends_at,
                'preferences' => $preferences,
            ],
            'whatsappSession' => $user->whatsappSession,
        ]);
    }
    
    /**
     * Update profile settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username,' . $user->id, 'regex:/^[a-zA-Z0-9_]+$/'],
            'phone' => ['nullable', 'string', 'max:20'],
            'preferences.notifications.email' => ['boolean'],
            'preferences.notifications.whatsapp' => ['boolean'],
            'preferences.notifications.daily_reminder' => ['boolean'],
            'preferences.privacy.public_profile' => ['boolean'],
            'preferences.privacy.show_progress' => ['boolean'],
        ]);
        
        // Update basic fields
        if (isset($validated['username'])) {
            $user->username = $validated['username'];
        }
        
        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        
        // Update preferences
        if (isset($validated['preferences'])) {
            $currentPreferences = $this->normalizePreferences($user->preferences);
            $incomingPreferences = $this->normalizePreferences($validated['preferences']);
            // array_merge_recursive transforma escalares (ex: boolean) em arrays quando a chave já existe,
            // causando estado quebrado no frontend (checkboxes "conflitando" e reset após salvar).
            // array_replace_recursive faz o comportamento esperado: override dos valores.
            $user->preferences = array_replace_recursive($currentPreferences, $incomingPreferences);
        }
        
        $user->save();
        
        // Invalidar cache relacionado
        CacheHelper::invalidateUserCache($user->id);
        
        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso!');
    }
    
    /**
     * Show user stats page
     */
    public function stats(Request $request): Response
    {
        $user = $request->user();
        
        // Get detailed stats
        $stats = $user->calculateStats();
        
        // Get challenges breakdown
        $challengesBreakdown = $user->userChallenges()
            ->with('challenge')
            ->get()
            ->groupBy('status')
            ->map(function ($challenges) {
                return $challenges->count();
            });
        
        // Get monthly checkins for chart
        $monthlyCheckins = $user->checkins()
            ->selectRaw('DATE_FORMAT(checked_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->pluck('count', 'month');
        
        // Get tasks performance
        $tasksPerformance = $user->checkins()
            ->with('task')
            ->get()
            ->groupBy('task.hashtag')
            ->map(function ($checkins) {
                return [
                    'task' => $checkins->first()->task,
                    'total_checkins' => $checkins->count(),
                    'last_checkin' => $checkins->max('checked_at'),
                ];
            })
            ->values();
        
        return Inertia::render('Profile/Stats', [
            'stats' => $stats,
            'challengesBreakdown' => $challengesBreakdown,
            'monthlyCheckins' => $monthlyCheckins,
            'tasksPerformance' => $tasksPerformance,
        ]);
    }
}
