<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\UserChallenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyCheckinSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  \Illuminate\Support\Collection<int,UserChallenge>|array<int,UserChallenge>  $pendingChallenges
     */
    public function __construct(public array $pendingChallenges)
    {
    }

    public function via(object $notifiable): array
    {
        $preferences = $notifiable->preferences['notifications'] ?? [];
        $channels = ['database'];

        if ($preferences['email'] ?? false) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->pendingChallenges);

        $mail = (new MailMessage)
            ->subject('⏰ Resumo diário dos seus desafios (DOPA Check)')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Segue o resumo dos seus desafios que ainda têm check-in pendente hoje ({$count} desafio" . ($count > 1 ? 's' : '') . "):");

        foreach ($this->pendingChallenges as $userChallenge) {
            $challengeTitle = $userChallenge->challenge->title;
            $day = $userChallenge->current_day;
            $progressToday = $userChallenge->today_progress_percentage ?? 0;

            $mail->line("- **{$challengeTitle}** (Dia {$day}) — Progresso de hoje: {$progressToday}%");
        }

        $mail->line('A consistência diária é o que constrói o resultado no longo prazo.')
            ->action('Fazer check-ins agora', url('/dopa'))
            ->line('Bora fechar o dia com orgulho de você mesmo(a)! 🚀');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'daily_checkin_summary',
            'pending_challenges' => array_map(function (UserChallenge $userChallenge) {
                return [
                    'user_challenge_id' => $userChallenge->id,
                    'challenge_title' => $userChallenge->challenge->title,
                    'day' => $userChallenge->current_day,
                    'today_progress' => $userChallenge->today_progress_percentage ?? 0,
                ];
            }, $this->pendingChallenges),
        ];
    }
}

