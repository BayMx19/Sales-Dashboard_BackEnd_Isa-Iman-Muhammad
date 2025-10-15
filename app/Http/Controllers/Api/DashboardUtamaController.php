<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardUtamaController extends Controller
{
    public function kpi()
    {
        $totalProductSales = TransactionModel::sum('qty_terjual');
        $uniqueCustomer = TransactionModel::distinct('customer')->count('customer');

        return response()->json([
            'success' => true,
            'data' => [
                'productSales' => $totalProductSales,
                'uniqueCustomer' => $uniqueCustomer,
            ]
        ]);
    }

    // 2. Revenue vs Sales (Combo Chart)
    public function revenueVsSales(Request $request)
    {
        $start = $request->input('start') ?? now()->subMonth()->format('Y-m-d');
        $end = $request->input('end') ?? now()->format('Y-m-d');

        $data = TransactionModel::select(
                'tanggal_transaksi as tanggal_transaksi',
                DB::raw('SUM(qty_terjual) as total_qty'),
                DB::raw('SUM(total_penjualan) as total_revenue')
            )
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->groupBy('tanggal_transaksi')
            ->orderBy('tanggal_transaksi', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // 3. Total Sales by Channel (Donut Chart)
    public function salesByChannel()
    {
        $data = TransactionModel::select(
                'channel',
                DB::raw('SUM(total_penjualan) as total_revenue')
            )
            ->groupBy('channel')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // 4. Top Selling Products (Table)
    public function topSellingProducts($limit = 10)
    {
        $data = TransactionModel::select(
                'products.nama as product_name',
                DB::raw('SUM(transactions.qty_terjual) as qty_sold'),
                'products.endorser as talent'
            )
            ->join('products', 'transactions.product_id', '=', 'products.id')
            ->groupBy('transactions.product_id', 'products.nama', 'products.endorser')
            ->orderByDesc('qty_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
