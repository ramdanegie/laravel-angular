<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 9/30/2019
 * Time: 9:48 AM
 */

namespace App\Http\Controllers\DesktopService;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;

use App\Transaksi\StrukOrder;
use App\Transaksi\StrukResep;
use App\Transaksi\OrderPelayanan;
use App\Master\LoginUser;



class DesktopServiceController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getDataDisplayAntrian(Request $request) {
        $tglAwal =  date('Y-m-d 00:00:00');;
        $tglAkhir =  date('Y-m-d 23:59:59');
        $data = DB::select(DB::raw("
                     select * from antrianpasienregistrasi_t  
                     where statuspanggil ='1' 
                     and tanggalreservasi between '$tglAwal' and '$tglAkhir'
              ")
        );
//        $RS = $data->pluck('tempatlahir','jenis','noantrian')->all();
        $RS = [];
        foreach ($data as $item){
            $RS[] = array(
                $item->tempatlahir,
                $item->jenis,
                $item->noantrian,
            );
        }
        $kolomfield = DB::select(DB::raw("
                  SELECT *
                  FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_NAME  ='antrianpasienregistrasi_t';
              ")
        );
        $fields= [];
//        foreach ($kolomfield as $item){
//            $fields[] = array(
//                "Name"=> $item->COLUMN_NAME,
//                "Type"=> "TEXT",//gettype($item->TABLE_NAME),
//                "PrimaryKey"=> false,
//                "Nullable"=> true,
//                "DefaultValue"=> null
//            );
//        }
        $fields= array(
            array(
                "Name"=> "tempatlahir",
                "Type"=> "TEXT",
                "PrimaryKey"=> false,
                "Nullable"=> true,
                "DefaultValue"=> null
            ),
            array(
                "Name"=> "jenis",
                "Type"=> "TEXT",
                "PrimaryKey"=> false,
                "Nullable"=> true,
                "DefaultValue"=> null
            ),
            array(
                "Name"=> "noantrian",
                "Type"=> "TEXT",
                "PrimaryKey"=> false,
                "Nullable"=> true,
                "DefaultValue"=> null
            )
        );

        $result = array(
            "RecordCount" => count($data),
            "Fields" => $fields,
            "RowsCols" => $RS,
            "message" => "as@epic",
        );

        return $this->respond($result);
    }
    public function getDataForRecordSet(Request $request) {
        $strSql = $request['strsql'];
        $data = DB::select(DB::raw("
                     $strSql
              ")
        );

        $result = array(
            "RecordCount" => count($data),
            "data" => $data,
            "message" => "as@epic",
        );

        return $this->respond($result);
    }
    public function saveDataFromRecordSet(Request $request) {
        $strSql = $request['strsql'];
        $data = DB::update(DB::raw("
                     $strSql
              ")
        );

        $result = array(
            "RecordCount" => count($data),
            "data" => $data,
            "message" => "as@epic",
        );

        return $this->respond($result);
    }
}