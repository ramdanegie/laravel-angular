<?php

namespace App\Master;


class CaraBayar extends MasterModel
{
    protected $table ="carabayar_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\CaraBayarTransformer');}

}
