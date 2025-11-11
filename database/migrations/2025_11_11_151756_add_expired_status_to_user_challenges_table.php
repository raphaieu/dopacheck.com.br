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
        // Alterar o enum para incluir 'expired'
        DB::statement("ALTER TABLE user_challenges MODIFY COLUMN status ENUM('active', 'completed', 'paused', 'abandoned', 'expired') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para o enum original
        DB::statement("ALTER TABLE user_challenges MODIFY COLUMN status ENUM('active', 'completed', 'paused', 'abandoned') DEFAULT 'active'");
        
        // Converter desafios 'expired' de volta para 'completed' ou 'active'
        DB::table('user_challenges')
            ->where('status', 'expired')
            ->update(['status' => 'completed']);
    }
};
