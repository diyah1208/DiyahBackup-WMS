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
        'pr_tanggal',
        'pr_status',
        'pr_pic'
    ];

    // RELASI KE USER (PIC)
    public function details()
    {
        return $this->hasMany(PurchaseRequestItemModel ::class, 'pr_id', 'pr_id');
    }
}
