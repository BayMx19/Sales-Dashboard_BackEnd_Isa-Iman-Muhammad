<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardProductController extends Controller
{
    // 1. Top Selling Products (Table)
    public function topSellingProducts($limit = 10)
    {
        $data = TransactionModel::select(
                'products.nama as product_name',
                DB::raw('SUM(transactions.qty_terjual) as qty_sold'),
                'products.endorser as talent',
                'transactions.channel',
                'products.harga'
            )
            ->join('products', 'transactions.product_id', '=', 'products.id')
            ->groupBy('transactions.product_id', 'products.nama', 'products.endorser', 'transactions.channel', 'products.harga')
            ->orderByDesc('qty_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // 2. Channel Overview (Radar Chart)
    public function channelOverview()
    {
        $data = TransactionModel::select(
                'channel',
                DB::raw('SUM(qty_terjual) as total_qty')
            )
            ->groupBy('channel')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // 3. Sessions Overview (Line Chart)
    public function sessionsOverview(Request $request)
    {
        // asumsi session = jumlah transaksi per hari
        $start = $request->input('start') ?? now()->subMonth()->format('Y-m-d');
        $end = $request->input('end') ?? now()->format('Y-m-d');

        $data = TransactionModel::select(
                'tanggal_transaksi',
                DB::raw('COUNT(DISTINCT customer) as sessions')
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
}
