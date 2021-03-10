<?php
/**
 * Created by PhpStorm.
 * User: asepic
 * Date: 18/12/2018
 * Time: 12.51
 */
namespace App\Transaksi;

class TenagaPengajar extends Transaksi
{
    protected $table = 'tenagapengajar_m';
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = 'norec';
}