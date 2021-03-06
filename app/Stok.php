<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stok extends Model
{
    protected $table = 'stok';
    protected $fillable = ['dc_id', 'tanggal_stok', 'keterangan', 'produk_id', 'qty_stok'];

    public $timestamps = true;
    use SoftDeletes;

    public function dc() {
    	return $this->belongsTo('App\DC');
    }

    public function produk() {
    	return $this->belongsTo('App\Produk');
    }
}
