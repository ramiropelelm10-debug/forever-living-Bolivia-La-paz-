<?php

namespace App\Services;

use App\Models\Backup;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupService
{
    /**
     * Carpeta absoluta donde se guardan los respaldos (storage/app/private/backups).
     */
    public function directorio(): string
    {
        $dir = Storage::disk('local')->path(config('backup.carpeta', 'backups'));
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    public function rutaArchivo(Backup $backup): string
    {
        return $this->directorio() . DIRECTORY_SEPARATOR . $backup->filename;
    }

    /**
     * Crea un respaldo completo: base de datos (pg_dump) + imágenes de productos, todo en un .zip.
     * $tipo: 'manual' (botón del admin) o 'automatico' (programado cada semana).
     */
    public function ejecutar(string $tipo = 'manual', ?int $userId = null): Backup
    {
        $dir   = $this->directorio();
        $stamp = Carbon::now(config('backup.timezone'))->format('Y-m-d_His');
        $zipName = "backup_{$stamp}_{$tipo}.zip";
        $zipPath = $dir . DIRECTORY_SEPARATOR . $zipName;
        $sqlPath = $dir . DIRECTORY_SEPARATOR . "tmp_{$stamp}.sql";

        $backup = Backup::create([
            'filename' => $zipName,
            'tipo'     => $tipo,
            'status'   => 'en_proceso',
            'user_id'  => $userId,
        ]);

        try {
            $this->volcarBaseDeDatos($sqlPath);
            $this->crearZip($zipPath, $sqlPath, $stamp, $tipo);

            $backup->update([
                'status'     => 'completado',
                'size_bytes' => File::size($zipPath),
            ]);

            $this->limpiarAntiguos($tipo);
        } catch (\Throwable $e) {
            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }
            $backup->update([
                'status' => 'error',
                'error'  => mb_substr($e->getMessage(), 0, 1000),
            ]);
        } finally {
            if (File::exists($sqlPath)) {
                File::delete($sqlPath);
            }
        }

        return $backup->fresh();
    }

    /**
     * Genera el archivo .sql con pg_dump usando las credenciales del .env.
     */
    private function volcarBaseDeDatos(string $sqlPath): void
    {
        $conn = config('database.default');
        if ($conn !== 'pgsql') {
            throw new Exception("El respaldo solo está preparado para PostgreSQL (conexión actual: {$conn}).");
        }

        $cfg = config('database.connections.pgsql');

        $process = new Process([
            config('backup.pg_dump_path', 'pg_dump'),
            '--host=' . $cfg['host'],
            '--port=' . $cfg['port'],
            '--username=' . $cfg['username'],
            '--dbname=' . $cfg['database'],
            '--no-owner',
            '--no-privileges',
            '--file=' . $sqlPath,
               ], null, [
            'PGPASSWORD' => (string) $cfg['password'],
            // En Windows pg_dump necesita SystemRoot para generar números aleatorios;
            // si no, falla con "no se pudo generar la llave restrict" al correr desde el servidor web.
            'SystemRoot' => getenv('SystemRoot') ?: (getenv('SYSTEMROOT') ?: 'C:\\Windows'),
        ]);

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            $detalle = trim($process->getErrorOutput()) ?: 'sin detalle';
            throw new Exception(
                'pg_dump falló (revisa que esté instalado y que BACKUP_PG_DUMP_PATH apunte a pg_dump): ' . $detalle
            );
        }

        if (!File::exists($sqlPath) || File::size($sqlPath) === 0) {
            throw new Exception('pg_dump no generó el archivo de la base de datos.');
        }
    }

    /**
     * Empaqueta el .sql + la carpeta de imágenes de productos en un solo .zip.
     */
    private function crearZip(string $zipPath, string $sqlPath, string $stamp, string $tipo): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('No se pudo crear el archivo .zip del respaldo.');
        }

        $zip->addFile($sqlPath, 'database.sql');

        $imagenes = storage_path('app/public/productos');
        if (File::isDirectory($imagenes)) {
            foreach (File::files($imagenes) as $archivo) {
                $zip->addFile($archivo->getPathname(), 'productos/' . $archivo->getFilename());
            }
        }

        $zip->addFromString('LEEME.txt',
            "Respaldo Forever Living Bolivia\n" .
            "Fecha: {$stamp} ({$tipo})\n\n" .
            "Para restaurar la base de datos:\n" .
            "  psql -U postgres -d forever_living_db -f database.sql\n\n" .
            "Las imagenes de productos van en la carpeta 'productos/' -> copiar a storage/app/public/productos\n"
        );

        $zip->close();
    }

    /**
     * Borra del disco y de la tabla los respaldos más viejos según la retención configurada.
     */
    private function limpiarAntiguos(string $tipo): void
    {
        $conservar = $tipo === 'automatico'
            ? (int) config('backup.retencion_automaticos', 8)
            : (int) config('backup.retencion_manuales', 30);

        $viejos = Backup::where('tipo', $tipo)
            ->where('status', 'completado')
            ->orderByDesc('created_at')
            ->skip($conservar)
            ->take(1000)
            ->get();

        foreach ($viejos as $viejo) {
            $this->eliminar($viejo);
        }
    }

    public function eliminar(Backup $backup): void
    {
        $ruta = $this->rutaArchivo($backup);
        if (File::exists($ruta)) {
            File::delete($ruta);
        }
        $backup->delete();
    }

    /**
     * Fecha del próximo respaldo automático (según día y hora del config).
     */
    public function proximoAutomatico(): Carbon
    {
        $tz   = config('backup.timezone');
        $dia  = (int) config('backup.dia_semana', 0);
        [$h, $m] = array_map('intval', explode(':', config('backup.hora', '02:00')));

        $ahora = Carbon::now($tz);
        $cand  = $ahora->copy()->setTime($h, $m, 0);

        if ($ahora->dayOfWeek !== $dia || $ahora->gte($cand)) {
            $cand = $ahora->copy()->next($dia)->setTime($h, $m, 0);
        }

        return $cand;
    }
}