<?php
namespace App\Master;

class AsuransiPasien extends MasterModel
{
    protected $table ="asuransipasien_m";
    protected $fillable = [];
    public $timestamps = false;

//    public function __construct(){$this->setTransformerPath('App\Transformers\Master\KategoryAccountTransformer');}
    public function rekanan(){
        return $this->belongsTo('App\Master\Rekanan', 'kdinstitusiasal');
    }
}
