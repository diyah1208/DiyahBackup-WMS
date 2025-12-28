<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // === SUMMARY COUNTERS ===
        $totalStock       = DB::table('tb_stock')->sum('stk_qty');
        $stockMinWarning  = DB::table('tb_stock')->whereColumn('stk_qty','<=','stk_min')->count();

        $totalMR          = DB::table('tb_material_request')->count();
        $mrPending        = DB::table('tb_material_request')->where('mr_status','pending')->count();

        $totalPR          = DB::table('tb_purchase_order')->count();
        $totalPO          = DB::table('tb_purchase_order')->where('po_status','open')->count();

        $totalReceive     = DB::table('tb_receive_item')->count();
        $totalDelivery    = DB::table('tb_delivery')->count();


        // === STOCK WARNING DETAIL ===
        $stockWarning = DB::table('tb_stock')
            ->leftJoin('tb_barang','tb_barang.part_id','=','tb_stock.part_id')
            ->whereColumn('stk_qty','<=','stk_min')
            ->select(
                'tb_barang.part_number',
                'tb_barang.part_name',
                'tb_stock.stk_qty',
                'tb_stock.stk_min',
                'tb_stock.stk_location'
            )
            ->orderBy('tb_stock.stk_qty')
            ->limit(10)
            ->get();

        // === MR LATEST ===
        $latestMR = DB::table('tb_material_request')
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();

        // === PR LATEST ===
        $latestPR = DB::table('tb_purchase_request')
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();

        // === PO LATEST ===
        $latestPO = DB::table('tb_purchase_order')
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();

        // === DELIVERY LATEST ===
        $latestDelivery = DB::table('tb_delivery')
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();

        // === RECEIVE LATEST ===
        $latestReceive = DB::table('tb_receive_item')
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();


        return response()->json([
            'status' => true,
            'summary' => [
                'total_stock'        => $totalStock,
                'stock_min_warning'  => $stockMinWarning,
                'total_mr'           => $totalMR,
                'mr_pending'         => $mrPending,
                'total_pr'           => $totalPR,
                'total_po_open'      => $totalPO,
                'total_delivery'     => $totalDelivery,
                'total_receive'      => $totalReceive,
            ],

            'details' => [
                'stock_warning'      => $stockWarning,
                'latest_mr'          => $latestMR,
                'latest_pr'          => $latestPR,
                'latest_po'          => $latestPO,
                'latest_delivery'    => $latestDelivery,
                'latest_receive'     => $latestReceive,
            ]
        ]);

    }
}
