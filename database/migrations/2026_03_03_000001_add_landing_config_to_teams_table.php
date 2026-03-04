<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->string('landing_template', 64)->default('dopamina')->after('onboarding_body');
            $table->json('form_schema')->nullable()->after('landing_template');
            $table->string('onboarding_behavior', 64)->default('application_only')->after('form_schema');
            $table->json('theme')->nullable()->after('onboarding_behavior');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropColumn(['landing_template', 'form_schema', 'onboarding_behavior', 'theme']);
        });
    }
};
