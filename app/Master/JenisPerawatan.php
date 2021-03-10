<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class JenisPerawatan extends MasterModel
{
    protected $table ="jenisperawatan_m";
    protected $fillable = [];

    public function jenis_perawatan(){
        return $this->hasMany('App\Master\JenisPerawatan', 'objectjenisperawatanfk');
    }
}
