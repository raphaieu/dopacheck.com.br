<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_challenges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'paused', 'abandoned'])->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->integer('current_day')->default(1); // Dia atual do desafio
            $table->integer('total_checkins')->default(0); // Cache de check-ins
            $table->integer('streak_days')->default(0); // Sequência atual
            $table->integer('best_streak')->default(0); // Melhor sequência
            $table->decimal('completion_rate', 5, 2)->default(0.00); // % de conclusão
            $table->json('stats')->nullable(); // Estatísticas personalizadas
            $table->text('notes')->nullable(); // Notas pessoais
            $table->timestamps();
            
            // Índices para performance
            $table->index(['user_id', 'status']);
            $table->index(['challenge_id', 'status']);
            $table->index('started_at');
            $table->index('completion_rate');
            $table->unique(['user_id', 'challenge_id']); // Um usuário só pode ter um desafio ativo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenges');
    }
};
