<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 24/01/2019
 * Time: 13.37
 */

namespace App\Transaksi;

class EMRPasien extends Transaksi
{
    protected $table ="emrpasien_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}