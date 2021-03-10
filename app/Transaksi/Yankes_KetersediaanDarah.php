<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 21/01/2019
 * Time: 12.09
 */

namespace App\Transaksi;

class Yankes_KetersediaanDarah extends Transaksi
{
    protected $table = "yankes_ketersediaandarah_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}