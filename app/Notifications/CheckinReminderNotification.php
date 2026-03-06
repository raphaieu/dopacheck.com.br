<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\UserChallenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CheckinReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public UserChallenge $userChallenge)
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
        $challengeTitle = $this->userChallenge->challenge->title;
        $day = $this->userChallenge->current_day;

        return (new MailMessage)
            ->subject('⏰ Não esqueça seu check-in de hoje!')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Estamos passando para lembrar que você ainda não completou seu check-in de hoje no desafio: **{$challengeTitle}** (Dia {$day}).")
            ->line('A consistência é a chave para o progresso!')
            ->action('Fazer Check-in Agora', url('/dopa'))
            ->line('Continue firme e bora pra cima! 🚀');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'checkin_reminder',
            'user_challenge_id' => $this->userChallenge->id,
            'challenge_title' => $this->userChallenge->challenge->title,
            'day' => $this->userChallenge->current_day,
            'message' => 'Você ainda não completou seu check-in de hoje!',
        ];
    }
}
