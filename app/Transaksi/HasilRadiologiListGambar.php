<?php
/**
 * Created by PhpStorm.
 * User: Khrisnanda
 * Date: 05/11/2019
 * Time: 16:36
 */
namespace App\Transaksi;

class HasilRadiologiListGambar extends Transaksi
{
    protected $table = "hasilradiologilistgambar_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}