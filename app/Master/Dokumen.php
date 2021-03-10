<?php
namespace App\Master;

class Dokumen extends MasterModel
{
    protected $table ="dokumen_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\DokumenTransformer');}

}