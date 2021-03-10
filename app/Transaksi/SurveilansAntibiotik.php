<?php

namespace App\Transaksi;


class SurveilansAntibiotik extends Transaksi
{
    protected $table = 'surveilansantibiotik_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}