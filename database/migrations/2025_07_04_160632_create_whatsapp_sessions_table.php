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
        Schema::create('whatsapp_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('phone_number'); // Número do usuário
            $table->string('bot_number')->nullable(); // Número do bot atribuído
            $table->string('session_id')->unique(); // ID da sessão EvolutionAPI
            $table->string('instance_name')->nullable(); // Nome da instância
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->json('metadata')->nullable(); // Dados extras da sessão
            $table->integer('message_count')->default(0); // Total de mensagens
            $table->integer('checkin_count')->default(0); // Total de check-ins
            $table->timestamps();
            
            // Índices para performance
            $table->index(['user_id', 'is_active']);
            $table->index('phone_number');
            $table->index('bot_number');
            $table->index('session_id');
            $table->index('last_activity');
            $table->unique(['user_id', 'phone_number']); // Um usuário por número
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
};
