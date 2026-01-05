<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrderModel;
use App\Models\PurchaseOrderDetailModel;
use App\Models\PurchaseRequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $pos = PurchaseOrderModel::with('purchaseRequest')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(
            $pos->map(fn ($po) => [
                'id' => $po->po_id,
                'kode' => $po->po_kode,
                'kode_pr' => $po->purchaseRequest->pr_kode ?? null,
                'tanggal' => $po->po_tanggal,
                'tanggal_estimasi' => $po->po_estimasi,
                'status' => strtolower($po->po_status),
                'pic' => $po->po_pic,
                'keterangan' => $po->po_keterangan,
                'created_at' => $po->created_at?->toDateTimeString(),
                'updated_at' => $po->updated_at?->toDateTimeString(),
            ])
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_kode'        => 'required|unique:tb_purchase_order,po_kode',
            'pr_id'          => 'required|exists:tb_purchase_request,pr_id',
            'po_tanggal'     => 'required|date',
            'po_estimasi'    => 'nullable|date',
            'po_keterangan'  => 'nullable|string',
            'details'        => 'required|array',
        ]);

        DB::transaction(function () use ($request) {

            $po = PurchaseOrderModel::create([
                'po_kode'       => $request->po_kode,
                'pr_id'         => $request->pr_id,
                'po_tanggal'    => $request->po_tanggal,
                'po_estimasi'   => $request->po_estimasi,
                'po_status'     => $request->po_status,
                'po_keterangan' => $request->po_keterangan,
                'po_pic'        => $request->po_pic,
            ]);

            foreach ($request->details as $item) {
                PurchaseOrderDetailModel::create([
                    'po_id'                 => $po->po_id,
                    'part_id'               => $item['part_id'],
                    'dtl_po_part_number'    => $item['dtl_po_part_number'],
                    'dtl_po_part_name'      => $item['dtl_po_part_name'],
                    'dtl_po_satuan'         => $item['dtl_po_satuan'],
                    'dtl_po_qty'            => $item['dtl_po_qty'],
                    'dtl_qty_received'   => 0,
                ]);
            }
        });

        return response()->json([
            'message' => 'Purchase Order created'
        ], 201);
    }

    public function showKode($kode)
    {
        $po = PurchaseOrderModel::with([
            'purchaseRequest',
            'details',
        ])
        ->where('po_kode', $kode)
        ->firstOrFail();

        return response()->json($po);
    }

    public function show($id)
    {
        $po = PurchaseOrderModel::with(['purchaseRequest', 'details'])
            ->findOrFail($id);

        return response()->json([
            'id' => $po->po_id,
            'kode' => $po->po_kode,
            'kode_pr' => $po->purchaseRequest->pr_kode ?? null,
            'tanggal' => $po->po_tanggal,
            'tanggal_estimasi' => $po->po_estimasi,
            'status' => strtolower($po->po_status),
            'pic' => $po->po_pic,
            'keterangan' => $po->po_keterangan,
            'created_at' => $po->created_at?->toDateTimeString(),
            'updated_at' => $po->updated_at?->toDateTimeString(),
            'details' => $po->details->map(fn ($d) => [
                'po_detail_id' => $d->dtl_po_id,
                'part_id' => $d->part_id,
                'part_number' => $d->dtl_po_part_number,
                'part_name' => $d->dtl_po_part_name,
                'satuan' => $d->dtl_po_satuan,
                'qty_order' => $d->dtl_po_qty,
                'qty_received' => $d->dtl_qty_received,
            ]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrderModel::findOrFail($id);

        $data = $request->validate([
            'po_kode' => 'sometimes|required|unique:tb_purchase_order,po_kode,' . $po->po_id . ',po_id',
            'po_tanggal' => 'sometimes|required|date',
            'po_estimasi' => 'nullable|date',
            'po_status' => 'nullable|string',
            'po_keterangan' => 'nullable|string',
        ]);

        $po->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Purchase Order berhasil diperbarui',
            'data' => [
                'id' => $po->po_id,
                'kode' => $po->po_kode,
                'status' => strtolower($po->po_status),
            ]
        ]);
    }

    public function destroy($id)
    {
        $po = PurchaseOrderModel::findOrFail($id);
        $po->delete();

        return response()->json([
            'status' => true,
            'message' => 'Purchase Order berhasil dihapus'
        ]);
    }
}
