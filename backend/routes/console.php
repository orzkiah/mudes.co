<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PROJECT_SPECIFICATION.md §3.9 - scheduled articles auto-publish.
Schedule::command('articles:publish-scheduled')->everyMinute();
Schedule::command('activities:update-statuses')->everyMinute();
