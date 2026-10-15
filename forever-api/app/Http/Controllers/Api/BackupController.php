<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function __construct(private BackupService $service) {}

    /**
     * Lista los respaldos y datos del resumen (último, próximo automático, espacio usado).
     */
    public function index()
    {
        $backups = Backup::with('user.persona')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(function (Backup $b) {
                return [
                    'id'         => $b->id,
                    'filename'   => $b->filename,
                    'tipo'       => $b->tipo,
                    'status'     => $b->status,
                    'size_bytes' => $b->size_bytes,
                    'error'      => $b->error,
                    'creado_por' => $b->user
                        ? trim(($b->user->persona->nombres ?? $b->user->name) . ' ' . ($b->user->persona->apellidos ?? ''))
                        : 'Sistema (automático)',
                    'created_at' => $b->created_at?->toIso8601String(),
                    'disponible' => $b->status === 'completado' && File::exists($this->service->rutaArchivo($b)),
                ];
            });

        $completados = Backup::where('status', 'completado');

        return response()->json([
            'backups'            => $backups,
            'ultimo'             => (clone $completados)->orderByDesc('created_at')->first()?->created_at?->toIso8601String(),
            'total'              => (clone $completados)->count(),
            'espacio_bytes'      => (int) (clone $completados)->sum('size_bytes'),
            'proximo_automatico' => $this->service->proximoAutomatico()->toIso8601String(),
        ]);
    }

    /**
     * Respaldo manual (botón "Crear respaldo ahora" del admin).
     */
    public function store(Request $request)
    {
        set_time_limit(300);

        $backup = $this->service->ejecutar('manual', $request->user()->id);

        if ($backup->status !== 'completado') {
            return response()->json([
                'message' => 'No se pudo crear el respaldo.',
                'error'   => $backup->error,
            ], 500);
        }

        return response()->json([
            'message' => 'Respaldo creado correctamente.',
            'backup'  => $backup,
        ], 201);
    }

    /**
     * Descarga el .zip del respaldo.
     */
    public function download($id)
    {
        $backup = Backup::findOrFail($id);
        $ruta   = $this->service->rutaArchivo($backup);

        if ($backup->status !== 'completado' || !File::exists($ruta)) {
            return response()->json(['message' => 'El archivo del respaldo ya no existe en el servidor.'], 404);
        }

        return response()->download($ruta, $backup->filename);
    }

    /**
     * Elimina un respaldo (archivo + registro).
     */
    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);
        $this->service->eliminar($backup);

        return response()->json(['message' => 'Respaldo eliminado.']);
    }
}