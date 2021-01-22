<?php
/**
 * Created by PhpStorm.
 * User: er@epic
 * Date: 3/18/2020
 * Time: 12:25 PM
 */
namespace App\Datatrans;

class PelayananRujukan extends Datatrans
{
    protected $table = "pelayananrujukan_t";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";
    protected $keyType = "string";
}