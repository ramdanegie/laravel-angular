<?php

/**
 * Created by PhpStorm.
 * PasienController
 * User: as@epic
 * Date: 9/19/2017
 * Time: 11:44 PM
 */

/**
 * Created by PhpStorm.
 * EISController
 * User: Egie Ramdan
 * Date: 08/08/2018
 * Time: 16.17
 */
/**
 * Created by PhpStorm.
 * RegistrasiPasienController
 * User: Ramdanegie
 * Date: 01/03/2018
 * Time: 16.20
 */
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 08/08/2019
 * Time: 14:51
 */

namespace App\Http\Controllers\RawatInap;

use App\Http\Controllers\ApiController;
use App\Master\Pasien;
use App\Master\PegawaiJadwalKerja;
use App\Master\TempatTidur;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\BatalRegistrasi;
use App\Transaksi\CheklisApd;
use App\Transaksi\EdukasiIpcln;
use App\Transaksi\IndikatorPasienJatuh;
use App\Transaksi\DiagnosaPasien;
use App\Transaksi\LoggingUser;
use App\Transaksi\PemakaianAsuransi;
use App\Transaksi\RegistrasiPelayananPasien;
use App\Transaksi\StrukBuktiPenerimaan;
use App\Transaksi\StrukBuktiPengeluaran;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\PasienDaftar;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PengkajianAwalBaru;
use App\Transaksi\RekamMedis;
use App\Transaksi\Anamnesis;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\StrukPelayananPenjamin;
use App\Transaksi\TempBilling;
use App\Master\JenisPetugasPelaksana;
use App\Master\Pegawai;
use App\Master\Ruangan;
use App\Master\Departemen;

//use App\Transaksi\StrukPelayananDetailK;
use App\Transaksi\HistoriCetakDokumen;
use App\Traits\Valet;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Transaksi\PostingJurnalTransaksi;
use App\Transaksi\LogAcc;
use App\Traits\SettingDataFixedTrait;
use Carbon\Carbon;

class RawatInapController extends ApiController
{
    use Valet, PelayananPasienTrait;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getDataComboDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', (int)$kdProfile)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.id', '18')
            ->where('dept.kdprofile', (int)$kdProfile)
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->whereIn('dept.id', [16,17,35])
            ->where('dept.kdprofile', (int)$kdProfile)
            ->orderBy('dept.namadepartemen')
            ->get();

        $ruanganRajal = \DB::table('ruangan_m as ru')
            ->where('statusenabled',true)
            ->wherein('ru.objectdepartemenfk', [18,24,28,27,3])
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();

        $ruanganRanap = \DB::table('ruangan_m as ru')
            ->where('statusenabled',true)
            ->wherein('ru.objectdepartemenfk', [16,17,35])
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();

//        $dataDokter = \DB::table('pegawai_m as ru')
//            ->where('ru.statusenabled', true)
//            ->where('ru.objectjenispegawaifk', 1)
//            ->orderBy('ru.namalengkap')
//            ->get();
//        foreach ($dataInstalasi as $item) {
//            $detail = [];
//            foreach ($dataRuangan as $item2) {
//                if ($item->id == $item2->objectdepartemenfk) {
//                    $detail[] = array(
//                        'id' => $item2->id,
//                        'ruangan' => $item2->namaruangan,
//                    );
//                }
//            }
//
//            $dataDepartemen[] = array(
//                'id' => $item->id,
//                'departemen' => $item->namadepartemen,
//                'ruangan' => $detail,
//            );
//        }
//        $dataKelompok = \DB::table('kelompokpasien_m as kp')
//            ->select('kp.id', 'kp.kelompokpasien')
//            ->where('kp.statusenabled', true)
//            ->orderBy('kp.kelompokpasien')
//            ->get();
//
//        $dataKelas = \DB::table('kelas_m as kl')
//            ->select('kl.id', 'kl.reportdisplay')
//            ->where('kl.statusenabled', true)
//            ->orderBy('kl.reportdisplay')
//            ->get();
//
//        $pembatalan = \DB::table('pembatal_m as p')
//            ->select('p.id', 'p.name')
//            ->where('p.statusenabled', true)
//            ->orderBy('p.name')
//            ->get();
//
//        $jenisDiagnosa = \DB::table('jenisdiagnosa_m as jd')
//            ->select('jd.id', 'jd.jenisdiagnosa')
////            ->where('jd.id',5)
//            ->where('jd.statusenabled', true)
//            ->orderBy('jd.jenisdiagnosa')
//            ->get();
//
//        $kdeDiagnosa = \DB::table('diagnosa_m as dm')
//            ->select('dm.id','dm.kddiagnosa')
//            ->where('dm.statusenabled', true)
//            ->orderBy('dm.id')
//            ->get();
//
//        $Diagnosa = \DB::table('diagnosa_m as dm')
//            ->select('dm.id','dm.namadiagnosa')
//            ->where('dm.statusenabled', true)
//            ->orderBy('dm.id')
//            ->get();

        $result = array(
//            'departemen' => $dataDepartemen,
//            'kelompokpasien' => $dataKelompok,
//            'dokter' => $dataDokter,
//            'datalogin' => $dataLogin,
//            'kelas' => $dataKelas,
            'dept' => $dept,
            'ruanganRajal' => $ruanganRajal,
            'ruanganRanap' => $ruanganRanap,
            'deptrirj' => $deptRajalInap,
            'ruanganall' => $dataRuangan,
//            'pembatalan' => $pembatalan,
//            'jenisdiagnosa'=> $jenisDiagnosa,
//            'diagnosa'=> $Diagnosa,
//            'kddiagnosa'=> $kdeDiagnosa,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDaftarRegistrasiDokterRanap(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $ruangId = '';
        if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
            $ruangId = ' AND ru.id = ' . $filter['ruangId'];
        }
        $noreg = '';
        if (isset($filter['noreg']) && $filter['noreg'] != "" && $filter['noreg'] != "undefined") {
            $noreg = " AND pd.noregistrasi = '" .  $filter['noreg']."'";
//            $data = $data->where('pd.noregistrasi','=', $filter['noreg']);
        }
        $norm = '';
        if (isset($filter['norm']) && $filter['norm'] != "" && $filter['norm'] != "undefined") {
            $norm = " AND ps.nocm ilike '%" .  $filter['norm']."%'";
//            $data = $data->where('ps.nocm', 'ilike', '%' . $filter['norm'] . '%');
        }
        $nama = '';
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $nama = " AND ps.namapasien ilike '%".  $filter['nama']."%'";
//            $data = $data->where('ps.namapasien', 'ilike', '%' . $filter['nama'] . '%');
        }

        $data =DB::select(DB::raw("select * from
                (select pd.tglregistrasi,  ps.id as nocmfk,  ps.nocm,  pd.noregistrasi,  ps.namapasien,  ps.tgllahir, 
                 jk.jeniskelamin,  apd.objectruanganfk, ru.namaruangan,  kls.id as idkelas,kls.namakelas,  kp.kelompokpasien,  rek.namarekanan, 
                 apd.objectpegawaifk,  pg.namalengkap as namadokter,ps.iskompleks,
                  --br.norec, 
                  klstg.namakelas as kelasditanggung,
                  age(current_date, to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))as lamarawat,
                 pd.norec as norec_pd, apd.tglmasuk, apd.norec as norec_apd, row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
                 from antrianpasiendiperiksa_t as apd
                 inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk and pd.objectruanganlastfk = apd.objectruanganfk
             --    left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                 inner join pasien_m as ps on ps.id = pd.nocmfk
                 left join registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec
                 left join jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
                 inner join kelas_m as kls on kls.id = pd.objectkelasfk
                 inner join ruangan_m as ru on ru.id = apd.objectruanganfk
                 inner join departemen_m as dept on dept.id = ru.objectdepartemenfk
                 left join pegawai_m as pg on pg.id = pd.objectpegawaifk
                 left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                 left join rekanan_m as rek on rek.id = pd.objectrekananfk
                left join pemakaianasuransi_t as pa on pa.noregistrasifk=pd.norec
                left join asuransipasien_m as asu on pa.objectasuransipasienfk=asu.id
                left join kelas_m as klstg on klstg.id=asu.objectkelasdijaminfk
                 --where br.norec is null 
                 where pd.statusenabled = true and pd.kdprofile = $idProfile
                --and dept.id in (16,  17,  35) 
                and pd.tglpulang is null --and pd.noregistrasi='1808010084'
                $ruangId $noreg $norm $nama
                
                 --order by ru.namaruangan asc
                 ) as x where x.rownum=1")
        );
//        if(count($data) > 0){
//            foreach ( $data  as $item){
//                if($item->foto != null ){
//                    $item->foto = "data:image/jpeg;base64," . base64_encode($item->foto);
//                }
//            }
//        }
        return $this->respond($data);
    }
    public function getDokters(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $dataDokters = \DB::table('pegawai_m as p')
            ->select('p.id','p.namalengkap')
            ->where('p.statusenabled', true)
            ->where('p.objectjenispegawaifk', 1)
            ->where('p.kdprofile', (int)$kdProfile)
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
        DB::beginTransaction();
        try {

            if ($request['norec_apd']!=null) {
                $apd =  AntrianPasienDiperiksa::where('norec', $request['norec_apd']) ->where('kdprofile', (int)$kdProfile)->first();
                // return $apd;
                $ddddd = AntrianPasienDiperiksa::where('norec', $request['norec_apd']) ->where('kdprofile', (int)$kdProfile)
                    ->update([
                            'objectpegawaifk' => $request['iddokter']
                        ]

                    );

                $pasienDaftar = PasienDaftar::where('norec',$apd->noregistrasifk)->where('kdprofile', (int)$kdProfile)->update([
                    'objectpegawaifk' => $request['iddokter']
                ]);
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
            $transMessage = "Simpan  Gagal!!";
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
    public function getRuanganLast(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as st')
            ->select('st.norec','st.objectruanganlastfk')
            ->where('st.norec', $request['norec_pd'])
            ->where('st.kdprofile', (int)$kdProfile)
            ->get();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function saveIndikatorPasienJatuh(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            if ($request['norec'] == ''){
                $new = new IndikatorPasienJatuh();
                $new->kdprofile = (int)$kdProfile;
                $new->norec = $new->generateNewId();

            }else{
                $new = IndikatorPasienJatuh::where('norec', $request['norec']) ->where('kdprofile', (int)$kdProfile)->first();
            }

            $new->statusenabled = $request['statusenabled'];
            $pasien = Pasien::where('nocm', $request['nocm'])->first();
            if(isset($request['nocmfk'])){
                $new->nocmfk = $request['nocmfk'] ;
            }ELSE{
                $new->nocmfk =$pasien->id;
            }

            $new->noregistrasifk = $request['noregistrasifk'] ;
            $new->tgljatuh = $request['tgljatuh'] ;
            $new->keterangan = $request['keterangan'] ;
            $new->jumlah = $request['jumlah'] ;
            $new->save();

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'result' => $new,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveAkomodasiOtomatis(Request $request) {
//        ini_set('max_execution_time', 3000); //6 minutes
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $data2 = DB::select(DB::raw("select apd.tglmasuk,apd.tglkeluar,apd.norec as norec_apd,pd.tglregistrasi
                    from pasiendaftar_t as pd
                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                    INNER JOIN ruangan_m as ru_pd on ru_pd.id=apd.objectruanganfk
                    where ru_pd.objectdepartemenfk=16 and apd.kdprofile = $idProfile
                    and pd.noregistrasi=:noregistrasi and pd.tglpulang is null order by apd.tglmasuk;"),
                array(
                    'noregistrasi' => $request['noregistrasi'],
                )
            );
            foreach ($data2 as $dateAPD){
                $tglMasuk = $dateAPD->tglmasuk;
                if (is_null($dateAPD->tglkeluar) == true){
                    $tglKeluar = date('Y-m-d 23:59:59');
                }else{
                    $tglKeluar = $dateAPD->tglkeluar;
                }
                $arrDate = $this->dateRange( $tglMasuk, $tglKeluar);
//                $arrDate = $this->dateRange( '2010-07-26', '2010-08-05');
                foreach ($arrDate as $itemDate){
                    $tglAwal = $itemDate . ' 00:00';
                    $tglAkhir = $itemDate . ' 23:59';

                    $data = DB::select(DB::raw("select pp.tglpelayanan,rpp.objectkelasfk,
                    rpp.objectruanganfk,rpp.israwatgabung
                    from pasiendaftar_t as pd
                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec
                    INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                    INNER JOIN produk_m as pr on pr.id=pp.produkfk
                    INNER JOIN ruangan_m as ru_pd on ru_pd.id=pd.objectruanganlastfk
                    where pd.tglpulang is null  and ru_pd.objectdepartemenfk=16 and pd.kdprofile = $idProfile
                    and pp.tglpelayanan between :tglAwal and :tglAkhir and pr.namaproduk ilike '%akomodasi%'
                    and pd.noregistrasi=:noregistrasi ;"),
                        array(
                            'tglAwal' => $tglAwal,//date('Y-m-d 00:00:00'),
                            'tglAkhir' => $tglAkhir,//date('Y-m-d 23:59:59'),
                            'noregistrasi' => $request['noregistrasi'],
                        )
                    );
                    if (count($data) == 0){
                        $dataDong = DB::select(DB::raw("select rpp.objectkelasfk,
                            rpp.objectruanganfk,rpp.israwatgabung ,apd.norec as norec_apd,pd.tglregistrasi
                            from pasiendaftar_t as pd
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                            INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec and rpp.objectruanganfk=apd.objectruanganfk
                            INNER JOIN ruangan_m as ru_pd on ru_pd.id=apd.objectruanganfk
                            where pd.tglpulang is null and  ru_pd.objectdepartemenfk=16  and pd.kdprofile = $idProfile
                            and pd.noregistrasi=:noregistrasi and apd.norec=:norec_apd;"),
                            array(
                                'noregistrasi' => $request['noregistrasi'],
                                'norec_apd' => $dateAPD->norec_apd,
                            )
                        );
                        if ($dataDong[0]->israwatgabung == 1){
                            $sirahMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
                                INNER JOIN harganettoprodukbykelas_m as hett on hett.objectprodukfk=map.objectprodukfk
                                where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid and map.israwatgabung=1  and hett.kdprofile = $idProfile"),
                                array(
                                    'ruanganid' => $dataDong[0]->objectruanganfk,
                                    'kelasid' => $dataDong[0]->objectkelasfk,
                                )
                            );
                        }else{
                            $sirahMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
                                INNER JOIN harganettoprodukbykelas_m as hett on hett.objectprodukfk=map.objectprodukfk
                                where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid and map.israwatgabung is null and hett.kdprofile = $idProfile"),
                                array(
                                    'ruanganid' => $dataDong[0]->objectruanganfk,
                                    'kelasid' => $dataDong[0]->objectkelasfk,
                                )
                            );
                        }

                        // request : RSABHK-1142
                        $diskon = 0 ;
                        $tglAwalDiskon = $itemDate . ' 23:59';
                        $start  = new Carbon($dateAPD->tglregistrasi);
                        $end    = new Carbon($tglAwalDiskon);
                        $tglRegis = date('Y-m-d', strtotime($dateAPD->tglregistrasi));
                        $selisihjam = $start->diff($end)->format('%H');
                        if ($tglRegis == $itemDate){
                            if ((int)$selisihjam <= 6 ){
                                $diskon = ((float)$sirahMacan[0]->hargasatuan * 50)/100;
                            }
                        }
                        // ## END ##


                        $PelPasien = new PelayananPasien();
                        $PelPasien->norec = $PelPasien->generateNewId();
                        $PelPasien->kdprofile = $idProfile;
                        $PelPasien->statusenabled = true;
                        $PelPasien->noregistrasifk =  $dateAPD->norec_apd;//$dataDong[0]->norec_apd;
                        $PelPasien->tglregistrasi = $dataDong[0]->tglregistrasi;
                        $PelPasien->hargadiscount = $diskon;//0;
                        $PelPasien->hargajual =  $sirahMacan[0]->hargasatuan;
                        $PelPasien->hargasatuan =  $sirahMacan[0]->hargasatuan;
                        $PelPasien->jumlah = 1;
                        $PelPasien->kelasfk =  $dataDong[0]->objectkelasfk;
                        $PelPasien->kdkelompoktransaksi =  1;
                        $PelPasien->piutangpenjamin =  0;
                        $PelPasien->piutangrumahsakit = 0;
                        $PelPasien->produkfk =  $sirahMacan[0]->objectprodukfk;
                        $PelPasien->stock =  1;
                        $PelPasien->tglpelayanan = $tglAwal;// date('Y-m-d H:i:22');
                        $PelPasien->harganetto =  $sirahMacan[0]->harganetto1;

                        $PelPasien->save();
                        $PPnorec = $PelPasien->norec;

                        if ($dataDong[0]->israwatgabung == 1){
                            $buntutMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
                                INNER JOIN harganettoprodukbykelasd_m as hett on hett.objectprodukfk=map.objectprodukfk
                                where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid  and map.israwatgabung=1 and hett.kdprofile = $idProfile"),
                                array(
                                    'ruanganid' => $dataDong[0]->objectruanganfk,
                                    'kelasid' => $dataDong[0]->objectkelasfk,
                                )
                            );
                        }else{
                            $buntutMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
                                INNER JOIN harganettoprodukbykelasd_m as hett on hett.objectprodukfk=map.objectprodukfk
                                where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid  and map.israwatgabung is null and hett.kdprofile = $idProfile"),
                                array(
                                    'ruanganid' => $dataDong[0]->objectruanganfk,
                                    'kelasid' => $dataDong[0]->objectkelasfk,
                                )
                            );
                        }

                        foreach ($buntutMacan as $itemKomponen) {
                            $PelPasienDetail = new PelayananPasienDetail();
                            $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
                            $PelPasienDetail->kdprofile = $idProfile;
                            $PelPasienDetail->statusenabled = true;
                            $PelPasienDetail->noregistrasifk = $dateAPD->norec_apd;//$dataDong[0]->norec_apd;
                            $PelPasienDetail->aturanpakai = '-';
                            $PelPasienDetail->hargadiscount = $diskon;
                            $PelPasienDetail->hargajual = $itemKomponen->hargasatuan;
                            $PelPasienDetail->hargasatuan = $itemKomponen->hargasatuan;
                            $PelPasienDetail->jumlah = 1;
                            $PelPasienDetail->keteranganlain = '-';
                            $PelPasienDetail->keteranganpakai2 = '-';
                            $PelPasienDetail->komponenhargafk = $itemKomponen->objectkomponenhargafk;
                            $PelPasienDetail->pelayananpasien = $PPnorec;
                            $PelPasienDetail->piutangpenjamin = 0;
                            $PelPasienDetail->piutangrumahsakit = 0;
                            $PelPasienDetail->produkfk = $itemKomponen->objectprodukfk;
                            $PelPasienDetail->stock = 1;
                            $PelPasienDetail->tglpelayanan = $tglAwal;//date('Y-m-d H:i:22');
                            $PelPasienDetail->harganetto = $itemKomponen->harganetto1;
                            $PelPasienDetail->save();

                            $diskon =0;
                        }
                    }
                }
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Akomodasi Otomatis";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $selisihjam,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 201,
                "data" => $data2,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond($result);

    }
    public function getKamarIbuLast(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idIbu = $request['id_ibu'];
        $noCm = $request['nocm'];
        $data = DB::select(DB::raw("
                select * from 
                            (select pd.norec as norec_pd,  apd.norec as norec_apd,  ps.nocm, 
                            pd.noregistrasi,  apd.objectkamarfk,  tt.id as nobed,  kmr.namakamar, 
                            tt.reportdisplay as tempattidur,ps.namapasien,
                            kls.id as kelasfk, kls.namakelas,ru.namaruangan,pd.objectruanganlastfk,
                            row_number() over (partition by apd.objectruanganfk order by apd.tglmasuk desc) as rownum
                            from pasiendaftar_t as pd
                            inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec and pd.objectruanganlastfk=apd.objectruanganfk
                            inner join pasien_m as ps on ps.id = pd.nocmfk
                            inner join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                            inner join kelas_m as kls on kls.id = pd.objectkelasfk
                            left join kamar_m as kmr on kmr.id = apd.objectkamarfk
                            left join tempattidur_m as tt on tt.id = apd.nobed
                            where ps.qpasien ='$idIbu' 
                            and ps.nocm <> '$noCm'
                            and ps.namapasien not  ilike '%By Ny%'
                            and pd.tglpulang is null 
                            and pd.kdprofile = $idProfile
                ) as x
                where x.rownum =1
               "));

        return $this->respond($data);
    }
    public function getPasienMasihDirawat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int)$kdProfile;
        $filter = $request->all();
        $noreg = '';
        if (isset($filter['noReg']) && $filter['noReg'] != "" && $filter['noReg'] != "undefined") {
            $noreg = " AND pd.noregistrasi ilike '%" .  $filter['noReg']."%'";
        }

        $norm = '';
        if (isset($filter['noRm']) && $filter['noRm'] != "" && $filter['noRm'] != "undefined") {
            $norm = " AND p.nocm ilike '%" .  $filter['noRm']."%'";
        }
        $namaPasien = '';
        if (isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $namaPasien = " AND p.namapasien ilike '%" .  $filter['namaPasien']."%'";
        }
        $ruId='';
        if(isset($filter['ruanganId']) && $filter['ruanganId'] != "" && $filter['ruanganId'] != "undefined") {
            $ruId = ' AND ru.id = ' . $filter['ruanganId'];
        }
        $jmlRow='';
        if(isset($filter['jmlRow']) && $filter['jmlRow'] != "" && $filter['jmlRow'] != "undefined") {
            $jmlRow = ' limit ' . $filter['jmlRow'];
        }
        $data=  DB::select(DB::raw("
        select  * from 
			                (
			select pd.tglregistrasi,
				p.nocm,
				pd.noregistrasi,
				ru.namaruangan,
				p.namapasien,
                kp.kelompokpasien,
                case when p.nobpjs is null then '-' else p.nobpjs end as nobpjs,
				kls.namakelas,
				alm.alamatlengkap,
				jk.jeniskelamin,
				pg.namalengkap AS namadokter,
				pd.norec AS norec_pd,
				pd.tglpulang,
				pd.statuspasien,
				p.tgllahir,
				pd.objectruanganlastfk,
				pd.objectkelasfk, apd.objectkamarfk,
				kmr.namakamar,apd.nobed,
				tt.reportdisplay as namabed,
				EXTRACT(day from age(current_date, to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))) || ' Hari' as lamarawat,
				--,
			row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
			FROM
				pasiendaftar_t AS pd
			INNER JOIN antrianpasiendiperiksa_t AS apd ON pd.norec = apd.noregistrasifk and apd.tglkeluar is null
			--inner join registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec
			INNER JOIN pasien_m AS p ON p.id = pd.nocmfk
			INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
			INNER JOIN kelas_m AS kls ON kls.id = pd.objectkelasfk
			INNER JOIN jeniskelamin_m AS jk ON jk.id = p.objectjeniskelaminfk
			LEFT JOIN pegawai_m AS pg ON pg.id = pd.objectpegawaifk
			LEFT JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
			LEFT JOIN departemen_m AS dept ON dept.id = ru.objectdepartemenfk
			LEFT JOIN batalregistrasi_t AS br ON pd.norec = br.pasiendaftarfk
			left JOIN kamar_m as kmr on kmr.id =apd.objectkamarfk
			left JOIN tempattidur_m as tt on tt.id =apd.nobed
			left join alamat_m as alm on alm.nocmfk = p.id
			where br.norec is null 
			$noreg $norm $namaPasien $ruId
			and pd.tglpulang is null
			and pd.kdprofile = $idProfile	
			and pd.statusenabled=true
            $jmlRow
			) as x 
			where x.rownum=1
			order by x.tglregistrasi desc
		"));
        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getComboPasienMasihDirawat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $ruanganRi = \DB::table('ruangan_m as ru')
            ->whereIn('ru.objectdepartemenfk',[16,17,25,35])
//            ->wherein('ru.objectdepartemenfk', ['18', '28'])
            ->where('ru.statusenabled',true)
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();

        $kdPelayananRanap = \DB::table('settingdatafixed_m as p')
            ->select('p.nilaifield')
            ->where('p.statusenabled', true)
            ->where('p.namafield','kddeptlayananRI')
            ->first();

        $kdPelayananOk = \DB::table('settingdatafixed_m as p')
            ->select('p.nilaifield')
            ->where('p.statusenabled', true)
            ->where('p.namafield','KdPelayananOk')
            ->first();

        $dataKelompokTanpaUmum = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->where('kp.id', '<>', 1)
            ->orderBy('kp.kelompokpasien')
            ->get();
        $jenisDiagnosa = \DB::table('jenisdiagnosa_m as jd')
            ->select('jd.id', 'jd.jenisdiagnosa')
//            ->where('jd.id',5)
            ->where('jd.statusenabled', true)
            ->orderBy('jd.jenisdiagnosa')
            ->get();
        $result = array(
            'ruanganRi' => $ruanganRi,
            'kelompokpasiensatu' => $dataKelompokTanpaUmum,
            'kddeptlayananranap' => $kdPelayananRanap,
            'kddeptlayananok' => $kdPelayananOk,
            'jenisdiagnosa' =>$jenisDiagnosa,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDetailAntrianPasienDiperiksa(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $noreg = '';
        if (isset($filter['noreg']) && $filter['noreg'] != "" && $filter['noreg'] != "undefined") {
            $noreg = " AND pd.noregistrasi = '" .  $filter['noreg']."'";
        }
        $ruangId = '';
        if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
            $ruangId = ' AND apd.objectruanganfk = ' . $filter['ruangId'];
        }
        $namaRuangan = '';
        if (isset($filter['namaRuangan']) && $filter['namaRuangan'] != "" && $filter['namaRuangan'] != "undefined") {
            $ruangId = " AND ru.namaruangan ilike '%"  . $filter['namaRuangan']."%'";
        }
        $data = DB::select(DB::raw("select * from
                (select pd.tglregistrasi,pd.noregistrasi, ru.namaruangan,
                 pd.norec as norec_pd, apd.tglmasuk, apd.norec as norec_apd, 
                 row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
                 from antrianpasiendiperiksa_t as apd
                 inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk and pd.objectruanganlastfk = apd.objectruanganfk
                 left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                 inner join ruangan_m as ru on ru.id = apd.objectruanganfk
                 where br.norec is null 
                and pd.tglpulang is null --and pd.noregistrasi='1808010084'
                and pd.kdprofile = $idProfile 
                $ruangId $noreg  $namaRuangan
              ) as x where x.rownum=1")
        );
        return $this->respond($data);
    }

    public function saveBatalRanap(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $deptRanap = explode (',',$this->settingDataFixed('kdDepartemenRanapFix',$kdProfile));
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap){
            $kdDepartemenRawatInap []=  (int)$itemRanap;
        }
        $norecPd = $request['data']['norec_pd'];
        $apd = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->leftjoin('diagnosapasien_t as dg','dg.noregistrasifk','=','apd.norec')
            ->select('ru.objectdepartemenfk','apd.norec','apd.objectruanganfk','dg.norec as norec_diagnosa')
            ->where('apd.noregistrasifk',$norecPd)
//                ->where('apd.objectruanganfk', $request['data']['objectruanganlastfk'])
//                ->wherenull('apd.tglkeluar')
            ->whereIn('ru.objectdepartemenfk',$kdDepartemenRawatInap)
            ->get();
        $ruanganAsal = \DB::table('registrasipelayananpasien_t as rpp')
            ->join('ruangan_m as ru','ru.id','=','rpp.objectruanganfk')
            ->select('ru.objectdepartemenfk','rpp.norec as norec_rpp','rpp.objectruanganfk','rpp.objectruanganasalfk','rpp.keteranganlainnyarencana')
            ->where('rpp.noregistrasifk',$norecPd)
            ->where('rpp.objectruanganfk', $request['data']['objectruanganlastfk'])
            ->whereNotIn('ru.objectdepartemenfk',[27,3])
            ->where('rpp.keteranganlainnyarencana','Mutasi Gawat Darurat')
            ->first();
        if(count($apd) > 1){
            $transMessage = 'Tidak Bisa Batal Rawat Inap, pasien sudah mendapatkan lebih dari 1 ruangan Rawat Inap !';
            return $this->setStatusCode(400)->respond([], $transMessage);
        }
//            return $this->respond($apd);
        foreach ($apd as $item){
            if($item->objectdepartemenfk == 16){
                $pelayanan = PelayananPasien::where('noregistrasifk',$item->norec)->first();
                if(!empty($pelayanan)){
                    $transMessage = 'Pasien sudah mendapatkan pelayanan, hapus pelayanan dulu !';
                    $pel = array('norec_pp' => $pelayanan->norec);
                    return $this->setStatusCode(400)->respond($pel, $transMessage);
                }
                if($item->norec_diagnosa != null){
                    $transMessage = 'Pasien sudah mendapatkan Diagnosis, hapus Diagnosis dulu !';
                    return $this->setStatusCode(400)->respond([], $transMessage);
                }
                DB::beginTransaction();
                try {
                    if(!empty($ruanganAsal)){
                        $updatePD = PasienDaftar::where('norec', $norecPd)
                            ->update([
                                    'objectruanganlastfk' => $ruanganAsal->objectruanganasalfk,
                                    'tglpulang' => $request['data']['tglregistrasi'],
                                    'objectkelasfk' => 6,
                                ]
                            );

                    }else{
                        $updatePD = PasienDaftar::where('norec', $norecPd)
                            ->update([
                                    'statusenabled'=>false,
                                    'tglpulang' => $request['data']['tglregistrasi']
                                ]
                            );
                    }
                    $delRPP = RegistrasiPelayananPasien::where('noregistrasifk', $norecPd)
                        ->where('objectruanganfk', $request['data']['objectruanganlastfk'])
                        ->delete();
                    //                    $delAPD = AntrianPasienDiperiksa::where('noregistrasifk', $norecPd)
                    //                        ->where('objectruanganfk',$item->objectruanganfk)
                    //    
                    $delemr = RekamMedis::where('noregistrasifk',$item->norec)
                        ->delete();
                    
                    $delanm = Anamnesis::where('noregistrasifk',$item->norec)
                        ->where('ruanganfk', $request['data']['objectruanganlastfk'])
                        ->delete();
                    
                    $delpga = PengkajianAwalBaru::where('objectnoregistrasifk',$item->norec)
                            ->where('objectruanganfk', $request['data']['objectruanganlastfk'])
                            ->delete();         

                    $delAPD = AntrianPasienDiperiksa::where('norec', $item->norec)
//                            ->where('objectruanganfk',$item->objectruanganfk)
                        ->delete();
                    

                    if(isset($request['data']['nobed']) && $request['data']['nobed'] !='null'  ){
                        //update statusbed jadi Kosong
                        TempatTidur::where('id',$request['data']['nobed'])->update(['objectstatusbedfk'=>2]);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                DB::commit();
            }
        }


        ## Logging User
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= 11;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Batal Rawat Inap';
        $logUser->noreff = $norecPd;
        $logUser->referensi='norec pasiendaftar';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $tglAyeuna;
        $logUser->keterangan = 'Batal Rawat Inap dengan No Registrasi ' . $request['data']['noregistrasi'].' dari ruangan '.$request['data']['namaruangan'];
        $logUser->save();

//
        $transStatus = 'true';
        $transMessage = "Batal Rawat Inap Sukses";
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "Batal Rawat Inap gagal";
//        }

        if ($transStatus != 'false') {
//            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
//            DB::rollBack();
            $result = array(
                "status" => 400,
                // "resep" => $resep,
                "message" => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveBatalPindahRuangan(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        try {
            $norecPd = $request['norec_pd'];
            $apd = \DB::table('antrianpasiendiperiksa_t as apd')
                ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
                ->select('ru.objectdepartemenfk','apd.norec','apd.objectruanganfk','apd.objectruanganasalfk','apd.objectkelasfk','apd.nobed','apd.objectkamarfk')
                ->where('apd.noregistrasifk',$norecPd)
                ->where('apd.kdprofile', (int)$kdProfile)
                ->get();
            $rpp = RegistrasiPelayananPasien::where('noregistrasifk',$norecPd)
                ->where('objectruanganfk',$request['objectruanganlastfk'])
                ->where('kdprofile', (int)$kdProfile)
                ->first();

            $ruanganAsal = \DB::table('registrasipelayananpasien_t as rpp')
                ->join('ruangan_m as ru','ru.id','=','rpp.objectruanganfk')
                ->select('ru.objectdepartemenfk','rpp.norec as norec_rpp','rpp.objectruanganfk','rpp.objectruanganasalfk','rpp.objectkelasfk',
                    'rpp.objectkamarfk','rpp.objecttempattidurfk')
                ->where('rpp.noregistrasifk',$norecPd)
                ->where('rpp.objectruanganfk',$rpp->objectruanganasalfk)
                ->whereNotIn('ru.objectdepartemenfk',[27,3])
                ->where('rpp.kdprofile', (int)$kdProfile)
                ->orderBy('rpp.tglpindah','desc')
                ->first();


            foreach ($apd as $item){
                if($item->objectdepartemenfk == 16 && $request['objectruanganlastfk'] == $item->objectruanganfk  ) {
                    $pelayanan = PelayananPasien::where('noregistrasifk', $item->norec)->where('kdprofile', (int)$kdProfile)->first();
                    if (!empty($pelayanan)) {
                        $transMessage = 'Pasien sudah mendapatkan pelayanan, hapus pelayanan dulu !';
                        $pel = array('norec_pp' => $pelayanan->norec);
                        return $this->setStatusCode(400)->respond($pel, $transMessage);
                    }else{
                        $updatePD = PasienDaftar::where('norec', $norecPd)->where('kdprofile', (int)$kdProfile)
                            ->update([
                                    'objectruanganlastfk' => $ruanganAsal->objectruanganfk,
                                    'objectkelasfk' => $ruanganAsal->objectkelasfk,
                                ]
                            );

                        $rpp = RegistrasiPelayananPasien::where('noregistrasifk',$norecPd)
                            ->where('objectruanganfk',$request['objectruanganlastfk'])
                            ->whereNull('tglpindah')
                            ->where('kdprofile', (int)$kdProfile)
                            ->delete();
                        $delAPD = AntrianPasienDiperiksa::where('noregistrasifk', $norecPd)
                            ->where('objectruanganfk',$request['objectruanganlastfk'])
                            ->whereNull('tglkeluar')
                            ->where('kdprofile', (int)$kdProfile)
                            ->delete();
                        $updateAPDs = AntrianPasienDiperiksa::where('noregistrasifk', $norecPd)
                            ->where('objectruanganfk',$ruanganAsal->objectruanganfk)
                            ->wherenotnull('tglkeluar')
                            ->where('kdprofile', (int)$kdProfile)
                            ->update(
                                [ 'tglkeluar' => null]
                            );
                        $updateRpp = RegistrasiPelayananPasien::where('noregistrasifk', $norecPd)
                            ->where('objectruanganfk',$ruanganAsal->objectruanganfk)
                            ->wherenotnull('tglpindah')
                            ->where('kdprofile', (int)$kdProfile)
                            ->update(
                                [ 'tglpindah' => null]
                            );
                        if(isset($request['nobed']) && $request['nobed'] !='null'  ){
                            //update statusbed jadi Kosong
                            TempatTidur::where('id',$request['nobed'])->where('kdprofile', (int)$kdProfile)->update(['objectstatusbedfk'=>2]);
                        }
                        if( $ruanganAsal->objecttempattidurfk !='null'  ){
                            //update statusbed jadi Kosong
                            TempatTidur::where('id', $ruanganAsal->objecttempattidurfk)->where('kdprofile', (int)$kdProfile)->update(['objectstatusbedfk'=>1]);
                        }
                        break;
                    }
                }
            }

            ## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= (int)$kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Pindah Ruangan';
            $logUser->noreff = $norecPd;
            $logUser->referensi='norec pasiendaftar';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = 'Batal Pindah Ruangan dengan No Registrasi ' . $request['noregistrasi'].' dari ruangan '.$request['namaruangan'];
            $logUser->save();

////
            $transStatus = 'true';
            $transMessage = "Batal Pindah Ruangan Sukses";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Tidak bisa Batal Pindah Ruangan";
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
                // "resep" => $resep,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function updateKamar(Request $request){
        DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        try {
            $antrians = AntrianPasienDiperiksa::where('noregistrasifk',$request['norec_pd'])
                ->where('objectruanganfk',$request['ruanganlastfk'])
                ->whereNull('tglkeluar')
                ->where('kdprofile', (int)$kdProfile)
                ->first();
            $rpp = RegistrasiPelayananPasien::where('noregistrasifk',$request['norec_pd'])
                ->where('objectruanganfk',$request['ruanganlastfk'])
                ->whereNull('tglkeluar')
                ->where('kdprofile', (int)$kdProfile)
                ->first();

//			if(!empty($antrians)){
            AntrianPasienDiperiksa::where('norec',$antrians->norec)->where('kdprofile', (int)$kdProfile)
                ->update(
                    [
                        'objectkamarfk' => $request['objectkamarfk'] ,
                        'nobed' => $request['nobed'] ,
                    ]
                );

//			}
//			if(!empty($rpp)){
            RegistrasiPelayananPasien::where('norec',$rpp->norec)->where('kdprofile', (int)$kdProfile)
                ->update(
                    [
                        'objectkamarfk' => $request['objectkamarfk'] ,
                        'nobed' => $request['nobed'] ,
                    ]
                );
//			}


            //update statusbed jadi Isi
            TempatTidur::where('id',$request['nobed'])->where('kdprofile', (int)$kdProfile)->update(['objectstatusbedfk'=>1]);

            if(isset($request['nobedasal']) && $request['nobedasal'] !='null' && $request['nobedasal'] != $request['nobed'] ){
                //update statusbed jadi Kosong
                TempatTidur::where('id',$request['nobedasal'])->where('kdprofile', (int)$kdProfile)->update(['objectstatusbedfk'=>2]);
            }

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Update Kamar Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Update Kamar Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarRencanaPindahPasien(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->where('mlu.kdprofile', (int)$kdProfile)
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $data = \DB::table('strukorder_t as so')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->JOIN('registrasipelayananpasien_t as rpp','rpp.strukorderfk','=','so.norec')
            ->JOIN('pasien_m as pas','pas.id','=','so.nocmfk')
            ->JOIN('pasiendaftar_t as pd','pd.norec','=','so.noregistrasifk')
            ->JOIN('antrianpasiendiperiksa_t as apd',function ($join){
                $join->on('apd.noregistrasifk','=','pd.norec');
                $join->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
            })
            ->JOIN('kelas_m as kls','kls.id','=','pd.objectkelasfk')
            ->JOIN('ruangan_m as ru1','ru1.id','=','so.objectruangantujuanfk')
            ->JOIN('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->JOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->select(DB::raw("so.norec as norec_so,pas.nocm,pd.noregistrasi,pas.namapasien,kls.namakelas,ru.namaruangan,
                     ru1.namaruangan as ruanganrencana,so.tglrencana,kp.kelompokpasien,
                     so.statusorder,pd.norec as norec_pd,apd.norec as norec_apd"))
            ->where('so.kdprofile', (int)$kdProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('so.tglrencana','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('so.tglrencana','<=', $tgl);
        }
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike','%'. $request['noReg']);
        }
        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="undefined"){
            $data = $data->where('pas.nocm','ilike','%'. $request['noCm']);
        }
        $data = $data->where('so.statusenabled',true);
        $data = $data->where('so.objectkelompoktransaksifk',122);
        $data = $data->orderBy('so.tglrencana');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'datalogin' => $dataLogin,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataPasienPindah(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->where('mlu.kdprofile', $idProfile)
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $dataAwal = DB::select(DB::raw("select pd.objectruanganlastfk,ru.namaruangan,rpp.objectkamarfk,km.namakamar,
		            rpp.objecttempattidurfk,tt.reportdisplay as nomorbed,rpp.objectkelasfk,kls.namakelas
                    from pasiendaftar_t as pd
                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec and pd.objectruanganlastfk = rpp.objectruanganfk
                    INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                    INNER JOIN kamar_m as km on km.id = rpp.objectkamarfk
                    INNER JOIN tempattidur_m as tt on tt.id = rpp.objecttempattidurfk
                    INNER JOIN kelas_m as kls on kls.id = rpp.objectkelasfk
                    where pd.norec=:norec and pd.kdprofile = $idProfile"),
            array(
                'norec' => $request['norec_pd'],
            )
        );

        $dataRencana = DB::select(DB::raw("select rpp.objectstatuskeluarrencanafk,sk.statuskeluar,op.objectruangantujuanfk,ru.namaruangan,op.israwatgabung,
                                     rpp.objectkelaskamarrencanafk,kls.namakelas,rpp.objectkamarrencanafk,km.namakamar,
                                     tt.id as nobedrencana,tt.reportdisplay as nomorbed,so.keteranganorder,so.tglrencana	
                        from strukorder_t as so
                        INNER JOIN orderpelayanan_t as op on op.noorderfk = so.norec
                        INNER JOIN registrasipelayananpasien_t as rpp on rpp.strukorderfk = so.norec
                        INNER JOIN statuskeluar_m as sk on sk.id = rpp.objectstatuskeluarrencanafk
                        INNER JOIN ruangan_m as ru on ru.id = op.objectruangantujuanfk
                        INNER JOIN kelas_m as kls on kls.id = rpp.objectkelaskamarrencanafk
                        INNER JOIN kamar_m as km on km.id = rpp.objectkamarrencanafk
                        INNER JOIN tempattidur_m as tt on tt.id = rpp.nobedrencana
                        where so.norec=:norec and pd.kdprofile = $idProfile"),
            array(
                'norec' => $request['norec_so'],
            )
        );

        $result = array(
            'dataawal' => $dataAwal,
            'datarencana' => $dataRencana,
            'datalogin' => $dataLogin,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getComboPasienJatuh(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', (int)$kdProfile)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();


        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.objectjenispegawaifk', 1)
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namalengkap')
            ->get();
        foreach ($dataInstalasi as $item) {
            $detail = [];
            foreach ($dataRuangan as $item2) {
                if ($item->id == $item2->objectdepartemenfk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'ruangan' => $item2->namaruangan,
                    );
                }
            }

            $dataDepartemen[] = array(
                'id' => $item->id,
                'departemen' => $item->namadepartemen,
                'ruangan' => $detail,
            );
        }
        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();


        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,

            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getIndikatorPasienJatuh(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $data = DB::table('indikatorpasienjatuh_t as in')
            ->join('pasiendaftar_t as pd','pd.norec','=','in.noregistrasifk')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->select('in.*','ps.nocm','pd.tglregistrasi','pd.noregistrasi','ps.namapasien','ru.namaruangan',
                'pd.norec as norec_pd')
            ->where('in.statusenabled',1)
            ->where('in.kdprofile', (int)$kdProfile);
        if(isset($request['tglAwal'] ) && $request['tglAwal']!= ''){
            $data = $data->where('in.tgljatuh','>=' ,$request['tglAwal'] );
        }
        if(isset($request['tglAkhir'] ) && $request['tglAkhir']!= ''){
            $data = $data->where('in.tgljatuh','<=' ,$request['tglAkhir'] );
        }
        if(isset($request['noreg'] ) && $request['noreg']!= ''){
            $data = $data->where('pd.noregistrasi',$request['noreg'] );
        }
        if(isset($request['norm'] ) && $request['norm']!= ''){
            $data = $data->where('ps.nocm','ilike','%'.$request['norm'].'%' );
        }
        if(isset($request['nama'] ) && $request['nama']!= ''){
            $data = $data->where('ps.namapasien','ilike','%'.$request['nama'].'%' );
        }
        if(isset($request['deptId'] ) && $request['deptId']!= ''){
            $data = $data->where('ru.objectdepartemenfk',$request['deptId'] );
        }
        if(isset($request['ruangId'] ) && $request['ruangId']!= ''){
            $data = $data->where('ru.id',$request['ruangId'] );
        }
        if(isset($request['ket'] ) && $request['ket']!= ''){
            $data = $data->where('in.keterangan','ilike','%'.$request['ket'].'%' );
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'as' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function getDataComboBoxGizi(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataProdukResult=[];
        $dataRuangGizi = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.id',54)
            ->where('ru.statusenabled',true)
            ->where('ru.kdprofile', $kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();
        $ruanganInap = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', [16,35,17])
            ->where('ru.kdprofile', $kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataJenisDiet = \DB::table('jenisdiet_m as jd')
            ->select('jd.id','jd.jenisdiet')
            ->where('jd.statusenabled',true)
            ->get();

        $dataJenisWaktu = \DB::table('jeniswaktu_m as jw')
            ->select('jw.id','jw.jeniswaktu')
            ->where('jw.statusenabled',true)
            ->get();

        $dataKategoryDiet = \DB::table('kategorydiet_m as kd')
            ->select('kd.id','kd.kategorydiet')
            ->where('kd.statusenabled',true)
            ->get();

        $dataKelas = \DB::table('kelas_m as kls')
            ->select('kls.id','kls.namakelas')
            ->where('kls.statusenabled',true)
            ->get();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->JOIN('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
//            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.statusenabled',true)
            ->where('kp.id',(int)$this->settingDataFixed('kdKelasNonKelasRegistrasi',$kdProfile))
            ->where('pr.kdprofile', $kdProfile)
//            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2', 'ks.nilaikonversi')
            ->where('ks.kdprofile', $kdProfile)
            ->where('ks.statusenabled',true)
            ->get();

        foreach ($dataProduk as $item){
            $satuanKonversi=[];
            foreach ($dataKonversiProduk  as $item2){
                if ($item->id == $item2->objekprodukfk){
                    $satuanKonversi[] =array(
                        'ssid' =>   $item2->satuanstandar_tujuan,
                        'satuanstandar' =>   $item2->satuanstandar2,
                        'nilaikonversi' =>   $item2->nilaikonversi,
                    );
                }
            }

            $dataProdukResult[]=array(
                'id' =>   $item->id,
                'namaproduk' =>   $item->namaproduk,
                'ssid' =>   $item->ssid,
                'satuanstandar' =>   $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
            );
        }
        $dataSatuan = \DB::table('satuanstandar_m as kls')
            ->select('kls.id','kls.satuanstandar')
            ->where('kls.statusenabled',true)
            ->get();


        $result = array(
            'ruangGizi' => $dataRuangGizi,
            'ruanginap' => $ruanganInap,
            'kelas' => $dataKelas,
            'jenisdiet' => $dataJenisDiet,
            'jeniswaktu' => $dataJenisWaktu,
            'kategorydiet' => $dataKategoryDiet,
            'produkkonversi' => $dataProdukResult,
            'produk' => $dataProduk,
            'satuanstandar' => $dataSatuan,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function getPasienDirawatGizi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('orderpelayanan_t as so','so.noregistrasifk','=',
                DB::raw("pd.norec and so.keteranganlainnya_quo = 'Order Gizi'"))
            ->select('pd.tglregistrasi', 'pd.nocmfk','p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
                'kls.namakelas','jk.jeniskelamin','pg.namalengkap as namadokter','pd.norec as norec_pd',
                'so.noregistrasifk as norecorder','so.keteranganlainnya_quo',
                'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','pd.objectkelasfk')
            ->where('pd.kdprofile', (int)$kdProfile)
            ->where('pd.statusenabled', true)
            ->whereNull('pd.tglpulang');

        $filter = $request->all();
//        if(isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
//            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
//        }
//
//        if(isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
//            $tgl = $filter['tglAkhir'] ;//. " 23:59:59";
//            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
//        }

        if(isset($filter['instalasiId']) && $filter['instalasiId'] != "" && $filter['instalasiId'] != "undefined") {
            $data = $data->where('dept.id', '=', $filter['instalasiId']);
        }

        if(isset($filter['ruanganId']) && $filter['ruanganId'] != "" && $filter['ruanganId'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['ruanganId']);
        }

        if(isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('p.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }

        if(isset($filter['noReg']) && $filter['noReg'] != "" && $filter['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noReg'] . '%');
        }
        if(isset($filter['noRm']) && $filter['noRm'] != "" && $filter['noRm'] != "undefined") {
            $data = $data->where('p.nocm', 'ilike', '%' . $filter['noRm'] . '%');
        }
        if(isset($filter['kelasId']) && $filter['kelasId'] != "" && $filter['kelasId'] != "undefined") {
            $data = $data->where('pd.objectkelasfk', '=',  $filter['kelasId']);
        }

        $data = $data->groupBy('pd.tglregistrasi', 'pd.nocmfk','p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
            'kls.namakelas','jk.jeniskelamin','pg.namalengkap','pd.norec',
            'so.noregistrasifk','so.keteranganlainnya_quo',
            'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','pd.objectkelasfk');
        $data = $data->orderBy('pd.tglregistrasi','desc');
//        $data = $data->take(50);
        $data = $data->get();
//        $res =[];
//        foreach ( $data as $item){
//            $statusOrder = '-';
//            $strukorder = StrukOrder::where('noregistrasifk', $item->norec_pd)->get();
//            if (count($strukorder) >0){
//                $statusOrder = 'Sudah Order';
//            }
//            $res[] = array(
//                'tglregistrasi' => $item->tglregistrasi,
//                'nocmfk' => $item->nocmfk,
//                'nocm' => $item->nocm,
//                'noregistrasi' => $item->noregistrasi,
//                'namaruangan' => $item->namaruangan,
//                'namapasien' => $item->namapasien,
//                'kelompokpasien' => $item->kelompokpasien,
//                'namakelas' => $item->namakelas,
//                'jeniskelamin' => $item->jeniskelamin,
//                'namadokter' => $item->namadokter,
//                'norec_pd' => $item->norec_pd,
//                'tglpulang' => $item->tglpulang,
//                'statuspasien' => $item->statuspasien,
//                'tgllahir' => $item->tgllahir,
//                'objectruanganlastfk' => $item->objectruanganlastfk,
//                'objectkelasfk' => $item->objectkelasfk,
//                'statusorder' => $statusOrder,
//            );
//        }
        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function saveEdukasiIpcln(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $tglAyeuna = date('Y-m-d H:i:s');
        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item) {
                if( !is_array( $item['tgl'])){
                    if ($request['id'] == '') {
                    $id = EdukasiIpcln::max('id');
                    $dataJadwal = new EdukasiIpcln();
                    $dataJadwal->id = $id + 1;
                    $dataJadwal->norec = $dataJadwal->generateNewId();
                    $dataJadwal->kdprofile = $kdProfile;
                    $dataJadwal->statusenabled = true;

                } else {
                    $dataJadwal = EdukasiIpcln::where('id', $request['id'])->first();
                }

                $dataJadwal->objectpegawaifk = $request['pegawaifk'];
                $dataJadwal->objectruanganfk = $request['ruanganfk'];
                $dataJadwal->jeniskegiatan = $item['jeniskegiatan'];
                $dataJadwal->tglinput = $tglAyeuna;
                $dataJadwal->tgl = $item['tgl'];
                if(isset($item['isi'])){
                    $dataJadwal->isi = $item['isi'];
                }

//                $dataJadwal->objectstatushadirfk = 1;//hadir $request['objectstatushadirfk'];
                $dataJadwal->save();
                }
                
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Jadwal Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "res" => $dataJadwal,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Jadwal Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataIPCLN(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('edukasiipcln_t as ei')
            ->join('pegawai_m as pg','pg.id','=','ei.objectpegawaifk')
            ->join('ruangan_m as ru','ru.id','=','ei.objectruanganfk')
            ->select(DB::raw("ei.*,ru.namaruangan,pg.namalengkap as petugas"))
            ->where('ei.kdprofile', (int)$kdProfile)
            ->where('ei.statusenabled', true);
//            ->orderByRaw('pg.namalengkap,pjk.tgljadwal desc');

        if(isset($request['bulan']) &&
            $request['bulan']!="" &&
            $request['bulan']!="undefined"){
            $tgl = $request['bulan']  ;
            $data = $data->whereRaw("to_char( ei.tglinput,'mm.yyyy')  ='$tgl' " );
        };
        if(isset($request['namalengkap']) &&
            $request['namalengkap']!="" &&
            $request['namalengkap']!="undefined"){
            $data = $data->where('ei.objectpegawaifk','=',$request['namalengkap']);
        };
        if(isset($request['idRuangan']) &&
            $request['idRuangan']!="" &&
            $request['idRuangan']!="undefined"){
            $data = $data->where('ru.id','=', $request['idRuangan'] );
        };

        $data = $data->get();
        return $this->respond($data);
    }
    public function hapusJadwalBulananPegawai(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item) {

                $dataJadwal = PegawaiJadwalKerja::where('id', $item['id'])->where('kdprofile', (int)$kdProfile)->update(
                    [ 'statusenabled' => false ]
                );

            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Jadwal Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "res" => $dataJadwal,
                "as" => 'ramdanegie@epic',
            );
        } else {
            $transMessage = "Hapus Jadwal Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ramdanegie@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataComboSurv(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id',[3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35])
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', (int)$kdProfile)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();


        foreach ($dataInstalasi as $item) {
            $detail = [];
            foreach ($dataRuangan as $item2) {
                if ($item->id == $item2->objectdepartemenfk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'ruangan' => $item2->namaruangan,
                    );
                }
            }

            $dataDepartemen[] = array(
                'id' => $item->id,
                'departemen' => $item->namadepartemen,
                'ruangan' => $detail,
            );
        }
        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();


        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'message' => 'as@',
        );

        return $this->respond($result);
    }
    public function getDataSurveilans(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('surveilans_t as sv')
            ->join('pasiendaftar_t as pd','pd.norec','=','sv.noregistrasifk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec','=','sv.norec_apd')
            ->join('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','pm.objectjeniskelaminfk')
            ->leftJoin('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->select(DB::raw("sv.norec,sv.tglsurveilans,sv.nosurvailens,pd.tglregistrasi,pd.noregistrasi,
		                      pm.nocm,pm.namapasien,jk.reportdisplay as jk,ru.namaruangan,ru.objectdepartemenfk,
		                      kp.kelompokpasien,pm.tgllahir"))
            ->whereNull('br.norec')
            ->where('sv.kdprofile', (int)$kdProfile)
            ->where('sv.statusenabled',true);

        $filter = $request->all();
        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('sv.tglsurveilans', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir'];//." 23:59:59";
            $data = $data->where('sv.tglsurveilans', '<=', $tgl);
        }
        if (isset($filter['idDept']) && $filter['idDept'] != "" && $filter['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', '=', $filter['idDept']);
        }
        if (isset($filter['idRuangan']) && $filter['idRuangan'] != "" && $filter['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['idRuangan']);
        }
        if (isset($filter['kelompokPasien']) && $filter['kelompokPasien'] != "" && $filter['kelompokPasien'] != "undefined") {
            $data = $data->where('pd.objectkelompokpasienlastfk', '=', $filter['kelompokPasien']);
        }
        if(isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('pm.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }
        if(isset($filter['noRegis']) && $filter['noRegis'] != "" && $filter['noRegis'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noRegis'] . '%');
        }
        if(isset($filter['noCm']) && $filter['noCm'] != "" && $filter['noCm'] != "undefined") {
            $data = $data->where('pm.nocm', 'ilike', '%' . $filter['noCm'] . '%');
        }
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataHarianSurveilans(Request $request){
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $idDept = $request['idDept'];
        $idRuangan = $request['idRuangan'];
        $kelompokPasien = $request['kelompokPasien'];
        $namaPasien = $request['namaPasien'];
        $noRegis = $request['noRegis'];
        $noCm = $request['noCm'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $paramDept = ' ';
        if (isset($idDept) && $idDept != "" && $idDept != "undefined") {
            $paramDept = ' and ru.objectdepartemenfk  = ' . $idDept;
        }

        $paramRuangan = ' ';
        if (isset($idRuangan) && $idRuangan != "" && $idRuangan != "undefined") {
            $paramRuangan = ' and apd.objectruanganfk = ' . $idRuangan;
        }


        $paramKelompokPasien = '';
        if (isset($kelompokPasien) && $kelompokPasien != "" && $kelompokPasien != "undefined"){
            $paramKelompokPasien = 'and pd.objectkelompokpasienlastfk = ' . $kelompokPasien;
        }

        $paramNamaPasien = '';
        if (isset($namaPasien) && $namaPasien != "" && $namaPasien != "undefined"){
            $paramNamaPasien = " and pm.namapasien iLIKE ". "'%" . $namaPasien . "%'";
        }

        $paramNoregistrasi = '';
        if (isset($noRegis) && $noRegis != "" && $noRegis != "undefined"){
            $paramNoregistrasi = 'and pd.noregistrasi = ' . $noRegis;
        }

        $paramNoRm = '';
        if (isset($noCm) && $noCm != "" && $noCm != "undefined"){
            $paramNoRm = " and pm.nocm iLIKE ". "'%" . $noCm . "%'";
        }

        $data =  DB::select(DB::raw("SELECT x.norec,x.tglsurveilans,x.tglregistrasi,x.nocm,x.namapasien,x.noregistrasi,x.tgllahir,x.jk,
                                           x.namadiagnosa,x.hasilkultur,x.namaproduk,
                                           sum(x.ett) as ett,
                                           sum(x.cvl) as cvl,
                                           sum(x.ivl) as ivl,
                                           sum(x.uc) as uc,
                                           -- CONVERT(VARCHAR, SUM(x.ett)) + ' hr' as ett,
                                           -- CONVERT(VARCHAR, SUM(x.cvl)) + ' hr' as cvl,
                                           -- CONVERT(VARCHAR, SUM(x.ivl)) + ' hr' as ivl,
                                           -- CONVERT(VARCHAR, SUM(x.uc)) + ' hr' as uc,
                                          x.PHLEBITIS,x.DIARE,x.ISK,x.SKABIES
                             FROM                                 
                             (SELECT sv.norec,sv.tglsurveilans,pd.tglregistrasi,pm.nocm,pm.namapasien,pd.noregistrasi,pm.tgllahir,jk.reportdisplay AS jk,
                                     CASE WHEN sv.diagnosamasukfk IS NOT NULL THEN dg.kddiagnosa || ', ' || dg.namadiagnosa WHEN sv.keterangandiagnosamasuk IS NOT NULL THEN
                                     '-, ' || sv.keterangandiagnosamasuk ELSE '-' END AS namadiagnosa,fr.hasilkultur,pro.namaproduk,
                                     CASE WHEN fr.tindakanoperasifk = 1 THEN EXTRACT(day from age(fr.tglmulai, case when fr.tglakhir is not null then fr.tglakhir else case when pd.tglpulang is not null then pd.tglpulang else now() end end)) else 0 END AS ett,
                                    CASE WHEN fr.tindakanoperasifk = 2 THEN EXTRACT(day from age(fr.tglmulai, case when fr.tglakhir is not null then fr.tglakhir else case when pd.tglpulang is not null then pd.tglpulang else now() end end)) else 0 END AS cvl,
                                    CASE WHEN fr.tindakanoperasifk = 3 THEN EXTRACT(day from age(fr.tglmulai, case when fr.tglakhir is not null then fr.tglakhir else case when pd.tglpulang is not null then pd.tglpulang else now() end end)) else 0 END AS ivl,
                                    CASE WHEN fr.tindakanoperasifk = 4 THEN EXTRACT(day from age(fr.tglmulai, case when fr.tglakhir is not null then fr.tglakhir else case when pd.tglpulang is not null then pd.tglpulang else now() end end)) else 0 END AS uc,
                                     CASE WHEN fr.infeksifk = 6 THEN fr.tglinfeksi ELSE NULL END AS PHLEBITIS,
                                     CASE WHEN fr.infeksifk = 17 THEN fr.tglinfeksi ELSE 	NULL END AS DIARE,
                                     CASE WHEN fr.infeksifk = 1 THEN fr.tglinfeksi ELSE NULL END AS ISK,
                                     CASE WHEN fr.infeksifk = 18 THEN fr.tglinfeksi ELSE 	NULL END AS SKABIES
                              FROM  surveilans_t AS sv
                                    LEFT JOIN surveilansfrd_t AS fr ON fr.nosurvailensfk = sv.norec
                                    LEFT JOIN surveilansantibiotik_t AS san ON san.nosurvailensfk = sv.norec
                                    LEFT JOIN diagnosa_m AS dg ON dg.id = sv.diagnosamasukfk
                                    INNER JOIN pasiendaftar_t AS pd ON pd.norec = sv.noregistrasifk
                                    INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = sv.norec_apd
                                    INNER JOIN pasien_m AS pm ON pm.id = pd.nocmfk
                                    INNER JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                                    LEFT JOIN jeniskelamin_m AS jk ON jk.id = pm.objectjeniskelaminfk
                                    LEFT JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
                                    LEFT JOIN batalregistrasi_t AS br ON br.pasiendaftarfk = pd.norec
                                    LEFT JOIN tindakanoperasi_m AS tno ON tno.id = fr.tindakanoperasifk
                                    LEFT JOIN infeksinosokomial_m AS inf ON inf.id = fr.infeksifk
                                    LEFT JOIN produk_m AS pro ON pro.id = san.tindakanoperasifk
                              WHERE br.norec IS NULL AND sv.statusenabled = true AND sv.kdprofile = $idProfile AND sv.tglsurveilans BETWEEN '$tglAwal' and '$tglAkhir'
                                    $paramDept
                                    $paramRuangan
                                    $paramKelompokPasien
                                    $paramNamaPasien
                                    $paramNoregistrasi
                                    $paramNoRm
                              ) AS x
                              GROUP BY 
                                    x.norec,x.tglsurveilans,x.tglregistrasi,x.nocm,x.namapasien,x.noregistrasi,x.tgllahir,x.jk,
                                    x.namadiagnosa,x.hasilkultur,x.namaproduk,x.PHLEBITIS,x.DIARE,x.ISK,x.SKABIES"));

        $result= array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataIdoSurveilans(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('surveilans_t as sv')
            ->join('surveilansoperasi_t as so','so.nosurvailensfk','=','sv.norec')
            ->leftJoin('surveilansantibiotik_t as sa','sa.nosurvailensfk','=','sv.norec')
            ->leftJoin('diagnosa_m as dg','dg.id','=','so.diagnosafk')
            ->join('pasiendaftar_t as pd','pd.norec','=','sv.noregistrasifk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec','=','sv.norec_apd')
            ->join('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','pm.objectjeniskelaminfk')
            ->leftJoin('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->leftJoin('produk_m as pro','pro.id','=','so.produkfk')
            ->leftJoin('jenisoperasi_m as jo','jo.id','=','so.jenisoperasifk')
            ->leftJoin('asascore_m as sc','sc.id','=','so.asascorefk')
            ->leftJoin('produk_m as pro1','pro1.id','=','sa.tindakanoperasifk')
            ->select(DB::raw("sv.norec,sv.tglsurveilans,pd.tglregistrasi,pm.nocm,pm.namapasien,pd.noregistrasi,
                              pm.tgllahir,jk.reportdisplay AS jk,CASE WHEN so.diagnosafk IS NOT NULL 
                              THEN dg.kddiagnosa || ', ' || dg.namadiagnosa WHEN so.keterangandiagnosa 
                              IS NOT NULL THEN '-, ' || so.keterangandiagnosa ELSE '-' END AS namadiagnosa,
                              apd.tglmasuk,apd.tglkeluar,CASE WHEN so.produkfk IS NOT NULL THEN pro.namaproduk ELSE
                              '-' END AS tindakan,CASE WHEN so.jenisoperasifk IS NOT NULL THEN jo.jenisoperasi ELSE
                              '-' END AS jenisoperasi,CASE WHEN so.asascorefk IS NOT NULL THEN sc.asascore ELSE
                              '-' END AS asascore,CASE WHEN sa.tindakanoperasifk IS NOT NULL THEN pro1.namaproduk ELSE
                              '-' END AS antibiotik,so.implant,so.hasilkultur"))
            ->whereNull('br.norec')
            ->where('sv.kdprofile', (int)$kdProfile)
            ->where('sv.statusenabled',true);

        $filter = $request->all();
        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('sv.tglsurveilans', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir'];//." 23:59:59";
            $data = $data->where('sv.tglsurveilans', '<=', $tgl);
        }
        if (isset($filter['idDept']) && $filter['idDept'] != "" && $filter['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', '=', $filter['idDept']);
        }
        if (isset($filter['idRuangan']) && $filter['idRuangan'] != "" && $filter['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['idRuangan']);
        }
        if (isset($filter['kelompokPasien']) && $filter['kelompokPasien'] != "" && $filter['kelompokPasien'] != "undefined") {
            $data = $data->where('pd.objectkelompokpasienlastfk', '=', $filter['kelompokPasien']);
        }
        if(isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('pm.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }
        if(isset($filter['noRegis']) && $filter['noRegis'] != "" && $filter['noRegis'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noRegis'] . '%');
        }
        if(isset($filter['noCm']) && $filter['noCm'] != "" && $filter['noCm'] != "undefined") {
            $data = $data->where('pm.nocm', 'ilike', '%' . $filter['noCm'] . '%');
        }
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveCheklisApd(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $tglAyeuna = date('Y-m-d H:i:s');
        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item) {
//                foreach ($item['tgl'] as $itemTgl){
                    if ($request['id'] == '') {
                        $dataJadwal = new CheklisApd();
                        $dataJadwal->norec = $dataJadwal->generateNewId();
                        $dataJadwal->kdprofile = (int)$kdProfile;
                        $dataJadwal->statusenabled = true;

                    } else {
                        $dataJadwal = CheklisApd::where('id', $request['id'])->first();
                    }

                    $dataJadwal->objectpegawaifk = $request['pegawaifk'];
                    $dataJadwal->objectruanganfk = $request['ruanganfk'];
                    $dataJadwal->jeniskegiatan = $item['jeniskegiatan'];
                    $dataJadwal->tglinput = $tglAyeuna;
                    $dataJadwal->tgl = $item['tgl'];
                    if(isset($item['isi'])){
                        $dataJadwal->isi = $item['isi'];
                    }
                    $dataJadwal->save();
//                }
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "res" => $dataJadwal,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataCheklisApd(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('cheklisapd_t as ei')
            ->join('pegawai_m as pg','pg.id','=','ei.objectpegawaifk')
            ->join('ruangan_m as ru','ru.id','=','ei.objectruanganfk')
            ->select(DB::raw("ei.*,ru.namaruangan,pg.namalengkap as petugas"))
            ->where('ei.kdprofile', $kdProfile)
            ->where('ei.statusenabled', true);
//            ->orderByRaw('pg.namalengkap,pjk.tgljadwal desc');

        if(isset($request['bulan']) &&
            $request['bulan']!="" &&
            $request['bulan']!="undefined"){
            $tgl = $request['bulan']  ;
            $data = $data->whereRaw("
            -- STUFF(CONVERT(varchar(10), ei.tglinput,104),1,3,'')  
            OVERLAY(to_char(ei.tglinput,'DD.MM.YYYY') placing '' from 1 for 3) ='$tgl' " );
        };
        if(isset($request['namalengkap']) &&
            $request['namalengkap']!="" &&
            $request['namalengkap']!="undefined"){
            $data = $data->where('ei.objectpegawaifk','=',$request['namalengkap']);
        };
        if(isset($request['idRuangan']) &&
            $request['idRuangan']!="" &&
            $request['idRuangan']!="undefined"){
            $data = $data->where('ru.id','=', $request['idRuangan'] );
        };

        $data = $data->get();
        return $this->respond($data);
    }
}