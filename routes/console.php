<?php

declare(strict_types=1);

use App\Jobs\SendDailyReminderJob;
use Illuminate\Support\Facades\Schedule;

Schedule::daily()
    ->onOneServer()
    ->group(fn () => [
        Schedule::command('sitemap:generate'),
        Schedule::command('dopa:check-expired-challenges'),
    ]);

Schedule::job(new SendDailyReminderJob)->dailyAt('20:00')->onOneServer();
