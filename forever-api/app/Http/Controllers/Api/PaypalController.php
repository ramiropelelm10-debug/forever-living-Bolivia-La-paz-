<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;
use App\Models\Venta; 

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

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => "default",
                    // 🔥 LA BÓVEDA: Guardamos el ID de usuario y los CC dentro de PayPal de forma segura
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
                // Mandamos una URL limpiecita y elegante
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
                // 1. 🔥 EL RECIBO OFICIAL: Sacamos el total EXACTO que PayPal reporta haber cobrado
                $montoCobrado = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0;
                
                // 2. 🔥 ABRIMOS LA BÓVEDA: Sacamos el ID del cliente y los CC intactos
                $customId = $response['purchase_units'][0]['custom_id'] ?? '1|0';
                $datosSecretos = explode('|', $customId);
                $userId = $datosSecretos[0] ?? 1;
                $cc = $datosSecretos[1] ?? 0;

                Venta::create([
                    'nro_factura'  => 'FAC-PP-' . strtoupper(uniqid()),
                    'user_id'      => $userId,
                    'nit_ci'       => '0', 
                    'razon_social' => 'Pago verificado por PayPal',
                    'monto_total'  => $montoCobrado, // ¡PRECIO REAL GARANTIZADO!
                    'monto_iva'    => $montoCobrado * 0.13, 
                    'total_cc'     => $cc,
                ]);

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