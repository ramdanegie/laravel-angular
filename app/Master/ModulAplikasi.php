<?php

namespace App\Master;
use App\Http\Controllers\Master;
use App\Master\MasterModel;
use App\Master\Pegawai;

class ModulAplikasi extends MasterModel
{
    protected $table = "modulaplikasi_s";
    protected $fillable = [];
    public $timestamps = false;
}
