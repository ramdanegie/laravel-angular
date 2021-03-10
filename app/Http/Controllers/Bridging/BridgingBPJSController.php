<?php
/**
 * Created by PhpStorm.
 * User: egie ramdan
 * Date: 31/01/2018
 * Time: 10.05
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
use Webpatser\Uuid\Uuid;

class BridgingBPJSController extends ApiController
{

    use Valet, PelayananPasienTrait;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getSignature(Request $request) {
        $data = $request['consid'];
        $secretKey = $request['secretkey'];
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        // urlencode…
        // $encodedSignature = urlencode($encodedSignature);

//        echo "X-cons-id: " .$data ." ";
//        echo "X-timestamp:" .$tStamp ." ";
//        echo "X-signature: " .$encodedSignature;

        $result = array(
            "X-cons-id" =>  $data ,
            "X-timestamp" => $tStamp,
            "X-signature" => $encodedSignature,
        );



        return $this->respond($result);
    }
    public function getNoPeserta(Request $request) {
        $data = $this->getIdConsumerBPJS();
//        return $data;
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        // $res['id'] =$data;
        // $res['url']=$this->getUrlBrigdingBPJS();
        // $res['pass']=$secretKey;
        // return $this->respond($res);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            // CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Peserta/nokartu/".$request['nokartu']."/tglSEP/".$request['tglsep'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nokartu/".$request['nokartu']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }


    public function getNIK(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }

    public function insertSEP(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
                CURLOPT_URL=> $this->getUrlBrigdingBPJS()."SEP/insert",
//                CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/insert",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>
                    "{\r\n\"request\": 
                         {\r\n\"t_sep\": 
                            {
                            \r\n\"noKartu\": \"".$request['nokartu']."\",
                            \r\n\"tglSep\": \"".$request['tglsep']."\",
                            \r\n\"ppkPelayanan\": \"0904R004\",
                            \r\n\"jnsPelayanan\": \"".$request['jenispelayanan']."\",
                            \r\n\"klsRawat\": \"".$request['kelasrawat']."\",
                            \r\n\"noMR\": \"".$request['nomr']."\",
                            \r\n\"rujukan\": {\r\n\"asalRujukan\": \"".$request['asalrujukan']."\",
                                            \r\n\"tglRujukan\": \"".$request['tglrujukan']."\",
                                            \r\n\"noRujukan\": \"".$request['norujukan']."\",
                                            \r\n\"ppkRujukan\": \"".$request['ppkrujukan']."\"\r\n},
                            \r\n\"catatan\": \"".$request['catatan']."\",
                            \r\n\"diagAwal\": \"".$request['diagnosaawal']."\",
                            \r\n\"poli\": {\r\n\"tujuan\": \"".$request['politujuan']."\",
                                         \r\n\"eksekutif\": \"".$request['eksekutif']."\"\r\n},
                            \r\n\"cob\": 
                                        {\r\n\"cob\": \"".$request['cob']."\"\r\n},
                            \r\n\"jaminan\": {\r\n\"lakaLantas\": \"".$request['lakalantas']."\",
                                                \r\n\"penjamin\": \"".$request['penjamin']."\",
                                                \r\n\"lokasiLaka\": \"".$request['lokasilaka']."\"\r\n},
                            \r\n\"noTelp\": \"".$request['notelp']."\",
                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
                            
                      }",
               
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: Application/x-www-form-urlencoded",
                    "X-cons-id: ".  (string)$data,
                    "X-signature: ". (string)$encodedSignature,
                    "X-timestamp: ". (string)$tStamp
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $result= "cURL Error #:" . $err;
            } else {
                $result = (array) json_decode($response);
            }
        return $this->respond($result);
       
    }

    public function cekSep(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."SEP/".$request['nosep'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/".$request['nosep'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: Application/x-www-form-urlencoded",
//                "Postman-Token: 07c605ad-c672-6e5b-562e-7cee1f9cd0ea",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }

    public function deleteSEP(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."SEP/Delete",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/Delete",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_POSTFIELDS => "{\r\n\"request\": 
                                       {\r\n\"t_sep\": 
                                         {
                                            \r\n\"noSep\": \"".$request['nosep']."\",
                                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
                                    }",
            CURLOPT_HTTPHEADER => array(
//                "Cache-Control: no-cache",
                "Content-Type: Application/x-www-form-urlencoded",
//                "Postman-Token: 161b9dda-e007-dea4-1f53-75f393715c1e",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);


        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function updateSEP(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."SEP/Update",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/Update",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS =>
                 "{\r\n\"request\": 
                        {\r\n\"t_sep\": 
                            {\r\n\"noSep\": \"".$request['nosep']."\",
                            \r\n\"klsRawat\": \"".$request['kelasrawat']."\",
                            \r\n\"noMR\": \"".$request['nomr']."\",
                            \r\n\"rujukan\": {\r\n\"asalRujukan\": \"".$request['asalrujukan']."\",
                            \r\n\"tglRujukan\": \"".$request['tglrujukan']."\",
                            \r\n\"noRujukan\": \"".$request['norujukan']."\",
                            \r\n\"ppkRujukan\": \"".$request['ppkrujukan']."\"\r\n},
                            \r\n\"catatan\": \"".$request['catatan']."\",
                            \r\n\"diagAwal\": \"".$request['kddiagnosaawal']."\",
                            \r\n\"poli\": {\r\n\"eksekutif\": \"".$request['eksekutif']."\"\r\n},
                            \r\n\"cob\": {\r\n\"cob\": \"".$request['cob']."\"\r\n},
                            \r\n\"jaminan\": {\r\n\"lakaLantas\": \"".$request['lakalantas']."\", 
                            \r\n\"penjamin\": \"".$request['penjamin']."\",
                            \r\n\"lokasiLaka\": \"".$request['lokasilaka']."\"\r\n},
                            \r\n\"noTelp\": \"".$request['notelp']."\",
                            \r\n\"user\": \"ramdanegie\"\r\n
                          }\r\n}
                      \r\n}",
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: Application/x-www-form-urlencoded",
//                "Postman-Token: c359f488-f523-6279-b14f-64a1dab17772",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "Services Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
       
    }

    public function getPoli(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/poli/".$request['kodeNamaPoli'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/referensi/poli/".$request['kodePoli'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getNoRujukanRs(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/RS/".$request['norujukan'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getNoRujukanPcare(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/".$request['norujukan'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function updateTglPulang(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $dataJsonSend = json_encode($request['data']);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Sep/updtglplg",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/Update",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $dataJsonSend,
                
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: Application/x-www-form-urlencoded",
//                "Postman-Token: c359f488-f523-6279-b14f-64a1dab17772",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "Services Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getDiagnosa(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),

//         CURLOPT_URL=> "https://vclaim.bpjs-kesehatan.go.id/tot/Referensi/getDiagnosa".$request['nama'],
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/diagnosa/".$request['kdNamaDiagnosa'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1   ,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);

    }

    public function getFaskes(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/faskes/".$request['kdNamaFaskes']."/".$request['jenisFakses'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function getProcedureDiagnosaTindakan(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/procedure/".$request['kdNamaDiagnosa'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }

    public function getKelasRawat(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/kelasrawat",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }

    public function getDokter(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/dokter/".$request['namaDokter'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }

    public function getSpesialistik(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/spesialistik",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function getRuangRawat(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/ruangrawat",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function getCaraKeluar(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/carakeluar",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function getPascaPulang(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/pascapulang",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }

    public function postPengajuan(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Sep/pengajuanSEP",
//                CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/insert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
                "{\r\n\"request\": 
                         {\r\n\"t_sep\": 
                            {
                            \r\n\"noKartu\": \"".$request['nokartu']."\",
                            \r\n\"tglSep\": \"".$request['tglsep']."\",
                            \r\n\"jnsPelayanan\": \"".$request['jenispelayanan']."\",
                            \r\n\"keterangan\": \"".$request['keterangan']."\",
                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
                            
                      }",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function postApprovalPengajuanSep(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Sep/aprovalSEP",
//                CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/insert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
                "{\r\n\"request\": 
                         {\r\n\"t_sep\": 
                            {
                            \r\n\"noKartu\": \"".$request['nokartu']."\",
                            \r\n\"tglSep\": \"".$request['tglsep']."\",
                            \r\n\"jnsPelayanan\": \"".$request['jenispelayanan']."\",
                            \r\n\"keterangan\": \"".$request['keterangan']."\",
                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
                            
                      }",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function getIntegrasiSepInaCbg(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."sep/cbg/".$request['noSEP'],
//                CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/insert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function getNoRujukanRsNoKartu(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/RS/Peserta/".$request['nokartu'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getNoRujukanPcareNoKartu(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/Peserta/".$request['nokartu'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function insertRujukan(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();
	    $dataSend = json_encode($request['data']);
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/insert",
//                CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/insert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
//            {
//       "request": {
//            "t_rujukan": {
//                "noSep": "{nomor sep}",
//             "tglRujukan": "{tanggal rujukan format : yyyy-mm-dd}",
//             "ppkDirujuk": "{faskes dirujuk -> data di referensi faskes}",
//             "jnsPelayanan": "{jenis pelayanan -> 1.R.Inap 2.R.Jalan}",
//             "catatan": "{catatan rujukan}",
//             "diagRujukan": "{kode diagnosa rujukan -> data di referensi diagnosa}",
//             "tipeRujukan": "{tipe rujukan -> 0.penuh, 1.Partial 2.rujuk balik}",
//             "poliRujukan": "{kode poli rujukan -> data di referensi poli}",
//             "user": "{user pemakai}"
//          }
//       }
//    }
               "{\r\n\"request\":
                        {\r\n\"t_rujukan\":
                           {
                           \r\n\"noSep\": \"".$request['nosep']."\",
                           \r\n\"tglRujukan\": \"".$request['tglrujukan']."\",
                           \r\n\"ppkDirujuk\": \"".$request['ppkdirujuk']."\",
                           \r\n\"jnsPelayanan\": \"".$request['jenispelayanan']."\",
                           \r\n\"catatan\": \"".$request['catatan']."\",
                            \r\n\"diagRujukan\": \"".$request['diagnosarujukan']."\",
                           \r\n\"tipeRujukan\": \"".$request['tiperujukan']."\",
                           \r\n\"poliRujukan\": \"".$request['polirujukan']."\",
                           \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n

                     }",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function updateRujukan(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/update",
//                CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/insert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS =>
//            {
//       "request": {
//            "t_rujukan": {
//                "noRujukan": "{nomor rujukan}",
//             "ppkDirujuk": "{faskes dirujuk -> data di referensi faskes}",
//             "tipe": "{tipe rujukan -> 0.penuh, 1.Partial 2.rujuk balik}",
//             "jnsPelayanan": "{jenis pelayanan -> 1.R.Inap 2.R.Jalan}",
//             "catatan": "{catatan rujukan}",
//             "diagRujukan": "{kode diagnosa rujukan -> data di referensi diagnosa}",
//             "tipeRujukan": "{tipe rujukan -> 0.penuh, 1.Partial 2.rujuk balik}",
//             "poliRujukan": "{kode poli rujukan -> data di referensi poli}",
//             "user": "{user pemakai}"
//          }
//       }
//    }
                "{\r\n\"request\": 
                         {\r\n\"t_rujukan\": 
                            {
                            \r\n\"noRujukan\": \"".$request['norujukan']."\",
                            \r\n\"tipe\": \"".$request['tipe']."\",
                            \r\n\"ppkDirujuk\": \"".$request['ppkdirujuk']."\",
                            \r\n\"jnsPelayanan\": \"".$request['jenispelayanan']."\",
                            \r\n\"catatan\": \"".$request['catatan']."\",
                             \r\n\"diagRujukan\": \"".$request['diagnosarujukan']."\",
                            \r\n\"tipeRujukan\": \"".$request['tiperujukan']."\",
                            \r\n\"poliRujukan\": \"".$request['polirujukan']."\",
                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
                            
                      }",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function deleteRujukan(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/Delete",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/Delete",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_POSTFIELDS => "{\r\n\"request\": 
                                       {\r\n\"t_rujukan\": 
                                         {
                                            \r\n\"noRujukan\": \"".$request['norujukan']."\",
                                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
                                    }",
            CURLOPT_HTTPHEADER => array(
//                "Cache-Control: no-cache",
                "Content-Type: Application/x-www-form-urlencoded",
//                "Postman-Token: 161b9dda-e007-dea4-1f53-75f393715c1e",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);


        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function insertLPK(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."LPK/insert",
//                CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/insert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
                "{\r\n\"request\": 
                         {\r\n\"t_lpk\": 
                            {
                            \r\n\"noSep\": \"".$request['nosep']."\",
                            \r\n\"tglMasuk\": \"".$request['tglmasuk']."\",
                            \r\n\"tglKeluar\":  \"".$request['tglkeluar']."\",
                            \r\n\"jaminan\": \"".$request['jaminan']."\",
                            \r\n\"poli\": 
                                        {\r\n\"poli\": \"".$request['poli']."\"\r\n},
                            \r\n\"perawatan\": 
                                         {\r\n\"ruangRawat\": \"".$request['ruangrawat']."\",
                                         \r\n\"kelasRawat\": \"".$request['kelasrawat']."\",
                                         \r\n\"spesialistik\": \"".$request['spesialistik']."\",
                                          \r\n\"caraKeluar\": \"".$request['carakeluar']."\",
                                         \r\n\"kondisiPulang\": \"".$request['kondisipulang']."\"\r\n},
                           \r\n\"diagnosa\": 
                                     [\r\n 
                                         {\r\n\"kode\": \"".$request['kodeprimer']."\",
                                         \r\n\"level\": \"".$request['levelprimer']."\"\r\n},
                                         {\r\n\"kode\": \"".$request['kodesekunder']."\",
                                         \r\n\"level\": \"".$request['levelsekunder']."\"\r\n},
                                      \r\n],
                           \r\n\"procedure\": 
                                     [\r\n 
                                         {\r\n\"kode\": \"".$request['kodediagtindakanprimer']."\"\r\n},
                                         {\r\n\"kode\": \"".$request['kodediagtindakansekunder']."\"\r\n},
                                      \r\n],
                           \r\n\"rencanaTL\": 
                                         {\r\n\"tindakLanjut\": \"".$request['tindaklanjut']."\",
                                         \r\n\"dirujukKe\": 
                                                        {\r\n\"kodePPK\": \"".$request['kodeppk']."\"\r\n},
                                          \r\n\"kontrolKembali\": 
                                                        {\r\n\"tglKontrol\": \"".$request['tglkontrol']."\",
                                                        \r\n\"poli\": \"".$request['polikontrol']."\",
                                                        \r\n},
                                         \r\n}, 
                            \r\n\"DPJP\": \"".$request['dpjp']."\",
                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
                            
                      }",
//             {
//       "request": {
//            "t_lpk": {
//                "noSep": "{nomor sep}",
//             "tglMasuk": "{tanggal masuk format yyyy-mm-dd}",
//             "tglKeluar": "{tanggal keluar format yyyy-mm-dd}",
//             "jaminan": "{penjamin -> 1. JKN}",
//             "poli": {
//                    "poli": "{kode poli -> data di referensi poli}"
//             },
//             "perawatan": {
//                    "ruangRawat": "{ruang rawat -> data di referensi ruang rawat}",
//                "kelasRawat": "{kelas rawat -> data di referensi kelas rawat}",
//                "spesialistik": "{spesialistik -> data di referensi spesialistik}",
//                "caraKeluar": "{cara keluar -> data di referensi cara keluar}",
//                "kondisiPulang": "{kondisi pulang -> data di referensi kondisi pulang}"
//             },
//             "diagnosa": [
//                {
//                    "kode": "{kode diagnosa  -> data di referensi diagnosa}",
//                   "level": "{level diagnosa -> 1.Primer 2.Sekunder}"
//                },
//                {
//                    "kode": "{kode diagnosa  -> data di referensi diagnosa}",
//                   "level": "{level diagnosa -> 1.Primer 2.Sekunder}"
//                }
//             ],
//             "procedure": [
//                {
//                    "kode": "{kode procedure -> data di referensi procedure/tindakan}"
//                },
//                {
//                    "kode": "{kode procedure -> data di referensi procedure/tindakan}"
//                }
//             ],
//             "rencanaTL": {
//                    "tindakLanjut": "{tindak lanjut -> 1:Diperbolehkan Pulang, 2:Pemeriksaan Penunjang, 3:Dirujuk Ke, 4:Kontrol Kembali}",
//                "dirujukKe": {
//                        "kodePPK": "{kode faskes -> data di referensi faskes}"
//                },
//                "kontrolKembali": {
//                        "tglKontrol": "{tanggal kontrol kembali format : yyyy-mm-dd}",
//                   "poli": "{kode poli -> data di referensi poli}"
//                }
//             },
//             "DPJP": "{kode dokter dpjp -> data di referensi dokter}",
//             "user": "{user pemakai}"
//          }
//       }
//    }
//
//
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }

    public function updateLPK(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."LPK/update",
//                CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/insert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS =>
                "{\r\n\"request\": 
                         {\r\n\"t_lpk\": 
                            {
                            \r\n\"noSep\": \"".$request['nosep']."\",
                            \r\n\"tglMasuk\": \"".$request['tglmasuk']."\",
                            \r\n\"tglKeluar\":  \"".$request['tglkeluar']."\",
                            \r\n\"jaminan\": \"".$request['jaminan']."\",
                            \r\n\"poli\": 
                                        {\r\n\"poli\": \"".$request['poli']."\"\r\n},
                            \r\n\"perawatan\": 
                                         {\r\n\"ruangRawat\": \"".$request['ruangrawat']."\",
                                         \r\n\"kelasRawat\": \"".$request['kelasrawat']."\",
                                         \r\n\"spesialistik\": \"".$request['spesialistik']."\",
                                          \r\n\"caraKeluar\": \"".$request['carakeluar']."\",
                                         \r\n\"kondisiPulang\": \"".$request['kondisipulang']."\"\r\n},
                           \r\n\"diagnosa\": 
                                     [\r\n 
                                         {\r\n\"kode\": \"".$request['kodeprimer']."\",
                                         \r\n\"level\": \"".$request['levelprimer']."\"\r\n},
                                         {\r\n\"kode\": \"".$request['kodesekunder']."\",
                                         \r\n\"level\": \"".$request['levelsekunder']."\"\r\n},
                                      \r\n],
                           \r\n\"procedure\": 
                                     [\r\n 
                                         {\r\n\"kode\": \"".$request['kodediagtindakanprimer']."\"\r\n},
                                         {\r\n\"kode\": \"".$request['kodediagtindakansekunder']."\"\r\n},
                                      \r\n],
                           \r\n\"rencanaTL\": 
                                         {\r\n\"tindakLanjut\": \"".$request['tindaklanjut']."\",
                                         \r\n\"dirujukKe\": 
                                                        {\r\n\"kodePPK\": \"".$request['kodeppk']."\"\r\n},
                                          \r\n\"kontrolKembali\": 
                                                        {\r\n\"tglKontrol\": \"".$request['tglkontrol']."\",
                                                        \r\n\"poli\": \"".$request['polikontrol']."\",
                                                        \r\n},
                                         \r\n}, 
                            \r\n\"DPJP\": \"".$request['dpjp']."\",
                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
                            
                      }",

            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function deleteLPK(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."LPK/Delete",
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/SEP/Delete",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_POSTFIELDS => "{\r\n\"request\": 
                                       {\r\n\"t_lpk\": 
                                         {
                                            \r\n\"noSep\": \"".$request['nosep']."\"\r\n}\r\n
                                            }\r\n
                                    }",
            CURLOPT_HTTPHEADER => array(
//                "Cache-Control: no-cache",
                "Content-Type: Application/x-www-form-urlencoded",
//                "Postman-Token: 161b9dda-e007-dea4-1f53-75f393715c1e",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);


        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }

    public function dataLPK(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
                //Fungsi : Pencarian data peserta berdasarkan NIK Kependudukan
                //Method : GET
                //Format : Json
                //Content-Type: application/json; charset=utf-8
                //Parameter 1 : Tanggal Masuk - format : yyyy-MM-dd
                //Parameter 2 : Jenis Pelayanan 1. Inap 2.Jalan

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."LPK/TglMasuk/".$request['tglmasuk']."/JnsPelayanan/".$request['jenispelayanan'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nokartu/".$request['nokartu']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }

    public function getMonitoringKunjungan(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        //Fungsi : data Kunjungan
        //Method : GET
        //Format : Json
        //Content-Type: Tanggal SEP format: yyyy-mm-dd
        //Parameter 1 : Tanggal Masuk - format : yyyy-MM-dd
        //Parameter 2 : Jenis Pelayanan 1. Inap 2.Jalan

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Monitoring/Kunjungan/Tanggal/".$request['tglsep']."/JnsPelayanan/".$request['jenispelayanan'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nokartu/".$request['nokartu']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getMonitoringKlaim(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        //Fungsi :Data Kunjungan
        //Method : GET
        //Format : Json
        //Content-Type: application/json; charset=utf-8
        //Parameter 1 : Tanggal Pulang format: yyyy-mm-dd
       // Parameter 2 : Jenis Pelayanan (1. Inap 2. Jalan)
       // Parameter 3 : Status Klaim (1. Proses Verifikasi 2. Pending Verifikasi 3. Klaim)
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),

            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Monitoring/Klaim/Tanggal/"
                .$request['tglsep']."/JnsPelayanan/".$request['jenispelayanan']."/Status/".$request['status'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nokartu/".$request['nokartu']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getDiagnosaTindakanSaeutik(Request $request)
    {
        $req = $request->all();
        $datRek = \DB::table('diagnosatindakan_m as dg')
            ->select('dg.id','dg.kddiagnosatindakan','dg.namadiagnosatindakan' )
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosatindakan');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $datRek = $datRek
                ->where('dg.kddiagnosatindakan','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
//                ->orWhere('dg.kddiagnosatindakan','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }


        $datRek=$datRek->take(10);
        $datRek=$datRek->get();

        return $this->respond($datRek);
    }
    public function getDiagnosaSaeutik(Request $request)
    {
        $req = $request->all();
        $datRek = \DB::table('diagnosa_m as dg')
            ->select('dg.id','dg.kddiagnosa','dg.namadiagnosa' )
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosa');
        if(isset($req['kdDiagnosa']) &&
            $req['kdDiagnosa']!="" &&
            $req['kdDiagnosa']!="undefined"){
            $datRek = $datRek->where('dg.kddiagnosa','ilike','%'. $req['kdDiagnosa'] .'%' );
        }
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $datRek = $datRek
                ->where('dg.kddiagnosa','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
//                ->orWhere('dg.kddiagnosatindakan','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }


        $datRek=$datRek->take(10);
        $datRek=$datRek->get();

        return $this->respond($datRek);
    }
    public function getRuanganRI(Request $request)
    {
        $data = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.kdinternal','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk',[26,16,35])
            ->orderBy('ru.namaruangan')
            ->get();
        $result = array(
            'data' => $data,
            'message' => 'niaramdanegie',
        );

        return $this->respond($result);
    }
    public function getRuanganRJ(Request $request)
    {
        $data = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.kdinternal','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk',[24,18,28])
            ->orderBy('ru.namaruangan')
            ->get();
        $result = array(
            'data' => $data,
            'message' => 'niaramdanegie',
        );

        return $this->respond($result);
    }

    public function getSepByNoregistrasi(Request $request)
    {
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJOIN ('pemakaianasuransi_t as pas','pas.noregistrasifk','=','pd.norec')
            ->leftjoin ('asuransipasien_m as aps','aps.id','=','pas.objectasuransipasienfk')
            ->join ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join ('kelas_m as kls','kls.id','=','aps.objectkelasdijaminfk')
            ->leftjoin ('diagnosa_m as dg','dg.id','=','pas.objectdiagnosafk')
            ->leftjoin ('jenispelayanan_m as jp','jp.kodeinternal','=','pd.jenispelayanan')
            ->select('pd.noregistrasi','ps.nocm','pas.nokepesertaan','pd.objectruanganlastfk','ru.namaruangan','ru.kdinternal',
                'aps.objectkelasdijaminfk','kls.namakelas','pas.tglrujukan','pas.norujukan','aps.kdprovider',
                'aps.nmprovider','pas.catatan','pas.diagnosisfk','dg.kddiagnosa',
                'pd.jenispelayanan as objectjenispelayananfk','jp.jenispelayanan','pas.lakalantas',
                'ps.notelepon','pas.nosep',
               DB::raw('case when ru.objectdepartemenfk in (26,16,35) then \'true\' 
               when ru.objectdepartemenfk in (24,18,28) then \'false\' end as israwatinap')
             )
            ->where('pd.objectruanganasalfk', null)
            ->where('pd.noregistrasi',$request['noRegistrasi'])
            ->get();
        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }


    public function  getDiagnosaReferen(Request $request){
        $data = \DB::table('diagnosa_m as ru')
            ->select('ru.id','ru.kddiagnosa','ru.namadiagnosa')
            ->where('ru.statusenabled', true)
            ->where('ru.namadiagnosa', 'ilike','%'.$request['nama'].'%')
            ->orderBy('ru.namadiagnosa')
            ->get();
        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function getFaskesSaeutik(Request $request)
    {
        $req = $request->all();
        $dataReq = $req['filter']['filters'][0]['value'];
//        return $this->respond($dataReq);

        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),

            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/faskes/". $dataReq ."/".$req['jenisFaskes'],//.$request['jenisFakses'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

//        if(isset($req['filter']['filters'][0]['value']) &&
//            $req['filter']['filters'][0]['value']!="" &&
//            $req['filter']['filters'][0]['value']!="undefined"){
//            $result = $result
//                ->where('ru.namalengkap','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
//        }
        $res=$result['response']->faskes;
        return $this->respond($res);
    }
    public function getDiagnosaPart(Request $request) {
        $req = $request->all();
        $dataReq = $req['filter']['filters'][0]['value'];
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),

//         CURLOPT_URL=> "https://vclaim.bpjs-kesehatan.go.id/tot/Referensi/getDiagnosa".$request['nama'],
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/diagnosa/".$dataReq,
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1   ,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $res=$result['response']->diagnosa;
        return $this->respond($res);

    }
    public function getProcedureDiagnosaTindakanPart(Request $request) {
        $req = $request->all();
        $dataReq = $req['filter']['filters'][0]['value'];
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/procedure/".$dataReq,
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $res=$result['response']->procedure;
        return $this->respond($res);
    }
    public function getDokterSaeutik(Request $request) {
        $req = $request->all();
        $dataReq = $req['filter']['filters'][0]['value'];
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/dokter/".$dataReq,
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $res=$result['response']->list;
        return $this->respond($res);
//        return $this->respond($result);
    }
    public function getPoliSaeutik(Request $request) {
        $req = $request->all();
        $dataReq = $req['filter']['filters'][0]['value'];
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/poli/".$dataReq,
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/referensi/poli/".$request['kodePoli'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        $res=$result['response']->poli;
        return $this->respond($res);
    }
    public function simpanBpjsKlaim(Request $request)
    {
//        ini_set('max_execution_time', 100);
        DB::beginTransaction();
        try {
            $data2 = BPJSKlaimTxt::where('txtfilename', $request['filename'])->delete();
            foreach ($request['data'] as $item){
                $data1 = new BPJSKlaimTxt();
                $data1->norec = $data1->generateNewId();
                $data1->kdprofile = 0;
                $data1->statusenabled = true;


                $data1->KODE_RS = $item['KODE_RS'];
                $data1->KELAS_RS = $item['KELAS_RS'];
                $data1->KELAS_RAWAT = $item['KELAS_RAWAT'];
                $data1->KODE_TARIF = $item['KODE_TARIF'];
                $data1->PTD = $item['PTD'];
                $data1->ADMISSION_DATE = $item['ADMISSION_DATE'];
                $data1->DISCHARGE_DATE = $item['DISCHARGE_DATE'];
                $data1->BIRTH_DATE = $item['BIRTH_DATE'];
                $data1->BIRTH_WEIGHT = $item['BIRTH_WEIGHT'];
                $data1->SEX = $item['SEX'];
                $data1->DISCHARGE_STATUS = $item['DISCHARGE_STATUS'];
                $data1->DIAGLIST = $item['DIAGLIST'];
                $data1->PROCLIST = $item['PROCLIST'];
                $data1->ADL1 = $item['ADL1'];
                $data1->ADL2 = $item['ADL2'];
                $data1->IN_SP = $item['IN_SP'];
                $data1->IN_SR = $item['IN_SR'];
                $data1->IN_SI = $item['IN_SI'];
                $data1->IN_SD = $item['IN_SD'];
                $data1->INACBG = $item['INACBG'];
                $data1->SUBACUTE = $item['SUBACUTE'];
                $data1->CHRONIC = $item['CHRONIC'];
                $data1->SP = $item['SP'];
                $data1->SR = $item['SR'];
                $data1->SI = $item['SI'];
                $data1->SD = $item['SD'];
                $data1->DESKRIPSI_INACBG = $item['DESKRIPSI_INACBG'];
                $data1->TARIF_INACBG = $item['TARIF_INACBG'];
                $data1->TARIF_SUBACUTE = $item['TARIF_SUBACUTE'];
                $data1->TARIF_CHRONIC = $item['TARIF_CHRONIC'];
                $data1->DESKRIPSI_SP = $item['DESKRIPSI_SP'];
                $data1->TARIF_SP = $item['TARIF_SP'];
                $data1->DESKRIPSI_SR = $item['DESKRIPSI_SR'];
                $data1->TARIF_SR = $item['TARIF_SR'];
                $data1->DESKRIPSI_SI = $item['DESKRIPSI_SI'];
                $data1->TARIF_SI = $item['TARIF_SI'];
                $data1->DESKRIPSI_SD = $item['DESKRIPSI_SD'];
                $data1->TARIF_SD = $item['TARIF_SD'];
                $data1->TOTAL_TARIF = $item['TOTAL_TARIF'];
                $data1->TARIF_RS = $item['TARIF_RS'];
                $data1->TARIF_POLI_EKS = $item['TARIF_POLI_EKS'];
                $data1->LOS = $item['LOS'];
                $data1->ICU_INDIKATOR = $item['ICU_INDIKATOR'];
                $data1->ICU_LOS = $item['ICU_LOS'];
                $data1->VENT_HOUR = $item['VENT_HOUR'];
                $data1->NAMA_PASIEN = $item['NAMA_PASIEN'];
                $data1->MRN = $item['MRN'];
                $data1->UMUR_TAHUN = $item['UMUR_TAHUN'];
                $data1->UMUR_HARI = $item['UMUR_HARI'];
                $data1->DPJP = $item['DPJP'];
                $data1->sep = $item['SEP'];
                $data1->NOKARTU = $item['NOKARTU'];
                $data1->PAYOR_ID = $item['PAYOR_ID'];
                $data1->CODER_ID = $item['CODER_ID'];
                $data1->VERSI_INACBG = $item['VERSI_INACBG'];
                $data1->VERSI_GROUPER = $item['VERSI_GROUPER'];
                $data1->C1 = $item['C1'];
                $data1->C2 = $item['C2'];
                $data1->C3 = $item['C3'];
                $data1->C4 = $item['C4'];
                $data1->txtfilename = $request['filename'];
                $data1->save();
            }
//            $strFileName = $request['filename'];
//            $dataClaim = DB::select(DB::raw("select pd.norec,pd.noregistrasi,pd.tglregistrasi,bpjs.\"TARIF_RS\" as tarif from bpjsklaimtxt_t as bpjs
//                    INNER JOIN pemakaianasuransi_t as pa on pa.nosep=bpjs.sep
//                    INNER JOIN pasiendaftar_t as pd on pd.norec=pa.noregistrasifk
//                    where txtfilename='$strFileName';")
//            );
//            foreach ($dataClaim as $item){
//                $dataSP = StrukPelayanan::where('noregistrasifk',$item->norec)
//                    ->where('statusenabled',null)
//                    ->update(array('totalselisihklaim' => $item->tarif));
//            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Simpan BPJS Klaim";
        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage . ' Berhasil',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage . ' Gagal',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getChecklistKlaim(Request $request) {
//        $aingMacan = DB::select(DB::raw("select tgl,
//                sum(case when objectdepartemenfk <> 16 then  BPJS else 0 end) as bpjs_rajal,
//                sum(case when objectdepartemenfk <> 16 then  dokumen else 0 end) as berkas_rajal,
//                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=3 then  dokumen else 0 end) as berkas_kls1,
//                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=2 then  dokumen else 0 end) as berkas_kls2,
//                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=1 then  dokumen else 0 end) as berkas_kls3,
//                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=3 then  BPJS else 0 end) as bpjs_kls1,
//                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=2 then  BPJS else 0 end) as bpjs_kls2,
//                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=1 then  BPJS else 0 end) as bpjs_kls3
//                 from
//                (select to_char(pa.tglregistrasi, 'YYYY-MM-DD') as tgl, ru.objectdepartemenfk,pd.objectkelasfk,kls.namakelas,ap.objectkelasdijaminfk,
//                case when bpjs.norec is null then 0 else 1 end as BPJS,case when pa.norec is null then 0 else 1 end as dokumen
//                from pemakaianasuransi_t as pa
//                INNER JOIN asuransipasien_m as ap on ap.id=pa.objectasuransipasienfk
//                inner JOIN bpjsklaimtxt_t as bpjs  on pa.nosep=bpjs.sep
//                INNER JOIN pasiendaftar_t as pd on pd.norec=pa.noregistrasifk
//                INNER JOIN strukpelayanan_t as sp on sp.noregistrasifk=pd.norec and sp.statusenabled is null
//                INNER JOIN strukpelayananpenjamin_t as spp on spp.nostrukfk=sp.norec and spp.noverifikasi is not null
//                INNER JOIN ruangan_m as ru on ru.id=pd.objectruanganlastfk
//                INNER JOIN kelas_m as kls on kls.id=ap.objectkelasdijaminfk
//                where pd.tglpulang between :tglAwal and :tglAkhir
//                and pd.objectkelompokpasienlastfk=2) as x group by tgl order by tgl;
//            "),
//            array(
//                'tglAwal' => $request['tglAwal'] ,
//                'tglAkhir' => $request['tglAkhir']
//            )
//        );
        $aingMacan = DB::select(DB::raw("select tgl,
                sum(case when objectdepartemenfk <> 16 then  BPJS else 0 end) as bpjs_rajal,
                sum(case when objectdepartemenfk <> 16 then  dokumen else 0 end) as berkas_rajal,
                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=3 then  dokumen else 0 end) as berkas_kls1,
                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=2 then  dokumen else 0 end) as berkas_kls2,
                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=1 then  dokumen else 0 end) as berkas_kls3,
                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=3 then  BPJS else 0 end) as bpjs_kls1,
                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=2 then  BPJS else 0 end) as bpjs_kls2,
                sum(case when objectdepartemenfk=16 and objectkelasdijaminfk=1 then  BPJS else 0 end) as bpjs_kls3
                 from
                (select to_char(pd.tglpulang, 'YYYY-MM-DD') as tgl, ru.objectdepartemenfk,pd.objectkelasfk,kls.namakelas,ap.objectkelasdijaminfk,
                case when bpjs.norec is null then 0 else 1 end as BPJS,case when pa.norec is null then 0 else 1 end as dokumen
                from pemakaianasuransi_t as pa
                INNER JOIN asuransipasien_m as ap on ap.id=pa.objectasuransipasienfk
                inner JOIN bpjsklaimtxt_t as bpjs  on pa.nosep=bpjs.sep
                INNER JOIN pasiendaftar_t as pd on pd.norec=pa.noregistrasifk
                INNER JOIN ruangan_m as ru on ru.id=pd.objectruanganlastfk
                INNER JOIN kelas_m as kls on kls.id=ap.objectkelasdijaminfk
                where pd.tglpulang between :tglAwal and :tglAkhir
                and pd.objectkelompokpasienlastfk=2) as x group by tgl order by tgl;
            "),
            array(
                'tglAwal' => $request['tglAwal'] ,
                'tglAkhir' => $request['tglAkhir']
            )
        );
        $result = array(
            'dat' => $aingMacan,
            'by' => 'as@epic'
        );
        return $this->respond($result);
    }
    public function simpanGagalHitungBpjsKlaim(Request $request)
    {
//        ini_set('max_execution_time', 100);
        DB::beginTransaction();
        try {
            $data2 = BPJSGagalKlaimTxt::where('txtfilename', $request['filename'])->delete();
            foreach ($request['data'] as $item){
                $data1 = new BPJSGagalKlaimTxt();
                $data1->norec = $data1->generateNewId();
                $data1->kdprofile = 0;
                $data1->statusenabled = true;

                $data1->nosep = $item['NOSEP'];
                $data1->tglsep = $item['TGLSEP'];
                $data1->nokartu = $item['NOKARTU'];
                $data1->nmpeserta = $item['NMPESERTA'];
                $data1->rirj = $item['RIRJ'];
                $data1->kdinacbg = $item['KDINACBG'];
                $data1->bypengajuan = $item['BYPENGAJUAN'];
                $data1->keterangan = $item['KETERANGAN'];
                $data1->txtfilename = $request['filename'];
                $data1->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Simpan BPJS Gagal Klaim";
        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage . ' Berhasil',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage . ' Gagal',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
     public function getDokterDPJP(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/dokter/pelayanan/".$request['jenisPelayanan']."/tglPelayanan/"
                .$request['tglPelayanan']."/Spesialis/".$request['kodeSpesialis'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nik/".$request['nik']."/tglSEP/".$request['tglsep'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function getPropinsi(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/propinsi",
//            CURLOPT_URL => "https://dvlp.bpjs-kesehatan.go.id/VClaim-rest/referensi/propinsi",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $res=$result['response']->list;
        return $this->respond($res);
    }
    public function getKabupaten(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/kabupaten/propinsi/".$request['kodePropinsi'],
//            CURLOPT_URL => "https://dvlp.bpjs-kesehatan.go.id/VClaim-rest/referensi/kabupaten/propinsi/".$request['kodePropinsi'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $res=$result['response']->list;
        return $this->respond($res);
    }
    public function getKecamatan(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."referensi/kecamatan/kabupaten/".$request['kodeKabupaten'],
//            CURLOPT_URL => "https://dvlp.bpjs-kesehatan.go.id/VClaim-rest/referensi/kecamatan/kabupaten/".$request['kodeKabupaten'],
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
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $res=$result['response']->list;
        return $this->respond($res);
    }
    public function getMonitoringHistori($noKartu) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        //Fungsi :Data Kunjungan
        //Method : GET
        //Format : Json
        //Content-Type: application/json; charset=utf-8
        //Parameter 1 : Tanggal Pulang format: yyyy-mm-dd
       // Parameter 2 : Jenis Pelayanan (1. Inap 2. Jalan)
       // Parameter 3 : Status Klaim (1. Proses Verifikasi 2. Pending Verifikasi 3. Klaim)
        // URL : http://localhost:8000/service/transaksi/bpjs/monitoring/HistoriPelayanan/NoKartu/0002235322484/tglAwal/2018-10-01/tglAkhir/2018-10-21
        $tglMulai = Carbon::now()->subMonth(4)->format('Y-m-d');
//        return $this->respond($tglMulai);
        $tglAkhir = Carbon::now()->format('Y-m-d');

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL =>$this->getUrlBrigdingBPJS()."monitoring/HistoriPelayanan/NoKartu/".$noKartu."/tglAwal/".$tglMulai."/tglAkhir/".$tglAkhir,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }

    public function getMonitoringJasaRaharja(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        //Fungsi :Data Kunjungan
        //Method : GET
        //Format : Json
        //Content-Type: application/json; charset=utf-8
        //Parameter 1 : Tanggal Pulang format: yyyy-mm-dd
       // Parameter 2 : Jenis Pelayanan (1. Inap 2. Jalan)
       // Parameter 3 : Status Klaim (1. Proses Verifikasi 2. Pending Verifikasi 3. Klaim)
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
//            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."monitoring/JasaRaharja/tglMulai/".$request['tglMulai']."/tglAkhir/".$request['tglAkhir'],

            CURLOPT_URL =>$this->getUrlBrigdingBPJS()."monitoring/JasaRaharja/tglMulai/"
                .$request['tglMulai']."/tglAkhir/".$request['tglAkhir'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }

    public function getRujukanNoKartuMulti(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
//            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/List/Peserta/".$request['nokartu'],
            CURLOPT_URL => $this->getUrlBrigdingBPJS()."Rujukan/List/Peserta/".$request['nokartu'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
     public function getRujukanNoKartuMultiRS(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
//            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
//            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/RS/Peserta/".$request['nokartu'],
            CURLOPT_URL => $this->getUrlBrigdingBPJS()."Rujukan/RS/List/Peserta/".$request['nokartu'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getRujukanByTglRujukan(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
//            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/List/Peserta/".$request['tglRujukan'],
            CURLOPT_URL => $this->getUrlBrigdingBPJS()."Rujukan/List/TglRujukan/".$request['tglRujukan'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getRujukanByTglRujukanRS(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
//            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Rujukan/RS/TglRujukan/".$request['tglRujukan'],
            CURLOPT_URL => $this->getUrlBrigdingBPJS()."Rujukan/RS/List/TglRujukan/".$request['tglRujukan'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function getSuplesiJasaRaharja(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
//            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."sep/RS/JasaRaharja/Suplesi/".$request['noKartu']."/tglPelayanan/".$request['tglPelayanan'],
            CURLOPT_URL => $this->getUrlBrigdingBPJS()."sep/JasaRaharja/Suplesi/"
                .$request['noKartu']."/tglPelayanan/".$request['tglPelayanan'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
     public function insertSepV11(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
            $curl = curl_init();
            $dataJsonSend = json_encode($request['data']);
            curl_setopt_array($curl, array(
                CURLOPT_PORT =>  $this->getPortBrigdingBPJS(),
                CURLOPT_URL=> $this->getUrlBrigdingBPJS()."SEP/1.1/insert",
//                CURLOPT_URL => "https://dvlp.bpjs-kesehatan.go.id/VClaim-rest/SEP/1.1/insert",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $dataJsonSend,

//                 "{\r\n\"request\":
//                         {\r\n\"t_sep\":
//                            {
//                            \r\n\"noKartu\": \"".$request['nokartu']."\",
//                            \r\n\"tglSep\": \"".$request['tglsep']."\",
//                            \r\n\"ppkPelayanan\": \"0904R004\",
//                            \r\n\"jnsPelayanan\": \"".$request['jenispelayanan']."\",
//                            \r\n\"klsRawat\": \"".$request['kelasrawat']."\",
//                            \r\n\"noMR\": \"".$request['nomr']."\",
//                            \r\n\"rujukan\": {\r\n\"asalRujukan\": \"".$request['asalrujukan']."\",
//                                            \r\n\"tglRujukan\": \"".$request['tglrujukan']."\",
//                                            \r\n\"noRujukan\": \"".$request['norujukan']."\",
//                                            \r\n\"ppkRujukan\": \"".$request['ppkrujukan']."\"\r\n},
//                            \r\n\"catatan\": \"".$request['catatan']."\",
//                            \r\n\"diagAwal\": \"".$request['diagnosaawal']."\",
//                            \r\n\"poli\": {\r\n\"tujuan\": \"".$request['politujuan']."\",
//                                         \r\n\"eksekutif\": \"".$request['eksekutif']."\"\r\n},
//                            \r\n\"cob\":
//                                        {\r\n\"cob\": \"".$request['cob']."\"\r\n},
//                            \r\n\"katarak\":
//                                        {\r\n\"katarak\": \"".$request['katarak']."\"\r\n},
//                            \r\n\"jaminan\": {\r\n\"lakaLantas\": \"".$request['lakalantas']."\",
//                                            \r\n\"penjamin\": { \r\n\"penjamin\"  : \"".$request['penjamin']."\",
//                                                                \r\n\"tglKejadian\": \"".$request['tglKejadian']."\",
//                                                                \r\n\"keterangan\"  : \"".$request['keterangan']."\",
//                                                                \r\n\"suplesi\": { \r\n\"suplesi\"  : \"".$request['suplesi']."\",
//                                                                                   \r\n\"noSepSuplesi\": \"".$request['noSepSuplesi']."\",
//                                                                                    \r\n\"lokasiLaka\":
//                                                                                    { \r\n\"kdPropinsi\"  : \"".$request['kdPropinsi']."\",
//                                                                                      \r\n\"kdKabupaten\": \"".$request['kdKabupaten']."\",
//                                                                                      \r\n\"kdKecamatan\"  : \"".$request['kdKecamatan']."\"\r\n}\r\n}\r\n}\r\n},
//                            \r\n\"skdp\":
//                                        {\r\n\"noSurat\": \"".$request['noSurat']."\",
//                                        \r\n\"kodeDPJP\": \"".$request['kodeDPJP']."\" \r\n},
//                            \r\n\"noTelp\": \"".$request['notelp']."\",
//                            \r\n\"user\": \"Ramdanegie\"\r\n}\r\n}\r\n
//
//                      }",

                CURLOPT_HTTPHEADER => array(
                    "Content-Type: Application/x-www-form-urlencoded",
                    "X-cons-id: ".  (string)$data,
                    "X-signature: ". (string)$encodedSignature,
                    "X-timestamp: ". (string)$tStamp
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $result= "cURL Error #:" . $err;
            } else {
                $result = (array) json_decode($response);
            }
        return $this->respond($result);
      
    }
     public function updateSepV11(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
         $dataJsonSend = json_encode($request['data']);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."SEP/1.1/Update",
//            CURLOPT_URL => "https://dvlp.bpjs-kesehatan.go.id/VClaim-rest/SEP/1.1/Update",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $dataJsonSend,
                 // new Vclaim v.1.1

            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: Application/x-www-form-urlencoded",
//                "Postman-Token: c359f488-f523-6279-b14f-64a1dab17772",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "Services Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
   
    }
    public function getHasCode(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha1','JASAMEDIKA', true);
        $sting ='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJhZG1pbi5vcGVyYXRvciJ9.2yCoQiRKSoXJhCzSdbLxvLWPPx02jzPgkUpT2f0uDLeKKPIK00xLbLlUeTlS7eNq6cLOE7XM03sOWgmQ5TLvVA';
        // base64 encode…
        $encodedSignature = base64_decode($sting);
        // $encodedSignature = urlencode($encodedSignature);
        
        $result = array(
            "X-cons-id" =>  $data ,
            "X-timestamp" => $tStamp,
            "X-signature" => $encodedSignature,
        );

        return $this->respond($encodedSignature);
    }

    public function deleteSiranap(Request $request) {
        # seting koneksi webservices #
        $xrsid = "kode_rumah_sakit";  # ID Rumah Sakit #
        $xpass = md5("password_rumah_sakit"); # Password #
        $kode_tipe_pasien="0004"; #kode tipe pasien
        $kode_kelas_ruang="0003"; # kode kelas ruang

        $strURLSiranap = "http://sirs.yankes.kemkes.go.id/sirsservice/sisrute/hapusdata/$xrsid/$kode_tipe_pasien/$kode_kelas_ruang";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $strURLSiranap);
        curl_setopt($curl, CURLOPT_HTTPHEADER, Array(
                "X-rs-id: $xrsid",
                "X-pass:$xpass",
                "Content-Type:application/xml; charset=ISO-8859-1",
                "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15")
        );
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $str = curl_exec($curl);
        curl_close($curl);
//        echo $str;
        return $this->respond($str);
//        $data = $this->getIdConsumerBPJS();
//        $secretKey = $this->getPasswordConsumerBPJS();
//        // Computes the timestamp
//        date_default_timezone_set('UTC');
//        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
//        // Computes the signature by hashing the salt with the secret key as the key
//        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);
//
//        // base64 encode…
//        $encodedSignature = base64_encode($signature);
//
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
////            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
////            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."sep/RS/JasaRaharja/Suplesi/".$request['noKartu']."/tglPelayanan/".$request['tglPelayanan'],
//            CURLOPT_URL => $this->getUrlBrigdingBPJS()."sep/RS/JasaRaharja/Suplesi/"
//                .$request['noKartu']."/tglPelayanan/".$request['tglPelayanan'],
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_SSL_VERIFYHOST => 0,
//            CURLOPT_SSL_VERIFYPEER => 0,
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 30,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "GET",
//            CURLOPT_HTTPHEADER => array(
//                "Content-Type: application/json; charset=utf-8",
//                "X-cons-id: ".  (string)$data,
//                "X-signature: ". (string)$encodedSignature,
//                "X-timestamp: ". (string)$tStamp
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//
//        if ($err) {
//            $result= "cURL Error #:" . $err;
//        } else {
//            $result = (array) json_decode($response);
//        }
//
//        return $this->respond($result);
    }
    public function getNoPesertaV1(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getUrlBrigdingBPJS()."Peserta/nokartu/".$request['nokartu']."/tglSEP/".$request['tglsep'],
//            CURLOPT_URL => "http://dvlp.bpjs-kesehatan.go.id:8080/VClaim-Rest/Peserta/nokartu/".$request['nokartu']."/tglSEP/".$request['tglsep'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function simpanLokalRujukan(Request $request)
    {
//        ini_set('max_execution_time', 100);
        DB::beginTransaction();
        try {
            $data1 = new BPJSRujukan();
            $data1->norec = $data1->generateNewId();
            $data1->kdprofile = 0;
            $data1->statusenabled = true;
            $data1->nosep = $request['nosep'];
            $data1->diagnosarujukan = $request['diagnosarujukan'];
            $data1->jenispelayanan = $request['jenispelayanan'];
            $data1->polirujukan  = $request['polirujukan'];
            $data1->ppkdirujuk = $request['ppkdirujuk'];
            $data1->kdppkdirujuk = $request['kdppkdirujuk'];
            $data1->tglrujukan = $request['tglrujukan'];
            $data1->tiperujukan = $request['tiperujukan'];
            $data1->nama = $request['nama'];
            $data1->nokartu = $request['nokartu'];
            $data1->catatan = $request['catatan'];
            $data1->tglsep = $request['tglsep'];
            $data1->sex = $request['sex'];
            $data1->norujukan = $request['norujukan'];
            $data1->nocm = $request['nocm'];
            $data1->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Simpan Rujukan";
        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage . ' Berhasil',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage . ' Gagal',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result);
    }
    public function getLokalRujukan(Request $request)
    {
        $data = \DB::table('bpjsrujukan_t as br')
            ->leftjoin('ruangan_m as ru','ru.kdinternal','=','br.polirujukan')
            ->select('br.*','ru.namaruangan')
            ->where('br.statusenabled',true);

        if(isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('br.tglrujukan', '>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('br.tglrujukan', '<=', $tgl);
        }
        if(isset($request['norujukan']) && $request['norujukan'] != "" && $request['norujukan'] != "undefined") {
            $data = $data->where('br.norujukan', 'ilike', '%' . $request['norujukan'] . '%');
        }
        if(isset($request['nocm']) && $request['nocm'] != "" && $request['nocm'] != "undefined") {
            $data = $data->where('br.nocm', 'ilike', '%' . $request['nocm'] . '%');
        }

        $data = $data->get();
        $result = array(
            'data'=>$data,
            'message' => 'Inhuman'
        );
        return $this->respond($result);
    }
    public function generateNoSKDP (Request $request) {
         $kdProfile = $this->getDataKdProfile($request);
        $noSKDP = $this->generateCodeBySeqTable(new PemakaianAsuransi, 'nosuratskdp', 6,'',$kdProfile);
        if ($noSKDP == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }

        $data = $this->genRandStr();
//        $noSKDP = $this->generateCode(new PemakaianAsuransi, 'nosuratskdp', 6, $this->getDateTime()->format('ym'));
        $result = array(
            'noskdp' => $noSKDP,
            'message' => 'Inhuman'
        );
        return $this->respond($result);
    }
    function genRandStr(){
        $a = $b = '';

        for($i = 0; $i < 3; $i++){
            $a .= chr(mt_rand(65, 90)); // see the ascii table why 65 to 90.
            $b .= mt_rand(0, 9);
        }

        return $a . $b;
    }
    public function getReferensiKamar(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          //  CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getLinkAplicare()."aplicaresws/rest/ref/kelas",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function updateKetersediaanTT($kodeppk, Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();
//        {
//            "kodekelas":"VIP",
//            "koderuang":"RG01",
//            "namaruang":"Ruang Anggrek VIP",
//            "kapasitas":"20",
//            "tersedia":"10",
//            "tersediapria":"0",
//            "tersediawanita":"0",
//            "tersediapriawanita":"0"
//           }
        $dataJsonSend = json_encode($request['json']);
        curl_setopt_array($curl, array(
         //   CURLOPT_PORT =>  $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getLinkAplicare()."aplicaresws/rest/bed/update/".$kodeppk,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function postRuanganBaru($kodeppk, Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();
        //        { "kodekelas":"VIP",
        //    "koderuang":"RG01",
        //    "namaruang":"Ruang Anggrek VIP",
        //    "kapasitas":"20",
        //    "tersedia":"10",
        //     "tersediapria":"0",
        //    "tersediawanita":"0",
        //    "tersediapriawanita":"0"
        //   }

        $dataJsonSend = json_encode($request['json']);
        curl_setopt_array($curl, array(
          //  CURLOPT_PORT =>  $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getLinkAplicare()."aplicaresws/rest/bed/create/".$kodeppk,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }


    public function getKetersedianKamarRS($kodeppk,$start, $limit) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
           // CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getLinkAplicare()."aplicaresws/rest/bed/read/".$kodeppk."/".$start."/".$limit,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
    public function hapusRuangan($kodeppk, Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);
        $curl = curl_init();

//        { "kodekelas":"VIP",
//            "koderuang":"RG01"
//          }


        $dataJsonSend = json_encode($request['json']);
        curl_setopt_array($curl, array(
         //   CURLOPT_PORT =>  $this->getPortBrigdingBPJS(),
            CURLOPT_URL=> $this->getLinkAplicare()."aplicaresws/rest/bed/delete/".$kodeppk,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: Application/x-www-form-urlencoded",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getInformasiDukcapilFromNIK(Request $request) {
     
        $nik = $request['nik'];
        $user_id = $request['user_id'];
        $password = $request['password'];
        $ip_user = $request['ip_user'];

        $dataJsonSend = json_encode( $request['data']);
           // return $dataJsonSend;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_PORT => $request['port'],
          CURLOPT_URL =>$request['url'],
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
            $status['status']= 400;
            $result= array(
                'error'=> $err 
            );
        } else {
            $status['status']= 200;
            $result = (array) json_decode($response);
        }
        return $this->respond($result,  $status);

    }
	public function getRuanganBPJSInternal(Request $request)
	{

		$data = \DB::table('ruangan_m')
			->select('*')
			->where('statusenabled', true)
			->whereNotNull('kdinternal')
			->orderBy('namaruangan');

		$data = $data->get();

		return $this->respond($data);
	}

    public function getNoSEPByNorecPd(Request $request){
        $norec_pd = $request['norec_pd'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int)$kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pemakaianasuransi_t as pem','pem.noregistrasifk','=','pd.norec')
            ->select('pd.noregistrasi','pem.nosep')
            ->where('pd.kdprofile',$idProfile)
            ->where('pd.norec',$norec_pd);
        $data = $data->first();
        return $this->respond($data);
    }
      public function getNoSEPByNorecPd2(Request $request){
        $norec_pd = $request['norec_pd'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int)$kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pemakaianasuransi_t as pem','pem.noregistrasifk','=','pd.norec')
            ->select('pd.noregistrasi','pem.nosep')
            ->where('pd.kdprofile',$idProfile)
            ->where('pd.norec',$norec_pd);
        $data = $data->first();
        return $this->respond($data);
    }
    public function getHistoryPelayananPeserta(Request $request) {
        $data = $this->getIdConsumerBPJS();
        $secretKey = $this->getPasswordConsumerBPJS();
        // Computes the timestamp
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // Computes the signature by hashing the salt with the secret key as the key
        $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // base64 encode…
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();
        //Fungsi :Data Kunjungan
        //Method : GET
        //Format : Json
        //Content-Type: application/json; charset=utf-8
        //Parameter 1 : Tanggal Pulang format: yyyy-mm-dd
        // Parameter 2 : Jenis Pelayanan (1. Inap 2. Jalan)
        // Parameter 3 : Status Klaim (1. Proses Verifikasi 2. Pending Verifikasi 3. Klaim)
        // URL : http://localhost:8000/service/transaksi/bpjs/monitoring/HistoriPelayanan/NoKartu/0002235322484/tglAwal/2018-10-01/tglAkhir/2018-10-21
        $tglMulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $tglAkhir = Carbon::now()->format('Y-m-d');
        $noKartu= $request['noKartu'];
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL =>$this->getUrlBrigdingBPJS()."monitoring/HistoriPelayanan/NoKartu/".$noKartu."/tglAwal/".$tglMulai."/tglAkhir/".$tglAkhir,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "X-cons-id: ".  (string)$data,
                "X-signature: ". (string)$encodedSignature,
                "X-timestamp: ". (string)$tStamp
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        return $this->respond($result);
    }
     public function getComboBPJS(Request $request){
    
  
        $upf = \DB::table('ruangan_m as st')
            ->select('st.id','st.namaruangan')
            ->where('st.statusenabled', true)
             ->whereIn('st.objectdepartemenfk',[18,28,24,3,27,26,34,30,45,16,35,17])
            ->orderBy('st.namaruangan')
            ->get();
    
        $result = array(
            'upf' => $upf,
            'message' => 'er@epic',
        );

        return $this->respond($result);
    }

  public function generateSEPDummy (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $kdppk = $request['kodeppk'];
        $sep = $this->generateCodeBySeqTable(new PemakaianAsuransi, 'nosep', 19,$kdppk.'0420V',$kdProfile);
        if ($sep == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }


        return $this->respond($sep);
    }
    public function getKetersediaanTTNew(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $paramRuangan = '';
        if(isset($request['namaruangan'])  && $request['namaruangan']!='undefined' && $request['namaruangan'] !='' ){
            $paramRuangan = " and ru.namaruangan ilike '%".$request['namaruangan']."%'";
        }
        $paramKelas = '';
        if(isset($request['kelas'])  && $request['kelas']!='undefined' && $request['kelas'] !='' ){
            $paramKelas = " and kl.namakelas ilike '%".$request['kelas']."%'";
        }
        $data = DB::select(DB::raw("SELECT
            x.kodekelas,
                x.koderuang,
                x.namaruang,
                COUNT (x.id) AS kapasitas,
        -- SUM (tersediapriawanita) + SUM (tersediawanita)+   SUM (tersediapria)
                0 AS tersedia,
                SUM (tersediapria) AS tersediapria,
                SUM (tersediawanita) AS tersediawanita,
                SUM (tersediapriawanita) AS tersediapriawanita,
           
                x.namakelas
            FROM
                (
                    SELECT
                        ru.id AS koderuang,
                    
                        kmr.id,
                        kmr.namakamar,
                        kl.namaexternal AS kodekelas,
                        kl.namakelas,
                        ru.id AS id_ruangan,
                        ru.namaruangan AS namaruang,
                      ru.jenis,
                        CASE
                    WHEN ru.jenis = 'male' THEN
                        1
                    ELSE
                        0
                    END AS tersediapria,
                    CASE
                WHEN ru.jenis = 'female' THEN
                    1
                ELSE
                    0
                END AS tersediawanita,
                     CASE
                WHEN ru.jenis = 'mix' THEN
                    1
                ELSE
                    0
                END AS tersediapriawanita
                FROM
                    tempattidur_m AS tt
                LEFT JOIN statusbed_m AS sb ON sb.id = tt.objectstatusbedfk
                LEFT JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                LEFT JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                LEFT JOIN kelas_m AS kl ON kl.id = kmr.objectkelasfk
                WHERE
                    tt.statusenabled = true
              and  ru.objectdepartemenfk IN (16, 35)
              and ru.kdprofile=$kdProfile
              
              
                $paramRuangan
                  $paramKelas
                ) AS x
            GROUP BY
                x.kodekelas,
                x.koderuang,
                x.namaruang,
                x.namakelas
            ORDER BY
                x.kodekelas"));
        // return $this->respond(  $data );

        // foreach ($data as $key => $values) {
        // $dat = 0;
        // if($values->tersediawanita == '0' && $values->tersediapria == '0' ){
        //     $dat =(float) $values->kapasitas /2;
        //     // return    $dat;
        //     if($this->is_decimal($dat)){
        //         $values->tersediawanita =$values->kapasitas ;
        //     }else{
        //         $values->tersediapria = $dat;
        //         $values->tersediawanita = $dat;
        //     }
        // }

        // if(( $values->tersediawanita != '0' && $values->tersediapria != '0'  )&&(
        //     (float) $values->tersediawanita +(float) $values->tersediapria  != (float) $values->kapasitas) ){
        //     $tot  = 0;
        //     $tot2  = 0;
        //     $totakhir =0;
        //     $tot = (float) $values->tersediawanita +(float) $values->tersediapria  ;
        //     $tot2 = (float) $values->kapasitas - $tot ;

        //     $totakhir = $tot2 /2;

        //     if($this->is_decimal($totakhir)){
        //         $values->tersediapria =(float)$values->tersediapria +$tot2  ;
        //     }else{
        // $values->tersedia = (float)$values->tersediapria+ $totakhir ;
        //         $values->tersediawanita = (float)$values->tersediawanita+$totakhir ;
        //     }
        // }
        # code...
        // }
        // return $data;


        $pasien = DB::select(DB::raw("SELECT
                pd.noregistrasi,
                    pd.tglregistrasi,
                    pd.tglpulang,
                    kl.namaexternal AS kodekelas,
                    ru.id AS koderuang,
                   DATE_PART('day',  CASE
                    WHEN pd.tglpulang IS NULL THEN
                    now()
                    ELSE
                    pd.tglpulang
                    END -pd.tglregistrasi )as hari,case when ps.objectjeniskelaminfk =1 then 'male' else 'female' end as jenis
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                INNER JOIN kelas_m AS kl ON kl.ID = pd.objectkelasfk
                INNER JOIN pasien_m AS ps ON ps.id= pd.nocmfk
                WHERE
                    ru.objectdepartemenfk = 16
                AND pd.tglpulang IS NULL
                and pd.statusenabled=true
                 and pd.kdprofile=$kdProfile
                 $paramRuangan
                    $paramKelas
                ORDER BY
                    pd.tglregistrasi ASC
                "));
        $total_tt=0;
        foreach ($data as $key => $ruang) {
            $total_tt=(float)$ruang->kapasitas + $total_tt;
            foreach ($pasien as $key => $psn) {
                # code...
                // if( $ruang->tersediapria ==0){
                // $psn->tersedia      ='female';
                // }
                if($psn->jenis == 'male' &&  $ruang->tersediapria !=0  ){
                    if($ruang->koderuang  ==  $psn->koderuang && $ruang->kodekelas  ==  $psn->kodekelas
                    ){
                        $ruang->tersediapria  = (float)$ruang->tersediapria - 1;

                    }
                }
                if($psn->jenis == 'male' &&  $ruang->tersediapria ==0  ){
                    if($ruang->koderuang  ==  $psn->koderuang && $ruang->kodekelas  ==  $psn->kodekelas
                    ){
                        $ruang->tersediapriawanita  = (float)$ruang->tersediapriawanita - 1;

                    }
                }
                if($psn->jenis == 'female' &&  $ruang->tersediawanita !=0  ){
                    if($ruang->koderuang  ==  $psn->koderuang && $ruang->kodekelas  ==  $psn->kodekelas
                    ){
                        $ruang->tersediawanita  = (float)$ruang->tersediawanita    - 1;

                    }
                }
                if($psn->jenis == 'female' &&  $ruang->tersediawanita ==0  ){
                    if($ruang->koderuang  ==  $psn->koderuang && $ruang->kodekelas  ==  $psn->kodekelas
                    ){
                        $ruang->tersediapriawanita  = (float)$ruang->tersediapriawanita - 1;

                    }
                }

            }
            # code...
        }
        // return $datas = array('da' => $data, 'psn' => $pasien );
        // $terpakai = 0;
        // $kosong = 0;
        // foreach ( $data as $key => $value) {
        //    if( (float)$value->kosongMale!=0){
        //         $value->kosongMale =(float) $value->kosongMale -(float) $value->terpakaiMale;
        //    }
        //    if( (float)$value->tersediawanita!=0){
        //         $value->kosongFemale =(float) $value->kosongFemale -(float) $value->terpakaiFemale;
        //    }


        //    // if(((float) $value->total_tt !=))
        //    # code...
        // }
        foreach ($data as $key ) {
            $key->tersedia =($key->tersediapriawanita +$key->tersediawanita    +$key->tersediapria  );
        }
        // $res =  array(
        //             'total_tt' => $total_tt,
        //          'totalPakai' => $terpakai,
        //          'kosong' => $kosong,
        //      'data' => $data
        //  );
        return $this->respond($data);

    }
    public function saveMonitoringKlaim(Request $request)
    {
          $kdProfile = $this->getDataKdProfile($request);
          DB::table('monitoringklaim_t')
              ->where('jenispelayanan',$request['jenispelayanan'])
              ->where('statusklaimfk',$request['statusklaimfk'])
              ->whereRaw("to_char(tglpulang,'yyyy-MM') ='$request[bulan]'")
              ->delete();

          $dataInsert = [];
          $newData2  = $request['details'];
          foreach ($newData2 as $item) {
                $dataInsert[] = array(
                    'norec' => substr(Uuid::generate(), 0, 32),
                    'kdprofile' => $kdProfile,
                    'statusenabled' => true,
                    'nofpk' => $item['nofpk'],
                    'tglpulang' => $item['tglpulang'],
                    'jenispelayanan' => $request['jenispelayanan'],
                    'nosep' => $item['nosep'],
                    'status' => $item['status'],
                    'totalpengajuan' => $item['totalpengajuan'],
                    'totalsetujui' => $item['totalsetujui'],
                    'totaltarifrs' => $item['totaltarifrs'],
                    'statusklaimfk' => $request['statusklaimfk'],
                    'totalgrouper' => $item['totalgrouper'],
                );
               

                if (count($dataInsert) > 100){
                    DB::table('monitoringklaim_t')->insert($dataInsert);
                    $dataInsert = [];
                }
          }

          DB::table('monitoringklaim_t')->insert($dataInsert);

          $result = array(
                'data3' => $dataInsert,
                'message' => 'er@epic',
                'messages' => 'Sukses',
          );

          return $this->respond($result);
    }


   
    public function getMonitoringKlaimStts(Request $request)
    {
          $kdProfile = $this->getDataKdProfile($request);
          $data = DB::table('monitoringklaim_t')
              ->where('jenispelayanan',$request['jenispelayanan'])
              ->where('statusklaimfk',$request['statusklaimfk'])
              ->whereRaw("to_char(tglpulang,'yyyy-MM') ='$request[bulan]'")
              ->first();

          $result = array(
                'data' => $data,
                'as' => 'er@epic',
          );

          return $this->respond($result);
    }
}
