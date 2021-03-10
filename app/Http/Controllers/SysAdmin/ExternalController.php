<?php
/**
 * Created by PhpStorm.
 * User: egiera2020
 * Date: 09/08/2020
 * Time: 13:36
 */
namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\ApiController;
use App\Master\Agama;
use App\Master\Evaluasi;
use App\Master\Implementasi;
use App\Master\Intervensi;
use App\Master\JenisKelamin;
use App\Master\Pendidikan;
use App\Master\StatusPerkawinan;
use App\Master\DiagnosaKeperawatan;
use App\Transaksi\MapRuanganToAkomodasi;
use App\Transaksi\PostingJurnal;
use App\Transaksi\PostingJurnalTransaksi;
use App\Transaksi\PostingJurnalTransaksiD;
use App\Transaksi\StrukPlanning;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;
use Response;
use Closure;


use App\Transaksi\IdentifikasiPasien;
use App\Transaksi\PasienDaftar;

class ExternalController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = true);
    }
  public function getDetailTransaksiLab(Request $request)
    {
    	$res = $this->checkTokenKab( $request);
    	if(!$res['status']){
    		return Response::json($res['data'], 401);
    	}
        $data = \DB::table('order_lab')

        ->select(DB::raw("norec,'' as  sebutan_nama, nama_pas as nama,alamat,
            no_rm as norm,
            tgl_lahir as tgllhr,
            jenis_kel as jnskel,
            kode_ruang as ruang,
             nama_ruang as ruang_name,
            6 as kelas,
            'Non Kelas' as kelas_name,
             kode_cara_bayar as penanggung,
            cara_bayar as  penanggung_name,
            kode_dok_kirim as dokter,
             nama_dok_kirim as dokter_name,
           
            kode_test as  kd_transaksi,
            no_lab as no_bukti_transaksi,
                tgl_order AS tgl_buktitrans,
            asal_lab"));
                        

            
         if (isset($request['no_bukti_transaksi']) && $request['no_bukti_transaksi'] != "" 
         	&& $request['no_bukti_transaksi'] != "undefined") {
            $data = $data->where('no_lab', 'ilike','%'. $request['no_bukti_transaksi'].'%');
            
         }
         if(isset($request['paging']) && $request['paging']!="" && $request['paging']!="undefined"){
            $data = $data->limit($request['paging']);
        }
        if(isset($request['offset']) && $request['offset']!="" && $request['offset']!="undefined"){
            $data = $data->offset($request['offset']);
        }
        $data=$data->distinct();
        $data=$data->get();

        return $this->respond($data);
    }
    public function getByTanggalLab(Request $request)
    {
    	// return   strval(time()-strtotime('1970-01-01 00:00:00'));

    	$res = $this->checkTokenKab( $request);
    	if(!$res['status']){
    		return Response::json($res['data'], 401);
    	}

        $data = \DB::table('order_lab')
        ->join('pasiendaftar_t AS pd','pd.noregistrasi','=','order_lab.no_registrasi')
        ->select(DB::raw("	no_rm AS norm,
			no_registrasi,
			no_lab AS nobukti,
			tgl_order AS tgl_buktitrans,
			kode_dok_kirim AS kode_dokter,
			pd.tglregistrasi AS tgl_registrasi,
			'' AS sebutan_nama,
			nama_pas AS nama_pasien,
			jenis_kel AS jnskel,
			cara_bayar AS penanggung,
			nama_ruang AS klinik,
			'' AS gelar_depan_dokter,
			nama_dok_kirim AS dokter,
			'' AS gelar_belakang_dokter"));
                        
       if(isset($request['dari']) && $request['dari']!="" && $request['dari']!="undefined"){
            $data = $data->where('tgl_order','>=', $request['dari'].' 00:00');
        };
        if(isset($request['sampai']) && $request['sampai']!="" && $request['sampai']!="undefined"){
            $data = $data->where('tgl_order','<=', $request['sampai'].' 23:59');
        };
            
         if (isset($request['no_bukti_transaksi']) && $request['no_bukti_transaksi'] != "" 
         	&& $request['no_bukti_transaksi'] != "undefined") {
            $data = $data->where('no_lab', 'ilike','%'. $request['no_bukti_transaksi'].'%');
            
         }
         if(isset($request['paging']) && $request['paging']!="" && $request['paging']!="undefined"){
            $data = $data->limit($request['paging']);
        }
        if(isset($request['offset']) && $request['offset']!="" && $request['offset']!="undefined"){
            $data = $data->offset($request['offset']);
        }
        $data=$data->get();

        return $this->respond($data);
    }
	public function checkTokenKab( $request)
    {
    	$idna = 'lis';
    	$keyna = 'list';
    	$timena =  strval(time()-strtotime('1970-01-01 00:00:00'));
    	$id =  $request->header('x-id');
        $key =  $request->header('x-key');
        $time =  $request->header('x-timestamp');
        $status =true;
        $data = array(
                'code' => 201,
                'message' =>  ''
            );
        if(!$id){
			$data = array(
                'code' => 401,
                'message' =>  'x-id tidak tersedia'
            );
            $status =false;
            // return Response::json($data, 401)->header('X-MESSAGE', 'x-id tidak tersedia');
        }
        else if(!$key){
        	$data = array(
                'code' => 401,
                'message' =>  'x-key tidak tersedia'
            );
            $status =false;
            // return Response::json($data, 401)->header('X-MESSAGE', 'x-key tidak tersedia');
        }
       else if(!$time){
         	$data = array(
                'code' => 401,
                'message' =>  'x-timestamp tidak tersedia'
            );
            $status =false;
            // return Response::json($data, 401)->header('X-MESSAGE', 'x-timestamp tidak tersedia');
        	
        }
        else if($idna != $id && $keyna !=$key){
        	$data = array(
                'code' => 401,
                'message' =>  'x-id atau x-key tidak sesuai'
            );
            $status =false;
        }
        // else if($time != $timena ){
        // 	$data = array(
        //         'code' => 401,
        //         'message' =>  'service expired'
        //     );
        //     $status =false;
        // }

       
        $rs = array(
        	'status' => $status,
        	'data' => $data
        );

        return $rs;
    }
    public function postLab(Request $request)
    {
    	// $data = $this->getIdConsumerBPJS();

     //    $secretKey = $this->getPasswordConsumerBPJS();
     //    // Computes the timestamp
     //    date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        return $tStamp;
        // Computes the signature by hashing the salt with the secret key as the key
        // $signature = hash_hmac('sha256', $data."&".$tStamp, $secretKey, true);

        // // base64 encode…
        // $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL=>'https://transmedic.co.id:8700/service/lab/get-by-tanggal?dari=2020-09-25&sampai=2020-09-25&nobukti=',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json;",
                "x-id: lis",
                "X-key: lis",
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

    public function updateKdProfile(){
       // DB::beginTransaction();
         // try{
            ini_set('max_execution_time', 1200); 
            $data = DB::select(DB::raw("select table_name 
                    from information_schema.tables 
                    where table_name ilike '%_m'
                    and table_name <> 'profile_m'
                    and table_name in (
                        SELECT table_name
                        FROM information_schema.columns 
                        WHERE  column_name='kdprofile'
                        and table_name ilike '%_m'
                        and table_name <> 'profile_m'
                    )
                
            "));
            // return $this->respond( $data);
            foreach ($data as $key => $value) {
                $tb = $value->table_name;
                $da = DB::table($tb)->update(['kdprofile' => 21]);
            }
        //        DB::commit();
        //      return $this->setStatusCode(200)->respond('Sukses');
        // } catch (\Exception $e) {
        //       DB::rollBack();
        //       return $this->setStatusCode(400)->respond($e);
        // }
      
    }
}