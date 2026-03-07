<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class MyTeamsController extends Controller
{
    /**
     * Lista todos os times do usuário (dono ou membro) para a tela "Meus times".
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $teams = Team::query()
            ->where('user_id', $user->id)
            ->orWhereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->withCount('users')
            ->get()
            ->map(function ($team) use ($user) {
                $isOwner = (int) $team->user_id === (int) $user->id;
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'personal_team' => (bool) $team->personal_team,
                    'is_owner' => $isOwner,
                    'members_count' => (int) $team->users_count,
                    'join_url' => $team->slug ? url('/join/' . $team->slug) : null,
                ];
            })
            ->values();

        return Inertia::render('Teams/Index', [
            'teams' => $teams,
        ]);
    }

    /**
     * Exibe o formulário de edição do time no layout DOPA (mesmo layout do criar).
     * Apenas o dono do time pode editar.
     */
    public function edit(Request $request, Team $team): Response|HttpResponse
    {
        if ((int) $team->user_id !== (int) $request->user()->id) {
            abort(403, 'Apenas o dono do time pode editar.');
        }
        if ($team->personal_team) {
            return redirect()->route('teams.my-index')
                ->with('error', 'Time pessoal não pode ser editado por aqui.');
        }

        return Inertia::render('Teams/Edit', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug ?? '',
                'whatsapp_join_url' => $team->whatsapp_join_url ?? '',
                'whatsapp_group_jid' => $team->whatsapp_group_jid ?? '',
                'whatsapp_group_name' => $team->whatsapp_group_name ?? '',
                'onboarding_title' => $team->onboarding_title ?? '',
                'onboarding_body' => $team->onboarding_body ?? '',
            ],
        ]);
    }

    /**
     * Atualiza o time (layout DOPA). Apenas o dono pode atualizar.
     */
    public function update(Request $request, Team $team): HttpResponse
    {
        if ((int) $team->user_id !== (int) $request->user()->id) {
            abort(403, 'Apenas o dono do time pode editar.');
        }
        if ($team->personal_team) {
            return redirect()->route('teams.my-index')
                ->with('error', 'Time pessoal não pode ser editado por aqui.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('teams', 'slug')->ignore($team->id),
            ],
            'whatsapp_join_url' => ['nullable', 'string', 'url', 'max:2048'],
            'whatsapp_group_jid' => ['nullable', 'string', 'max:64', 'regex:/^[0-9]+@g\.us$/'],
            'whatsapp_group_name' => ['nullable', 'string', 'max:255'],
            'onboarding_title' => ['nullable', 'string', 'max:255'],
            'onboarding_body' => ['nullable', 'string'],
        ]);

        $team->update([
            'name' => $validated['name'],
            'slug' => Str::lower($validated['slug']),
            'whatsapp_join_url' => $validated['whatsapp_join_url'] ?? null,
            'whatsapp_group_jid' => $validated['whatsapp_group_jid'] ?? null,
            'whatsapp_group_name' => $validated['whatsapp_group_name'] ?? null,
            'onboarding_title' => $validated['onboarding_title'] ?? null,
            'onboarding_body' => $validated['onboarding_body'] ?? null,
        ]);

        return redirect()->route('teams.my-index')
            ->with('success', 'Time atualizado com sucesso!');
    }
}
