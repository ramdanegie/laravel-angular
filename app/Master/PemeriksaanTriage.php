<?php
/**
 * Created by PhpStorm.
 * User: Ecep
 * Date: 23/10/2018
 * Time: 20:12
 */

namespace App\Master;


class PemeriksaanTriage extends MasterModel
{
    protected $table ="rm_pemeriksaantriage_m";
    protected $fillable = [];

    public $timestamps = false;
    protected $primaryKey = "id";

}