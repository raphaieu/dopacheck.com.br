<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class TeamApplicationsController extends Controller
{
    public function index(Request $request, Team $team): Response
    {
        Gate::authorize('viewAnyForTeam', [TeamApplication::class, $team]);

        $status = $request->query('status');
        if (! is_string($status) || $status === '') {
            $status = 'pending';
        }
        if (! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $applications = TeamApplication::query()
            ->where('team_id', $team->id)
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Teams/Applications/Index', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
            ],
            'status' => $status,
            'applications' => $applications->through(static fn (TeamApplication $app): array => [
                'id' => $app->id,
                'name' => $app->name,
                'birthdate' => $app->birthdate,
                'email' => $app->email,
                'whatsapp_number' => $app->whatsapp_number,
                'city' => $app->city,
                'neighborhood' => $app->neighborhood,
                'circle_url' => $app->circle_url,
                'status' => $app->status,
                'user_id' => $app->user_id,
                'approved_by' => $app->approved_by,
                'approved_at' => $app->approved_at,
                'created_at' => $app->created_at,
            ]),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    public function update(Request $request, Team $team, TeamApplication $application): RedirectResponse
    {
        abort_unless($application->team_id === $team->id, 404);

        $validated = $request->validate([
            'action' => ['required', 'string', Rule::in(['approve', 'reject'])],
        ]);

        $action = $validated['action'];
        if ($action === 'approve') {
            Gate::authorize('approve', $application);

            $application->forceFill([
                'status' => 'approved',
                'approved_by' => $request->user()?->id,
                'approved_at' => now(),
            ])->save();

            return back()->with('success', 'Inscrição aprovada com sucesso.');
        }

        Gate::authorize('reject', $application);

        $application->forceFill([
            'status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
        ])->save();

        return back()->with('success', 'Inscrição rejeitada.');
    }
}

