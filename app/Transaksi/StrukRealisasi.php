<?php
namespace App\Transaksi;

class StrukRealisasi extends Transaksi
{
    protected $table = "strukrealisasi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public function order_pelayanan(){
        return $this->hasMany('App\Transaksi\OrderPelayanan', 'noorderfk');
    }
}