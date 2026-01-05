<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderModel extends Model
{
    protected $table = 'tb_purchase_order';
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'po_kode',
        'pr_id',
        'po_tanggal',
        'po_estimasi',
        'po_keterangan',
        'po_status',
        'po_pic',
    ];

    //FK
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequestModel::class, 'pr_id', 'pr_id');
    }
    public function details()
    {
        return $this->hasMany(PurchaseOrderDetailModel::class, 'po_id', 'po_id');
    }
    
}