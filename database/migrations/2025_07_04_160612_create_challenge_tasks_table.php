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
        Schema::create('challenge_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->string('name'); // "Ler 30 minutos"
            $table->string('hashtag')->unique(); // "leitura" (sem #)
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // Ordem de exibição
            $table->boolean('is_required')->default(true); // Task obrigatória ou opcional
            $table->string('icon')->nullable(); // Ícone da task
            $table->string('color')->default('#3B82F6'); // Cor da task
            $table->json('validation_rules')->nullable(); // Regras de validação IA
            $table->timestamps();
            
            // Índices para performance
            $table->index(['challenge_id', 'order']);
            $table->index('hashtag');
            $table->unique(['challenge_id', 'hashtag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_tasks');
    }
};
