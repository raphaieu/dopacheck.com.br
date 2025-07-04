<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\OauthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\User\LoginLinkController;

// ========================================
// DOPA CHECK CONTROLLERS
// ========================================
use App\Http\Controllers\DopaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\UserChallengeController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\ReportController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'dopacheck-app',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

Route::get('/', [WelcomeController::class, 'home'])->name('home');

// ========================================
// PUBLIC DOPA CHECK ROUTES
// ========================================

// Public profiles
Route::prefix('u')->group(function () {
    Route::get('/{username}', [ProfileController::class, 'public'])->name('profile.public');
});

// Public challenges
Route::prefix('challenges')->group(function () {
    Route::get('/', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::get('/{challenge}', [ChallengeController::class, 'show'])->name('challenges.show');
});

// ========================================
// AUTHENTICATION ROUTES
// ========================================

Route::prefix('auth')->group(
    function () {
        // OAuth
        Route::get('/redirect/{provider}', [OauthController::class, 'redirect'])->name('oauth.redirect');
        Route::get('/callback/{provider}', [OauthController::class, 'callback'])->name('oauth.callback');
        // Magic Link
        Route::middleware('throttle:login-link')->group(function () {
            Route::post('/login-link', [LoginLinkController::class, 'store'])->name('login-link.store');
            Route::get('/login-link/{token}', [LoginLinkController::class, 'login'])
                ->name('login-link.login')
                ->middleware('signed');
        });
    }
);

// ========================================
// AUTHENTICATED ROUTES
// ========================================

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    
    // Original dashboard (mantido para compatibilidade)
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    
    // DOPA Check dashboard (principal)
    Route::get('/dopa', [DopaController::class, 'dashboard'])->name('dopa.dashboard');
    
    // OAuth management
    Route::delete('/auth/destroy/{provider}', [OauthController::class, 'destroy'])->name('oauth.destroy');
    
    // Chat (existente)
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    
    // Subscriptions (existente)
    Route::resource('/subscriptions', SubscriptionController::class)
        ->names('subscriptions')
        ->only(['index', 'create', 'store', 'show']);
    
    // ========================================
    // DOPA CHECK AUTHENTICATED ROUTES
    // ========================================
    
    // Challenges management
    Route::prefix('challenges')->group(function () {
        Route::post('/', [ChallengeController::class, 'store'])->name('challenges.store');
        Route::get('/create', [ChallengeController::class, 'create'])->name('challenges.create');
        Route::post('/{challenge}/join', [ChallengeController::class, 'join'])->name('challenges.join');
        Route::delete('/{challenge}/leave', [ChallengeController::class, 'leave'])->name('challenges.leave');
    });
    
    // User challenges management
    Route::prefix('my-challenges')->group(function () {
        Route::get('/', [UserChallengeController::class, 'index'])->name('user-challenges.index');
        Route::get('/{userChallenge}', [UserChallengeController::class, 'show'])->name('user-challenges.show');
        Route::patch('/{userChallenge}/pause', [UserChallengeController::class, 'pause'])->name('user-challenges.pause');
        Route::patch('/{userChallenge}/resume', [UserChallengeController::class, 'resume'])->name('user-challenges.resume');
        Route::patch('/{userChallenge}/abandon', [UserChallengeController::class, 'abandon'])->name('user-challenges.abandon');
    });
    
    // Check-ins
    Route::prefix('checkins')->group(function () {
        Route::get('/', [CheckinController::class, 'index'])->name('checkins.index');
        Route::post('/', [CheckinController::class, 'store'])->name('checkins.store');
        Route::get('/{checkin}', [CheckinController::class, 'show'])->name('checkins.show');
        Route::delete('/{checkin}', [CheckinController::class, 'destroy'])->name('checkins.destroy');
    });
    
    // WhatsApp integration
    Route::prefix('whatsapp')->group(function () {
        Route::get('/connect', [WhatsAppController::class, 'connect'])->name('whatsapp.connect');
        Route::post('/connect', [WhatsAppController::class, 'store'])->name('whatsapp.store');
        Route::delete('/disconnect', [WhatsAppController::class, 'disconnect'])->name('whatsapp.disconnect');
        Route::get('/status', [WhatsAppController::class, 'status'])->name('whatsapp.status');
    });
    
    // Profile settings específicas do DOPA Check
    Route::prefix('profile')->group(function () {
        Route::get('/settings', [ProfileController::class, 'settings'])->name('profile.settings');
        Route::patch('/settings', [ProfileController::class, 'updateSettings'])->name('profile.update-settings');
        Route::get('/stats', [ProfileController::class, 'stats'])->name('profile.stats');
    });
    
    // Reports and analytics
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/challenge/{userChallenge}', [ReportController::class, 'challenge'])->name('reports.challenge');
        Route::get('/export', [ReportController::class, 'export'])->name('reports.export');
    });
    
    // ========================================
    // API ROUTES FOR AJAX/MOBILE
    // ========================================
    
    // Quick actions (AJAX endpoints)
    Route::prefix('api')->group(function () {
        Route::post('/quick-checkin', [CheckinController::class, 'quickCheckin'])->name('api.quick-checkin');
        Route::get('/today-tasks', [CheckinController::class, 'todayTasks'])->name('api.today-tasks');
        Route::get('/whatsapp-status', [WhatsAppController::class, 'status'])->name('api.whatsapp-status');
    });
});
