<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 10/3/2019
 * Time: 4:26 PM
 */
namespace App\Http\Controllers\KiosK;

use App\Http\Controllers\ApiController;
use App\Master\Alamat;
use App\Master\Pasien;
use App\Master\Ruangan;
use App\Master\SlottingKiosk;
use App\Master\SlottingOnline;
use Carbon\Carbon;
//use Faker\Provider\DateTime;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use App\Traits\Valet;
use DB;
use App\Transaksi\AntrianPasienRegistrasi;
use App\Transaksi\SurveyKepuasanPelanggan;
use Webpatser\Uuid\Uuid;

class KiosKController extends ApiController
{
    use Valet, PelayananPasienTrait;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function saveAntrianTouchscreen(Request $request)
    {
         $kdProfile = $this->getDataKdProfile($request);
        DB::beginTransaction();
        $noRec = '';
        try {
        $tglAyeuna = date('Y-m-d H:i:s');
        $tglAwal = date('Y-m-d 00:00:00');
        $tglAkhir = date('Y-m-d 23:59:59');
        $kdRuanganTPP = $this->settingDataFixed('idRuanganTPP1',$kdProfile);

            $newptp = new AntrianPasienRegistrasi();
            $norec = $newptp->generateNewId();
            $nontrian = AntrianPasienRegistrasi::where('jenis', $request['jenis'])
                    ->whereBetween('tanggalreservasi', [$tglAwal, $tglAkhir])
                    ->max('noantrian') + 1;
            $newptp->norec = $norec;
            $newptp->kdprofile = $kdProfile;
            $newptp->statusenabled = true;
            $newptp->objectruanganfk = isset($request['ruanganfk']) ? $request['ruanganfk']: $kdRuanganTPP;
            $newptp->objectjeniskelaminfk = 0;
            $newptp->noantrian = $nontrian;
            $newptp->noreservasi = "-";
            $newptp->objectpendidikanfk = 0;
            $newptp->tanggalreservasi = $tglAyeuna;
            $newptp->tipepasien = "BARU";
            $newptp->type = "BARU";
            $newptp->jenis = $request['jenis'];
            $newptp->statuspanggil = 0;
            $newptp->iskiosk = true;
            $newptp->save();
            $noRec = $newptp->norec;


            $transMessage = "Simpan Antrian";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Antrian Gagal";
        }

        if ($transStatus != 'false') {

            DB::commit();
            $result = array(
                "noRec" => $noRec,
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
                "noRec" => $noRec,
                "status" => 400,
                "message" => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function getRuanganByKodeInternal($kode,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('ruangan_m')
            ->where('statusenabled',true)
            ->where('kdinternal', '=',$kode)
             ->where('kdprofile', '=',$kdProfile)
            ->whereRaw("(iseksekutif is null or iseksekutif =false)")
            ->orderBy('namaruangan')
            ->first();

        $result = array(
            'data' => $data,
            'as' => 'ramdan@epic',
        );
        return $this->respond($result);
    }
    public function getDiagnosaByKode($kode,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
     
        $data = \DB::table('diagnosa_m')
            ->where('statusenabled',true)
            ->where('kddiagnosa', '=',$kode)
             ->where('kdprofile', '=',$kdProfile)
            ->first();

        $result = array(
            'data' => $data,
            'as' => 'ramdan@epic',
        );
        return $this->respond($result);
    }
    public function getKetersediaanTempatTidurView (Request $request)
    {

        $kdProfile = $this->getDataKdProfile($request);
        $namaruangan= $request['namaruangan'];
        $idkelas= $request['idkelas'];
        $dataLogin = $request->all();
        if($namaruangan == "" && $idkelas == ""){
            $data = DB::select(DB::raw("select  COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
			    sum(x.kamarprosesadmin) as kamarprosesadmin, sum(x.kamartakterpakai) as kamartakterpakai from 
                (select 
                 ru.namaruangan, 
                 km.namakamar,
                 kl.id as kelasid,
                 kl.namakelas, 
                 tt.reportdisplay, 
                 tt.nomorbed, 
                 sb.id as idstatusbed, 
                 sb.statusbed,
                 (case when sb.id=1 then 1 else 0 end) as kamarisi,
                 (case when sb.id=2 then 1 else 0 end) as kamarkosong,
                 (case when sb.id=3 then 1 else 0 end) as kamarprosesadmin,
                 (case when sb.id=4 then 1 else 0 end) as kamartakterpakai
                 from tempattidur_m as tt
                 left join kamar_m as km on km.id = tt.objectkamarfk
                 left join ruangan_m as ru on ru.id = km.objectruanganfk
                 left join statusbed_m as sb on sb.id = tt.objectstatusbedfk
                 left join kelas_m as kl on kl.id = km.objectkelasfk
                 where ru.objectdepartemenfk in (16,35) and ru.statusenabled=true
                 and ru.kdprofile =$kdProfile
				 and km.statusenabled=true and tt.statusenabled=true)as x  limit 1"),
                array(
//                    'namaruangan' => $namaruangan,
//                    'idkelas' => $idkelas,
                )
            );
        } elseif ($namaruangan != "" && $idkelas == ""){
            $data = DB::select(DB::raw("select COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
			    sum(x.kamarprosesadmin) as kamarprosesadmin, sum(x.kamartakterpakai) as kamartakterpakai from 
                (select 
                 ru.namaruangan, 
                 km.namakamar,
                 kl.id as kelasid,
                 kl.namakelas, 
                 tt.reportdisplay, 
                 tt.nomorbed, 
                 sb.id as idstatusbed, 
                 sb.statusbed,
                 (case when sb.id=1 then 1 else 0 end) as kamarisi,
                 (case when sb.id=2 then 1 else 0 end) as kamarkosong,
                 (case when sb.id=3 then 1 else 0 end) as kamarprosesadmin,
                 (case when sb.id=4 then 1 else 0 end) as kamartakterpakai
                 from tempattidur_m as tt
                 left join kamar_m as km on km.id = tt.objectkamarfk
                 left join ruangan_m as ru on ru.id = km.objectruanganfk
                 left join statusbed_m as sb on sb.id = tt.objectstatusbedfk
                 left join kelas_m as kl on kl.id = km.objectkelasfk
                 where ru.objectdepartemenfk in (16,35) 
                  and ru.kdprofile =$kdProfile
                  and ru.namaruangan=:namaruangan)as x"),
                array(
                    'namaruangan' => $namaruangan,
//                    'idkelas' => $idkelas,
                )
            );
        } elseif ($namaruangan == "" && $idkelas != ""){
            $data = DB::select(DB::raw("select COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
			    sum(x.kamarprosesadmin) as kamarprosesadmin, sum(x.kamartakterpakai) as kamartakterpakai from 
                (select 
                 ru.namaruangan, 
                 km.namakamar,
                 kl.id as kelasid,
                 kl.namakelas, 
                 tt.reportdisplay, 
                 tt.nomorbed, 
                 sb.id as idstatusbed, 
                 sb.statusbed,
                 (case when sb.id=1 then 1 else 0 end) as kamarisi,
                 (case when sb.id=2 then 1 else 0 end) as kamarkosong,
                 (case when sb.id=3 then 1 else 0 end) as kamarprosesadmin,
                 (case when sb.id=4 then 1 else 0 end) as kamartakterpakai
                 from tempattidur_m as tt
                 left join kamar_m as km on km.id = tt.objectkamarfk
                 left join ruangan_m as ru on ru.id = km.objectruanganfk
                 left join statusbed_m as sb on sb.id = tt.objectstatusbedfk
                 left join kelas_m as kl on kl.id = km.objectkelasfk
                 where ru.objectdepartemenfk in (16,35)
                  and ru.kdprofile =$kdProfile
                   and kl.id=:idkelas)as x"),
                array(
//                    'namaruangan' => $namaruangan,
                    'idkelas' => $idkelas,
                )
            );
        } else {
            $data = DB::select(DB::raw("select COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
			    sum(x.kamarprosesadmin) as kamarprosesadmin, sum(x.kamartakterpakai) as kamartakterpakai from 
                (select 
                 ru.namaruangan, 
                 km.namakamar,
                 kl.id as kelasid,
                 kl.namakelas, 
                 tt.reportdisplay, 
                 tt.nomorbed, 
                 sb.id as idstatusbed, 
                 sb.statusbed,
                 (case when sb.id=1 then 1 else 0 end) as kamarisi,
                 (case when sb.id=2 then 1 else 0 end) as kamarkosong,
                 (case when sb.id=3 then 1 else 0 end) as kamarprosesadmin,
                 (case when sb.id=4 then 1 else 0 end) as kamartakterpakai
                 from tempattidur_m as tt
                 left join kamar_m as km on km.id = tt.objectkamarfk
                 left join ruangan_m as ru on ru.id = km.objectruanganfk
                 left join statusbed_m as sb on sb.id = tt.objectstatusbedfk
                 left join kelas_m as kl on kl.id = km.objectkelasfk
                 where ru.objectdepartemenfk in (16,35) 
                  and ru.kdprofile =$kdProfile
                  and ru.namaruangan=:namaruangan and kl.id=:idkelas)as x"),
                array(
                    'namaruangan' => $namaruangan,
                    'idkelas' => $idkelas,
                )
            );
        }
        return $this->respond($data);
    }
    public function viewBed(Request $request)
    {
        $data= \DB::table('tempattidur_m as tt')
            ->leftjoin('kamar_m as km', 'km.id', '=', 'tt.objectkamarfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'km.objectruanganfk')
            ->leftjoin('statusbed_m as sb', 'sb.id', '=', 'tt.objectstatusbedfk')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'km.objectkelasfk')
            ->select('ru.id as idruangan','ru.namaruangan','km.id as idkamar','km.namakamar','tt.id as idtempattidur',
                'tt.reportdisplay','tt.nomorbed','sb.id as idstatusbed','sb.statusbed','kl.id as idkelas','kl.namakelas')
            ->whereIn('ru.objectdepartemenfk',array(16,35))
            ->where('ru.statusenabled',true)
            ->where('km.statusenabled',true)
            ->where('tt.statusenabled',true);

        if(isset($request['namaruangan']) && $request['namaruangan']!="" && $request['namaruangan']!="undefined"){
            $data = $data->where('ru.namaruangan','ilike','%'. $request['namaruangan'] .'%');
        };
        if(isset($request['namakamar']) && $request['namakamar']!="" && $request['namakamar']!="undefined"){
            $data = $data->where('km.namakamar','ilike','%'. $request['namakamar'] .'%');
        };
        if(isset($request['idkelas']) && $request['idkelas']!="" && $request['idkelas']!="undefined"){
            $data = $data->where('kl.id', $request['idkelas']);
        };
        if(isset($request['namabed']) && $request['namabed']!="" && $request['namabed']!="undefined"){
            $data = $data->where('tt.reportdisplay','ilike','%'. $request['namabed'] .'%');
        };
        if(isset($request['idstatusbed']) && $request['idstatusbed']!="" && $request['idstatusbed']!="undefined"){
            $data = $data->where('sb.id', $request['idstatusbed']);
        };
        $data = $data->get();


        return $this->respond($data);
    }
    public function getDataCombo(Request $request)
    {
        $dataLogin = $request->all();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk','ru.kdinternal')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataKelas = \DB::table('kelas_m as kl')
            ->select('kl.id', 'kl.namakelas')
            ->where('kl.statusenabled', true)
            ->orderBy('kl.namakelas')
            ->get();

        $result = array(
            'ruangan' => $dataRuangan,

            'kelas' => $dataKelas,

            'message' => 'ramdan',
        );

        return $this->respond($result);
    }
    public function getDaftarTarif(Request $request)
    {
        $filter = $request->all();
        $produkid= '';
        if  ($filter['produkId'] != ''){
            $produkid= 'AND pr.id = ' . $filter['produkId'];
        }
        $ruanganid = '';
        if ($filter['ruanganId'] != ''){
            $ruanganid ='AND mprtp.objectruanganfk =' . $filter['ruanganId'];
        }
        $kelasid = '';
        if ($filter['kelasId'] != ''){
            $kelasid ='AND kls.id =' . $filter['kelasId'];
        }
        $jenispelid = '';
        if ($filter['jenispelayananId'] != ''){
            $jenispelid ='AND jnsp.id =' . $filter['jenispelayananId'];
        }
        $namaproduk = '';
        if ($filter['namaproduk'] != ''){
            $namaproduk ="AND pr.namaproduk like '%" . $filter['namaproduk'] . "%'";
        }

//        $jenispelid =$filter['jenispelid'];
        $data =DB::select(DB::raw("
               SELECT 
                distinct   pr.id,
                pr.namaproduk,
                hrpk.harganetto1 AS hargalayanan,kls.id as idkelas,kls.namakelas ,jnsp.id as jenispelayananid,jnsp.jenispelayanan,mprtp.objectruanganfk as ruid,
                  ru.id as ruid,ru.namaruangan
                FROM
                produk_m AS pr
                INNER JOIN mapruangantoproduk_m AS mprtp ON mprtp.objectprodukfk = pr.id
             
               
                LEFT JOIN harganettoprodukbykelas_m AS hrpk ON hrpk.objectprodukfk = pr.id
                INNER JOIN kelas_m as kls on kls.id=hrpk.objectkelasfk
                INNER JOIN jenispelayanan_m as jnsp on jnsp.id=hrpk.objectjenispelayananfk
                INNER JOIN ruangan_m as ru on ru.id=mprtp.objectruanganfk
                WHERE
                hrpk.statusenabled = true
                AND pr.statusenabled = true
          
            
                $produkid
                 $ruanganid 
                 $kelasid $jenispelid $namaproduk

                 limit 100
                  ")
        );

        $results =array();

        $produkid= '';
        if  ($filter['produkId'] != ''){
            $produkid= 'AND pr.id = ' . $filter['produkId'];
        }
        $ruanganid = '';
        if ($filter['ruanganId'] != ''){
            $ruanganid ='AND mprtp.objectruanganfk =' . $filter['ruanganId'];
        }
        $kelasid =$filter['kelasId'];
//        $jenispelid =$filter['jenispelid'];
//        $details =DB::select(DB::raw("
//                select  pr.id as kdeproduk,pr.namaproduk,kls.id as klsid,kh.id as idkomponen,kh.komponenharga,hrpkd.harganetto1,
//                hrpkd.harganetto2,hrpkd.hargasatuan,jnspel.id as jnspelid
//                from produk_m as pr
//                INNER JOIN mapruangantoproduk_m AS mprtp ON mprtp.objectprodukfk = pr.id
//                left join detailjenisproduk_m as djp on djp.id = pr.objectdetailjenisprodukfk
//                left join jenisproduk_m as jp on jp.id = djp.objectjenisprodukfk
//                left join kelompokproduk_m as kp on kp.id = jp.objectkelompokprodukfk
//                left join harganettoprodukbykelasd_m as hrpkd on hrpkd.objectprodukfk = pr.id
//                inner join kelas_m as kls on kls.id = hrpkd.objectkelasfk
//                inner join komponenharga_m as kh on kh.id = hrpkd.objectkomponenhargafk
//                INNER JOIN jenispelayanan_m as jnspel on jnspel.id=hrpkd.objectjenispelayananfk
//                where pr.statusenabled=1 and hrpkd.statusenabled = 1
//                --and pr.id =1002120383
//                --and kls.id=1 and jnspel.id=1
//                $produkid $ruanganid ")
//        );
//        foreach ($data as $item){
//            foreach ($details as $dtl){
//                if  ($item->id == $dtl->kdeproduk){
//                    $results[] = array(
//                        'kodeproduk' => $item->id,
//                        'namaproduk' => $item->namaproduk,
//                        'kdruangan' => $item->koderuangan,
//                        'namaruangan' => $item->namaruangan,
//                        'idkelas' => $item->idkelas,
//                        'namakelas' => $item->namakelas,
//                        'hargalayanan' => $item->hargalayanan,
//                        'details' => $dtl,
//                    );
//                }
//            }
//
//
//        }
        $result = array(
            'data'=>$data,
//            'detail'=> $details,
            'message' => 'cepot',
        );
        return $this->respond($result);
    }
    public function getDataViewBed(Request $request){

        $data= \DB::table('tempattidur_m as tt')
            ->leftjoin('kamar_m as km', 'km.id', '=', 'tt.objectkamarfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'km.objectruanganfk')
            ->leftjoin('statusbed_m as sb', 'sb.id', '=', 'tt.objectstatusbedfk')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'km.objectkelasfk')
            ->select('ru.id as idruangan','ru.namaruangan','km.id as idkamar','km.namakamar','tt.id as idtempattidur',
                'tt.reportdisplay','tt.nomorbed','sb.id as idstatusbed','sb.statusbed','kl.id as idkelas','kl.namakelas')
            ->whereIn('ru.objectdepartemenfk',array(16,35))
            ->where('ru.statusenabled',true)
            ->where('km.statusenabled',true)
            ->where('tt.statusenabled',true);

        if(isset($request['namaruangan']) && $request['namaruangan']!="" && $request['namaruangan']!="undefined"){
            $data = $data->where('ru.namaruangan','ilike','%'. $request['namaruangan'] .'%');
        };
        if(isset($request['namakamar']) && $request['namakamar']!="" && $request['namakamar']!="undefined"){
            $data = $data->where('km.namakamar','ilike','%'. $request['namakamar'] .'%');
        };
        if(isset($request['idkelas']) && $request['idkelas']!="" && $request['idkelas']!="undefined"){
            $data = $data->where('kl.id', $request['idkelas']);
        };
        if(isset($request['namabed']) && $request['namabed']!="" && $request['namabed']!="undefined"){
            $data = $data->where('tt.reportdisplay','ilike','%'. $request['namabed'] .'%');
        };
        if(isset($request['idstatusbed']) && $request['idstatusbed']!="" && $request['idstatusbed']!="undefined"){
            $data = $data->where('sb.id', $request['idstatusbed']);
        };
        $data = $data->get();


        return $this->respond($data);
    }
      public function saveSurvey(Request $request)
        {
            $kdProfile = $this->getDataKdProfile($request);
            $dataLogin = $request->all();
            DB::beginTransaction();

            try {

                $newptp = new SurveyKepuasanPelanggan();
                $newptp->norec = $newptp->generateNewId();
                $newptp->kdprofile = $kdProfile;
                $newptp->statusenabled = true;
                $newptp->objectparameterkepuasanfk = $request['id'];
                $newptp->namalengkap =  $request['namalengkap'];
                $newptp->tglsurvey =  date('Y-m-d H:i:s');
                $newptp->save();
                $transMessage = "Simpan Survey";
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
                $transMessage = "Simpan Survey Gagal";
            }

            if ($transStatus != 'false') {

                DB::commit();
                $result = array(

                    "status" => 201,
                    "message" => $transMessage,
                );
            } else {
                DB::rollBack();
                $result = array(

                    "status" => 400,
                    "message" => $transMessage,
                );
            }
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);

        }
        public function getComboDokterKios(Request $request){
            $kdProfile = $this->getDataKdProfile($request);
            $kdJenisPegawaiDokter = $this->settingDataFixed('kdJenisPegawaiDokter',$kdProfile);
            $req = $request->all();
            $data = \DB::table('pegawai_m')
                ->select('id as kode','namalengkap as nama')
                ->where('statusenabled', true)
                ->where('objectjenispegawaifk',$kdJenisPegawaiDokter)
                ->where('kdprofile', (int)$kdProfile)
                ->orderBy('namalengkap');

            if(isset($req['namalengkap']) &&
                $req['namalengkap']!="" &&
                $req['namalengkap']!="undefined"){
                $data = $data->where('namalengkap','ilike','%'. $req['namalengkap'] .'%' );
            }
      

            $data = $data->take(50);
            $data = $data->get();

            return $this->respond($data);
    }
     public function getComboSettingKios(Request $request){
            $kdProfile = $this->getDataKdProfile($request);
            $kodePPKRujukan = $this->settingDataFixed('kodePPKRujukan',$kdProfile);
            $isTemporaryBrigding = $this->settingDataFixed('isTemporaryBrigding',$kdProfile);
            $isAdminOtomatisKiosk = $this->settingDataFixed('isAdminOtomatisKiosk',$kdProfile);
            $data['ppkpelayanan']= $kodePPKRujukan;
            $data['isTemporaryBrigding']= $isTemporaryBrigding;
            $data['isAdminOtomatisKiosk']= $isAdminOtomatisKiosk;
            return $this->respond($data);
    }

    public function getComboRuanganKios(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $set = explode (',',$this->settingDataFixed('kdDepartemenKiosk',$kdProfile));
        $dp = [];
        foreach ($set as $it){
            $dp []=  (int)$it;
        }
        $data = DB::table('ruangan_m')->where('kdprofile',$kdProfile)
           ->where('statusenabled',true)
            ->whereIn('objectdepartemenfk',$dp)
           ->select('id','reportdisplay as namaruangan')
           ->orderby('namaruangan')
           ->get();
        return $this->respond($data);
    }
    public function getSlottingKios(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $ruangan = \DB::table('ruangan_m as ru')
            ->join('slottingkiosk_m as slot', 'slot.objectruanganfk', '=', 'ru.id')
            ->select('ru.id as idruangan','slot.id', 'ru.namaruangan', 'ru.objectdepartemenfk', 'slot.jambuka', 'slot.jamtutup',
                'slot.quota',
                DB::raw("extract(hour from slot.jamtutup) -extract(hour from slot.jambuka)as totaljam"))
            // DB::raw("datepart(hour,slot.jamtutup) -datepart(hour, slot.jambuka)as totaljam"))
            ->where('ru.statusenabled', true)
            ->where('slot.kdprofile', $kdProfile)
            ->where('slot.statusenabled', true);
//			->where('ru.id', $kode)
        if(isset($request['namaRuangan']) && $request['namaRuangan']!='undefined' && $request['namaRuangan']!=''){
            $ruangan =$ruangan->where('ru.namaruangan','ilike','%'.$request['namaRuangan'].'%');
        }
        if(isset($request['quota']) && $request['quota']!='undefined' && $request['quota']!=''){
            $ruangan =$ruangan->where('slot.quota','=',$request['quota']);
        }
        $ruangan=$ruangan->get();

        $result = array(
            'data' => $ruangan,
            'as' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function saveSlottingKios(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            if($request['id'] == ''){
                $newptp = new SlottingKiosk();
                $newptp->id = SlottingKiosk::max('id')+1;
                $newptp->statusenabled = true;
                $newptp->kdprofile = $kdProfile;
            }else{
                $newptp = SlottingKiosk::where('id',$request['id'])->first();
            }

            $newptp->objectruanganfk = $request['objectruanganfk'];
            $newptp->jambuka = $request['jambuka'];
            $newptp->jamtutup =  $request['jamtutup'];
            $newptp->quota =  $request['quota'];
            $newptp->save();

            $transMessage = "Sukses";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Slotting Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "data" =>$newptp,
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
//				"noRec" =>$noRec,
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteSlotting(Request $request){
        try {
            SlottingKiosk::where('id',$request['id'])->delete();
            $transMessage = "Sukses ";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Hapus slotting Gagal";
        }
        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getSlottingKosong(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $tgl = date('Y-m-d');
        $data = \DB::table('antrianpasienregistrasi_t as apr')
            ->select('apr.norec','apr.tanggalreservasi')
            ->whereRaw(" to_char(apr.tanggalreservasi,'yyyy-MM-dd') = '$tgl'")
            ->where('apr.objectruanganfk', $request['ruanganfk'])
            ->where('apr.noreservasi','=','-')
            ->where('apr.statusenabled',true)
            ->where('apr.kdprofile',$kdProfile)
            ->count();

         $dataSelfReg = \DB::table('pasiendaftar_t')
            ->select('norec','tglregistrasi','noregistrasi')
            ->whereRaw(" to_char(tglregistrasi,'yyyy-MM-dd') = '$tgl'")
            ->where('objectruanganlastfk', $request['ruanganfk'])
            ->where('statusenabled',true)
            ->where('kdprofile',$kdProfile)
            ->where('statusschedule','Kios-K')
            ->count();
        $dataSlot = SlottingKiosk::where('objectruanganfk', $request['ruanganfk'])
            ->where('statusenabled',true)
            ->first();
        $s['status'] = true;
        // return $data+  $dataSelfReg;
        if(empty($dataSlot)){
            $s['status'] = 'Kuota Ruangan belum di setting';
            return $this->respond($s);
        }

        if(($data +  $dataSelfReg) < (float) $dataSlot->quota ){
            $s['status'] = true;
        }else{
            $s['status'] = 'Kuota Sudah Habis, Mohon Hubungi Administrator IT !';
        }
        return $this->respond($s);


    }
    public function getListLoket(Request $request){
//        $kdProfile = $this->getDataKdProfile($request);
//        $loket = array(
//            'Loket '
//        );
//
//        return $this->respond($s);
    }
    public function getDokterInternal(Request $request)
    {
         $kdProfile = $this->getDataKdProfile($request);
        $dat = DB::table('pegawai_m')
        ->select('id','namalengkap')
        ->where('statusenabled',true)
        ->where('kdprofile',$kdProfile)
        ->where('kodeexternal',$request['kode'])
        ->first();
        if(!empty( $dat)){
           return $this->respond($dat);
       }else{
          return $this->respond(false);
       }
    }
    public function getComboKios2(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $deptJalan = explode (',',$this->settingDataFixed('kdDepartemenReservasiOnline',   $kdProfile ));
        $kdDepartemenRawatJalan = [];
        foreach ($deptJalan as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }

        $dataRuanganJalan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $kdProfile)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatJalan);
         if(isset($request['eksek']) && $request['eksek'] !='' && $request['eksek']!='undefined' ){
            if($request['eksek']=='false'){
             $dataRuanganJalan =$dataRuanganJalan->whereRaw("(ru.iseksekutif is null or ru.iseksekutif =false)")   ;
            }else if($request['eksek']=='true'){
                 $dataRuanganJalan =$dataRuanganJalan->whereRaw(" ru.iseksekutif =true");   
            }
         }   

         $dataRuanganJalan=$dataRuanganJalan->orderBy('ru.namaruangan');
         $dataRuanganJalan=$dataRuanganJalan->get();
     
        $kdJenisPegawaiDokter = $this->settingDataFixed('kdJenisPegawaiDokter',   $kdProfile );

        $dkoter = \DB::table('pegawai_m')
            ->select('*')
            ->where('statusenabled', true)
              ->where('kdprofile', $kdProfile)
            ->where('objectjenispegawaifk',$kdJenisPegawaiDokter)
            ->orderBy('namalengkap')
            ->get();

       
        $result = array(
            'ruanganrajal' => $dataRuanganJalan,
            'dokter' => $dkoter,
            'message' => 'ramdan@epic',
        );

        return $this->respond($result);
    }
}