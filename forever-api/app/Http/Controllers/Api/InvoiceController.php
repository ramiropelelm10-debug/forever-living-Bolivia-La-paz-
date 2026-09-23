<?php

namespace App\Http\Controllers\Api; // Se deja un solo namespace correcto

use App\Http\Controllers\Controller; // 🔥 IMPORTANTE: Se importa el controlador base
use Illuminate\Http\Request;
use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        // Se inicia la consulta base
        $query = Invoice::query();

        // 1. Filtro por estado (Emitida, Anulada, etc.)
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // 2. Búsqueda por NIT o Nombre
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nit', 'like', "%{$searchTerm}%")
                  ->orWhere('client_name', 'like', "%{$searchTerm}%")
                  ->orWhere('invoice_number', 'like', "%{$searchTerm}%");
            });
        }

        // 3. Filtros de Tiempo usando Carbon
        $period = $request->input('period', 'all'); // 'today', 'month', 'custom', 'all'

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                if ($request->has('start_date') && $request->has('end_date')) {
                    // Se usa endOfDay() para incluir todas las facturas hasta las 23:59 del último día
                    $start = Carbon::parse($request->start_date)->startOfDay();
                    $end = Carbon::parse($request->end_date)->endOfDay();
                    $query->whereBetween('created_at', [$start, $end]);
                }
                break;
        }

        // 4. Retornar con Paginación (Escalabilidad)
        // Se ordenan de la más reciente a la más antigua
        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }
}