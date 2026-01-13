<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamApplication;
use Illuminate\Auth\Access\HandlesAuthorization;

final class TeamApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Usado por recursos “globais” (ex: Filament) que não têm contexto de Team.
     * Libera apenas superadmin ou quem for owner/admin de ao menos um team não-pessoal.
     */
    public function viewAny(User $user): bool
    {
        $email = is_string($user->email) ? mb_strtolower(trim($user->email)) : '';
        if ($email === 'rapha@raphael-martins.com') {
            return true;
        }

        if ($user->ownedTeams()->where('personal_team', false)->exists()) {
            return true;
        }

        return $user->teams()
            ->where('personal_team', false)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    /**
     * Usado pelo painel interno (/teams/{team}/applications), com contexto de Team.
     */
    public function viewAnyForTeam(User $user, Team $team): bool
    {
        return $this->isTeamOwnerOrAdmin($user, $team);
    }

    public function approve(User $user, TeamApplication $application): bool
    {
        return $this->isTeamOwnerOrAdmin($user, $application->team);
    }

    public function reject(User $user, TeamApplication $application): bool
    {
        return $this->isTeamOwnerOrAdmin($user, $application->team);
    }

    private function isTeamOwnerOrAdmin(User $user, Team $team): bool
    {
        if ($user->ownsTeam($team)) {
            return true;
        }

        return $user->hasTeamRole($team, 'admin');
    }
}

