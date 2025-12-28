<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\StockModel;
use App\Models\BarangModel;

class StockController extends Controller
{
  
    public function index(Request $request)
    {
        $data = StockModel::with('barang')
            ->orderBy('stk_location')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'part_id'      => 'required|exists:tb_barang,part_id',
            'stk_location' => 'required',
            'stk_qty'      => 'required|integer|min:0',
            'stk_min'      => 'required|integer|min:0',
            'stk_max'      => 'required|integer|min:0',
        ]);

        // Cek stok berdasarkan kombinasi lokasi + part
        $stock = StockModel::where('part_id', $data['part_id'])
            ->where('stk_location', $data['stk_location'])
            ->first();

        if ($stock) {

            $stock->update([
                'stk_qty' => $data['stk_qty'],
                'stk_min' => $data['stk_min'],
                'stk_max' => $data['stk_max'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Stok berhasil diperbarui',
                'data' => $stock
            ], 200);
        }

        $stock = StockModel::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Stok baru berhasil ditambahkan',
            'data' => $stock
        ], 201);
    }

}


