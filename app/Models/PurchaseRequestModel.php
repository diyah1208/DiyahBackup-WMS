<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserModel; 


class PurchaseRequestModel extends Model
{
    protected $table = 'tb_purchase_request';
    protected $primaryKey = 'pr_id';

    protected $fillable = [
        'pr_kode',
        'pr_lokasi',
        'pr_pic_id',
        'pr_tanggal',
        'pr_status',
    ];

    // RELASI KE USER (PIC)
    public function pic()
    {
        return $this->belongsTo(UserModel::class, 'pr_pic_id', 'id');
    }
    public function details()
    {
        return $this->hasMany(PurchaseRequestItemModel ::class, 'pr_id', 'pr_id');
    }
}
