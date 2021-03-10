<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 11/18/2019
 * Time: 2:24 PM
 */

namespace App\Http\Controllers\Laundry;
use App\Http\Controllers\ApiController;
use App\Traits\Valet;
use App\Transaksi\KartuStok;
use App\Transaksi\KirimProduk;
use App\Transaksi\LoggingUser;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\PencucianLinen;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\StrukKirim;
use App\Transaksi\StrukOrder;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\StrukPelayananDetail;
use DB;
use Illuminate\Http\Request;

class LaundryController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getComboLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataSumberDana = \DB::table('asalproduk_m as lu')
            ->select('lu.id','lu.asalproduk as asalProduk')
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.statusenabled', true)
            ->get();

        $dataRuanganAll = \DB::table('ruangan_m as lu')
            ->select('lu.id','lu.namaruangan')
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.statusenabled',true)
            ->get();

        $ruanganLaundry = \DB::table('ruangan_m as lu')
            ->select('lu.id','lu.namaruangan')
            ->where('lu.statusenabled',true)
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.objectdepartemenfk',$this->settingDataFixed('KdDepartemenLaundry', $idProfile))
            ->get();

        $dataJenisProduk = \DB::table('jenisproduk_m as lu')
            ->select('lu.id','lu.jenisproduk as jenisProduk')
            ->where('lu.kdprofile', $idProfile)
            ->get();


        $jenisProduk =  \DB::table('jenisproduk_m as jp')
            ->select('jp.id','jp.jenisproduk')
            ->where('jp.statusenabled',true)
            ->where('jp.kdprofile', $idProfile)
            ->get();

        $detailJenis =  \DB::table('detailjenisproduk_m as djp')
            ->select('djp.id','djp.detailjenisproduk','objectjenisprodukfk')
            ->where('djp.kdprofile', $idProfile)
            ->where('djp.statusenabled',true)
            ->get();


        $dataKelompokProduk2 = \DB::table('kelompokproduk_m as jk')
            ->select('jk.id','jk.kelompokproduk')
            ->where('jk.statusenabled',true)
            ->where('jk.kdprofile', $idProfile)
            ->get();



        foreach ($jenisProduk as $item) {
            $detail = [];
            foreach ($detailJenis as $item2) {
                if ($item->id == $item2->objectjenisprodukfk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'detailjenisproduk' => $item2->detailjenisproduk,
                    );
                }
            }
            $dataJenisProduk[] = array(
                'id' => $item->id,
                'jenisproduk' => $item->jenisproduk,
                'detailjenisproduk' => $detail,
            );
        }

        $dataProduk = \DB::table('produk_m as pr')
            ->leftJOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
//            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.kdproduk as kdsirs','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.statusenabled',true)
            ->where('pr.objectdepartemenfk',$this->settingDataFixed('KdDepartemenLaundry', $idProfile))
            ->where('pr.kdprofile', $idProfile)
//            ->where('jp.id',97)
//            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','pr.kdproduk','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();
//        return $dataProduk;

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.statusenabled',true)
            ->where('ks.kdprofile', $idProfile)
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
                'kdsirs' => $item->kdsirs,
                'kdproduk' => $item->kdsirs,
                'ssid' =>   $item->ssid,
                'satuanstandar' =>   $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
            );
        }


        $dataSatuan = \DB::table('satuanstandar_m as ss')
            ->where('ss.statusenabled', true)
            ->where('ss.kdprofile', $idProfile)
            ->orderBy('ss.satuanstandar')
            ->get();

        $dataJenisProduk = \DB::table('jenisproduk_m as jp')
            ->where('jp.statusenabled', true)
            ->where('jp.kdprofile', $idProfile)
            ->orderBy('jp.jenisproduk')
            ->get();

        $result = array(
            'jenisbarang' => $dataJenisProduk,
            'asalproduk' => $dataSumberDana,
            'ruanganall' => $dataRuanganAll,
            'kelompokproduk' =>$dataKelompokProduk2,
            'produk' => $dataProdukResult,
            'satuan' => $dataSatuan,
            'ruanganlaundry' => $ruanganLaundry,
            'by' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function saveKirimLinen(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        if ($request['strukkirim']['jenispermintaanfk'] == 2) {
//            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 14, 'TRF-' . $this->getDateTime()->format('ym'));
//        }else{
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 14, 'TRL-' . $this->getDateTime()->format('ym'),$idProfile);
//        }
        if ($noKirim == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            \DB::rollBack();
            $result = array(
                "status" => 400,
                "NOKIRIM" => $noKirim,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }
        \DB::beginTransaction();

        try{
            if ($request['strukkirim']['noreckirim'] == ''){
                if ($request['strukkirim']['norecOrder'] != ''){
                    $dataAing = StrukOrder::where('norec',$request['strukkirim']['norecOrder'])
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'statusorder' => 1]
                        );
                }
                $dataSK = new StrukKirim;
                $dataSK->norec = $dataSK->generateNewId();
                $dataSK->nokirim = $noKirim;
            }else{

                $dataSK = StrukKirim::where('norec',$request['strukkirim']['noreckirim'])->where('kdprofile', $idProfile)->first();

                KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])->where('kdprofile', $idProfile)->delete();
            }

            $dataSK->kdprofile = $idProfile;
            $dataSK->statusenabled = true;
            $dataSK->objectpegawaipengirimfk = $request['strukkirim']['objectpegawaipengirimfk'];
            $dataSK->objectruanganasalfk = $request['strukkirim']['objectruanganfk'];
            $dataSK->objectruanganfk = $request['strukkirim']['objectruanganfk'];
            $dataSK->objectruangantujuanfk = $request['strukkirim']['objectruangantujuanfk'];
            $dataSK->jenispermintaanfk = $request['strukkirim']['jenispermintaanfk'];
            $dataSK->objectkelompoktransaksifk = 119;//PENRIMAAN LAUNDRY
            $dataSK->keteranganlainnyakirim =$request['strukkirim']['keteranganlainnyakirim'];
            $dataSK->qtydetailjenisproduk = 0;
            $dataSK->qtyproduk = $request['strukkirim']['qtyproduk'];
            $dataSK->tglkirim = date($request['strukkirim']['tglkirim']);
            $dataSK->totalbeamaterai = 0;
            $dataSK->totalbiayakirim = 0;
            $dataSK->totalbiayatambahan = 0;
            $dataSK->totaldiscount = 0;
            $dataSK->totalhargasatuan = 0;//$request['strukkirim']['totalhargasatuan'];
            $dataSK->totalharusdibayar = 0;
            $dataSK->totalpph =0;
            $dataSK->totalppn = 0;
//            $dataSK->noregistrasifk = $request['strukkirim']['norec_apd'];
            $dataSK->noorderfk = $request['strukkirim']['norecOrder'];
            $dataSK->save();

            $norecSK = $dataSK->norec;

            foreach ($request['details'] as $item) {

                $dataKP = new KirimProduk;
                $dataKP->norec = $dataKP->generateNewId();
                $dataKP->kdprofile = $idProfile;
                $dataKP->statusenabled = true;
//                $dataKP->objectasalprodukfk = $items->asalprodukfk;
                $dataKP->hargadiscount = 0;//$items->hargadiscount;
                $dataKP->harganetto = 0 ;// $items->harganetto;
                $dataKP->hargapph = 0;
                $dataKP->hargappn = 0;
                $dataKP->hargasatuan = 0;// $items->hargasatuan;
                $dataKP->hargatambahan = 0;
//                $dataKP->hasilkonversi = $qtyqtyqty;
                $dataKP->objectprodukfk = $item['produkfk'];
                $dataKP->objectprodukkirimfk = $item['produkfk'];
                $dataKP->nokirimfk = $norecSK;
                $dataKP->persendiscount = 0;
                $dataKP->qtyproduk = $item['jumlah'];
                $dataKP->qtyprodukkonfirmasi = $item['jumlah'];
                $dataKP->qtyprodukretur = 0;
                $dataKP->qtyorder = $item['qtyorder'];
                $dataKP->qtyprodukterima = $item['jumlah'];
//                $dataKP->nostrukterimafk = $items->nostrukterimafk;
                $dataKP->objectruanganfk = $request['strukkirim']['objectruangantujuanfk'];
                $dataKP->objectruanganpengirimfk = $request['strukkirim']['objectruanganfk'];
                $dataKP->satuan = '-';
                $dataKP->objectsatuanstandarfk = $item['satuanstandarfk'];//$item['satuanstandarfk'];
                $dataKP->satuanviewfk = $item['satuanviewfk'];
                $dataKP->tglpelayanan = date($request['strukkirim']['tglkirim']);
//                $dataKP->qtyprodukterimakonversi = $qtyqtyqty;
                $dataKP->save();

            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Penerimaan Linen";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSK,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarKirimLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
//        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
//                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
//                where lu.id=:idLoginUser"),
//            array(
//                'idLoginUser' => $request['userData']['id'],
//            )
//        );
//        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
//            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
//            ->select('ru.id')
//            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
//            ->get();
//        $strRuangan = [];
//        foreach ($dataRuangan as $epic){
//            $strRuangan[] = $epic->id;
//        }
        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('pencucianlinen_t as penc','penc.strukkirimfk','=','sp.norec')
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk',
                'ru.id as ruasalid','ru.namaruangan as ruanganasal','ru2.id as rutujuanid','ru2.namaruangan as ruangantujuan','sp.keteranganlainnyakirim','sp.statuskirim',
                'penc.tgl as tglcuci',
                DB::raw('count(kp.objectprodukfk) as jmlitem')
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim','sp.statuskirim','penc.tgl');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ilike','%'. $request['nokirim'].'%');
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru.namaruangan','ilike', '%'.$request['ruangantujuanfk'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('kp.objectprodukfk','=', $request['produkfk']);
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',$request['kelompokTransaksi']);
//        $data = $data->wherein('sp.objectruanganasalfk',$strRuangan);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select  pr.id as kdproduk,pr.kdproduk as kdsirs,pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.objectprodukfk
                     from kirimproduk_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nokirimfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $jeniskirim ='';
            if ($item->jenispermintaanfk == 1){
                $jeniskirim ='Amprahan';
            }
            if ($item->jenispermintaanfk == 2){
                $jeniskirim ='Transfer';
            }
            $sttaus ='';
            if($request['kelompokTransaksi'] == 120){
                $sttaus = 'Kirim Linen';
            }
            if($request['kelompokTransaksi'] == 119){
                $sttaus = 'Terima Linen';
            }
            $results[] = array(
                'status' => $sttaus,
                'tglstruk' => $item->tglkirim,
                'nostruk' => $item->nokirim,
                'noorderfk' => $item->noorderfk,
                'jenispermintaanfk' => $item->jenispermintaanfk,
                'jeniskirim' => $jeniskirim,
                'norec' => $item->norec,
                'ruasalid'=> $item->ruasalid,
                'namaruanganasal' => $item->ruanganasal,
                'rutujuanid'=> $item->rutujuanid,
                'namaruangantujuan' => $item->ruangantujuan,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganlainnyakirim,
                'jmlitem' => $item->jmlitem,
                'statuskirim' =>$item->statuskirim,
                'tglcuci' =>$item->tglcuci,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'as@epic',
//            'str' => $strRuangan,
        );

        return $this->respond($result);
    }
    public function getDetailKirimLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataStruk = \DB::table('strukkirim_t as sr')
            ->JOIN('pegawai_m as pg','pg.id','=','sr.objectpegawaipengirimfk')
            ->JOIN('ruangan_m as ru','ru.id','=','sr.objectruanganfk')
            ->JOIN('ruangan_m as ru2','ru2.id','=','sr.objectruangantujuanfk')
            ->select('sr.nokirim','pg.id as pgid','pg.namalengkap','ru.id','ru.namaruangan','ru2.id as ruid2','ru2.namaruangan as namaruangan2',
                'sr.jenispermintaanfk','sr.tglkirim','sr.keteranganlainnyakirim as keterangan')
            ->where('sr.kdprofile', $idProfile);
        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $dataStruk = $dataStruk->where('sr.norec','=', $request['norec']);
        }
        $dataStruk = $dataStruk->first();

        $data = \DB::table('strukkirim_t as sp')
            ->JOIN('kirimproduk_t as spd','spd.nokirimfk','=','sp.norec')
            ->JOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
//            ->JOIN('jeniskemasan_m as jk','jk.id','=','spd.objectjeniskemasanfk')
            ->JOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
            ->JOIN('satuanstandar_m as ss','ss.id','=','spd.objectsatuanstandarfk')
            ->select('sp.nokirim','spd.hargasatuan','spd.qtyprodukoutext','sp.objectruanganfk','ru.namaruangan','spd.nostrukterimafk',
                'spd.objectprodukfk as produkfk','pr.kdproduk','pr.namaproduk','spd.hasilkonversi as nilaikonversi',
                'spd.objectsatuanstandarfk','ss.satuanstandar','spd.objectsatuanstandarfk as satuanviewfk','ss.satuanstandar as ssview',
                'spd.qtyproduk as jumlah','spd.hargadiscount','spd.hargatambahan as jasa','spd.hargasatuan as hargajual','spd.harganetto','spd.qtyprodukretur')
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norec']);
        }
        $data = $data->get();
//        return $this->respond($data);

        $pelayananPasien=[];
        $i = 0;


        foreach ($data as $item){
            if ($item->jumlah > 0 ){
                $i = $i+1;

                $pelayananPasien[] = array(
                    'no' => $i,
                    'noregistrasifk' => '',
                    'tglregistrasi' => '',
                    'kelasfk' => '',
                    'ruanganfk' => $item->objectruanganfk,
                    'rke' => 0,
                    'jeniskemasanfk' => 0,
                    'jeniskemasan' => '',
                    'aturanpakai' => '',
                    'routefk' => 0,
                    'route' => '',
                    'produkfk' => $item->produkfk,
                    'kdproduk' => $item->kdproduk,
                    'namaproduk' => $item->namaproduk,
                    'nilaikonversi' => $item->nilaikonversi/$item->jumlah,
                    'satuanstandarfk' => $item->satuanviewfk,//objectsatuanstandarfk,
                    'satuanstandar' => $item->ssview,//satuanstandar,
                    'satuanviewfk' => $item->satuanviewfk,
                    'satuanview' => $item->ssview,
                    'jumlah' => $item->jumlah,
                    'qtyorder' => $item->jumlah,
                    'qtyretur' => $item->qtyprodukretur,
                    'jasa' => $item->jasa,
                );
            }

        }

        $result = array(
            'head' => $dataStruk,
            'detail' => $pelayananPasien,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function saveBatalKirim(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();

        try{

            StrukKirim::where('norec',$request['norec'])
                ->where('kdprofile', $idProfile)
                ->update([
                'statusenabled' => false,
                'keteranganlainnyakirim' =>$request['alasan']
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Batal Terima";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "nokirim" => $dataSK,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveOrderLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $KdPelayananLaundry = $this->settingDataFixed('KdPelayananLaundry',$idProfile);
        \DB::beginTransaction();
        try{
            if ($request['strukorder']['norecorder'] == '') {
//                if ($request['strukorder']['jenispermintaanfk'] != 1) {
                    $noOrder = $this->generateCode(new StrukOrder, 'noorder', 14, 'OTRL-' . $this->getDateTime()->format('ym'), $idProfile);
//                } else {
//                    $noOrder = $this->generateCode(new StrukOrder, 'noorder', 14, 'OAMP-' . $this->getDateTime()->format('ym'));
//                }
                $dataSO = new StrukOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->isdelivered = 0;
                $dataSO->noorder = $noOrder;
            }else {
                $dataSO = StrukOrder::where('norec',$request['strukorder']['norecorder'])->where('kdprofile', $idProfile)->first();
                OrderPelayanan::where('noorderfk',$request['strukorder']['norecorder'])->where('kdprofile', $idProfile)->delete();
            }
            $dataSO->jenispermintaanfk = $request['strukorder']['jenispermintaanfk'];
            $dataSO->objectkelompoktransaksifk = $KdPelayananLaundry;
            $dataSO->keteranganorder = $request['strukorder']['keteranganorder'];
            $dataSO->objectpegawaiorderfk = $request['strukorder']['pegawaiorderfk'];
            $dataSO->qtyjenisproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->qtyproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->objectruanganfk = $request['strukorder']['ruanganfk'];
            $dataSO->objectruangantujuanfk = $request['strukorder']['ruangantujuanfk'];
            $dataSO->tglorder = $request['strukorder']['tglorder'];
            $dataSO->statusorder = 0;
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim = 0;
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = 0;
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn = 0;
            $dataSO->save();
            $dataSO = $dataSO->norec;

            foreach ($request['details'] as $item) {
                $dataOP = new OrderPelayanan();
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = $idProfile;
                $dataOP->statusenabled = true;
//                $dataOP->hasilkonversi = $item['nilaikonversi'];
                $dataOP->iscito = 0;
                $dataOP->noorderfk = $dataSO;
                $dataOP->objectprodukfk = $item['produkfk'];
                $dataOP->qtyproduk = $item['jumlah'];
                $dataOP->qtyprodukretur = 0;
                $dataOP->objectruanganfk = $request['strukorder']['ruanganfk'];
                $dataOP->objectruangantujuanfk =  $request['strukorder']['ruangantujuanfk'];
                $dataOP->objectsatuanstandarfk = $item['satuanviewfk'];
                $dataOP->strukorderfk = $dataSO;
                $dataOP->tglpelayanan = $request['strukorder']['tglorder'];
                $dataOP->save();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Order Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSO,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Order Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteOrderLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();

        try{

            StrukOrder::where('norec',$request['norecorder'])
                ->where('kdprofile', $idProfile)
                ->update([
                'statusenabled' => false,
//                'keteranganlainnyakirim' =>$request['alasan']
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Batal Order";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "nokirim" => $dataSK,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarOrderLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
//        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
//                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
//                where lu.id=:idLoginUser"),
//            array(
//                'idLoginUser' => $request['userData']['id'],
//            )
//        );
//        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
//            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
//            ->select('ru.id')
//            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
//            ->get();
//        $strRuangan = [];
//        foreach ($dataRuangan as $epic){
//            $strRuangan[] = $epic->id;
//        }
        $data = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as op','op.strukorderfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','sp.jenispermintaanfk','pg.namalengkap',
                'ru.namaruangan as ruanganasal','ru2.namaruangan as ruangantujuan','sp.keteranganorder',
                'sp.statusorder','sp.qtyjenisproduk'
            )
            ->where('sp.kdprofile',$idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglorder','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglorder','<=', $tgl);
        }
        if(isset($request['noorder']) && $request['noorder']!="" && $request['noorder']!="undefined"){
            $data = $data->where('sp.noorder','ilike','%'. $request['noorder']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru.namaruangan','ilike', '%'. $request['ruangantujuanfk'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }
        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norecOrder']);
        }

//        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',6);
//        $data = $data->wherein('sp.objectruanganfk',$strRuangan);
        $data = $data->orderBy('sp.noorder');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select  pr.id as kdproduk,pr.kdproduk as kdsirs,pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk
                     from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and strukorderfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $jeniskirim ='';
            if ($item->jenispermintaanfk == 1){
                $jeniskirim ='Amprahan';
            }
            if ($item->jenispermintaanfk == 2){
                $jeniskirim ='Transfer';
            }
            if ($item->statusorder == 0){
                $status ='';
            }else if ($item->statusorder == 1){
                $status ='Sudah Kirim';
            }else if ($item->statusorder == 2){
                $status ='Batal Kirim';
            }

            $results[] = array(
                'status' => 'Order Laundry',
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'jeniskirim' => $jeniskirim,
                'norec' => $item->norec,
                'namaruanganasal' => $item->ruanganasal,
                'namaruangantujuan' => $item->ruangantujuan,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganorder,
                'statusorder' => $status,
                'jmlitem' => $item->qtyjenisproduk,
                'details' => $details,
            );
        }
//        $data2 = \DB::table('strukorder_t as sp')
//            ->JOIN('orderpelayanan_t as op','op.strukorderfk','=','sp.norec')
//            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
//            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
//            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
//            ->select('sp.norec','sp.tglorder','sp.noorder','sp.jenispermintaanfk','pg.namalengkap',
//                'ru.namaruangan as ruanganasal','ru2.namaruangan as ruangantujuan','sp.keteranganorder',
//                'sp.statusorder','sp.qtyjenisproduk'
//            );
//
//        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
//            $data2 = $data2->where('sp.tglorder','>=', $request['tglAwal']);
//        }
//        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
//            $tgl= $request['tglAkhir'];
//            $data2 = $data2->where('sp.tglorder','<=', $tgl);
//        }
//        if(isset($request['noorder']) && $request['noorder']!="" && $request['noorder']!="undefined"){
//            $data2 = $data2->where('sp.noorder','ilike','%'. $request['noorder']);
//        }
//        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
//            $data2 = $data2->where('ru2.namaruangan','ilike', '%'.$request['ruangantujuanfk'].'%');
//        }
//        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
//            $data2 = $data2->where('op.objectprodukfk','=',$request['produkfk']);
//        }
//
////        $data2 = $data2->distinct();
//        $data2 = $data2->where('sp.statusenabled',true);
//        $data2 = $data2->where('sp.objectkelompoktransaksifk',6);
////        $data2 = $data2->wherein('sp.objectruangantujuanfk',$strRuangan);
//        $data2 = $data2->orderBy('sp.noorder');
//        $data2 = $data2->get();
//
////        $results =array();
//        foreach ($data2 as $item){
//            $details = DB::select(DB::raw("
//                     select  pr.id as kdproduk,pr.kdproduk as kdsirs,pr.namaproduk,
//                    ss.satuanstandar,spd.qtyproduk
//                     from orderpelayanan_t as spd
//                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
//                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
//                    where strukorderfk=:norec"),
//                array(
//                    'norec' => $item->norec,
//                )
//            );
//            $jeniskirim ='';
//            if ($item->jenispermintaanfk == 1){
//                $jeniskirim ='Amprahan';
//            }
//            if ($item->jenispermintaanfk == 2){
//                $jeniskirim ='Transfer';
//            }
//            if ($item->statusorder == 0){
//                $status ='';
//            }else if ($item->statusorder == 1){
//                $status ='Sudah Kirim';
//            }else if ($item->statusorder == 2){
//                $status ='Batal Kirim';
//            }
//
//            $results[] = array(
//                'status' => 'Terima Order Laundry',
//                'tglorder' => $item->tglorder,
//                'noorder' => $item->noorder,
//                'jeniskirim' => $jeniskirim,
//                'norec' => $item->norec,
//                'namaruanganasal' => $item->ruanganasal,
//                'namaruangantujuan' => $item->ruangantujuan,
//                'petugas' => $item->namalengkap,
//                'keterangan' => $item->keteranganorder,
//                'statusorder' => $status,
//                'jmlitem' => $item->qtyjenisproduk,
//                'details' => $details,
//            );
//        }

        $result = array(
            'daftar' => $results,
//            'datalogin' =>$dataPegawaiUser,
            'message' => 'as@epic',
//            'str' => $strRuangan,
        );
        return $this->respond($result);
    }
    public function getDetailOrderLaundry(Request $request) {
//        $dataLogin = $request->all();
//        $dataAsalProduk = \DB::table('asalproduk_m as ap')
//            ->select('ap.id','ap.asalproduk')
//            ->get();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataStruk = \DB::table('strukorder_t as sp')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','sp.jenispermintaanfk as jeniskirimfk','pg.namalengkap',
                'ru.namaruangan as namaruanganasal','ru2.namaruangan as namaruangantujuan','sp.keteranganorder',
                'sp.statusorder','sp.objectruangantujuanfk','sp.objectruanganfk as objectruanganasalfk',
                \DB::raw("case when sp.jenispermintaanfk = 1 then 'Amprahan' 
                            when  sp.jenispermintaanfk = 2 then 'Transfer' end as jeniskirim ")
            )
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norecOrder']);
        }

        $dataStruk = $dataStruk->where('sp.statusenabled',true);
        $dataStruk = $dataStruk->where('sp.objectkelompoktransaksifk',6);
        $dataStruk = $dataStruk->orderBy('sp.noorder');
        $dataStruk = $dataStruk->first();

        $data = \DB::table('strukorder_t as so')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->leftjoin( 'ruangan_m as ru','ru.id','=','so.objectruanganfk')
            ->leftjoin( 'ruangan_m as ru2','ru2.id','=','so.objectruangantujuanfk')
            ->join( 'produk_m as pr','pr.id','=','op.objectprodukfk')
            ->join( 'satuanstandar_m as ss','ss.id','=','op.objectsatuanstandarfk')
            ->select('so.noorder',  'op.qtyproduk','op.objectprodukfk as produkfk','pr.kdproduk','pr.namaproduk',
                'op.hasilkonversi as nilaikonversi', 'op.objectsatuanstandarfk',
                'ss.satuanstandar',  'op.objectsatuanstandarfk as satuanviewfk',
                'ss.satuanstandar as ssview',  'op.qtyproduk as jumlah',
                'ru.namaruangan as ruanganasal', 'ru2.namaruangan as ruangantujuan',
                'so.objectruanganfk','so.objectruangantujuanfk')
            ->where('so.kdprofile', $idProfile);
        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('so.norec','=', $request['norecOrder']);
        }
        $data = $data->get();
        $details = [];
        $i = 0;
//        $dataStok = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
//                    spd.harganetto2 as hargajual,spd.harganetto2 as harganetto,spd.hargadiscount,ap.asalproduk,
//                    sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk
//                    from stokprodukdetail_t as spd
//                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
//                    inner JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
//                    where  spd.objectruanganfk =:ruanganid
//                    group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
//                            spd.harganetto2,spd.hargadiscount,ap.asalproduk,
//                    spd.objectruanganfk
//                    order By sk.tglstruk"),
//            array(
//                'ruanganid' => $dataStruk->objectruangantujuanfk
//            )
//        );
//        $hargajual=0;
//        $harganetto=0;
//        $nostrukterimafk='';
//        $asalprodukfk=0;
//        $asalproduk='';
//        $jmlstok=0;
//        $hargasatuan=0;
//        $hargadiscount=0;
//        $total=0;
//        $aturanpakaifk=0;
        foreach ($data as $item){
            if ($item->jumlah > 0 ){
                $i = $i+1;
//                foreach ($dataStok as $item2){
//                    if ($item2->objectprodukfk == $item->produkfk){
//                        if ($item2->qtyproduk >= $item->jumlah*$item->nilaikonversi){
//                            $nostrukterimafk = $item2->norec;
//                            $asalprodukfk = $item2->objectasalprodukfk;
//                            $jmlstok = $item2->qtyproduk;
//                            break;
//                        }
//                    }
//                }
//                foreach ($dataAsalProduk as $item3){
//                    if ($asalprodukfk == $item3->id){
//                        $asalproduk = $item3->asalproduk;
//                    }
//                }
                $details[] = array(
                    'no' => $i,
                    'noregistrasifk' => '',
                    'tglregistrasi' => '',
                    'generik' => null,
//                    'hargajual' => $hargajual,
                    'jenisobatfk' => '',
                    'kelasfk' => '',
//                    'stock' => $jmlstok,
//                    'harganetto' => $harganetto,
//                    'nostrukterimafk' => $nostrukterimafk,
                    'ruanganfk' => $item->objectruanganfk,
                    'rke' => 0,
                    'jeniskemasanfk' => 0,
                    'jeniskemasan' => '',
//                    'aturanpakaifk' => $aturanpakaifk,
                    'aturanpakai' => '',
                    'routefk' => 0,
                    'route' => '',
//                    'asalprodukfk' => $asalprodukfk,
//                    'asalproduk' => $asalproduk,
                    'produkfk' => $item->produkfk,
                    'kdproduk' => $item->kdproduk,
                    'namaproduk' => $item->namaproduk,
//                    'nilaikonversi' => $item->nilaikonversi,///$item->jumlah,
                    'satuanstandarfk' => $item->satuanviewfk,//objectsatuanstandarfk,
                    'satuanstandar' => $item->ssview,//satuanstandar,
                    'satuanviewfk' => $item->satuanviewfk,
                    'satuanview' => $item->ssview,
//                    'jmlstok' => $jmlstok,
                    'jumlah' => $item->jumlah,
                );
            }
        }

        $result = array(
            'head' => $dataStruk,
            'detail' => $details,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getDetailOrderLaundryForKirim(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        $dataAsalProduk = \DB::table('asalproduk_m as ap')
//            ->select('ap.id','ap.asalproduk')
//            ->get();
//        $dataSigna = \DB::table('stigma as st')
//            ->select('st.id','st.name')
//            ->get();
        $dataStruk = \DB::table('strukorder_t as sr')
            ->JOIN('pegawai_m as pg','pg.id','=','sr.objectpegawaiorderfk')
            ->JOIN('ruangan_m as ru','ru.id','=','sr.objectruanganfk')
            ->JOIN('ruangan_m as ru2','ru2.id','=','sr.objectruangantujuanfk')
            ->select('sr.noorder','pg.id as pgid','pg.namalengkap','ru.id as ruidasal','ru.namaruangan as ruanganasal',
                'ru2.id as ruidtujuan','ru2.namaruangan as ruangantujuan','sr.tglorder','sr.jenispermintaanfk','sr.keteranganorder')
            ->where('sr.kdprofile', $idProfile);

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sr.norec','=', $request['norecOrder']);
        }
        $dataStruk = $dataStruk->first();
        $jenis='';
//        foreach ($dataStruk as $item){
        if ($dataStruk->jenispermintaanfk == 1){
            $jenis='Amprahan';
        }
        if ($dataStruk->jenispermintaanfk == 2){
            $jenis='Transfer';
        }
        $detail=array(
            'tglorder' => $dataStruk->tglorder,
            'noorder' => $dataStruk->noorder,
            'pgid' => $dataStruk->pgid,
            'namalengkap' => $dataStruk->namalengkap,
            'ruidasal' => $dataStruk->ruidasal,
            'ruanganasal' => $dataStruk->ruanganasal,
            'ruidtujuan' => $dataStruk->ruidtujuan,
            'ruangantujuan' => $dataStruk->ruangantujuan,
            'jenis' => $jenis,
            'jenisid' => $dataStruk->jenispermintaanfk,
            'keterangan' => $dataStruk->keteranganorder,
        );
//        }

        $data = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as spd','spd.strukorderfk','=','sp.norec')
            ->JOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
//            ->JOIN('jeniskemasan_m as jk','jk.id','=','spd.objectjeniskemasanfk')
            ->JOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
            ->JOIN('satuanstandar_m as ss','ss.id','=','spd.objectsatuanstandarfk')
            ->select('sp.noorder','sp.objectruanganfk','ru.namaruangan',
                'spd.objectprodukfk as produkfk','pr.kdproduk','pr.namaproduk','spd.hasilkonversi as nilaikonversi',
                'spd.objectsatuanstandarfk','ss.satuanstandar','spd.objectsatuanstandarfk as satuanviewfk','ss.satuanstandar as ssview',
                'spd.qtyproduk as jumlah')
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norecOrder']);
        }
        $data = $data->get();

        $details=[];
        $i = 0;
//        $dataStok = \DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
//                    sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk
//                    from stokprodukdetail_t as spd
//                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
//                    where  spd.objectruanganfk =:ruanganid
//                    group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
//                            spd.harganetto2,spd.hargadiscount,
//                    spd.objectruanganfk
//                    order By sk.tglstruk"),
//            array(
//                'ruanganid' => $dataStruk->ruidtujuan
//            )
//        );
//        $hargajual=0;
//        $harganetto=0;
//        $nostrukterimafk='';
//        $asalprodukfk=0;
//        $asalproduk='';
//        $jmlstok=0;
//        $hargasatuan=0;
//        $hargadiscount=0;
//        $total=0;
//        $aturanpakaifk=0;
        foreach ($data as $item){
            $i = $i+1;
//            foreach ($dataStok as $item2){
////                if ($item2->objectprodukfk == $item->produkfk){
////                    $jmlstok =0;
////                    if ((float)$item2->qtyproduk >= (float)$item->jumlah*(float)$item->nilaikonversi){
//////                        $hargajual = $item2->hargajual+(($item2->hargajual*25)/100);
//////                        $harganetto = $item2->harganetto+(($item2->harganetto*25)/100);
////
//////                        $hargajual = $item->hargajual;
//////                        $harganetto = $item->hargasatuan;
////
////                        $nostrukterimafk = $item2->norec;
////                        $asalprodukfk = $item2->objectasalprodukfk;
////                        $jmlstok = (float)$item2->qtyproduk/(float)$item->nilaikonversi;
////                        break;
////                    }
////                }
////            }
////            foreach ($dataAsalProduk as $item3){
////                if ($asalprodukfk == $item3->id){
////                    $asalproduk = $item3->asalproduk;
////                }
////            }

            $details[] = array(
                'no' => $i,
//                'hargajual' => $hargajual,
//                'stock' => $jmlstok,
//                'harganetto' => $harganetto,
//                'nostrukterimafk' => $nostrukterimafk,
                'ruanganfk' => $item->objectruanganfk,
//                'asalprodukfk' => $asalprodukfk,
//                'asalproduk' => $asalproduk,
                'produkfk' => $item->produkfk,
                'kdproduk' => $item->kdproduk,
                'namaproduk' => $item->namaproduk,
                'nilaikonversi' => $item->nilaikonversi,
                'satuanstandarfk' => $item->satuanviewfk,//objectsatuanstandarfk,
                'satuanstandar' => $item->ssview,//satuanstandar,
                'satuanviewfk' => $item->satuanviewfk,
                'satuanview' => $item->ssview,
//                'jmlstok' => $jmlstok,
                'jumlah' => $item->jumlah,
                'qtyorder' => $item->jumlah,
//                'hargasatuan' => $hargasatuan,
//                'hargadiscount' => $hargadiscount,
//                'total' => $total ,//+$item->jasa,
            );
        }

        $result = array(
            'detail' => $detail,
            'details' => $details,
            'data' => $data,
//            'data2' => $dataStok,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function saveTerimaLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        if($request['strukkirim']['jenis'] =='terima'){
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 13, 'RL-' . $this->getDateTime()->format('ym'), $idProfile);
        }
        if($request['strukkirim']['jenis'] =='kirim'){
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 13, 'SL-' . $this->getDateTime()->format('ym'), $idProfile);
        }

        if ($noKirim == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            \DB::rollBack();
            $result = array(
                "status" => 400,
                "NOKIRIM" => $noKirim,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }
        DB::beginTransaction();
        $ruanganAsal = DB::select(DB::raw("
                     select ru.namaruangan
                     from ruangan_m as ru 
                    where ru.kdprofile = $idProfile and ru.id=:id"),
            array(
                'id' => $request['strukkirim']['objectruanganfk'],
            )
        );
        $strRuanganAsal ='';
        $strRuanganAsal = $ruanganAsal[0]->namaruangan;

        $ruanganTujuan = DB::select(DB::raw("
                     select  ru.namaruangan
                     from ruangan_m as ru 
                    where ru.kdprofile = $idProfile and ru.id=:id"),
            array(
                'id' => $request['strukkirim']['objectruangantujuanfk'],
            )
        );
        $strRuanganTujuan='';
        $strRuanganTujuan = $ruanganTujuan[0]->namaruangan;

        try{
            //region Save
            if ($request['strukkirim']['noreckirim'] == ''){
                if ($request['strukkirim']['norecOrder'] != ''){
                    $dataAing = StrukKirim::where('norec',$request['strukkirim']['norecOrder'])
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'statuskirim' => 'Kirim',
                                'nokirim_intern' => $request['strukkirim']['norecOrder']
                            ]
                        );
                }

                $dataSK = new StrukKirim;
                $dataSK->norec = $dataSK->generateNewId();
                $dataSK->nokirim = $noKirim;
            }else{
                //1
                $ruanganStrukKirimSebelumnya = DB::select(DB::raw("
                         select  ru.id, ru.namaruangan
                         from ruangan_m as ru 
                         where ru.kdprofile = $idProfile and ru.id=(select objectruangantujuanfk from strukkirim_t where norec = :norec)"),
                    array(
                        'norec' => $request['strukkirim']['noreckirim'],
                    )
                );
                $strNmRuanganStrukKirimSebelumnya='';
                $strIdRuanganStrukKirimSebelumnya='';
                $strNmRuanganStrukKirimSebelumnya = $ruanganStrukKirimSebelumnya[0]->namaruangan;
                $strIdRuanganStrukKirimSebelumnya = $ruanganStrukKirimSebelumnya[0]->id;
                //#1

                $dataSK = StrukKirim::where('norec',$request['strukkirim']['noreckirim'])->where('kdprofile', $idProfile)->first();
                $getDetails = KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])
                    ->where('kdprofile', $idProfile)
                    ->where('qtyproduk','>',0)
                    ->get();

                foreach ($getDetails as $item){
                    //PENGIRIM
                    $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                        array(
                            'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                            'produkfk' => $item->objectprodukfk,
                        )
                    );
                    $saldoAwalPengirim = 0;
                    foreach ($dataSaldoAwalK as $items) {
                        $saldoAwalPengirim = (float)$items->qty;
                    }
                    $tambah = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
                        ->where('kdprofile', $idProfile)
                        ->where('objectruanganfk',$request['strukkirim']['objectruanganfk'])
                        ->where('objectprodukfk',$item->objectprodukfk)
                        ->first();
                    StokProdukDetail::where('norec', $tambah->norec)
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'qtyproduk' => (float)$tambah->qtyproduk + (float)$item->qtyproduk]
                        );

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = (float)$item->qtyproduk;
                    $newKS->keterangan = 'Ubah Kirim Laundry, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strNmRuanganStrukKirimSebelumnya . ' No Kirim: ' .  $dataSK->nokirim;
                    $newKS->produkfk = $item->objectprodukfk;
                    $newKS->ruanganfk = $request['strukkirim']['objectruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalPengirim + (float)$item->qtyproduk;
                    $newKS->status = 1;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $item->nostrukterimafk;
                    $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                    $newKS->tabletransaksi = 'strukkirim_t';
                    $newKS->save();

//                    if ($request['strukkirim']['jenispermintaanfk'] == 2) {
                        //PENERIMA

                        $dataSaldoAwalT = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                            array(
                                'ruanganfk' => $strIdRuanganStrukKirimSebelumnya,//$request['strukkirim']['objectruangantujuanfk'],
                                'produkfk' => $item->objectprodukfk,
                            )
                        );
                        $saldoAwalPenerima = 0;
                        foreach ($dataSaldoAwalT as $items) {
                            $saldoAwalPenerima = (float)$items->qty;
                        }

                        $kurang = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
                            ->where('kdprofile', $idProfile)
                            ->where('objectruanganfk', $strIdRuanganStrukKirimSebelumnya)
                            ->where('objectprodukfk', $item->objectprodukfk)
//                              ->where('qtyproduk','>',0)
                            ->first();
                        StokProdukDetail::where('norec', $kurang->norec)
                            ->where('kdprofile', $idProfile)
                            ->update([
                                    'qtyproduk' => (float)$kurang->qtyproduk - (float)$item->qtyproduk]
                            );
//                            return $this->respond((float)$saldoAwalPenerima);
                        //## KartuStok
                        $newKS = new KartuStok();
                        $norecKS = $newKS->generateNewId();
                        $newKS->norec = $norecKS;
                        $newKS->kdprofile = $idProfile;
                        $newKS->statusenabled = true;
                        $newKS->jumlah = (float)$item->qtyproduk;
                        $newKS->keterangan = 'Ubah Terima Laundry, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strNmRuanganStrukKirimSebelumnya . ' No Terima: ' .  $dataSK->nokirim;
                        $newKS->produkfk = $item->objectprodukfk;
                        $newKS->ruanganfk = $strIdRuanganStrukKirimSebelumnya;//$request['strukkirim']['objectruangantujuanfk'];
                        $newKS->saldoawal = (float)$saldoAwalPenerima - (float)$item->qtyproduk;
                        $newKS->status = 0;
                        $newKS->tglinput = date('Y-m-d H:i:s');
                        $newKS->tglkejadian = date('Y-m-d H:i:s');
                        $newKS->nostrukterimafk =  $item->nostrukterimafk;
                        $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                        $newKS->tabletransaksi = 'strukkirim_t';
                        $newKS->save();


                }
                KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])->delete();
            }

            $dataSK->kdprofile = $idProfile;
            $dataSK->statusenabled = true;
            $dataSK->objectpegawaipengirimfk = $request['strukkirim']['objectpegawaipengirimfk'];
            $dataSK->objectruanganasalfk = $request['strukkirim']['objectruanganfk'];
            $dataSK->objectruanganfk = $request['strukkirim']['objectruanganfk'];
            $dataSK->objectruangantujuanfk = $request['strukkirim']['objectruangantujuanfk'];
//            $dataSK->jenispermintaanfk = $request['strukkirim']['jenispermintaanfk'];
            $dataSK->objectkelompoktransaksifk = $request['strukkirim']['kelompoktransaksi'];
            $dataSK->keteranganlainnyakirim =$request['strukkirim']['keteranganlainnyakirim'];
            $dataSK->qtydetailjenisproduk = 0;
            $dataSK->qtyproduk = $request['strukkirim']['qtyproduk'];
            $dataSK->tglkirim = date($request['strukkirim']['tglkirim']);
            $dataSK->totalbeamaterai = 0;
            $dataSK->totalbiayakirim = 0;
            $dataSK->totalbiayatambahan = 0;
            $dataSK->totaldiscount = 0;
            $dataSK->totalhargasatuan = 0;// $request['strukkirim']['totalhargasatuan'];
            $dataSK->totalharusdibayar = 0;
            $dataSK->totalpph =0;
            $dataSK->totalppn = 0;
            $dataSK->noregistrasifk = $request['strukkirim']['norec_apd'];
            $dataSK->noorderfk = $request['strukkirim']['norecOrder'];
            $dataSK->statuskirim = $request['strukkirim']['status'];
            $dataSK->save();

            $norecSK = $dataSK->norec;

            foreach ($request['details'] as $item) {
                //cari satuan standar
                $satuanstandar = DB::select(DB::raw("
                     select  ru.objectsatuanstandarfk
                     from produk_m as ru 
                    where ru.kdprofile = $idProfile and ru.id=:id"),
                    array(
                        'id' => $item['produkfk'],
                    )
                );
                $satuanstandarfk = $satuanstandar[0]->objectsatuanstandarfk;

//                if ($request['strukkirim']['jenispermintaanfk'] == 2) {
                    //PENGIRIM
                    $dataSaldoAwalK = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec,objectasalprodukfk as asalprodukfk,
                        hargadiscount,harganetto1 as harganetto,harganetto1 as hargasatuan
                        from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk 
                        --and qtyproduk > 0
                        "),
                        array(
                            'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                            'produkfk' => $item['produkfk'],
                        )
                    );
                    //PENERIMA
                    $dataSaldoAwalT = DB::select("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk",
                        array(
                            'ruanganfk' => $request['strukkirim']['objectruangantujuanfk'],
                            'produkfk' => $item['produkfk'],
                        )
                    );
                    $saldoAwalPenerima = 0;
                    foreach ($dataSaldoAwalT as $items) {
                        $saldoAwalPenerima = (float)$items->qty;
                    }

                    $saldoAwalPengirim = 0;
                    $jumlah=(float)$item['jumlah'] * (float)$item['nilaikonversi'];
                    foreach ($dataSaldoAwalK as $items) {
                        $saldoAwalPengirim =$saldoAwalPengirim + (float)$items->qty;
                        if ((float)$items->qty <= $jumlah){
                            if ((float)$items->qty >0) {
                                $qtyqtyqty = (float)$items->qty;

                                $dataKP = new KirimProduk;
                                $dataKP->norec = $dataKP->generateNewId();
                                $dataKP->kdprofile = $idProfile;
                                $dataKP->statusenabled = true;
                                $dataKP->objectasalprodukfk = $items->asalprodukfk;
                                $dataKP->hargadiscount = 0;// $items->hargadiscount;
                                $dataKP->harganetto = 0;//$items->harganetto;
                                $dataKP->hargapph = 0;
                                $dataKP->hargappn = 0;
                                $dataKP->hargasatuan = 0;//$items->hargasatuan;
                                $dataKP->hargatambahan = 0;
                                $dataKP->hasilkonversi = $qtyqtyqty;
                                $dataKP->objectprodukfk = $item['produkfk'];
                                $dataKP->objectprodukkirimfk = $item['produkfk'];
                                $dataKP->nokirimfk = $norecSK;
                                $dataKP->persendiscount = 0;
                                $dataKP->qtyproduk = $qtyqtyqty;
                                $dataKP->qtyprodukkonfirmasi = $qtyqtyqty;
                                $dataKP->qtyprodukretur = 0;
                                $dataKP->qtyorder = $item['qtyorder'];
                                $dataKP->qtyprodukterima = $qtyqtyqty;
                                $dataKP->nostrukterimafk = $items->nostrukterimafk;
                                $dataKP->objectruanganfk = $request['strukkirim']['objectruangantujuanfk'];
                                $dataKP->objectruanganpengirimfk = $request['strukkirim']['objectruanganfk'];
                                $dataKP->satuan = '-';
                                $dataKP->objectsatuanstandarfk = $satuanstandarfk;//$item['satuanstandarfk'];
                                $dataKP->satuanviewfk = $item['satuanviewfk'];
                                $dataKP->tglpelayanan = date($request['strukkirim']['tglkirim']);
                                $dataKP->qtyprodukterimakonversi = $qtyqtyqty;
                                $dataKP->save();

                                $jumlah = $jumlah - (float)$items->qty;
                                StokProdukDetail::where('norec', $items->norec)
                                    ->where('kdprofile', $idProfile)
                                    ->update([
                                            'qtyproduk' => 0]
                                    );
                                $dataStok = StokProdukDetail::where('norec', $items->norec)
                                    ->first();

                                $dataNewSPD = new StokProdukDetail;
                                $dataNewSPD->norec = $dataNewSPD->generateNewId();
                                $dataNewSPD->kdprofile = $idProfile;
                                $dataNewSPD->statusenabled = true;
                                $dataNewSPD->objectasalprodukfk = $dataStok->objectasalprodukfk;
                                $dataNewSPD->hargadiscount =  0;//$dataStok->hargadiscount;
                                $dataNewSPD->harganetto1 = 0;// $dataStok->harganetto1;
                                $dataNewSPD->harganetto2 =  0;//$dataStok->harganetto2;
                                $dataNewSPD->persendiscount = 0;
                                $dataNewSPD->objectprodukfk = $dataStok->objectprodukfk;
                                $dataNewSPD->qtyproduk = $qtyqtyqty;
                                $dataNewSPD->qtyprodukonhand = 0;
                                $dataNewSPD->qtyprodukoutext = 0;
                                $dataNewSPD->qtyprodukoutint = 0;
                                $dataNewSPD->objectruanganfk = $request['strukkirim']['objectruangantujuanfk'];
                                $dataNewSPD->nostrukterimafk = $dataStok->nostrukterimafk;
                                $dataNewSPD->noverifikasifk = $dataStok->noverifikasifk;
                                $dataNewSPD->nobatch = $dataStok->nobatch;
                                $dataNewSPD->tglkadaluarsa = $dataStok->tglkadaluarsa;
                                $dataNewSPD->tglpelayanan = $dataStok->tglpelayanan;
                                $dataNewSPD->tglproduksi = $dataStok->tglproduksi;
                                $dataNewSPD->save();
                            }
                        }else{
                            if ((float)$items->qty >0) {
                                $dataKP = new KirimProduk;
                                $dataKP->norec = $dataKP->generateNewId();
                                $dataKP->kdprofile = $idProfile;
                                $dataKP->statusenabled = true;
                                $dataKP->objectasalprodukfk = $items->asalprodukfk;
                                $dataKP->hargadiscount =  0;//$items->hargadiscount;
                                $dataKP->harganetto =  0;//$items->harganetto;
                                $dataKP->hargapph = 0;
                                $dataKP->hargappn = 0;
                                $dataKP->hargasatuan = 0;// $items->hargasatuan;
                                $dataKP->hargatambahan = 0;
                                $dataKP->hasilkonversi = $jumlah;
                                $dataKP->objectprodukfk = $item['produkfk'];
                                $dataKP->objectprodukkirimfk = $item['produkfk'];
                                $dataKP->nokirimfk = $norecSK;
                                $dataKP->persendiscount = 0;
                                $dataKP->qtyproduk = $jumlah;
                                $dataKP->qtyprodukkonfirmasi = $jumlah;
                                $dataKP->qtyprodukretur = 0;
                                $dataKP->qtyorder = $item['qtyorder'];
                                $dataKP->qtyprodukterima = $jumlah;
                                $dataKP->nostrukterimafk = $items->nostrukterimafk;
                                $dataKP->objectruanganfk = $request['strukkirim']['objectruangantujuanfk'];
                                $dataKP->objectruanganpengirimfk = $request['strukkirim']['objectruanganfk'];
                                $dataKP->satuan = '-';
                                $dataKP->objectsatuanstandarfk = $satuanstandarfk;//$item['satuanstandarfk'];
                                $dataKP->satuanviewfk = $item['satuanviewfk'];
                                $dataKP->tglpelayanan = date($request['strukkirim']['tglkirim']);
                                $dataKP->qtyprodukterimakonversi = $jumlah;
                                $dataKP->save();

                                $saldoakhir = (float)$items->qty - $jumlah;
                                StokProdukDetail::where('norec', $items->norec)
                                    ->where('kdprofile', $idProfile)
                                    ->update([
                                            'qtyproduk' => (float)$saldoakhir]
                                    );

                                $dataStok = StokProdukDetail::where('norec', $items->norec)
                                    ->where('kdprofile', $idProfile)
                                    ->first();

                                $dataNewSPD = new StokProdukDetail;
                                $dataNewSPD->norec = $dataNewSPD->generateNewId();
                                $dataNewSPD->kdprofile = $idProfile;
                                $dataNewSPD->statusenabled = true;
                                $dataNewSPD->objectasalprodukfk = $dataStok->objectasalprodukfk;
                                $dataNewSPD->hargadiscount =  0;//$dataStok->hargadiscount;
                                $dataNewSPD->harganetto1 =  0;//$dataStok->harganetto1;
                                $dataNewSPD->harganetto2 = 0;// $dataStok->harganetto2;
                                $dataNewSPD->persendiscount = 0;
                                $dataNewSPD->objectprodukfk = $dataStok->objectprodukfk;
                                $dataNewSPD->qtyproduk = ((float)$jumlah);
                                $dataNewSPD->qtyprodukonhand = 0;
                                $dataNewSPD->qtyprodukoutext = 0;
                                $dataNewSPD->qtyprodukoutint = 0;
                                $dataNewSPD->objectruanganfk = $request['strukkirim']['objectruangantujuanfk'];
                                $dataNewSPD->nostrukterimafk = $dataStok->nostrukterimafk;
                                $dataNewSPD->noverifikasifk = $dataStok->noverifikasifk;
                                $dataNewSPD->nobatch = $dataStok->nobatch;
                                $dataNewSPD->tglkadaluarsa = $dataStok->tglkadaluarsa;
                                $dataNewSPD->tglpelayanan = $dataStok->tglpelayanan;
                                $dataNewSPD->tglproduksi = $dataStok->tglproduksi;
                                $dataNewSPD->save();


                                $jumlah = 0;
                            }
                        }
                    }


                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->keterangan = 'Kirim Laundry, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strRuanganTujuan . ' No Kirim: ' .  $dataSK->nokirim;
                    $newKS->produkfk = $item['produkfk'];
                    $newKS->ruanganfk = $request['strukkirim']['objectruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalPengirim - ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $dataStok->nostrukterimafk;
                    $newKS->norectransaksi = $norecSK;
                    $newKS->tabletransaksi = 'strukkirim_t';
                    $newKS->save();


                    //## KartuStok
                    $newKS2 = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS2->norec = $norecKS;
                    $newKS2->kdprofile = $idProfile;
                    $newKS2->statusenabled = true;
                    $newKS2->jumlah = ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS2->keterangan = 'Terima Laundry, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strRuanganTujuan . ' No Kirim: ' .  $dataSK->nokirim;
                    $newKS2->produkfk = $item['produkfk'];
                    $newKS2->ruanganfk = $request['strukkirim']['objectruangantujuanfk'];
                    $newKS2->saldoawal = (float)$saldoAwalPenerima + ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS2->status = 1;
                    $newKS2->tglinput = date('Y-m-d H:i:s');
                    $newKS2->tglkejadian = date('Y-m-d H:i:s');
                    $newKS2->nostrukterimafk = $dataStok->nostrukterimafk;
                    $newKS2->norectransaksi = $norecSK;
                    $newKS2->tabletransaksi = 'strukkirim_t';
                    $newKS2->save();
//                }

                $dataSTOKDETAIL2[] = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk   "),
                    array(
                        'ruanganfk' => $request['strukkirim']['objectruangantujuanfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $dataSTOKDETAIL[] = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $kirim = KartuStok::where('ruanganfk',$request['strukkirim']['objectruanganfk'])
                    ->where('kdprofile', $idProfile)
                    ->where('produkfk',$item['produkfk'])
                    ->get();
                $terima = KartuStok::where('ruanganfk',$request['strukkirim']['objectruangantujuanfk'])
                    ->where('kdprofile', $idProfile)
                    ->where('produkfk',$item['produkfk'])
                    ->get();
            }
//

            //endregion
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage =$request['strukkirim']['jenis']. " Linen";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSK,//$noResep,,//$noResep,
                "stokdetailPenerima" => $dataSTOKDETAIL2,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSK,//$noResep,
//                "stokdetail" => $dataSTOKDETAIL,
//                "stokdetailTujuan" => $dataSTOKDETAIL2,
                "req" => $request->all(),
//                "kartuStok" => $kirim,
//                "kartuStokTujuan" => $terima,
//                "kirimproduk" => $dataKP,
//                "saldoawalTujuan" => $dataSaldoAwalT,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function CekProdukKirimLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        foreach ($request['details'] as $item) {
            $produkfk[] = $item['produkfk'] ;
        }
        $details = \DB::table('stokprodukdetail_t as spd')
            ->join('produk_m as pr','pr.id','=','spd.objectprodukfk')
            ->select(DB::raw("pr.id as produkfk,pr.namaproduk,sum(spd.qtyproduk) as stok"))
            ->where('spd.qtyproduk','>=', 0)
            ->whereIn('pr.id',$produkfk)
            ->where('spd.kdprofile', $idProfile)
            ->where('spd.objectruanganfk',$request['objectruanganfk'])
            ->groupBy('pr.id','pr.namaproduk')
            ->get();

        $result= array(
            'data' => $details,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getDaftarProdukToBatalLaundry(Request $request) {
        $dataLogin = $request->all();
        $noKirim=$request['nokirimfk'];
        $ruanganFk=$request['ruanganfk'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $i = 0;
        $str = explode(',',$request['objectprodukfk']);
        for ($i = 0; $i < count($str); $i++){
            $arr = (int)$str[$i];
            $str[$i] = $arr;
        }
        $produkfk = implode(',',$str);
        $idProduk = ' ';
        if (isset($request['objectprodukfk']) && $request['objectprodukfk'] != "" && $request['objectprodukfk'] != "undefined"  && $request['objectprodukfk'] != "0") {
            $idProduk ='  and  pr.id in ('. $produkfk .')' ;
        }

        $kelompoktransaksi = $request['kelompoktransaksi'];
        $details =  \DB::select(DB::raw("select pr.id as kdeproduk,pr.namaproduk,ss.satuanstandar,
                                  kp.nostrukterimafk,spd.tglpelayanan,SUM(spd.qtyproduk) as qtyproduk
                       from kirimproduk_t as kp
                       left join strukkirim_t as sk on sk.norec = kp.nokirimfk
                       left join produk_m as pr on pr.id=kp.objectprodukfk
                       left join stokprodukdetail_t as spd on spd.nostrukterimafk = kp.nostrukterimafk  and spd.objectprodukfk=kp.objectprodukfk
                       left join satuanstandar_m as ss on ss.id=kp.objectsatuanstandarfk
                       where kp.kdprofile = $idProfile and kp.nokirimfk='$noKirim' and sk.objectruangantujuanfk=$ruanganFk and spd.objectruanganfk=$ruanganFk
                       and sk.objectkelompoktransaksifk = $kelompoktransaksi
                            $idProduk
                       group by pr.id,pr.namaproduk,ss.satuanstandar,kp.nostrukterimafk,
                                spd.tglpelayanan,spd.qtyproduk")
        );
        return $this->respond($details);
    }
    public function BatalKirimTerimaLaundry (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {
            if($request['strukkirim']['noorderfk'] != ''){
                $dataAing = StrukOrder::where('norec',$request['strukkirim']['noorderfk'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                        'statusorder' => 2
                    ]);
            }
            $dataSK = StrukKirim::where('norec', $request['strukkirim']['noreckirim'])->first();
            if(isset($request['strukkirim']['jenis']) &&$request['strukkirim']['jenis'] =='distribusi' ){
                $dataTerima = StrukKirim::where('nokirim_intern', $request['strukkirim']['noorderfk'])->where('kdprofile', $idProfile)->first();
//                return $dataTerima;
                $dataTerima->statuskirim = '-';
                $dataTerima->nokirim_intern = null;
                $dataTerima->save();
            }

            $getDetails = KirimProduk::where('nokirimfk', $request['strukkirim']['noreckirim'])
                ->where('kdprofile', $idProfile)
                ->where('qtyproduk', '>', 0)
                ->get();
            foreach ($getDetails as $item) {
                //PENGIRIM
                $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $request['strukkirim']['objectruanganasal'],
                        'produkfk' => $item->objectprodukfk,
                    )
                );
                $saldoAwalPengirim = 0;
                foreach ($dataSaldoAwalK as $items) {
                    $saldoAwalPengirim = (float)$items->qty;
                }
                $tambah = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
                    ->where('kdprofile', $idProfile)
                    ->where('objectruanganfk', $request['strukkirim']['objectruanganasal'])
                    ->where('objectprodukfk', $item->objectprodukfk)
                    ->first();
                StokProdukDetail::where('norec', $tambah->norec)
                    ->where('kdprofile', $idProfile)
                    ->update([
                            'qtyproduk' => (float)$tambah->qtyproduk + (float)$item->qtyproduk]
                    );

                //## KartuStok
                $newKS = new KartuStok();
                $norecKS = $newKS->generateNewId();
                $newKS->norec = $norecKS;
                $newKS->kdprofile = $idProfile;
                $newKS->statusenabled = true;
                $newKS->jumlah = (float)$item->qtyproduk;
                $newKS->keterangan = 'Batal Kirim Laundry ke Ruangan ' . $request['strukkirim']['ruangantujuan'] . ' No Kirim: ' . $dataSK->nokirim;
                $newKS->produkfk = $item->objectprodukfk;
                $newKS->ruanganfk = $request['strukkirim']['objectruanganasal'];
                $newKS->saldoawal = (float)$saldoAwalPengirim + (float)$item->qtyproduk;
                $newKS->status = 1;
                $newKS->tglinput = date('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $item->nostrukterimafk;
                $newKS->save();

//                if ($request['strukkirim']['jenispermintaanfk'] == 2) {
                    //PENERIMA
                    $dataSaldoAwalT = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                        array(
                            'ruanganfk' => $request['strukkirim']['obejectruangantujuan'],
                            'produkfk' => $item->objectprodukfk,
                        )
                    );
                    $saldoAwalPenerima = 0;
                    foreach ($dataSaldoAwalT as $items) {
                        $saldoAwalPenerima = (float)$items->qty;
                    }

                    $kurang = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
                        ->where('kdprofile', $idProfile)
                        ->where('objectruanganfk', $request['strukkirim']['obejectruangantujuan'])
                        ->where('objectprodukfk', $item->objectprodukfk)
//                        ->where('qtyproduk','>',0)
                        ->first();
                    StokProdukDetail::where('norec', $kurang->norec)
                        ->update([
                                'qtyproduk' => (float)$kurang->qtyproduk - (float)$item->qtyproduk]
                        );
                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = (float)$item->qtyproduk;
                    $newKS->keterangan = 'Batal Terima Barang dari Ruangan ' . $request['strukkirim']['ruanganasal'] . ' No Kirim: ' . $dataSK->nokirim;
                    $newKS->produkfk = $item->objectprodukfk;
                    $newKS->ruanganfk = $request['strukkirim']['obejectruangantujuan'];
                    $newKS->saldoawal = (float)$saldoAwalPenerima - (float)$item->qtyproduk;
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk = $item->nostrukterimafk;
                    $newKS->save();
//                }
            }
            StrukKirim::where('norec', $request['strukkirim']['noreckirim'])
                ->where('kdprofile', $idProfile)
                ->update([
                    'statusenabled' => false
                ]);
            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Kirim Laundry';
            $logUser->noreff = $request['strukkirim']['noreckirim'];
            $logUser->referensi='norec Struk Kirim';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            $logUser->keterangan = $request['strukkirim']['keterangan'];
            $logUser->save();

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $dataSK,
//                "datalogin" => $dataLogin['userData'],
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "saldoAwal" => $saldoAwalPengirim,
                "data" => $dataSK,
//                "datalogin" => $dataLogin['userData'],
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftartCuciLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukkirim_t as sp')
            ->join('pencucianlinen_t as cuc','cuc.strukkirimfk','=','sp.norec')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk',
                'ru.id as ruasalid','ru.namaruangan as ruanganasal','ru2.id as rutujuanid','ru2.namaruangan as ruangantujuan','sp.keteranganlainnyakirim','sp.statuskirim',
                'cuc.tgl as tglcuci',
                DB::raw('count(kp.objectprodukfk) as jmlitem')
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim','sp.statuskirim','cuc.tgl');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ilike','%'. $request['nokirim'].'%');
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru.namaruangan','ilike', '%'.$request['ruangantujuanfk'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('kp.objectprodukfk','=', $request['produkfk']);
        }
//        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);

//        $data = $data->wherein('sp.objectruanganasalfk',$strRuangan);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select  pr.id as kdproduk,pr.kdproduk as kdsirs,pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.objectprodukfk
                     from kirimproduk_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nokirimfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $jeniskirim ='';
            if ($item->jenispermintaanfk == 1){
                $jeniskirim ='Amprahan';
            }
            if ($item->jenispermintaanfk == 2){
                $jeniskirim ='Transfer';
            }
            $sttaus ='';

            $results[] = array(
                'status' => 'Pencucian Linen',
                'tglstruk' => $item->tglkirim,
                'nostruk' => $item->nokirim,
                'noorderfk' => $item->noorderfk,
                'jenispermintaanfk' => $item->jenispermintaanfk,
                'jeniskirim' => $jeniskirim,
                'norec' => $item->norec,
                'ruasalid'=> $item->ruasalid,
                'namaruanganasal' => $item->ruanganasal,
                'rutujuanid'=> $item->rutujuanid,
                'namaruangantujuan' => $item->ruangantujuan,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganlainnyakirim,
                'jmlitem' => $item->jmlitem,
                'statuskirim' =>$item->statuskirim,
                'tglcuci' =>$item->tglcuci,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'as@epic',
//            'str' => $strRuangan,
        );

        return $this->respond($result);
    }
    public function saveCuciLinen (Request $request){
        \DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            $dataSK = new PencucianLinen();
            $dataSK->norec = $dataSK->generateNewId();
             $dataSK->kdprofile = $idProfile;
             $dataSK->statusenabled =true;
             $dataSK->berat =null;
             $dataSK->objectmappingcyclefk  =null;
             $dataSK->objectsatuanfk =null;
             $dataSK->tgl = $request['tglcuci'];
             $dataSK->strukkirimfk = $request['noreckirim'];
            $dataSK->save();

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $dataSK,

                "message" => $transMessage,
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,

                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataProdukDetail(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->JOIN('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->leftJOIN('stokprodukdetail_t as spd', 'spd.objectprodukfk', '=', 'pr.id')
            ->select('pr.id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar','pr.spesifikasi')
            ->where('pr.statusenabled', true)
            ->where('kp.id', $request['idkelompokproduk'])
            ->where('pr.kdprofile', $idProfile)
//            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id', 'pr.namaproduk', 'ss.id', 'ss.satuanstandar','pr.spesifikasi')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss', 'ss.id', '=', 'ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2', 'ss2.id', '=', 'ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk', 'ks.satuanstandar_asal', 'ss.satuanstandar', 'ks.satuanstandar_tujuan', 'ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kdprofile', $idProfile)
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
    public function saveRegistrasiLinen(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 13, 'LA/' . $this->getDateTime()->format('ym/'), $idProfile);
        if ($noStruk == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            \DB::rollBack();
            $result = array(
                "status" => 400,
                "noterima" => $noStruk,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }
        try {
            //region Save
            $req = $request;
            $status = "";

            if ($request['struk']['nostruk'] == '') {
                $SP = new StrukPelayanan();
                $norecSP = $SP->generateNewId();

                $SP->norec = $norecSP;
                $SP->kdprofile = $idProfile;
                $SP->statusenabled = true;
//                $SP->noorderfk = $req['struk']['norecOrder'];
                $SP->noterima = $noStruk;

            } else {

                //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                $dataKembaliStok = DB::select(DB::raw("select sp.norec,spd.qtyproduk,spd.hasilkonversi,sp.objectruanganfk,spd.objectprodukfk,
                          sp.nostruk
                                from strukpelayanandetail_t as spd
                                INNER JOIN strukpelayanan_t sp on sp.norec=spd.nostrukfk
                                where spd.kdprofile = $idProfile and sp.norec=:norec"),
                    array(
                        'norec' => $request['struk']['nostruk'],
                    )
                );

                $TambahStok=0;
                foreach ($dataKembaliStok as $item5) {
                    $TambahStok = (float)$item5->qtyproduk * (float)$item5->hasilkonversi;
                    $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
                                where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                        array(
                            'ruanganfk' => $item5->objectruanganfk,
                            'produkfk' => $item5->objectprodukfk,
                        )
                    );


                    $saldoAwal = 0;
                    foreach ($dataSaldoAwal as $itemss) {
                        $saldoAwal = (float)$itemss->qty;
                    }

                    foreach ($req['details'] as $hit) {

                        if ($saldoAwal == $hit['jumlah'] || $saldoAwal >= $hit['jumlah']) {

                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $idProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                            $newKS->keterangan = 'Ubah Registrasi Laundry No. ' . $item5->nostruk;
                            $newKS->produkfk = $item5->objectprodukfk;
                            $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
                            $newKS->saldoawal = (float)$saldoAwal - (float)$TambahStok;
                            $newKS->status = 0;
                            $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->nostrukterimafk = $request['struk']['nostruk'];
                            $newKS->norectransaksi = $request['struk']['norecOrder'];
                            $newKS->tabletransaksi = 'orderpelayanan_t';
                            $newKS->save();

                            //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                            $SP = StrukPelayanan::where('norec', $request['struk']['nostruk'])->where('kdprofile', $idProfile)->first();
                            $noStruk = $SP->nostruk;
                            //TODO: betulkan ubah penerimaan masih salah
                            //ubah penerimaan harusnya brg yg di terima hrs di keluarkan dulu
                            //tpi ini barang sudah terpakai, pengurang stok hanya delete spd dengan brang yg sudah kepake
                            $delSPD = StokProdukDetail::where('nostrukterimafk', $request['struk']['nostruk'])
                                ->where('kdprofile', $idProfile)
                                ->delete();
                            $delSPD = StrukPelayananDetail::where('nostrukfk', $request['struk']['nostruk'])
                                ->where('kdprofile', $idProfile)
                                ->delete();
                        } else {

                        }
                    }
                }
            }
            $SP->nostruk = $noStruk;
            $SP->objectkelompoktransaksifk = $req['struk']['kelompoktranskasi'];
//            $SP->objectrekananfk = $req['struk']['rekananfk'];
//            $SP->namarekanan = $req['struk']['namarekanan'];
            $SP->objectruanganfk = $req['struk']['ruanganfk'];
            $SP->keteranganlainnya = 'Registrasi Linen Ruangan';
//            $SP->nokontrak = $req['struk']['nokontrak'];;
//            $SP->nofaktur = $req['struk']['nofaktur'];
//            $SP->tglfaktur = date('Y-m-d H:i:s', strtotime($req['struk']['tglfaktur']));//$req['struk']['tglfaktur'];
            $SP->tglstruk = date('Y-m-d H:i:s', strtotime($req['struk']['tglstruk']));//$req['struk']['tglstruk'];
            $SP->objectpegawaipenerimafk = $req['struk']['pegawaimenerimafk'];
            $SP->objectpegawaimenerimafk = $req['struk']['pegawaimenerimafk'];
//            $SP->objectpegawaipenanggungjawabfk = $req['struk']['objectpegawaipenanggungjawabfk'];
            $SP->namapegawaipenerima = $req['struk']['namapegawaipenerima'];
            $SP->qtyproduk = $req['struk']['qtyproduk'];
            $SP->totalharusdibayar =  0;//$req['struk']['totalharusdibayar'];
            $SP->totalppn = 0;//$req['struk']['totalppn'];
            $SP->totaldiscount =0;// $req['struk']['totaldiscount'];
            $SP->totalhargasatuan = 0;//$req['struk']['totalhargasatuan'];
            $SP->keteranganambil = $req['struk']['ketterima'];
//            $SP->tgldokumen = $req['struk']['tglorder'];
//            $SP->tglkontrak = $req['struk']['tglkontrak'];
//            $SP->namapengadaan = $req['struk']['namapengadaan'];
//            $SP->tgljatuhtempo = date('Y-m-d H:i:s', strtotime($req['struk']['tgljatuhtempo']));//$req['struk']['tgljatuhtempo'];
            $SP->save();


            foreach ($req['details'] as $item) {
                $qtyJumlah = (float)$item['jumlah'] * (float)$item['nilaikonversi'];

                $SPD = new StrukPelayananDetail();
                $norecKS = $SPD->generateNewId();
                $SPD->norec = $norecKS;
                $SPD->kdprofile = $idProfile;
                $SPD->statusenabled = true;
                $SPD->nostrukfk = $SP->norec;
//                $SPD->objectasalprodukfk = $request['struk']['asalproduk'];//$item['asalprodukfk'];
                $SPD->objectprodukfk = $item['produkfk'];
                $SPD->objectruanganfk = $req['struk']['ruanganfk'];
                $SPD->objectruanganstokfk = $req['struk']['ruanganfk'];
                $SPD->objectsatuanstandarfk = $item['satuanstandarfk'];
                $SPD->hargadiscount = 0;//$item['hargadiscount'];
                $SPD->hargadiscountgive = 0;
                $SPD->hargadiscountsave = 0;
                $SPD->harganetto = 0;//$item['hargasatuan'];
                $SPD->hargapph = 0;
                $SPD->hargappn = 0;//$item['ppn'];
                $SPD->hargasatuan =0;// $item['hargasatuan'];
                $SPD->hasilkonversi = $item['nilaikonversi'];
                $SPD->namaproduk = $item['namaproduk'];
                $SPD->keteranganlainnya = $item['keterangan'];
                $SPD->hargasatuandijamin = 0;
                $SPD->hargasatuanppenjamin = 0;
                $SPD->hargatambahan = 0;
                $SPD->hargasatuanpprofile = 0;
                $SPD->isonsiteservice = 0;
                $SPD->kdpenjaminpasien = 0;
                $SPD->persendiscount = 0;//$item['persendiscount'];
                $SPD->persenppn = 0;//$item['persenppn'];
                $SPD->qtyproduk = $item['jumlah'];
                $SPD->qtyprodukoutext = 0;
                $SPD->qtyprodukoutint = 0;
                $SPD->qtyprodukretur = 0;
                $SPD->satuan = '-';//$item['satuanstandar'];;
                $SPD->satuanstandar = $item['satuanviewfk'];
                $SPD->tglpelayanan = date('Y-m-d H:i:s', strtotime($req['struk']['tglstruk']));//$req['struk']['tglstruk'];
                $SPD->is_terbayar = 0;
                $SPD->linetotal = 0;
//                $SPD->tglkadaluarsa = $item['tglkadaluarsa'];
//                $SPD->nobatch = $item['nobatch'];
                $SPD->save();

                //## StokProdukDetail
                $StokPD = new StokProdukDetail();
                $norecStokPD = $StokPD->generateNewId();
                $StokPD->norec = $norecKS;
                $StokPD->kdprofile = $idProfile;
                $StokPD->statusenabled = true;

//                $StokPD->objectasalprodukfk = $request['struk']['asalproduk'];//$item['asalprodukfk'];
                $StokPD->hargadiscount = 0;
//                $ppn = ((float) $item['persenppn'] * (float)$item['hargasatuan'] )/100;
                $StokPD->harganetto1 = 0;//((float)$item['hargasatuan'] + $ppn) / (float)$item['nilaikonversi'];
                // $StokPD->harganetto1 = ((float)$item['hargasatuan'] + (float)$item['ppn']) / (float)$item['nilaikonversi'];
                $StokPD->harganetto2 = 0;//((float)$item['hargasatuan']) / (float)$item['nilaikonversi'];
                $StokPD->persendiscount = 0;
                $StokPD->objectprodukfk = $item['produkfk'];
                $StokPD->qtyproduk = $qtyJumlah;
                $StokPD->qtyprodukonhand = 0;
                $StokPD->qtyprodukoutext = 0;
                $StokPD->qtyprodukoutint = 0;

                $StokPD->objectruanganfk = $req['struk']['ruanganfk'];
                $StokPD->nostrukterimafk = $SP->norec;
//                $StokPD->nobatch = $item['nobatch'];
                $StokPD->objectstrukpelayanandetail = $SPD->norec;
//                $StokPD->tglkadaluarsa = $item['tglkadaluarsa'];
                $StokPD->tglpelayanan = date('Y-m-d H:i:s', strtotime($req['struk']['tglstruk']));//$req['struk']['tglstruk'];
                $StokPD->save();

                $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                      where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $item['ruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );

                foreach ($dataSaldoAwal as $items) {
                    $saldoAwal = (float)$items->qty;
                }
                if ($saldoAwal == 0){
                    $saldoAwal = $qtyJumlah;
                }
//                 return $this->respond($saldoAwal);
                //## KartuStok
                $newKS = new KartuStok();
                $norecKS = $newKS->generateNewId();
                $newKS->norec = $norecKS;
                $newKS->kdprofile = $idProfile;
                $newKS->statusenabled = true;
                $newKS->jumlah = $qtyJumlah;
                $newKS->keterangan = 'Registrasi Linen Ruangan. No Registrasi. ' . $noStruk  . $req['struk']['namaruangan'];
                $newKS->produkfk = $item['produkfk'];
                $newKS->ruanganfk = $req['struk']['ruanganfk'];
                $newKS->saldoawal = (float)$saldoAwal;//- (float)$qtyJumlah;
                $newKS->status = 1;
                $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $SP->norec;
                $newKS->norectransaksi = $SP->norec;
                $newKS->tabletransaksi = 'strukpelayanan_t';
                $newKS->save();

            }

            //endregion Save
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = $status;


        if ($transStatus == 'true') {
            $transMessage = "Simpan Struk Pelayanan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $SP,
//                "datas" => $datanorecSR,
//                "dataSR" => $dataSR,
//                "dataRR" => $dataRR,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Struk Pelayanan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "data" =>$SP,
//                "datas" => $datanorecSR,
//                "dataSR" => $dataSR,
//                "dataRR" => $dataRR,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataStokRuanganDetailLaundry(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdSirs1 = $request['KdSirs1'];
        $kdSirs2= $request['KdSirs2'];
        $dataLogin=$request->all();
        $data = \DB::table('stokprodukdetail_t as spd')
            ->JOIN('strukpelayanan_t as sp','sp.norec','=','spd.nostrukterimafk')
            ->JOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('asalproduk_m as ap','ap.id','=','spd.objectasalprodukfk')
            ->JOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->join('ruangan_m as ru','ru.id','=','spd.objectruanganfk')
            ->select('sp.norec as norec_sp','sp.nostruk as noterima','spd.objectprodukfk','pr.kdproduk as kdsirs','pr.namaproduk','ap.asalproduk','spd.qtyproduk',
                'ss.satuanstandar','spd.tglkadaluarsa','spd.nobatch','spd.harganetto1','spd.norec as norec_spd',
                'spd.nostrukterimafk','ru.namaruangan')
            ->where('spd.kdprofile', $idProfile);

        if(isset($request['kelompokprodukid']) && $request['kelompokprodukid']!="" && $request['kelompokprodukid']!="undefined"){
            $data = $data->where('jp.objectkelompokprodukfk','=', $request['kelompokprodukid']);
        }
        if(isset($request['jeniskprodukid']) && $request['jeniskprodukid']!="" && $request['jeniskprodukid']!="undefined"){
            $data = $data->where('djp.objectjenisprodukfk','=', $request['jeniskprodukid']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('spd.objectprodukfk','=', $request['produkfk']);
        }
        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $data = $data->where('pr.namaproduk','ilike','%'. $request['namaproduk'] .'%');
        }
        if(isset($request['ruanganfk']) && $request['ruanganfk']!="" && $request['ruanganfk']!="undefined"){
            $data = $data->where('spd.objectruanganfk','=', $request['ruanganfk']);
        }
        if(isset($request['asalprodukfk']) && $request['asalprodukfk']!="" && $request['asalprodukfk']!="undefined"){
            $data = $data->where('spd.objectasalprodukfk','=', $request['asalprodukfk']);
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
        $data = $data->where('spd.qtyproduk','>', 0);
        $data = $data->where('sp.objectkelompoktransaksifk','=', 6);

        $data=$data->take($request['jmlRows']);
        $data = $data->get();
        $data2=[];
        foreach ($data as $item){
            $data2[] = array(
                'norec_sp' => $item->norec_sp,
                'noTerima' => $item->noterima,
                'kodeProduk' => $item->objectprodukfk,
                'kdsirs' => $item->kdsirs,
                'namaProduk' => $item->namaproduk,
                'asalProduk' => $item->asalproduk,
                'qtyProduk' => $item->qtyproduk,
                'satuanStandar' => $item->satuanstandar,
                'tglKadaluarsa' => $item->tglkadaluarsa,
                'noBatch' => $item->nobatch,
                'harga' => $item->harganetto1,
                'norec_spd' => $item->norec_spd,
                'nostrukterimafk' => $item->nostrukterimafk,
                'namaruangan' => $item->namaruangan,
            );
        }
        $result= array(
            'detail' => $data2,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getDetailRegistrasiLinen(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataStruk = \DB::table('strukpelayanan_t as sr')
            ->LEFTJOIN('riwayatrealisasi_t as rr', 'rr.penerimaanfk', '=', 'sr.norec')
            ->LEFTJOIN('strukrealisasi_t as srr', 'srr.norec', '=', 'rr.objectstrukrealisasifk')
            ->LEFTJOIN('mataanggaran_m as ma', 'ma.norec', '=', 'srr.objectmataanggaranfk')
            ->leftJOIN('pegawai_m as pg', 'pg.id', '=', 'sr.objectpegawaipenerimafk')
            ->leftJOIN('pegawai_m as pg1', 'pg1.id', '=', 'sr.objectpegawaipenanggungjawabfk')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'sr.objectruanganfk')
            ->select('sr.tglstruk', 'sr.nostruk', 'rr.noorderintern as nousulan', 'sr.nokontrak', 'pg.id as pgid', 'pg.namalengkap', 'ru.id', 'ru.namaruangan', 'sr.nofaktur',
                'sr.tglfaktur', 'sr.namarekanan', 'sr.objectrekananfk', 'sr.nosppb', 'srr.norealisasi', 'srr.norec as norecrealisasi', 'sr.tglkontrak', 'sr.tgldokumen',
                'rr.objectstrukfk', 'srr.objectmataanggaranfk as mataanggranid', 'ma.namamataanggaran', 'rr.tglrealisasi', 'sr.keteranganlainnya', 'sr.keteranganambil',
                'pg.id as pgid', 'pg.namalengkap', 'sr.objectpegawaipenanggungjawabfk', 'pg1.namalengkap as penanggungjawab', 'sr.namapengadaan', 'sr.noorderfk','sr.tgljatuhtempo')
            ->where('sr.kdprofile', $idProfile);

        if (isset($request['norec']) && $request['norec'] != "" && $request['norec'] != "undefined") {
            $dataStruk = $dataStruk->where('sr.norec', '=', $request['norec']);
        }

        $dataStruk = $dataStruk->first();

        $data = \DB::table('strukpelayanan_t as sp')
            ->JOIN('strukpelayanandetail_t as spd', 'spd.nostrukfk', '=', 'sp.norec')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'sp.objectruanganfk')
            ->JOIN('produk_m as pr', 'pr.id', '=', 'spd.objectprodukfk')
            ->leftJOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->leftJOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJOIN('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'spd.objectsatuanstandarfk')
            ->leftJOIN('asalproduk_m as ap', 'ap.id', '=', 'spd.objectasalprodukfk')
            ->select('sp.nostruk', 'spd.hargasatuan', 'spd.qtyproduk', 'sp.objectruanganfk', 'ru.namaruangan',
                'spd.objectprodukfk as produkfk', 'pr.namaproduk', 'spd.hasilkonversi as nilaikonversi',
                'spd.objectsatuanstandarfk', 'ss.satuanstandar', 'spd.satuanstandar as satuanviewfk', 'ss.satuanstandar as ssview',
                'spd.qtyproduk as jumlah', 'spd.hargadiscount', 'spd.hargappn', 'spd.hargasatuan', 'spd.objectasalprodukfk',
                'ap.asalproduk', 'spd.persendiscount', 'spd.persenppn', 'spd.keteranganlainnya', 'spd.nobatch', 'spd.tglkadaluarsa',
                'kp.id as kpid', 'kp.kelompokproduk')
            ->where('sp.kdprofile', $idProfile);

        if (isset($request['norec']) && $request['norec'] != "" && $request['norec'] != "undefined") {
            $data = $data->where('sp.norec', '=', $request['norec']);
        }
        $data = $data->get();

        $pelayananPasien = [];
        $i = 0;
        foreach ($data as $item) {
            $i = $i + 1;
            $pelayananPasien[] = array(
                'no' => $i,
                'hargasatuan' => $item->hargasatuan,
                'ruanganfk' => $item->objectruanganfk,
                'asalprodukfk' => $item->objectasalprodukfk,
                'asalproduk' => $item->asalproduk,
                'produkfk' => $item->produkfk,
                'namaproduk' => $item->namaproduk,
                'nilaikonversi' => $item->nilaikonversi,
                'satuanstandarfk' => $item->satuanviewfk,
                'satuanstandar' => $item->ssview,
                'satuanviewfk' => $item->satuanviewfk,
                'satuanview' => $item->ssview,
                'jumlah' => $item->jumlah,
//                'hargadiscount' => $item->hargadiscount,
//                'persendiscount' => $item->persendiscount,
//                'ppn' => $item->hargappn,
//                'persenppn' => $item->persenppn,
//                'total' =>  ((float)$item->hargasatuan *  (float) $item->jumlah )- (float)$item->hargadiscount + (float)$item->hargappn,
                'keterangan' => $item->keteranganlainnya,
//                'nobatch' => $item->nobatch,
//                'tglkadaluarsa' => $item->tglkadaluarsa,
                'kpid' => $item->kpid,
                'kelompokproduk' => $item->kelompokproduk,
            );
        }

        $result = array(
            'detailterima' => $dataStruk,
            'pelayananPasien' => $pelayananPasien,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getProdukDetailLaundry(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //        namafield	nilaifield	keteranganfungsi
//        MetodeAmbilHargaNetto	0	KOSONG .: pengambilan hn1 atau hn2

//        MetodeHargaNetto	2	AVG
//        MetodeHargaNetto	3	Harga Tertinggi
//        MetodeHargaNetto	1	Harga Netto #

//        MetodeStokHargaNetto	2	LIFO
//        MetodeStokHargaNetto	3	FEFO #
//        MetodeStokHargaNetto	4	LEFO
//        MetodeStokHargaNetto	1	FIFO
//        MetodeStokHargaNetto	5	Summary

//        SistemHargaNetto	7	Harga Terakhir #
//        SistemHargaNetto	6	LEFO
//        SistemHargaNetto	2	LIFO
//        SistemHargaNetto	3	Harga Tertinggi
//        SistemHargaNetto	4	AVG
//        SistemHargaNetto	5	FEFO
//        SistemHargaNetto	1	FIFO

        //bucat : # setting yg dipakai


//        select * from settingdatafixed_m where namafield  in ('SistemHargaNetto','MetodeHargaNetto','MetodeStokHargaNetto','MetodeAmbilHargaNetto') order by namafield;
//        select * from jenistransaksi_m where id=5;
//        select * from persenhargajualproduk_m where objectjenistransaksifk =5; .:25%

        $jenisTransaksi = \DB::table('jenistransaksi_m as jt')
            ->where('jt.kdprofile', $idProfile)
            ->where('jt.id',5)
            ->where('jt.statusenabled',true)
            ->first();

        if ($this->getCountArray($jenisTransaksi) == 0){
            return $this->respond(array(
                'Error' => 'Setting jenistransaksi_m dulu',
                'message' => 'as@epic',
            ));
        }
        $strMetodeAmbilHargaNetto = $jenisTransaksi->metodeambilharganetto;
//        $strMetodeHargaNetto = $jenisTransaksi->metodeharganetto; //ketika penerimaan saja
        $strMetodeStokHargaNetto = $jenisTransaksi->metodestokharganetto;
        $strSistemHargaNetto = $jenisTransaksi->sistemharganetto;

        $persenHargaJualProduk = \DB::table('persenhargajualproduk_m as phjp')
            ->JOIN('range_m as rg','rg.id','=','phjp.objectrangefk')
            ->select('rg.rangemin','rg.rangemax','phjp.persenuphargasatuan')
            ->where('phjp.kdprofile', $idProfile)
            ->where('phjp.objectjenistransaksifk',5)
            ->where('phjp.statusenabled',true)
            ->get();
        if (count($persenHargaJualProduk) == 0){
            return $this->respond(array(
                'Error' => 'Setting persenhargajualproduk_m dulu',
                'message' => 'as@epic',
            ));
        }

//        $persenUpHargaSatuan = $persenHargaJualProduk->persenuphargasatuan;
        $strHN ='';
        $strMSHT ='';
        $SistemHargaNetto='';
        $MetodeAmbilHargaNetto='';
        $MetodeStokHargaNetto='';

        // ### FIFO ### //
        if ($strSistemHargaNetto == 1){
            $SistemHargaNetto ='FIFO';

            if ($strMetodeAmbilHargaNetto == 1) {//HN1
                $strHN ='spd.harganetto1';
                $MetodeAmbilHargaNetto ='HN1';
            }
            if ($strMetodeAmbilHargaNetto == 2) {//HN2
                $strHN ='spd.harganetto2';
                $MetodeAmbilHargaNetto ='HN2';
            }

            if ($strMetodeStokHargaNetto == 1){//FIFO
                $strMSHT ='sk.tglstruk';
                $MetodeStokHargaNetto='FIFO';
            }
            if ($strMetodeStokHargaNetto == 2){//LIFO
                $strMSHT ='';
                $MetodeStokHargaNetto='LIFO';
            }
            if ($strMetodeStokHargaNetto == 3){//FEFO
                $strMSHT ='spd.tglkadaluarsa';
                $MetodeStokHargaNetto='FEFO';
            }
            if ($strMetodeStokHargaNetto == 4){//LEFO
                $strMSHT ='';
                $MetodeStokHargaNetto='LEFO';
            }
            if ($strMetodeStokHargaNetto == 5){//Summary
                $strMSHT ='';
                $MetodeStokHargaNetto='Summary';
            }
            $result = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, $strMSHT as tgl,spd.objectasalprodukfk,$strHN as harganetto ,
                      spd.hargadiscount,
                sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk,ap.asalproduk,spd.nostrukterimafk
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                where spd.kdprofile = $idProfile and spd.objectprodukfk =:produkId and spd.objectruanganfk =:ruanganid
                group by sk.norec,spd.objectprodukfk, $strMSHT,spd.objectasalprodukfk, 
                        $strHN,spd.hargadiscount,
                spd.objectruanganfk,ap.asalproduk,spd.nostrukterimafk
                order By $strMSHT"),
                array(
                    'produkId' => $request['produkfk'],
                    'ruanganid' => $request['ruanganfk'],
                )
            );

            $persenUpHargaSatuan=0;
            foreach ($result as $item){
                foreach ($persenHargaJualProduk as $hitem){
                    if ((float)$hitem->rangemin < (float)$item->harganetto && (float)$hitem->rangemax > (float)$item->harganetto){
                        $persenUpHargaSatuan = (float)$hitem->persenuphargasatuan;
                    }
                }
                $results[] = array(
                    'norec' => $item->norec,
                    'objectprodukfk' => $item->objectprodukfk,
                    'tgl' => $item->tgl,
                    'objectasalprodukfk' => $item->objectasalprodukfk,
                    'asalproduk' => $item->asalproduk,
                    'harganetto' => $item->harganetto,
                    'hargadiscount' => $item->hargadiscount,
                    'hargajual' => (float)$item->harganetto + (((float)$item->harganetto * (float)$persenUpHargaSatuan)/100),
                    'persenhargajualproduk'=>$persenUpHargaSatuan,
                    'qtyproduk' => $item->qtyproduk,
                    'objectruanganfk' => $item->objectruanganfk,
                    'nostrukterimafk' => $item->nostrukterimafk,
                );
            }
        }
        // ### END-FIFO ### //

        // ### Harga Tertinggi ### //
        if ($strSistemHargaNetto == 3){
            $SistemHargaNetto ='Harga Tertinggi';
            if ($strMetodeAmbilHargaNetto == 1) {//HN1
                $strHN ='spd.harganetto1';
                $MetodeAmbilHargaNetto ='HN1';
            }
            if ($strMetodeAmbilHargaNetto == 2) {//HN2
                $strHN ='spd.harganetto2';
                $MetodeAmbilHargaNetto ='HN2';
            }

            if ($strMetodeStokHargaNetto == 1){//FIFO
                $strMSHT ='sk.tglstruk';
                $MetodeStokHargaNetto='FIFO';
            }
            if ($strMetodeStokHargaNetto == 2){//LIFO
                $strMSHT ='';
                $MetodeStokHargaNetto='LIFO';
            }
            if ($strMetodeStokHargaNetto == 3){//FEFO
                $strMSHT ='spd.tglkadaluarsa';
                $MetodeStokHargaNetto='FEFO';
            }
            if ($strMetodeStokHargaNetto == 4){//LEFO
                $strMSHT ='';
                $MetodeStokHargaNetto='LEFO';
            }
            if ($strMetodeStokHargaNetto == 5){//Summary
                $strMSHT ='';
                $MetodeStokHargaNetto='Summary';
            }
            $maxHarga = DB::select(DB::raw("select $strHN as harga
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                where spd.kdprofile = $idProfile and spd.objectprodukfk =:produkId and spd.objectruanganfk =:ruanganid"),
                array(
                    'produkId' => $request['produkfk'],
                    'ruanganid' => $request['ruanganfk'],
                )
            );
            $hargaTertinggi=0;
            foreach ($maxHarga as $item){
                if ($hargaTertinggi < (float)$item->harga){
                    $hargaTertinggi =(float)$item->harga;
                }
            }

            $result = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, $strMSHT as tgl,spd.objectasalprodukfk,$hargaTertinggi as harganetto ,
                        $hargaTertinggi  as hargajual,spd.hargadiscount,
                sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk,ap.asalproduk,spd.nostrukterimafk
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                where spd.kdprofile = $idProfile and spd.objectprodukfk =:produkId and spd.objectruanganfk =:ruanganid
                group by sk.norec,spd.objectprodukfk, $strMSHT,spd.objectasalprodukfk, 
                        spd.hargadiscount,
                spd.objectruanganfk,ap.asalproduk,spd.nostrukterimafk
                order By $strMSHT"),
                array(
                    'produkId' => $request['produkfk'],
                    'ruanganid' => $request['ruanganfk'],
                )
            );

            $persenUpHargaSatuan=0;
            foreach ($result as $item){
                foreach ($persenHargaJualProduk as $hitem){
                    if ((float)$hitem->rangemin < (float)$item->harganetto && (float)$hitem->rangemax > (float)$item->harganetto){
                        $persenUpHargaSatuan = (float)$hitem->persenuphargasatuan;
                    }
                }
                $results[] = array(
                    'norec' => $item->norec,
                    'objectprodukfk' => $item->objectprodukfk,
                    'tgl' => $item->tgl,
                    'objectasalprodukfk' => $item->objectasalprodukfk,
                    'asalproduk' => $item->asalproduk,
                    'harganetto' => $item->harganetto,
                    'hargadiscount' => $item->hargadiscount,
                    'hargajual' => (float)$item->harganetto + (((float)$item->harganetto * (float)$persenUpHargaSatuan)/100),
                    'persenhargajualproduk'=>$persenUpHargaSatuan,
                    'qtyproduk' => $item->qtyproduk,
                    'objectruanganfk' => $item->objectruanganfk,
                    'nostrukterimafk' => $item->nostrukterimafk,
                );
            }
        }
        // ### END-Harga Tertinggi ### //

        // ### Harga Terakhir ### //
        if ($strSistemHargaNetto == 7){
            $SistemHargaNetto ='Harga Terakhir';
            if ($strMetodeAmbilHargaNetto == 1) {//HN1
                $strHN ='spd.harganetto1';
                $MetodeAmbilHargaNetto ='HN1';
            }
            if ($strMetodeAmbilHargaNetto == 2) {//HN2
                $strHN ='spd.harganetto2';
                $MetodeAmbilHargaNetto ='HN2';
            }

            if ($strMetodeStokHargaNetto == 1){//FIFO
                $strMSHT ='sk.tglstruk';
                $MetodeStokHargaNetto='FIFO';
            }
            if ($strMetodeStokHargaNetto == 2){//LIFO
                $strMSHT ='sk.tglstruk desc';
                $MetodeStokHargaNetto='LIFO';
            }
            if ($strMetodeStokHargaNetto == 3){//FEFO
                $strMSHT ='spd.tglkadaluarsa';
                $MetodeStokHargaNetto='FEFO';
            }
            if ($strMetodeStokHargaNetto == 4){//LEFO
                $strMSHT ='spd.tglkadaluarsa desc';
                $MetodeStokHargaNetto='LEFO';
            }
            if ($strMetodeStokHargaNetto == 5){//Summary
                $strMSHT ='';
                $MetodeStokHargaNetto='Summary';
            }
            $maxHarga = DB::select(DB::raw("select spd.tglpelayanan, $strHN as harga
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                where spd.kdprofile = $idProfile and spd.objectprodukfk =:produkId "),
                array(
                    'produkId' => $request['produkfk'],
                )
            );
            $hargaTerakhir=0;
            $tgl = date('2000-01-01 00:00');
            foreach ($maxHarga as $item){
                if ($tgl < $item->tglpelayanan){
                    $tgl = $item->tglpelayanan;
                    $hargaTerakhir =(float)$item->harga;
                }
            }
            $result =[];
            $result = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, $strMSHT as tgl,spd.objectasalprodukfk,$hargaTerakhir as harganetto ,
                        $hargaTerakhir  as hargajual,spd.hargadiscount,spd.nostrukterimafk,
                sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk,ap.asalproduk
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                where spd.kdprofile = $idProfile and spd.objectprodukfk =:produkId and spd.objectruanganfk =:ruanganid and spd.qtyproduk > 0
                group by sk.norec,spd.objectprodukfk, $strMSHT, spd.objectasalprodukfk, 
                        spd.hargadiscount,
                spd.objectruanganfk,ap.asalproduk,spd.nostrukterimafk
                order By $strMSHT"),
                array(
                    'produkId' => $request['produkfk'],
                    'ruanganid' => $request['ruanganfk'],
                )
            );
            $results=[];
            $persenUpHargaSatuan=0;
            foreach ($result as $item){
                foreach ($persenHargaJualProduk as $hitem){
                    if ((float)$hitem->rangemin < (float)$item->harganetto && (float)$hitem->rangemax > (float)$item->harganetto){
                        $persenUpHargaSatuan = (float)$hitem->persenuphargasatuan;
                    }
                }
                $results[] = array(
                    'norec' => $item->norec,
                    'objectprodukfk' => $item->objectprodukfk,
                    'tgl' => $item->tgl,
                    'objectasalprodukfk' => $item->objectasalprodukfk,
                    'asalproduk' => $item->asalproduk,
                    'harganetto' => (float)$item->harganetto,//$item->harganetto,
                    'hargadiscount' => $item->hargadiscount,
                    'hargajual' => (float)$item->harganetto + (((float)$item->harganetto * (float)$persenUpHargaSatuan)/100),
                    'persenhargajualproduk'=>$persenUpHargaSatuan,
                    'qtyproduk' => $item->qtyproduk,
                    'objectruanganfk' => $item->objectruanganfk,
                    'nostrukterimafk' => $item->nostrukterimafk,
                );
            }
        }
        // ### END-Harga Terakhir ### //

        $jmlstok =0;
        foreach ($result as $item){
            $jmlstok = $jmlstok+$item->qtyproduk;
        }

        $cekConsis = DB::select(DB::raw("select * from his_obat_ms_t where hobatid=:produkfk;"),
            array(
                'produkfk' =>  $request['produkfk'],
            )
        );
        $cekKekuatanSupranatural = DB::select(DB::raw("

            select pr.kekuatan,sdn.name as sediaan from produk_m as pr 
            inner join rm_sediaan_m as sdn on sdn.id=pr.objectsediaanfk
            where pr.kdprofile = $idProfile and pr.id=:produkfk;
            
            "),
            array(
                'produkfk' =>  $request['produkfk'],
            )
        );
        $kekuatan =0;
        $sediaan=0;
        if (count($cekKekuatanSupranatural)>0){
            $kekuatan = (float)$cekKekuatanSupranatural[0]->kekuatan;
            $sediaan = $cekKekuatanSupranatural[0]->sediaan;
            if ($kekuatan == null){
                $kekuatan =0;
            }
        }


        $result= array(
            'detail' => $results,
            'jmlstok'=> $jmlstok,
            'kekuatan' =>$kekuatan,
            'sediaan' => $sediaan,
            'sistemharganetto' => $SistemHargaNetto,
            'metodeambilharganetto' => $MetodeAmbilHargaNetto,
            'metodestokharganetto' => $MetodeStokHargaNetto,
            'consis' => count($cekConsis),
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getDataLaporanPenerimaanLinen(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('pencucianlinen_t as penc','penc.strukkirimfk','=','sp.norec')
            ->LEFTJOIN('produk_m as pro','pro.id','=','kp.objectprodukfk')
            ->LEFTJOIN('satuanstandar_m as ss','ss.id','=','pro.objectsatuanstandarfk')
            ->select(DB::raw("sp.norec,sp.tglkirim,sp.nokirim,sp.jenispermintaanfk,pg.namalengkap,sp.noorderfk,ru.id AS ruasalid,
                             ru.namaruangan AS ruanganasal,ru2.id AS rutujuanid,ru2.namaruangan AS ruangantujuan,sp.keteranganlainnyakirim,
                             sp.statuskirim,penc.tgl AS tglcuci,pro.namaproduk,ss.satuanstandar,kp.qtyproduk,'Terima Linen' as status"))
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                      'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim','sp.statuskirim','penc.tgl',
                      'pro.namaproduk','ss.satuanstandar','kp.qtyproduk');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ilike','%'. $request['nokirim'].'%');
        }
        if(isset($request['ruanganTujuanfk']) && $request['ruanganTujuanfk'] != "" && $request['ruanganTujuanfk'] != "undefined"){
            $data = $data->where('sp.objectruangantujuanfk','=', $request['ruanganTujuanfk']);
        }
        if(isset($request['ruanganAsalfk']) && $request['ruanganAsalfk'] != "" && $request['ruanganAsalfk'] != "undefined"){
            $data = $data->where('sp.objectruanganasalfk','=', $request['ruanganAsalfk']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('kp.objectprodukfk','=', $request['produkfk']);
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',$request['kelompokTransaksi']);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDataLaporanDistribusiLinen(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('pencucianlinen_t as penc','penc.strukkirimfk','=','sp.norec')
            ->LEFTJOIN('produk_m as pro','pro.id','=','kp.objectprodukfk')
            ->LEFTJOIN('satuanstandar_m as ss','ss.id','=','pro.objectsatuanstandarfk')
            ->select(DB::raw("sp.norec,sp.tglkirim,sp.nokirim,sp.jenispermintaanfk,pg.namalengkap,sp.noorderfk,ru.id AS ruasalid,
                             ru.namaruangan AS ruanganasal,ru2.id AS rutujuanid,ru2.namaruangan AS ruangantujuan,sp.keteranganlainnyakirim,
                             sp.statuskirim,penc.tgl AS tglcuci,pro.namaproduk,ss.satuanstandar,kp.qtyproduk,'Kirim Linen' as status"))
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim','sp.statuskirim','penc.tgl',
                'pro.namaproduk','ss.satuanstandar','kp.qtyproduk');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ilike','%'. $request['nokirim'].'%');
        }
        if(isset($request['ruanganTujuanfk']) && $request['ruanganTujuanfk'] != "" && $request['ruanganTujuanfk'] != "undefined"){
            $data = $data->where('sp.objectruangantujuanfk','=', $request['ruanganTujuanfk']);
        }
        if(isset($request['ruanganAsalfk']) && $request['ruanganAsalfk'] != "" && $request['ruanganAsalfk'] != "undefined"){
            $data = $data->where('sp.objectruanganasalfk','=', $request['ruanganAsalfk']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('kp.objectprodukfk','=', $request['produkfk']);
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',$request['kelompokTransaksi']);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDataLaporanPencucianLinen(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukkirim_t as sp')
            ->join('pencucianlinen_t as cuc','cuc.strukkirimfk','=','sp.norec')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('pencucianlinen_t as penc','penc.strukkirimfk','=','sp.norec')
            ->join('produk_m as pro','pro.id','=','kp.objectprodukfk')
            ->LEFTJOIN('satuanstandar_m as ss','ss.id','=','pro.objectsatuanstandarfk')
            ->select(DB::raw("sp.norec,sp.tglkirim,sp.nokirim,sp.jenispermintaanfk,pg.namalengkap,sp.noorderfk,ru.id AS ruasalid,
                             ru.namaruangan AS ruanganasal,ru2.id AS rutujuanid,ru2.namaruangan AS ruangantujuan,sp.keteranganlainnyakirim,
                             sp.statuskirim,penc.tgl AS tglcuci,pro.namaproduk,ss.satuanstandar,kp.qtyproduk,'Pencucian Linen' as status"))
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                      'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim','sp.statuskirim','penc.tgl',
                      'pro.namaproduk','ss.satuanstandar','kp.qtyproduk');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ilike','%'. $request['nokirim'].'%');
        }
        if(isset($request['ruanganTujuanfk']) && $request['ruanganTujuanfk'] != "" && $request['ruanganTujuanfk'] != "undefined"){
            $data = $data->where('sp.objectruangantujuanfk','=', $request['ruanganTujuanfk']);
        }
        if(isset($request['ruanganAsalfk']) && $request['ruanganAsalfk'] != "" && $request['ruanganAsalfk'] != "undefined"){
            $data = $data->where('sp.objectruanganasalfk','=', $request['ruanganAsalfk']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('kp.objectprodukfk','=', $request['produkfk']);
        }

        $data = $data->where('sp.statusenabled',true);
//        $data = $data->where('sp.objectkelompoktransaksifk',$request['kelompokTransaksi']);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
}