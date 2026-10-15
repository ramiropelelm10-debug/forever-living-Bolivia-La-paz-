<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run {--tipo=automatico : manual o automatico}';

    protected $description = 'Crea un respaldo completo (base de datos + imágenes de productos)';

    public function handle(BackupService $service): int
    {
        $tipo = $this->option('tipo') === 'manual' ? 'manual' : 'automatico';

        $this->info("Creando respaldo ({$tipo})...");
        $backup = $service->ejecutar($tipo);

        if ($backup->status !== 'completado') {
            $this->error('El respaldo falló: ' . $backup->error);
            return self::FAILURE;
        }

        $this->info("Respaldo creado: {$backup->filename} (" . round($backup->size_bytes / 1024 / 1024, 2) . ' MB)');
        return self::SUCCESS;
    }
}