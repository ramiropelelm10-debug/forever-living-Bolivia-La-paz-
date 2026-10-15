<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Exception;

class CarritoService
{
    // Tipo de cambio que usa la tienda (el mismo valor del carrito del frontend).
    public const TIPO_CAMBIO = 6.96;

    /**
     * Descuento (%) que le corresponde al usuario. Misma regla que la tienda:
     * un FBO paga con su discount_rate (o 30% si no tiene uno configurado); los demás no tienen descuento.
     */
    public function descuentoDe(?User $user): float
    {
        if (!$user || $user->tipo_usuario !== 'fbo') {
            return 0.0;
        }

        $rate = (float) optional($user->fbo)->discount_rate;

        return $rate > 0 ? $rate : 30.0;
    }

    /**
     * Calcula la compra con los precios REALES de la base de datos.
     * Ignora cualquier precio o total que mande el navegador.
     *
     * $bloquear = true bloquea las filas de productos (usar dentro de una transacción).
     */
    public function calcular(array $items, ?User $user, bool $bloquear = false): array
    {
        // Juntamos los productos repetidos en una sola línea.
        $cantidades = [];
        foreach ($items as $item) {
            $id  = (int) ($item['id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);

            if ($id <= 0 || $qty < 1) {
                throw new Exception('El carrito tiene un producto o una cantidad inválida.');
            }

            $cantidades[$id] = ($cantidades[$id] ?? 0) + $qty;
        }

        if (empty($cantidades)) {
            throw new Exception('El carrito está vacío.');
        }

        $descuento = $this->descuentoDe($user);
        $lineas    = [];
        $total     = 0.0;
        $cc        = 0.0;

        foreach ($cantidades as $id => $qty) {
            // Los productos borrados (papelera) no se encuentran, así que no se pueden comprar.
            $consulta = $bloquear ? Product::lockForUpdate() : Product::query();
            $producto = $consulta->find($id);

            if (!$producto) {
                throw new Exception("El producto #{$id} ya no está disponible.");
            }

            if ($producto->stock < $qty) {
                throw new Exception("Stock insuficiente para el producto: {$producto->name}");
            }

            $precioBase  = (float) $producto->price_bs;
            $precioFinal = $precioBase - ($precioBase * ($descuento / 100));
            $subtotal    = $precioFinal * $qty;

            $total += $subtotal;
            $cc    += (float) $producto->cc_value * $qty;

            $lineas[] = [
                'producto'        => $producto,
                'cantidad'        => $qty,
                'precio_unitario' => round($precioFinal, 2),
                'subtotal'        => round($subtotal, 2),
            ];
        }

        $totalBs = round($total, 2);

        return [
            'lineas'    => $lineas,
            'total_bs'  => $totalBs,
            'total_usd' => round($totalBs / self::TIPO_CAMBIO, 2),
            'total_cc'  => round($cc, 3),
            'descuento' => $descuento,
        ];
    }
}