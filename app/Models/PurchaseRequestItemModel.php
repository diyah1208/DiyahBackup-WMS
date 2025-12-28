<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItemModel extends Model
{
    protected $table = 'dtl_purchase_request';
    protected $primaryKey = 'dtl_pr_id';
    public $timestamps = true;

    protected $fillable = [
        'pr_id',
        'mr_id',
        'dtl_pr_kode_mr',
        'dtl_pr_part_number',
        'dtl_pr_part_name',
        'dtl_pr_satuan',
        'dtl_pr_qty',
    ];

    public function pr()
    {
        return $this->belongsTo(
            PurchaseRequestModel::class,
            'pr_id',
            'pr_id'
        );
    }

    public function mr()
    {
        return $this->belongsTo(
            MaterialRequestModel::class, 
            'mr_id',
            'mr_id'
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