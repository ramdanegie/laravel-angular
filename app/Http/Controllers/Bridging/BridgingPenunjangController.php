<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 9/5/2019
 * Time: 1:44 PM
 */

namespace App\Http\Controllers\Bridging;

// use Log;

use App\Http\Controllers\ApiController;
use App\Master\HargaNettoProdukByKelas1;
use App\Master\JenisPetugasPelaksana;
use App\Master\Produk;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\HasilPemeriksaan;
use App\Transaksi\KirimLIS_ext;
use App\Transaksi\LisOrder;
use App\Transaksi\LisOrderTmp;
use App\Transaksi\LoggingUser;
use App\Transaksi\OrderLab;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDelete;
use App\Transaksi\PelayananPasienDetail;

use App\Transaksi\PelayananPasienPetugas;

use App\Transaksi\RisOrder;
use App\Transaksi\StrukHasilPemeriksaan;
use App\Transaksi\VansLab;
use Illuminate\Http\Request;
use DB;

use App\Transaksi\StrukOrder;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\OrderProduk;
use App\Master\Pegawai;
use App\Master\Pasien;
use App\Traits\Valet;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;


class BridgingPenunjangController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function saveBridgingSysmex(Request $request) {

        DB::beginTransaction();
        $noorder = $request['noorder'];

        $raw = DB::select(DB::raw("select op.norec, 
		     op.iscito as cito,  op.tglpelayanan, prd.id as produkid ,  prd.namaproduk,
             ps.nocm,  so.noorderintern, so.noorder as noorder, ps.namapasien,  ps.tgllahir,  jk.jeniskelamin, 
             alm.alamatlengkap, ru.id as ruanganid, ru.namaruangan, 
             case when alm.alamatlengkap is null then '' else alm.alamatlengkap end as alamat, 
             case when ds.namadesakelurahan is null then '' else ds.namadesakelurahan end as kelurahan, 
             case when kc.namakecamatan is null then '' else kc.namakecamatan end as kecamatan, 
             case when kk.namakotakabupaten is null then '' else kk.namakotakabupaten end as kota,    
             pd.noregistrasi, pg.id as pgid,pg.namalengkap,
             dp.id as departemenid  ,lis.id as lisorderid
             FROM orderpelayanan_t as op
			 left join produk_m as prd on prd.id=op.objectprodukfk
			 left join strukorder_t as so on so.norec=op.strukorderfk
			 left join pasien_m as ps on ps.id =so.nocmfk
			 left join jeniskelamin_m as jk on jk.id=ps.objectjeniskelaminfk 
			 left join alamat_m as alm on alm.nocmfk=ps.id
			 left join desakelurahan_m as ds on ds.id=alm.objectdesakelurahanfk
			 left join kotakabupaten_m as kk on kk.id=alm.objectkotakabupatenfk
			 left join kecamatan_m as kc on kc.id=alm.objectkecamatanfk
			 left join pasiendaftar_t as pd on pd.norec=so.noregistrasifk
			 left join ruangan_m as ru on ru.id=pd.objectruanganlastfk
			 left join pegawai_m as pg on pg.id=op.objectnamapenyerahbarangfk
			 left join departemen_m as dp on dp.id=ru.objectdepartemenfk
			 left join lisorder as lis on lis.ono=so.noorder
			 where 
			 so.noorder= '$noorder' "));

        $loopProduk=[];
        $sama=false;
        foreach ($request['bridging'] as $dataA) {
            $i=0;
            $sama=false;
            foreach ($loopProduk as $hideung){
//                if ($dataA->noorder != null){
                $sama=true;
                $loopProduk[$i]['produkid']=$loopProduk[$i]['produkid']. '~'.($dataA['produkid']);
//                }
                $i=$i+1;
            }
            if ($sama==false){
                $loopProduk[]=array(
                    'produkid'=>$dataA['produkid'],
                );
            }
        }

//            try {
        if (is_null($raw[0]->lisorderid)) {
            $newBRG = new LisOrder();
            $newId = LisOrder::max('id');
            $newId = $newId + 1;
            $newBRG->id = $newId;
        }else {
            $newBRG = LisOrder::where('id',$raw[0]->lisorderid)->first();
        }
        $newBRG->address1 = (string) $raw[0]->alamat . ',' . (string)$raw[0]->kelurahan ;
        $newBRG->address2 = (string) $raw[0]->kecamatan . ',' . (string)$raw[0]->kota;
        $newBRG->address3 = '';
        $newBRG->address4 = '';
        $newBRG->birth_dt = (string)date('YmdHis',strtotime($raw[0]->tgllahir));
//                $newBRG->clinician = (string) $raw[0]->pgid . '^' . $raw[0]->namalengkap ;
        $newBRG->clinician = (string) $request['iddokterorder'] . '^' . $request['namadokterorder'] ;
        $newBRG->comment = '';
        $newBRG->flag = 0;
        $newBRG->message_dt =(string)date('YmdHis');
        $newBRG->ono = $raw[0]->noorderintern ;
        /*
         * NW : Transaksi baru RP : Update transaksi CA : Batal Transaksi
         */
        $newBRG->order_control = 'NW';
        $newBRG->order_testid = $loopProduk[0]['produkid']; //produkid
        $newBRG->pname = $raw[0]->namapasien;
        /*
         * - IN : Rawat inap - OP : Rawat jalan
         */
        if ($raw[0]->departemenid == 16 ) {
            $pType = 'IN';
        } else {
            $pType = 'OP';
        }
        $newBRG->ptype = $pType;
        $newBRG->pid = $raw[0]->nocm;
        /*- R : Rutin
        - U : Cito	*/
        if ($raw[0]->cito == '1') {
            $priority = 'U';
        } else {
            $priority = 'R';
        }
        $newBRG->priority = $priority;
        $newBRG->request_dt = (string)date('YmdHis'); //tglpelayanan
        $newBRG->room_no = '';
        /*
         * - 1 : Laki-laki - 2 : Perempuan - 0 : Tidak diketahui
         */
        if (strtolower($raw[0]->jeniskelamin) == 'laki-laki'){
            $jk = '1';
        }else if(strtolower($raw[0]->jeniskelamin) == 'perempuan'){
            $jk = '2';
        }else{
            $jk = '0';
        }

        $newBRG->sex = $jk;
        $newBRG->source = (string)$raw[0]->ruanganid .'^'.$raw[0]->namaruangan ;
        $newBRG->visitno = $raw[0]->noregistrasi;

        $newBRG->save();

        /* save ke TMP juga
        */
        if (is_null($raw[0]->lisorderid)) {
            $newBRGTmp = new LisOrderTmp();
            $newIds = LisOrderTmp::max('id');
            $newIds = $newIds + 1;
            $newBRGTmp->id = $newIds;
        }else{
            $newBRG = LisOrderTmp::where('ono',$raw[0]->noorder)->first();
        }
        $newBRGTmp->address1 = (string) $raw[0]->alamat . ',' . (string)$raw[0]->kelurahan ;
        $newBRGTmp->address2 = (string) $raw[0]->kecamatan . ',' . (string)$raw[0]->kota;
        $newBRGTmp->address3 = '';
        $newBRGTmp->address4 = '';
        $newBRGTmp->birth_dt = (string)date('YmdHis',strtotime($raw[0]->tgllahir));
        $newBRGTmp->clinician = (string) $request['iddokterorder'] . '^' . $request['namadokterorder'] ;
//                $newBRGTmp->clinician = (string) $raw[0]->pgid . '^' . $raw[0]->namalengkap ;
        $newBRGTmp->comment = '';
        $newBRGTmp->flag = 0;
        $newBRGTmp->message_dt =(string)date('YmdHis');
        $newBRGTmp->ono = $raw[0]->noorderintern ;
        $newBRGTmp->order_control = 'NW';
        $newBRGTmp->order_testid = $loopProduk[0]['produkid']; //produkid
        $newBRGTmp->pname = $raw[0]->namapasien;
        $newBRGTmp->ptype = $pType;
        $newBRGTmp->pid = $raw[0]->nocm;
        $newBRGTmp->priority = $priority;
        $newBRGTmp->request_dt = (string)date('YmdHis'); //tglpelayanan
        $newBRGTmp->room_no = '';
        $newBRGTmp->sex = $jk;
        $newBRGTmp->source = (string)$raw[0]->ruanganid .'^'.$raw[0]->namaruangan ;
        $newBRGTmp->visitno = $raw[0]->noregistrasi;

        $newBRGTmp->save();


        $transStatus = 'true';
//            } catch (\Exception $e) {
//                $transStatus = 'false';
//                $transMessage = "Simpan Bridging";
//            }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Bridging Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "sysmex" => $newBRG,
                "as" => 'inhuman',
            );
        } else {
            $transMessage = "Simpan Bridging Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "sysmex" => $newBRG,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond($noorder);
    }

    public function saveBridgingZeta(Request $request)
    {
        $dataLogin =$request->all();
        $kdProfile = (int)$this->getDataKdProfile($request);
        $noorder = $request['noorder'];
         if(isset($request['details'] )){

           DB::beginTransaction();
           try{

            $struk = StrukOrder::where('noorder',$noorder )->where('kdprofile',$kdProfile)->where('statusenabled',true)->first();
            OrderPelayanan::where('strukorderfk',$struk->norec)->delete();
            foreach ($request['details'] as $item) {
                $dataOP = new OrderPelayanan;
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = $kdProfile;
                $dataOP->statusenabled = true;
                if(isset($item['iscito'])){
                    $dataOP->iscito =(float) $item['iscito'];
                }else{
                    $dataOP->iscito = 0;
                }

                $dataOP->noorderfk = $struk->norec;
                $dataOP->objectprodukfk = $item['produkid'];
                $dataOP->qtyproduk = $item['qtyproduk'];
                $dataOP->objectkelasfk = $request['objectkelasfk'];
                $dataOP->qtyprodukretur = 0;
                $dataOP->objectruanganfk =$struk->objectruanganfk;
                $dataOP->objectruangantujuanfk = $struk->objectruangantujuanfk;
                $dataOP->strukorderfk = $struk->norec;
                if(isset($request['tglpelayanan'])){
                    $dataOP->tglpelayanan=$request['tglpelayanan'];
                }else{
                    $dataOP->tglpelayanan =$struk->tglorder;// date('Y-m-d H:i:s');
                }
                $dataOP->objectnamapenyerahbarangfk = $struk->objectpegawaiorderfk;
                
                $dataOP->save();
            }
                $stt = 'true';
            } catch (\Exception $e) {
                $stt = 'false';
            }
            if($stt=='true'){
                DB::commit();
            }else{
               DB::rollBack();
            }
        }

        DB::beginTransaction();
      

        try {
            StrukOrder::where('noorder',$noorder)
            ->where('kdprofile',$kdProfile)
            ->update(
                [
                    'statusorder' => 1
                ]
            );
            $raw = DB::select(DB::raw("select op.norec, 
		     op.iscito as cito,  op.tglpelayanan, prd.id as produkid , op.nourut, prd.namaproduk,
             ps.nocm,  so.noorderintern, so.noorder as noorder, ps.namapasien,  ps.tgllahir,  jk.jeniskelamin, 
             alm.alamatlengkap, ru.id as ruanganid, ru.namaruangan, pd.noregistrasi,
         
             pd.noregistrasi, pg.id as pgid,pg.namalengkap,
             dp.id as departemenid ,prd.objectdetailjenisprodukfk,
			 djp.detailjenisproduk,djp.kodeexternal as kodeexternaldjp ,djp.objectjenisprodukfk,
			 jp.jenisproduk,jp.namaexternal as namaexternaljp,kps.kelompokpasien,so.tglorder,
			 gdr.golongandarah,ris.order_key
             FROM orderpelayanan_t as op
			 left join produk_m as prd on prd.id=op.objectprodukfk
			 left join detailjenisproduk_m as djp on djp.id=prd.objectdetailjenisprodukfk
             left join jenisproduk_m as jp on jp.id=djp.objectjenisprodukfk
			 left join strukorder_t as so on so.norec=op.strukorderfk
			 left join pasien_m as ps on ps.id =so.nocmfk
			 left join jeniskelamin_m as jk on jk.id=ps.objectjeniskelaminfk 
			 left join alamat_m as alm on alm.nocmfk=ps.id

			 left join pasiendaftar_t as pd on pd.norec=so.noregistrasifk
			 left join kelompokpasien_m as kps on kps.id=pd.objectkelompokpasienlastfk
			 left join ruangan_m as ru on ru.id=pd.objectruanganlastfk
			 left join pegawai_m as pg on pg.id=so.objectpegawaiorderfk
			 left join departemen_m as dp on dp.id=ru.objectdepartemenfk
			 left join golongandarah_m as gdr on gdr.id=ps.objectgolongandarahfk
			 left join ris_order as ris on ris.order_no=so.noorder  and ris.order_code=cast( op.objectprodukfk as text)  
       where so.noorder = '$noorder'
       and so.kdprofile=$kdProfile "));
       
       $errorrr =  "";
       

//            try {
            foreach ($raw as $item) {
                $getAccNumber = $this->getAccNumber($item->kodeexternaldjp , $item->namaexternaljp);

                if($item->namaexternaljp == null){
                  $errorrr = "Modality belum disetting";
                  $modality = null;
//                    return $this->setStatusCode(400)->respond('', 'Kode External Bridging Produk '.$item->namaproduk. ' Kosong');
                }else{
                    if (strlen(trim($item->namaexternaljp)) > 4) {
                      $errorrr = "Modality tidak dikenal";
                      $modality = null;
                    } else {
                      $modality = str_limit(trim($item->namaexternaljp), 5);
                    }                    
                }

                if ($modality == null) {
                  break;
                }

                // if (empty($item->nourut)) {
                //   $errorrr = "Tidak ada nomor urut order pelayanan";
                //   break;
                // }

                $newBRG = new RisOrder();
//                    if (is_null($item->order_key)) {
                $newId = RisOrder::max('order_key');

                $newId = $newId + 1;
                $nourut = RisOrder::where('patient_id',$item->nocm)->max('order_cnt');
                
                if (is_null($nourut) || empty($nourut) || $nourut == null) {
                  $nourut = 0;
                }

                $nourut = $nourut + 1;
                $newBRG->order_cnt = $nourut;

                $newBRG->order_key = $newId;
                $newBRG->norec_op_fk = $item->norec;
//                    }else{
//                        $newBRG = RisOrder::where('order_key',$item->order_key)->first();
//                    }
                $newBRG->accession_num = $getAccNumber;
                $newBRG->aetitle = '-';
                $newBRG->charge_doc_id = $request['iddokterverif']; //dokter rad
                $newBRG->charge_doc_name =  $request['namadokterverif']; //dokter rad
                $newBRG->consult_doc_id =  $item->pgid;
                $newBRG->consult_doc_name =  $item->namalengkap ;
                $newBRG->create_date = (string)date('YmdHi');
                $newBRG->extension1 = $item->noregistrasi;
                /*
               * if JenisDiagnosa==1 : isi namadiagnosa ke extension2 & extension4
               */
                $newBRG->extension2 = '-';
                $newBRG->extension4 = '-';
                /*
               * if JenisDiagnosa==2 : isi namadiagnosa ke eextension3
               */
                $newBRG->extension3 = '-';
                $newBRG->extension5 = $item->kelompokpasien;
                $newBRG->extension6 = $dataLogin['userData']['id'];
                $newBRG->extension7 = '-';
                $newBRG->extension8 = '-';
                $newBRG->extension9 = '-';
                $newBRG->extension10 = '-';
                //            $newBRG->first_name = '';
                $newBRG->flag = 'Y';
                $newBRG->group1 = '-';
                $newBRG->group2 = '-';
                $newBRG->group3 = '-';
                /*
                * - 18 : R. Jalan - 16 : R. Inap
                */
                if ($raw[0]->departemenid == 18) {
                    $io_date = 'E'; // AWALNYA O HARUSNYA E
                } else{
                    $io_date = 'I';
                }
                $newBRG->io_date = $io_date;
                //            $newBRG->last_name = '';
                $newBRG->middle_name = '-';
                $newBRG->order_bodypart = '-';
                $newBRG->order_code = $item->produkid;
                $newBRG->order_comment = '-';
                $newBRG->order_date = (string)date('YmdHi',strtotime($item->tglorder));;
                $newBRG->order_dept = $request['objectruangantujuanfk'];
                //            $newBRG->order_diag = $item->namaexternaljp;

                $newBRG->order_modality = $modality;
                $newBRG->order_name = $item->namaproduk;
                $newBRG->order_no = $request['noorder'];
                $newBRG->order_reason ='-';
                $newBRG->order_status = 'NW';
                $newBRG->patient_birth_date = (string)date('Ymd',strtotime($item->tgllahir));
                $newBRG->patient_blood = $item->golongandarah;
                
                // $idPasien = ($item->nourut == null) ? $item->nocm : $item->nocm . '-' . $item->nourut;
                $idPasien = $item->nocm;

                $newBRG->patient_id = $idPasien;
                /*- E: Cito
                  -	*/
                if ($item->cito == '1') {
                    $patient_io = 'E'; // E = Emergency
                } else {
                    $patient_io = 'U'; // awalnya I, harusnya isinya bisa R = Routine, bisa A = Accident, bisa U = Urgent, bisa N = Newborn
                }
                $newBRG->patient_io = $patient_io;
                $newBRG->patient_name = $item->namapasien;
                /*
                   * - M : Laki-laki - F : Perempuan - O : Tidak diketahui
                   */
                if (strtolower($raw[0]->jeniskelamin) == 'laki-laki'){
                    $jk = 'M';
                }else if(strtolower($raw[0]->jeniskelamin) == 'perempuan'){
                    $jk = 'F';
                }else{
                    $jk = 'O';
                }
                $newBRG->patient_sex = $jk;
                $newBRG->patient_uid = '-';
                $newBRG->patient_ward = $item->namaruangan;
                $newBRG->study_remark = '-';
                $newBRG->study_reserv_date = (string)date('YmdHi',strtotime($item->tglpelayanan));
                $newBRG->kelas = $request['objectkelasfk'];

                $newBRG->save();
            }

            $transStatus = (empty( $errorrr)) ? 'true' : 'false';

        } catch (\Exception $e) {
          $errorrr = $e->getMessage();
          // Log::error($e->getMessage(), [
          //       'url' => Request::url(),
          //     'input' => Request::all()
          //   ]);

            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan RIS sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "zeta" => $newBRG,
                "as" => 'inhuman',
            );
        } else {
            $transMessage = "Simpan RIS gagal penyebab: " . $errorrr;
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                // "zeta" => $newBRG,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//            return $this->respond($getAccNumber);
    }
    public function getAccNumber($kdExternal, $namaExternal){
        $kode='';
        if($namaExternal == null){
            $kode="5";
        }
        else if($namaExternal== "CR"){
            if($kdExternal == "1") {
                $kode="1";
            } else {
                $kode="2";
            }
        }else if ($namaExternal == "US"){
            $kode="3";
        }else if ($namaExternal == "CT"){
            $kode="4";
        }else if ($namaExternal == "MR"){
            $kode="5";
        }else if ($namaExternal == "DX"){
            $kode="6";    
        }else{
            $kode="2";
        }

        if (!empty($kode)){
            $month=(string)date('m');
            $year=(string)date('Y');
            $accessNum = DB::select(DB::raw("select max(a.accession_num) as max
                            from ris_order a where 
                            substring(a.accession_num, 1, 1)= '$kode' and 
                            substring(a.create_date, 5, 2) = '$month' and 
                            substring(a.create_date, 1, 4)= '$year'"));

            if($accessNum[0]!= null){
                $accessNum=$accessNum[0]->max;
                $yearNow=(string)date('y');

                $digit = null;
                $number = null;
                if(!empty($accessNum)) {
                    $number =  (substr($accessNum, 5))+1;
                } else {
                    $number = 1;
                }

                if(strlen($number) == 1){
                    $digit="000";
                }
                else if(strlen($number) == 2){
                    $digit="00";
                }
                else if(strlen($number) == 3){
                    $digit="0";
                }
                if(strlen($month) == 1)
                    $month = "0".$month;

                if($kode == "1") {
                    $noUsulan = "1" . $yearNow . $month . $digit . $number;
                } else if($kode =="2") {
                    $noUsulan = "2" . $yearNow . $month . $digit . $number;
                } else if($kode =="3") {
                    $noUsulan = "3" . $yearNow . $month . $digit . $number;
                } else if($kode == "4") {
                    $noUsulan = "4" . $yearNow . $month . $digit . $number;
                } else if($kode == "5") {
                    $noUsulan = "5" . $yearNow . $month . $digit . $number;
                }
            }

        }

        return $noUsulan;
    }
    public function saveBridgingVansLab(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        $noorder = $request['noorder'];
        if(isset($request['details'] )){

           DB::beginTransaction();
           try{

            $struk = StrukOrder::where('noorder',$noorder )->where('kdprofile',$kdProfile)->where('statusenabled',true)->first();
            OrderPelayanan::where('strukorderfk',$struk->norec)->delete();
            foreach ($request['details'] as $item) {
                $dataOP = new OrderPelayanan;
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = $kdProfile;
                $dataOP->statusenabled = true;
                if(isset($item['iscito'])){
                    $dataOP->iscito =(float) $item['iscito'];
                }else{
                    $dataOP->iscito = 0;
                }

                $dataOP->noorderfk = $struk->norec;
                $dataOP->objectprodukfk = $item['produkid'];
                $dataOP->qtyproduk = $item['qtyproduk'];
                $dataOP->objectkelasfk =$request['objectkelasfk'];//(int)$this->settingDataFixed('kdKelasNonKelasRegistrasi',$kdProfile);
                $dataOP->qtyprodukretur = 0;
                $dataOP->objectruanganfk =$struk->objectruanganfk;
                $dataOP->objectruangantujuanfk = $struk->objectruangantujuanfk;
                $dataOP->strukorderfk = $struk->norec;
                if(isset($request['tglpelayanan'])){
                    $dataOP->tglpelayanan=$request['tglpelayanan'];
                }else{
                    $dataOP->tglpelayanan =$struk->tglorder;// date('Y-m-d H:i:s');
                }
                $dataOP->objectnamapenyerahbarangfk = $struk->objectpegawaiorderfk;
                
                $dataOP->save();
            }
                $stt = 'true';
            } catch (\Exception $e) {
                $stt = 'false';
            }
            if($stt=='true'){
                DB::commit();
            }else{
               DB::rollBack();
            }
        }
        
        
          $raw = DB::select(DB::raw("SELECT
                    pd.norec as norec_pd,
                    op.norec,op.iscito AS cito,
                    so.tglorder,to_char(so.tglorder, 'HH:mm') AS jamorder,
                    prd. ID AS produkid,prd.namaproduk,
                    ps.nocm,so.noorder AS noorder,	ps.namapasien,
                    ps.tgllahir,jk. ID AS jkid,jk.jeniskelamin,
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
                                WHEN pro.namapropinsi IS NOT NULL THEN
                                 'Prov. ' ||   pro.namapropinsi
                                ELSE
                                    ''
                                END
                            )
                        )
                    END AS alamatlengkap,
                ru. ID AS ruanganid, ru.namaruangan,
                 pd.noregistrasi, pg. ID AS pgid,pg.namalengkap,
                 dp. ID AS departemenid,
                 -- lis.no_lab AS lisorderid,
                -- DATEDIFF(HOUR, ps.tgllahir, GETDATE()) / 8766 AS usia,
                date_part('year', age(ps.tgllahir)) usia,
                 kp. ID AS kode_cara_bayar,
                 kp.kelompokpasien AS cara_bayar,
                 ru2. ID AS ideuangantujuan,
                 ru2.namaruangan AS ruangantujuan,
                 ps.noidentitas as nik
                FROM
                    orderpelayanan_t AS op
                INNER JOIN produk_m AS prd ON prd. ID = op.objectprodukfk
                INNER JOIN strukorder_t AS so ON so.norec = op.strukorderfk
                INNER JOIN pasien_m AS ps ON ps. ID = so.nocmfk
                left join alamat_m as alm on alm.nocmfk=ps.id
                left join desakelurahan_m as ds on ds.id=alm.objectdesakelurahanfk
                left join kotakabupaten_m as kk on kk.id=alm.objectkotakabupatenfk
                left join kecamatan_m as kc on kc.id=alm.objectkecamatanfk
                left join propinsi_m as pro on pro.id=alm.objectpropinsifk
                INNER JOIN pasiendaftar_t AS pd ON pd.norec = so.noregistrasifk
                INNER JOIN kelompokpasien_m AS kp ON kp. ID = pd.objectkelompokpasienlastfk
                INNER JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
                INNER JOIN ruangan_m AS ru2 ON ru2. ID = so.objectruangantujuanfk
                LEFT JOIN jeniskelamin_m AS jk ON jk. ID = ps.objectjeniskelaminfk
             
                LEFT JOIN pegawai_m AS pg ON pg. ID = so.objectpegawaiorderfk
                LEFT JOIN departemen_m AS dp ON dp. ID = ru.objectdepartemenfk 
                --   LEFT JOIN order_lab AS lis ON lis.no_lab = so.noorder
                WHERE
                    so.noorder = '$noorder'
                    and so.kdprofile=$kdProfile
                    and so.statusenabled=true
                                "));
        // return $this->respond($raw);

        DB::beginTransaction();
     
        try {

            $pdnorec = $raw[0]->norec_pd;
            $diag =  DB::select(DB::raw("select DISTINCT dg.kddiagnosa , dg.namadiagnosa,pd.noregistrasi
                from pasiendaftar_t as pd 
                join antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
                join detaildiagnosapasien_t  as ddp on ddp.noregistrasifk=apd.norec
                join diagnosa_m  as dg on dg.id=ddp.objectdiagnosafk
                where pd.kdprofile=$kdProfile
                and pd.statusenabled =true
                and pd.norec ='$pdnorec'"));
     
            $arrDiag ='';
            if(count($diag) > 0){
                foreach ($diag as $d){
                    $arrDiag = $d->kddiagnosa .'-' .$d->namadiagnosa .  ' ,' .$arrDiag;
                }
            }

            StrukOrder::where('noorder',$noorder)
                ->where('kdprofile', $kdProfile)
                ->update(
                [
                    'statusorder' => 1
                ]
            );


            foreach($raw as $item ){
                if ($item->jkid == 1){
                    $jk = 'L';
                }else if($item->jkid == 2){
                    $jk = 'P';
                }else{
                    $jk = 'L';
                }
                if ($item->cito == '1') {
                    $priority = 'U';
                } else {
                    $priority = 'R';
                }
                $deptRanap = explode (',',$this->settingDataFixed('kdDepartemenRanapFix',$kdProfile));
                $jenisRawat = '';
                foreach ($deptRanap as $itemRanap){
                    if((int)$itemRanap == $item->departemenid){
                        $jenisRawat = 'RANAP';
                        break;
                    }else{
                        $jenisRawat = 'RAJAL';
                        break;
                    }
                }
                $harga =  HargaNettoProdukByKelas1::where('objectprodukfk',$item->produkid)
                    ->where('kdprofile', $kdProfile)
                    ->where('objectkelasfk',6)
                    ->where('statusenabled',true)
                    ->first();
//                return $harga;
                $cek = OrderLab::where('no_lab',$noorder)
                    ->where('kode_test', $item->produkid)->first();
                if(!empty($cek)){
                     OrderLab::where('no_lab',$noorder)
                         ->where('kode_test', $item->produkid)->delete();
                }
                $newBRG = new OrderLab();
                $newBRG->asal_lab = $jenisRawat;
                $newBRG->no_lab = $item->noorder;
                $newBRG->no_registrasi = $item->noregistrasi;
                $newBRG->nama_pas = $item->namapasien;
                $newBRG->no_rm = $item->nocm;
                $newBRG->tgl_order = $item->tglorder;
                $newBRG->jam_order = $item->jamorder;
                $newBRG->jenis_kel = $jk;
                $newBRG->tgl_lahir = $item->tgllahir;
                $newBRG->usia = $item->usia;
                $newBRG->alamat = $item->alamatlengkap;
                $newBRG->kode_dok_kirim = $request['iddokterverif'];//$request['iddokterorder'];
                $newBRG->nama_dok_kirim =$request['namadokterverif'];//  $request['namadokterorder'];
                $newBRG->kode_ruang = $item->ruanganid;
                $newBRG->nama_ruang = $item->namaruangan;
                $newBRG->kode_cara_bayar = $item->kode_cara_bayar;
                $newBRG->cara_bayar = $item->cara_bayar;
                $newBRG->ket_klinis = '';

                $produk= Produk::where('statusenabled',true)
                    ->where('kdprofile', $kdProfile)
                    ->where('id',$item->produkid)
                    ->first();

                // $newBRG->kode_test = $produk->kodeexternal != 'SOLO' && $produk->kodeexternal != 'LAB PAKET'  ? $produk->kodeexternal : $item->produkid;
                $newBRG->kode_test = $item->produkid;
                $newBRG->nama_test = $item->namaproduk;
                $newBRG->waktu_kirim = date('Y-m-d H:i:s');
                $newBRG->jns_rawat = $jenisRawat;
                $newBRG->dok_jaga = $item->pgid;
                $newBRG->prioritas = $item->cito;
                $newBRG->hargasatuan = (float)$harga->hargasatuan;
//                $newBRG->status = $item->id;
                $newBRG->norec = $newBRG->generateNewId();
                if(isset( $request['catatan'])){
                    $newBRG->catatan =$request['catatan'];
                }
                $newBRG->save();

                $statusBrigding =  $this->settingDataFixed('isBridgingLIS',$kdProfile);
//                $users = DB::connection('sqlsrv')->table('KirimLis')->get();
//             return $this->respond($users);
                if(!empty($statusBrigding) && $statusBrigding =='true'){
                    try {
                         $catatan  = null;
                        if(isset( $request['catatan'])){
                           $catatan =$request['catatan'];
                        }
    //                    DB::connection('sqlsrv')->getPdo();
                        $dataExt =array(
                            'kode' => substr(Uuid::generate(), 0, 32),
                            'modified_date' =>  date('Y-m-d H:i:s') ,
                            'No_Pasien' => $item->nocm,
                            'Kode_Kunjungan' => $item->noorder,
                            'Nama' =>$item->namapasien,
                            'Email' =>'' ,
                            'Date_of_birth' => $item->tgllahir,
                            'UmurTahun' => $this->getUmurThn($item->tgllahir,date('Y-m-d')),
                            'UmurBulan' => $this->getUmurBln($item->tgllahir,date('Y-m-d')),
                            'UmurHari' => $this->getUmurHr($item->tgllahir,date('Y-m-d')),
                            'Gender' => $jk,
                            'Alamat' => $item->alamatlengkap ,
                            'Diagnosa' => '' ,
                            'Tgl_Periksa'=>  date('Y-m-d H:i:s'),//$item->tglorder ,//date('Y-m-d H:i:s') ;
                            'Pengirim'=> $request['iddokterverif'] ,
                            'pengirim_name' => $request['namadokterverif'],
                            'Kelas' => 6,
                            'kelas_name' => 'Non Kelas' ,
                            'Ruang' => $item->ruanganid ,
                            'ruang_name' => $item->namaruangan ,
                            'Cara_Bayar' => $item->kode_cara_bayar,
                            'cara_bayar_name' => $item->cara_bayar ,
                            'Kode_Tarif' =>$item->produkid ,
                            'IS_Inap'=> $jenisRawat =='RANAP' ? 1 : 0,
                            'Status' => null ,
                            'update' =>'N' ,
                            'No_reg' =>$item->noregistrasi ,
                            'FLAG_TAKEN' => 0 ,
                            'NIK' =>  $item->nik ,
                            'CATATAN' => $catatan  ,
                            'Diagnosa' => $arrDiag  ,
                        );
    //                    return $dataExt;
                        DB::connection('sqlsrv')->table('KirimLis')->insert(
                            $dataExt
                        );
                    } catch (\Exception $e) {
                        die("Could not connect to the database.  Please check your configuration. error:" . $e );
                    }
                }

//                return $mode_ext;
            }


            $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
            }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $newBRG,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = "Gagal Menyimpan Data";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "sysmex" => $newBRG,
                // "e" => $e,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getUmurThn($tgllahir,$now){
        $datetime = new \DateTime(date($tgllahir));
        return $datetime->diff(new \DateTime($now))
            ->format('%y');
    }
    public function getUmurBln($tgllahir,$now){
        $datetime = new \DateTime(date($tgllahir));
        return $datetime->diff(new \DateTime($now))
            ->format('%m');
    }
    public function getUmurHr($tgllahir,$now){
        $datetime = new \DateTime(date($tgllahir));
        return $datetime->diff(new \DateTime($now))
            ->format('%d');
    }

    public function saveHapusOrderLab(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        DB::beginTransaction();

        try {


            foreach($request['data'] as $item ){
                try {
                    $datas = OrderLab::where('no_lab',$item['noorder'])
                        ->where('kode_test', $item['produkfk'])->first();
//                    DB::connection('sqlsrv')->getPdo();
                    $dataExt =array(
                        'kode' => substr(Uuid::generate(), 0, 32),
                        'modified_date' =>  date('Y-m-d H:i:s') ,
                        'No_Pasien' => $datas->no_rm,
                        'Kode_Kunjungan' => $datas->no_lab,
                        'Nama' =>$datas->nama_pas,
                        'Email' =>'' ,
                        'Date_of_birth' => $datas->tgl_lahir,
                        'UmurTahun' =>'' ,
                        'UmurBulan' =>'' ,
                        'UmurHari' => '' ,
                        'Gender' => $datas->jenis_kel,
                        'Alamat' => $datas->alamat ,
                        'Diagnosa' => '' ,
                        'Tgl_Periksa'=> $datas->waktu_kirim ,//date('Y-m-d H:i:s') ;
                        'Pengirim'=> $datas->kode_dok_kirim ,
                        'pengirim_name' =>  $datas->nama_dok_kirim,
                        'Kelas' => 6,
                        'kelas_name' => 'Non Kelas' ,
                        'Ruang' => $datas->kode_ruang ,
                        'ruang_name' => $datas->nama_ruang ,
                        'Cara_Bayar' => $datas->kode_cara_bayar,
                        'cara_bayar_name' => $datas->cara_bayar ,
                        'Kode_Tarif' =>$datas->kode_test ,
                        'IS_Inap'=> $datas->asal_lab =='RANAP' ? 1 : 0,
                        'Status' => null ,
                        'update' =>'D' ,
                        'No_reg' => $datas->no_registrasi ,
                        'FLAG_TAKEN' => 0 ,
                    );
//                    return $dataExt;
                    DB::connection('sqlsrv')->table('KirimLis')->insert(
                        $dataExt
                    );

                    $cek = OrderLab::where('no_lab',$item['noorder'])
                        ->where('kode_test', $item['produkfk'])->delete();
                    $struk = StrukOrder::where('noorder',$item['noorder'])
                        ->where('kdprofile',$kdProfile)
                        ->update(
                          [
                              'statusenabled'=>false
                          ]
                        );
                } catch (\Exception $e) {
                    die("Could not connect to the database.  Please check your configuration. error:" . $e );
                }

//                return $mode_ext;
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses Hapus Table Ext";
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $newBRG,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = "Gagal Menyimpan Data";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "sysmex" => $newBRG,
                // "e" => $e,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getHasilLIS(Request $request)
    {
        $kdProfile = (int)$this->getDataKdProfile($request);
//        $data = DB::table('lisorderdetail')
//            ->where('kdprofile',$kdProfile)
//            ->where('ono',$request['noorder'])
//            ->first();
//        if(empty($data)){
//            $result = array(
//                "status" => false,
//                "as" => 'er@epic',
//            );
        // return $this->respond($request['noorder']);
//        }
        $noor =$request['noorder'];
//        $data = DB::select(DB::raw("
//            SELECT li.*,prd.namaproduk,djp.detailjenisproduk
//            FROM lisorderdetail as li
//            join produk_m as prd on prd.id=li.produkfk
//            left join detailjenisproduk_m as djp on djp.id=prd.objectdetailjenisprodukfk
//            where li.order_id ='$noor'"));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://10.122.250.13:83/lab_result/api/examination_result/result/'.$request['noorder'],//$data->result_id,
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
        $res = array(
            'resBridging' => $result,
            'produk' => []//$data
        );
        return $this->respond($res);

    }
    public function saveUpdateCatatan(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        DB::beginTransaction();

        try {
              try {
                    $datas = OrderLab::where('no_lab',$request['noorder'])
                        // ->where('kdprofile', $kdProfile )
                        ->update([
                            'catatan' => $request['catatan']
                        ]);
                    $dataExt =array(
                        'CATATAN' => $request['catatan']
                    );

                     $ext = DB::connection('sqlsrv')->table('KirimLis')->where('Kode_Kunjungan',$request['noorder'])
                     ->update(
                        $dataExt
                    );

                   
                } catch (\Exception $e) {
                    die("Could not connect to the database.  Please check your configuration. error:" . $e );
                }

//                return $mode_ext;
           

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $newBRG,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = "Gagal Menyimpan Data";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "sysmex" => $newBRG,
                // "e" => $e,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
      public function getCatatan(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        $datas = OrderLab::where('no_lab',$request['noorer'])
        ->first();
        return $this->respond($datas);
    }

}