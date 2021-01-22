<?php
/**
 * Created by PhpStorm.
 * User: er@epic
 * Date: 3/11/2020
 * Time: 10:27 AM
 */


namespace App\Datatrans;

class Ruangan extends Datatrans
{
    protected $table = "ruangan_m";
    protected $fillable = [];
    public $timestamps = false;
//    public $incrementing = false;
   protected $primaryKey = "id";
}