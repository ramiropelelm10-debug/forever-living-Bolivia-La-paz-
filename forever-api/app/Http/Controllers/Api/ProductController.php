<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
// Importaciones críticas para la seguridad profesional
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller implements HasMiddleware
{
    /**
     * Activa el "candado" de seguridad de Sanctum para todo el controlador.
     * 🔥 EXCLUIMOS 'index' y 'show' para que la tienda pública pueda ver el catálogo 🔥
     */
    public static function middleware(): array
    {
        return [
            (new Middleware('auth:sanctum'))->except(['index', 'show'])
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
        
        $productos = $query->orderBy('id', 'desc')->get()->map(function ($producto) {
            // 🔥 TRUCO: Le pasamos 'imagen' a Vue para que lo lea sin problemas
            // aunque en tu base de datos se llame 'foto_persona'
            $producto->imagen = $producto->foto_persona;
            return $producto;
        });

        return response()->json($productos, 200);
    }

    /**
     * Crear un nuevo producto.
     */
    public function store(Request $request)
    {
        // 1. Validamos los datos (La imagen ahora es validada para evitar hackeos)
        $request->validate([
            'name'  => 'required|string',
            'sku'   => 'required|string|unique:products',
            'stock' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3048', // Max 3MB
        ]);

        // 2. LA MAGIA DE LA IMAGEN: Guardamos en el disco duro
        $imageUrl = null;
        if ($request->hasFile('image') || $request->hasFile('imagen')) {
            $file = $request->file('image') ? $request->file('image') : $request->file('imagen');
            $path = $file->store('productos', 'public');
            $imageUrl = url('storage/' . $path); // Genera: http://localhost:8000/storage/productos/foto.jpg
        }

        // 3. Guardamos en PostgreSQL
        $product = Product::create([
            'name'         => $request->name ?? $request->nombre,
            'sku'          => $request->sku ?? $request->code,
            'stock'        => $request->stock ?? $request->cantidad,
            'price_bs'     => $request->price ?? $request->precio ?? 0,
            'category'     => $request->category ?? $request->categoria ?? 'General',
            'cc_value'     => $request->cc_value ?? 0,
            'foto_persona' => $imageUrl, // Guardamos la URL real
        ]);

        return response()->json($product, 201);
    }

    /**
     * Actualizar producto existente.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // 1. Validamos (El SKU ignora su propio ID para que te deje guardar sin error)
        $request->validate([
            'name'  => 'required|string',
            'sku'   => 'required|string|unique:products,sku,' . $id,
            'stock' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3048',
        ]);

        // 2. LA MAGIA DE LA IMAGEN: Si sube una nueva, la guardamos
        if ($request->hasFile('image') || $request->hasFile('imagen')) {
            // Opcional: Borrar la foto vieja del servidor para no acumular basura
            // if ($product->foto_persona) {
            //     $oldPath = str_replace(url('storage') . '/', '', $product->foto_persona);
            //     Storage::disk('public')->delete($oldPath);
            // }

            $file = $request->file('image') ? $request->file('image') : $request->file('imagen');
            $path = $file->store('productos', 'public');
            $product->foto_persona = url('storage/' . $path); // Sobrescribimos la URL
        }

        // 3. Actualizamos en PostgreSQL
        $product->update([
            'name'         => $request->name ?? $request->nombre ?? $product->name,
            'sku'          => $request->sku ?? $request->code ?? $product->sku,
            'stock'        => $request->stock ?? $request->cantidad ?? $product->stock,
            'price_bs'     => $request->price ?? $request->precio ?? $product->price_bs,
            'category'     => $request->category ?? $request->categoria ?? $product->category,
            'cc_value'     => $request->cc_value ?? $product->cc_value,
            // foto_persona ya se actualizó arriba si es que subió una nueva
        ]);

        return response()->json(['message' => 'Actualizado con éxito', 'data' => $product]);
    }

    /**
     * Eliminar producto (Soft Delete).
     */
    public function destroy($id)
    {
        // Se busca el producto por ID, sin inyección implícita para evitar fallos si buscas por SKU
        $product = Product::findOrFail($id);
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