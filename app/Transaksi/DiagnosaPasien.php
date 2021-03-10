<?php
/**
 * Created by PhpStorm.
 * User: Giw
 * Date: 12/12/2017
 * Time: 10:31 AM
 */
namespace App\Transaksi;

use App\Master\SettingDataFixed;
class DiagnosaPasien extends Transaksi
{
    protected $table ="diagnosapasien_t";
    protected $fillable = ['tglpendaftaran'];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public  function antrianpasiendiperiksa(){
        return $this->belongsTo('App\Transaksi\AntrianPasienDiperiksa', 'noregistrasifk');
    }

    public  function detaildiagnosapasien(){
        return $this->belongsTo('App\Transaksi\DetailDiagnosaPasien', 'norec');
    }

}
