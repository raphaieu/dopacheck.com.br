<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UserChallenge;
use App\Notifications\CheckinReminderNotification;
use App\Services\EvolutionApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDailyReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(EvolutionApiService $evolution): void
    {
        Log::info('Iniciando SendDailyReminderJob (Hybrid)...');

        $activeParticipations = UserChallenge::query()
            ->where('status', 'active')
            ->with(['user', 'challenge.tasks', 'challenge.team', 'team'])
            ->get();

        $remindersSentCount = 0;
        $groupsToNotify = []; // [jid => ['team_name' => str, 'users' => [name1, name2]]]

        foreach ($activeParticipations as $participation) {
            $user = $participation->user;
            
            // Se o dia já está completo, pula
            if ($participation->hasCompletedToday()) {
                continue;
            }

            // Preferências do usuário
            $prefs = $user->preferences['notifications'] ?? [];
            $reminderEnabled = (bool) ($prefs['daily_reminder'] ?? false);
            
            if (!$reminderEnabled) {
                continue;
            }

            // 1. Notificação via Canais Padrão (E-mail/Database) - Sempre envia se o lembrete estiver on
            try {
                $user->notify(new CheckinReminderNotification($participation));
            } catch (\Throwable $e) {
                Log::error('Erro ao enviar notificação de lembrete (Mail/DB)', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            // 2. Lógica Híbrida WhatsApp
            $team = $participation->team ?? $participation->challenge->team;
            $groupJid = $team?->whatsapp_group_jid;

            if ($groupJid) {
                // Adiciona para notificação coletiva no grupo
                if (!isset($groupsToNotify[$groupJid])) {
                    $groupsToNotify[$groupJid] = [
                        'team_name' => $team->name,
                        'users' => []
                    ];
                }
                $groupsToNotify[$groupJid]['users'][] = $user->name;
            } else {
                // Se não tem grupo, tenta DM (se habilitado via pref - hoje protegida no front)
                $whatsappDmEnabled = (bool) ($prefs['whatsapp'] ?? false);
                if ($whatsappDmEnabled) {
                    $this->sendWhatsappReminder($evolution, $participation);
                }
            }

            $remindersSentCount++;
        }

        // 3. Enviar notificações de grupo acumuladas
        $this->notifyGroups($evolution, $groupsToNotify);

        Log::info('SendDailyReminderJob finalizado.', [
            'participations_scanned' => $activeParticipations->count(),
            'reminders_sent' => $remindersSentCount,
            'groups_notified' => count($groupsToNotify)
        ]);
    }

    private function notifyGroups(EvolutionApiService $evolution, array $groupsToNotify): void
    {
        $instance = env('EVOLUTION_INSTANCE');
        if (!$instance || empty($groupsToNotify)) {
            return;
        }

        foreach ($groupsToNotify as $jid => $data) {
            $teamName = $data['team_name'];
            $usersList = array_unique($data['users']);
            
            if (empty($usersList)) continue;

            $message = "⏰ *DOPA Check - Hora do Foco!* 🚀\n\n";
            $message .= "E aí, time *{$teamName}*! Passando para lembrar dos check-ins que ainda não rolaram hoje.\n\n";
            $message .= "Atletas em modo offline:\n";
            
            foreach ($usersList as $name) {
                $message .= "• *{$name}*\n";
            }

            $message .= "\nA consistência é o que separa os amadores dos pros. Bora pra cima! 💪\n\n";
            $message .= "👉 " . url('/dopa');

            try {
                $evolution->sendTextMessage($instance, $jid, $message);
            } catch (\Throwable $e) {
                Log::error('Erro ao enviar lembrete coletivo via WhatsApp', [
                    'group_jid' => $jid,
                    'team' => $teamName,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function sendWhatsappReminder(EvolutionApiService $evolution, UserChallenge $participation): void
    {
        $user = $participation->user;
        $phone = $user->whatsapp_number;
        $instance = env('EVOLUTION_INSTANCE');

        if (!$phone || !$instance) {
            return;
        }

        $challengeTitle = $participation->challenge->title;
        $day = $participation->current_day;
        
        $message = "⏰ *DOPA Check - Lembrete*\n\n";
        $message .= "Olá, *{$user->name}*! Passando para lembrar do seu check-in de hoje.\n\n";
        $message .= "Desafio: *{$challengeTitle}*\n";
        $message .= "Dia: *{$day}*\n\n";
        $message .= "A constância é o que constrói o hábito. Bora pra cima! 🚀\n\n";
        $message .= "Acesse: " . url('/dopa');

        try {
            $evolution->sendTextMessage($instance, $phone, $message);
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar lembrete via WhatsApp', [
                'user_id' => $user->id,
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
        }
    }
}
