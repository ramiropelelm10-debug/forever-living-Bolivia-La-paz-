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
        
        // 🔥 AHORA GUARDAMOS TODO: ITEMS, NIT Y RAZÓN SOCIAL EN LA CACHÉ 🔥
        $datosCompra = [
            'items'        => $request->items,
            'razon_social' => $request->razon_social ?? 'Cliente de PayPal',
            'nit_ci'       => $request->nit_ci ?? '0'
        ];
        
        Cache::put('cart_' . $userId, $datosCompra, now()->addMinutes(30));

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => "default",
                    "custom_id" => $userId . '|' . $cc, 
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $monto
                    ],
                    "description" => "Compra en Forever Living Bolivia"
                ]
            ],
            "application_context" => [
                "cancel_url" => url('/api/paypal/cancel'),
                "return_url" => url('/api/paypal/success') 
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
                $montoCobrado = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0;
                $customId = $response['purchase_units'][0]['custom_id'] ?? '1|0';
                $datosSecretos = explode('|', $customId);
                $userId = $datosSecretos[0] ?? 1;
                $cc = $datosSecretos[1] ?? 0;

                // RECUPERAMOS LOS DATOS DE LA CACHÉ
                $datosCompra = Cache::get('cart_' . $userId);
                
                $razonSocial = 'Pago verificado por PayPal';
                $nitCi = '0';
                $itemsCarrito = [];

                if ($datosCompra) {
                    $itemsCarrito = $datosCompra['items'] ?? [];
                    $razonSocial  = $datosCompra['razon_social'] ?? 'Pago verificado por PayPal';
                    $nitCi        = $datosCompra['nit_ci'] ?? '0';
                }

                // 🔥 CREAMOS LA VENTA CON EL NOMBRE Y NIT REALES 🔥
                $venta = Venta::create([
                    'nro_factura'  => 'FAC-PP-' . strtoupper(uniqid()),
                    'user_id'      => $userId,
                    'nit_ci'       => $nitCi, 
                    'razon_social' => $razonSocial,
                    'monto_total'  => $montoCobrado, 
                    'monto_iva'    => $montoCobrado * 0.13, 
                    'total_cc'     => $cc,
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
                    Cache::forget('cart_' . $userId);
                }

                return redirect('http://localhost:5173/pago-exitoso');

            } catch (\Exception $e) {
                return response()->json([
                    'ALERTA' => 'Falló al guardar en tu Base de Datos.',
                    'EL_CULPABLE_ES' => $e->getMessage()
                ]);
            }
        }

        return redirect('http://localhost:5173/carrito');
    }

    public function cancelPayment()
    {
        return redirect('http://localhost:5173/carrito');
    }
}