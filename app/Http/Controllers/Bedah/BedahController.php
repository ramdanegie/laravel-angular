<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 08/08/2019
 * Time: 16:49
 */
namespace App\Http\Controllers\Bedah;

use App\Http\Controllers\ApiController;
use App\Transaksi\AntrianPasienDiperiksa;;
use App\Transaksi\PasienDaftar;;

use App\Traits\Valet;
use App\Traits\PelayananPasienTrait;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\StrukOrder;
use Illuminate\Http\Request;
use DB;
use App\Traits\SettingDataFixedTrait;
use Carbon\Carbon;

class BedahController extends ApiController
{
    use Valet, PelayananPasienTrait;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getDataComboDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$idProfile));
        $kdDeptJalan = (int) $this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $kdDeptRanapAll = explode(',',$this->settingDataFixed('KdDepartemenRIAll',$idProfile));
        $kdDeptRjRi = explode(',',$this->settingDataFixed('KdDepartemenRJRI',$idProfile));
        $kdDeptRajalAll = explode(',',$this->settingDataFixed('KdDeptRajalAll',$idProfile));
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $kdDepartemenRajalRanap = [];
        foreach ($kdDeptRjRi as $item){
            $kdDepartemenRajalRanap []=  (int)$item;
        }
        $kdDepartemenRajalAll = [];
        foreach ($kdDeptRajalAll as $bodas){
            $kdDepartemenRajalAll [] = (int)$bodas;
        }
        $dataLogin = $request->all();
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id', $kdDepartemenRawatPelayanan)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile', $idProfile)
            ->where('dept.id', $kdDeptJalan)
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile', $idProfile)
            ->whereIn('dept.id', $kdDeptRanapAll)
            ->orderBy('dept.namadepartemen')
            ->get();

        $ruanganRajal = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRajalAll)
            ->orderBy('ru.namaruangan')
            ->get();

        $ruanganRanap = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->wherein('ru.objectdepartemenfk', $kdDeptRanapAll)
            ->orderBy('ru.namaruangan')
            ->get();

        $result = array(
            'dept' => $dept,
            'ruanganRajal' => $ruanganRajal,
            'ruanganRanap' => $ruanganRanap,
            'deptrirj' => $deptRajalInap,
            'ruanganall' => $dataRuangan,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDaftarRegistrasiDokterBedah(Request $request){
        $idProfile =  (int)$this->getDataKdProfile($request);
        $kdDeptBedah = (int) $this->settingDataFixed('KdInstalasiBedahSentral',$idProfile);
        $filter = $request->all();
        $ruangId = '';
        if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
            $ruangId = ' AND ru.id = ' . $filter['ruangId'];
        }
        $noreg = '';
        if (isset($filter['noreg']) && $filter['noreg'] != "" && $filter['noreg'] != "undefined") {
            $noreg = " AND pd.noregistrasi = '" .  $filter['noreg']."'";
        }
        $norm = '';
        if (isset($filter['norm']) && $filter['norm'] != "" && $filter['norm'] != "undefined") {
            $norm = "AND ps.nocm = '" .  $filter['norm']."'";
        }
        $nama = '';
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $nama = " AND ps.namapasien ilike '%".  $filter['nama']."%'";
        }
        $tglAwal = $filter['tglawal'];
        $tglAkhir = $filter['tglakhir'];

        $data =DB::select(DB::raw("select * from
                (select pd.tglregistrasi,  ps.id as nocmfk,  ps.nocm,  pd.noregistrasi,  ps.namapasien,  ps.tgllahir, 
                 jk.jeniskelamin,  apd.objectruanganfk, ru.namaruangan,  kls.id as idkelas,kls.namakelas,  kp.kelompokpasien,  rek.namarekanan, 
                 apd.objectpegawaifk,  pg.namalengkap as namadokter,  br.norec,pg2.namalengkap as pegawaiverif, 
                 pd.norec as norec_pd, apd.tglmasuk, apd.norec as norec_apd, row_number() over (partition by pd.noregistrasi,apd.tglregistrasi order by apd.tglmasuk desc) as rownum 
                 from antrianpasiendiperiksa_t as apd
                 inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk 
                 left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                 inner join pasien_m as ps on ps.id = pd.nocmfk
                 left join jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
                 left join kelas_m as kls on kls.id = pd.objectkelasfk
                 left join ruangan_m as ru on ru.id = apd.objectruanganfk
                 left join departemen_m as dept on dept.id = ru.objectdepartemenfk
                 left join pegawai_m as pg on pg.id = apd.objectpegawaifk
                 left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                 left join rekanan_m as rek on rek.id = pd.objectrekananfk
                 left join strukorder_t as so on so.norec=apd.objectstrukorderfk
                 left join pegawai_m as pg2 on pg2.id = so.objectpetugasfk
                 where apd.kdprofile = $idProfile and br.norec is null 
                and dept.id = $kdDeptBedah 
                and apd.tglmasuk between '$tglAwal' and '$tglAkhir'
                $ruangId $noreg $norm $nama
                 --order by ru.namaruangan asc
                 ) as x where x.rownum=1")
        );
        return $this->respond($data);
    }
    public function getDokters(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdJeniPegawaiDokter = (int) $this->settingDataFixed('KdJenisPegawaiDokter',$idProfile);
        $dataLogin = $request->all();
        $dataDokters = \DB::table('pegawai_m as p')
            ->select('p.id','p.namalengkap')
            ->where('p.statusenabled', true)
            ->where('p.objectjenispegawaifk', $kdJeniPegawaiDokter)
            ->orderBy('p.namalengkap')
            ->get();

        $result = array(
            'dokter'=> $dataDokters,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function updateDokterAntrian(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            if ($request['norec_apd']!=null) {
                $apd =  AntrianPasienDiperiksa::where('norec', $request['norec_apd'])->where('kdprofile',$idProfile)->first();
                $ddddd = AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                    ->where('kdprofile',$idProfile)
                    ->update([
                            'objectpegawaifk' => $request['iddokter']
                        ]

                    );

                $pasienDaftar = PasienDaftar::where('norec',$apd->noregistrasifk)
                    ->where('kdprofile',$idProfile)
                    ->update(['objectpegawaifk' => $request['iddokter']]);
            }
            $transMessage = "Sukses";

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            DB::commit();
            $result = array(
                "status" => 201,
//                "message" =>   $transMessae,
                "message" => $transMessage,
                "struk" => $ddddd,//$noResep,,//$noResep,
                "as" => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan  Tanggal Pulang Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "struk" => $ddddd,
                "as" => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function savePelayananPasienBedah(Request $request) {
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try{
            StrukOrder::where('norec', $request['norec_so'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'statusorder' => 1
                    ]
                );
            $jenisPetugasPe = \DB::table('mapjenispetugasptojenispegawai_m as mpp')
                ->join ('jenispegawai_m as jp','jp.id','=','mpp.objectjenispegawaifk')
                ->join ('pegawai_m as pg','pg.objectjenispegawaifk','=','jp.id')
                ->join ('jenispetugaspelaksana_m as jpp','jpp.id','=','mpp.objectjenispetugaspefk')
                ->select( 'mpp.objectjenispegawaifk','jp.jenispegawai','mpp.objectjenispetugaspefk' ,'jpp.jenispetugaspe',
                    'pg.namalengkap','pg.id'
                )
                ->where('mpp.kdprofile', $kdProfile)
                ->where('pg.id', $request['objectpegawaiorderfk'])
                ->first();

            if($request['norec_pp']=='') {
                $pd = PasienDaftar::where('norec',$request['norec_pd'])->first();
                $dataAPD = new AntrianPasienDiperiksa;
                $dataAPD->norec = $dataAPD->generateNewId();
                $dataAPD->kdprofile = $kdProfile;
                $dataAPD->objectasalrujukanfk = 1;
                $dataAPD->statusenabled = true;
                $dataAPD->objectkelasfk = $request['objectkelasfk'];
                $dataAPD->noantrian = 1;
                $dataAPD->noregistrasifk = $request['norec_pd'];
                $dataAPD->objectpegawaifk = $request['objectpegawaiorderfk'];
                $dataAPD->objectruanganfk = $request['objectruangantujuanfk'];
                $dataAPD->statusantrian = 0;
                $dataAPD->statuspasien = 1;
                $dataAPD->objectstrukorderfk = $request['norec_so'];
                $dataAPD->objectpegawaifk = $request['pegawaifk'];
                $dataAPD->tglregistrasi =$request['tgloperasi'];;// date('Y-m-d H:i:s');
                $dataAPD->tglmasuk = $request['tgloperasi'];;
                $dataAPD->tglkeluar = null;
                $dataAPD->save();

                $dataSO = StrukOrder::where('norec',$request['norec_so'])
                    ->where('kdprofile', $kdProfile)
                    ->update([
                            'objectpetugasfk' => $request['iddokterverif']
                        ]);
                $dataAPDnorec = $dataAPD->norec;
                $dataAPDtglPel = $dataAPD->tglregistrasi;
            }else{
                $dataAPD =  PelayananPasien::where('norec',$request['norec_pp'])->where('kdprofile', $kdProfile)->first();
                $dataAPDnorec = $dataAPD->noregistrasifk;
                $dataAPDtglPel = $dataAPD->tglregistrasi;
                $HapusPP = PelayananPasien::where('strukorderfk', $request['norec_so'])->where('kdprofile', $kdProfile)->get();
                foreach ($HapusPP as $pp){
                    $HapusPPD = PelayananPasienDetail::where('pelayananpasien', $pp['norec'])->where('kdprofile', $kdProfile)->delete();
                    $HapusPPP = PelayananPasienPetugas::where('pelayananpasien', $pp['norec'])->where('kdprofile', $kdProfile)->delete();
                }
                $Edit = PelayananPasien::where('strukorderfk', $request['norec_so'])->where('kdprofile', $kdProfile)->delete();
            }

            foreach ($request['bridging'] as $item){
                $PelPasien = new PelayananPasien();
                $PelPasien->norec = $PelPasien->generateNewId();
                $PelPasien->kdprofile = $kdProfile;
                $PelPasien->statusenabled = true;
                $PelPasien->noregistrasifk =  $dataAPDnorec;
                $PelPasien->tglregistrasi = $dataAPDtglPel;
                $PelPasien->hargadiscount = 0;
                $PelPasien->hargajual =  $item['hargasatuan'];
                $PelPasien->hargasatuan =  $item['hargasatuan'];
                $PelPasien->jumlah =  $item['qtyproduk'];
                $PelPasien->kelasfk =  $request['objectkelasfk'];
                $PelPasien->kdkelompoktransaksi =  1;
                $PelPasien->piutangpenjamin =  0;
                $PelPasien->piutangrumahsakit = 0;
                $PelPasien->produkfk =  $item['produkid'];
                $PelPasien->stock =  1;
                $PelPasien->strukorderfk =  $request['norec_so'];
                $PelPasien->tglpelayanan = date('Y-m-d H:i:s');
                $PelPasien->harganetto =  $item['hargasatuan'];

                $PelPasien->save();
                $PPnorec = $PelPasien->norec;
                $PelPasienPetugas = new PelayananPasienPetugas();
                $PelPasienPetugas->norec = $PelPasienPetugas->generateNewId();
                $PelPasienPetugas->kdprofile = $kdProfile;
                $PelPasienPetugas->statusenabled = true;
                $PelPasienPetugas->nomasukfk = $dataAPDnorec;
                $PelPasienPetugas->objectpegawaifk = $request['iddokterverif'];//$request['objectpegawaiorderfk'];
                $PelPasienPetugas->objectjenispetugaspefk = 4;//$jenisPetugasPe->objectjenispetugaspefk;
                $PelPasienPetugas->pelayananpasien = $PPnorec;
                $PelPasienPetugas->save();
                $PPPnorec = $PelPasienPetugas->norec;


                foreach ($item['komponenharga'] as $itemKomponen) {
                    $PelPasienDetail = new PelayananPasienDetail();
                    $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
                    $PelPasienDetail->kdprofile = $kdProfile;
                    $PelPasienDetail->statusenabled = true;
                    $PelPasienDetail->noregistrasifk = $dataAPDnorec;
                    $PelPasienDetail->aturanpakai = '-';
                    $PelPasienDetail->hargadiscount = 0;
                    $PelPasienDetail->hargajual = $itemKomponen['hargasatuan'];
                    $PelPasienDetail->hargasatuan = $itemKomponen['hargasatuan'];
                    $PelPasienDetail->jumlah = 1;
                    $PelPasienDetail->keteranganlain = '-';
                    $PelPasienDetail->keteranganpakai2 = '-';
                    $PelPasienDetail->komponenhargafk = $itemKomponen['objectkomponenhargafk'];
                    $PelPasienDetail->pelayananpasien = $PPnorec;
                    $PelPasienDetail->piutangpenjamin = 0;
                    $PelPasienDetail->piutangrumahsakit = 0;
                    $PelPasienDetail->produkfk =  $item['produkid'];
                    $PelPasienDetail->stock = 1;
                    $PelPasienDetail->strukorderfk =  $request['norec_so'];
                    $PelPasienDetail->tglpelayanan =$dataAPDtglPel;
                    $PelPasienDetail->harganetto = $itemKomponen['hargasatuan'];
                    $PelPasienDetail->save();
                    $PPDnorec = $PelPasienDetail->norec;
                    $transStatus = 'true';
                }
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan PelPasien";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan PelayananPasien Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'dataPP' => $PelPasien,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan PelayananPasien Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'dataPP' => $PelPasien,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteVerifBedah(Request $request){
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try{
            StrukOrder::where('norec', $request['norec_so'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'statusenabled' => false
                    ]
                );
            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Hapus Tindakan Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Hapus Tindakan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getLaporanTindakanBedah(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $paramKlp = '';
        $paramDk = '';
        $idDokter = '';
        $dokid = '';
        $ruid = '';
        $tindakan = '';
        $nocm = $request['nocm'];
        $namapasien = $request['namapasien'];

        if(isset($request['idDokter']) && $request['idDokter']!="" && $request['idDokter']!="undefined"){
            $dokid = ' and pg.id = '.$request['idDokter'];
        }
        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $ruid = ' and rg.id = '.$request['ruid'];
        }

        if(isset($request['tindakan']) && $request['tindakan']!="" && $request['tindakan']!="undefined"){
            $tindakan = ' and pro.id = '.$request['tindakan'];
        }

        if (isset($request['KpArr']) && $request['KpArr']!="" && $request['KpArr']!="undefined"){
            $arrayKelompokPasien = explode(',',$request['KpArr']) ;
            $ids = [];
            $str = '';
            $d=0;
            foreach ( $arrayKelompokPasien as $item){
                if ($str == ''){
                    $str = $item;
                }else{
                    $str = $str . ',' . $item;
                }
                $d = $d + 1;
            }
            $paramKlp = " AND klp.id IN ($str)";
        }

        if (isset($request['dkArr']) && $request['dkArr']!="" && $request['dkArr']!="undefined"){
            $arrayDokter = explode(',',$request['dkArr']) ;
            $ids = [];
            $str = '';
            $d=0;
            foreach ( $arrayDokter as $item){
                if ($str == ''){
                    $str = $item;
                }else{
                    $str = $str . ',' . $item;
                }
                $d = $d + 1;
            }
            $paramDk = " AND pg1.id IN ($str)";
        }

        $data = DB::select(DB::raw("          
                SELECT pp.norec,pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,
                       CASE WHEN ru1.namaruangan IS NOT NULL THEN ru1.namaruangan ELSE ru2.namaruangan END AS ruangan,
                       klp.kelompokpasien,pro.namaproduk,pp.jumlah,pp.hargajual,
                       CASE WHEN pg.namalengkap IS NULL THEN '' ELSE pg.namalengkap END AS dokterdpjp,
			           CASE WHEN pg1.namalengkap IS NULL THEN '' ELSE pg1.namalengkap END AS dokter
                FROM pasiendaftar_t AS pd
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN pelayananpasienpetugas_t AS ppp ON ppp.pelayananpasien = pp.norec AND ppp.objectjenispetugaspefk = 4
                INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                INNER JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
                INNER JOIN ruangan_m AS rg ON rg.id = pd.objectruanganlastfk
                LEFT JOIN kelompokpasien_m AS klp ON klp.id = pd.objectkelompokpasienlastfk
                LEFT JOIN produk_m AS pro ON pro.id = pp.produkfk
                LEFT JOIN strukorder_t AS so ON so.norec = apd.objectstrukorderfk
                LEFT JOIN ruangan_m AS ru1 ON ru1.id = so.objectruanganfk
                LEFT JOIN ruangan_m AS ru2 ON ru2.id = apd.objectruanganasalfk
                LEFT JOIN batalregistrasi_t AS br ON br.pasiendaftarfk = pd.norec
                LEFT JOIN pegawai_m AS pg ON pg.id = apd.objectpegawaifk
                INNER JOIN pegawai_m AS pg1 ON pg1.id = ppp.objectpegawaifk
                WHERE pd.kdprofile = $kdProfile AND pd.statusenabled = true AND apd.objectruanganfk = 44
			          AND pro.namaproduk IS NOT NULL AND br.norec IS NULL
                      AND pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                      and ps.namapasien ilike '%$namapasien%'
                      and ps.nocm ilike '%$nocm%'
			          $ruid
			          $dokid
			          $paramKlp
                      $paramDk
                      $tindakan
        "));

        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getTindakanBedah(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::select(DB::raw("
            select pr.id, pr.namaproduk
            from produk_m as pr
            join mapruangantoproduk_m as mm on pr.id = mm.objectprodukfk
            where mm.objectruanganfk = 44
            order by pr.namaproduk
        "));

        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
}