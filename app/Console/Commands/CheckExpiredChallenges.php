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
    protected $description = 'Verifica e marca desafios expirados como completos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Verificando desafios expirados...');

        // Buscar todos os desafios ativos
        $activeChallenges = UserChallenge::where('status', 'active')
            ->with('challenge')
            ->get();

        $completed = 0;
        $checked = 0;

        foreach ($activeChallenges as $userChallenge) {
            $checked++;
            
            // Atualizar o dia atual e verificar se deve ser completado
            $oldStatus = $userChallenge->status;
            $userChallenge->updateCurrentDay();
            
            // Se foi marcado como completo, incrementar contador
            if ($oldStatus === 'active' && $userChallenge->status === 'completed') {
                $completed++;
                $this->line("✓ Desafio #{$userChallenge->id} ({$userChallenge->challenge->title}) marcado como completo");
            }
        }

        $this->info("Verificação concluída: {$checked} desafios verificados, {$completed} marcados como completos.");

        return Command::SUCCESS;
    }
}

