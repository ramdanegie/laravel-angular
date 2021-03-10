<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 31/07/2018
 * Time: 14.58
 */
namespace App\Transaksi;
class AturanPeminjaman extends Transaksi
{
    protected $table ="sdm_aturanpeminjaman_t";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";


}