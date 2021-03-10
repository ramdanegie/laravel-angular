<?php
// "It always seems impossible until it's done."
// , Nelson Mandela
/**
 * Created by PhpStorm.
 * AkuntansiController
 * User: Efan Andrian (ea@epic)
 * Date: 01/10/2019
 * Time: 14:12 PM
 */

namespace App\Http\Controllers\Akuntansi;


use App\Http\Controllers\ApiController;
use App\Master\ChartOfAccount;
use App\Master\TargetIndikator;
use App\Traits\Valet;
use App\Transaksi\PostingSaldoAwal;
use App\Transaksi\PostingJurnalTransaksiD;
use App\Transaksi\PostingJurnalTransaksi;
use App\Transaksi\PostingJurnal;
use App\Transaksi\PostingJurnalD;

use App\Transaksi\Sal;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class AkuntansiController extends ApiController{
    use Valet;
    public function __construct(){
        parent::__construct($skip_authentication=false);
    }

    public function getDataCoa(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $data=[];
        $data = \DB::table('chartofaccount_m as pr')
            ->JOIN('suratkeputusan_m as sk', 'sk.id', '=', 'pr.suratkeputusanfk')
            ->select('pr.id', 'pr.noaccount', 'pr.namaaccount')
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.statusenabled', '=',1)
            ->where('sk.statusenabled','=', 1)
            ->orderBy('pr.noaccount');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data->where('pr.namaaccount','ilike','%'. $req['filter']['filters'][0]['value'].'%' )
                ->orWhere('pr.noaccount','ilike',$req['filter']['filters'][0]['value'].'%');
        };

        $data = $data->take(20);
        $data = $data->get();
        $result = [];
        foreach($data as $item){
            $result[]= array(
                'id' => $item->id,
                'noaccount' => $item->noaccount,
                'namaaccount' => $item->namaaccount,
                'nonamaaccount' => $item->noaccount . ' ' . $item->namaaccount
            );
        }
        return $this->respond($result);
    }

    public function getDataBukuBesarRev2(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
//        ini_set('max_execution_time', 1000);
        $mydate = $request['tglAwal'];
        $daystosum = '1';

        $datesum = date('d-m-Y', strtotime($mydate.' - '.$daystosum.' months'));
        $data = date('Ym', strtotime($datesum));

        if ($request['noaccount'] == '-' and $request['noaccount2'] == '-'){
            $data10=[];
            $aingMacan = DB::select(DB::raw("select * from
                    (select  to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD') as tglbuktitransaksi ,
                    coa.noaccount as noaccount,pj.keteranganlainnya,
                    sum(pjd.hargasatuand) as hargasatuand,
                    sum(pjd.hargasatuank) as hargasatuank,coa.saldonormaladd,coa.saldonormalmin,coa.id as coaid,pj.nojurnal_intern,coa.namaaccount
                    from postingjurnal_t as pj
                    INNER JOIN postingjurnald_t as pjd on pjd.norecrelated=pj.norec
                    INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                    where pj.kdprofile = $idProfile and pj.tglbuktitransaksi between :tglAwal and :tglAkhir 
                    group by to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD'),pj.keteranganlainnya,pj.deskripsiproduktransaksi,coa.saldonormaladd,
                    coa.saldonormalmin,coa.id,pj.nojurnal_intern,coa.namaaccount,coa.noaccount)as x
                    order by x.noaccount ;
                "),
                array(
                    'tglAwal' => $request['tglAwal'] ,
                    'tglAkhir' => $request['tglAkhir']
                )
            );

            $aingSA = DB::select(DB::raw("
                  select case when psa.hargasatuand is null then 0 else psa.hargasatuand end as hargasatuand,
                    case when psa.hargasatuank is null then 0 else psa.hargasatuank end as hargasatuank,psa.objectaccountfk
                    from postingsaldoawal_t as psa
                    where psa.kdprofile = $idProfile and psa.ym ='$data' and statusenabled = 1 ;
                ")
            );

            $coaSA ='';
            foreach ($aingMacan as $item) {
                $tgl = date('Y-m-d', strtotime($item->tglbuktitransaksi));
                $detailData = [];

                $sama = false;
                if ($coaSA != $item->noaccount) {
                    $coaSA = $item->noaccount;
                    $sama = false;
                    foreach ($aingSA as $kutukupret) {
                        if ($item->noaccount == $kutukupret->noaccount) {
                            $saldo = 0;
//                            if ($kutukupret->hargasatuand > 0) {
//                                $saldo = $kutukupret->hargasatuand;
//                            } else {
//                                $saldo = $kutukupret->hargasatuank;
//                            }
                            $data10[] = array(
                                'hargasatuand' => $kutukupret->hargasatuand,
                                'hargasatuank' => $kutukupret->hargasatuank,
                                'keteranganlainnya' => 'Saldo Awal',
                                'noaccount' => $item->noaccount,
                                'saldonormaladd' => 'D',
                                'saldonormalmin' => 'K',
                                'tglbuktitransaksi' => $data . '31',
                                'coaid' => $item->coaid,
                                'noaccount' => $item->noaccount,
                                'namaaccount' => $item->namaaccount,
                                'KodePerkiraan' => $item->noaccount . ' ' . $item->namaaccount,
                                'details' => [],
                                'tgl' => (string)$data . '31',
                                'nojurnal' => '',
                                'saldo' => $saldo,
                                'noref' => '',
                            );
                            $sama = true;
                        }
                    }
                    if ($sama == false) {
                        $saldo = 0;
                        $data10[] = array(
                            'hargasatuand' => 0,
                            'hargasatuank' => 0,
                            'keteranganlainnya' => 'Saldo Awal',
                            'noaccount' => $item->noaccount,
                            'saldonormaladd' => 'D',
                            'saldonormalmin' => 'K',
                            'tglbuktitransaksi' => $data . '31',
                            'coaid' => $item->coaid,
                            'noaccount' => $item->noaccount,
                            'namaaccount' => $item->namaaccount,
                            'KodePerkiraan' => $item->noaccount . ' ' . $item->namaaccount,
                            'details' => [],
                            'tgl' => (string)$data . '31',
                            'nojurnal' => '',
                            'saldo' => 0,
                            'noref' => '',
                        );
                    }

                }

                $saldo = 0;//$saldo + $item->hargasatuand - $item->hargasatuank;


                $data10[] = array(
                    'hargasatuand' => $item->hargasatuand,
                    'hargasatuank' => $item->hargasatuank,
                    'keteranganlainnya' => $item->keteranganlainnya,
                    'noaccount' => $item->noaccount,
                    'saldonormaladd' => $item->saldonormaladd,
                    'saldonormalmin' => $item->saldonormalmin,
                    'tglbuktitransaksi' => $item->tglbuktitransaksi,
                    'coaid' => $item->coaid,
                    'noaccount' => $item->noaccount,
                    'namaaccount' => $item->namaaccount,
                    'KodePerkiraan' => $item->noaccount . ' ' . $item->namaaccount,
                    'details' => $detailData,
                    'tgl' => (string)$tgl,
                    'nojurnal' => $item->nojurnal_intern,
                    'saldo' => $saldo,
                    'noref' => '',
                );
//                }
            }

            $same = false;
            $data11=[];
            $i = 0;
            foreach ($aingMacan as $item) {
                $same = false;
                $i = 0;
                foreach ($data11 as $temtem){
                    if ( $item->coaid == $temtem['coaid']) {
                        $same = true;
                        $data11[$i]['hargasatuand'] = (float)$temtem['hargasatuand'] + (float)$item->hargasatuand;
                        $data11[$i]['hargasatuank'] = (float)$temtem['hargasatuank'] + (float)$item->hargasatuank;
                        $data11[$i]['saldo'] = (float)$data11[$i]['hargasatuand'] - (float)$data11[$i]['hargasatuank'];
                    }
                    $i =$i +1;
                }
                if ($same == false){
                    $data11[] = array(
                        'hargasatuand' => $item->hargasatuand,
                        'hargasatuank' => $item->hargasatuank,
                        'keteranganlainnya' => 'Total',
                        'noaccount' => $item->noaccount,
                        'saldonormaladd' => 'D',
                        'saldonormalmin' => 'K',
                        'tglbuktitransaksi' => '0-0-0-0-0',
                        'coaid' => $item->coaid,
                        'noaccount' => '',
                        'namaaccount' => $item->namaaccount,
                        'KodePerkiraan' => $item->noaccount . ' ' . $item->namaaccount,
                        'details' => [],
                        'tgl' => '0-0-0-0-0',
                        'nojurnal' => '',
                        'saldo' => (float) $item->hargasatuand - (float) $item->hargasatuank,
                        'noref' => '',
                    );
                }
            }
            foreach ($data11 as $temtem){
                $data10[] = $temtem;
            }
//            }
            $aingSA[] =array(
                'hargasatuand' => 0,
                'hargasatuank' => 0,
            );
        }
        if ($request['noaccount'] != '-' and $request['noaccount2'] != '-'){
            $data10=[];
            $noaccount = $request['noaccount'];
            $noaccount2 = $request['noaccount2'];
            $aingMacan = DB::select(DB::raw("select * from
                    (select  to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD') as tglbuktitransaksi ,
                    coa.noaccount as noaccount,pj.keteranganlainnya,pj.nobuktitransaksi as noref,
                    sum(pjd.hargasatuand) as hargasatuand,
                    sum(pjd.hargasatuank) as hargasatuank,coa.saldonormaladd,coa.saldonormalmin,coa.id as coaid,pj.nojurnal_intern,coa.namaaccount
                    from postingjurnal_t as pj
                    INNER JOIN postingjurnald_t as pjd on pjd.norecrelated=pj.norec
                    INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                    where pj.kdprofile = $idProfile and pj.tglbuktitransaksi between :tglAwal and :tglAkhir and ( coa.noaccount between '$noaccount' and '$noaccount2')
                    group by to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD'),pj.keteranganlainnya,pj.deskripsiproduktransaksi,coa.saldonormaladd,
                    coa.saldonormalmin,coa.id,pj.nojurnal_intern,coa.namaaccount,coa.noaccount,pj.nobuktitransaksi)as x
                    order by x.noaccount,x.nojurnal_intern ;
                "),
                array(
                    'tglAwal' => $request['tglAwal'] ,
                    'tglAkhir' => $request['tglAkhir'],
                )
            );

            $aingSA = DB::select(DB::raw("
                  select case when psa.hargasatuand is null then 0 else psa.hargasatuand end as hargasatuand,
                    case when psa.hargasatuank is null then 0 else psa.hargasatuank end as hargasatuank,psa.objectaccountfk
                    from postingsaldoawal_t as psa
                    INNER join chartofaccount_m as coa on coa.id=psa.objectaccountfk
                    where psa.kdprofile = $idProfile and cast(psa.ym as varchar) ='$data' and psa.statusenabled = 1  and coa.noaccount between '$noaccount' and '$noaccount2';
                ")
            );

            $coaSA ='';
            foreach ($aingMacan as $item) {
                $tgl = date('Y-m-d', strtotime($item->tglbuktitransaksi));
                $detailData = [];

                $sama = false;
                if ($coaSA != $item->noaccount) {
                    $coaSA = $item->noaccount;
                    $sama = false;
                    foreach ($aingSA as $kutukupret) {
                        if ($item->coaid == $kutukupret->objectaccountfk) {
                            $saldo = 0;
//                            if ($kutukupret->hargasatuand > 0) {
//                                $saldo = $kutukupret->hargasatuand;
//                            } else {
//                                $saldo = $kutukupret->hargasatuank;
//                            }
                            $data10[] = array(
                                'hargasatuand' => $kutukupret->hargasatuand,
                                'hargasatuank' => $kutukupret->hargasatuank,
                                'keteranganlainnya' => 'Saldo Awal',
                                'noaccount' => $item->noaccount,
                                'saldonormaladd' => 'D',
                                'saldonormalmin' => 'K',
                                'tglbuktitransaksi' => $data . '31',
                                'coaid' => $item->coaid,
                                'noaccount' => '',
                                'namaaccount' => $item->namaaccount,
                                'KodePerkiraan' => $item->noaccount . ' ' . $item->namaaccount,
                                'details' => [],
                                'tgl' => (string)$data . '31',
                                'nojurnal' => '',
                                'saldo' => $saldo,
                                'noref' => '',
                            );
                            $sama = true;
                        }
                    }
                    if ($sama == false) {
                        $saldo = 0;
                        $data10[] = array(
                            'hargasatuand' => 0,
                            'hargasatuank' => 0,
                            'keteranganlainnya' => 'Saldo Awal',
                            'noaccount' => $item->noaccount,
                            'saldonormaladd' => 'D',
                            'saldonormalmin' => 'K',
                            'tglbuktitransaksi' => $data . '31',
                            'coaid' => $item->coaid,
                            'noaccount' => '',
                            'namaaccount' => $item->namaaccount,
                            'KodePerkiraan' => $item->noaccount . ' ' . $item->namaaccount,
                            'details' => [],
                            'tgl' => (string)$data . '31',
                            'nojurnal' => '',
                            'saldo' => 0,
                            'noref' => '',
                        );
                    }

                }

                $saldo = 0;//$saldo + $item->hargasatuand - $item->hargasatuank;


                $data10[] = array(
                    'hargasatuand' => $item->hargasatuand,
                    'hargasatuank' => $item->hargasatuank,
                    'keteranganlainnya' => $item->keteranganlainnya,
                    'noaccount' => $item->noaccount,
                    'saldonormaladd' => $item->saldonormaladd,
                    'saldonormalmin' => $item->saldonormalmin,
                    'tglbuktitransaksi' => $item->tglbuktitransaksi,
                    'coaid' => $item->coaid,
                    'noaccount' => $item->noaccount,
                    'namaaccount' => $item->namaaccount,
                    'KodePerkiraan' => $item->noaccount . ' ' . $item->namaaccount,
                    'details' => $detailData,
                    'tgl' => (string)$tgl,
                    'nojurnal' => $item->nojurnal_intern,
                    'saldo' => $saldo,
                    'noref' => $item->noref,
                );
//                }
            }

            $same = false;
            $data11=[];
            $i = 0;
            foreach ($aingMacan as $item) {
                $same = false;
                $i = 0;
                foreach ($data11 as $temtem){
                    if ( $item->coaid == $temtem['coaid']) {
                        $same = true;
                        $data11[$i]['hargasatuand'] = (float)$temtem['hargasatuand'] + (float)$item->hargasatuand;
                        $data11[$i]['hargasatuank'] = (float)$temtem['hargasatuank'] + (float)$item->hargasatuank;
                        $data11[$i]['saldo'] = (float)$data11[$i]['hargasatuand'] - (float)$data11[$i]['hargasatuank'];
                    }
                    $i =$i +1;
                }
                if ($same == false){
                    $data11[] = array(
                        'hargasatuand' => $item->hargasatuand,
                        'hargasatuank' => $item->hargasatuank,
                        'keteranganlainnya' => 'Total',
                        'noaccount' => $item->noaccount,
                        'saldonormaladd' => 'D',
                        'saldonormalmin' => 'K',
                        'tglbuktitransaksi' => '0-0-0-0-0',
                        'coaid' => $item->coaid,
                        'noaccount' => '',
                        'namaaccount' => $item->namaaccount,
                        'KodePerkiraan' => $item->noaccount . ' ' . $item->namaaccount,
                        'details' => [],
                        'tgl' => '0-0-0-0-0',
                        'nojurnal' => '',
                        'saldo' => (float) $item->hargasatuand - (float) $item->hargasatuank,
                        'noref' => '',
                    );
                }
            }
            foreach ($data11 as $temtem){
                $data10[] = $temtem;
            }
//            }
            $aingSA[] =array(
                'hargasatuand' => 0,
                'hargasatuank' => 0,
            );
        }

        $result = array(
            'saldoawal' => $aingSA,
            'dat' => $data11,
            'data' => $data10,
//            'coa' => $coaAing,
            'by' => 'as@epic'
        );
        return $this->respond($result);
    }

    public function getDetailJurnalRev2018BukuBesar(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::select(DB::raw("select pj.nojurnal,coa.noaccount,
                case when pjd.hargasatuand = 0 then '--- ' || coa.namaaccount else coa.namaaccount end as namaaccount,
                pj.namaproduktransaksi as keteranganlainnya,pjd.hargasatuand,pjd.hargasatuank from postingjurnal_t as pj
                INNER JOIN postingjurnald_t as pjd on pj.norec=pjd.norecrelated
                INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                where pj.kdprofile = $idProfile and nojurnal_intern=:nojurnal 
                and pjd.hargasatuand + pjd.hargasatuank > 0
                --and coa.id=:accountid;
                "),
            array(
                'nojurnal' => $request['nojurnal'],
               // 'accountid' => $request['accountid'],
            )
        );
        return $this->respond($data);
    }

    public function getDaftarSaldoAwal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = ['as@epic'];
        if(isset($request['coaid']) && $request['coaid']!="" && $request['coaid']!="undefined"){
            $data = DB::select(DB::raw("select *,FORMAT(x.tgl, 'yyyy MMMM', 'en-us') from 
                (select cast(ym + '01' as date) as tgl,hargasatuand,hargasatuank,coa.noaccount,coa.namaaccount,psa.statusenabled,
                psa.norec,coa.id
                from postingsaldoawal_t as psa
                INNER JOIN chartofaccount_m as coa on coa.id=psa.objectaccountfk
                where psa.kdprofile = $idProfile and coa.id=:coaId order by ym desc limit 5) as x"),
                array(
                    'coaId' => $request['coaid'],
                )
            );
        }else{
            $data = DB::select(DB::raw("select *,FORMAT(x.tgl, 'yyyy MMMM', 'en-us') from 
                (select cast(ym + '01' as date) as tgl,hargasatuand,hargasatuank,coa.noaccount,coa.namaaccount,psa.statusenabled,
                psa.norec,coa.id
                from postingsaldoawal_t as psa
                INNER JOIN chartofaccount_m as coa on coa.id=psa.objectaccountfk
                where psa.kdprofile = $idProfile and coa.noaccount=:noaccount order by ym desc limit 5) as x;"),
                array(
                    'noaccount' => $request['noaccount'],
                )
            );
        };

        return $this->respond($data);
    }

    public function SaveSaldoAwal(Request $request) {
        DB::beginTransaction();
        $dataReq = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            if ($dataReq['norec'] == '-'){

                $postingSA = new PostingSaldoAwal();
                $norecHead = $postingSA->generateNewId();
                $postingSA->norec = $norecHead;
                $postingSA->kdprofile = $idProfile;
            }else{
                $postingSA = PostingSaldoAwal::where('norec', $dataReq['norec'])
                    ->first();

            }

            $postingSA->objectaccountfk = $dataReq['objectaccountfk'];
            $postingSA->hargasatuand = $dataReq['hargasatuand'];
            $postingSA->hargasatuank = $dataReq['hargasatuank'];
            $postingSA->statusenabled = $dataReq['statusenabled'];
            $postingSA->ym = $dataReq['ym'];
            $postingSA->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Add Saldo Awal Sukses';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function SaveHapusSaldoAwal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
//        $dataReq = $request->all();

        try {
            $newPPD2 = PostingSaldoAwal::where('norec', $request['head'])->where('kdprofile', $idProfile)->delete();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Hapus Berhasil';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataBukuBesarPembantu(Request $request) {
        $req=$request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $mydate = $request['tglAwal'];
        $daystosum = '1';

        $datesum = date('d-m-Y', strtotime($mydate.' - '.$daystosum.' months'));
        $data = date('Ym', strtotime($datesum));

        $aingSA = [];
        $aingMacan = [];
        if ($request['jenis'] == 'Piutang'){
            $piutang = " where  coaid in (10897) ";
            $aingMacan = DB::select(DB::raw("select * from
                (select  to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD') as tglbuktitransaksi ,
                sp.nostruk as noaccount,pd.noregistrasi || '/' || ps.nocm || ' '  || ps.namapasien  as  keteranganlainnya,
                sum(pjd.hargasatuand) as hargasatuand,
                sum(pjd.hargasatuank) as hargasatuank,coa.saldonormaladd,coa.saldonormalmin,coa.id as coaid,pj.nojurnal_intern
                from postingjurnaltransaksi_t as pj
                INNER JOIN postingjurnaltransaksid_t as pjd on pjd.norecrelated=pj.norec
                INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pj.norecrelated
                INNER JOIN pasiendaftar_t as pd on sp.noregistrasifk=pd.norec
                INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                left JOIN rekanan_m as rkn on rkn.id=pd.objectrekananfk
                where pj.kdprofile = $idProfile and pj.tglbuktitransaksi between :tglAwal and :tglAkhir  and rkn.id=:rknid --and sp.statusenabled not in ('f')
                group by to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD'),sp.nostruk,pd.noregistrasi || '/' || ps.nocm || ' '  || ps.namapasien ,pj.deskripsiproduktransaksi,coa.saldonormaladd,
                coa.saldonormalmin,coa.id,pj.nojurnal_intern)as x  $piutang
                order by x.tglbuktitransaksi ;
            "),
                array(
                    'tglAwal' => $request['tglAwal'] ,
                    'tglAkhir' => $request['tglAkhir'] ,
                    'rknid' => $request['rknid'] ,
                )
            );
            $aingSA = DB::select(DB::raw("
                select
                case when psa.hargasatuand is null then 0 else psa.hargasatuand end as hargasatuand,
                case when psa.hargasatuank is null then 0 else psa.hargasatuank end as hargasatuank
                from postingsaldoawal_t as psa
                where psa.kdprofile = $idProfile and psa.objectaccountfk=:noakun and cast(psa.ym as varchar) =:tglAwal and statusenabled=1;
            "),
                array(
                    'tglAwal' => (string)$data ,
                    'noakun' => (int)10897 ,
                )
            );
        }
        if ($request['jenis'] == 'Hutang'){
            $piutang = " where  coaid in (11136) ";
            $aingMacan = DB::select(DB::raw("select * from
                (select  to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD') as tglbuktitransaksi ,
                sp.nostruk as noaccount,sp.nostruk || '/' || rkn.id || ' '  || rkn.namarekanan  as  keteranganlainnya,
                sum(pjd.hargasatuand) as hargasatuand,
                sum(pjd.hargasatuank) as hargasatuank,coa.saldonormaladd,coa.saldonormalmin,coa.id as coaid,pj.nojurnal_intern
                from postingjurnaltransaksi_t as pj
                INNER JOIN postingjurnaltransaksid_t as pjd on pjd.norecrelated=pj.norec
                INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pj.norecrelated
                left JOIN rekanan_m as rkn on rkn.id=sp.objectrekananfk
                where pj.kdprofile = $idProfile and pj.tglbuktitransaksi between :tglAwal and :tglAkhir  and rkn.id=:rknid --and sp.statusenabled not in ('f')
                group by to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD'),sp.nostruk,sp.nostruk || '/' || rkn.id || ' '  || rkn.namarekanan ,pj.deskripsiproduktransaksi,coa.saldonormaladd,
                coa.saldonormalmin,coa.id,pj.nojurnal_intern)as x  $piutang
                order by x.tglbuktitransaksi ;
            "),
                array(
                    'tglAwal' => $request['tglAwal'] ,
                    'tglAkhir' => $request['tglAkhir'] ,
                    'rknid' => $request['rknid'] ,
                )
            );

            $aingSA = DB::select(DB::raw("
                select
                case when psa.hargasatuand is null then 0 else psa.hargasatuand end as hargasatuand,
                case when psa.hargasatuank is null then 0 else psa.hargasatuank end as hargasatuank
                from postingsaldoawal_t as psa
                where psa.kdprofile = $idProfile and psa.objectaccountfk=:noakun and psa.ym =:tglAwal and statusenabled=1;
            "),
                array(
                    'tglAwal' => (string)$data ,
                    'noakun' => (int)11136 ,
                )
            );
        }


        $data10=[];
        foreach ($aingMacan as $item) {

            //cari detail
            $tgl = date('Y-m-d', strtotime($item->tglbuktitransaksi));
            $detailData=[];
//            foreach ($dataDetail as $det){
//                if ($det->coaid2 == $item->coaid){
//                    if (strpos((string)'  '. $det->tglpelayanan , (string)$tgl) != false){
//                            $detailData[]=$det;
//                    }
//                }
//            }

            $data10[] = array(
                'hargasatuand' => $item->hargasatuand,
                'hargasatuank' => $item->hargasatuank,
                'keteranganlainnya' => $item->keteranganlainnya,
                'noaccount' => $item->noaccount,
                'saldonormaladd' => $item->saldonormaladd,
                'saldonormalmin' => $item->saldonormalmin,
                'tglbuktitransaksi' =>$item->tglbuktitransaksi,
                'coaid' => $item->coaid,
                'details' => $detailData,
//                'tglpelayanan'=>(string)$det->tglpelayanan ,
                'tgl' => (string)$tgl,
                'nojurnal'=> $item->nojurnal_intern,
            );

        }

        $datdatdat = array(
            'tglAwal' => (string)$data ,
            'noakun' => (int)$request['noaccount'] ,
        );
        if (count($aingSA) == 0){
            $aingSA[] =array(
                'hargasatuand' => 0,
                'hargasatuank' => 0,
                'datareq' => (string)$data,
            );
        }
        $result = array(
            'saldoawal' => $aingSA,
            'dat' => $datdatdat,
            'data' => $data10,
            'by' => 'as@epic'
        );
        return $this->respond($result);
    }

    public function SaveClosingJurnal(Request $request) {
        ini_set('max_execution_time', 200);
        DB::beginTransaction();
        $dataReq = $request->all();
        $data = $dataReq['data'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;

        try {
            $postingSA = PostingSaldoAwal::where('ym', $dataReq['ym'])
                ->delete();
            foreach ($data as $item){
                $postingSA = new PostingSaldoAwal();
                $norecHead = $postingSA->generateNewId();
                $postingSA->norec = $norecHead;
                $postingSA->kdprofile = $idProfile;

                $postingSA->objectaccountfk = $item['idaccount'];
                $postingSA->hargasatuand = $item['debetAkhir'];
                $postingSA->hargasatuank = $item['kreditAkhir'];
                $postingSA->statusenabled = 1;
                $postingSA->ym = $dataReq['ym'];
                $postingSA->save();
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Closing Jurnal ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $postingSA,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function SaveBatalClosingJurnal(Request $request) {
        DB::beginTransaction();
        $dataReq = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            $postingSA = PostingSaldoAwal::where('ym', $dataReq['ym'])
                ->where('kdprofile', $idProfile)
                ->delete();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Closing Jurnal ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $postingSA,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataTrialBalance(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $aingMacan = DB::select(DB::raw("select coa.id,coa.noaccount,coa.namaaccount,sum(pjd.hargasatuand) as debet,
                sum(pjd.hargasatuank) as kredit
                from chartofaccount_m as coa
                left JOIN postingjurnald_t as pjd on coa.id=pjd.objectaccountfk
                left JOIN postingjurnal_t as pj on pjd.norecrelated=pj.norec
                    join suratkeputusan_m as sk on sk.id=coa.suratkeputusanfk
                where coa.kdprofile = $idProfile and tglbuktitransaksi between :tglAwal and :tglAkhir and sk.statusenabled=true
                group by coa.id,coa.noaccount,coa.namaaccount
                order by coa.noaccount;
            "),
            array(
                'tglAwal' => $request['tglAwal'] ,
                'tglAkhir' => $request['tglAkhir'] ,
            )
        );
        $mydate = $request['tglAwal'];
        $daystosum = '1';

        $datesum = date('d-m-Y', strtotime($mydate.' - '.$daystosum.' months'));
        $data = date('Ym', strtotime($datesum));
        $strData = (string)$data;
        $strDT = substr($strData, 4, 2);

//        if ($strDT != '12'){
        $dataCoa = DB::select(DB::raw("
                    select coa.id,coa.noaccount,coa.namaaccount,psa.ym,
                    case when psa.hargasatuand is null then 0 else psa.hargasatuand end as debet,
                    case when psa.hargasatuank is null then 0 else psa.hargasatuank end as kredit
                    from chartofaccount_m as coa
                    join suratkeputusan_m as sk on sk.id=coa.suratkeputusanfk
                    left JOIN (select * from postingsaldoawal_t where statusenabled=1 and cast(ym as varchar) = '$strData') as psa  on psa.objectaccountfk=coa.id
                    where coa.kdprofile = $idProfile and sk.statusenabled=true and coa.statusenabled=true  order by coa.noaccount
                ")
        );
//        }else{
//            $dataCoa = DB::select(DB::raw("
//                    select coa.id,coa.noaccount,coa.namaaccount,psa.ym,
//                    case when psa.hargasatuand is null or left(coa.noaccount,1) in ('4','5') then 0 else psa.hargasatuand end as debet,
//                    case when psa.hargasatuank is null or left(coa.noaccount,1) in ('4','5') then 0 else psa.hargasatuank end as kredit
//                    from chartofaccount_m as coa
//                    join suratkeputusan_m as sk on sk.id=coa.suratkeputusanfk
//                    left JOIN (select * from postingsaldoawal_t where statusenabled=1 and ym = '$strData') as psa  on psa.objectaccountfk=coa.id
//                    where  sk.statusenabled=1 and coa.statusenabled=1  order by coa.noaccount
//                ")
//            );
//        }
        $sama=false;
        foreach ($dataCoa as $coa){
            $sama=false;
            if ($coa->ym == (string)$data) {
                $debetAwal = $coa->debet;
                $kreditAwal = $coa->kredit;
            }else{
                $debetAwal = 0;
                $kreditAwal = 0;
            }


            foreach ($aingMacan as $item) {
                $sama = false;
                if ($item->id == $coa->id) {
                    $debetMutasi = $item->debet;
                    $kreditMutasi = $item->kredit;
                    $result[] = array(
                        'idaccount' => $coa->id,
                        'noaccount' => $item->noaccount,
                        'namaaccount' => $item->namaaccount,
                        'debetAwal' => $debetAwal,
                        'kreditAwal' => $kreditAwal,
                        'debetMutasi' => $debetMutasi,
                        'kreditMutasi' => $kreditMutasi,
                    );
                    $sama = true;
                    break;
                }
            }
            if ($sama == false) {
                $result[] = array(
                    'idaccount' => $coa->id,
                    'noaccount' => $coa->noaccount,
                    'namaaccount' => $coa->namaaccount,
                    'debetAwal' => $debetAwal,
                    'kreditAwal' => $kreditAwal,
                    'debetMutasi' => 0,
                    'kreditMutasi' => 0,
                    'by' => 'as@epic'
                );
            }

        }

        return $this->respond($result);
    }

    public function getDataArusKas(Request $request) {
        // TODO : Akuntansi NERACA
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $tgltgl = $request['tgltgl'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $namaLaporan = $request['namalaporan'];
        //saldo mutasi
        $sql1 = "select mp.noaccount,mp.namaaccount,mp.namaexternal,  x.debet-x.kredit  as total
                from chartofaccount_m as mp
                INNER JOIN suratkeputusan_m as sk on sk.id=mp.suratkeputusanfk
                left join
                (select left(coa.noaccount,1) as noaccount, 
                sum(pjd.hargasatuand) as debet,sum(pjd.hargasatuank) as kredit  
                from chartofaccount_m as coa 
                INNER JOIN postingjurnald_t as pjd ON pjd.objectaccountfk= coa.id
                INNER JOIN postingjurnal_t as pj on pj.norec=pjd.norecrelated
                where coa.kdprofile = $idProfile and pj.tglbuktitransaksi between '$tglAwal' and '$tglAkhir'
                --and coa.reportdisplay='$namaLaporan'  
                group by left(coa.noaccount,1)
                )as x  on x.noaccount = left(mp.noaccount,1)
                where mp.kodeexternal='1' 
                and sk.statusenabled=true  --and mp.reportdisplay='$namaLaporan'
                and left(mp.noaccount,1) in ($namaLaporan) and mp.statusenabled=true";
        $sql2 = "select mp.noaccount,'---' || mp.namaaccount,mp.namaexternal,  x.debet-x.kredit  as total
                from chartofaccount_m as mp
                INNER JOIN suratkeputusan_m as sk on sk.id=mp.suratkeputusanfk
                left join
                (select left(coa.noaccount,3) as noaccount, 
                sum(pjd.hargasatuand) as debet,sum(pjd.hargasatuank) as kredit  
                from chartofaccount_m as coa 
                INNER JOIN postingjurnald_t as pjd ON pjd.objectaccountfk= coa.id
                INNER JOIN postingjurnal_t as pj on pj.norec=pjd.norecrelated
                where coa.kdprofile = $idProfile and pj.tglbuktitransaksi between '$tglAwal' and '$tglAkhir'
                --and coa.reportdisplay='$namaLaporan'  
                group by left(coa.noaccount,3)
                )as x  on x.noaccount = left(mp.noaccount,3)
                where mp.kodeexternal='2' 
                and sk.statusenabled=true  --and mp.reportdisplay='$namaLaporan'
                and left(mp.noaccount,1) in ($namaLaporan) and mp.statusenabled=true
                ";
        $sql3 = "select mp.noaccount,'------' || mp.namaaccount, mp.namaexternal, x.debet-x.kredit  as total
                from chartofaccount_m as mp
                INNER JOIN suratkeputusan_m as sk on sk.id=mp.suratkeputusanfk
                left join
                (select left(coa.noaccount,6) as noaccount, 
                sum(pjd.hargasatuand) as debet,sum(pjd.hargasatuank) as kredit  
                from chartofaccount_m as coa 
                INNER JOIN postingjurnald_t as pjd ON pjd.objectaccountfk= coa.id
                INNER JOIN postingjurnal_t as pj on pj.norec=pjd.norecrelated
                where coa.kdprofile = $idProfile and pj.tglbuktitransaksi between '$tglAwal' and '$tglAkhir'
                 --and coa.reportdisplay='$namaLaporan' 
                group by left(coa.noaccount,6)
                )as x  on x.noaccount = left(mp.noaccount,6)
                where mp.kodeexternal='3' 
                and sk.statusenabled=true  --and mp.reportdisplay='$namaLaporan'
                and left(mp.noaccount,1) in ($namaLaporan) and mp.statusenabled=true";

        $sql = $sql1 . " union all " . $sql2 . " union all " . $sql3 ;

        $sqlFinal = "select * from ($sql) as z  ORDER BY z.noaccount";
        $data = DB::select(DB::raw($sqlFinal));

        //saldo Awal
        $sql11 = "select mp.noaccount,mp.namaaccount,x.debet-x.kredit  as total
                from chartofaccount_m as mp
                INNER JOIN suratkeputusan_m as sk on sk.id=mp.suratkeputusanfk
                left join
                (select left(mp.noaccount,1) as kdmap, 
                sum(pj.hargasatuand) as debet,sum(pj.hargasatuank) as kredit  
                from chartofaccount_m as mp
                INNER JOIN postingsaldoawal_t as pj ON pj.objectaccountfk= mp.id
                where mp.kdprofile = $idProfile and pj.ym='$tgltgl'
                group by left(mp.noaccount,1)
                )as x  on x.kdmap = left(mp.noaccount,1)
                where mp.kodeexternal='1' 
                and left(mp.noaccount,1) in ($namaLaporan) 
                and sk.statusenabled=true and mp.statusenabled=true";
        $sql22 = "select mp.noaccount,'---'  || mp.namaaccount,x.debet-x.kredit  as total
                from chartofaccount_m as mp
                INNER JOIN suratkeputusan_m as sk on sk.id=mp.suratkeputusanfk
                left join
                (select left(mp.noaccount,3) as kdmap, 
                sum(pj.hargasatuand) as debet,sum(pj.hargasatuank) as kredit  
                from chartofaccount_m as mp
                INNER JOIN postingsaldoawal_t as pj ON pj.objectaccountfk= mp.id
                where mp.kdprofile = $idProfile and pj.ym='$tgltgl'
                group by left(mp.noaccount,3)
                )as x  on x.kdmap = left(mp.noaccount,3)
                where mp.kodeexternal='2' 
                and left(mp.noaccount,1) in ($namaLaporan)
                and sk.statusenabled=true and mp.statusenabled=true ";
        $sql33 = "select mp.noaccount,'------'  || mp.namaaccount,x.debet-x.kredit  as total
                from chartofaccount_m as mp
                INNER JOIN suratkeputusan_m as sk on sk.id=mp.suratkeputusanfk
                left join
                (select left(mp.noaccount,6) as kdmap, 
                sum(pj.hargasatuand) as debet,sum(pj.hargasatuank) as kredit  
                from chartofaccount_m as mp
                INNER JOIN postingsaldoawal_t as pj ON pj.objectaccountfk= mp.id
                where mp.kdprofile = $idProfile and pj.ym='$tgltgl'
                group by left(mp.noaccount,6)
                )as x  on x.kdmap = left(mp.noaccount,6)
                where mp.kodeexternal='3' 
                and left(mp.noaccount,1) in ($namaLaporan)
                and sk.statusenabled=true and mp.statusenabled=true";

        $sql2 = $sql11 . " union all " . $sql22 . " union all " . $sql33 ;

        $sqlFinal2= "select * from ($sql2) as z  ORDER BY z.noaccount";
        $data2 = DB::select(DB::raw($sqlFinal2));

        $result =[];
        $total=0;
        $total2=0;
        foreach ($data as $item){
            $total=0;
            $total2=0;
            foreach ($data2 as $itm){
                if ($item->noaccount == $itm->noaccount){
                    $total2 = $itm->total;
                    if ((float)$itm->total < 0){
                        $total2 = $total2 * (-1);
                    }
                }
            }
            $total = $item->total;
            if ((float)$item->total < 0){
                $total = $total * (-1);
            }
            $result[] = array(
                'kdmap' => $item->noaccount,
                'nomap' => $item->namaexternal,
                'namamap' => $item->namaaccount,
                'total' => $total2,
                'total2' => $total,
                'total3' => $total2 + $total ,
            );
        }

        return $this->respond($result);
    }

    public function getDataComboMasterAkun(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$idProfile));
        $kdBankDarah = (int) $this->settingDataFixed('KdRuanganBankDarah',$idProfile);
        $kdDeptJalan = (int) $this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $kdDeptRjRi = explode(',',$this->settingDataFixed('KdDepartemenRJRI',$idProfile));
        $kdDeptRanapAll = explode(',',$this->settingDataFixed('KdDepartemenRIAll',$idProfile));
        $kdJeniPegawaiDokter = (int) $this->settingDataFixed('KdJenisPegawaiDokter',$idProfile);
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $kdDepartemenRajalRanap = [];
        foreach ($kdDeptRjRi as $item){
            $kdDepartemenRajalRanap []=  (int)$item;
        }
        $kdDepartemenRanapAll = [];
        foreach ($kdDeptRanapAll as $items){
            $kdDepartemenRanapAll []=  (int)$items;
        }
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id', $kdDepartemenRawatPelayanan)
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $darah = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.kdruangan', $kdBankDarah)
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile', $idProfile)
            ->where('dept.id', $kdDeptJalan)
            ->orderBy('dept.namadepartemen')
            ->get();

        $departemen = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile', $idProfile)
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile', $idProfile)
            ->whereIn('dept.id', $kdDepartemenRajalRanap)
            ->orderBy('dept.namadepartemen')
            ->get();

        $ruanganRi = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->whereIn('ru.objectdepartemenfk',$kdDepartemenRanapAll)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->where('ru.objectjenispegawaifk', $kdJeniPegawaiDokter)
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
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataKelas = \DB::table('kelas_m as kl')
            ->select('kl.id', 'kl.reportdisplay')
            ->where('kl.kdprofile', $idProfile)
            ->where('kl.statusenabled', true)
            ->orderBy('kl.reportdisplay')
            ->get();

        $pembatalan = \DB::table('pembatal_m as p')
            ->select('p.id', 'p.name')
            ->where('p.kdprofile', $idProfile)
            ->where('p.statusenabled', true)
            ->orderBy('p.name')
            ->get();

        $kdPelayananRanap = \DB::table('settingdatafixed_m as p')
            ->select('p.nilaifield')
            ->where('p.kdprofile', $idProfile)
            ->where('p.statusenabled', true)
            ->where('p.namafield','kddeptlayananRI')
            ->first();

        $kdPelayananOk = \DB::table('settingdatafixed_m as p')
            ->select('p.nilaifield')
            ->where('p.kdprofile', $idProfile)
            ->where('p.statusenabled', true)
            ->where('p.namafield','KdPelayananOk')
            ->first();

        $dataKelompokTanpaUmum = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled', true)
            ->where('kp.id', '<>', 1)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataJenisAccount= \DB::table('jenisaccount_m as jd')
            ->where('jd.kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->get();
        $dataKategoryAccount= \DB::table('kategoryaccount_m as jd')
            ->where('jd.kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->get();
        $dataStatusAccount= \DB::table('statusaccount_m as jd')
            ->where('jd.kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->get();
        $dataStrukturAccount= \DB::table('strukturaccount_m as jd')
            ->where('jd.kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->get();

        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'datalogin' => $dataLogin,
            'kelas' => $dataKelas,
            'darah' => $darah,
            'dept' => $dept,
            'ruanganRi' => $ruanganRi,
            'deptrirj' => $deptRajalInap,
            'ruanganall' => $dataRuangan,
            'pembatalan' => $pembatalan,
            'deptt' => $departemen,
//            'rekanan' => $dataRekanan,
            'kelompokpasiensatu' => $dataKelompokTanpaUmum,
            'kddeptlayananranap' => $kdPelayananRanap,
            'kddeptlayananok' => $kdPelayananOk,
            'jenisaccount' =>   $dataJenisAccount,
            'kategoryaccount' =>   $dataKategoryAccount,
            'statusaccount' =>   $dataStatusAccount,
            'strukturaccount' =>   $dataStrukturAccount,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDataPiutangPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $departemen = '';
//        if ($request['departemenid'] != ''){
//            $departemen =' and dp.id = ' . $request['departemenid'];
//        }
        $ruangan = '';
//        if ($request['ruanganid'] != ''){
//            $ruangan =' and apd.objectruanganfk = ' . $request['ruanganid'];
//        }
        $kelompok = '';
        if ($request['kelompokid'] != ''){
            $kelompok =' and pd.objectkelompokpasienlastfk = ' . $request['kelompokid'];
        }
        $data = DB::select(DB::raw("select pd.tglregistrasi, pd.noregistrasi,ps.namapasien,dp.namadepartemen as instalasi,ru.namaruangan as ruangan, 
                        0 as produkfk,rkn.namarekanan as namaproduk,
                        sp.totalprekanan as  hargasatuan,0 as hargadiscount,1 as jumlah ,sp.totalprekanan as total,0 as jasa,
                        pj.nojurnal_intern as nojurnal,pj.norec as norec_pj,pd.norec as norec_pd,0 as norec_apd,sp.norec as norec_pp,
                        0 as objectjenisprodukfk,pd.tglpulang as tglpelayanan
                        from pasiendaftar_t as pd 
                        INNER JOIN ruangan_m as ru on ru.id=pd.objectruanganlastfk
                        INNER JOIN departemen_m as dp on dp.id=ru.objectdepartemenfk
                        INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                        INNER JOIN strukpelayanan_t as sp on sp.noregistrasifk=pd.norec
                        INNER JOIN rekanan_m as rkn on rkn.id=pd.objectrekananfk
                        left JOIN postingjurnaltransaksi_t as pj on pj.norecrelated=sp.norec and pj.deskripsiproduktransaksi='verifikasi_tarek'
                    where pd.kdprofile = $idProfile and pd.tglpulang between '$tglAwal' and '$tglAkhir' and sp.statusenabled is null $departemen $ruangan $kelompok
                    --limit 10
            ")
        );

        $data2 = DB::select(DB::raw("select pj.norec,sp.norec as norec_pp,
                    case when pjd.hargasatuand > 0 then coa.namaaccount else '' end as debet,
                    case when pjd.hargasatuank > 0 then coa.namaaccount else '' end as kredit,
                    case when pjd.hargasatuand > 0 then pjd.hargasatuand else pjd.hargasatuank end as hargajurnal
                    from pasiendaftar_t as pd 
                    INNER JOIN ruangan_m as ru on ru.id=pd.objectruanganlastfk
                    INNER JOIN departemen_m as dp on dp.id=ru.objectdepartemenfk
                    INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                    INNER JOIN strukpelayanan_t as sp on sp.noregistrasifk=pd.norec
                    INNER JOIN postingjurnaltransaksi_t as pj on pj.norecrelated=sp.norec
                    INNER JOIN postingjurnaltransaksid_t as pjd on pjd.norecrelated=pj.norec
                    INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                    where pd.kdprofile = $idProfile and pj.deskripsiproduktransaksi='verifikasi_tarek' and sp.statusenabled is null  and pd.tglpulang between '$tglAwal' and '$tglAkhir' $departemen $ruangan $kelompok
                    
            ")
        );
        $result = [];
        $debet = '';
        $kredit = '';
        foreach ($data as $item){
            $debet = '';
            $kredit = '';
            $hrgJurnal = 0;
            foreach ($data2 as $item2){
                if ($item->norec_pp == $item2->norec_pp){
                    if ($item2->debet != ''){
                        $debet = $item2->debet;
                    }
                    if ($item2->kredit != ''){
                        $kredit = $item2->kredit;
                    }
                    $hrgJurnal = $item2->hargajurnal;
                }
                if ($debet != '' and $kredit != ''){
                    break;
                }
            }
            $jenis='';
//            if ($item->objectjenisprodukfk != 97) {
//                if ($item->produkfk == 395 ) {//administrasi
//                    $jenis = 'Administrasi';
//                }elseif ($item->produkfk == 10011572 ) {//administrasi
//                    $jenis = 'Administrasi';
//                }elseif ( $item->produkfk == 10011571) {//administrasi
//                    $jenis = 'Administrasi';
//                }else{
//                    if ($item->objectjenisprodukfk == 101){//visite
//                        $jenis = 'Visite';
//                    }elseif ( $item->objectjenisprodukfk == 100){//konsultasi
//                        $jenis = 'Konsultasi';
//                    }elseif ($item->objectjenisprodukfk == 99 ){//akomodasi
//                        $jenis = 'Akomodasi';
//                    }elseif ($item->objectjenisprodukfk == 27666 ){//alat canggih
//                        $jenis = 'Alat Canggih';
//                    }else{//Tindakan
//                        $jenis = 'Tindakan';
//                    }
//                };
//            } else {//OBAT
            $jenis = 'Verifikasi Piutang';
//            };
            $result[] = array(
                'tglregistrasi' => $item->tglregistrasi,
                'noregistrasi' => $item->noregistrasi,
                'namapasien' => $item->namapasien,
                'instalasi' => $item->instalasi,
                'ruangan' => $item->ruangan,
                'produkfk' => $item->produkfk,
                'namaproduk' => $item->namaproduk,
                'hargasatuan' => $item->hargasatuan,
                'hargadiscount' => $item->hargadiscount,
                'jumlah' => $item->jumlah,
                'jasa' => $item->jasa,
                'total' => $item->total,
                'hargajurnal' => $hrgJurnal,
                'nojurnal' => $item->nojurnal,
                'debet' => $debet,
                'kredit' => $kredit,
                'jenis' => $jenis,
                'tglpelayanan' => $item->tglpelayanan,
                'norec' => $item->norec_pp,
                'norec_pj' => $item->norec_pj,
            );
        }

        $result = array(
            'data' => $result,
            'by' => 'as@epic'
        );
        return $this->respond($result);
    }

    public function getDaftarCoa(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('chartofaccount_m as coa')
            ->select('coa.norec','coa.id','coa.noaccount','coa.namaaccount',
                'coa.objectjenisaccountfk','ja.jenisaccount',
                'coa.objectkategoryaccountfk','ka.kategoryaccount',
                'coa.objectstatusaccountfk','sa.statusaccount',
                'coa.objectstrukturaccountfk','sta.strukturaccount',
                'coa.saldonormaladd','coa.saldonormalmin','coa.statusenabled')
            ->leftJOIN('jenisaccount_m as ja','ja.id','=','coa.objectjenisaccountfk')
            ->leftJOIN('kategoryaccount_m as ka','ka.id','=','coa.objectkategoryaccountfk')
            ->leftJOIN('statusaccount_m as sa','sa.id','=','coa.objectstatusaccountfk')
            ->leftJOIN('strukturaccount_m as sta','sta.id','=','coa.objectstrukturaccountfk')
            ->JOIN('suratkeputusan_m as sk','sk.id','=','coa.suratkeputusanfk')
            ->where('coa.kdprofile',$idProfile)
            ->where('sk.statusenabled','=',1)
            ->orderBy('coa.noaccount');


        if(isset($request['noaccount']) && $request['noaccount']!="" && $request['noaccount']!="undefined"){
            $data = $data->where('noaccount','ilike',''. $request['noaccount'].'%');
        }
        if(isset($request['namaaccount']) && $request['namaaccount']!="" && $request['namaaccount']!="undefined"){
            $data = $data->where('namaaccount','ilike','%'. $request['namaaccount'].'%');
        }
        $data = $data->take(100);
        $data = $data->get();
        return $this->respond($data);
    }

    public function SaveDataChartOfAccount(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();

        try {
            if ($request['id'] == ''){
                $lasID =  ChartOfAccount::max('id');
                $newID = $lasID + 1;

                $newCOA = new ChartOfAccount();
                $norecHead = $newCOA->generateNewId();
                $newCOA->id = $newID;
                $newCOA->kdprofile = $idProfile;
                $newCOA->norec = $norecHead;
                $newCOA->qaccount = $newID;
            }else{

                $newCOA =  ChartOfAccount::where('id',$request['id'])->where('kdprofile', $idProfile)->first();
            }
            $newCOA->namaexternal = '2018-03-01';
            $newCOA->statusenabled = $request['statusenabled'];
            $newCOA->objectjenisaccountfk = $request['objectjenisaccountfk'];
            $newCOA->objectkategoryaccountfk = $request['objectkategoryaccountfk'];
            $newCOA->objectstatusaccountfk = $request['objectstatusaccountfk'];
            $newCOA->objectstrukturaccountfk = $request['objectstrukturaccountfk'];
            $newCOA->noaccount = $request['kdaccount'];
            $newCOA->namaaccount = $request['namaaccount'];
            $newCOA->saldonormaladd = $request['saldonormaladd'];
            $newCOA->saldonormalmin = $request['saldonormalmin'];
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
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newPJT,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function BengkelJurnal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
//        $dataLogin = $request->all();
        $noJurnal = $request['nojurnal'];
        try{
            $countDeleteNa = 0;
            $delDetail = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (
                        select 
                        pj.norec
                        from postingjurnaltransaksi_t as pj
                        INNER JOIN postingjurnaltransaksid_t as pjd on pj.norec=pjd.norecrelated
                        INNER JOIN pelayananpasien_t as pp on pp.norec=pj.norecrelated
                        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                        INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                        INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                        where pj.kdprofile = $idProfile and nojurnal_intern='$noJurnal' and pjd.hargasatuank >0
                        and case when pjd.hargasatuand =0 then  pjd.hargasatuank else pjd.hargasatuand end  <>
                        ((case when pp.hargajual is null then 0 else pp.hargajual end ) * pp.jumlah ) + case when pp.jasa is null then 0 else pp.jasa end
                        and pj.deskripsiproduktransaksi = 'pelayananpasien_t'
                        --(case when pp.hargajual is null then 0 else pp.hargajual end-(case when pp.hargadiscount is null then 0 else pp.hargadiscount end)) *pp.jumlah 
                    );
                "
            );
            $delDetail = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (
                        select 
                        pj.norec
                        from postingjurnaltransaksi_t as pj
                        INNER JOIN postingjurnaltransaksid_t as pjd on pj.norec=pjd.norecrelated
                        INNER JOIN strukpelayanandetail_t as spd on spd.norec=pj.norecrelated
                        inner join strukpelayanan_t as sp on sp.norec=spd.nostrukfk
                        where pj.kdprofile = $idProfile and pj.nojurnal_intern='$noJurnal' and pjd.hargasatuank >0
                        and case when pjd.hargasatuand =0 then  pjd.hargasatuank else pjd.hargasatuand end  <>
                        ((spd.hargasatuan  )*spd.qtyproduk)+ spd.hargatambahan
                         and substring(sp.nostruk,1,2)='OB' and pj.deskripsiproduktransaksi = 'pelayananpasien_tob'
                    );
                "
            );
            $delHead = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (
                        select pj.norec from postingjurnaltransaksi_t as pj
                        left JOIN postingjurnaltransaksid_t as pjd on pjd.norecrelated=pj.norec
                        where pj.kdprofile = $idProfile and pj.nojurnal_intern='$noJurnal' and pjd.norec is null 
                      );
                "
            );

            $delPelayanan = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
                    left JOIN pelayananpasien_t as pp on pp.norec=pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='pelayananpasien_t' and pp.norec is null and posted.nojurnal_intern is null
                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal')"
            );
            $delPelayananHead = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
                    left JOIN pelayananpasien_t as pp on pp.norec=pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='pelayananpasien_t' and pp.norec is null and posted.nojurnal_intern is null
                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal');"
            );

            $delObatBebas = DB::delete("
                        delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
                        left JOIN strukpelayanandetail_t as pp on pp.norec=pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
                        left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                        where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='pelayananpasien_tob' and pp.norec is null and posted.nojurnal_intern is null
                        and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal')"
            );
            $delObatBebasHead = DB::delete("
                        delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
                        left JOIN strukpelayanandetail_t as pp on pp.norec=pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
                        left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                        where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='pelayananpasien_tob' and pp.norec is null and posted.nojurnal_intern is null
                        and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal');"
            );

            $delVerifTarek = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
                    INNER JOIN  strukpelayanan_t as pp on pp.norec=pjt.norecrelated and pp.tglstruk >'2019-01-01 00:00'
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='verifikasi_tarek' and pp.statusenabled=0 and posted.nojurnal_intern is null
                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal');"
            );
            $delVerifTarekHead = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
                    INNER JOIN  strukpelayanan_t as pp on pp.norec=pjt.norecrelated and pp.tglstruk >'2019-01-01 00:00'
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='verifikasi_tarek' and pp.statusenabled=0 and posted.nojurnal_intern is null
                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal');"
            );

            $delDeposit = DB::delete("
                delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
                INNER JOIN  strukbuktipenerimaan_t as pp on pp.norec=pjt.norecrelated and pp.tglsbm >'2019-01-01 00:00'
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='penerimaan_deposit' and pp.norec is null and posted.nojurnal_intern is null
                and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal');"
            );
            $delDepositHead = DB::delete("
                delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
                INNER JOIN  strukbuktipenerimaan_t as pp on pp.norec=pjt.norecrelated and pp.tglsbm >'2019-01-01 00:00'
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='penerimaan_deposit' and pp.norec is null and posted.nojurnal_intern is null
                and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal');"
            );
            $delDiskon = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
                    INNER JOIN  pelayananpasien_t as pp on pp.norec =pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='diskon' and pp.norec is null and posted.nojurnal_intern is null
                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal');"
            );
            $delDiskonHead = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
                    INNER JOIN  pelayananpasien_t as pp on pp.norec =pjt.norecrelated and pp.tglpelayanan >'2019-01-01 00:00'
                    left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                    where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='diskon' and pp.norec is null and posted.nojurnal_intern is null
                    and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal');"
            );
            $delPenerimaanKas = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
                INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.norec=pjt.norecrelated
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk and sbm.tglsbm  >'2019-01-01 00:00'
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' and (sbmc.totaldibayar is null or sbmc.totaldibayar = 0) and posted.nojurnal_intern is null
                and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal') "
            );
            $delPenerimaanKasHead = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
                INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.norec=pjt.norecrelated
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk and sbm.tglsbm  >'2019-01-01 00:00'
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='penerimaan_kas' and (sbmc.totaldibayar is null or sbmc.totaldibayar = 0) and posted.nojurnal_intern is null
                and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal')"
            );
//            $countDeleteNa =count($delHead) + count($delPelayananHead) + count($delObatBebasHead) + count($delVerifTarekHead) +
//                count($delDepositHead) + count($delDiskonHead) + count($delPenerimaanKasHead) + count($delTerimaBarangHead) + count($delAmprahanHead);


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Perbaikan Jurnal" ;


        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => 0,//$countDeleteNa,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function HapusDoubleJurnal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //“Don’t stop when you’re tired; stop when you’re done.”
        //— Marilyn Monroe
        DB::beginTransaction();
//        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $noJurnal = $request['nojurnal'];
        try{
            $delDetailVerif = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (
                        select pjt.norec from postingjurnaltransaksi_t as pjt
                        INNER JOIN strukpelayanan_t as sp on sp.norec=pjt.norecrelated 
                        where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='verifikasi_tarek' and sp.statusenabled = 0  
                        and nojurnal_intern='$noJurnal')
                "
            );
            $delHeadVerif = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (
                        select pjt.norec from postingjurnaltransaksi_t as pjt
                        INNER JOIN strukpelayanan_t as sp on sp.norec=pjt.norecrelated 
                        where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='verifikasi_tarek' and sp.statusenabled = 0  
                        and nojurnal_intern='$noJurnal');
                "
            );
            $delDetail = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (
                    select norec from (select norec, row_number() over (partition by norecrelated order by norec desc) as rownum  from postingjurnaltransaksi_t 
                    where kdprofile = $idProfile and 
                    --deskripsiproduktransaksi='pelayananpasien_t' and 
                    tglbuktitransaksi between '$tglAwal' and '$tglAkhir'  and nojurnal_intern='$noJurnal')
                    as x 
                    where x.rownum >1)
                "
            );
            $delHead = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (
                    select norec from (select norec, row_number() over (partition by norecrelated order by norec desc) as rownum  from postingjurnaltransaksi_t 
                    where kdprofile = $idProfile and 
                    --deskripsiproduktransaksi='pelayananpasien_t' and 
                    tglbuktitransaksi between '$tglAwal' and '$tglAkhir' and nojurnal_intern='$noJurnal')
                    as x where x.rownum >1);
                "
            );
            $delTerimaBarang = DB::delete("
                delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
                INNER JOIN strukpelayanan_t as sp on sp.norec=pjt.norecrelated and sp.tglstruk >'2019-01-01 00:00'
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='penerimaan_barang' and sp.norec is null  and posted.nojurnal_intern is null
                and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal') "
            );
            $delTerimaBarangHead = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
                INNER JOIN strukpelayanan_t as sp on sp.norec=pjt.norecrelated and sp.tglstruk >'2019-01-01 00:00'
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='penerimaan_barang'  and sp.norec is null  and posted.nojurnal_intern is null
                and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal')"
            );
            $delAmprahan = DB::delete("
                    delete from postingjurnaltransaksid_t where norecrelated in (select pjt.norec from postingjurnaltransaksi_t as pjt
                INNER JOIN strukkirim_t as sp on sp.norec=pjt.norecrelated and sp.tglkirim  >'2019-01-01 00:00'
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='amprahan_barang_ruangan' and sp.norec is null  and posted.nojurnal_intern is null
                and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal')"
            );
            $delAmprahanHead = DB::delete("
                    delete from postingjurnaltransaksi_t where norec in (select pjt.norec from postingjurnaltransaksi_t as pjt
                INNER JOIN strukkirim_t as sp on sp.norec=pjt.norecrelated and sp.tglkirim  >'2019-01-01 00:00'
                left JOIN postingjurnal_t as posted on pjt.nojurnal_intern=posted.norecrelated
                where pjt.kdprofile = $idProfile and pjt.deskripsiproduktransaksi='amprahan_barang_ruangan' and sp.norec is null  and posted.nojurnal_intern is null
                and pjt.tglbuktitransaksi  >'2019-01-01 00:00' and pjt.nojurnal_intern='$noJurnal')"
            );
//            $jumlahna = count($delHead);
//            $delDetail2 = DB::delete("
//                    delete from postingjurnaltransaksid_t where norecrelated in (select pj.norec from postingjurnaltransaksi_t as pj
//                        INNER JOIN postingjurnaltransaksid_t as pjd on pjd.norecrelated=pj.norec
//                        INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
//                        INNER JOIN pelayananpasien_t as pp on pp.norec=pj.norecrelated
//                        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
//                        inner join produk_m as pr on pr.id=pp.produkfk
//                        inner join detailjenisproduk_m as djp on djp.id=pr.objectdetailjenisprodukfk
//                        where pj.tglbuktitransaksi between '$tglAwal' and '$tglAkhir' and pjd.hargasatuank >0 and pj.nojurnal_intern='$noJurnal'
//                        and coa.namaaccount not like '%' + ru.namaruangan + '' and pp.strukresepfk is null
//                        and djp.objectjenisprodukfk <> 97 and pj.deskripsiproduktransaksi='pelayananpasien_t'
//                        and pp.produkfk not in (10011572,10011571))
//                "
//            );
//            $delHead2 = DB::delete("
//                    delete from postingjurnaltransaksi_t where norec in (select pj.norec from postingjurnaltransaksi_t as pj
//                        INNER JOIN postingjurnaltransaksid_t as pjd on pjd.norecrelated=pj.norec
//                        INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
//                        INNER JOIN pelayananpasien_t as pp on pp.norec=pj.norecrelated
//                        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
//                        inner join produk_m as pr on pr.id=pp.produkfk
//                        inner join detailjenisproduk_m as djp on djp.id=pr.objectdetailjenisprodukfk
//                        where pj.tglbuktitransaksi between '$tglAwal' and '$tglAkhir' and pjd.hargasatuank >0 and pj.nojurnal_intern='$noJurnal'
//                        and coa.namaaccount not like '%' + ru.namaruangan + '' and pp.strukresepfk is null
//                        and djp.objectjenisprodukfk <> 97 and pj.deskripsiproduktransaksi='pelayananpasien_t'
//                        and pp.produkfk not in (10011572,10011571))
//                "
//            );
//            $jumlahna = $jumlahna  + count($delHead2);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Perbaikan Jurnal" ;


        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => 0,//$jumlahna,
                "data2" => 0,//count($delHead2),
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data2" => $delHead2,
//                "data3" => $delDetail2,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function PostingJurnal_pembayaran_tagihan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
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

            $aingMacan = DB::select(DB::raw("select top 100 ps.nocm,ps.namapasien,sbm.nosbm,case when sbmc.totaldibayar is null then sbm.totaldibayar else sbmc.totaldibayar end as totaldibayar,sbm.tglsbm,sbmc.objectcarabayarfk ,
                    sbmc.norec as norec_smbc,sbm.keteranganlainnya,convert(VARCHAR ,sbm.tglsbm, 23) as tgl,pd.objectkelompokpasienlastfk
                    from strukbuktipenerimaancarabayar_t as sbmc
                    INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk
                    INNER JOIN strukpelayanan_t as sp on sp.nosbmlastfk=sbm.norec
                    INNER JOIN pasien_m as ps on ps.id=sp.nocmfk
                    INNER JOIN pasiendaftar_t as pd on pd.norec=sp.noregistrasifk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sbmc.norec
                    where sbmc.kdprofile = $idProfile and sbm.tglsbm BETWEEN :tglAwal and :tglAkhir and pjt.norec is null and sbm.statusenabled <>0 and (sbmc.totaldibayar is not null or sbmc.totaldibayar > 0)
                    and sbm.keteranganlainnya in ('Pembayaran Tagihan Pasien','Pembayaran Tagihan Non Layanan') "),
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
                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->where('kdprofile',$idProfile)->get();

                if (count($cekSudahPosting) == 0) {
                    $newPJT->kdprofile = $idProfile;
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
                    $newPJT->statusenabled = 1;
                    $newPJT->norecrelated = $item->norec_smbc;
                    $newPJT->save();


                    $debetId = 10891;//'Kas BLUD di Bendahara Penerimaan'
                    if ($item->objectkelompokpasienlastfk == 2) {//BPJS
                        $kreditId = 10897;
                    }
                    if ($item->objectkelompokpasienlastfk == 4) {//BPJS non PBI
                        $kreditId = 10897;
                    }
                    if ($item->objectkelompokpasienlastfk == 1){//1	Umum/Pribadi
                        $kreditId = 10896;
                    }
                    if ($item->objectkelompokpasienlastfk == 2){//2 BPJS
                        $kreditId = 10897;
                    }
                    if ($item->objectkelompokpasienlastfk == 3){//3	Asuransi lain
                        $kreditId = 10901;
                    }
                    if ($item->objectkelompokpasienlastfk == 5){//5	Perusahaan
                        $kreditId = 10901;
                    }
                    if ($item->objectkelompokpasienlastfk == 6){//6	Perjanjian
                        $kreditId = 10896;
                    }
                    if ($item->objectkelompokpasienlastfk == 7){//7	Dinas Sosial
                        $kreditId = 10900;
                    }
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
                    $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                    $postingJurnalTransaksiD->kdprofile = $idProfile;
                    $postingJurnalTransaksiD->nojurnal = 0;//$nojurnal;
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

    public function PostingJurnal_strukpelayanan_t_verifikasi_tarek(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
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
            $aingMacan = DB::select(DB::raw("select distinct   sp.norec,sp.nostruk,pd.noregistrasi,ps.namapasien,sp.tglstruk as tglstruk,sp.totalharusdibayar,sp.totalprekanan,
                      kp.id as kpid,ru.objectdepartemenfk,ru.objectdepartemenfk as dept_pd,convert(VARCHAR, sp.tglstruk, 23) as tgl,pd.objectruanganlastfk,pd.objectrekananfk,
                      ru.namaruangan,rkn.namarekanan
                    from pelayananpasien_t as pp
                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                    INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                    INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                    INNER JOIN ruangan_m as ru on ru.id=pd.objectruanganlastfk
                    INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                    INNER JOIN kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk
                    INNER JOIN rekanan_m as rkn on rkn.id=pd.objectrekananfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sp.norec
                    where pp.kdprofile = $idProfile and sp.tglstruk BETWEEN :tglAwal and :tglAkhir and pjt.norec is null --and pd.tglpulang is not null --and sp.totalprekanan >0
                    order by sp.norec "),
                array(
                    'tglAwal' => $request['tglAwal'],
                    'tglAkhir' => $request['tglAkhir'],
                )
            );
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

                $cekSudahPosting =  PostingJurnal::where('norecrelated',$noJurnalIntern)->get();
                if ($this->getCountArray($cekSudahPosting) == 0) {
                    $postingJurnalTransaksi = new PostingJurnalTransaksi;
                    $norecHead = $postingJurnalTransaksi->generateNewId();
                    $postingJurnalTransaksi->norec = $norecHead;
                    $postingJurnalTransaksi->kdprofile = $idProfile;
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
                        if ($item->kpid == 1){//1	Umum/Pribadi
                            $debetId = 10896;
                        }
                        if ($item->kpid == 2) {//BPJS
                            $debetId = 10897;//PIUTANG BPJS
                        }
                        if ($item->kpid == 3){//3	Asuransi lain
                            $debetId = 10901;
                        }
                        if ($item->kpid == 4) {//BPJS non PBI
                            $debetId = 10897;//PIUTANG BPJS
                        }
                        if ($item->kpid == 5){//5	Perusahaan
                            $debetId = 10901;
                        }
                        if ($item->kpid == 6){//6	Perjanjian
                            $debetId = 10896;
                        }
                        if ($item->kpid == 7){//7	Dinas Sosial
                            $debetId = 10900;
                        }

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        if ($item->kpid == 1){//1	Umum/Pribadi
                            $debetId2 = 10896;
                        }
                        if ($item->kpid == 2) {//BPJS
                            $debetId2 = 10897;//PIUTANG BPJS
                        }
                        if ($item->kpid == 3){//3	Asuransi lain
                            $debetId2 = 10901;
                        }
                        if ($item->kpid == 4) {//BPJS non PBI
                            $debetId2 = 10897;//PIUTANG BPJS
                        }
                        if ($item->kpid == 5){//5	Perusahaan
                            $debetId2 = 10901;
                        }
                        if ($item->kpid == 6){//6	Perjanjian
                            $debetId2 = 10896;
                        }
                        if ($item->kpid == 7){//7	Dinas Sosial
                            $debetId2 = 10900;
                        }

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                    $postingJurnalTransaksi->kdprofile = $idProfile;
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
                        if ($item->kpid == 1){//1	Umum/Pribadi
                            $debetId = 10896;
                        }
                        if ($item->kpid == 2) {//BPJS
                            $debetId = 10897;//PIUTANG BPJS
                        }
                        if ($item->kpid == 3){//3	Asuransi lain
                            $debetId = 10901;
                        }
                        if ($item->kpid == 4) {//BPJS non PBI
                            $debetId = 10897;//PIUTANG BPJS
                        }
                        if ($item->kpid == 5){//5	Perusahaan
                            $debetId = 10901;
                        }
                        if ($item->kpid == 6){//6	Perjanjian
                            $debetId = 10896;
                        }
                        if ($item->kpid == 7){//7	Dinas Sosial
                            $debetId = 10900;
                        }

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        if ($item->kpid == 1){//1	Umum/Pribadi
                            $debetId = 10896;
                        }
                        if ($item->kpid == 2) {//BPJS
                            $debetId = 10897;//PIUTANG BPJS
                        }
                        if ($item->kpid == 3){//3	Asuransi lain
                            $debetId = 10901;
                        }
                        if ($item->kpid == 4) {//BPJS non PBI
                            $debetId = 10897;//PIUTANG BPJS
                        }
                        if ($item->kpid == 5){//5	Perusahaan
                            $debetId = 10901;
                        }
                        if ($item->kpid == 6){//6	Perjanjian
                            $debetId = 10896;
                        }
                        if ($item->kpid == 7){//7	Dinas Sosial
                            $debetId = 10900;
                        }

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                    sbmc.norec as norec_smbc,convert(varchar, sbm.tglsbm, 23) as tgl
                    from strukbuktipenerimaancarabayar_t as sbmc
                    INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec=sbmc.nosbmfk
                    left JOIN strukpelayanan_t as sp on sp.norec=sbm.nostrukfk
                    left JOIN pasien_m as ps on ps.id=sp.nocmfk
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated=sbmc.norec
                    where sbmc.kdprofile = $idProfile and sbm.tglsbm BETWEEN :tglAwal and :tglAkhir and pjt.norec is null
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
                    $cekSudahPosting = PostingJurnal::where('norecrelated', $noJurnalIntern)->where('kdprofile',$idProfile)->get();

                    if ($this->getCountArray($cekSudahPosting) == 0) {

                        $newPJT = new PostingJurnalTransaksi;
                        $norecHead = $newPJT->generateNewId();
                        $newPJT->norec = $norecHead;
                        $newPJT->kdprofile = $idProfile;
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
                        $newPJT->statusenabled = 1;
                        $newPJT->norecrelated = $item->norec_smbc;
                        $newPJT->save();

                        $norec_pj = $postingJurnalTransaksi->norec;

                        $debetId = 11153;//Uang Muka Layanan
                        $kreditId = 1778;//Piutang Pasien dalam perawatan

                        //debet
                        $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                        $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        $newPJT->kdprofile = $idProfile;
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
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                        $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                    pp.norec as norec_pp,convert(varchar, pp.tglpelayanan, 23) as tgl,pjt.norec
                    from pasiendaftar_t pd 
                    inner join antrianpasiendiperiksa_t adp on adp.noregistrasifk = pd.norec 
                    inner join pasien_m as ps on ps.id = pd.nocmfk 
                    left join pelayananpasien_t pp on pp.noregistrasifk = adp.norec
                    left JOIN postingjurnaltransaksi_t as pjt on pjt.norecrelated  = pp.norec and pjt.deskripsiproduktransaksi='diskon'
                    where pd.kdprofile = $idProfile and pp.tglpelayanan BETWEEN :tglAwal and :tglAkhir and pjt.norec is null
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
                    $newPJT->kdprofile = $idProfile;
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
                    $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                    $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                    $newPJT->kdprofile = $idProfile;
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
                    $postingJurnalTransaksiD->kdprofile = $idProfile;
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
                    $postingJurnalTransaksiD->kdprofile = 1;
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
            $transMessage = $transMessage . "";
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
            $transMessage = $transMessage ." Gagal!!";
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
    public function getDetailJurnalRev2018(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::select(DB::raw("select pj.norec, pjd.objectaccountfk as accountid, pj.nojurnal,coa.noaccount,
                case when pjd.hargasatuand = 0 then '--- ' || coa.namaaccount else coa.namaaccount end as namaaccount,
                pj.namaproduktransaksi as keteranganlainnya,pjd.hargasatuand,pjd.hargasatuank from postingjurnaltransaksi_t as pj
                INNER JOIN postingjurnaltransaksid_t as pjd on pj.norec=pjd.norecrelated
                INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                where pj.kdprofile = $idProfile and nojurnal_intern=:nojurnal;"),
            array(
                'nojurnal' => $request['nojurnal'],
            )
        );
        return $this->respond($data);
    }
    public function getDetailJurnalPosting(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::select(DB::raw("select pjd.objectaccountfk as accountid, coa.noaccount,
                case when pjd.hargasatuand = 0 then '--- ' || coa.namaaccount else coa.namaaccount end as namaaccount,
                sum(pjd.hargasatuand) as hargasatuand,sum(pjd.hargasatuank) as hargasatuank, '' as keteranganlainnya,
                '' as nojurnal from postingjurnaltransaksi_t as pj
                INNER JOIN postingjurnaltransaksid_t as pjd on pj.norec=pjd.norecrelated
                INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                where pj.kdprofile = $idProfile and pj.nojurnal_intern=:nojurnal
                group by pjd.objectaccountfk, coa.noaccount,case when pjd.hargasatuand = 0 then '--- ' || coa.namaaccount else coa.namaaccount end 
                order by sum(pjd.hargasatuank);"),
            array(
                'nojurnal' => $request['nojurnal'],
            )
        );
        return $this->respond($data);
    }
    public function getCoaSaeutik(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $data=[];
        $data = \DB::table('chartofaccount_m as pr')
            ->select('pr.id', 'pr.noaccount', 'pr.namaaccount')
            ->join('suratkeputusan_m as sk', 'sk.id', '=', 'pr.suratkeputusanfk')
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.statusenabled', '=',true)
//            ->where('pr.namaexternal', '2018-03-01')
            ->where('sk.statusenabled','=',true)
            ->orderBy('pr.noaccount');

//        if ($request['jenis'] != 'noaccount'){
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data->where('pr.namaaccount','ilike','%'. $req['filter']['filters'][0]['value'].'%' )
                ->orWhere('pr.noaccount','ilike',$req['filter']['filters'][0]['value'].'%');
        };
//        }else{
//        if(isset($req['filter']['filters'][0]['value']) &&
//            $req['filter']['filters'][0]['value']!="" &&
//            $req['filter']['filters'][0]['value']!="undefined"){
//            $data = $data->where('pr.noaccount','ilike', $req['filter']['filters'][0]['value'].'%' );
//        };
//    }

        $data = $data->take(10);
        $data = $data->get();
        return $this->respond($data);
    }
    public function PostingHapusJurnal_entry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();
        $nojurnal = $dataReq['head'];
        try {
            if ($dataReq['head'] != '-'){
//                $delDetail = DB::raw("
//                    delete from postingjurnaltransaksid_t
//                    where norecrelated in (select norec from postingjurnaltransaksi_t where nojurnal_intern='$nojurnal');
//                  ");
                $norec_head = DB::select("select norec from postingjurnaltransaksi_t where kdprofile = $idProfile and nojurnal_intern='$nojurnal'");
                $norec=$norec_head[0]->norec;
                $HapusPPD = PostingJurnalTransaksiD::where('norecrelated','=',$norec)->where('kdprofile', $idProfile)->delete();
                $HapusPPD2 = PostingJurnalTransaksi::where('nojurnal_intern','=',$nojurnal)->where('kdprofile',$idProfile)->delete();
//                $delDetail = DB::raw("
//                    delete from postingjurnaltransaksid_t
//                    where norecrelated in (select norec from postingjurnaltransaksi_t where nojurnal_intern='$nojurnal');
//                ");
//                $delHead = DB::raw("
//                    delete from postingjurnaltransaksi_t where nojurnal_intern='$nojurnal'
//                ");

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
//                "data" => $delHead,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $delHead,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function PostingJurnal_entry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();

        try {
            if ($dataReq['head']['nojurnal'] == '-'){
                $noBuktiTransaksi ='-';
                $noPosting = '-';
                $noJurnalIntern = $this->generateCode(new PostingJurnalTransaksi, 'nojurnal_intern', 13,Carbon::parse($dataReq['head']['tglentry'])->format('ym'). 'MJ' . Carbon::parse($dataReq['head']['tglentry'])->format('d'),$idProfile);

                $nojurnal = 1;//$this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');

                $postingJurnalTransaksi = new PostingJurnalTransaksi;
                $norecHead = $postingJurnalTransaksi->generateNewId();
                $postingJurnalTransaksi->norec = $norecHead;
                $postingJurnalTransaksi->kdprofile = $idProfile;
                $postingJurnalTransaksi->noposting =  $noPosting;
                $postingJurnalTransaksi->nojurnal = $nojurnal;
                $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
                $postingJurnalTransaksi->objectjenisjurnalfk = 1;
                $postingJurnalTransaksi->nobuktitransaksi = $noBuktiTransaksi;
                $postingJurnalTransaksi->tglbuktitransaksi = $dataReq['head']['tglentry'];
                $postingJurnalTransaksi->kdproduk = null;
                $postingJurnalTransaksi->namaproduktransaksi ='Manual Jurnal';
                $postingJurnalTransaksi->deskripsiproduktransaksi = 'pelayananpasien_t_manual';
                $postingJurnalTransaksi->statusenabled = 1;
                $postingJurnalTransaksi->norecrelated = null;
            }else{

                $postingJurnalTransaksi = PostingJurnalTransaksi::where('nojurnal_intern', $dataReq['head']['nojurnal'])
                    ->where('kdprofile', $idProfile)
                    ->first();
                $nojurnal2 = $dataReq['head']['nojurnal'];
                $delDetail = DB::select(DB::raw("
                    delete from postingjurnaltransaksid_t 
                    where norecrelated in (select norec from postingjurnaltransaksi_t where kdprofile = $idProfile and nojurnal_intern='$nojurnal2');
                  ")
                );
//                $newPPD = PostingJurnalTransaksiD::where('norecrelated', $postingJurnalTransaksi->norec)->delete();
                $nojurnal = $postingJurnalTransaksi->nojurnal;
                $postingJurnalTransaksi->tglbuktitransaksi = $dataReq['head']['tglentry'];
                if  ($postingJurnalTransaksi->tglbuktitransaksi != $dataReq['head']['tglentry']){
                    $noJurnalIntern = $this->generateCode(new PostingJurnalTransaksi, 'nojurnal_intern', 13,Carbon::parse($dataReq['head']['tglentry'])->format('ym'). 'MJ' . Carbon::parse($dataReq['head']['tglentry'])->format('d'), $idProfile);
                    $postingJurnalTransaksi->nojurnal_intern = $noJurnalIntern;
                }

                $noPosting = '-';
                $norecHead = $postingJurnalTransaksi->norec;
            }

            $postingJurnalTransaksi->keteranganlainnya = $dataReq['head']['deskripsi'];
            $postingJurnalTransaksi->save();

            foreach ($dataReq['detail'] as $item){
                $postingJurnalTransaksiD = new PostingJurnalTransaksiD;
                $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                $postingJurnalTransaksiD->kdprofile = $idProfile;
                $postingJurnalTransaksiD->nojurnal = $nojurnal;
                $postingJurnalTransaksiD->noposting = $noPosting;
                $postingJurnalTransaksiD->objectaccountfk = $item['accountid'];;
                $postingJurnalTransaksiD->hargasatuand = $item['hargasatuand'];
                $postingJurnalTransaksiD->hargasatuank = $item['hargasatuank'];
                $postingJurnalTransaksiD->statusenabled = 1;
                $postingJurnalTransaksiD->norecrelated = $norecHead;
                $postingJurnalTransaksiD->save();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Entry ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $postingJurnalTransaksi,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $postingJurnalTransaksi,
//                "datadetail" => $postingJurnalTransaksiD,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function PostingJurnalRev2018(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $ceksudahposting =  PostingJurnal::where('norecrelated',$request['nojurnal'])->where('kdprofile',$idProfile)->get();

        if  (count($ceksudahposting) == 0 ){
            try {
                $noPosting = $this->generateCode(new PostingJurnal, 'nojurnal_intern', 10, 'PJ' . $this->getDateTime()->format('ym'),$idProfile);
                $nojurnal =  PostingJurnal::max('nojurnal');
                $nojurnal = $nojurnal + 1;

                $newPJT = new PostingJurnal();
                $norecHead = $newPJT->generateNewId();
                $newPJT->norec = $norecHead;
                $newPJT->kdprofile = $idProfile;
                $newPJT->noposting = $noPosting;
                $newPJT->objectjenisjurnalfk = 1;
                $newPJT->nobuktitransaksi = $request['nojurnal'];
                $newPJT->nojurnal = $nojurnal;
                $newPJT->nojurnal_intern = $noPosting;
                $newPJT->tglbuktitransaksi = $request['tglbuktitransaksi'];//$this->getDateTime()->format('Y-m-d H:i:s');
                //            $newPJT->kdproduk = null;
                //            $newPJT->namaproduktransaksi = null;
                $newPJT->deskripsiproduktransaksi = $request['keteranganlainnya'];
                $newPJT->keteranganlainnya = $request['keteranganlainnya'];
                $newPJT->statusenabled = 1;
                $newPJT->norecrelated = $request['nojurnal'];;
                $newPJT->save();

                $norecHead2 = $newPJT->norec;

                foreach ($request['data'] as $item){
                    //debet
                    $nojurnald =  PostingJurnalD::max('nojurnal');
                    $nojurnald = $nojurnald + 1;

                    $newPJD = new PostingJurnalD();
                    $newPJD->norec = $newPJD->generateNewId();
                    $newPJD->kdprofile = $idProfile;
                    $newPJD->noposting = $noPosting;
                    $newPJD->nojurnal = $nojurnal;
                    $newPJD->objectaccountfk = $item['accountid'];
                    $newPJD->hargasatuand = $item['hargasatuand'];
                    $newPJD->hargasatuank = $item['hargasatuank'];
                    $newPJD->statusenabled = 1;
                    $newPJD->norecrelated = $norecHead2;
                    $newPJD->save();
                }
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
            }
            $transMessage = "Posting Jurnal" . " Berhasil";
        }else{
            $transStatus = 'true';
            $transMessage = "Sudah Posting Jurnal";
        }


        if ($transStatus == 'true') {
            $transMessage = $transMessage ;
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $newPJT,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newPJT,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function getDataJurnalUmumRev2019(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $filterKeterangan='';
        if(isset($req['keterangan']) && $req['keterangan']!="" && $req['keterangan']!="undefined"){
            $filterKeterangan=' and pj.keteranganlainnya ilike \'%' . $req['keterangan'] .'%\'';
        }
        $dataHead = DB::select(DB::raw("
                    select x.tgl,x.nojurnal_intern as nojurnal,x.keteranganlainnya as kelompok,
                    x.nojurnal_posted as posted,
                    cast(sum(x.debet) AS float) as debet, cast(sum(x.kredit) AS float) as kredit from 
                    (select to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD') as tgl,coa.noaccount,coa.namaaccount,(pjd.hargasatuand) as debet,
                    (pjd.hargasatuank) as kredit,pj.deskripsiproduktransaksi,to_char(pj.tglbuktitransaksi, 'YYYY-MM-DD') as tgl2,
                    coa.id as coaid,pj.keteranganlainnya,pj.nojurnal_intern,
                    posted.nojurnal_intern as nojurnal_posted,posted.tglbuktitransaksi  as tglposting,posted.nojurnal
                    from postingjurnaltransaksi_t as pj
                    INNER JOIN postingjurnaltransaksid_t as pjd on pjd.norecrelated=pj.norec
                    INNER JOIN chartofaccount_m as coa on coa.id=pjd.objectaccountfk
                    left JOIN postingjurnal_t as posted on posted.norecrelated=pj.nojurnal_intern
                    where pj.kdprofile = $idProfile and pj.tglbuktitransaksi between '$request[tglAwal]' and '$request[tglAkhir]' 
                    and pj.deskripsiproduktransaksi <> 'SALDOAWAL'
                    $filterKeterangan
                    )as x
                    group by x.tgl,x.nojurnal_intern ,x.keteranganlainnya,
                    x.nojurnal_posted 
                    order by x.nojurnal_intern;
            "));
        // ,
        //     array(
        //         'tglAwal' => $request['tglAwal']  ,
        //         'tglAkhir' => $request['tglAkhir'] ,
        //     )
        // );


        return $this->respond($dataHead);
    }
    public function UnPostingJurnalRev2018(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();
        try{
            $data =  PostingJurnal::where('norecrelated',$request['nojurnal'])->where('kdprofile',$idProfile)->get();
            foreach ($data as $item){
                PostingJurnalD::where('norecrelated',$item->norec)->where('kdprofile',$idProfile)->delete();
            }
            PostingJurnal::where('norecrelated',$request['nojurnal'])->where('kdprofile',$idProfile)->delete();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Posting Jurnal" ;


        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $newPJT,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newPJT,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function SaveDataSal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        try {
            if ($request['norec'] == ''){
                $new = new Sal();
                $norecHead = $new->generateNewId();
                $new->kdprofile = $idProfile;
                $new->norec = $norecHead;
                $new->statusenabled = 1;
            }else{
                $new =  Sal::where('norec',$request['norec'])->first();
            }
                $new->uraian = $request['uraian'];
                $new->tahun = $request['tahun'];
                $new->tgl = $tglAyeuna;
                $new->nilaiuang = $request['nilaiuang'];
                $new->save();

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
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataSal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('sal_t as sl')
            ->where('sl.kdprofile',$idProfile)
            ->select(DB::raw("sl.*"));

        if(isset($request['Uraian']) && $request['Uraian']!="" && $request['Uraian']!="undefined"){
            $data = $data->where('sl.uraian','ilike', '%'.$request['Uraian'].'%');
        }
        if(isset($request['Tahun']) && $request['Tahun']!="" && $request['Tahun']!="undefined"){
            $data = $data->where('sl.tahun','=', $request['Tahun']);
        }
        $data = $data->where('sl.statusenabled',true);
        $data = $data->orderBy('sl.tgl');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function deleteDataSal(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{

            $TG = Sal::where('norec',$request['norec'])
                ->where('kdprofile', $idProfile)
                ->update(
                [ 'statusenabled' => 0]
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
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}