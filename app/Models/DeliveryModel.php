<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryModel extends Model
{
    protected $table = 'tb_delivery';
    protected $primaryKey = 'dlv_id';

    protected $fillable = [
        'dlv_kode',
        'mr_id',
        'dlv_dari_gudang',
        'dlv_ke_gudang',
        'dlv_ekspedisi',
        'dlv_jumlah_koli',
        'dlv_no_resi',
        'dlv_pic',
        'dlv_status',
        'created_at',
        'updated_at',
    ];
    
    public function mr()
    {
        return $this->belongsTo(MaterialRequestModel::class, 'mr_id', 'mr_id');
    }
    public function details()
    {
        return $this->hasMany(DeliveryDetailModel::class, 'dlv_id', 'dlv_id');
    }
}
