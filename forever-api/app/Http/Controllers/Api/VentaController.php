<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\ItemDeVenta;
use App\Models\Product;
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
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nit_ci'         => 'nullable|string',
            'razon_social'   => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'monto_total'    => 'required|numeric',
            'total_cc'       => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                
                $venta = Venta::create([
                    'nro_factura'  => 'FAC-' . strtoupper(uniqid()), 
                    'user_id'      => Auth::id() ?? 1, 
                    'nit_ci'       => $request->nit_ci,
                    'razon_social' => $request->razon_social,
                    'monto_total'  => $request->monto_total,
                    'monto_iva'    => $request->monto_total * 0.13, 
                    'total_cc'     => $request->total_cc,
                ]);

                foreach ($request->items as $item) {
                    $producto = Product::findOrFail($item['id']);

                    if ($producto->stock < $item['quantity']) {
                        throw new Exception("Stock insuficiente para el producto: " . $producto->name);
                    }

                    ItemDeVenta::create([
                        'venta_id'        => $venta->id,
                        'product_id'      => $producto->id,
                        'cantidad'        => $item['quantity'],
                        'precio_unitario' => $producto->price_bs,
                        'subtotal'        => $item['quantity'] * $producto->price_bs,
                    ]);

                    $producto->decrement('stock', $item['quantity']);
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
     */
    public function generarPdf($id)
    {
        try {
            $venta = Venta::with(['items.producto'])->findOrFail($id);
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