<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller implements HasMiddleware
{
    /**
     * Aplica la protección de autenticación mediante Sanctum a las operaciones del controlador, 
     * manteniendo públicas únicamente las rutas de visualización del catálogo.
     */
    public static function middleware(): array
    {
        return [
            (new Middleware('auth:sanctum'))->except(['index', 'show'])
        ];
    }

    /**
     * Obtiene el listado de productos disponibles, permitiendo filtrarlos opcionalmente 
     * mediante un término de búsqueda que coincida con el nombre o el código SKU.
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
            // Asigna el valor de la foto al atributo imagen para mantener compatibilidad con la interfaz de usuario.
            $producto->imagen = $producto->foto_persona;
            return $producto;
        });

        return response()->json($productos, 200);
    }

    /**
     * Valida la información recibida, almacena la imagen proporcionada en el disco 
     * y registra un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string',
            'sku'   => 'required|string|unique:products',
            'stock' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image') || $request->hasFile('imagen')) {
            $file = $request->file('image') ? $request->file('image') : $request->file('imagen');
            $path = $file->store('productos', 'public');
            $imageUrl = url('storage/' . $path);
        }

        $product = Product::create([
            'name'         => $request->name ?? $request->nombre,
            'sku'          => $request->sku ?? $request->code,
            'stock'        => $request->stock ?? $request->cantidad,
            'price_bs'     => $request->price ?? $request->precio ?? 0,
            'category'     => $request->category ?? $request->categoria ?? 'General',
            'cc_value'     => $request->cc_value ?? 0,
            'foto_persona' => $imageUrl,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Modifica los datos de un producto previamente registrado, actualizando su imagen 
     * en el servidor en caso de que se envíe un nuevo archivo.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'  => 'required|string',
            'sku'   => 'required|string|unique:products,sku,' . $id,
            'stock' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3048',
        ]);

        if ($request->hasFile('image') || $request->hasFile('imagen')) {
            $file = $request->file('image') ? $request->file('image') : $request->file('imagen');
            $path = $file->store('productos', 'public');
            $product->foto_persona = url('storage/' . $path);
        }

        $product->update([
            'name'         => $request->name ?? $request->nombre ?? $product->name,
            'sku'          => $request->sku ?? $request->code ?? $product->sku,
            'stock'        => $request->stock ?? $request->cantidad ?? $product->stock,
            'price_bs'     => $request->price ?? $request->precio ?? $product->price_bs,
            'category'     => $request->category ?? $request->categoria ?? $product->category,
            'cc_value'     => $request->cc_value ?? $product->cc_value,
        ]);

        return response()->json(['message' => 'Actualizado con éxito', 'data' => $product]);
    }

    /**
     * Oculta un producto del catálogo mediante un borrado lógico, enviándolo a la papelera 
     * sin eliminarlo definitivamente de la base de datos.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Movido a la papelera']);
    }

    /**
     * Recupera y muestra un listado con todos los productos que han sido eliminados 
     * de forma lógica y se encuentran en la papelera.
     */
    public function trash() 
    { 
        return response()->json(Product::onlyTrashed()->orderBy('deleted_at', 'desc')->get()); 
    }
    
    /**
     * Restablece un producto que se encontraba en la papelera, volviéndolo a hacer 
     * visible e interactuable dentro del catálogo principal.
     */
    public function restore($id) 
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return response()->json($product);
    }
}