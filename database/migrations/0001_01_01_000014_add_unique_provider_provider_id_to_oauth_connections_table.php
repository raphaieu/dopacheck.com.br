<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_connections', function (Blueprint $table): void {
            // Garante que a MESMA conta do provider não seja vinculada a múltiplos usuários.
            $table->unique(['provider', 'provider_id'], 'oauth_connections_provider_provider_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_connections', function (Blueprint $table): void {
            $table->dropUnique('oauth_connections_provider_provider_id_unique');
        });
    }
};

