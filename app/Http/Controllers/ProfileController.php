<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
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
        ]);
    }
    
    /**
     * Show profile settings
     */
    public function settings(Request $request): Response
    {
        $user = $request->user();
        
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
                'preferences' => $user->preferences ?? [],
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
            $currentPreferences = $user->preferences ?? [];
            $user->preferences = array_merge_recursive($currentPreferences, $validated['preferences']);
        }
        
        $user->save();
        
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
