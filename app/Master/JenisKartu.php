<?php

namespace App\Master;


class JenisKartu extends MasterModel
{
    protected $table ="jeniskartu_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\JenisKartuTransformer');}

}
