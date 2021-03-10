<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 9/26/2017
 * Time: 1:51 AM
 */


namespace App\Transaksi;

class BridgingMiniR45 extends Transaksi
{
    protected $table ="bridgingminir45";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}