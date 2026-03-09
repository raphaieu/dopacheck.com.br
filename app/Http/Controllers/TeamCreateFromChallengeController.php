<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use App\Services\WhatsappSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class TeamCreateFromChallengeController extends Controller
{
    /**
     * Exibe o formulário de criação de time (fluxo "Novo time" a partir de Criar desafio).
     * Só permite acesso se o usuário tiver WhatsApp cadastrado e vinculado (Redis).
     */
    public function show(Request $request, WhatsappSessionService $whatsappSession): Response|RedirectResponse
    {
        $user = $request->user();
        $phone = $user->whatsapp_number ?? '';
        $phone = is_string($phone) ? preg_replace('/\D/', '', $phone) : '';
        if (strlen($phone) === 11 && ! str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }

        $hasSession = (bool) ($user->whatsapp_confirmed ?? false);
        if (! $hasSession) {
            $hasSession = $phone !== '' && $whatsappSession->get($user->whatsapp_number ?? '') !== null;
        }

        if (!$hasSession) {
            return Inertia::render('Teams/CreateFromChallenge', [
                'whatsapp_required' => true,
                'whatsapp_connect_url' => route('whatsapp.connect'),
            ]);
        }

        return Inertia::render('Teams/CreateFromChallenge', [
            'whatsapp_required' => false,
            'whatsapp_connect_url' => route('whatsapp.connect'),
        ]);
    }

    /**
     * Cria o time (self-service) e redireciona para Criar desafio com o time selecionado.
     */
    public function store(Request $request, WhatsappSessionService $whatsappSession): RedirectResponse
    {
        $user = $request->user();
        $phone = $user->whatsapp_number ?? '';
        $phone = is_string($phone) ? preg_replace('/\D/', '', $phone) : '';
        if (strlen($phone) === 11 && ! str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }

        $hasSession = (bool) ($user->whatsapp_confirmed ?? false);
        if (! $hasSession) {
            $hasSession = $phone !== '' && $whatsappSession->get($user->whatsapp_number ?? '') !== null;
        }

        if (!$hasSession) {
            return redirect()->route('teams.create-from-challenge')
                ->with('error', 'Para criar um time para grupo, você precisa ter seu WhatsApp vinculado. Envie uma mensagem no privado para o bot para vincular.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('teams', 'slug'),
            ],
            'whatsapp_join_url' => ['nullable', 'string', 'url', 'max:2048'],
            'whatsapp_group_jid' => ['nullable', 'string', 'max:64', 'regex:/^[0-9]+@g\.us$/'],
            'whatsapp_group_name' => ['nullable', 'string', 'max:255'],
            'onboarding_title' => ['nullable', 'string', 'max:255'],
            'onboarding_body' => ['nullable', 'string'],
        ]);

        $team = $user->ownedTeams()->create([
            'name' => $validated['name'],
            'slug' => Str::lower($validated['slug']),
            'whatsapp_join_url' => $validated['whatsapp_join_url'] ?? null,
            'whatsapp_group_jid' => $validated['whatsapp_group_jid'] ?? null,
            'whatsapp_group_name' => $validated['whatsapp_group_name'] ?? null,
            'onboarding_title' => $validated['onboarding_title'] ?? null,
            'onboarding_body' => $validated['onboarding_body'] ?? null,
            'personal_team' => false,
            'landing_template' => Team::LANDING_TEMPLATE_DEFAULT,
            'onboarding_behavior' => Team::ONBOARDING_BEHAVIOR_CREATE_USER,
            'form_schema' => [
                ['key' => 'name', 'type' => 'text', 'label' => 'Nome', 'required' => true],
                ['key' => 'email', 'type' => 'email', 'label' => 'E-mail', 'required' => true],
                ['key' => 'whatsapp_number', 'type' => 'tel', 'label' => 'WhatsApp', 'required' => true],
            ],
        ]);

        $user->switchTeam($team);

        if ($request->query('return_to') === 'meus-times') {
            return redirect()->route('teams.my-index')
                ->with('success', 'Time criado com sucesso!');
        }

        return redirect()->route('challenges.create', ['team_id' => $team->id])
            ->with('success', 'Time criado com sucesso! Agora selecione-o no escopo do desafio.');
    }
}
