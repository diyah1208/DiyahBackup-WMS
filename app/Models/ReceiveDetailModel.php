<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiveDetailModel extends Model
{
    protected $table = 'dtl_receive_item';
    protected $primaryKey = 'dtl_ri_id';

    protected $fillable = [
        'ri_id',
        'part_id',
        'dtl_ri_part_number',
        'dtl_ri_part_name',
        'dtl_ri_satuan',
        'dtl_ri_qty',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'part_id', 'part_id');
    }
    public function receive()
    {
        return $this->belongsTo(ReceiveModel::class, 'ri_id', 'ri_id');
    }
}
