<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table): void {
            // Escopo de unicidade do hashtag:
            // - 0 => global (challenges.visibility = global)
            // - team_id => time (challenges.visibility = team)
            // - 1e12 + challenge_id => private (por desafio, sem colidir com global/team)
            $table->unsignedBigInteger('scope_team_id')
                ->default(0)
                ->after('challenge_id');

            $table->index('scope_team_id');
        });

        // Backfill baseado no challenge (compatível com SQLite/MySQL):
        // private => 1e12 + challenge.id
        // team => challenges.team_id
        // global => 0
        DB::statement("
            UPDATE challenge_tasks
            SET scope_team_id = (
                SELECT
                    CASE
                        WHEN challenges.visibility = 'private' THEN 1000000000000 + challenges.id
                        WHEN challenges.visibility = 'team' THEN COALESCE(challenges.team_id, 0)
                        ELSE 0
                    END
                FROM challenges
                WHERE challenges.id = challenge_tasks.challenge_id
            )
        ");

        Schema::table('challenge_tasks', function (Blueprint $table): void {
            // Remove unicidade global antiga
            $table->dropUnique('challenge_tasks_hashtag_unique');

            // Nova unicidade: hashtag único por escopo (global/team/private)
            $table->unique(['scope_team_id', 'hashtag']);
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table): void {
            $table->dropUnique(['scope_team_id', 'hashtag']);
            $table->unique('hashtag');

            $table->dropIndex(['scope_team_id']);
            $table->dropColumn('scope_team_id');
        });
    }
};

