<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 10/9/2019
 * Time: 3:10 PM
 */

namespace App\Http\Controllers\Bridging;

use App\Http\Controllers\ApiController;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\BPJSRujukan;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\BPJSKlaimTxt;
use App\Transaksi\BPJSGagalKlaimTxt;
use App\Transaksi\PemakaianAsuransi;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\TempBilling;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Traits\Valet;
use Carbon\Carbon;

class BridgingDukcapilController extends ApiController
{

    use Valet, PelayananPasienTrait;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getIdentitasByNIK($nik) {
        $url = $this->settingDataFixed('urlApiDukcapil',$kdProfile);
        $user_id = $this->settingDataFixed('userDukcapil',$kdProfile);
        $password = $this->settingDataFixed('passwordDukcapil',$kdProfile);
        if($url ==  null  || $user_id ==  null || $password == null){
            $result = array(
              'message' => 'Setting Data Fixed Dukcapil Kosong'
            );
            return $this->respond($result);
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
//            CURLOPT_PORT => ,
            CURLOPT_URL=> $url."?user_id=".$user_id."&password=".$password."&NIK=".$nik,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $this->setStatusCode(400)->respond('', "Proses Bridging Ke Ducapil Error #:" . $err);
            $result= "Proses Bridging Ke Ducapil Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getNIKwilayahProv ($nik, Request $r) {
        $kdProfile = $this->getDataKdProfile($r);
        $url = $this->settingDataFixed('urlApiDukcapil',$kdProfile);
        $user_id = $this->settingDataFixed('userDukcapil',$kdProfile);
        $password = $this->settingDataFixed('passwordDukcapil',$kdProfile);

        if($url ==  null  || $user_id ==  null || $password == null){
            $result = array(
                'message' => 'Setting Data Fixed Dukcapil Kosong'
            );
            return $this->respond($result);
        }

        $now = date('dmY');
        $pass = md5($password.$now);

        $curl = curl_init();
        $url = $url.'?USER_ID='.$user_id.'&PASSWORD='.$pass.
            '&NIK='.$nik;

        curl_setopt_array($curl, array(
//            CURLOPT_PORT => ,
            CURLOPT_URL=>$url,// $url."?user_id=".$user_id."&password=".$password."&NIK=".$nik,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $this->setStatusCode(400)->respond('', "Proses Bridging Ke Ducapil Error #:" . $err);

        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getNikNasional($nik, Request $r) {
     
        $kdProfile = $this->getDataKdProfile($r);
        $data = array(
            "nik" =>$nik,
            "user_id" => $this->settingDataFixed('userIdNikNasional',$kdProfile), 
            "password"=> $this->settingDataFixed('passNikNasional',$kdProfile), 
            "ip_user"=>$this->settingDataFixed('ipServer',$kdProfile),     
          ); 
        $dataJsonSend = json_encode($data);
           // return $dataJsonSend;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          // CURLOPT_PORT => $request['port'],
          CURLOPT_URL => $this->settingDataFixed('urlGetNikNasional',$kdProfile),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $dataJsonSend,
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            // "postman-token: f5103086-f421-0f8f-5270-f7e11de81651"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

       if ($err) {
            return $this->setStatusCode(400)->respond('', "Proses Bridging Ke Ducapil Error #:" . $err);

        } else {
            $result = (array) json_decode($response);
        }
         return $this->respond($result);

    }
}