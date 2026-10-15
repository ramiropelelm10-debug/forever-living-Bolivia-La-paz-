<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Respaldo automático semanal (día y hora configurables en config/backup.php).
// Requiere el scheduler activo: en desarrollo `php artisan schedule:work`.
Schedule::command('backup:run --tipo=automatico')
    ->weeklyOn((int) config('backup.dia_semana', 0), config('backup.hora', '02:00'))
    ->timezone(config('backup.timezone', 'America/La_Paz'))
    ->withoutOverlapping();