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

class VentaController extends Controller
{
    /**
     * Listar todas las ventas con sus detalles
     */
    public function index()
    {
        // ¡CORRECCIÓN AQUÍ! 
        // Quitamos 'user.persona' porque causaba el Error 500.
        // Ahora solo traemos los items y sus productos.
        $ventas = Venta::with(['items.producto'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ventas);
    }

    /**
     * Registrar una nueva venta (Cabecera + Detalles)
     */
    public function store(Request $request)
    {
        // 1. VALIDACIÓN DE ENTRADA
        $validator = Validator::make($request->all(), [
            'nit_ci'           => 'nullable|string',
            'razon_social'     => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'monto_total'      => 'required|numeric',
            'total_cc'         => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // 2. INICIO DE TRANSACCIÓN ATÓMICA
            return DB::transaction(function () use ($request) {
                
                // A. CREAR CABECERA DE LA VENTA
                $venta = Venta::create([
                    'nro_factura'  => 'FAC-' . strtoupper(uniqid()), 
                    'user_id'      => Auth::id() ?? 1, // Respaldo por si el Auth es nulo
                    'nit_ci'       => $request->nit_ci,
                    'razon_social' => $request->razon_social,
                    'monto_total'  => $request->monto_total,
                    'monto_iva'    => $request->monto_total * 0.13, // IVA 13% Bolivia
                    'total_cc'     => $request->total_cc,
                ]);

                // B. PROCESAR CADA PRODUCTO DEL CARRITO
                foreach ($request->items as $item) {
                    $producto = Product::findOrFail($item['id']);

                    // Verificar stock antes de proceder
                    if ($producto->stock < $item['quantity']) {
                        throw new Exception("Stock insuficiente para el producto: " . $producto->name);
                    }

                    // Guardar el detalle (Item de Venta)
                    ItemDeVenta::create([
                        'venta_id'        => $venta->id,
                        'product_id'      => $producto->id,
                        'cantidad'        => $item['quantity'],
                        'precio_unitario' => $producto->price_bs,
                        'subtotal'        => $item['quantity'] * $producto->price_bs,
                    ]);

                    // C. ACTUALIZAR STOCK DEL PRODUCTO
                    $producto->decrement('stock', $item['quantity']);
                }

                // 3. RESPUESTA EXITOSA
                return response()->json([
                    'message'     => '¡Venta procesada exitosamente!',
                    'nro_factura' => $venta->nro_factura,
                    'venta_id'    => $venta->id,
                    'total_bs'    => $venta->monto_total
                ], 201);
            });

        } catch (Exception $e) {
            // Si algo falla, Laravel hace Rollback automático gracias a DB::transaction
            return response()->json([
                'error'   => 'La venta no pudo ser procesada',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}