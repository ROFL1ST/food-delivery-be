<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    // overview restaurant
    public function overviewRestaurant(Request $request)
    {
        $user = $request->user();

        if ($user->roles === 'restaurant') {

            $totalOrders = Order::where('restaurant_id', $user->id)->count();


            $totalProducts = Product::where('user_id', $user->id)->count();


            $totalRevenue = Order::where('restaurant_id', $user->id)->sum('total_bill');

            return response()->json([
                'status' => 'success',
                'message' => 'Overview data for restaurant fetched successfully.',
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_revenue' => (float) $totalRevenue,
                ],
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    // overview driver
    public function overviewDriver(Request $request)
    {
        $user = $request->user();
        if ($user->roles == 'driver') {
            $totalOrders = Order::where('driver_id', $user->id)->count();
            $totalRevenue = Order::where('driver_id', $user->id)->sum('total_bill');
            $totalEarnings = Order::where('driver_id', $user->id)->sum('total_bill') * 0.1; // Assuming 10% commission
            return response()->json([
                'status' => 'success',
                'message' => 'Overview driver',
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_revenue' => (float) $totalRevenue,
                    'total_earnings' => (float) $totalEarnings,
                ],
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }
    // overview user
    public function overviewUser(Request $request)
    {
        $user = $request->user();
        if ($user->roles == 'user') {
            $totalOrders = Order::where('user_id', $user->id)->count();
            $totalSpent = Order::where('user_id', $user->id)->sum('total_bill');
            $totalEarnings = Order::where('user_id', $user->id)->sum('total_bill') * 0.1; // Assuming 10% commission
            return response()->json([
                'status' => 'success',
                'message' => 'Overview user',
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_spent' => (float) $totalSpent,
                    'total_earnings' => (float) $totalEarnings,
                ],
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }
}
