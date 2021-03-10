<?php

namespace App\Transaksi;


class PeriodeAccount extends Transaksi
{
    protected $table ="periodeaccount_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\PeriodeAccountTransformer');}

    public function periode_account_saldo(){
        return $this->hasMany('App\Transaksi\PeriodeAccountSaldo', 'kdperiodeaccount', 'kdperiodeaccount');
    }

    public  function ruangan(){
        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk');
    }

    public function jenis_account(){
        return $this->belongsTo('App\Master\JenisAccount', 'objectjenisaccountfk');
    }


}
