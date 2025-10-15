<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopSellingController extends Controller
{
    public function topSelling(Request $request)
{
    $topProducts = TransactionModel::with('product')
        ->select(
            'product_id',
            DB::raw('SUM(qty_terjual) as total_qty'),
            DB::raw('SUM(total_penjualan) as total_revenue')
        )
        ->groupBy('product_id')
        ->orderByDesc('total_qty') // bisa juga orderByDesc('total_revenue') jika ingin ranking by revenue
        ->take(10)
        ->get();

    return response()->json($topProducts);
}

}
