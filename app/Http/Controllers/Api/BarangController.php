<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ✅ WAJIB
use App\Models\BarangModel;
use App\Models\StockModel;

class BarangController extends Controller
{
    private const LOKASI_LIST = [
        ['nama' => 'JAKARTA', 'kode' => 'JKT'],
        ['nama' => 'TANJUNG ENIM', 'kode' => 'ENIM'],
        ['nama' => 'BALIKPAPAN', 'kode' => 'BPN'],
        ['nama' => 'SITE BA', 'kode' => 'BA'],
        ['nama' => 'SITE TAL', 'kode' => 'TAL'],
        ['nama' => 'SITE MIP', 'kode' => 'MIP'],
        ['nama' => 'SITE MIFA', 'kode' => 'MIFA'],
        ['nama' => 'SITE BIB', 'kode' => 'BIB'],
        ['nama' => 'SITE AMI', 'kode' => 'AMI'],
        ['nama' => 'SITE TABANG', 'kode' => 'TAB'],
        ['nama' => 'UNASSIGNED', 'kode' => 'UNASSIGNED'],
    ];

    public function index(Request $request)
    {
        $query = BarangModel::query();

        return response()->json([
            'status' => true,
            'data'   => $query->orderBy('part_name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_number' => 'required|unique:tb_barang,part_number',
            'part_name'   => 'required|string',
            'part_satuan' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {

            $barang = BarangModel::create([
                'part_number' => $request->part_number,
                'part_name'   => $request->part_name,
                'part_satuan' => $request->part_satuan,
            ]);

            foreach (self::LOKASI_LIST as $lokasi) {
                StockModel::create([
                    'part_id'      => $barang->part_id,
                    'stk_location' => $lokasi['nama'],
                    'stk_qty'      => 0,
                    'stk_min'      => 0,
                    'stk_max'      => 0,
                ]);
            }
        });

        return response()->json([
            'status'  => true,
            'message' => 'Barang & stok semua lokasi berhasil dibuat',
        ], 201);
    }

    public function show($id)
    {
        $barang = BarangModel::find($id);

        if (!$barang) {
            return response()->json([
                'status'  => false,
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $barang
        ]);
    }

    public function update(Request $request, $id)
    {
        $barang = BarangModel::find($id);

        if (!$barang) {
            return response()->json([
                'status'  => false,
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'part_number' => 'required|unique:tb_barang,part_number,' . $id . ',part_id',
            'part_name'   => 'required',
            'part_satuan' => 'required',
        ]);

        $barang->update($request->only([
            'part_number',
            'part_name',
            'part_satuan',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Barang berhasil diupdate',
            'data'    => $barang
        ]);
    }
}
