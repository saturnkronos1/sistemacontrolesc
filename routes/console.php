<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-creación de ciclo escolar: corre diario a la 01:00 AM desde agosto
Schedule::command('ciclos:autocrear')
    ->dailyAt('01:00')
    ->withoutOverlapping();
