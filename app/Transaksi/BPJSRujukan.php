<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/09/2018
 * Time: 10.10
 */

namespace App\Transaksi;

class BPJSRujukan extends Transaksi
{
    protected $table ="bpjsrujukan_t";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";


}