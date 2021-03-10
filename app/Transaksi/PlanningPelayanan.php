<?php

namespace App\Transaksi;

use Date;
class PlanningPelayanan extends Transaksi
{
    protected $table = 'planningpelayanan_t';
    protected $fillable =  [];
    public $timestamps = false;

    //accessor
    public function getTglkalibrasiAttribute($value){
        if($value!=null){
            return Date::parse($value)->format('d/m/Y');
        }
    }

    public function getTglkontrakserviceAttribute($value){
        if($value!=null){
            return Date::parse($value)->format('d/m/Y');

        }
    }

    public function getTglpemeliharaanAttribute($value){
        if($value!=null){
            return Date::parse($value)->format('d/m/Y');
        }
    }


    //mutator
    public function setTglkalibrasiAttribute($value){
        if($value!=null){
            $date = Date::createFromFormat('d/m/Y', $value);
            $this->attributes['tglkalibrasi'] = $date->toDateString('Y-m-d');
        }
    }

    public function setTglkontrakserviceAttribute($value){
        if($value!=null){
            $date = Date::createFromFormat('d/m/Y', $value);
            $this->attributes['tglkontrakservice'] = $date->toDateString('Y-m-d');
        }
    }

    public function setTglpemeliharaanAttribute($value){
        if($value!=null){
            $date = Date::createFromFormat('d/m/Y', $value);
            $this->attributes['tglpemeliharaan'] = $date->toDateString('Y-m-d');
        }
    }

    //relation
    public function rekanan(){
    	return $this->belongsTo('App\Master\Rekanan', 'objectrekananfk');
    }

    public function pegawai(){
        return $this->belongsTo('App\Master\Pegawai', 'objectpegawaifk');
    }
}
