<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
    public function index(){
        $products = ProductModel::orderBy('id', 'desc')->get();
        return response()->json($products);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'nama'     => 'required|string|max:255|unique:products,nama',
            'endorser' => 'nullable|string|max:255',
            'harga'    => 'nullable|numeric|min:0',
        ]);

        $product = ProductModel::create([
            'nama'     => $validated['nama'],
            'endorser' => $validated['endorser'] ?? null,
            'harga'    => $validated['harga'] === '' || $validated['harga'] === null
                    ? 0
                    : $validated['harga'],
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data'    => $product,
        ], 201);
    }

    public function show($id){
        $product = ProductModel::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json($product);
    }

    public function update(Request $request, $id){
        $product = ProductModel::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'nama'     => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'endorser' => 'nullable|string|max:255',
            'harga'    => 'nullable|numeric|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'data'    => $product,
        ]);
    }

    public function destroy($id){
        $product = ProductModel::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}
