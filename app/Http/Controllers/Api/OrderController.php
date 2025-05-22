<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //User: create new order
    public function createOrder(Request $request)
    {
        $request->validate([
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|integer|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'restaurant_id' => 'required|integer|exists:users,id',
            'shipping_cost' => 'required|integer',

        ]);

        $totalPrice = 0;
        foreach ($request->order_items as $item) {
            $product = Product::find($item['product_id']);
            $totalPrice += $product->price * $item['quantity'];
        }

        $totalBill = $totalPrice + $request->shipping_cost;

        $user = $request->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $shippingAddress = $user->address;
        $data['shipping_address'] = $shippingAddress;
        $shippingLatLong = $user->latlong;
        $data['shipping_latlong'] = $shippingLatLong;
        $data['status'] = 'pending';
        $data['total_price'] = $totalPrice;
        $data['total_bill'] = $totalBill;

        $order = Order::create($data);

        foreach ($request->order_items as $item) {
            $product = Product::find($item['product_id']);
            $orderItem = new OrderItem([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
            $order->orderItems()->save($orderItem);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);
    }


    // update purchase status
    public function updatePurchaseStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,canceled',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->status == 'completed') {
            $order->payment_method = $request->payment_method;
        }
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ], 200);
    }

    // order history
    public function orderHistory(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)->with(['orderItems.product'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Order history retrieved successfully',
            'data' => $orders
        ], 200);
    }

    // cancel order
    public function cancelOrder(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found',
            ], 404);
        }

        $order->status = 'canceled';
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order canceled successfully',
            'data' => $order
        ], 200);
    }

    // get order by id
    public function getOrderById(Request $request, $id)
    {
        $order = Order::with(['orderItems.product'])->find($id);
        if (!$order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order retrieved successfully',
            'data' => $order
        ], 200);
    }

    // get order by status for restaurant
    public function getOrderByStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,canceled,ready_for_delivery,prepared',
        ]);
        $user = $request->user();
        if ($user->roles != 'restaurant') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized',
            ], 401);
        }

        $orders = Order::where('restaurant_id', $user->id)->where('status', $request->status)->with(['orderItems.product'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Orders retrieved successfully',
            'data' => $orders
        ], 200);
    }

    // update order status for restaurant
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,canceled,ready_for_delivery,prepared',
        ]);

        $user = $request->user();
        if ($user->roles != 'restaurant') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized',
            ], 401);
        }

        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found',
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ], 200);
    }

    // get order by status for driver
    public function getOrdersByStatusDriver(Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,ready_for_delivery,prepared',
        ]);
        $user = $request->user();
        if ($user->roles != 'driver') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized',
            ], 401);
        }

        $orders = Order::where('driver_id', $user->id)->where('status', $request->status)->with(['orderItems.product'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Orders retrieved successfully',
            'data' => $orders
        ], 200);
    }

    // get order status ready for delivery
    public function getOrderStatusReadyForDelivery(Request $request)
    {
        // $user = $request->user();
        $orders = Order::with('restaurant')
            ->where('status', 'ready_for_delivery')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Get all orders by status ready for delivery',
            'data' => $orders
        ]);
    }

    // update order status for driver
    public function updateOrderStatusDriver(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,canceled,on_the_way,delivered',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Order not found',
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ], 200);
    }
}
