<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\MasterModel;

class JenisAccount extends MasterModel
{
    protected $table ="jenisaccount_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\JenisAccountTransformer');}

    public function chart_of_account(){
        return $this->hasMany('App\Master\ChartOfAccount', 'objectjenisaccountfk');
    }

    public function periode_account(){
        return $this->hasMany('App\Transaksi\PeriodeAccount', 'objectjenisaccountfk');
    }

    public function kategory_account(){
        return $this->hasMany('App\Master\KategoryAccount', 'objectjenisaccountfk');
    }

    public function status_account(){
        return $this->hasMany('App\Master\StatusAccount', 'objectjenisaccountfk');
    }

    public function struktur_account(){
        return $this->hasMany('App\Master\StrukturAccount', 'objectjenisaccountfk');
    }
}
