<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class StrukturAccount extends MasterModel
{
    protected $table ="strukturaccount_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\StrukturAccountTransformer');}

    public function chart_of_account(){
        return $this->hasMany('App\Master\ChartOfAccount', 'objectstrukturaccountfk');
    }

//    public function jenis_account(){
//        return $this->hasMany('App\Master\JenisAccount', 'objectjenisaccountfk');
//    }
}
