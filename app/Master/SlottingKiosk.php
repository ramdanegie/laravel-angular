<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie RAmdan
 * Date: 20/05/2019
 * Time: 15:52
 */
namespace App\Master;

class SlottingKiosk extends MasterModel
{
    protected $table = "slottingkiosk_m";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "id";
}