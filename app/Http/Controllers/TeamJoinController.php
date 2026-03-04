<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\CacheHelper;
use App\Models\Challenge;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\User;
use App\Models\UserChallenge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

final class TeamJoinController extends Controller
{
    public function show(Team $team): Response
    {
        $team->loadMissing([]);

        if ($team->usesDopaminaLanding()) {
            return Inertia::render('Join/TeamJoin', [
                'team' => $this->teamPayload($team),
            ]);
        }

        $payload = $this->teamPayload($team, forLanding: true);
        $customPage = $this->resolveCustomLandingPage($team);
        if ($customPage !== null) {
            return Inertia::render($customPage, ['team' => $payload]);
        }

        return Inertia::render('Join/LandingJoin', [
            'team' => $payload,
        ]);
    }

    public function store(Request $request, Team $team): RedirectResponse
    {
        if ($team->usesDopaminaLanding()) {
            return $this->storeDopamina($request, $team);
        }

        return $this->storeCustom($request, $team);
    }

    private function resolveCustomLandingPage(Team $team): ?string
    {
        $custom = $team->custom_landing;
        if (! is_string($custom) || trim($custom) === '') {
            return null;
        }
        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $custom);
        if ($safe === '') {
            return null;
        }

        return 'Join/Custom/' . $safe;
    }

    private function teamPayload(Team $team, bool $forLanding = false): array
    {
        $payload = [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'whatsapp_join_url' => $team->whatsapp_join_url,
            'onboarding_title' => $team->onboarding_title,
            'onboarding_body' => $team->onboarding_body,
        ];

        if ($forLanding) {
            $payload['landing_template'] = $team->landing_template ?? Team::LANDING_TEMPLATE_DEFAULT;
            $payload['form_schema'] = $team->form_schema ?? [];
            $payload['onboarding_behavior'] = $team->onboarding_behavior ?? Team::ONBOARDING_BEHAVIOR_APPLICATION_ONLY;
            $payload['theme'] = $team->theme ?? null;
            $payload['custom_landing'] = $team->custom_landing ?? null;
        }

        return $payload;
    }

    private function storeDopamina(Request $request, Team $team): RedirectResponse
    {
        $data = $request->all();

        if (isset($data['email']) && is_string($data['email'])) {
            $data['email'] = mb_strtolower(trim($data['email']));
        }
        if (isset($data['whatsapp_number']) && is_string($data['whatsapp_number'])) {
            $data['whatsapp_number'] = preg_replace('/\D+/', '', $data['whatsapp_number']) ?? '';
        }
        if (isset($data['circle_url']) && is_string($data['circle_url'])) {
            $data['circle_url'] = trim($data['circle_url']);
        }

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('team_applications', 'email')->where(fn ($q) => $q->where('team_id', $team->id)),
            ],
            'whatsapp_number' => [
                'required',
                'string',
                'min:10',
                'max:20',
                Rule::unique('team_applications', 'whatsapp_number')->where(fn ($q) => $q->where('team_id', $team->id)),
            ],
            'city' => ['required', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'circle_url' => ['required', 'url', 'max:2048'],
        ], [
            'circle_url.url' => 'Informe um link válido (com http/https).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $application = TeamApplication::create([
            'team_id' => $team->id,
            'name' => $data['name'],
            'birthdate' => $data['birthdate'],
            'email' => $data['email'],
            'whatsapp_number' => $data['whatsapp_number'],
            'city' => $data['city'],
            'neighborhood' => $data['neighborhood'],
            'circle_url' => $data['circle_url'],
            'status' => 'pending',
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ],
        ]);

        return back()->with('success', 'Inscrição enviada! Agora é só aguardar a aprovação do time.');
    }

    private function storeCustom(Request $request, Team $team): RedirectResponse
    {
        $schema = $team->form_schema ?? [];
        if (empty($schema)) {
            return back()->with('error', 'Este time ainda não tem formulário configurado.');
        }

        $data = $request->all();
        $rules = $this->buildRulesFromSchema($schema, $team);
        $isCreateUser = ($team->onboarding_behavior ?? Team::ONBOARDING_BEHAVIOR_APPLICATION_ONLY) === Team::ONBOARDING_BEHAVIOR_CREATE_USER;
        if ($isCreateUser) {
            $rules['terms_accepted'] = ['required', 'accepted'];
        }
        $normalized = $this->normalizeFormDataFromSchema($data, $schema);
        if ($isCreateUser && isset($data['terms_accepted'])) {
            $normalized['terms_accepted'] = $data['terms_accepted'];
        }

        $validator = Validator::make($normalized, $rules, [
            'terms_accepted.accepted' => 'É necessário aceitar os termos e condições.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $main = [
            'team_id' => $team->id,
            'status' => 'pending',
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ],
        ];

        foreach (['name', 'birthdate', 'email', 'whatsapp_number', 'city', 'neighborhood', 'circle_url'] as $col) {
            $main[$col] = $normalized[$col] ?? null;
        }

        $application = TeamApplication::create(array_merge($main, [
            'form_data' => $normalized,
        ]));

        $userCreated = null;
        if ($isCreateUser) {
            $userCreated = $this->createUserFromApplication($application, $team);
            if ($userCreated !== null) {
                $this->enrollUserInTeamChallenges($userCreated, $team);
            }
        }

        if ($userCreated !== null) {
            Auth::login($userCreated);

            return redirect()->route('dopa.dashboard')->with('success', 'Inscrição concluída! Você já está logado e inscrito no desafio.');
        }

        return back()->with('success', 'Inscrição enviada com sucesso.');
    }

    /**
     * @param array<int, array{key: string, type?: string, label?: string, required?: bool}> $schema
     * @return array<string, mixed>
     */
    private function normalizeFormDataFromSchema(array $data, array $schema): array
    {
        $out = [];
        $keys = array_column($schema, 'key');
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($key === 'email' && is_string($value)) {
                $value = mb_strtolower($value);
            }
            if ($key === 'whatsapp_number' && is_string($value)) {
                $value = preg_replace('/\D+/', '', $value);
            }
            $out[$key] = $value;
        }
        return $out;
    }

    /**
     * @param array<int, array{key: string, type?: string, required?: bool}> $schema
     * @return array<string, array<int, mixed>>
     */
    private function buildRulesFromSchema(array $schema, Team $team): array
    {
        $rules = [];
        foreach ($schema as $field) {
            $key = $field['key'] ?? null;
            if (! is_string($key) || $key === '') {
                continue;
            }
            $type = $field['type'] ?? 'text';
            $required = $field['required'] ?? false;
            $base = $required ? ['required'] : ['nullable'];

            if ($type === 'email') {
                $base[] = 'email:rfc';
                $base[] = 'max:255';
                $base[] = Rule::unique('team_applications', 'email')->where(fn ($q) => $q->where('team_id', $team->id));
            } elseif ($type === 'url') {
                $base[] = 'url';
                $base[] = 'max:2048';
            } elseif ($type === 'date') {
                $base[] = 'date';
            } elseif ($type === 'tel') {
                $base[] = 'min:10';
                $base[] = 'max:20';
                $base[] = Rule::unique('team_applications', 'whatsapp_number')->where(fn ($q) => $q->where('team_id', $team->id));
            } else {
                $base[] = 'max:2048';
            }
            $base[] = 'string';
            $rules[$key] = array_values(array_unique($base));
        }
        return $rules;
    }

    private function createUserFromApplication(TeamApplication $application, Team $team): ?User
    {
        $data = $application->form_data ?? [];
        $email = $data['email'] ?? $application->email;
        $name = $data['name'] ?? $application->name ?? 'Membro';

        if (! is_string($email) || $email === '') {
            return null;
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            if (! $user->teams()->where('teams.id', $team->id)->exists()) {
                $user->teams()->attach($team->id, ['role' => 'member']);
            }
            $application->update(['user_id' => $user->id]);

            return $user;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(str()->random(32)),
            'whatsapp_number' => $data['whatsapp_number'] ?? $application->whatsapp_number,
        ]);

        $user->teams()->attach($team->id, ['role' => 'member']);
        $application->update(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Inscreve o usuário em todos os desafios abertos do time (landing create_user).
     */
    private function enrollUserInTeamChallenges(User $user, Team $team): void
    {
        $today = Carbon::now()->startOfDay();

        $challenges = Challenge::query()
            ->where('team_id', $team->id)
            ->where(function ($q) use ($today): void {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->get();

        foreach ($challenges as $challenge) {
            $existing = $user->userChallenges()
                ->where('challenge_id', $challenge->id)
                ->first();

            if ($existing !== null) {
                if ($existing->status !== 'active') {
                    $existing->update([
                        'status' => 'active',
                        'started_at' => now(),
                        'current_day' => 1,
                        'total_checkins' => 0,
                        'streak_days' => 0,
                        'completion_rate' => 0.00,
                    ]);
                    $existing->checkins()->forceDelete();
                }
            } else {
                UserChallenge::create([
                    'user_id' => $user->id,
                    'challenge_id' => $challenge->id,
                    'team_id' => $challenge->team_id,
                    'status' => 'active',
                    'started_at' => now(),
                ]);
            }

            $challenge->updateParticipantCount();
            CacheHelper::invalidateUserCache($user->id);
            CacheHelper::invalidateChallengeCache($challenge->id);
        }
    }
}
