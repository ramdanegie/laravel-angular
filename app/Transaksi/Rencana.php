<?php
/**
 * Created by PhpStorm.
 * User: egie Ramdan
 * Date: 23/10/2018
 * Time: 12:28
 */

namespace App\Transaksi;

class Rencana extends Transaksi
{
    protected $table = "rencana_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}