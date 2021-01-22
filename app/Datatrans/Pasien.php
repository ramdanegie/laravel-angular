<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 3/11/2020
 * Time: 10:27 AM
 */


namespace App\Datatrans;

class Pasien extends Datatrans
{
    protected $table = "pasien_m";
    protected $fillable = [];
    public $timestamps = false;
//    public $incrementing = false;
//    protected $primaryKey = "id";
}