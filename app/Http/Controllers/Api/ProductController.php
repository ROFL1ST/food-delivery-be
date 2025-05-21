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

        //get product by request user id
        $products = Product::with('user')->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        // $products = Product::with('user')->whereHas('user', function ($query) use ($request) {
        //     $query->where('roles', 'restaurant');
        // })->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Product data loaded successfully',
            'data' => $products
        ]);
    }

    // get product by user id
    public function getProductByUserId($userId)
    {
        $products = Product::with('user')->where('user_id', $userId)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Product data loaded successfully',
            'data' => $products
        ]);
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
            $image->move(public_path('uploads/products'), $image_name);

            $product->image =  $image_name;
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
        if ($request->has('name')) {
            $request->validate(['name' => 'required|string']);
        }
        if ($request->has('description')) {
            $request->validate(['description' => 'required|string']);
        }
        if ($request->has('price')) {
            $request->validate(['price' => 'required|numeric']);
        }
        if ($request->has('stock')) {
            $request->validate(['stock' => 'required|integer']);
        }
        if ($request->has('is_available')) {
            $request->validate(['is_available' => 'required|boolean']);
        }
        if ($request->has('is_favorite')) {
            $request->validate(['is_favorite' => 'required|boolean']);
        }
        if ($request->has('image')) {
            $request->validate(['image' => 'required|image']);
        }

        // find product
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                "status" => "failed",
                'message' => 'Product not found',
            ], 404);
        }

        // update image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $image_name);

            // delete old image
            $oldImagePath = public_path('uploads/products') . DIRECTORY_SEPARATOR . $product->image;
            if ($product->image && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            $product->image = $image_name;
        }

        // update product
        $data = $request->except(['image']);
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
                "status" => "failed",
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
