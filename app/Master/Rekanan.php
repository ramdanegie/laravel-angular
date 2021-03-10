<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class Rekanan extends MasterModel
{
    protected $table ="rekanan_m";
    protected $fillable = [];
    protected $primaryKey = "norec";
    public $timestamps = false;

    public function planning_pelayanan(){
        return $this->hasMany('App\Transaksi\PlanningPelayanan', 'objectrekananfk');
    }
}
