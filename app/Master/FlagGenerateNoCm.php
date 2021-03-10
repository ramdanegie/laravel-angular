<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 09/04/2018
 * Time: 12.33
 */

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class FlagGenerateNoCm extends MasterModel
{
    protected $table ="generate_nocm";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "id";

}