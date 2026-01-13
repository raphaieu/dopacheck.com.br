<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $table->foreignId('team_id')->nullable()->after('created_by')->constrained('teams')->nullOnDelete();
            $table->index('team_id');
        });

        Schema::table('user_challenges', function (Blueprint $table): void {
            $table->foreignId('team_id')->nullable()->after('challenge_id')->constrained('teams')->nullOnDelete();
            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_challenges', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('team_id');
            $table->dropIndex(['team_id']);
        });

        Schema::table('challenges', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('team_id');
            $table->dropIndex(['team_id']);
        });
    }
};

