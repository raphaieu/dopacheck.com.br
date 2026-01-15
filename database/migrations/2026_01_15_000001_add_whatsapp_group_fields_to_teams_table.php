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
            // JID do grupo no WhatsApp (ex: 120363404774829500@g.us)
            $table->string('whatsapp_group_jid', 64)->nullable()->after('whatsapp_join_url');
            // Nome/subject do grupo (opcional, conveniência para UI)
            $table->string('whatsapp_group_name')->nullable()->after('whatsapp_group_jid');

            $table->unique('whatsapp_group_jid');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropUnique(['whatsapp_group_jid']);
            $table->dropColumn(['whatsapp_group_jid', 'whatsapp_group_name']);
        });
    }
};

