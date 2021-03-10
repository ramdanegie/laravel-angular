<?php
namespace App\Http\Controllers\Bridging;

use App\Exceptions\BillingException;
use App\Helpers\String\DclHashing;
use App\Http\Controllers\ApiController;

use App\Traits\BniTrait;
use App\Transaksi\BNITransaction;
use App\Transaksi\BniEnc;
use App\Transaksi\VirtualAccount;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Traits\Valet;
use Carbon\Carbon;
use Webpatser\Uuid\Uuid;
use App\Transaksi\StrukBuktiPenerimaan;
use App\Transaksi\StrukBuktiPenerimaanCaraBayar;
use App\Transaksi\StrukOrder;
use App\Transaksi\StrukPelayanan;

class BridgingBNIController extends ApiController
{

    use Valet, PelayananPasienTrait,BniTrait;

    protected $request;
    protected $client_id = '13513';
    protected $secret_key = 'fda741b2e353fce9856fb0a4674095b1';
    protected $prefix = '98813513';
    protected $url = 'https://apibeta.bni-ecollection.com/';

    public function __construct()
    {
        parent::__construct($skip_authentication = true);
    }
    function getClientId (){
        return '13513';
    }
    function getSecretKey (){
        return 'fda741b2e353fce9856fb0a4674095b1';//'ea0c88921fb033387e66ef7d1e82ab83';
    }
    function getPrefix(){
        return '98813513';
    }

    public function createBilling(Request $request)
    {
        // $kdProfile = $this->getDataKdProfile($request);
        $r = $request->input();
       if($r['trx_id'] == '' || $r['trx_id'] == null){
           $r['trx_id']=  $this->generateCodeBySeqTable(new VirtualAccount(), 'trx_id', 10, date('ymd'), $this->kdProfile);
       }
//        if(date('c',strtotime($r['datetime_expired'])) < date('Y-m-d H:i:s')){
//            $r['datetime_expired'] = date('c', time() + 2 * 3600); // billing will be expired in 2 hours
//        }
//        $r['description'] =$r['description'].' ' .$r['trx_id'];
        $r['virtual_account'] =$this->getPrefix().date('ym')."0003";
        $valid = $this->validate_data($r);
        if(!$valid['status']){
            $respond = array(
                 'status' => '009',
                 'message' =>  $valid['message'] ,
             );
             return $this->respond($respond);
        }
//        dd($valid);
        $response = $this->encryptBNI($r) ;
        if($response['status'] =='105'){
            $r['trx_id']=  $this->generateCodeBySeqTable(new VirtualAccount(), 'trx_id', 10, date('ymd'), $this->kdProfile);

            $response = $this->encryptBNI($r) ;
        }
        if($response['status'] == '000'){
            $newVA = New VirtualAccount();
            $newVA->trx_id =  $response['data']['trx_id'];
            $newVA->type =  $r['type'];
            $newVA->client_id =  $r['client_id'];
            $newVA->trx_amount =  $r['trx_amount'];
            $newVA->billing_type =  $r['billing_type'];
            $newVA->customer_name =  $r['customer_name'];
            $newVA->customer_email =  $r['customer_email'];
            $newVA->customer_phone =  $r['customer_phone'];
            $newVA->datetime_expired =  $r['datetime_expired'];
            $newVA->description =  $r['description'];
            $newVA->virtual_account =  $response['data']['virtual_account'];
            $newVA->bank =  'BNI';
            $newVA->save();
        }
        return $this->respond($response);
//         $status = $this->validate_data($r);
//         if(!$status['status']){
//             $respond = array(
//                 'status' => '001',
//                 'message' =>  $status['message'] ,
//             );
//             return $this->respond($respond);
//         }
//        $data_asli = array(
//            "type"=> "createbilling",
//            "client_id" => $this->client_id,
//            "trx_id"=> $trxId,
//            "trx_amount"=> 10000,
//            "billing_type"=> "c",
//            "customer_name"=> "Mr. Egie Ramdan",
//            "customer_email"=> "ramdanegie@email.com",
//            "customer_phone"=> "082211333013",
//            "virtual_account"=> "",//$this->getPrefix()."00000001",
//            "datetime_expired"=>  $this->setDateTimeExpired(2)->dateTimeExpired,
//            "description"=> "Payment of Trx ".$trxId
//        );
//        return $this->respond($this->encryptBNI($r));
    }
    
    public function createBillingSMS(Request $request)
    {
        $r = $request->input();
        if(date('c',strtotime($r['datetime_expired'])) < date('Y-m-d H:i:s')){
            $r['datetime_expired'] = date('c', time() + 2 * 3600); // billing will be expired in 2 hours
        }
//        $status = $this->validate_data($r);
//        dd($r);
//        if (!$status['status']) {
//            $respond = array(
//                'status' => '001',
//                'message' => $status['message'],
//            );
//            return $this->respond($respond);
//        }
        return $this->respond($this->encryptBNI($r));
    }
    public function validate_data($r){
        if(isset($r['type'])){
            if($r['type'] == 'createbilling'){
                if(!isset($r['client_id'])){
                    return array('status' => false, 'message' => 'tidak input client id' );
                }
                if($r['client_id'] ==''){
                    return array('status' => false, 'message' => 'tidak input client id' );
                }
                if($r['client_id'] != $this->client_id){
                    return array('status' => false, 'message' => 'client id yang tidak terdaftar / client id milik client lain' );
                }
                if($r['trx_amount'] == '-'){
                    return array('status' => false, 'message' => 'amount tidak boleh (-)' );
                }
                if(substr($r['virtual_account'],0,8) != '98813513'){
                    return array('status' => false, 'message' => 'virtual_account tidak sesuai format' );
                }
                if(strlen($r['virtual_account']) < 16){
                    return array('status' => false, 'message' => 'virtual_account < 16 digit' );
                }
                if(strlen($r['virtual_account']) > 16){
                    return array('status' => false, 'message' => 'virtual_account > 16 digit' );
                }
                if(!isset($r['billing_type']) || !$r['billing_type']){
                    return array('status' => false, 'message' => 'billing_type harus di input' );
                }
                if(!in_array($r['billing_type'],['o','c','i','m','n','x'])){
                    return array('status' => false, 'message' => 'billing_type tidak terdaftar' );
                }
                if($r['trx_amount'] == '-'){
                    return array('status' => false, 'message' => 'amount tidak boleh (-)' );
                }
                if(!isset($r['datetime_expired']) || $r['datetime_expired'] == ''){
                    return array('status' => false, 'message' => 'datetime_expired tidak sesuai format' );
                }
                if(!isset($r['trx_id']) || $r['trx_id'] ==''){
                    return array('status' => false, 'message' => 'tidak input trx_id' );
                }
                if(strlen($r['trx_id']) > 30){
                    return array('status' => false, 'message' => 'trx_id tidak boleh lebih dari 30 digit' );
                }
                if(strlen($r['customer_phone']) <= 8){
                    return array('status' => false, 'message' => 'customer_phone tidak boleh kurang dari 8 digit' );
                }
                if(strlen($r['customer_phone']) > 13){
                    return array('status' => false, 'message' => 'customer_phone tidak boleh lebih dari 13 digit' );
                }

                return array('status' => true );
            }else  if($r['type'] == 'createbillingsms'){
                return array('status' => true );
            }else  if($r['type'] == 'inquirybilling'){
                if(!isset($r['client_id'])){
                    return array('status' => false, 'message' => 'tidak input client id' );
                }
                if($r['client_id'] ==''){
                    return array('status' => false, 'message' => 'tidak input client id' );
                }
                if($r['client_id'] != $this->client_id){
                    return array('status' => false, 'message' => 'client id yang tidak terdaftar / client id milik client lain' );
                }
                if(!isset($r['trx_id']) || $r['trx_id'] ==''){
                    return array('status' => false, 'message' => 'Billing ID harus di isi' );
                }
                if(strlen($r['trx_id']) > 30){
                    return array('status' => false, 'message' => 'trx_id tidak boleh lebih dari 30 digit' );
                }

                return array('status' => true );
            }else if($r['type'] == 'updateBilling'){
                if(!isset($r['client_id'])){
                    return array('status' => false, 'message' => 'tidak input client id' );
                }
                if($r['client_id'] ==''){
                    return array('status' => false, 'message' => 'tidak input client id' );
                }
                if($r['client_id'] != $this->client_id){
                    return array('status' => false, 'message' => 'client id yang tidak terdaftar / client id milik client lain' );
                }
                if($r['trx_amount'] == '-'){
                    return array('status' => false, 'message' => 'amount tidak boleh (-)' );
                }
                if(isset($r['virtual_account']) ){
                    return array('status' => false, 'message' => 'VA tidak bisa di update' );
                }

                if($r['trx_amount'] == '-'){
                    return array('status' => false, 'message' => 'amount tidak boleh (-)' );
                }
                if(!isset($r['datetime_expired']) || $r['datetime_expired'] == ''){
                    return array('status' => false, 'message' => 'datetime_expired tidak sesuai format' );
                }
                if(!isset($r['trx_id']) || $r['trx_id'] ==''){
                    return array('status' => false, 'message' => 'tidak input trx_id' );
                }
                if(strlen($r['trx_id']) > 30){
                    return array('status' => false, 'message' => 'trx_id tidak boleh lebih dari 30 digit' );
                }
                if(!isset($r['customer_phone']) || $r['customer_phone']==''){
                    return array('status' => false, 'message' => 'Customer Phone harus di isi' );
                }
                if(strlen($r['customer_phone']) <= 8){
                    return array('status' => false, 'message' => 'customer_phone tidak boleh kurang dari 8 digit' );
                }
                if(strlen($r['customer_phone']) > 13){
                    return array('status' => false, 'message' => 'customer_phone tidak boleh lebih dari 13 digit' );
                }

                return array('status' => true );
            }else{
                return array('status' => false, 'message' => 'type tidak sesuai' );
            }
        }
        if(!isset($r['type'])) {
            return array('status' => false, 'message' => 'tidak input type ' );
        }

        return array('status' => true );
    }
    public function encryptBNI($data_asli){
        $hashed_string = BniEnc::encrypt(
            $data_asli,
            $this->client_id,
            $this->secret_key
        );
        $data = array(
            'client_id' => $this->client_id,
            'data' => $hashed_string,
        );
//        dd($data);
        $response = $this->get_content($this->url, json_encode($data));
        $response_json = json_decode($response, true);
        if ($response_json['status'] !== '000') {
            // handling jika gagal
            return $response_json;
        }else {
            $data_response = BniEnc::decrypt($response_json['data'], $this->client_id, $this->secret_key);
            $respond = array(
                'status' => $response_json['status'],
                'data' =>  $data_response ,
            );
            return $respond;
        }
    }
    public function encryptData($requestArray){
        $requestHash = DclHashing::hashData($requestArray, $this->getClientId(), $this->getSecretKey());

        if (is_null($requestHash)) {
            throw new BillingException("Hashing data is fail");
        }

        $data = json_encode(['client_id' => $this->getClientId(), 'data' => $requestHash]);

        return $data;
    }
    public function setDateTimeExpired($dateTimeExpired)
    {
        $now = Carbon::now();

        if (is_int($dateTimeExpired)) {
            $this->dateTimeExpired = $now->addHours($dateTimeExpired)->toDateTimeString();
        } else {
            $this->dateTimeExpired = $now->addHours($this->dateTimeExpired)->toDateTimeString();
        }

        return $this;
    }
    public function inquiryBilling(Request $request)
    {
        $r = $request->input();
        $valid = $this->validate_data($r);
        if(!$valid['status']){
            $respond = array(
                'status' => '009',
                'message' =>  $valid['message'] ,
            );
            return $this->respond($respond);
        }
        $response = $this->encryptBNI($r);

        if($response['status'] == '000'){
            $newVA =  VirtualAccount::where('trx_id', $response['data']['trx_id'])->first();
            $newVA->trx_id =  $response['data']['trx_id'];
            $newVA->client_id =  $response['data']['client_id'];
            $newVA->trx_amount =  $response['data']['trx_amount'];
            $newVA->customer_name =  $response['data']['customer_name'];
            $newVA->customer_email = $response['data']['customer_email'];
            $newVA->customer_phone = $response['data']['customer_phone'];
            $newVA->datetime_expired =  $response['data']['datetime_expired'];
            $newVA->description = $response['data']['description'];
            $newVA->virtual_account =  $response['data']['virtual_account'];
            $newVA->datetime_created =  $response['data']['datetime_created'];
            $newVA->datetime_expired =  $response['data']['datetime_expired'];
            $newVA->datetime_payment =  $response['data']['datetime_payment'];
            $newVA->datetime_last_updated =  $response['data']['datetime_last_updated'];
            $newVA->payment_ntb =  $response['data']['payment_ntb'];
            $newVA->payment_amount =  $response['data']['payment_amount'];
            $newVA->va_status =  $response['data']['va_status'];
            $newVA->description =  $response['data']['description'];
            $newVA->billing_type =  $response['data']['billing_type'];
            $newVA->datetime_created_iso8601 =  $response['data']['datetime_created_iso8601'];
            $newVA->datetime_expired_iso8601 =  $response['data']['datetime_expired_iso8601'];
            $newVA->datetime_payment_iso8601 =  $response['data']['datetime_payment_iso8601'];
            $newVA->datetime_last_updated_iso8601 =  $response['data']['datetime_last_updated_iso8601'];
            $newVA->status = $r['type'];
            $newVA->save();
        }
        return $this->respond($response);
    }
    public function updateTransaction(Request $request)
    {
        $r = $request->input();
        $valid = $this->validate_data($r);
        if(!$valid['status']){
            $respond = array(
                'status' => '009',
                'message' =>  $valid['message'] ,
            );
            return $this->respond($respond);
        }
        $response = $this->encryptBNI($r);

        if($response['status'] == '000'){
            $newVA =  VirtualAccount::where('trx_id', $response['data']['trx_id'])->first();
//            $newVA->trx_id =  $response['data']['trx_id'];
            $newVA->client_id =  $r['client_id'];
            $newVA->trx_amount =  $r['trx_amount'];
            $newVA->customer_name =  $r['customer_name'];
            $newVA->customer_email =  $r['customer_email'];
            $newVA->customer_phone =  $r['customer_phone'];
            $newVA->datetime_expired =  $r['datetime_expired'];
            $newVA->description =  $r['description'];
            $newVA->virtual_account =  $response['data']['virtual_account'];
            $newVA->status =$r['type'];
            $newVA->save();
        }
        return $this->respond($response);
    }
    public function callBackPayment(Request $request){

        $data = file_get_contents('php://input');
        $data_json = json_decode($data, true);


        if (!$data_json) {
            $data = array(
                "status" => "999",
                "message" => "Terjadi kesalahan."
            );
            return $this->respond($data);
        } else {
            if ($data_json['client_id'] === $this->client_id) {
                if(!isset( $data_json['data'])){
                    $data = array(
                        "status" => "999",
                    );
                    return $this->respond($data);
                }
                $data_asli = BniEnc::decrypt(
                    $data_json['data'],
                    $this->client_id,
                    $this->secret_key
                );

                if (!$data_asli) {
                   $data = array(
                       "status" => "999",
                       "message" => "waktu server tidak sesuai NTP atau secret key salah."
                   );
                   return $this->respond($data);
               }
               else {

                   DB::beginTransaction();
                   try {
                       // insert data asli ke db

                       $newVA =  VirtualAccount::where('trx_id', $data_asli['trx_id'])->first();
                    
                       $newVA->trx_amount =  $data_asli['trx_amount'];
                       $newVA->customer_name =  $data_asli['customer_name'];
                       $newVA->cumulative_payment_amount =  $data_asli['cumulative_payment_amount'];
                       $newVA->payment_ntb =  $data_asli['payment_ntb'];
                       $newVA->datetime_payment = $data_asli['datetime_payment'];
                       $newVA->datetime_payment_iso8601 = $data_asli['datetime_payment_iso8601'];
                       $newVA->status = 'callback';
                       $newVA->save();
                        // dd( $data_asli);
                       if($newVA->norec_sp!=null && $newVA->norec_pd!=null){
                           $kdProfile=21;
                            $strukPelayanan = StrukPelayanan::where('norec', $newVA->norec_sp)->first();
                            $sisa =0;
                            if($strukPelayanan->nosbmlastfk==null || $strukPelayanan->nosbmlastfk==''){
                                $sisa = $sisa + $this->getDepositPasien($strukPelayanan->pasien_daftar->noregistrasi);
                            }

                            $deposit = $sisa;

                            $sisa = $sisa + $data_asli['trx_amount'];

                            // foreach($request['pembayaran'] as $pembayaran){
                            $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
                            $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
                            $strukBuktiPenerimanan->kdprofile=$kdProfile;
                            $strukBuktiPenerimanan->keteranganlainnya = "Pembayaran Tagihan Pasien Virtual Account";
                            $strukBuktiPenerimanan->statusenabled= 1;
                            $strukBuktiPenerimanan->nostrukfk = $strukPelayanan->norec;
                            $strukBuktiPenerimanan->objectkelompokpasienfk = $strukPelayanan->pasien_daftar->pasien->objectkelompokpasienfk;
                            $strukBuktiPenerimanan->objectkelompoktransaksifk = 1;
                            $strukBuktiPenerimanan->objectpegawaipenerimafk  = $this->getCurrentLoginID();
                            $strukBuktiPenerimanan->tglsbm  = $data_asli['datetime_payment'];//$this->getDateTime();
                            $strukBuktiPenerimanan->totaldibayar  = $data_asli['trx_amount'];
                            $strukBuktiPenerimanan->nosbm = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'), $kdProfile);
                            $strukBuktiPenerimanan->save();

                            $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                            $SBPCB->norec = $SBPCB->generateNewId();
                            $SBPCB->kdprofile= $kdProfile;
                            $SBPCB->statusenabled = 1;
                            $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                            $SBPCB->objectcarabayarfk = 8;
                            $SBPCB->totaldibayar = $data_asli['trx_amount'];
                            $SBPCB->save();
                           
                           $strukPelayanan->nosbmlastfk =$strukBuktiPenerimanan->norec;
                           $strukPelayanan->save();
                           $pd = $strukPelayanan->pasien_daftar;
                           $pd->nosbmlastfk =$strukBuktiPenerimanan->norec;
                           $pd->save();
                           $newVA->norec_sbm = $strukBuktiPenerimanan->norec;
                           $newVA->save();
                       }
                       $stt = true;
                   } catch (\Exception $e) {
                       $stt = false;
                   }
                   if($stt){
                       DB::commit();
                       $data = array(
                           "status" => "000"
                       );
                   }else{
                       DB::rollBack();
                       $data = array(
                           "status" => "999",
                           "message" => $e
                       );
                   }
                   return $this->respond($data);
               }
            }else{
                $data = array(
                    "status" => "999",
                    "message" => "Client Id salah."
                );
                return $this->respond($data);
            }
        }
    }
    public function checkCallBackPayment(Request $request){
        $r = $request->input();
        if(!isset($r['virtual_account']) &&  !isset($r['customer_name'])){
              $newVA =  VirtualAccount::where('trx_id', $request['trx_id'])->first();
                $r['virtual_account'] = $newVA->virtual_account;
                $r['customer_name'] = $newVA->customer_name;
                $r['trx_amount'] = $newVA->trx_amount;
                $r['payment_amount'] = $newVA->trx_amount;
                $r['cumulative_payment_amount'] = $newVA->trx_amount;
                $r['payment_ntb'] = substr(mt_rand(),0,6 );
                $r['datetime_payment'] =date('Y-m-d H:i:s');
                $r['datetime_payment_iso8601'] = date('c');
        }
  
        $hashed_string = BniEnc::encrypt(
            $r,
            $this->client_id,
            $this->secret_key
        );
        $data = array(
            'client_id' => $this->client_id,
            'data' => $hashed_string,
        );
//        dd($data);
//        $response = $this->encryptBNI($r);

        return $this->respond($data);
    }
    function get_content($url, $post = '') {
//        $usecookie = __DIR__ . "/cookie.txt";
        $header[] = 'Content-Type: application/json';
        $header[] = "Accept-Encoding: gzip, deflate";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Accept-Language: en-US,en;q=0.8,id;q=0.6";

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