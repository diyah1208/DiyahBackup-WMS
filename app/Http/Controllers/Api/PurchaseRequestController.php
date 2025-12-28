<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequestModel;
use App\Models\BarangModel;

class PurchaseRequestController extends Controller
{
    // ===================== GET / LIST =====================
    public function index(Request $request)
    {
        $query = PurchaseRequestModel::with('pic','details');

        if ($request->filled('pr_kode')) {
            $query->where('pr_kode', 'like', '%' . $request->pr_kode . '%');
        }

        if ($request->filled('pr_lokasi')) {
            $query->where('pr_lokasi', 'like', '%' . $request->pr_lokasi . '%');
        }

        if ($request->filled('pr_status')) {
            $query->where('pr_status', $request->pr_status);
        }

        if ($request->filled('pic_nama')) {
            $query->whereHas('pic', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->pic_nama . '%');
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pr_kode', 'like', "%{$search}%")
                  ->orWhere('pr_lokasi', 'like', "%{$search}%")
                  ->orWhere('pr_status', 'like', "%{$search}%")
                  ->orWhereHas('pic', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        return response()->json($query->get(), 200);
    }

    // ===================== POST / CREATE =====================
    public function store(Request $request)
    {
        $data = $request->validate([
            'pr_kode'    => 'required|string|unique:tb_purchase_request,pr_kode',
            'pr_lokasi'  => 'required|string',
            'pr_pic_id'  => 'required|exists:users,id',
            'pr_tanggal' => 'required|date',
            'pr_status'  => 'nullable|string|in:open,approved,closed',
        ]);

        $pr = PurchaseRequestModel::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'Purchase Request berhasil ditambahkan',
            'data'    => $pr
        ], 201);
    }

    // ===================== PUT / UPDATE =====================
    public function update(Request $request, $id)
    {
        $pr = PurchaseRequestModel::find($id);

        if (!$pr) {
            return response()->json([
                'status'  => false,
                'message' => 'Purchase Request tidak ditemukan'
            ], 404);
        }

        $data = $request->validate([
            'pr_kode'    => 'sometimes|required|string|unique:tb_purchase_request,pr_kode,' . $id . ',pr_id',
            'pr_lokasi'  => 'sometimes|required|string',
            'pr_pic_id'  => 'sometimes|required|exists:users,id',
            'pr_tanggal' => 'sometimes|required|date',
            'pr_status'  => 'nullable|string|in:open,approved,closed',
        ]);

        $pr->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Purchase Request berhasil diperbarui',
            'data'    => $pr
        ], 200);
    }

    // ===================== DELETE =====================
    public function destroy($id)
    {
        $pr = PurchaseRequestModel::find($id);

        if (!$pr) {
            return response()->json([
                'status'  => false,
                'message' => 'Purchase Request tidak ditemukan'
            ], 404);
        }

        $pr->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Purchase Request berhasil dihapus'
        ], 200);
    }

    // ===================== GET DETAIL BY ID =====================
    public function show($id)
    {
        $pr = PurchaseRequestModel::with([
            'pic',
            'details.mr',
            'details.part'
        ])
        ->find($id);

        if (!$pr) {
            return response()->json([
                'status' => false,
                'message' => 'Purchase Request tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $pr
        ], 200);
    }


}
