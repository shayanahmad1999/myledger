<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('finance:process-recurring')
    ->hourly()
    ->withoutOverlapping()
    ->timezone(config('finance.timezone'));

Schedule::command('finance:send-reminders')
    ->everyMinute()
    ->withoutOverlapping()
    ->timezone(config('finance.timezone'));
