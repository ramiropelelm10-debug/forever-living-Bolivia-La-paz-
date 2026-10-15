<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FboNetworkController extends Controller
{
    /**
     * Obtiene el listado de FBOs con sus CC acumulados del mes y su patrocinador
     */
    public function getFboRankings(Request $request)
    {
        // Definimos el mes actual para calcular solo las ventas recientes
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $fbos = User::where('tipo_usuario', 'FBO') 
            ->with('sponsor:id,name') // Traemos el nombre del patrocinador
            ->withSum(['ventas' => function ($query) use ($startOfMonth, $endOfMonth) {
                // Filtramos las ventas solo del mes actual
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            }], 'total_cc') 
            ->orderByDesc('ventas_sum_total_cc') // Ordenamos de mayor a menor para el Ranking
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Ranking y puntos CC calculados correctamente.',
            'data' => $fbos
        ]);
    }
}