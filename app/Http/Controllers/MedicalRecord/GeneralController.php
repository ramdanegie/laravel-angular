<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 3/11/2020
 * Time: 12:51 PM
 */
namespace App\Http\Controllers\MedicalRecord;


use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;

use App\Datatrans\Pasien;
use App\Datatrans\PelayananMedis;
use App\Datatrans\PelayananMedisDetail;
use App\Datatrans\TransaksiMedis;
use App\Datatrans\Ruangan;
use App\Datatrans\Profile;

class GeneralController extends ApiController
{
    public function __construct()
    {
        parent::__construct($skip_authentication = true);
    }


    public function saveRuanganBpjs(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        try {
            foreach ($data['referensi'] as $key => $value) {
                $cek = Ruangan::where('id',$value['kode'])->first();
                if(empty( $cek )){
                    $saveData = new Ruangan();
                    $saveData->id = $value['kode'];
                }else{
                    $saveData = Ruangan::where('id',$value['kode'])->first();
                }
                $saveData->statusenabled = 1;
                $saveData->ruangan = $value['nama'];
                // $saveData->kodeexternal = $value['ruangan'];
                // $saveData->namaexternal = $value['ruangan'];
                // $saveData->lokasiruangan = $value['lokasiruangan'];
                $saveData->save();
            }
          
           
            $transMessage = "Simpan Berhasil";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'er@epic',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 500,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }   
    public function saveFaskesBPJS(Request $request){
        ini_set('max_execution_time', 200);
        DB::beginTransaction();
        $data = $request->all();
        try {
            foreach ($data['data'] as $key => $value) {
                $cek = Profile::where('kodeppk',$value['kdppk'])->first();
                if(empty( $cek )){
                    $saveData = new Profile();
                    $saveData->id =Profile::max('id')+1;
                }else{
                    $saveData = Profile::where('kodeppk',$value['kdppk'])->first();
                }
                $saveData->statusenabled = 1;
                $saveData->namaprofile = $value['nmppk'];
                $saveData->jenisprofilefk = 2;
                $saveData->kodeppk = $value['kdppk'];
                $saveData->alamatlengkap = $value['nmjlnppk'];
                $saveData->notelpon = $value['telpppk'];
                $saveData->latitude = $value['latitude'];
                $saveData->longitude = $value['longitude'];
                $saveData->jenisppk = $value['nmjnsppk'];
                $saveData->provinsifk = $data['prp'];
                $saveData->save();
            }
          
           
            $transMessage = "Simpan Berhasil";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'er@epic',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 500,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPasien(Request $request) {
        $data = \DB::table('pasien_m as ps')
            ->select('*');
        if(isset($request['nik']) && $request['nik']!="" && $request['nik']!="undefined"){
            $data = $data->where('ps.nik','=', $request['nik']);
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('ps.namapasien','like', '%'.$request['namapasien'].'%');
        }
        if(isset($request['nobpjs']) && $request['nobpjs']!="" && $request['nobpjs']!="undefined"){
            $data = $data->where('ps.nobpjs','=', $request['nobpjs']);
        }
        if(isset($request['nokk']) && $request['nokk']!="" && $request['nokk']!="undefined"){
            $data = $data->where('ps.nokk','=', $request['nokk']);
        }
        $data = $data->paginate(10);
//        $data = $data->get();



        return view('form/emr',compact('data'));
    }
    public function getDetailEMR(Request $request) {
        $dataPS = \DB::table('pasien_m as ps')
            ->join('jeniskelamin_m as jk','jk.id','=','ps.jeniskelaminfk')
            ->leftjoin('agama_m as ag','ag.id','=','ps.agamafk')
            ->leftjoin('golongandarah_m as gd','gd.id','=','ps.golongandarahfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.pekerjaanfk')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.pendidikanfk')
            ->leftjoin('statusperkawinan_m as spkw','spkw.id','=','ps.statusperkawinanfk')
            ->select('ps.*','jk.jeniskelamin','ag.agama','gd.golongandarah',
                'pk.pekerjaan','pdd.pendidikan','spkw.statusperkawinan');
//        if(isset($request['nik']) && $request['nik']!="" && $request['nik']!="undefined"){
        $dataPS = $dataPS->where('ps.nik','=', $request['nik']);
//        }
        if(isset($request['nobpjs']) && $request['nobpjs']!="" && $request['nobpjs']!="undefined"){
            $dataPS = $dataPS->where('ps.nobpjs','=', $request['nobpjs']);
        }
        if(isset($request['nokk']) && $request['nokk']!="" && $request['nokk']!="undefined"){
            $dataPS = $dataPS->where('ps.nokk','=', $request['nokk']);
        }
        $dataPS = $dataPS->first();
        $data = \DB::table('pasien_m as ps')
            ->join('pelayananmedis_t as pm','pm.pasienfk','=','ps.id')
            ->join('pelayananmedisdetail_t as pmd','pmd.pelayananmedisfk','=','pm.norec')
            ->join('transaksimedis_t as tm','tm.pelayananmedisdetailfk','=','pmd.norec')
            ->join('emr_m as emr','emr.id','=','tm.emrfk')
            ->join('profile_m as pf','pf.id','=','pm.profilefk')
            ->join('jenisemr_m as je','je.id','=','emr.jenisemrfk')
            ->leftjoin('ruangan_m as ru','ru.id','=','pmd.ruanganfk')
            ->leftjoin('kelompokvariabel_m as kv','kv.id','=','tm.kelompokvariabelfk')
            ->select('ps.namapasien','pm.noregistrasi','pm.tglregistrasi','pm.norm','pm.tglpulang','pf.namaprofile',
                'ru.ruangan','pmd.tglmasuk','pmd.tglkeluar','pmd.dpjp','tm.tgltransaksi','emr.id as emrid','je.kelompok','emr.namaemr',
                'tm.deskripsi','tm.jumlah','tm.satuan',
                'kv.id as namavariabelid','kv.namavariabel');
//        if(isset($request['nik']) && $request['nik']!="" && $request['nik']!="undefined"){
        $data = $data->where('ps.nik','=', $request['nik']);
//        }
        if(isset($request['tglawal']) && $request['tglawal']!="" && $request['tglawal']!="undefined"){
            $data = $data->where('pm.tglregistrasi','>=',  $request['tglawal']);
        }
        if(isset($request['tglakhir']) && $request['tglakhir']!="" && $request['tglakhir']!="undefined"){
            $data = $data->where('pm.tglregistrasi','<=', $request['tglakhir']);
        }
        if(isset($request['nobpjs']) && $request['nobpjs']!="" && $request['nobpjs']!="undefined"){
            $data = $data->where('ps.nobpjs','=', $request['nobpjs']);
        }
        if(isset($request['nokk']) && $request['nokk']!="" && $request['nokk']!="undefined"){
            $data = $data->where('ps.nokk','=', $request['nokk']);
        }
        if(isset($request['deskripsi']) && $request['deskripsi']!="" && $request['deskripsi']!="undefined"){
            $data = $data->where('tm.deskripsi','=', $request['deskripsi']);
        }

        $data = $data->get();
        foreach ($data as $item){

        }
        return view('form.emrdetail',compact('data','dataPS'));
//        return $this->respond($dataResponse);
    }
  
}