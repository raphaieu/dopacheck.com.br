<?php

declare(strict_types=1);

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Events\AddingTeam;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\CreatesTeams;

final class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array{name: string}  $input
     */
    public function create(User $user, array $input): Team
    {
        Gate::forUser($user)->authorize('create', Jetstream::newTeamModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createTeam');

        AddingTeam::dispatch($user);

        /** @var Team $team */
        $team = $user->ownedTeams()->create([
            'name' => $input['name'],
            'personal_team' => false,
        ]);

        // Slug é nullable e NÃO fazemos backfill automático para teams existentes.
        // Para novos teams, geramos um slug estável para uso no onboarding público.
        if (($team->slug ?? null) === null) {
            $base = Str::slug($team->name);
            if ($base === '') {
                $base = 'team';
            }

            $slug = Team::query()->where('slug', $base)->exists()
                ? "{$base}-{$team->id}"
                : $base;

            $team->forceFill(['slug' => $slug])->save();
        }

        $user->switchTeam($team);

        return $team;
    }
}
