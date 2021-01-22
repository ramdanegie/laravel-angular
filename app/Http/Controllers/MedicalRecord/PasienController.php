<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan + as@Epic
 * Date: 09/03/2020
 * Time: 04.22
 */



namespace App\Http\Controllers\MedicalRecord;


use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Datatrans\Pasien;
use App\Datatrans\Alamat;

class PasienController extends ApiController {
    public function __construct() {
        parent::__construct($skip_authentication=false);
    }


 	public function getAgama(Request $request) {
        $agama = \DB::table('agama_m')
            ->select('*')
            ->get();

        return $this->respond($agama);
    }

    public function getPasien(Request $request) {
        $data = \DB::table('pasien_m as ps')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.jeniskelaminfk')
            ->leftjoin('agama_m as ag','ag.id','=','ps.agamafk')
            ->leftjoin('golongandarah_m as gd','gd.id','=','ps.golongandarahfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.pekerjaanfk')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.pendidikanfk')
            ->leftjoin('statusperkawinan_m as spkw','spkw.id','=','ps.statusperkawinanfk')
            ->select('ps.*','jk.jeniskelamin','ag.agama','gd.golongandarah',
                'pk.pekerjaan','pdd.pendidikan','spkw.statusperkawinan');
        if(isset($request['nik']) && $request['nik']!="" && $request['nik']!="undefined"){
            $data = $data->where('ps.nik','=', $request['nik']);
        }
        if(isset($request['nobpjs']) && $request['nobpjs']!="" && $request['nobpjs']!="undefined"){
            $data = $data->where('ps.nobpjs','=', $request['nobpjs']);
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('ps.namapasien','like', '%'.$request['namapasien'].'%');
        }
        if(isset($request['nokk']) && $request['nokk']!="" && $request['nokk']!="undefined"){
            $data = $data->where('ps.nokk','=', $request['nokk']);
        }
        $data = $data->take(50);
        $data = $data->get();


        return $this->respond($data);
    }

    public function saveDataPasien(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $idPasien=0;
//        try {
            $saveData1 =  Pasien::where('nik',$data['nik'])->get();
            if (count($saveData1) > 0){
                $result = array(
                    "status" => 500,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, "NIK sudah terdaftar");
            }
            if($data['nobpjs'] != null){
                $saveData2 =  Pasien::where('nobpjs',$data['nobpjs'])->get();
                if (count($saveData2) > 0){
                    $result = array(
                        "status" => 500,
                        "as" => 'as@epic',
                    );
                    return $this->setStatusCode($result['status'])->respond($result, "NO BPJS tersebut sudah terdaftar");
//                return "NO BPJS tersebut sudah terdaftar";
                }
            }

//            if ($data['id'] == null || $data['id'] == ''){
                $newId = Pasien::max('id');
                $idPasien =$newId + 1;
                $saveData = new Pasien();
                $saveData->id = $idPasien;

//            }else{
//                $saveData =  Pasien::where('id',$data['id'])->first();
//            }

            $saveData->statusenabled =  1;
            $saveData->namapasien = $data['namapasien'];
            $saveData->tgllahir = $data['tgllahir'];
            $saveData->tempatlahir = $data['tempatlahir'];
            $saveData->nik = $data['nik'];
            $saveData->nobpjs = $data['nobpjs'];
            $saveData->nokk = $data['nokk'];
            $saveData->kewarganegaraan = $data['kewarganegaraan'];
            $saveData->jeniskelaminfk = $data['jeniskelaminfk'];
            $saveData->agamafk = $data['agamafk'];
            $saveData->golongandarahfk = $data['golongandarahfk'];
            $saveData->pekerjaanfk = $data['pekerjaanfk'];
            $saveData->pendidikanfk = $data['pendidikanfk'];
            $saveData->statusperkawinanfk = $data['statusperkawinanfk'];
            $saveData->notelpon = $data['notelpon'];
            $saveData->nohp = $data['nohp'];
            $saveData->save();

            $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Simpan Gagal";
//        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $data,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 500,
                "message"  => $transMessage,
                "data" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveUpdatePasien(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $idPasien=0;
//        try {
            $saveData1 =  Pasien::where('nik',$data['nik'])->get();
            if (count($saveData1) == 0){
                $result = array(
                    "status" => 500,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, "NIK belum terdaftar");
            }
            $saveData2 =  Pasien::where('nobpjs',$data['nobpjs'])->get();
            if (count($saveData2) == 0){
                $result = array(
                    "status" => 500,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, "NO BPJS tersebut belum terdaftar");
            }
//            if ($data['id'] == null || $data['id'] == ''){
//            $newId = Pasien::max('id');
//            $idPasien =$newId + 1;
//            $saveData = new Pasien();
//            $saveData->id = $idPasien;

//            }else{
                $saveData =  Pasien::where('nik',$data['nik'])->first();
//            }

            $saveData->statusenabled =  1;
            $saveData->namapasien = $data['namapasien'];
            $saveData->tgllahir = $data['tgllahir'];
            $saveData->tempatlahir = $data['tempatlahir'];
            $saveData->nik = $data['nik'];
            $saveData->nobpjs = $data['nobpjs'];
            $saveData->nokk = $data['nokk'];
            $saveData->kewarganegaraan = $data['kewarganegaraan'];
            $saveData->jeniskelaminfk = $data['jeniskelaminfk'];
            $saveData->agamafk = $data['agamafk'];
            $saveData->golongandarahfk = $data['golongandarahfk'];
            $saveData->pekerjaanfk = $data['pekerjaanfk'];
            $saveData->pendidikanfk = $data['pendidikanfk'];
            $saveData->statusperkawinanfk = $data['statusperkawinanfk'];
            $saveData->notelpon = $data['notelpon'];
            $saveData->nohp = $data['nohp'];
            $saveData->save();

            $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Simpan Gagal";
//        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $data,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 500,
                "message"  => $transMessage,
                "data" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getPasienMaster(Request $request) {
        $jk = \DB::table('jeniskelamin_m as ps')
            ->select('*')->get();
        $ag = \DB::table('agama_m as ps')
            ->select('*')->get();
        $gd = \DB::table('golongandarah_m as ps')
            ->select('*')->get();
        $kerja = \DB::table('pekerjaan_m as ps')
            ->select('*')->get();
        $didik = \DB::table('pendidikan_m as ps')
            ->select('*')->get();

        $res =  array(
            "jeniskelamin" => $jk,
            "agama" => $ag,
            "golongandarah" => $gd,
            "pekerjaan" => $kerja,
            "pendidikan" => $didik,
        );

        return $this->respond($res);
    }
    public function saveUpdateAlamat(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $idPasien = 0;
//        try {
        $saveData1 =  Pasien::where('nik',$data['nik'])->get();
        // return $this->respond($saveData1);
        if (count($saveData1) == 0){
            $result = array(
                "status" => 500,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, "NIK belum terdaftar");
        }
        $saveData2 =  Alamat::where('pasienfk',$saveData1[0]->id)->get();
        if (count($saveData2) == 0){
//            $result = array(
//                "status" => 500,
//                "as" => 'as@epic',
//            );
//            return $this->setStatusCode($result['status'])->respond($result, "NO BPJS tersebut belum terdaftar");
//        }
//            if ($data['id'] == null || $data['id'] == ''){
            $newId = Alamat::max('id');
            $idAlamat =$newId + 1;
            $saveData = new Alamat();
            $saveData->id = $idAlamat;
            $saveData->pasienfk = $saveData1[0]->id;

        }else{
            $saveData =  Alamat::where('pasienfk',$saveData2[0]->id)->first();
        }

        $saveData->statusenabled =  1;
        $saveData->alamatlengkap = $data['alamatlengkap'];
        $saveData->desakelurahanfk = $data['desakelurahanfk'];
        $saveData->rtrw = $data['rtrw'];
        $saveData->kecamatanfk = $data['kecamatanfk'];
        $saveData->kotakabupatenfk = $data['kotakabupatenfk'];
        $saveData->provinsifk = $data['provinsifk'];
        $saveData->kodepos = $data['kodepos'];
        $saveData->negarafk = $data['negarafk'];
        $saveData->save();

        $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Simpan Gagal";
//        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $data,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 500,
                "message"  => $transMessage,
                "data" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function savePasienWithAlamat(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $idPasien=0;
//        try {
        if($data['nik'] == null){
            $result = array(
                "status" => 500,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, "NIK harus di isi");
        }
        $saveData1 =  Pasien::where('nik',$data['nik'])->get();
        if (count($saveData1) == 0){
            $newId = Pasien::max('id');
            $idPasien = $newId + 1;
            $saveData = new Pasien();
            $saveData->id = $idPasien;
        }else{
            $saveData =  Pasien::where('id',$saveData1[0]['id'])->first();
            $idPasien = $saveData->id;
        }
        $saveData->statusenabled =  1;
        $saveData->namapasien = $data['namapasien'];
        $saveData->tgllahir = $data['tgllahir'];
        $saveData->tempatlahir = $data['tempatlahir'];
        $saveData->nik = $data['nik'];
        $saveData->nobpjs = $data['nobpjs'];
        $saveData->nokk = $data['nokk'];
        $saveData->kewarganegaraan = $data['kewarganegaraan'];
        $saveData->jeniskelaminfk = $data['jeniskelaminfk'];
        $saveData->agamafk = $data['agamafk'];
        $saveData->golongandarahfk = $data['golongandarahfk'];
        $saveData->pekerjaanfk = $data['pekerjaanfk'];
        $saveData->pendidikanfk = $data['pendidikanfk'];
        $saveData->statusperkawinanfk = $data['statusperkawinanfk'];
        $saveData->notelpon = $data['notelpon'];
        $saveData->nohp = $data['nohp'];
        $saveData->save();

//        $idPasien =  $saveData->id ;

        $saveData3 =  Alamat::where('pasienfk',$idPasien )->get();
//        return $this->respond($saveData3);
        if (count($saveData3) == 0){
            $newId = Alamat::max('id');
            $idAlamat = $newId + 1;
            $saveData4 = new Alamat();
            $saveData4->id = $idAlamat;

        }else{
            $saveData4 =  Alamat::where('id',$saveData3[0]->id)->first();
        }
        $saveData4->pasienfk = $idPasien;
        $saveData4->statusenabled =  1;
        $saveData4->alamatlengkap = $data['alamatlengkap'];
        $saveData4->desakelurahanfk = $data['desakelurahanfk'];
        $saveData4->rtrw = $data['rtrw'];
        $saveData4->kecamatanfk = $data['kecamatanfk'];
        $saveData4->kotakabupatenfk = $data['kotakabupatenfk'];
        $saveData4->provinsifk = $data['provinsifk'];
        $saveData4->kodepos = $data['kodepos'];
        $saveData4->negarafk = $data['negarafk'];
        if(isset($data['desakelurahan'])){
            $saveData4->desakelurahan = $data['desakelurahan'];
        }
        if(isset($data['kecamatan'])) {
            $saveData4->kecamatan = $data['kecamatan'];
        }
        if(isset($data['kotakabupaten'])) {
            $saveData4->kotakabupaten = $data['kotakabupaten'];
        }
        if(isset($data['provinsi'])) {
            $saveData4->provinsi = $data['provinsi'];
        }
        $saveData4->save();

        $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Simpan Gagal";
//        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $data,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 500,
                "message"  => $transMessage,
                "data" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}
