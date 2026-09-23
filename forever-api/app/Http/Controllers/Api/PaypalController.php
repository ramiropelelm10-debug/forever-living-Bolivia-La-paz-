<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; 
use App\Models\Venta; 
use App\Models\ItemDeVenta; 
use App\Models\Product;

class PaypalController extends Controller
{
    public function createPayment(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        
        $monto = $request->monto_total ?? 10.00; 
        $cc = $request->total_cc ?? 0; 
        $userId = $request->user() ? $request->user()->id : 1;
        
        // 🔥 GUARDAMOS TODO EN CACHÉ 🔥
        $datosCompra = [
            'items'        => $request->items,
            'razon_social' => $request->razon_social ?? 'Cliente de PayPal',
            'nit_ci'       => $request->nit_ci ?? '0',
            'user_id'      => $userId, // Lo guardamos explícitamente
            'total_cc'     => $cc
        ];
        
        // Guardamos en caché por 30 minutos usando un token único generado por nosotros
        $sessionToken = uniqid('cart_');
        Cache::put($sessionToken, $datosCompra, now()->addMinutes(30));

        // 🔥 PASAMOS NUESTRO TOKEN SEGURO POR LA URL DE RETORNO 🔥
        $returnUrl = url("/api/paypal/success?st={$sessionToken}");

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => "default",
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $monto
                    ],
                    "description" => "Compra en Forever Living Bolivia"
                ]
            ],
            "application_context" => [
                "cancel_url" => url('/api/paypal/cancel'),
                "return_url" => $returnUrl // Usamos la nueva URL con el token
            ]
        ]);

        return response()->json($order);
    }

    public function capturePayment(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($request->token);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            
            try {
                // Recuperamos nuestro token de la URL
                $sessionToken = $request->query('st');

                // Si no hay token, fallamos de manera segura
                if (!$sessionToken) {
                    throw new \Exception("Token de sesión de carrito no proporcionado por PayPal.");
                }

                // RECUPERAMOS LOS DATOS DE LA CACHÉ
                $datosCompra = Cache::get($sessionToken);
                
                if (!$datosCompra) {
                    throw new \Exception("Los datos de la compra expiraron o no existen en caché.");
                }

                $montoCobrado = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0;
                
                $userId = $datosCompra['user_id'];
                $cc = $datosCompra['total_cc'];
                $itemsCarrito = $datosCompra['items'] ?? [];
                $razonSocial  = $datosCompra['razon_social'];
                $nitCi        = $datosCompra['nit_ci'];

                // 🔥 CREAMOS LA VENTA 🔥
                $venta = Venta::create([
                    'nro_factura'  => 'FAC-PP-' . strtoupper(uniqid()),
                    'user_id'      => $userId,
                    'nit_ci'       => (string)$nitCi, 
                    'razon_social' => $razonSocial,
                    'monto_total'  => floatval($montoCobrado * 6.96), // Lo convertimos de USD a Bs de nuevo
                    'monto_iva'    => floatval(($montoCobrado * 6.96) * 0.13), 
                    'total_cc'     => floatval($cc),
                ]);

                if (!empty($itemsCarrito)) {
                    foreach ($itemsCarrito as $item) {
                        $producto = Product::find($item['id']);
                        if ($producto) {
                            ItemDeVenta::create([
                                'venta_id'        => $venta->id,
                                'product_id'      => $producto->id,
                                'cantidad'        => $item['quantity'],
                                'precio_unitario' => $producto->price_bs ?? $item['price_bs'],
                                'subtotal'        => $item['quantity'] * ($producto->price_bs ?? $item['price_bs']),
                            ]);

                            if($producto->stock >= $item['quantity']){
                                $producto->decrement('stock', $item['quantity']);
                            }
                        }
                    }
                    Cache::forget($sessionToken); // Limpiamos la caché
                }

                return redirect('http://localhost:5173/pago-exitoso');

            } catch (\Exception $e) {
                Log::error('🔥 ERROR PAYPAL 🔥: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
                // Si falla la BD, mandamos al carrito pero con un mensaje en la URL para debuggear
                return redirect('http://localhost:5173/carrito?error=db_fail');
            }
        }

        return redirect('http://localhost:5173/carrito?error=paypal_cancel');
    }

    public function cancelPayment()
    {
        return redirect('http://localhost:5173/carrito');
    }
}