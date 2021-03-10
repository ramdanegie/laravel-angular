<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 17/12/2018
 * Time: 17.35
 */
namespace App\Transaksi;

class LembarKerjaInvestigasi extends Transaksi
{
    protected $table = "lembarkerjainvestigasi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}