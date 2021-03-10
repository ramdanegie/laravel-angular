<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 13/03/2019
 * Time: 09.41
 */



namespace App\Transaksi;

class EMROdontogram extends Transaksi
{
    protected $table ="emrodontogram_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}