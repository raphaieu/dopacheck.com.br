<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->string('whatsapp_join_url', 2048)->nullable()->after('slug');
            $table->string('onboarding_title')->nullable()->after('whatsapp_join_url');
            $table->longText('onboarding_body')->nullable()->after('onboarding_title');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropColumn(['whatsapp_join_url', 'onboarding_title', 'onboarding_body']);
        });
    }
};

