<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 22/08/2019
 * Time: 15:03
 */

/**
 * Created by PhpStorm.
 * User: epic01
 * Date: 9/22/2017
 * Time: 2:55 AM
 */

namespace App\Http\Controllers\Farmasi;
use App\Http\Controllers\ApiController;

use App\Master\LoginUser;
use Illuminate\Http\Request;
use DB;
use App\Transaksi\BridgingMiniR45;
use App\Transaksi\HIS_Obat_MS;
use App\Transaksi\HIS_Trans_HD;
use App\Transaksi\HIS_Trans_IT;


use App\Traits\Valet;
use PhpParser\Node\Expr\Cast\String_;

//use Webpatser\Uuid\Uuid;

class BridgingMinir45Controller extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct();
    }
    public function SimpanBridgingMinir45(Request $request) {
        DB::beginTransaction();
        $data = \DB::table('strukresep_t as sr')
            ->JOIN('pelayananpasien_t as pp','pp.strukresepfk','=','sr.norec')
            ->JOIN('antrianpasiendiperiksa_t as apd','apd.norec','=','sr.pasienfk')
            ->JOIN('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->JOIN('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->JOIN('produk_m as pr','pr.id','=','pp.produkfk')
            ->leftJoin('alamat_m as ala','ala.nocmfk','=','pd.nocmfk')
            ->JOIN('pegawai_m as pg','pg.id','=','sr.penulisresepfk')
            ->JOIN('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->JOIN('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->select('sr.norec','sr.noresep','sr.tglresep','ps.id as psid','pd.noregistrasi','ps.nocm','ps.namapasien','ru.id as ruid',
                'ru.namaruangan','pg.id as pgid','pg.namalengkap as dokter',
                'jk.jeniskelamin','ps.tgllahir',
                'pd.tglregistrasi','ala.alamatlengkap','ps.tgllahir','pp.jumlah','pp.dosis',
                'pp.aturanpakai','pp.produkfk','pr.namaproduk','pp.rke');

        if(isset($request['strukresep']) && $request['strukresep']!="" && $request['strukresep']!="undefined"){
            $data = $data->where('pp.strukresepfk','=', $request['strukresep']);
        }
        if(isset($request['rke']) && $request['rke']!="" && $request['rke']!="undefined"){
            $data = $data->where('pp.rke','=', $request['rke']);
        }
        $data = $data->where('pp.jenisobatfk','=', 1);
        $data = $data->get();
        $transStatus = 'true';
//        return $this->respond($data);
        foreach ($data as $dataA){
            try{
                $doseQty =(float)$dataA->jumlah / (float)$dataA->dosis;
            } catch (\Exception $e) {
                $doseQty=0;
            }
            if ($dataA->jeniskelamin == 'Perempuan'){
                $jk = 'F';
            }else{
                $jk = 'M';
            };
            $nmpasien = $dataA->namapasien;
            $alamat = str_limit($dataA->alamatlengkap, 37);
            $dokter = str_limit($dataA->dokter, 37);
            $wardcode = str_limit($dataA->ruid, 5);
            $namaRuangan = str_limit($dataA->namaruangan, 27);
            $transactionCode =$dataA->noresep . '_'. $dataA->rke;
            $aturanpakai = str_limit($dataA->aturanpakai, 37);

            $nmpasien =str_replace(',','',$nmpasien);
            $nmpasien =str_replace('\'','',$nmpasien);
            $nmpasien =str_replace('"','',$nmpasien);
            $nmpasien =str_replace('/','',$nmpasien);
            $nmpasien =str_replace('\\','',$nmpasien);

            $alamat =str_replace(',','',$alamat);
            $alamat =str_replace('\'','',$alamat);
            $alamat =str_replace('"','',$alamat);
            $alamat =str_replace('/','',$alamat);
            $alamat =str_replace('\\','',$alamat);

            $dokter =str_replace(',','',$dokter);
            $dokter =str_replace('\'','',$dokter);
            $dokter =str_replace('"','',$dokter);
            $dokter =str_replace('/','',$dokter);
            $dokter =str_replace('\\','',$dokter);

            $wardcode =str_replace(',','',$wardcode);
            $wardcode =str_replace('\'','',$wardcode);
            $wardcode =str_replace('"','',$wardcode);
            $wardcode =str_replace('/','',$wardcode);
            $wardcode =str_replace('\\','',$wardcode);

            $namaRuangan =str_replace(',','',$namaRuangan);
            $namaRuangan =str_replace('\'','',$namaRuangan);
            $namaRuangan =str_replace('"','',$namaRuangan);
            $namaRuangan =str_replace('/','',$namaRuangan);
            $namaRuangan =str_replace('\\','',$namaRuangan);

            $transactionCode =str_replace(',','',$transactionCode);
            $transactionCode =str_replace('\'','',$transactionCode);
            $transactionCode =str_replace('"','',$transactionCode);
            $transactionCode =str_replace('/','',$transactionCode);
            $transactionCode =str_replace('\\','',$transactionCode);

            $aturanpakai =str_replace(',','',$aturanpakai);
            $aturanpakai =str_replace('\'','',$aturanpakai);
            $aturanpakai =str_replace('"','',$aturanpakai);
            $aturanpakai =str_replace('/','',$aturanpakai);
            $aturanpakai =str_replace('\\','',$aturanpakai);
            try {
                $newBRG = new BridgingMiniR45();
                $norecBRG = $newBRG->generateNewId();
                $newBRG->norec = $norecBRG;
                $newBRG->kdprofile = 0;
                $newBRG->statusenabled = true;
                $newBRG->address = $alamat;
                $newBRG->dob =(string)date('Ymd',strtotime($dataA->tgllahir));
                $newBRG->doctor = $dokter;
                $newBRG->doseqty = $doseQty;
//            $newBRG->duration = $dataA->xxxx;
//            $newBRG->expireddate = $dataA->xxxx;
                $newBRG->medicationcode = $dataA->produkfk;
                $newBRG->medicationname = $dataA->namaproduk;
                $newBRG->patientid = $dataA->nocm;
                $newBRG->patientname =$nmpasien ;
//            $newBRG->roomcode = $dataA->xxxx;
                $newBRG->sex = $jk;
//            $newBRG->signacode = $dataA->xxxx;
                $newBRG->signadescription = $aturanpakai;
                $newBRG->transactioncode = $transactionCode;
                $newBRG->unitcode = 1;//$dataA->produkfk;
                $newBRG->unitname = 'Puyer';//$dataA->namaproduk;
                $newBRG->wardcode = $wardcode;
                $newBRG->warddescription = $namaRuangan;

                $newBRG->save();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
                $transMessage = "Simpan Bridging";
            }
        }



        if ($transStatus == 'true') {
            $transMessage = "Simpan Bridging Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $newBRG,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Bridging Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noresep" => $newBRG,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function SimpanBridgingConsisD(Request $request) {
        DB::beginTransaction();
        $data = DB::select(DB::raw("
             select pp.produkfk ,sr.norec, sr.noresep, sr.tglresep, ps.id as psid, pd.noregistrasi, ps.nocm, ps.namapasien, ru.id as ruid, ru.namaruangan, pg.id as pgid, 
            pg.namalengkap as dokter, jk.jeniskelamin, ps.tgllahir, pd.tglregistrasi, ala.alamatlengkap, ps.tgllahir, pp.jumlah, pp.dosis, pp.aturanpakai, 
            pp.produkfk, pr.namaproduk, pp.rke
             from strukresep_t as sr inner join pelayananpasien_t as pp on pp.strukresepfk = sr.norec 
            inner join antrianpasiendiperiksa_t as apd on apd.norec = sr.pasienfk inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk 
            inner join pasien_m as ps on ps.id = pd.nocmfk inner join produk_m as pr on pr.id = pp.produkfk left join alamat_m as ala on ala.nocmfk = pd.nocmfk 
            inner join pegawai_m as pg on pg.id = sr.penulisresepfk inner join ruangan_m as ru on ru.id = apd.objectruanganfk 
            inner join jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk inner join his_obat_ms_t as ho on cast(ho.hobatid as INTEGER) = pp.produkfk 
            where pp.strukresepfk = :norec_resep and ho.statusenabled =true and pp.jeniskemasanfk=2"),
            array(
                'norec_resep' => $request['strukresep'],
            )
        );

        $statusCounter = 'Kosong';
//        $dataCounterLast = DB::select(DB::raw("
//            select max(counterid) as maxcounterid from his_trans_hd_t where status=:status and cast(counterid as INTEGER)<9"),
//            array(
//                'status' => '0',
//            )
//        );
//            if ((int)$dataCounterLast[0]->maxcounterid < 8){
//                $counterid=1+(int)$dataCounterLast[0]->maxcounterid;
//            }else{
//                $dataCounterKosong = DB::select(DB::raw("
//                    select counterid  from his_trans_hd_t where status=:status"),
//                    array(
//                        'status' => '1',
//                    )
//                );
//                if (count($dataCounterKosong) == 0){
//                    $statusCounter = 'Penuh';
//                }else{
//                    $counterid=(int)$dataCounterKosong[0]->counterid;
//                    $update = DB::select(DB::raw("
//                    update  his_trans_hd_t set status='2' where counterid=:counterid"),
//                        array(
//                            'counterid' => $counterid,
//                        )
//                    );
//                }
//            }
        if ($statusCounter == 'Kosong') {
            if ($data[0]->jeniskelamin == 'Perempuan') {
                $jk = 'F';
            } else {
                $jk = 'M';
            };
            $nmpasien = $data[0]->namapasien;#
            $alamat = str_limit($data[0]->alamatlengkap, 100);#
            $transactionCode = $data[0]->noresep;#
            $umur = $this->hitungUmur($data[0]->tgllahir);

            $umur = str_replace('Tahun', true, $umur);
            $umur = str_replace('Bulan', 'b', $umur);
            $umur = str_replace('Hari', 'h', $umur);
            $umur = str_replace(' ', '', $umur);
            $umur = str_replace(',', '', $umur);
            $umur = str_replace('.', '', $umur);
            $umur = str_limit($umur, 9);

            $nmpasien = str_replace(',', '', $nmpasien);
            $nmpasien = str_replace('\'', '', $nmpasien);
            $nmpasien = str_replace('"', '', $nmpasien);
            $nmpasien = str_replace('/', '', $nmpasien);
            $nmpasien = str_replace('\\', '', $nmpasien);

            $alamat = str_replace(',', '', $alamat);
            $alamat = str_replace('\'', '', $alamat);
            $alamat = str_replace('"', '', $alamat);
            $alamat = str_replace('/', '', $alamat);
            $alamat = str_replace('\\', '', $alamat);

            $transactionCode = str_replace(',', '', $transactionCode);
            $transactionCode = str_replace('\'', '', $transactionCode);
            $transactionCode = str_replace('"', '', $transactionCode);
            $transactionCode = str_replace('/', '', $transactionCode);
            $transactionCode = str_replace('\\', '', $transactionCode);


            try {
                $newBRG = new HIS_Trans_HD();#
                $norecBRG = $newBRG->generateNewId();#
                $norecHIS = $this->generateCode(new HIS_Trans_HD, 'transaksiid', 13, $transactionCode . '/');
                $newBRG->norec = $norecBRG;#
                $newBRG->kdprofile = 0;#
                $newBRG->statusenabled = true;#
                $newBRG->transaksiid = $norecHIS;//$transactionCode;#
                $newBRG->counterid = $request['counterid'];#
                $newBRG->mrn = $data[0]->nocm;#
                $newBRG->nama = $nmpasien;#
                $newBRG->umur = $umur;#
                $newBRG->alamat = $alamat;#
                $newBRG->jeniskelamin = $jk;#
                $newBRG->status = '0';#

                $newBRG->save();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
                $transMessage = "Simpan Bridging";
            }

            foreach ($data as $item) {
                try {
                    $dataobat = DB::select(DB::raw("
                            select packageunit from his_obat_ms_t where hobatid=:hobatid"),
                        array(
                            'hobatid' => $item->produkfk,
                        )
                    );
                    $qtypack = (int)$dataobat[0]->packageunit;
                    if ((int)$item->jumlah % $qtypack > 0) {
                        $qty = (int)((int)$item->jumlah / $qtypack) + 1;
                    } else {
                        $qty = (int)((int)$item->jumlah / $qtypack);
                    }


                    $newIT = new HIS_Trans_IT();#
                    $norecIT = $newIT->generateNewId();#
                    $newIT->norec = $norecIT;#
                    $newIT->kdprofile = 0;#
                    $newIT->statusenabled = true;#
                    $newIT->obatid = $item->produkfk;#
                    $newIT->qty = $qty;#
                    $newIT->transaksiid =$norecHIS;// $transactionCode;#

                    $newIT->save();
                    $transStatus = 'true';
                } catch (\Exception $e) {
                    $transStatus = 'false';
                    $transMessage = "Simpan Bridging";
                }
            }
        }

//
        $transMessage = "Simpan Bridging Gagal!!";
        if ($statusCounter == 'Penuh'){
            $transStatus = 'false';
            $transMessage = "CounterID Full";
            $newBRG ='penuh';
        }
        if ($transStatus == 'true' ) {
            $transMessage = "Simpan Bridging Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $newBRG,
                "as" => 'as@epic',
            );
        } else {

            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noresep" => $newBRG,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getConsisD(Request $request) {
        $datalogin = $request->all();
        $data = \DB::table('his_trans_hd_t as hd')
            ->select('hd.norec','hd.kdprofile','hd.statusenabled','hd.alamat','hd.counterid','hd.jeniskelamin',
                'hd.mrn','hd.nama','hd.transaksiid','hd.umur','hd.status')
            ->where('hd.statusenabled',true)
            ->where('hd.status','0')
            ->orderBy('hd.counterid');
        if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
            $data = $data->where('hd.nama','ilike','%'. $request['nama'].'%' );
        };
        if(isset($request['transaksiid']) && $request['transaksiid']!="" && $request['transaksiid']!="undefined"){
            $data = $data->where('hd.transaksiid','ilike','%'. $request['transaksiid'].'' );
        };
        if(isset($request['mrn']) && $request['mrn']!="" && $request['namaproduk']!="mrn"){
            $data = $data->where('hd.mrn',$request['mrn'] );
        };
        if(isset($request['alamat']) && $request['alamat']!="" && $request['alamat']!="undefined"){
            $data = $data->where('hd.alamat','ilike','%'. $request['alamat'].'%' );
        };
        $data = $data->take(100);
        $data = $data->get();

        $result =[];
        $details = [];
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select hd.norec,hd.kdprofile,hd.statusenabled,hd.obatid,hd.qty,hd.transaksiid,pr.namaproduk
                     from his_trans_it_t as hd inner join produk_m as pr on pr.id=cast(hd.obatid as INTEGER)
                     where hd.statusenabled=true and hd.transaksiid=:transaksiid"),
                array(
                    'transaksiid' => $item->transaksiid,
                )
            );
//            $details = \DB::table('his_trans_it_t as hd')
//                ->leftJOIN('produk_m as pr','pr.id','=','cast(hd.obatid as INTEGER)')
//                ->select('hd.norec','hd.kdprofile','hd.statusenabled','hd.obatid','hd.qty','hd.transaksiid','pr.namaproduk')
//                ->where('hd.statusenabled',true)
//                ->where('hd.transaksiid',$item->transaksiid)
//                ->get();

            $result[]=array(
                'norec' => $item->norec,
                'kdprofile' => $item->kdprofile,
                'statusenabled' => $item->statusenabled,
                'alamat' => $item->alamat,
                'counterid' => $item->counterid,
                'jeniskelamin' => $item->jeniskelamin,
                'mrn' => $item->mrn,
                'nama' => $item->nama,
                'transaksiid' => $item->transaksiid,
                'umur' => $item->umur,
                'status' => $item->status,
                'detail' => $details,
            );
        }

        $result = array(
            'data' => $result,
            'dataLogin' => $datalogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getMiniR45(Request $request) {
        $datalogin = $request->all();
        $data = \DB::table('bridgingminir45 as hd')
            ->where('hd.statusenabled',true)
            ->orderBy('hd.transactioncode','desc');
        if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
            $data = $data->where('hd.patientname','ilike','%'. $request['nama'].'%' );
        };
        if(isset($request['transaksiid']) && $request['transaksiid']!="" && $request['transaksiid']!="undefined"){
            $data = $data->where('hd.transactioncode','ilike','%'. $request['transaksiid'].'' );
        };
        if(isset($request['mrn']) && $request['mrn']!="" && $request['namaproduk']!="mrn"){
            $data = $data->where('hd.patientid',$request['mrn'] );
        };
        if(isset($request['alamat']) && $request['alamat']!="" && $request['alamat']!="undefined"){
            $data = $data->where('hd.address','ilike','%'. $request['alamat'].'%' );
        };
        $data = $data->take(100);
        $data = $data->get();

        $result = array(
            'data' => $data,
            'dataLogin' => $datalogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function SimpanCounterID(Request $request) {
        DB::beginTransaction();
        $transMessage = "Simpan CounterID";
        try {
            $data = HIS_Trans_HD::where('norec',$request['norec'])->update(array('counterid' => $request['counterid']));
//            $data->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $data,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noresep" => $data,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function SimpanBridgingConsisDBebas(Request $request) {
        DB::beginTransaction();
        $data = DB::select(DB::raw("select spd.objectprodukfk as produkfk,sp.norec,sp.nostruk as noresep,sp.tglstruk as tglresep,
                                 case when ps.nocm is null then '-' else ps.nocm end as nocm,'-' as noregistrasi,
                                 upper(sp.namapasien_klien) as namapasien,ru.id as ruid,ru.namaruangan,pg.id as pgid,
                                 pg.namalengkap as dokter,jk.jeniskelamin,ps.tgllahir,'-' as tglregistrasi,al.alamatlengkap, 
                                 ps.tgllahir,spd.qtyproduk as jumlah,'' as dosis,spd.aturanpakai,spd.objectprodukfk as produkfk, 
                                 pr.namaproduk,spd.resepke as rke            
                                from strukpelayanan_t as sp
                                inner join strukpelayanandetail_t as spd on spd.nostrukfk=sp.norec
                                inner join produk_m as pr on pr.id = spd.objectprodukfk
                                left join satuanstandar_m as ss on ss.id = spd.objectsatuanstandarfk 
                                left join jeniskemasan_m as jkm on jkm.id = spd.objectjeniskemasanfk
                                left join pasien_m as ps on ps.nocm = sp.nostruk_intern 
                                left join alamat_m as al on al.nocmfk = ps.id 
                                left join jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
                                left JOIN pegawai_m as pg on pg.id = sp.objectpegawaipenanggungjawabfk 
                                left JOIN ruangan_m as ru on ru.id = sp.objectruanganfk
                                inner join his_obat_ms_t as ho on cast(ho.hobatid as integer) = spd.objectprodukfk     
                                where sp.norec=:norec_resep and ho.statusenabled =true and spd.objectjeniskemasanfk=2"),
            array(
                'norec_resep' => $request['strukresep'],
            )
        );

        $statusCounter = 'Kosong';
//        $dataCounterLast = DB::select(DB::raw("
//            select max(counterid) as maxcounterid from his_trans_hd_t where status=:status and cast(counterid as INTEGER)<9"),
//            array(
//                'status' => '0',
//            )
//        );
//            if ((int)$dataCounterLast[0]->maxcounterid < 8){
//                $counterid=1+(int)$dataCounterLast[0]->maxcounterid;
//            }else{
//                $dataCounterKosong = DB::select(DB::raw("
//                    select counterid  from his_trans_hd_t where status=:status"),
//                    array(
//                        'status' => '1',
//                    )
//                );
//                if (count($dataCounterKosong) == 0){
//                    $statusCounter = 'Penuh';
//                }else{
//                    $counterid=(int)$dataCounterKosong[0]->counterid;
//                    $update = DB::select(DB::raw("
//                    update  his_trans_hd_t set status='2' where counterid=:counterid"),
//                        array(
//                            'counterid' => $counterid,
//                        )
//                    );
//                }
//            }
        if ($statusCounter == 'Kosong') {
            if ($data[0]->jeniskelamin == 'Perempuan') {
                $jk = 'F';
            } else {
                $jk = 'M';
            };
            $nmpasien = $data[0]->namapasien;#
            $alamat = str_limit($data[0]->alamatlengkap, 100);#
            $transactionCode = $data[0]->noresep;#
            $umur = $this->hitungUmur($data[0]->tgllahir);

            $umur = str_replace('Tahun', true, $umur);
            $umur = str_replace('Bulan', 'b', $umur);
            $umur = str_replace('Hari', 'h', $umur);
            $umur = str_replace(' ', '', $umur);
            $umur = str_replace(',', '', $umur);
            $umur = str_replace('.', '', $umur);
            $umur = str_limit($umur, 9);

            $nmpasien = str_replace(',', '', $nmpasien);
            $nmpasien = str_replace('\'', '', $nmpasien);
            $nmpasien = str_replace('"', '', $nmpasien);
            $nmpasien = str_replace('/', '', $nmpasien);
            $nmpasien = str_replace('\\', '', $nmpasien);

            $alamat = str_replace(',', '', $alamat);
            $alamat = str_replace('\'', '', $alamat);
            $alamat = str_replace('"', '', $alamat);
            $alamat = str_replace('/', '', $alamat);
            $alamat = str_replace('\\', '', $alamat);

            $transactionCode = str_replace(',', '', $transactionCode);
            $transactionCode = str_replace('\'', '', $transactionCode);
            $transactionCode = str_replace('"', '', $transactionCode);
            $transactionCode = str_replace('/', '', $transactionCode);
            $transactionCode = str_replace('\\', '', $transactionCode);


            try {
                $newBRG = new HIS_Trans_HD();#
                $norecBRG = $newBRG->generateNewId();#
                $norecHIS = $this->generateCode(new HIS_Trans_HD, 'transaksiid', 13, $transactionCode . '/');
                $newBRG->norec = $norecBRG;#
                $newBRG->kdprofile = 0;#
                $newBRG->statusenabled = true;#
                $newBRG->transaksiid = $norecHIS;//$transactionCode;#
                $newBRG->counterid = $request['counterid'];#
                $newBRG->mrn = $data[0]->nocm;#
                $newBRG->nama = $nmpasien;#
                $newBRG->umur = $umur;#
                $newBRG->alamat = $alamat;#
                $newBRG->jeniskelamin = $jk;#
                $newBRG->status = '0';#

                $newBRG->save();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
                $transMessage = "Simpan Bridging";
            }

            foreach ($data as $item) {
                try {
                    $dataobat = DB::select(DB::raw("
                            select packageunit from his_obat_ms_t where hobatid=:hobatid"),
                        array(
                            'hobatid' => $item->produkfk,
                        )
                    );
                    $qtypack = (int)$dataobat[0]->packageunit;
                    if ((int)$item->jumlah % $qtypack > 0) {
                        $qty = (int)((int)$item->jumlah / $qtypack) + 1;
                    } else {
                        $qty = (int)((int)$item->jumlah / $qtypack);
                    }


                    $newIT = new HIS_Trans_IT();#
                    $norecIT = $newIT->generateNewId();#
                    $newIT->norec = $norecIT;#
                    $newIT->kdprofile = 0;#
                    $newIT->statusenabled = true;#
                    $newIT->obatid = $item->produkfk;#
                    $newIT->qty = $qty;#
                    $newIT->transaksiid =$norecHIS;// $transactionCode;#

                    $newIT->save();
                    $transStatus = 'true';
                } catch (\Exception $e) {
                    $transStatus = 'false';
                    $transMessage = "Simpan Bridging";
                }
            }
        }

        $transMessage = "Simpan Bridging Gagal!!";
        if ($statusCounter == 'Penuh'){
            $transStatus = 'false';
            $transMessage = "CounterID Full";
            $newBRG ='penuh';
        }
        if ($transStatus == 'true' ) {
            $transMessage = "Simpan Bridging Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $newBRG,
                "as" => 'as@epic',
            );
        } else {

            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noresep" => $newBRG,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}