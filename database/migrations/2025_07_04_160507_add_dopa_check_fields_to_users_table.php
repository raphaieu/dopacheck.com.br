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
        Schema::table('users', function (Blueprint $table): void {
            // DOPA Check specific fields
            $table->string('username')->unique()->nullable()->after('email');
            $table->string('plan')->default('free')->after('username'); // free, pro
            $table->string('whatsapp_number')->nullable()->after('plan');
            $table->string('phone')->nullable()->after('whatsapp_number');
            $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            $table->json('preferences')->nullable()->after('subscription_ends_at');
            
            // Índices para performance
            $table->index('plan');
            $table->index('whatsapp_number');
            $table->index('subscription_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['plan']);
            $table->dropIndex(['whatsapp_number']);
            $table->dropIndex(['subscription_ends_at']);
            
            $table->dropColumn([
                'username',
                'plan',
                'whatsapp_number',
                'phone',
                'subscription_ends_at',
                'preferences'
            ]);
        });
    }
};
