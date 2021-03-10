<?php
/**
 * Created by PhpStorm.
 * User: GIW
 * Date: 7/31/2019
 * Time: 4:33 PM
 */

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\ApiController;
use App\Master\Agama;
use App\Master\Pegawai;
use App\Traits\CrudMaster;
use App\Traits\Valet;
use App\Transaksi\LoggingUser;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Web\LoginUser;
use App\Web\Token;
// use App\Web\Admin\ProfileHistoriAwards as ProfileHistoriAwards;
// use App\Web\Admin\Awards as Awards_M;
// use App\Web\Asal as Asal_M;
// use App\Transaksi\StrukHistori as StrukHistori_T;
use DB;
use Illuminate\Support\Facades\Hash;
use Namshi\JOSE\Base64\Base64UrlSafeEncoder;
use Namshi\JOSE\JWT;
use Namshi\JOSE\JWS;
use Namshi\JOSE\Base64\Encoder;
use Webpatser\Uuid\Uuid;

use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;

class LoginController extends ApiController {
    use CrudMaster;


    public function __construct() {
        parent::__construct($skip_authentication=true);
    }


    public function createToken($namaUser){
        $class = new Builder();
        $signer = new Sha512();
        $token = $class->setHeader('alg','HS512')
            ->set('sub', $namaUser)
            ->sign($signer, "JASAMEDIKA")
            ->getToken();
        return $token;
    }
    public function loginUser(Request $request)
    {
        /*
         * composer update --no-plugins --no-scripts
         * composer require lcobucci/jwt
         * sumber -> https://github.com/lcobucci/jwt
         */
//        try {
//            DB::connection()->getPdo();
//            if(DB::connection()->getDatabaseName()){
////                return "Yes! Successfully connected to the DB: " . DB::connection()->getDatabaseName();
//            }else {
//                $result = array(
//                    'data' => [],
//                    'messages' => 'Database tidak ditemukan. Silahkan periksa konfigurasi Anda ',
//                    'status'=> 400,
//                    'as'=> '#Inhuman'
//                );
//                return $this->setStatusCode($result['status'])->respond($result);
//            }
//
//        } catch (\Exception $e) {
//            $result = array(
//                'data' => [],
//                'messages' => 'Tidak dapat membuka koneksi ke server database. Silahkan periksa konfigurasi Anda ',
//                'status'=> 400,
//                'as'=> '#Inhuman'
//            );
//            return $this->setStatusCode($result['status'])->respond($result);
//        }
        $login = DB::table('loginuser_s')
            ->where('passcode', '=', $this->encryptSHA1($request->input('kataSandi')))
            ->where('namauser', '=', $request->input('namaUser'));
        $LoginUser = $login->get();
        if (count($LoginUser) > 0){
            //region Cek Login Expired
            $now = date('Y-m-d H:i:s');
            $cekWaktuLogin_M = \Schema::hasTable('waktulogin_m');
            if($cekWaktuLogin_M == true){
                $loginExpired = DB::table('waktulogin_m')
                    ->select('id','expired')
                    ->where('loginuserfk',$LoginUser[0]->id)
                    ->get();
                if(count($loginExpired) > 0){
                    $status = false;
                    foreach ($loginExpired as $item){
                        if(date($item->expired) > $now ){
                            $status = true;
                            break;
                        }
                    }
                    if($status == false){
                        $result = array(
                            'data' => [],
                            'messages' => 'Login gagal, User Expired',
                            'status'=> 400,
                            'as'=> '#Inhuman'
                        );
                        return $this->setStatusCode($result['status'])->respond($result);
                    }
                }
            }

            //endregion
            //region Login Suskes
            $kelompokUser = DB::table('kelompokuser_s')
                ->select('id','kelompokuser as kelompokUser')
                ->where('id', '=',$LoginUser[0]->objectkelompokuserfk)
                ->first();
            $pegawai = Pegawai::where('id',$LoginUser[0]->objectpegawaifk)
                ->first();
            $mapLoginUserToRuangan = DB::table('maploginusertoruangan_s as mlur')
                ->join('loginuser_s as lu','lu.id','=','mlur.objectloginuserfk')
                ->join('ruangan_m as ru','ru.id','=','mlur.objectruanganfk')
                ->join('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
                ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk','dept.namadepartemen as departemen')
                ->where('lu.id', '=',$LoginUser[0]->id)
                ->get();
//            $agama = Agama::where('id',$pegawai->objectagamafk)
//                ->first();
//            $detailKat = DB::table('detailkategorypegawai_m')
//                ->where('id', '=',$pegawai->objectdetailkategorypegawaifk)
//                ->first();
//            $golongan = DB::table('golonganpegawai_m')
//                ->where('id', '=',$pegawai->objectgolonganfk)
//                ->first();
//            $jabatanFungsional = DB::table('jabatan_m')
//                ->where('id', '=',$pegawai->objectjabatanfungsionalfk)
//                ->first();
//            $jenisKelamin = DB::table('jeniskelamin_m')
//                ->where('id', '=',$pegawai->objectjeniskelaminfk)
//                ->first();
            $jenisPegawai =  DB::table('jenispegawai_m')
                ->where('id', '=',$pegawai->objectjenispegawaifk)
                ->first();
//            $kategoryPegawai =  DB::table('kategorypegawai_m')
//                ->where('id', '=',$pegawai->objectkategorypegawaifk)
//                ->first();
//            $kualifikasiJurusan =  DB::table('kualifikasijurusan_m')
//                ->where('id', '=',$pegawai->objectkualifikasijurusanfk)
//                ->first();
//            $negara =  DB::table('negara_m')
//                ->where('id', '=',$pegawai->objectnegarafk)
//                ->first();
//            $pendidikan =  DB::table('pendidikan_m')
//                ->where('id', '=',$pegawai->objectpendidikanterakhirfk)
//                ->first();
            $ruangKerja =  DB::table('ruangan_m')
                ->where('id', '=',$pegawai->objectruangankerjafk)
                ->first();
            if(empty($ruangKerja)){
                $ruangKerja = array(
                    'id' => null,
                    'namaruangan' => 'tidak ada mapping ke ruangan'
                );
            }
//            $shiftKerja =  DB::table('shiftkerja_m')
//                ->where('id', '=',$pegawai->objectshiftkerja)
//                ->first();
//            $statusPegawai =  DB::table('statuspegawai_m')
//                ->where('id', '=',$pegawai->objectstatuspegawaifk)
//                ->first();
            $profile =  DB::table('profile_m')
                ->where('id', '=', $LoginUser[0]->kdprofile)
                ->first();
            $resPegawai= array(
                'id' =>$pegawai->id,
                'namaLengkap' =>$pegawai->namalengkap,
                'tempatLahir'=> $pegawai->tempatlahir,
                'tglLahir'=> $pegawai->tgllahir,
                'noIdentitas' => $pegawai->noidentitas,
                'statusEnabled' => $pegawai->statusenabled,
//                'bankRekeningAtasNama' => $pegawai->bankrekeningatasnama,
//                'agama'=> $agama,
//                'detailKategoryPegawai' =>$detailKat,
//                'golongan' => $golongan,
//                'idFinger' => $pegawai->idfinger,
//                'jabatanFungsional' =>$jabatanFungsional,
//                'jadwalPemeriksaanSet' => [],
//                'jenisKelamin' =>$jenisKelamin,
                'jenisPegawai' => $jenisPegawai,
                'kdProfile' => $pegawai->kdprofile,
//                'kategoryPegawai' =>$kategoryPegawai,
//                'kedudukan' =>[],
//                'kodePos' => $pegawai->kodepos,
//                'kualifikasiJurusan' => $kualifikasiJurusan,
//                'nama' => $pegawai->nama,
//                'namaKeluarga' => $pegawai->namakeluarga,
//                'namaPanggilan' => $pegawai->namapanggilan,
//                'negara' => $negara,
//                'nikIntern' => $pegawai->nik_intern,
//                'noRec' => $pegawai->norec,
//                'npwp' => $pegawai->npwp,
//                'pendidikan' => $pendidikan,
//                'pensiun' => $pegawai->pensiun,
//                'photoDiri' => $pegawai->photodiri,
//                'qPegawai' => $pegawai->qpegawai,
//                'qtyAnak' => $pegawai->qtyanak,
//                'riwayatPendidikanSet'=> [],
                'ruangan'=> $ruangKerja,
//                'shiftKerja'=> $shiftKerja,
//                'statusPegawai'=>$statusPegawai,
//                'statusRhesus' => $pegawai->statusrhesus,
//                'tglMasuk'=> $pegawai->tglmasuk,
//                'tglPensiun'=> $pegawai->tglpensiun,
            );

            $dataLogin = array(
                'id' => $LoginUser[0]->id,
                'kdProfile' => $LoginUser[0]->kdprofile,
                'namaUser' => $LoginUser[0]->namauser,
                'kataSandi'=> $LoginUser[0]->katasandi,
                'passCode'=> $LoginUser[0]->passcode,
                'kelompokUser'=> $kelompokUser,
                'mapLoginUserToRuangan' => $mapLoginUserToRuangan,
//                'mapPegawaiToModulAplikasiSet' => [],
                'pegawai' => $resPegawai,
                'profile' => $profile
            ) ;
            $token['X-AUTH-TOKEN'] = $this->createToken($LoginUser[0]->namauser).'';

            $result = array(
                'data' => $dataLogin,
                'messages' =>$token,
                'status'=> 201,
                'as'=> '#Inhuman'
            );

            //endregion
        }else{
            //region Login Gagal send 400 code
            $result = array(
                'data' => [],
                'messages' => 'Login gagal, Username atau Password salah',
                'status'=> 400,
                'as'=> '#Inhuman'
            );
            //endregion
        }

        //region Hash & Bcrypt Password
//        $password['sha1'] = $this->encryptSHA1('admin');
//        $password['hash'] = Hash ::make('admin');
//        $password['bcrypt'] = bcrypt('admin');
//        if (Hash::check('admin',   $password['hash'] ))
//        {
//            $password['cocok'] = true ;
//            // The passwords match...
//        }
//        return $this->respond($password);
        //endregion
        return $this->setStatusCode($result['status'])->respond($result);
    }
    public function signOut (Request $request)
    {
        $result['code'] = 401;
        $result['message'] = 'You have not logged';
        $QueryLogin = DB::table('loginuser_s')
//            ->where('katasandi', '=', $this->encryptSHA1($request->input('kataSandi')))
            ->where('namauser', '=', $request->input('kdUser'));
        $LoginUser = $QueryLogin->get();
        if(count($LoginUser) > 0){
            $result['message'] = 'Logout Success';
            $result['id'] =$LoginUser[0]->id;
            $result['kdUser'] = $LoginUser[0]->namauser;
            $result['code'] = 200;
            $result['as'] = '#Inhuman';
        }
        $resData = array(
            'data' => $result
        );
        return response()->json($resData);
    }

    protected function encryptSHA1($pass)
    {
        return sha1($pass);
    }
    protected function encryptHash($pass)
    {
        return  Hash::make($pass);
    }
    public function ubahPassword (Request $request)
    {
        try{
            $cekUser = LoginUser::where('namauser',$request['namaUser'])
                ->first();
            $sama = false ;
            if(!empty($cekUser)){
                if($cekUser->id != $request['id'] && $request['namaUser'] == $cekUser->namauser ){
                    $sama = true;
                }
            }
            if($sama ==  true){
                $result = array(
                    "status" => 400,
                    "as" => '#Inhuman'
                );
                return $this->setStatusCode($result['status'])->respond($result, 'Nama User sudah ada');
            }

            LoginUser::where('id',$request['id'])->update(
                [
                    'passcode' => $this->encryptSHA1($request->input('kataSandi')),
                    'objectkelompokuserfk' => $request['kelompokUser']['id'],
                    'namauser' => $request['namaUser']
                    //'katasandi' => $this->encryptSHA1($request->input('kataSandi'))
                ]
            );

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => '#Inhuman'
            );
        } else {
            $transMessage = "Failed";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => '#Inhuman'
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function loginAndro(Request $request)
    {
        /*
         * composer update --no-plugins --no-scripts
         * composer require lcobucci/jwt
         * sumber -> https://github.com/lcobucci/jwt
         */
        $login = DB::table('loginuser_s')
            ->where('passcode', '=', $this->encryptSHA1($request->input('kataSandi')))
            ->where('namauser', '=', $request->input('namaUser'));
        $LoginUser = $login->get();
        if (count($LoginUser) > 0){
            //region Cek Login Expired
            $now = date('Y-m-d H:i:s');
            $cekWaktuLogin_M = \Schema::hasTable('waktulogin_m');
            if($cekWaktuLogin_M == true){
                $loginExpired = DB::table('waktulogin_m')
                    ->select('id','expired')
                    ->where('loginuserfk',$LoginUser[0]->id)
                    ->get();
                if(count($loginExpired) > 0){
                    $status = false;
                    foreach ($loginExpired as $item){
                        if(date($item->expired) > $now ){
                            $status = true;
                            break;
                        }
                    }
                    if($status == false){
                        $result = array(
                            'data' => [],
                            'messages' => 'Login gagal, User Expired',
                            'status'=> 400,
                            'as'=> '#Inhuman'
                        );
                        return $this->setStatusCode($result['status'])->respond($result);
                    }
                }
            }

            //endregion
            //region Login Suskes
            $kelompokUser = DB::table('kelompokuser_s')
                ->select('id','kelompokuser as kelompokUser')
                ->where('id', '=',$LoginUser[0]->objectkelompokuserfk)
                ->first();
            $pegawai = Pegawai::where('id',$LoginUser[0]->objectpegawaifk)
                ->first();
            $jenisKelamin = DB::table('jeniskelamin_m')
                ->where('id', '=',$pegawai->objectjeniskelaminfk)
                ->first();
            $mapLoginUserToRuangan = DB::table('maploginusertoruangan_s as mlur')
                ->join('loginuser_s as lu','lu.id','=','mlur.objectloginuserfk')
                ->join('ruangan_m as ru','ru.id','=','mlur.objectruanganfk')
                ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
                ->where('lu.id', '=',$LoginUser[0]->id)
                ->get();

            $resPegawai= array(
                'id' =>$pegawai->id,
                'namaLengkap' =>$pegawai->namalengkap,
                'tempatLahir'=> $pegawai->tempatlahir,
                'tglLahir'=> $pegawai->tgllahir,
                'noIdentitas' => $pegawai->noidentitas,
                'statusEnabled' => $pegawai->statusenabled,
                'jenisKelamin' => !empty($jenisKelamin) ? $jenisKelamin->jeniskelamin : '',
            );

            $dataLogin = array(
                'namaUser' => $LoginUser[0]->namauser,
                'kataSandi'=> $LoginUser[0]->katasandi,
                'namaPegawai' => $pegawai->namalengkap,
                'kdUser' => $LoginUser[0]->id,
                'token' =>$this->createToken($LoginUser[0]->namauser).'',

                'passCode'=> $LoginUser[0]->passcode,
                'kelompokUser'=> $kelompokUser,
                'mapLoginUserToRuangan' => $mapLoginUserToRuangan,
                'pegawai' => $resPegawai

            ) ;



            return $this->setStatusCode(201)->respond($dataLogin);
            //endregion
        }else{

            //region Login Gagal send 400 code
            return $this->setStatusCode(400)->respond([]);
            //endregion
        }
        //endregion

    }

    public function getTokens(Request $request)
    {
        $req =  $request->json()->all();
        $login = DB::table('loginuser_s')
            ->where('passcode', '=', $this->encryptSHA1($req['password']))
            ->where('namauser', '=', $req['username']);
        $LoginUser = $login->get();
        if (count($LoginUser) > 0){
            $profile =  DB::table('profile_m')
                ->where('id', '=', $LoginUser[0]->kdprofile)
                ->first();
            $dataLogin = array(
                'id' => $LoginUser[0]->id,
                'kdProfile' => $LoginUser[0]->kdprofile,
                'namaProfile' => $profile->namaexternal,
                'namaUser' => $LoginUser[0]->namauser,

            ) ;
            $token['X-AUTH-TOKEN'] = $this->createToken($LoginUser[0]->namauser).'';
            $result = array(
                "response"=>array(
                    "token"=> $this->createToken($LoginUser[0]->namauser).'',
                ),
                "metadata"=>array(
                    "message" => 'Ok',
                    "code" => 200,
                )
            );
            //endregion
        }else{
            $result = array(
                "response"=> null,
                "metadata"=> array(
                    "message" => 'Username atau password salah',
                    "code" => 400,
                )
            );
            //endregion
        }


        return $this->setStatusCode($result['metadata']['code'])->respond($result);
    }
    public function saveECG(Request $r)
    {
        $req =  $r->getContent();
        echo "##$EPI";
        $req = str_replace('[ECG SERVER V1.0]','',$req);
        $req = str_replace('as@epic=##$ECG','',$req);
        $req = str_replace('~','',$req);

        $xml = simplexml_load_string($req, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array =json_decode($json,TRUE);
        $uid=  substr(Uuid::generate(), 0, 6);
        $save =[];
        $fdata = array_values($array['Data']) ;
        $i = 1;
        for ($x = 0; $x < count($fdata); $x++) {
            $ket = str_replace('xmlECG','', array_keys($array['Data'])[$x]) ;
            if($ket !='xmlHeader' && $ket  != 'Data'){
                if($ket == 'date'){
                    $ket ='ecgDate';
                }
                if($ket == 'Time'){
                    $ket ='ecgTime';
                }
                $save[] =array(
                    'norec'=> date('YmdHis').$uid.$array['Data']['xmlECGCustomerID'],
                    'kunci' => $ket,
                    'nilai' =>$fdata[$x],
                    'urut' => $i,
                    'customerid' => $array['Data']['xmlECGCustomerID'],
                    'datesend' => $array['Data']['xmlECGdate'].' '.$array['Data']['xmlECGTime']
                );
                $i++;
            }
        }
        $save[] =array(
            'norec'=> date('YmdHis').$uid.$array['Data']['xmlECGCustomerID'],
            'kunci' => 'expertise',
            'nilai' =>'',
            'urut' => 10,
            'customerid' => $array['Data']['xmlECGCustomerID'],
            'datesend' => $array['Data']['xmlECGdate'].' '.$array['Data']['xmlECGTime']
        );

        $frame = array_values($array['Data']['xmlECGData']) ;
        for ($x = 0; $x < count($frame); $x++) {
            $save[] =array(
                'norec'=> date('YmdHis').$uid.$array['Data']['xmlECGCustomerID'],
                'kunci' => 'xmlframe',
                'nilai' => $frame[$x],
                'urut' => 20 + $x,
                'customerid' => $array['Data']['xmlECGCustomerID'],
                'datesend' => $array['Data']['xmlECGdate'].' '.$array['Data']['xmlECGTime']
            );
        }
//        DB::beginTransaction();
//        try{
//        return $save;
            $dataInsert =[];
            foreach ($save as $s){
                $dataInsert[] = [
                    'norec' => $s['norec'],
                    'reportdisplay' => 'er&as@epic',
                    'kunci' => $s['kunci'],
                    'nilai' => $s['nilai'],
                    'urut' => $s['urut'],
                    'customerid' => $s['customerid'],
                    'datesend' => $s['datesend'],
                ];
            
                if (count($dataInsert) > 100){
                    DB::table('eecg_t')->insert($dataInsert);
                    $dataInsert = [];
                }
             }
             DB::table('eecg_t')->insert($dataInsert);
//            DB::commit();
             $result = array("response"=>'ECG',
                 "metadata"=>
                     array(
                         "code" => "200",
                         "message" => "Sukses")
             );
            return response()->json($result);

//        } catch (\Exception $e) {
//            DB::rollBack();
//            return response()->json($e);
//        }
    }
}

