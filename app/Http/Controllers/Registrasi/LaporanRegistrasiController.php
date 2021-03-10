<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 8/14/2019
 * Time: 10:30 AM
 */

namespace App\Http\Controllers\Registrasi;

use App\Traits\Holiday;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\PasienDaftar;
use App\Transaksi\RisOrder;
use App\Transaksi\TempLaporanLayanan;
use App\Transaksi\TempLaporanPasienPulang;
use App\Transaksi\TempRiwayatPersediaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
//use App\Pegawai\ModulAplikasi;
//use App\Pegawai\MapObjekModulToKelompokUser;
//use App\Pegawai\MapObjekModulAplikasiToModulAplikasi;
//use App\Pegawai\ObjekModulAplikasi;
//use App\Master\KelompokUser;

use App\Transaksi\StrukOrder;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\OrderProduk;
use App\Master\Pegawai;
use App\Traits\Valet;
use PHPExcel;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Settings;
use App;
use Dompdf\Dompdf;
use Dompdf\Options;
class LaporanRegistrasiController extends  App\Http\Controllers\ApiController
{
    use Valet;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }
    public function getDataCombo(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$idProfile));
        $deptPelayananRanap = explode (',',$this->settingDataFixed('KdListDepartemen',$idProfile));
        $deptPelayananRajal = explode (',',$this->settingDataFixed('KdListDepartemenRajal',$idProfile));
        $kdJeniPegawaiDokter = (int) $this->settingDataFixed('KdJenisPegawaiDokter',$idProfile);
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $kdDepartemenRawatInap = [];
        foreach ($deptPelayananRanap as $iteng){
            $kdDepartemenRawatInap []=  (int)$iteng;
        }
        $kdDepartemenRawatRajal = [];
        foreach ($deptPelayananRajal as $item){
            $kdDepartemenRawatRajal []=  (int)$item;
        }
        $dataLogin = $request->all();
        $dataPegawaiLogin = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $idProfile)
            ->first();

        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id',$kdDepartemenRawatPelayanan)
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', $idProfile)
            ->orderBy('dp.namadepartemen')
            ->get();
            // return $dataInstalasi;
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.objectjenispegawaifk', $kdJeniPegawaiDokter)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.namalengkap')
            ->get();

        $dataDokters = \DB::table('pegawai_m as ru')
            ->select('ru.id','ru.namalengkap as namaLengkap')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.objectjenispegawaifk', $kdJeniPegawaiDokter)
            ->orderBy('ru.namalengkap')
            ->get();

        $dataDiagnosa = \DB::table('diagnosa_m as dgm')
            ->select('dgm.id','dgm.kddiagnosa','dgm.namadiagnosa')
            ->where('dgm.statusenabled', true)
//            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('dgm.kddiagnosa')
            ->take(100)
            ->get();

        $dataJenisDiagnosa = \DB::table('jenisdiagnosa_m as jd')
            ->select('jd.id','jd.jenisdiagnosa')
            ->where('jd.kdprofile', $idProfile)
            ->where('jd.statusenabled', true)
//            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('jd.jenisdiagnosa')
            ->get();
        $dataDepartemen =[];
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
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataKelas = \DB::table('kelas_m as kl')
            ->select('kl.id', 'kl.namakelas')
            ->where('kl.kdprofile', $idProfile)
            ->where('kl.statusenabled', true)
            ->orderBy('kl.namakelas')
            ->get();

        $dataKamar = \DB::table('kamar_m as kmr')
            ->select('kmr.id', 'kmr.namakamar')
            ->where('kmr.kdprofile', $idProfile)
            ->where('kmr.statusenabled', true)
            ->orderBy('kmr.namakamar')
            ->get();

        $dataRuanganInap = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
//            ->where('ru.objectdepartemenfk', '16')
            ->whereIn('ru.objectdepartemenfk',$kdDepartemenRawatInap)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataRuanganJalan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->whereIn('ru.objectdepartemenfk', $kdDepartemenRawatRajal)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataPegawai = \DB::table('pegawai_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
//            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('ru.namalengkap')
            ->get();

        $dataPetugasPe = \DB::table('jenispetugaspelaksana_m as ru')
            ->select('ru.jenispetugaspe', 'ru.id')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.jenispetugaspe')
            ->get();

        $dataKasir= DB::select(DB::raw("select pg.id,pg.namalengkap,lu.id as luid from loginuser_s lu
                INNER JOIN pegawai_m pg on lu.objectpegawaifk=pg.id
                where pg.kdprofile = $idProfile and objectkelompokuserfk=:id;"),
            array(
                'id' => 20,
            )
        );

        $result = array(
            'departemen' => $dataDepartemen,
            'ruangan' => $dataRuangan,
            'ruanganinap' => $dataRuanganInap,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'datalogin' => $dataLogin,
            'kelas' => $dataKelas,
            'kamar' => $dataKamar,
            'dataDokters'=>$dataDokters,
//            'rekanan' => $dataRekanan,
            'pegawai' => $dataPegawai,
            'kasir'=>$dataKasir,
            'diagonsa'=> $dataDiagnosa,
            'jenisdiagnosa'=>$dataJenisDiagnosa,
            'petugaspe' =>$dataPetugasPe,
            'ruanganjalan' => $dataRuanganJalan,
            'user' => $dataPegawaiLogin,
            'message' => 'as@egieramdan',
        );

        return $this->respond($result);
    }
    public function getDataLaporanPasienDaftar(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $namaPasien = $request['namaPasien'];
        $noRm = $request['noRm'];
        $idDept =  ' ';
        $paramsTgl = 'pd.tglregistrasi';

        if (isset($request['isTglPulang']) && $request['isTglPulang'] != "" && $request['isTglPulang'] != "undefined") {
            if($request['isTglPulang'] == true){
                   $paramsTgl = 'pd.tglpulang';
            }
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $idDept =  ' and ru.objectdepartemenfk =  ' . $request['idDept'];
        }
        $idRuangan =  ' ';
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $idRuangan = ' and ru.id = ' . $request['idRuangan'];
        }
        $idKelompok = ' ';
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            if ($idKelompok == 135 || $idKelompok == '135'){
                $idKelompok = 'and pd.objectkelompokpasienlastfk in (1,5,3)';
            }else{
                $idKelompok = 'and pd.objectkelompokpasienlastfk = '. $request['kelompokPasien'];
            }
        }
        $idDokter = ' ';
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $idDokter = 'and pg.id  = '. $request['idDokter'];
        }
        $tindakan = ' ';
        if (isset($request['tindakan']) && $request['tindakan'] != "" && $request['tindakan'] != "undefined") {
            $tindakan = ' and pr.id = ' . $request['tindakan'];
        }
        $kelas = ' ';
        if (isset($request['kelas']) && $request['kelas'] != "" && $request['kelas'] != "undefined") {
            $kelas = ' and kl.id = ' . $request['kelas'];
        }
        $PetugasPe= ' ';
        if (isset($request['PetugasPe']) && $request['PetugasPe'] != "" && $request['PetugasPe'] != "undefined") {
            $PetugasPe = ' and ppp.objectjenispetugaspefk =' . $request['PetugasPe'];
        }
        $norm= ' ';
        if (isset($request['noRm']) && $request['noRm'] != "" && $request['noRm'] != "undefined") {
            $norm = ' and pm.nocm = \'' . $request['noRm'].'\'';
        }
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("SELECT pd.noregistrasi,pm.nocm,pm.namapasien,pm.tgllahir,jk.reportdisplay as jk, kl.namakelas,pg.namalengkap as dokterpj,pd.tglregistrasi,pd.tglpulang,rk.namarekanan,
                                     ru.namaruangan as ruangandaftar,ru.id as idruangandaftar,ru.objectdepartemenfk as iddepartementdaftar,
                                     klp.id as idkelompokpasien,klp.kelompokpasien,
                                     case when apd.statuskunjungan='BARU' then 'y' else 'n' end as statuskunjungan,
                                     alm.alamatlengkap,sp.statusperkawinan,ag.agama,pend.pendidikan, peker.pekerjaan,
                                     br.norec,alm.namadesakelurahan as kelurahan, alm.namakecamatan as kecamatan,alm.namakotakabupaten as kabupaten,sk.suku,
                                     dg.kddiagnosa as kodemasuk, dg.namadiagnosa AS diagnosamasuk,
                                    dg1.kddiagnosa as kodekeluar, dg1.namadiagnosa AS diagnosakeluar,
                                    pg1.namalengkap as pegawaimasuk, pg2.namalengkap as pegawaiprimer, to_char(pd.tglregistrasi,'DD-MM-YYYY') as tglregistrasi1,
                                     to_char(pd.tglregistrasi,'HH24:MI') as jamregistrasi,to_char(pd.tglpulang,'DD-MM-YYYY') as tglpulang1,
                                     to_char(pd.tglpulang,'HH24:MI') as jampulang,to_char(pm.tgllahir,'DD-MM-YYYY') as tgllahir1, pd.statuspasien

                        from pasiendaftar_t as pd
                        inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                        inner join pasien_m as pm on pm.id = pd.nocmfk
                        inner join jeniskelamin_m as jk on jk.id=pm.objectjeniskelaminfk
                        inner join ruangan_m  as ru on ru.id=apd.objectruanganfk
                        left join kelas_m as kl on kl.id=pd.objectkelasfk
                        left join pegawai_m  as pg on pg.id=pd.objectpegawaifk
                        -- left join pegawai_m as pg2 on pg2.id=apd.objectpegawaifk
                        left join rekanan_m  as rk on rk.id=pd.objectrekananfk
                        left join kelompokpasien_m as klp on klp.id=pd.objectkelompokpasienlastfk
                        -- left join asalrujukan_m as ar on ar.id=apd.objectasalrujukanfk
                        left join alamat_m as alm on alm.nocmfk = pm.id
                        left join agama_m as ag on ag.id = pm.objectagamafk
                        left join statusperkawinan_m as sp on sp.id = pm.objectstatusperkawinanfk
                        left join pendidikan_m as pend on pend.id=pm.objectpendidikanfk
                        left join pekerjaan_m as peker on peker.id=pm.objectpekerjaanfk
                        -- left join kondisipasien_m as kdp on kdp.id=pd.objectkondisipasienfk
                        left join departemen_m as dpt on dpt.id=ru.objectdepartemenfk
                        left join suku_m as sk on sk.id=pm.objectsukufk
                        left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                        LEFT JOIN detaildiagnosapasien_t AS ddp ON ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 5
                        left join pegawai_m  as pg1 on pg1.id=ddp.objectpegawaifk
                        LEFT JOIN diagnosa_m AS dg ON dg.id = ddp.objectdiagnosafk
                        LEFT JOIN detaildiagnosapasien_t AS ddp1 ON ddp1.noregistrasifk = apd.norec and ddp1.objectjenisdiagnosafk = 1
                        left join pegawai_m  as pg2 on pg2.id=ddp1.objectpegawaifk
                        LEFT JOIN diagnosa_m AS dg1 ON dg1.id = ddp1.objectdiagnosafk
                        WHERE pd.kdprofile = $idProfile and $paramsTgl between '$tglAwal' and '$tglAkhir' and apd.objectruanganfk=pd.objectruanganlastfk and br.norec is null 
                        and pm.namapasien ilike '%$namaPasien%'
                        $idDept
                        $idRuangan
                        $idKelompok
                        $norm"));

        return $this->respond($data);
    }
    public function getDataLaporanPasienMasukRawatInap(Request $request) {
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $kelasId = $request['idkelas'];
        $ruanganasal = $request['ruanganAsal'];
        $namapasien = $request['namapasien'];
        $norm = $request['norm'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $paramRuangan = ' ';
        $paramRuanganSatu = ' ';
        $paramRuanganAsal = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and rpp.objectruanganfk = ' . $ruanganId;
        }

        $paramKelas = ' ';
        if (isset($kelasId) && $kelasId != "" && $kelasId != "undefined") {
            $paramKelas = ' and rpp.objectkelasfk = ' . $kelasId;
        }

        $paramRuanganAsal = ' ';
        if (isset($ruanganasal) && $ruanganasal != "" && $ruanganasal != "undefined") {
            $paramRuanganAsal = ' and rpp.objectruanganasalfk = ' . $ruanganasal;
        }

        $paramNoRm = ' ';
        if (isset($norm) && $norm != "" && $norm != "undefined") {
            $paramNoRm = ' and pm.nocm = ' . $norm;
        }

        $data = DB::select(DB::raw("
select '-' as smf,* from  (
    select pd.tglregistrasi,apd.tglmasuk,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,apd.norec,ru1.namaruangan as asal,
                                          case when ddp.noregistrasifk is not null THEN  cast (dm.kddiagnosa as text) || ', ' || dm.namadiagnosa else '-' end as namadiagnosa,

                                            row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                                               and rpp.objectruanganfk = apd.objectruanganfk
                                               and rpp.tglmasuk = apd.tglmasuk
                                               and rpp.objectstatuskeluarfk is null
                                    LEFT JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                    LEFT JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                    LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = apd.objectkelasfk
                                    INNER JOIN ruangan_m as ru on ru.id = rpp.objectruanganfk
                                    LEFT JOIN ruangan_m as ru1 on ru1.id = rpp.objectruanganasalfk and ru1.objectdepartemenfk NOT IN (25,28)
                                    LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                    where pd.kdprofile = $idProfile and apd.tglmasuk  BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuangan
                                    $paramKelas $paramRuanganAsal 
                                    ) as x where x.rownum =1
                                    UNION ALL
                                    select '-' as smf, * from  (
                                    select pd.tglregistrasi,apd.tglmasuk,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,apd.norec,ru1.namaruangan as asal,
                                           case when ddp.noregistrasifk is not null THEN  cast (dm.kddiagnosa as text) || ', ' || dm.namadiagnosa else '-' end as namadiagnosa,

                                            row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                                               and rpp.objectruanganfk = apd.objectruanganfk
                                               and rpp.tglmasuk = apd.tglmasuk
                                               and rpp.objectstatuskeluarfk <> 2
                                    LEFT JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                    LEFT JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                    LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = apd.objectkelasfk
                                    INNER JOIN ruangan_m as ru on ru.id = rpp.objectruanganfk
                                    LEFT JOIN ruangan_m as ru1 on ru1.id = rpp.objectruanganasalfk and ru1.objectdepartemenfk NOT IN (25,28)
                                    LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                    where pd.kdprofile = $idProfile and apd.tglmasuk  BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuangan
                                    $paramKelas $paramRuanganAsal
                                     ) as x where x.rownum =1"));
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataLaporanPasienKeluarRuanganRawatInap(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAyeuna = date('Y-m-d H:i:s');
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $kelasId = $request['idkelas'];
        $namapasien = $request['namapasien'];
        $norm = $request['norm'];

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and apd.objectruanganfk = ' . $ruanganId;
        }

        $paramKelas = ' ';
        if (isset($kelasId) && $kelasId != "" && $kelasId != "undefined") {
            $paramKelas = ' and pd.objectkelasfk  = ' . $kelasId;
        }
        $paramNamaPasien = ' ';
        if (isset($namapasien) && $namapasien != "" && $namapasien != "undefined") {
            $paramNamaPasien = ' and pm.namapasien = ' . $namapasien;
        }

        $paramNoRm = ' ';
        if (isset($norm) && $norm != "" && $norm != "undefined") {
            $paramNoRm = ' and pm.nocm = ' . $norm;
        }

        $data = DB::select(DB::raw("select * from  (
                        select pd.tglregistrasi,pd.tglpulang,apd.tglkeluar,apd.tglmasuk,pm.nocm,pd.noregistrasi,
                        pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,'-' as smf,sk1.statuskeluar,
                        pg.namalengkap,
                       case when ddp.noregistrasifk is not null THEN dm.kddiagnosa || ', ' || dm.namadiagnosa else '-' end as namadiagnosa,
                        --  case when ddp.noregistrasifk is not null THEN dm.kddiagnosa + ', ' + dm.namadiagnosa else '-' end as namadiagnosa,
                           row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                        FROM pasiendaftar_t as pd
                        INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                        and apd.objectruanganfk = pd.objectruanganlastfk
                        and apd.tglkeluar = pd.tglpulang
                        LEFT JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                        LEFT JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                        INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                        INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                        INNER JOIN kelas_m as kls on kls.id = pd.objectkelasfk
                        LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                        JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                        INNER JOIN statuskeluar_m as sk1 on sk1.id = pd.objectstatuskeluarfk
                        LEFT JOIN pegawai_m AS pg ON pg.id = pd.objectdokterpemeriksafk
                        WHERE pd.kdprofile = $idProfile and pd.tglpulang BETWEEN '$tglAwal' and '$tglAkhir'
                        and pd.tglmeninggal is null
                        and pd.statusenabled =true
                        and pm.namapasien ilike '%$namapasien%' $paramNoRm
                        $paramRuangan
                        $paramKelas
                        ) as x where x.rownum=1

                         "));


        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataLaporanPasienPindahan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $kelasId = $request['idkelas'];
        $namapasien = $request['namapasien'];
        $norm = $request['norm'];

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and rpp.objectruanganfk = ' . $ruanganId;
        }

        $paramKelas = ' ';
        if (isset($kelasId) && $kelasId != "" && $kelasId != "undefined") {
            $paramKelas = ' and rpp.objectkelasfk  = ' . $kelasId;
        }
        $paramNamaPasien = ' ';
        if (isset($namapasien) && $namapasien != "" && $namapasien != "undefined") {
            $paramNamaPasien = ' and pm.namapasien = ' . $namapasien;
        }

        $paramNoRm = ' ';
        if (isset($norm) && $norm != "" && $norm != "undefined") {
            $paramNoRm = ' and pm.nocm = ' . $norm;
        }

        $data = DB::select(DB::raw("select * from (
                select pd.tglregistrasi,apd.tglkeluar,apd.tglmasuk,rpp.tglpindah,pm.nocm,pd.noregistrasi,
                                           pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan as ruanganskrng,'-' as smf,
                                        case when ddp.noregistrasifk is not null THEN dm.kddiagnosa || ', ' || dm.namadiagnosa else '-' end as namadiagnosa,
                                             --  case when ddp.noregistrasifk is not null THEN dm.kddiagnosa + ', ' + dm.namadiagnosa else '-' end as namadiagnosa,
                                           rpp.objectruanganasalfk,rpp.objectruanganfk,ru1.namaruangan as ruangansblm,
                                           row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum,
                                           row_number() over (partition by apd.objectruanganfk ,pd.noregistrasi order by apd.tglmasuk asc) as rownum_ru
                                          FROM pasiendaftar_t as pd
                                          INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                          INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                                                   and apd.objectruanganfk = rpp.objectruanganfk
                                                   and apd.tglmasuk = rpp.tglpindah
                                                   and rpp.objectstatuskeluarfk = 2
                                          LEFT  JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                          LEFT  JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                          LEFT  JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                          INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                          INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                          INNER JOIN kelas_m as kls on kls.id = rpp.objectkelasfk
                                          LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                          LEFT JOIN ruangan_m as ru on ru.id = rpp.objectruanganfk
                                          LEFT JOIN ruangan_m as ru1 on ru1.id = rpp.objectruanganasalfk
                                          INNER JOIN statuskeluar_m as sk1 on sk1.id = rpp.objectstatuskeluarfk
                                          WHERE pd.kdprofile = $idProfile and rpp.tglpindah BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                          and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                          $paramRuangan
                                          $paramKelas
                                          ) as x where x.rownum =1
                                          and x.rownum_ru =1"));
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataLaporanPasienMeninggalRuangan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $kelasId = $request['idkelas'];
        $namapasien = $request['namapasien'];
        $norm = $request['norm'];

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and apd.objectruanganfk = ' . $ruanganId;
        }

        $paramKelas = ' ';
        if (isset($kelasId) && $kelasId != "" && $kelasId != "undefined") {
            $paramKelas = ' and apd.objectkelasfk  = ' . $kelasId;
        }
        $paramNamaPasien = ' ';
        if (isset($namapasien) && $namapasien != "" && $namapasien != "undefined") {
            $paramNamaPasien = ' and pm.namapasien = ' . $namapasien;
        }

        $paramNoRm = ' ';
        if (isset($norm) && $norm != "" && $norm != "undefined") {
            $paramNoRm = ' and pm.nocm = ' . $norm;
        }

        $data = DB::select(DB::raw("select pd.norec,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,'-' as smf,
                 pd.tglregistrasi,pd.tglpulang,pd.tglmeninggal
                FROM pasiendaftar_t as pd
                LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                INNER JOIN kelas_m as kls on kls.id = pd.objectkelasfk
                INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $idProfile and pd.tglmeninggal BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                 and pd.objectstatuskeluarfk = 5
                and pm.namapasien ilike '%$namapasien%' $paramNoRm
                $paramRuangan
                $paramKelas"));

                $norecaPd = '';
                foreach ($data as $ob){
                    $norecaPd = $norecaPd.",'".$ob->norec . "'";
                    $ob->kddiagnosa = [];
                }
                $norecaPd = substr($norecaPd, 1, strlen($norecaPd)-1);
                $diagnosa = [];
                if($norecaPd!= ''){
                    $diagnosa = DB::select(DB::raw("
                        select dg.kddiagnosa,ddp.noregistrasifk as norec_apd,apd.noregistrasifk
                        from antrianpasiendiperiksa_t as apd
                        join detaildiagnosapasien_t as ddp on  ddp.noregistrasifk=apd.norec
                        left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
                        left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                        where apd.noregistrasifk in ($norecaPd) "));
                   $i = 0;
                   foreach ($data as $h){
                       $data[$i]->kddiagnosa = '';
                       foreach ($diagnosa as $d){
                           if($data[$i]->norec == $d->noregistrasifk){
                                $data[$i]->kddiagnosa =  $data[$i]->kddiagnosa .', '.$d->kddiagnosa;
                           }
                       }
                       $data[$i]->kddiagnosa = substr($data[$i]->kddiagnosa , 1, strlen($data[$i]->kddiagnosa )-1);
                       $i++;
                   }
                }
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataLaporanInformasiRuangan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $date = $request['tglAkhirKemarin'];
        $tglAwalKemarin = Carbon::parse($tglAwal)->subDays(1);
        $tglAkhirKemarin = Carbon::parse($date)->subDays(1);
        $ruanganId = $request['idRuangan'];
        $kelasId = $request['idkelas'];
        $namapasien = $request['namapasien'];
        $norm = $request['norm'];

        $paramRuangan = '';
        $paramRuanganSatu = '';
        $paramRuanganKeluar = '';
        $paramRuanganPindah = '';
        $paramRuanganDiPindah = '';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and apd.objectruanganfk = ' . $ruanganId;
            $paramRuanganSatu = ' and pd.objectruanganlastfk = ' . $ruanganId;
            $paramRuanganKeluar = ' and apd.objectruanganfk = ' . $ruanganId;
            $paramRuanganPindah = ' and rpp.objectruanganfk = ' . $ruanganId;
            $paramRuanganDiPindah = ' and rpp.objectruanganasalfk = ' . $ruanganId;
        }

        $paramKelas = ' ';
        $paramKelasApd = '';
        if (isset($kelasId) && $kelasId != "" && $kelasId != "undefined") {
            $paramKelas = ' and rpp.objectkelasfk  = ' . $kelasId;
            $paramKelasApd = ' and apd.objectkelasfk  = ' . $kelasId;
        }
        $paramNamaPasien = ' ';
        if (isset($namapasien) && $namapasien != "" && $namapasien != "undefined") {
            $paramNamaPasien = ' and pm.namapasien = ' . $namapasien;
        }

        $paramNoRm = ' ';
        if (isset($norm) && $norm != "" && $norm != "undefined") {
            $paramNoRm = ' and pm.nocm = ' . $norm;
        }
        $data = DB::select(DB::raw("

                        select *,'-' as smf from  (select pd.tglregistrasi,apd.tglmasuk,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,
                                           case when ddp.noregistrasifk is not null THEN dm.kddiagnosa || ', '  || dm.namadiagnosa else '-' end as namadiagnosa,apd.norec,
                                           row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                                               and rpp.objectruanganfk = apd.objectruanganfk
                                               and rpp.tglmasuk = apd.tglmasuk
                                               and rpp.objectstatuskeluarfk is null
                                    LEFT JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                    LEFT JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                    LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = apd.objectkelasfk
                                    INNER JOIN ruangan_m as ru on ru.id = rpp.objectruanganfk
                                    LEFT JOIN ruangan_m as ru1 on ru1.id = rpp.objectruanganasalfk and ru1.objectdepartemenfk NOT IN (18,25,28)
                                    LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                    where pd.kdprofile = $idProfile and apd.tglmasuk BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuanganPindah
                                    $paramKelas
                                    ) as x where  x.rownum =1
                                    UNION ALL
                                      select *,'-' as smf from  (
                                    select pd.tglregistrasi,apd.tglmasuk,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,
                                           case when ddp.noregistrasifk is not null THEN dm.kddiagnosa ||  ', ' ||  dm.namadiagnosa else '-' end as namadiagnosa,apd.norec,
                                                 row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                                               and rpp.objectruanganfk = apd.objectruanganfk
                                               and rpp.tglmasuk = apd.tglmasuk
                                               and rpp.objectstatuskeluarfk <> 2
                                    LEFT JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                    LEFT JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                    LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = apd.objectkelasfk
                                    INNER JOIN ruangan_m as ru on ru.id = rpp.objectruanganfk
                                    LEFT JOIN ruangan_m as ru1 on ru1.id = rpp.objectruanganasalfk and ru1.objectdepartemenfk NOT IN (18,25,28)
                                    LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                    where pd.kdprofile = $idProfile and apd.tglmasuk  BETWEEN'$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuanganPindah
                                    $paramKelas

                                    ) as x where  x.rownum =1 "));
//        $dataKemarin = DB::select(DB::raw("select apd.tglmasuk,apd.tglkeluar,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien
//                                    FROM pasiendaftar_t as pd
//                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
//                                    LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
//                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
//                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
//                                    INNER JOIN kelas_m as kls on kls.id = apd.objectkelasfk
//                                    INNER JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
//                                    where apd.tglkeluar > '$tglAkhirKemarin' and apd.tglmasuk < '$tglAwal' and br.pasiendaftarfk is null
//                                    $paramRuangan
//                                    $paramKelasApd"));
        $dataKemarin = DB::select(DB::raw("select apd.tglmasuk,apd.tglkeluar,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = apd.objectkelasfk
                                    INNER JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                                    where apd.tglmasuk < '$tglAkhirKemarin' and br.pasiendaftarfk is null
                                    and pd.kdprofile = $idProfile and apd.tglkeluar is null
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuangan
                                    $paramKelasApd"));
        $dataAskes = DB::select(DB::raw("select apd.tglmasuk,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                                    where pd.kdprofile = $idProfile and apd.tglmasuk BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                    and kp.id in (2,4) 
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuanganSatu
                                    $paramKelasApd"));
        $dataKeluar = DB::select(DB::raw("select * from (
select pd.tglregistrasi,pd.tglpulang,apd.tglkeluar,apd.tglmasuk,pm.nocm,pd.noregistrasi,
                                           pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,'-' as smf,
                                           case when ddp.noregistrasifk is not null THEN dm.kddiagnosa || ', ' || dm.namadiagnosa
                                           else '-' end as namadiagnosa,sk1.statuskeluar,
                                                 row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                               and apd.objectruanganfk = pd.objectruanganlastfk
                                               and apd.tglkeluar = pd.tglpulang
                                    LEFT  JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                    LEFT  JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                    LEFT  JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = pd.objectkelasfk
                                    LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                    JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                                    INNER JOIN statuskeluar_m as sk1 on sk1.id = pd.objectstatuskeluarfk
                                    WHERE pd.kdprofile = $idProfile and pd.tglpulang BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk IS NULL
                                          and pd.tglmeninggal is null 
                                          and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuanganKeluar
                                    $paramKelas
                                    ) as x where x.rownum=1"));
        $dataPindah = DB::select(DB::raw("select * from (
select pd.tglregistrasi,apd.tglkeluar,apd.tglmasuk,rpp.tglpindah,pm.nocm,pd.noregistrasi,
                                           pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,'-' as smf,
                                           case when ddp.noregistrasifk is not null THEN dm.kddiagnosa || ', ' || dm.namadiagnosa
                                           else '-' end as namadiagnosa,rpp.objectruanganasalfk,rpp.objectruanganfk,
                                                 row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                           FROM pasiendaftar_t as pd
                                          INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                          INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                                                   and apd.objectruanganfk = rpp.objectruanganfk
                                                   and apd.tglmasuk = rpp.tglpindah
                                                   and rpp.objectstatuskeluarfk = 2
                                          LEFT  JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                          LEFT  JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                          LEFT  JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                          INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                          INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                          INNER JOIN kelas_m as kls on kls.id = rpp.objectkelasfk
                                          LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                          LEFT JOIN ruangan_m as ru on ru.id = rpp.objectruanganfk
                                          INNER JOIN statuskeluar_m as sk1 on sk1.id = rpp.objectstatuskeluarfk
                                          WHERE pd.kdprofile = $idProfile and rpp.tglpindah BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                          and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                          $paramRuangan
                                          $paramKelas
                                          ) as x where x.rownum=1"));
        $dataDipindahkan = DB::select(DB::raw("
                                  select * from (select
                                            pd.tglregistrasi,apd.tglkeluar,apd.tglmasuk,pm.nocm,pd.noregistrasi,
                                            pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,'-' as smf,
                                            case when ddp.noregistrasifk is not null THEN dm.kddiagnosa || ', ' || dm.namadiagnosa
                                            else '-' end as namadiagnosa,rpp.objectstatuskeluarfk,
                                                  row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    LEFT JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                                              and apd.tglkeluar = rpp.tglpindah
                                              and rpp.objectstatuskeluarfk = 2
                                    LEFT  JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                    LEFT  JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                    LEFT  JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = pd.objectkelasfk
                                    LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                    LEFT JOIN ruangan_m as ru on ru.id = rpp.objectruanganasalfk
                                    LEFT JOIN statuskeluar_m as sk1 on sk1.id = pd.objectstatuskeluarfk
                                    WHERE pd.kdprofile = $idProfile and rpp.tglpindah BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuanganDiPindah
                                    $paramKelas
                                    ) as x where x.rownum=1"));
        $dataMinggal = DB::select(DB::raw("select * from (select apd.tglmasuk,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan,'-' as smf,
                                                 case when dm.namadiagnosa = '-' then '-, ' || ddp.keterangan
                                                 when dm.namadiagnosa = '-' and ddp.keterangan is null then '-'
                                                 else dm.kddiagnosa || ', ' || dm.namadiagnosa end as namadiagnosa,
                                                 pd.tglregistrasi,pd.tglpulang,pd.tglmeninggal,
                                                       row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    LEFT JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                    LEFT JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                    LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = apd.objectkelasfk
                                    INNER JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                                    LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                    where pd.kdprofile = $idProfile and pd.tglmeninggal BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                    and pd.objectstatuskeluarfk = 5
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuangan
                                    $paramKelasApd) as x where x.rownum=1"));


        $result = array(
            'datamasuk' => $data,
            'datakemarin' => $dataKemarin,
            'dataaskes' => $dataAskes,
            'datakeluar' => $dataKeluar,
            'datapindah' => $dataPindah,
            'datadipindahkan' => $dataDipindahkan,
            'datameninggal' => $dataMinggal,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataLaporanPasienDipindahankan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $kelasId = $request['idkelas'];
        $namapasien = $request['namapasien'];
        $norm = $request['norm'];

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and rpp.objectruanganasalfk = ' . $ruanganId;
        }

        $paramKelas = ' ';
        if (isset($kelasId) && $kelasId != "" && $kelasId != "undefined") {
            $paramKelas = ' and rpp.objectkelasfk  = ' . $kelasId;
        }
        $paramNamaPasien = ' ';
        if (isset($namapasien) && $namapasien != "" && $namapasien != "undefined") {
            $paramNamaPasien = ' and pm.namapasien = ' . $namapasien;
        }

        $paramNoRm = ' ';
        if (isset($norm) && $norm != "" && $norm != "undefined") {
            $paramNoRm = ' and pm.nocm = ' . $norm;
        }

        $data = DB::select(DB::raw("select * from (
              select
                                            pd.tglregistrasi,apd.tglkeluar,apd.tglmasuk,pm.nocm,pd.noregistrasi,
                                            pm.namapasien,kp.kelompokpasien,kls.namakelas,ru.namaruangan as ruangansblm,'-' as smf,
                                            case when ddp.noregistrasifk is not null THEN dm.kddiagnosa || ', ' || dm.namadiagnosa
                                            else '-' end as namadiagnosa,rpp.objectstatuskeluarfk,ru1.namaruangan as ruanganskrng,
                                             row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum
                                    FROM pasiendaftar_t as pd
                                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                                    LEFT JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                                                    and apd.tglkeluar = rpp.tglpindah
                                                    and rpp.objectstatuskeluarfk = 2
                                    LEFT  JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec and ddp.objectjenisdiagnosafk = 1
                                    LEFT  JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                                    LEFT  JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                                    INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN kelas_m as kls on kls.id = pd.objectkelasfk
                                    LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                                    LEFT JOIN ruangan_m as ru on ru.id = rpp.objectruanganasalfk
                                    LEFT JOIN ruangan_m as ru1 on ru1.id = rpp.objectruanganfk
                                    LEFT JOIN statuskeluar_m as sk1 on sk1.id = pd.objectstatuskeluarfk
                                    WHERE pd.kdprofile = $idProfile and rpp.tglpindah BETWEEN '$tglAwal' and '$tglAkhir' and br.pasiendaftarfk is null
                                    and pm.namapasien ilike '%$namapasien%' $paramNoRm
                                    $paramRuangan
                                    $paramKelas
        ) as x where  x.rownum =1 "));
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataLaporanPasienPulang (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('v_pasienpulang as pp');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpulang', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pp.tglpulang', '<=', $tgl);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            if ($request['idDept'] == '18') {
                $data = $data->wherein('pp.objectdepartemenfk', [18,3,24,27,28]);
            } else {
                $data = $data->where('pp.objectdepartemenfk', '=', $request['idDept']);
            }
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('pp.ruanganid', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('pp.idkelompokpasien', '=', $request['kelompokPasien']);
        }
        if (isset($request['institusiAsalPasien']) && $request['institusiAsalPasien'] != "" && $request['institusiAsalPasien'] != "undefined") {
            $data = $data->where('pp.objectrekananfk', '=', $request['institusiAsalPasien']);
        }
        $data = $data->orderBy('pp.nosep');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'Cepot',
        );
        return $this->respond($result);
    }

    public function CetakLaporanPasienPulang(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $IdLaporan = $this->generateCode(new TempLaporanPasienPulang(),'idlappulang',10,'PP'.$this->getDateTime()->format('ym'), $idProfile);
            foreach ($request['details'] as $item) {
                if($IdLaporan != ''){
                    $data = new TempLaporanPasienPulang();
                    $str = $data->generateNewId();
                    $data->kdprofile = $idProfile;
                    $data->idlappulang = $IdLaporan;
                    $data->norec=$str;
                    $data->tglregistrasi = $item['tglregistrasi'];
                    $data->tglpulang = $item['tglpulang'];
                    $data->nosep = $item['nosep'];
                    $data->tglstruk = $item['tglstruk'];
                    $data->nodaftar = $item['nodaftar'];
                    $data->namapasien = $item['namapasien'];
                    $data->objectruanganfk = $item['ruanganid'];
                    $data->namaruangan = $item['namaruangan'];
                    $data->namakelas = $item['namakelas'];
                    $data->nobilling = $item['nobilling'];
                    $data->nokwitansi = $item['nokwitansi'];
                    $data->totalresep = $item['totalresep'];
                    $data->jumlahbiaya = $item['jumlahbiaya'];
                    $data->diskon = $item['diskon'];
                    $data->namarekanan = $item['namarekanan'];
                    $data->jumlahdeposit = $item['jumlahdeposit'];
                    $data->totalharusbayar = $item['totalharusdibayar'];
                    $data->totalppenjamin = $item['totalppenjamin'];
                    $data->pendapatanlainlain = $item['pendapatanlainlain'];
                    $data->idkelompokpasien = $item['idkelompokpasien'];
                    $data->kelompokpasien = $item['kelompokpasien'];
                    $data->keteranganlainnya = $item['keteranganlainnya'];
                    $data->inap = $item['inap'];
                    $data->save();
//                    $norec=$data->norec;
                }

            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Cetak Laporan Pulang!!!";
        }

        if ($transStatus == 'true') {
            $transMessage = "";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noId" => $IdLaporan,
                "norec" => $data->norec,
                "as" => 'cepotTea',
            );
        } else {
            $transMessage = "Cetak Laporan Pulang!!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noId" => $IdLaporan,
                "norec" => $data->norec,
                "as" => 'cepotTea',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataLaporanPendapatanPoli(Request $request){
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pelayananpasien_t as pp')
            ->LEFTJOIN('strukpelayanan_t as sp', 'pp.strukfk', '=', 'sp.norec')
            ->LEFTJOIN('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->LEFTJOIN('strukpelayananpenjamin_t as sppj', 'sp.norec', '=', 'sppj.nostrukfk')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'pp.noregistrasifk')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->JOIN('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->JOIN('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->JOIN('kelompokpasien_m as klp', 'klp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->select('apd.objectruanganfk', 'ru.namaruangan', 'apd.statuskunjungan', 'pg.namalengkap', 'pd.objectkelompokpasienlastfk',
                'klp.kelompokpasien', 'pd.tglregistrasi', 'pd.noregistrasi', 'ps.nocm', 'ps.namapasien', 'ps.tgllahir',
                DB::raw('sum(case when pr.id =395 then pp.jumlah else 0 end) as jmlkarcis,
                        sum(case when pr.id =395 then pp.hargajual* pp.jumlah else 0 end) as karcis,
                        sum(case when pr.id =10013116  then pp.jumlah else 0 end) as jmlembos,
                        sum(case when pr.id =10013116  then pp.hargajual* pp.jumlah else 0 end) as embos,
                        sum(case when kp.id = 26 then pp.jumlah else 0 end) as jmlkonsul,
                        sum(case when kp.id = 26 then pp.hargajual* pp.jumlah else 0 end) as konsul,
                        sum(case when kp.id in (1,2,3,4,8,9,10,11,13,14) then pp.jumlah else 0 end) as jmltindakan,
                        sum(case when kp.id in (1,2,3,4,8,9,10,11,13,14) then pp.hargajual* pp.jumlah else 0 end) as tindakan,
                        sum((case when pp.hargadiscount is null then 0 else pp.hargadiscount end)* pp.jumlah) as diskon,
                        sum(case when pr.id =395 then pp.hargajual* pp.jumlah else 0 end)
                        +sum(case when pr.id =10013116  then pp.hargajual* pp.jumlah else 0 end)
                        +sum(case when kp.id = 26 then pp.hargajual* pp.jumlah else 0 end)
                        +sum(case when kp.id in (1,2,3,4,8,9,10,11,13,14) then pp.hargajual* pp.jumlah else 0 end)
                        -sum((case when pp.hargadiscount is null then 0 else pp.hargadiscount end)* pp.jumlah) as total')
//                        '(CASE WHEN pp.strukfk is null then "Belum" WHEN sp.nosbmlastfk is null then "Belum" else "Sudah" END )as statusbayar')
            )
            ->where('pp.kdprofile',$idProfile);

//             ->whereNull('apd.statusenabled');
//            ->Where('apd.statusenabled','=',true);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $data = $data->where('pg.id', '=', $request['idDokter']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('pd.objectkelompokpasienlastfk', '=', $request['kelompokPasien']);
        }

        $data = $data->groupBy('apd.objectruanganfk', 'ru.namaruangan', 'pd.objectkelompokpasienlastfk',
            'klp.kelompokpasien', 'pd.tglregistrasi', 'pd.noregistrasi', 'ps.nocm', 'ps.namapasien',
            'ps.tgllahir', 'pg.namalengkap', 'apd.statuskunjungan', 'pp.strukfk', 'sp.nosbmlastfk');
        $data = $data->distinct();
        $data = $data->orderBy('pd.noregistrasi');

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'egie@',
        );
        return $this->respond($result);
    }
    public function getLaporanLayanan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $idDept =  ' ';
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $idDept =  ' and ru.objectdepartemenfk =  ' . $request['idDept'];
        }
        $idRuangan =  ' ';
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $idRuangan = ' and ru.id = ' . $request['idRuangan'];
        }
        $idKelompok = ' ';
        if (isset($request['idKelompok']) && $request['idKelompok'] != "" && $request['idKelompok'] != "undefined") {
            if ($idKelompok == 135 || $idKelompok == '135'){
                $idKelompok = 'and pd.objectkelompokpasienlastfk in (1,5,3)';
            }else{
                $idKelompok = 'and pd.objectkelompokpasienlastfk = '. $request['idKelompok'];
            }
        }
        $idDokter = ' ';
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $idDokter = 'and pg.id  = '. $request['idDokter'];
        }
        $tindakan = ' ';
        if (isset($request['tindakan']) && $request['tindakan'] != "" && $request['tindakan'] != "undefined") {
            $tindakan = ' and pr.id = ' . $request['tindakan'];
        }
        $kelas = ' ';
        if (isset($request['kelas']) && $request['kelas'] != "" && $request['kelas'] != "undefined") {
            $kelas = ' and kl.id = ' . $request['kelas'];
        }
        $PetugasPe= ' ';
        if (isset($request['PetugasPe']) && $request['PetugasPe'] != "" && $request['PetugasPe'] != "undefined") {
            $PetugasPe = ' and ppp.objectjenispetugaspefk =' . $request['PetugasPe'];
        }
        if ($request['kondisi'] == ''){
            $data = \DB::table('pasiendaftar_t as pd')
                ->leftJOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
                ->leftJOIN ('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
                ->leftJoin ('pelayananpasienpetugas_t as ppp','ppp.pelayananpasien','=','pp.norec')
                ->leftJoin ('pegawai_m as pg','pg.id','=','ppp.objectpegawaifk')
                ->leftJOIN ('produk_m as pr','pr.id','=','pp.produkfk')
                ->leftJOIN ('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
                ->leftJOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
                ->leftJOIN ('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
                ->leftJOIN ('kelas_m as kl','kl.id','=','apd.objectkelasfk')
                ->leftJOIN ('strukpelayanan_t as sp','sp.noregistrasifk','=','pd.norec')
                ->leftJOIN ('strukbuktipenerimaan_t as sbm','sbm.norec','=','sp.nosbmlastfk')
                ->leftjoin ('mapruangantoproduk_m as mrtp',function($join)
                {
                    $join->on('mrtp.objectprodukfk','=','pr.id');
                    $join->on('mrtp.objectruanganfk','=','apd.objectruanganfk');
                })
                ->leftjoin ('ruangan_m as ru1','ru1.id','=','mrtp.objectruanganfk')
                ->leftJOIN ('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
                ->leftJOIN ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
                ->select('pp.norec','pp.tglpelayanan','apd.objectruanganfk','ru.namaruangan','kl.namakelas','pg.id as iddokter','pg.namalengkap',
                    'pd.noregistrasi', 'ps.nocm','ps.namapasien',
                    'pr.namaproduk','pp.jumlah','kps.kelompokpasien',
                    DB::raw('case when ru.objectdepartemenfk in (16,35) then \'Y\' ELSE \'N\' END as inap,
                           case when rk.namarekanan is not null then rk.namarekanan else \' - \' end as namarekanan,
                           case when pp.hargajual is not null then pp.hargajual else 0 end as harga,
                           case when pd.nosbmlastfk is null then \'n\' else \'y\' end as sbm'
                    ))
                ->where('pd.kdprofile',$idProfile)
//            ->whereNull('sp.statusenabled')
                ->Where('pg.objectjenispegawaifk','=',1)
                ->whereNotIn('djp.objectjenisprodukfk', [97,283])
//                ->whereNotIn('djp.id',[58,155,161,167,476,477,149,1435,1440])
                ->whereNotIn('pr.id',[395])
                ->where('mrtp.statusenabled','=',true)
//                ->whereNotIn('ru1.objectdepartemenfk',[3,27])
                ->groupBy( 'pp.norec','pp.tglpelayanan',
                    'apd.objectruanganfk',
                    'ru.namaruangan',
                    'kl.namakelas',
                    'pg.id',
                    'pg.namalengkap',
                    'pd.noregistrasi',
                    'ps.nocm',
                    'ps.namapasien',
                    'pr.namaproduk',
                    'pp.jumlah',
                    'kps.kelompokpasien','ru.objectdepartemenfk',
                    'rk.namarekanan','pp.hargajual',
                    'pd.nosbmlastfk');

            if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
                $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
            }
            if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
                $tgl = $request['tglAkhir'];//." 23:59:59";
                $data = $data->where('pp.tglpelayanan', '<=', $tgl);
            }
            if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
                $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
            }
            if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
                $data = $data->where('ru.id', '=', $request['idRuangan']);
            }
            if (isset($request['idKelompok']) && $request['idKelompok'] != "" && $request['idKelompok'] != "undefined") {
                if ($request['idKelompok'] == 135 || $request['idKelompok']=='135'){
                    $data = $data->whereIn('pd.objectkelompokpasienlastfk',[1,5,3]);
                }else{
                    $data = $data->where('pd.objectkelompokpasienlastfk', '=', $request['idKelompok']);
                }
            }
            if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
                $data = $data->where('pg.id', '=', $request['idDokter']);
            }
            if (isset($request['tindakan']) && $request['tindakan'] != "" && $request['tindakan'] != "undefined") {
                $data = $data->where('pr.id', '=', $request['tindakan']);
            }
            if (isset($request['kelas']) && $request['kelas'] != "" && $request['kelas'] != "undefined") {
                $data = $data->where('kl.id', '=', $request['kelas']);
            }
            if (isset($request['PetugasPe']) && $request['PetugasPe'] != "" && $request['PetugasPe'] != "undefined") {
                $data = $data->where('ppp.objectjenispetugaspefk', '=', $request['PetugasPe']);
            }
//            $data = $data->distinct();
            $data = $data->get();

            $results =array();
            foreach ($data as $item) {
                $results[]=array(
                    'tglpelayanan'=>$item->tglpelayanan,
                    'nocm'=>$item->nocm,
                    'noregistrasi'=>$item->noregistrasi,
                    'namapasien'=>$item->namapasien,
                    'namakelas'=>$item->namakelas,
                    'kelompokpasien'=>$item->kelompokpasien,
                    'rekanan'=>$item->namarekanan,
                    'inap'=>$item->inap,
                    'ruangan'=>$item->namaruangan,
                    'layanan'=>$item->namaproduk,
                    'tarif'=>$item->harga,
                    'jumlah'=>$item->jumlah,
                    'statusbayar'=>$item->sbm,
                    'iddokter'=>$item->iddokter,
                    'dokter'=>$item->namalengkap,
                );
            }
        }elseif ($request['kondisi'] == 1){

            $data = DB::select(DB::raw("select pp.norec,pp.tglpelayanan,apd.objectruanganfk,ru.namaruangan,kl.namakelas,pg.id as iddokter,pg.namalengkap,
                 pd.noregistrasi,ps.nocm,upper(ps.namapasien) as namapasien,case when ru.objectdepartemenfk in (16,35) then 'y' else 'n' end as inap,
                 pr.namaproduk,pp.jumlah,case when pp.hargajual is not null then pp.hargajual else 0 end as harga,
                 case when pd.nosbmlastfk is null then 'n' else 'y' end as sbm,sf.jammasuk,
                 case when rk.namarekanan is not null then rk.namarekanan else ' - ' end as namarekanan,kps.kelompokpasien
                 from pasiendaftar_t as pd
                 inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                 left join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                 left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pp.norec
                 left join pegawai_m as pg on pg.id=ppp.objectpegawaifk
                 inner join produk_m as pr on pr.id=pp.produkfk
                 inner join detailjenisproduk_m as djp on djp.id=pr.objectdetailjenisprodukfk
                 inner join pasien_m as ps on ps.id=pd.nocmfk
                 left join strukpelayanan_t as sp  on sp.noregistrasifk=pd.norec
                 left join strukbuktipenerimaan_t as sbm  on sbm.norec=sp.nosbmlastfk
                 left join ruangan_m as ru on ru.id=apd.objectruanganfk
                 left join kelas_m as kl on kl.id = apd.objectkelasfk
                 left join mapruangantoproduk_m as mrtp on mrtp.objectprodukfk = pr.id and mrtp.objectruanganfk=apd.objectruanganfk
                 left join ruangan_m as ru1 on ru1.id = mrtp.objectruanganfk
                INNER JOIN pegawaijadwalkerja_m as jdw on jdw.objectpegawaifk=ppp.objectpegawaifk
                INNER JOIN kalender_s as kld on kld.id=jdw.objecttanggalfk
                INNER JOIN shiftkerja_m as sf on sf.id=jdw.objectshiftfk
                left join  rekanan_m as rk on rk.id = pd.objectrekananfk
                left join kelompokpasien_m as kps on kps.id = pd.objectkelompokpasienlastfk
                where pd.kdprofile = $idProfile and pg.objectjenispegawaifk = 1 and djp.objectjenisprodukfk not in  (97,283)
                --and ru1.objectdepartemenfk not in (3,27)

                and pr.id not in (395) and mrtp.statusenabled =true
                and pp.tglpelayanan >= '$tglAwal' and pp.tglpelayanan <= '$tglAkhir'
                $idDept
                $idKelompok
                $idRuangan
                $idDokter
                $tindakan
                $kelas
                $PetugasPe
                and (pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jammasuk,'.',':'),'YYYY-MM-DD HH24:MI')
                and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jampulang,'.',':'),'YYYY-MM-DD HH24:MI') )
                GROUP BY pp.norec,pp.tglpelayanan,
                 apd.objectruanganfk,
                 ru.namaruangan,
                 kl.namakelas,
                 pg.id,
                 pg.namalengkap,
                 pd.noregistrasi,
                 ps.nocm,
                 ps.namapasien,
                 pr.namaproduk,
                 pp.jumlah,
                 kps.kelompokpasien,ru.objectdepartemenfk,
                rk.namarekanan,pp.hargajual,
                pd.nosbmlastfk,
                sf.jammasuk
                order by pp.tglpelayanan")
            );
            $results =array();
            foreach ($data as $item) {
                $results[]=array(
                    'tglpelayanan'=>$item->tglpelayanan,
                    'nocm'=>$item->nocm,
                    'noregistrasi'=>$item->noregistrasi,
                    'namapasien'=>$item->namapasien,
                    'namakelas'=>$item->namakelas,
                    'kelompokpasien'=>$item->kelompokpasien,
                    'rekanan'=>$item->namarekanan,
                    'inap'=>$item->inap,
                    'ruangan'=>$item->namaruangan,
                    'layanan'=>$item->namaproduk,
                    'tarif'=>$item->harga,
                    'jumlah'=>$item->jumlah,
                    'statusbayar'=>$item->sbm,
                    'iddokter'=>$item->iddokter,
                    'dokter'=>$item->namalengkap,
                );
            }
        }elseif ($request['kondisi'] == 2){

            $data = DB::select(DB::raw("select pp.norec, pp.tglpelayanan,apd.objectruanganfk,ru.namaruangan,kl.namakelas,pg.id as iddokter,pg.namalengkap,
                 pd.noregistrasi,ps.nocm,upper(ps.namapasien) as namapasien,case when ru.objectdepartemenfk in (16,35) then 'y' else 'n' end as inap,
                 pr.namaproduk,pp.jumlah,case when pp.hargajual is not null then pp.hargajual else 0 end as harga,
                 case when pd.nosbmlastfk is null then 'n' else 'y' end as sbm,sf.jammasuk,
                  case when rk.namarekanan is not null then rk.namarekanan else ' - ' end as namarekanan,kps.kelompokpasien
                 from pasiendaftar_t as pd
                 inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                 left join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                 left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pp.norec
                 left join pegawai_m as pg on pg.id=ppp.objectpegawaifk
                 inner join produk_m as pr on pr.id=pp.produkfk
                 inner join detailjenisproduk_m as djp on djp.id=pr.objectdetailjenisprodukfk
                 inner join pasien_m as ps on ps.id=pd.nocmfk
                 left join strukpelayanan_t as sp  on sp.noregistrasifk=pd.norec
                 left join strukbuktipenerimaan_t as sbm  on sbm.norec=sp.nosbmlastfk
                 left join ruangan_m as ru on ru.id=apd.objectruanganfk
                 left join kelas_m as kl on kl.id = apd.objectkelasfk
                 left join mapruangantoproduk_m as mrtp on mrtp.objectprodukfk = pr.id and mrtp.objectruanganfk=apd.objectruanganfk
                 left join ruangan_m as ru1 on ru1.id = mrtp.objectruanganfk
                INNER JOIN pegawaijadwalkerja_m as jdw on jdw.objectpegawaifk=ppp.objectpegawaifk
                INNER JOIN kalender_s as kld on kld.id=jdw.objecttanggalfk
                INNER JOIN shiftkerja_m as sf on sf.id=jdw.objectshiftfk
                 left join  rekanan_m as rk on rk.id = pd.objectrekananfk
                 left join kelompokpasien_m as kps on kps.id = pd.objectkelompokpasienlastfk
                where pd.kdprofile = $idProfile and pg.objectjenispegawaifk = 1 and djp.objectjenisprodukfk not in (97,283)
                 --and ru1.objectdepartemenfk not in (3,27)

                and pr.id not in (395) and mrtp.statusenabled =true
                and pp.tglpelayanan >= '$tglAwal' and pp.tglpelayanan <= '$tglAkhir'
                $idDept
                $idKelompok
                $idRuangan
                $idDokter
                $tindakan
                $kelas
                $PetugasPe
                and ((pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' 00:00','YYYY-MM-DD HH24:MI')
                        and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jammasuk,'.',':'),'YYYY-MM-DD HH24:MI')- interval '1' minute  )
                or (pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jampulang,'.',':'),'YYYY-MM-DD HH24:MI') + interval '1' minute
                        and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' 23:59:59','YYYY-MM-DD HH24:MI') ))
                GROUP BY pp.norec,pp.tglpelayanan,
                 apd.objectruanganfk,
                 ru.namaruangan,
                 kl.namakelas,
                 pg.id,
                 pg.namalengkap,
                 pd.noregistrasi,
                 ps.nocm,
                 ps.namapasien,
                 pr.namaproduk,
                 pp.jumlah,
                 kps.kelompokpasien,ru.objectdepartemenfk,
                rk.namarekanan,pp.hargajual,
                pd.nosbmlastfk,
                sf.jammasuk
                order by pp.tglpelayanan")
            );
            $results =array();
            foreach ($data as $item) {
                $results[]=array(
                    'tglpelayanan'=>$item->tglpelayanan,
                    'nocm'=>$item->nocm,
                    'noregistrasi'=>$item->noregistrasi,
                    'namapasien'=>$item->namapasien,
                    'namakelas'=>$item->namakelas,
                    'kelompokpasien'=>$item->kelompokpasien,
                    'rekanan'=>$item->namarekanan,
                    'inap'=>$item->inap,
                    'ruangan'=>$item->namaruangan,
                    'layanan'=>$item->namaproduk,
                    'tarif'=>$item->harga,
                    'jumlah'=>$item->jumlah,
                    'statusbayar'=>$item->sbm,
                    'iddokter'=>$item->iddokter,
                    'dokter'=>$item->namalengkap,
                );
            }
        }
        $result = array(
            'data' => $results,
            'message' => 'mn@epic',
        );

        return $this->respond($result);
    }
    public function getJadwalDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pegawai_m as pg')
            ->JOIN ('pegawaijadwalkerja_m as jdw','jdw.objectpegawaifk','=','pg.id')
            ->JOIN ('kalender_s as kld','kld.id','=','jdw.objecttanggalfk')
            ->JOIN ('shiftkerja_m as sf','sf.id','=','jdw.objectshiftfk')
            ->select('pg.id as iddokter','pg.namalengkap as namadokter','kld.tanggal','kld.namahari','kld.namabulan','sf.jammasuk','sf.jampulang')
            ->where('pg.kdprofile', $idProfile)
            ->Where('pg.objectjenispegawaifk','=',1)
            ->Where('pg.objecttypepegawaifk','=',1);

        if (isset($request['namabulan']) && $request['namabulan'] != "" && $request['namabulan'] != "undefined") {
//            $data = $data->where('kld.namabulan', 'ilike', '%' . $request['namabulan'] . '%');
            $data = $data->where('kld.namabulan', '=', $request['namabulan']);
        }
//        if (isset($request['tanggal']) && $request['tanggal'] != "" && $request['tanggal'] != "undefined") {
//            $data = $data->where('pp.tglpelayanan', '<=');
//        }
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $data = $data->where('pg.id',$request['idDokter']);
        }
//        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
//            $data = $data->where('ru.id',$request['idRuangan']);
//        }
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'cepot',
        );

        return $this->respond($result);
    }
    public function CetakLaporanLayanan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $IdLaporan = $this->generateCode(new TempLaporanLayanan(),'idlaplayanan',10,'LL'.$this->getDateTime()->format('ym'), $idProfile);
            foreach ($request['details'] as $item) {
                if($IdLaporan != ''){
                    $data = new TempLaporanLayanan();
                    $str = $data->generateNewId();
                    $data->idlaplayanan = $IdLaporan;
                    $data->kdprofile = $idProfile;
                    $data->norec=$str;
                    $data->tgllayanan = $item['tglpelayanan'];
                    $data->nocm = $item['nocm'];
                    $data->namapasien = $item['namapasien'];
                    $data->statusinap = $item['inap'];
                    $data->rekanan = $item['rekanan'];
                    $data->unit = $item['ruangan'];
                    $data->kelas = $item['namakelas'];
                    $data->layanan = $item['layanan'];
                    $data->tarif = $item['tarif'];
                    $data->jumlah = $item['jumlah'];
                    $data->paid = $item['statusbayar'];
//                    $data->tglawal = $item['tglawal'];
//                    $data->tglakhir = $item['tglakhir'];
                    $data->iddokter = $item['iddokter'];
                    $data->namadokter = $item['dokter'];
                    $data->kelompokpasien = $item['kelompokpasien'];
                    $data->save();
//                    $norec=$data->norec;
                }

            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Cetak Laporan Layanan!!!";
        }

        if ($transStatus == 'true') {
            $transMessage = "";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noId" => $IdLaporan,
                "norec" => $data->norec,
                "as" => 'cepotTea',
            );
        } else {
            $transMessage = "Cetak Laporan Layanan!!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noId" => $IdLaporan,
                "norec" => $data->norec,
                "as" => 'cepotTea',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataProduk(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->select('pr.id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar','pr.kdproduk')
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.statusenabled', true)
//            ->where('jp.id',97)
            ->orderBy('pr.namaproduk');
//            ->take($req['take'])
//            ->get();
        if(isset($req['namaproduk']) &&
            $req['namaproduk']!="" &&
            $req['namaproduk']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ilike','%'. $req['namaproduk'] .'%' );
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(20);
        $dataProduk = $dataProduk->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kdprofile', $idProfile)
            ->where('ks.statusenabled',true)
            ->get();


        $dataProdukResult=[];
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
                'kdproduk' => $item->kdproduk,
            );
        }

        return $this->respond($dataProdukResult);
    }

    public function GetLaporanPasienBaruLama (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('pasien_m as ps','ps.id','=','pd.nocmfk')
//            ->leftjoin ('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->join ('ruangan_m as rg','rg.id','=','pd.objectruanganlastfk')
            ->leftjoin ('batalregistrasi_t as btl','btl.pasiendaftarfk','=','pd.norec')
            ->leftjoin ('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin ('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->select('ps.namapasien','ps.nocm','pd.noregistrasi','pd.tglregistrasi','pd.statuspasien','rg.namaruangan','kp.kelompokpasien',
                DB::raw("CASE WHEN rk.namarekanan is null then '-' ELSE rk.namarekanan end as penjaminpasien,
                case when
                SUBSTRING( ps.nocm, 1, 3) = '016' or SUBSTRING( ps.nocm, 1, 3) = '008'
                then 'BARU' else 'LAMA' end as statuspatok"))
            ->where('pd.kdprofile', $idProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        if (isset($request['deptId']) && $request['deptId'] != "" && $request['deptId'] != "undefined") {
            $data = $data->where('rg.objectdepartemenfk', '=', $request['deptId']);
        }
        if (isset($request['ruangId']) && $request['ruangId'] != "" && $request['ruangId'] != "undefined") {
            $data = $data->where('pd.objectruanganlastfk', '=', $request['ruangId']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('pd.objectkelompokpasienlastfk', '=', $request['kelompokPasien']);
        }
        if (isset($request['StatusPasien']) && $request['StatusPasien'] != "" && $request['StatusPasien'] != "undefined") {
            $data = $data->where('pd.statuspasien', 'ilike', '%'.$request['StatusPasien'].'%');
        }
//        if (isset($request['jmlRows']) && $request['jmlRows'] != "" && $request['jmlRows'] != "undefined") {
//            $data = $data->take($request['jmlRows']);
//        }
        $data = $data->whereNull('btl.pasiendaftarfk');
        $data = $data->whereNotIn('rg.objectdepartemenfk', [3,27]);
        $data = $data->orderBy('pd.tglregistrasi','Asc');
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Cepot',
        );
        return $this->respond($result);
    }

     public function getDataLaporanRincianPelayanan(Request $request){
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $idDept =  ' ';
        $paramsTgl = 'pd.tglregistrasi';
        $data = [];
        if (isset($request['isTglPulang']) && $request['isTglPulang'] != "" && $request['isTglPulang'] != "undefined") {
            if($request['isTglPulang'] == true){
                   $paramsTgl = 'pd.tglpulang';
            }
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $idDept =  ' and ru.objectdepartemenfk =  ' . $request['idDept'];
        }
        $idRuangan =  ' ';
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $idRuangan = ' and ru.id = ' . $request['idRuangan'];
        }

        if ($request['idDept'] == 16 || $request['idDept'] == '16'){
                $data = DB::select(DB::raw("
                                 select * from ( SELECT convert(varchar, apd.tglmasuk,105) as tglregistrasi,convert(varchar,apd.tglkeluar,105) as tglpulang,
                             convert(varchar,apd.tglmasuk,8) as jamperiksa,ru.namaruangan,pg.namalengkap as dokter,
                             --kp.kelompokpasien as tipepasien,
                             pd.noregistrasi,pm.nocm,pm.namapasien,pm.nohp,convert(varchar, pm.tgllahir, 105) as tgllahir,
                             jk.jeniskelamin,ag.agama,pend.pendidikan,pkr.pekerjaan,sp.statusperkawinan,
                             CASE WHEN pc.ALAMAT IS NULL THEN pm.alamatrmh ELSE REPLACE(REPLACE(pc.ALAMAT, CHAR(13), ''), CHAR(10), '') END AS alamat,
                             CASE WHEN kel.KELURAHANI IS NULL THEN dsk.namadesakelurahan ELSE LTRIM(RTRIM(kel.KELURAHANI)) END AS kelurahan,
                             CASE WHEN kec.KECAMATANI IS NULL THEN kcm.namakecamatan ELSE LTRIM(RTRIM(kec.KECAMATANI)) END AS kecamatan,
                             CASE WHEN kab.KABUPATENI IS NULL THEN kb.namakotakabupaten ELSE LTRIM(RTRIM(kab.KABUPATENI)) END AS kabupaten,
                             pd.tglregistrasi as tglregistrasiReal,pm.tgllahir as tgllahirReal,pd.statuspasien as jenispasien,
                             CASE WHEN ddp.noregistrasifk IS NULL THEN '-' ELSE dg.kddiagnosa + '-' + dg.namadiagnosa END as kodepenyakit,
                             CASE WHEN cus.NAME IS NULL THEN kp.kelompokpasien ELSE cus.NAME END as carabayar,ru2.namaruangan as ruanganakhir,
                                  CASE WHEN ap.jenispeserta IS NULL OR ap.jenispeserta = '' THEN kp.kelompokpasien ELSE ap.jenispeserta END AS tipepasien,
                             row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum,
                              row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum_ru

                FROM pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec 
                INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                INNER JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                INNER JOIN ruangan_m as ru2 on ru2.id = pd.objectruanganlastfk
                INNER JOIN pasien_m as pm on pm.id = pd.nocmfk 
                LEFT JOIN alamat_m as alm on alm.nocmfk = pm.id
                LEFT JOIN desakelurahan_m as dsk on dsk.id = alm.objectdesakelurahanfk
                LEFT JOIN kecamatan_m as kcm on kcm.id = alm.objectkecamatanfk
                LEFT JOIN kotakabupaten_m as kb on kb.id = alm.objectkotakabupatenfk
                LEFT JOIN statusperkawinan_m as sp on sp.id = pm.objectstatusperkawinanfk
                LEFT JOIN pendidikan_m as pend on pend.id = pm.objectpendidikanfk
                LEFT JOIN pekerjaan_m as pkr on pkr.id = pm.objectpekerjaanfk
                INNER JOIN jeniskelamin_m as jk on jk.id = pm.objectjeniskelaminfk
                LEFT JOIN agama_m as ag on ag.id = pm.objectagamafk
                LEFT JOIN pegawai_m as pg on pg.id = pd.objectpegawaifk
                LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                LEFT JOIN pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                LEFT JOIN PASIEN_copy as pc on pc.nocmfk = pm.id
                LEFT JOIN KELURAHANI as kel on kel.KD_KELURAHANI = pc.KD_KELURAHAN
                LEFT JOIN KECAMATANI as kec on kec.KD_KECAMATANI = kel.KD_KECAMATAN
                LEFT JOIN KABUPATENI as kab on kab.KD_KABUPATENI = kec.KD_KABUPATEN
                LEFT JOIN CUSTOMERI as cus on cus.KELOMPOK_ID = pc.KD_ASURANSI
                LEFT JOIN diagnosapasien_t as dp on dp.noregistrasifk = apd.norec
                LEFT JOIN detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
                LEFT JOIN diagnosa_m as dg on dg.id = ddp.objectdiagnosafk 
                LEFT JOIN asuransipasien_m as ap on ap.nocmfk = pm.id
                WHERE br.pasiendaftarfk IS NULL AND pp.noregistrasifk IS NOT NULL AND (ddp.objectjenisdiagnosafk IS NULL OR ddp.objectjenisdiagnosafk = 1)    
                AND  $paramsTgl BETWEEN '$tglAwal' and '$tglAkhir'
                $idDept 
                $idRuangan      
                GROUP BY pd.tglregistrasi, apd.tglmasuk,apd.tglkeluar,apd.tglmasuk,ru.namaruangan,pg.namalengkap,
                         kp.kelompokpasien,cus.NAME,pd.noregistrasi,pm.nocm,pm.namapasien,pm.nohp,pm.tgllahir,
                         jk.jeniskelamin,ag.agama,pend.pendidikan,pkr.pekerjaan,sp.statusperkawinan,
                         pc.ALAMAT,kel.KELURAHANI,kec.KECAMATANI,kab.KABUPATENI,pd.statuspasien,ddp.noregistrasifk,
                         dg.kddiagnosa,dg.namadiagnosa,pm.alamatrmh,dsk.namadesakelurahan,kcm.namakecamatan,
                         kb.namakotakabupaten,ru.namaruangan,ru2.namaruangan,ap.jenispeserta,apd.norec,ddp.tglinputdiagnosa) 
                         as x where  rownum =1 and rownum_ru = 1"));
        }else{
            $data = DB::select(DB::raw("select * from (SELECT convert(varchar, apd.tglmasuk,105) as tglregistrasi,convert(varchar,apd.tglkeluar,105) as tglpulang,
                         convert(varchar,apd.tglmasuk,8) as jamperiksa,ru.namaruangan,pg.namalengkap as dokter,
                         pd.noregistrasi,pm.nocm,pm.namapasien,pm.nohp,convert(varchar, pm.tgllahir, 105) as tgllahir,
                         jk.jeniskelamin,ag.agama,pend.pendidikan,pkr.pekerjaan,sp.statusperkawinan,
                         CASE WHEN pc.ALAMAT IS NULL THEN pm.alamatrmh ELSE REPLACE(REPLACE(pc.ALAMAT, CHAR(13), ''), CHAR(10), '') END AS alamat,
                         CASE WHEN kel.KELURAHANI IS NULL THEN dsk.namadesakelurahan ELSE LTRIM(RTRIM(kel.KELURAHANI)) END AS kelurahan,
                         CASE WHEN kec.KECAMATANI IS NULL THEN kcm.namakecamatan ELSE LTRIM(RTRIM(kec.KECAMATANI)) END AS kecamatan,
                         CASE WHEN kab.KABUPATENI IS NULL THEN kb.namakotakabupaten ELSE LTRIM(RTRIM(kab.KABUPATENI)) END AS kabupaten,
                         pd.tglregistrasi as tglregistrasiReal,pm.tgllahir as tgllahirReal,pd.statuspasien as jenispasien,
                         CASE WHEN ddp.noregistrasifk IS NULL THEN '-' ELSE dg.kddiagnosa + '-' + dg.namadiagnosa END as kodepenyakit,
                         ru2.namaruangan as ruanganakhir,
                         CASE WHEN ap.jenispeserta IS NULL OR ap.jenispeserta = '' THEN kp.kelompokpasien ELSE ap.jenispeserta END AS tipepasien,
                               row_number() over (partition by apd.norec order by ddp.tglinputdiagnosa asc) as rownum 
                FROM pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk                
                LEFT JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                INNER JOIN ruangan_m as ru2 on ru2.id = pd.objectruanganlastfk
                INNER JOIN pasien_m as pm on pm.id = pd.nocmfk 
                LEFT JOIN alamat_m as alm on alm.nocmfk = pm.id
                LEFT JOIN desakelurahan_m as dsk on dsk.id = alm.objectdesakelurahanfk
                LEFT JOIN kecamatan_m as kcm on kcm.id = alm.objectkecamatanfk
                LEFT JOIN kotakabupaten_m as kb on kb.id = alm.objectkotakabupatenfk
                LEFT JOIN statusperkawinan_m as sp on sp.id = pm.objectstatusperkawinanfk
                LEFT JOIN pendidikan_m as pend on pend.id = pm.objectpendidikanfk
                LEFT JOIN pekerjaan_m as pkr on pkr.id = pm.objectpekerjaanfk
                INNER JOIN jeniskelamin_m as jk on jk.id = pm.objectjeniskelaminfk
                LEFT JOIN agama_m as ag on ag.id = pm.objectagamafk
                LEFT JOIN pegawai_m as pg on pg.id = pd.objectpegawaifk
                LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                LEFT JOIN pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                LEFT JOIN PASIEN_copy as pc on pc.nocmfk = pm.id
                LEFT JOIN KELURAHANI as kel on kel.KD_KELURAHANI = pc.KD_KELURAHAN
                LEFT JOIN KECAMATANI as kec on kec.KD_KECAMATANI = kel.KD_KECAMATAN
                LEFT JOIN KABUPATENI as kab on kab.KD_KABUPATENI = kec.KD_KABUPATEN
                LEFT JOIN CUSTOMERI as cus on cus.KELOMPOK_ID = pc.KD_ASURANSI
                LEFT JOIN diagnosapasien_t as dp on dp.noregistrasifk = apd.norec
                LEFT JOIN detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
                LEFT JOIN diagnosa_m as dg on dg.id = ddp.objectdiagnosafk 
                LEFT JOIN asuransipasien_m as ap on ap.nocmfk = pm.id
                WHERE br.pasiendaftarfk IS NULL AND pp.noregistrasifk IS NOT NULL AND (ddp.objectjenisdiagnosafk IS NULL OR ddp.objectjenisdiagnosafk IN (1,5))
                AND  $paramsTgl BETWEEN '$tglAwal' and '$tglAkhir'
                $idDept 
                $idRuangan      
                GROUP BY pd.tglregistrasi, apd.tglmasuk, apd.tglkeluar,apd.tglmasuk,ru.namaruangan,pg.namalengkap,
                         kp.kelompokpasien,cus.NAME,pd.noregistrasi,pm.nocm,pm.namapasien,pm.nohp,pm.tgllahir,
                         jk.jeniskelamin,ag.agama,pend.pendidikan,pkr.pekerjaan,sp.statusperkawinan,
                         pc.ALAMAT,kel.KELURAHANI,kec.KECAMATANI,kab.KABUPATENI,pd.statuspasien,ddp.noregistrasifk,
                         dg.kddiagnosa,dg.namadiagnosa,pm.alamatrmh,dsk.namadesakelurahan,kcm.namakecamatan,
                         kb.namakotakabupaten,ru.namaruangan,ru2.namaruangan,ap.jenispeserta,apd.norec,ddp.tglinputdiagnosa,apd.norec,ddp.tglinputdiagnosa) as x where  rownum =1 "));
        }

        return $this->respond($data);
    }

    public function getSensusPasienRawatInap(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();

        $tglAwal=$filter['tglAwal'];

        $tglAkhir=$filter['tglAkhir'];

        $kelas = ' ';
        if (isset($filter['idkelas']) && $filter['idkelas'] != "" && $filter['idkelas'] != "undefined") {
            $kelas = ' AND pd.objectkelasfk = ' . $filter['idkelas'];
        }

        $ruId='';
        if(isset($filter['idRuangan']) && $filter['idRuangan'] != "" && $filter['idRuangan'] != "undefined") {
            $ruId = ' AND ru.id = ' . $filter['idRuangan'];
        }
//        $jmlRow='';
//        if(isset($filter['jmlRow']) && $filter['jmlRow'] != "" && $filter['jmlRow'] != "undefined") {
//            $jmlRow = ' top ' . $filter['jmlRow'];
//        }
        $data=  DB::select(DB::raw("
        select * from
			                (
			select pd.tglregistrasi,
				p.nocm,
				pd.noregistrasi,
				ru.namaruangan,
				p.namapasien,
				kp.kelompokpasien,
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
				case when apd.noregistrasifk is not null THEN dm.kddiagnosa + ', ' + dm.namadiagnosa else '-' end as namadiagnosa
				--,
			--row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum
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
			LEFT JOIN kamar_m as kmr on kmr.id =apd.objectkamarfk
			LEFT JOIN tempattidur_m as tt on tt.id =apd.nobed
			LEFT JOIN alamat_m as alm on alm.nocmfk = p.id
			LEFT JOIN diagnosapasien_t as dt on dt.norec = pd.norec
			LEFT JOIN detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dt.norec
			LEFT JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
			LEFT JOIN jenisdiagnosa_m as jd on jd.id = ddp.objectjenisdiagnosafk
			where pd.kdprofile = $idProfile and br.norec is null
			$ruId
			$kelas
			and pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
			and pd.tglpulang is null
			) as x
			--where x.rownum=1
			order by x.tglregistrasi descph
		"));
        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getDataLaporanPasienPulangNew (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        //        $data = \DB::table('v_pasienpulang as pp')
        $data=  DB::select(DB::raw("
        SELECT        TOP (100) PERCENT pd.tglregistrasi, pd.tglpulang, CASE WHEN (pa.nosep IS NULL) THEN '-' ELSE pa.nosep END AS nosep, pp.strukfk, sp.tglstruk, ps.nocm + ' / ' + pd.noregistrasi AS nodaftar, ps.namapasien,
                         ru.objectdepartemenfk, sp.objectruanganfk, ru.namaruangan, kl.namakelas, pd.noregistrasi AS nobilling, sbm.nosbm AS nokwitansi, SUM(CASE WHEN (djp.objectjenisprodukfk = 97)
                         THEN (((pp.hargajual - CASE WHEN (pp.hargadiscount IS NULL) THEN (0) ELSE pp.hargadiscount END) * pp.jumlah) + CASE WHEN (pp.jasa IS NULL) THEN (0) ELSE pp.jasa END) ELSE (0) END) AS totalresep,
                         SUM(CASE WHEN (pp.produkfk <> 402611) THEN ((pp.jumlah * (pp.hargajual - CASE WHEN (pp.hargadiscount IS NULL) THEN (0) ELSE pp.hargadiscount END)) + CASE WHEN (pp.jasa IS NULL) THEN (0) ELSE pp.jasa END)
                         ELSE (0) END) AS jumlahbiaya, SUM(CASE WHEN (pp.hargadiscount IS NULL) THEN (0) ELSE pp.hargadiscount END * pp.jumlah) AS diskon, CASE WHEN (rk.namarekanan IS NULL)
                         THEN '-' ELSE rk.namarekanan END AS namarekanan, SUM(CASE WHEN (pp.produkfk = 402611) THEN ((pp.jumlah * (pp.hargajual - CASE WHEN (pp.hargadiscount IS NULL) THEN (0) ELSE pp.hargadiscount END))
                         + CASE WHEN (pp.jasa IS NULL) THEN (0) ELSE pp.jasa END) ELSE (0) END) AS jumlahdeposit, sp.totalharusdibayar, CASE WHEN (sp.totalprekanan IS NULL) THEN (0) ELSE sp.totalprekanan END AS totalppenjamin,
                         CASE WHEN (sp.totalbiayatambahan IS NULL) THEN (0) ELSE sp.totalbiayatambahan END AS pendapatanlainlain, rk.id AS objectrekananfk, pd.objectkelompokpasienlastfk AS idkelompokpasien, klp.kelompokpasien,
                         sbm.keteranganlainnya, CASE WHEN ru.objectdepartemenfk IN (16, 35) THEN 'y' ELSE 'n' END AS inap, ru.id AS ruanganid
FROM            dbo.pasiendaftar_t AS pd LEFT OUTER JOIN
                         dbo.antrianpasiendiperiksa_t AS apd ON apd.norec = pd.norec LEFT OUTER JOIN
                         dbo.strukpelayanan_t AS sp ON sp.noregistrasifk = pd.norec LEFT OUTER JOIN
                         dbo.pelayananpasien_t AS pp ON pp.strukfk = sp.norec LEFT OUTER JOIN
                         dbo.strukbuktipenerimaan_t AS sbm ON sp.nosbmlastfk = sbm.norec LEFT OUTER JOIN
                         dbo.pemakaianasuransi_t AS pa ON pa.noregistrasifk = pd.norec LEFT OUTER JOIN
                         dbo.pegawai_m AS pg ON pg.id = apd.objectpegawaifk LEFT OUTER JOIN
                         dbo.ruangan_m AS ru ON ru.id = pd.objectruanganlastfk LEFT OUTER JOIN
                         dbo.produk_m AS pr ON pr.id = pp.produkfk INNER JOIN
                         dbo.detailjenisproduk_m AS djp ON djp.id = pr.objectdetailjenisprodukfk LEFT OUTER JOIN
                         dbo.pasien_m AS ps ON ps.id = pd.nocmfk LEFT OUTER JOIN
                         dbo.kelompokpasien_m AS klp ON klp.id = pd.objectkelompokpasienlastfk LEFT OUTER JOIN
                         dbo.kelas_m AS kl ON kl.id = pd.objectkelasfk LEFT OUTER JOIN
                         dbo.rekanan_m AS rk ON rk.id = pd.objectrekananfk
where pd.kdprofile = $idProfile
GROUP BY pp.strukfk, pd.tglregistrasi, pa.nosep, pd.tglpulang, sp.tglstruk, ps.nocm, pd.noregistrasi, ps.namapasien, sp.objectruanganfk, ru.namaruangan, kl.namakelas, sp.nostruk, sbm.nosbm, rk.id, rk.namarekanan, sp.totalharusdibayar,
                          sp.totalprekanan, sp.totalbiayatambahan, pd.objectkelompokpasienlastfk, klp.kelompokpasien, sbm.keteranganlainnya, ru.objectdepartemenfk, ru.id
ORDER BY nosep
        "));


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpulang', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pp.tglpulang', '<=', $tgl);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            if ($request['idDept'] == '18') {
                $data = $data->wherein('pp.objectdepartemenfk', [18,3,24,27,28]);
            } else {
                $data = $data->where('pp.objectdepartemenfk', '=', $request['idDept']);
            }
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('pp.ruanganid', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('pp.idkelompokpasien', '=', $request['kelompokPasien']);
        }
        if (isset($request['institusiAsalPasien']) && $request['institusiAsalPasien'] != "" && $request['institusiAsalPasien'] != "undefined") {
            $data = $data->where('pp.objectrekananfk', '=', $request['institusiAsalPasien']);
        }
        $data = $data->orderBy('pp.nosep');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'Cepot',
        );
        return $this->respond($result);
    }
    public function getKinerjaPelayananRanap(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $bulan = $request['bulan'];
        $dateStart = Carbon::now();
        $dayInMonth = array();
        $type = CAL_GREGORIAN;
        $month = Carbon::parse($bulan)->format('m'); // Month ID, 1 through to 12.

        $year = Carbon::parse($bulan)->format('Y'); //date('Y'); // Year in 4 digit 2009 format.
        $day_count = cal_days_in_month($type, $month, $year); // Get the amount of days\

        for ($i = 1; $i <= $day_count; $i++) {
            $date = $year.'/'.$month.'/'.$i; //format date
            $get_name = date('l', strtotime($date)); //get week day
            $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

            //if not a weekend add day to array
//            if($day_name != 'Sun' && $day_name != 'Sat'){
            $strLength= strlen($i);
            if($strLength  == 1){
                $i = '0'.$i;
            }
//            return $this->respond($countDay);
            $dayInMonth[] = Carbon::parse($bulan)->format('Y').'-'.$month.'-'.$i;// date ('Y-'.$month.'-'.$i);
//            }
        }

        $kamar =  DB::select(DB::raw("select count(x.idkelas) as tt,x.namakelas ,x.idkelas,
            0 as ld,0 as hp,0 as jmlpasienkeluar,0 as bor,0 as los ,0 as toi, 0 as bto, 0 as ndr,0 as gdr
          from (
            SELECT
                ru.id AS idruangan,  ru.namaruangan,
                km.id AS idkamar,
                km.namakamar,
                kl.id AS idkelas,
                kl.namakelas
            FROM
                tempattidur_m AS tt
            LEFT JOIN kamar_m AS km ON km.id = tt.objectkamarfk
            LEFT JOIN ruangan_m AS ru ON ru.id = km.objectruanganfk
            LEFT JOIN kelas_m AS kl ON kl.id = km.objectkelasfk
            WHERE tt.kdprofile = $idProfile and ru.objectdepartemenfk IN (16, 35)
            AND ru.statusenabled = true
            AND km.statusenabled = true
            AND tt.statusenabled = true
            ) as x
            group by x.namakelas,x.idkelas"));

        $firstDay = $bulan.'-01';
        $lastDay = $bulan.'-'. $day_count;
//        return $this->respond($firstDay);
        $pasien = DB::select(DB::raw(" SELECT
                pd.noregistrasi,
                pd.tglregistrasi,
                pd.tglpulang,
                 pd.objectkelasfk,
                    date_part('DAY', age(pd.tglregistrasi, pd.tglpulang)) as hari
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                WHERE pd.kdprofile = $idProfile and
                    ru.objectdepartemenfk = 16
               -- AND pd.tglpulang BETWEEN '$firstDay' and '$lastDay'
                order by pd.tglpulang asc
                "));
        $LD = DB::select(DB::raw(" SELECT
                pd.noregistrasi,
                pd.tglregistrasi,
                pd.tglpulang,
                 pd.objectkelasfk,
                 date_part('DAY', age(pd.tglregistrasi, pd.tglpulang)) as hari
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                WHERE pd.kdprofile = $idProfile and
                    ru.objectdepartemenfk = 16
              AND pd.tglregistrasi BETWEEN '$firstDay' and '$lastDay'
                order by pd.tglpulang asc
                "));

        $dataMeninggal = DB::select(DB::raw("select count(x.noregistrasi) as jumlahmeninggal, x.bulanregis,
                count(case when x.objectkondisipasienfk = '6' then 1 end ) AS jumlahlebih48 FROM
                (
                select noregistrasi,date_part('month',tglregistrasi)  as bulanregis ,statuskeluar,kondisipasien,objectkondisipasienfk
                from pasiendaftar_t
                join statuskeluar_m on statuskeluar_m.id =pasiendaftar_t.objectstatuskeluarfk
                left join kondisipasien_m on kondisipasien_m.id =pasiendaftar_t.objectkondisipasienfk
                where pasiendaftar_t.kdprofile = $idProfile and objectstatuskeluarfk =5
                and  tglregistrasi BETWEEN '$firstDay' and '$lastDay'
                ) as x
                GROUP BY x.bulanregis;"));
//        $dayInMonth = [ '2019-12-30'];
        $jmlHP = 0 ;
        $i = 0;
        $data = [];
        foreach ($dayInMonth as $day){
            foreach ($pasien as $item){
                foreach ($kamar as $kamarss){
                    if($item->tglpulang != null){
                        if(Carbon::parse($item->tglregistrasi)->format('Y-m-d') <= date($dayInMonth[$i])
                            && date($dayInMonth[$i]) <= Carbon::parse($item->tglpulang)->format('Y-m-d')
                            && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                            $kamarss->hp =(int)  $kamarss->hp + 1;
                        }
                    }else{
                        if(Carbon::parse($item->tglregistrasi)->format('Y-m-d') <= date($dayInMonth[$i])
                            && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                            $kamarss->hp =(int)  $kamarss->hp + 1;
                        }
                    }

                }
            }
            $i = $i+1;
        }
        foreach ($kamar as $kamarss) {
            foreach ($LD as $item) {
                if( (int) $item->objectkelasfk == (int) $kamarss->idkelas){
                    $kamarss->ld = (int)$kamarss->ld  + (int)$item->hari;
                    $kamarss->jmlpasienkeluar = (int) $kamarss->jmlpasienkeluar  +1;
                }
            }
        }
        foreach ($kamar as $item) {
            /** @var  $bor = (Jumlah hari perawatn RS dibagi ( jumlah TT x Jumlah hari dalam satu periode ) ) x 100 % */
            $item->bor = ((int)$item->hp / ((float)$item->tt * (float)$day_count)) * 100;//$numday['jumlahhari']));

            /** @var  $alos = (Jumlah Lama Dirawat dibagi Jumlah pasien Keluar (Hidup dan Mati) */
            if ((int)$item->jmlpasienkeluar > 0){
                $item->los = (int)$item->ld / (int)$item->jmlpasienkeluar;
            }

            /** @var  $toi = (Jumlah TT X Periode) - Hari Perawatn DIBAGI Jumlah pasien Keluar (Hidup dan Mati)*/
            if ( (int)$item->jmlpasienkeluar > 0){
                $item->toi =  (( (float)$item->tt  *  (float)$day_count) - (int)$item->hp)  /(int)$item->jmlpasienkeluar ;
            }

            /** @var  $bto = Jumlah pasien Keluar (Hidup dan Mati) DIBAGI Jumlah tempat tidur */
            $item->bto = (int)$item->jmlpasienkeluar / (float)$item->tt;

            if(count($dataMeninggal)> 0 ) {
                foreach ($dataMeninggal as $itemDead) {
                    /** @var  $gdr = (Jumlah Mati dibagi Jumlah pasien Keluar (Hidup dan Mati) */
                    $item->gdr = (int)$itemDead->jumlahmeninggal * 1000 / (int)$item->jmlpasienkeluar;

                    /** @var  $NDR = (Jumlah Mati > 48 Jam dibagi Jumlah pasien Keluar (Hidup dan Mati) */
                    $item->ndr = (int)$itemDead->jumlahlebih48 * 1000 / (int)$item->jmlpasienkeluar;
                }
            }
        }
        foreach ($kamar as $key => $row) {
            $count[$key] = $row->namakelas;
        }
        array_multisort($count, SORT_ASC, $kamar);
        $result = array(
            'data' => $kamar,
            'by' => 'er@epic'
        );

        return $this->respond($result);
    }

    public function getKinerjaPelayananRanapTahunan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tahun = $request['tahun'];
        $year = [];
        for($m=1; $m<=12; ++$m){
            $year[]= $m;//date('F', mktime(0, 0, 0, $m, 1));
        }
//        $stat = false;
//        if('Desember' <= 'February'&& 'February' <='January' ){
//            $stat =true;
//        }
//        return $this->respond($year);
//        return $this->respond($stat);
        $kamar =  DB::select(DB::raw("select count(x.idkelas) as tt,x.namakelas ,x.idkelas,
          0 as januari_hp, 0 as januari_bor,
          0 as februari_hp, 0 as februari_bor,
          0 as maret_hp, 0 as maret_bor,
          0 as april_hp, 0 as april_bor,
          0 as mei_hp, 0 as mei_bor,
          0 as juni_hp, 0 as juni_bor,
          0 as juli_hp, 0 as juli_bor,
          0 as agustus_hp, 0 as agustus_bor,
          0 as september_hp, 0 as september_bor,
          0 as oktober_hp, 0 as oktober_bor,
          0 as november_hp, 0 as november_bor,
          0 as desember_hp, 0 as desember_bor
          from (
            SELECT
                ru.id AS idruangan,  ru.namaruangan,
                km.id AS idkamar,
                km.namakamar,
                kl.id AS idkelas,
                kl.namakelas
            FROM
                tempattidur_m AS tt
            LEFT JOIN kamar_m AS km ON km.id = tt.objectkamarfk
            LEFT JOIN ruangan_m AS ru ON ru.id = km.objectruanganfk
            LEFT JOIN kelas_m AS kl ON kl.id = km.objectkelasfk
            WHERE tt.kdprofile = $idProfile and ru.objectdepartemenfk IN (16, 35)
            AND ru.statusenabled = true
            AND km.statusenabled = true
            AND tt.statusenabled = true
            ) as x
            group by x.namakelas,x.idkelas"));


        $pasien = DB::select(DB::raw("
		    SELECT
                    pd.objectkelasfk,
                    pd.noregistrasi,
                    pd.tglregistrasi,
                    pd.tglpulang,
                    date_part('month',pd.tglpulang) AS bulan,
                    DATE_PART('day', age(pd.tglregistrasi,pd.tglpulang)) AS hari,
                    kls.namakelas
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                JOIN kelas_m AS kls ON kls.id = pd.objectkelasfk
                WHERE pd.kdprofile = $idProfile and
                    ru.objectdepartemenfk = 16
--              AND format(pd.tglpulang,'yyyy') ='$tahun'

                "));
        $data= [];
        foreach ($pasien as $item){
            foreach ($kamar as $kamarss){
                $tglRegis = (int)  Carbon::parse($item->tglregistrasi)->format('m');
                $tglPulang = (int)  Carbon::parse($item->tglpulang)->format('m');

                if( $tglRegis <=   $year[0]
                    && $year[0] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                     $kamarss->januari_hp =(int)  $kamarss->januari_hp + 1;
                }

                if( $tglRegis <=   $year[1]
                    && $year[1] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->februari_hp =(int)  $kamarss->februari_hp + 1;

                }
                if($tglRegis <=   $year[2]
                    && $year[2] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->maret_hp =(int)  $kamarss->maret_hp + 1;
                }
                if( $tglRegis <=   $year[3]
                    && $year[3] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->april_hp =(int)  $kamarss->april_hp + 1;
                }
                if($tglRegis <=   $year[4]
                    && $year[4] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->mei_hp =(int)  $kamarss->mei_hp + 1;
                }
                if($tglRegis <=   $year[5]
                    && $year[5] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->juni_hp =(int)  $kamarss->juni_hp + 1;
                }
                if( $tglRegis <=   $year[6]
                    && $year[6] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->juli_hp =(int)  $kamarss->juli_hp + 1;
                }
                if( $tglRegis <=   $year[7]
                    && $year[7] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->agustus_hp =(int)  $kamarss->agustus_hp + 1;
                }
                if( $tglRegis <=   $year[8]
                    && $year[8] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->september_hp =(int)  $kamarss->september_hp + 1;
                }
                if($tglRegis <=   $year[9]
                    && $year[9] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->oktober_hp =(int)  $kamarss->oktober_hp + 1;
                }
                if( $tglRegis <=   $year[10]
                    && $year[10] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->november_hp =(int)  $kamarss->november_hp + 1;
//                    return $this->respond( $kamarss->november_hp );

                } if( $tglRegis <=   $year[11]
                    && $year[11] <= $tglPulang
                    && (int)  $kamarss->idkelas == (int) $item->objectkelasfk ){
                    $kamarss->desember_hp =(int)  $kamarss->desember_hp + 1;
                }

            }
        }


//        return $this->respond($kamarss);
        $lamaRawat = 0;
//        foreach ($kamar as $kamarss) {
//            foreach ($pasien as $item) {
//                if( (int) $item->objectkelasfk == (int) $kamarss->idkelas){
//                    $kamarss->ld = (int)$kamarss->ld  + (int)$item->hari;
//                    $kamarss->jmlpasienkeluar = (int) $kamarss->jmlpasienkeluar  +1;
//                }
//            }
//        }
        foreach ($kamar as $item) {
            /** @var  $bor = (Jumlah hari perawatn RS dibagi ( jumlah TT x Jumlah hari dalam satu periode ) ) x 100 % */
//            $item->bor = ((int)$item->hp / ((float)$item->tt * 12)) * 100;//$numday['jumlahhari']));

            /** @var  $alos = (Jumlah Lama Dirawat dibagi Jumlah pasien Keluar (Hidup dan Mati) */
//            if ((int)$item->jmlpasienkeluar > 0){
//                $item->los = (int)$item->ld / (int)$item->jmlpasienkeluar;
//            }
//
//            /** @var  $toi = (Jumlah TT X Periode) - Hari Perawatn DIBAGI Jumlah pasien Keluar (Hidup dan Mati)*/
//            if ( (int)$item->jmlpasienkeluar > 0){
//                $item->toi =  (( (float)$item->tt  *  (float)$day_count) - (int)$item->hp)  /(int)$item->jmlpasienkeluar ;
//            }
//
//            /** @var  $bto = Jumlah pasien Keluar (Hidup dan Mati) DIBAGI Jumlah tempat tidur */
//            $item->bto = (int)$item->jmlpasienkeluar / (float)$item->tt;
//
//            if(count($dataMeninggal)> 0 ) {
//                foreach ($dataMeninggal as $itemDead) {
//                    /** @var  $gdr = (Jumlah Mati dibagi Jumlah pasien Keluar (Hidup dan Mati) */
//                    $item->gdr = (int)$itemDead->jumlahmeninggal * 1000 / (int)$item->jmlpasienkeluar;
//
//                    /** @var  $NDR = (Jumlah Mati > 48 Jam dibagi Jumlah pasien Keluar (Hidup dan Mati) */
//                    $item->ndr = (int)$itemDead->jumlahlebih48 * 1000 / (int)$item->jmlpasienkeluar;
//                }
//            }
        }
        foreach ($kamar as $key => $row) {
            $count[$key] = $row->namakelas;
        }
        array_multisort($count, SORT_ASC, $kamar);
        $result = array(
            'data' => $kamar,
            'by' => 'er@epic'
        );

        return $this->respond($result);
    }
}
