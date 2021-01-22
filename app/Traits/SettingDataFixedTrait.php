<?php
namespace App\Traits;

use App\Master\SettingDataFixed;

Trait SettingDataFixedTrait
{
  
  protected $jabatanTeknisiID=null;

  /**
   * @return mixed
   */
  protected function getJabatanTeknisiID()
  {
    if($this->jabatanTeknisiID==null){
      $set = SettingDataFixed::where('namafield', 'IDPEGAWAITEKNISI')->first();
      $this->jabatanTeknisiID= ($set) ? (int)$set->nilaifield: 0;
    }
    return $this->jabatanTeknisiID;
  }

  /**
   * @param null $kdJabatanTeknisi
   */
  protected function setJabatanTeknisiID($jabatanTeknisiID)
  {
    $this->jabatanTeknisiID = $jabatanTeknisiID;
  }

  protected function getRekananIdJaminanPasien(){
      $set = SettingDataFixed::where('namafield', 'kdJenisRekananPenjaminPasien')->first();
      $this->jenisrekananID= ($set) ? (int)$set->nilaifield: null;
      return $this->jenisrekananID;
  }

    protected function getKelompokPasienPerjanjian(){
        $set = SettingDataFixed::where('namafield', 'idJenisPasienPerjanjian')->first();
        $this->id= ($set) ? (int)$set->nilaifield: null;
        return $this->id;
    }

    protected function  getKdTransaksiNonLayanan(){
        $set = SettingDataFixed::where('namafield', 'kdTransaksiNonLayanan')->first();
        if($set){
            return $set->nilaifield;
        }else{
            return null;
        }
    }


    protected function getGlobalSettingDataFixed($stringCode){
        $set = SettingDataFixed::where('namafield', $stringCode)->first();
        if($set){
            return $set->nilaifield;
        }else{
            return null;
        }
    }




  

}