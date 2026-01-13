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
            $table->string('slug')->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

