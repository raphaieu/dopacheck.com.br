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
        Schema::create('checkins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_challenge_id')->constrained('user_challenges')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('challenge_tasks')->cascadeOnDelete();
            $table->string('image_path')->nullable(); // Caminho da imagem
            $table->string('image_url')->nullable(); // URL da imagem (CDN)
            $table->text('message')->nullable(); // Mensagem do usuário
            $table->enum('source', ['web', 'whatsapp'])->default('web'); // Origem do check-in
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->json('ai_analysis')->nullable(); // Análise da IA (PRO)
            $table->decimal('confidence_score', 3, 2)->nullable(); // Score da IA (0.00-1.00)
            $table->integer('challenge_day')->nullable(); // Dia do desafio
            $table->timestamp('checked_at')->useCurrent(); // Horário do check-in
            $table->timestamps();
            $table->softDeletes(); // Para TTL de imagens (free users)
            
            // Índices para performance
            $table->index(['user_challenge_id', 'checked_at']);
            $table->index(['task_id', 'checked_at']);
            $table->index(['challenge_day', 'user_challenge_id']);
            $table->index(['created_at', 'deleted_at']); // Para TTL cleanup
            $table->index('source');
            $table->index('status');
            
            // Constraint: um check-in por task por dia
            $table->unique(['user_challenge_id', 'task_id', 'challenge_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
