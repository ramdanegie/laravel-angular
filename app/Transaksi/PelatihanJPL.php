<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/12/2018
 * Time: 09.47
 */


namespace App\Transaksi;

class PelatihanJPL extends Transaksi
{
    protected $table = "pelatihanjpl_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}

