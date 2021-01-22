<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 4/8/2020
 * Time: 4:28 PM
 */

namespace App\Datatrans;


class LoginUser extends Datatrans
{
    protected $table = 'loginuser_s';
    protected $fillable = [];
    public $timestamps = false;
}
