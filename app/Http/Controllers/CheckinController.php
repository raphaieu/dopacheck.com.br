<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\ChallengeTask;
use App\Models\UserChallenge;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class CheckinController extends Controller
{
    /**
     * Display user's checkins
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $query = $user->checkins()
            ->with(['task', 'userChallenge.challenge'])
            ->latest('checked_at');
        
        // Filter by challenge
        if ($request->challenge_id) {
            $query->whereHas('userChallenge', function ($q) use ($request) {
                $q->where('challenge_id', $request->challenge_id);
            });
        }
        
        // Filter by task
        if ($request->task_id) {
            $query->where('task_id', $request->task_id);
        }
        
        // Filter by date range
        if ($request->start_date && $request->end_date) {
            $query->inDateRange($request->start_date, $request->end_date);
        }
        
        $checkins = $query->paginate(20);
        
        // Get user's challenges for filter
        $userChallenges = $user->userChallenges()
            ->with('challenge')
            ->get();
        
        return Inertia::render('Checkins/Index', [
            'checkins' => $checkins,
            'userChallenges' => $userChallenges,
            'filters' => [
                'challenge_id' => $request->challenge_id,
                'task_id' => $request->task_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }
    
    /**
     * Store a new checkin
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'task_id' => ['required', 'exists:challenge_tasks,id'],
            'message' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
        ]);
        
        $task = ChallengeTask::findOrFail($validated['task_id']);
        
        // Find user's active challenge for this task
        $userChallenge = $user->userChallenges()
            ->active()
            ->whereHas('challenge.tasks', function ($q) use ($task) {
                $q->where('id', $task->id);
            })
            ->first();
        
        if (!$userChallenge) {
            return $this->errorResponse('Você não está participando de um desafio que contenha esta task.');
        }
        
        // Check if user already checked in today for this task
        $existingCheckin = $userChallenge->checkins()
            ->where('task_id', $task->id)
            ->where('challenge_day', $userChallenge->current_day)
            ->first();
        
        if ($existingCheckin) {
            return $this->errorResponse('Você já fez check-in nesta task hoje.');
        }
        
        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('checkins', 'public');
        }
        
        // Create checkin
        $checkin = $userChallenge->addCheckin($task, [
            'image_path' => $imagePath,
            'message' => $validated['message'],
            'source' => 'web',
            'status' => 'approved', // Auto-approve web checkins
        ]);
        
        // Update user challenge stats
        $userChallenge->updateStats();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in realizado com sucesso! 🎉',
                'checkin' => $checkin->load('task'),
                'userChallenge' => $userChallenge->fresh(),
            ]);
        }
        
        return redirect()->back()->with('success', 'Check-in realizado com sucesso! 🎉');
    }
    
    /**
     * Show checkin details
     */
    public function show(Request $request, Checkin $checkin): Response
    {
        // Ensure user owns this checkin
        if ($checkin->userChallenge->user_id !== $request->user()->id) {
            abort(403);
        }
        
        $checkin->load(['task', 'userChallenge.challenge']);
        
        return Inertia::render('Checkins/Show', [
            'checkin' => $checkin,
        ]);
    }
    
    /**
     * Delete a checkin
     */
    public function destroy(Request $request, Checkin $checkin): RedirectResponse
    {
        // Ensure user owns this checkin
        if ($checkin->userChallenge->user_id !== $request->user()->id) {
            abort(403);
        }
        
        // Delete image if exists
        if ($checkin->image_path && Storage::disk('public')->exists($checkin->image_path)) {
            Storage::disk('public')->delete($checkin->image_path);
        }
        
        $checkin->delete();
        
        // Update user challenge stats
        $checkin->userChallenge->updateStats();
        
        return redirect()->back()->with('success', 'Check-in removido com sucesso.');
    }
    
    /**
     * Quick checkin (for mobile/AJAX)
     */
    public function quickCheckin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hashtag' => ['required', 'string', 'exists:challenge_tasks,hashtag'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);
        
        $user = $request->user();
        
        // Find task by hashtag
        $task = ChallengeTask::where('hashtag', $validated['hashtag'])->first();
        
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task não encontrada.',
            ], 404);
        }
        
        // Find user's active challenge for this task
        $userChallenge = $user->userChallenges()
            ->active()
            ->whereHas('challenge.tasks', function ($q) use ($task) {
                $q->where('id', $task->id);
            })
            ->first();
        
        if (!$userChallenge) {
            return response()->json([
                'success' => false,
                'message' => 'Você não está participando de um desafio que contenha esta task.',
            ], 400);
        }
        
        // Check if already checked in today
        $existingCheckin = $userChallenge->checkins()
            ->where('task_id', $task->id)
            ->where('challenge_day', $userChallenge->current_day)
            ->first();
        
        if ($existingCheckin) {
            return response()->json([
                'success' => false,
                'message' => 'Você já fez check-in nesta task hoje.',
            ], 400);
        }
        
        // Create checkin
        $checkin = $userChallenge->addCheckin($task, [
            'message' => $validated['message'],
            'source' => 'web',
            'status' => 'approved',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Check-in realizado com sucesso! 🎉',
            'checkin' => $checkin->load('task'),
            'userChallenge' => $userChallenge->fresh(),
        ]);
    }
    
    /**
     * Get today's tasks for current user
     */
    public function todayTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentChallenge = $user->currentChallenge();
        
        if (!$currentChallenge) {
            return response()->json([
                'tasks' => [],
                'message' => 'Você não tem nenhum desafio ativo.',
            ]);
        }
        
        // Get today's checkins
        $todayCheckins = $currentChallenge->todayCheckins()
            ->pluck('task_id')
            ->toArray();
        
        // Get all tasks for the challenge
        $tasks = $currentChallenge->challenge->tasks()
            ->orderBy('order')
            ->get()
            ->map(function ($task) use ($todayCheckins) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'hashtag' => $task->hashtag,
                    'hashtag_with_prefix' => $task->hashtag_with_prefix,
                    'description' => $task->description,
                    'icon' => $task->icon,
                    'color' => $task->color,
                    'is_required' => $task->is_required,
                    'completed' => in_array($task->id, $todayCheckins),
                ];
            });
        
        return response()->json([
            'tasks' => $tasks,
            'challenge' => [
                'id' => $currentChallenge->id,
                'title' => $currentChallenge->challenge->title,
                'current_day' => $currentChallenge->current_day,
                'days_remaining' => $currentChallenge->days_remaining,
            ],
        ]);
    }
    
    /**
     * Helper method for error responses
     */
    private function errorResponse(string $message, int $status = 400): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }
        
        return redirect()->back()->with('error', $message);
    }
}
