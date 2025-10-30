<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

//log a message every minute
Artisan::command('log:message', function () {
    \Log::info('This is a log message from the log:message command.');
})->purpose('Log a message every minute')->everyMinute();

