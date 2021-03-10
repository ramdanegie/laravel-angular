<?php
/**
 * Created by Visual Studio lah.
 * User: 2+2=5
 * Date: 09/04/2018
 * Time: 09.35
 */


namespace App\Transaksi;

class RujukanKeluar extends Transaksi
{
    protected $table = "rujukankeluar_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}


