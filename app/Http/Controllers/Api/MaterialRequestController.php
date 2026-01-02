<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MaterialRequestModel;
use App\Models\MaterialRequestItemModel;

class MaterialRequestController extends Controller
{
    /**
     * Mapping lokasi nama → kode
     */
    private array $lokasiKodeMap = [
        'JAKARTA'        => 'JKT',
        'TANJUNG ENIM'   => 'ENIM',
        'BALIKPAPAN'     => 'BPN',
        'SITE BA'        => 'BA',
        'SITE TAL'       => 'TAL',
        'SITE MIP'       => 'MIP',
        'SITE MIFA'      => 'MIFA',
        'SITE BIB'       => 'BIB',
        'SITE AMI'       => 'AMI',
        'SITE TABANG'    => 'TAB',
    ];

    private function getLokasiKode(string $lokasi): string
    {
        return $this->lokasiKodeMap[strtoupper($lokasi)] ?? 'UNK';
    }

    public function index(Request $request)
    {
        $query = MaterialRequestModel::with('details');
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'mr_tanggal'  => 'required',
            'mr_lokasi'   => 'required',
            'mr_pic'      => 'required',
            'mr_due_date' => 'required',
            'details'     => 'required|array'
        ]);

        return DB::transaction(function () use ($request) {

            $lokasiNama = strtoupper($request->mr_lokasi);    
            $lokasiKode = $this->getLokasiKode($lokasiNama); 

            $now = now();

            $lastMr = MaterialRequestModel::where('mr_lokasi', $lokasiNama)
                ->whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->lockForUpdate()
                ->orderBy('mr_id', 'desc')
                ->first();

            $urutan = $lastMr
                ? ((int) substr($lastMr->mr_kode, -5)) + 1
                : 1;

            $mrKode = sprintf(
                "GMI/%s/%s/%05d",
                $lokasiKode,
                $now->format('y/m'),
                $urutan
            );

            $materialreq = MaterialRequestModel::create([
                'mr_kode'     => $mrKode,
                'mr_tanggal'  => $request->mr_tanggal,
                'mr_lokasi'   => $lokasiNama,
                'mr_pic'      => $request->mr_pic,
                'mr_due_date' => $request->mr_due_date,
                'mr_status'   => 'open'
            ]);

            foreach ($request->details as $item) {
                MaterialRequestItemModel::create([
                    'mr_id'               => $materialreq->mr_id,
                    'part_id'             => $item['part_id'],
                    'dtl_mr_part_number'  => $item['dtl_mr_part_number'],
                    'dtl_mr_part_name'    => $item['dtl_mr_part_name'],
                    'dtl_mr_satuan'       => $item['dtl_mr_satuan'],
                    'dtl_mr_prioritas'    => $item['dtl_mr_prioritas'],
                    'dtl_mr_qty_request'  => (int) ($item['dtl_mr_qty_request'] ?? 0),
                    'dtl_mr_qty_received' => 0
                ]);
            }

            return response()->json($materialreq, 201);
        });
    }


    public function show($id)
    {
        $mr = MaterialRequestModel::with('pic')->findOrFail($id);
        return response()->json($mr);
    }

    public function getOpenMR()
    {
        $data = MaterialRequestModel::where('mr_status', 'open')
            ->orderBy('created_at', 'desc')
            ->get(['mr_id', 'mr_kode']);

        return response()->json($data);
    }


    public function showKode($kode)
    {
        $mr = MaterialRequestModel::with('details')
            ->where('mr_kode', $kode)
            ->firstOrFail();

        return response()->json($mr);
    }   

    public function generateKode(Request $request)
    {
        $lokasiNama = strtoupper(trim($request->lokasi)); 
        $lokasiKode = $this->getLokasiKode($lokasiNama);  
        $tahunBulan = now()->format('y/m');

        $last = MaterialRequestModel::where('mr_lokasi', $lokasiNama)
            ->where('mr_kode', 'like', "%/$tahunBulan/%")
            ->orderBy('mr_id', 'desc')
            ->first();

        $nextNumber = $last
            ? ((int) substr($last->mr_kode, -5)) + 1
            : 1;

        $kode = sprintf(
            "GMI/%s/%s/%05d",
            $lokasiKode, 
            $tahunBulan,
            $nextNumber
        );

        return response()->json($kode);
    }


}
