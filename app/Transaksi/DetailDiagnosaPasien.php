<?php
/**
 * Created by PhpStorm.
 * User: Giw
 * Date: 12/12/2017
 * Time: 10:31 AM
 */

namespace App\Transaksi;

use App\Master\SettingDataFixed;
class DetailDiagnosaPasien extends Transaksi
{
    protected $table ="detaildiagnosapasien_t";
    protected $fillable = ['objectdiagnosafk'];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public  function antrianpasiendiperiksa(){
        return $this->belongsTo('App\Transaksi\AntrianPasienDiperiksa', 'noregistrasifk');
    }
    public  function diagnosa(){
        return $this->belongsTo('App\Master\Diagnosa', 'objectdiagnosafk');
    }
    public  function jenisdiagnosa(){
        return $this->belongsTo('App\Master\JenisDiagnosa', 'objectjenisdiagnosafk');
    }
    public  function diagnosapasien(){
        return $this->belongsTo('App\Transaksi\DiagnosaPasien', 'objectdiagnosapasienfk');
    }
}
