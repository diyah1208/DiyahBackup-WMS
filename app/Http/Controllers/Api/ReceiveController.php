<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReceiveModel;
use App\Models\ReceiveDetailModel;
use App\Models\PurchaseOrderModel;
use App\Models\StockModel;
use App\Models\MaterialRequestModel;
use App\Models\MaterialRequestItemModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiveController extends Controller
{

    public function getPoPurchased(Request $request)
    {
        $data = PurchaseOrderModel::with([
                'details',          // detail PO
                'purchaseRequest'   // kalau relasi ada
            ])
            ->where('po_status', 'purchased')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }
    public function index()
    {
        $data = ReceiveModel::with([
            'purchaseOrder',
            'details'
        ])
        ->orderBy('ri_tanggal', 'desc')
        ->get();

        return response()->json($data);
    }

    public function showByKode($kode)
    {
        $receive = ReceiveModel::with([
            'purchaseOrder',
            'details'
        ])
        ->where('ri_kode', $kode)
        ->firstOrFail();

        return response()->json($receive);
    }

    public function store(Request $request)
    {
        $request->validate([
            "ri_kode" => "required|unique:tb_receive_item,ri_kode",
            "po_id"   => "required|exists:tb_purchase_order,po_id",
            "ri_lokasi" => "required",
            "ri_tanggal" => "required|date",
            "details" => "required|array|min:1",

            "details.*.part_id" => "required|exists:tb_barang,part_id",
            "details.*.mr_id"   => "required|exists:tb_material_request,mr_id",
            "details.*.dtl_ri_part_number" => "required",
            "details.*.dtl_ri_part_name" => "required",
            "details.*.dtl_ri_satuan" => "required",
            "details.*.dtl_ri_qty" => "required|integer|min:1",
        ]);

        $receive = null;

        DB::transaction(function () use ($request, &$receive) {

            $receive = ReceiveModel::create([
                "ri_kode"      => $request->ri_kode,
                "po_id"        => $request->po_id,
                "ri_lokasi"    => $request->ri_lokasi,
                "ri_tanggal"   => $request->ri_tanggal,
                "ri_keterangan"=> $request->ri_keterangan,
                "ri_pic"       => $request->ri_pic ?? auth()->user()->name,
                "ri_status"    => "received"
            ]);

            foreach ($request->details as $item) {

                // INSERT DETAIL RECEIVE
                ReceiveDetailModel::create([
                    "ri_id"  => $receive->ri_id,
                    "po_id"  => $request->po_id,
                    "mr_id"  => $item["mr_id"],
                    "part_id"=> $item["part_id"],

                    "dtl_ri_part_number" => $item["dtl_ri_part_number"],
                    "dtl_ri_part_name"   => $item["dtl_ri_part_name"],
                    "dtl_ri_satuan"      => $item["dtl_ri_satuan"],
                    "dtl_ri_qty"         => $item["dtl_ri_qty"],
                ]);

                $stock = StockModel::firstOrCreate(
                    [
                        "part_id"      => $item["part_id"],
                        "stk_location" => $request->ri_lokasi
                    ],
                    [
                        "stk_qty" => 0,
                        "stk_min" => 0,
                        "stk_max" => 0
                    ]
                );

                $stock->increment("stk_qty", $item["dtl_ri_qty"]);

                $mrDetail = MaterialRequestItemModel::where("mr_id", $item["mr_id"])
                    ->where("part_id", $item["part_id"])
                    ->lockForUpdate()
                    ->firstOrFail();

                // cegah over receive
                $sisa = $mrDetail->dtl_mr_qty_request - $mrDetail->dtl_mr_qty_received;
                $qtyMasuk = min($item["dtl_ri_qty"], $sisa);

                $mrDetail->update([
                    "dtl_mr_qty_received" =>
                        $mrDetail->dtl_mr_qty_received + $qtyMasuk
                ]);
            }
            $mr = MaterialRequestModel::with('details')
                ->findOrFail($request->details[0]["mr_id"]);
            $mr->load('details');

            $mr->update([
                "mr_status" => $mr->details->every(
                    fn ($d) => $d->dtl_mr_qty_received >= $d->dtl_mr_qty_request
                ) ? "close" : "open"
            ]);

            PurchaseOrderModel::where("po_id", $request->po_id)
                ->update(["po_status" => "received"]);
        });

        return response()->json([
            "status" => true,
            "message" => "Receive berhasil dibuat",
            "ri_id" => $receive->ri_id,
            "ri_kode" => $receive->ri_kode
        ]);
    }
}
