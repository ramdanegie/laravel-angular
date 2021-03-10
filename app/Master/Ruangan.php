<?php

namespace App\Master;

//use Illuminate\Database\Eloquent\Model;

class Ruangan extends MasterModel
{
    protected $table = 'ruangan_m';
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\RuanganTransformer');}

    public function departemen(){
        return $this->belongsTo('App\Master\Departemen', 'objectdepartemenfk');
    }

    public function kelas()
    {
        return $this->belongsToMany('Kelas', 'mapruangantokelas_m', 'objectruangafk', 'objectkelasfk');
    }

    public function periode_account(){
        return $this->hasMany('App\Transaksi\PeriodeAccount', 'objectruangafk');
    }

//    public function map_to_ruangan()
//    {
//        return $this->hasManyThrough('App\Master\Produk', 'App\Master\MapRuanganToProduk', 'noregistrasifk', 'noregistrasifk', 'norec');
//    }
}
