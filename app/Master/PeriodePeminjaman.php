<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 31/07/2018
 * Time: 17.30
 */

namespace App\Master;

class PeriodePeminjaman extends MasterModel
{
    protected $table ="sdm_periodepinjaman_m";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "id";


}