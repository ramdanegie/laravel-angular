<?php

namespace App\Datatrans;

class EECG extends Datatrans
{
    protected $table ="eecg_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
