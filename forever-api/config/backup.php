<?php

return [

    // Ruta del ejecutable pg_dump. En Windows pon la ruta completa en el .env, por ejemplo:
    // BACKUP_PG_DUMP_PATH='C:\Program Files\PostgreSQL\18\bin\pg_dump.exe'
    'pg_dump_path' => env('BACKUP_PG_DUMP_PATH', 'pg_dump'),

    // Zona horaria usada para el nombre de los archivos y la programación semanal.
    'timezone' => 'America/La_Paz',

    // Respaldo automático semanal: 0 = domingo, 1 = lunes ... 6 = sábado.
    'dia_semana' => 0,
    'hora'       => '02:00',

    // Cuántos respaldos se conservan (los más antiguos se borran solos).
    'retencion_automaticos' => 8,
    'retencion_manuales'    => 30,

    // Carpeta dentro de storage/app/private
    'carpeta' => 'backups',
];