<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequestModel;
use App\Models\BarangModel;
use App\Models\PurchaseRequestItemModel;


class PurchaseRequestController extends Controller
{
    public function index()
    {
        $data = PurchaseRequestModel::with([
            'details',
            'details.mr', 
        ]); 
        return response()->json($data->get());
    }

    public function showKode($kode)
    {
        $kode = urldecode($kode);
        $pr = PurchaseRequestModel::with([
            'details',
            'details.mr',
        ])
        ->where('pr_kode', $kode)
        ->firstOrFail();

        return response()->json($pr);
    }


    public function show($id)
    {
        $pr = PurchaseRequestModel::with([
            'details',
        ])->findOrFail($id);

        return response()->json($pr);
    }

     public function store(Request $request)
    {
        $request->validate([
            'pr_kode'        => 'required|unique:tb_purchase_request,pr_kode',
            'pr_lokasi'      => 'required',
            'pr_tanggal'     => 'required',
            'pr_pic'         => 'required',
            'details'        => 'required|array',
        ]);

        DB::transaction(function () use ($request) {

            $delivery = PurchaseRequestModel::create([
                'pr_kode'     => $request->pr_kode,
                'pr_lokasi'   => $request->pr_lokasi,
                'pr_tanggal'  => $request->pr_tanggal,
                'pr_status'   => 'open',
                'pr_pic'      => $request->pr_pic,
            ]);

            foreach ($request->details as $item) {
                PurchaseRequestItemModel::create([
                    'pr_id'                 => $delivery->pr_id,
                    'mr_id'                 => $item['mr_id'],
                    'part_id'               => $item['part_id'],
                    'dtl_pr_part_number'    => $item['dtl_pr_part_number'],
                    'dtl_pr_part_name'      => $item['dtl_pr_part_name'],
                    'dtl_pr_satuan'         => $item['dtl_pr_satuan'],
                    'dtl_pr_qty'            => 0,
                ]);
            }
        });

        return response()->json(['message' => 'Purchase Request created']);
    }
}