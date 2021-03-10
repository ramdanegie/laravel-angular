<?php
namespace App\Master;

class KelompokPasien extends MasterModel
{
    protected $table ="kelompokpasien_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\KelompokPasienTransformer');}

}
