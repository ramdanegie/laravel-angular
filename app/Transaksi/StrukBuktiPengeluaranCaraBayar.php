<?php

namespace App\Transaksi;

class StrukBuktiPengeluaranCaraBayar extends Transaksi
{
    protected $table ="strukbuktipengeluarancarabayar_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public function cara_bayar(){
        return $this->belongsTo('App\Master\CaraBayar', 'carabayarfk');
    }

    public function SBM(){
        return $this->belongsTo('App\Transaksi\StrukBuktiPenerimaan', 'nosbkfk');
    }

    public function jenis_kartu(){
        return $this->belongsTo('App\Master\JenisKartu', 'jeniskartufk');
    }
}
