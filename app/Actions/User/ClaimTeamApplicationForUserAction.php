<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamApplication;
use Illuminate\Support\Str;

final readonly class ClaimTeamApplicationForUserAction
{
    public function handle(User $user): void
    {
        $email = is_string($user->email) ? mb_strtolower(trim($user->email)) : '';
        if ($email === '') {
            return;
        }

        /** @var TeamApplication|null $application */
        $application = TeamApplication::query()
            ->where('email', $email)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->latest('id')
            ->first();

        if (! $application) {
            // Mesmo sem application, garantimos username se ainda não existe.
            $this->ensureUsername($user);
            return;
        }

        $team = Team::query()->find($application->team_id);
        if ($team) {
            // Garantir vínculo (team_user) — idempotente.
            $team->users()->syncWithoutDetaching([
                $user->id => ['role' => 'member'],
            ]);

            // "team_id" do usuário na prática é o current_team_id (Jetstream).
            // Se o usuário ainda está sem time atual (ou está no time pessoal), trocamos para o time do application.
            if ($user->current_team_id === null || $this->isOnPersonalTeam($user)) {
                $user->switchTeam($team);
            }
        }

        // Preencher telefone se estiver vazio
        $whatsapp = is_string($application->whatsapp_number) ? preg_replace('/\D+/', '', $application->whatsapp_number) : null;
        if (is_string($whatsapp) && $whatsapp !== '') {
            if (! is_string($user->whatsapp_number) || trim($user->whatsapp_number) === '') {
                $user->whatsapp_number = $whatsapp;
            }
            if (! is_string($user->phone) || trim($user->phone) === '') {
                $user->phone = $whatsapp;
            }
        }

        $this->ensureUsername($user, fallbackName: $application->name);

        if ($application->user_id === null) {
            $application->forceFill(['user_id' => $user->id])->save();
        }

        if ($user->isDirty()) {
            $user->save();
        }
    }

    private function ensureUsername(User $user, ?string $fallbackName = null): void
    {
        $attributes = $user->getAttributes();
        $currentUsername = $attributes['username'] ?? null;

        if (is_string($currentUsername) && trim($currentUsername) !== '') {
            return;
        }

        $name = is_string($user->name) && trim($user->name) !== '' ? $user->name : ($fallbackName ?? '');
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base;
        $i = 2;
        while (User::query()
            ->where('username', $candidate)
            ->whereKeyNot($user->id)
            ->exists()
        ) {
            $candidate = "{$base}-{$i}";
            $i++;
        }

        $user->username = $candidate;
    }

    private function isOnPersonalTeam(User $user): bool
    {
        if (! is_int($user->current_team_id) || $user->current_team_id <= 0) {
            return false;
        }

        return $user->ownedTeams()
            ->whereKey($user->current_team_id)
            ->where('personal_team', true)
            ->exists();
    }
}

