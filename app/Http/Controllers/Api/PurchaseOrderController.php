<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrderModel;
use App\Models\PurchaseRequestModel;
use App\Models\PurchaseOrderDetailModel;

class PurchaseOrderController extends Controller
{
    // Menampilkan list PO dengan filter/search
    public function index(Request $request)
    {
        $query = PurchaseOrderModel::with('purchaseRequest.pic'); // relasi ke PR -> PIC

        return response()->json($query->get());
    }

    // Menambahkan PO baru atau update jika po_kode sudah ada
    public function store(Request $request)
    {
        $data = $request->validate([
            'po_kode' => 'required|string|unique:tb_purchase_order,po_kode',
            'pr_id' => 'required|exists:tb_purchase_request,pr_id',
            'po_tanggal' => 'required|date',
            'po_estimasi' => 'nullable|date',
            'po_keterangan' => 'nullable|string',
            'po_status' => 'nullable|string|in:open,approved,closed',
        ]);

        $po = PurchaseOrderModel::where('po_kode', $data['po_kode'])->first();

        if ($po) {
            $po->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Purchase Order berhasil diperbarui',
                'data' => $po
            ], 200);
        }

        $po = PurchaseOrderModel::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Purchase Order berhasil ditambahkan',
            'data' => $po
        ], 201);
    }

    // Menampilkan detail PO
    public function show($id)
    {
        $po = PurchaseOrderModel::with('purchaseRequest.pic')->findOrFail($id);
        return response()->json($po);
    }

    // Update PO
    public function update(Request $request, $id)
    {
        $po = PurchaseOrderModel::findOrFail($id);

        $data = $request->validate([
            'po_kode' => 'sometimes|required|string|unique:tb_purchase_order,po_kode,' . $po->po_id . ',po_id',
            'pr_id' => 'sometimes|required|exists:tb_purchase_request,pr_id',
            'po_tanggal' => 'sometimes|required|date',
            'po_estimasi' => 'nullable|date',
            'po_keterangan' => 'nullable|string',
            'po_status' => 'nullable|string|in:open,approved,closed',
        ]);

        $po->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Purchase Order berhasil diperbarui',
            'data' => $po
        ], 200);
    }

    // Hapus PO
    public function destroy($id)
    {
        $po = PurchaseOrderModel::findOrFail($id);
        $po->delete();

        return response()->json([
            'status' => true,
            'message' => 'Purchase Order berhasil dihapus'
        ], 200);
    }
}
