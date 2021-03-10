<?php
/**
 * Created by PhpStorm.
 * User: efan (ea@epic)
 * Date: 13/09/2019
 * Time: 09:12
 */

namespace App\Http\Controllers\BendaharaPenerimaan;


use App\Http\Controllers\ApiController;
use App\Master\Pegawai;
use App\Traits\Valet;
use App\Transaksi\StrukBuktiPenerimaan;
use App\Transaksi\StrukBuktiPenerimaanCaraBayar;
use App\Transaksi\StrukBuktiPengeluaran;
use App\Transaksi\StrukClosing;
use App\Transaksi\StrukClosingKasir;
use App\Transaksi\StrukHistori;
use App\Transaksi\TempBeritaAcaraKasBank;
use App\Transaksi\TempBukuKasUmum;
use App\Transaksi\TempLampiranBeritaAcaraKasBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BendaharaPenerimaanController extends ApiController {

    use Valet;
    public function __construct(){
        parent::__construct($skip_authentication = false);
    }

    public function getDataCombo(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $caraSetor = \DB::table('carasetor_m as cs')
            ->select('cs.*')
            ->where('cs.kdprofile',$idProfile)
            ->where('cs.statusenabled', true)
            ->orderBy('cs.id');
        if (isset($request['kdcarasetor']) && $request['kdcarasetor'] != "" && $request['kdcarasetor'] != "undefined") {
            $caraSetor = $caraSetor->where('cs.kdcarasetor', '=', $request['kdcarasetor']);
        }
        if (isset($request['id']) && $request['id'] != "" && $request['id'] != "undefined") {
            $caraSetor = $caraSetor->where('cs.id', '=', $request['id']);
        }
        if (isset($request['carasetor']) && $request['carasetor'] != "" && $request['carasetor'] != "undefined") {
            $caraSetor = $caraSetor->where('cs.carasetor', 'ilike','%'. $request['carasetor'].'%');
        }
        $caraSetor=$caraSetor->get();

        $dataKasir= \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s lu
                INNER JOIN pegawai_m pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and objectkelompokuserfk=:id and pg.statusenabled=true"),
            array(
                'id' => 20,
            )
        );

        $kelompokTransaksi= \DB::table('mapbkutokelompoktransaksi_m as mp')
            ->join('bku_m as bku','bku.id','=','mp.idbku')
            ->join('kelompoktransaksi_m as kt','kt.id','=','mp.kelompoktransaksifk')
            ->select('mp.id','mp.kelompoktransaksifk','mp.idbku','bku.bku','kt.kelompoktransaksi')
            ->where('mp.kdprofile',$idProfile)
            ->where('mp.statusenabled',true);
        if (isset($request['keterangan']) && $request['keterangan'] != "" && $request['keterangan'] != "undefined") {
            $kelompokTransaksi = $kelompokTransaksi->where('bku.bku', '=', $request['keterangan']);
        }
        $kelompokTransaksi= $kelompokTransaksi ->get();

        $mataAnggaran= \DB::table('mataanggaran_t as m')
            ->select('m.mataanggaran','m.norec')
            ->where('m.kdprofile',$idProfile)
            ->where('m.statusenabled',true)
            ->orderBy('m.mataanggaran')
            ->get();

        $caraBayar = \DB::table('carabayar_m')
            ->select('id','carabayar as caraBayar','carabayar as namaExternal')
            ->where('kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->get();

        $listBank = \DB::table('bank_m')
            ->select('id','nama')
            ->where('kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->get();

        $listBankAccount = \DB::table('bankaccount_m')
            ->select('id','reportdisplay as bankAccountNama')
            ->where('kdprofile', $idProfile)
            ->where('statusenabled',true)
            ->get();

        $asalProduk = \DB::table('asalproduk_m')
            ->select('id','asalproduk')
            ->where('statusenabled',true)
            ->where('kdprofile', $idProfile)
            ->get();

        $result = array(
            'carasetor' => $caraSetor,
            'datalogin' => $dataLogin,
            'datakasir' => $dataKasir,
            'kelompoktransaksi' => $kelompokTransaksi,
            'mataanggaran' => $mataAnggaran,
            'carabayar' => $caraBayar,
            'listbank' => $listBank,
            'listbankaccount' => $listBankAccount,
            'asalproduk'=>$asalProduk,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getDaftarSBM(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukbuktipenerimaan_t as sbm')
            ->join('strukpelayanan_t as sp', 'sbm.nostrukfk', '=', 'sp.norec')
            ->leftjoin('pasiendaftar_t as pd', 'sp.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as p', 'p.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pasien_m as ps', 'ps.id', '=', 'sp.nocmfk')
            ->leftjoin('strukbuktipenerimaancarabayar_t as sbmcr', 'sbmcr.nosbmfk', '=', 'sbm.norec')
            ->leftjoin('carabayar_m as cb', 'cb.id', '=', 'sbmcr.objectcarabayarfk')
            ->leftjoin('kelompoktransaksi_m as kt', 'kt.id', '=', 'sbm.objectkelompoktransaksifk')
            ->leftjoin('strukclosing_t as sc', 'sc.norec', '=', 'sbm.noclosingfk')
            ->leftjoin('strukverifikasi_t as sv', 'sv.norec', '=', 'sbm.noverifikasifk')
            ->select('sbm.norec as noRec','cb.carabayar as caraBayar','sbmcr.objectcarabayarfk as idCaraBayar','sbm.objectkelompoktransaksifk as idKelTransaksi',
                'kt.kelompoktransaksi as kelTransaksi','sbm.keteranganlainnya as keterangan','p.id as idPegawai','p.namalengkap as namaPenerima',
                'sc.noclosing as noClosing','sbm.nosbm as noSbm','sv.noverifikasi as noVerifikasi','sc.tglclosing as tglClosing',
                'sbm.tglsbm as tglSbm','sv.tglverifikasi as tglVerif','sbm.totaldibayar as totalPenerimaan','pd.noregistrasi','ps.namapasien',
                'sp.norec as norec_sp','ru.id as ruid','ru.namaruangan','sp.namapasien_klien','ps.nocm','sbm.noclosingfk')
            ->where('sbm.kdprofile', $idProfile);
        //->whereNull('pd.nostruklastfk');

        $filter = $request->all();
        if(isset($filter['dateStartTglSbm']) && $filter['dateStartTglSbm'] != "" && $filter['dateStartTglSbm'] != "undefined") {
            $tgl2 = $filter['dateStartTglSbm'] ;//. " 00:00:00";
            $data = $data->where('sbm.tglsbm', '>=', $tgl2);
        }

        if(isset($filter['dateEndTglSbm']) && $filter['dateEndTglSbm'] != "" && $filter['dateEndTglSbm'] != "undefined") {
            $tgl = $filter['dateEndTglSbm'] ;//. " 23:59:59";
            $data = $data->where('sbm.tglsbm', '<=', $tgl);
        }

        if(isset($filter['idPegawai']) && $filter['idPegawai'] != "" && $filter['idPegawai'] != "undefined") {
            $data = $data->where('p.id', '=', $filter['idPegawai']);
        }

        if(isset($filter['idCaraBayar']) && $filter['idCaraBayar'] != "" && $filter['idCaraBayar'] != "undefined") {
            $data = $data->where('cb.id', '=', $filter['idCaraBayar']);
        }

        if(isset($filter['idKelTransaksi']) && $filter['idKelTransaksi'] != "" && $filter['idKelTransaksi'] != "undefined") {
            $data = $data->where('kt.id', $filter['idKelTransaksi']);
        }
        if(isset($filter['ins']) && $filter['ins'] != "" && $filter['ins'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', $filter['ins']);
        }
        if(isset($filter['nosbm']) && $filter['nosbm'] != "" && $filter['nosbm'] != "undefined") {
            $data = $data->where('sbm.nosbm','ilike','%'.$filter['nosbm'].'%');
        }
        if(isset($filter['nocm']) && $filter['nocm'] != "" && $filter['nocm'] != "undefined") {
            $data = $data->where('ps.nocm','ilike','%'.$filter['nocm'].'%');
        }
        if(isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien','ilike','%'.$filter['nama'].'%');
        }
        if(isset($filter['desk']) && $filter['desk'] != "" && $filter['desk'] != "undefined") {
            $data = $data->where('sp.namapasien_klien','ilike','%'.$filter['desk'].'%');
        }
        if(isset($filter['KetSetor']) && $filter['KetSetor'] != "" && $filter['KetSetor'] != "undefined") {
            if ($filter['KetSetor'] == 1){
                $data = $data->whereNull('sc.noclosing');
            }elseif ($filter['KetSetor'] == 2){
                $data = $data->whereNotNull('sc.noclosing');
            }
        }
        if(isset($request['KasirArr']) && $request['KasirArr']!="" && $request['KasirArr']!="undefined"){
            $arrRuang = explode(',',$request['KasirArr']) ;
            $kodeRuang = [];
            foreach ( $arrRuang as $item){
                $kodeRuang[] = (int) $item;
            }
            $data = $data->whereIn('p.id',$kodeRuang);
        }
//        $data = $data->whereNull('sbm.noclosingfk');
        $data = $data->get();

        $result=[];
        foreach ($data as $item) {
            $noclosingfk = $item->noclosingfk;
            $caraBayar = $item->caraBayar;
            $details= \DB::select(DB::raw("
                    select
                  --count(x.id) as idCaraBayar,x.caraBayar ,sum (x.jumlah) as jumlah from(
                    distinct sck.noclosingfk,cb.id,
                    cb.carabayar as caraBayar,sh.noclosing,sck.totaldibayar as jumlah,
                    sc.totaldibayar as total,
                    sh.objectpegawaiterimafk, pg.namalengkap as pegawaipenerima
                    from strukclosingkasir_t  as sck
                    left join strukclosing_t as sc on sc.norec=sck.noclosingfk
                    left join carabayar_m as cb on cb.id=sck.carabayarfk
                    left join strukhistori_t as sh on sh.noclosing=sc.noclosing
                    LEFT JOIN carasetor_m as cs on cs.id=sck.objectcarasetorfk
                    left join pegawai_m as pg on pg.id=sh.objectpegawaiterimafk
                    where sck.kdprofile = $idProfile and sck.noclosingfk ='$noclosingfk'
                   -- and sc.objectkelompoktransaksifk=60
                   -- and cb.carabayar ='$caraBayar'
                    GROUP BY cs.carasetor,sck.objectcarasetorfk,cb.id,
                    sck.totaldibayar, sc.totaldibayar,sck.noclosingfk, sh.noclosing,
                    sh.objectpegawaiterimafk, pg.namalengkap,sck.totaldibayar,cb.carabayar
                    --)as x GROUP BY x.caraBayar")
            );

            $result[] = array(
                'noRec' => $item->noRec,
                'caraBayar' => $item->caraBayar,
                'idCaraBayar' => $item->idCaraBayar,
                'idKelTransaksi' => $item->idKelTransaksi,
                'kelTransaksi' => $item->kelTransaksi,
                'keterangan' => $item->keterangan,
                'idPegawai' => $item->idPegawai,
                'namaPenerima' => $item->namaPenerima,
                'noClosing' => $item->noClosing,
                'noSbm' => $item->noSbm,
                'tglSbm' =>$item->tglSbm,
                'noVerifikasi' => $item->noVerifikasi,
                'tglClosing' => $item->tglClosing,
                'norec_sp' => $item->norec_sp,
                'totalPenerimaan' => $item->totalPenerimaan,
                'namapasien' => $item->namapasien,
                'ruid' => $item->ruid,
                'namaruangan' => $item->namaruangan,
                'namapasien_klien' => $item->namapasien_klien,
//                'noclosingfk' => $item->noclosingfk,
                'nocm' => $item->nocm,
                'details' => $details,
            );
        }

        $result = array(
            'data' => $result,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function simpanSetoranKasir(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdKelTrans = (int) $this->settingDataFixed('KdTransSetoranKasir',$idProfile);
        $transMsg = null;
        \DB::beginTransaction();
        try{
            $input  = $request->all();

            $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap,lu.id as userId from loginuser_s as lu
                    INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                    where lu.kdprofile = $idProfile and pg.id=:idLoginUser limit 1"),
                array(
                    'idLoginUser' => $input['kdPegawai'],
                )
            );

            $UserId = 0;
            foreach ($dataPegawaiUser as $items){
                $UserId = $items->userid;
            }

            $dataruangan = \DB::table('maploginusertoruangan_s as mlu')
                ->leftjoin('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
                ->leftjoin('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
                ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
                ->where('mlu.kdprofile', $idProfile)
                ->where('objectloginuserfk',$input['userData']['id'])
                ->get();
            if (count($dataruangan)> 0){
                $idRuangan=$dataruangan[0]->id;
            }
//            return $this->respond($UserId);
            $pegawai = Pegawai::find($input['kdPegawai']);
            $tglAwal=$input['tglAwal'];
            $tglAkhir=$input['tglAkhir'];

            $SC = new StrukClosing();
            $SC->norec  = $SC->generateNewId();
            $SC->kdprofile = $idProfile;
            $SC->noclosing = $this->generateCode(new StrukClosing, 'noclosing', 10, 'C-'.$this->getDateTime()->format('ym'),$idProfile);
            $SC->objectpegawaidiclosefk = $input['kdPegawai'];
            $SC->tglclosing = $input['tglsetor'];
            $SC->totaldibayar = $input['totalSetoran'];
            $SC->objectkelompoktransaksifk = $kdKelTrans;
            $SC->keteranganlainnya = "Setoran Kasir";
            $SC->tglawal = $tglAwal;
            $SC->tglakhir = $tglAkhir;
            $SC->tglclosing = $this->getDateTime()->format('Y-m-d H:i:s');
            $SC->save();
            $NorecSc = $SC->norec;

            $SH = new StrukHistori();
            $SH->norec  = $SH->generateNewId();
            $SH->nonhistori = $this->generateCode(new StrukHistori(), 'nonhistori', 14, 'SK-'.$this->getDateTime()->format('ym'),$idProfile);
            //$SH->objectbankaccountfk= $item['kdAccountBank'];
            $SH->kdprofile = $idProfile;
            $SH->statusenabled= true;
            $SH->totalsetortarikdeposit =$input['totalSetoran'];
            $SH->tglsetortarikdeposit =  $input['tglsetor'];;//$this->getDateTime();
            $SH->objectpegawaitarikdepositfk = $input['kdPegawai'];
            $SH->objectpegawaiterimafk = $this->getCurrentUserID();
            $SH->objectkelompoktransaksifk = $kdKelTrans;
            $SH->noclosing = $NorecSc;//$SC->noclosing;
            // $SH->objectcarasetorfk = $item['idCaraSetor'];
            $SH->ketlainya ='Setoran Kasir';
            if(isset($idRuangan)){
                $SH->objectruanganterimafk = $idRuangan;
                $SH->objectruanganfk = $idRuangan;
            }

            $SH->nobukti = '-';
            $SH->kdperkiraan = '-';//$input['kdperkiraan'];
            $SH->namaperkiraan = 'Penerimaan Kasir ';//$input['keterangan'];
            $SH->kettransaksi = '-';// $input['keterangantransaksi'];
            $SH->save();

            /** @Save_StrukBuktiPenerimaan */
            $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
            $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
            $strukBuktiPenerimanan->kdprofile = $idProfile;
            $strukBuktiPenerimanan->keteranganlainnya = 'Setoran Kasir';//nama perkiraan
            $strukBuktiPenerimanan->statusenabled = 1;
            //      $strukBuktiPenerimanan->nostrukfk = $strukPelayanan->norec;
            //      $strukBuktiPenerimanan->objectkelompokpasienfk = $strukPelayanan->pasien_daftar->pasien->objectkelompokpasienfk;
            $strukBuktiPenerimanan->objectpegawaipenerimafk = $this->getCurrentLoginID();
            $strukBuktiPenerimanan->tglsbm =  $input['tglsetor'];;//date('Y-m-d H:i:s');
            $strukBuktiPenerimanan->totaldibayar =  $input['totalSetoran'];//$input['totalSetoran'];
            $strukBuktiPenerimanan->objectkelompoktransaksifk = $kdKelTrans;
            $strukBuktiPenerimanan->nosbm = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-' . $this->getDateTime()->format('ym'),$idProfile);
            $strukBuktiPenerimanan->noclosingfk = $NorecSc;
            $strukBuktiPenerimanan->save();
            /** @End_Save_StrukBuktiPenerimaan */

            foreach ($input['detailSetoran'] as $item){
                $SCK = new StrukClosingKasir();
                $SCK->norec  = $SC->generateNewId();
                $SCK->noclosingfk = $NorecSc; //$SC->norec;
                $SCK->totaldibayar = $item['totalPenerimaan'];
                $SCK->totaldibayarcashin = 0;
                $SCK->totaldibayarcashout = 0;
                $SCK->totaldibayarclose = 0;
                $SCK->carabayarfk = $item['kdCaraBayar'];
                $SCK->qtystrukbuktiextclose = 0;
                $SCK->qtystrukbuktiintclose = 0;
                $SCK->objectcarasetorfk = $item['idCaraSetor'];
                $SCK->save();

                /** @Save_StrukBuktiPenerimaanCaraBayar */
                $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                $SBPCB->norec = $SBPCB->generateNewId();
                $SBPCB->kdprofile = $idProfile;
                $SBPCB->statusenabled = 1;
                $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                $SBPCB->objectcarabayarfk = $item['kdCaraBayar'];
                if (isset($input['detailBank'])) {
                    $SBPCB->objectbankaccountfk = $input['detailBank']['id'];
                    $SBPCB->namabankprovider = $input['detailBank']['namaBank'];
                    $SBPCB->namapemilik = $input['detailBank']['namaKartu'];
                }
                $SBPCB->save();
                /** @End_Save_SStrukBuktiPenerimaanCaraBayar */
            }
            foreach ($input['detailSBM'] as $item2){
                $updateSBM = StrukBuktiPenerimaan::where('norec', $item2['norec_sbm'])//$input['kdPegawaiLu'])
                ->where('kdprofile',$idProfile)
                ->whereNull('noclosingfk')
                ->update([
                        'noclosingfk' => $NorecSc
                ]);
            }
//            $data2= StrukBuktiPenerimaan::where('objectpegawaipenerimafk', $UserId)//$input['kdPegawaiLu'])
//                ->whereNull('noclosingfk')
//                ->where('tglsbm','>=', $tglAwal)
//                ->where('tglsbm','<=', $tglAkhir)
//                ->update([
//                        'noclosingfk' => $NorecSc
//                ]);

        $transStatus = true;
        } catch(\Exception $e){
            $transStatus= false;
        }

        $transMsg = "";
        if ($transStatus == true) {
            $transMsg = 'Simpan Berhasil';
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMsg,
                "data" => $SC,
                "as" => 'as@epic',
            );
        } else {
            $transMsg = 'Simpan Gagal!!';
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMsg,
                "data" => $SC,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMsg);
    }

    public function batalSetoranKasir(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $transStatus = true;
        \DB::beginTransaction();
        $input  = $request->all();
        /** @Save_OLD */
//        $tglAwal=$input['tglAwal'];
//        $tglAkhir=$input['tglAkhir'];
//
//
//        $sbm= StrukBuktiPenerimaan::where('objectpegawaipenerimafk', $input['kdPegawaiLu'])
//            ->whereNotNull('noclosingfk')
//            ->where('tglsbm','>=', $tglAwal)
//            ->where('tglsbm','<=', $tglAkhir)
//            ->first();
//        $sc= StrukClosing::where('norec',$sbm->noclosingfk)->first();
//        $sh= StrukHistori::where('noclosing',$sc->noclosing)->first();
//        try{
//            $data3= StrukHistori::where('norec', $sh->norec)
//                ->update([
//                    'statusenabled' => 'f'
//                ]);
//        }
//        catch(\Exception $e){
//            $transStatus= false;
//            $transMsg = "Transaksi Gagal (update SBP)";
//        }
//        if($transStatus) {
//            try {
//                $data2 = StrukBuktiPenerimaan::where('objectpegawaipenerimafk', $input['kdPegawaiLu'])
//                    ->whereNotNull('noclosingfk')
//                    ->where('tglsbm', '>=', $tglAwal)
//                    ->where('tglsbm', '<=', $tglAkhir)
//                    ->update(
//                        [
//                            'noclosingfk' => null
//                        ]);
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Transaksi Gagal (update SBP)";
//            }
//        }
        /** @End_Save_OLD */
        try {
            foreach ($input['details'] as $item){
                $sc = StrukClosing::where('noclosing',$item['noclosing'])->where('kdprofile', $idProfile)->first();
                $sh = StrukHistori::where('noclosing', $sc->norec)
                    ->where('kdprofile', $idProfile)
                    ->update([
                        'statusenabled' => false
                    ]);
                $sbm = StrukBuktiPenerimaan::where('norec', $item['norec_sbm'])
                        ->where('kdprofile', $idProfile)
                        ->whereNotNull('noclosingfk')
                        ->update(
                            [
                                'noclosingfk' => null
                            ]);

            }
        } catch (\Exception $e) {
                $transStatus = false;
        }
        if ($transStatus == true) {
            $transMsg = 'Batal Setor Sukses';
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMsg,
                "data" => $sh,
                "as" => 'as@epic',
            );
        } else {
            $transMsg = 'Gagal!!';
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMsg,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMsg);
    }

    public function daftarBKU(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $list =  explode ( ',',$this->settingDataFixed('KdTransaksiBendaharaPenerimaan',$idProfile));
        $kdTrans = [];
        $KdTransak = $this->settingDataFixed('KdTransaksiBendaharaPenerimaan',$idProfile);
        foreach ($list as $itemTrans){
            $kdTrans []=  (int)$itemTrans;
        }
        $dataPenerimaanBank= \DB::table('strukhistori_t as sh')
            ->join('strukclosing_t as sc', 'sc.norec', '=', 'sh.noclosing')
            ->leftjoin ('strukbuktipenerimaan_t as spp',function($join)
            {
                $join->on('spp.noclosingfk','=','sc.norec')
                    ->where('spp.keteranganlainnya', 'ilike', '%'.'Setoran'.'%');
//                $join->on('spp.keteranganlainnya','=','Setoran Kasir');
            })
//            ->leftjoin('strukbuktipenerimaan_t as spp', 'spp.noclosingfk', '=','sc.norec')
//                \DB::raw("sc.norec and spp.objectkelompoktransaksifk in (60,64,70,105,106,119,120)"))
            ->leftjoin('strukbuktipengeluaran_t as sbk', 'sbk.noclosingfk', '=', 'sc.norec')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'spp.objectpegawaipenerimafk')
            ->leftjoin('loginuser_s as lu2', 'lu2.id', '=', 'sbk.objectpegawaipembayarfk')
            ->leftjoin('pegawai_m as p', 'p.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pegawai_m as p2', 'p2.id', '=', 'lu2.objectpegawaifk')
            ->leftjoin('pegawai_m as psetor', 'psetor.id', '=', 'sh.objectpegawaitarikdepositfk')
            ->join('kelompoktransaksi_m as kt', 'kt.id', '=', 'sc.objectkelompoktransaksifk')
            ->join('mapbkutokelompoktransaksi_m as mbk','mbk.kelompoktransaksifk','=','kt.id')
            // ->leftjoin('strukbuktipenerimaancarabayar_t as spc', 'spc.nosbmfk', '=', 'spp.norec')
            // ->leftjoin('bankaccount_m as ba', 'ba.id', '=', 'spc.objectbankaccountfk')
            ->select('spp.norec', 'spp.tglsbm', 'spp.keteranganlainnya','spp.nosbm_intern',
                'spp.objectpegawaipenerimafk','p.namalengkap', 'kt.kelompoktransaksi','spp.nostrukfk',
                'sc.objectkelompoktransaksifk',
                'sc.norec as norec_sc',
                // 'spc.objectbankaccountfk','ba.bankaccountnama','spc.namabankprovider','spc.namapemilik',
                'sc.noclosing','sh.nonhistori','sbk.objectpegawaipembayarfk','p2.namalengkap as pegawaibayar',
                'sh.ketlainya','sh.norec as norec_sh','sh.kdperkiraan','sh.namaperkiraan','sh.kettransaksi','sh.nobukti','psetor.namalengkap as penyetor',
                DB::raw(" coalesce((spp.totaldibayar ), 0) debit ,
                     coalesce((sbk.totaldibayar ), 0) kredit ,
                    case when spp.nosbm is null then sbk.nosbk else spp.nosbm end as notransaksi,
                    cast(sh.tglsetortarikdeposit as date)as tglsetortarikdeposit"))
            ->groupBy('spp.norec','spp.tglsbm','spp.keteranganlainnya','spp.nosbm_intern','spp.objectpegawaipenerimafk','p.namalengkap',
                      'kt.kelompoktransaksi','spp.nostrukfk','sc.objectkelompoktransaksifk','sc.norec','sc.noclosing','sh.nonhistori',
                      'sbk.objectpegawaipembayarfk','p2.namalengkap','sh.ketlainya','sh.norec','sh.kdperkiraan',
                      'sh.namaperkiraan','sh.kettransaksi','sh.nobukti','psetor.namalengkap','spp.totaldibayar','sbk.totaldibayar',
                      'spp.nosbm','sbk.nosbk','sh.tglsetortarikdeposit','sc.tglclosing')
            ->orderBy('sc.tglclosing','asc')
            ->where('sh.kdprofile', $idProfile)
            ->where('sh.statusenabled',true);
//            ->whereIn('sh.objectkelompoktransaksifk',[60,64,70,105,106,119,120]);

        $filter = $request->all();
//        if($filter['noStruk']!="" && $filter['noStruk']!="undefined"){
//            $dataPenerimaanBank = $dataPenerimaanBank->where('spp.norec', $filter['noStruk']);
//        }
        if(isset( $filter['jenisTransaksi']) && $filter['jenisTransaksi']!="" && $filter['jenisTransaksi']!="undefined"){

            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.objectkelompoktransaksifk', $filter['jenisTransaksi']);
        }
        if(isset( $filter['jenisTransaksiLike']) && $filter['jenisTransaksiLike']!="" && $filter['jenisTransaksiLike']!="undefined"){

            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.objectkelompoktransaksifk', $filter['jenisTransaksiLike']);
        }
        if(isset( $filter['noRekening']) &&$filter['noRekening']!="" && $filter['noRekening']!="undefined"){
            $dataPenerimaanBank = $dataPenerimaanBank->where('spc.namabankprovider', $filter['noRekening']);
        }
        if(isset( $filter['tglAwal']) &&$filter['tglAwal']!=""){
            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.tglsetortarikdeposit','>=', $filter['tglAwal']." 00:00:00");
        }
        if(isset( $filter['tglAkhir']) &&$filter['tglAkhir']!=""){
            $tgl= $filter['tglAkhir']." 23:59:59";
            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.tglsetortarikdeposit','<=', $tgl);
        }
        if(isset( $filter['namaPerkiraan']) &&$filter['namaPerkiraan']!="" && $filter['namaPerkiraan']!="undefined"){
            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.namaperkiraan', $filter['namaPerkiraan']);
        }
        if(isset( $filter['keterangan']) &&$filter['keterangan']!="" && $filter['keterangan']!="undefined"){
            $dataPenerimaanBank = $dataPenerimaanBank->where('psetor.namalengkap','ilike','%'. $filter['keterangan'].'%');
        }
        if(isset( $filter['nohitori']) &&$filter['nohitori']!="" && $filter['nohitori']!="undefined"){
            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.nohitori', $filter['nohitori']);
        }
//        $dataPenerimaanBank = $dataPenerimaanBank->distinct();
        $dataPenerimaanBank = $dataPenerimaanBank->get();



        /** @Function_ Ambil Saldo Satu Bulan Sebelum */
        $explode =  explode("-", $filter['tglAwal']);
        $arr1 = $explode[0];
        $arr2 = $explode[1];
        if ($arr2 == 1){
            $arr2 = $explode[1];
        }else{
            $arr2 = $explode[1] - 1 ; //bulan kurangi 1
        }
        $arr3 = '01'; //ambil tgl 1
        $arr2 = str_pad($arr2, 2, '0', STR_PAD_LEFT);
//        $arrayExplode = array($arr1,$arr2,$arr3);
//        $tglMinSabulan = implode("-", $arrayExplode);
        $tglMinSabulan = $arr1 . '-' . $arr2 . '-' . $arr3;
        /** @End_Function */

        $tglAwal= $filter['tglAwal'];
        $dataSaldo= DB::select(DB::raw(" select sh.tglsetortarikdeposit,
                     coalesce((spp.totaldibayar ), 0) debit ,
                     coalesce((sbk.totaldibayar ), 0) kredit           
                     from strukhistori_t as sh
                     inner join strukclosing_t as sc on sc.norec = sh.noclosing 
                     left join strukbuktipenerimaan_t as spp on spp.noclosingfk = sc.norec
                     left join strukbuktipengeluaran_t as sbk on sbk.noclosingfk = sc.norec
                     inner join kelompoktransaksi_m as kt on kt.id = sc.objectkelompoktransaksifk
                     inner join mapbkutokelompoktransaksi_m as mbk on mbk.kelompoktransaksifk = kt.id
                     where sh.kdprofile = $idProfile and sh.tglsetortarikdeposit > '$tglMinSabulan' and sh.tglsetortarikdeposit < '$tglAwal'
                       and sh.statusenabled=true
                       and sh.objectkelompoktransaksifk in ($KdTransak)
                     order by sh.nonhistori asc"));
        $saldolama=0;
        $jmlSaldoLama=0;
        $saldo=0;
        if (count($dataSaldo) > 0) {
            foreach ($dataSaldo as $dataSaldoLama) {
                if ($dataSaldoLama->debit == 0) {
                    $saldolama = $saldolama + (float)$dataSaldoLama->debit - (float)$dataSaldoLama->kredit;
                } else {
                    $saldolama = $saldolama + (float)$dataSaldoLama->debit;
                }
                $jmlSaldoLama = $saldolama;
            }
            $saldo=$jmlSaldoLama;
        }

        $result = array();
        $jumlahD = 0;
        $jumlahK = 0;
        $saldoAkhir = 0;

        foreach ($dataPenerimaanBank as $dataPenerimaan){
            /** @Function_ Non TUNAI Ambil */
            $norec = $dataPenerimaan->norec_sc;
            $dataPenerimaan->nontunai = 0;
            $SBMC = DB::select(DB::raw("
                 select  spc.noclosingfk,spc.carabayarfk,cb.carabayar,
                 coalesce((spc.totaldibayar ), 0) totaldibayar
                 from strukclosingkasir_t as spc 
                 join carabayar_m as cb on cb.id = spc.carabayarfk
                 where spc.kdprofile = $idProfile and spc.noclosingfk ='$norec'
            "));

            if(count($SBMC) > 0){
                $tunai = 0;
                $nonTunai = 0;
                foreach ($SBMC as $itemSbmc){
                    if($itemSbmc->carabayarfk == "1") {//TUNAI
                        $tunai = $tunai + (float) $itemSbmc->totaldibayar;
                    }else{
                        $nonTunai =$nonTunai +  (float)$itemSbmc->totaldibayar ;
                    }
                }

                $dataPenerimaan->debit = $tunai;
                $dataPenerimaan->nontunai =$nonTunai;
            }
            /** @End_Function */
            if ($dataPenerimaan->debit == 0) {
                $saldo = $saldo + (float)$dataPenerimaan->debit - (float)$dataPenerimaan->kredit;
            } else {
                $saldo = $saldo + (float)$dataPenerimaan->debit;
            }
            $jumlahD = $jumlahD + (float)$dataPenerimaan->debit;
            $jumlahK = $jumlahK +  (float)$dataPenerimaan->kredit;
            $saldoAkhir = $saldo;
//            if ($dataPenerimaan->nostrukfk != null){
//                $status = 'Sudah Di Kompensasi';
//            }else{
//                $status = '-';
//            }
            $result[] = array(
                'norec_sh'  =>$dataPenerimaan->norec_sh,
                'noStruk'  =>$dataPenerimaan->norec,
                'tglStruk'  =>$dataPenerimaan->tglsetortarikdeposit,
                'keterangan'  =>$dataPenerimaan->ketlainya,
                'jenisTransaksi'  =>$dataPenerimaan->kelompoktransaksi,
                'idJenisTransaksi'  =>$dataPenerimaan->objectkelompoktransaksifk,
                'kredit'  =>(float) $dataPenerimaan->kredit,
                'debit'  => (float)$dataPenerimaan->debit,
                'saldo'  => $saldo,
                'nontunai'  => $dataPenerimaan->nontunai,
//                'idPegawai'  =>@$login_user->objectpegawaifk,
//                'namaPegawai' => @$login_user->pegawai->namalengkap,
//                'status' => $status,
//                'idPegawai'  =>@$dataPenerimaan->objectpegawaipenerimafk,
//                'namaPegawai' => @$dataPenerimaan->namalengkap,
//                'nostrukfk' => $dataPenerimaan->nostrukfk,
                // 'objectbankaccountfk' => $dataPenerimaan->objectbankaccountfk,
                // 'bankaccountnama' => $dataPenerimaan->bankaccountnama,
                // 'namabankprovider' => $dataPenerimaan->namabankprovider,
                // 'namapemilik' => $dataPenerimaan->namapemilik,
                'nohistori' => $dataPenerimaan->nonhistori,
//                'noclosing' => $dataPenerimaan->noclosing,
                'notransaksi' => $dataPenerimaan->notransaksi,
//                'kdmataanggaran' =>$dataPenerimaan->kdchildkeempat,
//                'mataanggaran' =>$dataPenerimaan->mataanggaran,
                'nobukti' =>$dataPenerimaan->nobukti,
                'kdperkiraan' =>$dataPenerimaan->kdperkiraan,
                'namaperkiraan' =>$dataPenerimaan->namaperkiraan,
                'kettransaksi' =>$dataPenerimaan->kettransaksi,
                'penyetor'=>$dataPenerimaan->penyetor

            );
        }
        $uhman = array(
            'data' =>  $result,
            'saldolama' =>  $jmlSaldoLama,
            'dataawal' => $dataSaldo,
            'jumlahD' => $jumlahD,
            'jumlahK' => $jumlahK,
            'saldoAkhir' => $saldoAkhir,
            'message' => "@Uhman"
        );
        return $this->respond($uhman);
    }

    public function simpanBKU(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $input = $request->all();
        $nohistori = '';
        $idRuangan=0;
        $transStatus=true;
        try {
            $dataruangan = \DB::table('maploginusertoruangan_s as mlu')
                ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'mlu.objectruanganfk')
                ->leftjoin('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                ->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk')
                ->where('mlu.kdprofile', $idProfile)
                ->where('objectloginuserfk', $input['userData']['id'])
                ->get();
            if (count($dataruangan) == 0) {
                $idRuangan = 0;
            } else {
                $idRuangan = $dataruangan[0]->id;//471
            }

            $SC = new StrukClosing();
            $SC->norec = $SC->generateNewId();
            $SC->kdprofile = $idProfile;
            $SC->noclosing = $this->generateCode(new StrukClosing, 'noclosing', 10, 'C-' . $this->getDateTime()->format('ym'), $idProfile);
            $SC->objectpegawaidiclosefk = $this->getCurrentUserID();
            $SC->totaldibayar = $input['totalSetor'];
            $SC->objectkelompoktransaksifk = $input['jenisTransaksi'];

            if ($input['penerimaan'] == true) {
                $SC->keteranganlainnya = "PENERIMAAN BKU";
            } else {
                $SC->keteranganlainnya = "PENGELUARAN BKU";
            }
            $SC->tglawal = $this->getDateTime()->format('Y-m-d H:i:s');
            $SC->tglakhir = $this->getDateTime()->format('Y-m-d H:i:s');
            $SC->tglclosing = $this->getDateTime()->format('Y-m-d H:i:s');;//$input['tglbku'];
            $SC->save();
            $norec_sc = $SC->norec;
            $noclosing_SC = $SC->noclosing;

            $SH = new StrukHistori();
            $SH->norec = $SH->generateNewId();
            $nohistori = $this->generateCode(new StrukHistori(), 'nonhistori', 14, 'BKU-' . $this->getDateTime()->format('ym'), $idProfile);
            $SH->nonhistori = $nohistori;
            $SH->kdprofile = $idProfile;
            $SH->statusenabled = true;
            $SH->totalsetortarikdeposit = $input['totalSetor'];
            $SH->tglsetortarikdeposit = $input['tglbku'];// $this->getDateTime();
            $SH->objectpegawaitarikdepositfk = $this->getCurrentUserID();
            $SH->objectpegawaiterimafk = $this->getCurrentUserID();
            $SH->objectruanganterimafk = $idRuangan;
            $SH->objectruanganfk = $idRuangan;
            $SH->objectkelompoktransaksifk = $input['jenisTransaksi'];
            $SH->noclosing = $norec_sc;//$SC->noclosing;
            $SH->ketlainya = $input['keterangan'];
            $SH->nobukti = $input['nobukti'];
            $SH->kdperkiraan = $input['kdperkiraan'];
            $SH->namaperkiraan = $input['keterangan'];
            $SH->kettransaksi = $input['keterangantransaksi'];
            $SH->objectasalprodukhasilfk = $input['sumberdana'];
            $SH->save();

            if ($input['penerimaan'] == true) {
                $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
                $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
                $strukBuktiPenerimanan->kdprofile = $idProfile;
                $strukBuktiPenerimanan->keteranganlainnya = 'Setoran';//$input['keterangan'];
                $strukBuktiPenerimanan->statusenabled = 1;
                $strukBuktiPenerimanan->objectpegawaipenerimafk = $this->getCurrentLoginID();
                $strukBuktiPenerimanan->tglsbm = $input['tglbku'];//$this->getDateTime();
                $strukBuktiPenerimanan->totaldibayar = $input['totalSetor'];
                $strukBuktiPenerimanan->objectkelompoktransaksifk = $input['jenisTransaksi'];
                $strukBuktiPenerimanan->nosbm = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-' . $this->getDateTime()->format('ym'), $idProfile);
                $strukBuktiPenerimanan->noclosingfk = $norec_sc;//$SC->norec;
                $strukBuktiPenerimanan->asalprodukfk = $input['sumberdana'];
                $strukBuktiPenerimanan->save();

                $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                $SBPCB->norec = $SBPCB->generateNewId();
                $SBPCB->kdprofile = $idProfile;
                $SBPCB->statusenabled = 1;
                $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                $SBPCB->objectcarabayarfk = $input['caraBayar'];
                if ($input['detailBank'] != 'KOSONG') {
                    $SBPCB->objectbankaccountfk = $input['detailBank']['id'];
                    $SBPCB->namabankprovider = $input['detailBank']['namaBank'];
                    $SBPCB->namapemilik = $input['detailBank']['namaKartu'];
                }
                $SBPCB->save();

            } else {

                $SBK = new StrukBuktiPengeluaran();
                $SBK->norec = $SBK->generateNewId();
                $SBK->kdprofile = $idProfile;
                $SBK->keteranganlainnya = $input['keterangan'];
                $SBK->statusenabled = 1;
                $SBK->objectpegawaipembayarfk = $this->getCurrentLoginID();
                $SBK->tglsbk = $input['tglbku'];// $this->getDateTime();
                $SBK->totaldibayar = $input['totalSetor'];
                $SBK->objectkelompoktransaksifk = $input['jenisTransaksi'];
                $SBK->nosbk = $this->generateCode(new StrukBuktiPengeluaran(), 'nosbk', 14, 'PV-' . $this->getDateTime()->format('ym'), $idProfile);
                $SBK->noclosingfk = $SC->norec;
                $SBK->asalprodukfk = $input['sumberdana'];
                $SBK->save();
            }

        $transStatus = true;
        } catch(\Exception $e){
            $transStatus = false;
            $this->transMessage = "Simpan Pembayaran Bank Gagal";
        }

        if($transStatus){
            $transMessage = "Simpan Sukses";
            $result = array(
                "status" => 201,
                "message"  => $transMessage,
                "nohistori" => $nohistori,
                "as" => 'uhman',
            );
            DB::commit();
        }else{
            $transMessage = "Simpan Gagal";
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "nohistori" => $nohistori,
                "as" => 'uhman',
            );
            DB::rollBack();
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveTempBukuKasUmum(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('lu.id',$request['userData']['id'])
            ->first();
        try {
            $idbukukasumum = $this->generateCode(new TempBukuKasUmum(),
                'idbukukasumum',14,'IB-'.$this->getDateTime()->format('ym'), $idProfile);
            foreach ($request['tempbku']['data'] as $item) {
                $newBKU = new TempBukuKasUmum();
                $newBKU->kdprofile = $idProfile;
                $newBKU->norec = $newBKU->generateNewId();;
                $newBKU->nostrukhistorifk = $item['norec_sh'];
                $newBKU->kelompoktransaksifk = $item['idJenisTransaksi'];
                $newBKU->pegawaifk = $dataPegawai->objectpegawaifk; //$item['idPegawai'];
                $newBKU->kdperkiraan = $item['kdperkiraan'];
                $newBKU->namaperkiraan = $item['namaperkiraan'];
                $newBKU->kettransaksi = $item['kettransaksi'];
                $newBKU->namapenerima = $dataPegawai->namalengkap;//$item['namaPegawai'];
//                $newBKU->namabankprovider = $item['namabankprovider'];
//                $newBKU->namapemilikrekening = $item['namapemilik'];
                $newBKU->nobukti = $item['nobukti'];
                $newBKU->nohistori = $item['nohistori'];
                $newBKU->nosbm = $item['notransaksi'];
//                $newBKU->objectbankaccountfk = $item['objectbankaccountfk'];
                $newBKU->debit = $item['debit'];
                $newBKU->kredit = $item['kredit'];
                $newBKU->saldo = $item['saldo'];
                $newBKU->tglpenerimaan = $item['tglStruk'];
                $newBKU->idbukukasumum = $idbukukasumum;
                $newBKU->saldoawal = $request['saldoawal'];
                $newBKU->saldoakhir = $request['saldoakhir'];
                $newBKU->save();
            }

            $transStatus = 'true';
            $transMessage = "Simpan Temp Buku Kas Umum";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Gagal Simpan Temp Buku Kas Umum";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $newBKU,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "data" => $newBKU,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveBeritaAcara(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try {
            $id = $this->generateCode(new TempBeritaAcaraKasBank(),
                'idtempberita',14,'KB-'.$this->getDateTime()->format('ym'),$idProfile);
            foreach ($request['tempdata'] as $item) {
                $newBKU = new TempBeritaAcaraKasBank();
                $newBKU->norec = $newBKU->generateNewId();
                $newBKU->kdprofile = $idProfile;
                $newBKU->jenis = $item['jenis'];
                $newBKU->penerimaan = $item['penerimaan'];
                $newBKU->pengeluaran = $item['pengeluaran'];
                $newBKU->jumlah = $item['jumlah'];
                $newBKU->saldoawal = $item['saldoawal'];
                $newBKU->saldoakhir = $item['saldoakhir'];
                $newBKU->idtempberita = $id;
                $newBKU->save();
            }
            $transStatus = 'true';
            $transMessage = "Simpan";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $newBKU,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "data" => $newBKU,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result);
    }

    public function getTrialBalanceBendahara(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdListAKun = $this->settingDataFixed('KdAkunBendPenerimaan',$idProfile);
        $req=$request->all();
        $result = [];
        $aingMacan = \DB::select(DB::raw("select coa.id,coa.noaccount,coa.namaaccount,sum(pjd.hargasatuand) as debet,
                sum(pjd.hargasatuank) as kredit
                from chartofaccount_m as coa
                left JOIN postingjurnald_t as pjd on coa.id=pjd.objectaccountfk
                left JOIN postingjurnal_t as pj on pjd.norecrelated=pj.norec
                where coa.kdprofile = $idProfile and tglbuktitransaksi between :tglAwal and :tglAkhir and coa.statusenabled = true
                and pjd.objectaccountfk in ($kdListAKun) 
                group by coa.id,coa.noaccount,coa.namaaccount
                order by coa.noaccount;
            "),
            array(
                'tglAwal' => $request['tglAwal'] ,
                'tglAkhir' => $request['tglAkhir'] ,
            )
        );
//        return $this->respond($aingMacan);
        $mydate = $request['tglAwal'];
        $daystosum = '1';

        $datesum = date('d-m-Y', strtotime($mydate.' - '.$daystosum.' months'));
        $data = date('Ym', strtotime($datesum));
        $strData = (string)$data;

        $dataCoa = DB::select(DB::raw("
                select coa.id,coa.noaccount,coa.namaaccount,psa.ym,
                case when psa.hargasatuand is null then 0 else psa.hargasatuand end as debet,
                case when psa.hargasatuank is null then 0 else psa.hargasatuank end as kredit
                from chartofaccount_m as coa
                left JOIN (select * from postingsaldoawal_t where statusenabled=1 and ym = '$strData') as psa  on psa.objectaccountfk=coa.id
                where coa.kdprofile = $idProfile and coa.namaexternal='2018-03-01' and coa.statusenabled=true
                  and coa.id in ($kdListAKun) 
            ")
        );
//        return $this->respond($dataCoa);
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

    public function saveTempLampiranBA(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try {
            $id = $this->generateCode(new TempLampiranBeritaAcaraKasBank(),
                'idtemplampiran',14,'KB-'.$this->getDateTime()->format('ym'), $idProfile);

            $item =$request['tempdata'] ;
            $newBKU = new TempLampiranBeritaAcaraKasBank();
            $newBKU->norec = $newBKU->generateNewId();
            $newBKU->kdprofile = $idProfile;
            $newBKU->idtemplampiran = $id;
            $newBKU->pecahan100k = $item['pecahan100k'];
            $newBKU->pecahan50k = $item['pecahan50k'];
            $newBKU->pecahan20k = $item['pecahan20k'];
            $newBKU->pecahan10k = $item['pecahan10k'];
            $newBKU->pecahan5k = $item['pecahan5k'];
            $newBKU->pecahan2k = $item['pecahan2k'];
            $newBKU->pecahan1k = $item['pecahan1k'];
            $newBKU->pecahan500 = $item['pecahan500'];
            $newBKU->pecahan200 = $item['pecahan200'];
            $newBKU->pecahan100 = $item['pecahan100'];
            $newBKU->bribank = $item['bribank'];
            $newBKU->depositobank = $item['depositobank'];
            $newBKU->terbilangbank = $item['terbilangbank'];
            $newBKU->save();

            $transStatus = 'true';
            $transMessage = "Simpan";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $newBKU,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "data" => $newBKU,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result);
    }

    public function hapusBKU(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try{

            if($request['norec_struk_histori'] != '') {
                $sH = StrukHistori::where('norec', $request['norec_struk_histori'])->where('kdprofile',$idProfile)->first();

                $strukClosing = StrukClosing::where('noclosing',$sH->noclosing)->where('kdprofile',$idProfile)->first();
                $sbm = StrukBuktiPenerimaan::where('noclosingfk',$strukClosing['norec'])
                    ->where('kdprofile',$idProfile)
                    ->where('keteranganlainnya','<>','Setoran Kasir')
                    ->where('statusenabled',true)
                    ->update([ 'noclosingfk' => null ]);
                $disableSH = StrukHistori::where('norec', $request['norec_struk_histori'])
                    ->where('kdprofile',$idProfile)
                    ->update([
//                        'statusenabled' => false,
                          'statusenabled' => 0,
                    ]
                );

            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "update status enabled";
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataRekapitulasiPendapatanDaerah(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];

        $data = \DB::select(DB::raw("
            SELECT x.id,x.lingkuppelayanan, sum(x.tunai) as tunai,
            sum(x.bpjs) as bpjs, sum(x.kabbogor) as kabbogor,  sum(x.kotbogor) as kotbogor,  sum(x.kotdepok) as kotdepok  
            FROM (
            SELECT 1 AS id,'Pelayanan Instalasi Rawat Jalan' AS lingkuppelayanan,
            CASE WHEN pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND ru.objectdepartemenfk = 18 AND ru.id not in (12,701,692)
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 2 AS id,'Pelayanan Instalasi Gawat Darurat' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.objectdepartemenfk = 24
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 3 AS id,'Pelayanan Instalasi Rawat Inap' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.objectdepartemenfk = 16 and ru.id not in (731,732,721,720,740,739)
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 4 AS id,'Pelayanan Instalasi Intensif' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.objectdepartemenfk = 16 and ru.id in (731,732,721,720,740,739)
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 5 AS id,'Pelayanan Instalasi Laboratorium' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.objectdepartemenfk = 3 and ru.id not in (41)
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 6 AS id,'Pelayanan Instalasi Radiologi' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.objectdepartemenfk = 27
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 7 AS id,'Pelayanan Instalasi Rehabilitas Medik' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.objectdepartemenfk in (18,28) AND ru.id in (701,692,577)
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 8 AS id,'Pelayanan Instalasi Bedah Sentral' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.objectdepartemenfk = 25
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 10 AS id,'Pelayanan Hemodialisa' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.id = 12
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT lp.id,lp.lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            LEFT JOIN maplaporankeuangantolingkuppelayanan_m AS mlk ON mlk.produkfk=pp.produkfk
            LEFT JOIN lingkuppelayanan_m AS lp ON lp.id = mlk.lingkuppelayananfk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   lp.id = 11
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT lp.id,lp.lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            LEFT JOIN maplaporankeuangantolingkuppelayanan_m AS mlk ON mlk.produkfk=pp.produkfk
            LEFT JOIN lingkuppelayanan_m AS lp ON lp.id = mlk.lingkuppelayananfk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   lp.id = 12
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 13 AS id,'Pelayanan Bank Darah' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
            AND   ru.objectdepartemenfk = 3 and ru.id in (41)
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            AND pp.statusenabled = true
            
            UNION ALL
            
            SELECT 14 AS id,'Pelayanan Farmasi' AS lingkuppelayanan,
            CASE WHEN   pt.objectkelompokpasienlastfk = 1 THEN pp.hargajual ELSE 0 END AS tunai,
            CASE WHEN   pt.objectkelompokpasienlastfk = 2 THEN pp.hargajual ELSE 0 END AS bpjs,
            CASE WHEN   pt.objectrekananfk = 581159 THEN pp.hargajual ELSE 0 END AS kabbogor,
            CASE WHEN   pt.objectrekananfk = 11817 THEN pp.hargajual ELSE 0 END AS kotbogor,
            CASE WHEN   pt.objectrekananfk = 581160 THEN pp.hargajual ELSE 0 END AS kotdepok
            FROM antrianpasiendiperiksa_t AS apd
            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
            inner join pasiendaftar_t pt on pt.norec = apd.noregistrasifk
            WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = true
            AND pp.strukresepfk IS NOT NULL
            AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
            
            UNION ALL
            
            SELECT id,lingkuppelayanan,0 AS tunai,0 AS bpjs,0 AS kabbogor,0 AS kotbogor,0 AS kotdepok FROM lingkuppelayanan_m WHERE statusenabled = true
            ) AS x
            WHERE x.lingkuppelayanan IS NOT NULL
            GROUP BY x.id,x.lingkuppelayanan
            ORDER BY x.id  
        "));

        return $this->respond($data);
    }

    public function getDataRekapitulasiPendapatanDaerahTahunan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];

        $data = \DB::select(DB::raw("
                    SELECT x.id,x.lingkuppelayanan,SUM(x.januari) AS januari,SUM(x.febuari) AS febuari,
                    SUM(x.maret) AS maret,SUM(x.april) AS april,SUM(x.mei) AS mei,SUM(x.juni) AS juni,
                    SUM(x.juli) AS juli,SUM(x.agustus) AS agustus,SUM(x.september) AS september,
                    SUM(x.oktober) AS oktober,SUM(x.november) AS november,SUM(x.desember) AS desember,
                    SUM(x.januari)+SUM(x.febuari)+SUM(x.maret)+SUM(x.april)+SUM(x.mei)+SUM(x.juni)+
                    SUM(x.juli)+SUM(x.agustus)+SUM(x.september)+SUM(x.oktober)+SUM(x.november)+SUM(x.desember) AS total  
                    FROM(SELECT 1 AS id,'Pelayanan Instalasi Rawat Jalan' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND ru.objectdepartemenfk = 18 AND ru.id not in (12,701,692)
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 2 AS id,'Pelayanan Instalasi Gawat Darurat' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND ru.objectdepartemenfk = 24
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 3 AS id,'Pelayanan Instalasi Rawat Inap' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND ru.objectdepartemenfk = 16 and ru.id not in (731,732,721,720,740,739) 
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 4 AS id,'Pelayanan Instalasi Intensif' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND ru.objectdepartemenfk = 16 and ru.id in (731,732,721,720,740,739) 
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 5 AS id,'Pelayanan Instalasi Laboratorium' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND ru.objectdepartemenfk = 3 and ru.id not in (41)
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 6 AS id,'Pelayanan Instalasi Radiologi' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL 
                    AND ru.objectdepartemenfk = 27
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    
                    UNION ALL
                    
                    SELECT 7 AS id,'Pelayanan Instalasi Rehabilitas Medik' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND ru.objectdepartemenfk in (18,28) AND ru.id in (701,692,577)
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 8 AS id,'Pelayanan Instalasi Bedah Sentral' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND ru.objectdepartemenfk = 25
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 10 AS id,'Pelayanan Hemodialisa' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL 
                    AND ru.id = 12
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT lp.id,lp.lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    LEFT JOIN maplaporankeuangantolingkuppelayanan_m AS mlk ON mlk.produkfk=pp.produkfk
                    LEFT JOIN lingkuppelayanan_m AS lp ON lp.id = mlk.lingkuppelayananfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND lp.id = 11
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT lp.id,lp.lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    LEFT JOIN maplaporankeuangantolingkuppelayanan_m AS mlk ON mlk.produkfk=pp.produkfk
                    LEFT JOIN lingkuppelayanan_m AS lp ON lp.id = mlk.lingkuppelayananfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND lp.id = 12
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 13 AS id,'Pelayanan Bank Darah' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true and pp.strukresepfk IS NULL
                    AND ru.objectdepartemenfk = 3 and ru.id in (41)
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL
                    
                    SELECT 14 AS id,'Pelayanan Farmasi' AS lingkuppelayanan,
                    CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 1 THEN
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                    0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 2 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 3 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 4 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 5 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 6 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 7 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 8 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 9 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 10 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 11 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from pp.tglpelayanan) = 12 THEN
                     (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) ELSE 0 END AS desember
                    FROM antrianpasiendiperiksa_t AS apd
                    INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec 
                    LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = true 
                    AND pp.strukresepfk IS NOT NULL
                    AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                    
                    UNION ALL 
                    
                    SELECT 14 AS id,'Pelayanan Farmasi' AS lingkuppelayanan,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 1 THEN
                     spd.totalharusdibayar ELSE 0 END AS januari,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 2 THEN
                     spd.totalharusdibayar ELSE 0 END AS febuari,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 3 THEN
                     spd.totalharusdibayar ELSE 0 END AS maret,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 4 THEN
                     spd.totalharusdibayar ELSE 0 END AS april,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 5 THEN
                     spd.totalharusdibayar ELSE 0 END AS mei,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 6 THEN
                     spd.totalharusdibayar ELSE 0 END AS juni,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 7 THEN
                     spd.totalharusdibayar ELSE 0 END AS juli,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 8 THEN
                     spd.totalharusdibayar ELSE 0 END AS agustus,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 9 THEN
                     spd.totalharusdibayar ELSE 0 END AS september,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 10 THEN
                     spd.totalharusdibayar ELSE 0 END AS oktober,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 11 THEN
                     spd.totalharusdibayar ELSE 0 END AS november,
                     CASE WHEN EXTRACT(MONTH from spd.tglstruk) = 12 THEN
                     spd.totalharusdibayar ELSE 0 END AS desember
                    FROM strukpelayanan_t AS spd
                    WHERE spd.kdprofile =$kdProfile AND spd.statusenabled = true 
                    AND SUBSTRING(spd.nostruk,1,2) = 'OB'
                    AND spd.tglstruk BETWEEN '$tglAwal' AND '$tglAkhir'
                                                                                
                    UNION ALL
                    
                    SELECT id,lingkuppelayanan,0 as januari,0 as febuari,0 as maret,0 as april,
                    0 as mei,0 as juni,0 as juli,0 as agustus,0 as september,0 as oktober,0 as november,0 as desember
                    FROM lingkuppelayanan_m WHERE statusenabled = true
                    ) AS x
                    WHERE x.lingkuppelayanan IS NOT NULL
                    GROUP BY x.id,x.lingkuppelayanan
                    ORDER BY x.id 
        "));

        return $this->respond($data);
    }

    public function getDataPendapatanBP(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("
                SELECT x.namaruangan,SUM(x.jumlah) AS jumlah,SUM(x.totalp) AS totalp,SUM(x.totalt) AS totalt
                FROM( SELECT ru.namaruangan,pp.jumlah,(((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalp,
                             0 AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (18,28,24)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,pp.jumlah,0 AS totalp,
                             (((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (18,28,24)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT ru.namaruangan,pp.jumlah,(((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalp,
                             0 AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (3)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,pp.jumlah,0 AS totalp,
                             (((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (3)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT ru.namaruangan,pp.jumlah,(((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalp,
                             0 AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (27)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,pp.jumlah,0 AS totalp,
                             (((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (27)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT ru.namaruangan,pp.jumlah,(((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalp,
                             0 AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (31)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,pp.jumlah,0 AS totalp,
                             (((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (31)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT ru.namaruangan,pp.jumlah,(((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalp,
                             0 AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (5)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,pp.jumlah,0 AS totalp,
                             (((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                             0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS totalt
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile = $kdProfile AND apd.statusenabled = TRUE
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (5)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT namaruangan,0 AS jumlah,0 AS totalp,0 AS totalt 
                FROM ruangan_m WHERE statusenabled=true AND kdprofile=21 
                AND objectdepartemenfk IN (18,28,24,3,27,31,5)) AS x
                GROUP BY x.namaruangan
        "));

        $dataDetail = \DB::select(DB::raw("
                SELECT x.namaruangan,SUM(x.jaspelp) AS jaspelp,SUM(x.jassarp) AS jassarp,
                SUM(x.jaspelt) AS jaspelt,SUM(x.jassart) AS jassart
                FROM( 
                SELECT ru.namaruangan,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelp,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassarp,
                0 AS jaspelt,0 AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (18,28,24)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,
                0 AS jaspelp,0 AS jassarp,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelt,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (18,28,24)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT ru.namaruangan,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelp,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassarp,
                0 AS jaspelt,0 AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (3)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,
                0 AS jaspelp,0 AS jassarp,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelt,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (3)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT ru.namaruangan,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelp,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassarp,
                0 AS jaspelt,0 AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (27)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,
                0 AS jaspelp,0 AS jassarp,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelt,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (27)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT ru.namaruangan,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelp,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassarp,
                0 AS jaspelt,0 AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (31)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,
                0 AS jaspelp,0 AS jassarp,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelt,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (31)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT ru.namaruangan,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelp,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassarp,
                0 AS jaspelt,0 AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (5)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL 
                
                SELECT ru.namaruangan,
                0 AS jaspelp,0 AS jassarp,
                CASE WHEN ppd.komponenhargafk = 94 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jaspelt,
                CASE WHEN ppd.komponenhargafk = 93 THEN pp.jumlah*ppd.hargajual ELSE 0 END AS jassart
                FROM antrianpasiendiperiksa_t AS apd
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasiendetail_t AS ppd ON ppd.pelayananpasien = pp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                WHERE apd.kdprofile =$kdProfile AND apd.statusenabled = TRUE
                AND ppd.statusenabled = true
                AND pp.produkfk not in (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33740,403531)
                AND pp.strukresepfk IS NULL AND ru.objectdepartemenfk in (5)
                AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                
                UNION ALL
                
                SELECT namaruangan,0 AS jaspelp,0 AS jassarp,0 AS jaspelt,0 AS jassart
                FROM ruangan_m WHERE statusenabled=true AND kdprofile=21 
                AND objectdepartemenfk IN (18,28,24,3,27,31,5) ) AS x
                GROUP BY x.namaruangan
        "));

        $i=0;
        foreach ($data as $items){
            foreach ($dataDetail as $dD) {
//                $data[$i]->jaspelp = 0;
//                $data[$i]->jassarp = 0;
//                $data[$i]->jaspelt = 0;
//                $data[$i]->jassart = 0;
                if ($data[$i]->namaruangan == $dD->namaruangan){
                    $data[$i]->jaspelp = $dD->jaspelp;
                    $data[$i]->jassarp = $dD->jassarp;
                    $data[$i]->jaspelt = $dD->jaspelt;
                    $data[$i]->jassart = $dD->jassart;
                }
            }
            $i = $i + 1;
        }

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
}