<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDetailModel extends Model
{
    protected $table = 'dtl_delivery';
    protected $primaryKey = 'dtl_dlv_id';

    protected $fillable = [
        'dlv_id',
        'part_id',
        'dtl_dlv_part_number',
        'dtl_dlv_part_name',
        'dtl_dlv_satuan',
        'qty_delivered',
        'qty_on_delivery',
        'qty_pending',
        'created_at',
        'updated_at',
    ];

    public function delivery()
    {
        return $this->belongsTo(DeliveryModel::class, 'dlv_id', 'dlv_id');
    }
    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'part_id', 'part_id');
    }
}
