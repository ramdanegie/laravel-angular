<?php

namespace App\Transaksi;


class SurveilansFrd extends Transaksi
{
    protected $table = 'surveilansfrd_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}