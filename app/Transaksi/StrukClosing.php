<?php

namespace App\Transaksi;

class StrukClosing extends Transaksi
{
    protected $table ="strukclosing_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public function pegawai(){
        return $this->belongsTo('App\Master\Pegawai','objectpegawaidiclosefk');
    }

}
