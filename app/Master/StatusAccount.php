<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class StatusAccount extends MasterModel
{
    protected $table ="statusaccount_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\StatusAccountTransformer');}

    public function chart_of_account(){
        return $this->hasMany('App\Master\ChartOfAccount', 'objectstatusaccountfk');
    }

    public function jenis_account(){
        return $this->belongsTo('App\Master\JenisAccount', 'objectjenisaccountfk');
    }
}
