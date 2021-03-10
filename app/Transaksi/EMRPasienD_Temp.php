<?php
namespace App\Transaksi;

class EMRPasienD_Temp extends Transaksi
{
    protected $table ="emrpasiend_temp_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}