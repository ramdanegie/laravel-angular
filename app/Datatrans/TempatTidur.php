<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 3/11/2020
 * Time: 12:54 PM
 */

namespace App\Datatrans;

class TempatTidur extends Datatrans
{
    protected $table = 'tempattidur_m';
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "id";
}