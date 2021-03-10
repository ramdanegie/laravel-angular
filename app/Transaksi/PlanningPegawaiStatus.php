<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/10/2019
 * Time: 10:30 PM
 */
namespace App\Transaksi;

class PlanningPegawaiStatus extends Transaksi
{
    protected $table = "planningpegawaistatus_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
