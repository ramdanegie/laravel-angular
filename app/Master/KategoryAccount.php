<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class KategoryAccount extends MasterModel
{
    protected $table ="kategoryaccount_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\KategoryAccountTransformer');}

    public function chart_of_account(){
        return $this->hasMany('App\Master\ChartOfAccount', 'objectkategoryaccountfk');
    }


    public function jenis_account(){
      return $this->belongsTo('App\Master\JenisAccount', 'objectjenisaccountfk');
    }
}
