<?php


namespace App\Http\Controllers\Report;


use App\Http\Controllers\ApiController;
use App\Master\Ruangan;
use App\Traits\Valet;
use App\Transaksi\HasilLaboratorium;
use Illuminate\Http\Request;
use DB;

use Barryvdh\DomPDF\Facade as PDF;


class ReportController extends ApiController{

    use Valet;

    public function __construct(){
        parent::__construct($skip_authentication = true);
    }

    public function getDataHasilLabCetak(Request $request){
//        $kdProfile = (int) $this->getDataKdProfile($request);
        $umur = $request['umur'];
        $Tgl = self::getDateIndo( date('Y-m-d H:i:s'));
        $idKelapaLab = (int) $this->settingDataFixed('PenanggungJawabLabRSKI', 17);
//         $data =  DB::table('pelayananpasien_t as pp')
//                 ->join('produk_m as prd','prd.id','=','pp.produkfk')
//                 ->join('detailjenisproduk_m as djp','djp.id','=','prd.objectdetailjenisprodukfk')
//                 ->leftJOIN('produkdetaillaboratorium_m as pdl','pdl.produkfk','=','prd.id')
//                 ->leftjoin('produkdetaillaboratoriumnilainormal_m as pdlm', function ($join)use ($request)  {
//                      $join->on('pdlm.objectprodukdetaillabfk','=','pdl.id')
//                         ->where('pdlm.objectjeniskelaminfk','=',$request['objectjeniskelaminfk'] );
//                 })
//                 ->leftJoin('hasillaboratorium_t as hh', function ($join) {
//                     $join->on('pp.noregistrasifk', '=', 'hh.noregistrasifk')
//                         ->on('pp.norec','=','hh.pelayananpasienfk')
//                         ->on('hh.produkdetaillabfk','=','pdl.id');
//                 })
//                 ->leftJOIN('satuanstandar_m as ss','ss.id','=','pdl.objectsatuanstandarfk')
//                 ->select(DB::raw("pp.noregistrasifk as norec_apd,djp.detailjenisproduk,pp.produkfk,prd.namaproduk ,
//                     pdl.detailpemeriksaan,
//                     hh.hasil,pdlm.rangemin as nilaimin,pdlm.rangemax as nilaimax,pdlm.refrange as nilaitext,
//                     ss.satuanstandar as satuan,pdl.id as iddetailproduk,
//                     hh.metode,pp.norec as norec_pp,
//                     hh.norec as norec_hasil"))
// //                ->where('pp.kdprofile', $kdProfile)
//                 ->where('pp.noregistrasifk', $request['norec_apd'])
//                 ->orderBy('prd.namaproduk', 'asc')
//                 ->get();

//                  $data = $data->groupBy('detailjenisproduk');
        $data =collect(DB::select("SELECT pp.noregistrasifk as norec_apd,djp.detailjenisproduk,pp.produkfk,prd.namaproduk ,
                    pdl.detailpemeriksaan,
                    hh.hasil,pdlm.rangemin as nilaimin,pdlm.rangemax as nilaimax,pdlm.refrange as nilaitext,
                    ss.satuanstandar as satuan,pdl.id as iddetailproduk,
                    hh.metode,pp.norec as norec_pp,
                    hh.norec as norec_hasil
                    FROM pelayananpasien_t as pp
                    inner join produk_m as prd on prd.id = pp.produkfk
                    inner join detailjenisproduk_m as djp on djp.id = prd.objectdetailjenisprodukfk
                    left JOIN produkdetaillaboratorium_m pdl on pdl.produkfk = prd.id 
                    left join produkdetaillaboratoriumnilainormal_m as pdlm on pdlm.objectprodukdetaillabfk = pdl.id
                    and pdlm.objectjeniskelaminfk=$request[objectjeniskelaminfk] 
                    left join hasillaboratorium_t as hh on hh.produkfk = prd.id
                    and pp.noregistrasifk=hh.noregistrasifk
                    and pp.norec=hh.pelayananpasienfk
                    and hh.produkdetaillabfk =pdl.id

                    left join satuanstandar_m as ss on ss.id = pdl.objectsatuanstandarfk
                    where pp.noregistrasifk='$request[norec_apd]'
                    and pp.kdprofile=17 
                    order by prd.namaproduk asc"));
        // $data =$data->groupBy('namaproduk');
        // dd($data);
        $diagnosa = \DB::table('pasiendaftar_t AS pd')
            ->join('antrianpasiendiperiksa_t AS apd','apd.noregistrasifk','=','pd.norec')
            ->join('detaildiagnosapasien_t as ddg','ddg.noregistrasifk','=','apd.norec')
            ->join('diagnosa_m AS dm','dm.id','=','ddg.objectdiagnosafk')
            ->select(DB::raw("CASE WHEN apd.norec IS NULL THEN ' , ' ELSE dm.kddiagnosa || ', ' || dm.namadiagnosa END AS diagnosa"))
//            ->where('pd.kdprofile', $kdProfile)
            ->where('ddg.objectjenisdiagnosafk', 1)
            ->where('apd.norec',  $request['norec_apd'])
            ->first();
        $namaDiagnosa='';
        if ($diagnosa != null){
            $namaDiagnosa = $diagnosa->diagnosa;
        }
        $getNorePd = \DB::table('pasiendaftar_t AS pd')
            ->join('antrianpasiendiperiksa_t AS apd','apd.noregistrasifk','=','pd.norec')
            ->leftJoin('strukorder_t AS so','so.noregistrasifk','=','pd.norec')
            ->join('pasien_m AS pm','pm.id','=','pd.nocmfk')
            ->join('jeniskelamin_m AS jk','jk.id','=','pm.objectjeniskelaminfk')
            ->join('ruangan_m AS ru','ru.id','=','pd.objectruanganlastfk')
            ->join('departemen_m AS dept','dept.id','=','ru.objectdepartemenfk')
            ->leftJoin('pegawai_m AS pg','pg.id','=','so.objectpegawaiorderfk')
            ->join('kelas_m AS kls','kls.id','=','pd.objectkelasfk')
            ->select(DB::raw(  "'B/287/VIII/' || to_char(now(),'YYYY') AS nomor,pm.nocm,pm.namapasien,pm.tempatlahir,
                                                to_char(pm.tgllahir,'DD-MM-YYYY') AS tgllahir,jk.jeniskelamin,kls.namakelas,
			                                    dept.namadepartemen,ru.namaruangan,so.tglorder,pg.namalengkap,apd.tglmasuk,
			                                    CASE WHEN pm.notelepon IS NULL THEN '' ELSE pm.notelepon END AS notelepon"))
            ->where('apd.norec', $request['norec_apd'])
//                    ->where('apd.kdprofile', $kdProfile)
            ->first();
        $KepalaLab = \DB::table('pegawai_m AS pg')
            ->leftJoin('pangkat_m AS pt','pt.id','=','pg.objectpangkatfk')
            ->leftJoin('jabatan_m AS jb','jb.id','=','pg.objectjabataninternalfk')
            ->select(DB::raw("pg.namalengkap,pg.nippns,CASE WHEN pg.objectpangkatfk IS NULL THEN '' ELSE pt.namapangkat END AS pangkat,
			                                 CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nippns,CASE WHEN pg.objectjabataninternalfk IS NULL THEN '' ELSE jb.namajabatan END AS jabatan"))
//                     ->where('pg.kdprofile', $kdProfile)
            ->where('pg.id',$idKelapaLab)
            ->first();
        if(count($data) == 0){
            return 'Data tidak ada';
            die;
        }
        $head = array(
            "nomor" => $getNorePd->nomor,
            "nocm" => $getNorePd->nocm,
            "namapasien" => $getNorePd->namapasien,
            "tgllahir" => $getNorePd->tgllahir,
            "jeniskelamin" => $getNorePd->jeniskelamin,
            "namakelas" => $getNorePd->namakelas,
            "namadepartemen" => $getNorePd->namadepartemen,
            "namaruangan" => $getNorePd->namaruangan,
            "tglorder" => $getNorePd->tglorder,
            "namalengkap" => $getNorePd->namalengkap,
            "tglmasuk" => $getNorePd->tglmasuk,
            "notelepon" => $getNorePd->notelepon,
            "diagnosa" => $namaDiagnosa,
            "tgl" => $Tgl,
            "namakepala" => $KepalaLab->namalengkap,
            "pangkat" => $KepalaLab->pangkat . " NRP " . $KepalaLab->nippns
        );
        if(count($data) == 0){
            return 'Data tidak ada';
            die;
        }
        $dataReport = array(
            'head' => $head,
            'data' => $data,
        );
//        return $this->respond($dataReport);
        return view('design.cetak-hasil-laboratorium',compact('dataReport'));
    }

    public function getDataSuratKeteranganPulangCetak(Request $request){
        $user = $request['strIdPegawai'];
        $kdProfile = (int) $request['kdProfile'];
        $Tgl = self::getDateIndo( date('Y-m-d H:i:s'));
        $idKepalaRs = (int) $this->settingDataFixed('KdKepalaRumahSakit', $kdProfile);
        $bulanromawi =  $this->KonDecRomawi($this->getDateTime()->format('m'));
        $DataProfile = DB::table('profile_m')
            ->where('id', $kdProfile)
            ->first();
        $dataSurat = [];
        if ($DataProfile->id == 17){
            $dataSurat = DB::table('suratketerangan_t as sk')
                ->join('pasiendaftar_t as pd','pd.norec','=', 'sk.pasiendaftarfk')
                ->join('pasien_m as pm','pm.id','=','pd.nocmfk')
                ->leftJoin('alamat_m as alm','alm.nocmfk','=','pm.id')
                ->join('pegawai_m as pg','pg.id','=','sk.dokterfk')
                ->join('jeniskelamin_m as jk','jk.id','=','pm.objectjeniskelaminfk')
                ->leftJoin('pekerjaan_m as pk','pk.id','=','pm.objectpekerjaanfk')
                ->leftJoin('pangkat_m AS pt','pt.id','=','pg.objectpangkatfk')
                ->leftJoin('jabatan_m AS jb','jb.id','=','pg.objectjabataninternalfk')
                ->select(DB::raw("'/VIII/' || to_char(now(),'YYYY') AS nomor,sk.nosurat,sk.norec,pm.nocm,pd.noregistrasi,pm.namapasien,pm.tempatlahir,  
                                            to_char(pm.tgllahir, 'DD-MM-YYYY') AS tgllahir,jk.jeniskelamin,alm.alamatlengkap,  
                                            sk.keterangan,pg.namalengkap,'NIP. ' || CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nip,  
                                            CASE WHEN pk.pekerjaan IS NULL THEN '' ELSE pk.pekerjaan END AS pekerjaan,  
                                            pd.tglregistrasi,CASE WHEN pg.objectpangkatfk IS NULL THEN '' ELSE pt.namapangkat END AS pangkat,  
                                            CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nippns,  
                                            CASE WHEN pg.objectjabataninternalfk IS NULL THEN '' ELSE jb.namajabatan END AS jabatan,  
                                            CASE WHEN pd.tglpulang IS NOT NULL THEN pd.tglpulang ELSE NOW() END AS tglpulang"))
                ->where('sk.kdprofile', $kdProfile)
                ->where('sk.norec', $request['norec'])
//                ->where('sk.statusenabled', true)
                ->get();
        }elseif ($DataProfile->id == 18){
            $dataSurat = DB::table('pasiendaftar_t as pd')
                ->leftJoin('suratketerangan_t as sk',function($join){
                    $join->on('sk.pasiendaftarfk','=', 'pd.norec');
                    $join->where('sk.statusenabled','=',true);
                    $join->where('sk.jenissuratfk','=',12);

                })
                ->leftJoin('pasien_m as pm','pm.id','=','pd.nocmfk')
                ->leftJoin('alamat_m as alm','alm.nocmfk','=','pm.id')
                ->leftJoin('pegawai_m as pg','pg.id','=','sk.dokterfk')
                ->leftJoin('jeniskelamin_m as jk','jk.id','=','pm.objectjeniskelaminfk')
                ->leftJoin('pekerjaan_m as pk','pk.id','=','pm.objectpekerjaanfk')
                ->leftJoin('pangkat_m AS pt','pt.id','=','pg.objectpangkatfk')
                ->leftJoin('jabatan_m AS jb','jb.id','=','pg.objectjabataninternalfk')
                ->leftJoin('desakelurahan_m as dk','dk.id','=','alm.objectdesakelurahanfk')
                ->leftJoin('kecamatan_m AS kc','kc.id', '=','alm.objectkecamatanfk')
                ->leftJoin('kotakabupaten_m AS kb','kb.id','=', 'alm.objectkotakabupatenfk')
                ->leftJoin('propinsi_m as prop','prop.id','=','alm.objectpropinsifk')
                ->leftjoin('ruangan_m AS ru','ru.id','=','pd.objectruanganlastfk')
                ->leftjoin('departemen_m AS dep','dep.id','=','ru.objectdepartemenfk')
                ->LEFTjoin('strukorder_t as so',function($joins){
                    $joins->on('so.noregistrasifk','=','pd.norec');
                    $joins->where('so.statusenabled','=',true);
                    $joins->where('so.objectkelompoktransaksifk','=',153);
                })
                ->select(DB::raw("'SKSI/'||case when sk.nosurat is null then '' else SUBSTRING(sk.nosurat,5,10) end || '/$bulanromawi/' || to_char(now(),'YYYY') || '/RSDCWA' AS nomor,sk.nosurat,sk.norec,pm.nocm,pd.noregistrasi,pm.namapasien,pm.tempatlahir,  
                                            to_char(pm.tgllahir, 'DD-MM-YYYY') AS tgllahir,jk.jeniskelamin,alm.alamatlengkap,  
                                            sk.keterangan,pg.namalengkap,'NIP. ' || CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nip,  
                                            CASE WHEN pk.pekerjaan IS NULL THEN '' ELSE pk.pekerjaan END AS pekerjaan,  
                                            pd.tglregistrasi,CASE WHEN pg.objectpangkatfk IS NULL THEN '' ELSE pt.namapangkat END AS pangkat,  
                                            CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nippns,  
                                            CASE WHEN pg.objectjabataninternalfk IS NULL THEN '' ELSE jb.namajabatan END AS jabatan,  
                                            CASE WHEN pd.tglpulang IS NOT NULL THEN pd.tglpulang 
                                            WHEN so.tglrencana IS NOT NULL THEN so.tglrencana ELSE NOW() END AS tglpulang,
                                            dk.namadesakelurahan,kc.namakecamatan,kb.namakotakabupaten,prop.namapropinsi,alm.kodepos,
                                            CASE WHEN pd.tglpulang IS NOT NULL THEN EXTRACT(day from age(to_date(to_char(pd.tglpulang,'YYYY-MM-DD'),'YYYY-MM-DD'), to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))) || ' Hari'
                                            WHEN so.tglrencana IS NOT NULL THEN EXTRACT(day from age(to_date(to_char(so.tglrencana,'YYYY-MM-DD'),'YYYY-MM-DD'), to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))) || ' Hari'
                                            ELSE EXTRACT(day from age(current_date, to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))) || ' Hari' END AS lamarawat,dep.namadepartemen || ', '|| ru.namaruangan as rawat

                "))
                ->where('pd.kdprofile', $kdProfile)
                ->where('pd.norec', $request['norec'])
                ->get();
        }

        if(count($dataSurat) == 0){
            return 'Data tidak ada';
            die;
        }
        $KepalaRs = \DB::table('pegawai_m AS pg')
            ->leftJoin('pangkat_m AS pt','pt.id','=','pg.objectpangkatfk')
            ->leftJoin('jabatan_m AS jb','jb.id','=','pg.objectjabataninternalfk')
            ->select(DB::raw("pg.namalengkap,pg.nippns,CASE WHEN pg.objectpangkatfk IS NULL THEN '' ELSE pt.namapangkat END AS pangkat,
			                         CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nippns,CASE WHEN pg.objectjabataninternalfk IS NULL THEN '' ELSE jb.namajabatan END AS jabatan"))
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.id',$idKepalaRs)
            ->first();



        $tglRegis= '';//self::getDateIndo($dataSurat['tglregistrasi']);
        $tglPulang='';
        $nrp ='';
        foreach ($dataSurat as $item){
            $tglRegis = self::getDateIndo($item->tglregistrasi) ;
            $tglPulang = self::getDateIndo($item->tglpulang);
            $nrp = $item->pangkat . ' NRP ' . $item->nippns;
        }

        $dataReport = array(
            'data' => $dataSurat,
            'kepalaRs' => $KepalaRs,
            'tanggal' => $Tgl,
            'tanggalrawat' => $tglRegis . ' s.d ' . $tglPulang,
            'nrp'=> $nrp,
            'namaprofile' => $DataProfile->namalengkap,
            'namaexternal' => $DataProfile->namaexternal,
            'namakota' => $DataProfile->namakota,
            'profileId' => $DataProfile->id,
        );

        if ($DataProfile->id == 17){
            //   return $this->respond($dataReport->namalengkap);
            return view('design.cetak-surat-keterangan-pulang',compact('dataReport'));
        }elseif ($DataProfile->id == 18){
//               return $this->respond($dataReport);
            return view('design.cetak-surat-keterangan-pulang-wsa',compact('dataReport'));
        }
    }

    public function getDataRekapLabel(Request $request){
        $user = $request['strIdPegawai'];
        $kdProfile = (int) $request['kdProfile'];
        $Tgl = self::getDateIndo( date('Y-m-d H:i:s'));
        $noreSR = $request['norec'];
        $DataProfile = DB::table('profile_m')
            ->where('id', $kdProfile)
            ->first();//DB::select(DB::raw("select * from profile_m where id = '$kdProfile' limit 1"));
        $data = DB::select(DB::raw("
            						SELECT distinct ps.nocm, ps.namapasien,to_char(ps.tgllahir, 'DD/MM/YYYY') as tgllahir,CASE WHEN aa.noantri IS NULL THEN sr.noresep ELSE aa.jenis || '-' || aa.noantri END AS noresep,to_char(sr.tglresep, 'DD-MM-YYYY') as tglresep,pr.namaproduk || ' (' || CAST(pp.jumlah AS VARCHAR) || ')' AS namaproduk,pp.aturanpakai,pp.rke,  
                                    CASE WHEN alm.alamatlengkap is null then '-' else alm.alamatlengkap end as alamat,ps.notelepon,ss.satuanstandar,pp.jumlah,  
                                    CASE WHEN pp.issiang = 't' THEN 'Siang' ELSE '-' END AS siang, CASE WHEN pp.ispagi = 't' THEN 'Pagi' ELSE '-' END AS pagi,  
                                    CASE WHEN pp.ismalam = 't' THEN 'Malam' ELSE '-' END as malam, CASE WHEN pp.issore = 't' THEN 'Sore' ELSE '-' END as sore,  
                                    CASE WHEN pp.keteranganpakai  = '' OR pp.keteranganpakai IS NULL THEN '-' else pp.keteranganpakai END AS keteranganpakai,
                                    ru.namaruangan,dep.namadepartemen 
                                    from pelayananpasien_t as pp inner join strukresep_t as sr on sr.norec= pp.strukresepfk  
                                    LEFT join produk_m as pr on pr.id = pp.produkfk  
                                    LEFT join antrianpasiendiperiksa_t as apd on apd.norec = pp.noregistrasifk  
                                    LEFT join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk  
                                    LEFT join pasien_m as ps on ps.id = pd.nocmfk  
                                    left join alamat_m as alm on alm.nocmfk = ps.id  
                                    LEFT JOIN satuanstandar_m as ss on ss.id = pp.satuanviewfk  
                                    LEFT JOIN antrianapotik_t as aa on aa.noresep = sr.noresep  
                                    LEFT JOIN ruangan_m as ru on ru.id = apd.objectruanganfk 
                                    LEFT JOIN departemen_m as dep on dep.id = ru.objectdepartemenfk 
                                    where pp.kdprofile = $kdProfile and pp.jeniskemasanfk = 2 and sr.norec ='$noreSR'        
            
                                    union all 
        
                                    select distinct ps.nocm,ps.namapasien,to_char(ps.tgllahir, 'DD/MM/YYYY') as tgllahir,CASE WHEN aa.noantri IS NULL THEN sr.noresep ELSE aa.jenis || '-' || aa.noantri END AS noresep,to_char(sr.tglresep, 'DD-MM-YYYY') as tglresep,  
                                    ' Racikan' || ' (' || CAST(((CAST(pp.qtydetailresep as INTEGER)/CAST(pp.dosis as INTEGER))*CAST(pro.kekuatan as INTEGER)) AS VARCHAR) || ')' AS namaproduk,pp.aturanpakai,pp.rke,  
                                    case when alm.alamatlengkap is null then '-' else alm.alamatlengkap end as alamat,ps.notelepon,CASE when jr.jenisracikan IS NULL THEN '' ELSE jr.jenisracikan END AS satuanstandar,  
                                    ((CAST(pp.qtydetailresep as INTEGER)/CAST(pp.dosis as INTEGER))*CAST(pro.kekuatan as INTEGER)) as jumlah,  
                                    CASE WHEN pp.issiang = 't' THEN 'Siang' ELSE '-' END AS siang, CASE WHEN pp.ispagi = 't' THEN 'Pagi' ELSE '-' END AS pagi,  
                                    CASE WHEN pp.ismalam = 't' THEN 'Malam' ELSE '-' END as malam, CASE WHEN pp.issore = 't' THEN 'Sore' ELSE '-' END as sore,  
                                    CASE WHEN pp.keteranganpakai  = '' OR pp.keteranganpakai IS NULL THEN '-' else pp.keteranganpakai END AS keteranganpakai,
                                    ru.namaruangan,dep.namadepartemen 
                                    from strukresep_t as sr   
                                    LEFT join pelayananpasien_t as pp on sr.norec= pp.strukresepfk  
                                    LEFT join antrianpasiendiperiksa_t as apd on apd.norec = sr.pasienfk  
                                    LEFT join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk  
                                    LEFT join pasien_m as ps on ps.id = pd.nocmfk  
                                    left join alamat_m as alm on alm.nocmfk = ps.id  
                                    LEFT JOIN produk_m as pro on pro.id = pp.produkfk  
                                    LEFT JOIN satuanstandar_m as ss on ss.id = pp.satuanviewfk  
                                    LEFT JOIN jenisracikan_m as jr on jr.id = pp.jenisobatfk  
                                    LEFT JOIN antrianapotik_t as aa on aa.noresep = sr.noresep
                                    LEFT JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                                    LEFT JOIN departemen_m as dep on dep.id = ru.objectdepartemenfk 
                                    where pp.kdprofile = $kdProfile and pp.jeniskemasanfk = 1 and sr.norec ='$noreSR' "));
        if(count($data) == 0){
            return 'Data tidak ada';
            die;
        }
        $tglRegis= '';
        $tglPulang='';
        $nrp ='';

//        $tglRegis = '';
//        foreach ($data as $item){
//            $tglRegis = self::getDateIndo($item->tglregistrasi);
//        }

        $dataReport = array(
            'data' => $data,
            'namaprofile' => $DataProfile->namalengkap,
            'namaexternal' => $DataProfile->namaexternal,
            'namakota' => $DataProfile->namakota,
            'alamatlengkap' => $DataProfile->alamatlengkap,
            'profileId' => $DataProfile->id,
        );

//          return $this->respond($dataReport);
        return view('design.cetak-label-rekap',compact('dataReport'));
    }

    public function getDataBuktiPendaftaraan(Request $request){
        $user = $request['strIdPegawai'];
        $kdProfile = (int) $request['kdProfile'];
        $Tgl = self::getDateIndo( date('Y-m-d H:i:s'));
        $noregistrasi = $request['noRegistrasi'];
        $DataProfile = DB::table('profile_m')
            ->where('id', $kdProfile)
            ->first();
        $data = DB::select(DB::raw("SELECT pd.noregistrasi,ps.nocm,ps.tgllahir,ps.namapasien,pd.tglregistrasi,jk.reportdisplay AS jk,
                                                 ap.alamatlengkap,ap.mobilephone2,ru.namaruangan AS ruanganPeriksa,pp.namalengkap AS namadokter,
                                                 kp.kelompokpasien,apdp.noantrian,pd.statuspasien,apr.noreservasi,apr.tanggalreservasi,
                                                 kmr.namakamar,tt.nomorbed,ru.namaruangan || ' No Kamar : ' || kmr.namakamar || ' No Bed : ' || tt.nomorbed AS kamar,
                                                 dept.namadepartemen
                                    FROM pasiendaftar_t pd
                                    INNER JOIN registrasipelayananpasien_t AS rpp ON rpp.noregistrasifk = pd.norec
                                    INNER JOIN pasien_m ps ON pd.nocmfk = ps.id
                                    LEFT JOIN alamat_m ap ON ap.nocmfk = ps.id
                                    INNER JOIN jeniskelamin_m jk ON ps.objectjeniskelaminfk = jk.id
                                    INNER JOIN ruangan_m ru ON pd.objectruanganlastfk = ru.id
                                    LEFT JOIN departemen_m AS dept ON dept.id = ru.objectdepartemenfk
                                    LEFT JOIN pegawai_m pp ON pd.objectpegawaifk = pp.id
                                    INNER JOIN kelompokpasien_m kp ON pd.objectkelompokpasienlastfk = kp.id
                                    INNER JOIN antrianpasiendiperiksa_t apdp ON apdp.noregistrasifk = pd.norec
                                    LEFT JOIN antrianpasienregistrasi_t as apr on apr.noreservasi=pd.statusschedule
                                    LEFT JOIN kamar_m AS kmr ON kmr.id = apdp.objectkamarfk
                                    LEFT JOIN tempattidur_m AS tt ON tt.id = apdp.nobed
                                    WHERE pd.kdprofile = $kdProfile and pd.noregistrasi = '$noregistrasi'"));

        if(count($data) == 0){
            return 'Data tidak ada';
            die;
        }

        $tglRegis = '';
        foreach ($data as $item){
            $tglRegis = self::getDateIndo($item->tglregistrasi);
        }

        $dataReport = array(
            'data' => $data,
            'namaprofile' => $DataProfile->namalengkap,
            'namaexternal' => $DataProfile->namaexternal,
            'namakota' => $DataProfile->namakota,
            'alamatlengkap' => $DataProfile->alamatlengkap,
            'profileId' => $DataProfile->id,
            'tglregis' => $tglRegis,
        );
//        return $this->respond($dataReport);
        return view('design.cetak-bukti-pendaftaraan',compact('dataReport'));
    }

    public function getDataSuratPersetujuanUmumCetak(Request $request){
        $user = $request['strIdPegawai'];
        $kdProfile = (int) $request['kdProfile'];
        $Tgl = self::getDateIndo( date('Y-m-d H:i:s'));
        $idKepalaRs = (int) $this->settingDataFixed('KdKepalaRumahSakit', $kdProfile);
        $DataProfile = DB::table('profile_m')
            ->where('id', $kdProfile)
            ->first();
        $dataSurat = DB::table('suratketerangan_t as sk')
            ->join('pasiendaftar_t as pd','pd.norec','=', 'sk.pasiendaftarfk')
            ->join('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->leftJoin('alamat_m as alm','alm.nocmfk','=','pm.id')
            ->join('pegawai_m as pg','pg.id','=','sk.dokterfk')
            ->join('jeniskelamin_m as jk','jk.id','=','pm.objectjeniskelaminfk')
            ->leftJoin('pekerjaan_m as pk','pk.id','=','pm.objectpekerjaanfk')
            ->leftJoin('pangkat_m AS pt','pt.id','=','pg.objectpangkatfk')
            ->leftJoin('jabatan_m AS jb','jb.id','=','pg.objectjabataninternalfk')
            ->join('antrianpasiendiperiksa_t as apd',function($join){
                $join->on('apd.noregistrasifk','=','pd.norec');
                $join->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
            })
            ->join('ruangan_m AS ru','ru.id','=','pd.objectruanganlastfk')
            ->join('departemen_m AS dep','dep.id','=','ru.objectdepartemenfk')
            ->leftJoin('kamar_m AS kmr','kmr.id','=','apd.objectkamarfk')
            ->leftJoin('tempattidur_m AS tt','tt.id','=','apd.nobed')
            ->leftJoin('desakelurahan_m as dk','dk.id','=','alm.objectdesakelurahanfk')
            ->leftJoin('kecamatan_m AS kc','kc.id', '=','alm.objectkecamatanfk')
            ->leftJoin('kotakabupaten_m AS kb','kb.id','=', 'alm.objectkotakabupatenfk')
            ->leftJoin('propinsi_m as prop','prop.id','=','alm.objectpropinsifk')
            ->select(DB::raw("'/VIII/' || to_char(now(),'YYYY') AS nomor,sk.nosurat,sk.norec,pm.nocm,pd.noregistrasi,pm.namapasien,pm.tempatlahir,  
                                    to_char(pm.tgllahir, 'DD-MM-YYYY') AS tgllahir,jk.jeniskelamin,alm.alamatlengkap,  
                                    sk.keterangan,pg.namalengkap,'NIP. ' || CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nip,  
                                    CASE WHEN pk.pekerjaan IS NULL THEN '' ELSE pk.pekerjaan END AS pekerjaan,  
                                    pd.tglregistrasi,CASE WHEN pg.objectpangkatfk IS NULL THEN '' ELSE pt.namapangkat END AS pangkat,  
                                    CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nippns,  
                                    CASE WHEN pg.objectjabataninternalfk IS NULL THEN '' ELSE jb.namajabatan END AS jabatan,  
                                    CASE WHEN pd.tglpulang IS NOT NULL THEN pd.tglpulang ELSE NOW() END AS tglpulang,
			                        ru.namaruangan	|| ' / ' || kmr.namakamar || ' ( ' || tt.nomorbed || ' )' as kamar,
			                        CASE WHEN pm.penanggungjawab IS NOT NULL THEN pm.penanggungjawab ELSE pm.namapasien END AS penanggungjawab,
			                        CASE WHEN pm.notelepon IS NULL AND pm.nohp IS NULL THEN '' 
			                        WHEN pm.notelepon IS NOT NULL AND pm.nohp IS NOT NULL THEN pm.notelepon || ' / ' || pm.nohp
			                        WHEN pm.notelepon IS NOT NULL AND pm.nohp IS NULL THEN pm.notelepon
			                        WHEN pm.nohp IS NOT NULL AND pm.notelepon IS NULL THEN pm.nohp END AS notelepon,
			                        dk.namadesakelurahan,kc.namakecamatan,kb.namakotakabupaten,prop.namapropinsi,alm.kodepos,dep.namadepartemen"))
            ->where('sk.kdprofile', $kdProfile)
            ->where('sk.norec', $request['norec'])
            ->get();
        if(count($dataSurat) == 0){
            return 'Data tidak ada';
            die;
        }
        $KepalaRs = \DB::table('pegawai_m AS pg')
            ->leftJoin('pangkat_m AS pt','pt.id','=','pg.objectpangkatfk')
            ->leftJoin('jabatan_m AS jb','jb.id','=','pg.objectjabataninternalfk')
            ->select(DB::raw("pg.namalengkap,pg.nippns,CASE WHEN pg.objectpangkatfk IS NULL THEN '' ELSE pt.namapangkat END AS pangkat,
			                         CASE WHEN pg.nippns IS NULL THEN '' ELSE pg.nippns END AS nippns,CASE WHEN pg.objectjabataninternalfk IS NULL THEN '' ELSE jb.namajabatan END AS jabatan"))
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.id',$idKepalaRs)
            ->first();

        $tglRegis= '';
        $tglPulang='';
        $nrp ='';
        foreach ($dataSurat as $item){
            $tglRegis = self::getDateIndo($item->tglregistrasi) ;
            $tglPulang = self::getDateIndo($item->tglpulang);
            $nrp = $item->pangkat . ' NRP ' . $item->nippns;
        }

        $dataReport = array(
            'data' => $dataSurat,
            'kepalaRs' => $KepalaRs,
            'tanggal' => $Tgl,
            'tanggalrawat' => $tglRegis . ' s.d ' . $tglPulang,
            'nrp'=> $nrp,
            'namaprofile' => $DataProfile->namalengkap,
            'namaexternal' => $DataProfile->namaexternal,
            'namakota' => $DataProfile->namakota,
            'profileId' => $DataProfile->id,
        );

//        return $this->respond($dataSurat);
        return view('design.cetak-surat-persetujuan-umum',compact('dataReport'));
    }
    public function cetakHasilLIS(Request $r) {
        $kdProfile = (int)$r['kdprofile'];
        $raw = collect(DB::select("SELECT
                so.tglorder,
                ps.nocm,
                so.noorder AS noorder,
                ps.namapasien,
                ps.tgllahir,
                jk.jeniskelamin,
            pg2.namalengkap as dokter,
                CASE
            WHEN alm.alamatlengkap IS NULL THEN
                '-'
            ELSE
                alm.alamatlengkap
            END AS alamatlengkap,
             ru.namaruangan,
             pd.noregistrasi,
             pg.namalengkap as djp,
             date_part('year', age(ps.tgllahir)) usia,
             kp.kelompokpasien AS cara_bayar,
             ru2.namaruangan AS ruangantujuan,
             pd.norec as norec_pd,kps.kelompokpasien
            FROM
                strukorder_t AS so
            INNER JOIN pasien_m AS ps ON ps. ID = so.nocmfk
            INNER JOIN pasiendaftar_t AS pd ON pd.norec = so.noregistrasifk
            INNER JOIN kelompokpasien_m AS kp ON kp. ID = pd.objectkelompokpasienlastfk
            INNER JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
            INNER JOIN ruangan_m AS ru2 ON ru2. ID = so.objectruangantujuanfk
            LEFT JOIN jeniskelamin_m AS jk ON jk. ID = ps.objectjeniskelaminfk
            LEFT JOIN kelompokpasien_m AS kps ON kps. ID = pd.objectkelompokpasienlastfk
            LEFT JOIN alamat_m AS alm ON alm.nocmfk = ps. ID
            LEFT JOIN pegawai_m AS pg ON pg. ID = so.objectpegawaiorderfk
            LEFT JOIN pegawai_m AS pg2 ON pg2. ID = pd.objectpegawaifk
            WHERE
                so.noorder = '$r[noorder]'
            AND so.kdprofile = $kdProfile
            AND so.statusenabled = TRUE;"))->first();
    $order_lab = collect(DB::select("SELECT
              *
            FROM
                order_lab AS so
             WHERE
                so.no_lab = '$r[noorder]'"))->first();
        if(!empty($raw)){
            $raw->umur = $this->getAge($raw->tgllahir ,date('Y-m-d'));
            $pdnorec = $raw->norec_pd;
            $diag =  DB::select(DB::raw("select DISTINCT dg.kddiagnosa , dg.namadiagnosa,pd.noregistrasi
                from pasiendaftar_t as pd 
                join antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
                join detaildiagnosapasien_t  as ddp on ddp.noregistrasifk=apd.norec
                join diagnosa_m  as dg on dg.id=ddp.objectdiagnosafk
                where pd.kdprofile=$kdProfile
                and pd.statusenabled =true
                and pd.norec ='$pdnorec'"));
            $raw->diagnosa = '';
            $arr ='';
            if(count($diag) > 0){
                foreach ($diag as $d){
                    $arr = $d->kddiagnosa .'-' .$d->namadiagnosa .  ' ,' .$arr;
                }
                $raw->diagnosa = $arr;
            }
        }

//        dd($raw);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://10.122.250.13:83/lab_result/api/examination_result/result/'.$r['noorder'],//$data->result_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
//            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json;",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result = "cURL Error #:" . $err;
        } else {
            $result = json_decode($response);
        }
        $pageWidth = 950;
        $data['jenis'] = 'bridging';
//        dd($result);
        if($result->status ==200){
            $data['res'] = $result->data;
        }else{
            echo 'Data Tidak Ada';
            return;
        }

        if($result == null){

           $result= collect( DB::connection('sqlsrv')
               ->select("select * from HasilLIS
           where NOLAB_RS= '$r[noorder]'"));
//           dd($result);

            $data['jenis'] = 'non';
            $data['res'] = $result;
        }
        $footer =true;
//        dd($data);
        return view('report.lab.hasil-lis',
            compact('data','raw', 'pageWidth','r','footer','order_lab'));

    }
    public function cetakEkspertise(Request $r) {
        $kdProfile = (int)$r['kdprofile'];
        $raw = collect(DB::select("
            SELECT
                so.nofoto,ps.nocm, ps.namapasien,ps.tgllahir,kp.kelompokpasien,
            ru.namaruangan, so.tanggal,jk.jeniskelamin,  
            CASE WHEN alm.alamatlengkap IS NULL THEN
                '-' ELSE (
                alm.alamatlengkap || ' ' || ds.namadesakelurahan || ' '|| kc.namakecamatan
                || ' ' || kk.namakotakabupaten || ' '  || pro.namapropinsi )
            END AS alamatlengkap,
            pg.namalengkap as perujuk,pg2.namalengkap as dokterrad,
            pr.namaproduk,so.keterangan,pd.noregistrasi,pg2.nippns,
            pg2.id as pgid
            FROM
                hasilradiologi_t AS so
            INNER JOIN pasiendaftar_t AS pd ON pd.norec = so.noregistrasifk
            INNER JOIN pasien_m AS ps ON ps. ID = pd.nocmfk
            INNER JOIN kelompokpasien_m AS kp ON kp. ID = pd.objectkelompokpasienlastfk
            INNER JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
            INNER JOIN pelayananpasien_t AS pp ON pp.norec = so.pelayananpasienfk
            INNER JOIN produk_m AS pr ON pr.id = pp.produkfk
            LEFT JOIN jeniskelamin_m AS jk ON jk. ID = ps.objectjeniskelaminfk
            LEFT JOIN kelompokpasien_m AS kps ON kps. ID = pd.objectkelompokpasienlastfk
            LEFT JOIN alamat_m AS alm ON alm.nocmfk = ps. ID
            left join desakelurahan_m as ds on ds.id=alm.objectdesakelurahanfk
            left join kotakabupaten_m as kk on kk.id=alm.objectkotakabupatenfk
            left join kecamatan_m as kc on kc.id=alm.objectkecamatanfk
            left join propinsi_m as pro on pro.id=alm.objectpropinsifk
            LEFT JOIN pegawai_m AS pg ON pg. ID = pd.objectpegawaifk
            LEFT JOIN pegawai_m AS pg2 ON pg2. ID = so.pegawaifk
            WHERE
                so.norec = '$r[norec]'
            AND so.kdprofile = $kdProfile
            AND so.statusenabled = TRUE
        "))->first();
//        dd($raw);
        if(!empty($raw)){
            $raw->umur = $this->getAge($raw->tgllahir ,date('Y-m-d'));
        }else{
            echo 'Data Tidak ada ';
            return;
        }
//        dd($raw);
        $pageWidth = 950;

        return view('report.rad.expertise',
            compact('raw', 'pageWidth','r'));

    }
    public function getKegiatanRanap(Request $r){
        $kdProfile = (int)$r['kdprofile'];
        $deptRanap = explode (',',$this->settingDataFixed('kdDepartemenRanapFix',  $kdProfile ));
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap){
            $kdDepartemenRawatInap []=  (int)$itemRanap;
        }
        $ruangan = Ruangan::where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->whereIn('objectdepartemenfk',$kdDepartemenRawatInap)
            ->select('id','namaruangan')
            ->get();

//        dd($ruangan);
//        $borlos = $this->getBorlostoi($r);
//        dd($borlos);
        $pasienPulang = $this->getPasienPulang($r);
//        dd($borlos);
        $pageWidth = 950;

        return view('report.rekammedis.kegiatan-ranap',
                compact('ruangan',
                    'pageWidth','r'));


    }
    function  getPasienPulang($request){
        $dat = DB::select(DB::raw("
            select case when x.skid =1 and x.spid in (1,2,6) then 'Biasa'
            when x.spid=12 then 'Paksa'
            when x.spid=3 then 'Kabur'
            when x.spid in (4,5,10,11) then 'Rujuk'
            when x.spid in (7) then 'Pindah'
            when x.kpid in (5) then '< 24 jam'
            when x.kpid in (6) then '> 24 jam'
            end as uraian,x.namaruangan
            from (
            select pd.noregistrasi, pd.tglregistrasi,pd.tglpulang, ru.namaruangan,
            ru.id,pd.objectstatuskeluarfk as skid,sk.statuskeluar,
            pd.objectstatuspulangfk as spid,
            sp.statuspulang,apd.israwatgabung,
            pd.objectkondisipasienfk as kpid,kp.kondisipasien,
            row_number() over (partition by pd.noregistrasi order by apd.tglmasuk asc) as rownum 
            from pasiendaftar_t as pd 
            join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
            and apd.objectruanganfk=pd.objectruanganlastfk
            left join ruangan_m as ru on ru.id = pd.objectruanganlastfk 
            left join statuskeluar_m as sk on sk.id = pd.objectstatuskeluarfk
            left join statuspulang_m as sp on sp.id = pd.objectstatuspulangfk
            left join kondisipasien_m as kp on kp.id = pd.objectkondisipasienfk
            where pd.kdprofile = 21 
            and ru.objectdepartemenfk =16
            and pd.statusenabled = true 
            and to_char(pd.tglpulang,'yyyy-MM') ='2020-10'
            )
            as x where x.rownum=1
            "));
    }
        public function getBorlostoi( $request)
        {
            $kdProfile = (int)$request['kdprofile'];
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
            WHERE 	ru.objectdepartemenfk IN (16, 35)
            AND ru.statusenabled = 1
            AND km.statusenabled = 1
            AND tt.statusenabled = 1
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
                    DATEDIFF(DAY, pd.tglregistrasi, pd.tglpulang) as hari 
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                WHERE
                    ru.objectdepartemenfk = 16
               -- AND pd.tglpulang BETWEEN '$firstDay' and '$lastDay'
                order by pd.tglpulang asc
                "));
            $LD = DB::select(DB::raw(" SELECT
                pd.noregistrasi,
                pd.tglregistrasi,
                pd.tglpulang,
                 pd.objectkelasfk,
                    DATEDIFF(DAY, pd.tglregistrasi, pd.tglpulang) as hari 
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                WHERE
                    ru.objectdepartemenfk = 16
              AND pd.tglregistrasi BETWEEN '$firstDay' and '$lastDay'
                order by pd.tglpulang asc
                "));

            $dataMeninggal = DB::select(DB::raw("select count(x.noregistrasi) as jumlahmeninggal, x.bulanregis,  
                count(case when x.objectkondisipasienfk = '6' then 1 end ) AS jumlahlebih48 FROM
                (
                select noregistrasi,Format(tglregistrasi , 'mm')  as bulanregis ,statuskeluar,kondisipasien,objectkondisipasienfk
                from pasiendaftar_t 
                join statuskeluar_m on statuskeluar_m.id =pasiendaftar_t.objectstatuskeluarfk
                left join kondisipasien_m on kondisipasien_m.id =pasiendaftar_t.objectkondisipasienfk
                where objectstatuskeluarfk =5
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

    public static  function getPasienAwal($ruid,$bulan,$kdprofile){
        $data = collect(DB::select("select pd.noregistrasi, pd.tglregistrasi,pd.tglpulang, ru.namaruangan,
            ru.id
            from pasiendaftar_t as pd 
            left join ruangan_m as ru on ru.id = pd.objectruanganlastfk 
            where pd.kdprofile = $kdprofile 
            and pd.statusenabled = true 
            and ru.id = $ruid
            and pd.tglpulang is null and to_char(pd.tglregistrasi,'yyyy-MM') <'$bulan'"))
        ->count();
//        dd($data);
        return $data;
    }
    public function cetakHispatologi(Request $r) {
        $kdProfile = (int)$r['kdprofile'];
        $raw = collect(DB::select("
            SELECT
                pd.noregistrasi, pm.nocm, pm.namapasien, hpl.dokterluar, dokterpengirim.namalengkap as namadokterpengirim,
                jk.jeniskelamin || '/ ' || EXTRACT ( YEAR
                    FROM AGE(  pd.tglregistrasi,pm.tgllahir )
                ) || ' Thn ' || EXTRACT (MONTH  FROM AGE(  pd.tglregistrasi, pm.tgllahir )
                ) || ' Bln ' || EXTRACT ( DAY  FROM  AGE( pd.tglregistrasi, pm.tgllahir )  ) || ' Hr' || '(' || to_char(pm.tgllahir, 'DD-MM-YYYY') || ')' AS umur,
                to_char(  so.tglorder, 'DD-MM-YYYY HH24:MI:SS') AS tglorder,
                to_char( hpl.tanggal, 'DD-MM-YYYY HH24:MI:SS'  ) AS tgljawab,
                to_char( pp.tglpelayanan,'DD-MM-YYYY HH24:MI:SS' ) AS tglterima,
                to_char(  sbm.tglsbm,'DD-MM-YYYY HH24:MI:SS' ) AS tglbayar, pg.namalengkap,
             CASE WHEN hpl.diagnosaklinik IS NULL THEN  ''  ELSE  diagnosaklinik  END AS diagnosaklinik,
             CASE WHEN hpl.keteranganklinik IS NULL THEN ''   ELSE hpl.keteranganklinik  END AS keteranganklinik,
             CASE WHEN hpl.makroskopik IS NULL THEN   '' ELSE  hpl.makroskopik END AS makroskopik, 
             CASE WHEN hpl.mikroskopik IS NULL THEN ''   ELSE  hpl.mikroskopik END AS mikroskopik, 
             CASE WHEN hpl.kesimpulan IS NULL THEN '' ELSE hpl.kesimpulan END AS kesimpulan,
             CASE WHEN hpl.anjuran IS NULL THEN  '' ELSE  hpl.anjuran END AS anjuran,
             CASE WHEN hpl.topografi IS NULL THEN   '' ELSE hpl.topografi  END AS topografi, 
             CASE WHEN hpl.morfologi IS NULL THEN  '' ELSE hpl.morfologi END AS morfologi,
             CASE WHEN hpl.diagnosapb IS NULL THEN  '' ELSE  hpl.diagnosapb  END AS diagnosapb,
             CASE WHEN hpl.keteranganpb IS NULL THEN ''  ELSE hpl.keteranganpb END AS keteranganpb,
             CASE WHEN pg1.namalengkap IS NULL THEN '' ELSE  pg1.namalengkap   END AS namapenanggungjawab,
             CASE WHEN pg1.nippns IS NULL THEN ''ELSE  pg1.nippns END AS nippns,hpl.nomorpa,
             ru.namaruangan as asal,pg1.nosip,
              CASE
                    WHEN alm.alamatlengkap IS NULL THEN
                        '-'
                    ELSE
                        (
                            alm.alamatlengkap || ' ' || (
                                CASE
                                WHEN ds.namadesakelurahan IS NOT NULL THEN
                                   'Kel. ' ||  ds.namadesakelurahan
                                ELSE
                                    ''
                                END
                            ) || ' ' || (
                                CASE
                                WHEN kc.namakecamatan IS NOT NULL THEN
                                   'Kec. ' || kc.namakecamatan
                                ELSE
                                    ''
                                END
                            ) || ' ' || (
                                CASE
                                WHEN kk.namakotakabupaten IS NOT NULL THEN
                                  kk.namakotakabupaten
                                ELSE
                                    ''
                                END
                            ) || ' ' || (
                                CASE
                                WHEN prop.namapropinsi IS NOT NULL THEN
                                 'Prov. ' ||   prop.namapropinsi
                                ELSE
                                    ''
                                END
                            )
                        )
                    END AS alamatlengkap,
                    kps.kelompokpasien,pd.norec as norec_pd,pd.objectruanganlastfk
            FROM
                hasilpemeriksaanlab_t AS hpl
            INNER JOIN pasiendaftar_t AS pd ON pd.norec = hpl.noregistrasifk
            INNER JOIN pelayananpasien_t AS pp ON pp.norec = hpl.pelayananpasienfk
            LEFT JOIN strukorder_t AS so ON so.norec = pp.strukorderfk
            LEFT JOIN strukpelayanan_t AS sp ON sp.norec = pp.strukfk
            LEFT JOIN strukbuktipenerimaan_t AS sbm ON sbm.nostrukfk = pp.norec
            INNER JOIN produk_m AS pro ON pro. ID = pp.produkfk
            INNER JOIN pasien_m AS pm ON pm. ID = pd.nocmfk
            LEFT JOIN jeniskelamin_m AS jk ON jk. ID = pm.objectjeniskelaminfk
            LEFT JOIN pegawai_m AS pg ON pg. ID = so.objectpegawaiorderfk
            LEFT JOIN pegawai_m AS pg1 ON pg1. ID = hpl.pegawaifk
            LEFT JOIN pegawai_m AS dokterpengirim ON dokterpengirim. ID = hpl.dokterpengirimfk
             LEFT JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
            left join alamat_m as alm on alm.nocmfk=pm.id
            left join desakelurahan_m as ds on ds.id=alm.objectdesakelurahanfk
            left join kotakabupaten_m as kk on kk.id=alm.objectkotakabupatenfk
            left join kecamatan_m as kc on kc.id=alm.objectkecamatanfk
            left join propinsi_m as prop on prop.id=alm.objectpropinsifk
              left join kelompokpasien_m as kps on kps.id=pd.objectkelompokpasienlastfk
            WHERE
                pp.norec = '$r[norec]'
                and hpl.statusenabled=true
        "))->first();
//        dd($raw);
        if(!empty($raw)){
            $norec_pd = $raw->norec_pd;
            $objectruanganlastfk = $raw->objectruanganlastfk;
            $asalRujukan = collect(DB::select("select
                    asalrujukan from antrianpasiendiperiksa_t as apd 
                join asalrujukan_m as asl on asl.id=apd.objectasalrujukanfk
                where apd.noregistrasifk='$norec_pd'
                and apd.objectruanganfk=$objectruanganlastfk
                and apd.kdprofile=$kdProfile
                "))->first();
            $raw->asalrujukan ='';
            if(!empty( $asalRujukan )){
              $raw->asalrujukan=  $asalRujukan->asalrujukan;
            }

//            $raw->umur = $this->getAge($raw->tgllahir ,date('Y-m-d'));
        }else{
            echo 'Data Tidak ada ';
            return;
        }
//        dd($raw);
        $pageWidth = 950;

        return view('report.lab.hispatologi',
            compact('raw', 'pageWidth','r'));

    }

    
    function getDataLaporanPenerimaanSemuaKasirPDF(Request $request) {
        $kdProfile = $request['kdProfile'];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        
        $idKasir = '';
        $idRuangan = '';
        if (isset($request['idKasir']) && $request['idKasir'] != "" && $request['idKasir'] != "undefined") {
            $idKasir = 'AND p.id ='.$request['idKasir'];
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $idRuangan = 'AND p.id ='.$request['idRuangan'];
        }

        $data = \DB::select(DB::raw("
                    SELECT
                        p.namalengkap AS namapenerima,
                        sum(cast(sbm.totaldibayar AS float)) AS totalpenerimaan,
                        '' AS keterangan
                    FROM strukbuktipenerimaan_t AS sbm
                    INNER JOIN strukpelayanan_t AS sp ON sbm.nostrukfk = sp.norec
                    LEFT JOIN pasiendaftar_t AS pd ON sp.noregistrasifk = pd.norec
                    LEFT JOIN ruangan_m as ru ON ru.id=pd.objectruanganlastfk
                    LEFT JOIN loginuser_s AS lu ON lu.id = sbm.objectpegawaipenerimafk
                    LEFT JOIN pegawai_m AS p ON p.id = lu.objectpegawaifk
                    WHERE
                        sbm.kdprofile = $kdProfile
                        AND sbm.tglsbm >= '$tglAwal' 
                        AND sbm.tglsbm <= '$tglAkhir' 
                        $idKasir
                        $idRuangan
                    GROUP BY p.namalengkap"
                ));
            
            $totalsaldo = 0;
            foreach ($data as $d) {
                $totalsaldo += $d->totalpenerimaan;
            }
            $terbilang = $this->terbilang($totalsaldo);

            $pdf = PDF::loadView('report.pdf.LaporanPenerimaanSemuaKasir', array(
                        'data' => $data,
                        'terbilang' => $terbilang,
                        'tglAwal' => $tglAwal,
                        'tglAkhir' => $tglAkhir)
                    );

            return $pdf->download('LaporanPenerimaanSemuaKasir.pdf');
            // return view('report.pdf.LaporanPenerimaanSemuaKasir', compact('data','terbilang','tglAwal','tglAkhir'));
    }
    public function cetakResepDokter(Request $r) {
        $kdProfile = (int)$r['kdprofile'];
        $noorder = $r['noorder'];
        $norec = $r['norec'];
        $nocm = $r['nocm'];
        
        $raw = collect(DB::select("
            select pm2.nocm ,to_char(pm2.tgllahir,'dd-mm-yyyy') as tgllahir,age(pm2.tgllahir) as umur ,jm.jeniskelamin ,pm2.namapasien ,pm3.namalengkap, rm.namaruangan, 
            to_char(st.tglorder,'dd-mm-yyyy MM:ss') as tglorder,pm3.nosip, kp.kelompokpasien from strukorder_t st
            inner join pasien_m pm2 on pm2.id = st.nocmfk
            inner join jeniskelamin_m jm on jm.id = pm2.objectjeniskelaminfk
            inner join pegawai_m pm3 on pm3.id = st.objectpegawaiorderfk
            inner join ruangan_m rm on rm.id = st.objectruanganfk 
            inner join pasiendaftar_t AS pd ON pd.norec = st.noregistrasifk
            left join kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
            where st.noorder = '$noorder'
        "))->first();
        $detel = [];
        $details = \DB::select(DB::raw("select ot.rke,pm.namaproduk,ot.dosis,pm.kekuatan ,ot.jumlah , ot.aturanpakai from strukorder_t st 
            inner join orderpelayanan_t ot on ot.strukorderfk = st.norec 
            inner join produk_m pm on pm.id = ot.objectprodukfk 
            where st.noorder = '$noorder'"));
//        dd($raw);
        if(!empty($raw)){
            // $raw->umur = $this->getAge($raw->tgllahir ,date('Y-m-d'));
        }else if (empty($raw)){
            $raw = collect(DB::select("
                select pm2.nocm ,to_char(pm2.tgllahir,'dd-mm-yyyy') as tgllahir,age(pm2.tgllahir) as umur ,jm.jeniskelamin ,pm2.namapasien ,pm3.namalengkap, rm.namaruangan, to_char(s.tglresep ,'dd-mm-yyyy MM:ss') as tglorder,pm3.nosip, kp.kelompokpasien
                from strukresep_t s
                inner join antrianpasiendiperiksa_t at2 on at2.norec = s.pasienfk 
                inner join pasiendaftar_t pt on pt.norec = at2.noregistrasifk 
                inner join pasien_m pm2 on pm2.id = pt.nocmfk 
                inner join jeniskelamin_m jm on jm.id = pm2.objectjeniskelaminfk
                inner join pegawai_m pm3 on pm3.id = s.penulisresepfk 
                inner join ruangan_m rm on rm.id = s.ruanganfk 
                left join kelompokpasien_m AS kp ON kp.id = pt.objectkelompokpasienlastfk
                where s.norec = '$norec'
            "))->first();
            $details = \DB::select(DB::raw("
                select pt.rke,pt.dosis, pt.jumlah , pt.aturanpakai , pm.namaproduk, pm.kekuatan from pelayananpasien_t pt 
                inner join produk_m pm on pm.id = pt.produkfk 
                where strukresepfk = '$norec'
            "));
        }else{
            echo 'Data Tidak ada ';
            return;
        }
//        dd($raw);
        $pageWidth = 550;

        // return $r['norec'];

        return view('report.apotik.resepdokter',
            compact('raw', 'pageWidth','r','details'));

    }
}