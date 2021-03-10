<?php

namespace App\Transaksi;


class SurveilansFaktorResiko extends Transaksi
{
    protected $table = 'surveilansfaktorresiko_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}