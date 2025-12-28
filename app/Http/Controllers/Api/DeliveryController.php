<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryModel;
use App\Models\DeliveryDetailModel;
use App\Models\MaterialRequestModel;
use App\Models\MaterialRequestItemModel;
use App\Models\StockModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index()
    {
        $data = DeliveryModel::with([
            'details',
            'mr.details'
        ])
            ->orderBy('dlv_status', 'desc')
            ->orderBy('dlv_kode', 'desc')
            ->get();

        return response()->json($data);
    }

    public function showKode($kode)
    {
        $delivery = DeliveryModel::with([
            'details',
            'mr',
            'mr.details'
        ])
            ->where('dlv_kode', $kode)
            ->firstOrFail();

        return response()->json($delivery);
    }

    public function show($id)
    {
        $delivery = DeliveryModel::with([
            'details',
            'mr',
            'mr.details'
        ])->findOrFail($id);

        return response()->json($delivery);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dlv_kode'        => 'required|unique:tb_delivery,dlv_kode',
            'mr_id'           => 'required',
            'dlv_dari_gudang' => 'required',
            'dlv_ke_gudang'   => 'required',
            'dlv_pic'         => 'required',
            'details'         => 'required|array'
        ]);

        DB::transaction(function () use ($request) {

            $delivery = DeliveryModel::create([
                'dlv_kode'        => $request->dlv_kode,
                'mr_id'           => $request->mr_id,
                'dlv_dari_gudang' => $request->dlv_dari_gudang,
                'dlv_ke_gudang'   => $request->dlv_ke_gudang,
                'dlv_ekspedisi'   => $request->dlv_ekspedisi,
                'dlv_no_resi'     => $request->dlv_no_resi,
                'dlv_jumlah_koli' => $request->dlv_jumlah_koli ?? 0,
                'dlv_pic'         => $request->dlv_pic,
                'dlv_status'      => 'pending'
            ]);

            foreach ($request->details as $item) {
                DeliveryDetailModel::create([
                    'dlv_id'              => $delivery->dlv_id,
                    'part_id'             => $item['part_id'],
                    'dtl_dlv_part_number' => $item['dtl_dlv_part_number'],
                    'dtl_dlv_part_name'   => $item['dtl_dlv_part_name'],
                    'dtl_dlv_satuan'      => $item['dtl_dlv_satuan'],
                    'qty_pending'         => $item['qty_pending'],
                    'qty_on_delivery'     => 0,
                    'qty_delivered'       => 0
                ]);
            }
        });

        return response()->json(['message' => 'Delivery created']);
    }

    public function update(Request $request, $kode)
    {
        $delivery = DeliveryModel::where('dlv_kode', $kode)->firstOrFail();

        if ($delivery->dlv_status !== 'pending') {
            throw new Exception('Delivery hanya bisa diedit saat status pending');
        }

        $request->validate([
            'dlv_ekspedisi'   => 'required',
            'dlv_no_resi'     => 'required',
            'dlv_jumlah_koli' => 'required|integer|min:1'
        ]);

        $delivery->update([
            'dlv_ekspedisi'   => $request->dlv_ekspedisi,
            'dlv_no_resi'     => $request->dlv_no_resi,
            'dlv_jumlah_koli' => $request->dlv_jumlah_koli
        ]);

        return response()->json(['message' => 'Delivery updated']);
    }

    public function updateStatus(Request $request, $kode)
    {
        $delivery = DeliveryModel::with('details')
            ->where('dlv_kode', $kode)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:on delivery,delivered'
        ]);

        $current = $delivery->dlv_status;
        $next    = $request->status;

        // VALID STATUS TRANSITION
        if ($current === 'pending' && $next !== 'on delivery') {
            throw new Exception('Status tidak valid');
        }

        if ($current === 'on delivery' && $next !== 'delivered') {
            throw new Exception('Status tidak valid');
        }

        if ($current === 'delivered') {
            throw new Exception('Delivery sudah selesai');
        }

        if ($next === 'on delivery') {
            $this->moveToOnDelivery($delivery);
        }

        if ($next === 'delivered') {
            $this->moveToDelivered($delivery);
        }

        $delivery->update(['dlv_status' => $next]);

        return response()->json([
            'message' => 'Status updated',
            'status'  => $next
        ]);
    }


    private function moveToOnDelivery($delivery)
    {
        DB::transaction(function () use ($delivery) {

            foreach ($delivery->details as $item) {

                $qtyKirim = $item->qty_pending;

                if ($qtyKirim <= 0) {
                    continue;
                }

                $origin = StockModel::where('part_id', $item->part_id)
                    ->where('stk_location', $delivery->dlv_dari_gudang)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($origin->stk_qty < $qtyKirim) {
                    throw new Exception("Stok kurang untuk part {$item->dtl_dlv_part_number}");
                }

                // kurangi stok asal
                $origin->update([
                    'stk_qty' => $origin->stk_qty - $qtyKirim
                ]);

                // pindahkan qty
                $item->update([
                    'qty_pending'     => $item->qty_pending - $qtyKirim,
                    'qty_on_delivery' => $qtyKirim
                ]);
            }
        });
    }


    private function moveToDelivered($delivery)
    {
        DB::transaction(function () use ($delivery) {

            // ambil MR + detail
            $mr = MaterialRequestModel::with('details')
                ->findOrFail($delivery->mr_id);

            foreach ($delivery->details as $item) {

                $qtyTerima = $item->qty_on_delivery;

                if ($qtyTerima <= 0) {
                    continue;
                }

                $stock = StockModel::firstOrCreate(
                    [
                        'part_id'      => $item->part_id,
                        'stk_location' => $delivery->dlv_ke_gudang
                    ],
                    [
                        'stk_qty' => 0
                    ]
                );

                $stock->update([
                    'stk_qty' => $stock->stk_qty + $qtyTerima
                ]);

                $item->update([
                    'qty_delivered'   => $item->qty_delivered + $qtyTerima,
                    'qty_on_delivery' => 0
                ]);

                $mrDetail = MaterialRequestItemModel::where('mr_id', $mr->mr_id)
                    ->where('part_id', $item->part_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $mrDetail->update([
                    'dtl_mr_qty_received' =>
                        $mrDetail->dtl_mr_qty_received + $qtyTerima
                ]);
            }

            $mr->load('details');
            $mr->update([
                'mr_status' => $mr->details->every(
                    fn ($d) => $d->dtl_mr_qty_received >= $d->dtl_mr_qty_request
                ) ? 'close' : 'open'
            ]);
        });
    }

}
