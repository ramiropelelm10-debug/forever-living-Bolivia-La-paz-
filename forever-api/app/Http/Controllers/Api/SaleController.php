<?php

namespace App\Http\Controllers\Api; // <-- Ubicación actualizada

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller; // <-- Importante para extender de Controller

class SaleController extends Controller
{
    /**
     * Listar ventas con sus productos asociados
     */
    public function index()
    {
        // Usamos 'with' para traer los datos del producto asociado (Eager Loading)
        $sales = Sale::with('product')->latest()->get();
        return response()->json($sales);
    }

    /**
     * Registrar una venta con impuestos de Bolivia (IVA e IT)
     */
    public function store(Request $request)
    {
        // 1. Identificar al usuario (Admin/Vendedor)
        $userId = Auth::id() ?: 1;

        // 2. Buscar producto y validar stock
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        if ($product->stock < $request->cantidad) {
            return response()->json(['message' => 'Stock insuficiente para Forever Bolivia'], 400);
        }

        $total = $product->price_bs * $request->cantidad;

        // 3. Crear la venta con cálculos de impuestos bolivianos
        $sale = Sale::create([
            'nro_factura'    => 'FAC-' . time(),
            'user_id'        => $userId,
            'product_id'     => $product->id,
            'cantidad'       => $request->cantidad,
            'monto_total'    => $total,
            'monto_iva'      => $total * 0.13, // 13% IVA
            'monto_it'       => $total * 0.03, // 3% IT
            'monto_neto'     => $total - ($total * 0.13),
            'total_cc'       => $product->cc_value * $request->cantidad,
            'cliente_nombre' => $request->cliente_nombre ?: 'Mostrador'
        ]);

        // 4. Descontar del inventario
        $product->decrement('stock', $request->cantidad);

        return response()->json($sale, 201);
    }
}