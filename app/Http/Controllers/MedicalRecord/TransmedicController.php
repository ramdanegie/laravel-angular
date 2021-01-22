<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 3/11/2020
 * Time: 12:51 PM
 */
namespace App\Http\Controllers\MedicalRecord;


use App\Datatrans\KetersediaanTempatTidur;
use App\Datatrans\Pegawai;
use App\Datatrans\PelayananRujukan;
use App\Datatrans\TransaksiStok;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;

use App\Datatrans\Pasien;
use App\Datatrans\PelayananMedis;
use App\Datatrans\PelayananMedisDetail;
use App\Datatrans\TransaksiMedis;

class TransmedicController extends ApiController
{
    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }


    public function saveDataMedicalRecord(Request $request){
        DB::beginTransaction();
        $data = $request->all();
//        try {
            $cariPasien =  Pasien::select('id')
                ->where('nik',$data['nik'])
                ->get();
            if (count($cariPasien) == 0){
                $result = array(
                    "status" => 500,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, "NIK belum terdaftar");
            }
            $dataPasienfk = $cariPasien[0]->id;
            $cekData =  PelayananMedis::select('norec')
                ->where('profilefk',$data['profilefk'])
                ->where('noregistrasi',$data['noregistrasi'])
                ->get();
            if (count($cekData) > 0){
                // $result = array(
                //     "status" => 500,
                //     "as" => 'as@epic',
                // );
                // return $this->setStatusCode($result['status'])->respond($result, "NOREGISTRASI ini sudah tersimpan!");
               foreach ($cekData as $itmhead){
                   $norecCekdata = $itmhead->norec;
                   $cekdata1 = PelayananMedisDetail::where('pelayananmedisfk',$norecCekdata)
                       ->select('*')
                       ->get();
                   foreach ($cekdata1 as $itm){
                       TransaksiMedis::where('pelayananmedisdetailfk',$itm->norec)->delete();
                       PelayananMedisDetail::where('norec',$itm->norec)->delete();
                   }
                   PelayananMedis::where('norec',$norecCekdata)->delete();
               }
            }
            $saveData = new PelayananMedis();
            $norec1 = substr(Uuid::generate(), 0, 32);
            $saveData->norec = $norec1;
            $saveData->statusenabled = 1;
            $saveData->profilefk = $data['profilefk'];
            $saveData->noregistrasi = $data['noregistrasi'];
            $saveData->tglregistrasi = $data['tglregistrasi'];
            $saveData->norm = $data['norm'];
            $saveData->pasienfk = $dataPasienfk;//$data['pasienfk'];
            $saveData->tglpulang = $data['tglpulang'];
            $saveData->norujukan = $data['norujukan'];
            $saveData->tglrujukan = $data['tglrujukan'];
            $saveData->nosep = $data['nosep'];
            $saveData->tglsep = $data['tglsep'];
            $saveData->ppkpelayanan = $data['ppkpelayanan'];
            $saveData->diagnosafk = $data['diagnosafk'];
			if(isset($data['statuscovidfk'])){
			   $saveData->statuscovidfk = $data['statuscovidfk'];
			}
            if(isset($data['kddiagnosa'])){
              $saveData->kddiagnosa = $data['kddiagnosa'];
            }
            if(isset($data['namadiagnosa'])){
              $saveData->namadiagnosa = $data['namadiagnosa'];
            }
            $saveData->lokasilakalantas = $data['lokasilakalantas'];
            $saveData->penjaminlaka = $data['penjaminlaka'];
            $saveData->cob = $data['cob'];
            $saveData->katarak = $data['katarak'];
            $saveData->keteranganlaka = $data['keteranganlaka'];
            $saveData->tglkejadian = $data['tglkejadian'];
            $saveData->suplesi = $data['suplesi'];
            $saveData->nosepsuplesi = $data['nosepsuplesi'];
            $saveData->iddpjp = $data['iddpjp'];
            $saveData->dpjp = $data['dpjp'];
            $saveData->prolanisprb = $data['prolanisprb'];
            $saveData->kelasfk = $data['kelasfk'];
            if(isset($data['statuscovidfk'])){
                $saveData->statuscovidfk = $data['statuscovidfk'];
            }
            $saveData->save();
            $norecTransaksiMedis = $norec1;//$saveData->norec;

            foreach ($data['pelayananmedisdetail'] as $item) {
                $savePD = new PelayananMedisDetail();
                $norec2 = substr(Uuid::generate(), 0, 32);
                $savePD->norec = $norec2;
                $savePD->statusenabled = 1;
                $savePD->profilefk = $item['profilefk'];
                $savePD->tglmasuk = $item['tglmasuk'];
                $savePD->tglkeluar = $item['tglkeluar'];
                $savePD->ruanganfk = $item['ruanganfk'];
                $savePD->pelayananmedisfk = $norecTransaksiMedis;
                $savePD->iddpjp = $item['iddpjp'];
                $savePD->dpjp = $item['dpjp'];
                $savePD->save();

                $norecPelayananMedis = $norec2;//$savePD->norec;

                foreach ($item['transaksimedis'] as $itemdetail) {
                    $saveTD = new TransaksiMedis();
                    $saveTD->norec = substr(Uuid::generate(), 0, 32);
                    $saveTD->statusenabled = 1;
                    $saveTD->profilefk = $itemdetail['profilefk'];
                    $saveTD->pelayananmedisdetailfk = $norecPelayananMedis;
                    $saveTD->emrfk = $itemdetail['emrfk'];
                    $saveTD->tgltransaksi = $itemdetail['tgltransaksi'];
                    $saveTD->deskripsi = $itemdetail['deskripsi'];
                    $saveTD->jumlah = $itemdetail['jumlah'];
                    $saveTD->satuan = $itemdetail['satuan'];
                    $saveTD->tarif = $itemdetail['tarif'];
                    $saveTD->kelompokvariabelfk = $itemdetail['kelompokvariabelfk'];
                    $saveTD->iddpjp = $itemdetail['iddpjp'];
                    $saveTD->dpjp = $itemdetail['dpjp'];
                    $saveTD->save();
                }
            }
            $transMessage = "Simpan Berhasil";

            $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Simpan Gagal";
//        }

        if ($transStatus == 'true') {

            DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
//                "data" => $saveData,
                "as" => 'as@epic',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 500,
//                "message"  => $transMessage,
//                "data" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getPasienMedicalRecord(Request $request) {

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
            ->select('pm.noregistrasi','pm.tglregistrasi','pm.norm','pm.tglpulang','pf.namaprofile',
                'ru.ruangan','pmd.tglmasuk','pmd.tglkeluar','pmd.dpjp','tm.tgltransaksi','emr.id as emrid','je.kelompok','emr.namaemr','tm.deskripsi','tm.jumlah','tm.satuan',
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

//        $data = $data->where('pm.tglregistrasi','>=', $request['tglawal']);
//        $data = $data->where('pm.tglregistrasi','<=', $request['tglakhir']);
//        $data = $data->take(50);
        $data = $data->get();

        $dataResponse = array(
                "pasien" => $dataPS,
                "medicalrecord"=> $data
            );


        return $this->respond($dataResponse);
    }
    public function getProfile(Request $request) {
        $a = \DB::table('profile_m as ps')
            ->where('ps.statusenabled',1)
            ->select('*');
        if(isset($request['namaprofile']) && $request['namaprofile']!="" && $request['namaprofile']!="undefined"){
            $a = $a->where('ps.namaprofile','like','%'. $request['namaprofile'].'%');
        }
        if(isset($request['jenis']) && $request['jenis']!="" && $request['jenis']!="undefined"){
            $a = $a->where('ps.jenisppk','=',$request['jenis']);
        }
        if(isset($request['paging']) && $request['paging']!="" && $request['paging']!="undefined"){
            $a = $a->limit($request['paging']);
        }
        if(isset($request['offset']) && $request['offset']!="" && $request['offset']!="undefined"){
            $a = $a->offset($request['offset']);
        }
        $a =$a->get();


        $res =  array(
            "profile" => $a
        );

        return $this->respond($res);
    }
    public function getRuangan(Request $request) {
        $a = \DB::table('ruangan_m as ps')
            ->where('ps.statusenabled',1)
            ->select('ps.id','ps.ruangan');

        if(isset($request['namaruangan']) && $request['namaruangan']!="" && $request['namaruangan']!="undefined"){
            $a = $a->where('ps.ruangan','like','%'. $request['namaruangan'].'%');
        }
        if(isset($request['id']) && $request['id']!="" && $request['id']!="undefined"){
            $a = $a->where('ps.id','like','%'. $request['id'].'%');
        }

        if(isset($request['paging']) && $request['paging']!="" && $request['paging']!="undefined"){
            $a = $a->limit($request['paging']);
        }
        if(isset($request['offset']) && $request['offset']!="" && $request['offset']!="undefined"){
            $a = $a->offset($request['offset']);
        }
        $a = $a->get();
        $res =  array(
            "ruangan" => $a
        );

        return $this->respond($res);
    }
    public function getMasterEMR(Request $request) {
        $a = \DB::table('emr_m as ps')
            ->join('jenisemr_m as jns','jns.id','=','ps.jenisemrfk')
            ->select('jns.id as jenisemrfk','jns.jenisemr','ps.*');
        if(isset($request['namaemr']) && $request['namaemr']!="" && $request['namaemr']!="undefined"){
            $a = $a->where('ps.namaemr','like','%'. $request['namaemr'] . '%');
        }
        if(isset($request['emrfk']) && $request['emrfk']!="" && $request['emrfk']!="undefined"){
            $a = $a->where('ps.id','=', $request['emrfk']);
        }
        if(isset($request['jenisemr']) && $request['jenisemr']!="" && $request['jenisemr']!="undefined"){
            $a = $a->where('jns.jenisemr','like','%'. $request['jenisemr'] . '%');
        }
        if(isset($request['jenisemrfk']) && $request['jenisemrfk']!="" && $request['jenisemrfk']!="undefined"){
            $a = $a->where('jns.id','=', $request['jenisemrfk']);
        }
        $a = $a->where('ps.statusenabled',1);
        $a = $a->take($request['limit'])->get();

        $res =  array(
            "emr" => $a
        );

        return $this->respond($res);
    }
    public function getKelompokVariable(Request $request) {
        $a = \DB::table('kelompokvariabel_m as ps')
            ->where('ps.statusenabled',1)
            ->select('*')->take(50)->get();

        $res =  array(
            "kelompokvariabel_m" => $a
        );

        return $this->respond($res);
    }
      public function getRuanganAll(Request $request) {
        $a = \DB::table('ruangan_m as ps')
            ->select('*')->get();

        $res =  array(
            "ruangan" => $a
        );

        return $this->respond($res);
    }
    public function saveRujukan(Request $request){
        DB::beginTransaction();
        $data = $request->all();
//        try {
            $cariPasien =  Pasien::select('id')
                ->where('nik',$data['nik'])
                ->get();
            if (count($cariPasien) == 0){
                $result = array(
                    "status" => 500,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, "NIK belum terdaftar");
            }
            $dataPasienfk = $cariPasien[0]->id;
            $cekData =  PelayananRujukan::select('norec')
                ->where('profilefk',$data['profilefk'])
                ->where('norujukan',$data['norujukan'])
                ->where('tglrujukan',$data['tglrujukan'])
                ->where('profilerujukanfk',$data['profilerujukanfk'])
                ->get();
            if (count($cekData) > 0){
                $result = array(
                    "status" => 500,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, "Rujukan ini sudah tersimpan!");

            }

            $saveData = new PelayananRujukan();
            $norec1 = substr(Uuid::generate(), 0, 32);
            $saveData->norec = $norec1;
            $saveData->statusenabled = 1;
            $saveData->profilefk = $data['profilefk'];
            // $saveData->noregistrasi = $data['noregistrasi'];
            // $saveData->tglregistrasi = $data['tglregistrasi'];
            $saveData->norm = $data['norm'];
            $saveData->pasienfk = $dataPasienfk;//$data['pasienfk'];
            // $saveData->tglpulang = $data['tglpulang'];
            $saveData->norujukan = $data['norujukan'];
            $saveData->tglrujukan = $data['tglrujukan'];
            // $saveData->nosep = $data['nosep'];
            // $saveData->tglsep = $data['tglsep'];
            // $saveData->ppkpelayanan = $data['ppkpelayanan'];
            $saveData->namadiagnosa = $data['namadiagnosa'];
            $saveData->kddiagnosa = $data['kddiagnosa'];
            $saveData->diagnosafk = $data['diagnosafk'];
//            $saveData->lokasilakalantas = $data['lokasilakalantas'];
//            $saveData->penjaminlaka = $data['penjaminlaka'];
//            $saveData->cob = $data['cob'];
//            $saveData->katarak = $data['katarak'];
//            $saveData->keteranganlaka = $data['keteranganlaka'];
//            $saveData->tglkejadian = $data['tglkejadian'];
            // $saveData->suplesi = $data['suplesi'];
            // $saveData->nosepsuplesi = $data['nosepsuplesi'];
            $saveData->iddpjp = $data['iddpjp'];
            $saveData->dpjp = $data['dpjp'];
            // $saveData->prolanisprb = $data['prolanisprb'];
            $saveData->kelasfk = $data['kelasfk'];
            $saveData->profilerujukanfk = $data['profilerujukanfk'];
            $saveData->ruanganrujukanfk = $data['ruanganrujukanfk'];
//            $saveData->status = $data['status'];
            $saveData->save();
            $norecTransaksiMedis = $norec1;//$saveData->norec;


            $transMessage = "Simpan Berhasil";

            $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Simpan Gagal";
//        }

        if ($transStatus == 'true') {

            DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
//                "data" => $saveData,
                "as" => 'as@epic',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 500,
//                "message"  => $transMessage,
//                "data" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getRujukan(Request $request) {

    $data = \DB::table('pasien_m as ps')
            ->join('pelayananrujukan_t as pm','pm.pasienfk','=','ps.id')
            ->join('profile_m as pf','pf.id','=','pm.profilefk')
            ->join('ruangan_m as ru','ru.id','=','pm.ruanganrujukanfk')
            ->join('profile_m as ruju','ruju.id','=','pm.profilerujukanfk')
            ->leftjoin('kelas_m as kl','kl.id','=','pm.kelasfk')
            ->select('pm.norujukan','pm.tglrujukan','pm.norm','pf.namaprofile as asalrujukan',
                'ru.ruangan',  'ru.id as kdruangan','pm.iddpjp','pm.dpjp','pm.kelasfk as idkelas',
                'kl.namakelas',
                'pm.kddiagnosa','pm.namadiagnosa','ps.nik','ps.nobpjs');

        if(isset($request['nobpjs']) && $request['nobpjs']!="" && $request['nobpjs']!="undefined"){
            $data = $data->where('ps.nobpjs','=', $request['nobpjs']);
        }
        if(isset($request['norujukan']) && $request['norujukan']!="" && $request['norujukan']!="undefined"){
            $data = $data->where('pm.norujukan','=', $request['norujukan']);
        }
        if(isset($request['nik']) && $request['nik']!="" && $request['nik']!="undefined"){
            $data = $data->where('ps.nik','=', $request['nik']);
        }


        $data = $data->get();
        if(count($data)> 0){
          $dataPS = \DB::table('pasien_m as ps')
            ->join('jeniskelamin_m as jk','jk.id','=','ps.jeniskelaminfk')
            ->join('agama_m as ag','ag.id','=','ps.agamafk')
            ->join('golongandarah_m as gd','gd.id','=','ps.golongandarahfk')
            ->join('pekerjaan_m as pk','pk.id','=','ps.pekerjaanfk')
            ->join('pendidikan_m as pdd','pdd.id','=','ps.pendidikanfk')
            ->join('statusperkawinan_m as spkw','spkw.id','=','ps.statusperkawinanfk')
            ->select('ps.*','jk.jeniskelamin','ag.agama','gd.golongandarah',
                'pk.pekerjaan','pdd.pendidikan','spkw.statusperkawinan');

            $dataPS = $dataPS->where('ps.nik','=', $data[0]->nik);

            if(isset($request['nobpjs']) && $request['nobpjs']!="" && $request['nobpjs']!="undefined"){
                $dataPS = $dataPS->where('ps.nobpjs','=', $request['nobpjs']);
            }
            if(isset($request['nokk']) && $request['nokk']!="" && $request['nokk']!="undefined"){
                $dataPS = $dataPS->where('ps.nokk','=', $request['nokk']);
            }
            $dataPS = $dataPS->first();

        }

        $dataResponse = array(
                "pasien" => $dataPS,
                "rujukan"=> $data
            );


        return $this->respond($dataResponse);
    }
    public function getDaftarRujukan(Request $request) {

        $data = \DB::table('pasien_m as ps')
            ->join('pelayananrujukan_t as pm','pm.pasienfk','=','ps.id')
            ->leftjoin('profile_m as pf','pf.id','=','pm.profilefk')
            ->leftjoin('ruangan_m as ru','ru.id','=','pm.ruanganrujukanfk')
            ->join('profile_m as ruju','ruju.id','=','pm.profilerujukanfk')
            ->leftjoin('kelas_m as kl','kl.id','=','pm.kelasfk')
            ->join('jeniskelamin_m as jk','jk.id','=','ps.jeniskelaminfk')
            ->leftjoin('agama_m as ag','ag.id','=','ps.agamafk')
            ->leftjoin('golongandarah_m as gd','gd.id','=','ps.golongandarahfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.pekerjaanfk')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.pendidikanfk')
            ->leftjoin('statusperkawinan_m as spkw','spkw.id','=','ps.statusperkawinanfk')
            ->select('pm.norujukan','pm.tglrujukan','pm.norm','ps.namapasien','ps.tgllahir','ps.tempatlahir',
                'pf.namaprofile as asalrujukan','pm.profilerujukanfk','pm.profilefk',
                'pf.kodeppk as kodeppkasal','ruju.kodeppk as kodeppktujuan','ruju.namaprofile as tujuanrujukan',
                'ru.ruangan', 'ru.id as kdruangan','pm.iddpjp','pm.dpjp','pm.kelasfk as idkelas','pm.status',
                'kl.namakelas','jk.jeniskelamin','ag.agama','gd.golongandarah','pk.pekerjaan','pdd.pendidikan',
                'spkw.statusperkawinan','pm.kddiagnosa','pm.namadiagnosa','ps.nik','ps.nobpjs','nohp','pm.namadiagnosa'
                );

        if(isset($request['nobpjs']) && $request['nobpjs']!="" && $request['nobpjs']!="undefined"){
            $data = $data->where('ps.nobpjs','=', $request['nobpjs']);
        }
        if(isset($request['norujukan']) && $request['norujukan']!="" && $request['norujukan']!="undefined"){
            $data = $data->where('pm.norujukan','=', $request['norujukan']);
        }
        if(isset($request['nik']) && $request['nik']!="" && $request['nik']!="undefined"){
            $data = $data->where('ps.nik','=', $request['nik']);
        }
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined"){
            $data = $data->where('pm.norm','=', $request['norm']);
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('ps.namapasien','like', '%'.$request['namapasien'].'%');
        }
        $data = $data->orderby('tglrujukan','desc');
        $data = $data->take(50);
        $data = $data->get();
        $dataResponse = array(
            "rujukan"=> $data
        );
        return $this->respond($dataResponse);
    }
    public function updateStatusRujukan(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $tgl =$data['tglrujukan'];
//       try{
        $cekData =  DB::table('pelayananrujukan_t')
            ->where('profilefk',$data['profilefk'])
            ->where('norujukan',$data['norujukan'])
            ->whereRaw("DATE_FORMAT(tglrujukan,'%Y-%m-%d')='$tgl'")
            ->where('profilerujukanfk',$data['profilerujukanfk'])
            // ->get();
            ->update([
                'status'=> 'Sudah Di Respon'
            ]);

        $transMessage ='Update Rujukan Sukses';
        $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Update Rujukan Gagal";
//        }

        if ($transStatus == 'true') {

            DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
//                "data" => $saveData,
                "as" => 'as@epic',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 500,
//                "message"  => $transMessage,
//                "data" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getEMRTransaksiDetailForm(Request $request)
    {
        //todo : detail
        $paramNocm = '';
        $paramNoreg = '';
        $data = \DB::table('pelayananmedisdetail_t as pmd')
//            ->join('pelayananmedisdetail_t as pmd','pmd.norec','=','emrp.pelayananmedisdetailfk')
            ->join('pelayananmedis_t as pm','pmd.pelayananmedisfk','=','pm.norec')
            ->join('pasien_m as ps','ps.id','=','pm.pasienfk')
            ->join('profile_m as pr','pr.id','=','pm.profilefk')
            ->select('pmd.norec as pelayananmedisdetailfk',  'pmd.tglmasuk as tgltransaksi','pmd.dpjp',
                'pmd.norec','pmd.norec as noemr',
               'pm.noregistrasi','pm.tglregistrasi','ps.namapasien','pm.norm','ps.nik','pr.namaprofile')
//            ->whereNotIn('emrp.emrfk',[410019,410018]);
            ->orderBy('pm.tglregistrasi', 'desc');

        if (isset($request['norm']) && $request['norm'] != '') {
            $data = $data->where('pm.norm', $request['norm']);
        }
        if (isset($request['nik']) && $request['nik'] != '') {
            $data = $data->where('ps.nik', $request['nik']);
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $data = $data->where('pm.noregistrasi', $request['noregistrasi']);
        }
        $data = $data->get();
//        $jenisEMr = $request['jenisEmr'];
        $result = [];
        foreach ($data as $item) {
            $noemr = $item->noregistrasi;
            $norecpmd = $item->norec;
            $details = DB::select(DB::raw("
            SELECT DISTINCT
            pd.norec ,
	           pd.norec as emrpasienfk,
	       emrd.id  as emrfk,
                emrd.reportdisplay,
                emrd.jenisemr AS namaform
            FROM
                transaksimedis_t AS emrdp
            INNER JOIN emr_m AS emr ON emr.id = emrdp.emrfk
            INNER JOIN jenisemr_m AS emrd ON emrd.id = emr.jenisemrfk
            INNER JOIN pelayananmedisdetail_t AS pd ON pd.norec = emrdp.pelayananmedisdetailfk
            INNER JOIN pelayananmedis_t AS pp ON pp.norec = pd.pelayananmedisfk

           where pd.norec = '$norecpmd'
            and emr.namaemr not in('PELAYANAN TINDAKAN','PELAYANAN OBAT')
            GROUP BY emrd.reportdisplay,
	    emrd.jenisemr,   emr.id ,emrd.id , pd.norec

            "));

            $item->details =$details;
              // $details=[];
//            $result [] = array(
//                'norec' => $item->norec,
////               'kdprofile' => $item->kdprofile,
////               'statusenabled' => $item->statusenabled,
//                'namapasien' => $item->norm,
//                'emrfk' => $item->emrfk,
//                'noregistrasifk' => $item->noregistrasifk,
//                'noemr' => $item->noemr,
//                'nocm' => $item->nocm,
//                'namapasien' => $item->namapasien,
//                'jeniskelamin' => $item->jeniskelamin,
//                'noregistrasi' => $item->noregistrasi,
//                'umur' => $item->umur,
//                'kelompokpasien' => $item->kelompokpasien,
//                'tglregistrasi' => $item->tglregistrasi,
//                'norec_apd' => $item->norec_apd,
//                'namakelas' => $item->namakelas,
//                'namaruangan' => $item->namaruangan,
//                'tglemr' => $item->tglemr,
//                'tgllahir' => $item->tgllahir,
//                'notelepon' => $item->notelepon,
//                'alamat' => $item->alamat,
//                'jenisemr' => $item->jenisemr,
//                'pegawaifk' => $item->pegawaifk,
//                'namalengkap' => $item->namalengkap,
//                'details' => $details
//            );
        }
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getMenuRekamMedisAtuh(Request $request)
    {

        $dataRaw = \DB::table('jenisemr_m as emr')
            ->where('emr.statusenabled', true)
            ->where('emr.kelompok',$request['namaemr'])
            ->select('emr.*')
            ->orderBy('emr.nourut');
        $dataRaw = $dataRaw->get();
        foreach ($dataRaw as $dataRaw2) {
            $dataraw3[] = array(
                'id' => $dataRaw2->id,
               'profilefk' => $dataRaw2->profilefk,
                'statusenabled' => $dataRaw2->statusenabled,
                'kodeexternal' => $dataRaw2->kodeexternal,
                'namaexternal' => $dataRaw2->namaexternal,
                'reportdisplay' => $dataRaw2->reportdisplay,
                'namaemr' => $dataRaw2->kelompok,
                'caption' => $dataRaw2->jenisemr,
                'headfk' => $dataRaw2->headfk,
                'nourut' => $dataRaw2->nourut
            );
        }
        $data = $dataraw3;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
//                $element['child'] = [];
                $id = $element['id'];
                $parent_id = $element['headfk'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                } else {
                    if ($parent_id <= 10) {
                        $tree[] = &$element;
                    }
                }
                //}
            }
            return $tree;
        }

        $data = recursiveElements($data);

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getRekamMedisAtuh(Request $request)
    {
        $dataRaw = \DB::table('emr_m as emrd')
            ->join('jenisemr_m as emr', 'emr.id', '=', 'emrd.jenisemrfk')
            ->where('emr.id', $request['emrid'])
            ->where('emrd.statusenabled', '=', true)
            ->select('emrd.*','emrd.namaemr as caption','emrd.jenisemrfk as emrfk', 'emr.kelompok as namaemr',
                'emr.jenisemr as captionemr', 'emr.classgrid')
            ->orderBy('emrd.nourut');
        $dataRaw = $dataRaw->get();

        $dataraw3A = [];
        $dataraw3B = [];
        $dataraw3C = [];
        $dataraw3D = [];
        foreach ($dataRaw as $dataRaw2) {
            $head = '';
            if ($dataRaw2->kolom == 1) {
                $dataraw3A[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $dataRaw2->namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => []
                );
            } elseif ($dataRaw2->kolom == 2) {
                $dataraw3B[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $dataRaw2->namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => []
                );
            } elseif ($dataRaw2->kolom == 3) {
                $dataraw3C[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $dataRaw2->namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => []
                );
            } else {
                $dataraw3D[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $dataRaw2->namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => []
                );
            }

            $title = $dataRaw2->captionemr;
            $classgrid = $dataRaw2->classgrid;
        }
//        $dataA = $dataraw3A;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
//                $element['child'] = [];
                $id = $element['id'];
                $parent_id = $element['headfk'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                } else {
                    if ($parent_id <= 10) {
                        $tree[] = &$element;
                    }
                }
                //}
            }
            return $tree;
        }


        $dataA = [];
        $dataB = [];
        $dataC = [];
        $dataD = [];
        if (count($dataraw3A) > 0) {
            $dataA = recursiveElements($dataraw3A);
        }
        if (count($dataraw3B) > 0) {
            $dataB = recursiveElements($dataraw3B);
        }
        if (count($dataraw3C) > 0) {
            $dataC = recursiveElements($dataraw3C);
        }
        if (count($dataraw3D) > 0) {
            $dataD = recursiveElements($dataraw3D);
        }


        $result = array(
            'kolom1' => $dataA,
            'kolom2' => $dataB,
            'kolom3' => $dataC,
            'kolom4' => $dataD,
            'title' => $title,
            'classgrid' => $classgrid,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getEMRTransaksiDetail(Request $request)
    {
        //todo : detail
        $data = \DB::table('transaksimedis_t as emrdp')
            ->join('pelayananmedisdetail_t as emrp', 'emrp.norec', '=', 'emrdp.pelayananmedisdetailfk')
            ->leftjoin('emr_m as emrd', 'emrd.id', '=', 'emrdp.emrfk')
            // ->JOIN('emrd_t as emrd',function ($join){
            // $join->on('emrd.id','=','emrdp.emrdfk');
            // $join->on('emrd.emrfk','=','emrdp.emrfk');
            // })
//            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'emrdp.pegawaifk')
            //->leftjoin('emrfoto_t as ef', 'ef.noemrpasienfk', '=', 'emrp.noemr')
            ->select('emrdp.*', 'emrd.namaemr as caption', 'emrd.type', 'emrd.nourut',
                'emrdp.emrfk as emrdfk','emrd.jenisemrfk as emrfk', 'emrd.reportdisplay',
                'emrd.kodeexternal as kodeex','emrd.kodeexternal', 'emrd.satuan','emrdp.deskripsi as value','emrdp.jumlah')
//            ->where('emrdp.statusenabled', true)
            ->orderBy('emrd.nourut');
        if (isset($request['noemr']) && $request['noemr'] != '') {
            $data = $data->where('emrp.norec', $request['noemr']);
        }
        if (isset($request['emrfk']) && $request['emrfk'] != '') {
            $data = $data->where('emrd.jenisemrfk', $request['emrfk']);
        }
        if (isset($request['norec']) && $request['norec'] != '') {
            $data = $data->where('emrp.norec', $request['norec']);
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $data = $data->where('emrp.noregistrasifk', $request['noregistrasi']);
        }
        if (isset($request['objectid']) && $request['objectid'] != '') {
            $data = $data->where('emrdp.emrdfk', $request['objectid']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getMenuEmrById(Request $request)
    {

        $dataRaw = \DB::table('emr_m as emr')
            ->where('emr.statusenabled', true)
            ->where('emr.id', $request['id'])
            ->select('emr.*')
            ->first();

        $result = array(
            'data' => $dataRaw,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getMasterTable($param,Request $request) {
        try {
            $a = \DB::table($param.'_m')
                ->where('statusenabled',1)
                ->select('*');

            if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
                $a = $a->where($param,'like','%'. $request['nama'].'%');
            }

            if(isset($request['paging']) && $request['paging']!="" && $request['paging']!="undefined"){
                $a = $a->limit($request['paging']);
            }
            if(isset($request['offset']) && $request['offset']!="" && $request['offset']!="undefined"){
                $a = $a->offset($request['offset']);
            }
            $a = $a->get();
            $res =  array(
                $param => $a,
                'message' => 'Suskes',
            );

            return $this->respond($res);
        } catch (\Exception $e) {
            $res =  array(
                'data' => [],
                'message' => $e
            );
            return $this->respond($res)->setStatusCode(500);
        }

    }

    public function saveUpdateStatusCovidPasien(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $idPasien = 0;
//        try {
        $cariPasien =  Pasien::select('id')
            ->where('nik',$data['nik'])
            ->get();
        if (count($cariPasien) == 0){
            $result = array(
                "status" => 500,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, "NIK belum terdaftar");
        }
        $dataPasienfk = $cariPasien[0]->id;
        $cekData =  PelayananMedis::select('norec')
            ->where('profilefk',$data['profilefk'])
            ->where('noregistrasi',$data['noregistrasi'])
            ->get();
        if (count($cekData) == 0){
            $saveData = new PelayananMedis();
            $norec1 = substr(Uuid::generate(), 0, 32);
            $saveData->norec = $norec1;
            $saveData->statusenabled = 1;
            $saveData->profilefk = $data['profilefk'];
            $saveData->noregistrasi = $data['noregistrasi'];
            $saveData->tglregistrasi = $data['tglregistrasi'];
            $saveData->norm = $data['norm'];
            $saveData->pasienfk = $dataPasienfk;//$data['pasienfk'];
        }else{
            $saveData =  PelayananMedis::where('norec',$cekData[0]->norec)->first();
        }
            $saveData->tglpulang = $data['tglpulang'];
            $saveData->norujukan = $data['norujukan'];
            $saveData->tglrujukan = $data['tglrujukan'];
            $saveData->nosep = $data['nosep'];
            $saveData->tglsep = $data['tglsep'];
            $saveData->ppkpelayanan = $data['ppkpelayanan'];
            $saveData->diagnosafk = $data['diagnosafk'];
            if(isset($data['statuscovidfk'])){
			   $saveData->statuscovidfk = $data['statuscovidfk'];
			}
            if(isset($data['kddiagnosa'])){
                $saveData->kddiagnosa = $data['kddiagnosa'];
            }
            if(isset($data['namadiagnosa'])){
                $saveData->namadiagnosa = $data['namadiagnosa'];
            }
            $saveData->lokasilakalantas = $data['lokasilakalantas'];
            $saveData->penjaminlaka = $data['penjaminlaka'];
            $saveData->cob = $data['cob'];
            $saveData->katarak = $data['katarak'];
            $saveData->keteranganlaka = $data['keteranganlaka'];
            $saveData->tglkejadian = $data['tglkejadian'];
            $saveData->suplesi = $data['suplesi'];
            $saveData->nosepsuplesi = $data['nosepsuplesi'];
            $saveData->iddpjp = $data['iddpjp'];
            $saveData->dpjp = $data['dpjp'];
            $saveData->prolanisprb = $data['prolanisprb'];
            $saveData->kelasfk = $data['kelasfk'];
            $saveData->save();
//            $norecTransaksiMedis = $norec1;

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
                "data" => $cekData,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 500,
                "message"  => $transMessage,
                "data" => $cekData,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getStatusCovid(Request $request) {
        try {
            $a = \DB::table('statuscovid_m')
                ->where('statusenabled',1)
                ->select('id','status');

            if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
                $a = $a->where('status','like','%'. $request['nama'].'%');
            }

            if(isset($request['paging']) && $request['paging']!="" && $request['paging']!="undefined"){
                $a = $a->limit($request['paging']);
            }
            if(isset($request['offset']) && $request['offset']!="" && $request['offset']!="undefined"){
                $a = $a->offset($request['offset']);
            }
            $a = $a->get();
            $res =  array(
                'list' => $a,
                'message' => 'Suskes',
            );

            return $this->respond($res);
        } catch (\Exception $e) {
            $res =  array(
                'list' => [],
                'message' => $e
            );
            return $this->respond($res)->setStatusCode(500);
        }

    }
     public function updateStatusCovid(Request $request){
        DB::beginTransaction();
        // $data = $request->all();
        // $tgl =$data['tglrujukan'];
//       try{
        $arr = [];
        foreach ($request['list'] as $key => $value) {
            $cekData2=  DB::table('pelayananmedis_t')
            ->where('profilefk',$value['profilefk'])
            ->where('noregistrasi',$value['noregistrasi'])
            ->first();
            if(empty($cekData2)){
                 $arr[] = array(
                        'noregistrasi' => $value['noregistrasi'],
                        'message' => 'No Registrasi tidak ditemukan',
                     );
            }else{
                  $cekData =  DB::table('pelayananmedis_t')
                    ->where('profilefk',$value['profilefk'])
                    ->where('noregistrasi',$value['noregistrasi'])
                    ->update([
                        'statuscovidfk'=> $value['statuscovidfk']
                    ]);

                 $arr[] = array(
                    'noregistrasi' => $value['noregistrasi'],
                    'message' => 'Success',
                 );
            }

        }


        $transMessage ='Update Status Covid ';
        $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Update Rujukan Gagal";
//        }

        if ($transStatus == 'true') {

            DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
                "result" => $arr,
                "as" => 'as@epic',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 500,
//                "message"  => $transMessage,
//                "data" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveDataSyncPegawai(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $idPegawai = 0;

        try {

            foreach ($data['list'] as $item){
                $cekData =  DB::table('pegawai_m')
                        ->where('nip',$item['nip'])
                        ->where('profilefk',$item['profilefk'])
                        ->first();
                if ($cekData == null){
                    $newId = Pegawai::max('id');
                    $idPegawai =$newId + 1;
                    $saveData = new Pegawai();
                    $saveData->id = $idPegawai;
                    $saveData->statusenabled = 1;
                    $saveData->profilefk = $item['profilefk'];
                }else{
                    $saveData =  Pegawai::where('id',$cekData->id)->first();
                }
                    $saveData->namalengkap = $item['namalengkap'];
                    $saveData->objectjeniskelaminfk = $item['objectjeniskelaminfk'];
                    $saveData->tgllahir = $item['tgllahir'];
                    $saveData->tempatlahir = $item['tempatlahir'];
                    $saveData->nip = $item['nip'];
                    $saveData->objectpendidikanfk = $item['objectpendidikanfk'];
                    $saveData->objectjabtanfk = $item['objectjabtanfk'];
                    $saveData->objectpangkatfk = $item['objectpangkatfk'];
                    $saveData->tglmasuk = $item['tglmasuk'];
                    $saveData->tglkeluar = $item['tglkeluar'];
                    $saveData->objectjenispegawaifk = $item['objectjenispegawaifk'];
                    $saveData->tglupdate = $tglAyeuna;
                    $saveData->save();
            }

        $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Sync Data Pegawai Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Sync Data Pegawai Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $saveData,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Sync Data Pegawai Gagal!!";
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

    public function getJabatanAll(Request $request) {
        $a = \DB::table('jabatan_m as ps')
            ->select('*')->get();

        $res =  array(
            "jabatan" => $a
        );

        return $this->respond($res);
    }

    public function getPangkatAll(Request $request) {
        $a = \DB::table('pangkat_m as ps')
            ->select('*')->get();

        $res =  array(
            "pangkat" => $a
        );

        return $this->respond($res);
    }

    public function getPendidikanAll(Request $request) {
        $a = \DB::table('pendidikan_m as ps')
            ->select('*')->get();

        $res =  array(
            "pendidikan" => $a
        );

        return $this->respond($res);
    }

    public function getJenisPegawaiAll(Request $request) {
        $a = \DB::table('jenispegawai_m as ps')
            ->select('*')->get();

        $res =  array(
            "jenispegawai" => $a
        );

        return $this->respond($res);
    }

    public function saveDataSyncKetersediaanTempatTidur(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $idPegawai = 0;
        try {
            foreach ($data['data']['list'] as $item){
                $cekData =  DB::table('ketersediaantempattidur_t')
                    ->where('objectkelasfk',$item['objectkelasfk'])
                    ->where('profilefk',$item['profilefk'])
                    ->first();
//                return $this->respond($cekData->norec);
                if ($cekData == null){
                    $saveData = new KetersediaanTempatTidur();
                    $norec1 = substr(Uuid::generate(), 0, 32);
                    $saveData->norec = $norec1;
                    $saveData->statusenabled = 1;
                    $saveData->profilefk = $item['profilefk'];
                    $saveData->objectkelasfk = $item['objectkelasfk'];
                }else{
                    $saveData =  KetersediaanTempatTidur::where('norec',$cekData['norec'])->first();
                }
                $saveData->kapasitas = $item['kapasitas'];
                $saveData->tersedia = $item['tersedia'];
                $saveData->tglupdate = $tglAyeuna;
                $saveData->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Sync Ketersediaan Tempat Tidur Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Sync Ketersediaan Tempat Tidur Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $saveData,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Sync Ketersediaan Tempat Tidur Gagal!!";
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

    public function getProdukAll(Request $request) {
        $a = \DB::table('produk_m as ps')
            ->select('*')->get();

        $res =  array(
            "produk" => $a
        );

        return $this->respond($res);
    }

    public function getSatuanAll(Request $request) {
        $a = \DB::table('satuanstandar_m as ps')
            ->select('*')->get();

        $res =  array(
            "satuanstandar" => $a
        );

        return $this->respond($res);
    }

    public function saveDataSyncTransaksiStok(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $idPegawai = 0;
        try {
            foreach ($data['data']['list'] as $item){
                $cekData =  DB::table('transaksistok_t')
                    ->where('produkfk',$item['produkfk'])
                    ->where('profilefk',$item['profilefk'])
                    ->first();
//                return $this->respond($cekData->norec);
                if ($cekData == null){
                    $saveData = new TransaksiStok();
                    $norec1 = substr(Uuid::generate(), 0, 32);
                    $saveData->norec = $norec1;
                    $saveData->statusenabled = 1;
                    $saveData->profilefk = $item['profilefk'];
                    $saveData->produkfk = $item['produkfk'];
                }else{
                    $saveData =  TransaksiStok::where('norec',$cekData['norec'])->first();
                }
                    $saveData->satuanstandarfk = $item['satuanstandarfk'];
//                    $saveData->qtykeluar = $item['qtykeluar'];
//                    $saveData->qtymasuk = $item['qtymasuk'];
                    $saveData->total = $item['total'];
                    $saveData->tglupdate = $tglAyeuna;
                    $saveData->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Sync Transaksi Stok Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Sync Transaksi Stok Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $saveData,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Sync Ketersediaan Tempat Tidur Gagal!!";
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

    public function getKelasAll(Request $request) {
        $a = \DB::table('kelas_m as ps')
            ->select('*')->get();

        $res =  array(
            "kelas" => $a
        );

        return $this->respond($res);
    }


}
