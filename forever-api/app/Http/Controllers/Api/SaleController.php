<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Estas son las importaciones que eliminan el error de la rayita roja
use App\Models\Sale;
use App\Models\Product;

class SaleController extends Controller
{
    /**
     * Lista todas las ventas para el panel administrativo
     */
    public function index()
    {
        // Cargamos las relaciones para que en el panel se vea el nombre del FBO y del producto
        return Sale::with(['user', 'product'])->orderBy('created_at', 'desc')->get();
    }

    /**
     * Procesa la compra desde la tienda "Wouuu"
     */
    public function store(Request $request)
    {
        $user = Auth::user(); // El FBO autenticado
        $cartItems = $request->items; // Array de productos enviados desde el frontend

        if (!$cartItems || count($cartItems) == 0) {
            return response()->json(['error' => 'El carrito está vacío'], 400);
        }

        try {
            // Iniciamos transacción para que si un producto falla, no se guarde nada
            return DB::transaction(function () use ($user, $cartItems, $request) {
                
                $salesProcessed = [];
                $totalCCAcumulado = 0;

                foreach ($cartItems as $item) {
                    // Buscar el producto por SKU
                    $product = Product::where('sku', $item['sku'])->first();

                    if (!$product) {
                        throw new \Exception("Producto con SKU {$item['sku']} no encontrado.");
                    }

                    // 1. Verificar Stock
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para {$product->name}. Disponibles: {$product->stock}");
                    }

                    // 2. Descontar Stock
                    $product->decrement('stock', $item['quantity']);

                    // 3. Cálculos de Precios e Impuestos (Bolivia)
                    // Aplicamos el descuento que tenga el FBO en su perfil
                    $discountRate = ($user->discount_percent ?? 0) / 100;
                    $precioConDescuento = $product->price_bs * (1 - $discountRate);
                    
                    $montoTotal = $precioConDescuento * $item['quantity'];
                    $montoIva = $montoTotal * 0.13; // 13% IVA
                    $montoIt = $montoTotal * 0.03;  // 3% IT
                    $montoNeto = $montoTotal - $montoIva;
                    $ccCalculados = $product->cc_value * $item['quantity'];

                    // 4. Generar Número de Factura Correlativo
                    $nextId = (DB::table('sales')->max('id') ?? 0) + 1;
                    $nroFactura = "FAC-" . date('Ymd') . "-" . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                    // 5. Guardar en la base de datos con tus nombres de columna de pgAdmin
                    $sale = Sale::create([
                        'nro_factura'    => $nroFactura,
                        'user_id'        => $user->id,
                        'product_id'     => $product->id,
                        'cantidad'       => $item['quantity'],
                        'cliente_nit'    => $request->nit_ci ?? '0',
                        'cliente_nombre' => $request->razon_social ?? 'SIN NOMBRE',
                        'monto_total'    => $montoTotal,
                        'monto_iva'      => $montoIva,
                        'monto_it'       => $montoIt,
                        'monto_neto'     => $montoNeto,
                        'total_cc'       => $ccCalculados
                    ]);

                    $totalCCAcumulado += $ccCalculados;
                    $salesProcessed[] = $sale;
                }

                return response()->json([
                    'status' => 'success',
                    'message' => '¡Venta procesada en PostgreSQL! 🚀',
                    'nro_factura' => $salesProcessed[0]->nro_factura,
                    'cc_logrados' => round($totalCCAcumulado, 3)
                ], 201);
            });

        } catch (\Exception $e) {
            // Si hay cualquier error (de stock o de base de datos), se cancela todo
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra el detalle de una venta única
     */
    public function show($id)
    {
        $sale = Sale::with(['user', 'product'])->find($id);
        if (!$sale) return response()->json(['message' => 'Venta no encontrada'], 404);
        return $sale;
    }
}