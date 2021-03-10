<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 17/12/2018
 * Time: 17.35
 */
namespace App\Transaksi;

class InsidenKeselamatanPasien extends Transaksi
{
    protected $table = "insidenkeselamatanpasien_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}