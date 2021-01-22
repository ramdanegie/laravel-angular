<?php
/**
 * Created by PhpStorm.
 * User: er@epic
 * Date: 3/11/2020
 * Time: 10:27 AM
 */


namespace App\Datatrans;

class Profile extends Datatrans
{
    protected $table = "profile_m";
    protected $fillable = [];
    public $timestamps = false;
//    public $incrementing = false;
   protected $primaryKey = "id";
}