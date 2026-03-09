<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UserChallenge;
use App\Notifications\DailyCheckinSummaryNotification;
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
        $groupsToNotify = []; // [jid => ['team_name' => str, 'completed' => [], 'pending' => []]]
        $pendingByUser = []; // [user_id => ['user' => User, 'participations' => UserChallenge[]]]

        foreach ($activeParticipations as $participation) {
            $user = $participation->user;
            $hasCompletedToday = $participation->hasCompletedToday();
            
            // Preferências do usuário
            $prefs = $user->preferences['notifications'] ?? [];
            $reminderEnabled = (bool) ($prefs['daily_reminder'] ?? false);
            
            // Lógica de Grupo (Sempre coleta para o resumo do time, se houver grupo)
            $team = $participation->team ?? $participation->challenge->team;
            $groupJid = $team?->whatsapp_group_jid;

            if ($groupJid) {
                if (!isset($groupsToNotify[$groupJid])) {
                    $groupsToNotify[$groupJid] = [
                        'team_name' => $team->name,
                        'completed' => [],
                        'pending' => []
                    ];
                }
                
                if ($hasCompletedToday) {
                    $groupsToNotify[$groupJid]['completed'][] = $user->name;
                } else {
                    $groupsToNotify[$groupJid]['pending'][] = $user->name;
                }
            }

            // Se o dia já está completo, pula o lembrete individual (E-mail/DM)
            if ($hasCompletedToday) {
                continue;
            }
            
            if (!$reminderEnabled) {
                continue;
            }

            // Guarda participação pendente para resumo consolidado por usuário
            $userId = $user->id;
            if (! isset($pendingByUser[$userId])) {
                $pendingByUser[$userId] = [
                    'user' => $user,
                    'participations' => [],
                ];
            }
            $pendingByUser[$userId]['participations'][] = $participation;

            // 2. Tenta DM se não há grupo (ou se habilitado especificamente)
            if (!$groupJid) {
                $whatsappDmEnabled = (bool) ($prefs['whatsapp'] ?? false);
                if ($whatsappDmEnabled) {
                    $this->sendWhatsappReminder($evolution, $participation);
                }
            }

            $remindersSentCount++;
        }

        // 2. Enviar uma notificação consolidada por usuário (e-mail/DB)
        foreach ($pendingByUser as $bucket) {
            $user = $bucket['user'];
            $participations = $bucket['participations'];

            try {
                $user->notify(new DailyCheckinSummaryNotification($participations));
            } catch (\Throwable $e) {
                Log::error('Erro ao enviar notificação de resumo diário consolidado (Mail/DB)', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
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
            Log::info('SendDailyReminderJob: notifyGroups ignorado (sem instance ou sem grupos)', [
                'has_instance' => !empty($instance),
                'groups_count' => count($groupsToNotify),
            ]);
            return;
        }

        Log::info('SendDailyReminderJob: enviando resumo diário para grupos', [
            'instance' => $instance,
            'groups_count' => count($groupsToNotify),
            'group_jids' => array_keys($groupsToNotify),
        ]);

        foreach ($groupsToNotify as $jid => $data) {
            $teamName = $data['team_name'];
            $completed = array_unique($data['completed']);
            $pending = array_unique($data['pending']);
            
            $total = count($completed) + count($pending);
            if ($total === 0) {
                Log::debug('SendDailyReminderJob: grupo sem participantes hoje', ['jid' => $jid, 'team' => $teamName]);
                continue;
            }
            
            $rate = round((count($completed) / $total) * 100);

            $message = "📊 *Resumo Diário - Time {$teamName}* 🏆\n\n";
            $message .= "O dia está quase acabando! Veja como estamos:\n";
            $message .= "📈 *Performance do Time:* {$rate}%\n\n";
            
            if (!empty($completed)) {
                $message .= "✅ *Focados de hoje:* (" . count($completed) . ")\n";
                foreach ($completed as $name) {
                    $message .= "• {$name}\n";
                }
                $message .= "\n";
            }

            if (!empty($pending)) {
                $message .= "⏰ *Ainda dá tempo:* (" . count($pending) . ")\n";
                foreach ($pending as $name) {
                    $message .= "• *{$name}*\n";
                }
                $message .= "\nBora fechar esse check-in! 🚀\n";
            } else {
                $message .= "🌟 *SENSACIONAL!* Todo o time completou o desafio hoje! 100% de consistência! 🔥\n";
            }

            $message .= "\n👉 " . url('/dopa');

            Log::info('SendDailyReminderJob: enviando resumo para grupo', [
                'group_jid' => $jid,
                'team_name' => $teamName,
                'message_length' => strlen($message),
                'completed_count' => count($completed),
                'pending_count' => count($pending),
            ]);

            try {
                $evolution->sendTextMessage($instance, $jid, $message);
                Log::info('SendDailyReminderJob: resumo enviado com sucesso para grupo', [
                    'group_jid' => $jid,
                    'team_name' => $teamName,
                ]);
            } catch (\Throwable $e) {
                Log::error('SendDailyReminderJob: erro ao enviar resumo diário via WhatsApp', [
                    'group_jid' => $jid,
                    'team_name' => $teamName,
                    'error' => $e->getMessage(),
                    'exception_class' => $e::class,
                    'trace' => $e->getTraceAsString(),
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
            Log::info('SendDailyReminderJob: lembrete DM enviado', ['user_id' => $user->id, 'phone' => $phone]);
        } catch (\Throwable $e) {
            Log::error('SendDailyReminderJob: erro ao enviar lembrete via WhatsApp (DM)', [
                'user_id' => $user->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
                'exception_class' => $e::class,
            ]);
        }
    }
}
