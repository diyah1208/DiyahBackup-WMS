<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiveModel extends Model
{
    protected $table = 'tb_receive_item';
    protected $primaryKey = 'ri_id';

    protected $fillable = [
        'ri_kode',
        'po_id',
        'ri_lokasi',
        'ri_tanggal',
        'ri_keterangan',
        'ri_pic',
    ];

     public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrderModel::class, 'po_id', 'po_id');
    }
    public function details()
    {
        return $this->hasMany(ReceiveDetailModel::class, 'ri_id', 'ri_id');
    }
}
