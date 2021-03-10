<?php

namespace App\Master;

//use Illuminate\Database\Eloquent\Model;

class MapProdukToTindakanPelayanan extends MasterModel
{
    protected $table = 'mapproduktotindakanpelayanan_m';
    protected $primaryKey = 'norec';

    protected $fillable = [];
    public $timestamps = false;

//    public function __construct(){$this->setTransformerPath('App\Transformers\Master\RuanganTransformer');}
}
