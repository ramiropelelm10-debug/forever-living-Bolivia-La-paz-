<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\ItemDeVenta;
use App\Models\Product;
use App\Services\CarritoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Validator;
use Exception;

// 🔥 IMPORTACIONES PARA PDF Y EXCEL
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;

class VentaController extends Controller
{
    /**
     * Listar todas las ventas con sus detalles
     */
    public function index()
    {
        $ventas = Venta::with(['items.producto', 'user.persona'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ventas);
    }

    /**
     * 🔥 HISTORIAL PERSONAL DEL USUARIO (Lo que pide PerfilView.vue) 🔥
     */
    public function mySales(Request $request)
    {
        // Trae solo las compras del usuario que tiene el token activo
        $ventas = Venta::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ventas);
    }

    /**
     * REGISTRO AVANZADO (Facturación oficial)
     * El total y los puntos CC se calculan aquí con los precios reales de la base de datos.
     * Lo que mande el navegador (monto_total, total_cc, precios) se ignora.
     */
    public function store(Request $request, CarritoService $carrito)
    {
        $validator = Validator::make($request->all(), [
            'nit_ci'           => 'nullable|string',
            'razon_social'     => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $carrito) {

                $usuario = $request->user();

                // Calcula con precios reales y bloquea el stock mientras dura la transacción
                $calculo = $carrito->calcular($request->items, $usuario, true);

                $venta = Venta::create([
                    'nro_factura'  => 'FAC-' . strtoupper(uniqid()), 
                    'user_id'      => $usuario->id, 
                    'nit_ci'       => $request->nit_ci,
                    'razon_social' => $request->razon_social,
                    'monto_total'  => $calculo['total_bs'],
                    'monto_iva'    => $calculo['total_bs'] * 0.13, 
                    'total_cc'     => $calculo['total_cc'],
                ]);

                foreach ($calculo['lineas'] as $linea) {
                    ItemDeVenta::create([
                        'venta_id'        => $venta->id,
                        'product_id'      => $linea['producto']->id,
                        'cantidad'        => $linea['cantidad'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'subtotal'        => $linea['subtotal'],
                    ]);

                    $linea['producto']->decrement('stock', $linea['cantidad']);
                }

                return response()->json([
                    'message'     => '¡Venta procesada exitosamente!',
                    'nro_factura' => $venta->nro_factura,
                    'venta_id'    => $venta->id,
                    'total_bs'    => $venta->monto_total
                ], 201);
            });

        } catch (Exception $e) {
            return response()->json([
                'error'   => 'La venta no pudo ser procesada',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 NUEVO: REGISTRO SIMPLE (La función que pediste)
     */
    public function storeSimple(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'cantidad'   => 'required',
            'total_bs'   => 'required',
        ]);

        return Venta::create($request->all());
    }

    /**
     * GENERAR PDF DE LA VENTA
     * Solo puede descargarlo el dueño de la venta o un administrador.
     */
    public function generarPdf(Request $request, $id)
    {
        try {
            $venta = Venta::with(['items.producto'])->findOrFail($id);

            $usuario = $request->user();
            if ($usuario->role !== 'admin' && (int) $venta->user_id !== (int) $usuario->id) {
                return response()->json(['message' => 'No tienes permiso para ver esta factura.'], 403);
            }

            $pdf = Pdf::loadView('pdf.factura', compact('venta'));
            return $pdf->download("Factura_{$venta->nro_factura}.pdf");
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo generar el PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * EXPORTAR HISTORIAL A EXCEL
     */
    public function exportExcel() 
    {
        try {
            if (ob_get_contents()) ob_end_clean(); 
            return Excel::download(new VentasExport, 'Reporte_Ventas_Forever_Bolivia.xlsx');
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al generar el archivo Excel',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}