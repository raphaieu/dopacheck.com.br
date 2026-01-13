<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_applications', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();

            $table->string('name');
            $table->date('birthdate');
            $table->string('email');
            $table->string('whatsapp_number');
            $table->string('city');
            $table->string('neighborhood');
            $table->string('circle_url');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['team_id', 'email']);
            $table->unique(['team_id', 'whatsapp_number']);

            $table->index('status');
            $table->index('approved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_applications');
    }
};

