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
        Schema::create('challenges', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_days')->default(21);
            $table->boolean('is_template')->default(false); // Templates oficiais
            $table->boolean('is_public')->default(true); // Visível na lista pública
            $table->boolean('is_featured')->default(false); // Destacado na home
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('participant_count')->default(0); // Cache de participantes
            $table->string('category')->nullable(); // fitness, mindfulness, learning, etc
            $table->string('difficulty')->default('beginner'); // beginner, intermediate, advanced
            $table->string('image_url')->nullable(); // Imagem do desafio
            $table->json('tags')->nullable(); // Tags para busca
            $table->timestamps();
            
            // Índices para performance
            $table->index(['is_template', 'is_public']);
            $table->index(['is_featured', 'is_public']);
            $table->index('participant_count');
            $table->index('category');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
