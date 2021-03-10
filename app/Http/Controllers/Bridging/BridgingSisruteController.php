<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 15/08/2019
 * Time: 10.26
 * SISRUTE (Sistem Informasi Rujukan Terintegrasi)
 */

namespace App\Http\Controllers\Bridging;

use App\Http\Controllers\ApiController;
use App\Master\Pegawai;
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

class BridgingSisruteController extends  ApiController
{

    use Valet, PelayananPasienTrait;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    // protected  function userSisrute (){
    //    return  $this->getuserIdSisrute();//$this->settingDataFixed('userIdSisrute');
    // }
    // protected  function passwordSisrute (){
    //     return $this->passwordSisrute();
    // }
    public function getFaskes(Request $request) {
        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $param = '';
        if(isset($request['query']) && $request['query']!=''){
            $param = "?query=".$request['query'];
        }
        if(isset($request['kode']) && $request['kode']!=''){
            $param = "/".$request['kode'];
        }
        $urlSetting = $this->getUrlSisrute();
        $url = $urlSetting."/referensi/faskes".$param;
        $method = "GET"; // POST / PUT / DELETE
        $postdata = "";
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

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
    public function getAlasanRujukan(Request $request) {
        // id & pass dari kemkes
        $id = 3174260;
        $pass = md5("12345");

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $param = '';
        if(isset($request['query']) && $request['query'] != ''){
            $param = "?query=".$request['query'];
        }
        if(isset($request['kode']) && $request['kode']!=''){
            $param = "/".$request['kode'];
        }
        $urlSetting = $this->getUrlSisrute();
        $url = $urlSetting."/referensi/alasanrujukan".$param;
        $method = "GET"; // POST / PUT / DELETE
        $postdata = "";
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

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
    public function getDiagnosa(Request $request) {
        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $param = '';
        if(isset($request['query']) && $request['query']!=''){
            $param = "?query=".$request['query'];
        }
        if(isset($request['kode']) && $request['kode']!=''){
            $param = "/".$request['kode'];
        }

        $url =  $this->getUrlSisrute()."/referensi/diagnosa".$param;
        $method = "GET"; // POST / PUT / DELETE
        $postdata = "";
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

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
    public function getDiagnosaPaging(Request $request) {
        $req = $request->all();
        $dataReq = $req['filter']['filters'][0]['value'];
        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $url = $this->getUrlSisrute()."/referensi/diagnosa?query=".$dataReq;
        $method = "GET"; // POST / PUT / DELETE
        $postdata = "";
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $res = $result['data'];
        return $this->respond($res);

    }
    public function getFaskesPaging(Request $request) {
        $req = $request->all();
        if(isset($req['filter']['filters'][0]['value'])){
            $dataReq = $req['filter']['filters'][0]['value'];
        }else{
            $dataReq = '';
        }

        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $url = $this->getUrlSisrute()."/referensi/faskes?query=".$dataReq;
        $method = "GET"; // POST / PUT / DELETE
        $postdata = "";
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $res = $result['data'];
        return $this->respond($res);

    }
    //region Get data Rujukan
    /** untuk menampilkan rujukan dari luar
     * jika ingin menampilkan rujukan yg telah di buat tambahkan parameter create= true
     */
    public function getRujukan(Request $request) {

        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $param1 = '';
        $param2 = '';
        $param3 = '';
        if(isset($request['create']) && $request['create']!=''){
            $param1 ='create='. $request['create'];
        }
        if(isset($request['nomor']) && $request['nomor']!=''){
            $param2 ='&nomor='.$request['nomor'];
        }
        if(isset($request['tanggal']) && $request['tanggal']!=''){
            $param3 = '&tanggal='.$request['tanggal'];
        }


        $url = $this->getUrlSisrute()."/rujukan?".$param1.$param2.$param3;
        $method = "GET"; // POST / PUT / DELETE
        $postdata = "";
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

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
    //endregion

    //region Insert data Rujukan
    public function postRujukan(Request $request) {
        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $url = $this->getUrlSisrute()."/rujukan";
        $method = "POST"; // POST / PUT / DELETE

        $postdata = json_encode($request['data']);
//        return $this->respond($postdata);
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-type: application/vnd.rujukan.v1+json",
//            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }

        $transMessage = $result['detail'];
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    //endregion

    //region Update data Rujukan
    public function putRujukan(Request $request) {
        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $nomor = '';
        if(isset($request['nomor']) && $request['nomor']!= ''){
            $nomor = "/".$request['nomor'];
        }
        $url = $this->getUrlSisrute()."/rujukan".$nomor;
        $method = "PUT"; // POST / PUT / DELETE
        //region Format Data Send
//        {
//            "PASIEN": {
//            "NORM": 11223345,
//              "NIK": "7371140101010003",
//              "NO_KARTU_JKN": "0000001234501",
//              "NAMA": "Rahmat Hidayat",
//              "JENIS_KELAMIN": "1",
//              "TANGGAL_LAHIR": "1980-01-03",
//              "TEMPAT_LAHIR": "Makassar",
//              "ALAMAT": "Pettarani",
//              "KONTAK": "085123123122"
//           },
//           "RUJUKAN": {
//                    "JENIS_RUJUKAN": "2",
//              "TANGGAL": "2018-08-29 10:00:00",
//              "FASKES_TUJUAN": "3404015",
//              "ALASAN": "1",
//              "ALASAN_LAINNYA": "Pusing",
//              "DIAGNOSA": "I10",
//              "DOKTER": {
//                        "NIK": "7371140101010111",
//                 "NAMA": "Dr. Raffi"
//              },
//              "PETUGAS": {
//                        "NIK": "7371140101010112",
//                 "NAMA": "Enal"
//              }
//           },
//           "KONDISI_UMUM": {
//                    "KESADARAN": "1",
//              "TEKANAN_DARAH": "120/90",
//              "FREKUENSI_NADI": "50",
//              "SUHU": "37",
//              "PERNAPASAN": "25",
//              "KEADAAN_UMUM": "sesak, gelisah",
//              "NYERI": 0,
//              "ALERGI": "-"
//           },
//           "PENUNJANG": {
//                    "LABORATORIUM": "WBC:11,2;HB:15,6;PLT:215;",
//              "RADIOLOGI": "EKG:Sinus Takikardi;Foto Thorax:Cor dan pulmo normal;",
//              "TERAPI_ATAU_TINDAKAN": "TRP:LOADING NACL 0.9% 500 CC;INJ. RANITIDIN 50 MG;#TDK:TERPASANG INTUBASI ET NO 8 BATAS BIBIR 21CM;"
//           }
//        }

        //endregion
        $postdata = json_encode($request['data']);
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-type: application/vnd.rujukan.v1+json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

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
    //endregion

    //region Jawab/ Respon Rujukan yang di terima dari faskes perujuk
    public function jawabRujukan(Request $request) {
        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());
        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $nomor = '';
        if(isset($request['nomor']) && $request['nomor']!= ''){
            $nomor = "/".$request['nomor'];
        }
        $url = $this->getUrlSisrute()."/rujukan/jawab".$nomor;
        $method = "PUT"; // POST / PUT / DELETE

        //region Format Data Send
//        {
//            "DITERIMA": 1, # Status Jawaban Rujukan 1. Diterima, 0. Tidak Diterima
//           "KETERANGAN": "Silahkan rujuk pasien tersebut", # Keterangan Diterima / Tidak Diterima
//           "PETUGAS": {
//                    "NIK": "7371140101010015", # Nomor Induk Kependudukan Petugas
//              "NAMA": "Sultan"           # Nama Petugas
//           }
//        }

        //endregion

        $postdata = json_encode($request['data']);
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $transMessage = $result['detail'];
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    //endregion

    //region Pembatalan Rujukan
    public function batalRujukan(Request $request) {
        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $nomor = '';
        if(isset($request['nomor']) && $request['nomor']!= ''){
            $nomor = "/".$request['nomor'];
        }
        $url = $this->getUrlSisrute()."/rujukan/batal".$nomor;
        $method = "PUT"; // POST / PUT / DELETE

        //region Format Data Send
//        {
//            "PETUGAS": {
//            "NIK": "7371140101010015", # Nomor Induk Kependudukan Petugas
//              "NAMA": "Sultan"           # Nama Petugas
//           }
//        }

        //endregion

        $postdata = json_encode($request['data']);

        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        $transMessage = $result['detail'];
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    //endregion

    //region Notif Rujukan
    public function notifRujukan(Request $request) {
        // id & pass dari kemkes
        $id = $this->userSisrute();
        $pass = md5( $this->passwordSisrute());

        // get Timestamp
        $dt = new \DateTime(null,new \DateTimeZone("UTC"));
        $timeStamp = $dt->getTimestamp();

        // generate signature
        $key = $id."&".$timeStamp;
        $signature = base64_encode(hash_hmac("sha256",utf8_encode($key), utf8_encode($pass),true));

        $nomor = '';
        if(isset($request['nomor']) && $request['nomor']!= ''){
            $nomor = "/".$request['nomor'];
        }
        $url = $this->getUrlSisrute()."/rujukan/notif".$nomor;
        $method = "PUT"; // POST / PUT / DELETE

        //region Format Data Send
        //endregion

        $postdata = json_encode($request['data']);
        $headers = [
            "X-cons-id: ".$id,
            "X-Timestamp: ".$timeStamp,
            "X-signature: ".$signature,
            "Content-type: application/json",
            "Content-type: 	application/vnd.rujukan.v1+json",
            "Content-length: ".strlen($postdata)
        ];

        // Gunakan curl untuk mengakses/merequest alamat api
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

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
    //endregion

    public function getPasienByNoCMSisrute(Request $request) {
        $data = \DB::table('pasien_m as ps')
            ->leftJOIN ('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin ('pendidikan_m as pdd','ps.objectpendidikanfk','=','pdd.id')
            ->leftjoin ('pekerjaan_m as pk','ps.objectpekerjaanfk','=','pk.id')
            ->leftjoin ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
//            ->leftjoin ('pasiendaftar_t as pd','pd.nocmfk','=','ps.id')
            ->select('ps.nocm','ps.id as nocmfk','ps.namapasien','ps.objectjeniskelaminfk','jk.jeniskelamin','ps.tgllahir',
                    'alm.alamatlengkap','pdd.pendidikan','pk.pekerjaan','ps.noidentitas','ps.notelepon','ps.nobpjs',
                'ps.tempatlahir','ps.nohp')
            ->where('ps.nocm', $request['nocm'])
            ->first();

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getComboSisrute(Request $request) {
        $dokter = Pegawai::where('objectjenispegawaifk',1)
            ->orderBy('namalengkap')
            ->where('statusenabled',true)->get();
        $pegawai = Pegawai::where('statusenabled',true)
            ->orderBy('namalengkap')->get();
        $result = array(
            'dokter'=> $dokter,
            'pegawai'=> $pegawai,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
}
