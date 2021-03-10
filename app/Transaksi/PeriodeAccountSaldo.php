<?php

namespace App\Transaksi;


class PeriodeAccountSaldo extends Transaksi
{
    protected $table ="periodeaccountsaldo_t";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";

    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\PeriodeAccountSaldoTransformer');}

    public function periode_account(){
        return $this->belongsTo('App\Transaksi\PeriodeAccount', 'kdperiodeaccount', 'kdperiodeaccount');
    }

}
