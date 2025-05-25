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
        // Total transaksi hari ini
        $totalOrdersToday = Order::where('restaurant_id', $user->id)
            ->whereDate('updated_at', Carbon::today())
            ->count();

        // Total transaksi kemarin
        $totalOrdersYesterday = Order::where('restaurant_id', $user->id)
            ->whereDate('updated_at', Carbon::yesterday())
            ->count();

        // Persentase perubahan transaksi hari ini
        $transactionPercentage = $totalOrdersYesterday > 0
            ? (($totalOrdersToday - $totalOrdersYesterday) / $totalOrdersYesterday) * 100
            : ($totalOrdersToday > 0 ? 100 : 0);

        // Pesanan tertunda hari ini
        $pendingOrders = Order::where('restaurant_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Pesanan tertunda kemarin
        $pendingOrdersYesterday = Order::where('restaurant_id', $user->id)
            ->where('status', 'pending')
            ->whereDate('updated_at', Carbon::yesterday())
            ->count();

        // Persentase perubahan pesanan tertunda
        $pendingPercentage = $pendingOrdersYesterday > 0
            ? (($pendingOrders - $pendingOrdersYesterday) / $pendingOrdersYesterday) * 100
            : ($pendingOrders > 0 ? 100 : 0);

        // Pengiriman hari ini
        $todayDeliveries = Order::where('restaurant_id', $user->id)
            ->whereDate('updated_at', Carbon::today())
            ->where('status', 'completed')
            ->count();

        // Pengiriman kemarin
        $yesterdayDeliveries = Order::where('restaurant_id', $user->id)
            ->whereDate('updated_at', Carbon::yesterday())
            ->where('status', 'completed')
            ->count();

        // Persentase perubahan pengiriman hari ini
        $deliveryPercentage = $yesterdayDeliveries > 0
            ? (($todayDeliveries - $yesterdayDeliveries) / $yesterdayDeliveries) * 100
            : ($todayDeliveries > 0 ? 100 : 0);

        // Data lainnya
        $totalProducts = Product::where('user_id', $user->id)->count();
        $totalRevenue = Order::where('restaurant_id', $user->id)
            ->where('status', 'completed')
            ->sum('total_bill');

        return response()->json([
            'status' => 'success',
            'message' => 'Overview data for restaurant fetched successfully.',
            'data' => [
                'total_orders_today' => $totalOrdersToday,
                'transaction_percentage' => round($transactionPercentage, 2),
                'total_products' => $totalProducts,
                'total_revenue' => (float) $totalRevenue,
                'pending_orders' => $pendingOrders,
                'pending_percentage' => round($pendingPercentage, 2),
                'today_deliveries' => $todayDeliveries,
                'delivery_percentage' => round($deliveryPercentage, 2),
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
                    'price' => (int)$product->price,
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
