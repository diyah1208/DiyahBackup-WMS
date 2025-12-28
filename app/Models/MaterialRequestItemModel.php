<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequestItemModel extends Model
{
    protected $table = 'dtl_material_request';
    protected $primaryKey = 'dtl_mr_id';

    protected $fillable = [
        'mr_id',
        'part_id',
        'dtl_mr_part_number',
        'dtl_mr_part_name',
        'dtl_mr_satuan',
        'dtl_mr_prioritas',
        'dtl_mr_qty_request',
        'dtl_mr_qty_received',
    ];

    public function materialRequest()
    {
        return $this->belongsTo(
            MaterialRequestModel::class,
            'mr_id',
            'mr_id'
        );
    }
    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'part_id', 'part_id');
    }
}
