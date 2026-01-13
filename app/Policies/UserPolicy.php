<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

final class UserPolicy
{
    private const SUPERADMIN_EMAIL = 'rapha@raphael-martins.com';

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }

        // Allow if it's the same user and token has read ability
        if ($user->id === $model->id && $user->tokenCan('read')) {
            return true;
        }

        // Allow if user has read permission and belongs to same team
        $team = $this->teamById($model->current_team_id);
        if (! $team) {
            return false;
        }

        return $user->belongsToTeam($team)
            && $user->hasTeamPermission($team, 'read')
            && $user->tokenCan('read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }

        $team = $this->teamById($user->current_team_id);
        if (! $team) {
            return false;
        }

        return ($user->hasTeamRole($team, 'admin')
            || $user->hasTeamPermission($team, 'create'))
            && $user->tokenCan('create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }

        // Allow if it's the same user and token has update ability
        if ($user->id === $model->id && $user->tokenCan('update')) {
            return true;
        }

        // Allow if user has write permission and belongs to same team
        $team = $this->teamById($model->current_team_id);
        if (! $team) {
            return false;
        }

        return $user->belongsToTeam($team)
            && $user->hasTeamPermission($team, 'update')
            && $user->tokenCan('update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Prevent self-deletion
        if ($user->id === $model->id) {
            return false;
        }

        if ($this->isSuperadmin($user)) {
            return true;
        }

        $modelTeam = $this->teamById($model->current_team_id);
        $userTeam = $this->teamById($user->current_team_id);
        if (! $modelTeam || ! $userTeam) {
            return false;
        }

        // Only admin can delete users
        return $user->belongsToTeam($modelTeam)
            && $user->hasTeamRole($userTeam, 'admin')
            && $user->tokenCan('delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }

        $modelTeam = $this->teamById($model->current_team_id);
        $userTeam = $this->teamById($user->current_team_id);
        if (! $modelTeam || ! $userTeam) {
            return false;
        }

        return $user->belongsToTeam($modelTeam)
            && $user->hasTeamRole($userTeam, 'admin')
            && $user->tokenCan('delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): bool
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }

        $team = $this->teamById($user->current_team_id);
        if (! $team) {
            return false;
        }

        return $user->hasTeamRole($team, 'admin')
            && $user->tokenCan('delete');
    }

    private function isSuperadmin(User $user): bool
    {
        $email = is_string($user->email) ? mb_strtolower(trim($user->email)) : '';

        return $email !== '' && $email === self::SUPERADMIN_EMAIL;
    }

    private function teamById(?int $teamId): ?Team
    {
        if (! is_int($teamId) || $teamId <= 0) {
            return null;
        }

        return Team::query()->find($teamId);
    }
}
