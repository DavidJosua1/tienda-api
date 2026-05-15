<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::where('persona_id', auth()->id())->get();
        return response()->json($productos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'stock' => 'nullable|integer',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
        ]);

        $imagen_url = null;
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            $imagen_url = asset('storage/' . $path);
        }

        $producto = Producto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'stock' => $request->stock ?? 0,
            'imagen_url' => $imagen_url,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'persona_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'producto' => $producto,
        ], 201);
    }

    public function show($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json($producto);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        if ($request->hasFile('imagen')) {
            if ($producto->imagen_url) {
                $oldPath = str_replace(asset('storage/'), '', $producto->imagen_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('imagen')->store('productos', 'public');
            $producto->imagen_url = asset('storage/' . $path);
        }

        $producto->update($request->except('imagen'));
        $producto->save();

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'producto' => $producto,
        ]);
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        if ($producto->imagen_url) {
            $oldPath = str_replace(asset('storage/'), '', $producto->imagen_url);
            Storage::disk('public')->delete($oldPath);
        }

        $producto->delete();
        return response()->json(['message' => 'Producto eliminado exitosamente']);
    }
}
