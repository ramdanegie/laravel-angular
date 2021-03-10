<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 14/11/2018
 * Time: 11:23
 */


namespace App\Http\Controllers\Bridging;

use App\Http\Controllers\ApiController;
use App\Transaksi\Yankes_KetersediaanDarah;
use App\Transaksi\Yankes_MutuPelayanan;
use App\Transaksi\Yankes_PemenuhanDarah;
use App\Transaksi\Yankes_TopTenDarah;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Traits\Valet;
use Carbon\Carbon;

class BridgingYankesController extends ApiController {

    use Valet, PelayananPasienTrait;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getSignatures(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/kunjungan";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];
        return $this->respond($headers);
        //region Format Send Data
        //        {
        //            "data": {
        //            "tanggal": "2018-08-13",
        //            "kunjungan_rj": 0,
        //            "kunjungan_igd": 0,
        //            "pasien_ri": 0
        //          }
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($headers);

    }
    //region Service Kunjungan
    public function insertKunjungan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/kunjungan";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //            "data": {
        //            "tanggal": "2018-08-13",
        //            "kunjungan_rj": 0,
        //            "kunjungan_igd": 0,
        //            "pasien_ri": 0
        //          }
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateKunjungan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updatekunjungan";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//                  {"kode_kirim":"r97EvyKikGIwOU62","tanggal":"2018-08-13","kunjungan_rj":0,"kunjungan_igd":0,"pasien_ri":0}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteKunjungan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/kunjungan?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getKunjungan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tgl'];
        $urlYankes = $this->getUrlYankes()."/datakunjungan?koders=".$koders."&tgl=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service Pelayanan Rujukan
    public function insertRujukan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/rujukan";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //            "data":  {"tanggal":"2018-08-13","jumlah_rujukan":20,"jumlah_rujuk_balik":5}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function insertTopTenRujukan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/toptenrujukan";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //            "data":  {"bulan":7,"tahun":2018,"dirujuk":[{"kode_icd_10":"xx.xx","jumlah":0}]}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateRujukan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updaterujukan";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//                 {"kode_kirim":"1LDQlZ2guadNhVH2","tanggal":"2018-08-13","jumlah_rujukan":20,"jumlah_rujuk_balik":5}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateTopTenRujukan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updaterujukan";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//             {"bulan":7,"tahun":2018,"dirujuk":{[]},"kode_kirim:""}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteRujukan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/rujukan?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteTopTenRujukan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/toptenrujukan?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getRujukan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tgl'];
        $urlYankes = $this->getUrlYankes()."/datarujukan?koders=".$koders."&tgl=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service Indikator
    public function insertIndikator(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/indikator";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //          	{"periode":[],"bor":,"alos":,"bto":,"toi":,"ndr":,"gdr":,"tahun":}
        //periode diisi: 1/2/34/5/6/7/8/9/10/11/12 (untuk data bulanan) dan tw1/tw2/tw3/tw4 (untuk data triwulan)
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateIndikator(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updateindikator";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//                 {periode:,"bor":,"alos":,"bto":,"toi":,"ndr":,"gdr":,"kode_kirim":}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteIndikator(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/indikator?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getIndikator(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/dataindikator?koders=".$koders."&tahun=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service 10 BESAR PENYAKIT
    public function insertTopTenPenyakitRanap(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/toptenranap";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
//        Bulanan :
//        {"bulan":7,"tahun":2018,"[rawat_inap]/[rawat_jalan]":[{"kode_icd_10":"xx.xx","jumlah":},]}
//        Tahunan :
//        {"tahun":2018,"[rawat_inap]/[rawat_jalan]":[{"kode_icd_10":"xx.xx"},]}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function insertTopTenPenyakitRajal(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/toptenrajal";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
//        Bulanan :
//        {"bulan":7,"tahun":2018,"[rawat_inap]/[rawat_jalan]":[{"kode_icd_10":"xx.xx","jumlah":},]}
//        Tahunan :
//        {"tahun":2018,"[rawat_inap]/[rawat_jalan]":[{"kode_icd_10":"xx.xx"},]}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateTopTenPenyakitRanap(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updatetoptenranap";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//             	Bulanan : {"bulan":7,"tahun":2018,"[rawat_inap]/[rawat_jalan]":[{"kode_icd_10":"xx.xx"},],"kode_kirim":[]}
//Tahunan : {"tahun":2018",[rawat_inap]/[rawat_jalan]":[{"kode_icd_10":"xx.xx"},],"kode_kirim":[]}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateTopTenPenyakitRajal(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updatetoptenrajal";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//              Bulanan : {"bulan":7,"tahun":2018,"[rawat_inap]/[rawat_jalan]":[{"kode_icd_10":"xx.xx"},],"kode_kirim":[]}
//Tahunan : {"tahun":2018",[rawat_inap]/[rawat_jalan]":[{"kode_icd_10":"xx.xx"},],"kode_kirim":[]}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteTopTenPenyakitRanap(Request $request) {
    $xrsid = 3174260;
    $xpass = md5("12345");
    date_default_timezone_set('UTC');
    $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

    $kodeGenerate = $request['kode'];
    $urlYankes = $this->getUrlYankes()."/toptenranap?kode=".$kodeGenerate;

    $postdata = json_encode($request['data']);
    $method = "DELETE";
    $headers = [
        "X-rs-id: $xrsid",
        "X-pass: $xpass",
        "X-Timestamp: $tStamp",
        "Content-type: application/x-www-form-urlencoded",
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
        $result= "Ada Kesalahan #:" . $err;
    } else {
        $result = (array) json_decode($response);
    }
    return $this->respond($result);

}
    public function deleteTopTenPenyakitRajal(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/toptenrajal?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getTopTenPenyakitRanap(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/data10ranap?koders=".$koders."&tahun=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getTopTenPenyakitRajal(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/data10rajal?koders=".$koders."&tahun=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service Jumlah Kematian
    public function insertKematian(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/kematian";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //          	{"bulan":1,"tahun":2018,"data_kematian":[{"kode_ruang":"1","jumlah":"22"},{"kode_ruang":"6","jumlah":"63"}]}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateKematian(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/update_kematian";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//            	{"bulan":1,"tahun":2018,"data_kematian":[{"kode_ruang":"1","jumlah":"22"},{"kode_ruang":"6","jumlah":"63"}],"kode_kirim":"t8vfRadYF61DBEw1"}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteKematian(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/kematian?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getKematian(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/datakematian?koders=".$koders."&tahun=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getMasterRuang(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/master_ruang";

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service Hasil Pemeriksaan Lab
    public function insertLab(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/lab";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //          		{"bulan":7,"tahun":2018,"data_pemeriksaan":[{"kode":"2","ratarata":11,"pasien":100},]}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateLab(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updatelab";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//            {"bulan":7,"tahun":2018,"data_pemeriksaan":[{"kode":"2","ratarata":11,"pasien":100},],"kode_kirim":""}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteLab(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/lab?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getLab(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/datalab?koders=".$koders."&tahun=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getMasterLab(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/master_lab";

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service Golongan Darah
    public function insertGolonganDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/darah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //          {"bulan":"1","tahun":"2018","kode_darah":"2","jumlah":100}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateGolonganDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updatedarah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//         {"kode_kirim":"6vRF8e7IdN5E4y22","bulan":"2","tahun":"2018","kode_darah":"6","jumlah":"200"}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteGolonganDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/darah?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getGolonganDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/datadarah?koders=".$koders."&tahun=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getMasterGolonganDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/master_darah";

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service Hasil Pemeriksaan Radiologi
    public function insertRadiologi(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/radiologi";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //      	{"bulan":"1","tahun":"2018","kode_rad":"2","jumlah":100}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateRadiologi(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updateradiologi";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//       	{"kode_kirim":"2T8dRv4KX3cEFyw2","bulan":"2","tahun":"2018","kode_rad":"1","jumlah":50}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteRadiologi(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/rad?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getRadiologi(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/dataradiologi?koders=".$koders."&tahun=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getMasterRadiologi(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/master_radiologi";

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service Stok Darah
    public function insertStokDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/stokdarah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
            //      	{"tanggal":"2018-10-09","kode_gol_darah":"2","jumlah":10,"penggunaan":7}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateStokDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/updatestokdarah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//       {"kode_kirim":"T3LC80ufOItx9Wa1","tanggal":"2018-10-09","kode_gol_darah":"4","jumlah":8,"penggunaan":4}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteStokDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/stokdarah?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function getStokDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tahun'];
        $urlYankes = $this->getUrlYankes()."/datastokdarah?koders=".$koders."&tgl=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service  Pemenuhan Permintaan Darah
    public function insertPemenuhanDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/pemenuhan_darah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //      		{"bulan":"01","pemenuhan_darah":"7","permintaan_terpakai":10}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updatePemenuhanDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/update_pemenuhan_darah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//      	{"kode_kirim":"Ozpgn76qjUI0Jhw2","bulan":"01","pemenuhan_darah":"7","permintaan_terpakai":10}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deletePemenuhanDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/pemenuhan_darah?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service 10 Besar penyakit yang membutuhkan transfusi darah
    public function insertTopTenDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/topten_darah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //      		{"bulan":"01","kode_icd":"77.00","jumlah":10}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateTopTenDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/update_topten_darah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//      	{"kode_kirim":"BFgzEc35G1vRVb81","bulan":"01","kode_icd":"77.00","jumlah":10}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteTopTenDarah(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/topten_darah?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    //region Service Mutu Pelayanan
    public function insertMutuPelayanan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/topten_darah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
        //        {
        //      		{"bulan":"01","kode_icd":"77.00","jumlah":10}
        //        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function updateMutuPelayanan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $urlYankes = $this->getUrlYankes()."/update_topten_darah";

        $postdata = json_encode($request['data']);
        $method = "POST";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        //region Format Send Data
//        {
//            "data" : {
//      	{"kode_kirim":"BFgzEc35G1vRVb81","bulan":"01","kode_icd":"77.00","jumlah":10}
//             }
//        }
        //endregion

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    public function deleteMutuPelayanan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $kodeGenerate = $request['kode'];
        $urlYankes = $this->getUrlYankes()."/topten_darah?kode=".$kodeGenerate;

        $postdata = json_encode($request['data']);
        $method = "DELETE";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }
    //endregion

    public function getDiagnosaPart(Request $request) {
        $req = $request->all();
        $diagnosa = DB::table('diagnosa_m')
            ->select('id','kddiagnosa','namadiagnosa')
            ->where('statusenabled',true);

        if(isset($req['namadiagnosa']) &&
            $req['namadiagnosa']!="" &&
            $req['namadiagnosa']!="undefined"){
            $diagnosa = $diagnosa->where('namadiagnosa','ilike','%'. $req['namadiagnosa'] .'%' );
        };
        if(isset($req['kddiagnosa']) &&
            $req['kddiagnosa']!="" &&
            $req['kddiagnosa']!="undefined"){
            $diagnosa = $diagnosa->where('kddiagnosa','ilike','%'. $req['kddiagnosa'] .'%' )
             ->orWhere('namadiagnosa','ilike','%'. $req['kddiagnosa'] .'%' );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $diagnosa = $diagnosa
                ->where('kddiagnosa','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }

        $diagnosa=$diagnosa->take(10);
        $diagnosa=$diagnosa->get();
        return $this->respond($diagnosa);
    }

    public function saveLocalKetersediaanDarah(Request $request) {
        DB::beginTransaction();
        try {
            if( $request['norec'] == null){
                $new = New Yankes_KetersediaanDarah();
                $new->norec = $new->generateNewId();
                $new->kdprofile =1;
                $new->statusenabled =true;
            }else{
                $new = Yankes_KetersediaanDarah::where('norec', $request['norec'])->first();
            }
            $new->kode_kirim = $request['kode_kirim'];
            $new->tanggal = $request['tanggal'];
            $new->kode_gol_darah = $request['kode_gol_darah'];
            $new->gol_darah = $request['gol_darah'];
            $new->jumlah = $request['jumlah'];
            $new->penggunaan = $request['penggunaan'];
            $new->save();
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus == true) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getLocalStokDarah(Request $request) {
        $data = Yankes_KetersediaanDarah::where('statusenabled',true);
        if(isset($request['tgl']) && $request['tgl']!= ''){
            $data = $data->where('tanggal',$request['tgl']);
        }
        if(isset($request['kode_gol_darah']) && $request['kode_gol_darah']!= ''){
            $data = $data->where('kode_gol_darah',$request['kode_gol_darah']);
        }
        $data = $data->get();
        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);

    }
    public function deleteLocalStokDarah(Request $request) {
        $data = Yankes_KetersediaanDarah::where('norec',$request['norec'])->update([
            'statusenabled' => false
        ]);

        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);

    }

    public function saveLocalPemenuhanDarah(Request $request) {
        DB::beginTransaction();
        try {
            if( $request['norec'] == null){
                $new = New Yankes_PemenuhanDarah();
                $new->norec = $new->generateNewId();
                $new->kdprofile =1;
                $new->statusenabled =true;
            }else{
                $new = Yankes_PemenuhanDarah::where('norec', $request['norec'])->first();
            }
            $new->kode_kirim = $request['kode_kirim'];
            $new->tanggal = $request['tanggal'];
            $new->pemenuhan_darah = $request['pemenuhan_darah'];
            $new->permintaan_terpakai = $request['permintaan_terpakai'];
            $new->save();
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus == true) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getLocalPemenuhanDarah(Request $request) {
        $tgl ='';

        if (isset($request['tgl']) && $request['tgl'] != "" && $request['tgl'] != "undefined") {
            $tgl =  "and  to_char( tanggal,'YYYY-MM') =  '" . $request['tgl']."'";
        }

       $data = DB::select(DB::raw("select * from  yankes_pemenuhandarah_t 
        where statusenabled = true
        $tgl"));
        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);

    }
    public function deletelPemenuhanDarah(Request $request) {
        $data = Yankes_PemenuhanDarah::where('norec',$request['norec'])->update([
            'statusenabled' => false
        ]);

        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);

    }
    public function saveLocalMutuPelayanan(Request $request) {
        DB::beginTransaction();
        try {
            if( $request['norec'] == null){
                $new = New Yankes_MutuPelayanan();
                $new->norec = $new->generateNewId();
                $new->kdprofile =1;
                $new->statusenabled =true;
            }else{
                $new = Yankes_MutuPelayanan::where('norec', $request['norec'])->first();
            }
            $new->kode_kirim = $request['kode_kirim'];
            $new->tanggal = $request['tanggal'];
            $new->bulan = $request['bulan'];
            $new->kode_mutu = $request['kode_mutu'];
            $new->mutu_pelayanan = $request['mutu_pelayanan'];
            $new->nilai = $request['nilai'];
            $new->save();
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus == true) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getLocalMutuPelayanan(Request $request) {
        $tgl ='';

        if (isset($request['tgl']) && $request['tgl'] != "" && $request['tgl'] != "undefined") {
            $tgl =  "and  to_char( tanggal,'YYYY-MM') =  '" . $request['tgl']."'";
        }

        $data = DB::select(DB::raw("select * from  yankes_mutupelayanan_t 
        where statusenabled = true
        $tgl"));
        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);

    }
    public function deletelMutuPelayanan(Request $request) {
        $data = Yankes_MutuPelayanan::where('norec',$request['norec'])->update([
            'statusenabled' => false
        ]);

        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);

    }
    public function getComboMutu(Request $request) {
      $data = DB::table('yankes_mutupelayanan_m')->get();

        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);

    }
    public function saveLocalTopTenDarah(Request $request) {
        DB::beginTransaction();
        try {
            if( $request['norec'] == null){
                $new = New Yankes_TopTenDarah();
                $new->norec = $new->generateNewId();
                $new->kdprofile =1;
                $new->statusenabled =true;
            }else{
                $new = Yankes_TopTenDarah::where('norec', $request['norec'])->first();
            }
            $new->kode_kirim = $request['kode_kirim'];
            $new->tanggal = $request['tanggal'];
            $new->bulan = $request['bulan'];
            $new->kode_icd = $request['kode_icd'];
            $new->nama_icd = $request['nama_icd'];
            $new->jumlah = $request['jumlah'];
            $new->save();
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus == true) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getLocalTopTenDarah(Request $request) {
        $tgl ='';

        if (isset($request['tgl']) && $request['tgl'] != "" && $request['tgl'] != "undefined") {
            $tgl =  "and  to_char( tanggal,'YYYY-MM') =  '" . $request['tgl']."'";
        }

        $data = DB::select(DB::raw("select * from  yankes_toptendarah_t 
        where statusenabled = true
        $tgl"));
        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);

    }
    public function deletelTopTenDarah(Request $request) {
        $data = Yankes_TopTenDarah::where('norec',$request['norec'])->update([
            'statusenabled' => false
        ]);

        $result = array(
            'list' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }
    public function countKunjungan(Request $request)
    {
        $tglAwal = date('Y-m-d 00:00');
        $tglAkhir = date('Y-m-d H:i:s');

        $data = DB::select(DB::raw("
                     select dp.id ,dp.namadepartemen,count(apd.norec) as jumlah
                    from antrianpasiendiperiksa_t  as apd 
                    join ruangan_m as ru on ru.id =apd.objectruanganfk
                    join departemen_m as dp on dp.id =ru.objectdepartemenfk
                    left join batalregistrasi_t as br on br.pasiendaftarfk=apd.noregistrasifk
                    where apd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
                    --and ru.objectdepartemenfk =24
                    --and br.norec is null
                    group by dp.namadepartemen,dp.id 
            "));
        $dataRanap = DB::select(DB::raw("select count(x.noregistrasi) as jumlah  
            from ( select  pd.noregistrasi,pd.tglregistrasi
            from pasiendaftar_t as pd 
            inner join ruangan_m as ru on ru.id = pd.objectruanganlastfk
            --where ru.objectdepartemenfk ='16'
            and pd.tglpulang IS NULL
         ) as x
            "));
        $dataFarmasi =DB::select(DB::raw("
             SELECT
                COUNT (x.noresep) AS jumlah
              FROM
                (
                    SELECT *
                    FROM
                        strukresep_t AS sr
                    WHERE
                        sr.tglresep BETWEEN '$tglAwal'
                    AND '$tglAkhir'
                     and (sr.statusenabled is null or sr.statusenabled = true)
                ) AS x"));

        $rawatjalan =0;
        $rawatinap=0;
        $igd =0;

        $masihDirawat=0;
        $res=[];
        if (count($dataFarmasi) > 0){
            $farmasi =$dataFarmasi[0]->jumlah;
        }
        if (count($dataRanap) > 0){
            $masihDirawat =$dataRanap[0]->jumlah;
        }
        foreach ($data as $item) {
            if ($item->id == 18) {
                $rawatjalan = $item->jumlah;
            }
            if ($item->id == 16) {
                $rawatinap = $item->jumlah;
            }

            if ($item->id == 24) {
                $igd = $item->jumlah;
            }
            $res = array(
                'rawat_jalan' => (int)$rawatjalan,
                'igd' => (int)$igd,
                'rawat_inap' => (int)$rawatinap,

            );
        }

        $result = array(
            'data' => $res,
//            'farmasi'=> (float) $farmasi,
            'masihDirawat'=>(float) $masihDirawat,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);

    }
    public function countHasilRadiologi(Request $request){
        $yearNow = date('Y');
        $data = DB::select(DB::raw("select count(x.bulan) as jumlah , x.kddiagnosa,x.namadiagnosa, x.bulan,x.tahun from (
            select  dia.kddiagnosa , dia.namadiagnosa, to_char(ddp.tglinputdiagnosa,'MM') as bulan, to_char(ddp.tglinputdiagnosa,'YYYY') as tahun
            from pasiendaftar_t as pd
            inner join antrianpasiendiperiksa_t as apd on pd.norec=apd.noregistrasifk
            inner join diagnosapasien_t as dp on dp.noregistrasifk=apd.norec
            INNER join detaildiagnosapasien_t as ddp on dp.norec =ddp.objectdiagnosapasienfk
            INNER join diagnosa_m as dia on dia.id =ddp.objectdiagnosafk
            where ddp.objectdiagnosafk  in (1163,67)
            ) as x 
            where x.tahun ='$yearNow'
            GROUP BY x.kddiagnosa,x.namadiagnosa,x.bulan,x.tahun
            order by x.bulan"));
        $tbc = [];
        $paru = [];
        foreach ($data as $item){
            if($item->kddiagnosa == 'A16.2'){
                $tbc [] = array(
                    'kddiagnosa'=> $item->kddiagnosa,
                    'namadiagnosa'=> $item->namadiagnosa,
                    'jumlah'=> (float)$item->jumlah,
                    'bulan'=> (float)$item->bulan,
                    'tahun'=> (float)$item->tahun,
                );
            }else{
                $paru [] = array(
                    'kddiagnosa'=> $item->kddiagnosa,
                    'namadiagnosa'=> $item->namadiagnosa,
                    'jumlah'=> (float)$item->jumlah,
                    'bulan'=> (float)$item->bulan,
                    'tahun'=> (float)$item->tahun,
                );
            }

        }
        $result = array(
            'tbc' =>  $tbc,
            'kankerparu' =>  $paru,
            'as' =>  'inhuman',
        );
        return $this->respond($result);

    }
    /***
     *
     *
     ***/
    public function getSiranapKunjungan(Request $request) {
        $xrsid = 3174260;
        $xpass = md5("12345");
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));

        $koders = 3174260;
        $tgl = $request['tgl'];
        $urlYankes = $this->getUrlYankes()."/datakunjungan?koders=".$koders."&tgl=".$tgl;

        $postdata = json_encode($request['data']);
        $method = "GET";
        $headers = [
            "X-rs-id: $xrsid",
            "X-pass: $xpass",
            "X-Timestamp: $tStamp",
            "Content-type: application/x-www-form-urlencoded",
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlYankes);
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
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);

    }

}
