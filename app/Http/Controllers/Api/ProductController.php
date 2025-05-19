<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // index
    public function index(Request $request)
    {
        // get all products
        $products = Product::with('user')->where('user_id', $request->user_id)->with('user')->get();

        return response()->json([
            "status" => "success",
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ], 200);
    }

    // get product by user id
    public function getProductByUserId(Request $request)
    {
        // get all products by user id
        $products = Product::with('user')->where('user_id', $request->user_id)->with('user')->get();

        return response()->json([
            "status" => "success",
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ], 200);
    }

    // store
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'is_available' => 'required|boolean',
            'is_favorite' => 'required|boolean',
            'image' => 'required|image',
        ]);

        $user = $request->user();
        $request->merge(['user_id' => $user->id]);

        $data = $request->all();

        $product = Product::create($data);

        //check if image is available
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            $filePath = $image->storeAs('images/products', $image_name, 'public');

            $product->image = 'images/' . $filePath;
            $product->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product added successfully',
            'data' => $product
        ], 201);
    }


    // update
    public function update(Request $request, $id)
    {
        // validate request
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'is_available' => 'required|boolean',
            'is_favorite' => 'nullable|boolean',
        ]);

        // find product
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                "status" => "error",
                'message' => 'Product not found',
            ], 404);
        }

        // update product
        $data = $request->all();
        $product->update($data);



        return response()->json([
            "status" => "success",
            'message' => 'Product updated successfully',
            'data' => $product,
        ], 200);
    }

    // destroy
    public function destroy($id)
    {
        // find product
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                "status" => "error",
                'message' => 'Product not found',
            ], 404);
        }

        // delete product
        $product->delete();

        return response()->json([
            "status" => "success",
            'message' => 'Product deleted successfully',
        ], 200);
    }
}
