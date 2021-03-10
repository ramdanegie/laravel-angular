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

class BridgingAGIController extends ApiController
{

    use Valet, PelayananPasienTrait;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    function getUrl (){
        return 'https://115.85.74.55:7065';
    }
    function getHeaderBAGI($Method,$RelativeUrl,$RequestBody){
        date_default_timezone_set('Asia/Jakarta');
        $tStamp = date('c'); // yyyy-MM-dd'T'HH:mm:ss.SSSZ;
        $secretKey = 'TEST';
        $ApiKey = 'e03f8697-e782-4b50-8ade-9fb5b8ef9a05';

        if($Method == 'GET'){
            $RequestBody = '';
        }
        $encoderData  = json_encode($RequestBody);

        $jsonBodyEscaped                = preg_replace('/"/', '\\"', $encoderData);
        $jsonBodyEscaped                = preg_replace('/\\\\/', '\\', $jsonBodyEscaped);

        $path = public_path('jar/lib-hmac-bag.jar');
        $signature = shell_exec('java -jar '.$path.' "'.$ApiKey.'" "'.$Method.'" "'.$RelativeUrl.'" "'.$jsonBodyEscaped.'" "'.$tStamp.'"');

        $header = array(
            "API-Key: ".$ApiKey,
            "Content-Type: application/json",
            "Timestamp: ".$tStamp,
            "Signature: ".$signature,
        );

        return $header;
    }
    function getHeaderBAGI2($Method,$RelativeUrl,$RequestBody){
        date_default_timezone_set('Asia/Jakarta');
//        $timestamp = new \DateTime();
//        $timestamp->format(\DateTime::ISO8601); // Works the same since const ISO8601 =
//        $timestamp->format('c'); // Returns ISO8601 in proper format

//        $tStamp = date('Y-m-d');//date('c'); // yyyy-MM-dd'T'HH:mm:ss.SSSZ;
//        $tStamp = date('Y-m-d').'T'.date('H:i:s.SSSTZD');

        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
//        $micro= substr($micro,0,3);
        $d = new \DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

        $tStamp= $d->format("Y-m-d\TH:i:s.") . substr($d->format('u'),0,3).$d->format('O'); // note at point on "u"
//        echo date('Y-m-d\TH:i:s.SSSO');
//        dd($tStamp);
        $secretKey = 'TEST';
        $ApiKey = 'e03f8697-e782-4b50-8ade-9fb5b8ef9a05';

        if($Method == 'GET'){
            $RequestBody = '';
        }
        $encoderData  = json_encode($RequestBody);

        $jsonBodyEscaped                = preg_replace('/"/', '\\"', $encoderData);
        $jsonBodyEscaped                = preg_replace('/\\\\/', '\\', $jsonBodyEscaped);

        $path = public_path('jar/lib-hmac-bag.jar');
        $signature = shell_exec('java -jar '.$path.' "'.$ApiKey.'" "'.$Method.'" "'.$RelativeUrl.'" "'.$jsonBodyEscaped.'" "'.$tStamp.'"');
        $sign = $this->test($ApiKey, $ApiKey, $RelativeUrl, $encoderData, $tStamp, $path);


//        $header = array(
//            "API-Key: ".$ApiKey,
//            "Content-Type: application/json",
//            "Timestamp: ".$tStamp,
//            "Signature: ".$signature,
//        );
        $header []=  "API-Key: $ApiKey";
        $header []=  "Content-Type: application/json";
        $header []=  "Timestamp: $tStamp";
        $header []=  "Signature: $sign";
        return $header;
    }
    function test($apik, $method, $relative, $body, $time, $fullpathjar){
        $bodyResult = str_replace('"','\"',$body);
        $signature = shell_exec('java -jar '.$fullpathjar.' "'.$apik.'" "'.$method.'" "'.$relative.'" "'.$bodyResult.'" "'.$time.'"');
        return $signature;
    }
    public function inquiryOut(Request $request)
    {
        /*
         * Mendapatkan data informasi rekening bank tujuan (Bank Lain atau Bank Artha Graha)
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];
        $relativeUrl = '/api/v1/ibft/inquiry';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI2($methods,$relativeUrl,$body);
//        return $headers;
        $dataJsonSend = $body;
        $url = $this->getUrl() .$relativeUrl;

        $response = $this->get_content($url, $dataJsonSend,$headers);
        $response_json = json_decode($response, true);
//        $dd = array(
//            'url' => $url,
//            'header' => $headers,
//            'body' => $dataJsonSend,
//            'response' => $response_json
//        );
//        return $dd;

        return $response_json;

//        $dataJsonSend =  json_encode($dataJsonSend);
//        dd($dataJsonSend);
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods,'soap-xml');

        return $this->respond($response);
    }

    public function inquiryOutTEST(Request $request)
    {
        /*
         * Mendapatkan data informasi rekening bank tujuan (Bank Lain atau Bank Artha Graha)
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];
        $relativeUrl = '/api/v1/ibft/inquiry';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI2($methods,$relativeUrl,$body);
//        return $headers;
        $dataJsonSend = $body;
        $url = $this->getUrl() .$relativeUrl;

        $response = $this->get_content($url, $dataJsonSend,$headers);
        $response_json = json_decode($response, true);
        $dd = array(
            'url' => $url,
            'header' => $headers,
            'body' => $dataJsonSend,
            'response' => $response_json
        );
        return $dd;

        return $response_json;

//        $dataJsonSend =  json_encode($dataJsonSend);
//        dd($dataJsonSend);
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods,'soap-xml');

        return $this->respond($response);
    }
    public function transferOut(Request $request)
    {
        /*
         * Transfer dari VA 3rd party ke rekening Bank Lain atau BAG
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];
        $relativeUrl = '/api/v1/ibft/transferFromVA';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI($methods,$relativeUrl,$body);

        $url = $this->getUrl() .$relativeUrl;
        $response = $this->sendBridgingCurl($headers , $body, $url, $methods,'soap-xml');
        return $this->respond($response);
    }
    public function inquiryIncomingVA(Request $request)
    {
        /*
         * Melakukan inquiry informasi VA 3rd party
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];
        $relativeUrl = '/api/v1/va/inquiry';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI($methods,$relativeUrl,$body);
//        return $headers;
        $dataJsonSend = $body;

        $url = $this->getUrl() .$relativeUrl;
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods,'soap-xml');
        return $this->respond($response);
    }
    public function notifTransfer(Request $request)
    {
        /*
         * Melakukan transferke VA 3rd party dari rekening bank lain atau BAG
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];
        $relativeUrl = '/api/v1/va/transactionNotif';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI($methods,$relativeUrl,$body);
//        return $headers;
        $dataJsonSend = $body;

        $url = $this->getUrl() .$relativeUrl;
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods,'soap-xml');
        return $this->respond($response);
    }
    public function transferOutRealAccount(Request $request)
    {
        /*
         * Mendapatkan data informasi rekening bank tujuan (Bank Lain atau Bank Artha Graha)
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];
        $relativeUrl = '/api/v1/ibft/inquiry';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI($methods,$relativeUrl,$body);
//        return $headers;
        $dataJsonSend = $body;

        $url = $this->getUrl() .$relativeUrl;
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods,'soap-xml');
        return $this->respond($response);
    }

    public function transferBankLain(Request $request)
    {
        /*
         * Transfer dari Real Account ke rekening Bank Lain atau BAG
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];

        $relativeUrl = '/api/v1/ibft/transferFromAccount';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI($methods,$relativeUrl,$body);
//        return $headers;
        $dataJsonSend = $body;

        $url = $this->getUrl() .$relativeUrl;
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods,'soap-xml');
        return $this->respond($response);
    }
    public function accounInfo(Request $request)
    {
        /*
         * Memberikan informasi saldo rekeningBank Artha Graha
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];
        $relativeUrl = '/api/v1/account/balance';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI($methods,$relativeUrl,$body);
//        return $headers;
        $dataJsonSend = $body;

        $url = $this->getUrl() .$relativeUrl;
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods,'soap-xml');
        return $this->respond($response);
    }
    public function historyTransaction(Request $request)
    {
        /*
         * Informasi histori transaksi rekening Bank Artha Graha
         */
        $kdProfile = $this->getDataKdProfile($request);
        $body =  $request->json()->all();//['req'];
        $body = $body['req'];
        $relativeUrl = '/api/v1/account/history';
        $methods = 'POST';
        $headers =  $this->getHeaderBAGI($methods,$relativeUrl,$body);
//        return $headers;
        $dataJsonSend = $body;

        $url = $this->getUrl() .$relativeUrl;
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods,'soap-xml');
        return $this->respond($response);
    }

    function get_content($url, $post = '',$header) {
//        $usecookie = __DIR__ . "/cookie.txt";
        $header[] = 'Content-Type: application/json';
        $header[] = "Accept-Encoding: gzip, deflate";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Accept-Language: en-US,en;q=0.8,id;q=0.6";
//        dd($post);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        // curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36");

        if ($post)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//dd($post);
        $rs = curl_exec($ch);

        if(empty($rs)){
            var_dump($rs, curl_error($ch));
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $rs;
    }
}