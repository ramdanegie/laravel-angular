<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 10/4/2019
 * Time: 1:40 PM
 */


namespace App\Http\Controllers\EIS;

use App\Http\Controllers\ApiController;
use App\Master\JenisIndikator;
use App\Master\KelompokTransaksi;
use App\Master\Pasien;
use App\Master\SettingDataFixed;
use App\Master\TargetIndikator;
use App\Transaksi\IndikatorPasienJatuh;
use App\Transaksi\IndikatorRensar;
use App\Transaksi\IndikatorRensarDetail;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use App\Transaksi\BPJSKlaimTxt;
use App\Traits\Valet;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;

class EISController extends ApiController
{
    use Valet;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getPasienRawatJalan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = date('Y-m-d'.' 00:00');
        $tglAkhir = date('Y-m-d'.' 23:59');
        $depRawatJalan = 18;
        $depRehabMedik = 28;
        $data = DB::select(DB::raw("select count(x.noregistrasi) as jumlah  
            from ( select pd.noregistrasi,pd.tglregistrasi
            from pasiendaftar_t as pd 
            inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
            inner join ruangan_m as ru on ru.id = apd.objectruanganfk 
            where pd.kdprofile = $idProfile and ru.objectdepartemenfk ='$depRawatJalan'
            and  pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
            GROUP BY pd.tglregistrasi, pd.noregistrasi) as x
            "));
        foreach ($data as $item){
            $dataResult = array(
                'jumlahData' => $item->jumlah
            );
        }

        $result = array(
            'result' => $dataResult,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function getPasienIGDTerlayani(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = date('Y-m-d'.' 00:00');
        $tglAkhir = date('Y-m-d'.' 23:59');
        $igd= 36;
        $data = DB::select(DB::raw("select count(x.noregistrasi) as jumlah, count (x.terlayani) as  terlayani ,
                count (x.tidakterlayani) as  tidakterlayani 
                from (
                select DISTINCT pd.noregistrasi,pd.tglregistrasi, ps.nocm,ps.namapasien,
                ru.namaruangan, case when pp.norec is null then 'tidak terlayani' end as tidakterlayani,
                case when pp.norec is not null  then 'terlayani' end as terlayani
                 from pasiendaftar_t as pd 
                inner join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec and pd.objectruanganlastfk=ru.id
                left join pelayananpasien_t as pp on pp.noregistrasifk =apd.norec
                inner join pasien_m as ps on ps.id = pd.nocmfk
                where pd.kdprofile = $idProfile and
                ru.id ='$igd'
                and  pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
                GROUP BY pd.tglregistrasi, ps.nocm,ps.namapasien,ru.namaruangan ,pd.noregistrasi, pp.norec
                ) as x;  "));
        foreach ($data as $item){
            if( (float) $item->jumlah > 0){
                $dataResult = array(
                    'jumlahData' => (float)$item->jumlah,
                    'terlayani' => (float)$item->terlayani / (float)$item->jumlah * 100 .' %',
                    'tidakTerlayani' => (float) $item->tidakterlayani /(float) $item->jumlah * 100 .' %'
                );
            }else{
                $dataResult = array(
                    'jumlahData' => 0,
                    'terlayani' => 0 .' %',
                    'tidakTerlayani' =>  0 .' %'
                );
            }
        }

        $result = array(
            'result' => $dataResult,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getPasienRawatInap(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = date('Y-m-d'.' 00:00');
        $tglAkhir = date('Y-m-d'.' 23:59');
        $deptRawatInap = 16;
        $data = DB::select(DB::raw("select count(x.noregistrasi) as jumlah  
            from ( select distinct pd.noregistrasi,pd.tglregistrasi
            from pasiendaftar_t as pd 
            inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
            inner join ruangan_m as ru on ru.id = apd.objectruanganfk
            where pd.kdprofile = $idProfile and ru.objectdepartemenfk ='$deptRawatInap'
            and pd.tglpulang
          --  and  pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
            GROUP BY pd.tglregistrasi, pd.noregistrasi) as x
            "));

        foreach ($data as $item){
            $dataResult = array(
                'jumlahData' => (float) $item->jumlah
            );
        }

        $result = array(
            'result' => $dataResult,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getPasienRadiologi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = date('Y-m-d'.' 00:00');
        $tglAkhir = date('Y-m-d'.' 23:59');
        $depRadiologi = 27;
        $data = DB::select(DB::raw("select count(x.noregistrasi) as jumlah  
            from ( select DISTINCT pd.noregistrasi,pd.tglregistrasi
            from pasiendaftar_t as pd 
            inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
            inner join ruangan_m as ru on ru.id = apd.objectruanganfk
            where pd.kdprofile = $idProfile and ru.objectdepartemenfk = '$depRadiologi'
            and  pd.tglregistrasi BETWEEN   '$tglAwal' and '$tglAkhir'
            GROUP BY pd.tglregistrasi, pd.noregistrasi) as x;
            "));
        foreach ($data as $item){
            $dataResult = array(
                'jumlahData' =>(float)  $item->jumlah
            );
        }

        $result = array(
            'result' => $dataResult,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getPasienLaborat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = date('Y-m-d'.' 00:00');
        $tglAkhir = date('Y-m-d'.' 23:59');
        $depRadiologi = 3;
        $data = DB::select(DB::raw("select count(x.noregistrasi) as jumlah  
            from ( select DISTINCT pd.noregistrasi,pd.tglregistrasi
            from pasiendaftar_t as pd 
            inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
            inner join ruangan_m as ru on ru.id = apd.objectruanganfk
            where pd.kdprofile = $idProfile and ru.objectdepartemenfk = '$depRadiologi'
            and  pd.tglregistrasi BETWEEN   '$tglAwal' and '$tglAkhir'
            GROUP BY pd.tglregistrasi, pd.noregistrasi) as x;
            "));
        foreach ($data as $item){
            $dataResult = array(
                'jumlahData' =>(float)  $item->jumlah
            );
        }

        $result = array(
            'result' => $dataResult,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getPasienOperasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = date('Y-m-d'.' 00:00');
        $tglAkhir = date('Y-m-d'.' 23:59');
        $depBedah = 25;
        $data = DB::select(DB::raw("
                select count(x.noregistrasi) as jumlah,
                 count(*) FILTER (WHERE x.keterangan = 'pasien bedah') AS operasi,
                 count(*) FILTER (WHERE x.keterangan = 'pasien non bedah') AS nonoperasi
                from ( 
                select distinct pd.noregistrasi,pd.tglregistrasi,ru.objectdepartemenfk ,
                CASE
                WHEN (ru.objectdepartemenfk = 25) THEN 'pasien bedah'::text
                ELSE 'pasien non bedah'::text
                END AS keterangan from pasiendaftar_t as pd 
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
                inner join ruangan_m as ru on ru.id = apd.objectruanganfk 
        --      where ru.objectdepartemenfk = 25
                where pd.kdprofile = $idProfile
                and  pd.tglregistrasi BETWEEN   '$tglAwal' and '$tglAkhir'
                GROUP BY pd.tglregistrasi, pd.noregistrasi,ru.objectdepartemenfk
                order by pd.noregistrasi
                ) as x

            "));
        foreach ($data as $item){
            if ((float)$item->jumlah > 0){
                $dataResult = array(
                    'jumlahPasien' =>(float) $item->jumlah,
                    'operasi' =>(float)  $item->operasi,
                    'nonOperasi' => (float) $item->nonoperasi,
                    'percenOperasi' => (float) $item->operasi / (float) $item->jumlah * 100 .' %',
                    'percenNonOperasi' =>  (float) $item->nonoperasi / (float) $item->jumlah * 100 .' %'
                );
            }else{
                $dataResult = array(
                    'jumlahPasien' => 0,
                    'operasi' =>0,
                    'nonOperasi' => 0,
                    'percenOperasi' => 0 .' %',
                    'percenNonOperasi' =>  0 .' %'
                );
            }
        }

        $result = array(
            'result' => $dataResult,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getPasienRehabMedik(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = date('Y-m-d'.' 00:00');
        $tglAkhir = date('Y-m-d'.' 23:59');
        $depRehabMedik = 28;
        $data = DB::select(DB::raw("select count(x.noregistrasi) as jumlah  
            from ( select distinct pd.noregistrasi,pd.tglregistrasi
            from pasiendaftar_t as pd 
            inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
            inner join ruangan_m as ru on ru.id = apd.objectruanganfk 
            where pd.kdprofile = $idProfile and
            ru.objectdepartemenfk = '$depRehabMedik'
            and  pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
            GROUP BY pd.tglregistrasi, pd.noregistrasi) as x
            "));
        foreach ($data as $item){
            $dataResult = array(
                'jumlahData' => (float)$item->jumlah
            );
        }

        $result = array(
            'result' => $dataResult,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getKunjunganRS(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal =  $request['tgl'].' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir =  $request['tgl'].' 23:59';
        $bulan = Carbon::now()->format('F');
        $data = DB::select(DB::raw("select count(x.noregistrasi) as total  ,
                         count(case when x.keterangan = 'Instalasi Rawat Jalan' then 1 end) AS rawatjalan,
                         count(case when x.keterangan = 'Instalasi Rehabilitasi Medik' then 1 end) AS rehabmedik,
                         count(case when x.keterangan = 'Instalasi Rawat Inap' then 1 end) AS rawatinap,
                         count(case when x.keterangan = 'Instalasi Radiologi' then 1 end) AS radiologi,
                         count(case when x.keterangan = 'Instalasi Laboratorium' then 1 end) AS laboratorium,
                         count(case when x.keterangan = 'Instalasi Gawat Darurat' then 1 end) AS igd
                         from ( 
                                    select DISTINCT pd.noregistrasi,pd.tglregistrasi,
                                    case when ru.objectdepartemenfk = 18 and br.norec is null then 'Instalasi Rawat Jalan'
                                    when ru.objectdepartemenfk = 28 and br.norec is null then 'Instalasi Rehabilitasi Medik'
                                    when ru.objectdepartemenfk in ( 16,17, 35, 26) and br.norec is null then 'Instalasi Rawat Inap'
                                    when ru.objectdepartemenfk =27 and br.norec is null then 'Instalasi Radiologi'
                                    when ru.objectdepartemenfk = 3  and br.norec is null then 'Instalasi Laboratorium'
                                    when ru.objectdepartemenfk = 24 and br.norec is null then 'Instalasi Gawat Darurat'
                                    end as keterangan
                                    from pasiendaftar_t as pd 
                                    inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
                                     left join batalregistrasi_t as br on br.pasiendaftarfk =pd.norec
                                    inner join ruangan_m as ru on ru.id = apd.objectruanganfk 
                                    where pd.kdprofile = $idProfile and  pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
                                    and pd.statusenabled=true
                                    GROUP BY pd.tglregistrasi, pd.noregistrasi, br.norec,
                                    ru.objectdepartemenfk  
                                  --  order by pd.noregistrasi
                         ) as x;
            "));
        foreach ($data as $item){
            $datas = array(
                'total' =>  (int)$item->total,
                'rawatjalan' =>  (int)$item->rawatjalan,
                'rawatinap' =>  (int)$item->rawatinap,
                'rehabmedik' =>  (int)$item->rehabmedik,
                'radiologi' =>  (int)$item->radiologi,
                'laboratorium' =>  (int)$item->laboratorium,
                'igd' =>  (int)$item->igd,
            );
        }
        $result = array(
            'result' => $datas,
            'bulan' => $bulan,
            'message' => 'ramdanegie',

        );
        return $this->respond($datas);
    }

    public function getTopTenDiagnosa(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        // $dataLogin = $request->all();
        $tglAwal =  $request['tgl'].' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir =  $request['tgl'].' 23:59';
        $bulan = Carbon::now()->format('F');
        $paramProp = '';
        $paramKota ='';
        $paramKec = '';
        if(isset($request['propinsiId']) && $request['propinsiId']!=''&& $request['propinsiId']!='undefined'){
            $paramProp = ' and pro.id='.$request['propinsiId'];
        }
        if(isset($request['kotaId']) && $request['kotaId']!=''&& $request['kotaId']!='undefined'){
            $paramKota = ' and kot.id='.$request['kotaId'];
        }
        if(isset($request['kecamatanId']) && $request['kecamatanId']!='' && $request['kecamatanId']!='undefined'){
            $paramKec = ' and kec.id='.$request['kecamatanId'];
        }
        $data = DB::select(DB::raw("select * from (
                select count(x.kddiagnosa)as jumlah,x.kddiagnosa,x.namadiagnosa
                from (select dm.kddiagnosa, 
                dm.namadiagnosa
                from antrianpasiendiperiksa_t as app
                left join diagnosapasien_t as dp on dp.noregistrasifk = app.norec
                left join detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
                inner join diagnosa_m as dm on ddp.objectdiagnosafk = dm.id
                inner join pasiendaftar_t as pd on pd.norec = app.noregistrasifk
                inner join pasien_m as ps on ps.id = pd.nocmfk
                left join alamat_m as alm on alm.nocmfk = ps.id
                 left join kecamatan_m as kec on kec.id = alm.objectkecamatanfk
                left join kotakabupaten_m as kot on kot.id = alm.objectkotakabupatenfk
                left join propinsi_m as pro on pro.id = alm.objectpropinsifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where app.kdprofile = $idProfile and dm.kddiagnosa <> '-'  and   pd.statusenabled=true and               
                pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                $paramProp
                $paramKota 
                 $paramKec

                )as x GROUP BY x.namadiagnosa ,x.kddiagnosa 
                ) as z
                ORDER BY z.jumlah desc  limit 10

            "));
        if (count($data)>0){
            foreach ($data as $item){
                $result[] = array(
                    'jumlah' =>$item->jumlah,
                    'kddiagnosa' => $item->kddiagnosa .' '.$item->namadiagnosa  ,
                    'namadiagnosa' => $item->namadiagnosa,
                );
            }

        }else{
            $result[] = array(
                'jumlah' => 0,
                'kddiagnosa' => null,
                'namadiagnosa' => null
            );
        }

        $results = array(
            'result' => $result,
            'month' => $bulan,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getTopTenAsalPerujukBPJS(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $IdBPJS=2;
        $tglAwal =  $request['tgl'].' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir =  $request['tgl'].' 23:59';
        $bulan = Carbon::now()->format('F');
        $data = DB::select(DB::raw("SELECT * FROM
            (
                SELECT COUNT (x.ppkrujukan) AS jumlah, x.ppkrujukan, x.kodeperujuk AS kodeppkrujukan
                FROM (SELECT pd.noregistrasi, CASE WHEN ap.kdprovider IS NULL THEN '-' ELSE ap.kdprovider
                END AS kodeperujuk,CASE WHEN ap.nmprovider IS NULL THEN '-' ELSE ap.nmprovider END AS ppkrujukan,
                pa.ppkrujukan AS kodepa
                FROM pasiendaftar_t AS pd
                LEFT JOIN pemakaianasuransi_t AS pa ON pa.noregistrasifk = pd.norec
                LEFT JOIN asuransipasien_m AS ap ON ap. ID = pa.objectasuransipasienfk
                WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                AND pd.objectkelompokpasienlastfk in (2,4,10) 
                and   pd.statusenabled=true
                -- and ap.kdprovider  <> ''
                -- and ap.kdprovider  <> '-'
                --order by ap.kdprovider
                ) AS x GROUP BY x.ppkrujukan, x.kodeperujuk
            ) AS z
          ORDER BY
          z.jumlah DESC
         "));
        if (count($data) > 0){
            $result = $data ;
        }else{
            $result []= array(
                'jumlah' => 0,
                'kodeppkrujukan' => null,
                'ppkrujukan' => null,
            );
        }

        $results = array(
            'result' => $result,
            'month' => $bulan,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getKunjunganRuanganRawatInap(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $depInap = 16;
        $tglAwal =  $request['tgl'].' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir =  $request['tgl'].' 23:59';
        $bulan = Carbon::now()->format('F');
          $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $data = DB::select(DB::raw("SELECT
                        COUNT (z.kdruangan) AS jumlah,
                        z.namaruangan
                       FROM
                        (
                            select pd.noregistrasi, pd.tglregistrasi, ru.namaruangan, ru.id as kdruangan
                            from pasiendaftar_t as pd 
                            --left join registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec 
                            left join ruangan_m as ru on ru.id = pd.objectruanganlastfk 
                            where pd.kdprofile = $idProfile and ru.objectdepartemenfk = $idDepRanap 
                            and pd.statusenabled = true 
                            and (  pd.tglregistrasi < '$tglAwal' AND pd.tglpulang >= '$tglAkhir' 
                           )
                            or pd.tglpulang is null
                            and pd.kdprofile = $idProfile and ru.objectdepartemenfk = $idDepRanap 
                            and pd.statusenabled = true 
                            group by pd.tglregistrasi, pd.noregistrasi, ru.namaruangan, ru.id
                           
                        ) AS z
                    GROUP BY
                        z.namaruangan
            "));

        $result = array(
            'result' => $data,
            'month' => $bulan,
            'jml' => count($data),
            'message' => 'ramdanegie',
        );
        return $this->respond($data);
    }

    public function getKunjunganFasilitasLain(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal =date('Y-m-d'.' 00:00');// Carbon::now()->startOfMonth();//  Carbon::now()->subMonth(1);
        $tglAkhir = Carbon::now()->format('Y-m-d 23:59');
        $bulan = Carbon::now()->format('F');
        $idPerjanjian = 6;
        $data = DB::select(DB::raw("
                    select DISTINCT pd.noregistrasi, kps.kelompokpasien,pd.objectkelompokpasienlastfk
                    from pasiendaftar_t as pd
                    inner join kelompokpasien_m as kps on kps.id= pd.objectkelompokpasienlastfk
                    where pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal'  and '$tglAkhir'
                    order by pd.noregistrasi desc
                     --SELECT count(x.kelompokpasien) as jumlah , x.kelompokpasien from 
                    --) as x group by x.kelompokpasien
            "));
        $jmlBPJS  = 0;
        $jmlAsuransiLain  = 0;
        $jmlPerusahaan  = 0;
        $jmlUmum  = 0;
        $jmlPerjanjian  = 0;

        foreach ($data as $item){
            if ($item->objectkelompokpasienlastfk == 1){
                $jmlUmum= (float)$jmlUmum +1 ;
            } if ($item->objectkelompokpasienlastfk == 2){
                $jmlBPJS= (float)$jmlBPJS +1 ;
            } if ($item->objectkelompokpasienlastfk == 3){
                $jmlAsuransiLain= (float)$jmlAsuransiLain +1 ;
            } if ($item->objectkelompokpasienlastfk == 5){
                $jmlPerusahaan= (float)$jmlPerusahaan +1 ;
            } if ($item->objectkelompokpasienlastfk == 6){
                $jmlPerjanjian= (float)$jmlPerjanjian +1 ;
            }

        }
        $resultData = array(
            'Umum/Pribadi' => $jmlUmum,
            'BPJS' => $jmlBPJS,
            'Asuransi Lain' => $jmlAsuransiLain,
            'Perusahaan' => $jmlPerusahaan,
            'Perjanjian' => $jmlPerjanjian,
        );
        $result = array(
            'result' => $resultData,
            'month' => $bulan,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getTempatTidurTerpakai(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $tglAwal = $request['tgl'].' 00:00';//date('Y-m-d 00:00');//Carbon::now()->startOfMonth();//  Carbon::now()->subMonth(1);
        $tglAkhir =  $request['tgl'].' 23:59';//Carbon::now()->format('Y-m-d 23:59');
//        $data = \DB::table('pasiendaftar_t as pds')
////              ->leftjoin ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
////              ->join ('registrasipelayananpasien_t as rpp','rpp.noregistrasifk','=',
////                       DB::raw('pd.norec and rpp.objectruanganfk =pd.objectruanganlastfk') )
////              ->join ('tempattidur_m ass tt','tt.id','=','rpp.objecttempattidurfk')
//            ->leftjoin('registrasipelayananpasien_t AS rpp','rpp.noregistrasifk','=','pd.norec')
//            ->leftjoin ('pasien_m as ps','ps.id','=','pd.nocmfk')
//            ->leftjoin ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
//            ->leftjoin ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
//            ->select('pd.noregistrasi','pd.tglregistrasi','ru.namaruangan',
//                'jk.jeniskelamin','ps.objectjeniskelaminfk',
//                DB::raw("CONVERT(int,ROUND(DATEDIFF(hour,ps.tgllahir,GETDATE())/8766.0,0)) AS umur,
//                    DATEDIFF(DAY,  ps.tgllahir, GETDATE()) as hari
//                    "))
////              ->wherein('ru.objectdepartemenfk',[16,35])
//            ->whereNull('pd.tglpulang')
////              ->whereBetween('pd.tglregistrasi',[$tglAwal,$tglAkhir])
//            ->groupBy('pd.tglregistrasi', 'pd.noregistrasi','ru.namaruangan',
//                'ru.objectdepartemenfk'  ,'ps.tgllahir', 'jk.jeniskelamin',
//                'ps.objectjeniskelaminfk')
//            ->orderBy('pd.noregistrasi','desc')
//            ->get();
        $data = DB::select(DB::raw("SELECT
                pd.noregistrasi,
                pd.tglregistrasi,
                ru.namaruangan,
                jk.jeniskelamin,
                ps.objectjeniskelaminfk,
              date_part('year',age(ps.tgllahir))as umur,
                date_part('day',now()-ps.tgllahir)as hari
            FROM
                pasiendaftar_t AS pd
            LEFT JOIN pasien_m AS ps ON ps.id = pd.nocmfk
            LEFT JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
            LEFT JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
            
            WHERE pd.kdprofile = $idProfile 
                and ru.objectdepartemenfk = $idDepRanap 
                 and pd.statusenabled = true
                and (pd.tglregistrasi < '$tglAwal' AND pd.tglpulang >= '$tglAkhir' 
                )
                or pd.tglpulang is null
                and pd.kdprofile = $idProfile 
                and pd.statusenabled = true
                and ru.objectdepartemenfk = $idDepRanap "));
        $jmlBalitaL = 0;
        $jmlBalitaP = 0;
        $jmlAnakLaki = 0;
        $jmlAnakPerempuan = 0;
        $jmlDewasa = 0;
        $jmlGeriatri = 0;
        $jmlAll = 0;
        $jmlLakiDewasa= 0;
        $jmlGeriatriLaki= 0;

        foreach ($data as $item) {
            $jmlAll = $jmlAll + 1;
            //   bayi 0-30 hari
//               anak 30 hari - 17 th
            //   dewsa >17-50 th
//               geriatri >50  keatas
//            return  $this->respond($item->)
            if ($item->objectjeniskelaminfk == 1 && (float)$item->hari <= 30) {
                $jmlBalitaL = (float)$jmlBalitaL + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->hari <= 30) {
                $jmlBalitaP = (float)$jmlBalitaP + 1;
            }
            if ($item->objectjeniskelaminfk == 1 &&(float) $item->hari > 30 && (float)$item->umur <= 17) {
                $jmlAnakLaki = (float)$jmlAnakLaki + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->hari > 30 &&(float) $item->umur <= 17) {
                $jmlAnakPerempuan = (float)$jmlAnakPerempuan + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur > 17 && (float)$item->umur <= 60) {
                $jmlDewasa = (float)$jmlDewasa + 1;
            }
            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur > 17 && (float)$item->umur <= 60) {
                $jmlLakiDewasa = (float)$jmlLakiDewasa + 1;
            }
            if ( $item->objectjeniskelaminfk == 2 && (float)$item->umur > 60) {
                $jmlGeriatri = (float)$jmlGeriatri + 1;
            }
            if ($item->objectjeniskelaminfk == 1 &&  (float)$item->umur > 60) {
                $jmlGeriatriLaki = (float)$jmlGeriatriLaki + 1;
            }


        }

        $resultData = array(
            'jumlah' =>count($data),
            'geriatri' => $jmlGeriatri,
            'geriatrilaki' => $jmlGeriatriLaki,
            'lakidewasa' => $jmlLakiDewasa,
            'perempuandewasa' => $jmlDewasa,
            'anaklaki' => $jmlAnakLaki,
            'anakperempuan' => $jmlAnakPerempuan,
            'balitalaki' => $jmlBalitaL,
            'balitaperempuan' => $jmlBalitaP,
            'all' => $jmlAll,

        );
        $result = array(
            'result' => $resultData,
            'message' => 'ramdanegie',

        );

        return $this->respond($resultData);
    }
    public function getKetersediaanTempatTidurPerkelas(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
         $tglAwal =$request['tgl'].' 00:00';
         $tglAkhir = $request['tgl'].' 23:59';
        //  $dataRanap = DB::select(DB::raw("select * from (SELECT pd.noregistrasi,pd.tglregistrasi,pd.tglpulang,apd.nobed
        //          ,row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
        //         FROM pasiendaftar_t AS pd
        //         LEFT JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
        //          join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
        //         WHERE ru.objectdepartemenfk = 16 
        //         and (  pd.tglregistrasi < '$tglAwal' AND pd.tglpulang >= '$tglAkhir' 
        //         and pd.statusenabled = 1 )
        //         or pd.tglpulang is null
        //         ) as x  where x.rownum=1
           
        //     "));
       
        // $data = \DB::table('tempattidur_m as tt')
        //     ->leftjoin ('statusbed_m as sb','sb.id','=','tt.objectstatusbedfk')
        //     ->leftjoin ('kamar_m as kmr','kmr.id','=','tt.objectkamarfk')
        //     ->leftjoin ('ruangan_m as ru','ru.id','=','kmr.objectruanganfk')
        //     ->leftjoin ('kelas_m as kl','kl.id','=','kmr.objectkelasfk')
        //     ->select('kmr.id','kmr.namakamar','kl.id as id_kelas','kl.namakelas','ru.id as id_ruangan',
        //         'ru.namaruangan','kmr.jumlakamarisi','kmr.qtybed','sb.id as id_statusbed','tt.id as nobed')

        //     ->where('tt.statusenabled',1)
        //     ->get();
       

        // foreach ($data as $kamar  ){
          
        //    foreach ( $dataRanap as $pasien ) {
        //     # code...
        //     if($pasien->nobed == $kamar->nobed){
        //         $result []= array(
        //             'namakelas' => $kamar->namakelas, 
        //             // 'jumlah' =>  ,
        //             'kosong' => 0,
        //             'terpakai' => $isi ,
              
        //             ); 
        //         }   
        //     }
        //  }    
   // return $this->respond($result);
//         $kls1 = 0;
//         $kls2 = 0;
//         $kls3 = 0;
//         $vipA = 0;
//         $vipB = 0;
//         $vip = 0;
//         $vvip =0;
//         $nonKelas = 0;
//         $terpakaikls1 = 0;
//         $terpakaikls2 = 0;
//         $terpakaikls3 = 0;
//         $terpakaivipA = 0;
//         $terpakaivipB = 0;
//         $terpakainonKelas = 0;
//         $terpakaivip = 0;
//         $terpakaivvip= 0;
//         foreach ($data as $item) {
//             if ( $item->id_statusbed == 2 ) {
//                 if ($item->id_kelas == 3  ) {
//                     $kls1 = (float)$kls1 + 1;
//                 }
//                 if ($item->id_kelas == 2 ) {
//                     $kls2 = (float)$kls2 + 1;
//                 }
//                 if ($item->id_kelas == 1 ) {
//                     $kls3 = (float)$kls3 + 1;
//                 }
//                 if ($item->id_kelas == 5  ) {
//                     $vipA = (float)$vipA + 1;
//                 }
//                 if ($item->id_kelas == 8 ) {
//                     $vipB = (float)$vipB + 1;
//                 }
//                 if ($item->id_kelas == 6  ) {
//                     $nonKelas = (float)$nonKelas + 1;
//                 }
//                 if ($item->id_kelas == 4  ) {
//                     $vip = (float)$vip + 1;
//                 }

//                 if ($item->id_kelas == 7  ) {
//                     $vvip = (float)$vvip + 1;
//                 }
//             }

// //            terpakai
//             if ($item->id_statusbed == 1 ){
//                 if ($item->id_kelas == 3) {
//                     $terpakaikls1 = (float)$terpakaikls1 + 1;
//                 }
//                 if ($item->id_kelas == 2 ) {
//                     $terpakaikls2 = (float)$terpakaikls2 + 1;
//                 }
//                 if ($item->id_kelas == 1 ) {
//                     $terpakaikls3 = (float)$terpakaikls3 + 1;
//                 }
//                 if ($item->id_kelas == 5  ) {
//                     $terpakaivipA = (float)$terpakaivipA + 1;
//                 }
//                 if ($item->id_kelas == 8  ) {
//                     $terpakaivipB = (float)$terpakaivipB + 1;
//                 }
//                 if ($item->id_kelas == 6) {
//                     $terpakainonKelas = (float)$terpakainonKelas + 1;
//                 }
//                 if ($item->id_kelas == 4  ) {
//                     $terpakaivip = (float)$terpakaivip + 1;
//                 }

//                 if ($item->id_kelas == 7  ) {
//                     $terpakaivvip = (float)$terpakaivvip + 1;
//                 }
//             }
//         }
//         $kamarKosong = array(
//             'kelas_1' =>$kls1,
//             'kelas_2' => $kls2,
//             'kelas_3' => $kls3,
//             'vip_a' => $vipA,
//             'vip_b' => $vipB,
//             'vip' => $vip,
//             'vvip' => $vvip,
//             'non_kelas' => $nonKelas,
//             'jumlah' => $nonKelas + $kls1 + $kls2 + $kls3 +$vipA +  $vipB +$vip +$vvip,

//             'kelas_1a' =>$terpakaikls1,
//             'kelas_2a' => $terpakaikls2,
//             'kelas_3a' => $terpakaikls3,
//             'vip_aa' => $terpakaivipA,
//             'vip_ba' => $terpakaivipB,
//             'non_kelasa' => $terpakainonKelas,
//             'vvip_aa' => $terpakaivip,
//             'vvvip_aa' => $terpakaivvip,
//             'jumlaha' => $terpakainonKelas + $terpakaikls1 + $terpakaikls2 + $terpakaikls3 +$terpakaivipA +  $terpakaivipB + $terpakaivvip + $terpakaivip,

//             'kelas_1b' =>$kls1 + $terpakaikls1,
//             'kelas_2b' => $kls2 + $terpakaikls2,
//             'kelas_3b' => $kls3 + $terpakaikls3,
//             'vip_ab' => $vipA + $terpakaivipA,
//             'vip_bb' => $vipB + $terpakaivipB,
//             'non_kelasb' => $nonKelas + $terpakainonKelas,
//             'vvip_bb' => $vip + $terpakaivip,
//             'vvvip_bb' => $vvip + $terpakaivvip,
//             'jumlahb' => $nonKelas + $kls1 + $kls2 + $kls3 +$vipA +  $vipB + $vvip+  $vip+$terpakainonKelas + $terpakaikls1 + $terpakaikls2 + $terpakaikls3 +$terpakaivipA +  $terpakaivipB,

//         );
//         $kamarTerpakai = array(
//             'kelas_1' =>$terpakaikls1,
//             'kelas_2' => $terpakaikls2,
//             'kelas_3' => $terpakaikls3,
//             'vip_a' => $terpakaivipA,
//             'vip_b' => $terpakaivipB,
//             'vip' => $terpakaivip,
//             'vvip' => $terpakaivvip,
//             'non_kelas' => $terpakainonKelas,
//             'jumlah' => $terpakainonKelas + $terpakaikls1 + $terpakaikls2 + $terpakaikls3 +$terpakaivipA +  $terpakaivipB +$terpakaivip,$terpakaivvip,
//         );
        // $data = DB::select(DB::raw("select sum(x.isi) as terpakai, sum(x.kosong) as kosong,x.namakelas,
        // sum(x.isi)+ sum(x.kosong) as jml from (
        //     SELECT
        //         kmr.id,
        //         kmr.namakamar,
        //         kl.id AS id_kelas,
        //         kl.namakelas,
        //         ru.id AS id_ruangan,
        //         ru.namaruangan,
        //         kmr.jumlakamarisi,
        //         kmr.qtybed,
        //         case when sb.id=1 then 1 else 0 end as isi,
        //         case when sb.id=2 then 1 else 0 end as kosong
        //     FROM
        //         tempattidur_m AS tt
        //     LEFT JOIN statusbed_m AS sb ON sb.id = tt.objectstatusbedfk
        //     LEFT JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
        //     LEFT JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
        //     LEFT JOIN kelas_m AS kl ON kl.id = kmr.objectkelasfk
        //     WHERE tt.kdprofile = $idProfile and
        //         tt.statusenabled = true
        //       and   kmr.statusenabled = true
        //         ) as x GROUP BY x.namakelas
        //     order by  x.namakelas

        //     "));
      $data = DB::select(DB::raw("select sum(COALESCE (x.isi, 0)) as terpakai, sum(COALESCE (x.kosong, 0)) as kosong,x.namakelas,
        sum(COALESCE (x.isi, 0))+ sum(COALESCE (x.kosong, 0)) as jml from (
            select klz.namakelas,ttt.* from kelas_m as klz 
                    left join (SELECT 
                kmr.id,
                kmr.namakamar,
                kl.id AS id_kelas,
              
                ru.id AS id_ruangan,
                ru.namaruangan,
                kmr.jumlakamarisi,
                kmr.qtybed,
                case when sb.id=1 then 1 else 0 end as isi,
                case when sb.id=2 then 1 else 0 end as kosong,
kmr.objectkelasfk
            FROM
                tempattidur_m AS tt
            LEFT JOIN statusbed_m AS sb ON sb.id = tt.objectstatusbedfk
            LEFT JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
            LEFT JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
            LEFT JOIN kelas_m AS kl ON kl.id = kmr.objectkelasfk
            WHERE tt.kdprofile = $idProfile and
                tt.statusenabled = true
              and   kmr.statusenabled = true) ttt on (ttt.objectkelasfk=klz.id) 
where klz.statusenabled =true and klz.kdprofile=$idProfile
                ) as x GROUP BY x.namakelas
            order by  x.namakelas

            "));

        return $this->respond($data);
    }
    public function getTrendKunjunganPasienRajal(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $tglAwal = Carbon::now()->subWeek(3);//  Carbon::now()->subMonth(1);
        $tglAkhir = Carbon::now()->format('Y-m-d 23:59');
        $currentDate = Carbon::now();
        $last2week = $currentDate->subWeek();
        // $data = DB::select(DB::raw("
        //            select pd.norec,pd.noregistrasi, Format( pd.tglregistrasi, 'dd, MMM yyyy') as tglregistrasi,
        //                 Format( pd.tglregistrasi, 'dd. MMM') as tanggal,
        //             pd.tglregistrasi as tgl,
        //             case when  br.norec is null and pd.nostruklastfk is not null then 'sudahdiperiksa' 
        //             when br.norec is null and pd.nostruklastfk is null then 'belumdiperiksa'
        //             when br.norec is not null then 'batalregis'  end as keterangan,
        //             ps.namapasien, br.norec as norec_batal
        //             from pasiendaftar_t as pd
        //              --  inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
        //             inner join ruangan_m as ru on ru.id = pd.objectruanganlastfk
        //             inner join pasien_m as ps on ps.id = pd.nocmfk
        //             left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
        //             where pd.tglregistrasi between '$tglAwal' and '$tglAkhir'  and ru.objectdepartemenfk = '18'
        //              -- GROUP BY pd.noregistrasi ,apd.tglregistrasi,ps.namapasien,br.norec,apd.statusantrian,pd.nostruklastfk
        //             order by pd.noregistrasi
        //        "));

         $data = DB::select(DB::raw("
                select * from (
                SELECT
                    pd.norec,
                    pd.noregistrasi,
                    to_char (
                        pd.tglregistrasi,
                       'dd, Mon YYYY'
                    ) AS tglregistrasi,
                    to_char (pd.tglregistrasi, 'dd. Mon') AS tanggal,
                    pd.tglregistrasi AS tgl,
                    CASE
                WHEN  apd.tgldipanggilsuster IS NOT NULL  and br.norec is  null THEN
                    'sudahdiperiksa'
                WHEN  apd.tgldipanggilsuster IS NULL  and br.norec is  null THEN
                    'belumdiperiksa'
                WHEN br.norec is not null THEN
                    'batalregis'
                END AS keterangan,
                 ps.namapasien
                ,
                row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
                FROM
                    pasiendaftar_t AS pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
                INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                 left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                WHERE pd.kdprofile = $idProfile and
                    pd.tglregistrasi BETWEEN  '$tglAwal'
                AND '$tglAkhir'
                AND ru.objectdepartemenfk = $idDepRaJal 
                ) as x where x.rownum=1
                ORDER BY
                x.noregistrasi
        "));
        $data10=[];
        $sudahPeriksa = 0;
        $belumPeriksa = 0;
        $batalRegis = 0;
        $totalAll = 0;
        if (count($data) > 0) {
            foreach ($data as $item) {
                $sama = false;
                $i = 0;
                foreach ($data10 as $hideung) {
                    if ($item->tglregistrasi == $data10[$i]['tglregistrasi']) {
                        $sama = 1;
                        $jml = (float)$hideung['totalterdaftar'] + 1;
                        $data10[$i]['totalterdaftar'] = $jml;
                        if ($item->keterangan == 'sudahdiperiksa') {
                            $data10[$i]['diperiksa'] = (float)$hideung['diperiksa'] + 1;
                        }
                        if ($item->keterangan == 'belumdiperiksa') {
                            $data10[$i]['belumperiksa'] = (float)$hideung['belumperiksa'] + 1;
                        }
                        if ($item->keterangan == 'batalregis') {
                            $data10[$i]['batalregistrasi'] = (float)$hideung['batalregistrasi'] + 1;
                        }
                    }
                    $i = $i + 1;
                }
                if ($sama == false) {
                    if ($item->keterangan == 'sudahdiperiksa') {
//                    if ($item->nostruklastfk != null && $item->norec_batal == null) {
                        $sudahPeriksa = 1;
                        $belumPeriksa = 0;
                        $batalRegis = 0;
                    }
//                    if ($item->nostruklastfk == null && $item->norec_batal == null) {
                    if ($item->keterangan == 'belumdiperiksa') {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 1;
                        $batalRegis = 0;
                    }
//                    if ($item->norec_batal != null) {
                    if ($item->keterangan == 'batalregis') {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 0;
                        $batalRegis = 1;
                    }
                    $data10[] = array(
                        'tglregistrasi' => $item->tglregistrasi,
                        'tanggal' =>$item->tanggal,
                        'totalterdaftar' => 1,
                        'diperiksa' => $sudahPeriksa,
                        'belumperiksa' => $belumPeriksa,
                        'batalregistrasi' => $batalRegis,

                    );
                }
                // foreach ($data10 as $key => $row) {
                //     $count[$key] = $row['totalterdaftar'];
                // }
                // array_multisort($count, SORT_DESC, $data10);
            }
        }
        return $this->respond($data10);

    }
    public function getInfoKunjunganRawatJalanPerhari(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $tglAwal = $request['tgl'].' 00:00';
        $tglAkhir =$request['tgl'].' 23:59';
//         $data = \DB::table('pasiendaftar_t as pds')
// //            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
//             ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
//             ->join ( 'pasien_m as ps','ps.id','=','pd.nocmfk')
//             ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
//             ->select('pd.noregistrasi','ps.namapasien','br.norec as norec_batal','pd.nostruklastfk',
//                 'ru.namaruangan','pd.objectruanganlastfk as kdruangan')
//             ->whereBetween('pd.tglregistrasi',[ $tglAwal, $tglAkhir ])
//             ->where('ru.objectdepartemenfk',$idDepRaJal);
// //            ->groupBy('pd.noregistrasi','ps.namapasien','br.norec','pd.nostruklastfk',
// //                'ru.namaruangan','pd.objectruanganlastfk',);
//         $data= $data->get();
        $data = DB::select(DB::raw("
        
                    select * from (SELECT
                    pd.noregistrasi,
                    ps.namapasien,
                    br.norec AS norec_batal,
                    pd.nostruklastfk,
                    ru.namaruangan,
                    apd.tgldipanggilsuster,
                    pd.objectruanganlastfk AS kdruangan,row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
                    FROM
                    pasiendaftar_t AS pd
                    INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
                    INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                    inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                    LEFT JOIN batalregistrasi_t AS br ON br.pasiendaftarfk = pd.norec
                    WHERE pd.kdprofile = $idProfile and
                    pd.tglregistrasi BETWEEN '$tglAwal'
                    AND '$tglAkhir'
                    and pd.statusenabled=true
                    AND ru.objectdepartemenfk = $idDepRaJal) as x where x.rownum=1
                    ORDER BY
                    x.noregistrasi"));

        $data10=[];
        $sudahPeriksa = 0;
        $belumPeriksa = 0;
        $batalRegis = 0;
        $totalAll = 0;
        if (count($data) > 0) {
            foreach ($data as $item) {
                $sama = false;
                $i = 0;
                foreach ($data10 as $hideung) {
                    if ($item->kdruangan == $data10[$i]['kdruangan']) {
                        $sama = 1;
                        $jml = (float)$hideung['total'] + 1;
                        $data10[$i]['total'] = $jml;
                        if ($item->tgldipanggilsuster != null && $item->norec_batal == null) {
//                         if ($item->statusantrian != '0' && $item->norec_batal == null) {
                            $data10[$i]['diperiksa'] = (float)$hideung['diperiksa'] + 1;

                        }
                        if ($item->tgldipanggilsuster == null && $item->norec_batal == null) {
//                        if ($item->statusantrian == '0' && $item->norec_batal == null) {
                            $data10[$i]['belumperiksa'] = (float)$hideung['belumperiksa'] + 1;
                        }
                        if ($item->norec_batal != null) {
                            $data10[$i]['batalregistrasi'] = (float)$hideung['batalregistrasi'] + 1;
                        }
                        //                    $data10[$i]['totalAll'] = $data10[$i]['diperiksa'] + $data10[$i]['belumperiksa'] + $data10[$i]['batalregistrasi'];
                    }
                    $i = $i + 1;
                }
                if ($sama == false) {
                    if ($item->nostruklastfk != null && $item->norec_batal == null) {
                        $sudahPeriksa = 1;
                        $belumPeriksa = 0;
                        $batalRegis = 0;
                    }
                    if ($item->nostruklastfk == null && $item->norec_batal == null) {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 1;
                        $batalRegis = 0;
                    }
                    if ($item->norec_batal != null) {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 0;
                        $batalRegis = 1;
                    }
                    $data10[] = array(
                        'kdruangan' => $item->kdruangan,
                        'namaruangan' => $item->namaruangan,
                        'total' => 1,
                        'diperiksa' => $sudahPeriksa,
                        'belumperiksa' => $belumPeriksa,
                        'batalregistrasi' => $batalRegis,
//                        'count' => $totalAll,
                    );
                }
                foreach ($data10 as $key => $row) {
                    $count[$key] = $row['total'];
                }
                array_multisort($count, SORT_DESC, $data10);
            }
        }else{
            $data10[] = array(
                'kdruangan' => '-',
                'namaruangan' => 'Tidak Ada Data',
                'total' => 0,
                'diperiksa' => 0,
                'belumperiksa' => 0,
                'batalregistrasi' => 0,
            );
        }
        $result = array(
            'result' => $data10,
            'date' => date('d F Y'),
            'message' => 'ramdanegie',
        );
        return $this->respond($data10);
    }
    public function getKunjunganRSPerJenisPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdKelompokPasienUmum = (int) $this->settingDataFixed('KdKelompokPasienUmum',$idProfile);
        $KelompokPasienBpjs = (int) $this->settingDataFixed('KdKelPasienBpjs', $idProfile);
        $KelompokPasienAsuransi = (int) $this->settingDataFixed('KdKelompokPasienAsuransi', $idProfile);
        $KdKelPasienPerusahaan = (int) $this->settingDataFixed('KdKelPasienPerusahaan', $idProfile);
        $KdKelPasienPerjanjian = (int) $this->settingDataFixed('KdKelPasienPerjanjian', $idProfile);
        // $tglAwal =  date('Y-m-d'.' 00:00');
        $tglAwal = $request['tgl'].' 00:00';//Carbon::now()->subMonth(2);
        $tglAkhir =$request['tgl'].' 23:59';
        $dataALL = DB::select(DB::raw("select x.kelompokpasien ,count(x.kelompokpasien) as jumlah from (
                select pd.noregistrasi, 
                 kps.kelompokpasien, 
                 pd.objectkelompokpasienlastfk, 
               to_char (pd.tglregistrasi,'YYYY') as tahunregis
                 from pasiendaftar_t as pd
                 inner join kelompokpasien_m as kps on kps.id = pd.objectkelompokpasienlastfk
                left join batalregistrasi_t as br on br.pasiendaftarfk=pd.norec
                 --left join pemakaianasuransi_t as pa on pa.noregistrasifk=pd.norec
                 WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal'AND '$tglAkhir'
                 and pd.statusenabled=true
                and br.norec is null
                )as  x
                GROUP BY x.kelompokpasien"));


        $data = \DB::table('pasiendaftar_t as pd')
            ->join('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->select('pd.noregistrasi','kps.kelompokpasien',
                'pd.objectkelompokpasienlastfk','dp.id',
                'dp.namadepartemen',
                'pd.norec as norec_pd',
                DB::raw("to_char (pd.tglregistrasi,'YYYY') as tahunregis"))
            ->where('pd.kdprofile',$idProfile)
            ->where('pd.statusenabled',true)
            ->whereBetween('pd.tglregistrasi',[ $tglAwal, $tglAkhir ]);

        $data = $data ->get();

        $data10=[];
        $jmlBPJS  = 0;
        $jmlAsuransiLain  = 0;
        $jmlPerusahaan  = 0;
        $jmlUmum  = 0;
        $jmlPerjanjian  = 0;
//         if (count($data) > 0) {
        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->id == $data10[$i]['id']) {
                    $sama = 1;
                    $jml = (float)$hideung['total'] + 1;
                    $data10[$i]['total'] = $jml;
                    if ($item->objectkelompokpasienlastfk == $kdKelompokPasienUmum) {
                        $data10[$i]['jmlUmum'] = (float)$hideung['jmlUmum'] + 1;
                    }
                    if ($item->objectkelompokpasienlastfk == $KelompokPasienBpjs) {
                        $data10[$i]['jmlBPJS'] = (float)$hideung['jmlBPJS'] + 1;
                    }
                    if ($item->objectkelompokpasienlastfk == $KelompokPasienAsuransi) {
                        $data10[$i]['jmlAsuransiLain'] = (float)$hideung['jmlAsuransiLain'] + 1;
                    }
                    if ($item->objectkelompokpasienlastfk == $KdKelPasienPerusahaan) {
                        $data10[$i]['jmlPerusahaan'] = (float)$hideung['jmlPerusahaan'] + 1;
                    }
                    if ($item->objectkelompokpasienlastfk == $KdKelPasienPerjanjian) {
                        $data10[$i]['jmlPerjanjian'] = (float)$hideung['jmlPerjanjian'] + 1;
                    }
                }
                $i = $i + 1;
            }
            if ($sama == false) {
                if ($item->objectkelompokpasienlastfk == $kdKelompokPasienUmum) {
                    $jmlBPJS  = 0;
                    $jmlAsuransiLain  = 0;
                    $jmlPerusahaan  = 0;
                    $jmlUmum  = 1;
                    $jmlPerjanjian  = 0;
                }
                if ($item->objectkelompokpasienlastfk == $KelompokPasienBpjs) {
                    $jmlBPJS  = 1;
                    $jmlAsuransiLain  = 0;
                    $jmlPerusahaan  = 0;
                    $jmlUmum  = 0;
                    $jmlPerjanjian  = 0;
                }
                if ($item->objectkelompokpasienlastfk == $KelompokPasienAsuransi) {
                    $jmlBPJS  = 0;
                    $jmlAsuransiLain  = 1;
                    $jmlPerusahaan  = 0;
                    $jmlUmum  = 0;
                    $jmlPerjanjian  = 0;
                }
                if ($item->objectkelompokpasienlastfk == $KdKelPasienPerusahaan) {
                    $jmlBPJS  = 0;
                    $jmlAsuransiLain  = 0;
                    $jmlPerusahaan  = 1;
                    $jmlUmum  = 0;
                    $jmlPerjanjian  = 0;
                }
                if ($item->objectkelompokpasienlastfk == $KdKelPasienPerjanjian) {
                    $jmlBPJS  = 0;
                    $jmlAsuransiLain  = 0;
                    $jmlPerusahaan  = 0;
                    $jmlUmum  = 0;
                    $jmlPerjanjian  = 1;
                }
                $data10[] = array(
                    'id' => $item->id,
                    'namadepartemen' => $item->namadepartemen,
                    'total' => 1,
                    'jmlBPJS' => $jmlBPJS,
                    'jmlAsuransiLain' => $jmlAsuransiLain,
                    'jmlPerusahaan' => $jmlPerusahaan,
                    'jmlUmum' => $jmlUmum,
                    'jmlPerjanjian' => $jmlPerjanjian,
                );
            }
            foreach ($data10 as $key => $row) {
                $count[$key] = $row['total'];
            }
            array_multisort($count, SORT_DESC, $data10);
        }

        $result = array(
            'dataAll' => $dataALL,
            'data' => $data10,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }


    public function getPasienMasihDirawat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=',
                DB::raw("apd.noregistrasifk and apd.objectruanganfk=pd.objectruanganlastfk"))
//            ->leftjoin('registrasipelayananpasien_t as rpp', 'rpp.noregistrasifk', '=',
//                DB::raw("pd.norec and rpp.objectruanganfk=pd.objectruanganlastfk"))
            ->leftjoin('kamar_m as kmr', 'kmr.id', '=', 'apd.objectkamarfk')
//            ->leftjoin('tempattidur_m as tt', 'tt.id', '=', 'rpp.objecttempattidurfk')
            ->select('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan',
                'p.namapasien', 'kp.kelompokpasien','kmr.namakamar',
                'kls.namakelas','jk.jeniskelamin','pg.namalengkap','apd.nobed',
                'p.tgllahir')
            ->where('pd.kdprofile', $idProfile)
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

        $data = $data->groupBy('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
            'kls.namakelas','jk.jeniskelamin','pg.namalengkap','pd.norec','kmr.namakamar','apd.nobed',
            'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk');
        $data = $data->orderBy('pd.tglregistrasi','desc');
        $data = $data->get();
        foreach ($data as $item){
            $tgllahir = $this->getAge($item->tgllahir) ;
            $tglregis = new \DateTime(date($item->tglregistrasi));
            $tglregis = $tglregis->format('Y-m-d');
            $res[]=array(
                'tglmasuk' => $tglregis,
                'nocm' => $item->nocm,
                'noregistrasi' => $item->noregistrasi,
                'namapasien' => $item->namapasien,
                'jenispasien' => $item->kelompokpasien,
                'ruangperawatan' => $item->namaruangan,
                'kelas' => $item->namakelas,
                'jeniskelamin' => $item->jeniskelamin,
                'dokter' => $item->namalengkap,
                'tgllahir' => $tgllahir,
                'kamar' => $item->namakamar,
                'nobed' => $item->nobed,
            );
        }
        $result = array(
            'data' => $res,
            'count' => count($res),
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getAge($tgllahir){
        $datetime = new \DateTime(date($tgllahir));
        return $datetime->diff(Carbon::now())
            ->format('%ythn %mbln %dhr');
    }
    public function getRekapKunjunganPasienPertahun(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $tglAwal = Carbon::now()->startOfYear()->subMonth(5);
        $tglAkhir = date('Y-m-d' . ' 23:59');
        $data = \DB::table('pasiendaftar_t as pd')
//            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('batalregistrasi_t as kps', 'kps.pasiendaftarfk', '=', 'pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select('pd.noregistrasi', 'kps.norec as norec_batal','pd.nostruklastfk',
                'pd.objectkelompokpasienlastfk',
                DB::raw("extract (year from pd.tglregistrasi) as tahunregis"))
            ->where('pd.kdprofile', $idProfile)
            ->where('ru.objectdepartemenfk',$idDepRaJal)
            ->whereBetween('pd.tglregistrasi', [$tglAwal, $tglAkhir])
            ->groupBy('pd.noregistrasi', 'kps.norec','pd.nostruklastfk',
                'pd.objectkelompokpasienlastfk','pd.tglregistrasi');
        $data = $data->get();

        $data10 = [];
        $sudahPeriksa = 0;
        $belumPeriksa = 0;
        $batalRegis = 0;
        if (count($data) > 0) {
            foreach ($data as $item) {
                $sama = false;
                $i = 0;
                foreach ($data10 as $hideung) {
                    if ($item->tahunregis == $data10[$i]['tahunregis']) {
                        $sama = 1;
                        $jml = (float)$hideung['total'] + 1;
                        $data10[$i]['total'] = $jml;
                        if ($item->nostruklastfk != null && $item->norec_batal == null) {
                            $data10[$i]['diperiksa'] = (float)$hideung['diperiksa'] + 1;
                        }
                        if ($item->nostruklastfk == null && $item->norec_batal == null) {
                            $data10[$i]['belumperiksa'] = (float)$hideung['belumperiksa'] + 1;
                        }
                        if ($item->norec_batal != null) {
                            $data10[$i]['batalregistrasi'] = (float)$hideung['batalregistrasi'] + 1;
                        }
                    }
                    $i = $i + 1;
                }
                if ($sama == false) {
                    if ($item->nostruklastfk != null && $item->norec_batal == null) {
                        $sudahPeriksa = 1;
                        $belumPeriksa = 0;
                        $batalRegis = 0;
                    }
                    if ($item->nostruklastfk == null && $item->norec_batal == null) {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 1;
                        $batalRegis = 0;
                    }
                    if ($item->norec_batal != null) {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 0;
                        $batalRegis = 1;
                    }
                    $data10[] = array(
                        'tahunregis' => $item->tahunregis,
                        'total' => 1,
                        'diperiksa' => $sudahPeriksa,
                        'belumperiksa' => $belumPeriksa,
                        'batalregistrasi' => $batalRegis,
//
                    );
                }
                foreach ($data10 as $key => $row) {
                    $count[$key] = $row['total'];
                }
                array_multisort($count, SORT_DESC, $data10);
            }
        } else {
            $data10[] = array(
                'tahunregis' => null,
                'total' => 0,
                'diperiksa' => 0,
                'belumperiksa' => 0,
                'batalregistrasi' => 0,
            );
        }
        $result = array(
            'result' => $data10,
            'count' => count($data),
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function countPasienRS(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $tglAwal = $request['tgl'] .' 00:00' ;//date('Y-m-d 00:00');
        $tglAkhir =  $request['tgl'] .' 23:59' ;//date('Y-m-d 23:59');
        $dept = $this->settingDataFixed('kdDepartemenEIS',$kdProfile);
        $data = DB::select(DB::raw("SELECT dp.id ,dp.namadepartemen,count(pd.norec) as jumlah
                FROM departemen_m dp
                join ruangan_m as ru on ru.objectdepartemenfk=dp.id
                LEFT JOIN (SELECT pasiendaftar_t.norec,pasiendaftar_t.objectruanganlastfk FROM pasiendaftar_t
               -- join pasien_m on pasien_m.id= pasiendaftar_t.nocmfk and pasien_m.statusenabled=true
                where tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir' and pasiendaftar_t.statusenabled=true
                and pasiendaftar_t.kdprofile=$kdProfile) pd ON (ru.id= pd.objectruanganlastfk)
                WHERE dp.id in ($dept)
                and dp.kdprofile =$kdProfile
                and dp.statusenabled =true
                group by dp.namadepartemen,dp.id,dp.qdepartemen
                order by dp.qdepartemen asc
        "));
         return $this->respond($data);
        // $idProfile = (int) $kdProfile;
        // $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        // $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        // $idDepRehab = (int) $this->settingDataFixed('KdDepartemenInstalasiRehabilitasiMedik', $idProfile);
        // $idDepBedahSentral = (int) $this->settingDataFixed('KdDeptBedahSentral', $idProfile);
        // $idDepLaboratorium = (int) $this->settingDataFixed('KdDepartemenInstalasiLaboratorium',$idProfile);
        // $idDepRadiologi = (int) $this->settingDataFixed('KdDepartemenInstalasiRadiologi',$idProfile);
        // $idDepIGD = (int) $this->settingDataFixed('KdDepartemenInstalasiGawatDarurat',$idProfile);
        // $tglAwal = $request['tgl'] .' 00:00' ;//date('Y-m-d 00:00');
        // $tglAkhir =  $request['tgl'] .' 23:59' ;//date('Y-m-d 23:59');
        // $data = DB::select(DB::raw("select dp.id ,dp.namadepartemen,count(pd.norec) as jumlah
        //             from pasiendaftar_t as pd
        //             join ruangan_m as ru on ru.id =pd.objectruanganlastfk
        //             join departemen_m as dp on dp.id =ru.objectdepartemenfk
        //               left join batalregistrasi_t as br on br.pasiendaftarfk=pd.norec
        //             where pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir' 
        //             --and ru.objectdepartemenfk =24
        //             and br.norec is null
        //             group by dp.namadepartemen,dp.id 
        //     "));
        // $rawatjalan =0;
        // $rawatinap=0;
        // $igd =0;
        // $rehab =0;
        // $bedah =0;
        // $lab =0;
        // $rad=0;
        // $res=[];
        // if(count($data)> 0) {
        //     foreach ($data as $item) {
        //         if ($item->id == $idDepRaJal) {
        //             $rawatjalan = $item->jumlah;
        //         }
        //         if ($item->id == $idDepRanap) {
        //             $rawatinap = $item->jumlah;
        //         }
        //         if ($item->id == $idDepRehab) {
        //             $rehab = $item->jumlah;
        //         }
        //         if ($item->id == $idDepBedahSentral) {
        //             $bedah = $item->jumlah;
        //         }
        //         if ($item->id == $idDepLaboratorium) {
        //             $lab = $item->jumlah;
        //         }
        //         if ($item->id == $idDepRadiologi) {
        //             $rad = $item->jumlah;
        //         }
        //         if ($item->id == $idDepIGD) {
        //             $igd = $item->jumlah;
        //         }
        //         $res = array(
        //             'rawat_jalan' => (int)$rawatjalan,
        //             'igd' => (int)$igd,
        //             'rawat_inap' => (int)$rawatinap,
        //             'radiologi' => (int)$rad,
        //             'laboratorium' => (int)$lab,
        //             'operasi' => (int)$bedah,
        //             'rehab_medik' => (int)$rehab,
        //             'jumlah' => (int)$rawatjalan + (int)$igd + (int)$rawatinap + (int)$rad
        //                 + (int)$lab + (int)$bedah + (int)$rehab
        //         );
        //     }
        // }else{
        //     $res = array(
        //         'rawat_jalan' => 0,
        //         'igd' => 0,
        //         'rawat_inap' => 0,
        //         'radiologi' => 0,
        //         'laboratorium' => 0,
        //         'operasi' => 0,
        //         'rehab_medik' => 0,
        //         'jumlah' => 0,
        //     );
        // }
        // $result = array(
        //     'result' => $data,
        //     'message' => 'ramdanegie',
        // );
        // return $this->respond($res);

    }

    public function getRekapPasienDirawat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        $data = DB::select(DB::raw("
//            select DISTINCT pd.tglregistrasi, pd.noregistrasi,ps.namapasien,
//            date_part('day'::text, ((('now'::text)::date)::timestamp without time zone - pd.tglregistrasi)) AS hari
//            FROM pasiendaftar_t pd
//            JOIN antrianpasiendiperiksa_t apd On apd.noregistrasifk = pd.norec
//            JOIN ruangan_m ru ON ru.id = pd.objectruanganlastfk
//            JOIN pasien_m ps ON ps.id = pd.nocmfk
//            WHERE ru.objectdepartemenfk = ANY (ARRAY[35, 16]) AND pd.tglpulang IS NULL
//            --AND pd.nostruklastfk IS NULL
//           -- and pd.tglregistrasi between '2018-07-01' and '2018-08-21'
//            "));
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=',
                DB::raw("apd.noregistrasifk and apd.objectruanganfk=pd.objectruanganlastfk"))
            ->leftjoin('kamar_m as kmr', 'kmr.id', '=', 'apd.objectkamarfk')
            ->select('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan',
                'p.namapasien', 'kp.kelompokpasien','kmr.namakamar',
                'kls.namakelas','jk.jeniskelamin','pg.namalengkap','apd.nobed',
                'p.tgllahir',
                DB::raw("date_part('day'::text, ((('now'::text)::date)::timestamp without time zone - pd.tglregistrasi)) AS hari "))
            ->where('pd.kdprofile', $idProfile)
            ->whereNull('pd.tglpulang');
        $data = $data->groupBy('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
            'kls.namakelas','jk.jeniskelamin','pg.namalengkap','pd.norec','kmr.namakamar','apd.nobed',
            'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk');
        $data = $data->orderBy('pd.tglregistrasi','desc');
        $data = $data->get();

        $data10=[];
        $satusampai3 = 0;
        $empatsampai5 = 0;
        $enamsampai10 = 0;
        $sebelassampai15 = 0;
        $enambelassampai20 = 0;
        $lebih20 = 0;
        $persen1 =0;
        $persen4 =0;
        $persen6 =0;
        $persen11 =0;
        $persen16 =0;
        $persen20 =0;
        foreach ($data as $item) {
            $total = 0;
            if ($item->hari >= 0 && $item->hari <= 3 ) {
                $satusampai3= (float) $satusampai3 + 1;
            }
            if ($item->hari >= 4 && $item->hari <= 5 ) {
                $empatsampai5= (float) $empatsampai5 + 1;
            }
            if ($item->hari >= 6 && $item->hari <= 10 ) {
                $enamsampai10= (float)   $enamsampai10 + 1;
            }
            if ($item->hari >= 11 && $item->hari <= 15 ) {
                $sebelassampai15 = (float) $sebelassampai15  + 1;
            }
            if ($item->hari >= 16 && $item->hari <= 20 ) {
                $enambelassampai20  = (float) $enambelassampai20   + 1;
            }
            if ($item->hari > 20  ) {
                $lebih20= (float) $lebih20 + 1;
            }
            $total = $satusampai3+ $empatsampai5 +$enamsampai10+ $sebelassampai15 +  $enambelassampai20 + $lebih20;

            if ($satusampai3 > 0){
                $persen1 = $satusampai3 * 100 /$total;
            }
            if ($empatsampai5 > 0){
                $persen4 = $empatsampai5 * 100 /$total;
            }
            if ($enamsampai10 > 0){
                $persen6 = $enamsampai10 * 100 /$total;
            }
            if ($sebelassampai15 > 0){
                $persen11 = $sebelassampai15 * 100 /$total;
            }
            if ($enambelassampai20 > 0){
                $persen16 = $enambelassampai20 * 100 /$total;
            }
            if ($lebih20 > 0){
                $persen20 = $lebih20 * 100 /$total;
            }

            $data10 = array(
                'total' => $total,
                'satu' => $satusampai3,
                'persen1' =>number_format($persen1,1).'%',
                'empat' => $empatsampai5,
                'persen4' => number_format($persen4,1) .'%',
                'enam' => $enamsampai10,
                'persen6' =>number_format($persen6,1) .'%',
                'sebelas' => $sebelassampai15,
                'persen11' => number_format($persen11,1) .'%',
                'enambelas' => $enambelassampai20,
                'persen16' => number_format($persen16,1) .'%',
                'lebih20' => $lebih20,
                'persen20' =>number_format($persen20,1) .'%',
            );
        }
        return $this->respond( $data10);
    }
    public function getPasienPerjenisPenjadwalan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $idDepRehab = (int) $this->settingDataFixed('KdDepartemenInstalasiRehabilitasiMedik', $idProfile);
        $idDepBedahSentral = (int) $this->settingDataFixed('KdDeptBedahSentral', $idProfile);
        $idDepLaboratorium = (int) $this->settingDataFixed('KdDepartemenInstalasiLaboratorium',$idProfile);
        $idDepRadiologi = (int) $this->settingDataFixed('KdDepartemenInstalasiRadiologi',$idProfile);
        $idDepIGD = (int) $this->settingDataFixed('KdDepartemenInstalasiGawatDarurat',$idProfile);
        $tglAwal= $request['tgl'].' 00:00';
        $tglAkhir=$request['tgl'].' 23:59';
        $data = DB::select(DB::raw("
                                   select count(x.noregistrasi) as jumlah  ,x.keterangan,
    count (case when x.departemen = 'rawat_jalan' then 1 end) AS rawatjalan,
count(case when x.departemen = 'igd' then 1 end) AS igd,
count(case when x.departemen = 'rawat_inap' then 1 end) AS rawat_inap,
count(case when x.departemen = 'radiologi' then 1 end) AS radiologi,
count(case when x.departemen = 'laboratorium' then 1 end) AS laboratorium,
count(case when x.departemen = 'operasi' then 1 end) AS operasi,
count(case when x.departemen = 'rahab_medik' then 1 end) AS rehab_medik
    from (
    SELECT
    case when apr.noreservasi is not null then 'Registrasi Online' else 'Loket Pendaftaran' end as keterangan,
            pd.noregistrasi,
    ru.namaruangan,pd.statusschedule,
    case when ru.objectdepartemenfk = $idDepRaJal  then 'rawat_jalan'
    when ru.objectdepartemenfk = $idDepIGD then 'igd'
    when ru.objectdepartemenfk = $idDepRanap   then 'rawat_inap'
    when ru.objectdepartemenfk = $idDepRadiologi  then 'radiologi'
    when ru.objectdepartemenfk = $idDepLaboratorium  then 'laboratorium'
    when ru.objectdepartemenfk = $idDepBedahSentral  then 'operasi'
    when ru.objectdepartemenfk = $idDepRehab  then 'rahab_medik'
    end as departemen
    FROM
    pasiendaftar_t AS pd
    left join antrianpasienregistrasi_t as apr on apr.noreservasi=pd.statusschedule
    inner JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
    WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal'   AND '$tglAkhir'
    and pd.statusenabled=true
    ) as x  group BY x.keterangan 

            "));
        if (count($data) >0){
            $res = array(
                'data' => $data,
                'message' => 'inhuman',
            );
        }else{
            $res = array(
                'data' => '',
                'message' => 'inhuman',
            );
        }

        return $this->respond($res);
    }
    public function getBorLosToi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $idStatKelMeninggal = (int) $this->settingDataFixed('KdStatKeluarMeninggal',$idProfile);
        $idKondisiPasienMeninggal = (int) $this->settingDataFixed('KdKondisiPasienMeninggal', $idProfile);
        $tglAwal =  $request['tgl'].' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir =  $request['tgl'].' 23:59';
        $tahun = new \DateTime($tglAkhir);
        $tahun = date ('Y');
        $datetime1 = new \DateTime($tglAwal);
        $datetime2 = new \DateTime($tglAkhir);
        $interval = $datetime1->diff($datetime2);
        $sehari = 1;//$interval->format('%d');
        $data10=[];
        $jumlahTT = collect(DB::select("SELECT
                    tt.id,
                    tt.objectstatusbedfk
            FROM
                    tempattidur_m AS tt
            INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
            INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
            WHERE
                    tt.kdprofile = $idProfile
            AND tt.statusenabled = true
            AND kmr.statusenabled = true
            "))->count();
        if($jumlahTT == 0){
            $data10[] = array(
                'lamarawat'=> 0 ,
                'hariperawatan' =>0,
                'pasienpulang' =>0,
                'meninggal' => 0,
                'matilebih48' =>  0,
                'tahun' => 0,
                'bulan' => date('d-M-Y') ,//(float)$item->bulanregis ,
                'bor' => 0,
                'alos' => 0,
                'bto' => 0,
                'toi' =>  0,
                'gdr' => 0,
                'ndr' =>  0,
            );
       
            return $this->respond($data10);
        }

//        $hariPerawatan = DB::select(DB::raw("select count (x.noregistrasi) as jumlahhariperawatan,x.bulanregis from (
//                                  select pd.noregistrasi ,extract (month from pd.tglregistrasi) as bulanregis
//                                  from pasiendaftar_t as pd
//                                  inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec and pd.objectruanganlastfk=apd.objectruanganfk
//                                  inner join ruangan_m as ru on ru.id=apd.objectruanganfk
//                                  where
//                                  pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
//                                  and ru.objectdepartemenfk =16
//                                  GROUP BY pd.noregistrasi, pd.tglregistrasi,pd.tglpulang
//                                  order by pd.tglregistrasi
//                    )as x
//                    group by x.bulanregis order by x.bulanregis;
//                "));
        $hariPerawatan = DB::select(DB::raw("
           SELECT   COUNT (x.noregistrasi) AS jumlahhariperawatan
            FROM
            (
                SELECT
                    pd.noregistrasi,
                    pd.tglpulang,
                    to_char ( pd.tglregistrasi,'mm') AS bulanregis
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                WHERE
                    ru.objectdepartemenfk = $idDepRanap 
                    and pd.kdprofile = $idProfile
            and (  pd.tglregistrasi < '$tglAwal' AND pd.tglpulang >= '$tglAkhir' 
            )
            or pd.tglpulang is null
            and pd.statusenabled = true
            and pd.kdprofile = $idProfile
           and  ru.objectdepartemenfk = $idDepRanap 
            ) AS x"
        ));
//        return $this->respond($hariPerawatan);
//        $hariPerawatan = DB::select(DB::raw("SELECT
//                            SUM (z.jumlah) AS jumlahhariperawatan
//                        FROM
//                            (
//                                SELECT
//                                    COUNT (x.noregistrasi) AS jumlah
//                                FROM
//                                    (
//                                        SELECT
//                                            pd.noregistrasi,
//                                            pd.tglpulang,
//                                            Format ( pd.tglregistrasi,'mm') AS bulanregis
//                                        FROM
//                                            pasiendaftar_t AS pd
//                                        INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
//                                        WHERE
//                                            ru.objectdepartemenfk = 16
//                                        AND pd.tglpulang IS NULL
//                                        AND pd.tglregistrasi NOT BETWEEN '$tglAwal'
//                                        AND '$tglAkhir'
//                                    ) AS x
//                                UNION ALL
//                                    SELECT
//                                        COUNT (x.noregistrasi) AS jumlah
//                                    FROM
//                                        (
//                                            SELECT
//                                                pd.noregistrasi,
//                                                pd.tglpulang,
//                                                    Format ( pd.tglregistrasi,'mm')  AS bulanregis
//                                            FROM
//                                                pasiendaftar_t AS pd
//                                            INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
//                                            WHERE
//                                                ru.objectdepartemenfk = 16
//                                            AND pd.tglregistrasi NOT BETWEEN '$tglAwal'
//                                        AND '$tglAkhir'
//                                            AND pd.tglpulang IS NULL
//                                        ) AS x
//                                    UNION ALL
//                                        SELECT
//                                            COUNT (x.noregistrasi) AS jumlah
//                                        FROM
//                                            (
//                                                SELECT
//                                                    pd.noregistrasi,
//                                                    pd.tglpulang,
//                                                    Format ( pd.tglregistrasi,'mm')  AS bulanregis
//                                                FROM
//                                                    pasiendaftar_t AS pd
//                                                INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
//                                                WHERE
//                                                    ru.objectdepartemenfk = 16
//                                                  AND pd.tglregistrasi NOT BETWEEN '$tglAwal'
//                                             AND '$tglAkhir'
//                                            ) AS x
//                            ) AS z"));
        $lamaRawat = DB::select(DB::raw("
                        select sum(x.hari) as lamarawat, count(x.noregistrasi)as jumlahpasienpulang from (
                        SELECT 
                            date_part('DAY', pd.tglpulang- pd.tglregistrasi) as hari ,pd.noregistrasi
                            FROM
                                    pasiendaftar_t AS pd
                        --  INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                            INNER JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
                            WHERE pd.kdprofile = $idProfile and
                            pd.tglpulang BETWEEN '$tglAwal'
                              AND '$tglAkhir' 
                            and pd.tglpulang is not null
                            and pd.statusenabled=true
                            and  ru.objectdepartemenfk = $idDepRanap 
                            GROUP BY pd.noregistrasi,pd.tglpulang,pd.tglregistrasi
                         -- order by pd.noregistrasi 
                      ) as x       
                "));
//        return $this->respond($lamaRawat);
//        $lamaRawat = DB::select(DB::raw("select count(x.tglpulang) as jumlahpasienpulang, sum(x.hari) as lamarawat ,x.bulanpulang from (
//                                  select
//                                  date_part('day'::text, ((pd.tglpulang::date)::timestamp without time zone - pd.tglregistrasi))  AS hari,pd.tglpulang,
//                                    extract (month from pd.tglpulang) as bulanpulang
//                                  from pasiendaftar_t as pd
//                                  inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec and pd.objectruanganlastfk=apd.objectruanganfk
//                                  inner join ruangan_m as ru on ru.id=apd.objectruanganfk
//                                  where
//                                  pd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
//                                  and ru.objectdepartemenfk =16
//                                  and pd.tglpulang is not null
//                                  GROUP BY pd.noregistrasi, pd.tglregistrasi,pd.tglpulang
//                                  order by pd.tglregistrasi
//                    )as x
//                    group by x.bulanpulang "));

        $dataMeninggal = DB::select(DB::raw("select count(x.noregistrasi) as jumlahmeninggal, x.bulanregis,  
                count(case when x.objectkondisipasienfk = $idKondisiPasienMeninggal then 1 end ) AS jumlahlebih48 FROM
                (
                select noregistrasi,to_char(tglregistrasi , 'mm')  as bulanregis ,statuskeluar,kondisipasien,objectkondisipasienfk
                from pasiendaftar_t 
                join statuskeluar_m on statuskeluar_m.id =pasiendaftar_t.objectstatuskeluarfk
                left join kondisipasien_m on kondisipasien_m.id =pasiendaftar_t.objectkondisipasienfk
                where pasiendaftar_t.kdprofile = $idProfile and objectstatuskeluarfk = $idStatKelMeninggal
                and  tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
                and pasiendaftar_t.statusenabled=true
                ) as x
                GROUP BY x.bulanregis;"));
//        return $this->respond($dataMeninggal);
        $year = Carbon::now()->year;
        $num_of_days = [];
        if($year == date('Y'))
            $total_month = date('m');
        else
            $total_month = 12;

        for($m=1; $m<=$total_month; $m++){
            $num_of_days[] = array(
                'bulan' =>  $m,
                'jumlahhari' =>  cal_days_in_month(CAL_GREGORIAN, $m, $year),
            );
        }
        $bor = 0;
        $alos = 0;
        $toi = 0;
        $bto = 0;
        $ndr = 0;
        $gdr = 0;
        $hariPerawatanJml = 0;
        $jmlPasienPlg = 0;
        $jmlLamaRawat = 0;
        $jmlMeninggal = 0;
        $jmlMatilebih48=0;
        foreach ($hariPerawatan as $item){
            foreach ($lamaRawat as $itemLamaRawat){
                foreach ($dataMeninggal as $itemDead) {
//                         if ($item->bulanregis == $itemLamaRawat->bulanpulang &&
//                             $itemLamaRawat->bulanpulang == $itemDead->bulanregis ) {
                    /** @var  $gdr = (Jumlah Mati dibagi Jumlah pasien Keluar (Hidup dan Mati) */
                    $gdr = (int) $itemDead->jumlahmeninggal * 1000 /(int)$itemLamaRawat->jumlahpasienpulang ;
                    /** @var  $NDR = (Jumlah Mati > 48 Jam dibagi Jumlah pasien Keluar (Hidup dan Mati) */
                    $ndr = (int) $itemDead->jumlahlebih48 * 1000 /(int)$itemLamaRawat->jumlahpasienpulang ;

                    $jmlMeninggal = (int) $itemDead->jumlahmeninggal ;
                    $jmlMatilebih48= (int) $itemDead->jumlahlebih48;
//                         }
                }
//                if ($item->bulanregis == $itemLamaRawat->bulanpulang ) {
                /** @var  $alos = (Jumlah Lama Dirawat dibagi Jumlah pasien Keluar (Hidup dan Mati) */
//                return $this->respond($itemLamaRawat->jumlahpasienpulang );
                if ( (int)$itemLamaRawat->jumlahpasienpulang > 0){
                    $alos = (int)$itemLamaRawat->lamarawat / (int)$itemLamaRawat->jumlahpasienpulang;
                }

                /** @var  $bto = Jumlah pasien Keluar (Hidup dan Mati) DIBAGI Jumlah tempat tidur */
                $bto = (int)$itemLamaRawat->jumlahpasienpulang / $jumlahTT;

//                }
//                foreach ($num_of_days as $numday){
//                    if ($numday['bulan'] == $item->bulanregis){
                /** @var  $bor = (Jumlah hari perawatn RS dibagi ( jumlah TT x Jumlah hari dalam satu periode ) ) x 100 % */
                $bor = ( (int)$item->jumlahhariperawatan * 100 / ($jumlahTT *  (float)$sehari ));//$numday['jumlahhari']));
                /** @var  $toi = (Jumlah TT X Periode) - Hari Perawatn DIBAGI Jumlah pasien Keluar (Hidup dan Mati)*/
//                        $toi = ( ( $jumlahTT * $numday['jumlahhari'] )- (int)$item->jumlahhariperawatan ) /(int)$itemLamaRawat->jumlahpasienpulang ;
                if ( (int)$itemLamaRawat->jumlahpasienpulang > 0){
                    $toi = ( ( $jumlahTT * (float)$sehari)- (int)$item->jumlahhariperawatan ) /(int)$itemLamaRawat->jumlahpasienpulang ;
                }
                $hariPerawatanJml = (int)$item->jumlahhariperawatan;
                $jmlPasienPlg = (int)$itemLamaRawat->jumlahpasienpulang;
//                    }
//                }
            }

            $data10[] = array(
                'lamarawat'=>(int)$itemLamaRawat->lamarawat,
                'hariperawatan' => $hariPerawatanJml,
                'pasienpulang' => $jmlPasienPlg,
                'meninggal' => $jmlMeninggal,
                'matilebih48' =>  $jmlMatilebih48,
                'tahun' => $tahun,
                'bulan' => date('d-M-Y') ,//(float)$item->bulanregis ,
                'bor' =>(float) number_format($bor,2),
                'alos' =>(float) number_format($alos,2),
                'bto' =>(float) number_format($bto,2),
                'toi' => (float)number_format($toi,2),
                'gdr' => (float)number_format($gdr,2),
                'ndr' => (float) number_format($ndr,2),
            );
        }
        return $this->respond($data10);

    }
    public function getHakAkses(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $reqAll = $request->all();
        $data = DB::table('loginuser_s as lu')
            ->join('kelompokuser_s as kps','kps.id','=','lu.objectkelompokuserfk')
            ->select('lu.namauser','kps.kelompokuser','lu.objectkelompokuserfk')
//            ->where('lu.objectkelompokuserfk',  42)
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.objectpegawaifk',$request['pegawaiId'])
            ->first();
        $message ='sukses';
//        if ($data->objectkelompokuserfk == 42){
//            $message ='sukses';
//        }else{
//            $message ='gagal';
//        }

        return $this->respond($message);

    }

    public  function getKunjunganPerJenisPelayanan (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $JenisPelayananReg = $this->settingDataFixed('KdJenisPelayananReg',$idProfile);
        $JenisPelayananEks = $this->settingDataFixed('KdJenisPelayananEks',$idProfile);
        $tglAwal =  $request['tgl'].' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir =  $request['tgl'].' 23:59';
        $data = DB::select(DB::raw("
            SELECT dp.id,
            dp.namadepartemen,
            pd.norec as norec_pd,
            CASE WHEN jp.jenispelayanan IS NULL THEN 'REGULER' else jp.jenispelayanan end as jenispelayanan
            FROM
            pasiendaftar_t AS pd
            left JOIN jenispelayanan_m AS jp ON CAST (jp.id AS CHAR) = pd.jenispelayanan
            JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
            JOIN departemen_m AS dp ON dp. ID = ru.objectdepartemenfk
            WHERE pd.kdprofile = $idProfile and
            pd.tglregistrasi BETWEEN '$tglAwal'
           
            AND '$tglAkhir'
            and pd.statusenabled=true
        "));
        $data10=[];
        $data20=[];
        $reguler=0;
        $eksekutif=0;
        if (count($data) > 0) {
            foreach ($data as $item) {
                $sama = false;
                $i = 0;
                foreach ($data10 as $hideung) {
                    if ($item->id == $data10[$i]['id']) {
                        $sama = 1;
                        $jml = (float)$hideung['total'] + 1;
                        $data10[$i]['total'] = $jml;
                        if ($item->jenispelayanan == $JenisPelayananReg) {
                            $data10[$i]['reguler'] = (float)$hideung['reguler'] + 1;
                        }
                        if ($item->jenispelayanan == $JenisPelayananEks) {
                            $data10[$i]['eksekutif'] = (float)$hideung['eksekutif'] + 1;
                        }
                    }
                    $i = $i + 1;
                }
                if ($sama == false) {
                    if ($item->jenispelayanan == $JenisPelayananReg) {
                        $reguler = 1;
                        $eksekutif = 0;

                    }
                    if ($item->jenispelayanan == $JenisPelayananEks) {
                        $reguler = 0;
                        $eksekutif = 1;
                    }

                    $data10[] = array(
                        'id' => $item->id,
                        'namadepartemen' => $item->namadepartemen,
                        'total' => 1,
                        'reguler' => $reguler,
                        'eksekutif' => $eksekutif


                    );

                }
                foreach ($data10 as $key => $row) {
                    $count[$key] = $row['id'];
                }
                array_multisort($count, SORT_DESC, $data10);
            }
        }



        $result = array(
            'data'=> $data10 ,
            'jml' => count($data),

        );
        return $this->respond($result);

    }
    public function detailPasienRJ(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal = $request['tgl'].' 00:00';
        $tglAkhir =  $request['tgl'].' 23:59';
        $data = DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join ('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->select('pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien','ru.namaruangan',
                'pd.tglpulang')
            ->where('pd.kdprofile', $idProfile)
            ->whereBetween('pd.tglregistrasi',[$tglAwal,$tglAkhir])
            ->where('ru.objectdepartemenfk',$request['idRuangan'])
                 ->where('pd.statusenabled',1)
            ->get();

        $result = array(
            'data' => $data,
            'count' =>count($data),
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function countPasienRSTerlayani(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal = $request['tgl'] .' 00:00' ;//date('Y-m-d 00:00');
        $tglAkhir =  $request['tgl'] .' 23:59' ;//date('Y-m-d 23:59');
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
         $dept = $this->settingDataFixed('kdDepartemenRawatJalanFix',$kdProfile);
         $data = DB::select(DB::raw("SELECT dp.id ,dp.namadepartemen,count(pd.norec) as jumlah, dp.qdepartemen
                FROM departemen_m dp
                join ruangan_m as ru on ru.objectdepartemenfk=dp.id
                LEFT JOIN (SELECT antrianpasiendiperiksa_t.norec,objectruanganfk FROM antrianpasiendiperiksa_t
                where tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir' 
                and   antrianpasiendiperiksa_t.statusenabled=true
                and antrianpasiendiperiksa_t.kdprofile=$idProfile)
                 pd ON (ru.id= pd.objectruanganfk)
                WHERE dp.id in ($dept)
                and dp.kdprofile =$idProfile
                group by dp.namadepartemen,dp.id,dp.qdepartemen
                order by dp.qdepartemen asc
        "));
         $dataRanap = DB::select(DB::raw("select count(x.noregistrasi) as jumlah  
            from ( select  pd.noregistrasi,pd.tglregistrasi
            from pasiendaftar_t as pd 
            inner join ruangan_m as ru on ru.id = pd.objectruanganlastfk
            where ru.objectdepartemenfk = $idDepRanap 
            and pd.statusenabled = true
            and pd.kdprofile=$idProfile
            and (  pd.tglregistrasi < '$tglAwal' 
                AND pd.tglpulang >= '$tglAkhir' 
             ) or pd.tglpulang is null
               and pd.statusenabled = true
            and pd.kdprofile=$idProfile
          
         ) as x
            "));
        $dataFarmasi =DB::select(DB::raw("
             SELECT
                COUNT (x.noresep) AS jumlah
              FROM
                (
                    SELECT *
                    FROM
                        strukresep_t AS sr
                    WHERE
                        sr.tglresep BETWEEN '$tglAwal'
                    AND '$tglAkhir'
                     and (sr.statusenabled is null or sr.statusenabled = true)
                     and sr.kdprofile=$idProfile
                ) AS x"));
        $farmasi = 0;
        $masihDirawat = 0;
        if (count($dataFarmasi) > 0){
            $farmasi =$dataFarmasi[0]->jumlah;
        }
        if (count($dataRanap) > 0){
            $masihDirawat =$dataRanap[0]->jumlah;
        }
        $data10 = [];
        foreach ($data as $key => $value) {
             $data10 [] =  array('id' => $value->id,
             'namadepartemen' => $value->namadepartemen,
             'jumlah' => $value->jumlah,
             'qdepartemen' =>  $value->qdepartemen  ); 

            # code...
        }
          $data10 [] =  array(
                 'id' => 16,
                 'namadepartemen' => 'Instalasi Rawat Inap',
                 'jumlah' =>$masihDirawat,
                 'qdepartemen' => 3
          );    
        $data10 [] =  array(
                 'id' => 14,
                 'namadepartemen' => 'Instalasi Farmasi',
                 'jumlah' =>$farmasi,
                  'qdepartemen' => 13
            ); 
        if(count($data10) >0){
            foreach ($data10 as $key => $row) {
                $count[$key] = $row['qdepartemen'];
            }
            array_multisort($count, SORT_ASC, $data10);
        }
   
        return $this->respond($data10);
        // $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        // $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        // $idDepRehab = (int) $this->settingDataFixed('KdDepartemenInstalasiRehabilitasiMedik', $idProfile);
        // $idDepBedahSentral = (int) $this->settingDataFixed('KdDeptBedahSentral', $idProfile);
        // $idDepLaboratorium = (int) $this->settingDataFixed('KdDepartemenInstalasiLaboratorium',$idProfile);
        // $idDepRadiologi = (int) $this->settingDataFixed('KdDepartemenInstalasiRadiologi',$idProfile);
        // $idDepIGD = (int) $this->settingDataFixed('KdDepartemenInstalasiGawatDarurat',$idProfile);
        // $data = DB::select(DB::raw("
        //              select dp.id ,dp.namadepartemen,count(apd.norec) as jumlah
        //             from antrianpasiendiperiksa_t  as apd 
        //             join ruangan_m as ru on ru.id =apd.objectruanganfk
        //             join departemen_m as dp on dp.id =ru.objectdepartemenfk
        //             left join batalregistrasi_t as br on br.pasiendaftarfk=apd.noregistrasifk
        //             where apd.kdprofile = $idProfile and apd.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
        //             --and ru.objectdepartemenfk =24
        //             and br.norec is null
        //             group by dp.namadepartemen,dp.id 
        //     "));
        // $dataRanap = DB::select(DB::raw("select count(x.noregistrasi) as jumlah  
        //     from ( select  pd.noregistrasi,pd.tglregistrasi
        //     from pasiendaftar_t as pd 
        //     inner join ruangan_m as ru on ru.id = pd.objectruanganlastfk
        //     where apd.kdprofile = $idProfile and ru.objectdepartemenfk = $idDepRanap 
        //     and (  pd.tglregistrasi < '$tglAwal' AND pd.tglpulang >= '$tglAkhir' 
        //     and pd.statusenabled = 1 )
        //     or pd.tglpulang is null
        //  ) as x
        //     "));
        // $dataFarmasi =DB::select(DB::raw("
        //      SELECT
        //         COUNT (x.noresep) AS jumlah
        //       FROM
        //         (
        //             SELECT *
        //             FROM
        //                 strukresep_t AS sr
        //             WHERE sr.kdprofile = $idProfile and
        //                 sr.tglresep BETWEEN '$tglAwal'
        //             AND '$tglAkhir'
        //              and (sr.statusenabled is null or sr.statusenabled = 1)
        //         ) AS x"));

        // $rawatjalan =0;
        // $rawatinap=0;
        // $igd =0;
        // $rehab =0;
        // $bedah =0;
        // $lab =0;
        // $rad=0;
        // $farmasi=0;
        // $masihDirawat=0;
        // $res=[];
        // if (count($dataFarmasi) > 0){
        //     $farmasi =$dataFarmasi[0]->jumlah;
        // }
        // if (count($dataRanap) > 0){
        //     $masihDirawat =$dataRanap[0]->jumlah;
        // }
        // foreach ($data as $item) {
        //     if ($item->id == $idDepRaJal) {
        //         $rawatjalan = $item->jumlah;
        //     }
        //     if ($item->id == $idDepRanap) {
        //         $rawatinap = $item->jumlah;
        //     }
        //     if ($item->id == $idDepRehab) {
        //         $rehab = $item->jumlah;
        //     }
        //     if ($item->id == $idDepBedahSentral) {
        //         $bedah = $item->jumlah;
        //     }
        //     if ($item->id == $idDepLaboratorium) {
        //         $lab = $item->jumlah;
        //     }
        //     if ($item->id == $idDepRadiologi) {
        //         $rad = $item->jumlah;
        //     }
        //     if ($item->id == $idDepIGD) {
        //         $igd = $item->jumlah;
        //     }
        //     $res = array(
        //         'rawat_jalan' => (int)$rawatjalan,
        //         'igd' => (int)$igd,
        //         'rawat_inap' => (int)$rawatinap,
        //         'radiologi' => (int)$rad,
        //         'laboratorium' => (int)$lab,
        //         'operasi' => (int)$bedah,
        //         'rehab_medik' => (int)$rehab,
        //         'jumlah' => (int)$rawatjalan + (int)$igd + (int)$rawatinap + (int)$rad
        //             + (int)$lab + (int)$bedah + (int)$rehab
        //     );
        // }

        // $result = array(
        //     'data' => $res,
        //     'farmasi'=> (float) $farmasi,
        //     'masihDirawat'=>(float) $masihDirawat,
        //     'message' => 'ramdanegie',
        // );
        // return $this->respond($result);

    }
    public function detailPasienTerlayani($idDept,Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $idDepFarmasi = (int) $this->settingDataFixed('IdDepartemenInstalasiFarmasi',$idProfile);
        $tglAwal = $request['tgl'].' 00:00';
        $tglAkhir =  $request['tgl'].' 23:59';
        if ($idDept == $idDepRanap) {
            $data = DB::select(DB::raw("SELECT
                    pd.noregistrasi,
                    pd.tglregistrasi,
                    ps.nocm,
                    ps.namapasien,
                    ru.namaruangan,
                    pd.tglpulang
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
                INNER JOIN departemen_m AS dp ON dp.id = ru.objectdepartemenfk
                WHERE pd.kdprofile = $idProfile and
                    ru.objectdepartemenfk = $idDepRanap
                    and pd.statusenabled = true
        
                AND (
                pd.tglpulang IS NULL or
                    pd.tglregistrasi < '$tglAwal'
                    AND pd.tglpulang >= '$tglAkhir'
                 
                )
              
             "));
        }else if($idDept==$idDepFarmasi){
            $data =DB::select(DB::raw("
                SELECT
                    apd.norec,  pd.noregistrasi,pd.tglregistrasi,
                    ps.nocm,ps.namapasien,
                    ru.namaruangan, ru2.namaruangan AS ruanganfarmasi,sr.noresep,
                    sr.tglresep,    pg.namalengkap
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
                INNER JOIN pasien_m AS ps ON ps. ID = pd.nocmfk
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                INNER JOIN strukresep_t AS sr ON sr.pasienfk = apd.norec
                LEFT JOIN pegawai_m AS pg ON sr.penulisresepfk = pg. ID
                LEFT JOIN ruangan_m AS ru2 ON ru2. ID = sr.ruanganfk
                WHERE pd.kdprofile = $idProfile and 
                    sr.tglresep BETWEEN '$tglAwal'
                AND '$tglAkhir'
                and pd.statusenabled=true
                 and (sr.statusenabled is null or sr.statusenabled = true)
               "));
        }else{
            $data = DB::table('antrianpasiendiperiksa_t as apd')
                ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
                ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
                ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
                ->leftjoin ('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
                ->select('pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien','ru.namaruangan',
                    'pd.tglpulang')
                ->where('apd.kdprofile',$idProfile)
                ->whereBetween('apd.tglregistrasi',[$tglAwal,$tglAkhir])
                ->where('apd.statusenabled',true)
                ->where('ru.objectdepartemenfk',$idDept)
                ->get();
        }


        $result = array(
            'count' => count($data),
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function getPendapatanRumahSakit(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdKelompokPasienUmum = (int) $this->settingDataFixed('KdKelompokPasienUmum',$idProfile);
        $data =[];

        if($request['tipe'] == 'sehari'){
            $tglAwal = $request['tglAwal'];
            $tglAkhir = $request['tglAkhir'];
        }
        if($request['tipe'] == 'seminggu'){
            $tglAwal = Carbon::now()->subWeek(1)->toDateString();//  Carbon::now()->subMonth(1);
            $tglAwal = date($tglAwal.' 00:00');
            $tglAkhir =date('Y-m-d 23:59');
        }


        //region Old Query
//        $data =DB::select(DB::raw("
//         SELECT p.tglpencarian,p.nocm,p.noregistrasi,p.namapasien,
//            p.namaruangan,p.namalengkap,p.kelompokpasien,p.namadepartemen,
//            SUM (p.karcis + p.embos +p.konsul + p.tindakan -p.diskon) AS total
//            FROM
//                (SELECT p.tglpelayanan as tglpencarian,* FROM v_eispendapatan AS p
//                            WHERE p.tglpelayanan BETWEEN  '$tglAwal' and '$tglAkhir'
//                    AND p.objectjenisprodukfk <> 97
//                    AND p.norecbatal IS NULL
//                    ORDER BY p.tglpelayanan
//                ) AS p
//            GROUP BY p.tglpencarian,p.nocm,p.noregistrasi,p.namapasien,
//            p.namaruangan,p.namalengkap,p.kelompokpasien,p.namadepartemen
//         UNION ALL
//            select p.tglpencarian , p.nocm,p.noregistrasi,p.namapasien,p.ruanganapotik as namaruangan,p.namalengkap,p.kelompokpasien,
//            p.namadepartemen, sum(p.subtotal - p.diskon  + p.jasa) as total
//            from (
//                    select sr.tglresep as tglpencarian, sr.noresep,pd.tglregistrasi,pd.noregistrasi, ps.nocm,upper(ps.namapasien) as namapasien,
//                     kp.kelompokpasien, pg.namalengkap, ru2.namaruangan, ru.namaruangan as ruanganapotik, dp.namadepartemen,
//                    pp.jumlah, pp.hargajual as harga, pp.rke as rke, (pp.jumlah)*(pp.hargajual) as subtotal,
//                     case when pp.hargadiscount is null then 0 else pp.hargadiscount end as diskon,
//                    case when pp.jasa is null then 0 else pp.jasa end as jasa, 0 as ppn, (pp.jumlah*pp.hargajual)-0-0-0 as total,
//                     case when pd.nosbmlastfk is null then 'n' else'p' end as statuspaid,
//                    pg3.namalengkap as namakasir
//                     from strukresep_t as sr
//                     left join pelayananpasien_t as pp on pp.strukresepfk = sr.norec
//                     left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                     left join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
//                     left join pasien_m as ps on ps.id=pd.nocmfk
//                     left join jeniskelamin_m as jk on jk.id=ps.objectjeniskelaminfk
//                     left join pegawai_m as pg on pg.id=sr.penulisresepfk
//                     left join ruangan_m as ru on ru.id=sr.ruanganfk
//                     left join ruangan_m as ru2 on ru2.id=apd.objectruanganfk
//                     left join departemen_m as dp on dp.id=ru.objectdepartemenfk
//                     left join kelompokpasien_m kp on kp.id=pd.objectkelompokpasienlastfk
//                     left join strukbuktipenerimaan_t as sbm on sbm.norec = pd.nosbmlastfk
//                     left join loginuser_s as lu on lu.id = sbm.objectpegawaipenerimafk
//                     left join pegawai_m as pg3 on pg3.id = lu.objectpegawaifk
//                     where sr.tglresep between   '$tglAwal' and '$tglAkhir'
//              UNION ALL
//                    select  sp.tglstruk as tglpencarian,
//                    sp.nostruk,
//                    sp.tglstruk as tglregistrasi,
//                    '-' as noregistrasi, sp.nostruk_intern as nocm,upper(sp.namapasien_klien) as namapasien, 'Umum/Pribadi' as kelompokpasien,
//                    pg.namalengkap, '-' as namaruangan, ru.namaruangan as ruanganapotik, dp.namadepartemen,
//                    spd.qtyproduk as jumlah, spd.hargasatuan as harga, cast(spd.resepke as text) as rke,
//                     (spd.qtyproduk)*(spd.hargasatuan) as subtotal,
//                    case when spd.hargadiscount is null then 0 else spd.hargadiscount end as diskon,
//                    case when spd.hargatambahan is null then 0 else spd.hargatambahan end as jasa,
//                     0 as ppn,
//                    (spd.qtyproduk*spd.hargasatuan)-0-0-0 as total,
//                    case when sp.nosbmlastfk is null then 'n' else'p' end as statuspaid,
//                    pg3.namalengkap as namakasir
//                     from strukpelayanan_t as sp
//                     left join strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
//                     left join pegawai_m as pg on pg.id=sp.objectpegawaipenanggungjawabfk
//                     left join strukbuktipenerimaan_t as sbm on sbm.norec = sp.nosbmlastfk
//                     left join pegawai_m as pg2 on pg2.id = sbm.objectpegawaipenerimafk
//                     left join loginuser_s as lu on lu.id = sbm.objectpegawaipenerimafk
//                     left join pegawai_m as pg3 on pg3.id = lu.objectpegawaifk
//                     left join ruangan_m as ru on ru.id=sp.objectruanganfk
//                     left join departemen_m as dp on dp.id=ru.objectdepartemenfk
//                     where sp.tglstruk between '$tglAwal' and '$tglAkhir' and sp.nostruk_intern='-' and substring(sp.nostruk
//                     from 1 for 2)='OB'
//               UNION ALL
//                    select  sp.tglstruk as tglpencarian,
//                    sp.nostruk,  sp.tglstruk as tglregistrasi, '-' as noregistrasi,sp.nostruk_intern as nocm,
//                    upper(sp.namapasien_klien) as namapasien,  'Umum/Pribadi' as kelompokpasien,
//                    pg.namalengkap, '-' as namaruangan, ru.namaruangan as ruanganapotik, dp.namadepartemen,
//                    spd.qtyproduk as jumlah, spd.hargasatuan as harga, cast(spd.resepke as text) as rke,
//                    (spd.qtyproduk)*(spd.hargasatuan) as subtotal,
//                    case when spd.hargadiscount is null then 0 else spd.hargadiscount end as diskon,
//                    case when spd.hargatambahan is null then 0 else spd.hargatambahan end as jasa,
//                     0 as ppn,(spd.qtyproduk*spd.hargasatuan)-0-0-0 as total,
//                    case when sp.nosbmlastfk is null then 'n' else'p' end as statuspaid,
//                    pg3.namalengkap as namakasir
//                     from strukpelayanan_t as sp
//                     left join strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
//                     left join pasien_m as ps on ps.nocm=sp.nostruk_intern
//                     left join jeniskelamin_m as jk on jk.id=ps.objectjeniskelaminfk
//                     left join pegawai_m as pg on pg.id=sp.objectpegawaipenanggungjawabfk
//                     left join strukbuktipenerimaan_t as sbm on sbm.norec = sp.nosbmlastfk
//                     left join pegawai_m as pg2 on pg2.id = sbm.objectpegawaipenerimafk
//                     left join loginuser_s as lu on lu.id = sbm.objectpegawaipenerimafk
//                     left join pegawai_m as pg3 on pg3.id = lu.objectpegawaifk
//                     left join ruangan_m as ru on ru.id=sp.objectruanganfk
//                     left join departemen_m as dp on dp.id=ru.objectdepartemenfk
//                     where sp.tglstruk between   '$tglAwal' and '$tglAkhir'   and sp.nostruk_intern not in ('-') and substring(sp.nostruk
//                     from 1 for 2)='OB'
//                    -- order by tglresep
//          ) as p WHERE p.total is not null
//          GROUP BY  p.tglpencarian, p.nocm,p.noregistrasi,p.namapasien,p.ruanganapotik ,p.namalengkap,p.kelompokpasien,
//            p.namadepartemen
//
//           "));
        //endregion
        $data =DB::select(DB::raw("
        
      SELECT
    x.tglpencarian,
    x.namaruangan,
    x.namadepartemen,
    x.kelompokpasien,
    SUM (x.total) AS total
FROM
    (
        SELECT
            to_char (
                pp.tglpelayanan,
                'yyyy-MM-dd HH:mm'
            ) AS tglpencarian,
            ru.namaruangan,
            dpm.namadepartemen,
            kps.kelompokpasien,
            SUM (
                (
                    (
                        CASE
                        WHEN pp.hargajual IS NULL THEN
                            0
                        ELSE
                            pp.hargajual
                        END - CASE
                        WHEN pp.hargadiscount IS NULL THEN
                            0
                        ELSE
                            pp.hargadiscount
                        END
                    ) * pp.jumlah
                ) + CASE
                WHEN pp.jasa IS NULL THEN
                    0
                ELSE
                    pp.jasa
                END
            ) AS total
        FROM
            pelayananpasien_t AS pp
        JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
        JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
        JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
        LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
        LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
        WHERE pp.kdprofile = $idProfile and
            pp.tglpelayanan BETWEEN '$tglAwal'
        AND '$tglAkhir'
        AND pp.strukresepfk IS NULL
        AND pd.statusenabled = true
        GROUP BY
            ru.namaruangan,
            dpm.namadepartemen,
            kps.kelompokpasien,
            pp.tglpelayanan
        UNION ALL
            SELECT
                to_char (
                    sp.tglstruk,
                    'yyyy-MM-dd HH:mm'
                ) AS tglpencarian,
                ru.namaruangan,
                dp.namadepartemen,
                'Umum/Pribadi' AS kelompokpasien,
                SUM (
                    spd.qtyproduk * (
                        spd.hargasatuan - CASE
                        WHEN spd.hargadiscount IS NULL THEN
                            0
                        ELSE
                            spd.hargadiscount
                        END
                    ) + CASE
                    WHEN spd.hargatambahan IS NULL THEN
                        0
                    ELSE
                        spd.hargatambahan
                    END
                ) AS total
            FROM
                strukpelayanan_t AS sp
            JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = sp.objectruanganfk
            LEFT JOIN departemen_m AS dp ON dp.id = ru.objectdepartemenfk
            WHERE sp.kdprofile = $idProfile and
                sp.tglstruk BETWEEN '$tglAwal'
            AND '$tglAkhir'
            AND sp.nostruk LIKE 'OB%'
            AND sp.statusenabled <> false
            GROUP BY
                sp.tglstruk,
                ru.namaruangan,
                dp.namadepartemen
            UNION ALL
                SELECT
                    to_char (
                        pp.tglpelayanan,
                        'yyyy-MM-dd HH:mm'
                    ) AS tglpencarian,
                    ru.namaruangan,
                    dpm.namadepartemen,
                    kps.kelompokpasien,
                    SUM (
                        (
                            (
                                CASE
                                WHEN pp.hargajual IS NULL THEN
                                    0
                                ELSE
                                    pp.hargajual
                                END - CASE
                                WHEN pp.hargadiscount IS NULL THEN
                                    0
                                ELSE
                                    pp.hargadiscount
                                END
                            ) * pp.jumlah
                        ) + CASE
                        WHEN pp.jasa IS NULL THEN
                            0
                        ELSE
                            pp.jasa
                        END
                    ) AS total
                FROM
                    pelayananpasien_t AS pp
                JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
                JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                JOIN ruangan_m AS ru ON ru.id = sr.ruanganfk
                LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
                LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
                WHERE pp.kdprofile = $idProfile and
                    pp.tglpelayanan BETWEEN '$tglAwal'
                AND '$tglAkhir'
                AND pp.strukresepfk IS NOT NULL
                AND pd.statusenabled = true
                GROUP BY
                    ru.namaruangan,
                    dpm.namadepartemen,
                    kps.kelompokpasien,
                    pp.tglpelayanan
    ) AS x
GROUP BY
    x.tglpencarian,
    x.kelompokpasien,
    x.namaruangan,
    x.namadepartemen
   

           "));
    if(count($data) >0){
        foreach ($data as $key => $row) {
            $count[$key] = $row->tglpencarian;
        }


        array_multisort($count, SORT_ASC, $data);
    }


        $result = array(
            'data' => $data,
//            'count' => count($data),
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
//        SELECT
//            p.tglregistrasi,p.nocm,p.noregistrasi,p.namapasien,
//            p.ruanganfarmasi as namaruangan,p.namalengkap,p.kelompokpasien,'Instalasi Farmasi' as  namadepartemen,
//            SUM (p.farmasi) AS total
//            FROM(
//                SELECT * FROM v_eispendapatan AS p
//                WHERE p.tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
//    AND p.norecbatal IS NULL
//    and p.statuenabledfarmasi =1
//                ORDER BY p.noregistrasi ASC
//            ) AS p
//            GROUP BY  p.tglregistrasi,p.nocm,p.noregistrasi,p.namapasien,
//            p.ruanganfarmasi,p.namalengkap,p.kelompokpasien,p.namadepartemen
//        UNION ALL
//            SELECT
//           p.tglstruk as tglregistrasi ,p.nostruk_intern as nocm,p.nostruk as noregistrasi,p.namapasien_klien as namapasien,
//            p.namaruangan,p.namalengkap, 'Umum/Pribadi' as kelompokpasien,'Instalasi Farmasi' as  namadepartemen,
//            SUM (p.totalharusdibayar) AS total
//            FROM(
//                SELECT * FROM strukpelayanan_t AS p
//                              left join pegawai_m as pg on pg.id = p.objectpegawaipenanggungjawabfk
//                              left join ruangan_m as ru on ru.id = p.objectruanganfk
//                WHERE p.tglstruk BETWEEN '$tglAwal' and '$tglAkhir'
//    and p.objectkelompoktransaksifk =2 and p.statusenabled =1
//    and ru.objectdepartemenfk=14
//                ORDER BY p.nostruk ASC
//            ) AS p
//            GROUP BY  p.tglstruk,p.nostruk_intern,p.nostruk,p.namapasien_klien,
//            p.namaruangan,p.namalengkap,p.namarekanan
    }
    public function getPenerimaanKasir(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('strukbuktipenerimaan_t as sbm')
            ->leftJOIN('strukbuktipenerimaancarabayar_t as sbmc', 'sbmc.nosbmfk', '=', 'sbm.norec')
            ->leftJOIN('carabayar_m as cb', 'cb.id', '=', 'sbmc.objectcarabayarfk')
            ->join('strukpelayanan_t as sp', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->leftJOIN('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftJOIN('pegawai_m as pg2', 'pg2.id', '=', 'lu.objectpegawaifk')
            ->leftJOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'sp.noregistrasifk')
            ->leftJOIN('pasien_m as ps', 'ps.id', '=', 'sp.nocmfk')
            ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftJOIN('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJOIN('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->leftJOIN('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->select('sbm.tglsbm', 'ps.nocm', 'ru.namaruangan', 'pg.namalengkap', 'pg2.namalengkap as kasir',
                'sp.totalharusdibayar', 'sbm.totaldibayar','ru.objectdepartemenfk','ru.id as ruid','dp.namadepartemen',
                DB::raw('( case when pd.noregistrasi is null then sp.nostruk else pd.noregistrasi end) as noregistrasi, 
                (case when ps.namapasien is null then sp.namapasien_klien else ps.namapasien end) as namapasien,
                (case when kp.kelompokpasien is null then null else kp.kelompokpasien end) as kelompokpasien,
                (CASE WHEN sp.totalprekanan is null then 0 else sp.totalprekanan end) as hutangpenjamin,
                (case when cb.id = 1 then sbm.totaldibayar else 0 end) as tunai, 
                (case when cb.id > 1 then sbm.totaldibayar else 0 end) as nontunai')
            )
            ->where('sbm.kdprofile',$idProfile);
//            ->where('djp.objectjenisprodukfk','<>',97)
//            ->whereNull('sp.statusenabled')
//            ->where('ru.objectdepartemenfk',18);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sbm.tglsbm', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('sbm.tglsbm', '<=', $tgl);
        }
        if (isset($request['idKasir']) && $request['idKasir'] != "" && $request['idKasir'] != "undefined") {
            $data = $data->where('pg2.id', '=', $request['idKasir']);
        }
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $data = $data->where('pd.objectpegawaifk', '=', $request['idDokter']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('pd.objectruanganlastfk', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('kp.id', '=', $request['kelompokPasien']);
        }


        $data = $data->orderBy('pd.noregistrasi', 'ASC');

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'inhuman'
        );
        return $this->respond($result);
    }

    public function getCountPegawai(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kateg = SettingDataFixed::where('namafield', 'statusDataPegawaiException')->where('kdprofile', $idProfile)->first();
        $keduduk = SettingDataFixed::where('namafield', 'listDataKedudukanException')->where('kdprofile', $idProfile)->first();
        $jenisKelamin = DB::select(DB::raw("select count ( x.namalengkap) as total, x.jeniskelamin from (
                select jp.jeniskelamin,pg.namalengkap 
                from pegawai_m  as pg
                left JOIN jeniskelamin_m as jp on jp.id =pg.objectjeniskelaminfk
                where pg.statusenabled=true
                --and pg.kedudukanfk not in ($keduduk->nilaifield)
                --and pg.kategorypegawai in ($kateg->nilaifield)
                 )as x GROUP BY x.jeniskelamin"));
        $kategoryPegawai = DB::select(DB::raw("select count ( x.namalengkap) as total, x.kategorypegawai from (
                select jp.kategorypegawai,pg.namalengkap 
                from pegawai_m  as pg
                left JOIN kategorypegawai_m as jp on jp.id =pg.kategorypegawai
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                 -- and pg.kedudukanfk not in ($keduduk->nilaifield)
               -- and pg.kategorypegawai in ($kateg->nilaifield)
                )as x GROUP BY x.kategorypegawai"));
        $kelompokJabatan = DB::select(DB::raw("select count ( x.id) as total, x.namakelompokjabatan from (
                select jp.detailkelompokjabatan as namakelompokjabatan,pg.namalengkap ,pg.id
                from pegawai_m  as pg
                inner JOIN nilaikelompokjabatan_m as jp on jp.id =pg.objectkelompokjabatanfk
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                --and pg.kedudukanfk not in ($keduduk->nilaifield)
               -- and pg.kategorypegawai in ($kateg->nilaifield)
                 )as x GROUP BY x.namakelompokjabatan"));
        $unitKerja = DB::select(DB::raw("
            
            select count ( x.id) as total, x.unitkerja from (
            select uk.name as unitkerja,pg.namalengkap ,pg.id
            from pegawai_m as pg
            left JOIN mappegawaijabatantounitkerja_m as jp on jp.objectpegawaifk =pg.id
            left JOIN subunitkerja_m as su on jp.objectsubunitkerjapegawaifk=su.id 
            left JOIN unitkerjapegawai_m as uk on jp.objectunitkerjapegawaifk =uk.id 
            where pg.kdprofile = $idProfile and pg.statusenabled=true and jp.isprimary=true
              -- and pg.kedudukanfk not in ($keduduk->nilaifield)
               -- and pg.kategorypegawai in ($kateg->nilaifield)
              )
            as x GROUP BY x.unitkerja
                "));
        $statusPegawai = DB::select(DB::raw("select count ( x.id) as total,x.jenis as statuspegawai from (
                select pg.namalengkap ,pg.id, case when pg.kedudukanfk not in ($keduduk->nilaifield)  
                  and pg.kategorypegawai in ($kateg->nilaifield) then 'tetap' else 'outsourcing' end as jenis
                from pegawai_m  as pg
                left JOIN statuspegawai_m as jp on jp.id =pg.objectstatuspegawaifk
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                )as x
                GROUP BY x.jenis
                "));

        $kedudukanPeg = DB::select(DB::raw("select count ( x.namalengkap) as total, x.kedudukan  from (
                select jp.name as kedudukan,pg.namalengkap from pegawai_m  as pg
                left JOIN sdm_kedudukan_m as jp on jp.id =pg.kedudukanfk
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                --and pg.kedudukanfk not in ($keduduk->nilaifield)
              
                )as x
                GROUP BY x.kedudukan
                "));

        $pendidikan = DB::select(DB::raw("select x.total, x.pendidikan  from (
                select jp.pendidikan, count(pg.namalengkap) as total from pegawai_m  as pg
                left JOIN pendidikan_m as jp on pg.objectpendidikanterakhirfk = jp.id
                where pg.statusenabled=true
               -- and pg.kedudukanfk not in ($keduduk->nilaifield)
               -- and pg.kategorypegawai in ($kateg->nilaifield)
                 GROUP by jp.pendidikan
                )as x
                order by x.total  
                "));
        $usia = DB::select(DB::raw("
                select pg.namalengkap,pg.tgllahir ,
                --CONVERT(int,ROUND(DATEDIFF(hour,pg.tgllahir,GETDATE())/8766.0,0)) AS umur
                 date_part('year',pg.tgllahir ) as umur
                 from pegawai_m  as pg
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                --and pg.kedudukanfk not in ($keduduk->nilaifield)
                --and pg.kategorypegawai in ($kateg->nilaifield)
                "));
        $under20 = 0;
        $under30 =0;
        $under40 =0;
        $under50 =0;
        $up51=0;
        $usiaa =[];
        foreach ($usia as $itemu){
            if($itemu->umur <= 20){
                $under20 = $under20 +1;
            }
            if( $itemu->umur > 20 && $itemu->umur <= 30){
                $under30 = $under30 +1;
            }
            if($itemu->umur > 30 && $itemu->umur <=40){
                $under40 = $under40 +1;
            }
            if($itemu->umur > 40 && $itemu->umur <=50){
                $under50 = $under50 +1;
            }
            if($itemu->umur > 50){
                $up51 = $up51 +1;
            }
        }
        $usiaa []= array(
            'total' => $under20,
            'usia' => 'dibawah 20 Tahun',
        );
        $usiaa []= array(
            'total' => $under30,
            'usia' => '21 s/d 30 Tahun',
        );
        $usiaa []= array(
            'total' => $under40,
            'usia' => '31 s/d 40 Tahun',
        );
        $usiaa []= array(
            'total' => $under50,
            'usia' => '41 s/d 50 Tahun',
        );
        $usiaa []= array(
            'total' => $up51,
            'usia' =>'diatas 51 Tahun' ,
        );
        $tglAwal = Carbon::now()->startOfMonth();
        $tglAkhir = Carbon::now()->endOfMonth();
        $dataPensiun = DB::select(DB::raw("
            select pg.id,pg.namalengkap,to_char(pg.tglpensiun,'YYYY-MM-DD') as tglpensiun,to_char (pg.tgllahir,'YYYY-MM-DD') as tgllahir,
            pg.nippns,gp.golonganpegawai,
            pdd.pendidikan,sm.name as subunitkerja,uk.name as unitkerja
            from mappegawaijabatantounitkerja_m as mappe
            left join pegawai_m as pg on mappe.objectpegawaifk =pg.id
            left join golonganpegawai_m as gp on pg.objectgolonganpegawaifk = gp.id
            left join pendidikan_m as pdd on pg.objectpendidikanterakhirfk = pdd.id
            left join subunitkerja_m sm on mappe.objectsubunitkerjapegawaifk = sm.id
            left join unitkerjapegawai_m  as uk on mappe.objectunitkerjapegawaifk = uk.id
            where mappe.kdprofile = $idProfile and mappe.isprimary=true
            and pg.tglpensiun between '$tglAwal' and '$tglAkhir'
            order by pg.namalengkap"));

        $pensiun['tglAwal'] =$tglAwal;
        $pensiun['tglAkhir'] =$tglAkhir;
        $pensiun['bulan'] = Carbon::now()->format('F Y');
        $pensiun['data'] =$dataPensiun;

        $result = array(
            'jeniskelamin' => $jenisKelamin,
            'countjk'=>count($jenisKelamin),
            'kategoripegawai' => $kategoryPegawai,
            'kelompokjabatan' => $kelompokJabatan,
            'unitkerjapegawai' => $unitKerja,
            'statuspegawai' => $statusPegawai,
            'kedudukan' => $kedudukanPeg,
            'pendidikan' => $pendidikan,
            'usia' => $usiaa,
            'datapensiun' => $pensiun,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function getInfoStok(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::select(DB::raw("select sum(spd.qtyproduk) as qtyproduk,prd.namaproduk,
                ru.namaruangan,ss.satuanstandar
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                inner JOIN ruangan_m as ru on ru.id=spd.objectruanganfk
                inner JOIN produk_m as prd on prd.id=spd.objectprodukfk
                left JOIN satuanstandar_m as ss on ss.id=prd.objectsatuanstandarfk
                inner JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                where spd.kdprofile = $idProfile and spd.qtyproduk > 0 
                and prd.statusenabled=true 
                and ru.statusenabled=true  
                group by prd.namaproduk,ru.namaruangan,ss.satuanstandar
                order by prd.namaproduk"));
        if(count($data) > 0){
            foreach ($data as $key => $row) {
                $count[$key] = $row->qtyproduk;
            }
            array_multisort($count, SORT_DESC, $data);
        }


        $result= array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function getPenerimaanRealisasiTarget (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $tglAwal= Carbon::now()->startOfYear()->format('Y-m-d 00:00');
        $tglAkhir =Carbon::now()->endOfYear()->format('Y-m-d 23:59');
//        $data = DB::select(DB::raw("select x.blnpelayanan,sum(x.total) as total, sum(x.volume) as volume, x.keterangan from
//                             (select Format(tp.tglpelayanan, 'MM')as blnpelayanan, pd.noregistrasi, ru.namaruangan, tp.produkfk,
//                             (case when ru2.objectdepartemenfk <> 16 then 'Pendapatan R.Jalan'
//                             else 'Pendapatan R.Inap' end)as keterangan,
//                             (case when tp.hargajual is null then 0 else tp.hargajual end-(case when tp.hargadiscount is null then 0 else tp.hargadiscount end))*tp.jumlah as total,
//                             tp.jumlah as volume
//                             from pasiendaftar_t as pd left JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
//                             left join pelayananpasien_t as tp on tp.noregistrasifk = apd.norec
//                             LEFT JOIN produk_m AS pro ON tp.produkfk = pro.id
//                             left JOIN detailjenisproduk_m as djp on djp.id=pro.objectdetailjenisprodukfk
//                             left JOIN jenisproduk_m as jp on jp.id=djp.objectjenisprodukfk
//                             left JOIN kelompokproduk_m as kp on kp.id=jp.objectkelompokprodukfk
//                           left JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
//                           left JOIN ruangan_m as ru2 on ru2.id=pd.objectruanganlastfk
//                             left join mapjurnalmanual as map on map.objectruanganfk = ru.id and map.jpid=jp.id or map.jpid=jp.id and map.objectruanganfk = 999
//                             left join departemen_m as dp on dp.id = ru.objectdepartemenfk inner JOIN pasien_m as ps on ps.id=pd.nocmfk
//                             where tp.tglpelayanan between :tglAwal and :tglAkhir and tp.produkfk not in (402611,10011571,10011572) and tp.strukresepfk is null and map.jenis='Pendapatan'
//                           order by pro.namaproduk) as x
//                           group by x.blnpelayanan,x.keterangan
//                           order by x.blnpelayanan"),
//            array(
//                'tglAwal' => $tglAwal,
//                'tglAkhir' => $tglAkhir,
//            )
//        );
        $data = DB::select(DB::raw("select x.blnpelayanan,sum(x.total) as total, sum(x.volume) as volume, x.keterangan
                    from(
                    select Format(pp.tglpelayanan, 'MM')as blnpelayanan,
                    (case when ru.objectdepartemenfk <> $idDepRanap then 'Pendapatan R.Jalan' else 'Pendapatan R.Inap' end)as keterangan,
                    sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) 
                    * pp.jumlah )+ case when pp.jasa is null then 0 else pp.jasa end ) as total,sum(pp.jumlah) as volume
                    from pelayananpasien_t as pp
                    join antrianpasiendiperiksa_t as apd on apd.norec= pp.noregistrasifk
                    join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
                    join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                    where pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN  '$tglAwal' and '$tglAkhir'  
                     and pp.strukresepfk is null
                    GROUP BY pp.tglpelayanan,ru.objectdepartemenfk
                    ) as x
                    GROUP BY x.blnpelayanan, x.keterangan
                    order by x.blnpelayanan asc"));
        $dataHeader1=[];
        $dataHeader=[];
        foreach ($data as $item) {
            $arr=[];
            $arr[]=(integer)$item->total;
            if ($item->keterangan == 'Pendapatan R.Jalan') {
                $dataTargetHeader = \DB::table('targetkinerja_m as tk')
                    ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                    ->where('pelayanan','Total Rawat Jalan & IGD')
                    ->where('tk.kdprofile', $idProfile)
                    ->where('tk.statusenabled',1)
                    ->orderBy('tk.pelayanan')
                    ->get();

                foreach ($dataTargetHeader as $get) {
                    $dataHeader[] = array(
//                        'id' => $item->id,
                        'blnpelayanan' => $item->blnpelayanan,
                        'pelayanan' => $item->keterangan,
                        'totaltarget' =>  number_format($get->targetrupiah / 12, 2, '.', ''),
                        'volumetarget' =>  number_format($get->targetvolume/ 12, 2, '.', ''),
                        'total' => $item->total,
                        'volume' => $item->volume,
//                        'detail' => $dataIsi,
                    );
                }
            }elseif ($item->keterangan == 'Pendapatan R.Inap'){
                $dataTargetHeader = \DB::table('targetkinerja_m as tk')
                    ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                    ->where('tk.kdprofile', $idProfile)
                    ->where('pelayanan','Total Rawat Inap')
                    ->where('tk.statusenabled',1)
                    ->orderBy('tk.pelayanan')
                    ->get();

                foreach ($dataTargetHeader as $gets) {
                    $dataHeader1[] = array(
//                        'id' => $item->id,
                        'blnpelayanan' => $item->blnpelayanan,
                        'pelayanan' => $item->keterangan,
                        'totaltargetinap' =>   number_format($gets->targetrupiah / 12, 2, '.', ''),
                        'volumetargetinap' =>  number_format($gets->targetvolume / 12, 2, '.', ''),
                        'totalinap' => $item->total,
                        'volumeinap' => $item->volume,
//                        'detail' => $dataIsi1,
                    );
                }
            }
        }
        $result = array(
            'Rajal' => $dataHeader,
            'Ranap' => $dataHeader1,
//            'maxDataTot' => $maxDataTot,
//            'maxtDataVol' => $maxtDataVol,
            'message' => '@UNKNOWS',
        );
        return $this->respond($result);
    }
    public function displayReportBilling(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $fromDate = date('Y-m-d 00:00');//$request->input('from_date');
        $toDate = date('Y-m-d 23:00');
//        $sortBy = $request->input('sort_by');

        $title = 'Registered User Report'; // Report title

        $meta = [ // For displaying filters description on header
            'Registered on' => $fromDate . ' To ' . $toDate,
//            'Sort By' => $sortBy
        ];

        $queryBuilder = PelayananPasien::select(['produkfk', 'hargasatuan', 'tglpelayanan'])
        ->where('kdprofile', $idProfile)// Do some querying..
        ->whereBetween('tglpelayanan', [$fromDate, $toDate]);
//            ->orderBy($sortBy);

        $columns = [
            'Name' => function($customer) {
                return $customer->produkfk;
            },
            'Registered At' => function($customer) {
                return $customer->tglpelayanan;
            },
            'Total Balance' => function($customer) {
                return $customer->hargasatuan;
            },
            'Status' => function($customer) { // You can do if statement or any action do you want inside this closure
                return ($customer->hargasatuan > 100000) ? 'Rich Man' : 'Normal Guy';
            }
        ];
        $pdf =PdfReport::of ($title, $meta, $queryBuilder, $columns)
            ->editColumn('Registered At', [ // Change column class or manipulate its data for displaying to report
                'displayAs' => function($result) {
                    return $result->tglpelayanan->format('d M Y');
                },
                'class' => 'left'
            ])
            ->editColumns(['Total Balance', 'Status'], [ // Mass edit column
                'class' => 'right bold'
            ])
            ->setPaper('a4')
            ->showTotal([ // Used to sum all value on specified column on the last table (except using groupBy method). 'point' is a type for displaying total with a thousand separator
                'Total Balance' => 'point' // if you want to show dollar sign ($) then use 'Total Balance' => '$'
            ])
            ->limit(20) // Limit record to be showed
            ->stream(); // other available method: download('filename') to download pdf / make() that will producing DomPDF / SnappyPdf instance so you could do any other DomPDF / snappyPdf method such as stream() or download()

        // Generate Report with flexibility to manipulate column class even manipulate column value (using Carbon, etc).
        return   $pdf ;}

    public function getPenerimaanRealisasiTargetGrid (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal= Carbon::now()->startOfYear()->format('Y-m-d 00:00');
        $tglAkhir =Carbon::now()->endOfYear()->format('Y-m-d 23:59');
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $KelompokPasienBpjs = (int) $this->settingDataFixed('KdKelPasienBpjs', $idProfile);
        $data =  DB::select(DB::raw("select sum(x.total) as total, sum(x.volume) as volume, x.keterangan
                    from(
                    select Format(pp.tglpelayanan, 'MM')as blnpelayanan,
                    (case when ru.objectdepartemenfk <> $idDepRanap then 'Pendapatan R.Jalan' else 'Pendapatan R.Inap' end)as keterangan,
                    sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) 
                    * pp.jumlah )+ case when pp.jasa is null then 0 else pp.jasa end ) as total,sum(pp.jumlah) as volume
                    from pelayananpasien_t as pp
                    join antrianpasiendiperiksa_t as apd on apd.norec= pp.noregistrasifk
                    join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
                    join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                    where pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN  :tglAwal and :tglAkhir
                   and pp.strukresepfk is null
                    GROUP BY pp.tglpelayanan,ru.objectdepartemenfk
                    ) as x
                    GROUP BY x.keterangan
          "),
            array(
                'tglAwal' => $tglAwal,
                'tglAkhir' => $tglAkhir,
            )
        );

        $i = 0;
        $jmlstok=0;
        $dataHeader=[];
        $dataHeader1=[];
        $dataIsi=[];
        $dataIsi1=[];
        $results =array();
        foreach ($data as $item) {
            if($item->keterangan == 'Pendapatan R.Jalan'){
                $details = DB::select(DB::raw("select x.tipepasien, sum(x.total) as total, sum(x.volume) as volume, x.keterangan
                        from(
                        select  (case when pd.objectkelompokpasienlastfk=$KelompokPasienBpjs then 'Pasien BPJS' ELSE
                       'Pasien Non BPJS' end) as tipepasien, Format(pp.tglpelayanan, 'MM')as blnpelayanan,
                        (case when ru.objectdepartemenfk <> $idDepRanap then 'Pendapatan R.Jalan' else 'Pendapatan R.Inap' end)as keterangan,
                        sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) 
                        * pp.jumlah )+ case when pp.jasa is null then 0 else pp.jasa end ) as total,sum(pp.jumlah) as volume
                        from pelayananpasien_t as pp
                        join antrianpasiendiperiksa_t as apd on apd.norec= pp.noregistrasifk
                        join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
                        join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                        where pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN  :tglAwal and :tglAkhir
                        and pp.strukresepfk is null
                        and ru.objectdepartemenfk <> $idDepRanap
                        GROUP BY pp.tglpelayanan,ru.objectdepartemenfk,pd.objectkelompokpasienlastfk
                        ) as x
                        GROUP BY x.keterangan,x.tipepasien
                       "),
                    array(
                        'tglAwal' => $tglAwal,
                        'tglAkhir' => $tglAkhir,
//                        'id' => $item->id,
                    )
                );
                foreach ($details as $item2) {
                    if ($item2->tipepasien == 'Pasien BPJS') {
                        $datas = \DB::table('targetkinerja_m as tk')
                            ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                            ->where('pelayanan','Pasien BPJS Rawat Jalan & IGD')
                            ->where('tk.statusenabled',1)
                            ->orderBy('tk.pelayanan')
                            ->get();

                        foreach ($datas as $get) {
                            $dataIsi[] = array(
//                                    'id' => $item2->id,
                                'pelayanan' => $item2->keterangan,
                                'pelayanantarget' => $get->pelayanan,
                                'totaltarget' => $get->targetrupiah,
                                'volumetarget' => (float) $get->targetvolume,
                                'total' => $item2->total,
                                'volume' => $item2->volume,
                            );
                        }
                    }elseif ($item2->tipepasien == 'Pasien Non BPJS') {
                        $datas = \DB::table('targetkinerja_m as tk')
                            ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                            ->where('pelayanan','Pasien Umum Rawat Jalan & IGD')
                            ->where('tk.statusenabled',1)
                            ->orderBy('tk.pelayanan')
                            ->get();

                        foreach ($datas as $get) {
                            $dataIsi[] = array(
//                                    'id' => $item2->id,
                                'pelayanan' => $item2->keterangan,
                                'pelayanantarget' => $get->pelayanan,
                                'totaltarget' => $get->targetrupiah,
                                'volumetarget' => $get->targetvolume,
                                'total' => $item2->total,
                                'volume' => $item2->volume,
                            );
                        }
                    }
                }
            }
            elseif ($item->keterangan == 'Pendapatan R.Inap'){
                $details = DB::select(DB::raw("select x.tipepasien, sum(x.total) as total, sum(x.volume) as volume, x.keterangan
                        from(
                        select  (case when pd.objectkelompokpasienlastfk = $KelompokPasienBpjs then 'Pasien BPJS' ELSE
                       'Pasien Non BPJS' end) as tipepasien, Format(pp.tglpelayanan, 'MM')as blnpelayanan,
                        (case when ru.objectdepartemenfk <> $idDepRanap then 'Pendapatan R.Jalan' else 'Pendapatan R.Inap' end)as keterangan,
                        sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) 
                        * pp.jumlah )+ case when pp.jasa is null then 0 else pp.jasa end ) as total,sum(pp.jumlah) as volume
                        from pelayananpasien_t as pp
                        join antrianpasiendiperiksa_t as apd on apd.norec= pp.noregistrasifk
                        join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
                        join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                        where  pp.tglpelayanan BETWEEN  :tglAwal and :tglAkhir
                        and pp.strukresepfk is null
                        and ru.objectdepartemenfk = $idDepRanap
                        GROUP BY pp.tglpelayanan,ru.objectdepartemenfk,pd.objectkelompokpasienlastfk
                        ) as x
                        GROUP BY x.keterangan,x.tipepasien
                       "),
                    array(
                        'tglAwal' => $tglAwal,
                        'tglAkhir' => $tglAkhir,
//                        'id' => $item->id,
                    )
                );
                foreach ($details as $item2) {
                    if ($item2->tipepasien == 'Pasien BPJS') {
                        $datas = \DB::table('targetkinerja_m as tk')
                            ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                            ->where('tk.kdprofile', $idProfile)
                            ->where('pelayanan','Pasien BPJS Rawat Inap')
                            ->where('tk.statusenabled',1)
                            ->orderBy('tk.pelayanan')
                            ->get();

                        foreach ($datas as $get) {
                            $dataIsi1[] = array(
//                                    'id' => $item2->id,
                                'pelayanan' => $item2->keterangan,
                                'pelayanantarget' => $get->pelayanan,
                                'totaltarget' => $get->targetrupiah,
                                'volumetarget' => $get->targetvolume,
                                'total' => $item2->total,
                                'volume' => $item2->volume,
                            );
                        }
                    }elseif ($item2->tipepasien == 'Pasien Non BPJS'){
                        $datas = \DB::table('targetkinerja_m as tk')
                            ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                            ->where('tk.kdprofile', $idProfile)
                            ->where('pelayanan','Pasien Umum Rawat Inap')
                            ->where('tk.statusenabled',1)
                            ->orderBy('tk.pelayanan')
                            ->get();

                        foreach ($datas as $get) {
                            $dataIsi1[] = array(
//                                    'id' => $item2->id,
                                'pelayanan' => $item2->keterangan,
                                'pelayanantarget' => $get->pelayanan,
                                'totaltarget' => $get->targetrupiah,
                                'volumetarget' => $get->targetvolume,
                                'total' => $item2->total,
                                'volume' => $item2->volume,
                            );
                        }
                    }
                }
            }
            if ($item->keterangan == 'Pendapatan R.Jalan') {
                $dataTargetHeader = \DB::table('targetkinerja_m as tk')
                    ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                    ->where('tk.kdprofile', $idProfile)
                    ->where('pelayanan','Total Rawat Jalan & IGD')
                    ->where('tk.statusenabled',1)
                    ->orderBy('tk.pelayanan')
                    ->get();

                foreach ($dataTargetHeader as $get) {
                    $dataHeader[] = array(
//                        'id' => $item->id,
                        'pelayanan' => $item->keterangan,
                        'totaltarget' => number_format( $get->targetrupiah/ 12, 2, '.', ''),
                        'volumetarget' =>  number_format( $get->targetvolume/ 12, 2, '.', ''),
                        'total' => $item->total,
                        'volume' => $item->volume,
                        'detail' => $dataIsi,
                    );
                }
            }elseif ($item->keterangan == 'Pendapatan R.Inap'){
                $dataTargetHeader = \DB::table('targetkinerja_m as tk')
                    ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                    ->where('tk.kdprofile', $idProfile)
                    ->where('pelayanan','Total Rawat Inap')
                    ->where('tk.statusenabled',1)
                    ->orderBy('tk.pelayanan')
                    ->get();

                foreach ($dataTargetHeader as $get) {
                    $dataHeader1[] = array(
//                        'id' => $item->id,
                        'pelayanan' => $item->keterangan,
                        'totaltarget' =>number_format( $get->targetrupiah/ 12, 2, '.', ''),
                        'volumetarget' =>number_format( $get->targetvolume/ 12, 2, '.', ''),
                        'total' => $item->total,
                        'volume' => $item->volume,
                        'detail' => $dataIsi1,
                    );
                }
            }
        }


        $result = array(
            'datajalan' => $dataHeader,
            'datainap' => $dataHeader1,
            'message' => 'giw',
        );

        return $this->respond($result);
    }
    public function getRealisasiTargetFarmasi (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal= Carbon::now()->startOfYear()->format('Y-m-d 00:00');
        $tglAkhir =Carbon::now()->endOfYear()->format('Y-m-d 23:59');
        $data = DB::select(DB::raw("select x.tipepasien,x.blnpelayanan,sum(x.total) as total,sum(x.volume) as volume from
                        (select (case when pd.objectkelompokpasienlastfk=2 then 'Pasien BPJS' ELSE
                         'Pasien Non BPJS' end) as tipepasien, Format(pp.tglpelayanan, 'MM')as blnpelayanan,
                        (case when ru.objectdepartemenfk <> 16 then 'Pendapatan R.Jalan' else 'Pendapatan R.Inap' end)as keterangan,
                        sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) 
                        * pp.jumlah )+ case when pp.jasa is null then 0 else pp.jasa end ) as total,sum(pp.jumlah) as volume
                        from pelayananpasien_t as pp
                        join antrianpasiendiperiksa_t as apd on apd.norec= pp.noregistrasifk
                        join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
                        join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                        where pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN  :tglAwal and :tglAkhir
                        and pp.strukresepfk is not null
                        GROUP BY pp.tglpelayanan,ru.objectdepartemenfk,pd.objectkelompokpasienlastfk) as x
                        group by x.tipepasien,x.blnpelayanan
                        order by x.blnpelayanan asc"),
            array(
                'tglAwal' => $tglAwal,
                'tglAkhir' => $tglAkhir,
            )
        );
        $datas=[];
        $datasu=[];
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        foreach ($data as $item) {
            if ($item->tipepasien == 'Pasien BPJS') {
                $dataTarget = \DB::table('targetkinerja_m as tk')
                    ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                    ->where('id',$idDepRaJal)
                    ->where('tk.statusenabled',1)
                    ->orderBy('tk.pelayanan')
                    ->get();
                foreach ($dataTarget as $get) {
                    $datas[] = array(
//                        'id' => $item->id,
                        'blnpelayanan' => $item->blnpelayanan,
                        'jenispasien' => $item->tipepasien,
                        'totaltarget' => number_format(  $get->targetrupiah/ 12, 2, '.', ''),
                        'volumetarget' => number_format(  $get->targetvolume/ 12, 2, '.', ''),
                        'total' => $item->total,
                        'volume' => $item->volume,
//                        'detail' => $dataIsi,
                    );
                }

            }elseif ($item->tipepasien == 'Pasien Non BPJS') {
                $dataTarget = \DB::table('targetkinerja_m as tk')
                    ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                    ->where('id',$idDepRaJal)
                    ->where('tk.statusenabled',1)
                    ->orderBy('tk.pelayanan')
                    ->get();

                foreach ($dataTarget as $get) {
                    $datasu[] = array(
//                        'id' => $item->id,
                        'blnpelayanan' => $item->blnpelayanan,
                        'jenispasien' => $item->tipepasien,
                        'totaltarget' => number_format(  $get->targetrupiah/ 12, 2, '.', ''),
                        'volumetarget' =>  number_format(  $get->targetvolume/ 12, 2, '.', ''),
                        'total' => $item->total,
                        'volume' => $item->volume,
//                        'detail' => $dataIsi,
                    );
                }
            }
        }
        $kdListTargetKinerja = explode(',',$this->settingDataFixed('KdTargetKinerja',$idProfile));
        $ListTargetKinerja = [];
        foreach ($kdListTargetKinerja as $item){
            $ListTargetKinerja []=  (int)$item;
        }
        $dataTarget = \DB::table('targetkinerja_m as tk')
            ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
            ->whereIn('id',$kdListTargetKinerja)
            ->where('tk.statusenabled',1)
            ->orderBy('tk.pelayanan')
            ->get();
        $result = array(
            'bpjs' => $datas,
            'nonbpjs' =>$datasu,
            'targetfarmasi' =>$dataTarget,
            'message' => 'giw',
        );
        return $this->respond($result);
    }

    public function getUsahaLainnya (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdTargetUsahaLainnya = (int) $this->settingDataFixed('KdTargetKinerjaUsahaLain', $idProfile);
        $tglAwal= Carbon::now()->startOfYear()->format('Y-m-d 00:00');
        $tglAkhir =Carbon::now()->endOfYear()->format('Y-m-d 23:59');
        $kdTransNonLayanan = (int) $this->settingDataFixed('kdTransaksiNonLayanan',$idProfile);
        $kdlistTargetUsahaLain = explode(',',$this->settingDataFixed('KdListTargetKinerjaUsahaLain',$idProfile));
        $listTargetUsahaLain = [];
        foreach ($kdlistTargetUsahaLain as $items){
            $listTargetUsahaLain [] =  (int)$items;
        }
        $data = DB::select(DB::raw("select x.tgl,sum(x.totalharusdibayar)as total from
                (select Format(sp.tglstruk, 'MM') as tgl,sp.nostruk,sp.qtyproduk,sp.totalharusdibayar,kt.kelompoktransaksi
                from strukpelayanan_t as sp 
                LEFT JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                LEFT JOIN kelompoktransaksi_m as kt on kt.id = sp.objectkelompoktransaksifk
                where sp.kdprofile = $idProfile and sp.tglstruk between :tglAwal and :tglAkhir  and
                kt.id in ($kdTransNonLayanan)) as x
                GROUP BY x.tgl
                order by x.tgl"),
            array(
                'tglAwal' => $tglAwal,
                'tglAkhir' => $tglAkhir,
            )
        );
        $datas=[];
        $datasu=[];
        foreach ($data as $item) {
            $dataTarget = \DB::table('targetkinerja_m as tk')
                ->select('tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
                ->where('id',$kdTargetUsahaLainnya)
                ->where('tk.statusenabled',1)
                ->orderBy('tk.pelayanan')
                ->get();
            foreach ($dataTarget as $get) {
                $datas[] = array(
                    'blnpelayanan' => $item->tgl,
                    'totaltarget' => $get->targetrupiah,
                    'total' => $item->total,
                );
            }
        }

        $dataTarget = \DB::table('targetkinerja_m as tk')
            ->select('tk.id','tk.pelayanan','tk.targetvolume','tk.targetrupiah','tk.tahun')
            ->whereIn('id',$listTargetUsahaLain)
            ->where('tk.statusenabled',1)
            ->orderBy('tk.pelayanan')
            ->get();

        $resTarget = [];
        $totalcapain = 0;
        $totalcapainBLU = 0;
        foreach ($data as $itemss){
            $totalcapain = $totalcapain + (float)$itemss->total;
        }
        foreach ( $dataTarget as $itemtarget){
            if ( $itemtarget->id == 8)//usaha lain
            {
                $jenis = 'PENDAPATAN BLU LAINNYA';
                $totalcapainBLU = $totalcapain;
            }else{
                $jenis = 'PENERIMAAN RUPIAH MURNI (RM)';

            }
            $resTarget []= array(
                'jenis' => $jenis,
                'pelayanan' => $itemtarget->pelayanan,
                'targetvolume' => (float) $itemtarget->targetvolume,
                'targetrupiah' =>(float)  $itemtarget->targetrupiah,
                'totalcapaian' => $totalcapainBLU,
                'tahun' => $itemtarget->tahun,
            );

        }

        $result = array(
            'chart' => $data,
            'grid' => $resTarget,
            'message' => 'giw',
        );
        return $this->respond($result);
    }

    public function getTrendPemakaianObat (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal= Carbon::now()->format('Y-m-d 00:00');
        $tglAkhir =Carbon::now()->format('Y-m-d 23:59');
        $data = DB::select(DB::raw("select * from
                (
                select sum(pp.jumlah) as jumlah,prd.namaproduk  
                from pelayananpasien_t  as pp 
                join produk_m as prd on pp.produkfk= prd.id
                where pp.tglpelayanan BETWEEN '$tglAwal' and  '$tglAkhir'
                and pp.strukresepfk is not null
                GROUP BY prd.namaproduk

                UNION ALL
                SELECT  sum(spd.qtyproduk) as jumlah,pr.namaproduk
               FROM strukpelayanan_t as sp  
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN '$tglAwal' and  '$tglAkhir'
                AND sp.nostruk_intern='-' AND substring(sp.nostruk,1,2)='OB'  
                and sp.statusenabled != false
                GROUP BY pr.namaproduk
                UNION ALL    
                 SELECT sum  (spd.qtyproduk) as jumlah,pr.namaproduk
               FROM strukpelayanan_t as sp  
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN '$tglAwal' and  '$tglAkhir'
              AND sp.nostruk_intern not in ('-') AND substring(sp.nostruk,1,2)='OB'  
                and sp.statusenabled != false
                        GROUP BY pr.namaproduk
                ) as x
                order by x.jumlah desc")
        );

        $result = array(
            'chart' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function getIndikatorRensar(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tahun = $request['tahun'];//Carbon::now()->format('Y');
        $data = DB::select(DB::raw("
                 select * from (
                    select  x.indikator as namaindikator, x.satuan, 
                    sum (x.capaian) / (case count(x.tanggal) when 0 then 1 else count(x.tanggal) end ) as capaian,
                    x.tahun,x.bulan, x.monthmm,
                    sum (x.targets) / (case count(x.targets) when 0 then 1 else count(x.targets) end )  as target, x.pic,
                    x.warnagrafik,x.urutan
                    from (
                        select head.indikator,head.satuan,dt.capaian,
                        dt.target,dt.tahun,dt.bulan,
                        Format(dt.tgl,'Month')  as tanggal,  Format(dt.tgl,'MM')  as monthmm,  head.pic,
                        cast(tg.target as float) as targets,head.warnagrafik,head.urutan
                        from indikatorrensar_m as head
                        INNER JOIN indikatorrensardetail_t as dt on dt.indikatorfk =head.id
                        left join targetindikator_m as tg on head.id= tg.indikatorrensarfk 
                        where head.kdprofile = $idProfile and dt.tahun ilike '$tahun%'
                        and tg.tahun  ilike '$tahun%'
                        and dt.statusenabled =1
                        and tg.kdprofile=1
                        and tg.statusenabled =1
                        and head.jenisindikatorfk = 5 --IKI DIRUT
                        order by dt.tgl asc
                    )as x 
                    GROUP BY x.indikator, x.satuan, x.pic,x.monthmm,
                    x.tahun,x.bulan,x.bulan,x.warnagrafik,x.urutan
                ) as y
                order BY y.monthmm
              ") );

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function saveIndikatorRensar(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $getHead = IndikatorRensar::where('id',$request['indikatorfk'])->first();
            if ($request['norec'] == ''){
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
            }else{
                $TP = IndikatorRensarDetail::where('norec', $request['norec'])->first();
            }

            $TP->bulan = $request['bulan'];
            $TP->indikatorfk =$request['indikatorfk'];
            $TP->capaian = $request['capaian'];
            $TP->denumerator = $request['denumerator'];
            $TP->numerator = $request['numerator'];
            $TP->tahun = $request['tahun'];
            $TP->tgl = $request['tgl'];
            $TP->save();

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarIndikatorRensar(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tahun = Carbon::now()->format('Y');
        $data = DB::table('indikatorrensar_m as head')
            ->join('indikatorrensardetail_t as dt','head.id','=','dt.indikatorfk')
            ->leftjoin('targetindikator_m as tg','head.id','=','tg.indikatorrensarfk')
            ->leftjoin('jenisindikator_m as ji','ji.id','=','head.jenisindikatorfk')
            ->select('dt.norec','head.indikator','head.satuan','dt.capaian','dt.indikatorfk',
//                    'dt.target',
                     'dt.tahun','dt.bulan','dt.tgl','head.pic','ji.jenisindikator','head.jenisindikatorfk',
                     'dt.numerator','dt.denumerator',
                DB::raw("EXTRACT (day from dt.tgl)  as tanggal,   sum( cast(tg.target as float) ) / count ( tg.target)as target"))
            ->where('head.kdprofile', $idProfile)
            ->where('dt.statusenabled',true)
//            ->where('dt.kdprofile','1')
            ->where('tg.statusenabled',true)
            ->where('ji.statusenabled',true)
            ->groupBy('dt.norec','head.indikator','head.satuan','dt.capaian','dt.indikatorfk',
//                    'dt.target',
                'dt.tahun','dt.bulan','dt.tgl','head.pic','ji.jenisindikator','head.jenisindikatorfk','dt.numerator','dt.denumerator')
            ->orderBy('dt.tgl','asc');


        if(isset($request['indikator']) && $request['indikator']!= ''){
            $data = $data->where('head.id',$request['indikator']);
        }
        if(isset($request['capaian']) && $request['capaian']!= ''){
            $data = $data->where('dt.capaian',$request['capaian']);
        }
        if(isset($request['target']) && $request['target']!= ''){
            $data = $data->where('dt.target',$request['target']);
        }
        if(isset($request['bulan']) && $request['bulan']!= ''){
            $data = $data->where('dt.bulan',$request['bulan']);
        }
        if(isset($request['tahun']) && $request['tahun']!= ''){
            $data = $data->where('dt.tahun',$request['tahun']);
        }
        if(isset($request['jenisindikatorfk']) && $request['jenisindikatorfk']!= ''){
            $data = $data->where('head.jenisindikatorfk',$request['jenisindikatorfk']);
        }
        if(isset($request['idDept']) && $request['idDept']!= ''){
            $data = $data->where('head.objectdepartemenfk',$request['idDept']);
        }
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function deleteIndikatorRensar(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if ($request['norec'] != '') {
                $TP = IndikatorRensarDetail::where('norec', $request['norec'])->where('kdprofile',$idProfile)->delete();
            }
            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getTargetIndikator(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tahun = Carbon::now()->format('Y');
        $data = DB::select(DB::raw("
                    select tg.id,ids.indikator, tg.tahun as tahuns, tg.target,tg.keterangan,ids.pic,
                    tg.indikatorrensarfk,ids.jenisindikatorfk,jd.jenisindikator
                    from targetindikator_m as tg
                    join indikatorrensar_m as ids on tg.indikatorrensarfk =ids.id
                    left join jenisindikator_m as jd on jd.id =ids.jenisindikatorfk
                    where tg.statusenabled = true and tg.kdprofile = $idProfile
                    and jd.statusenabled = true and jd.kdprofile = $idProfile
                    order by ids.urutan"

        ));

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function saveTargetIndikator(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $newID = TargetIndikator::max('id') + 1;
            if ($request['Id'] == null) {
                $TG = new TargetIndikator();
                $TG->id = $newID;
                $TG->kdprofile = $idProfile;
                $TG->statusenabled = 1;
            }else{
                $TG = TargetIndikator::where('id',$request['Id'])->first();
            }
            $TG->indikatorrensarfk = $request['Indikator'] ;
            $TG->tahun = $request['Tahun'] ;
            $TG->target = $request['Target'] ;
            if(isset($request['Keterangan'])){
                $TG->keterangan = $request['Keterangan'] ;
            }else{
                $TG->keterangan = '-';
            }

            $TG->save();

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TG,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteTargetIndikator(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $TG = TargetIndikator::where('id',$request['id'])
                ->where('kdprofile',$idProfile)
                ->update(
                [ 'statusenabled' => false]
            );
            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TG,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getCombo(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $indikator = IndikatorRensar::where('statusenabled',1)
            ->where('kdprofile',$idProfile)
            ->get();

        $Jenisindikator = JenisIndikator::where('statusenabled',1)
            ->where('kdprofile',$idProfile)
            ->get();

        $result = array(
            'indikator' => $indikator,
            'jenisindikator' => $Jenisindikator,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function saveJenisInidkator(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if ($request['id'] == ''){
                $TP = new JenisIndikator();
                $idMax = JenisIndikator::max('id');
                $TP->kdprofile = $idProfile;
                $TP->id = $idMax + 1 ;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = JenisIndikator::where('id', $request['id'])->first();
            }
            $TP->statusenabled = $request['statusenabled'];;
            $TP->kodeexternal = $request['kodeexternal'];
            $TP->namaexternal = $request['jenisindikator'];
            $TP->reportdisplay = $request['jenisindikator'];
            $TP->jenisindikator = $request['jenisindikator'];
            $TP->save();

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'result' => $TP,
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
    public function getJenisIndikator(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $indikator = JenisIndikator::where('statusenabled',1)->where('kdprofile',$idProfile)->get();
        $result = array(
            'data' => $indikator,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function saveIndikatorRensar_M(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if ($request['id'] == ''){
                $new = new \App\Master\IndikatorRensar();
                $id = IndikatorRensar::max('id');
                $new->id = $id + 1;
                $new->kdprofile = $idProfile;
                $new->norec = $new->generateNewId();

            }else{
                $new = \App\Master\IndikatorRensar::where('id', $request['id'])->first();
            }
            $new->statusenabled =  $request['statusenabled'] ;
            $new->definisioperasional = $request['definisioperasional'] ;
            $new->formula = $request['formula'] ;
            $new->indikator = $request['indikator'] ;
//            $new->objectsasaranstrategisfk = $request['objectsasaranstrategisfk'] ;
            $new->pic = $request['pic'] ;
//            $new->urutan = $request['urutan'] ;
//            $new->satuan = $request['satuan'] ;
            $new->jenisindikatorfk = $request['jenisindikatorfk'] ;
            $new->numerator = $request['numerator'] ;
            $new->denominator = $request['denominator'] ;
            $new->dasarpemikiran = $request['dasarpemikiran'] ;
            $new->demensimutufk = $request['dimensimutu'] ;
            $new->tujuan = $request['tujuan'] ;
            $new->targetpencapaian = $request['targetpencapaian'] ;
            $new->inklusi = $request['inklusi'] ;
            $new->eksklusi = $request['eksklusi'] ;
            $new->sumberdata = $request['sumberdata'] ;
            $new->frekuensifk = $request['pengumpulandata'] ;
            $new->waktulaporanfk = $request['jangkalaporan'] ;
            $new->periodefk = $request['periodeanalis'] ;
            $new->metologifk = $request['metodologipengumpulandata'] ;
            $new->cakupandatafk = $request['cakupandata'] ;
            $new->sampel = $request['sampel'] ;
            $new->analisisdatafk = $request['metodologianalisisdata'] ;
            $new->instrumenpengambilandata = $request['instrumenpengambilandata'] ;
            $new->publikasidatafk = $request['publikasidata'] ;
            $new->penanggungjawab = $request['penanggungjawab'];
            $new->objectdepartemenfk = $request['objectdepartemenfk'];
            $new->kategoryindikatorfk = $request['kategoryindikatorfk'];
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
    public function getIndikatorRensar_M(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $indikator = DB::table('indikatorrensar_m as ir')
            ->leftjoin('jenisindikator_m as ji','ji.id','=','ir.jenisindikatorfk')
            ->leftjoin('dimensimutu_m as dm','dm.id','=','ir.demensimutufk')
            ->leftjoin('frekuensidata_m as fd','fd.id','=','ir.frekuensifk')
            ->leftjoin('waktulaporan_m as wl','wl.id','=','ir.waktulaporanfk')
            ->leftjoin('periodepelaporan_m as pl','pl.id','=','ir.periodefk')
            ->leftjoin('metologi_m as mtl','mtl.id','=','ir.metologifk')
            ->leftjoin('metologianalisisdata_m as man','ji.id','=','ir.analisisdatafk')
            ->leftjoin('cakupandata_m as cd','cd.id','=','ir.analisisdatafk')
            ->leftjoin('publikasidata_m as pd','pd.id','=','ir.publikasidatafk')
            ->leftjoin('kategoryindikator_m as ki','ki.id','=','ir.kategoryindikatorfk')
            ->leftjoin('departemen_m as dept','dept.id','=','ir.objectdepartemenfk')
            ->select('ir.*','ji.jenisindikator','dm.demensimutu','fd.frekuensi',
                     'wl.waktulaporan','pl.periodepelaporan','mtl.metologi','man.analisisdata',
                     'cd.cakupandata','pd.publikasidata','ki.kategoryindikator','dept.namadepartemen')
            ->where('ir.statusenabled',1)
            ->where('ir.kdprofile',$idProfile)
            ->orderBy('ir.urutan');
//            ->get();
            if(isset($request['idDept']) && $request['idDept']!= ''){
                $indikator = $indikator->where('ir.objectdepartemenfk',$request['idDept']);
            }
        $indikator = $indikator->get();

        $result = array(
            'data' => $indikator,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function postWaktuTungguPelayananLab(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $tahun = Carbon::now()->format('Y');

            $delete = IndikatorRensarDetail::where('indikatorfk',13779)
//                ->where('keterangan','batching')
                ->where('kdprofile', $idProfile)
                ->where('tahun',$tahun)
                ->delete();

            $dataIhHeuh = DB::select(DB::raw("select * from (
                    select ind.id,ind.indikator,ind.satuan,sum(z.menit)/ count(z.tanggal) as capaian , tg.tahun,
                    z.tanggal || '-01' as tanggal ,     z.bulans , sum (cast(tg.target as float)) / count(tg.target) as target,ind.pic
                    from (select *,
                    case when x.tglakhir is not null then 
                    ( extract(minute from x.tglakhir::TIMESTAMP - x.tglawal::TIMESTAMP) +
                    extract(hour from x.tglakhir::TIMESTAMP - x.tglawal::TIMESTAMP) * 60 )::int else 0 end as menit
                    from (select DISTINCT  pd.noregistrasi,so.tglorder as tglawal,rep.validate_on, 
                    Format(so.tglorder,'YYYY-MM')  as tanggal,  Format(so.tglorder,'Month')  as bulans,
                    case when rep.validate_on <> '' then
                    SUBSTRING ( rep.validate_on , 1 , 4 )|| '-'|| 
                    SUBSTRING ( rep.validate_on , 5 , 2 ) || '-'||
                    SUBSTRING ( rep.validate_on , 7 , 2 ) || ' '||
                    SUBSTRING ( rep.validate_on , 9 , 2 ) || ':'||
                    SUBSTRING ( rep.validate_on , 11 , 2 ) else null end as tglakhir
                    from pasiendaftar_t as pd 
                    join strukorder_t as so on so.noregistrasifk=pd.norec
                    join lisorder as lis on so.noorder=lis.ono
                    join resdt as rep on lis.ono= rep.ono
                    where 
                    Format(so.tglorder,'YYYY')  ='$tahun'
                    and rep.validate_on <>''
                    ) as x ORDER BY x.tanggal
                    ) as z
                    join indikatorrensar_m as ind on ind.id=13779
                    join targetindikator_m as tg on tg.indikatorrensarfk =ind.id
                    where pd.kdprofile = $idProfile and tg.tahun='$tahun'
                    group by z.tanggal,ind.indikator,ind.satuan, tg.target,tg.tahun,ind.pic, ind.id, z.bulans 
                    ) as y 
                    order BY y.tanggal"));


            foreach ( $dataIhHeuh as $item){
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item->bulans;
                $TP->indikatorfk = $item->id;
                $TP->capaian =  $item->capaian;
                $TP->tahun = $item->tahun;
                $TP->tgl =  $item->tanggal;
                $TP->keterangan = 'batching';
                $TP->save();
            }

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Post WTPL';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = ' ';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function postWaktuTungguPelayananRad(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $tahun = Carbon::now()->format('Y');

            $delete = IndikatorRensarDetail::where('indikatorfk',13780)
                ->where('kdprofile', $idProfile)
                ->where('keterangan','batching')
                ->where('tahun',$tahun)
                ->delete();

            $dataIhHeuh = DB::select(DB::raw("select * from (
                    select ind.id,ind.indikator,ind.satuan,sum(z.menit)/ count(z.tanggal) as capaian , tg.tahun,
                    z.tanggal ||'-01' as tanggal, sum (cast(tg.target as float)) / count(tg.target) as target,ind.pic,z.bulans
                    from (
                    select *,
                    case when x.tglakhir is not null then 
                    ( extract(minute from x.tglakhir::TIMESTAMP - x.tglawal::TIMESTAMP) +
                    extract(hour from x.tglakhir::TIMESTAMP - x.tglawal::TIMESTAMP) * 60 )::int else 0 end as menit
                    from (select pd.noregistrasi,so.tglorder as tglawal,ris.create_date,rep.confirm_date,  Format(so.tglorder,'YYYY-MM')  as tanggal,
                    Format(so.tglorder,'Month')  as bulans,
                    case when rep.confirm_date <> '' then
                    SUBSTRING ( rep.confirm_date , 1 , 4 )|| '-'|| 
                    SUBSTRING ( rep.confirm_date , 5 , 2 ) || '-'||
                    SUBSTRING ( rep.confirm_date , 7 , 2 ) || ' '||
                    SUBSTRING ( rep.confirm_date , 9 , 2 ) || ':'||
                    SUBSTRING ( rep.confirm_date , 11 , 2 ) else null end as tglakhir
                    from pasiendaftar_t as pd 
                    join strukorder_t  as so on pd.norec=so.noregistrasifk
                    join ris_order as ris on so.noorder=ris.order_no
                    join ris_report as rep on ris.accession_num = rep.accession_num
                    where pd.kdprofile = $idProfile and Format(so.tglorder,'YYYY')  ='$tahun'
                    ) as x  ORDER BY x.tanggal
                    ) as z
                    join indikatorrensar_m as ind on ind.id=13780
                    join targetindikator_m as tg on tg.indikatorrensarfk =ind.id
                    where tg.tahun='$tahun'
                    group by z.tanggal,ind.indikator,ind.satuan, tg.target,tg.tahun,ind.pic, z.bulans ,ind.id
                    ) as y 
                    order BY y.tanggal"));

            foreach ( $dataIhHeuh as $item){
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item->bulans;
                $TP->indikatorfk = $item->id;
                $TP->capaian =  $item->capaian;
                $TP->tahun = $item->tahun;
                $TP->tgl =  $item->tanggal;
                $TP->keterangan = 'batching';
                $TP->save();
            }

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Post WTPR';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = ' ';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveIndikatorPasienJatuh(Request $request){
        DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try{
            if ($request['norec'] == ''){
                $new = new IndikatorPasienJatuh();
                $new->kdprofile = $idProfile;
                $new->norec = $new->generateNewId();

            }else{
                $new = IndikatorPasienJatuh::where('norec', $request['norec'])->first();
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
    public function getIndikatorPasienJatuh(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::table('indikatorpasienjatuh_t as in')
            ->join('pasiendaftar_t as pd','pd.norec','=','in.noregistrasifk')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->select('in.*','ps.nocm','pd.tglregistrasi','pd.noregistrasi','ps.namapasien','ru.namaruangan',
                'pd.norec as norec_pd')
            ->where('in.kdprofile',$idProfile)
            ->where('in.statusenabled',1);
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
    public function postPasienJatuh(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $tahun = Carbon::now()->format('Y');

            $delete = IndikatorRensarDetail::where('indikatorfk',13786)
                ->where('kdprofile', $idProfile)
//                ->where('keterangan','batching')
                ->where('tahun',$tahun)
                ->delete();

            $dataPasien = DB::select(DB::raw("select count(x.bulan),x.bulan,x.tanggal || '-01' as tangal from(
                select pd.noregistrasi, Format(tglregistrasi, 'Month') as bulan ,
                Format(tglregistrasi, 'YYYY-MM') as tanggal ,ru.namaruangan
                from pasiendaftar_t as pd
                join ruangan_m as ru on ru.id= pd.objectruanganlastfk
                where pd.kdprofile = $idProfile and ru.objectdepartemenfk=16 and Format(pd.tglregistrasi, 'YYYY')= '$tahun'
                order by pd.noregistrasi
                ) as x GROUP BY x.bulan,x.tanggal
                ORDER BY x.tanggal;"));

            $dataJatuh = DB::select(DB::raw("select x.bulan,sum( COALESCE (x.jumlah,0) ) as jumlah,
                    x.tanggal || '-01' as tangal 
                    from(
                    select pd.noregistrasi, Format(ind.tgljatuh, 'Month') as bulan ,
                    Format(ind.tgljatuh, 'YYYY-MM') as tanggal,
                    ind.jumlah
                    from pasiendaftar_t as pd
                    join indikatorpasienjatuh_t as ind on ind.noregistrasifk=pd.norec
                    where pd.kdprofile = $idProfile and Format(tgljatuh, 'YYYY')= '$tahun'
                    and ind.statusenabled=true
                    ) as x GROUP BY x.bulan,x.tanggal
                    ORDER BY x.tanggal "));

            $arr = [];
            if(count($dataPasien) > 0 ){
                foreach ($dataPasien as $item){
                    foreach ($dataJatuh as $item2){
                        if($item->bulan == $item2->bulan ){
                            $jumlahJatuh = $item2->jumlah ;
                        }else{
                            $jumlahJatuh = 0;
                        }

                    }
                    $arr [] = array(
                        'jumlahinap' => (float) $item->count,
                        'bulan' => $item->bulan,
                        'pasienjatuh' => (float) $jumlahJatuh,
                        'tanggal' => $item->tangal,
                        'capaian' => number_format(( (float) $jumlahJatuh /(float) $item->count ) * 100, 1) ,
                        //(Jumlah kejadian pasien jatuh dibagi jumlah pasien rawat inap) x 100%
                    );
                }
            }

//            return $this->respond($arr);
            foreach ( $arr as $item){
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item['bulan'];
                $TP->indikatorfk = 13786;
                $TP->capaian =  $item['capaian'];
                $TP->tahun = $tahun;
                $TP->tgl = $item['tanggal'];
                $TP->keterangan = 'batching';
                $TP->save();
            }

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Post Pasien Jatuh';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = ' ';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function postTindakanOperasiNICU(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $tahun = Carbon::now()->format('Y');

            $dataPasien = DB::select(DB::raw("select (z.jumlah/z.jumlah*100 ) as capaian ,z.bulan,z.tanggal
                        from (
                        select sum(x.jumlah) as jumlah,x.bulan, (SUBSTRING(x.tanggal, 1, 7)) + '-01' as tanggal from(
                        select Month(pp.tglpelayanan) as bulan ,convert(VARCHAR(10),pp.tglpelayanan,121) AS  tanggal,
                        prd.namaproduk,pp.jumlah
                        from pelayananpasien_t as pp
                        join produk_m as prd on prd.id = pp.produkfk 
                        where pp.kdprofile = $idProfile and prd.id=10111734
                        and year(pp.tglpelayanan) = '$tahun'
                        ) as x
                        GROUP BY x.bulan,x.tanggal
                        ) as z ORDER BY z.bulan;
                "));

//           return $this->respond($dataPasien);

            foreach ( $dataPasien as $item){
                $delete = IndikatorRensarDetail::where('indikatorfk',13792)
                    ->where('bulan',$item->bulan)
                    ->where('tahun',$tahun)
                    ->delete();
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item->bulan;
                $TP->indikatorfk = 13792;
                $TP->capaian =  $item->capaian;
                $TP->tahun = $tahun;
                $TP->tgl = $item->tanggal;
                $TP->keterangan = 'batching';
                $TP->save();
            }
//
            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Rensar NICU';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = ' ';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function postKeluhanPelanggan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $tahun = Carbon::now()->format('Y');
            $dataJumlah = DB::select(DB::raw("select count(x.bulan),x.bulan,x.tanggal || '-01' as tangal from(
                select kp.id, Format(kp.tglkeluhan, 'Month') as bulan ,
                Format(kp.tglkeluhan, 'YYYY-MM') as tanggal
                from keluhanpelanggan_m as kp
                where kp.kdprofile = $idProfile and Format(kp.tglkeluhan, 'YYYY')= '2018'
                order by kp.id
                ) as x GROUP BY x.bulan,x.tanggal
                ORDER BY x.bulan;
            ;"));

            $dataTertangani = DB::select(DB::raw("select count(x.bulan),x.bulan,x.tanggal || '-01' as tangal from(
                select pkp.norec, Format(pkp.tglpenanganan, 'Month') as bulan ,
                Format(pkp.tglpenanganan, 'YYYY-MM') as tanggal
                from keluhanpelanggan_m as kp
                join penanganankeluhanpelanggan_t as pkp on pkp.keluhanpelangganfk = kp.id
                where kp.kdprofile = $idProfile and Format(pkp.tglpenanganan, 'YYYY')= '2018'
                order by kp.id
                ) as x GROUP BY x.bulan,x.tanggal
                ORDER BY x.bulan;"));

            $arr = [];
            if(count($dataJumlah) > 0 ){
                foreach ($dataJumlah as $item){
                    foreach ($dataTertangani as $item2){
                        if($item->bulan == $item2->bulan ){
                            $direspon = $item2->count ;
                        }else{
                            $direspon = 0;
                        }
                        $arr [] = array(
                            'jumlah' => (float) $item->count,
                            'bulan' => $item->bulan,
                            'direspon' => (float) $direspon,
                            'tanggal' => $item->tangal,
                            'capaian' => number_format(( (float) $direspon /(float) $item->count ) * 100, 1) ,
                            //(Jumlah seluruh komplain (kategori merah, kuning, hijau) yg ditanggapi & ditindaklanjuti sesuai masing-masing standar waktu / Jumlah seluruh komplain (merah, kuning, hijau)) x 100%
                        );
                    }
                }
            }

//            return $this->respond($arr);
            foreach ( $arr as $item){
                $delete = IndikatorRensarDetail::where('indikatorfk',13776)
                    ->where('bulan', $item['bulan'])
                    ->where('tahun',$tahun)
                    ->delete();
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item['bulan'];
                $TP->indikatorfk = 13776;
                $TP->capaian =  $item['capaian'];
                $TP->tahun = $tahun;
                $TP->tgl = $item['tanggal'];
                $TP->keterangan = 'batching';
                $TP->save();
            }
//
            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Post Repon Pelanggan';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = ' ';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function postPengembalianRekamMedik(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $tahun = Carbon::now()->format('Y');
            $delete = IndikatorRensarDetail::whereIn('indikatorfk',[13777,13796])
//                ->where('keterangan','batching')
                ->where('kdprofile', $idProfile)
                ->where('tahun',$tahun)
                ->delete();

            $dataPasien = DB::select(DB::raw("select count(x.bulan),x.bulan,x.tanggal || '-01' as tangal from(
                    select * from ( 
                    select pd.noregistrasi,   row_number() over (partition by pd.noregistrasi order by kdrm.tglkembali desc) as rownum,
                    Format (pd.tglpulang,'YYYY-MM') as tanggal,Format (pd.tglpulang,'Month') AS bulan,  
                    (extract(day from kdrm.tglkembali::TIMESTAMP - pd.tglpulang::TIMESTAMP) * 24 + 
                    extract(hour from kdrm.tglkembali::TIMESTAMP - pd.tglpulang::TIMESTAMP)  )::int as jam
                    from pasiendaftar_t as pd
                    inner join kendalidokumenrekammedis_t as kdrm on kdrm.nocmfk = pd.nocmfk
                    where pd.kdprofile = $idProfile and kdrm.objectstatuskendalidokumenfk = 3 and Format (pd.tglpulang,'YYYY')='$tahun'
                    and kdrm.tglkembali is not null
                    ) as z where  z.rownum=1 
                    ) as x 
                    GROUP BY x.bulan,x.tanggal
                    order by x.tanggal
                    "));

            $dataJatuh = DB::select(DB::raw("select count(x.bulan),x.bulan,x.tanggal || '-01' as tangal from(
                    select * from ( 
                    select pd.noregistrasi,   row_number() over (partition by pd.noregistrasi order by kdrm.tglkembali desc) as rownum,
                    Format (pd.tglpulang,'YYYY-MM') as tanggal,Format (pd.tglpulang,'Month') AS bulan,  
                    (extract(day from kdrm.tglkembali::TIMESTAMP - pd.tglpulang::TIMESTAMP) * 24 + 
                    extract(hour from kdrm.tglkembali::TIMESTAMP - pd.tglpulang::TIMESTAMP)  )::int as jam
                    from pasiendaftar_t as pd
                    inner join kendalidokumenrekammedis_t as kdrm on kdrm.nocmfk = pd.nocmfk
                    where pd.kdprofile = $idProfile and kdrm.objectstatuskendalidokumenfk = 3 and Format (pd.tglpulang,'YYYY')='$tahun'
                    and kdrm.tglkembali is not null
                    ) as z where  z.rownum=1 
                    ) as x where x.jam > 24
                    GROUP BY x.bulan,x.tanggal
                    order by x.tanggal"));


            if(count($dataPasien) > 0 ){
                $arr = [];
                foreach ($dataPasien as $item){
                    foreach ($dataJatuh as $item2){
                        if($item->bulan == $item2->bulan ){
                            $jumlahJatuh = $item2->count ;
                        }
//                        else{
//                            $jumlahJatuh = 0;
//                        }

                    }
                    $arr [] = array(
                        'jumlah' => (float) $item->count,
                        'bulan' => $item->bulan,
                        'jumlah24' => (float) $jumlahJatuh,
                        'tanggal' => $item->tangal,
                        'capaian' => number_format(( (float) $jumlahJatuh /(float) $item->count ) * 100, 1) ,
                        //(Jumlah kejadian pasien jatuh dibagi jumlah pasien rawat inap) x 100%
                    );
                }
            }

            $data10=[];
            foreach ($arr as $item) {
                $sama=false;
                $i=0;
                foreach ($data10 as $hideung){
                    if ($item['bulan'] == $data10[$i]['bulan']){
                        $sama=1;
                        $data10[$i]['capaian'] = (float)$data10[$i]['capaian'] + (float)($item['capaian']);
                    }
                    $i=$i+1;
                }
                if ($sama==false){
                    $data10[]=array(
                        'tanggal'=>$item['tanggal'],
                        'bulan'=>$item['bulan'],
                        'capaian'=>$item['capaian'],
                    );
                }
            }

//            return $this->respond($data10);
            foreach ( $data10 as $item){
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item['bulan'];
                $TP->indikatorfk = 13777;
                $TP->capaian =  $item['capaian'];
                $TP->tahun = $tahun;
                $TP->tgl = $item['tanggal'];
                $TP->keterangan = 'batching';
                $TP->save();
            }
            foreach ( $data10 as $item){
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item['bulan'];
                $TP->indikatorfk = 13796;
                $TP->capaian =  $item['capaian'];
                $TP->tahun = $tahun;
                $TP->tgl = $item['tanggal'];
                $TP->keterangan = 'batching';
                $TP->save();
            }

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Post Pengembalian Rekam Medik';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = ' ';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function postWaktuTungguRawatJalan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $tahun = Carbon::now()->format('Y');

            $delete = IndikatorRensarDetail::where('indikatorfk',13782)
                ->where('kdprofile', $idProfile)
                ->where('keterangan','batching')
                ->where('tahun',$tahun)
                ->delete();

            $dataIhHeuh = DB::select(DB::raw("select * from (
                select ind.id,ind.indikator,ind.satuan,sum(z.menit)/ count(z.tanggal) as capaian , tg.tahun,
                z.tanggal || '-01' as tanggal, z.bulan, sum (cast(tg.target as float)) / count(tg.target) as target,ind.pic
                from (
                SELECT Format( pd.tglregistrasi,'Month') as bulan,Format( pd.tglregistrasi,'YYYY-MM') as tanggal, 
                case when app.tgldipanggildokter is not null then 
                ( extract(minute from app.tgldipanggildokter::TIMESTAMP - pd.tglregistrasi::TIMESTAMP) +
                extract(hour from app.tgldipanggildokter::TIMESTAMP - pd.tglregistrasi::TIMESTAMP) * 60 )::int else 0 end as menit,
                pd.noregistrasi, pd.tglregistrasi, app.tgldipanggildokter
                from antrianpasiendiperiksa_t as app
                left join pasiendaftar_t as pd on pd.norec = app.noregistrasifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where app.kdprofile = $idProfile and ru.objectdepartemenfk in (18,27,28,24,3) and    Format(pd.tglregistrasi,'YYYY')  ='$tahun'
                order by pd.tglregistrasi
                ) as z
                join indikatorrensar_m as ind on ind.id=13782
                join targetindikator_m as tg on tg.indikatorrensarfk =ind.id
                where tg.tahun ilike '$tahun%'
                group by z.tanggal,ind.indikator,ind.satuan, tg.target,tg.tahun,ind.pic, ind.id, z.bulan
                ) as y 
                order BY y.tanggal"));

//            return $this->respond($dataIhHeuh);
            foreach ( $dataIhHeuh as $item){
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = 1;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item->bulan;
                $TP->indikatorfk = $item->id;
                $TP->capaian =  $item->capaian;
                $TP->tahun = $item->tahun;
                $TP->tgl =  $item->tanggal;
                $TP->keterangan = 'batching';
                $TP->save();
            }

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Post WTRJ';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = ' ';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getIndikatorIKT(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tahun = $request['tahun'];//Carbon::now()->format('Y');

        $data = DB::select(DB::raw("
                     select * from (
                        select  x.indikator as namaindikator, x.satuan, 
                        sum (x.capaian) / (case count(x.tanggal) when 0 then 1 else count(x.tanggal) end ) as capaian,
                        x.tahun,x.bulan, x.monthmm,
                        sum (x.targets) / (case count(x.targets) when 0 then 1 else count(x.targets) end )  as target, x.pic,
                        x.warnagrafik,x.urutan
                        from (
                            select head.indikator,head.satuan,dt.capaian,
                            dt.target,dt.tahun,dt.bulan,dt.tgl,
                            Format(dt.tgl,'Month')  as tanggal,  Format(dt.tgl,'MM')  as monthmm,  head.pic,
                            cast(tg.target as float) as targets,head.warnagrafik,head.urutan
                            from indikatorrensar_m as head
                            INNER JOIN indikatorrensardetail_t as dt on dt.indikatorfk =head.id
                            left join targetindikator_m as tg on head.id= tg.indikatorrensarfk 
                            where head.kdprofile = $idProfile and dt.tahun ilike '$tahun%'
                            and tg.tahun  ilike '$tahun%'
                            and dt.statusenabled =1
                            and tg.kdprofile=1
                            and tg.statusenabled =1
                            and head.jenisindikatorfk = 6 -- IKT
                            order by dt.tgl asc
                        )as x 
                        GROUP BY x.indikator, x.satuan, x.pic,x.monthmm,
                        x.tahun,x.bulan,x.tgl,x.bulan,x.warnagrafik,x.urutan
                    ) as y
                    order BY y.monthmm

              ") );
        $resul = [];
        $categories = '';
        foreach ($data as $item){
            if($item->monthmm == '01' ||$item->monthmm == '02' ||$item->monthmm == '03'){
                $categories = 'Triwulan 1';
            }
            if($item->monthmm == '04' ||$item->monthmm == '05' ||$item->monthmm == '06'){
                $categories = 'Triwulan 2';
            }
            if($item->monthmm == '07' ||$item->monthmm == '08' ||$item->monthmm == '09'){
                $categories = 'Triwulan 3';
            }
            if($item->monthmm == '10' ||$item->monthmm == '11' ||$item->monthmm == '12'){
                $categories = 'Triwulan 4';
            }
            $resul []= array(
                'namaindikator' => $item->namaindikator,
                'satuan' => $item->satuan,
                'capaian' => $item->capaian,
                'tahun' => $item->tahun,
                'bulan' => $item->bulan,
                'monthmm' => $item->monthmm,
                'target' => $item->target,
                'pic' => $item->pic,
                'warnagrafik' => $item->warnagrafik,
                'urutan' => $item->urutan,
                'category' => $categories,
                'jumlah' => 1,
            );
        }
        $data10 =[];
//                return $this->respond($resul);
        foreach ($resul as $res2){
            $i = 0;
            $sama = false;
            foreach ($data10 as $hideung){
                if($res2['category'] ==  $data10[$i]['category'] && $res2['namaindikator'] ==  $data10[$i]['namaindikator']){
                    $sama = 1;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;
                    $data10[$i]['capaian'] = (float) $data10[$i]['capaian'] + (float)$res2['capaian'] ;

                }
                $i = $i +1;
            }
            if($sama == false){
                $data10 [] = array(
                    'namaindikator' => $res2['namaindikator'],
                    'satuan' => $res2['satuan'],
                    'capaian' => (float)$res2['capaian']  ,
                    'tahun' => $res2['tahun'],
                    'target' => $res2['target'],
                    'pic' => $res2['pic'],
                    'warnagrafik' => $res2['warnagrafik'],
                    'urutan' => $res2['urutan'],
                    'category' =>$res2['category'],
                    'jumlah' => 1
                );
            }
        }
        $data11 = [];
        foreach ($data10 as $item){
            $data11 [] = array(
                'namaindikator' => $item['namaindikator'],
                'satuan' => $item['satuan'],
                'capaian' =>number_format(((float) $item['capaian']/ $item['jumlah'] ),0),
                'tahun' => $item['tahun'],
                'target' => $item['target'],
                'pic' => $item['pic'],
                'warnagrafik' => $item['warnagrafik'],
                'urutan' => $item['urutan'],
                'category' =>$item['category'],
            );
        }
//        return $this->respond($data11);
        if(count($data11) > 0){
            foreach ($data11 as $key => $row ) {
                $count[$key] = $row['category'];
            }
            array_multisort($count, SORT_ASC, $data11);
        }
//        return $this->respond($resul);

        $result = array(
            'data' => $data11,
            'jenis' => 'IKT',
            'message' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function postKetepatanJamVisite(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $tahun = Carbon::now()->format('Y');

            $delete = IndikatorRensarDetail::where('indikatorfk',13798)
//                ->where('keterangan','batching')
                ->where('kdprofile', $idProfile)
                ->where('tahun',$tahun)
                ->delete();

            $dataIhHeuh = DB::select(DB::raw("           
                select (z.jam1 + z.jam2 + z.jam3) / z.jumlah as capaian, z.bulan,z.tanggal  from (
                select SUM(CASE When x.jam <='12.00' Then x.jumlah * 100 Else 0 End ) as jam1 ,
                SUM(CASE When x.jam >  '12.00' and  x.jam <  '14.00' Then x.jumlah  * 50 Else 0 End ) as jam2,
                SUM(CASE When x.jam >=  '14.00' Then x.jumlah Else 0 End ) as jam3,sum(x.jumlah) as jumlah,
                x.bulan,x.tanggal
                from (
                select Format(pp.tglpelayanan, 'Month') as bulan , 
                Format(pp.tglpelayanan, 'YYYY-MM') as tanggal,
                Format(pp.tglpelayanan, 'HH24.MI') as jam,
                prd.namaproduk, sum (pp.jumlah) as jumlah
                from pelayananpasien_t as pp
                join produk_m as prd on prd.id = pp.produkfk 
                where pp.kdprofile = $idProfile and prd.id=402509
                and Format(pp.tglpelayanan, 'YYYY')= '$tahun'
                GROUP BY prd.namaproduk,pp.tglpelayanan
                ) as x GROUP BY x.bulan,x.tanggal
                ) as z ORDER BY z.bulan;
                
                "));
//            return $this->respond(number_format ($dataIhHeuh[0]->capaian,2));
//            return $this->respond($dataIhHeuh);
            foreach ( $dataIhHeuh as $item){
                $TP = new IndikatorRensarDetail();
                $TP->kdprofile = $idProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled = 1;
                $TP->bulan = $item->bulan;
                $TP->indikatorfk = 13798;
                $TP->capaian = number_format ($item->capaian,2);
                $TP->tahun = $tahun;
                $TP->tgl =  $item->tanggal.'-01';
                $TP->keterangan = 'batching';
                $TP->save();
            }


            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Post IKT Visite';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = ' ';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getLaporanLayanan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;

        $tglAwal = date('Y-m-d 00:00');
        $tglAkhir = date('Y-m-d 23:59');

        $results = DB::select(DB::raw("
                select pg.id as iddokter,pp.norec,pg.namalengkap as dokter,pp.jumlah as count, pp.hargasatuan as tariff ,
                pr.namaproduk as layanan, pp.hargasatuan * pp.jumlah as totall ,
                ps.nocm,ps.namapasien,pp.tglpelayanan
                from pelayananpasien_t as pp 
                join antrianpasiendiperiksa_t as apd on apd.norec = pp.noregistrasifk
                join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk
                join pasien_m as ps on ps.id = pd.nocmfk
                join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pp.norec
                join pegawai_m as pg on pg.id =ppp.objectpegawaifk
                join produk_m as pr on pr.id = pp.produkfk
                where pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN '$tglAwal'  and '$tglAkhir'
                and pp.strukresepfk is null
                and ppp.objectjenispetugaspefk=4
                "));
        $result = array(
            'data' => $results,
            'message' => 'er@epic',
        );

        return $this->respond($result);
    }
     public function getRegisterSummary(Request $request){
         $kdProfile = $this->getDataKdProfile($request);
         $idProfile = (int) $kdProfile;
         $data = [];
         $tglAwal =  $request['tgl'].' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
         $tglAkhir =  $request['tgl'].' 23:59';
         $ruanganId = $request['ruanganId'];
         $kelompokId = $request['kelompokPasien'];

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and pd.objectruanganlastfk = ' . $ruanganId;
        }

        $paramKelompok = ' ';
        if (isset($kelompokId) && $kelompokId != "" && $kelompokId != "undefined") {
            $paramKelompok = ' and pd.objectkelompokpasienlastfk = ' . $kelompokId;
        }

        // $deptRawatJalan = $this->settingDataFixed('kdDepartemenRawatJalanFix');
         if($request['tipe'] == 'Ruangan'){
             $data = DB::select(DB::raw("select x.namaruangan,x.kelompokpasien,sum(x.laki)as laki,sum(x.wanita)as wanita,
                    sum(x.baru) as baru,sum(x.lama) as lama ,sum(x.laki + x.wanita) as jml
                     from (SELECT  ru.namaruangan,kp.kelompokpasien,CASE WHEN jk.id = 1 THEN 1 ELSE 0 END AS laki,
                                     CASE WHEN jk.id = 2 THEN 1 ELSE 0 END AS wanita,
                                  CASE WHEN pd.statuspasien = 'BARU' or pd.statuspasien = '' or pd.statuspasien is null   THEN 1 ELSE 0 END AS baru,
                                     CASE WHEN pd.statuspasien = 'LAMA' THEN 1 ELSE 0 END AS lama
                                    FROM pasiendaftar_t as pd 
                                    INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN pasien_m as ps on ps.id = pd.nocmfk
                                    LEFT JOIN jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
                                    WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                                         and pd.statusenabled=true
                                   
                                ) as x
                               GROUP BY x.namaruangan,x.kelompokpasien
                    order by x.namaruangan"
             )
             );
         }
         if($request['tipe'] == 'Instalasi'){
             $data = DB::select(DB::raw("select x.namaruangan,x.kelompokpasien,sum(x.laki)as laki,sum(x.wanita)as wanita,
                    sum(x.baru) as baru,sum(x.lama) as lama ,sum(x.laki + x.wanita) as jml
                     from (SELECT  dp.namadepartemen as namaruangan,kp.kelompokpasien,CASE WHEN jk.id = 1 THEN 1 ELSE 0 END AS laki,
                                     CASE WHEN jk.id = 2 THEN 1 ELSE 0 END AS wanita,CASE WHEN pd.statuspasien = 'BARU' THEN 1 ELSE 0 END AS baru,
                                     CASE WHEN pd.statuspasien = 'LAMA' THEN 1 ELSE 0 END AS lama
                                    FROM pasiendaftar_t as pd 
                                    INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                                    INNER JOIN departemen_m as dp on dp.id = ru.objectdepartemenfk
                                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                                    INNER JOIN pasien_m as ps on ps.id = pd.nocmfk
                                    LEFT JOIN jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
                                    WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                                         and pd.statusenabled=true
                                    
                                ) as x
                               GROUP BY x.namaruangan,x.kelompokpasien
                    order by x.namaruangan"
             )
             );
         }
       
        return $this->respond($data);
    }
     public function getKunjunganBerdasarkanParameter(Request $request){
         $kdProfile = $this->getDataKdProfile($request);
         $idProfile = (int) $kdProfile;
        // $tglAwal =  date('Y-m-d'.' 00:00');
        $tglAwal =  $request['tgl'].' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir =  $request['tgl'].' 23:59';

         if($request['params'] == 'Asal Rujukan'){
             $dataALL = DB::select(DB::raw("select x.name ,count(x.name) as jumlah from (
                select DISTINCT 
                apd.norec, 
                kps.asalrujukan as name, 
                pd.objectasalrujukanfk
                from antrianpasiendiperiksa_t as pd
                inner join pasiendaftar_t as apd on apd.norec =pd.noregistrasifk
                inner join asalrujukan_m as kps on kps.id = pd.objectasalrujukanfk
                 inner join ruangan_m as ru on ru.id = apd.objectruanganlastfk
                -- left join batalregistrasi_t as br on br.pasiendaftarfk=pd.norec
                --left join pemakaianasuransi_t as pa on pa.noregistrasifk=pd.norec
                WHERE pd.kdprofile = $idProfile and apd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                and apd.statusenabled =true
                and pd.statusenabled=true
                -- and br.norec is null
      
                )as  x
                GROUP BY x.name"));

         }
         if($request['params'] == 'Diagnosa'){
             $dataALL = DB::select(DB::raw("select * from (
                select count(x.kddiagnosa)as jumlah,x.kddiagnosa || ' - '||  x.namadiagnosa as name
                from (select dm.kddiagnosa, 
                dm.namadiagnosa
                from antrianpasiendiperiksa_t as app
                left join diagnosapasien_t as dp on dp.noregistrasifk = app.norec
                left join detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
                inner join diagnosa_m as dm on ddp.objectdiagnosafk = dm.id
                inner join pasiendaftar_t as pd on pd.norec = app.noregistrasifk
                inner join pasien_m as ps on ps.id = pd.nocmfk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where app.kdprofile = $idProfile and dm.kddiagnosa <> '-' and
                                pd.statusenabled =true
                        and
          
                pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                )as x GROUP BY x.namadiagnosa ,x.kddiagnosa 
                ) as z
                ORDER BY z.jumlah desc"));
         }
         if($request['params'] == 'Tindakan'){
             $dataALL = DB::select(DB::raw("select * from (select count(x.namaproduk) as jumlah,x.namaproduk as name from (
                    select pr.namaproduk,pp.norec from pelayananpasien_t as pp 
                    inner join produk_m as pr on pr.id=pp.produkfk
                    where pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    and pp.statusenabled=true
                    and pp.strukresepfk is null
                    ) as x
                    GROUP BY x.namaproduk
                    ) as z order by z.jumlah desc"));
         }
         if($request['params'] == 'Wilayah'){
             $dataALL = DB::select(DB::raw("select * from (
                    select x.name,count(x.name) as jumlah from (
                    select pd.norec, 
                    kec.namakecamatan as name
                    from pasiendaftar_t as pd
                    inner join pasien_m as ps on ps.id = pd.nocmfk
                    inner join alamat_m as alm on alm.nocmfk= ps.id
                    inner join kecamatan_m as kec on kec.id= alm.objectkecamatanfk
                    left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                    WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN   '$tglAwal' AND '$tglAkhir'
                    and pd.statusenabled =true
              
                    )as  x
                    GROUP BY x.name
                    )as z
                    order by z.jumlah desc
                    "));
         }
        return $this->respond($dataALL);
    }

 public function getLaporanPemakaianObat (Request $request){
     $kdProfile = $this->getDataKdProfile($request);
     $idProfile = (int) $kdProfile;
        $tglAwal= Carbon::now()->format('Y-m-d 00:00');
        $tglAkhir =Carbon::now()->format('Y-m-d 23:59');
        $data = DB::select(DB::raw("
            select * from (
                select x.namaproduk ,sum (x.jumlah) as jumlah ,sum(x.total ) as total from (

                select
                prd.namaproduk  , pp.jumlah , (
                ((  CASE WHEN   pp.hargasatuan IS NULL THEN 0 ELSE pp.hargasatuan END 
                - CASE WHEN pp.hargadiscount IS NULL THEN   0 ELSE pp.hargadiscount END     ) * pp.jumlah
                ) + CASE    WHEN    pp.jasa IS NULL THEN 0  ELSE        pp.jasa END) AS total
                from  strukresep_t as sr
                join pelayananpasien_t  as pp  on pp.strukresepfk =sr.norec
                join produk_m as prd on pp.produkfk= prd.id
                where sr.kdprofile = $idProfile and pp.tglpelayanan BETWEEN  '$tglAwal' and '$tglAkhir'
                and pp.strukresepfk is not null

                Union all

                SELECT pr.namaproduk,  (spd.qtyproduk) as jumlah,
                  (((   CASE WHEN   spd.hargasatuan IS NULL THEN 0 ELSE spd.hargasatuan END 
                - CASE WHEN spd.hargadiscount IS NULL THEN  0 ELSE spd.hargadiscount END    ) * spd.qtyproduk
                ) + CASE    WHEN    spd.hargatambahan IS NULL THEN 0    ELSE        spd.hargatambahan END) AS total
                FROM strukpelayanan_t as sp  
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN   '$tglAwal' and '$tglAkhir'
                AND sp.nostruk_intern='-' AND substring(sp.nostruk,1,2)='OB'  
                and sp.statusenabled != false
                Union ALL

                SELECT pr.namaproduk, (spd.qtyproduk) as jumlah,
                  (((   CASE WHEN   spd.hargasatuan IS NULL THEN 0 ELSE spd.hargasatuan END 
                - CASE WHEN spd.hargadiscount IS NULL THEN  0 ELSE spd.hargadiscount END    ) * spd.qtyproduk
                ) + CASE    WHEN    spd.hargatambahan IS NULL THEN 0    ELSE        spd.hargatambahan END) AS total
                FROM strukpelayanan_t as sp  
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN   '$tglAwal' and '$tglAkhir'
                AND sp.nostruk_intern not in ('-') AND substring(sp.nostruk,1,2)='OB'  
                and sp.statusenabled != false
                ) as x
                group by x.namaproduk
            )  as z order by z.total desc")
        );

        $result = array(
            'data' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
     public function getDaftarPenerimaanSuplier(Request $request){
         $kdProfile = $this->getDataKdProfile($request);
         $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('strukpelayanan_t as sp')
            ->JOIN('strukpelayanandetail_t as spd', 'spd.nostrukfk', '=', 'sp.norec')
            ->leftJOIN('rekanan_m as rkn', 'rkn.id', '=', 'sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'sp.objectpegawaipenerimafk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'sp.objectruanganfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk', 'sbk.norec', '=', 'sp.nosbklastfk')
//            ->LEFTJOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
//            ->LEFTJOIN('jeniskemasan_m as jkm','jkm.id','=','spd.objectjeniskemasanfk')
            ->select('sp.tglstruk', 'sp.nostruk', 'rkn.namarekanan', 'pg.namalengkap', 'sp.nokontrak',
                'ru.namaruangan', 'sp.norec', 'sp.nofaktur', 'sp.tglfaktur', 'sp.totalharusdibayar', 'sbk.nosbk',
                'sp.nosppb', 'sp.noorderfk', 'sp.qtyproduk'
//                'spd.hargasatuan','spd.hargadiscount','spd.qtyproduk','spd.hargatambahan' ,
//                'pr.namaproduk as namaprodukstandar',
//                'spd.resepke as rke','jkm.jeniskemasan'
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.tglstruk', 'sp.nostruk', 'rkn.namarekanan', 'pg.namalengkap', 'sp.nokontrak', 'ru.namaruangan', 'sp.norec', 'sp.nofaktur',
                'sp.tglfaktur', 'sp.totalharusdibayar', 'sbk.nosbk', 'sp.nosppb', 'sp.noorderfk', 'sp.qtyproduk');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sp.tglstruk', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('sp.tglstruk', '<=', $tgl);
        }
        if (isset($request['nostruk']) && $request['nostruk'] != "" && $request['nostruk'] != "undefined") {
            $data = $data->where('sp.nostruk', 'ilike', '%' . $request['nostruk']);
        }
        if (isset($request['namarekanan']) && $request['namarekanan'] != "" && $request['namarekanan'] != "undefined") {
            $data = $data->where('rkn.namarekanan', 'ilike', '%' . $request['namarekanan'] . '%');
        }
        if (isset($request['nofaktur']) && $request['nofaktur'] != "" && $request['nofaktur'] != "undefined") {
            $data = $data->where('sp.nofaktur', 'ilike', '%' . $request['nofaktur'] . '%');
        }
        if (isset($request['produkfk']) && $request['produkfk'] != "" && $request['produkfk'] != "undefined") {
            $data = $data->where('spd.objectprodukfk', '=', $request['produkfk']);
        }
        if (isset($request['noSppb']) && $request['noSppb'] != "" && $request['noSppb'] != "undefined") {
            $data = $data->where('sp.nosppb', 'ilike', '%' . $request['noSppb'] . '%');
        }
//        $data = $data->distinct();
        $data = $data->where('sp.statusenabled', true);
        $data = $data->where('sp.objectkelompoktransaksifk', 35);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();

        foreach ($data as $item) {
            $details = \DB::select(DB::raw("select  pr.namaproduk,ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.hargasatuan,spd.hargadiscount,
                    --spd.hargappn,((spd.hargasatuan-spd.hargadiscount+spd.hargappn)*spd.qtyproduk) as total,spd.tglkadaluarsa,spd.nobatch
                    spd.hargappn,((spd.hargasatuan * spd.qtyproduk)-spd.hargadiscount+spd.hargappn) as total,spd.tglkadaluarsa,spd.nobatch
                    from strukpelayanandetail_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nostrukfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'tglstruk' => $item->tglstruk,
                'nostruk' => $item->nostruk,
                'nofaktur' => $item->nofaktur,
                'tglfaktur' => $item->tglfaktur,
                'namarekanan' => $item->namarekanan,
                'norec' => $item->norec,
                'namaruangan' => $item->namaruangan,
                'namapenerima' => $item->namalengkap,
                'totalharusdibayar' => $item->totalharusdibayar,
                'nosbk' => $item->nosbk,
                'nosppb' => $item->nosppb,
                'nokontrak' => $item->nokontrak,
                'noorderfk' => $item->noorderfk,
                'jmlitem' => $item->qtyproduk,
                'details' => $details,
            );
        }
        if (count($data) == 0) {
            $result = [];
        }

        $result = array(
            'daftar' => $result,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
      public function getDaftarDistribusiBarangPerUnit(Request $request) {
          $kdProfile = $this->getDataKdProfile($request);
          $idProfile = (int) $kdProfile;
        $kdSirs1 = $request['KdSirs1'];
        $kdSirs2= $request['KdSirs2'];
        // $dataLogin = $request->all();
        // $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
        //         INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
        //         where lu.id=:idLoginUser"),
        //     array(
        //         'idLoginUser' => $request['userData']['id'],
        //     )
        // );
        // $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
        //     ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
        //     ->select('ru.id')
        //     ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            // ->get();
//        $strRuangan = [];

        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN ('produk_m as pr','pr.id','=','kp.objectprodukfk')
            ->LEFTJOIN ('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->LEFTJOIN ('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->LEFTJOIN ('kelompokproduk_m as kps','kps.id','=','jp.objectkelompokprodukfk')
            ->LEFTJOIN ('asalproduk_m as ap','ap.id','=','kp.objectasalprodukfk')
            ->LEFTJOIN ('satuanstandar_m as ss','ss.id','=','kp.objectsatuanstandarfk')
            ->select(
                DB::raw('sp.norec,pr.id as kodebarang,pr.kdproduk as kdsirs,pr.namaproduk,sp.nokirim,sp.jenispermintaanfk,sp.tglkirim,ss.satuanstandar,
                         kp.qtyproduk,kp.hargasatuan,ru.namaruangan as ruanganasal,ru2.namaruangan as ruangantujuan,(kp.qtyproduk*kp.hargasatuan) as total,
                         pr.objectdetailjenisprodukfk,djp.detailjenisproduk,djp.objectjenisprodukfk,jp.jenisproduk,jp.jenisproduk,jp.objectkelompokprodukfk,
                         kps.kelompokproduk,kp.objectasalprodukfk,ap.asalproduk')
            )
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ilike','%'. $request['nokirim']);
        }
        if(isset($request['ruanganasalfk']) && $request['ruanganasalfk']!="" && $request['ruanganasalfk']!="undefined"){
            $data = $data->where('ru.id','=', $request['ruanganasalfk']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru2.id','=', $request['ruangantujuanfk']);
        }
        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $data = $data->where('pr.namaproduk','ilike','%'. $request['namaproduk']);
        }

        if(isset($request['jenisProduk']) && $request['jenisProduk']!="" && $request['jenisProduk']!="undefined"){
            $data = $data->where('djp.objectjenisprodukfk','=', $request['jenisProduk']);
        }
        if(isset($request['AsalProduk']) && $request['AsalProduk']!="" && $request['AsalProduk']!="undefined"){
            $data = $data->where('kp.objectasalprodukfk','=',$request['AsalProduk']);
        }
        if(isset($request['kelompokProduk']) && $request['kelompokProduk']!="" && $request['kelompokProduk']!="undefined"){
            $data = $data->where('jp.objectkelompokprodukfk','=',$request['kelompokProduk']);
        }
        if(isset( $request['KdSirs1'])&&  $request['KdSirs1']!=''){
            if($request['KdSirs2'] != null &&  $request['KdSirs2']!='' && $request['KdSirs1'] != null &&  $request['KdSirs1']!= ''){
                $data = $data->whereRaw (" (pr.kdproduk BETWEEN '".$request['KdSirs1']."' and '".$request['KdSirs2']."') ");
            }elseif ($request['KdSirs2'] &&  $request['KdSirs2']!= '' && $request['KdSirs1'] == '' ||  $request['KdSirs1'] == null){
                $data = $data->whereRaw = (" pr.kdproduk like '".$request['KdSirs2']."%'");
            }elseif ($request['KdSirs1'] &&  $request['KdSirs1']!= '' && $request['KdSirs2'] == '' ||  $request['KdSirs2'] == null){
                $data = $data->whereRaw = (" pr.kdproduk like '".$request['KdSirs1']."%'");
            }
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',34);
        $data = $data->where('kp.qtyproduk','>', 0);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();
        $result = array(
//            'datalogin' => $dataLogin,
            'data' => $data,
            'message' => 'Cepot'
        );
        return $this->respond($result);
    }
   public function getComboAddressEIS(Request $request){
       $kdProfile = $this->getDataKdProfile($request);
       $idProfile = (int) $kdProfile;
        $kebangsaan = DB::table('kebangsaan_m')
            ->select(DB::raw("id, UPPER(name) as name"))
            ->where('kdprofile',$idProfile)
            ->where('statusenabled',true)
            ->get();

        $negara = DB::table('negara_m')
            ->select(DB::raw("id, UPPER(namanegara) as namanegara"))
            ->where('statusenabled',true)
            ->orderBy('namanegara')
            ->get();

        $kotakabupaten = DB::table('kotakabupaten_m')
            ->select(DB::raw("id, UPPER(namakotakabupaten) as namakotakabupaten"))
            ->where('statusenabled',true)
            ->orderBy('namakotakabupaten')
            ->get();

        $propinsi = DB::table('propinsi_m')
            ->select(DB::raw("id, UPPER(namapropinsi) as namapropinsi"))
            ->where('statusenabled',true)
            ->orderBy('namapropinsi')
            ->get();

        $kecamatan = DB::table('kecamatan_m')
            ->select(DB::raw("id, UPPER(namakecamatan) as namakecamatan"))
            ->where('statusenabled',true)
            ->orderBy('namakecamatan')
            ->get();
        $result = array(
            'kebangsaan' => $kebangsaan,
            'negara' => $negara,
            'kotakabupaten' => $kotakabupaten,
            'propinsi' => $propinsi,
            'kecamatan' => $kecamatan,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function getPropinsi(Request $request){
        $propinsi = DB::table('propinsi_m')
            ->select(DB::raw("id, UPPER(namapropinsi) as namapropinsi"))
            ->where('statusenabled',true)
            ->where('namapropinsi','ilike','%'.$request['params'].'%')
            ->orderBy('namapropinsi')
            ->get();
         

        return $this->respond($propinsi);
    }

    public function getKecamatan(Request $request){
        $propinsi = DB::table('kecamatan_m')
            ->select(DB::raw("id, UPPER(namakecamatan) as namakecamatan"))
            ->where('statusenabled',true)
            ->where('namakecamatan','ilike','%'.$request['params'].'%')
            ->orderBy('namakecamatan')
            ->get();
         

        return $this->respond($propinsi);
    }

    public function getKota(Request $request){
        $propinsi = DB::table('kotakabupaten_m')
            ->select(DB::raw("id, UPPER(namakotakabupaten) as namakotakabupaten"))
            ->where('statusenabled',true)
            ->where('namakotakabupaten','ilike','%'.$request['params'].'%')
            ->orderBy('namakotakabupaten')
            ->get();
         

        return $this->respond($propinsi);
    }

    public function getInfoAbsen (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal =date('Y-m-d 00:00');
        $tgAkhir =date('Y-m-d 23:59');
        $data = \DB::table('pegawai_m as pg')
            ->Join('sdm_absensipegawai_t as abn','abn.pegawaifk','=','pg.id')
            ->select(\DB::raw("abn.pegawaifk,pg.namalengkap,to_char( abn.jammasuk,'dd-MM-yyyy') as tglabsen , to_char(abn.jammasuk,'HH:mm') as jammasuk, to_char(abn.jamkeluar,'HH:mm')  as jamkeluar,'-' as namaruangan"))
            ->where('pg.kdprofile', $idProfile)
            ->where('pg.statusenabled',true)
            ->where('abn.statusenabled',true);

//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('abn.jammasuk', '>=', $tglAwal);
//        }

//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('abn.jammasuk', '<=', $tgAkhir);
//        }

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pg.id','=',$request['idPegawai']);
        }
        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $data = $data->where('abn.norec','=',$request['norec']);
        }

        $data = $data->orderBy('pg.namalengkap');
        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ea@epic',
        );
        return $this->respond($data);
    }

    public function getKelompokTransaksi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $indikator = KelompokTransaksi::where('statusenabled',1)
            ->where('kdprofile', $idProfile)
            ->get();
        $result = array(
            'data' => $indikator,
            'message' => 'khris@epic',
        );
        return $this->respond($result);

    }

    public function saveKelompokTransaksi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if ($request['id'] == ''){
                $TP = new KelompokTransaksi();
                $idMax = KelompokTransaksi::max('id');
                $newId = $idMax + 1;
                $TP->kdprofile = $idProfile;
                $TP->id =  $newId;
                $TP->norec = $TP->generateNewId();
                $TP->kodeexternal = $newId ;
                $TP->kdkelompoktransaksi = $newId ;
                $TP->qkelompoktransaksi = $newId ;
            }else{
                $TP = KelompokTransaksi::where('id', $request['id'])->first();
            }
            $TP->statusenabled = $request['statusenabled'];
            $TP->namaexternal = $request['kelompoktransaksi'];
            $TP->reportdisplay = $request['kelompoktransaksi'];
            $TP->kelompoktransaksi = $request['kelompoktransaksi'];
            $TP->save();

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'result' => $TP,
                'as' => 'khris@epic',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'khris@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

     public function simpanTXTBPJS(Request $request){
//        ini_set('max_execution_time', 100);
         $kdProfile = $this->getDataKdProfile($request);
         $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        // try {
            // $data2 = BPJSKlaimTxt::where('txtfilename', $request['filename'])->delete();
            $data_to_insert = [];
            foreach ($request['data'] as $item){
                $data1 = new BPJSKlaimTxt();
                $norec = $data1->generateNewId();
                 array_push($data_to_insert, [
                    'norec' => $norec,
                    'kdprofile' => $idProfile,
                    'statusenabled' => true,
                    'KODE_RS' => $item['KODE_RS'],
                    'KELAS_RS' => $item['KELAS_RS'],
                    'KELAS_RAWAT' => $item['KELAS_RAWAT'],
                    'KODE_TARIF' => $item['KODE_TARIF'],
                    'PTD' => $item['PTD'],
                    'ADMISSION_DATE' => $item['ADMISSION_DATE'],
                    'DISCHARGE_DATE' => $item['DISCHARGE_DATE'],
                    'BIRTH_DATE' => $item['BIRTH_DATE'],
                    'BIRTH_WEIGHT' => $item['BIRTH_WEIGHT'],
                    'SEX' => $item['SEX'],
                    'DISCHARGE_STATUS' => $item['DISCHARGE_STATUS'],
                    'DIAGLIST' => $item['DIAGLIST'],
                    'PROCLIST' => $item['PROCLIST'],
                    'ADL1' => $item['ADL1'],
                    'ADL2' => $item['ADL2'],
                    'IN_SP' => $item['IN_SP'],
                    'IN_SR' => $item['IN_SR'],
                    'IN_SI' => $item['IN_SI'],
                    'IN_SD' => $item['IN_SD'],
                    'INACBG' => $item['INACBG'],
                    'SUBACUTE' => $item['SUBACUTE'],
                    'CHRONIC' => $item['CHRONIC'],
                    'SP' => $item['SP'],
                    'SR' => $item['SR'],
                    'SI' => $item['SI'],
                    'SD' => $item['SD'],
                    'DESKRIPSI_INACBG' => $item['DESKRIPSI_INACBG'],
                    'TARIF_INACBG' => $item['TARIF_INACBG'],
                    'TARIF_SUBACUTE' => $item['TARIF_SUBACUTE'],
                    'TARIF_CHRONIC' => $item['TARIF_CHRONIC'],
                    'DESKRIPSI_SP' => $item['DESKRIPSI_SP'],
                    'TARIF_SP' => $item['TARIF_SP'],
                    'DESKRIPSI_SR' => $item['DESKRIPSI_SR'],
                    'TARIF_SR' => $item['TARIF_SR'],
                    'DESKRIPSI_SI' => $item['DESKRIPSI_SI'],
                    'TARIF_SI' => $item['TARIF_SI'],
                    'DESKRIPSI_SD' => $item['DESKRIPSI_SD'],
                    'TARIF_SD' => $item['TARIF_SD'],
                    'TOTAL_TARIF' => $item['TOTAL_TARIF'],
                    'TARIF_RS' => $item['TARIF_RS'],
                    'TARIF_POLI_EKS' => $item['TARIF_POLI_EKS'],
                    'LOS' => $item['LOS'],
                    'ICU_INDIKATOR' => $item['ICU_INDIKATOR'],
                    'ICU_LOS' => $item['ICU_LOS'],
                    'VENT_HOUR' => $item['VENT_HOUR'],
                    'NAMA_PASIEN' => $item['NAMA_PASIEN'],
                    'MRN' => $item['MRN'],
                    'UMUR_TAHUN' => $item['UMUR_TAHUN'],
                    'UMUR_HARI' => $item['UMUR_HARI'],
                    'DPJP' => $item['DPJP'],
                    'SEP' => $item['SEP'],
                    'NOKARTU' => $item['NOKARTU'],
                    'PAYOR_ID' => $item['PAYOR_ID'],
                    'CODER_ID' => $item['CODER_ID'],
                    'VERSI_INACBG' => $item['VERSI_INACBG'],
                    'VERSI_GROUPER' => $item['VERSI_GROUPER'],
                    'C1' => $item['C1'],
                    'C2' => $item['C2'],
                    'C3' => $item['C3'],
                    'C4' => $item['C4'],
                   


                ]);
                // $data1 = new BPJSKlaimTxt();
                // $data1->norec = $data1->generateNewId();
                // $data1->kdprofile = 0;
                // $data1->statusenabled = true;


                // $data1->KODE_RS = $item['KODE_RS'];
                // $data1->KELAS_RS = $item['KELAS_RS'];
                // $data1->KELAS_RAWAT = $item['KELAS_RAWAT'];
                // $data1->KODE_TARIF = $item['KODE_TARIF'];
                // $data1->PTD = $item['PTD'];
                // $data1->ADMISSION_DATE = $item['ADMISSION_DATE'];
                // $data1->DISCHARGE_DATE = $item['DISCHARGE_DATE'];
                // $data1->BIRTH_DATE = $item['BIRTH_DATE'];
                // $data1->BIRTH_WEIGHT = $item['BIRTH_WEIGHT'];
                // $data1->SEX = $item['SEX'];
                // $data1->DISCHARGE_STATUS = $item['DISCHARGE_STATUS'];
                // $data1->DIAGLIST = $item['DIAGLIST'];
                // $data1->PROCLIST = $item['PROCLIST'];
                // $data1->ADL1 = $item['ADL1'];
                // $data1->ADL2 = $item['ADL2'];
                // $data1->IN_SP = $item['IN_SP'];
                // $data1->IN_SR = $item['IN_SR'];
                // $data1->IN_SI = $item['IN_SI'];
                // $data1->IN_SD = $item['IN_SD'];
                // $data1->INACBG = $item['INACBG'];
                // $data1->SUBACUTE = $item['SUBACUTE'];
                // $data1->CHRONIC = $item['CHRONIC'];
                // $data1->SP = $item['SP'];
                // $data1->SR = $item['SR'];
                // $data1->SI = $item['SI'];
                // $data1->SD = $item['SD'];
                // $data1->DESKRIPSI_INACBG = $item['DESKRIPSI_INACBG'];
                // $data1->TARIF_INACBG = $item['TARIF_INACBG'];
                // $data1->TARIF_SUBACUTE = $item['TARIF_SUBACUTE'];
                // $data1->TARIF_CHRONIC = $item['TARIF_CHRONIC'];
                // $data1->DESKRIPSI_SP = $item['DESKRIPSI_SP'];
                // $data1->TARIF_SP = $item['TARIF_SP'];
                // $data1->DESKRIPSI_SR = $item['DESKRIPSI_SR'];
                // $data1->TARIF_SR = $item['TARIF_SR'];
                // $data1->DESKRIPSI_SI = $item['DESKRIPSI_SI'];
                // $data1->TARIF_SI = $item['TARIF_SI'];
                // $data1->DESKRIPSI_SD = $item['DESKRIPSI_SD'];
                // $data1->TARIF_SD = $item['TARIF_SD'];
                // $data1->TOTAL_TARIF = $item['TOTAL_TARIF'];
                // $data1->TARIF_RS = $item['TARIF_RS'];
                // $data1->TARIF_POLI_EKS = $item['TARIF_POLI_EKS'];
                // $data1->LOS = $item['LOS'];
                // $data1->ICU_INDIKATOR = $item['ICU_INDIKATOR'];
                // $data1->ICU_LOS = $item['ICU_LOS'];
                // $data1->VENT_HOUR = $item['VENT_HOUR'];
                // $data1->NAMA_PASIEN = $item['NAMA_PASIEN'];
                // $data1->MRN = $item['MRN'];
                // $data1->UMUR_TAHUN = $item['UMUR_TAHUN'];
                // $data1->UMUR_HARI = $item['UMUR_HARI'];
                // $data1->DPJP = $item['DPJP'];
                // $data1->sep = $item['SEP'];
                // $data1->NOKARTU = $item['NOKARTU'];
                // $data1->PAYOR_ID = $item['PAYOR_ID'];
                // $data1->CODER_ID = $item['CODER_ID'];
                // $data1->VERSI_INACBG = $item['VERSI_INACBG'];
                // $data1->VERSI_GROUPER = $item['VERSI_GROUPER'];
                // $data1->C1 = $item['C1'];
                // $data1->C2 = $item['C2'];
                // $data1->C3 = $item['C3'];
                // $data1->C4 = $item['C4'];
                // $data1->txtfilename = $request['filename'];
                // $data1->save();
            }
         
        DB::table('bpjsklaimtxt_t')->insert($data_to_insert);
        //     $transStatus = 'true';
        // } catch (\Exception $e) {
            $transStatus = 'false';
        // }
        DB::disableQueryLog();
        $transMessage = "Simpan BPJS Klaim";
        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage . ' Berhasil',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage . ' Gagal',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getMonitoringKlaimEIS(Request $request)
    {
          $kdProfile = $this->getDataKdProfile($request);
          $data = DB::table('monitoringklaim_t')
              ->where('jenispelayanan',$request['jenispelayanan'])
              ->where('statusklaimfk',$request['statusklaimfk'])
              ->whereRaw("to_char(tglpulang,'yyyy-MM') ='$request[bulan]'")
              ->get();

          $result = array(
                'data' => $data,
                'as' => 'er@epic',
          );

          return $this->respond($result);
    }
    function formatRp($number)
    {
       return 'Rp.'.number_format((float)$number ,2,".",",");
    }
    public function getAllMonitoringKlaim(Request $request)
    {
        $kdProfile = (int)$this->getDataKdProfile($request);
        $start = $month = strtotime('2019-01-01');
        $end = strtotime(date('Y-m-d'));
        $arrM = [];
        while($month < $end)
        {
             $arrM [] = array(
                'blntahun' =>  date('Y-m', $month),
                'tahun' =>  date('Y', $month),
                'bulan' =>  date('F', $month),
                'jmlkasus_ri' => 0,
                'jmlkasuspending_ri' => 0,
                'pengajuan_ri' =>  $this->formatRp(0),
                'pending_ri' => $this->formatRp(0),
                'klaim_ri' => $this->formatRp(0),
                'jmlkasus_rj' => 0,
                'jmlkasuspending_rj' => 0,
                'pengajuan_rj' =>$this->formatRp(0),
                'pending_rj' => $this->formatRp(0),
                'klaim_rj' =>$this->formatRp(0),
              ); 
             $month = strtotime("+1 month", $month);
        }
        foreach ($arrM  as $key => $row) {
            $count[$key] = $row['blntahun'];
        }
        array_multisort($count, SORT_DESC, $arrM);
        // return $arrM ;
        $data =  DB::select(DB::raw("select x.tahun,x.bulan,
            sum(x.jmlkasus_ri) as jmlkasus_ri ,sum(x.jmlkasuspending_ri) as jmlkasuspending_ri ,
            sum(x.pengajuan_ri) as pengajuan_ri ,
            sum(x.pending_ri) as pending_ri ,
            sum(x.klaim_ri) as klaim_ri ,

            sum(x.jmlkasus_rj) as jmlkasus_rj ,sum(x.jmlkasuspending_rj) as jmlkasuspending_rj,
            sum(x.pengajuan_rj) as pengajuan_rj,
            sum(x.pending_rj) as pending_rj ,
            sum(x.klaim_rj) as klaim_rj,
            x.blntahun
             from (
            SELECT to_char(tglpulang,'yyyy') as tahun,to_char(tglpulang,'MM')as  bulan,
             to_char(tglpulang,'yyyy') || '-' ||to_char(tglpulang,'MM')as blntahun,
            case when jenispelayanan ='1' then count(norec) else 0 end as jmlkasus_ri,
            case when jenispelayanan ='2' then count(norec) else 0  end as jmlkasus_rj,
            case when jenispelayanan ='1'  and status='Proses Pending' then 1 else 0 end as jmlkasuspending_ri,
            case when jenispelayanan ='2'  and status='Proses Pending' then 1 else 0 end as jmlkasuspending_rj,
            case when jenispelayanan ='1' then   sum(totalpengajuan) else 0 end as pengajuan_ri,
            case when jenispelayanan ='2' then   sum(totalpengajuan) else 0 end as pengajuan_rj,
            case when jenispelayanan ='1' and  status='Proses Pending' then sum(totalpengajuan) else 0 end as pending_ri,
            case when jenispelayanan ='2' and  status='Proses Pending' then sum(totalpengajuan) else 0 end as pending_rj,
            case when jenispelayanan ='1' and status='Klaim' then sum(totalpengajuan) else 0 end as klaim_ri,
            case when jenispelayanan ='2' and status='Klaim' then sum(totalpengajuan) else 0 end as klaim_rj

            FROM monitoringklaim_t
            where kdprofile=$kdProfile
            group by tglpulang,status,jenispelayanan
            ) as x GROUP BY x.tahun,x.bulan,x.blntahun
            order by x.blntahun desc;


                "));
            $i = 0;
         foreach ($arrM as $key => $v) {
            foreach ($data as $key2 => $k) {
                if($arrM[$i]['blntahun'] == $k->blntahun){
                    $arrM[$i]['blntahun'] = $k->blntahun;
                    $arrM[$i]['tahun']  = $k->tahun;
                    // $arrM[$i]['bulan']  =  $k->bulan;
                    $arrM[$i]['jmlkasus_ri'] =  $k->jmlkasus_ri;
                    $arrM[$i]['jmlkasuspending_ri'] =  $k->jmlkasuspending_ri;
                    $arrM[$i]['pengajuan_ri'] =  $this->formatRp( $k->pengajuan_ri);
                    $arrM[$i]['pending_ri'] = $this->formatRp( $k->pending_ri);
                    $arrM[$i]['klaim_ri'] = $this->formatRp( $k->klaim_ri);
                    $arrM[$i]['jmlkasus_rj'] =  $k->jmlkasus_rj;
                    $arrM[$i]['jmlkasuspending_rj'] = $k->jmlkasuspending_rj;
                    $arrM[$i]['pengajuan_rj'] =$this->formatRp( $k->pengajuan_rj);
                    $arrM[$i]['pending_rj'] = $this->formatRp( $k->pending_rj);
                    $arrM[$i]['klaim_rj'] =$this->formatRp( $k->klaim_rj);
                     
                }
            }
            $i++;
         }


          $result = array(
                'data' => $arrM,
                'as' => 'er@epic',
          );

          return $this->respond($result);
    }
}