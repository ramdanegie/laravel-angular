<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class LoginUser extends MasterModel
{
    protected $table ="loginuser_s";
    protected $fillable = [];

    public function pegawai(){
        return $this->belongsTo('App\Master\Pegawai', 'objectpegawaifk');
    }
}
