<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 04/02/2019
 * Time: 11.50
 */

namespace App\Master;

class MapRuanganToJenisRuangan extends MasterModel
{
    protected $table = 'mapruangantojenisruangan_m';
    protected $primaryKey = 'id';

    protected $fillable = [];
    public $timestamps = false;

}
