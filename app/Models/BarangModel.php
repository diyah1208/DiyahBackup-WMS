<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangModel extends Model
{
    protected $table = 'tb_barang';
    protected $primaryKey = 'part_id';

    protected $fillable = [
        'part_number',
        'part_name',
        'part_satuan',
    ];


     public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'part_id', 'part_id');
    }
}
