<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 10/26/2020
 * Time: 3:50 PM
 */


namespace App\Http\Controllers\Sterilisasi;

use App\Http\Controllers\ApiController;
use App\Master\KelompokAlat;
use App\Master\KelompokAlatDetail;
use App\Master\PaketObat;
use App\Master\PaketObatDetail;
use App\Traits\Valet;
use App\Transaksi\KartuStok;
use App\Transaksi\KirimProduk;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\StrukKirim;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\StrukPelayananDetail;
use Illuminate\Http\Request;
use App\Transaksi\StrukPlanning;
use DB;

class SterilisasiController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getComboSteril(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap,lu.objectkelompokuserfk
                from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        $data = \DB::table('loginuser_s as lu')
            ->JOIN('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('pg.id','pg.namalengkap')
            ->where('lu.kdprofile',$idProfile)
            ->where('lu.id',$request['userData']['id'])
            ->get();

        $dataSumberDana = \DB::table('asalproduk_m as lu')
            ->select('lu.id','lu.asalproduk as asalProduk')
            ->where('lu.statusenabled', true)
            ->get();

        $dataRuangCssd = \DB::table('ruangan_m as lu')
            ->select('lu.id','lu.namaruangan')
            ->where('lu.id', 66)
            ->where('lu.statusenabled', true)
            ->get();

        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select('pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled',true)
            ->whereIn('djp.id',[1476])
            ->groupBy('pr.id','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataKelompok = KelompokAlat::where('kdprofile', $kdProfile)
                        ->where('statusenabled', true)
                        ->select('id','namakelompokalat')
                        ->get();

        $result = array(
            'pegawai' => $data,
            'datalogin' => $dataPegawaiUser,
            'sumberdana' => $dataSumberDana,
            'produk' => $dataProduk,
            'kelompokalat' => $dataKelompok,
            'ruangancssd' => $dataRuangCssd,
            'by' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getDataStokInsSteril(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdSirs1 = $request['KdSirs1'];
        $kdSirs2= $request['KdSirs2'];
        $dataLogin=$request->all();
        $data = \DB::table('stokprodukdetail_t as spd')
            ->JOIN('strukpelayanan_t as sp','sp.norec','=','spd.nostrukterimafk')
            ->JOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
            ->JOIN('ruangan_m as ru','ru.id','=','spd.objectruanganfk')
//            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
//            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->JOIN('asalproduk_m as ap','ap.id','=','spd.objectasalprodukfk')
            ->JOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select('sp.nostruk as noterima','spd.objectprodukfk','pr.kdproduk as kdsirs','pr.namaproduk','ap.asalproduk',DB::raw('sum(spd.qtyproduk) as qtyproduk'),
                'ss.satuanstandar','spd.tglkadaluarsa','spd.nobatch','spd.harganetto1',
//                'spd.norec as norec_spd',
                'spd.nostrukterimafk','ru.namaruangan')
            ->where('spd.statusenabled', true)
            ->where('pr.statusenabled', true)
            ->where('pr.objectdetailjenisprodukfk', 1476)
            ->where('spd.qtyproduk','>', 0)
            ->where('spd.kdprofile', $idProfile)
            ->groupBy('sp.nostruk','spd.objectprodukfk','pr.kdproduk','pr.namaproduk','ap.asalproduk',
                      'ss.satuanstandar','spd.tglkadaluarsa','spd.nobatch','spd.harganetto1',
                      'spd.nostrukterimafk','ru.namaruangan');
        if(isset($request['kelompokprodukid']) && $request['kelompokprodukid']!="" && $request['kelompokprodukid']!="undefined"){
            $data = $data->where('jp.objectkelompokprodukfk','=', $request['kelompokprodukid']);
        }
//        if(isset($request['jeniskprodukid']) && $request['jeniskprodukid']!="" && $request['jeniskprodukid']!="undefined"){
//            $data = $data->where('djp.objectjenisprodukfk','=', $request['jeniskprodukid']);
//        }
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
                $data = $data->whereRaw = (" pr.kdproduk ilike '".$request['KdSirs2']."%'");
            }elseif ($request['KdSirs1'] &&  $request['KdSirs1']!= '' && $request['KdSirs2'] == '' ||  $request['KdSirs2'] == null){
                $data = $data->whereRaw = (" pr.kdproduk ilike '".$request['KdSirs1']."%'");
            }
        }
        if (isset($request['jmlRows']) && $request['jmlRows']!="" && $request['jmlRows']!="undefined"){
            $data=$data->take($request['jmlRows']);
        }
        $data = $data->get();
        $data2=[];

        $dataOrder = \DB::table('strukorder_t as so')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->leftJOIN('strukkirim_t as sk','sk.noorderfk','=','so.norec')
            ->select('so.objectruanganfk','op.objectprodukfk',DB::raw('sum(op.qtyproduk) as qty'))
            ->where('so.kdprofile', $idProfile);

        if(isset($request['ruanganfk']) && $request['ruanganfk']!="" && $request['ruanganfk']!="undefined"){
            $dataOrder = $dataOrder->where('so.objectruanganfk','=', $request['ruanganfk']);
        }
//        $dataOrder = $dataOrder->where('so.objectruanganfk','=', $request['ruanganfk']);
        $dataOrder = $dataOrder->whereNull('sk.noorderfk')
            ->groupBy('so.objectruanganfk','op.objectprodukfk');
        $dataOrder = $dataOrder->get();
        foreach ($data as $item){
            $data2[] = array(
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
//                'norec_spd' => $item->norec_spd,
                'nostrukterimafk' => $item->nostrukterimafk,
                'namaruangan' => $item->namaruangan,
            );
        }
        $result= array(
            'detail' => $data2,
            'detailorder' => $dataOrder,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getComboTerimaBarang(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();

        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where pg.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $dataRuanganAll = \DB::table('ruangan_m as lu')
            ->select('lu.id','lu.namaruangan')
            ->where('lu.statusenabled',true)
            ->where('lu.kdprofile', $idProfile)
            ->get();

        $dataSumberDana = \DB::table('asalproduk_m as lu')
            ->select('lu.id','lu.asalproduk as asalProduk')
            ->where('lu.statusenabled', true)
            ->get();

        $dataJabatan = \DB::table('jabatan_m as kp')
            ->select('kp.id','kp.namajabatan')
            ->where('kp.statusenabled',true)
            ->orderBy('kp.namajabatan')
            ->get();

        $dataProduk = \DB::table('produk_m as pr')
//            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
//            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.kdproduk as kdsirs','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.statusenabled',true)
            ->where('spd.statusenabled',true)
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.objectdetailjenisprodukfk', 1476)
//            ->where('jp.id',97)
            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','pr.kdproduk','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

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
                'kdsirs' => $item->kdsirs,
                'kdproduk' => $item->kdsirs,
                'ssid' =>   $item->ssid,
                'satuanstandar' =>   $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
            );
        }

        $dataProdukBR = \DB::table('produk_m as pr')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select('pr.id','pr.kdproduk as kdsirs','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.statusenabled',true)
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.objectdetailjenisprodukfk', 1476)
            ->groupBy('pr.id','pr.kdproduk','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();
        $dataKonversiProdukBR = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kdprofile', $idProfile)
            ->where('ks.statusenabled',true)
            ->get();
        $dataProdukResultBR=[];
        foreach ($dataProdukBR as $itemBR){
            $satuanKonversiBR=[];
            foreach ($dataKonversiProdukBR  as $item2BR){
                if ($itemBR->id == $item2BR->objekprodukfk){
                    $satuanKonversiBR[] =array(
                        'ssid' =>   $item2BR->satuanstandar_tujuan,
                        'satuanstandar' =>   $item2BR->satuanstandar2,
                        'nilaikonversi' =>   $item2BR->nilaikonversi,
                    );
                }
            }

            $dataProdukResultBR[]=array(
                'id' =>   $itemBR->id,
                'namaproduk' =>   $itemBR->namaproduk,
                'kdsirs' => $itemBR->kdsirs,
                'kdproduk' => $itemBR->kdsirs,
                'ssid' =>   $itemBR->ssid,
                'satuanstandar' =>   $itemBR->satuanstandar,
                'konversisatuan' => $satuanKonversiBR,
            );
        }

        $result = array(
            'asalproduk' => $dataSumberDana,
            'ruanganall' => $dataRuanganAll,
            'jabatan' => $dataJabatan,
            'detaillogin' => $dataPegawaiUser,
            'produk' => $dataProdukResult,
            'produkbaru' => $dataProdukResultBR,
            'by' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarDistribusiBarangSteril(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->where('mlu.kdprofile', $idProfile)
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }
        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.statuskirim','sp.noorderfk',
                'ru.id as ruasalid','ru.namaruangan as ruanganasal','ru2.id as rutujuanid','ru2.namaruangan as ruangantujuan','sp.keteranganlainnyakirim',
                'sp.statusbersih','sp.statussteril as statussterilisasi',
                DB::raw('count(kp.objectprodukfk) as jmlitem')
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim','sp.statuskirim','sp.statusbersih','sp.statussteril');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ILIKE','%'. $request['nokirim'].'%');
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('sp.objectruangantujuanfk','=', $request['ruangantujuanfk']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('kp.objectprodukfk','=', $request['produkfk']);
        }
//        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',34);
        $data = $data->wherein('sp.objectruanganasalfk',$strRuangan);
        $data = $data->where('sp.noregistrasifk','=',0);
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
            $results[] = array(
                'status' => 'Kirim Barang',
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
                'details' => $details,
                'statussteril' => $item->statuskirim,
                'statusbersih' => $item->statusbersih,
                'statussterilisasi' => $item->statussterilisasi,
            );
        }
        $data2 = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap',
                'sp.statuskirim','sp.statusbersih','sp.statussteril as statussterilisasi',
                'ru.namaruangan as ruanganasal','ru.id as ruasalid','ru2.namaruangan as ruangantujuan','ru2.id as rutujuanid','sp.keteranganlainnyakirim',
                DB::raw('count(kp.objectprodukfk) as jmlitem')
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim','sp.statuskirim','sp.statusbersih','sp.statussteril');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data2 = $data2->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data2 = $data2->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data2 = $data2->where('sp.nokirim','ILIKE','%'. $request['nokirim']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data2 = $data2->where('sp.objectruangantujuanfk','=', $request['ruangantujuanfk']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data2 = $data2->where('kp.objectprodukfk','=', $request['produkfk']);
        }
//        $data2 = $data2->distinct();
        $data2 = $data2->where('sp.statusenabled',true);
        $data2 = $data2->where('sp.objectkelompoktransaksifk',34);
        $data2 = $data2->wherein('sp.objectruangantujuanfk',$strRuangan);
        $data2 = $data2->orderBy('sp.nokirim');
        $data2 = $data2->get();

//        $results =array();
        foreach ($data2 as $item){
            $details = DB::select(DB::raw("
                     select  pr.id as kdproduk,pr.kdproduk as kdsirs,pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.objectprodukfk
                     from kirimproduk_t as spd
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nokirimfk=:norec and spd.qtyproduk <> 0"),
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
            $results[] = array(
                'status' => 'Terima Barang',
                'tglstruk' => $item->tglkirim,
                'nostruk' => $item->nokirim,
                'jeniskirim' => $jeniskirim,
                'norec' => $item->norec,
                'jenispermintaanfk' => $item->jenispermintaanfk,
                'ruasalid'=> $item->ruasalid,
                'namaruanganasal' => $item->ruanganasal,
                'rutujuanid'=> $item->rutujuanid,
                'namaruangantujuan' => $item->ruangantujuan,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganlainnyakirim,
                'jmlitem' => $item->jmlitem,
                'details' => $details,
                'statussteril' => $item->statuskirim,
                'statusbersih' => $item->statusbersih,
                'statussterilisasi' => $item->statussterilisasi,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataPegawaiUser,//$dataLogin,
            'message' => 'as@epic',
            'str' => $strRuangan,
        );

        return $this->respond($result);
    }
    public function UpdateStatusSterilisasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {

            StrukKirim::where('norec', $request['strukkirimfk'])
                ->where('kdprofile',$idProfile)
                ->update([
                        'statussteril' => $request['status']]
                );


            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
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

    public function saveRegistrasiBarangSteril(Request $request){
        $idProfile = (int) $this->getDataKdProfile($request);
        $req = $request;
        $noKirim = $this->generateCodeBySeqTable(new StrukPelayanan(), 'noregistrasialatcssd', 14, 'RSTR-' . $this->getDateTime()->format('ym'), $idProfile);
        if ($noKirim == '') {
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            \DB::rollBack();
            $result = array(
                "status" => 400,
                "NOKIRIM" => $noKirim,
                "message" => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }
        DB::beginTransaction();
        try{
            if ($request['strukkirim']['noreckirim'] == ""){
                $SP = new StrukPelayanan();
                $norecSP = $SP->generateNewId();
                $noStruk = $noKirim;
                $SP->norec = $norecSP;
                $SP->kdprofile = $idProfile;
                $SP->statusenabled = true;
                $SP->nostruk = $noStruk;
                $SP->objectkelompoktransaksifk = 267;
            }else{
//                $dataKS =  KartuStok::where('keterangan',  'Penerimaan Barang Suplier. No Terima. ' . $req['struk']['noterima'] . ' Faktur No.' . $req['struk']['nofaktur'] . ' ' . $req['struk']['namarekanan'])
//                    ->where('kdprofile', $idProfile)
//                    ->update([
//                        'flagfk' => null
//                    ]);
                //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                $dataKembaliStok = \DB::select(DB::raw("
                            select sp.norec,spd.qtyproduk,spd.hasilkonversi,sp.objectruanganfk,spd.objectprodukfk,sp.nostruk
                            from strukpelayanandetail_t as spd
                            INNER JOIN strukpelayanan_t sp on sp.norec=spd.nostrukfk
                            where sp.kdprofile = $idProfile and sp.norec=:norec"),
                    array(
                        'norec' => $request['strukkirim']['noreckirim'],
                    )
                );
                $TambahStok = 0;
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
                            $tglnow = date('Y-m-d H:i:s');
                            $tglUbah = date('Y-m-d H:i:s', strtotime('-1 minutes', strtotime($tglnow)));
                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $idProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                            $newKS->keterangan = 'Ubah Registrasi Alat CSSD No.  ' . $item5->nostruk;
                            $newKS->produkfk = $item5->objectprodukfk;
                            $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
                            $newKS->saldoawal = (float)$saldoAwal - (float)$TambahStok;
                            $newKS->status = 0;
                            $newKS->tglinput = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->tglkejadian = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->nostrukterimafk = $request['strukkirim']['noreckirim'];
                            $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                            $newKS->tabletransaksi = 'strukpelayanan_t';
                            $newKS->save();

                            //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                            $SP = StrukPelayanan::where('norec', $request['strukkirim']['noreckirim'])->first();
                            $noStruk = $SP->nostruk;
                            //TODO: betulkan ubah penerimaan masih salah
                            //ubah penerimaan harusnya brg yg di terima hrs di keluarkan dulu
                            //tpi ini barang sudah terpakai, pengurang stok hanya delete spd dengan brang yg sudah kepake
                            $delSPD = StokProdukDetail::where('nostrukterimafk', $request['strukkirim']['noreckirim'])
                                ->where('kdprofile', $idProfile)
                                ->delete();
                            $delSPD = StrukPelayananDetail::where('nostrukfk', $request['strukkirim']['noreckirim'])
                                ->where('kdprofile', $idProfile)
                                ->delete();
                        } else {

                            $hasil = 0;
                            $penamBahan = (float)$saldoAwal - (float)$TambahStok;
                            if ($penamBahan < 0) {
                                $hasil = 0;
                            } else {
                                $hasil = (float)$saldoAwal - (float)$TambahStok;
                            }

                            $tglnow1 = date('Y-m-d H:i:s');
                            $tglUbah1 = date('Y-m-d H:i:s', strtotime('-1 minutes', strtotime($tglnow1)));

                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $idProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = $TambahStok;
                            $newKS->keterangan = 'Ubah Registrasi Alat CSSD No. ' . $item5->nostruk;
                            $newKS->produkfk = $item5->objectprodukfk;
                            $newKS->ruanganfk = $item5->objectruanganfk;
                            $newKS->saldoawal = $hasil;
                            $newKS->status = 0;
                            $newKS->tglinput = $tglUbah1;
                            $newKS->tglkejadian = $tglUbah1;
                            $newKS->nostrukterimafk = $request['strukkirim']['noreckirim'];
                            $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                            $newKS->tabletransaksi = 'strukpelayanan_t';
                            $newKS->save();

                            //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                            $SP = StrukPelayanan::where('norec', $request['strukkirim']['noreckirim'])->first();
                            $noStruk = $SP->nostruk;
                            //TODO: betulkan ubah penerimaan masih salah
                            //ubah penerimaan harusnya brg yg di terima hrs di keluarkan dulu
                            //tpi ini barang sudah terpakai, pengurang stok hanya delete spd dengan brang yg sudah kepake
                            $delSPD = StokProdukDetail::where('nostrukterimafk', $request['strukkirim']['noreckirim'])
                                ->where('kdprofile', $idProfile)
                                ->delete();
                            $delSPD = StrukPelayananDetail::where('nostrukfk', $request['strukkirim']['noreckirim'])
                                ->where('kdprofile', $idProfile)
                                ->delete();
                        }
                    }
                }
            }

            $SP->tglstruk = $req['strukkirim']['tglkirim'];
            $SP->objectpegawaipenanggungjawabfk = $req['strukkirim']['objectpegawaipengirimfk'];
            $SP->qtyproduk = $req['strukkirim']['qtyproduk'];
            $SP->objectruanganfk = $req['strukkirim']['objectruangantujuanfk'];
            $SP->totalharusdibayar = 0;
            $SP->totalppn = 0;
            $SP->totaldiscount = 0;
            $SP->totalhargasatuan = 0;
            $SP->save();
            $norecSpt=$SP->norec;
            $noStrukT=$SP->nostruk;

            foreach ($req['details'] as $item) {
                $qtyJumlah = (float)$item['jumlah'] * (float)$item['nilaikonversi'];
                $SPD = new StrukPelayananDetail();
                $norecKS = $SPD->generateNewId();
                $SPD->norec = $norecKS;
                $SPD->kdprofile = $idProfile;
                $SPD->statusenabled = true;
                $SPD->nostrukfk = $SP->norec;
                $SPD->objectasalprodukfk = 11;
                $SPD->objectprodukfk = $item['produkfk'];
                $SPD->objectruanganfk = $req['strukkirim']['objectruangantujuanfk'];
                $SPD->objectruanganstokfk = $req['strukkirim']['objectruangantujuanfk'];
                $SPD->objectsatuanstandarfk = $item['satuanstandarfk'];
                $SPD->hargadiscount = 0;
                $SPD->hargadiscountgive = 0;
                $SPD->hargadiscountsave = 0;
                $SPD->harganetto = 0;
                $SPD->hargapph = 0;
                $SPD->hargappn =  0;
                $SPD->hargasatuan =  0;
                $SPD->hasilkonversi = $item['nilaikonversi'];
                $SPD->namaproduk = $item['namaproduk'];
//                $SPD->keteranganlainnya = $item['keterangan'];
                $SPD->hargasatuandijamin = 0;
                $SPD->hargasatuanppenjamin = 0;
                $SPD->hargatambahan = 0;
                $SPD->hargasatuanpprofile = 0;
                $SPD->isonsiteservice = 0;
                $SPD->kdpenjaminpasien = 0;
                $SPD->persendiscount = 0;
                $SPD->persenppn = 0;
                $SPD->qtyproduk = $qtyJumlah;
                $SPD->qtyprodukoutext = 0;
                $SPD->qtyprodukoutint = 0;
                $SPD->qtyprodukretur = 0;
                $SPD->satuan = '-';//$item['satuanstandar'];;
                $SPD->satuanstandar = $item['satuanviewfk'];
                $SPD->tglpelayanan = $req['strukkirim']['tglkirim'];
                $SPD->is_terbayar = 0;
                $SPD->linetotal = 0;
                $SPD->nobatch = "STERIL";
                $SPD->save();

                //## StokProdukDetail
                $StokPD = new StokProdukDetail();
                $norecStokPD = $StokPD->generateNewId();
                $StokPD->norec = $norecKS;
                $StokPD->kdprofile = $idProfile;
                $StokPD->statusenabled = true;
                $StokPD->objectasalprodukfk = 11;//$request['struk']['asalproduk'];//$item['asalprodukfk'];
                $StokPD->hargadiscount = 0;
                $StokPD->harganetto1 = 0;
                $StokPD->harganetto2 = 0;
                $StokPD->persendiscount = 0;
                $StokPD->objectprodukfk = $item['produkfk'];
                $StokPD->qtyproduk = $qtyJumlah;
                $StokPD->qtyprodukonhand = 0;
                $StokPD->qtyprodukoutext = 0;
                $StokPD->qtyprodukoutint = 0;
                $StokPD->objectruanganfk = $req['strukkirim']['objectruangantujuanfk'];
                $StokPD->nostrukterimafk = $SP->norec;
                $StokPD->nobatch = "STERIL";
                $StokPD->objectstrukpelayanandetail = $SPD->norec;
                $StokPD->tglpelayanan = date('Y-m-d H:i:s', strtotime($req['strukkirim']['tglkirim']));
                $StokPD->save();

                $dataSaldoAwal = \DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                  where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $req['strukkirim']['objectruangantujuanfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );

                foreach ($dataSaldoAwal as $items) {
                    $saldoAwal = (float)$items->qty;
                }
                if ($saldoAwal == 0){
                    $saldoAwal = $qtyJumlah;
                }

                //## KartuStok
                $newKS = new KartuStok();
                $norecKS = $newKS->generateNewId();
                $newKS->norec = $norecKS;
                $newKS->kdprofile = $idProfile;
                $newKS->statusenabled = true;
                $newKS->jumlah = $qtyJumlah;
                $newKS->keterangan = 'Registrasi Alat CSSD. ' . $noStruk;
                $newKS->produkfk = $item['produkfk'];
                $newKS->ruanganfk = $req['strukkirim']['objectruangantujuanfk'];
                $newKS->saldoawal = (float)$saldoAwal;
                $newKS->status = 1;
                $newKS->tglinput = date('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $SP->norec;
                $newKS->norectransaksi = $SP->norec;
                $newKS->tabletransaksi = 'strukpelayanan_t';
                $newKS->flagfk = 1;
                $newKS->save();
            }


       $transStatus = 'true';
       } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "GAGAL";
       }

        if ($transStatus == 'true') {
            $transMessage = "Registrasi Alat Berhasil";
            \DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "norec" => $norecSpt,
                "nostruk" => $noStrukT,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Registrasi Alat Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataRegistrasiBarangSteril(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->where('mlu.kdprofile', $idProfile)
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }
        $data = \DB::table('strukpelayanan_t AS sp')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipenanggungjawabfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->select(
                DB::raw(
                    "sp.norec,sp.nostruk,sp.tglstruk,
                           sp.objectruanganfk,ru.namaruangan,
                           sp.qtyproduk AS jumlah,
                           sp.objectpegawaipenanggungjawabfk,pg.namalengkap"
                )
            )
            ->where('sp.kdprofile', $idProfile)
            ->where('sp.statusenabled', true)
            ->where('sp.objectkelompoktransaksifk', 267);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglstruk','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglstruk','<=', $tgl);
        }
        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('sp.nostruk','ILIKE','%'. $request['nostruk'].'%');
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru.namaruangan','ILIKE', '%'.$request['ruangantujuanfk'].'%');
        }
        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norec']);
        }
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                    select  pr.id as kdproduk,pr.kdproduk as kdsirs,pr.namaproduk,
                    spd.objectsatuanstandarfk,ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.objectprodukfk,
                    spd.qtyproduk as jumlah,pr.id as produkfk
                    from strukpelayanandetail_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nostrukfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'tglstruk' => $item->tglstruk,
                'nostruk' => $item->nostruk,
                'norec' => $item->norec,
                'objectruanganfk'=> $item->objectruanganfk,
                'namaruangan' => $item->namaruangan,
                'petugas' => $item->namalengkap,
                'jmlitem' => $item->jumlah,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataPegawaiUser,
            'message' => 'ea@epic',
//            'str' => $strRuangan,
        );

        return $this->respond($result);
    }

    public function DeleteRegistrasiAlatCssd(Request $request){
        \DB::beginTransaction();
        $transMessage = '';
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            $dataKembaliStok = \DB::select(DB::raw("
                            select sp.norec,spd.qtyproduk,spd.hasilkonversi,
                                   sp.objectruanganfk,spd.objectprodukfk,
                                   sp.nostruk
                            from strukpelayanandetail_t as spd
                            INNER JOIN strukpelayanan_t sp on sp.norec=spd.nostrukfk
                            where spd.kdprofile = $idProfile and sp.norec=:norec"),
                array(
                    'norec' => $request['nostruk'],
                )
            );

            $dataStokSudahKirim = StokProdukDetail::where('nostrukterimafk', $request['nostruk'])
                ->where('kdprofile', $idProfile)
                ->whereNotIn('objectruanganfk', [$dataKembaliStok[0]->objectruanganfk])
                ->where('qtyproduk', '>', 0)
                ->get();
            if (count($dataStokSudahKirim) == 0) {
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

                    $dataPenerimaan = \DB::table('strukpelayanan_t as sr')
                        ->leftJoin('rekanan_m as rkn','rkn.id','=','sr.objectrekananfk')
                        ->select(DB::raw("sr.nostruk,sr.nofaktur,rkn.namarekanan"))
                        ->where('sr.kdprofile',$idProfile)
                        ->where('sr.norec',$request['nostruk'])
                        ->first();

                    $dataKS = KartuStok::where('keterangan',  'Registrasi Alat CSSD. ' . $dataPenerimaan->nostruk )
                        ->where('kdprofile', $idProfile)
                        ->update([
                            'flagfk' => null
                        ]);

                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = $TambahStok;
                    $newKS->keterangan = 'Batal Registrasi Alat CSSD No. ' . $item5->nostruk;
                    $newKS->produkfk = $item5->objectprodukfk;
                    $newKS->ruanganfk = $item5->objectruanganfk;
                    $newKS->saldoawal = (float)$saldoAwal - (float)$TambahStok;
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk = $request['nostruk'];
                    $newKS->save();
                }
                $SP = StrukPelayanan::where('norec', $request['nostruk'])->where('kdprofile', $idProfile)->first();
                $SP->statusenabled = false;
                $SP->save();

                $delSPD = StokProdukDetail::where('nostrukterimafk', $request['nostruk'])
                    ->where('kdprofile', $idProfile)
                    ->delete();

                $kirim = KartuStok::where('ruanganfk', $item5->objectruanganfk)
                    ->where('kdprofile', $idProfile)
                    ->where('produkfk', $item5->objectprodukfk)
                    ->get();

                $kartuStok[] = $kirim;

                $dataSTOKDETAIL[] = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $item5->objectruanganfk,
                        'produkfk' => $item5->objectprodukfk,
                    )
                );

                $stokdetail[] = $dataSTOKDETAIL;
                $transStatus = 'true';
                $transMessage = "Hapus Registrasi";
            } else {
                $transStatus = 'false';
                $transMessage = "Sudah ada distribusi, tidak dapat di batalkan!!";
            }

        } catch (\Exception $e) {
            $transStatus = 'false';
        };
        if ($transStatus == 'true') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $SP,
                "as" => 'ea@epic',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveKelompokAlat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            if($request['idPaket'] == '') {
                $data = new KelompokAlat();
                $newId = KelompokAlat::max('id') + 1;
                $data->id = $newId;
                $data->kdkelompokalat = $newId;
                $data->norec = $data->generateNewId();
                $data->kdprofile = $kdProfile;
                $data->statusenabled = true;
            }else{
                $data = KelompokAlat::where('id',$request['idPaket'])->where('kdprofile', $kdProfile)->first();
                $dataDetail = KelompokAlatDetail::where('objectkelompokalatfk', $request['idPaket'])->where('kdprofile', $kdProfile)->delete();
            }
            $data->namakelompokalat = $request['namakelompok'];
            $data->reportdisplay = $request['namakelompok'];
            $data->namaexternal = $request['namakelompok'];
            $data->save();
            $idPaket = $data->id;

            foreach ( $request['details'] as $item){
                $map = new KelompokAlatDetail();
                $map->id = KelompokAlatDetail::max('id') + 1;
                $map->kdprofile = $kdProfile;//12;
                $map->statusenabled = true;
                $map->norec =  $data->generateNewId();
                $map->objectkelompokalatfk = $idPaket;
                $map->produkfk = $item['produkfk'];
                $map->qty = $item['jumlah'];
                $map->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();

            $result = array(
                'status' => 201,
                'data' => $data,
                'detail' => $map,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataKelompokAlat (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $result=[];
        $data = \DB::table('kelompokalat_m as sp')
            ->select('sp.id as kelompokAlatId','sp.namakelompokalat')
            ->where('sp.kdprofile', $kdProfile)
            ->where('sp.statusenabled', true);

        if(isset($request['paketId']) && $request['paketId'] !='' ){
            $data = $data->where('sp.id',$request['paketId']);
        }
        if(isset($request['namaPaket']) && $request['namaPaket'] !='' ){
            $data = $data->where('sp.namakelompokalat','ilike','%'.$request['namaPaket'].'%');
        }
        $data = $data->get();
        foreach ($data as $item) {
            $details = \DB::select(DB::raw("SELECT pkd.*,pro.namaproduk,
                         pro.objectsatuanstandarfk,ss.satuanstandar,
                         pkd.qty as jumlah
                    FROM kelompokalatdetail_t as pkd
                    INNER JOIN produk_m As pro ON pro.id = pkd.produkfk
                    LEFT JOIN satuanstandar_m AS ss ON ss.id = pro.objectsatuanstandarfk                   
                    where pkd.kdprofile = $kdProfile and pkd.objectkelompokalatfk=:norec"),
                array(
                    'norec' => $item->kelompokAlatId,
                )
            );
            $result[] = array(
                'kelompokAlatId' => $item->kelompokAlatId,
                'namakelompokalat' => $item->namakelompokalat,
                'details' => $details,
            );
        }
        $result = array(
            'data' => $result,
            'as' => 'ea@epic'
        );
        return $this->respond($result);
    }

    public function deleteKelompokAlat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {

            $data = KelompokAlat::where('id',$request['idPaket'])->where('kdprofile', $kdProfile)
                ->update(['statusenabled' => 'f',]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();

            $result = array(
                'status' => 201,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function UpdateStatusPemakaianAlat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {

            StrukKirim::where('norec', $request['strukkirimfk'])
                ->where('kdprofile',$idProfile)
                ->update([
                        'statuskirim' => $request['status']]
                );


            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
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

    public function UpdateStatusBersih(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {

            StrukKirim::where('norec', $request['strukkirimfk'])
                ->where('kdprofile',$idProfile)
                ->update([
                        'statusbersih' => $request['status']]
                );


            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
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

    public function getDaftarOrderAlatSteril(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawaiUser = \DB::select(\Illuminate\Support\Facades\DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where pg.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.kdprofile', $idProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }
        $data = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as op','op.strukorderfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','sp.jenispermintaanfk','pg.namalengkap',
                'ru.namaruangan as ruanganasal','ru2.namaruangan as ruangantujuan','sp.keteranganorder',
                'sp.statusorder','sp.qtyjenisproduk'
            )
            ->where('sp.keteranganorder','=','Order Barang Steril')
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglorder','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglorder','<=', $tgl);
        }
        if(isset($request['noorder']) && $request['noorder']!="" && $request['noorder']!="undefined"){
            $data = $data->where('sp.noorder','ILIKE','%'. $request['noorder']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru2.namaruangan','ILIKE', '%'. $request['ruangantujuanfk'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }
        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norecOrder']);
        }

        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',34);
        $data = $data->wherein('sp.objectruanganfk',$strRuangan);
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
                'status' => 'Kirim Order Barang',
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
        $data2 = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as op','op.strukorderfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','sp.jenispermintaanfk','pg.namalengkap',
                'ru.namaruangan as ruanganasal','ru2.namaruangan as ruangantujuan','sp.keteranganorder',
                'sp.statusorder','sp.qtyjenisproduk'
            )
            ->where('sp.keteranganorder','=','Order Barang Steril')
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data2 = $data2->where('sp.tglorder','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data2 = $data2->where('sp.tglorder','<=', $tgl);
        }
        if(isset($request['noorder']) && $request['noorder']!="" && $request['noorder']!="undefined"){
            $data2 = $data2->where('sp.noorder','ILIKE','%'. $request['noorder']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data2 = $data2->where('ru2.namaruangan','ILIKE', '%'.$request['ruangantujuanfk'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data2 = $data2->where('op.objectprodukfk','=',$request['produkfk']);
        }

        $data2 = $data2->distinct();
        $data2 = $data2->where('sp.statusenabled',true);
        $data2 = $data2->where('sp.objectkelompoktransaksifk',34);
        $data2 = $data2->wherein('sp.objectruangantujuanfk',$strRuangan);
        $data2 = $data2->orderBy('sp.noorder');
        $data2 = $data2->get();

//        $results =array();
        foreach ($data2 as $item){
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
                'status' => 'Terima Order Barang',
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

        $result = array(
            'daftar' => $results,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'as@epic',
            'str' => $strRuangan,
        );
        return $this->respond($result);
    }

    public function UpdateStatusAlatKotor(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {

            StrukKirim::where('norec', $request['strukkirimfk'])
                ->where('kdprofile',$idProfile)
                ->update([
                        'statuskirim' => $request['status']]
                );


            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
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
}