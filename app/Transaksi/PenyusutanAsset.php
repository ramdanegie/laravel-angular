<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 12/4/2019
 * Time: 3:16 AM
 */

namespace App\Transaksi;

use App\BaseModel;

class PenyusutanAsset extends Transaksi
{
    protected $table = "penyusutanasset_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}