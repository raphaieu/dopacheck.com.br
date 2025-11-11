<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\UserChallenge;
use Illuminate\Console\Command;

class CheckExpiredChallenges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dopa:check-expired-challenges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e finaliza desafios expirados (completo ou expirado)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Verificando desafios que devem ser finalizados...');

        // Buscar todos os desafios ativos
        $activeChallenges = UserChallenge::where('status', 'active')
            ->with('challenge')
            ->get();

        $completed = 0;
        $expired = 0;
        $checked = 0;

        foreach ($activeChallenges as $userChallenge) {
            $checked++;
            
            // Atualizar o dia atual e verificar se deve ser finalizado
            // O método updateCurrentDay() chama complete() que verifica se completou tudo
            // Se completou tudo, marca como 'completed', senão marca como 'expired'
            $oldStatus = $userChallenge->status;
            $userChallenge->updateCurrentDay();
            $userChallenge->refresh();
            
            // Verificar se foi finalizado e qual status recebeu
            if ($oldStatus === 'active' && in_array($userChallenge->status, ['completed', 'expired'])) {
                if ($userChallenge->status === 'completed') {
                    $completed++;
                    $this->line("✓ Desafio #{$userChallenge->id} ({$userChallenge->challenge->title}) marcado como COMPLETO (todos check-ins feitos)");
                } else {
                    $expired++;
                    $this->line("⚠ Desafio #{$userChallenge->id} ({$userChallenge->challenge->title}) marcado como EXPIRADO (tempo acabou mas não completou tudo)");
                }
            }
        }

        $this->info("Verificação concluída: {$checked} desafios verificados, {$completed} completos, {$expired} expirados.");

        return Command::SUCCESS;
    }
}

