<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $table->date('start_date')->nullable()->after('duration_days');
            $table->date('end_date')->nullable()->after('start_date');

            $table->index('start_date');
            $table->index('end_date');
        });

        // Backfill para desafios existentes (cross-DB: sem SQL específico do driver)
        DB::table('challenges')
            ->select(['id', 'created_at', 'duration_days', 'start_date', 'end_date'])
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $createdAt = $row->created_at ? Carbon::parse($row->created_at) : now();
                    $durationDays = (int) ($row->duration_days ?? 21);
                    $durationDays = max(1, min(365, $durationDays));

                    $start = $row->start_date
                        ? Carbon::parse($row->start_date)->startOfDay()
                        : $createdAt->copy()->startOfDay();

                    $end = $row->end_date
                        ? Carbon::parse($row->end_date)->startOfDay()
                        : $start->copy()->addDays($durationDays - 1);

                    DB::table('challenges')
                        ->where('id', $row->id)
                        ->update([
                            'start_date' => $start->toDateString(),
                            'end_date' => $end->toDateString(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table): void {
            $table->dropIndex(['start_date']);
            $table->dropIndex(['end_date']);
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};

