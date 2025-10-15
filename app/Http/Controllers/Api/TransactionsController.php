<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use App\Models\TransactionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionsController extends Controller
{
    public function index(Request $request){
        $length = $request->input('length', 10);
        $start = $request->input('start', 0);   
        $search = $request->input('search.value'); 
        $order = $request->input('order');

        $columns = ['product.nama','qty_terjual','total_penjualan','lokasi','channel','customer','tanggal_transaksi'];

        $query = TransactionModel::with(['product:id,nama'])
            ->select('id','product_id','qty_terjual','total_penjualan','lokasi','channel','customer','tanggal_transaksi');

        if ($search) {
            $query->whereHas('product', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            })->orWhere('customer', 'like', "%{$search}%");
        }

        $totalData = $query->count();

        if ($order) {
            $orderColumnIndex = $order[0]['column'];
            $orderDir = $order[0]['dir'] ?? 'asc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';

            if ($orderColumn === 'product.nama') {
                $query->join('products','transactions.product_id','=','products.id')
                    ->orderBy('products.nama', $orderDir)
                    ->select('transactions.*'); // tetap ambil columns transaksi
            } else {
                $query->orderBy($orderColumn, $orderDir);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $transactions = $query->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')), // untuk DataTables
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalData,
            'data' => $transactions,
        ]);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'product_id'         => 'required|exists:products,id',
            'qty_terjual'        => 'required|integer|min:1',
            'total_penjualan'    => 'required|numeric|min:0',
            'lokasi'             => 'required|string|max:255',
            'channel'            => ['required', Rule::in(['Online', 'Offline', 'Event'])],
            'customer'           => 'required|string|max:255',
            'tanggal_transaksi'  => 'required|date',
        ]);

        $transaction = TransactionModel::create($validated);

        return response()->json([
            'message' => 'Transaksi berhasil ditambahkan',
            'data'    => $transaction->load('product'),
        ], 201);
    }

    public function show($id){
        $transaction = TransactionModel::with('product')->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json($transaction);
    }

    public function update(Request $request, $id){
        $transaction = TransactionModel::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'product_id'         => 'sometimes|required|exists:products,id',
            'qty_terjual'        => 'sometimes|required|integer|min:1',
            'total_penjualan'    => 'sometimes|required|numeric|min:0',
            'lokasi'             => 'sometimes|required|string|max:255',
            'channel'            => ['sometimes', 'required', Rule::in(['Online', 'Offline', 'Event'])],
            'customer'           => 'sometimes|required|string|max:255',
            'tanggal_transaksi'  => 'sometimes|required|date',
        ]);

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui',
            'data'    => $transaction->load('product'),
        ]);
    }

    public function destroy($id){
        $transaction = TransactionModel::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaksi berhasil dihapus']);
    }

    public function import(Request $request){
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:1024',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $data = array_map('str_getcsv', file($path));

        if (count($data) < 2) {
            return response()->json(['error' => 'File CSV kosong atau tidak valid.'], 422);
        }
        $header = array_map('trim', $data[0]);
        unset($data[0]);

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                if (empty(array_filter($row))) continue;
                $row = @array_combine($header, $row);
                if (!$row) {
                    throw new \Exception("Format CSV salah di baris ke-" . ($index + 2));
                }

                $productName = trim($row['Nama Produk'] ?? '');
                $tanggalStr = trim($row['Tanggal Transaksi'] ?? '');
                $qty = (int) ($row['Produk Terjual'] ?? 0);
                $total = (float) str_replace(',', '', $row['Total Penjualan Produk (Rp)'] ?? 0);
                $lokasi = trim($row['Lokasi'] ?? '');
                $channel = trim($row['Channel (Online Offline Event)'] ?? '');
                $customer = trim($row['Customer'] ?? '');

                if (!$productName) continue;

                $productName = preg_replace('/^[=+\-@]/', "'", $productName);
                $customer = preg_replace('/^[=+\-@]/', "'", $customer);
                $lokasi = preg_replace('/^[=+\-@]/', "'", $lokasi);

                if ($qty <= 0) {
                    throw new \Exception("Qty harus lebih dari 0 di baris ke-" . ($index + 2));
                }
                if ($total <= 0) {
                    throw new \Exception("Total Penjualan harus lebih dari 0 di baris ke-" . ($index + 2));
                }

                $product = ProductModel::firstOrCreate(['nama' => $productName], ['harga' => 0]);

                try {
                    $tanggal = Carbon::createFromFormat('j-M-y', $tanggalStr)->format('Y-m-d');
                } catch (\Exception $e) {
                    $tanggal = now()->format('Y-m-d'); // fallback
                }

                TransactionModel::create([
                    'product_id' => $product->id,
                    'qty_terjual' => $qty,
                    'total_penjualan' => $total,
                    'lokasi' => $lokasi,
                    'channel' => ucfirst(strtolower($channel)), // Online / Offline / Event
                    'customer' => $customer,
                    'tanggal_transaksi' => $tanggal,
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal import: ' . $th->getMessage(),
                'line' => $th->getLine(),
            ], 500);
        }
    }

}
