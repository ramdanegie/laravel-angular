<?php
/**
 * Created by PhpStorm.
 * User: asepic
 * Date: 17/12/2018
 * Time: 18.57
 */

namespace App\Transaksi;

class PesertaDidik extends Transaksi
{
    protected $table = 'sdm_pesertadidik_t';
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = 'norec';
}
