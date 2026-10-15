<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Venta; 
use App\Models\ItemDeVenta; 
use App\Models\Product;
use App\Services\CarritoService;

class PaypalController extends Controller
{
    public function createPayment(Request $request, CarritoService $carrito)
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'razon_social'     => 'nullable',
            'nit_ci'           => 'nullable',
        ]);

        $usuario = $request->user();

        // 🔥 EL TOTAL SE CALCULA AQUÍ, con los precios reales y el descuento que le corresponde al usuario.
        // Lo que mande el navegador (monto_total, total_cc, precios) se ignora.
        try {
            $calculo = $carrito->calcular($request->items, $usuario);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // 🔥 GUARDAMOS TODO EN CACHÉ (ya calculado por el servidor) 🔥
        $datosCompra = [
            'user_id'      => $usuario->id,
            'razon_social' => $request->razon_social ?: 'Cliente de PayPal',
            'nit_ci'       => (string) ($request->nit_ci ?: '0'),
            'total_bs'     => $calculo['total_bs'],
            'total_usd'    => $calculo['total_usd'],
            'total_cc'     => $calculo['total_cc'],
            'lineas'       => array_map(fn ($l) => [
                'product_id'      => $l['producto']->id,
                'cantidad'        => $l['cantidad'],
                'precio_unitario' => $l['precio_unitario'],
                'subtotal'        => $l['subtotal'],
            ], $calculo['lineas']),
            'order_id'     => null,
        ];

        // Token único e impredecible para este carrito
        $sessionToken = 'cart_' . Str::random(40);

        // 🔥 PASAMOS NUESTRO TOKEN SEGURO POR LA URL DE RETORNO 🔥
        $returnUrl = url("/api/paypal/success?st={$sessionToken}");

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => "default",
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => number_format($calculo['total_usd'], 2, '.', '')
                    ],
                    "description" => "Compra en Forever Living Bolivia"
                ]
            ],
            "application_context" => [
                "cancel_url" => url('/api/paypal/cancel'),
                "return_url" => $returnUrl // Usamos la nueva URL con el token
            ]
        ]);

        // Atamos el carrito al pedido de PayPal y lo guardamos 30 minutos
        if (isset($order['id'])) {
            $datosCompra['order_id'] = $order['id'];
            Cache::put($sessionToken, $datosCompra, now()->addMinutes(30));
        }

        return response()->json($order);
    }

    public function capturePayment(Request $request)
    {
        $sessionToken = $request->query('st');
        $orderId      = $request->query('token'); // PayPal manda el id del pedido en ?token=

        $datosCompra = $sessionToken ? Cache::get($sessionToken) : null;

        // 🔐 Validamos ANTES de cobrar: el carrito debe existir y pertenecer a ESTE pedido de PayPal
        if (!$datosCompra || !$orderId || ($datosCompra['order_id'] ?? null) !== $orderId) {
            Log::warning('PayPal: carrito inexistente, vencido o que no corresponde al pedido.', ['order' => $orderId]);
            return redirect('http://localhost:5173/carrito?error=db_fail');
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($orderId);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            
            try {
                $montoCobrado = (float) ($response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0);

                // Si lo cobrado no coincide con lo calculado, lo dejamos en el log para revisarlo
                if (abs($montoCobrado - (float) $datosCompra['total_usd']) > 0.01) {
                    Log::critical('PayPal: el monto cobrado no coincide con el calculado.', [
                        'cobrado'   => $montoCobrado,
                        'esperado'  => $datosCompra['total_usd'],
                        'pedido'    => $orderId,
                    ]);
                }

                // 🔥 CREAMOS LA VENTA (todo o nada) 🔥
                DB::transaction(function () use ($datosCompra) {
                    $venta = Venta::create([
                        'nro_factura'  => 'FAC-PP-' . strtoupper(uniqid()),
                        'user_id'      => $datosCompra['user_id'],
                        'nit_ci'       => $datosCompra['nit_ci'], 
                        'razon_social' => $datosCompra['razon_social'],
                        'monto_total'  => floatval($datosCompra['total_bs']),
                        'monto_iva'    => floatval($datosCompra['total_bs'] * 0.13), 
                        'total_cc'     => floatval($datosCompra['total_cc']),
                    ]);

                    foreach ($datosCompra['lineas'] as $linea) {
                        ItemDeVenta::create([
                            'venta_id'        => $venta->id,
                            'product_id'      => $linea['product_id'],
                            'cantidad'        => $linea['cantidad'],
                            'precio_unitario' => $linea['precio_unitario'],
                            'subtotal'        => $linea['subtotal'],
                        ]);

                        // El pago ya se cobró, así que no cortamos la venta si el stock cambió; lo avisamos en el log
                        $producto = Product::lockForUpdate()->find($linea['product_id']);
                        if ($producto && $producto->stock >= $linea['cantidad']) {
                            $producto->decrement('stock', $linea['cantidad']);
                        } else {
                            Log::warning('PayPal: stock insuficiente al descontar tras el pago.', ['product_id' => $linea['product_id']]);
                        }
                    }
                });

                Cache::forget($sessionToken); // Limpiamos la caché

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