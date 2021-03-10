<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/9/2019
 * Time: 4:25 PM
 */
namespace App\Transaksi;

class InformasiTanggungan extends Transaksi
{
    protected $table = "informasitanggungansementara_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}