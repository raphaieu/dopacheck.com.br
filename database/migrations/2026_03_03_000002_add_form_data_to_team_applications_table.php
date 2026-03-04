<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_applications', function (Blueprint $table): void {
            $table->json('form_data')->nullable()->after('circle_url');
        });

        Schema::table('team_applications', function (Blueprint $table): void {
            $table->string('name')->nullable()->change();
            $table->date('birthdate')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('whatsapp_number')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('neighborhood')->nullable()->change();
            $table->string('circle_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('team_applications', function (Blueprint $table): void {
            $table->dropColumn('form_data');
            $table->string('name')->nullable(false)->change();
            $table->date('birthdate')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('whatsapp_number')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('neighborhood')->nullable(false)->change();
            $table->string('circle_url')->nullable(false)->change();
        });
    }
};
