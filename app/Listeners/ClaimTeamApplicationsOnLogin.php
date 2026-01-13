<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\TeamApplication;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ClaimTeamApplicationsOnLogin
{
    public function handle(Login $event): void
    {
        // Segurança: se o banco ainda não foi migrado (ex: sqlite local), não quebra o login.
        if (! Schema::hasTable('team_applications')) {
            return;
        }

        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        $email = is_string($user->email) ? mb_strtolower(trim($user->email)) : '';
        $whatsapp = is_string($user->whatsapp_number) ? (preg_replace('/\D+/', '', $user->whatsapp_number) ?? '') : '';

        if ($email === '' && $whatsapp === '') {
            return;
        }

        DB::transaction(function () use ($user, $email, $whatsapp): void {
            $applications = TeamApplication::query()
                ->with('team')
                ->where('status', 'approved')
                ->whereNull('user_id')
                ->where(function ($q) use ($email, $whatsapp): void {
                    if ($email !== '') {
                        $q->orWhere('email', $email);
                    }
                    if ($whatsapp !== '') {
                        $q->orWhere('whatsapp_number', $whatsapp);
                    }
                })
                ->lockForUpdate()
                ->get();

            foreach ($applications as $application) {
                if (! $application->relationLoaded('team') || ! $application->team) {
                    continue;
                }

                $application->forceFill(['user_id' => $user->id])->save();

                // "member" => viewer (decisão do projeto). Não altera caso já esteja no time.
                $application->team->users()->syncWithoutDetaching([
                    $user->id => ['role' => 'viewer'],
                ]);
            }
        });
    }
}

