<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recalcula challenge_day com base na data real do check-in (checked_at)
        // e no início global do desafio (challenges.start_date).
        // Isso evita inconsistências ao habilitar retroativos.

        DB::table('checkins')
            ->select(['id', 'user_challenge_id', 'task_id', 'checked_at', 'challenge_day'])
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                $userChallengeIds = collect($rows)->pluck('user_challenge_id')->unique()->values()->all();

                $userChallenges = DB::table('user_challenges')
                    ->join('challenges', 'challenges.id', '=', 'user_challenges.challenge_id')
                    ->whereIn('user_challenges.id', $userChallengeIds)
                    ->select([
                        'user_challenges.id as user_challenge_id',
                        'challenges.start_date as challenge_start_date',
                        'challenges.duration_days as duration_days',
                    ])
                    ->get()
                    ->keyBy('user_challenge_id');

                foreach ($rows as $row) {
                    $uc = $userChallenges->get($row->user_challenge_id);
                    if (! $uc) {
                        continue;
                    }

                    $start = $uc->challenge_start_date
                        ? Carbon::parse($uc->challenge_start_date)->startOfDay()
                        : null;

                    if (! $start) {
                        continue;
                    }

                    $checkedDate = $row->checked_at
                        ? Carbon::parse($row->checked_at)->startOfDay()
                        : null;

                    if (! $checkedDate) {
                        continue;
                    }

                    $duration = (int) ($uc->duration_days ?? 1);
                    $duration = max(1, min(365, $duration));

                    $day = (int) ($start->diffInDays($checkedDate, false) + 1);
                    $day = max(1, min($day, $duration));

                    // Evita trabalho desnecessário
                    $current = isset($row->challenge_day) ? (int) $row->challenge_day : null;
                    if ($current === $day) {
                        continue;
                    }

                    // Protege contra violações do unique(user_challenge_id, task_id, challenge_day)
                    $conflict = DB::table('checkins')
                        ->where('user_challenge_id', $row->user_challenge_id)
                        ->where('task_id', $row->task_id)
                        ->where('challenge_day', $day)
                        ->where('id', '!=', $row->id)
                        ->exists();

                    if ($conflict) {
                        continue;
                    }

                    DB::table('checkins')
                        ->where('id', $row->id)
                        ->update(['challenge_day' => $day]);
                }
            });
    }

    public function down(): void
    {
        // Sem rollback seguro sem perder informação histórica.
    }
};

