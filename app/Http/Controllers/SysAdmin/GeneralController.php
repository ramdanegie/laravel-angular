<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 09/08/2019
 * Time: 13:36
 */
namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\ApiController;
use App\Master\Agama;
use App\Master\Evaluasi;
use App\Master\Implementasi;
use App\Master\Intervensi;
use App\Master\JenisKelamin;
use App\Master\Pendidikan;
use App\Master\StatusPerkawinan;
use App\Master\DiagnosaKeperawatan;
use App\Transaksi\MapLaporangKeuanganToLingkupPelayanan;
use App\Transaksi\MapRuanganToAdministrasi;
use App\Transaksi\MapRuanganToAkomodasi;
use App\Transaksi\PostingJurnal;
use App\Transaksi\PostingJurnalTransaksi;
use App\Transaksi\PostingJurnalTransaksiD;
use App\Transaksi\StrukPlanning;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;


use App\Transaksi\IdentifikasiPasien;
use App\Transaksi\PasienDaftar;

class GeneralController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function IdentifikasiSEP(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])->where('kdprofile', $kdProfile)->first();
        if($pasien == ''){
            $ip = new IdentifikasiPasien();
            $norec = $ip->generateNewId();
            $ip->norec = $norec;
            $ip->kdprofile = $kdProfile;
            $ip->statusenabled = true;
            $ip->noregistrasifk = $request['norec_pd'];
            $ip->issep = true;
            $ip->save();
        }else{
            $ip = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'issep' => true,
                ]);
        }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'CEPOT'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'CEPOT'
            );
        }
        return $this->respond($result);
    }
    public function getStatusPostingTgl(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tgl= $request['tgl'];
        $status=true;
        $noJurnalIntern = Carbon::parse($tgl)->format('ym').'PN'.Carbon::parse($tgl)->format('d');//.'00001';
        $data = \DB::table('postingjurnal_t')
            ->select('norec')
            ->where('kdprofile', $kdProfile)
            ->where('norecrelated','ilike',$noJurnalIntern . '%');
        $data = $data->first();

        if (count($data) == 0 ){
            $status=false;
        }

        $status=array(
            'status' => $status,
            'noJurnalIntern' => $noJurnalIntern,
        );

        return $this->respond($status);
    }
    public function getStatusClosePeriksa($noregistrasi, Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data  = PasienDaftar::where('noregistrasi',$noregistrasi)->where('kdprofile', $kdProfile)->first();
        $status = false;
        $tgl = null;
        if(!empty($data) && $data->isclosing != null){
            $status = $data->isclosing;
            $tgl = $data->tglclosing;
        }
        $result = array(
            'status'=> $status,
            'tglclosing'=> $tgl,
            'message' => 'ramdan@epic',
        );
        return $this->respond($result);
    }
    public function getPostingTgl(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
//        $data = DB::select(DB::raw("
//              select max(pjt.tglbuktitransaksi)   + interval '-30' day as max from postingjurnal_t as pj
//                INNER JOIN postingjurnaltransaksi_t as pjt on pjt.nojurnal_intern=pj.norecrelated
//                 where pj.norecrelated like '%PN%' and  RIGHT(pj.norecrelated,5) in ('00001','00002')
//          ")
//        );
        $data = DB::select(DB::raw("
           select now()  + interval '-26' day as max
              --   SELECT  DATEADD(month, -1, GETDATE()) AS max
          ")
        );
        $tgl = $data[0]->max;
        $tgl = $data[0]->max;
        $datadate =[];
        // $datadate = DB::select(DB::raw("

        //    --   select distinct DAY( pjt.tglbuktitransaksi) as tgl  from postingjurnal_t as pj
        //    --   INNER JOIN postingjurnaltransaksi_t as pjt on pjt.nojurnal_intern=pj.norecrelated
        //     --  where pj.kdprofile = $kdProfile and pj.norecrelated ilike '%PN%' and RIGHT(pj.norecrelated,5) in ('00001','00002') 
        //     --  and pjt.tglbuktitransaksi >  '$tgl';
        //    select distinct to_char(pjt.tglbuktitransaksi,'dd') as tgl   from postingjurnal_t as pj
        //       INNER JOIN postingjurnaltransaksi_t as pjt on pjt.nojurnal_intern=pj.norecrelated
        //        where pj.norecrelated ilike '%PN%' and  RIGHT(pj.norecrelated,5) in ('00001','00002') 
        //        and pjt.tglbuktitransaksi > '$tgl';
        //   ")
        // );
        // $datadate = DB::select(DB::raw("
        //       select distinct to_char(pjt.tglbuktitransaksi,'dd') as tgl     from postingjurnal_t as pj
        //         INNER JOIN postingjurnaltransaksi_t as pjt on pjt.nojurnal_intern=pj.norecrelated
        //         where pj.norecrelated like '%PN%' and  RIGHT(pj.norecrelated,5) in ('00001','00002')
        //         and pjt.tglbuktitransaksi > '$tgl';
        //   ")
        // );
        $arrtgl = [] ;
        foreach ($datadate as $item){
            $arrtgl[] = (int)$item->tgl;
        }

        $status=array(
            'mindate' => $data,
            'datedate' => $arrtgl,
            'by' =>'as@epic'
        );

        return $this->respond($status);
    }

    public function PostingHapusJurnal_Penerimaan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $isAktif = $this->settingDataFixed('PostingHapusJurnal_Penerimaan_isaktif', $kdProfile);
        if ($isAktif == 0){
            return;
        }
        $dataReq = $request->all();
        $nojurnal = $dataReq['nostruk'];
        try {
            if ($dataReq['nostruk'] != '-'){
                $delDetail = DB::select(DB::raw("
                    delete from postingjurnaltransaksid_t 
                    where kdprofile = $kdProfile and norecrelated in (select norec from postingjurnaltransaksi_t where nobuktitransaksi='$nojurnal');
                  ")
                );
                $delHead = DB::select(DB::raw("
                    delete from postingjurnaltransaksi_t where kdprofile = $kdProfile and nobuktitransaksi='$nojurnal'
                  ")
                );
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Hapus';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $delHead,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $delHead,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function PostingJurnal_terimabarang(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $isAktif = $this->settingDataFixed('PostingJurnal_terimabarang_isaktif', $kdProfile);
        if ($isAktif == 0){
            return;
        }
        $dataLogin = $request->all();
//        ini_set('max_execution_time', 1000); //6 minutes
        try {
            // TODO : Jurnal Penerimaa Barang Supplier
//            $delMacan = DB::select(DB::raw("
//                delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                INNER JOIN strukpelayanan_t as sp on sp.norec=pjt.norecrelated and sp.tglstruk >'2019-01-01 00:00'
//                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                where pjt.deskripsiproduktransaksi='penerimaan_barang' and sp.norec is null  and posted.nojurnal_intern is null
//                and pjt.tglbuktitransaksi  >'2019-01-01 00:00') ")
//            );
//            $delMacanHead = DB::select(DB::raw("
//                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                INNER JOIN strukpelayanan_t as sp on sp.norec=pjt.norecrelated and sp.tglstruk >'2019-01-01 00:00'
//                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                where pjt.deskripsiproduktransaksi='penerimaan_barang'  and sp.norec is null  and posted.nojurnal_intern is null
//                and pjt.tglbuktitransaksi  >'2019-01-01 00:00')")
//            );


            $aingMacan = DB::select(DB::raw("select sp.norec, sp.tglstruk, sp.nostruk,rkn.namarekanan,
                        ru.namaruangan,sp.nofaktur,sp.totalharusdibayar,to_char(sp.tglstruk, 'YYYY-MM-DD') as tgl
                        from strukpelayanan_t as sp
                        INNER JOIN rekanan_m as rkn on rkn.id=sp.objectrekananfk
                        INNER JOIN ruangan_m as ru on ru.id=sp.objectruanganfk
                        left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sp.norec
                        where sp.kdprofile = $kdProfile and sp.tglstruk between :tglAwal and :tglAkhir and
                        sp.objectkelompoktransaksifk=35 and pjt.norec is null and sp.statusenabled <> 'f'; 
                    "),
                array(
                    'tglAwal' => $request['tglAwal'],
                    'tglAkhir' => $request['tglAkhir'],
                )
            );

            foreach ($aingMacan as $item) {
//                $nocm = $item->nocm;
//                $nama = $item->namapasien;
                $totalRp = $item->totalharusdibayar;

                $noBuktiTransaksi = $item->nostruk;
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'KS-' . $this->getDateTime()->format('ym'));
//                $norec_smbc = $item->norec;

//                $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                $newPJT = new PostingJurnalTransaksi;
                $norecHead = $newPJT->generateNewId();
                $newPJT->norec = $norecHead;

                $noJurnalIntern = Carbon::parse($item->tglstruk)->format('ym') . 'RS' . Carbon::parse($item->tglstruk)->format('d') . '00001';
                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->where('kdprofile', $kdProfile)->get();

                if (count($cekSudahPosting) == 0) {
                    $newPJT->kdprofile = $kdProfile;
                    $newPJT->noposting = $noPosting;
                    $newPJT->nojurnal = 0;//$nojurnal;
                    $newPJT->nojurnal_intern = $noJurnalIntern;
                    $newPJT->objectjenisjurnalfk = 1;
                    $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                    $newPJT->tglbuktitransaksi = $item->tglstruk;// $this->getDateTime()->format('Y-m-d H:i:s');
                    $newPJT->kdproduk = null;
                    $newPJT->namaproduktransaksi = 'Penerimaan Barang ' . $item->nostruk . ' ' . $item->namarekanan;
                    $newPJT->deskripsiproduktransaksi = 'penerimaan_barang';
                    $newPJT->keteranganlainnya = 'Penerimaan Barang ' . $item->tgl;;
                    $newPJT->statusenabled = true;
                    $newPJT->norecrelated = $item->norec;
                    $newPJT->save();

                    $debetId = 1853;
                    $kreditId = 1791;

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = true;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = true;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                }
            }
            
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Posting";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
//                "data" => $aingMacan,
                "count" => count($aingMacan),
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "message"  => $transMessage,
                // "data" => $aingMacan,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataPegawaiGeneral(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk=[];
        $dataProduk  = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap')
            ->where('st.kdprofile', $kdProfile)
            ->where('st.statusenabled',true)
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('st.namalengkap','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        foreach ($dataProduk as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }

        return $this->respond($dataPenulis2);
    }

    public function getComboAddressGeneral(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $kebangsaan = DB::table('kebangsaan_m')
            ->select(DB::raw("id, UPPER(name) as name"))
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->get();

        $negara = DB::table('negara_m')
            ->select(DB::raw("id, UPPER(namanegara) as namanegara"))
            ->where('kdprofile', $kdProfile)
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

    public function getDesaKelurahanGeneral(Request $request){
        $req = $request->all();
        $Desa = \DB::table('desakelurahan_m as ds')
            ->join('kecamatan_m as kc','ds.objectkecamatanfk','=','kc.id')
            ->join('kotakabupaten_m as kk','ds.objectkotakabupatenfk','=','kk.id')
            ->join('propinsi_m as pp','ds.objectpropinsifk','=','pp.id')
            ->select(DB::raw("ds.id,UPPER(ds.namadesakelurahan) as namadesakelurahan,ds.kodepos,
			                 ds.objectkecamatanfk,ds.objectkotakabupatenfk,ds.objectpropinsifk,
				             kc.namakecamatan,kk.namakotakabupaten,pp.namapropinsi"))
            ->where('ds.statusenabled', true)
            ->orderBy('ds.namadesakelurahan');

        if(isset($req['namadesakelurahan']) &&
            $req['namadesakelurahan']!="" &&
            $req['namadesakelurahan']!="undefined"){
            $Desa = $Desa->where('ds.namadesakelurahan','ilike','%'. $req['namadesakelurahan'] .'%' );
        };
        if(isset($req['iddesakelurahan']) &&
            $req['iddesakelurahan']!="" &&
            $req['iddesakelurahan']!="undefined"){
            $Desa = $Desa->where('ds.id', $req['iddesakelurahan'] );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $Desa = $Desa
                ->where('ds.namadesakelurahan','ilike','%'.$req['filter']['filters'][0]['value'].'%' )
                ->Orwhere('kc.namakecamatan','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }

        $Desa = $Desa->take(10);
        $Desa = $Desa->get();
        $tempDesa = [];
        if(count($Desa) != 0){
            foreach ($Desa as $item){
                $tempDesa [] = array(
                    'id' => $item->id,
                    'namadesakelurahan' => $item->namadesakelurahan,
                    'kodepos' => $item->kodepos,
                    'namakecamatan' => $item->namakecamatan,
                    'namakotakabupaten' => $item->namakotakabupaten,
                    'namapropinsi' => $item->namapropinsi,
                    'desa' => $item->namadesakelurahan .', '. $item->namakecamatan .',  '. $item->namakotakabupaten .', '.
                        $item->namapropinsi ,
                    'objectkecamatanfk' => $item->objectkecamatanfk,
                    'objectkotakabupatenfk' => $item->objectkotakabupatenfk,
                    'objectpropinsifk' => $item->objectpropinsifk,
                );
            }
        }
        return $this->respond($tempDesa);
    }

    public function getAlamatByKodePosGeneral(Request $request){
        $dataLogin = $request->all();
        $data = \DB::table('desakelurahan_m as dk')
            ->Join('kecamatan_m as kcm','kcm.id','=','dk.objectkecamatanfk')
            ->Join('kotakabupaten_m as kk','kk.id','=','dk.objectkotakabupatenfk')
            ->Join('propinsi_m as pp','pp.id','=','dk.objectpropinsifk')
            ->select(DB::raw("dk.id,dk.id as objectdesakelurahanfk,UPPER(dk.namadesakelurahan) as namadesakelurahan,dk.kodepos,
			                 dk.objectkecamatanfk,dk.objectkotakabupatenfk,dk.objectpropinsifk,
				             UPPER(kcm.namakecamatan) as namakecamatan,UPPER(kk.namakotakabupaten) as namakotakabupaten,
				             UPPER(pp.namapropinsi) as namapropinsi"))
            ->where('dk.statusenabled', true)
            ->where('dk.kodepos', $request['kodePos'])
            ->get();

        $result = array(
            'data' => $data,
            'datalogin' => $dataLogin,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function PostingJurnal_amprahanForDaftar(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $isAktif = $this->settingDataFixed('PostingJurnal_amprahanForDaftar_isaktif', $kdProfile);
        if ($isAktif == 0){
            return;
        }
        $dataLogin = $request->all();
        try {
            return;
            // TODO : Jurnal Amprahan Ruangan Dri Daftar
//            $delMacan = DB::select(DB::raw("
//                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                INNER JOIN strukkirim_t as sp on sp.norec=pjt.norecrelated  and sp.tglkirim >'2019-01-01 00:00'
//                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                where pjt.deskripsiproduktransaksi='amprahan_barang_ruangan' and sp.norec is null  and posted.nojurnal_intern is null
//                and pjt.tglbuktitransaksi  >'2019-01-01 00:00') ")
//            );
//            $delMacanHead = DB::select(DB::raw("
//                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                INNER JOIN strukkirim_t as sp on sp.norec=pjt.norecrelated  and sp.tglkirim >'2019-01-01 00:00'
//                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                where pjt.deskripsiproduktransaksi='amprahan_barang_ruangan'  and sp.norec is null  and posted.nojurnal_intern is null
//                and pjt.tglbuktitransaksi  >'2019-01-01 00:00')")
//            );

            $aingMacan = DB::select(DB::raw("select xx.norec,xx.tglkirim,xx.nokirim,xx.ruanganid,xx.ruanganasal,xx.ruangantujuanid,xx.ruangantujuan,xx.jenispermintaanfk,xx.tgl,SUM(xx.total) as total from
                    (select x.norec,x.tglkirim,x.nokirim,x.ruanganid,x.ruanganasal,x.ruangantujuanid,x.ruangantujuan,x.tgl,x.jenispermintaanfk,
                     x.objectprodukfk,x.qtyproduk*x.hargasatuan as total from
                    (SELECT sk.norec,sk.tglkirim,sk.nokirim,ru.id as ruanganid,ru.namaruangan as ruanganasal,ru1.id as ruangantujuanid,
                     ru1.namaruangan as ruangantujuan,format(sk.tglkirim, 'YYYY-MM-DD') as tgl,kp.objectprodukfk,kp.qtyproduk,kp.hargasatuan,
                     sk.jenispermintaanfk
                    FROM strukkirim_t as sk 
                    INNER JOIN kirimproduk_t as kp on kp.nokirimfk = sk.norec
                    INNER JOIN ruangan_m as ru on ru.id = sk.objectruanganasalfk
                    INNER JOIN ruangan_m as ru1 on ru1.id = sk.objectruangantujuanfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sk.norec
                    where sk.kdprofile = $kdProfile and sk.tglkirim between :tglAwal and :tglAkhir and
                    sk.objectkelompoktransaksifk=34 and pjt.norec is null and sk.statusenabled <> 0 
                    and sk.jenispermintaanfk =1
                    GROUP BY sk.norec,sk.tglkirim,sk.nokirim,ru.id,ru.namaruangan,ru1.id,ru1.namaruangan,kp.qtyproduk,kp.hargasatuan,kp.objectprodukfk,sk.jenispermintaanfk) as x) as xx
                    GROUP BY xx.norec,xx.tglkirim,xx.nokirim,xx.ruanganid,xx.ruanganasal,xx.ruangantujuanid,xx.ruangantujuan,xx.tgl,xx.jenispermintaanfk;"),
                array(
                    'tglAwal' => $request['tglAwal'],
                    'tglAkhir' => $request['tglAkhir'],
                )
            );

            $dataCoa = DB::select(DB::raw("select ru.id as ruid, ru.namaruangan,coa.namaaccount,coa.kdaccount,coa.id as coaid
                            from chartofaccount_m as coa 
                            left JOIN ruangan_m as ru on coa.namaaccount ilike '%' + ru.namaruangan + ''
                            where coa.kdprofile = $kdProfile and coa.namaexternal='2018-03-01' and coa.namaaccount ilike 'Biaya Obat%' and ru.namaruangan <>'-' and ru.statusenabled=1;")
            );

            $debetId = '';//1791;//1853;
            $kreditId = 1791;
            foreach ($aingMacan as $item) {
                $ruanganId =$item->ruangantujuanid;
//                return $this->respond($dataCoa) ;
                $debetId = '';
                foreach ($dataCoa as $coa){
                    if ($coa->ruid == $item->ruangantujuanid){
                        $debetId = $coa->coaid;
                        break;
                    }else {
                        $debetId = 2363;
                    }
                }

                $totalRp = $item->total;

                $noBuktiTransaksi = $item->nokirim;
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'KS-' . $this->getDateTime()->format('ym'));
//                $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                $newPJT = new PostingJurnalTransaksi;
                $norecHead = $newPJT->generateNewId();
                $newPJT->norec = $norecHead;
                $noJurnalIntern = Carbon::parse($item->tglkirim)->format('ym') . 'AMP-' . Carbon::parse($item->tglkirim)->format('d') . '00001';
                $namaproduktransaksi ='Amprahan barang ruangan dengan nokirim ' . $item->nokirim . '  ,dari ruangan ' . $item->ruanganasal .' ,keruangan '.$item->ruangantujuan ;
                $deskripsiproduktransaksi='amprahan_barang_ruangan';
                $keteranganlainnya = 'Amprahan barang ruangan ' . $item->tgl;

                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->get();
//                    if ($item->jenispermintaanfk == 1){
                if (count($cekSudahPosting) == 0) {
                    $newPJT->kdprofile = $kdProfile;
                    $newPJT->noposting = $noPosting;
                    $newPJT->nojurnal = 1;//$nojurnal;
                    $newPJT->nojurnal_intern = $noJurnalIntern;
                    $newPJT->objectjenisjurnalfk = 1;
                    $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                    $newPJT->tglbuktitransaksi = $item->tglkirim;// $this->getDateTime()->format('Y-m-d H:i:s');
                    $newPJT->kdproduk = null;
                    $newPJT->namaproduktransaksi = $namaproduktransaksi;//'Amprahan barang ruangan dengan nokirim ' . $item->nokirim . '  ,dari ruangan ' . $item->ruanganasal .' ,keruangan '.$item->ruangantujuan ;
                    $newPJT->deskripsiproduktransaksi =$deskripsiproduktransaksi;// 'amprahan_barang_ruangan';
                    $newPJT->keteranganlainnya = $keteranganlainnya;//'Amprahan barang ruangan ' . $item->tgl;
                    $newPJT->statusenabled = 1;
                    $newPJT->norecrelated = $item->norec;
                    $newPJT->save();

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                }
//                    }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Posting";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $aingMacan,
                "count" => count($aingMacan),
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $aingMacan,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function PostingHapusJurnal_BatalKirim(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $isAktif = $this->settingDataFixed('PostingHapusJurnal_BatalKirim_isaktif', $kdProfile);
        if ($isAktif == 0){
            return;
        }
        $dataReq = $request->all();
        $nojurnal = $dataReq['strukkirim']['nokrim'];
        try {
            if ($dataReq['strukkirim'] != '-'){
                $delDetail = DB::select(DB::raw("
                    delete from postingjurnaltransaksid_t 
                    where kdprofile = $kdProfile and norecrelated in (select norec from postingjurnaltransaksi_t where nobuktitransaksi='$nojurnal');
                  ")
                );
                $delHead = DB::select(DB::raw("
                    delete from postingjurnaltransaksi_t where kdprofile = $kdProfile and nobuktitransaksi='$nojurnal'
                  ")
                );
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Hapus';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $delHead,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $delHead,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function UpdatePostingJurnal_BatalKirimPerItem(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataReq = $request->all();
        $nojurnal = $dataReq['strukkirim']['noreckirim'];
        $aingMacan = DB::select(DB::raw("select xx.norecJurnal,xx.norec,xx.tglkirim,xx.nokirim,xx.ruanganid,xx.ruanganasal,xx.ruangantujuanid,xx.ruangantujuan,xx.tgl,SUM(xx.total) as total from
                        (select x.norecJurnal,x.norec,x.tglkirim,x.nokirim,x.ruanganid,x.ruanganasal,x.ruangantujuanid,x.ruangantujuan,x.tgl,
                         x.objectprodukfk,x.qtyproduk*x.hargasatuan as total from
                        (SELECT pjt.norec as norecJurnal,sk.norec,sk.tglkirim,sk.nokirim,ru.id as ruanganid,ru.namaruangan as ruanganasal,ru1.id as ruangantujuanid,
                         ru1.namaruangan as ruangantujuan,to_char(sk.tglkirim, 'YYYY-MM-DD') as tgl,kp.objectprodukfk,kp.qtyproduk,kp.hargasatuan
                        FROM strukkirim_t as sk 
                        INNER JOIN kirimproduk_t as kp on kp.nokirimfk = sk.norec
                        INNER JOIN ruangan_m as ru on ru.id = sk.objectruanganasalfk
                        INNER JOIN ruangan_m as ru1 on ru1.id = sk.objectruangantujuanfk
                        left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sk.norec
                        where sk.kdprofile = $kdProfile and 
                        --sk.tglkirim between :tglAwal and :tglAkhir and
                        sk.objectkelompoktransaksifk=34 and sk.statusenabled <> 'f' and sk.norec = :norec
                        --sk.jenispermintaanfk =1
                        GROUP BY pjt.norec,sk.norec,sk.tglkirim,sk.nokirim,ru.id,ru.namaruangan,ru1.id,ru1.namaruangan,kp.qtyproduk,kp.hargasatuan,kp.objectprodukfk) as x) as xx
                        GROUP BY xx.norecJurnal,xx.norec,xx.tglkirim,xx.nokirim,xx.ruanganid,xx.ruanganasal,xx.ruangantujuanid,xx.ruangantujuan,xx.tgl;"),
            array(
                //                        'tglAwal' => $request['tglAwal'],
                //                        'tglAkhir' => $request['tglAkhir'],
                'norec' => $nojurnal
            ));
        try {
            foreach ($aingMacan as $item) {
                $norecrelated=$item->norec;
                if ($item != null){
                    $updateDebet = DB::select(DB::raw("UPDATE postingjurnaltransaksid_t SET
                                         hargasatuand = $item->total
                                         where kdprofile = $kdProfile and norecrelated in (select norec from postingjurnaltransaksi_t where norecrelated='$norecrelated')
                                         and hargasatuank=0")
                    );

                    $updateKredit = DB::select(DB::raw("UPDATE postingjurnaltransaksid_t SET
                                         hargasatuank = $item->total
                                         where kdprofile = $kdProfile and norecrelated in (select norec from postingjurnaltransaksi_t where kdprofile = $kdProfile and norecrelated='$norecrelated')
                                         and hargasatuand=0")
                    );
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Update';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $aingMacan,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $aingMacan,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function viewBed(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdRanapIcu = explode(',', $this->settingDataFixed('KdDeptRanapICU', $kdProfile));
        $listKdRanapIcu = [];
        foreach ($KdRanapIcu as $item){
            $listKdRanapIcu [] = (int)$item;
        }
        $data= \DB::table('tempattidur_m as tt')
            ->leftjoin('kamar_m as km', 'km.id', '=', 'tt.objectkamarfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'km.objectruanganfk')
            ->leftjoin('statusbed_m as sb', 'sb.id', '=', 'tt.objectstatusbedfk')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'km.objectkelasfk')
            ->select('ru.id as idruangan','ru.namaruangan','km.id as idkamar','km.namakamar','tt.id as idtempattidur',
                'tt.reportdisplay','tt.nomorbed','sb.id as idstatusbed','sb.statusbed','kl.id as idkelas','kl.namakelas')
            ->where('tt.kdprofile', $kdProfile)
            ->whereIn('ru.objectdepartemenfk',$listKdRanapIcu)
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
    public function getVerifikasiNoregistrasiGeneral(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdRuanganIGD = explode(',', $this->settingDataFixed('KdRuanganIGD', $kdProfile));
        $kdListKdRuanganIGD = [];
        foreach ($KdRuanganIGD as $item){
            $kdListKdRuanganIGD [] = (int)$item;
        }
        $status=true;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->join('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select('sp.nostruk')
//            ->where('ru.objectdepartemenfk','=',16)
            ->where('pd.kdprofile', $kdProfile)
            ->whereNotIn('apd.objectruanganfk',$kdListKdRuanganIGD);

        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi', $request['noregistrasi']);
        }
        if(isset($request['norec_pd']) && $request['norec_pd']!="" && $request['norec_pd']!="undefined"){
            $data = $data->where('pd.noregistrasi', $request['norec_pd']);
        }
        $data = $data->get();

        if (count($data) == 0 ){
            $status=false;
        }

        $status=array(
            'status' => $status,
        );

        return $this->respond($status);
    }

    public function DeletePenerimaanSuplier(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            StrukPelayanan::where('norec', $request['norec_sp'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'statusenabled' => 'f']
                );
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "gagal Pelayanan Pasien";
        }
        #KartuStok


        if ($transStatus == 'true') {
            $transMessage = "Hapus Pelayanan OB Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Hapus Pelayanan OB Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataComboRuanganGeneral(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk  = \DB::table('ruangan_m')
            ->select('id as value','namaruangan as text','namaruangan')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('namaruangan');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('namaruangan','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataComboRekananGeneral(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk  = \DB::table('rekanan_m')
            ->select('id','namarekanan')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('namarekanan');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('namarekanan','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function hapusJurnalpembayaranTagihanNoBatch(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataReq = $request->all();
        $noSbm = $dataReq['nosbm'];
        try {
            $delDetail = DB::select(DB::raw("
                   delete  from postingjurnaltransaksid_t 
                        where norecrelated in 
                        (select pjt.norec 
                        from postingjurnaltransaksi_t as pjt 
                        INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.norec=pjt.norecrelated
                        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk
                        left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                        where pjt.kdprofile = $kdProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' 
                        and posted.nojurnal_intern is null
                        and sbm.nosbm ='$noSbm')
                  ")
            );
            $delHead = DB::select(DB::raw("
                   delete  from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt 
                        INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.norec=pjt.norecrelated
                        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk
                        left JOIN loginuser_s as lu on lu.id=sbm.objectpegawaipenerimafk
                        left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                        where pjt.kdprofile = $kdProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' 
                        and posted.nojurnal_intern is null
                        and sbm.nosbm='$noSbm');

                  ")
            );


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Hapus Posting';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $delHead,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $delHead,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function PostingJurnal_strukpelayanan_t_verifikasi_tarek(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $isAktif = $this->settingDataFixed('PostingJurnal_strukpelayanan_t_verifikasi_tarek_isaktif', $kdProfile);
        if ($isAktif == 0){
            return;
        }
        $dataLogin = $request->all();
//        ini_set('max_execution_time', 1000); //6 minutes

        try {

            // TODO : Jurnal Piutang Verifikasi Tarek
//            $dataCoaDiskon = DB::select(DB::raw("select ru.id as ruid, ru.namaruangan,coa.namaaccount,coa.kdaccount,coa.id as coaid
//                    from chartofaccount_m as coa
//                    left JOIN ruangan_m as ru on coa.namaaccount like '%' || ru.namaruangan || ''
//                    JOIN suratkeputusan_m as sk on sk.id=coa.suratkeputusanfk
//                    where sk.statusenabled=1 and coa.namaaccount like 'Biaya  Subsidi Fasilitas %' and ru.namaruangan <>'-';")
//            );

            //DELETE JIKA DI TABEL TRANSAKSI SUDAH TIDAK ADA
//            $delMacan = DB::select(DB::raw("
//                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                    INNER JOIN  strukpelayanan_t as pp on pp.norec=pjt.norecrelated and pp.tglstruk >'2019-01-01 00:00'
//                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                    where pjt.deskripsiproduktransaksi='verifikasi_tarek' and pp.statusenabled='f' and posted.nojurnal_intern is null
//                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00');")
//            );
//            $delMacanHead = DB::select(DB::raw("
//                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                    INNER JOIN  strukpelayanan_t as pp on pp.norec=pjt.norecrelated and pp.tglstruk >'2019-01-01 00:00'
//                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                    where pjt.deskripsiproduktransaksi='verifikasi_tarek' and pp.statusenabled='f' and posted.nojurnal_intern is null
//                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00');")
//            );
            $aingMacan=[];
            $aingMacan = DB::select(DB::raw("select distinct sp.norec,sp.nostruk,pd.noregistrasi,ps.namapasien,sp.tglstruk as tglstruk,sp.totalharusdibayar,sp.totalprekanan,
                      kp.id as kpid,ru.objectdepartemenfk,ru.objectdepartemenfk as dept_pd,to_char(sp.tglstruk, 'YYYY-MM-DD') as tgl,pd.objectruanganlastfk,pd.objectrekananfk,
                      ru.namaruangan,rkn.namarekanan
                    from pelayananpasien_t as pp
                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                    INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                    INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                    INNER JOIN ruangan_m as ru on ru.id=pd.objectruanganlastfk
                    INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                    INNER JOIN kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk
                    left JOIN rekanan_m as rkn on rkn.id=pd.objectrekananfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sp.norec
                    where pp.kdprofile = $kdProfile and sp.tglstruk BETWEEN :tglAwal and :tglAkhir and pjt.norec is null --and pd.tglpulang is not null --and sp.totalprekanan >0
                    order by sp.norec "),
                array(
                    'tglAwal' => $request['tglAwal'],
                    'tglAkhir' => $request['tglAkhir'],
                )
            );
//            return $this->respond($aingMacan);
            $coaruangandiskon=[];
            foreach ($aingMacan as $item) {
                $noReg = $item->noregistrasi;
                $namaPasien = $item->namapasien;
//                $deptId =$item->dept_apd;
//                $deptId =$item->dept_pd;

                $noBuktiTransaksi = $item->nostruk;
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'NT-' . $this->getDateTime()->format('ym'));
//                $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

//                if ($deptId == 16) {
//                    $noJurnalIntern = Carbon::parse($item->tglstruk)->format('ym') . 'PN' . Carbon::parse($item->tglstruk)->format('d') . '00004';
//                } else {
                $noJurnalIntern = Carbon::parse($item->tglstruk)->format('ym') . 'PN' . Carbon::parse($item->tglstruk)->format('d') . '00002';
//                }

                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->where('kdprofile', $kdProfile)->get();
                if ($this->getCountArray($cekSudahPosting) == 0) {
                    $postingJurnalTransaksi = new PostingJurnalTransaksi;
                    $norecHead = $postingJurnalTransaksi->generateNewId();
                    $postingJurnalTransaksi->norec = $norecHead;
                    $postingJurnalTransaksi->kdprofile = $kdProfile;
                    $postingJurnalTransaksi->noposting = $noPosting;
                    $postingJurnalTransaksi->nojurnal = 0;
                    $postingJurnalTransaksi->objectjenisjurnalfk = 1;
                    $postingJurnalTransaksi->nobuktitransaksi = $noBuktiTransaksi;
                    $postingJurnalTransaksi->tglbuktitransaksi = $item->tglstruk;/// $this->getDateTime()->format('Y-m-d H:i:s');
                    $postingJurnalTransaksi->kdproduk = null;
                    $postingJurnalTransaksi->namaproduktransaksi = 'Verifikasi tagihan ' . $noReg . ', ' . $namaPasien . ' di TataRekening';
                    $postingJurnalTransaksi->deskripsiproduktransaksi = 'verifikasi_tarek';
//                    if ($deptId == 16) {
//                        $postingJurnalTransaksi->keteranganlainnya = 'Pendapatan RI Non Tunai Tgl. ' . $item->tgl;
//                        $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
//                    } else {
                    $postingJurnalTransaksi->keteranganlainnya = 'Verifikasi Tagihan Tgl. ' . $item->tgl;
                    $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
//                    }
                    $postingJurnalTransaksi->statusenabled = 1;
                    $postingJurnalTransaksi->norecrelated = $item->norec;
                    $postingJurnalTransaksi->save();

                    $norec_pj = $postingJurnalTransaksi->norec;

                    $totalRp = $item->totalharusdibayar;
                    $totalRekananRp = $item->totalprekanan;




                    if ($totalRekananRp > 0) {
                        $kreditId = 1778;
                        $debetId = 11543;
//                        if ($item->kpid == 1){//1	Umum/Pribadi
//                            $debetId = 10896;
//                        }
//                        if ($item->kpid == 2) {//BPJS
//                            $debetId = 10897;//PIUTANG BPJS
//                        }
//                        if ($item->kpid == 3){//3	Asuransi lain
//                            $debetId = 10901;
//                        }
//                        if ($item->kpid == 4) {//BPJS non PBI
//                            $debetId = 10897;//PIUTANG BPJS
//                        }
//                        if ($item->kpid == 5){//5	Perusahaan
//                            $debetId = 10901;
//                        }
//                        if ($item->kpid == 6){//6	Perjanjian
//                            $debetId = 10896;
//                        }
//                        if ($item->kpid == 7){//7	Dinas Sosial
//                            $debetId = 10900;
//                        }

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $debetId;
                        $postingJurnalTransaksiD->hargasatuand = $totalRekananRp;
                        $postingJurnalTransaksiD->hargasatuank = 0;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();

                        //kredit
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                        $postingJurnalTransaksiD->hargasatuand = 0;
                        $postingJurnalTransaksiD->hargasatuank = $totalRekananRp;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();
                    }
                    if ($totalRp > 0) {
                        $kreditId2 = 1778;
                        $debetId2 = 11543;
//                        if ($item->kpid == 1){//1	Umum/Pribadi
//                            $debetId2 = 10896;
//                        }
//                        if ($item->kpid == 2) {//BPJS
//                            $debetId2 = 10897;//PIUTANG BPJS
//                        }
//                        if ($item->kpid == 3){//3	Asuransi lain
//                            $debetId2 = 10901;
//                        }
//                        if ($item->kpid == 4) {//BPJS non PBI
//                            $debetId2 = 10897;//PIUTANG BPJS
//                        }
//                        if ($item->kpid == 5){//5	Perusahaan
//                            $debetId2 = 10901;
//                        }
//                        if ($item->kpid == 6){//6	Perjanjian
//                            $debetId2 = 10896;
//                        }
//                        if ($item->kpid == 7){//7	Dinas Sosial
//                            $debetId2 = 10900;
//                        }

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $debetId2;
                        $postingJurnalTransaksiD->hargasatuand = $totalRp;
                        $postingJurnalTransaksiD->hargasatuank = 0;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();

                        //kredit
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $kreditId2;
                        $postingJurnalTransaksiD->hargasatuand = 0;
                        $postingJurnalTransaksiD->hargasatuank = $totalRp;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();
                    }
                }else{
                    $noJurnalIntern = Carbon::parse($item->tglstruk)->format('ym') . 'AJ' . Carbon::parse($item->tglstruk)->format('d') . '00002';
                    $postingJurnalTransaksi = new PostingJurnalTransaksi;
                    $norecHead = $postingJurnalTransaksi->generateNewId();
                    $postingJurnalTransaksi->norec = $norecHead;
                    $postingJurnalTransaksi->kdprofile = $kdProfile;
                    $postingJurnalTransaksi->noposting = $noPosting;
                    $postingJurnalTransaksi->nojurnal = 0;
                    $postingJurnalTransaksi->objectjenisjurnalfk = 1;
                    $postingJurnalTransaksi->nobuktitransaksi = $noBuktiTransaksi;
                    $postingJurnalTransaksi->tglbuktitransaksi = date('Y-m-t', strtotime($item->tglstruk));//$item->tglstruk;/// $this->getDateTime()->format('Y-m-d H:i:s');
                    $postingJurnalTransaksi->kdproduk = null;
                    $postingJurnalTransaksi->namaproduktransaksi = 'Adjustment Verifikasi tagihan ' . $noReg . ', ' . $namaPasien . ' di TataRekening';
                    $postingJurnalTransaksi->deskripsiproduktransaksi = 'verifikasi_tarek';
//                    if ($deptId == 16) {
//                        $postingJurnalTransaksi->keteranganlainnya = 'Adjustment Pendapatan RI Non Tunai Tgl. ' . $item->tgl;
//                        $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
//                    } else {
                    $postingJurnalTransaksi->keteranganlainnya = 'Adjustment Verifikasi Tagihan Tgl. ' . $item->tgl;
                    $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
//                    }
                    $postingJurnalTransaksi->statusenabled = 1;
                    $postingJurnalTransaksi->norecrelated = $item->norec;
                    $postingJurnalTransaksi->save();

                    $norec_pj = $postingJurnalTransaksi->norec;

                    $totalRp = $item->totalharusdibayar;
                    $totalRekananRp = $item->totalprekanan;




                    if ($totalRekananRp > 0) {
                        $kreditId = 1778;
                        $debetId = 11543;
//                        if ($item->kpid == 1){//1	Umum/Pribadi
//                            $debetId = 10896;
//                        }
//                        if ($item->kpid == 2) {//BPJS
//                            $debetId = 10897;//PIUTANG BPJS
//                        }
//                        if ($item->kpid == 3){//3	Asuransi lain
//                            $debetId = 10901;
//                        }
//                        if ($item->kpid == 4) {//BPJS non PBI
//                            $debetId = 10897;//PIUTANG BPJS
//                        }
//                        if ($item->kpid == 5){//5	Perusahaan
//                            $debetId = 10901;
//                        }
//                        if ($item->kpid == 6){//6	Perjanjian
//                            $debetId = 10896;
//                        }
//                        if ($item->kpid == 7){//7	Dinas Sosial
//                            $debetId = 10900;
//                        }

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $debetId;
                        $postingJurnalTransaksiD->hargasatuand = $totalRekananRp;
                        $postingJurnalTransaksiD->hargasatuank = 0;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();

                        //kredit
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                        $postingJurnalTransaksiD->hargasatuand = 0;
                        $postingJurnalTransaksiD->hargasatuank = $totalRekananRp;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();
                    }
                    if ($totalRp > 0) {
                        $kreditId = 1778;
                        $debetId = 11543;
//                        if ($item->kpid == 1){//1	Umum/Pribadi
//                            $debetId = 10896;
//                        }
//                        if ($item->kpid == 2) {//BPJS
//                            $debetId = 10897;//PIUTANG BPJS
//                        }
//                        if ($item->kpid == 3){//3	Asuransi lain
//                            $debetId = 10901;
//                        }
//                        if ($item->kpid == 4) {//BPJS non PBI
//                            $debetId = 10897;//PIUTANG BPJS
//                        }
//                        if ($item->kpid == 5){//5	Perusahaan
//                            $debetId = 10901;
//                        }
//                        if ($item->kpid == 6){//6	Perjanjian
//                            $debetId = 10896;
//                        }
//                        if ($item->kpid == 7){//7	Dinas Sosial
//                            $debetId = 10900;
//                        }

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $debetId;
                        $postingJurnalTransaksiD->hargasatuand = $totalRp;
                        $postingJurnalTransaksiD->hargasatuank = 0;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();

                        //kredit
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                        $postingJurnalTransaksiD->hargasatuand = 0;
                        $postingJurnalTransaksiD->hargasatuank = $totalRp;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();
                    }
                }
            }

            //############################################################################################################################################
            //##################################################### DEPOSIT ##############################################################################
            //############################################################################################################################################
            // TODO : Jurnal Deposit
            //DELETE JIKA DI TABEL TRANSAKSI SUDAH TIDAK ADA
            if (1 == 1 ) {
//                $delMacan = DB::select(DB::raw("
//                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                    INNER JOIN  strukbuktipenerimaan_t as pp on pp.norec=pjt.norecrelated and pp.tglsbm >'2019-01-01 00:00'
//                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                    where pjt.deskripsiproduktransaksi='penerimaan_deposit' and pp.norec is null and posted.nojurnal_intern is null
//                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00');")
//                );
//                $delMacanHead = DB::select(DB::raw("
//                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                    INNER JOIN  strukbuktipenerimaan_t as pp on pp.norec=pjt.norecrelated and pp.tglsbm >'2019-01-01 00:00'
//                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                    where pjt.deskripsiproduktransaksi='penerimaan_deposit' and pp.norec is null and posted.nojurnal_intern is null
//                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00');")
//                );

                $aingMaung = [];
                $aingMaung = DB::select(DB::raw("select ps.nocm,ps.namapasien,sbm.nosbm,sbmc.totaldibayar,sbm.tglsbm,sbmc.objectcarabayarfk ,
                    sbmc.norec as norec_smbc,to_char(sbm.tglsbm, 'YYYY-MM-DD') as tgl
                    from strukbuktipenerimaancarabayar_t as sbmc
                    INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk
                    left JOIN strukpelayanan_t as sp on sp.norec=sbm.nostrukfk
                    left JOIN pasien_m as ps on ps.id=sp.nocmfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sbmc.norec
                    where sbmc.kdprofile = $kdProfile and sbm.tglsbm BETWEEN :tglAwal and :tglAkhir and pjt.norec is null
                    and sbm.keteranganlainnya = 'Pembayaran Deposit Pasien'"),
                    array(
                        'tglAwal' => $request['tglAwal'],
                        'tglAkhir' => $request['tglAkhir'],
                    )
                );

                foreach ($aingMaung as $item) {
                    $nocm = $item->nocm;
                    $nama = $item->namapasien;
                    $noBuktiTransaksi = $item->nosbm;
                    $totalRp = $item->totaldibayar;
                    $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'NT-' . $this->getDateTime()->format('ym'));
//                    $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                    $noJurnalIntern = Carbon::parse($item->tglsbm)->format('ym') . 'PN' . Carbon::parse($item->tglsbm)->format('d') . '00003';
                    $cekSudahPosting = PostingJurnal::where('norecrelated', $noJurnalIntern)->where('kdprofile', $kdProfile)->get();

                    if ($this->getCountArray($cekSudahPosting) == 0) {

                        $newPJT = new PostingJurnalTransaksi;
                        $norecHead = $newPJT->generateNewId();
                        $newPJT->norec = $norecHead;
                        $newPJT->kdprofile = $kdProfile;
                        $newPJT->noposting = $noPosting;
                        $newPJT->nojurnal = 0;
                        $newPJT->nojurnal_intern = $noJurnalIntern;
                        $newPJT->objectjenisjurnalfk = 1;
                        $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                        $newPJT->tglbuktitransaksi = $item->tglsbm;// $this->getDateTime()->format('Y-m-d H:i:s');
                        $newPJT->kdproduk = null;
                        $newPJT->namaproduktransaksi = 'Penerimaan Deposit dari ' . $nocm . ' ' . $nama;
                        $newPJT->deskripsiproduktransaksi = 'penerimaan_deposit';
                        $newPJT->keteranganlainnya = 'Penerimaan Deposit Tgl. ' . $item->tgl;

//                $newPJT->keteranganlainnya = 'Penerimaan Deposit dari ' . $nocm . ' ' . $nama;
                        $newPJT->statusenabled = true;
                        $newPJT->norecrelated = $item->norec_smbc;
                        $newPJT->save();

                        $norec_pj = $postingJurnalTransaksi->norec;

                        $debetId = 11153;//Uang Muka Layanan
                        $kreditId = 1778;//Piutang Pasien dalam perawatan

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $debetId;
                        $postingJurnalTransaksiD->hargasatuand = $totalRp;
                        $postingJurnalTransaksiD->hargasatuank = 0;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();
                        //kredit
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                        $postingJurnalTransaksiD->hargasatuand = 0;
                        $postingJurnalTransaksiD->hargasatuank = $totalRp;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();
                    }else{
                        $noJurnalIntern = Carbon::parse($item->tglsbm)->format('ym') . 'AJ' . Carbon::parse($item->tglsbm)->format('d') . '00003';
                        $newPJT = new PostingJurnalTransaksi;
                        $norecHead = $newPJT->generateNewId();
                        $newPJT->norec = $norecHead;
                        $newPJT->kdprofile = $kdProfile;
                        $newPJT->noposting = $noPosting;
                        $newPJT->nojurnal = 0;
                        $newPJT->nojurnal_intern = $noJurnalIntern;
                        $newPJT->objectjenisjurnalfk = 1;
                        $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                        $newPJT->tglbuktitransaksi = date('Y-m-t', strtotime($item->tglsbm));//$item->tglsbm;// $this->getDateTime()->format('Y-m-d H:i:s');
                        $newPJT->kdproduk = null;
                        $newPJT->namaproduktransaksi = 'Adjustment Penerimaan Deposit dari ' . $nocm . ' ' . $nama;
                        $newPJT->deskripsiproduktransaksi = 'penerimaan_deposit';
                        $newPJT->keteranganlainnya = 'Adjustment Penerimaan Deposit Tgl. ' . $item->tgl;

//                $newPJT->keteranganlainnya = 'Penerimaan Deposit dari ' . $nocm . ' ' . $nama;
                        $newPJT->statusenabled = 1;
                        $newPJT->norecrelated = $item->norec_smbc;
                        $newPJT->save();

                        $norec_pj = $postingJurnalTransaksi->norec;

                        $debetId = 11153;//Uang Muka Layanan
                        $kreditId = 1778;//Piutang Pasien dalam perawatan

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $debetId;
                        $postingJurnalTransaksiD->hargasatuand = $totalRp;
                        $postingJurnalTransaksiD->hargasatuank = 0;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();
                        //kredit
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $kdProfile;
                        $postingJurnalTransaksiD->nojurnal = 0;
                        $postingJurnalTransaksiD->noposting = $noPosting;
                        $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                        $postingJurnalTransaksiD->hargasatuand = 0;
                        $postingJurnalTransaksiD->hargasatuank = $totalRp;
                        $postingJurnalTransaksiD->statusenabled = 1;
                        $postingJurnalTransaksiD->norecrelated = $norec_pj;
                        $postingJurnalTransaksiD->save();
                    }
                }
            }


            //############################################################################################################################################
            //##################################################### DISKON/Biaya ##############################################################################
            //############################################################################################################################################
            // TODO : Jurnal Diskon/Biaya
//            $dataCoaDiskon = DB::select(DB::raw("select ru.id as ruid, ru.namaruangan,coa.namaaccount,coa.kdaccount,coa.id as coaid
//                    from chartofaccount_m as coa
//                    left JOIN ruangan_m as ru on coa.namaaccount like '%' || ru.namaruangan || ''
//                    where coa.namaexternal='2018-03-01' and coa.namaaccount like 'Biaya  Subsidi Fasilitas %' and ru.namaruangan <>'-';")
//            );
            //DELETE JIKA DI TABEL TRANSAKSI SUDAH TIDAK ADA
//            $delMacan = DB::select(DB::raw("
//                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                    INNER JOIN  pelayananpasien_t as pp on pp.norec =pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
//                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                    where pjt.deskripsiproduktransaksi='diskon' and pp.norec is null and posted.nojurnal_intern is null
//                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00');")
//            );
//            $delMacanHead = DB::select(DB::raw("
//                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                    INNER JOIN  pelayananpasien_t as pp on pp.norec =pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
//                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                    where pjt.deskripsiproduktransaksi='diskon' and pp.norec is null and posted.nojurnal_intern is null
//                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00');")
//            );

            $aingDiskon = DB::select(DB::raw("select ps.nocm,ps.namapasien,pd.noregistrasi,case when ((pp.hargadiscount * pp.jumlah) is null) then (0) else (pp.hargadiscount * pp.jumlah) end as total,
                    pp.tglpelayanan,adp.objectruanganfk,
                    pp.norec as norec_pp,to_char(pp.tglpelayanan, 'YYYY-MM-DD') as tgl,pjt.norec
                    from pasiendaftar_t pd 
                    inner join antrianpasiendiperiksa_t adp on adp.noregistrasifk = pd.norec 
                    inner join pasien_m as ps on ps.id = pd.nocmfk 
                    left join pelayananpasien_t pp on pp.noregistrasifk = adp.norec
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated  = pp.norec and pjt.deskripsiproduktransaksi='diskon'
                    where pd.kdprofile = $kdProfile and pp.tglpelayanan BETWEEN :tglAwal and :tglAkhir and pjt.norec is null
                    and pp.hargadiscount is not null and pp.hargadiscount > 0"),
                array(
                    'tglAwal' => $request['tglAwal'],
                    'tglAkhir' => $request['tglAkhir'],
                )
            );
            $coaruangandiskon =[];
            foreach ($aingDiskon as $item) {
                $nocm = $item->nocm;
                $nama = $item->namapasien;
                $noBuktiTransaksi = $item->noregistrasi;
                $totalRp = $item->total;
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'NT-' . $this->getDateTime()->format('ym'));
//                $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

//                $noJurnalIntern = Carbon::parse($item->tglsbm)->format('ym') . 'PN' . Carbon::parse($item->tglsbm)->format('d') . '00004';
//                if ($item->objectdepartemenfk == 16) {
//                    $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym') . 'PN' . Carbon::parse($item->tglpelayanan)->format('d') . '00004';
//                } else {
                $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym') . 'PN' . Carbon::parse($item->tglpelayanan)->format('d') . '00004';
//                }
                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->get();

                if ($this->getCountArray($cekSudahPosting) == 0) {

                    $newPJT = new PostingJurnalTransaksi;
                    $norecHead = $newPJT->generateNewId();
                    $newPJT->norec = $norecHead;
                    $newPJT->kdprofile = $kdProfile;
                    $newPJT->noposting = $noPosting;
                    $newPJT->nojurnal = 0;
                    $newPJT->nojurnal_intern = $noJurnalIntern;
                    $newPJT->objectjenisjurnalfk = 1;
                    $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                    $newPJT->tglbuktitransaksi = $item->tglpelayanan;// $this->getDateTime()->format('Y-m-d H:i:s');
                    $newPJT->kdproduk = null;
                    $newPJT->namaproduktransaksi = 'Diskon dari ' . $nocm . ' ' . $nama;
                    $newPJT->deskripsiproduktransaksi = 'diskon';
//                    if ($item->objectdepartemenfk == 16) {
//                        $newPJT->keteranganlainnya = 'Pendapatan RI Non Tunai Tgl. ' . $item->tgl;
//                    } else {
                    $newPJT->keteranganlainnya = 'Biaya Subsidi Pasien Tgl. ' . $item->tgl;
//                    }
//                    $newPJT->keteranganlainnya = 'Pendapatan RI Non Tunai Tgl. ' . $item->tgl;

//                $newPJT->keteranganlainnya = 'Penerimaan Deposit dari ' . $nocm . ' ' . $nama;
                    $newPJT->statusenabled = 1;
                    $newPJT->norecrelated = $item->norec_pp ;
                    $newPJT->save();

                    $norec_pj = $postingJurnalTransaksi->norec;

                    $debetId = 11216;//Biaya Jasa Pelayanan - Medis
                    $kreditId = 1778;//Piutang Pasien dalam perawatan

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();
                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();
                }else{
                    $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym') . 'AJ' . Carbon::parse($item->tglpelayanan)->format('d') . '00004';
                    $newPJT = new PostingJurnalTransaksi;
                    $norecHead = $newPJT->generateNewId();
                    $newPJT->norec = $norecHead;
                    $newPJT->kdprofile = $kdProfile;
                    $newPJT->noposting = $noPosting;
                    $newPJT->nojurnal = 0;
                    $newPJT->nojurnal_intern = $noJurnalIntern;
                    $newPJT->objectjenisjurnalfk = 1;
                    $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                    $newPJT->tglbuktitransaksi = date('Y-m-t', strtotime($item->tglpelayanan));//$item->tglpelayanan;
                    $newPJT->kdproduk = null;
                    $newPJT->namaproduktransaksi = 'Adjustment Diskon dari ' . $nocm . ' ' . $nama;
                    $newPJT->deskripsiproduktransaksi = 'diskon';
//                    if ($item->objectdepartemenfk == 16) {
//                        $newPJT->keteranganlainnya = 'Pendapatan RI Non Tunai Tgl. ' . $item->tgl;
//                    } else {
                    $newPJT->keteranganlainnya = 'Adjustment Biaya Subsidi Pasien Tgl. ' . $item->tgl;
//                    }
                    $newPJT->statusenabled = 1;
                    $newPJT->norecrelated = $item->norec_pp ;
                    $newPJT->save();

                    $norec_pj = $postingJurnalTransaksi->norec;

//                    $debetId=0;
//                    foreach ($dataCoaDiskon as $coa){
//                        if ($coa->ruid == $item->objectruanganfk){
//                            $debetId = $coa->coaid;
//                        }
//                    }
//                    $coaruangandiskon[]=array(
//                        'coaid'=>$debetId,
//                        'ruangan'=>$item->namaruangan,
//                    );
                    $debetId = 11216;//Biaya Jasa Pelayanan - Medis
                    $kreditId = 1778;//Piutang Pasien dalam perawatan

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();
                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Posting";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Jurnal Berhasil!!";
            DB::commit();
            $result = array(
                "status" => 201,
                "count" => count($aingMacan),
                "countDeposit" => count($aingMaung),
                "countDiskon" => count($aingDiskon),
//                "datacoadiskon" => $dataCoaDiskon,
//                "jml" => $jmlposting,
//                "data2" => $aingMacan,
//                "trans" => $postingJurnalTransaksi,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ."Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "count" => count($aingMacan),
//                "countDeposit" => count($aingMaung),
//                "data" => $aingMacan,//$noResep,
                "datadiskon" => $coaruangandiskon,//$noResep,
//                "datacoadiskon" => $dataCoaDiskon,
//                "trans" => $postingJurnalTransaksi,
                "as" => 'as@epic',
            );
            //"Don't Stop When You're Tired, Stop When You're Done"
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function PostingJurnal_pelayananpasien_t(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $isAktif = $this->settingDataFixed('PostingJurnal_pelayananpasien_t_isaktif', $kdProfile);
        if ($isAktif == 0){
            return;
        }
        $dataLogin = $request->all();
//        ini_set('max_execution_time', 1000); //6 minutes
        try {
            // TODO : Jurnal Pelayanan
//            $dataCoa = DB::select(DB::raw("select ru.id as ruid, ru.namaruangan,coa.namaaccount,coa.kdaccount,coa.id as coaid
//                    from chartofaccount_m as coa
//                    left JOIN ruangan_m as ru on coa.namaaccount like '%' || ru.namaruangan || ''
//                    JOIN suratkeputusan_m as sk on sk.id=coa.suratkeputusanfk
//                    where sk.statusenabled=1 and coa.namaaccount like 'Pend%' and ru.namaruangan <>'-';")
//            );
            //DELETE JIKA DI TABEL TRANSAKSI SUDAH TIDAK ADA
//            $delMacan = DB::select(DB::raw("
//                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                    left JOIN pelayananpasien_t as pp on pp.norec=pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
//                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                    where pjt.deskripsiproduktransaksi='pelayananpasien_t' and pp.norec is null and posted.nojurnal_intern is null
//                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00')")
//            );
//            $delMacanHead = DB::select(DB::raw("
//                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                    left JOIN pelayananpasien_t as pp on pp.norec=pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
//                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                    where pjt.deskripsiproduktransaksi='pelayananpasien_t' and pp.norec is null and posted.nojurnal_intern is null
//                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00');")
//            );

            if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
                $aingMacan = DB::select(DB::raw("
                
                    select 
                    -- top 100
                    pp.tglpelayanan,pr.namaproduk,pp.hargajual,pp.jumlah,pp.harganetto,pd.objectkelompokpasienlastfk,
                    case when pp.aturanpakai is null then 'XObat' when pp.aturanpakai ='-' then 'XObat' else 'Obat' end as Obat,pp.produkfk,pr.objectdetailjenisprodukfk,pr.objectkelompokprodukbpjsfk,
                    ((case when pp.hargajual is null then 0 else pp.hargajual end)*pp.jumlah) + case when jasa is null then 0 else jasa end as harga,
                    pr.id as prid,
                    to_char(pp.tglpelayanan, 'YYYY-MM-DD') as tgl,
                    pp.norec as norec_pp
                    from pelayananpasien_t as pp 
                    inner join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                    inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                    inner join produk_m as pr on pr.id=pp.produkfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=pp.norec and pjt.deskripsiproduktransaksi = 'pelayananpasien_t'
                    where pp.kdprofile = $kdProfile and pd.noregistrasi = :noregistrasi and pjt.norec is  null 
                    and pp.produkfk not in (402611)
                    limit 100
                "),
                    array(
                        'noregistrasi' => $request['noregistrasi'],
                    )
                );
            }else {

                $aingMacan = DB::select(DB::raw("
                
                    select 
                    -- top 1000
                    pp.tglpelayanan,pr.namaproduk,pp.hargajual,pp.jumlah,pp.harganetto,pd.objectkelompokpasienlastfk,
                    case when pp.aturanpakai is null then 'XObat' when pp.aturanpakai ='-' then 'XObat' else 'Obat' end as Obat,pp.produkfk,pr.objectdetailjenisprodukfk,pr.objectkelompokprodukbpjsfk,
                    ((case when pp.hargajual is null then 0 else pp.hargajual end)*pp.jumlah) + case when jasa is null then 0 else jasa end as harga,
                    pr.id as prid,
                    to_char(pp.tglpelayanan, 'YYYY-MM-DD') as tgl,
                    pp.norec as norec_pp
                    from pelayananpasien_t as pp 
                    inner join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                    inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                    inner join produk_m as pr on pr.id=pp.produkfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=pp.norec and pjt.deskripsiproduktransaksi = 'pelayananpasien_t'
                    where pp.kdprofile = $kdProfile and pp.tglpelayanan between :tglAwal and :tglAkhir and pjt.norec is  null 
                    and pp.produkfk not in (402611)
                    limit 1000
                "),
                    array(
                        'tglAwal' => $request['tglAwal'],
                        'tglAkhir' => $request['tglAkhir'],
                    )
                );
            }
//            return $this->respond($aingMacan);
            foreach ($aingMacan as $item){
                $coaAdministrasi='a';
                $coaKonsultasi='b';
                $coaVisite='c';
                $coaAkomodasi='d';
                $coaTindakan='e';
                $coaAlatCanggih='f';

//                $ruanganId =$item->ruid;
//                $deptId =$item->dept_apd;
//                $deptId =$item->dept_pd;
//                if ($item->produkfk == 10011571){
//                    $ruanganId =$item->ruid_pd;
//                    $deptId =$item->dept_pd;
//                }
//                if ($item->produkfk == 10011572){
//                    $ruanganId =$item->ruid_pd;
//                    $deptId =$item->dept_pd;
//                }
//                foreach ($dataCoa as $coa){
//                    if ($coa->ruid == $ruanganId){
//                        if (strpos($coa->namaaccount , 'Administrasi')!= false){
//                            $coaAdministrasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Konsul')!= false){
//                            $coaKonsultasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Visite')!= false){
//                            $coaVisite=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Akomodasi')!= false){
//                            $coaAkomodasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Tindakan')!= false){
//                            $coaTindakan=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Alat Canggih')!= false){
//                            $coaAlatCanggih=$coa->coaid;
//                        }
//                    }
//                }

                $totalRp = $item->harga;//($item->hargajual) * ($item->jumlah);
                $totalNettoRp = ($item->harganetto) * ($item->jumlah);

//                $debet = 'Piutang Pasien dalam Perawatan ';
//                $debetId = 1778;
//                if ($item->objectjenisprodukfk == 97) {
//                    $kredit = 'Pendat. Tindakan Ka Instalasi Farmasi';
//                    $kreditId = 2195;
//                }else{
//                    if ($item->produkfk == 395 ) {//administrasi
//                        $kreditId = $coaAdministrasi;
//                    }elseif ($item->produkfk == 10011572 ) {//administrasi
//                        $kreditId = $coaAdministrasi;
//                    }elseif ( $item->produkfk == 10011571) {//administrasi
//                        $kreditId = $coaAdministrasi;
//                    }else{
//                        if ($item->objectjenisprodukfk == 101){//visite
//                            $kreditId = $coaVisite;
//                        }elseif ( $item->objectjenisprodukfk == 100){//konsultasi
//                            $kreditId = $coaKonsultasi;
//                        }elseif ($item->objectjenisprodukfk == 99 ){//akomodasi
//                            $kreditId = $coaAkomodasi;
//                        }elseif ($item->objectjenisprodukfk == 27666 ){//alat canggih
//                            $kreditId = $coaAlatCanggih;
//                        }else{//Tindakan
//                            $kreditId = $coaTindakan;
//                        }
//                    };
//                }
                $debetId = 1778;
                $kreditId = 12211;
//                if ($item->objectkelompokpasienlastfk == 1){//1	Umum/Pribadi
////                    $debetId = 10896;
//                    $kreditId = 11164;
//                }
//                if ($item->objectkelompokpasienlastfk == 2){//2 BPJS
////                    $debetId = 10897;
//                    $kreditId = 11165;
//                }
//                if ($item->objectkelompokpasienlastfk == 3){//3	Asuransi lain
////                    $debetId = 10901;
//                    $kreditId = 11169;
//                }
//                if ($item->objectkelompokpasienlastfk == 5){//5	Perusahaan
////                    $debetId = 10901;
//                    $kreditId = 11169;
//                }
//                if ($item->objectkelompokpasienlastfk == 6){//6	Perjanjian
////                    $debetId = 10896;
//                    $kreditId = 11164;
//                }
//                if ($item->objectkelompokpasienlastfk == 7){//7	Dinas Sosial
////                    $debetId = 10900;
//                    $kreditId = 11168;
//                }
//                $debetId = 0;
//                10896	Piutang Pasien Umum
//                10897	Piutang BPJS
//                10898	Piutang APBD Kab/Kota (Jamkesda)
//                10899	Piutang APBD Provinsi
//                10900	Piutang APBN (Jamkesmas)
//                10901	Piutang Perusahaan Kerjasama
//                10902	Piutang Diklat
//                10903	Piutang Fasilitas

                //KREDIT
//                $kreditId = 0;
//                11164	Pendapatan Pasien Umum
//                11165	Pendapatan Pasien BPJS
//                11166	Pendapatan Pasien Jamkesda Kota
//                11167	Pendapatan Pasien Jamkesda Provinsi
//                11168	Pendapatan Pasien Jamkesmas
//                11169	Pendapatan Pasien Kerjasama
//                11170	Pendapatan Diklat
//                11171	Pendapatan Lainnya
//                11172	Penyesuaian Tarif Kontraktual


//                $coacoa[] =array(
//                    'namaProduk' => $item->namaproduk . '  ' . $item->namaruangan,
//                    'kreditID' => $kreditId,
//                    'debetID' => $debetId,
////                    'coaAdministrasi' => $coaAdministrasi,
////                    'coaKonsultasi' => $coaKonsultasi,
////                    'coaVisite' => $coaVisite,
////                    'coaAkomodasi' => $coaAkomodasi,
////                    'coaTindakan' => $coaTindakan,
////                    'coaAlatCanggih' => $coaAlatCanggih,
//                    'data' => $item,
//                );
                if ($kreditId == 'a' || $kreditId == 'b' || $kreditId == 'c' || $kreditId == 'd' || $kreditId == 'e' || $kreditId == 'f'){
                    $kreditId = 2363; //SUSPEND	4999
                }
                $noBuktiTransaksi =$item->prid;
//                $namaPasien = $item->namapasien;
//                $namaTindakan = $item->namaproduk;

                $noJurnalIntern ='';
//                if ($item->objectjenisprodukfk != 97) {
//                if ($deptId == 16) {
//                    $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'RI-' . $this->getDateTime()->format('ym'));
//                    $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym').'PN'.Carbon::parse($item->tglpelayanan)->format('d').'00002';
//                }else{
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'RJ-' . $this->getDateTime()->format('ym'));
                $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym').'PN'.Carbon::parse($item->tglpelayanan)->format('d').'00001';
//                }
                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->where('kdprofile', $kdProfile)->get();

                if ($this->getCountArray($cekSudahPosting) == 0){
//                    $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                    $postingJurnalTransaksi = new PostingJurnalTransaksi;
                    $norecHead = $postingJurnalTransaksi->generateNewId();
                    $postingJurnalTransaksi->norec = $norecHead;
                    $postingJurnalTransaksi->kdprofile = $kdProfile;
                    $postingJurnalTransaksi->noposting =  $noPosting;
                    $postingJurnalTransaksi->nojurnal = 0;
                    $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
                    $postingJurnalTransaksi->objectjenisjurnalfk = 1;
                    $postingJurnalTransaksi->nobuktitransaksi = $noBuktiTransaksi;
                    $postingJurnalTransaksi->tglbuktitransaksi = $item->tglpelayanan;
                    $postingJurnalTransaksi->kdproduk = $item->prid;
                    $postingJurnalTransaksi->namaproduktransaksi = $item->namaproduk ;//. ' ' . $item->obat;
                    $postingJurnalTransaksi->deskripsiproduktransaksi = 'pelayananpasien_t';
//                    if ($deptId == 16){
//                        $postingJurnalTransaksi->keteranganlainnya = 'Pendapatan RI tgl. ' . $item->tgl;
//                    }else{
                    $postingJurnalTransaksi->keteranganlainnya = 'Pendapatan tgl. ' . $item->tgl;
//                    }

                    $postingJurnalTransaksi->statusenabled = 1;
                    $postingJurnalTransaksi->norecrelated = $item->norec_pp;
                    $postingJurnalTransaksi->save();

                    $norec_pj = $postingJurnalTransaksi->norec;

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();

                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();
                }else{
                    $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym').'AJ'.Carbon::parse($item->tglpelayanan)->format('d').'00001';
//                    $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                    $postingJurnalTransaksi = new PostingJurnalTransaksi;
                    $norecHead = $postingJurnalTransaksi->generateNewId();
                    $postingJurnalTransaksi->norec = $norecHead;
                    $postingJurnalTransaksi->kdprofile = $kdProfile;
                    $postingJurnalTransaksi->noposting =  $noPosting;
                    $postingJurnalTransaksi->nojurnal = 0;
                    $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
                    $postingJurnalTransaksi->objectjenisjurnalfk = 1;
                    $postingJurnalTransaksi->nobuktitransaksi = $noBuktiTransaksi;
                    $postingJurnalTransaksi->tglbuktitransaksi = date('Y-m-t', strtotime($item->tglpelayanan));//$item->tglpelayanan;
                    $postingJurnalTransaksi->kdproduk = $item->prid;
                    $postingJurnalTransaksi->namaproduktransaksi = $item->namaproduk;
                    $postingJurnalTransaksi->deskripsiproduktransaksi = 'pelayananpasien_t';
//                    if ($deptId == 16){
//                        $postingJurnalTransaksi->keteranganlainnya = 'Adjustment Pendapatan RI tgl. ' . $item->tgl;
//                    }else{
                    $postingJurnalTransaksi->keteranganlainnya = 'Adjustment Pendapatan tgl. ' . $item->tgl;
//                    }

                    $postingJurnalTransaksi->statusenabled = true;
                    $postingJurnalTransaksi->norecrelated = $item->norec_pp;
                    $postingJurnalTransaksi->save();


                    $norec_pj = $postingJurnalTransaksi->norec;

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();

                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();
                }
            }

            //ADJ Perbedaan Harga
            // TODO : Jurnal Adj Perbedaan Harga
//            $aingMacan2 = DB::select(DB::raw("
//                        select
//                        pp.tglpelayanan,pr.namaproduk,ru.namaruangan,ru_pd.namaruangan as namaruangan_pd,pp.hargajual,pp.jumlah,pp.harganetto,
//                        case when pp.aturanpakai is null then 'XObat' when pp.aturanpakai ='-' then 'XObat' else 'Obat' end as Obat,pp.produkfk,pr.objectdetailjenisprodukfk,pr.objectkelompokprodukbpjsfk,
//                        --(case when pp.hargajual is null then 0 else pp.hargajual end-(case when pp.hargadiscount is null then 0 else pp.hargadiscount end))*pp.jumlah as harga,
//                        ((case when pp.hargajual is null then 0 else pp.hargajual end)*pp.jumlah) + case when jasa is null then 0 else jasa end as harga,
//                        case when pjd.hargasatuand =0 then  pjd.hargasatuank else pjd.hargasatuand end as jrl,
//                        case when pp.hargadiscount is null then 0 else pp.hargadiscount end as diskon,
//                        djp.objectjenisprodukfk,ru.id as ruid,ru_pd.id as ruid_pd,pr.id as prid,pr.namaproduk,
//                        to_char(pp.tglpelayanan, 'YYYY-MM-DD') as tgl,pp.norec as norec_pp,
//                        ru.objectdepartemenfk as dept_apd,ru_pd.objectdepartemenfk as dept_pd
//                        from postingjurnaltransaksi_t as pj
//                        INNER JOIN postingjurnaltransaksid_t as pjd on pj.norec=pjd.norecrelated
//                        INNER JOIN pelayananpasien_t as pp on pp.norec=pj.norecrelated and pj.deskripsiproduktransaksi='pelayananpasien_t'
//                        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
//                        INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
//                        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
//                        INNER JOIN ruangan_m as ru_pd on ru_pd.id=pd.objectruanganlastfk
//                        INNER JOIN produk_m as pr on pr.id=pp.produkfk
//                        inner join detailjenisproduk_m as djp on djp.id=pr.objectdetailjenisprodukfk
//                        INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
//                        left JOIN postingjurnaltransaksi_t as pj2 on pj2.norecrelated=pp.norec and pj2.deskripsiproduktransaksi='adjpelayananpasien_t'
//                        where pj.tglbuktitransaksi between :tglAwal and :tglAkhir
//                        and pjd.hargasatuand >0
//                        and case when pjd.hargasatuand =0 then  pjd.hargasatuank else pjd.hargasatuand end  <>
//                        (((case when pp.hargajual is null then 0 else pp.hargajual end)*pp.jumlah) + case when jasa is null then 0 else jasa end)
//                         and pj2.norec is null
//                        limit 100 ;"),
//                array(
//                    'tglAwal' => $request['tglAwal'],
//                    'tglAkhir' => $request['tglAkhir'],
//                )
//            );
//            foreach ($aingMacan2 as $item){
//                $coaAdministrasi='a';
//                $coaKonsultasi='b';
//                $coaVisite='c';
//                $coaAkomodasi='d';
//                $coaTindakan='e';
//                $coaAlatCanggih='f';
//
//                $ruanganId =$item->ruid;
////                $deptId =$item->dept_apd;
//                $deptId =$item->dept_pd;
//                if ($item->produkfk == 10011571){
//                    $ruanganId =$item->ruid_pd;
//                    $deptId =$item->dept_pd;
//                }
//                if ($item->produkfk == 10011572){
//                    $ruanganId =$item->ruid_pd;
//                    $deptId =$item->dept_pd;
//                }
//                foreach ($dataCoa as $coa){
//                    if ($coa->ruid == $ruanganId){
//                        if (strpos($coa->namaaccount , 'Administrasi')!= false){
//                            $coaAdministrasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Konsul')!= false){
//                            $coaKonsultasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Visite')!= false){
//                            $coaVisite=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Akomodasi')!= false){
//                            $coaAkomodasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Tindakan')!= false){
//                            $coaTindakan=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Alat Canggih')!= false){
//                            $coaAlatCanggih=$coa->coaid;
//                        }
//                    }
//                }
//
//                $totalRp = (float)$item->jrl - (float)$item->harga;//($item->hargajual) * ($item->jumlah);
//
//                $debet = 'Piutang Pasien dalam Perawatan ';
//                $debetId = 1778;
//                if ($item->objectjenisprodukfk != 97) {
//                    if ($item->produkfk == 395 ) {//administrasi
//                        $kreditId = $coaAdministrasi;
//                    }elseif ($item->produkfk == 10011572 ) {//administrasi
//                        $kreditId = $coaAdministrasi;
//                    }elseif ( $item->produkfk == 10011571) {//administrasi
//                        $kreditId = $coaAdministrasi;
//                    }else{
//                        if ($item->objectjenisprodukfk == 101){//visite
//                            $kreditId = $coaVisite;
//                        }elseif ( $item->objectjenisprodukfk == 100){//konsultasi
//                            $kreditId = $coaKonsultasi;
//                        }elseif ($item->objectjenisprodukfk == 99 ){//akomodasi
//                            $kreditId = $coaAkomodasi;
//                        }elseif ($item->objectjenisprodukfk == 27666 ){//alat canggih
//                            $kreditId = $coaAlatCanggih;
//                        }else{//Tindakan
//                            $kreditId = $coaTindakan;
//                        }
//                    };
//                } else {//OBAT
//                    $kredit = 'Pendat. Tindakan Ka Instalasi Farmasi';
//                    $kreditId = 2195;
//                };
//                $coacoa[] =array(
//                    'namaProduk' => $item->namaproduk . '  ' . $item->namaruangan,
//                    'kreditID' => $kreditId,
//                    'coaAdministrasi' => $coaAdministrasi,
//                    'coaKonsultasi' => $coaKonsultasi,
//                    'coaVisite' => $coaVisite,
//                    'coaAkomodasi' => $coaAkomodasi,
//                    'coaTindakan' => $coaTindakan,
//                    'coaAlatCanggih' => $coaAlatCanggih,
//                    'data' => $item,
//                );
//                $noBuktiTransaksi =$item->prid;
//                $noPosting = '-';
//                $ddt = '';
//                if ((float)$item->jrl > (float)$item->harga){
//                    $totalRp = (float)$item->jrl - (float)$item->harga - (float)$item->diskon;
//                }else{
//                    $totalRp = (float)$item->harga - (float)$item->jrl;
//                    $ddt = $debetId;
//                    $debetId = $kreditId ;
//                    $kreditId = $ddt;
//                }
//                if ($totalRp > 0){
//                    $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym').'AJ'.Carbon::parse($item->tglpelayanan)->format('d').'00003';
//                    $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');
//
//                    $postingJurnalTransaksi = new PostingJurnalTransaksi;
//                    $norecHead = $postingJurnalTransaksi->generateNewId();
//                    $postingJurnalTransaksi->norec = $norecHead;
//                    $postingJurnalTransaksi->kdprofile = 1;
//                    $postingJurnalTransaksi->noposting =  $noPosting;
//                    $postingJurnalTransaksi->nojurnal = $nojurnal;
//                    $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
//                    $postingJurnalTransaksi->objectjenisjurnalfk = 1;
//                    $postingJurnalTransaksi->nobuktitransaksi = $noBuktiTransaksi;
//                    $postingJurnalTransaksi->tglbuktitransaksi = date('Y-m-t', strtotime($item->tglpelayanan));//$item->tglpelayanan;
//                    $postingJurnalTransaksi->kdproduk = $item->prid;
//                    $postingJurnalTransaksi->namaproduktransaksi = $item->namaproduk;
//                    $postingJurnalTransaksi->deskripsiproduktransaksi = 'adjpelayananpasien_t';
//                    $postingJurnalTransaksi->keteranganlainnya = 'Adjustment Perubahan Harga tgl. ' . $item->tgl;
//
//                    $postingJurnalTransaksi->statusenabled = 1;
//                    $postingJurnalTransaksi->norecrelated = $item->norec_pp;
//                    $postingJurnalTransaksi->save();
//
//                    //debet
//                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
//                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
//                    $postingJurnalTransaksiD->kdprofile = 1;
//                    $postingJurnalTransaksiD->nojurnal = $nojurnal;
//                    $postingJurnalTransaksiD->noposting = $noPosting;
//                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
//                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
//                    $postingJurnalTransaksiD->hargasatuank = 0;
//                    $postingJurnalTransaksiD->statusenabled = 1;
//                    $postingJurnalTransaksiD->norecrelated = $norecHead;
//                    $postingJurnalTransaksiD->save();
//
//                    //kredit
//                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
//                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
//                    $postingJurnalTransaksiD->kdprofile = 1;
//                    $postingJurnalTransaksiD->nojurnal = $nojurnal;
//                    $postingJurnalTransaksiD->noposting = $noPosting;
//                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
//                    $postingJurnalTransaksiD->hargasatuand = 0;
//                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
//                    $postingJurnalTransaksiD->statusenabled = 1;
//                    $postingJurnalTransaksiD->norecrelated = $norecHead;
//                    $postingJurnalTransaksiD->save();
//                }
//            }
            //end Perbedaan Harga

            //Posting Obat Bebas
            // TODO : Jurnal Obat Bebas
            //DELETE JIKA DI TABEL TRANSAKSI SUDAH TIDAK ADA
//            $delMacan = DB::select(DB::raw("
//                        delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                        left JOIN strukpelayanandetail_t as pp on pp.norec=pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
//                        left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                        where pjt.deskripsiproduktransaksi='pelayananpasien_tob' and pp.norec is null and posted.nojurnal_intern is null
//                        and pjt.tglbuktitransaksi  >'2019-01-01 00:00')")
//            );
//            $delMacanHead = DB::select(DB::raw("
//                        delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                        left JOIN strukpelayanandetail_t as pp on pp.norec=pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
//                        left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                        where pjt.deskripsiproduktransaksi='pelayananpasien_tob' and pp.norec is null and posted.nojurnal_intern is null
//                        and pjt.tglbuktitransaksi  >'2019-01-01 00:00');")
//            );
            $aingMacan33 = DB::select(DB::raw("
                    
                    select pjt.norec as pjtnorec,
                    sp.tglstruk as  tglpelayanan,pr.namaproduk,(spd.hargasatuan + spd.hargatambahan) as  hargajual,spd.qtyproduk as jumlah,spd.harganetto,
                    'Obat'  as Obat,spd.objectprodukfk as produkfk,pr.objectdetailjenisprodukfk,pr.objectkelompokprodukbpjsfk,
                    ((spd.hargasatuan  )*spd.qtyproduk)+ spd.hargatambahan as harga,
                    pr.id as prid,pr.namaproduk,
                    to_char(sp.tglstruk, 'YYYY-MM-DD') as tgl,spd.norec as norec_pp
                    from strukpelayanandetail_t as spd 
                    inner join strukpelayanan_t as sp on sp.norec=spd.nostrukfk
                    inner join produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=spd.norec and pjt.deskripsiproduktransaksi = 'pelayananpasien_tob'
                    where spd.kdprofile = $kdProfile and sp.tglstruk between :tglAwal and :tglAkhir and pjt.norec is  null 
                    and substring(sp.nostruk,1,2)='OB'
                    
                    "),
                array(
                    'tglAwal' => $request['tglAwal'],
                    'tglAkhir' => $request['tglAkhir'],
                )
            );
            foreach ($aingMacan33 as $item){
                $coaAdministrasi='a';
                $coaKonsultasi='b';
                $coaVisite='c';
                $coaAkomodasi='d';
                $coaTindakan='e';
                $coaAlatCanggih='f';

//                $ruanganId =$item->ruid;
//                $deptId =$item->dept_apd;
//                $deptId =$item->dept_pd;
//                if ($item->produkfk == 10011571){
//                    $ruanganId =$item->ruid_pd;
//                    $deptId =$item->dept_pd;
//                }
//                if ($item->produkfk == 10011572){
//                    $ruanganId =$item->ruid_pd;
//                    $deptId =$item->dept_pd;
//                }
//                foreach ($dataCoa as $coa){
//                    if ($coa->ruid == $ruanganId){
//                        if (strpos($coa->namaaccount , 'Administrasi')!= false){
//                            $coaAdministrasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Konsul')!= false){
//                            $coaKonsultasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Visite')!= false){
//                            $coaVisite=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Akomodasi')!= false){
//                            $coaAkomodasi=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Tindakan')!= false){
//                            $coaTindakan=$coa->coaid;
//                        }
//                        if (strpos($coa->namaaccount , 'Alat Canggih')!= false){
//                            $coaAlatCanggih=$coa->coaid;
//                        }
//                    }
//                }

                $totalRp = $item->harga;//($item->hargajual) * ($item->jumlah);
                $totalNettoRp = ($item->harganetto) * ($item->jumlah);

//                $debet = 'Piutang Pasien dalam Perawatan ';
//                $debetId = 1778;
//                    $kredit = 'Pendat. Tindakan Ka Instalasi Farmasi';
//                    $kreditId = 2195;

                $debetId = 1778;//10896;
                $kreditId = 12211;
//                };
//                $coacoa[] =array(
//                    'namaProduk' => $item->namaproduk . '  ' . $item->namaruangan,
//                    'kreditID' => $kreditId,
//                    'coaAdministrasi' => $coaAdministrasi,
//                    'coaKonsultasi' => $coaKonsultasi,
//                    'coaVisite' => $coaVisite,
//                    'coaAkomodasi' => $coaAkomodasi,
//                    'coaTindakan' => $coaTindakan,
//                    'coaAlatCanggih' => $coaAlatCanggih,
//                    'data' => $item,
//                );
                $noBuktiTransaksi =$item->prid;
//                $namaPasien = $item->namapasien;
//                $namaTindakan = $item->namaproduk;

                $noJurnalIntern ='';
//                if ($item->objectjenisprodukfk != 97) {
//                if ($deptId == 16) {
//                    $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'RI-' . $this->getDateTime()->format('ym'));
//                    $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym').'PN'.Carbon::parse($item->tglpelayanan)->format('d').'00002';
//                }else{
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'RJ-' . $this->getDateTime()->format('ym'));
                $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym').'PN'.Carbon::parse($item->tglpelayanan)->format('d').'00001';
//                }
                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->where('kdprofile', $kdProfile)->get();

                if ($this->getCountArray($cekSudahPosting) == 0){
//                    $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                    $postingJurnalTransaksi = new PostingJurnalTransaksi;
                    $norecHead = $postingJurnalTransaksi->generateNewId();
                    $postingJurnalTransaksi->norec = $norecHead;
                    $postingJurnalTransaksi->kdprofile = $kdProfile;
                    $postingJurnalTransaksi->noposting =  $noPosting;
                    $postingJurnalTransaksi->nojurnal = 0;
                    $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
                    $postingJurnalTransaksi->objectjenisjurnalfk = 1;
                    $postingJurnalTransaksi->nobuktitransaksi = $noBuktiTransaksi;
                    $postingJurnalTransaksi->tglbuktitransaksi = $item->tglpelayanan;
                    $postingJurnalTransaksi->kdproduk = $item->prid;
                    $postingJurnalTransaksi->namaproduktransaksi = $item->namaproduk;
                    $postingJurnalTransaksi->deskripsiproduktransaksi = 'pelayananpasien_tob';
//                    if ($deptId == 16){
//                        $postingJurnalTransaksi->keteranganlainnya = 'Pendapatan RI tgl. ' . $item->tgl;
//                    }else{
                    $postingJurnalTransaksi->keteranganlainnya = 'Pendapatan tgl. ' . $item->tgl;
//                    }

                    $postingJurnalTransaksi->statusenabled = 1;
                    $postingJurnalTransaksi->norecrelated = $item->norec_pp;
                    $postingJurnalTransaksi->save();

                    $norec_pj = $postingJurnalTransaksi->norec;

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();

                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();
                }else{
                    $noJurnalIntern = Carbon::parse($item->tglpelayanan)->format('ym').'AJ'.Carbon::parse($item->tglpelayanan)->format('d').'00001';
//                    $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                    $postingJurnalTransaksi = new PostingJurnalTransaksi;
                    $norecHead = $postingJurnalTransaksi->generateNewId();
                    $postingJurnalTransaksi->norec = $norecHead;
                    $postingJurnalTransaksi->kdprofile = $kdProfile;
                    $postingJurnalTransaksi->noposting =  $noPosting;
                    $postingJurnalTransaksi->nojurnal = 0;
                    $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
                    $postingJurnalTransaksi->objectjenisjurnalfk = 1;
                    $postingJurnalTransaksi->nobuktitransaksi = $noBuktiTransaksi;
                    $postingJurnalTransaksi->tglbuktitransaksi = date('Y-m-t', strtotime($item->tglpelayanan));//$item->tglpelayanan;
                    $postingJurnalTransaksi->kdproduk = $item->prid;
                    $postingJurnalTransaksi->namaproduktransaksi = $item->namaproduk;
                    $postingJurnalTransaksi->deskripsiproduktransaksi = 'pelayananpasien_tob';
//                    if ($deptId == 16){
//                        $postingJurnalTransaksi->keteranganlainnya = 'Adjustment Pendapatan RI tgl. ' . $item->tgl;
//                    }else{
                    $postingJurnalTransaksi->keteranganlainnya = 'Adjustment Pendapatan tgl. ' . $item->tgl;
//                    }

                    $postingJurnalTransaksi->statusenabled = 1;
                    $postingJurnalTransaksi->norecrelated = $item->norec_pp;
                    $postingJurnalTransaksi->save();
                    $norec_pj = $postingJurnalTransaksi->norec;

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();

                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norec_pj;
                    $postingJurnalTransaksiD->save();
                }
            }
            //end Posting Obat Bebas

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Posting';
        $req = $request->all();
        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Jurnal Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $aingMacan,
                "count" => count($aingMacan),
                "count2" => 0,//count($aingMacan2),
                "count3" => count($aingMacan33),
//                "posting" => $cekSudahPosting,
                "data" => $aingMacan33 ,
                "req" => $req,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Jurnal Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "count" => count($aingMacan),
                "data" => $aingMacan,//$noResep,
//                "coa" => $coacoa,
//                "dataCoa" => $dataCoa,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function  getProdukPart(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req = $request->all();
        $dataProd = \DB::table('produk_m as dg')
            ->select('dg.id','dg.namaproduk as namaProduk' )
            ->where('dg.kdprofile', $kdProfile)
            ->where('dg.statusenabled', true)
            ->orderBy('dg.namaproduk');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProd = $dataProd
                ->where('dg.namaproduk','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }
        if(isset($req['namaProduk']) && $req['namaProduk']!="" && $req['namaProduk']!="undefined"){
            $dataProd = $dataProd
                ->where('dg.namaproduk','ilike','%'.$req['namaProduk'].'%' );
        }

        $dataProd=$dataProd->take(10);
        $dataProd=$dataProd->get();

        return $this->respond($dataProd);
    }

    public function getIcd10(Request $request){
        $req = $request->all();
        $icdIX = \DB::table('diagnosa_m as dg')
            ->select('dg.id','dg.kddiagnosa','dg.namadiagnosa')
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosa');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $icdIX = $icdIX
                ->where('dg.namadiagnosa','ilike','%'.$req['filter']['filters'][0]['value'].'%' )
                ->orWhere('dg.kddiagnosa','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }


        $icdIX=$icdIX->take(10);
        $icdIX=$icdIX->get();

        return $this->respond($icdIX);
    }

    public function PostingJurnal_pembayaranTagihanNoBatch(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();
//        ini_set('max_execution_time', 1000); //6 minutes
        try {
            // TODO : Jurnal Penerimaan Kas
            $delMacan = DB::select(DB::raw("
                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt 
                INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.norec=pjt.norecrelated
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $kdProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' and (sbmc.totaldibayar is null or sbmc.totaldibayar = 0) and posted.nojurnal_intern is null) ")
            );
            $delMacanHead = DB::select(DB::raw("
                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt 
                INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.norec=pjt.norecrelated
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $kdProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' and (sbmc.totaldibayar is null or sbmc.totaldibayar = 0) and posted.nojurnal_intern is null)")
            );

            $aingMacan = \DB::table('strukbuktipenerimaancarabayar_t as sbmc')
                ->JOIN ('strukbuktipenerimaan_t as sbm','sbm.norec','=','sbmc.nosbmfk')
                ->leftJOIN ('strukpelayanan_t as sp','sp.norec','=','sbm.nostrukfk')
                ->leftJOIN ('pasien_m as ps','ps.id','=','sp.nocmfk')
                ->leftJOIN ('postingjurnaltransaksi_t as pjt','pjt.norecrelated','=','sbmc.norec')
                ->select('ps.nocm','ps.namapasien','sbm.nosbm','sbmc.objectcarabayarfk','sbm.tglsbm',
                    'sbmc.norec as norec_smbc','sbm.keteranganlainnya',
                    DB::raw("case when sbmc.totaldibayar is null then sbm.totaldibayar else sbmc.totaldibayar end as totaldibayar,
                to_char(sbm.tglsbm, 'YYYY-MM-DD') as tgl"))
                ->where('sbmc.kdprofile', $kdProfile)
                ->whereNull('pjt.norec')
                ->where('sbm.statusenabled',true)
                ->where('sbmc.totaldibayar','>',0)
//            ->orWhereNotNull('sbmc.totaldibayar')
                ->whereIn('sbm.nosbm', $request['nosbm'])
                ->get();

//        return $this->respond($aingMacan);
            foreach ($aingMacan as $item) {
                $nocm = $item->nocm;
                $nama = $item->namapasien;
                $totalRp = $item->totaldibayar;

                $noBuktiTransaksi = $item->nosbm;
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'KS-' . $this->getDateTime()->format('ym'));

                $nojurnal = 0;//$this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                $newPJT = new PostingJurnalTransaksi;
                $norecHead = $newPJT->generateNewId();
                $newPJT->norec = $norecHead;
                $noJurnalIntern = Carbon::parse($item->tglsbm)->format('ym') . 'KS' . Carbon::parse($item->tglsbm)->format('d') . '00001';
                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->where('kdprofile', $kdProfile)->get();

                if (count($cekSudahPosting) == 0) {
                    $newPJT->kdprofile = $kdProfile;
                    $newPJT->noposting = $noPosting;
                    $newPJT->nojurnal = $nojurnal;
                    $newPJT->nojurnal_intern = $noJurnalIntern;
                    $newPJT->objectjenisjurnalfk = 1;
                    $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                    $newPJT->tglbuktitransaksi = $item->tglsbm;// $this->getDateTime()->format('Y-m-d H:i:s');
                    $newPJT->kdproduk = null;
                    $newPJT->namaproduktransaksi = 'Pembayaran tagihan a.n ' . $nama . ' (' . $nocm . ')';
                    $newPJT->deskripsiproduktransaksi = 'penerimaan_kas';
                    $newPJT->keteranganlainnya = 'Penerimaan Kas ' . $item->tgl;;
                    $newPJT->statusenabled = 1;
                    $newPJT->norecrelated = $item->norec_smbc;
                    $newPJT->save();

                    switch ($item->objectcarabayarfk) {
                        //tunai
                        case 1:
                            $debetId = 1754;//'Kas bendahara Penerimaan'
                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
                            break;

                        //kartu kredit
                        case 2:
                            $debetId = 1779;//Piutang Kartu kredit
                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
                            break;

                        //TRANSFER BANK
                        case 3:
                            $debetId = 1754;//'Kas bendahara Penerimaan'
                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
                            break;

                        //KARTU DEBIT
                        case 4:
//                            $this->transStatus = false;
                            $debetId = 10740;//Piutang Kartu Debit
                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
                            break;

                        //DONASI
                        case 5:
                            $this->transStatus = false;
                            $debetId = 1754;//'Kas bendahara Penerimaan'
                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
                            break;

                        //MIX
                        case 6:
                            $this->transStatus = false;
                            $debetId = 1754;//'Kas bendahara Penerimaan'
                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
                            break;
                    }

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = $nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = $nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = 1;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Posting";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
//                "data" => $aingMacan,
                "count" => count($aingMacan),
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "message"  => $transMessage,
                "data" => $aingMacan,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond(array(count($aingMacan), 'as@epic'));
    }

    public function PostingJurnal_pembayaran_tagihan(Request $request) {

        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $isAktif = $this->settingDataFixed('PostingJurnal_pembayaran_tagihan_isaktif', $kdProfile);
        if ($isAktif == 0){
            return "non aktif";
        }
// return "AKTIF"
            
        $dataLogin = $request->all();
//        ini_set('max_execution_time', 1000); //6 minutes
        try {
            // TODO : Jurnal Penerimaan Kas
//            $delMacan = DB::select(DB::raw("
//                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.norec=pjt.norecrelated
//                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk and sbm.tglsbm  >'2019-01-01 00:00'
//                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                where pjt.deskripsiproduktransaksi='penerimaan_kas' and (sbmc.totaldibayar is null or sbmc.totaldibayar = 0) and posted.nojurnal_intern is null
//                and pjt.tglbuktitransaksi  >'2019-01-01 00:00') ")
//            );
//            $delMacanHead = DB::select(DB::raw("
//                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
//                INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.norec=pjt.norecrelated
//                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk and sbm.tglsbm  >'2019-01-01 00:00'
//                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
//                where pjt.deskripsiproduktransaksi='penerimaan_kas' and (sbmc.totaldibayar is null or sbmc.totaldibayar = 0) and posted.nojurnal_intern is null
//                and pjt.tglbuktitransaksi  >'2019-01-01 00:00')")
//            );

            $aingMacan = DB::select(DB::raw("select ps.nocm,ps.namapasien,sbm.nosbm,case when sbmc.totaldibayar is null then sbm.totaldibayar else sbmc.totaldibayar end as totaldibayar,sbm.tglsbm,sbmc.objectcarabayarfk ,
                    sbmc.norec as norec_smbc,sbm.keteranganlainnya,to_char(sbm.tglsbm, 'YYYY-MM-DD') as tgl,pd.objectkelompokpasienlastfk
                    from strukbuktipenerimaancarabayar_t as sbmc
                    INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk
                    INNER JOIN strukpelayanan_t as sp on sp.nosbmlastfk=sbm.norec
                    INNER JOIN pasien_m as ps on ps.id=sp.nocmfk
                    INNER JOIN pasiendaftar_t as pd on pd.norec=sp.noregistrasifk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sbmc.norec
                    where sbmc.kdprofile = $kdProfile and sbm.tglsbm BETWEEN :tglAwal and :tglAkhir and pjt.norec is null and sbm.statusenabled =true and (sbmc.totaldibayar is not null or sbmc.totaldibayar > 0)
                    and sbm.keteranganlainnya in ('Pembayaran Tagihan Pasien','Pembayaran Tagihan Non Layanan') limit 100"),
                array(
                    'tglAwal' => $request['tglAwal'],
                    'tglAkhir' => $request['tglAkhir'],
                )
            );

            foreach ($aingMacan as $item) {
                $nocm = $item->nocm;
                $nama = $item->namapasien;
                $totalRp = $item->totaldibayar;

                $noBuktiTransaksi = $item->nosbm;
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'KS-' . $this->getDateTime()->format('ym'));
                $norec_smbc = $item->norec_smbc;

//                $cekdata = DB::select(DB::raw("select * from postingjurnaltransaksi_t where norecrelated='$norec_smbc'"));
//                if(count($cekdata) > 0){
//                    $newPJT = PostingJurnalTransaksi::where('norec',$cekdata[0]->norec)->get();
//                    $deleteDetail = PostingJurnalTransaksiD::where('norecrelated',$cekdata[0]->norec)->delete();
//                }elseif(count($cekdata) > 1){
//                    $newPJT = PostingJurnalTransaksi::where('norec',$cekdata[1]->norec)->get();
//                    $deleteDetail = PostingJurnalTransaksiD::where('norecrelated',$cekdata[0]->norec)->delete();
//                    $deletePJT = PostingJurnalTransaksi::where('norec',$cekdata[0]->norec)->delete();
//                }else{
//                    $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                $newPJT = new PostingJurnalTransaksi;
                $norecHead = $newPJT->generateNewId();
                $newPJT->norec = $norecHead;
//                }
                $noJurnalIntern = Carbon::parse($item->tglsbm)->format('ym') . 'PN' . Carbon::parse($item->tglsbm)->format('d') . '00005';
                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->where('kdprofile', $kdProfile)->get();

                if (count($cekSudahPosting) == 0) {
                    $newPJT->kdprofile = $kdProfile;
                    $newPJT->noposting = $noPosting;
                    $newPJT->nojurnal = 0;//$nojurnal;
                    $newPJT->nojurnal_intern = $noJurnalIntern;
                    $newPJT->objectjenisjurnalfk = 1;
                    $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                    $newPJT->tglbuktitransaksi = $item->tglsbm;// $this->getDateTime()->format('Y-m-d H:i:s');
                    $newPJT->kdproduk = null;
                    $newPJT->namaproduktransaksi = 'Pembayaran tagihan a.n ' . $nama . ' (' . $nocm . ')';
                    $newPJT->deskripsiproduktransaksi = 'penerimaan_kas';
                    $newPJT->keteranganlainnya = 'Penerimaan Kas ' . $item->tgl;;
                    $newPJT->statusenabled = true;
                    $newPJT->norecrelated = $item->norec_smbc;
                    $newPJT->save();


                    $debetId = 11513;//'Kas BLUD di Bendahara Penerimaan'
                    $kreditId = 11543;
//                    if ($item->objectkelompokpasienlastfk == 2) {//BPJS
//                        $kreditId = 10897;
//                    }
//                    if ($item->objectkelompokpasienlastfk == 4) {//BPJS non PBI
//                        $kreditId = 10897;
//                    }
//                    if ($item->objectkelompokpasienlastfk == 1){//1	Umum/Pribadi
//                        $kreditId = 10896;
//                    }
//                    if ($item->objectkelompokpasienlastfk == 2){//2 BPJS
//                        $kreditId = 10897;
//                    }
//                    if ($item->objectkelompokpasienlastfk == 3){//3	Asuransi lain
//                        $kreditId = 10901;
//                    }
//                    if ($item->objectkelompokpasienlastfk == 5){//5	Perusahaan
//                        $kreditId = 10901;
//                    }
//                    if ($item->objectkelompokpasienlastfk == 6){//6	Perjanjian
//                        $kreditId = 10896;
//                    }
//                    if ($item->objectkelompokpasienlastfk == 7){//7	Dinas Sosial
//                        $kreditId = 10900;
//                    }
//                    switch ($item->objectcarabayarfk) {
//                        //tunai
//                        case 1:
//                            $debetId = 1754;//'Kas bendahara Penerimaan'
//                            $kreditId = 1778;
//                            break;
//
//                        //kartu kredit
//                        case 2:
//                            $debetId = 1779;
//                            $kreditId = 1778;
//                            break;
//
//                        //TRANSFER BANK
//                        case 3:
//                            $debetId = 1754;//'Kas bendahara Penerimaan'
//                            $kreditId = 1778;
//                            break;
//
//                        //KARTU DEBIT
//                        case 4:
////                            $this->transStatus = false;
//                            $debetId = 10740;//Piutang Kartu Debit
//                            $kreditId = 1778;
//                            break;
//
//                        //DONASI
//                        case 5:
//                            $this->transStatus = false;
//                            $debetId = 1754;//'Kas bendahara Penerimaan'
//                            $kreditId = 1778;
//                            break;
//
//                        //MIX
//                        case 6:
//                            $this->transStatus = false;
//                            $debetId = 1754;//'Kas bendahara Penerimaan'
//                            $kreditId = 1778;
//                            break;
//
//                    }

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = true;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = true;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();

                    $debetId = 12688;// Perubahan SAL
                    $kreditId = 11840;// Pendapatan BLUD - LRA

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = true;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = true;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Posting";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
//                "data" => $aingMacan,
                "count" => count($aingMacan),
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "message"  => $transMessage,
                "data" => $aingMacan,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond(array(count($aingMacan), 'as@epic'));
    }

    public function PostingJurnal_setoranKasir(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $isAktif = $this->settingDataFixed('PostingJurnal_setoranKasir_isaktif', $kdProfile);
        if ($isAktif == 0){
            return;
        }
        $dataLogin = $request->all();
//        ini_set('max_execution_time', 1000); //6 minutes
        try {
            // TODO : Jurnal Penerimaan Kas
            $delMacan = DB::select(DB::raw("
                  delete  from postingjurnaltransaksid_t where norecrelated in (
                    select pjt.norec from postingjurnaltransaksi_t as pjt 
                    INNER JOIN strukclosing_t as sc on sc.norec=pjt.norecrelated
                    INNER JOIN strukbuktipenerimaan_t as sbm on sbm.noclosingfk=sc.norec
                    INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.nosbmfk=sbm.norec
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $kdProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' and (sc.totaldibayar is null or sc.totaldibayar = 0) and posted.nojurnal_intern is null)")
            );
            $delMacanHead = DB::select(DB::raw("
                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt 
                    INNER JOIN strukclosing_t as sc on sc.norec=pjt.norecrelated
                    INNER JOIN strukbuktipenerimaan_t as sbm on sbm.noclosingfk=sc.norec
                    INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.nosbmfk=sbm.norec
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $kdProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' and (sc.totaldibayar is null or sc.totaldibayar = 0) and posted.nojurnal_intern is null)")
            );

            $aingMacan = DB::select(DB::raw("select sc.noclosing,ps.nocm, ps.namapasien,sbm.nosbm,case when sbmc.totaldibayar is null then sbm.totaldibayar else sbmc.totaldibayar end as totaldibayar,sbm.tglsbm,sbmc.objectcarabayarfk ,
                    sbmc.norec as norec_smbc,sbm.keteranganlainnya,to_char(sbm.tglsbm, 'YYYY-MM-DD') as tgl,lu.objectpegawaifk,pg.namalengkap as kasir,
                    sc.norec as norec_sc
                    from strukclosing_t as sc
                    INNER JOIN strukbuktipenerimaan_t as sbm on sbm.noclosingfk=sc.norec
                    INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbm.norec=sbmc.nosbmfk
                    left JOIN strukpelayanan_t as sp on sp.norec=sbm.nostrukfk
                    left JOIN pasien_m as ps on ps.id=sp.nocmfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sc.norec
                    left JOIN loginuser_s as lu on lu.id=sbm.objectpegawaipenerimafk
                    left JOIN pegawai_m as pg on pg.id=lu.objectpegawaifk
                    where sc.kdprofile = $kdProfile and sbm.tglsbm BETWEEN :tglAwal and :tglAkhir and pjt.norec is null and sbm.statusenabled <>'f' and (sbmc.totaldibayar is not null or sbmc.totaldibayar > 0)
                    and sbm.keteranganlainnya in ('Pembayaran Tagihan Pasien','Pembayaran Tagihan Non Layanan')
                    and lu.objectpegawaifk=:idKasir  "),
                array(
                    'tglAwal' => $request['tglAwal'],
                    'tglAkhir' => $request['tglAkhir'],
                    'idKasir' => $request['idKasir'],
                )
            );

            $idKasirSub = substr($request['idKasir'],0,5);
            if (strlen($idKasirSub) == 4){
                $idKasirSub = '0'.$idKasirSub ;
            }
            if (strlen($idKasirSub) == 3){
                $idKasirSub = '00'.$idKasirSub ;
            }
            if (strlen($idKasirSub) == 2){
                $idKasirSub = '000'.$idKasirSub ;
            }
            if (strlen($idKasirSub) == 1){
                $idKasirSub = '0000'.$idKasirSub ;
            }
            foreach ($aingMacan as $item) {
                $nocm = $item->nocm;
                $nama = $item->namapasien;
                $kasir = $item->kasir;
                $noClosing = $item->noclosing;

                $totalRp = $item->totaldibayar;

                $noBuktiTransaksi = $item->nosbm;
                $noPosting = '-';//$this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'KS-' . $this->getDateTime()->format('ym'));
                $norec_smbc = $item->norec_smbc;

                $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                $newPJT = new PostingJurnalTransaksi;
                $norecHead = $newPJT->generateNewId();
                $newPJT->norec = $norecHead;

                $noJurnalIntern = Carbon::parse($item->tglsbm)->format('ym') . 'SK' . Carbon::parse($item->tglsbm)->format('d') . $idKasirSub;//'00001';
                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->where('kdprofile', $kdProfile)->get();
//                return $this->respond($cekSudahPosting);
                if (count($cekSudahPosting) == 0) {
                    $newPJT->kdprofile = $kdProfile;
                    $newPJT->noposting = $noPosting;
                    $newPJT->nojurnal = $nojurnal;
                    $newPJT->nojurnal_intern = $noJurnalIntern;
                    $newPJT->objectjenisjurnalfk = 1;
                    $newPJT->nobuktitransaksi = $noBuktiTransaksi;
                    $newPJT->tglbuktitransaksi = $item->tglsbm;// $this->getDateTime()->format('Y-m-d H:i:s');
                    $newPJT->kdproduk = null;
                    $newPJT->namaproduktransaksi = 'Pembayaran tagihan a.n ' . $nama . ' (' . $nocm . ')';
                    $newPJT->deskripsiproduktransaksi = 'penerimaan_kas';
                    $newPJT->keteranganlainnya = 'Setoran Kasir ' . $kasir . ' No Closing '.$noClosing ;
                    $newPJT->statusenabled = 1;
                    $newPJT->norecrelated = $item->norec_sc;
                    $newPJT->save();

                    $kreditId = 1754;//'Kas bendahara Penerimaan'
                    $debetId = 1761; //Bank Bend. Penerimaan BRI

//                    switch ($item->objectcarabayarfk) {
//                        //tunai
//                        case 1:
//                            $debetId = 1754;//'Kas bendahara Penerimaan'
//                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
//                            break;
//
//                        //kartu kredit
//                        case 2:
//                            $debetId = 1779;//Piutang Kartu kredit
//                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
//                            break;
//
//                        //TRANSFER BANK
//                        case 3:
//                            $debetId = 1754;//'Kas bendahara Penerimaan'
//                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
//                            break;
//
//                        //KARTU DEBIT
//                        case 4:
////                            $this->transStatus = false;
//                            $debetId = 10740;//Piutang Kartu Debit
//                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
//                            break;
//
//                        //DONASI
//                        case 5:
//                            $this->transStatus = false;
//                            $debetId = 1754;//'Kas bendahara Penerimaan'
//                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
//                            break;
//
//                        //MIX
//                        case 6:
//                            $this->transStatus = false;
//                            $debetId = 1754;//'Kas bendahara Penerimaan'
//                            $kreditId = 1778;//Piutang Pasien dalam Perawatan
//                            break;
//
//                    }

                    //debet
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = $nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $debetId;
                    $postingJurnalTransaksiD->hargasatuand = $totalRp;
                    $postingJurnalTransaksiD->hargasatuank = 0;
                    $postingJurnalTransaksiD->statusenabled = true;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                    //kredit
                    $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                    $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                    $postingJurnalTransaksiD->kdprofile = $kdProfile;
                    $postingJurnalTransaksiD->nojurnal = $nojurnal;
                    $postingJurnalTransaksiD->noposting = $noPosting;
                    $postingJurnalTransaksiD->objectaccountfk = $kreditId;
                    $postingJurnalTransaksiD->hargasatuand = 0;
                    $postingJurnalTransaksiD->hargasatuank = $totalRp;
                    $postingJurnalTransaksiD->statusenabled = true;
                    $postingJurnalTransaksiD->norecrelated = $norecHead;
                    $postingJurnalTransaksiD->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Posting";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
//                "data" => $aingMacan,
                "count" => count($aingMacan),
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "message"  => $transMessage,
                "data" => $aingMacan,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function hapusJurnalSetoranKasir(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataReq = $request->all();
        $noCLosing = $dataReq['noclosing'];
        $arr = explode(',', $noCLosing);
        $hijikeunLah = '';
        foreach ($arr as $item){
            $hijikeunLah = $hijikeunLah ."','". $item;
        }
        $resultNoClos=substr($hijikeunLah,2);
        $resultNoClos = $resultNoClos ."'";
//        return $this->respond($hasilNorecSo);
        try {
            $delDetail = DB::select(DB::raw("
               delete  from postingjurnaltransaksid_t 
                    where norecrelated in 
                    (select pjt.norec 
                    from postingjurnaltransaksi_t as pjt 
                    INNER JOIN strukclosing_t as sc on sc.norec=pjt.norecrelated
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $kdProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' 
                    and posted.nojurnal_intern is null
                    and sc.noclosing in ($resultNoClos)
                  )
              ")
            );
            $delHead = DB::select(DB::raw("
                   delete  from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt 
                    INNER JOIN strukclosing_t as sc on sc.norec=pjt.norecrelated
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $kdProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' 
                    and posted.nojurnal_intern is null
                      and sc.noclosing in ($resultNoClos)
                    )
              ")
            );
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Hapus Posting';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $delHead,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $delHead,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getJenisPelayananByNorecPd($norec,Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.*')
            ->where('kdprofile',$kdProfile)
            ->where('pd.norec',$norec)
            ->first();
        return $this->respond($data);
    }
    public function getTindakanPart(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        //TODO : GET LIST TINDAKAN
        $req = $request->all();
        $ruangan = \DB::table('ruangan_m as ru')
                    ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
                    ->where('ru.kdprofile', $kdProfile)
                    ->where('ru.statusenabled', 1);
//        if (isset($req['Ruangan']) && $req['Ruangan'] != "" && $req['Ruangan'] != "undefined") {
//            $ruangan = $ruangan->where('ru.namaruangan', '=', $req['Ruangan']);
//        }
        if (isset($req['idRuangan']) && $req['idRuangan'] != "" && $req['idRuangan'] != "undefined") {
            $ruangan = $ruangan->where('ru.id', '=', $req['idRuangan']);
        }
        $ruangan = $ruangan->first();
//                    ->where('ru.namaruangan','=',$req['Ruangan'])
//                    ->first();
        $data = [];
//        return $this->respond($ruangan) ;
        if ($ruangan->objectdepartemenfk == "16"){
           $data = \DB::table('mapruangantoproduk_m as mpr')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
//            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
//            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
//            ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
//            ->select('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
//                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan',
            ->select('mpr.objectprodukfk as id','prd.namaproduk',
                'mpr.objectruanganfk',
                'prd.namaproduk'
            )
            ->where('mpr.kdprofile', $kdProfile)
            ->where('mpr.objectruanganfk',$request['idRuangan'])
//            ->where('hnp.objectkelasfk',$request['idKelas'])
//           ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
//            ->where('mpr.statusenabled',true)
//            ->where('hnp.statusenabled',true)
//            ->where('sk.statusenabled',true)
            ->where('mpr.statusenabled',true)

            // ->where('sk.statusenabled',true)
            ->where('prd.statusenabled',true)
            ->whereNotIn('prd.id', ['4041857','4041858','4041859','4041860','4041861','4041862','4041863','4041864'])
            // ->where('mpr.kodeexternal','2017')
            // ->where('hnp.kodeexternal', '2017')
            ;



            if(isset($req['filter']['filters'][0]['value']) &&
                $req['filter']['filters'][0]['value']!="" &&
                $req['filter']['filters'][0]['value']!="undefined"){
                $data = $data
                    ->where('prd.namaproduk','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
            }
            if(isset($req['idProduk']) &&
                $req['idProduk']!="" &&
                $req['idProduk']!="undefined"){
                $data = $data
                    ->where('prd.id','=',$req['idProduk']);
            }
            $data = $data->orderBy('prd.namaproduk', 'ASC');
            $data = $data->take(15);
            $data = $data->get();
                 $result = array(
                     'data' => $data,
                     'message' => 'ramdanegie',
                 );
        }else{
            $data = \DB::table('mapruangantoproduk_m as mpr')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
                ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
//            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
//            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
//            ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
//            ->select('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
//                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan',
                ->select('mpr.objectprodukfk as id','prd.namaproduk',
                    'mpr.objectruanganfk',
                    'prd.namaproduk'
                )
                ->where('mpr.kdprofile', $kdProfile)
                ->where('mpr.objectruanganfk',$request['idRuangan'])
//            ->where('hnp.objectkelasfk',$request['idKelas'])
//           ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
//            ->where('mpr.statusenabled',true)
//            ->where('hnp.statusenabled',true)
//            ->where('sk.statusenabled',true)
                ->where('mpr.statusenabled',true)

                // ->where('sk.statusenabled',true)
                ->where('prd.statusenabled',true)
//                ->whereIn('prd.id', ['4041857','4041858','4041859','4041860','4041861','4041862','4041863','4041864'])
                // ->where('mpr.kodeexternal','2017')
                // ->where('hnp.kodeexternal', '2017')
            ;



            if(isset($req['filter']['filters'][0]['value']) &&
                $req['filter']['filters'][0]['value']!="" &&
                $req['filter']['filters'][0]['value']!="undefined"){
                $data = $data
                    ->where('prd.namaproduk','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
            }
            if(isset($req['idProduk']) &&
                $req['idProduk']!="" &&
                $req['idProduk']!="undefined"){
                $data = $data
                    ->where('prd.id','=',$req['idProduk']);
            }
            $data = $data->orderBy('prd.namaproduk', 'ASC');
            $data = $data->take(15);
            $data = $data->get();
//            return $this->respond($data) ;
            $result = array(
                'data' => $data,
                'message' => 'ramdanegie',
            );
        }
        return $this->respond($data);
    }
    public function getKomponenHarga(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        //TODO :GET HARGA & HARGA KOMPONEN
//        $dataLogin = $request->all();
        $data = \DB::table('harganettoprodukbykelasd_m as hnp')
            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
            ->join ('komponenharga_m as kh','kh.id','=','hnp.objectkomponenhargafk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
            ->select('hnp.objectkomponenhargafk','kh.komponenharga','hnp.hargasatuan','mpr.objectprodukfk','kh.iscito')
            ->where('hnp.kdprofile', $kdProfile)
            ->where('mpr.objectruanganfk',$request['idRuangan'])
            ->where('hnp.objectkelasfk',$request['idKelas'])
            ->where('mpr.objectprodukfk',$request['idProduk'])
            ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
            ->where('mpr.statusenabled',true)
            ->where('hnp.statusenabled',true)
            // ->where('sk.statusenabled',true)
            ->where('prd.statusenabled',true)
//            ->where('mpr.statusenabled',true)
//            ->where('hnp.statusenabled',true)
//            ->where('prd.statusenabled',true)
            // ->where('mpr.kodeexternal','2017')
//            ->where('hnp.kodeexternal', '2017')
//            ->where('prd.kodeexternal','2017')
        ;

//        $data = $data->groupBy('prd.namaproduk','hnp.objectkomponenhargafk','kh.komponenharga','hnp.hargasatuan','mpr.objectprodukfk');
        $data = $data->distinct();
        $data = $data->get();


        $data2 = \DB::table('harganettoprodukbykelas_m as hnp')
            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
            ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
            ->select('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan',
                'prd.namaproduk'
            )


            ->where('hnp.kdprofile', $kdProfile)
            ->where('mpr.objectruanganfk',$request['idRuangan'])
            ->where('hnp.objectkelasfk',$request['idKelas'])
            ->where('mpr.objectprodukfk',$request['idProduk'])
            ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
            ->where('hnp.statusenabled',true)
            ->where('sk.statusenabled',true)
            ->where('mpr.statusenabled',true)
            ->where('prd.statusenabled',true);
        $data2 = $data2->distinct();
        $data2 = $data2->get();


        $result = array(
            'data' => $data,
            'data2' => $data2,
            'message' => 'ramdanegie',
            'edited' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getKomponenHargaPaket(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        //TODO :GET HARGA & HARGA KOMPONEN
//        $dataLogin = $request->all();
        $data = \DB::table('harganettoprodukbykelasd_m as hnp')
//            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','hnp.objectprodukfk')
            ->join ('komponenharga_m as kh','kh.id','=','hnp.objectkomponenhargafk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
//            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
            ->select('hnp.objectkomponenhargafk','kh.komponenharga','hnp.hargasatuan','hnp.objectprodukfk','kh.iscito')
            ->where('hnp.kdprofile', $kdProfile)
//            ->where('mpr.objectruanganfk',$request['idRuangan'])
            ->where('hnp.objectkelasfk',$request['idKelas'])
            ->where('prd.id',$request['idProduk'])
            ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
//            ->where('mpr.statusenabled',true)
            ->where('hnp.statusenabled',true)
            // ->where('sk.statusenabled',true)
            ->where('prd.statusenabled',true);

        $data = $data->distinct();
        $data = $data->get();


        $data2 = \DB::table('harganettoprodukbykelas_m as hnp')
//            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','hnp.objectprodukfk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
//            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
            ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
            ->select('prd.id','prd.id as objectprodukfk','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
                'kls.namakelas',
                'prd.namaproduk'
            )

            ->where('hnp.kdprofile', $kdProfile)

//            ->where('mpr.objectruanganfk',$request['idRuangan'])
            ->where('hnp.objectkelasfk',$request['idKelas'])
            ->where('prd.id',$request['idProduk'])
            ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
            ->where('hnp.statusenabled',true)
            ->where('sk.statusenabled',true)
//            ->where('mpr.statusenabled',true)
            ->where('prd.statusenabled',true);
        $data2 = $data2->distinct();
        $data2 = $data2->get();


        $result = array(
            'data' => $data,
            'data2' => $data2,
            'message' => 'ramdanegie',
            'edited' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getAccNumberRadiologi( Request $request) {
        $data = \DB::table('ris_order as ro')
            ->select('ro.*')
            ->where('ro.order_no',$request['noOrder']);

        $data=$data->get();

        $result = array(
            'data' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function getDataProduk(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->select('pr.id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar','pr.kdproduk')
            ->where('pr.kdprofile', $kdProfile)
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
            ->where('ks.kdprofile', $kdProfile)
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
    public function getDiagnosaKeperawatan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('diagnosakeperawatan_m as dp')
            ->select('dp.id','dp.namadiagnosakep as namaDiagnosaKep','dp.kodeexternal','dp.diagnosakep','dp.deskripsidiagnosakep')
            ->where('dp.kdprofile', $kdProfile)
            ->where('dp.statusenabled',true);
        if(isset($request['id']) && $request['id']!=''){
            $data = $data->where('dp.id',$request['id'] );
        }

        if(isset($request['namadiagnosakep']) && $request['namadiagnosakep']!=''){
            $data = $data->where('dp.namadiagnosakep', 'ilike','%'.$request['namadiagnosakep'].'%' );
        }
        if(isset($request['kodeexternal']) && $request['kodeexternal']!=''){
            $data = $data->where('dp.kodeexternal', 'ilike','%'.$request['kodeexternal'].'%' );
        }
        $data = $data->orderBy('dp.namadiagnosakep');
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function postMasterDiagnosaKeperawatan( $method,Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            if ($method == 'save'){
                if ($request['id']==''){
                    $RekamMedis = new DiagnosaKeperawatan();
                    $RekamMedis->id = DiagnosaKeperawatan::max('id') + 1;
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $kdProfile;
                    $RekamMedis->statusenabled = true;
                    $kode = DiagnosaKeperawatan::max('kodeexternal');
                    $sub = substr($kode,7,3);
                    $kodeEx = $sub + 1;
                    $kodeEx = substr($kode,0,7).$kodeEx;
                    $RekamMedis->kodeexternal = $kodeEx;
                }else{
                    $RekamMedis= DiagnosaKeperawatan::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();
                }
                $RekamMedis->deskripsidiagnosakep =$request['deskripsidiagnosakep'];
                $RekamMedis->reportdisplay = $request['namadiagnosakep'];
                $RekamMedis->namadiagnosakep =  $request['namadiagnosakep'];
                $RekamMedis->save();

            }
            if ($method == 'delete') {
                DiagnosaKeperawatan::where('id',$request['id'])->where('kdprofile', $kdProfile)->update(
                    [ 'statusenabled' => false ]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function postDetailDiagnoaKep(Request $request,$table, $method) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            if ($method == 'save'){
                if($table == 'intervensi'){
                    if ($request['id']==''){
                        $RekamMedis = new Intervensi();
                        $RekamMedis->id = Intervensi::max('id') + 1;
                        $RekamMedis->norec = $RekamMedis->generateNewId();
                        $RekamMedis->kdprofile = $kdProfile;
                        $RekamMedis->statusenabled = true;
                        $kode = Intervensi::max('kodeexternal');
                        $sub = substr($kode,7,3);
                        $kodeEx = $sub + 1;
                        $kodeEx = substr($kode,0,7).$kodeEx;
                        $RekamMedis->kodeexternal = $kodeEx;
                    }else{
                        $RekamMedis= Intervensi::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();
                    }
                    $RekamMedis->name =$request['name'];
                    $RekamMedis->objectdiagnosakeperawatanfk = $request['objectdiagnosakeperawatanfk'];
                    $RekamMedis->reportdisplay =  $request['name'];
                    $RekamMedis->save();
                }

                if($table == 'implementasi'){
                    if ($request['id']==''){
                        $RekamMedis = new Implementasi();
                        $RekamMedis->id = Implementasi::max('id') + 1;
                        $RekamMedis->norec = $RekamMedis->generateNewId();
                        $RekamMedis->kdprofile = $kdProfile;
                        $RekamMedis->statusenabled = true;
                        $kode = Implementasi::max('kodeexternal');
                        $sub = substr($kode,7,3);
                        $kodeEx = $sub + 1;
                        $kodeEx = substr($kode,0,7).$kodeEx;
                        $RekamMedis->kodeexternal = $kodeEx;
                    }else{
                        $RekamMedis= Implementasi::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();
                    }
                    $RekamMedis->name =$request['name'];
                    $RekamMedis->objectdiagnosakeperawatanfk = $request['objectdiagnosakeperawatanfk'];
                    $RekamMedis->reportdisplay =  $request['name'];
                    $RekamMedis->save();
                }
                if($table == 'evaluasi'){
                    if ($request['id']==''){
                        $RekamMedis = new Evaluasi();
                        $RekamMedis->id = Evaluasi::max('id') + 1;
                        $RekamMedis->norec = $RekamMedis->generateNewId();
                        $RekamMedis->kdprofile = $kdProfile;
                        $RekamMedis->statusenabled = true;
                        $kode = Evaluasi::max('kodeexternal');
                        $sub = substr($kode,7,3);
                        $kodeEx = $sub + 1;
                        $kodeEx = substr($kode,0,7).$kodeEx;
                        $RekamMedis->kodeexternal = $kodeEx;
                    }else{
                        $RekamMedis= Evaluasi::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();
                    }
                    $RekamMedis->name =$request['name'];
                    $RekamMedis->objectdiagnosakeperawatanfk = $request['objectdiagnosakeperawatanfk'];
                    $RekamMedis->reportdisplay =  $request['name'];
                    $RekamMedis->save();
                }

            }
            if ($method == 'delete') {
                if($table == 'intervensi') {
                    Intervensi::where('id', $request['id'])
                        ->where('kdprofile', $kdProfile)
                        ->update(
                        ['statusenabled' => false]
                    );
                }

                if($table == 'implementasi') {
                    Implementasi::where('id', $request['id'])
                        ->where('kdprofile', $kdProfile)
                        ->update(
                        ['statusenabled' => false]
                    );
                }
                if($table == 'evaluasi'){
                    Evaluasi::where('id', $request['id'])
                        ->where('kdprofile', $kdProfile)
                        ->update(
                        ['statusenabled' => false]
                    );
                }
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getRuanganPart(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $data  = \DB::table('ruangan_m as st')
            ->select('st.id','st.namaruangan')
            ->where('st.kdprofile', $kdProfile)
            ->where('st.statusenabled',true)
            ->orderBy('st.namaruangan');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data->where('st.namaruangan','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        if(isset($req['namaruangan']) && $req['namaruangan']!="" && $req['namaruangan']!="undefined"){
            $data = $data
                ->where('st.namaruangan','ilike','%'.$req['namaruangan'].'%' );
        }
        if(isset($req['idRuangan']) && $req['idRuangan']!="" && $req['idRuangan']!="undefined"){
            $data = $data
                ->where('st.id','=',$req['idRuangan'] );
        }
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getComboPegawai(Request $request){
//        $kdJenisPegawaiDokter = $this->settingDataFixed('kdJenisPegawaiDokter');
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req = $request->all();
        $data = \DB::table('pegawai_m')
            ->select('id','namalengkap')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled', true)
//            ->where('objectjenispegawaifk',$kdJenisPegawaiDokter)
            ->orderBy('namalengkap');

        if(isset($req['namalengkap']) &&
            $req['namalengkap']!="" &&
            $req['namalengkap']!="undefined"){
            $data = $data->where('namalengkap','ilike','%'. $req['namalengkap'] .'%' );
        };
        if(isset($req['idpegawai']) &&
            $req['idpegawai']!="" &&
            $req['idpegawai']!="undefined"){
            $data = $data->where('id', $req['idpegawai'] );
        };
        if(isset($req['objectjenispegawaifk']) &&
            $req['objectjenispegawaifk']!="" &&
            $req['objectjenispegawaifk']!="undefined"){
            $data = $data->where('objectjenispegawaifk', $req['objectjenispegawaifk'] );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data
                ->where('namalengkap','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }

//        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }
	
    public function getPartIcd9(Request $request)
    {
        $req = $request->all();
        $icdIX = \DB::table('diagnosatindakan_m as dg')
            ->select('dg.id','dg.kddiagnosatindakan','dg.namadiagnosatindakan')
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosatindakan');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $icdIX = $icdIX
                ->where('dg.namadiagnosatindakan','ilike','%'.$req['filter']['filters'][0]['value'].'%' )
                ->orWhere('dg.kddiagnosatindakan','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }


        $icdIX=$icdIX->take(10);
        $icdIX=$icdIX->get();

        return $this->respond($icdIX);
    }

    public function getPartIcd10(Request $request){
        $req = $request->all();
        $icdIX = \DB::table('diagnosa_m as dg')
            ->select('dg.id','dg.kddiagnosa','dg.namadiagnosa')
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosa');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $icdIX = $icdIX
                ->where('dg.namadiagnosa','ilike','%'.$req['filter']['filters'][0]['value'].'%' )
                ->orWhere('dg.kddiagnosa','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }


        $icdIX=$icdIX->take(10);
        $icdIX=$icdIX->get();

        return $this->respond($icdIX);
    }

	public function getTerbilangGeneral($number){
        $terbilang = $this->makeTerbilang($number);
        return $this->respond(array('terbilang' => $terbilang));
    }

    public function getPaketTindakan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('paket_m as mpr')
            ->select('mpr.id', 'mpr.namapaket','mpr.harga' )
            ->where('mpr.kdprofile', $kdProfile)
            ->where('mpr.statusenabled',true)
            ->orderBy('mpr.namapaket', 'ASC')
            ->get();

        $data2 =[];
        foreach ($data as $item){
            $idPaket = $item->id;
            $details = DB::select(DB::raw("select 
                    maps.id,prd.namaproduk, maps.objectprodukfk
                    from mappakettoproduk_m as maps
                    join produk_m as prd on prd.id =maps.objectprodukfk
                   -- join mapruangantoproduk_m as mpr on mpr.objectprodukfk = prd.id
                    where maps.kdprofile = $kdProfile and maps.objectpaketfk='$idPaket'
                    and maps.statusenabled = true"));
            if(count($details) > 0){
                $data2 [] = array(
                    'id'=>   $item->id,
                    'namapaket'=>   $item->namapaket,
                    'jml' => count($details),
                    'hargapaket' => $item->harga == null ? 0 : (float) $item->harga,
                    'details' => $details
                ) ;
            }

        }

        return $this->respond($data2);
    }

    public function getComboRegGeneral(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('lu.kdprofile', $kdProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        $jk = JenisKelamin::where('statusenabled',true)
            ->select(DB::raw("id, UPPER(jeniskelamin) as jeniskelamin"))
            ->where('kdprofile', $kdProfile)
            ->get();

        $agama = Agama::where('statusenabled',true)
            ->select(DB::raw("id, UPPER(agama) as agama"))
            ->where('kdprofile', $kdProfile)
            ->get();

        $statusPerkawinan = StatusPerkawinan::where('statusenabled',true)
            ->select(DB::raw("id, UPPER(statusperkawinan) as statusperkawinan"))
            ->get();

        $pendidikan = Pendidikan::where('statusenabled',true)
            ->select(DB::raw("id, UPPER(pendidikan) as pendidikan"))
            ->where('kdprofile', $kdProfile)
            ->get();

        $pekerjaan = DB::table('pekerjaan_m')
            ->select(DB::raw("id, UPPER(pekerjaan) as pekerjaan"))
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->get();

        $gd = DB::table('golongandarah_m')
            ->select(DB::raw("id, UPPER(golongandarah) as golongandarah"))
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->get();

        $suku = DB::table('suku_m')
            ->select(DB::raw("id, UPPER(suku) as suku"))
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->get();

        $HubunganKeluarga = DB::table('hubungankeluarga_m')
            ->select(DB::raw("id, UPPER(hubungankeluarga) as hubungankeluarga"))
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->get();

        $JenisAlamat = DB::table('jenisalamat_m')
            ->select(DB::raw("id, UPPER(jenisalamat) as jenisalamat"))
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->get();

        $PengantarPasien = DB::table('jenispengantarpasien_m')
            ->select(DB::raw("id, UPPER(jenispengantar) as jenispengantar"))
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->get();

        $KelompokPasien = DB::table('kelompokpasien_m')
                        ->select('id','kelompokpasien')
                        ->where('statusenabled', true)
                        ->where('kdprofile', $kdProfile)
                        ->get();

        $result = array(
            'jeniskelamin' => $jk,
            'agama' => $agama,
            'statusperkawinan' => $statusPerkawinan,
            'pendidikan' => $pendidikan,
            'pekerjaan' => $pekerjaan,
            'pegawaiLogin' => $dataPegawai->namalengkap,
            'golongandarah' => $gd,
            'suku' => $suku,
            'hubungankeluarga' => $HubunganKeluarga,
            'jenisalamat' => $JenisAlamat,
            'pengantar' => $PengantarPasien,
            'kelompokpasien' => $KelompokPasien,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getPsnByNoCmGeneral(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasien_m as ps')
            ->leftJOIN ('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin ('pendidikan_m as pdd','ps.objectpendidikanfk','=','pdd.id')
            ->leftjoin ('pekerjaan_m as pk','ps.objectpekerjaanfk','=','pk.id')
            ->leftjoin ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin ('agama_m as agm','agm.id','=','ps.objectagamafk')
            ->leftjoin ('statusperkawinan_m as sp','sp.id','=','ps.objectstatusperkawinanfk')
            ->leftjoin ('kebangsaan_m as kb','kb.id','=','ps.objectkebangsaanfk')
            ->leftjoin ('negara_m as ng','ng.id','=','alm.objectnegarafk')
            ->leftjoin ('desakelurahan_m as dsk','dsk.id','=','alm.objectdesakelurahanfk')
            ->leftjoin ('kecamatan_m as kcm','kcm.id','=','alm.objectkecamatanfk')
            ->leftjoin ('kotakabupaten_m as kkb','kkb.id','=','alm.objectkotakabupatenfk')
            ->leftjoin ('propinsi_m as prp','prp.id','=','alm.objectpropinsifk')
            ->leftjoin ('pekerjaan_m as pk1','ps.pekerjaanpenangggungjawab','=','pk1.pekerjaan')
            ->leftjoin ('suku_m as sk','sk.id','=','ps.objectsukufk')
            ->leftjoin ('golongandarah_m as gol','gol.id','=','ps.objectgolongandarahfk')
            ->select('ps.nocm','ps.id as nocmfk','ps.namapasien','ps.tgllahir','ps.tempatlahir',
                'ps.objectjeniskelaminfk','jk.jeniskelamin','ps.objectagamafk','agm.agama','ps.objectstatusperkawinanfk',
                'sp.statusperkawinan','ps.objectpendidikanfk','pdd.pendidikan','ps.objectpekerjaanfk','pk.pekerjaan',
                'ps.objectkebangsaanfk','kb.name as kebangsaan','alm.objectnegarafk','ng.namanegara','ps.noidentitas',
                'ps.nobpjs','ps.noasuransilain','alm.alamatlengkap','alm.kodepos','alm.objectdesakelurahanfk','dsk.namadesakelurahan',
                'alm.objectkecamatanfk','kcm.namakecamatan','alm.objectkotakabupatenfk','kkb.namakotakabupaten',
                'alm.objectpropinsifk','prp.namapropinsi','ps.notelepon','ps.nohp','ps.namaayah','ps.namaibu',
                'ps.namakeluarga','ps.namasuamiistri','ps.penanggungjawab','ps.hubungankeluargapj','ps.pekerjaanpenangggungjawab',
                'ps.ktppenanggungjawab','ps.alamatrmh','ps.alamatktr','pk1.id as idpek','ps.objectgolongandarahfk','gol.golongandarah','ps.objectsukufk','sk.suku')
            ->where('ps.kdprofile',$kdProfile);
        if(isset($request['noCm'] ) && $request['noCm']!= '' && $request['noCm']!= 'undefined'){
            $data= $data->where('ps.nocm', $request['noCm']);
        }
        if(isset($request['idPasien'] ) && $request['idPasien']!= '' && $request['idPasien']!= 'undefined'){
            $data= $data->where('ps.id', $request['idPasien']);
        }

        $data=$data->first();
        $dt = array(
            'nocm' => $data->nocm,
            'nocmfk' => $data->nocmfk,
            'namapasien' => $data->namapasien,
            'tgllahir' => $data->tgllahir,
            'tempatlahir' => $data->tempatlahir,
            'objectjeniskelaminfk' => $data->objectjeniskelaminfk,
            'jeniskelamin' => $data->jeniskelamin,
            'objectagamafk' => $data->objectagamafk,
            'agama' => $data->agama,
            'objectstatusperkawinanfk' => $data->objectstatusperkawinanfk,
            'statusperkawinan' => $data->statusperkawinan,
            'objectpendidikanfk' => $data->objectpendidikanfk,
            'pendidikan' => $data->pendidikan,
            'objectpekerjaanfk' => $data->objectpekerjaanfk,                'pekerjaan' => $data->pekerjaan,
            'objectkebangsaanfk' => $data->objectkebangsaanfk,                'kebangsaan' => $data->kebangsaan,
            'objectnegarafk' => $data->objectnegarafk,                'namanegara' => $data->namanegara,
            'noidentitas' => $data->noidentitas,                'nobpjs' => $data->nobpjs,
            'noasuransilain' => $data->noasuransilain,                'alamatlengkap' => $data->alamatlengkap,
            'kodepos' => $data->kodepos,                'objectdesakelurahanfk' => $data->objectdesakelurahanfk,
            'namadesakelurahan' => $data->namadesakelurahan,                'objectkecamatanfk' => $data->objectkecamatanfk,
            'namakecamatan' => $data->namakecamatan,                'objectkotakabupatenfk' => $data->objectkotakabupatenfk,
            'namakotakabupaten' => $data->namakotakabupaten,
            'objectpropinsifk' => $data->objectpropinsifk,
            'namapropinsi' => $data->namapropinsi,
            'notelepon' => $data->notelepon,'nohp' => $data->nohp,
            'namaayah' => $data->namaayah,'namaibu' => $data->namaibu,
            'namakeluarga' => $data->namakeluarga,'namasuamiistri' => $data->namasuamiistri,'penanggungjawab' => $data->penanggungjawab,'hubungankeluargapj' => $data->hubungankeluargapj,
            'pekerjaanpenangggungjawab' => $data->pekerjaanpenangggungjawab,'ktppenanggungjawab' => $data->ktppenanggungjawab,'penanggungjawab' => $data->penanggungjawab,'alamatrmh' => $data->alamatrmh,
            'alamatktr' => $data->alamatktr,'idpek' => $data->idpek,
            'suku' => $data->suku,
            'objectsukufk' => $data->objectsukufk,
            'golongandarah' => $data->golongandarah,
            'objectgolongandarahfk' => $data->objectgolongandarahfk,
        );
//        }

        $result = array(
            'data'=> $dt,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getRuangan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $deptJalan = explode (',',$this->settingDataFixed('kdDepartemenRawatJalanFix',  $kdProfile ));
        $kdDepartemenRawatJalan = [];
        foreach ($deptJalan as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }
        $dpGD = explode (',',$this->settingDataFixed('KdDepartemenInstalasiGawatDarurat',  $kdProfile ));
        foreach ($dpGD as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }
        $ruangan = DB::table('ruangan_m')
            ->select('id','namaruangan','objectdepartemenfk')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->whereIn('objectdepartemenfk',$kdDepartemenRawatJalan);
//        if(isset($request['departemenfk']) && $request['departemenfk']!=''){
//            $ruangan = $ruangan->where('objectdepartemenfk',$request['departemenfk']);
//        }
        $ruangan=$ruangan->get();


        return $this->respond($ruangan);
    }
    public function getComboAkomodasi(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idDepRawatInap = (int) $this->settingDataFixed('idDepRawatInap', $kdProfile);
//        if ($request['ruangan']==1) {
            $dataRuanganInap = \DB::table('ruangan_m as ru')
                ->select('ru.id', 'ru.namaruangan')
                ->where('ru.objectdepartemenfk', $idDepRawatInap)
                ->where('ru.statusenabled', true)
                ->where('ru.kdprofile', $kdProfile)
                ->orderBy('ru.namaruangan')
                ->get();
//        }

        $dataProduk=[];
        $datalistAkomodasi=[];
        if ($request['produk']==1){
            $dataProduk = \DB::table('produk_m as pr')
                ->JOIN('mapruangantoproduk_m as ma','ma.objectprodukfk','=','pr.id')
                ->select('pr.id','pr.namaproduk')
                ->where('pr.statusenabled',true)
                ->where('pr.kdprofile', $kdProfile)
                ->orderBy('pr.namaproduk');
            if(isset($request['objectruanganfk']) && $request['objectruanganfk']!="" && $request['objectruanganfk']!="undefined"){
                $dataProduk = $dataProduk->where('ma.objectruanganfk','=', $request['objectruanganfk']);
            }
            $dataProduk->where('pr.namaproduk','ilike','%akomodasi%');
            $dataProduk = $dataProduk->get();

            $datalistAkomodasi = \DB::table('produk_m as pr')
                ->JOIN('mapruangantoakomodasi_t as ma','ma.objectprodukfk','=','pr.id')
                ->select('pr.id','pr.namaproduk','ma.israwatgabung','ma.id as maid','ma.statusenabled')
                ->where('pr.kdprofile', $kdProfile)
                ->where('pr.statusenabled',true)
                ->where('ma.statusenabled',true)
                ->orderBy('pr.namaproduk');
            if(isset($request['objectruanganfk']) && $request['objectruanganfk']!="" && $request['objectruanganfk']!="undefined"){
                $datalistAkomodasi = $datalistAkomodasi->where('ma.objectruanganfk','=', $request['objectruanganfk']);
            }
            $datalistAkomodasi = $datalistAkomodasi->get();
        }

        $result = array(
            'produk' => $dataProduk,
            'ruangan' => $dataRuanganInap,
            'listakomodasi' => $datalistAkomodasi,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function saveMappingAkomodasiCuy(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = $request->all();
        try {
            if ($data['status'] == 'HAPUS') {
                $newKS = MapRuanganToAkomodasi::where('id', $request['maid'])->where('kdprofile', $kdProfile)->delete();

            }else {
                if ($data['maid'] == '') {
                    if ($data['rg'] == 'YES'){
                        $RG =1;
                    }else{
                        $RG =null;
                    }
                    $newKS = new MapRuanganToAkomodasi();
                    $norecKS = MapRuanganToAkomodasi::max('id');
                    $norecKS = $norecKS + 1;
                    $newKS->id = $norecKS;
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $kdProfile;
                    $newKS->statusenabled = true;
                    $newKS->objectprodukfk = $data['pelayanan'];
                    $newKS->objectruanganfk = $data['ruangan'];
                    $newKS->israwatgabung = $RG;

                    $newKS->save();
                } else {
                    if ($data['rg'] == 'YES'){
                        $newKS = MapRuanganToAkomodasi::where('id', $request['maid'])
                            ->where('kdprofile', $kdProfile)
                            ->update([
                                    'objectruanganfk' => $data['ruangan'],
                                    'israwatgabung' => 1,
                                ]
                            );
                    }else{
                        $newKS = MapRuanganToAkomodasi::where('id', $request['maid'])
                            ->where('kdprofile', $kdProfile)
                            ->update([
                                    'objectruanganfk' => $data['ruangan'],
                                    'israwatgabung' => null,
                                ]
                            );
                    }

//                    $newKSasas = MapRuanganToAkomodasi::where('id', $request['maid'])->first();
                }

            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Simpan Map";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $newKS,
                "as" => 'as@epic',
            );
        } else {
            $transMessage =  $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noresep" => $newKS,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getComboRuanganGeneral(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', $kdProfile)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $kdProfile)
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

        $result = array(
            'departemen' => $dataDepartemen,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getcomboDokterPart(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $kdJeniPegawaiDokter = (int) $this->settingDataFixed('KdJenisPegawaiDokter',$kdProfile);
        $req = $request->all();
        $data = \DB::table('pegawai_m')
            ->select('id','namalengkap')
            ->where('statusenabled', true)
            ->where('kdprofile', $kdProfile)
            ->where('objectjenispegawaifk',$kdJeniPegawaiDokter)
            ->orderBy('namalengkap');

        if(isset($req['namalengkap']) &&
            $req['namalengkap']!="" &&
            $req['namalengkap']!="undefined"){
            $data = $data->where('namalengkap','ilike','%'. $req['namalengkap'] .'%' );
        };
        if(isset($req['idpegawai']) &&
            $req['idpegawai']!="" &&
            $req['idpegawai']!="undefined"){
            $data = $data->where('id', $req['idpegawai'] );
        };
        if(isset($req['objectjenispegawaifk']) &&
            $req['objectjenispegawaifk']!="" &&
            $req['objectjenispegawaifk']!="undefined"){
            $data = $data->where('objectjenispegawaifk', $req['objectjenispegawaifk'] );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data
                ->where('namalengkap','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }

//        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }
    public function getDiagnosaPasien( Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.noregistrasi','pd.tglregistrasi','apd.objectruanganfk','ru.namaruangan','apd.norec as norec_apd',
                'ddp.objectdiagnosafk','dg.kddiagnosa','dg.namadiagnosa','ddp.tglinputdiagnosa','ddp.objectjenisdiagnosafk',
                'jd.jenisdiagnosa','dp.norec as norec_diagnosapasien','ddp.norec as norec_detaildpasien','ddp.tglinputdiagnosa',
                'pg.namalengkap',
                'dp.ketdiagnosis','ddp.keterangan','dg.*','dp.iskasusbaru','dp.iskasuslama')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join ('diagnosapasien_t as dp','dp.noregistrasifk','=','apd.norec')
            ->join ('detaildiagnosapasien_t as ddp','ddp.objectdiagnosapasienfk','=','dp.norec')
            ->join ('diagnosa_m as dg','dg.id','=','ddp.objectdiagnosafk')
            ->join ('jenisdiagnosa_m as jd','jd.id','=','ddp.objectjenisdiagnosafk')
            ->leftjoin('pegawai_m as pg','pg.id','=','ddp.objectpegawaifk')
            ->where('pd.kdprofile', $kdProfile);
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };

        $data=$data->get();

        $result = array(
            'datas' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function getDataProdukDetail(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->JOIN('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->leftJOIN('stokprodukdetail_t as spd', 'spd.objectprodukfk', '=', 'pr.id')
            ->select('pr.id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar','pr.spesifikasi')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled', true)
            ->where('kp.id', $request['idkelompokproduk'])
//            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id', 'pr.namaproduk', 'ss.id', 'ss.satuanstandar','pr.spesifikasi')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss', 'ss.id', '=', 'ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2', 'ss2.id', '=', 'ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk', 'ks.satuanstandar_asal', 'ss.satuanstandar', 'ks.satuanstandar_tujuan', 'ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kdprofile', $kdProfile)
            ->where('ks.statusenabled', true)
            ->get();

        $dataProdukResult=[];
        foreach ($dataProduk as $item) {
            $satuanKonversi = [];
            foreach ($dataKonversiProduk as $item2) {
                if ($item->id == $item2->objekprodukfk) {
                    $satuanKonversi[] = array(
                        'ssid' => $item2->satuanstandar_tujuan,
                        'satuanstandar' => $item2->satuanstandar2,
                        'nilaikonversi' => $item2->nilaikonversi,
                    );
                }
            }

            $dataProdukResult[] = array(
                'id' => $item->id,
                'namaproduk' => $item->namaproduk,
                'ssid' => $item->ssid,
                'satuanstandar' => $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
                'spesifikasi' => $item->spesifikasi
            );
        }

        $result = array(
            'produk' => $dataProdukResult,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDataComboDepartemenGeneral(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk  = \DB::table('departemen_m')
            ->select('id as value','namadepartemen as text','namadepartemen')
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->orderBy('namadepartemen');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('namadepartemen','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(20);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDetailPasienGeneral(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasien_m as ps')
            ->leftJOIN('alamat_m as al','al.nocmfk','=','ps.id')
            ->leftJOIN('pekerjaan_m as pkr','pkr.id','=','ps.objectpekerjaanfk')
            ->leftJOIN('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->select('ps.nocm','ps.namapasien','ps.notelepon','ps.tgllahir','al.alamatlengkap','pkr.id as pekerjaanid','pkr.pekerjaan','jk.id as jkid',
                'jk.jeniskelamin','al.alamatemail')
            ->where('ps.kdprofile', $kdProfile)
            ->where('ps.statusenabled',true);
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm', $request['nocm'] );
        };
        $data = $data->first();
        return $this->respond($data);
    }
    public function getDataComboHandHygieneGeneral(Request $request) {
        $req=$request->all();
        $dataProduk  = \DB::table('handhygiene_m')
            ->select('id as value','tindakan as text','tindakan')
            ->where('statusenabled',true)
            ->orderBy('tindakan');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('tindakan','like','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }
    public function getDataComboIndikasiGeneral(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $req=$request->all();
        $dataProduk  = \DB::table('indikasi_m')
            ->select('id as value','indikasi as text','indikasi')
            ->where('statusenabled',true)
            ->orderBy('id');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('indikasi','like','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }
    public function getDataComboJenisPegawaiGeneral(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk  = \DB::table('jenispegawai_m')
            ->select('id as value','jenispegawai as text','jenispegawai')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('jenispegawai');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('jenispegawai','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataComboJenisPegawaiCPPT(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdListJenisPegawai = explode(',',$this->settingDataFixed('KdListJenisPegawai', $kdProfile));
        $listtKdListJenisPegawai = [];
        foreach ($KdListJenisPegawai as $items){
            $listtKdListJenisPegawai [] = (int) $items;
        }
        $req=$request->all();
        $dataProduk  = \DB::table('jenispegawai_m')
            ->select('id as value','jenispegawai as text','jenispegawai')
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->whereIn('id', $KdListJenisPegawai)
            ->orderBy('jenispegawai', 'desc');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('jenispegawai','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getAPD(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->select('apd.norec as norec_apd', 'ps.nocm', 'ps.id as nocmfk', 'ps.namapasien', 'pd.noregistrasi',
                'apd.objectruanganfk as id','ru.objectdepartemenfk',
                'ru.namaruangan', 'apd.tglregistrasi', 'kls.namakelas', 'apd.objectruanganasalfk')
            ->where('apd.kdprofile', $idProfile)
            ->where('pd.noregistrasi', $request['noregistrasi'])
            ->orderBy('pd.objectruanganlastfk')
            ->get();

        $result = array(
            'ruangan' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }

    public function getDiagnosaPasienGen( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.noregistrasi','pd.tglregistrasi','apd.objectruanganfk','ru.namaruangan','apd.norec as norec_apd',
                'ddp.objectdiagnosafk','dg.kddiagnosa','dg.namadiagnosa','ddp.tglinputdiagnosa','ddp.objectjenisdiagnosafk',
                'jd.jenisdiagnosa','dp.norec as norec_diagnosapasien','ddp.norec as norec_detaildpasien','ddp.tglinputdiagnosa',
                'pg.namalengkap',
                'dp.ketdiagnosis','ddp.keterangan','dg.*','dp.iskasusbaru','dp.iskasuslama')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join ('diagnosapasien_t as dp','dp.noregistrasifk','=','apd.norec')
            ->join ('detaildiagnosapasien_t as ddp','ddp.objectdiagnosapasienfk','=','dp.norec')
            ->join ('diagnosa_m as dg','dg.id','=','ddp.objectdiagnosafk')
            ->join ('jenisdiagnosa_m as jd','jd.id','=','ddp.objectjenisdiagnosafk')
            ->leftjoin('pegawai_m as pg','pg.id','=','ddp.objectpegawaifk')
            ->where('pd.kdprofile',$idProfile);
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };

        $data=$data->get();

        $result = array(
            'datas' => $data,
            'message' => 'giw',
        );
        return $this->respond($result);
    }

    public function getPasienByNoreg(Request $request){
        $norec_pd = $request['norec_pd'];
        $norec_apd = $request['norec_apd'];
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.objectpekerjaanfk')
            ->leftjoin('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftjoin('pegawai_m as peg','peg.id','=','pd.objectpegawaifk')
            ->join('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->LEFTjoin('jenispelayanan_m as jpl','jpl.kodeinternal','=','pd.jenispelayanan')
            ->select('ps.nocm','ps.id as nocmfk','ps.noidentitas','ps.namapasien','pd.noregistrasi', 'pd.tglregistrasi','jk.jeniskelamin',
                'ps.tgllahir','alm.alamatlengkap','pdd.pendidikan','pk.pekerjaan','ps.nohp as notelepon','ps.objectjeniskelaminfk',
                'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd','pd.norec as norec_pd',
                'kps.kelompokpasien','kls.namakelas','apd.objectkelasfk','pd.objectkelompokpasienlastfk','pd.objectrekananfk',
                'rk.namarekanan','pd.objectruanganlastfk','jpl.jenispelayanan','apd.objectasalrujukanfk',
                'ru.kdinternal','jpl.kodeinternal as objectjenispelayananfk','pd.objectpegawaifk','pd.statuspasien',
                'ps.nobpjs','pd.statuspasien',
                DB::raw('case when ru.objectdepartemenfk in (16,35,17) then \'true\' else \'false\' end as israwatinap')
            )
            ->where('pd.norec','=',$norec_pd)
            ->where('apd.norec','=',$norec_apd)
            ->where('pd.kdprofile', (int)$kdProfile)
            ->get();

        return $this->respond($data);
    }

    public function SavePermohonanSIMRS(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = true;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->tglplanning = $request['tglplanning'];
            $newCOA->objectruanganfk = $request['ruangandesc'];
            $newCOA->objectkelompoktransaksifk = 151;
            $newCOA->rincianexecuteplanning_askep = $request['rincian'];
            $newCOA->narasumberfk = $request['pelapor'];
            $newCOA->save();

            $norecHead2 = $newCOA->norec;

//            $sid    = "ACc6ac08ce1cc9096e0fd57a0c38801118";
//            $token  = "cada716b5a36721fd86832662bbd06fe";
//            $twilio = new Client($sid, $token);
//            $array =[
//                "whatsapp:+6282110191673",
//                "whatsapp:+6282211333013",
//                "whatsapp:+6285702501576",
//                "whatsapp:+6283838339887",
//                "whatsapp:+6281649111417",
//                "whatsapp:+6285654497677",
//                "whatsapp:+628995395195",
//                "whatsapp:+6281931694912",
//                "whatsapp:+6282133156669",
//                "whatsapp:+6285799995502",
//                "whatsapp:+6285643965969",
//            ];
//            $i = 0;
//            foreach ($array as $arr){
//                $message = $twilio->messages
//                    ->create($array[$i], // to
//                        array(
//                            "from" => "whatsapp:+14155238886",
//                            "body" => "Laporan Baru SIMRS dari ruangan ".$request['namaruangan']." - ".$request['rincian']
//                        )
//                    );
//                $i = $i+1;
//            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "";


        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil" ;
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $newPJT,
                "as" => 'dy@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newPJT,
                "as" => 'dy@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }

    public function getDaftarPermohonanSIMRS(Request $request){
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tgl="";
        $jenisKerusakan="";
        $idRuangan="";

        if ((isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") && (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined"))
        {
            $tgl = " AND stp.tglplanning BETWEEN '".$request['tglAwal']."' AND '".$request['tglAkhir']."' ";
        }

        if (isset($request['jenisKerusakan']) && $request['jenisKerusakan'] != "" && $request['jenisKerusakan'] != "undefined")
        {
            $jenisKerusakan = " AND stp.objectjenispekerjaanfk = '".$request['jenisKerusakan']."' ";
        }

        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined")
        {
            $idRuangan = " AND stp.objectruanganfk = '".$request['idRuangan']."' ";
        }

        $data = DB::select(DB::raw("
        SELECT stp.norec, rg.namaruangan, stp.tglplanning, pg.nama, stp.startdate, stp.duedate, stp.rincianexecuteplanning_askep, stp.pelapor, stp.deskripsiplanning, stp.keteranganverifikasi, sttp.namaexternal,stp.signdate, jpk.reportdisplay, stp.worklist, stp.keteranganverifikasi, pg2.nama as namainspektor, pg3.nama as namapelapor,
                sttp.reportdisplay as statuspekerjaan
            FROM strukplanning_t as stp
            INNER JOIN ruangan_m as rg on rg.id = stp.objectruanganfk
            LEFT JOIN pegawai_m as pg on pg.id = stp.objectpegawaipjawabfk
            LEFT JOIN pegawai_m as pg2 on pg2.id = stp.objectpegawaipjawabevaluasifk
            LEFT JOIN pegawai_m as pg3 on pg3.id = stp.narasumberfk 
            LEFT JOIN statuspekerjaan_m as sttp on sttp.id = stp.objectstatuspekerjaanfk
			LEFT JOIN jenispekerjaan_m as jpk on jpk.id = stp.objectjenispekerjaanfk
			WHERE stp.kdprofile = $kdProfile and stp.objectkelompoktransaksifk=151 
			AND stp.statusenabled=true 
			$tgl
			$jenisKerusakan
			$idRuangan
            ORDER BY stp.tglplanning DESC"));

        return $this->respond($data);

    }

    public function HapusPermohonanSIMRS(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            StrukPlanning::where('norec', $request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update(['statusenabled' => false]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'dy@epic',
            );
        } else {
            $transMessage = " Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'dy@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getJenisPekerjaanSIMRS(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("
        SELECT *
            FROM jenispekerjaan_m
			WHERE kdprofile = $kdProfile and jenispekerjaan='SIMRS' AND namaexternal='Kegiatan SIMRS'"));

        return $this->respond($data);

    }

    public function SaveDataJenisKerusakanSIMRS(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = true;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->objectjenispekerjaanfk = $request['jenisKerusakan'];
            $newCOA->save();

            $norecHead2 = $newCOA->norec;

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "";


        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil" ;
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $newPJT,
                "dy" => 'dy@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newPJT,
                "dy" => 'dy@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }

    public function getStatusPekerjaanSIMRS(Request $request){
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::select(DB::raw("
        SELECT *
            FROM statuspekerjaan_m
			WHERE kdprofile = $kdProfile and kodeexternal = 'SIMRS'"));

        return $this->respond($data);

    }

    public function getDataPegawaiGeneralSIMRS(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk=[];
        $dataProduk = \DB::table('maploginusertoruangan_s as ml')
            ->join('loginuser_s as lu','ml.objectloginuserfk','=','lu.id')
            ->join('pegawai_m as st','lu.objectpegawaifk','=','st.id')
            ->select('st.id','st.namalengkap')
            ->where('ml.kdprofile', $kdProfile)
            ->where('ml.objectruanganfk', '1')
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('st.namalengkap','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        foreach ($dataProduk as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }

        return $this->respond($dataPenulis2);
    }

    public function getComboAdministrasi(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $deptJalan = explode (',',$this->settingDataFixed('kdDepartemenRawatJalanFix',  $kdProfile ));
        $kdDepartemenRawatJalan = [];
        foreach ($deptJalan as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }
        $dpGD = explode (',',$this->settingDataFixed('KdDepartemenInstalasiGawatDarurat',  $kdProfile ));
        foreach ($dpGD as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }
        $dataRuanganInap = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan')
            ->whereIn('ru.objectdepartemenfk', $kdDepartemenRawatJalan)
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();
//        }

        $dataProduk=[];
        $datalistAkomodasi=[];
        if ($request['produk']==1){
            $dataProduk = \DB::table('produk_m as pr')
                ->JOIN('mapruangantoproduk_m as ma','ma.objectprodukfk','=','pr.id')
                ->select('pr.id','pr.namaproduk')
                ->where('pr.statusenabled',true)
                ->where('pr.kdprofile', $kdProfile)
                ->orderBy('pr.namaproduk');
            if(isset($request['objectruanganfk']) && $request['objectruanganfk']!="" && $request['objectruanganfk']!="undefined"){
                $dataProduk = $dataProduk->where('ma.objectruanganfk','=', $request['objectruanganfk']);
            }
//            $dataProduk->where('pr.namaproduk','ilike','%akomodasi%');
            $dataProduk = $dataProduk->get();

            $datalistAkomodasi = \DB::table('produk_m as pr')
                ->JOIN('mapruangantoadministrasi_t as ma','ma.objectprodukfk','=','pr.id')
                ->JOIN('jenispelayanan_m as jp','jp.id','=','ma.jenispelayananfk')
                ->select('pr.id','pr.namaproduk','ma.israwatgabung','ma.id as maid','ma.statusenabled',
                    'ma.jenispelayananfk','jp.jenispelayanan')
                ->where('pr.kdprofile', $kdProfile)
                ->where('pr.statusenabled',true)
                ->where('ma.statusenabled',true)
                ->orderBy('pr.namaproduk');
            if(isset($request['objectruanganfk']) && $request['objectruanganfk']!="" && $request['objectruanganfk']!="undefined"){
                $datalistAkomodasi = $datalistAkomodasi->where('ma.objectruanganfk','=', $request['objectruanganfk']);
            }
            $datalistAkomodasi = $datalistAkomodasi->get();
        }

        $result = array(
            'produk' => $dataProduk,
            'ruangan' => $dataRuanganInap,
            'listakomodasi' => $datalistAkomodasi,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function saveMappingAdminstrasiCuy(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = $request->all();
        try {
            if ($data['status'] == 'HAPUS') {
                $newKS = MapRuanganToAdministrasi::where('id', $request['maid'])->where('kdprofile', $kdProfile)->delete();

            }else {
                if ($data['maid'] == '') {
                    if ($data['rg'] == 'YES'){
                        $RG =1;
                    }else{
                        $RG =null;
                    }
                    $newKS = new MapRuanganToAdministrasi();
                    $norecKS = MapRuanganToAdministrasi::max('id');
                    $norecKS = $norecKS + 1;
                    $newKS->id = $norecKS;
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $kdProfile;
                    $newKS->statusenabled = true;
                    $newKS->objectprodukfk = $data['pelayanan'];
                    $newKS->objectruanganfk = $data['ruangan'];
                    $newKS->jenispelayananfk = $data['jenispelayanan'];
                    $newKS->israwatgabung = $RG;
                    $newKS->save();
                } else {

                    $newKS = MapRuanganToAdministrasi::where('id', $request['maid'])
                        ->where('kdprofile', $kdProfile)
                        ->update([
                                'objectruanganfk' => $data['ruangan'],
                                'objectprodukfk' => $data['pelayanan'],
                                'jenispelayananfk' => $data['jenispelayanan'],
                                'israwatgabung' => null,
                            ]
                        );

//                    $newKSasas = MapRuanganToAkomodasi::where('id', $request['maid'])->first();
                }

            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Simpan Map";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $newKS,
                "as" => 'as@epic',
            );
        } else {
            $transMessage =  $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noresep" => $e,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getJenisPelayanan(Request $r){

        $jenisPelayanan = \DB::table('jenispelayanan_m as jp')
            ->select('jp.kodeinternal as id', 'jp.jenispelayanan')
            ->where('jp.statusenabled', true)
            ->orderBy('jp.jenispelayanan')
            ->get();
        return $this->respond($jenisPelayanan);
    }

    public function getLingkupPelayanan(Request $r){

        $jenisPelayanan = \DB::table('lingkuppelayanan_m as jp')
            ->select('jp.id', 'jp.lingkuppelayanan')
            ->where('jp.statusenabled', true)
            ->orderBy('jp.id')
            ->get();
        return $this->respond($jenisPelayanan);
    }

    public function getDepartemen(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $jenisPelayanan = \DB::table('departemen_m as jp')
            ->select('jp.id', 'jp.namadepartemen')
            ->where('jp.statusenabled', true)
            ->orderBy('jp.id')
            ->get();
        return $this->respond($jenisPelayanan);
    }

    public function saveMappingLaporanKeuanganToLingkupPelayanan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = $request->all();
        try {
            if ($data['status'] == 'HAPUS') {
                $newKS = MapLaporangKeuanganToLingkupPelayanan::where('id', $request['maid'])->where('kdprofile', $kdProfile)->delete();
            }else {
                if ($data['maid'] == '') {
                    $newKS = new MapLaporangKeuanganToLingkupPelayanan();
                    $norecKS = MapLaporangKeuanganToLingkupPelayanan::max('id');
                    $norecKS = $norecKS + 1;
                    $newKS->id = $norecKS;
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $kdProfile;
                    $newKS->statusenabled = true;
                    $newKS->produkfk = $data['pelayanan'];
                    if (isset($data['departemen'])){
                        $newKS->objectdepartemenfk = $data['departemen'];
                    }
                    $newKS->lingkuppelayananfk = $data['lingkuppelayananfk'];
                    $newKS->save();
                } else {
                    $newKS = MapLaporangKeuanganToLingkupPelayanan::where('id', $request['maid'])
                        ->where('kdprofile', $kdProfile)
                        ->update([
                                'objectdepartemenfk' => $data['departemen'],
                                'produkfk' => $data['pelayanan'],
                                'lingkuppelayananfk' => $data['jenispelayanan'],
                            ]
                        );
                }
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Simpan Map";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $newKS,
                "as" => 'as@epic',
            );
        } else {
            $transMessage =  $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataMapLapKeuanganToLingkupPelayanan(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataProduk=[];
        $datalistAkomodasi=[];
        $datalistMap = \DB::table('produk_m as pr')
            ->JOIN('maplaporankeuangantolingkuppelayanan_m as ma','ma.produkfk','=','pr.id')
            ->JOIN('lingkuppelayanan_m as jp','jp.id','=','ma.lingkuppelayananfk')
            ->leftjoin('departemen_m as dp','dp.id','=','ma.objectdepartemenfk')
            ->select('ma.produkfk','pr.namaproduk','ma.id as maid','ma.statusenabled',
                     'ma.lingkuppelayananfk','jp.lingkuppelayanan','ma.objectdepartemenfk','dp.namadepartemen')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled',true)
            ->where('ma.statusenabled',true)
            ->orderBy('pr.namaproduk');

        if(isset($request['objectdepartemenfk']) && $request['objectdepartemenfk']!="" && $request['objectdepartemenfk']!="undefined"){
            $datalistMap = $datalistMap->where('ma.objectdepartemenfk','=', $request['objectdepartemenfk']);
        }

        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $datalistMap = $datalistMap->where('ma.produkfk','=', $request['produkfk']);
        }

        if(isset($request['lingkuppelayananfk']) && $request['lingkuppelayananfk']!="" && $request['lingkuppelayananfk']!="undefined"){
            $datalistMap = $datalistMap->where('ma.lingkuppelayananfk','=', $request['lingkuppelayananfk']);
        }

        $datalistMap = $datalistMap->get();

        $result = array(
            'listmap' => $datalistMap,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
     public function readBed (){
        $data = DB::select(DB::raw("
            SELECT tt.id,
            kls.id as kodekelas,kls.namakelas,ru.id as koderuang,
            ru.namaruangan as namaruang,0 as terpakaipria,
            0 as terpakai,0 as kosongpria,0 as kosongwanita,
            0 as terpakaiwanita,count(tt.id ) as kapasitas
            FROM tempattidur_m AS tt
            INNER JOIN statusbed_m AS sb ON sb. ID = tt.objectstatusbedfk
            INNER JOIN kamar_m AS kmr ON kmr. ID = tt.objectkamarfk
            INNER JOIN kelas_m AS kls ON kls. ID = kmr.objectkelasfk
            INNER JOIN ruangan_m AS ru ON ru. ID = kmr.objectruanganfk
            WHERE tt.kdprofile =21
            AND tt.statusenabled = TRUE
            AND kmr.statusenabled = TRUE
            GROUP BY kls.id ,
            kls.namakelas,ru.id,
            ru.namaruangan,tt.id

            "));
        $pasien = DB::select(DB::raw("
                select * from (
                select   row_number() over (partition by pd.noregistrasi 
                order by apd.tglmasuk desc) as rownum ,ps.nocm,
                ps.objectjeniskelaminfk AS jkid,
                apd.objectruanganfk,apd.objectkamarfk,apd.nobed,apd.objectkelasfk
                from pasiendaftar_t as pd 
                join antrianpasiendiperiksa_t as apd on pd.norec =apd.noregistrasifk
                and apd.objectruanganfk=pd.objectruanganlastfk
                and apd.tglkeluar is null
                join pasien_m as ps on ps.id=pd.nocmfk
                where pd.tglpulang is null 
                and pd.statusenabled=TRUE
                and pd.kdprofile=21
                )as x where x.rownum=1
                "));
        $terpPria = 0;
        $terpWan = 0;
        $kosongPria = 0;
        $kosongterpWan = 0;
        foreach ($data as $key => $v) {
            foreach ($pasien as $key2 => $v2) {
              if($v->id == $v2->nobed ){
                if($v2->jkid == 1){
                    $v->terpakaipria = $v->terpakaipria+1;
                      $terpPria =   $terpPria +1;
                }
                if($v2->jkid == 2){
                    $v->terpakaiwanita = $v->terpakaiwanita+1;
                    $terpWan =   $terpWan +1;
                }
                $v->terpakai = $v->terpakai +1;
              }
            }
        }
        $terpPria = 0;
        $terpWan = 0;
        $kosongPria = 0;
        $kosongterpWan = 0;
        // foreach ($data as $key => $v) {
        //     foreach ($pasien as $key2 => $v2) {
        //       if($v->koderuang == $v2->koderuang &&  $v->kodekelas == $v2->kodekelas){
        //         if($v2->jkid == 1){
        //             $v->terpakaipria = $v->terpakaipria+1;
        //               $terpPria =   $terpPria +1;
        //         }
        //         if($v2->jkid == 2){
        //             $v->terpakaiwanita = $v->terpakaiwanita+1;
        //             $terpWan =   $terpWan +1;
        //         }
        //       }
        //     }
        // }
        $data10 =[];
         foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->kodekelas == $data10[$i]['kodekelas'] && $item->koderuang == $data10[$i]['koderuang']) {
                    $sama = true;
                    $data10[$i]['terpakaipria'] = $data10[$i]['terpakaipria'] + $item->terpakaipria; 
                    $data10[$i]['terpakaiwanita']  = $data10[$i]['terpakaiwanita'] + $item->terpakaiwanita;   
                    $data10[$i]['kapasitas']  = $data10[$i]['kapasitas'] + $item->kapasitas;       
                    // $data10[$i]['terpakai']  =  $data10[$i]['terpakai']+  $item->terpakai;                 
                }
                $i = $i + 1;
            }
            if($sama == false){
                 $data10 [] = array(
                    'kodekelas' => $item->kodekelas , 
                    'namakelas' => $item->namakelas , 
                    'koderuang' => $item->koderuang , 
                    'namaruang' => $item->namaruang , 
                    'kapasitas' => $item->kapasitas , 
                    'terpakaipria' => $item->terpakaipria,
                    'terpakaiwanita' => $item->terpakaiwanita,
                    // 'terpakai' => $item->terpakai ,
                    'kapasitaspria' => 0,
                    'kapasitaswanita' => 0,
                );
               
            }

        }
        $i = 0;
        foreach ($data10 as $key => $value) {
            $terpPria =$terpPria +    $value['terpakaipria'] ;  
            $terpWan =$terpWan +   $value['terpakaiwanita'] ;  
            $data10[$i]['kapasitaswanita']  = floor($value['kapasitas'] /2);
            $data10[$i]['kapasitaspria']  = round($value['kapasitas'] /2);
            $i++;
        }

        $res['list'] = $data10 ; 
        // $res['terpakaiPria'] =  $terpPria; 
        // $res['terpWan'] =  $terpWan; 
        // $res['total'] =  $terpWan+ $terpPria; 

        $result = array(
            "response"=> $res,
            "metadata"=>array(
                "code" => "200",
                "message" => "Ok"
            ));
        return $this->setStatusCode($result['metadata']['code'])->respond($result);
    }
}