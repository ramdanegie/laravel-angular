<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 19/12/2018
 * Time: 18.15
 */

namespace App\Transaksi;

class IndikatorPasienJatuh extends Transaksi
{
    protected $table ="indikatorpasienjatuh_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
