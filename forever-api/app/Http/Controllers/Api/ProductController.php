<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
// Importaciones críticas para la seguridad profesional
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    /**
     * Activa el "candado" de seguridad de Sanctum para todo el controlador.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum')
        ];
    }

    /**
     * Listar productos con búsqueda opcional.
     */
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('name', 'ilike', $searchTerm)
                  ->orWhere('sku', 'ilike', $searchTerm);
        }
        return response()->json($query->orderBy('id', 'desc')->get(), 200);
    }

    /**
     * Crear un nuevo producto.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string',
            'sku'   => 'required|string|unique:products',
            'stock' => 'required|integer',
        ]);

        $product = Product::create([
            'name'         => $request->name,
            'sku'          => $request->sku,
            'stock'        => $request->stock,
            'price_bs'     => $request->price_bs ?? $request->price ?? 0,
            'cc_value'     => $request->cc_value ?? 0,
            'foto_persona' => $request->foto_persona ?? $request->image,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Actualizar producto existente.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'  => 'required|string',
            'sku'   => 'required|string|unique:products,sku,' . $id,
            'stock' => 'required|integer',
        ]);

        $product->update([
            'name'         => $request->name,
            'sku'          => $request->sku,
            'stock'        => $request->stock,
            'price_bs'     => $request->price_bs ?? $request->price ?? $product->price_bs,
            'cc_value'     => $request->cc_value ?? $product->cc_value,
            'foto_persona' => $request->foto_persona ?? $request->image ?? $product->foto_persona,
        ]);

        return response()->json(['message' => 'Actualizado con éxito', 'data' => $product]);
    }

    /**
     * Eliminar producto (Soft Delete).
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Movido a la papelera']);
    }

    /**
     * Ver productos en la papelera.
     */
    public function trash() 
    { 
        return response()->json(Product::onlyTrashed()->orderBy('deleted_at', 'desc')->get()); 
    }
    
    /**
     * Restaurar producto de la papelera.
     */
    public function restore($id) 
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return response()->json($product);
    }
}
