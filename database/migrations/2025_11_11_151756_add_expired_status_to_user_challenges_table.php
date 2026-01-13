<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite (usado em testes) não suporta MODIFY COLUMN / ENUM via ALTER TABLE.
        // O schema de testes não depende desse ajuste, então pulamos com segurança.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Alterar o enum para incluir 'expired'
        DB::statement("ALTER TABLE user_challenges MODIFY COLUMN status ENUM('active', 'completed', 'paused', 'abandoned', 'expired') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Reverter para o enum original
        DB::statement("ALTER TABLE user_challenges MODIFY COLUMN status ENUM('active', 'completed', 'paused', 'abandoned') DEFAULT 'active'");
        
        // Converter desafios 'expired' de volta para 'completed' ou 'active'
        DB::table('user_challenges')
            ->where('status', 'expired')
            ->update(['status' => 'completed']);
    }
};
