<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 02/04/2019
 * Time: 16.21
 */
//MapJenisPaguToPegawai


namespace App\Transaksi;

class MapJenisPaguToPegawai extends Transaksi
{
    protected $table = 'mapjenispagutopegawai_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}