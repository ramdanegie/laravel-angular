<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 9/30/2019
 * Time: 9:25 AM
 */

namespace App\Http\Controllers\Gizi;
use App\Http\Controllers\ApiController;
use App\Master\SiklusGizi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use App\Traits\Valet;
use Webpatser\Uuid\Uuid;
use App\Master\Pegawai;
use App\Master\Pasien;
use App\Master\Ruangan;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\KartuStok;
use App\Transaksi\StrukKirim;
use App\Transaksi\KirimProduk;
use App\Transaksi\StrukClosing;
use App\Transaksi\StokProdukDetailOpname;
use App\Transaksi\StrukOrder;
use App\Transaksi\OrderPelayanan;
use App\Master\JenisDiet;
use App\Master\JenisWaktu;
use App\Master\KategoryDiet;
use App\Transaksi\PasienDaftar;
use App\Transaksi\AntrianPasienDiperiksa;

class GiziController extends ApiController
{
    use Valet;
    public function __construct()
    {
        parent::__construct($skip_authentication=false);
    }
    public function getProdukMenu(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $siklusKe = $request['siklusKe'];
        $kelasId = $request['kelasId'];
        $jenisDietId = $request['jenisDietId'];
        $jenisWaktuId = $request['jenisWaktuId'];
        $kat = $request['kategoryDiet'];
        $norec_pd = $request['norec_pd'];
        $data = DB::select(DB::raw("select  prd.id,sm.objectprodukfk as produkfk, prd.namaproduk,
                    sm.sikluske,sm.objectjeniswaktufk,jw.jeniswaktu,sm.objectjenisdietfk,jd.jenisdiet ,
                    sm.objectkelasfk,kls.namakelas
                    from siklusgizi_m as sm
                    inner join produk_m as prd on prd.id=sm.objectprodukfk
                    inner join jeniswaktu_m as jw on jw.id=sm.objectjeniswaktufk
                    inner join jenisdiet_m as jd on jd.id=sm.objectjenisdietfk
                    inner join kelas_m as kls on kls.id=sm.objectkelasfk
                        INNER JOIN kategorydiet_m kat on kat.id=sm.objectkategoryprodukfk
                    where sm.kdprofile = $kdProfile 
                    and sm.statusenabled =true
                    and sm.sikluske = '$siklusKe' 
                    and kat.id='$kat'
                    and
                     sm.objectjeniswaktufk in (0,1,2,3,4) and --pagi siang sore
                --    sm.objectjeniswaktufk = '$jenisWaktuId'::integer and --pagi siang sore
                    sm.objectjenisdietfk= '$jenisDietId' and
                    sm.objectkelasfk = '$kelasId';
                    "));
        $results=[];
        if (count($data) > 0) {
            foreach ($data as $item) {
                $results[] = array(
                    'id' => $item->id,
                    'produkfk' => $item->produkfk,
                    'namaproduk' => $item->namaproduk,
                    'sikluske' => $item->sikluske,
                    'objectjeniswaktufk' => $item->objectjeniswaktufk,
                    'jeniswaktu' => $item->jeniswaktu,
                    'objectjenisdietfk' => $item->objectjenisdietfk,
                    'jenisdiet' => $item->jenisdiet,
                    'objectkelasfk' => $item->objectkelasfk,
                    'namakelas' => $item->namakelas,
                    'qtyproduk' => 1,
                    'norec_op' => $request['norec_op'],
                );
            }
        }

        $result = array(
            'data' => $results,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function saveOrderGizi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $item =$request['strukorder'] ;
//            foreach ($request['strukorder'] as $item){
                if ($item['norec_so']== '') {
                    $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'G' . $this->getDateTime()->format('ym'),$kdProfile);
                    $dataSO = new StrukOrder;
                    $dataSO->norec = $dataSO->generateNewId();
                    $dataSO->kdprofile = $kdProfile;
                    $dataSO->statusenabled = true;
                    $dataSO->isdelivered = 1;
                    $dataSO->noorder = $noOrder;
                    $dataSO->noorderintern = $noOrder;
                }else{
                    $dataSO = StrukOrder::where('norec',$item['norec_so'])->where('kdprofile', $kdProfile)->first();
                    $del = OrderPelayanan::where('strukorderfk',$item['norec_so'])->where('kdprofile', $kdProfile)->delete();

                }
    //            $dataSO->noregistrasifk = $items['noregistrasifk'];
                $dataSO->objectpegawaiorderfk = $this->getCurrentUserID();
                $dataSO->qtyjenisproduk = 1;
                $dataSO->qtyproduk = $item['qtyproduk'];
                $dataSO->objectruangantujuanfk = $this->settingDataFixed('kdRuanganGizi',$kdProfile);
                $dataSO->keteranganorder = 'Order Gizi';
                $dataSO->objectkelompoktransaksifk = 8; /* Pelayanan Gizi*/
                $dataSO->tglorder = $item['tglorder'];// date('Y-m-d H:i:s');
                $dataSO->tglpelayananawal =$item['tglmenu'];//$item['tglorder'];
                $dataSO->totalbeamaterai = 0;
                $dataSO->totalbiayakirim = 0;
                $dataSO->totalbiayatambahan = 0;
                $dataSO->totaldiscount = 0;
                $dataSO->totalhargasatuan = 0;
                $dataSO->totalharusdibayar = 0;
                $dataSO->totalpph = 0;
                $dataSO->totalppn = 0;
                $dataSO->save();
                $dataSOnorec = $dataSO->norec;

                foreach ($item['details'] as $itemDetails) {
                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $kdProfile;
                    $dataOP->statusenabled = true;
                    $dataOP->iscito = 0;
                    // $dataOP->objectjenisdietfk = $itemDetails['objectjenisdietfk'];
                    $dataOP->arrjenisdiet = $item['jenisdietfk'];
                    if(isset( $item['jeniswaktufk'])){
                        $dataOP->objectjeniswaktufk =$item['jeniswaktufk'];
                    }
                     if(isset( $itemDetails['objectjenisdietfk'])){
                        $dataOP->objectjenisdietfk =$itemDetails['objectjenisdietfk'];
                    }

                    $dataOP->objectkategorydietfk =$itemDetails['objectkategorydietfk'];
                    //                    $dataOP->objectketerangandietfk =$itemDetails['objectprodukfk'];
                    $dataOP->keteranganlainnya = $itemDetails['keterangan']; //'Order Gizi';
                    $dataOP->keteranganlainnya_quo = 'Order Gizi';
                    $dataOP->statusgizi = $item['jenisorder'];
                    $dataOP->nocmfk = $itemDetails['nocmfk'];
                    $dataOP->noregistrasifk = $itemDetails['norec_pd'];
                    $dataOP->noorderfk = $dataSOnorec;
                    $dataOP->qtyproduk = 1;
                    $dataOP->qtyprodukinuse = $itemDetails['cc'];
                    $dataOP->jumlah = $itemDetails['volume'];
                    $dataOP->objectkelasfk = $itemDetails['objectkelasfk'];
                    $dataOP->qtyprodukretur = 0;
                    $dataOP->objectruanganfk =  $itemDetails['objectruanganlastfk'];
                    $dataOP->objectruangantujuanfk = $this->settingDataFixed('kdRuanganGizi', $kdProfile);;
                    $dataOP->strukorderfk = $dataSOnorec;
                    $dataOP->tglpelayanan =$item['tglorder'];
                    $dataOP->save();
                }
//            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Order Pelayanan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Simpan Order Pelayanan gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "nokirim" => $dataSO,//$noResep,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataComboBox(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $dataProdukResult=[];
        $dataRuangGizi = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.id',54)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();
        $ruanganInap = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', [16,35,17])
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

        $departemen = \DB::table('departemen_m as dept')
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
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
            ->where('kp.id',(int)$this->settingDataFixed('kdKelasNonKelasRegistrasi', $kdProfile))
//            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.statusenabled',true)
            ->get();


        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        foreach ($departemen as $item) {
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
            'kelompokpasien' => $dataKelompok,
            'departemen' => $dataDepartemen,
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
    public function getDaftarOrderGizi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('orderpelayanan_t as op')
            ->join ('pasiendaftar_t as pd','pd.norec','=','op.noregistrasifk')
            ->join ('ruangan_m as ru','ru.id','=','op.objectruanganfk')
            ->join ('pasien_m as ps','ps.id','=','op.nocmfk')
            ->leftjoin ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->join ('strukorder_t as so','so.norec','=','op.strukorderfk')
            ->join ('ruangan_m as ru2','ru2.id','=','so.objectruangantujuanfk')
            ->leftjoin ('strukkirim_t as sk','sk.norec','=','op.strukkirimfk')
            ->leftjoin ('jeniswaktu_m as jw','jw.id','=','op.objectjeniswaktufk')
            // ->join ('jenisdiet_m as jd','jd.id','=','op.objectjenisdietfk')
            ->join ('kategorydiet_m as kd','kd.id','=','op.objectkategorydietfk')
            ->leftjoin ('kelas_m as kls','kls.id','=','op.objectkelasfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
            ->select('so.norec as norec_so','op.norec as norec_op', 'so.noorder','so.tglorder',
                'so.tglpelayananawal as tglmenu','pd.tglregistrasi','ps.tgllahir','ps.namapasien','ps.nocm','ps.id as nocmfk',
                'ru.namaruangan as ruanganasal', 'jk.jeniskelamin','op.objectruanganfk',
                'jw.jeniswaktu', 
                // 'jd.jenisdiet', 
                'op.strukorderfk','op.objectkategorydietfk',
                'kd.kategorydiet','op.qtyproduk','op.objectjeniswaktufk','op.objectjenisdietfk'	,
                'op.keteranganlainnya'	,'op.statusgizi as jenisorder'	,'op.qtyprodukinuse as cc'	,'op.jumlah as volume'	,
                'op.objectkelasfk','kls.namakelas','pd.noregistrasi','so.objectpegawaiorderfk','pg.namalengkap as pegawaiorder',
                'sk.nokirim','sk.qtyproduk','pd.norec as norec_pd','so.objectruangantujuanfk','op.strukkirimfk','sk.nokirim',
                'ru2.namaruangan as ruangantujuan','jw.jeniswaktu','op.objectjeniswaktufk','op.arrjenisdiet',
                DB::raw("case when op.strukkirimfk is not null then 'Sudah Dikirim'  else '-' end as statuskirim"))
            ->where('so.kdprofile', $kdProfile)
            ->where('so.statusenabled',true)
            ->where('so.objectkelompoktransaksifk',8);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('so.tglpelayananawal','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('so.tglpelayananawal','<=', $tgl);
        }
        if(isset($request['deptId']) && $request['deptId']!="" && $request['deptId']!="undefined"){
            $data = $data->where('ru.objectdepartemenfk','=', $request['deptId']);
        }

        if(isset($request['pegId']) && $request['pegId']!="" && $request['pegId']!="undefined"){
            $data = $data->where('so.objectpegawaiorderfk','=', $request['pegId']);
        }
        if(isset($request['ruangId']) && $request['ruangId']!="" && $request['ruangId']!="undefined"){
            $data = $data->where('ru.id','=', $request['ruangId']);
        }
        if(isset($request['jenisDietId']) && $request['jenisDietId']!="" && $request['jenisDietId']!="undefined"){
            $data = $data->where('op.objectjenisdietfk','=', $request['jenisDietId']);
        }
        if(isset($request['jenisWaktuId']) && $request['jenisWaktuId']!="" && $request['jenisWaktuId']!="undefined"){
            $data = $data->where('op.objectjeniswaktufk','=', $request['jenisWaktuId']);
        }

        if(isset($request['noorder']) && $request['noorder']!="" && $request['noorder']!="undefined"){
            $data = $data->where('so.noorder','ilike','%'. $request['noorder'].'%');
        }

        if(isset($request['noreg']) && $request['noreg']!="" && $request['noreg']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike', '%'.$request['noreg'].'%');
        }
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined"){
            $data= $data->where('ps.nocm','ilike', '%'.$request['norm'].'%');
        }
        if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
            $data = $data->where('ps.namapasien','ilike', '%'.$request['nama'].'%');
        }
        $data = $data->whereNull('pd.tglpulang');
        $data = $data->orderBy('so.noorder');
        $data = $data->get();
        $datas =[];
        foreach ($data as $item){
            $item->umur = $this->getAge($item->tgllahir,$item->tglregistrasi);
        }

        $dataResult=array(
            'message' =>  'inhuman',
            'data' =>  $data,

        );
        return $this->respond($dataResult);
    }
    public function deleteOrderPelayananGizi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{

            if ($request['norec_op'] != 'undefined'){
                $del = OrderPelayanan::where('norec' , $request['norec_op'])
                    ->delete();
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
                "message" => $transMessage,
                // "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveKirimMenuGizi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            if ($request['strukkirim']['norec_sk'] == '') {
                $noKirim = $this->generateCode(new StrukKirim, 'nokirim', 14, 'KM-' . $this->getDateTime()->format('ym'),$kdProfile);
                $dataSK = new StrukKirim();
                $dataSK->norec = $dataSK->generateNewId();
                $dataSK->nokirim = $noKirim;
                $dataSK->kdprofile = $kdProfile;
                $dataSK->statusenabled = true;
                $dataSK->tglkirim = date('Y-m-d H:i:s');
            }else{
                $dataSK = StrukKirim::where('norec',$request['strukkirim']['norec_sk'])->first();
                $delKP = KirimProduk::where('nokirimfk',$request['strukkirim']['norec_sk'])->delete();
            }
            $dataSK->objectpegawaipengirimfk = $this->getCurrentUserID();
            $dataSK->objectruanganasalfk = $request['strukkirim']['objectruanganasalfk']; /*ruang gizi */
            $dataSK->objectruanganfk = $request['strukkirim']['objectruanganasalfk'];
//            $dataSK->objectruangantujuanfk = $request['strukkirim']['objectruangantujuanfk'];
            $dataSK->jenispermintaanfk = 1; /*transfer */
            $dataSK->objectkelompoktransaksifk = 98;
            $dataSK->keteranganlainnyakirim = $request['strukkirim']['keterangan'];
            $dataSK->qtydetailjenisproduk = 0;
            $dataSK->qtyproduk = $request['strukkirim']['qtyproduk'];
            $dataSK->totalbeamaterai = 0;
            $dataSK->totalbiayakirim = 0;
            $dataSK->totalbiayatambahan = 0;
            $dataSK->totaldiscount = 0;
            $dataSK->totalhargasatuan =0;
            $dataSK->totalharusdibayar = 0;
            $dataSK->totalpph =0;
            $dataSK->totalppn = 0;
            if(isset( $request['strukkirim']['norec_pd'])){
                $dataSK->noregistrasifk = $request['strukkirim']['norec_pd'];
            }
//            $dataSK->noregistrasifk = $request['strukkirim']['norec_pd'];
            $dataSK->save();

            $norecSK = $dataSK->norec;
            foreach ($request['strukkirim']['details'] as $items) {
                $dataKP = new KirimProduk;
                $dataKP->norec = $dataKP->generateNewId();
                $dataKP->kdprofile = $kdProfile;
                $dataKP->statusenabled = true;
//                    $dataKP->objectasalprodukfk = $items->asalprodukfk;
                $dataKP->hargadiscount = 0;
                $dataKP->harganetto = 0;
                $dataKP->hargapph = 0;
                $dataKP->hargappn = 0;
                $dataKP->hargasatuan = 0;
                $dataKP->hargatambahan = 0;
//                    $dataKP->hasilkonversi = $jumlah;
                $dataKP->objectprodukfk = $items['produkfk'];
                $dataKP->objectprodukkirimfk = $items['produkfk'];
                $dataKP->nokirimfk = $norecSK;
                $dataKP->persendiscount = 0;
                $dataKP->qtyproduk = $items['qtyproduk'];
                $dataKP->qtyprodukkonfirmasi = $items['qtyproduk'];
                $dataKP->qtyprodukretur = 0;
                $dataKP->qtyprodukterima = $items['qtyproduk'];
//                    $dataKP->nostrukterimafk = $items->nostrukterimafk;
//                    $dataKP->objectruanganfk = $request['strukkirim']['objectruangantujuanfk'];
                $dataKP->objectruanganpengirimfk = $request['strukkirim']['objectruanganasalfk'];
                $dataKP->satuan = '-';
//                    $dataKP->objectsatuanstandarfk = $satuanstandarfk;//$item['satuanstandarfk'];
//                    $dataKP->satuanviewfk = $item['satuanviewfk'];
                $dataKP->tglpelayanan = date('Y-m-d H:i:s');
                $dataKP->qtyprodukterimakonversi = $items['qtyproduk'];
                $dataKP->save();


            }
            if(isset($request['strukkirim']['datapasien'])){
                foreach ($request['strukkirim']['datapasien'] as $itempasien){
                    if (isset($itempasien['norec_op'])){
                        $updateOp= OrderPelayanan::where('noregistrasifk', $itempasien['norec_pd'])
                            ->where('norec', $itempasien['norec_op'])
                            ->update(
                                [
                                    'strukkirimfk' => $norecSK,
//                                    'objectjeniswaktufk' =>  $request['strukkirim']['objectjeniswaktufk']
                                ]
                            );
                    }
                }
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Struk Kirim";
            DB::commit();
            $result = array(
                "status" => 201,
                "nokirim" => $dataSK,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Simpan gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarKirim(Request $request) {
        $data = \DB::table('strukkirim_t as sk')
            ->leftjoin ('pasiendaftar_t as pd','pd.norec','=','sk.noregistrasifk')
            ->leftjoin ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin ('ruangan_m as ru','ru.id','=','sk.objectruanganasalfk')
            ->leftjoin('ruangan_m as ru2', 'ru2.id', '=', 'sk.objectruangantujuanfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'sk.objectpegawaipengirimfk')
            ->select('sk.norec as norec_sk','sk.nokirim', 'pg.namalengkap as pegawaikirim',  'ru2.namaruangan as ruangantujuan',
                'sk.tglkirim', 'ru.namaruangan as ruanganasal', 'sk.objectruangantujuanfk',
                'sk.objectruanganasalfk',  'sk.objectpegawaipengirimfk','pd.noregistrasi','ps.nocm','ps.namapasien',
                'sk.keteranganlainnyakirim','sk.qtyproduk'
            )
            ->where('sk.statusenabled',true)
            ->where('sk.objectkelompoktransaksifk',98);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sk.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sk.tglkirim','<=', $tgl);
        }

        if(isset($request['pegId']) && $request['pegId']!="" && $request['pegId']!="undefined"){
            $data = $data->where('sk.objectpegawaipengirimfk','=', $request['pegId']);
        }
        if(isset($request['ruangId']) && $request['ruangId']!="" && $request['ruangId']!="undefined"){
            $data = $data->where('sk.objectruangantujuanfk','=', $request['ruangId']);
        }
        if(isset($request['noreg']) && $request['noreg']!="" && $request['noreg']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike', '%'.$request['noreg'].'%');
        }
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined"){
            $data = $data->where('ps.nocm','ilike', '%'.$request['norm'].'%');
        }
        if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
            $data = $data->where('ps.namapasien','ilike', '%'.$request['nama'].'%');
        }

        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sk.nokirim','ilike','%'. $request['nokirim'].'%');
        }

        $data = $data->orderBy('sk.nokirim','desc');
        $data = $data->get();
        $result=[];
        foreach ($data as $item){
            $details2 = DB::select(DB::raw("
                    select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.objectprodukfk as produkfk
                    from kirimproduk_t as spd
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where nokirimfk=:norec_sk and spd.qtyproduk <> 0"),
                array(
                    'norec_sk' => $item->norec_sk,
                )
            );
            $details = DB::select(DB::raw("
                    select  pd.nocmfk,pd.noregistrasi,ps.namapasien,ps.nocm,
                    kd.kategorydiet,jd.jenisdiet,ru.namaruangan
                  ,jw.jeniswaktu
                    from orderpelayanan_t as op 
                     JOIN pasiendaftar_t as pd on pd.norec=op.noregistrasifk
                    left JOIN pasien_m as ps on ps.id=pd.nocmfk
                    left JOIN kategorydiet_m as kd on kd.id=op.objectkategorydietfk
                    left JOIN jenisdiet_m as jd on jd.id=op.objectjenisdietfk
                    left JOIN jeniswaktu_m as jw on jw.id=op.objectjeniswaktufk
                     left JOIN ruangan_m as ru on ru.id=op.objectruanganfk
                    
                    where op.strukkirimfk=:norec_sk "),
                array(
                    'norec_sk' => $item->norec_sk,
                )
            );

            $result[] = array(
                'norec_sk' => $item->norec_sk,
                'nokirim' => $item->nokirim,
                'pegawaikirim' => $item->pegawaikirim,
                'ruangantujuan' => $item->ruangantujuan,
                'tglkirim' => $item->tglkirim,
                'ruanganasal' => $item->ruanganasal,
                'objectruangantujuanfk' => $item->objectruangantujuanfk,
                'objectruanganasalfk' =>  $item->objectruanganasalfk,
                'objectpegawaipengirimfk' => $item->objectpegawaipengirimfk,
                'noregistrasi' => $item->noregistrasi,
                'nocm' => $item->nocm,
                'namapasien' => $item->namapasien,
                'keterangan' =>$item->keteranganlainnyakirim,
                'qtyproduk' =>$item->qtyproduk,
                'details' => $details,
                'details2' => $details2,
            );
        }

        $dataResult=array(
            'message' =>  'inhuman',
            'data' =>  $result,

        );
        return $this->respond($dataResult);
    }
    public function updateOrderPelayananGizi(Request $request) {
        $detLogin =$request->all();
        DB::beginTransaction();
        try{

            if ($request['norec_op'] != 'undefined'){
                $update = OrderPelayanan::where('norec' , $request['norec_op'])
                    ->update([
                            'tglpelayanan' => $request['tglpelayanan'],
                            'objectjeniswaktufk' => $request['objectjeniswaktufk'],
                            'objectprodukfk' => $request['objectprodukfk'],
                            'qtyproduk' => $request['qtyproduk'],
                            'objectjenisdietfk' => $request['objectjenisdietfk'],
                            'objectkategorydietfk' => $request['objectkategorydietfk'],
                        ]
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
                "status" => 201,
                "message" => $transMessage,
                //                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Update Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteKirimMenu(Request $request) {
        DB::beginTransaction();
        try{
//            $delKP = KirimProduk::where('nokirimfk',$request['norec'])->delete();
            $hapus = StrukKirim::where('norec',$request['norec'])->update(
                ['statusenabled' => false]
            );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses Batal Kirim";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Gagal Batal Kirim";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarOrderGiziDetail(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::table('strukorder_t as so')
            ->join ('pegawai_m as pg','pg.id','=','so.objectpegawaiorderfk')
            ->join('ruangan_m as ru','ru.id','=','so.objectruangantujuanfk')
            ->select('so.norec', 'so.noorder','so.qtyproduk','so.objectruangantujuanfk','ru.namaruangan as ruangantujuan',
            'so.keteranganorder','so.tglorder','so.tglpelayananawal','so.objectpegawaiorderfk','pg.namalengkap')
            ->where('so.objectkelompoktransaksifk',8)
            ->where('so.kdprofile', $kdProfile)
            ->where('so.statusenabled',true);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('so.tglpelayananawal','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('so.tglpelayananawal','<=', $tgl);
        }
  
        if(isset($request['pegId']) && $request['pegId']!="" && $request['pegId']!="undefined"){
            $data = $data->where('so.objectpegawaiorderfk','=', $request['pegId']);
        }
        
        if(isset($request['jenisDietId']) && $request['jenisDietId']!="" && $request['jenisDietId']!="undefined"){
            $data = $data->where('op.objectjenisdietfk','=', $request['jenisDietId']);
        }
        if(isset($request['jenisWaktuId']) && $request['jenisWaktuId']!="" && $request['jenisWaktuId']!="undefined"){
            $data = $data->where('op.objectjeniswaktufk','=', $request['jenisWaktuId']);
        }

        if(isset($request['noorder']) && $request['noorder']!="" && $request['noorder']!="undefined"){
            $data = $data->where('so.noorder','ilike','%'. $request['noorder'].'%');
        }

        if(isset($request['noreg']) && $request['noreg']!="" && $request['noreg']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike', '%'.$request['noreg'].'%');
        }
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined"){
            $data= $data->where('ps.nocm','ilike', '%'.$request['norm'].'%');
        }
        if(isset($request['pengorderId']) && $request['pengorderId']!="" && $request['pengorderId']!="undefined"){
            $data = $data->where('pg.id','=', $request['pengorderId']);
        }

        if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
            $data = $data->where('ps.namapasien','ilike', '%'.$request['nama'].'%');
        }
        if(isset($request['jmlRows']) && $request['jmlRows']!="" && $request['jmlRows']!="undefined"){
            $data = $data->take($request['jmlRows']);
        }
        $data = $data->orderBy('so.noorder');
        $data = $data->get();
        // return $this->respond(  $data );
        $datas =[];
        foreach ($data as $item){
            $norec= $item->norec;
            $deptId = '';
            $ruangId ='';
            if(isset($request['deptId']) && $request['deptId']!="" && $request['deptId']!="undefined"){
                $deptId = ' and ru.objectdepartemenfk ='. $request['deptId'];
            }

            if(isset($request['ruangId']) && $request['ruangId']!="" && $request['ruangId']!="undefined"){
                $ruangId = ' and ru.id='.$request['ruangId'];
            }
            $detail = DB::select(DB::raw("select op.noorderfk, op.objectjenisdietfk,
                --jd.jenisdiet,
                    op.objectjeniswaktufk,jw.jeniswaktu,op.arrjenisdiet,
                    op.objectkategorydietfk,kt.kategorydiet,op.keteranganlainnya,
                    op.statusgizi as jenisorder,op.qtyprodukinuse as cc,op.jumlah as volume,op.objectkelasfk,kls.namakelas,
                    ru.namaruangan as ruangorder,op.objectruanganfk,ps.nocm,pd.noregistrasi,ps.namapasien,sk.nokirim,pd.norec as norec_pd,ps.id as nocmfk,op.norec as norec_op
                    from orderpelayanan_t as op
                    --  join jenisdiet_m as jd on jd.id=op.objectjenisdietfk
                  join jeniswaktu_m as jw on jw.id=op.objectjeniswaktufk
                    left join kategorydiet_m as kt on kt.id=op.objectkategorydietfk
                     join pasiendaftar_t as pd on pd.norec=op.noregistrasifk
                     join pasien_m as ps on ps.id=pd.nocmfk
                    join kelas_m as kls on kls.id=op.objectkelasfk
                    join ruangan_m as ru on ru.id=op.objectruanganfk
                    left join strukkirim_t as sk on sk.norec=op.strukkirimfk
                    where op.noorderfk='$norec' AND op.kdprofile = $kdProfile
                    $deptId
                    $ruangId
                    "));
            if(count($detail) > 0){
                 $datas [] =array(
                    'norec'=>  $item->norec,
                    'noorder'=>  $item->noorder,
                    'tglorder'=>$item->tglorder,
                    'objectruangantujuanfk'=>  $item->objectruangantujuanfk,
                    'ruangantujuan'=>  $item->ruangantujuan,
                    'keteranganorder'=>  $item->keteranganorder,
                    'tglmenu'=>  $item->tglpelayananawal,
                    'objectpegawaiorderfk'=>  $item->objectpegawaiorderfk,
                    'pengorder'=>  $item->namalengkap,
                    'details'=> $detail
                );
            }
        }

        $dataResult=array(
            'message' =>  'inhuman',
            'data' =>  $datas,

        );
        return $this->respond($dataResult);
    }
    public function hapusOrderGzi(Request $request) {

        DB::beginTransaction();
        try{
            StrukOrder::where('norec',$request['norec'])->update(
                [
                    'statusenabled' => false
                ]
            );
            $transStatus = 'true';
        } catch (Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Order ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus Order Pelayana";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "nokirim" => $dataSO,//$noResep,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function hapusOrderGziPeritem(Request $request) {

        DB::beginTransaction();
        try{
            foreach ($request['orderpelayanan'] as $item){
                OrderPelayanan::where('norec',$item['norec_op'])->delete();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Order ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus Order Pelayanan";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "nokirim" => $dataSO,//$noResep,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function updateOrderGizi(Request $request) {

        DB::beginTransaction();
        try{

            StrukOrder::where('norec',$request['strukorder']['norec'])->update(
                [
                    'tglpelayananawal' => $request['strukorder']['tglmenu'],
                ]
            );
            OrderPelayanan::where('norec',$request['orderpelayanan']['norec'])->update(
                [
                    'objectjeniswaktufk' => $request['orderpelayanan']['objectjeniswaktufk'],
                    'objectkategorydietfk' => $request['orderpelayanan']['objectkategorydietfk'],
                    'keteranganlainnya' => $request['orderpelayanan']['keterangan'],
                    // 'objectjenisdietfk' => $request['orderpelayanan']['objectjenisdietfk'],
                     'arrjenisdiet' => $request['orderpelayanan']['objectjenisdietfk'],
                ]
            );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Order ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Simpan Order Pelayanan Gagal" ;
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "nokirim" => $dataSO,//$noResep,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

}
