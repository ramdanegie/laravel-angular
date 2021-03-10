<?php
/**
 * Created by PhpStorm.
 * User: GIW
 * Date: 11/20/2019
 * Time: 6:43 PM
 */
namespace App\Transaksi;

class PencucianLinen extends Transaksi
{
    protected $table = "pencucianlinen_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}