<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\OauthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\User\LoginLinkController;

use Illuminate\Support\Facades\Artisan;

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

// ========================================
// HEALTH CHECK
// ========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'dopacheck-app',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// ========================================
// PUBLIC ROUTES
// ========================================
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
    Route::get('/{challenge}', [ChallengeController::class, 'show'])
    ->where('challenge', '[0-9]+')
    ->name('challenges.show');
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
    // DOPA CHECK MAIN ROUTES
    // ========================================
    
    // DOPA Check dashboard (principal)
    Route::get('/dopa', [DopaController::class, 'dashboard'])->name('dopa.dashboard');
    
    // ========================================
    // CHALLENGES MANAGEMENT
    // ========================================
    Route::prefix('challenges')->name('challenges.')->group(function () {
        // Authenticated challenge routes
        Route::get('/create', [ChallengeController::class, 'create'])->name('create');
        Route::post('/', [ChallengeController::class, 'store'])->name('store');
        Route::post('/{challenge}/join', [ChallengeController::class, 'join'])->name('join');
        Route::post('/{challenge}/leave', [ChallengeController::class, 'leave'])->name('leave');
    });
    
    // ========================================
    // USER CHALLENGES MANAGEMENT
    // ========================================
    Route::prefix('my-challenges')->name('user-challenges.')->group(function () {
        Route::get('/', [UserChallengeController::class, 'index'])->name('index');
        Route::get('/{userChallenge}', [UserChallengeController::class, 'show'])->name('show');
        Route::patch('/{userChallenge}/pause', [UserChallengeController::class, 'pause'])->name('pause');
        Route::patch('/{userChallenge}/resume', [UserChallengeController::class, 'resume'])->name('resume');
        Route::patch('/{userChallenge}/abandon', [UserChallengeController::class, 'abandon'])->name('abandon');
    });
    
    // ========================================
    // CHECK-INS SYSTEM
    // ========================================
    Route::prefix('checkins')->name('checkins.')->group(function () {
        Route::get('/', [CheckinController::class, 'index'])->name('index');
        Route::post('/', [CheckinController::class, 'store'])->name('store');
        Route::get('/{checkin}', [CheckinController::class, 'show'])->name('show');
        Route::delete('/{checkin}', [CheckinController::class, 'destroy'])->name('destroy');
    });
    
    // ========================================
    // WHATSAPP INTEGRATION
    // ========================================
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/connect', [WhatsAppController::class, 'connect'])->name('connect');
        Route::post('/connect', [WhatsAppController::class, 'store'])->name('store');
        Route::delete('/disconnect', [WhatsAppController::class, 'disconnect'])->name('disconnect');
        Route::get('/status', [WhatsAppController::class, 'status'])->name('status');
    });
    
    // ========================================
    // PROFILE & SETTINGS
    // ========================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::patch('/settings', [ProfileController::class, 'updateSettings'])->name('update-settings');
        Route::get('/stats', [ProfileController::class, 'stats'])->name('stats');
    });
    
    // ========================================
    // REPORTS & ANALYTICS
    // ========================================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/challenge/{userChallenge}', [ReportController::class, 'challenge'])->name('challenge');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
    
    // ========================================
    // API ROUTES FOR AJAX/MOBILE
    // ========================================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Dashboard APIs
        Route::get('/today-tasks', [DopaController::class, 'todayTasks'])->name('today-tasks');
        Route::get('/quick-stats', [DopaController::class, 'quickStats'])->name('quick-stats');
        Route::get('/activity-feed', [DopaController::class, 'activityFeed'])->name('activity-feed');
        
        // Check-in APIs
        Route::middleware('throttle:30,1')->group(function () {
            Route::post('/quick-checkin', [CheckinController::class, 'quickCheckin'])->name('quick-checkin');
        });
        Route::get('/checkin-stats', [CheckinController::class, 'stats'])->name('checkin-stats');
        
        // Challenge APIs
        Route::middleware('cache.headers:public;max_age=1800')->group(function () {
            Route::get('/recommended-challenges', [ChallengeController::class, 'recommended'])->name('recommended-challenges');
        });
        Route::get('/challenge-stats/{challenge}', [ChallengeController::class, 'stats'])->name('challenge-stats');
        
        // User Challenge APIs  
        Route::get('/user-challenge-progress/{userChallenge}', [UserChallengeController::class, 'progress'])->name('user-challenge-progress');
        
        // WhatsApp APIs
        Route::get('/whatsapp-status', [WhatsAppController::class, 'status'])->name('whatsapp-status');
        
    });
    
});

// ========================================
// WEBHOOK ROUTES (sem autenticação)
// ========================================
Route::prefix('webhook')->name('webhook.')->group(function () {
    
    // WhatsApp webhook (EvolutionAPI) - para Sprint 3
    Route::post('/whatsapp', [WhatsAppController::class, 'webhook'])->name('whatsapp');
    
    // Stripe webhook (futuro)
    // Route::post('/stripe', [SubscriptionController::class, 'webhook'])->name('stripe');
    
});

// ========================================
// FALLBACK & REDIRECTS
// ========================================

// Redirect antigos
Route::get('/home', function () {
    return redirect()->route('dopa.dashboard');
});

// Redirect dashboard genérico para DOPA
Route::middleware('auth')->group(function () {
    Route::get('/dash', function () {
        return redirect()->route('dopa.dashboard');
    });
});

// ========================================
// DEVELOPMENT/DEBUG ROUTES (remover em produção)
// ========================================
if (app()->environment('local', 'staging')) {
    Route::prefix('dev')->group(function () {
        Route::get('/reset-demo', function () {
            // Reset para dados de demonstração
            Artisan::call('migrate:fresh --seed');
            return redirect()->route('dopa.dashboard')->with('success', 'Dados de demonstração resetados!');
        });
        
        Route::get('/cache-clear', function () {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            return response()->json(['message' => 'Cache limpo com sucesso!']);
        });
    });
}

// ========================================
// 404 PERSONALIZADA
// ========================================
Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json(['message' => 'Endpoint não encontrado'], 404);
    }
    
    return inertia('Error404', [
        'status' => 404,
        'message' => 'Página não encontrada'
    ]);
});