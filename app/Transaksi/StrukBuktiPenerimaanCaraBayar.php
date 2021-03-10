<?php

namespace App\Transaksi;

class StrukBuktiPenerimaanCaraBayar extends Transaksi
{
    protected $table ="strukbuktipenerimaancarabayar_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public function cara_bayar(){
        return $this->belongsTo('App\Master\CaraBayar', 'objectcarabayarfk');
    }

    public function SBM(){
        return $this->belongsTo('App\Transaksi\StrukBuktiPenerimaan', 'nosbmfk');
    }

    public function jenis_kartu(){
        return $this->belongsTo('App\Master\JenisKartu', 'objectjeniskartufk');
    }
}
