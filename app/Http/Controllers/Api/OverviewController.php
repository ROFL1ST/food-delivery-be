<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $pendingOrders = Order::where('restaurant_id', $user->id)
                ->where('status', 'pending')
                ->count();
            $todayDeliveries = Order::where('restaurant_id', $user->id)
                ->whereDate('updated_at', Carbon::today())
                ->where('status', 'completed')
                ->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Overview data for restaurant fetched successfully.',
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_revenue' => (float) $totalRevenue,
                    'pending_orders' => $pendingOrders,
                    'today_deliveries' => $todayDeliveries,
                ],
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function popularMenuItems(Request $request)
    {
        $user = $request->user();

        if ($user->roles === 'restaurant') {
            $popularProducts = Product::where('user_id', $user->id)
                ->withCount(['orderItems as total_sales' => function ($query) {
                    $query->select(DB::raw('SUM(quantity)'))
                        ->whereHas('order', function ($query) {

                            $query->where('status', 'completed');
                        });
                }])
                ->having('total_sales', '>', 0)
                ->orderByDesc('total_sales')
                ->limit(3)
                ->get();

            $formattedItems = $popularProducts->map(function ($product) {
                return [
                    'name' => $product->name,
                    'image' => $product->image ?? 'https://via.placeholder.com/150/F2F2F2/333333?text=Gambar+Menu',
                    'sales' => (int)$product->total_sales . 'x terjual',
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Popular menu items fetched successfully.',
                'data' => $formattedItems,
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
