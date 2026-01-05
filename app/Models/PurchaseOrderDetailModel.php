<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetailModel extends Model
{
    protected $table = 'dtl_purchase_order';
    protected $primaryKey = 'dtl_po_id';

    protected $fillable = [
        'po_id',
        'part_id',
        'dtl_po_part_number',
        'dtl_po_part_name',
        'dtl_po_satuan',
        'dtl_po_qty',
        'dtl_qty_received',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrderModel::class,
            'po_id',
            'po_id'
        );
    }

    public function part()
    {
        return $this->belongsTo(
            BarangModel::class,
            'part_id',
            'part_id'
        );
    }
}