<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 21/01/2019
 * Time: 15.18
 */
namespace App\Transaksi;

class Yankes_MutuPelayanan extends Transaksi
{
    protected $table = "yankes_mutupelayanan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
