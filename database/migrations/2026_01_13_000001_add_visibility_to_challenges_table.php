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
        Schema::table('challenges', function (Blueprint $table): void {
            // Substitui semanticamente o antigo "is_public" por um campo explícito:
            // - private: só o criador
            // - team: membros de um team_id
            // - global: qualquer usuário da plataforma
            $table->enum('visibility', ['private', 'team', 'global'])
                ->default('global')
                ->after('is_public');

            $table->index('visibility');
            $table->index(['visibility', 'team_id']);
        });

        // Backfill baseado no modelo antigo:
        // - is_public=false => private
        // - is_public=true + team_id != null => team
        // - demais => global
        DB::table('challenges')
            ->where('is_public', false)
            ->update(['visibility' => 'private']);

        DB::table('challenges')
            ->where('is_public', true)
            ->whereNotNull('team_id')
            ->update(['visibility' => 'team']);
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $table->dropIndex(['visibility']);
            $table->dropIndex(['visibility', 'team_id']);
            $table->dropColumn('visibility');
        });
    }
};

