<?php
//“Great men are not born great, they grow great . . .”
//― Mario Puzo, The Godfather
/**
 * Created by PhpStorm.
 * User: Efan Andrian(ea@epic)
 * Date: 09-Aug-19
 * Time: 15:09
 */
namespace App\Http\Controllers\Logistik;
use App\Http\Controllers\ApiController;
use App\Master\Ruangan;
use App\Traits\Valet;
use App\Http\Controllers\Transaksi\Pegawai\Pegawai;
use App\Transaksi\KartuStok;
use App\Transaksi\KirimProduk;
use App\Transaksi\KirimProdukAset;
use App\Transaksi\LoggingUser;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\PersediaanSaldoAwal;
use App\Transaksi\RegistrasiAset;
use App\Transaksi\PenyusutanAsset;
use App\Transaksi\RiwayatRealisasi;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\StokProdukDetailAdjusment;
use App\Transaksi\StokProdukDetailOpname;
use App\Transaksi\StokProdukKadaluarsa;
use App\Transaksi\StrukClosing;
use App\Transaksi\StrukKirim;
use App\Transaksi\StrukKonfirmasi;
use App\Transaksi\StrukOrder;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\StrukPelayananDetail;
use App\Transaksi\StrukPraOrder;
use App\Transaksi\StrukPraOrderDetail;
use App\Transaksi\StrukRealisasi;
use App\Transaksi\StrukRetur;
use App\Transaksi\StrukReturDetail;
use App\Transaksi\StrukVerifikasi;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Response;
Use DateTime;
class LogistikController extends ApiController{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication=false);
    }

    public function getComboLogistik(Request $request) {
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
        $getPasswordStokOpname = $this->settingDataFixed('passwordso',$idProfile);
        $getPasswordAdjusment = $this->settingDataFixed('PasswordAdjusment', $idProfile);
        $data = \DB::table('loginuser_s as lu')
            ->JOIN('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('pg.id','pg.namalengkap')
            ->where('lu.kdprofile',$idProfile)
            ->where('lu.id',$request['userData']['id'])
            ->get();

        $dataRuangan = DB::select(DB::raw("
            SELECT ru.id,ru.namaruangan 
            FROM maploginusertoruangan_s AS mlur
            INNER JOIN loginuser_s lu on lu.id = mlur.objectloginuserfk
            INNER JOIN ruangan_m ru on ru.id = mlur.objectruanganfk
            where lu.kdprofile = $idProfile 
            and ru.statusenabled = true 
            and lu.id=:idLoginUser
            GROUP BY ru.id,ru.namaruangan"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );

        $dataSumberDana = \DB::table('asalproduk_m as lu')
            ->select('lu.id','lu.asalproduk as asalProduk')
            ->where('lu.statusenabled', true)
            ->get();

        $dataRuanganAll = \DB::table('ruangan_m as lu')
            ->select('lu.id','lu.namaruangan')
            ->where('lu.statusenabled',true)
            ->where('lu.kdprofile', $idProfile)
            ->get();

        $dataJenisProduk = \DB::table('jenisproduk_m as lu')
            ->select('lu.id','lu.jenisproduk as jenisProduk')
            ->get();

        $dataKelompokProduk = \DB::table('kelompokproduk_m as jk')
            ->select('jk.id','jk.kelompokproduk')
            ->where('jk.kdprofile', $idProfile)
            ->where('jk.statusenabled',true)
            ->get();

        $dataKelompokProduk2 = \DB::table('kelompokproduk_m as jk')
            ->select('jk.id','jk.kelompokproduk as kelompokProduk')
            ->where('jk.kdprofile', $idProfile)
            ->where('jk.statusenabled',true)
            ->get();

        $jenisProduk =  \DB::table('jenisproduk_m as jp')
            ->select('jp.id','jp.jenisproduk')
            ->where('jp.kdprofile', $idProfile)
            ->where('jp.statusenabled',true)
            ->get();

        $detailJenis =  \DB::table('detailjenisproduk_m as djp')
            ->select('djp.id','djp.detailjenisproduk','objectjenisprodukfk')
            ->where('djp.kdprofile', $idProfile)
            ->where('djp.statusenabled',true)
            ->get();

        $dataJenisUsulan = \DB::table('jenisusulan_m as jk')
            ->select('jk.id','jk.jenisusulan')
            ->where('jk.statusenabled',true)
            ->get();

        $dataAnggaran = \DB::table('mataanggaran_m as ma')
            ->select('ma.id','ma.namamataanggaran')
            ->where('ma.kdprofile', $idProfile)
            ->where('ma.statusenabled',true)
            ->orderBy('ma.namamataanggaran')
            ->get();

        $dataAnggaranT = \DB::table('mataanggaran_t as ma')
            ->select('ma.norec','ma.mataanggaran','ma.saldoawalblu','ma.saldoawalrm')
            ->where('ma.statusenabled',true)
            ->where('ma.kdprofile', $idProfile)
            ->orderBy('ma.mataanggaran')
            ->get();

        $dataJabatan = \DB::table('jabatan_m as kp')
            ->select('kp.id','kp.namajabatan')
            ->where('kp.statusenabled',true)
            ->orderBy('kp.namajabatan')
            ->get();

        $idJabatanPenglolaUrusan = $this->settingDataFixed('IdJabatanPengelolaUrusan',$idProfile);
        $idJenisJabatanKaInstalasi = $this->settingDataFixed('IdJabatanKepalaInstalasi',$idProfile);
        $JabatanPengelolaUrusan = \DB::select(DB::raw("select id,jenisjabatan from jenisjabatan_m where id = '$idJabatanPenglolaUrusan'"));
        $JenisJabatanKaInstalasi = DB::select(DB::raw("select id,jenisjabatan from jenisjabatan_m where id = '$idJenisJabatanKaInstalasi'"));
        $bulanromawi =  $this->KonDecRomawi($this->getDateTime()->format('m'));
        $prefix = $this->KonDecRomawi($this->getDateTime()->format('m')) . '/' . $this->getDateTime()->format('y');
        $resultr = StrukOrder::where('noorder', 'like', '%'. $prefix)->max('noorder');
        $subPrefix = str_replace($prefix, '', $resultr);
        $noOrder = (str_pad((int)$subPrefix+1, 3, "0", STR_PAD_LEFT)).'/'.$prefix;

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
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.kdproduk as kdsirs','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.statusenabled',true)
            ->where('pr.kdprofile', $idProfile)
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

        $dataTipeProduk = \DB::table('typeproduk_m as tp')
            ->where('tp.statusenabled', true)
            ->orderBy('tp.typeproduk')
            ->get();

        $dataSatuan = \DB::table('satuanstandar_m as ss')
            ->where('ss.statusenabled', true)
            ->orderBy('ss.satuanstandar')
            ->get();

        $dataJenisProduk = \DB::table('jenisproduk_m as jp')
            ->where('jp.statusenabled', true)
            ->orderBy('jp.jenisproduk')
            ->get();

        $dataDetailJenisProduk = \DB::table('detailjenisproduk_m as djp')
            ->where('djp.statusenabled', true)
            ->orderBy('djp.detailjenisproduk')
            ->get();

        $dataKelompokProduk = \DB::table('kelompokproduk_m as kp')
            ->where('kp.statusenabled', true)
            ->where('kp.kdprofile', $idProfile)
            ->orderBy('kp.kelompokproduk')
            ->get();

        $dataKelompokAset = \DB::table('kelompokaset_m as ka')
            ->where('ka.statusenabled', true)
            ->orderBy('ka.kelompokaset')
            ->get();

        $dataMerkProduk = \DB::table('merkproduk_m as mp')
            ->where('mp.statusenabled', true)
            ->orderBy('mp.merkproduk')
            ->get();

        $dataJenisAset = \DB::table('jenisaset_m as ja')
            ->where('ja.statusenabled', true)
            ->orderBy('ja.jenisaset')
            ->get();

        $dataKondisiAset = \DB::table('kondisiaset_m as kna')
            ->where('kna.statusenabled', true)
            ->orderBy('kna.kondisiaset')
            ->get();

        $dataSatuanAset = \DB::table('satuanaset_m as sa')
            ->where('sa.statusenabled', true)
            ->orderBy('sa.satuanaset')
            ->get();

        $dataUsiaAset = \DB::table('usiaaset_m as ua')
            ->where('ua.statusenabled', true)
            ->orderBy('ua.usiaaset')
            ->get();

        $dataJenisSertifikat = \DB::table('jenissertifikat_m as ua')
            ->where('ua.statusenabled', true)
            ->orderBy('ua.reportdisplay')
            ->get();

        $dataProdusen = \DB::table('produsenproduk_m as ua')
            ->where('ua.statusenabled', true)
            ->orderBy('ua.namaprodusenproduk')
            ->get();

        $dataFungsiProduk = \DB::table('fungsiproduk_m as ua')
            ->where('ua.statusenabled', true)
            ->orderBy('ua.fungsiproduk')
            ->get();

        $dataBahanProduk = \DB::table('bahanproduk_m as ua')
            ->where('ua.statusenabled', true)
            ->orderBy('ua.namabahanproduk')
            ->get();

        $dataWarna = \DB::table('warnaproduk_m as ua')
            ->where('ua.statusenabled', true)
            ->orderBy('ua.warnaproduk')
            ->get();
        
        $dataBulan = \DB::table('bulan_m as b')
            ->where('b.statusenabled',true)
            ->orderBy('b.id')
            ->get();

        $result = array(
            'pegawai' => $data,
            'ruangan' => $dataRuangan,
            'kelompokproduk' => $dataKelompokProduk,
            'kelompokproduk2' => $dataKelompokProduk2,
            'jenisbarang' => $dataJenisProduk,
            'asalproduk' => $dataSumberDana,
            'ruanganall' => $dataRuanganAll,
            'datalogin' => $dataLogin,
            'passwordstokopname' => $getPasswordStokOpname,
            'detailjenisproduk' =>$detailJenis,
            'jenisproduk' =>$dataJenisProduk,
            'jenisusulan' => $dataJenisUsulan,
            'dataAnggaran' => $dataAnggaran,
            'mataanggaran' => $dataAnggaranT,
            'jabatanpengelolaurusan' => $JabatanPengelolaUrusan,
            'kainstalasi' =>$JenisJabatanKaInstalasi,
            'bulanromawi' => $bulanromawi,
            'jabatan' => $dataJabatan,
            'detaillogin' => $dataPegawaiUser,
            'kodeusulan' => $noOrder,
            'produk' => $dataProdukResult,
            'tipeproduk' => $dataTipeProduk,
            'satuan' => $dataSatuan,
            'jenisproduk' => $dataJenisProduk,
            'detailjenisproduk' => $dataDetailJenisProduk,
            'kelompokproduk' => $dataKelompokProduk,
            'kelompokaset' => $dataKelompokAset,
            'merkproduk' => $dataMerkProduk,
            'jenisaset' => $dataJenisAset,
            'kondisiaset' => $dataKondisiAset,
            'satuanaset' => $dataSatuanAset,
            'usiaaset' => $dataUsiaAset,
            'jenissertifikat' => $dataJenisSertifikat,
            'produsen'=> $dataProdusen,
            'fungsiproduk' => $dataFungsiProduk,
            'bahanproduk'=> $dataBahanProduk,
            'warna'=> $dataWarna,
            'passwordAdjusment' => $getPasswordAdjusment,
            'bulan' => $dataBulan,
            'by' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataStokRuanganDetail(Request $request){
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
            ->JOIN('asalproduk_m as ap','ap.id','=','spd.objectasalprodukfk')
            ->JOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select(DB::raw("sp.nostruk as noterima,spd.objectprodukfk,pr.kdproduk as kdsirs,pr.namaproduk,ap.asalproduk,CAST(spd.qtyproduk AS FLOAT),
                              ss.satuanstandar,spd.tglkadaluarsa,spd.nobatch,CAST(spd.harganetto1 AS FLOAT),spd.norec as norec_spd,
                              spd.nostrukterimafk"))
            ->where('pr.statusenabled', true)
            ->where('pr.kdprofile', $idProfile)
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
                $data = $data->whereRaw = (" pr.kdproduk ilike '".$request['KdSirs2']."%'");
            }elseif ($request['KdSirs1'] &&  $request['KdSirs1']!= '' && $request['KdSirs2'] == '' ||  $request['KdSirs2'] == null){
                $data = $data->whereRaw = (" pr.kdproduk ilike '".$request['KdSirs1']."%'");
            }
        }
        $data = $data->where('spd.qtyproduk','>', 0);
        if(isset($request['jmlRows']) && $request['jmlRows']!="" && $request['jmlRows']!="undefined"){
            $data=$data->take($request['jmlRows']);
        }
        $data = $data->get();
        $data2=[];


        $dataOrder = \DB::table('strukorder_t as so')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->leftJOIN('strukkirim_t as sk','sk.noorderfk','=','so.norec')
            ->select('so.objectruanganfk','op.objectprodukfk',DB::raw('CAST(sum(op.qtyproduk) AS FLOAT) as qty'))
            ->where('so.kdprofile', $idProfile);
        $dataOrder = $dataOrder->where('so.objectruanganfk','=', $request['ruanganfk']);
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
                'norec_spd' => $item->norec_spd,
                'nostrukterimafk' => $item->nostrukterimafk,
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

    public function getDataProdukLogistik(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->select('pr.id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar','pr.kdproduk')
            ->where('pr.statusenabled', true)
            ->where('pr.kdprofile',$idProfile)
            ->orderBy('pr.namaproduk');

        if(isset($req['namaproduk']) &&
            $req['namaproduk']!="" &&
            $req['namaproduk']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ILIKE','%'. $req['namaproduk'] .'%' );
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ILIKE','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(20);
        $dataProduk = $dataProduk->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar',
                     'ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                     'ks.nilaikonversi')
            ->where('ks.kdprofile',$idProfile)
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

    public function getDataPegawaiPart(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataProduk=[];
        $dataProduk  = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap')
            ->where('st.kdprofile',$idProfile)
            ->where('st.statusenabled',true)
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('st.namalengkap','ILIKE','%'. $req['filter']['filters'][0]['value'].'%' );
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
            ->where('pr.kdprofile',$idProfile)
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
            ->where('ks.kdprofile',$idProfile)
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

    public function GetDataKartuStok(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('kartustok_t as ks')
            ->JOIN('produk_m as pro','pro.id','=','ks.produkfk')
            ->JOIN('ruangan_m as ru','ru.id','=','ks.ruanganfk')
            ->leftJoin('flag_m as fg','fg.id','=','ks.flagfk')
            ->select('ks.keterangan','ks.tglinput','ks.tglkejadian','ks.produkfk','pro.namaproduk','ks.ruanganfk','ru.namaruangan','ks.status','fg.flag',
                    DB::raw('COALESCE(ks.jumlah,0.0) as jumlah, coalesce(ks.saldoawal,0.0) as saldoakhir')
            )
            ->where('ks.kdprofile',$idProfile);
        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('ks.tglkejadian','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('ks.tglkejadian','<=', $tgl);
        }
        if(isset($request['ruanganfk']) && $request['ruanganfk']!="" && $request['ruanganfk']!="undefined"){
            $data = $data->where('ks.ruanganfk','=', $request['ruanganfk']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('ks.produkfk','=', $request['produkfk']);
        }
        $data = $data->where('ks.statusenabled',true);
        $data = $data->orderBy('ks.tglkejadian');
        $data = $data->get();

        return $this->respond($data);
    }

    public function getFastMoving(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('pelayananpasien_t as pp')
            ->join ('produk_m as prd','prd.id','=','pp.produkfk')
            ->leftjoin ('detailjenisproduk_m as djp','djp.id','=','prd.objectdetailjenisprodukfk')
            ->leftjoin ('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftjoin ('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftjoin ('antrianpasiendiperiksa_t as apd','apd.norec','=','pp.noregistrasifk')
            ->JOIN ('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->select('apd.objectruanganfk','ru.namaruangan','pp.tglpelayanan','pp.produkfk','prd.namaproduk', 'pp.jumlah',
                'kp.id as idkelompokproduk','kp.kelompokproduk','jp.id as idjenisproduk', 'jp.jenisproduk'
            )
            ->where('pp.kdprofile', $idProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '<=', $request['tglAkhir']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $request['idRuangan']);
        }
        if (isset($request['idKelProduk']) && $request['idKelProduk'] != "" && $request['idKelProduk'] != "undefined") {
            $data = $data->where('kp.id', '=', $request['idKelProduk']);
        }
        if (isset($request['idJenisProduk']) && $request['idJenisProduk'] != "" && $request['idJenisProduk'] != "undefined") {
            $data = $data->where('jp.id', '=', $request['idJenisProduk']);
        }
        if(isset($request['namaProduk']) && $request['namaProduk']!="" && $request['namaProduk']!="undefined"){
            $data = $data->where('prd.namaproduk','ILIKE','%'. $request['namaProduk']. '%');
        }
        $data = $data->whereIn('kp.id',[6,20,23,24]);
        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $sama = false;

        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->produkfk == $data10[$i]['produkfk']) {
                    $sama = true;
                    $jml = (float)$hideung['total'] + 1 * $item->jumlah;
                    $data10[$i]['total'] = $jml;
                }
                $i = $i + 1;
            }
            if ($sama == false) {
                $data10[] = array(
                    'objectruanganfk' => $item->objectruanganfk,
                    'namaruangan' => $item->namaruangan,
                    'tglpelayanan' => $item->tglpelayanan,
                    'produkfk' => $item->produkfk,
                    'namaproduk' => $item->namaproduk,
                    'idkelompokproduk' => $item->idkelompokproduk,
                    'idjenisproduk' => $item->idjenisproduk,
                    'total' => 1,

                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['total'];
            }
            array_multisort($count, SORT_DESC, $data10);
        }
        $result = array(
            'data' => $data10,
            'message' => 'ramdanegie',

        );
        return $this->respond($result);

    }

    public function getSlowMoving(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('pelayananpasien_t as pp')
            ->join ('produk_m as prd','prd.id','=','pp.produkfk')
            ->leftjoin ('detailjenisproduk_m as djp','djp.id','=','prd.objectdetailjenisprodukfk')
            ->leftjoin ('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftjoin ('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftjoin ('antrianpasiendiperiksa_t as apd','apd.norec','=','pp.noregistrasifk')
            ->JOIN ('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->select('apd.objectruanganfk','ru.namaruangan','pp.tglpelayanan','pp.produkfk','prd.namaproduk', 'pp.jumlah',
                'kp.id as idkelompokproduk','kp.kelompokproduk','jp.id as idjenisproduk', 'jp.jenisproduk'
            )
            ->where('pp.kdprofile', $idProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '<=', $request['tglAkhir']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $request['idRuangan']);
        }
        if (isset($request['idKelProduk']) && $request['idKelProduk'] != "" && $request['idKelProduk'] != "undefined") {
            $data = $data->where('kp.id', '=', $request['idKelProduk']);
        }
        if (isset($request['idJenisProduk']) && $request['idJenisProduk'] != "" && $request['idJenisProduk'] != "undefined") {
            $data = $data->where('jp.id', '=', $request['idJenisProduk']);
        }
        if(isset($request['namaProduk']) && $request['namaProduk']!="" && $request['namaProduk']!="undefined"){
            $data = $data->where('prd.namaproduk','ILIKE','%'. $request['namaProduk']. '%');
        }
        $data = $data->whereIn('kp.id',[6,20,23,24]);
        $data = $data->take($request['jmlRows']);

        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $sama = false;

        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->produkfk == $data10[$i]['produkfk']) {
                    $sama = true;
                    $jml = (float)$hideung['total'] + 1 * $item->jumlah;
                    $data10[$i]['total'] = $jml;
                }
                $i = $i + 1;
            }
            if ($sama == false) {
                $data10[] = array(
                    'objectruanganfk' => $item->objectruanganfk,
                    'namaruangan' => $item->namaruangan,
                    'tglpelayanan' => $item->tglpelayanan,
                    'produkfk' => $item->produkfk,
                    'namaproduk' => $item->namaproduk,
                    'idkelompokproduk' => $item->idkelompokproduk,
                    'idjenisproduk' => $item->idjenisproduk,
                    'total' => 1,

                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['total'];
            }

            array_multisort($count, SORT_ASC, $data10);
        }
        $result = array(
            'data' => $data10,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function getStokRuanganSO(Request $request){
        $dataLogin=$request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('produk_m as pr')
//            ->JOIN('strukpelayanan_t as sp','sp.norec','=','spd.nostrukterimafk')
            ->leftJoin('stokprodukdetail_t as spd','pr.id','=','spd.objectprodukfk')
            ->leftJoin('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJoin('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
//            ->JOIN('asalproduk_m as ap','ap.id','=','spd.objectasalprodukfk')
            ->leftJoin('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select(DB::raw('sum(spd.qtyproduk) as qty, pr.id as prid,pr.namaproduk,ss.satuanstandar'))
            ->where('pr.kdprofile', $idProfile)
            ->where('spd.kdprofile', $idProfile)
            ->groupBy('pr.id','pr.namaproduk',
                'ss.satuanstandar')
//            ->select('spd.objectprodukfk','pr.namaproduk','spd.qtyproduk as qty',
//                'ss.satuanstandar')
            ->orderBy('pr.namaproduk');

        if(isset($request['kelompokprodukid']) && $request['kelompokprodukid']!="" && $request['kelompokprodukid']!="undefined"){
            $data = $data->where('jp.objectkelompokprodukfk','=', $request['kelompokprodukid']);
        }
        if(isset($request['detailjenisprodukfk']) && $request['detailjenisprodukfk']!="" && $request['detailjenisprodukfk']!="undefined"){
            $arrDetJenis=explode(',',$request['detailjenisprodukfk']) ;
            $kodeDet = [];
            foreach ( $arrDetJenis as $item){
                $kodeDet[] = (int) $item;
            }
            $data = $data->whereIn('djp.id',$kodeDet);
        }
        if(isset($request['jeniskprodukid']) && $request['jeniskprodukid']!="" && $request['jeniskprodukid']!="undefined"){
            $arrJenis=explode(',',$request['jeniskprodukid']) ;
            $kode = [];
            foreach ( $arrJenis as $item){
                $kode[] = (int) $item;
            }
            $data = $data->whereIn('djp.objectjenisprodukfk',$kode);
            // $data = $data->where('djp.objectjenisprodukfk','=', $request['jeniskprodukid']);
        }
        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $data = $data->where('pr.namaproduk','ilike','%'. $request['namaproduk'] . '%');
//            $data = $data->where('pr.namaproduk','=',$request['namaproduk']);
        }
        if(isset($request['ruanganfk']) && $request['ruanganfk']!="" && $request['ruanganfk']!="undefined"){
            $data = $data->where('spd.objectruanganfk','=', $request['ruanganfk']);
        }
        $data = $data->where('pr.statusenabled',true);
        $data = $data->where('spd.statusenabled',true);
        $data = $data->get();
        $data2=[] ;
        foreach ($data as $item){
            $data2[] = array(
                'kodeProduk' => $item->prid,
                'namaProduk' => strtoupper($item->namaproduk),
                'qtyProduk' => $item->qty,
                'satuanStandar' => $item->satuanstandar,
            );
        }
        $result= array(
            'detail' => $data2,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getStokRuanganSOFromFile(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();
//        $kode='';
        $arr = explode("||",$dataLogin['data']) ;
        foreach ($arr as $item){
            $array_data = explode(',',$item);
            $kd = explode('=',$array_data[0]);
            $qty = explode('=',$array_data[1]);
            $arr_fix[] = array(
                'kd' =>  (double)$kd[1],
                'qty' => (double)$qty[1]
            );
            $kdd = (string)$kd[1];
            $kode[] = (double)$kdd;
        }


        $data = \DB::table('produk_m as pr')
            ->Join('stokprodukdetail_t as spd','pr.id','=','spd.objectprodukfk')
            ->leftJoin('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJoin('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJoin('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select(DB::raw('sum(spd.qtyproduk) as qty, pr.id as prid,pr.namaproduk,ss.satuanstandar'))
            ->where('pr.kdprofile', $idProfile)
            ->groupBy('pr.id','pr.namaproduk',
                'ss.satuanstandar')
            ->orderBy('pr.namaproduk');


        $data = $data->whereIn('pr.id',$kode);
        if(isset($request['ruanganfk']) && $request['ruanganfk']!="" && $request['ruanganfk']!="undefined"){
            $data = $data->where('spd.objectruanganfk','=', $request['ruanganfk']);
        }
        $data = $data->get();

        $data3 = \DB::table('produk_m as pr')
            ->Join('stokprodukdetail_t as spd','pr.id','=','spd.objectprodukfk')
            ->leftJoin('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select(DB::raw('pr.id as prid,pr.namaproduk,ss.satuanstandar'))
            ->where('pr.kdprofile', $idProfile)
            ->orderBy('pr.namaproduk');
        $data3 = $data3->whereIn('pr.id',$kode);
        $data3 = $data3->get();

        $sama = false;
        $sami = false;
        $gaBisaSave = true;
        foreach ($arr_fix as $ketanItem){
            $sama = false;
            foreach ($data as $item){
                if ($ketanItem['kd'] == $item->prid){
                    $sama = true;
                    $data2[] = array(
                        'kodeProduk' => $item->prid,
                        'namaProduk' => strtoupper($item->namaproduk),
                        'qtyProduk' => $item->qty,
                        'satuanStandar' => $item->satuanstandar,
                        'stokReal' => $ketanItem['qty'],
                        'selisih' => (double)$ketanItem['qty'] - (double)$item->qty,
                    );
                }
            }
            if ($sama == false){
                foreach ($data3 as $berasMerah){
                    $sami = false;
                    if ($ketanItem['kd'] == $berasMerah->prid){
                        $sami = true;
                        $data2[] = array(
                            'kodeProduk' => $ketanItem['kd'],
                            'namaProduk' => strtoupper($berasMerah->namaproduk),
                            'qtyProduk' => 0,
                            'satuanStandar' => $item->satuanstandar,
                            'stokReal' => $ketanItem['qty'],
                            'selisih' => (double)$ketanItem['qty'] ,
                        );
                        break;
                    }
                }
                if ($sami == false){
                    $gaBisaSave = false;
                    $data4 = \DB::table('produk_m as pr')
                        ->leftJoin('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
                        ->leftJoin('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
                        ->leftJoin('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
                        ->select(DB::raw('pr.id as prid,pr.namaproduk,ss.satuanstandar'))
                        ->where('pr.kdprofile', $idProfile)
                        ->orderBy('pr.namaproduk');
                    $data4 = $data4->where('pr.id',$ketanItem['kd']);
                    $data4 = $data4->get();
                    if (count($data4)==0){
                        $data2[] = array(
                            'kodeProduk' => 'xxx'.$ketanItem['kd'].'xxx',
                            'namaProduk' => 'xxx !Kode Produk Tidak ada! xxx',
                            'qtyProduk' => 0,
                            'satuanStandar' => 'xxxxxx',
                            'stokReal' => $ketanItem['qty'],
                            'selisih' => (double)$ketanItem['qty'] ,
                        );
                    }else{
                        foreach ($data4 as $berasItem){
                            $data2[] = array(
                                'kodeProduk' => 'xxx'.$ketanItem['kd'].'xxx',
                                'namaProduk' => 'xxx'.strtoupper($berasItem->namaproduk).'xxx!belum ada penerimaan!',
                                'qtyProduk' => 0,
                                'satuanStandar' => $berasItem->satuanstandar,
                                'stokReal' => $ketanItem['qty'],
                                'selisih' => (double)$ketanItem['qty'] ,
                            );
                        }
                    }

                }
            }
        }


        $result= array(
            'detail' => $data2,
            'data_stok_kosong' => $data3,
            'datalogin' => $dataLogin,
            'arr_req' => $arr_fix,
            'save_cmd' => $gaBisaSave,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getStokRuanganSOFromFileExcel(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();
        $arr_fix = $dataLogin['data'];
        $kode = [];
        foreach ( $dataLogin['data'] as $item){
            $kode[] = (double) $item['kd'];
        }
//        return $arr_fix;

        $data = \DB::table('produk_m as pr')
            ->Join('stokprodukdetail_t as spd','pr.id','=','spd.objectprodukfk')
            ->leftJoin('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJoin('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJoin('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select(DB::raw('sum(spd.qtyproduk) as qty, pr.id as prid,pr.namaproduk,ss.satuanstandar'))
            ->where('pr.kdprofile', $idProfile)
            ->groupBy('pr.id','pr.namaproduk',
                'ss.satuanstandar')
            ->orderBy('pr.namaproduk');


        $data = $data->whereIn('pr.id',$kode);
        if(isset($request['ruanganfk']) && $request['ruanganfk']!="" && $request['ruanganfk']!="undefined"){
            $data = $data->where('spd.objectruanganfk','=', $request['ruanganfk']);
        }
        $data = $data->get();

        $data3 = \DB::table('produk_m as pr')
            ->Join('stokprodukdetail_t as spd','pr.id','=','spd.objectprodukfk')
            ->leftJoin('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select(DB::raw('pr.id as prid,pr.namaproduk,ss.satuanstandar'))
            ->where('pr.kdprofile', $idProfile)
            ->orderBy('pr.namaproduk');
        $data3 = $data3->whereIn('pr.id',$kode);
        $data3 = $data3->get();

        $sama = false;
        $sami = false;
        $gaBisaSave = true;
        foreach ($arr_fix as $ketanItem){
            $sama = false;
            foreach ($data as $item){
                if ($ketanItem['kd'] == $item->prid){
                    $sama = true;
                    $data2[] = array(
                        'kodeProduk' => $item->prid,
                        'namaProduk' => strtoupper($item->namaproduk),
                        'qtyProduk' => $item->qty,
                        'satuanStandar' => $item->satuanstandar,
                        'stokReal' => $ketanItem['qty'],
                        'selisih' => (double)$ketanItem['qty'] - (double)$item->qty,
                    );
                }
            }
//            $data2=[];
            if ($sama == false){
                foreach ($data3 as $berasMerah){
                    $sami = false;
                    if ($ketanItem['kd'] == $berasMerah->prid){
                        $sami = true;
                        $data2[] = array(
                            'kodeProduk' => $ketanItem['kd'],
                            'namaProduk' => strtoupper($berasMerah->namaproduk),
                            'qtyProduk' => 0,
                            'satuanStandar' => $item->satuanstandar,
                            'stokReal' => $ketanItem['qty'],
                            'selisih' => (double)$ketanItem['qty'] ,
                        );
                        break;
                    }
                }
                if ($sami == false){
                    $gaBisaSave = false;
                    $data4 = \DB::table('produk_m as pr')
                        ->leftJoin('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
                        ->leftJoin('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
                        ->leftJoin('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
                        ->select(DB::raw('pr.id as prid,pr.namaproduk,ss.satuanstandar'))
                        ->where('pr.kdprofile', $idProfile)
                        ->orderBy('pr.namaproduk');
                    $data4 = $data4->where('pr.id',$ketanItem['kd']);
                    $data4 = $data4->get();
                    if (count($data4)==0){
                        $data2[] = array(
                            'kodeProduk' => 'xxx'.$ketanItem['kd'].'xxx',
                            'namaProduk' => 'xxx !Kode Produk Tidak ada! xxx',
                            'qtyProduk' => 0,
                            'satuanStandar' => 'xxxxxx',
                            'stokReal' => $ketanItem['qty'],
                            'selisih' => (double)$ketanItem['qty'] ,
                        );
                    }else{
                        foreach ($data4 as $berasItem){
                            $data2[] = array(
                                'kodeProduk' => 'xxx'.$ketanItem['kd'].'xxx',
                                'namaProduk' => 'xxx'.strtoupper($berasItem->namaproduk).'xxx!belum ada penerimaan!',
                                'qtyProduk' => 0,
                                'satuanStandar' => $berasItem->satuanstandar,
                                'stokReal' => $ketanItem['qty'],
                                'selisih' => (double)$ketanItem['qty'] ,
                            );
                        }
                    }

                }
            }
        }


        $result= array(
            'detail' => $data2,
            'data_stok_kosong' => $data3,
            'datalogin' => $dataLogin,
            'arr_req' => $arr_fix,
            'save_cmd' => $gaBisaSave,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarMonitoringUsulan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
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

        $data = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','sp.norec')
            ->LEFTJOIN('strukverifikasi_t as sv','sv.norec','=','sp.objectsrukverifikasifk')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.objectstrukfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap as penanggungjawab','pg2.namalengkap as mengetahui',
                'sp.tglvalidasi as tglkebutuhan','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.keteranganorder','ru.namaruangan as ruangan','ru.id as ruid',
                'ru2.namaruangan as ruangantujuan','ru2.id as ruidtujuan','sv.tglverifikasi','sv.noverifikasi','sp.totalhargasatuan','sp.status',
                'rr.objectstrukrealisasifk'
            )
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
        if(isset($request['noKontrak']) && $request['noKontrak']!="" && $request['noKontrak']!="undefined"){
            $data = $data->where('sp.nokontrakspk','ILIKE','%'. $request['noKontrak']);
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganorder','ILIKE','%'. $request['keterangan']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }

        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',89);
        $data = $data->orderBy('sp.tglorder');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("SELECT rr.tglrealisasi,
                                 (case when kt.id = 89 then so.noorder 
                                    when kt.id = 91 then sv.noverifikasi
                                    when kt.id = 101 then rr.nohps
                                    when kt.id = 90 then so1.nokontrakspk
                                    when kt.id = 88 then so2.noorderintern 
                                    when kt.id = 35 then sp.nostruk 
                                    when kt.id = 102 then sva.noverifikasi
                                    when kt.id = 109 then sk.nokonfirmasi end) as noverifikasi,pg.namalengkap,kt.id,kt.kelompoktransaksi
                                from strukrealisasi_t as sr
                                left join riwayatrealisasi_t as rr on rr.objectstrukrealisasifk=sr.norec
                                left join strukorder_t as so on so.norec = rr.objectstrukfk
                                left join strukverifikasi_t as sv on sv.norec = rr.objectverifikasifk
                                left join strukverifikasianggaran_t as sva on sva.norec = rr.objectverifikasifk
                                left join strukkonfirmasi_t as sk on sk.norec =  rr.objectverifikasifk
                                left join pegawai_m as pg on pg.id = rr.objectpetugasfk
                                left join strukorder_t as so1 on so1.norec = rr.kontrakfk
                                left join strukorder_t as so2 on so2.norec = rr.sppbfk
                                left join strukpelayanan_t as sp on sp.norec = rr.penerimaanfk
                                left join kelompoktransaksi_m as kt on kt.id = rr.objectkelompoktransaksifk
                                where sr.kdprofile = $idProfile and sr.norec =:norec 
                                order by rr.tglrealisasi asc;"),
                array(
                    'norec' => $item->objectstrukrealisasifk,
                )
            );

            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'penanggungjawab' => $item->penanggungjawab,
                'keterangan' => $item->keteranganorder,
                'koordinator' => $item->keteranganlainnya,
                'tglkebutuhan' => $item->tglkebutuhan,
                'tglusulan' => $item->tglorder,
                'nousulan' => $item->noorderintern,
                'namapengadaan' => $item->keterangankeperluan,
                'mengetahui' => $item->mengetahui,
                'ruangan' => $item->ruangan,
                'ruangantujuan' => $item->ruangantujuan,
                'totalhargasatuan' => $item->totalhargasatuan,
                'tglverifikasi' =>$item->tglverifikasi,
                'noverifikasi' =>$item->noverifikasi,
                'status' => $item->status,
                'objectstrukrealisasifk' => $item->objectstrukrealisasifk,
                'details' => $details,

            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarUsulanPermintaan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $data = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','sp.norec')
            ->LEFTJOIN('strukverifikasi_t as sv','sv.norec','=','sp.objectsrukverifikasifk')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.objectstrukfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('strukkonfirmasi_t as sk','sk.norec','=','sp.objectkonfirmasifk')
            ->LEFTJOIN('strukkonfirmasi_t as sk1','sk1.norec','=','sp.konfirmasidkfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap as penanggungjawab','pg2.namalengkap as mengetahui',
                'sp.tglvalidasi as tglkebutuhan','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.keteranganorder','ru.namaruangan as ruangan','ru.id as ruid',
                'ru2.namaruangan as ruangantujuan','ru2.id as ruidtujuan','sv.tglverifikasi','sv.noverifikasi','sp.totalhargasatuan','sp.status',
                'sr.norec as norecrealisasi','sk.nokonfirmasi','sk.tglkonfirmasi','sk1.nokonfirmasi as nokonfirmasidk','sk1.tglkonfirmasi as tglkonfirmasidk'
            )
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
        if(isset($request['noKontrak']) && $request['noKontrak']!="" && $request['noKontrak']!="undefined"){
            $data = $data->where('sp.nokontrakspk','ILIKE','%'. $request['noKontrak']);
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganorder','ILIKE','%'. $request['keterangan']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }

        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',89);
        $data = $data->orderBy('sp.tglorder');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = \DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,spd.hargappn,(spd.qtyproduk*(spd.hargasatuan)) as total,
                    spd.tglpelayananakhir as tglkebutuhan,spd.deskripsiprodukquo as spesifikasi,pr.id as prid
                     from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and strukorderfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'penanggungjawab' => $item->penanggungjawab,
                'keterangan' => $item->keteranganorder,
                'koordinator' => $item->keteranganlainnya,
                'tglkebutuhan' => $item->tglkebutuhan,
                'tglusulan' => $item->tglorder,
                'nousulan' => $item->noorderintern,
                'namapengadaan' => $item->keterangankeperluan,
                'mengetahui' => $item->mengetahui,
                'ruangan' => $item->ruangan,
                'ruangantujuan' => $item->ruangantujuan,
                'totalhargasatuan' => $item->totalhargasatuan,
                'tglverifikasi' =>$item->tglverifikasi,
                'noverifikasi' =>$item->noverifikasi,
                'status' => $item->status,
                'details' => $details,
                'norecrealisasi' => $item->norecrealisasi,
                'nokonfirmasi' => $item->nokonfirmasi,
                'tglkonfirmasi' => $item->tglkonfirmasi,
                'nokonfirmasidk' => $item->nokonfirmasidk,
                'tglkonfirmasidk' => $item->tglkonfirmasidk,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function saveBatalUsulanPermintaanBarang(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $Kel = StrukOrder::where('norec', $request['data']['norec'])
                ->update([
                    'statusenabled' => 'f',
                ]);

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Usulan Permintaan Barang';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Struk Order';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Batal Usulan Permintaan Barang Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Batal Usulan Permintaan Barang Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'Mr.Cepot',
            );
        } else {
            $transMessage = "Batal Usulan Permintaan Barang Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'as' => 'Mr.Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveRencanaUsulanPermintaanNew(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{
            if ($request['strukorder']['norec'] == '') {
                //###1###
                //CARI KODE USULAN BERDASARKAN RUANGAN PENG-USUL
                $dataRuanganLogin = \DB::table('maploginusertoruangan_s as mlu')
                    ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
                    ->select('ru.id','ru.namaruangan','ru.website')
                    ->where('ru.id',$request['strukorder']['ruanganfkPengusul'])
                    ->first();
                //####1####

                $dataSO = new StrukPraOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->noorder = $request['strukorder']['noUsulan'];
            }else {
                $dataSO = StrukPraOrder::where('norec', $request['strukorder']['norec'])->first();
                $noStruk = $dataSO->nostruk;

                $delSPD = StrukPraOrderDetail::where('strukorderfk', $request['strukorder']['norec'])
                    ->delete();
            }
            $dataSO->isdelivered = 0;
            $dataSO->objectkelompoktransaksifk = 117;
            $dataSO->keteranganorder = $request['strukorder']['keteranganorder'];
            $dataSO->qtyjenisproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->qtyproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->tglorder = $request['strukorder']['tglUsulan'];
            $dataSO->tglvalidasi = $request['strukorder']['tglDibutuhkan'];
            $dataSO->keteranganlainnya = $request['strukorder']['koordinator'];
            $dataSO->noorderintern = $request['strukorder']['nousulan'];
            $dataSO->objectruanganfk = $request['strukorder']['ruanganfkPengusul'];
            $dataSO->objectruangantujuanfk = $request['strukorder']['ruanganfkTujuan'];
            $dataSO->objectpegawaiorderfk = $request['strukorder']['penanggungjawabfk'];
            $dataSO->objectpetugasfk = $request['strukorder']['mengetahuifk'];
            $dataSO->noorderintern = $request['strukorder']['nousulan'];
            $dataSO->objectpegawaiperencanafk =$dataPegawai->objectpegawaifk;
            $dataSO->statusorder = 0;
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim = 0;
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = $request['strukorder']['total'];
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn =  $request['strukorder']['ppn'];

            $dataSO->save();
            $SO = array(
                "norec"  => $dataSO->norec,
                "objectkelompoktransaksifk" => $dataSO->objectkelompoktransaksifk,

            );
            foreach ($request['details'] as $item) {
                $dataOP = new StrukPraOrderDetail();
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = $idProfile;
                $dataOP->statusenabled = true;
                $dataOP->hasilkonversi = $item['nilaikonversi'];
                $dataOP->iscito = 0;
                $dataOP->noorderfk =$SO['norec'];
                $dataOP->objectprodukfk = $item['produkfk'];
                $dataOP->objectasalprodukfk = $request['strukorder']['asalproduk'];
                $dataOP->qtyproduk = $item['jumlah'];
                $dataOP->qtyprodukretur = 0;
                $dataOP->objectsatuanstandarfk = $item['satuanviewfk'];
                $dataOP->strukorderfk = $SO['norec'];
                $dataOP->tglpelayanan = $request['strukorder']['tglUsulan'];
                $dataOP->hargasatuan = $item['hargasatuan'];
                $dataOP->hargadiscount = $item['hargadiscount'];
                $dataOP->hargappn = $item['ppn'];
                $dataOP->deskripsiprodukquo = $item['spesifikasi'];//$item['spesifikasi'];
                $dataOP->tglpelayananakhir = $item['tglkebutuhan'];
                $dataOP->save();
            }

            //***** Struk Realisasi *****
            $datanorecSR='';
            if ($request['strukorder']['norecrealisasi'] == '') {
                $dataSR= new StrukRealisasi();
                $norealisasi = $this->generateCode(new StrukRealisasi(),'norealisasi',10,'RA-'.$this->getDateTime()->format('ym'), $idProfile);
                $dataSR->norec = $dataSR->generateNewId();
                $dataSR->kdprofile = $idProfile;
                $dataSR->statusenabled = true;
                $dataSR->norealisasi = $norealisasi;
            }else {
                $dataRR = StrukRealisasi::where('norec', $request['strukorder']['norecrealisasi'])->first();
            }
            $dataSR->tglrealisasi = $request['strukorder']['tglUsulan'];
            $dataSR->totalbelanja = $request['strukorder']['total'];
            $dataSR->tglrealisasi = $request['strukorder']['tglUsulan'];
            $dataSR->totalbelanja = $request['strukorder']['total'];
            $dataSR->save();
            $SR = array(
                "norec"  => $dataSR->norec,
            );

            //***** Riwayat Realisasi *****
            if ($request['strukorder']['norecrealisasi'] == '') {
                $dataRR= new RiwayatRealisasi();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = $idProfile;
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 117;
                $dataRR->rencanaorderfk = $SO['norec'];
            }else {
                $dataRR = RiwayatRealisasi::where('objectstrukrealisasifk', $request['strukorder']['norecrealisasi'])->first();
            }
            $dataRR->objectstrukrealisasifk = $SR['norec'];
            $dataRR->tglrealisasi =$request['strukorder']['tglUsulan'];
            $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
            $dataRR->noorderintern = $request['strukorder']['nousulan'];
            $dataRR->keteranganlainnya = $request['strukorder']['keteranganorder'];
            $dataRR->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Please Try Again";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSO,
                "data" => $datanorecSR,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO,
                "data" => $datanorecSR,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarRencanaUsulanPermintaan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $data = \DB::table('strukpraorder_t as sp')
            ->JOIN('strukpraorderdetail_t as op','op.noorderfk','=','sp.norec')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.rencanaorderfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('strukverifikasi_t as sv','sv.norec','=','sp.objectsrukverifikasifk')
            ->LEFTJOIN('strukverifikasi_t as sv1','sv1.norec','=','sp.objectsrukverifikasikafk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap as penanggungjawab','pg2.namalengkap as mengetahui',
                'sp.tglvalidasi as tglkebutuhan','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.keteranganorder','ru.namaruangan as ruangan','ru.id as ruid',
                'ru2.namaruangan as ruangantujuan','ru2.id as ruidtujuan','sp.totalhargasatuan','sp.status',
                'sr.norec as norecrealisasi','sv.noverifikasi as noverifpengelolaurusan',
                'sv1.noverifikasi as noverifkepalainstalasi')
            ->where('sp.kdprofile',$idProfile);

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
        if(isset($request['noKontrak']) && $request['noKontrak']!="" && $request['noKontrak']!="undefined"){
            $data = $data->where('sp.nokontrakspk','ILIKE','%'. $request['noKontrak']);
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganorder','ILIKE','%'. $request['keterangan']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }

        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',117);
        $data = $data->orderBy('sp.tglorder');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,spd.hargappn,(spd.qtyproduk*(spd.hargasatuan)) as total,
                    spd.tglpelayananakhir as tglkebutuhan,spd.deskripsiprodukquo as spesifikasi,pr.id as prid
                     from strukpraorderdetail_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and strukorderfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'penanggungjawab' => $item->penanggungjawab,
                'keterangan' => $item->keteranganorder,
                'koordinator' => $item->keteranganlainnya,
                'tglkebutuhan' => $item->tglkebutuhan,
                'tglusulan' => $item->tglorder,
                'nousulan' => $item->noorderintern,
                'namapengadaan' => $item->keterangankeperluan,
                'mengetahui' => $item->mengetahui,
                'ruangan' => $item->ruangan,
                'ruangantujuan' => $item->ruangantujuan,
                'totalhargasatuan' => $item->totalhargasatuan,
                'status' => $item->status,
                'noverifpengelolaurusan' => $item->noverifpengelolaurusan,
                'noverifkepalainstalasi' => $item->noverifkepalainstalasi,
                'details' => $details,
                'norecrealisasi' => $item->norecrealisasi,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getNoUsulan (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukorder_t as so')
            ->where('so.kdprofile', $idProfile)
            ->select('so.noorder');

        if(isset($request['NoSPK']) && $request['NoSPK']!="" && $request['NoSPK']!="undefined"){
            $data = $data->where('so.noorder', $request['NoSPK']);
        }

        $data = $data->where('so.statusenabled',true);
        $data = $data->get();
        return $this->respond($data);
    }

    public function getDetailDataRencanaUsulan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataReq = $request->all();
        $dataStruk = \DB::table('strukorder_t as sp')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.objectstrukfk','=','sp.norec')
            ->LEFTJOIN('riwayatrealisasi_t as rr2','rr2.sppbfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('strukrealisasi_t as sr2','sr2.norec','=','rr2.objectstrukrealisasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg1','pg1.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('rekanan_m as rkn1','rkn1.id','=','sp.objectrekanansalesfk')
            ->LEFTJOIN('mataanggaran_t as ma','ma.norec','=','sr.objectmataanggaranfk')
            ->LEFTJOIN('mataanggaran_t as ma1','ma1.norec','=','sr2.objectmataanggaranfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru1','ru1.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap','pg.id as pgid','pg1.nippns',
                'sp.alamat','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.nokontrakspk','sp.noorderrfq','sp.keteranganorder','rkn.namarekanan','rkn.id as rknid',
                'sp.namarekanansales','sp.totalhargasatuan','sp.objectpetugasfk','pg1.namalengkap as mengetahui','pg1.nippns',
                'rkn1.namarekanan as namarekanansales','sp.objectrekanansalesfk','rkn.alamatlengkap as alamatrekanan',
                'rkn1.alamatlengkap as alamatrekanansales','rkn.faksimile as faxrekanan','rkn.telepon as tlprekanan',
                'rkn1.faksimile as faxrekanansales','rkn1.telepon as tlprekanansales','sr.norealisasi','sr.norec as norecrealisasi',
                'rr.norec as norecrrusulan','sr2.norec as norecrealisasisppb','rr2.norec as norecrrsppb','sr.objectmataanggaranfk as mataanggranid','ma.mataanggaran',
                'sr2.objectmataanggaranfk as mataanggranfk','ma1.mataanggaran as mataanggaransppb','ru.id as idunitpengusul','ru.namaruangan as unitpengusul',
                'ru1.id as idunittujuan','ru1.namaruangan as unittujuan','sp.jenisusulanfk',
                \DB::raw('format(sp.tglorder, \'YYYY\') as tahunusulan'))
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norecOrder']);
        }
        $dataStruk = $dataStruk->first();

        $detail = array(
            'tglorder' => $dataStruk->tglorder,
            'noorder' => $dataStruk->noorder,
            'norec' => $dataStruk->norec,
            'petugasid' => $dataStruk->pgid,
            'petugas' => $dataStruk->namalengkap,
            'petugasmengetahui'=> $dataStruk->mengetahui,
            'petugasmengetahuiid' => $dataStruk->objectpetugasfk,
            'nippns' => $dataStruk->nippns,
            'keterangan' => $dataStruk->keteranganorder,
            'alamat' => $dataStruk->alamat,
            'telp' => $dataStruk->alamattempattujuan,
            'koordinator' => $dataStruk->keteranganlainnya,
            'tglusulan' => $dataStruk->tglvalidasi,
            'nousulan' => $dataStruk->noorderintern,
            'namapengadaan' => $dataStruk->keterangankeperluan,
            'nokontrak' => $dataStruk->nokontrakspk,
            'tahunusulan' => $dataStruk->tahunusulan,
            'namarekananid' => $dataStruk->rknid,
            'namarekanan' => $dataStruk->namarekanan,
            'alamatrekanan' => $dataStruk->alamatrekanan,
            'faxrekanan'=> $dataStruk->faxrekanan,
            'tlprekanan'=> $dataStruk->tlprekanan,
            'rekanansalesfk'=> $dataStruk->objectrekanansalesfk,
            'namarekanansales'=> $dataStruk->namarekanansales,
            'alamatrekanansales'=>$dataStruk->alamatrekanansales,
            'faxrekanansales'=> $dataStruk->faxrekanansales,
            'tlprekanansales'=> $dataStruk->tlprekanansales,
            'totalhargasatuan' => $dataStruk->totalhargasatuan,
            'norealisasi'=>$dataStruk->norealisasi,
            'norecrealisasi'=>$dataStruk->norecrealisasi,
            'norecrealisasisppb'=>$dataStruk->norecrealisasisppb,
            'norecrrusulan'=>$dataStruk->norecrrusulan,
            'norecrrsppb'=>$dataStruk->norecrrsppb,
            'norecrrsppb'=>$dataStruk->norecrrsppb,
            'mataanggranid'=>$dataStruk->mataanggranid,
            'mataanggaran'=>$dataStruk->mataanggaran,
            'mataanggranfk'=>$dataStruk->mataanggranfk,
            'mataanggaransppb'=>$dataStruk->mataanggaransppb,
            'idunitpengusul' =>$dataStruk->idunitpengusul,
            'unitpengusul' =>$dataStruk->unitpengusul,
            'idunittujuan' =>$dataStruk->idunittujuan,
            'unittujuan' =>$dataStruk->unittujuan,
            'jenisusulanfk' =>$dataStruk->jenisusulanfk
        );

        $i = 0;
        $dataStok = $details = DB::select(DB::raw("select spd.norec as norec_op, pr.id as produkfk,pr.namaproduk,spd.objectrekananfk,rek.namarekanan,pr.kdproduk,
                    ss.satuanstandar,ss.id as ssid,spd.qtyproduk,spd.qtyterimalast,spd.hargasatuan,spd.deskripsiprodukquo,
                    spd.hargasatuanquo,spd.qtyprodukkonfirmasi,spd.hargadiscountquo,spd.hargappnquo,spd.hargadiscount,spd.hargappn,sb.name as statusbarang,
                    spd.qtyproduk*spd.hargasatuan as subtotal,
                    (spd.qtyproduk*(spd.hargasatuan+spd.hargappn-spd.hargadiscount)) as total,
                    (spd.qtyproduk*(spd.hargasatuanquo-spd.hargadiscountquo+spd.hargappnquo)) totalkonfirmasi,
                    (spd.qtyprodukkonfirmasi*(spd.hargasatuanquo)) as totalkonfirmasiss,
                    spd.hasilkonversi,spd.noorderfk,spd.objectasalprodukfk,ap.id as apid,ap.asalproduk,
                    spd.tglpelayananakhir as tglkebutuhan,spd.qtyterimalast
                    from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                    left JOIN rekanan_m as rek on rek.id=spd.objectrekananfk
                    left JOIN status_barang_m as sb on sb.id = spd.objectstatusbarang
                    where spd.kdprofile = $idProfile and spd.noorderfk=:norec"),
            array(
                'norec' => $request['norecOrder'],
            )
        );
        $jmlstok=0;
        $details=[];
        foreach ($dataStok as $item){
            $i = $i+1;
            if ($item->qtyterimalast == null){
                $qtyterima = 0;
            }else{
                $qtyterima = (float)$item->qtyterimalast;
            }
            if ((float)$item->qtyproduk - $qtyterima > 0){
                $details[] = array(
                    'no' => $i,
                    'kdproduk' => $item->kdproduk,
                    'produkfk' => $item->produkfk,
                    'norec_op' => $item->norec_op,
                    'namaproduk' => $item->namaproduk,
                    'namarekanan' => $item->namarekanan,
                    'rekananfk' =>$item->objectrekananfk,
                    'nilaikonversi' => $item->hasilkonversi,
                    'satuanstandarfk' => $item->ssid,
                    'satuanstandar' => $item->satuanstandar,
                    'satuanviewfk' => $item->ssid,
                    'satuanview' => $item->satuanstandar,
                    'spesifikasi' => $item->deskripsiprodukquo,
                    'jmlstok' => $jmlstok,
                    'jumlahsppb' =>(float)$item->qtyproduk,
                    'jumlahterima' => $qtyterima,
                    'jumlah' => (float)$item->qtyproduk - $qtyterima,
                    'hargasatuan' => $item->hargasatuan,
                    'hargasatuanquo' => $item->hargasatuanquo,
                    'hargadiscountquo' => $item->hargadiscountquo,
                    'hargappnquo' => $item->hargappnquo,
                    'qtyprodukkonfirmasi' => $item->qtyprodukkonfirmasi,
                    'qtyterima' => $item->qtyterimalast,
                    'hargasatuankonfirmasi' => $item->hargasatuanquo,
                    'totalkonfirmasi' => $item->totalkonfirmasi,
                    'totalkonfirmasiss'=> $item->totalkonfirmasiss,
                    'hargadiscount' => $item->hargadiscount,
                    'ppn' => $item->hargappn,
                    'subtotal' => $item->subtotal ,
                    'total' => $item->total ,
                    'ruanganfk'=> 50 ,
                    'asalprodukfk'=> $item->apid ,
                    'asalproduk'=> $item->asalproduk ,
                    'persendiscount'=> 0 ,
                    'persenppn'=> 0 ,
                    'keterangan'=> '',
                    'nobatch'=> '',
                    'statusbarang'=> $item->statusbarang,
                    'tglkadaluarsa'=> null,
                    'tglkebutuhan'=> $item->tglkebutuhan,
                );
            }
        }

        $result = array(
            'detail' => $detail,
            'details' => $details,
            'datalogin' => $dataReq,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDetailRUPB(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataReq = $request->all();
        $dataStruk = \DB::table('strukpraorder_t as sp')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.rencanaorderfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg1','pg1.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpegawaiperencanafk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru1','ru1.id','=','sp.objectruangantujuanfk')
            ->select(\DB::raw("sp.norec,sp.tglorder,sp.noorder,pg.namalengkap,pg.id as pgid,pg1.nippns,sp.alamat,
			 sp.alamattempattujuan,sp.keteranganlainnya,sp.tglvalidasi,sp.noorderintern,sp.keterangankeperluan,
			 sp.nokontrakspk,sp.noorderrfq,sp.keteranganorder,sp.namarekanansales,sp.totalhargasatuan,
			 sp.objectpetugasfk,pg1.namalengkap as mengetahui,pg1.nippns,sr.norealisasi,sr.norec as norecrealisasi,
			 ru.id as idunitpengusul,ru.namaruangan as unitpengusul,ru1.id as idunittujuan,ru1.namaruangan as unittujuan,		 	
             EXTRACT(YEAR FROM sp.tglorder) AS tahunusulan,pg2.id as idperencana,pg2.namalengkap as namaperencana"))
            ->where('sp.kdprofile',$idProfile);

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norecOrder']);
        }
        $dataStruk = $dataStruk->first();

        $detail = array(
            'tglorder' => $dataStruk->tglorder,
            'noorder' => $dataStruk->noorder,
            'norec' => $dataStruk->norec,
            'petugasid' => $dataStruk->pgid,
            'petugas' => $dataStruk->namalengkap,
            'petugasmengetahui'=> $dataStruk->mengetahui,
            'petugasmengetahuiid' => $dataStruk->objectpetugasfk,
            'nippns' => $dataStruk->nippns,
            'keterangan' => $dataStruk->keteranganorder,
            'koordinator' => $dataStruk->keteranganlainnya,
            'tglusulan' => $dataStruk->tglvalidasi,
            'nousulan' => $dataStruk->noorderintern,
            'namapengadaan' => $dataStruk->keterangankeperluan,
            'nokontrak' => $dataStruk->nokontrakspk,
            'tahunusulan' => $dataStruk->tahunusulan,
            'totalhargasatuan' => $dataStruk->totalhargasatuan,
            'norealisasi'=>$dataStruk->norealisasi,
            'norecrealisasi'=>$dataStruk->norecrealisasi,
            'idunitpengusul' =>$dataStruk->idunitpengusul,
            'unitpengusul' =>$dataStruk->unitpengusul,
            'idunittujuan' =>$dataStruk->idunittujuan,
            'unittujuan' =>$dataStruk->unittujuan,
        );

        $i = 0;
        $dataStok = $details = DB::select(DB::raw("select spd.norec as norec_op, pr.id as produkfk,pr.namaproduk,pr.kdproduk,
                                 pr.spesifikasi,ss.satuanstandar,ss.id as ssid,spd.qtyproduk,spd.hargasatuan,
                                 spd.hargadiscount,spd.hargappn,spd.qtyproduk*spd.hargasatuan as subtotal,
                                 (spd.qtyproduk*(spd.hargasatuan+spd.hargappn-spd.hargadiscount)) as total,
                                 spd.hasilkonversi,spd.strukorderfk,spd.tglpelayananakhir as tglkebutuhan
                    from strukpraorderdetail_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                    left JOIN status_barang_m as sb on sb.id = spd.objectstatusbarang
                    where spd.kdprofile = $idProfile and spd.strukorderfk=:norec"),
            array(
                'norec' => $request['norecOrder'],
            )
        );
        $jmlstok=0;
        $details=[];
        foreach ($dataStok as $item){
            $i = $i+1;
            $details[] = array(
                'no' => $i,
                'kdproduk' => $item->kdproduk,
                'produkfk' => $item->produkfk,
                'norec_op' => $item->norec_op,
                'namaproduk' => $item->namaproduk,
                'nilaikonversi' => $item->hasilkonversi,
                'satuanstandarfk' => $item->ssid,
                'satuanstandar' => $item->satuanstandar,
                'satuanviewfk' => $item->ssid,
                'satuanview' => $item->satuanstandar,
                'spesifikasi' => $item->spesifikasi,
                'jumlah' => (float)$item->qtyproduk,
                'hargasatuan' => $item->hargasatuan,
                'hargadiscount' => $item->hargadiscount,
                'ppn' => $item->hargappn,
                'subtotal' => $item->subtotal ,
                'total' => $item->total ,
                'persendiscount'=> 0 ,
                'persenppn'=> 0 ,
                'keterangan'=> '',
                'nobatch'=> '',
                'tglkadaluarsa'=> null,
                'tglkebutuhan'=> $item->tglkebutuhan,
            );
        }

        $result = array(
            'detail' => $detail,
            'details' => $details,
            'datalogin' => $dataReq,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function hapusDataRUPB(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile',$idProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $Kel = StrukPraOrder::where('norec', $request['data']['norec'])
                ->where('lu.kdprofile',$idProfile)
                ->update([
                    'statusenabled' => 'f',
                ]);

            if ($request['data']['norecrealisasi'] != '') {

                $dataRR = new RiwayatRealisasi();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = $idProfile;
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 118;
                $dataRR->objectstrukrealisasifk = $request['data']['norecrealisasi'];
                $dataRR->tglrealisasi = $tglAyeuna; //$request['data']['tglusulan'];
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['nousulan'];
                $dataRR->rencanaorderfk = $request['data']['norec'];
                $dataRR->keteranganlainnya = 'Batal Input Rencana Usulan Permintaan Barang';
                $dataRR->save();
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog ='Batal Input Rencana Usulan Permintaan Barang';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec strukpraorder_t';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Simpan Berhasil';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = 'Hapus Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveVerifikasiDK (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try{
            if ($request['strukverifikasi']['norec'] != '') {

//                //#struk Verifikasi
                $noVerifikasi = $this->generateCode(new StrukKonfirmasi(),
                    'nokonfirmasi', 10, 'CF'.$this->getDateTime()->format('Y'), $idProfile);
                $dataSV = new StrukKonfirmasi();
                $dataSV->norec = $dataSV->generateNewId();
                $dataSV->nokonfirmasi = $noVerifikasi;
                $dataSV->kdprofile = $idProfile;
                $dataSV->statusenabled = true;
                $dataSV->objectkelompoktransaksifk = 104;
                $dataSV->keteranganlainnya = 'CONFIRM USULAN PERMINTAAN BARANG/JASA';
                $dataSV->objectpegawaifk = $request['strukverifikasi']['objectpegawaipjawabfk'];
                $dataSV->namakonfirmasi = 'Confirm Direktur Keuangan';
                //$dataSV->objectruanganfk = $request['strukverifikasi']['objectruanganfk'];
                $dataSV->tglkonfirmasi = $request['strukverifikasi']['tglconfirm'];
                $dataSV->save();
                $dataSV = $dataSV->norec;

                $dataSO = StrukOrder::where('norec', $request['strukverifikasi']['norec'])
                    ->update([
                            'konfirmasidkfk' => $dataSV,
//                        'keteranganlainnya' =>$request['strukorder']['koordinator'],
//                        'totalhargasatuan' => $request['strukorder']['total'],
//                        'totalppn' => $request['strukorder']['ppn'],
//                        'objectmataanggaranfk' => $request['strukorder']['objectmataanggaranfk']
                        ]
                    );


            }

//          //***** Riwayat Realisasi *****
//        if ($request['strukverifikasi']['norecrealisasi'] != null) {
            $dataRR= new RiwayatRealisasi();
//          $norealisasi = $this->generateCode(new StrukRealisasi(),'norealisasi',10,'RC-'.$this->getDateTime()->format('ym'));
            $dataRR->norec = $dataRR->generateNewId();
            $dataRR->kdprofile = $idProfile;
            $dataRR->statusenabled = true;
            $dataRR->objectkelompoktransaksifk = 104;
//        }else {
//            $dataRR = RiwayatRealisasi::where('objectstrukrealisasifk', $request['strukverifikasi']['norecrealisasi'])->first();
//        }
            $dataRR->objectstrukrealisasifk = $request['strukverifikasi']['norecrealisasi'];
            $dataRR->objectstrukfk = $request['strukverifikasi']['norec'];
            $dataRR->tglrealisasi = $request['strukverifikasi']['tglconfirm'];
            $dataRR->objectpetugasfk = $request['strukverifikasi']['objectpegawaipjawabfk'];
            $dataRR->noorderintern = $request['strukorder']['nousulan'];
            $dataRR->keteranganlainnya = 'CONFIRM USULAN PERMINTAAN BARANG/JASA';
            $dataRR->objectverifikasifk = $dataSV;
            $dataRR->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Verifikasi";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $request['strukorder']['norec'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan VerifikasiGagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $request['strukorder']['norec'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveVerifikasiPengelolaUrusan (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{
            if ($request['data']['norec'] != '') {

                //#struk Verifikasi
                if ($request['data']['verifikasifk'] == ''){
                    $noVerifikasi = $this->generateCode(new StrukVerifikasi(),
                        'noverifikasi', 10, 'VPU'.$this->getDateTime()->format('ym'), $idProfile);
                    $dataSV = new StrukVerifikasi();
                    $dataSV->norec = $dataSV->generateNewId();
                    $dataSV->noverifikasi = $noVerifikasi;
                    $dataSV->kdprofile = $idProfile;
                    $dataSV->statusenabled = true;
                    $dataSV->objectkelompoktransaksifk = 119;
                }else{
                    $dataSV = StrukVerifikasi::where('norec', $request['data']['verifikasifk'])->first();
                }
                $dataSV->keteranganlainnya = 'Verifikasi RUPB Pengelola Urusan';
                $dataSV->objectpegawaipjawabfk = $dataPegawai->objectpegawaifk;
                $dataSV->namaverifikasi = 'Verifikasi RUPB Pengelola Urusan';
                $dataSV->tglverifikasi = $tglAyeuna;
                $dataSV->tgleksekusi = $tglAyeuna;
                $dataSV->save();
                $dataSV = $dataSV->norec;

                $dataSP = StrukPraOrder::where('norec', $request['data']['norec'])
                    ->update([
                            'objectsrukverifikasifk' => $dataSV,
                        ]
                    );


            }

            //***** Monitoring Pengajuan Pelatihan Detail *****
            if ($request['data']['norecrealisasi'] != '') {
                $dataRR = new RiwayatRealisasi();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = $idProfile;
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 119;
                $dataRR->objectstrukrealisasifk = $request['data']['norecrealisasi'];
                $dataRR->tglrealisasi = $tglAyeuna;
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['nousulan'];
                $dataRR->rencanaorderfk = $request['data']['norec'];
                $dataRR->keteranganlainnya = 'Verifikasi RUPB Pengelola Urusan';
                $dataRR->save();
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Verifikasi RUPB Pengelola Urusan';
            $logUser->noreff =$dataSV;
            $logUser->referensi='norec RUPB Pengelola Urusan';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noplanning" => $request['data']['nousulan'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $request['data']['nousulan'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveVerifikasiKepalaInstalasi (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{
            if ($request['data']['norec'] != '') {

                //#struk Verifikasi
                if ($request['data']['verifikasifk'] == ''){
                    $noVerifikasi = $this->generateCode(new StrukVerifikasi(),
                        'noverifikasi', 10, 'VKA'.$this->getDateTime()->format('ym'), $idProfile);
                    $dataSV = new StrukVerifikasi();
                    $dataSV->norec = $dataSV->generateNewId();
                    $dataSV->noverifikasi = $noVerifikasi;
                    $dataSV->kdprofile = $idProfile;
                    $dataSV->statusenabled = true;
                    $dataSV->objectkelompoktransaksifk = 119;
                }else{
                    $dataSV = StrukVerifikasi::where('norec', $request['data']['verifikasifk'])->first();
                }
                $dataSV->keteranganlainnya = 'Verifikasi RUPB Kepala Instalasi';
                $dataSV->objectpegawaipjawabfk = $dataPegawai->objectpegawaifk;
                $dataSV->namaverifikasi = 'Verifikasi RUPB Kepala Instalasi';
                $dataSV->tglverifikasi = $tglAyeuna;
                $dataSV->tgleksekusi = $tglAyeuna;
                $dataSV->save();
                $dataSV = $dataSV->norec;

                $dataSP = StrukPraOrder::where('norec', $request['data']['norec'])
                    ->update([
                            'objectsrukverifikasikafk' => $dataSV,
                        ]
                    );


            }

            //***** Monitoring Pengajuan Pelatihan Detail *****
            if ($request['data']['norecrealisasi'] != '') {
                $dataRR = new RiwayatRealisasi();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = $idProfile;
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 120;
                $dataRR->objectstrukrealisasifk = $request['data']['norecrealisasi'];
                $dataRR->tglrealisasi = $tglAyeuna;
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['nousulan'];
                $dataRR->rencanaorderfk = $request['data']['norec'];
                $dataRR->keteranganlainnya = 'Verifikasi RUPB Kepala Instalasi';
                $dataRR->save();
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Verifikasi RUPB Kepala Instalasi';
            $logUser->noreff =$dataSV;
            $logUser->referensi='norec RUPB Kepala Instalasi';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noplanning" => $request['data']['nousulan'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $request['data']['nousulan'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataDetailUsulanPermintaanBarangRuangan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataReq = $request->all();
        $dataStruk = \DB::table('strukorder_t as sp')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.objectstrukfk','=','sp.norec')
            ->LEFTJOIN('riwayatrealisasi_t as rr2','rr2.sppbfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('strukrealisasi_t as sr2','sr2.norec','=','rr2.objectstrukrealisasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg1','pg1.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('rekanan_m as rkn1','rkn1.id','=','sp.objectrekanansalesfk')
            ->LEFTJOIN('mataanggaran_t as ma','ma.norec','=','sr.objectmataanggaranfk')
            ->LEFTJOIN('mataanggaran_t as ma1','ma1.norec','=','sr2.objectmataanggaranfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru1','ru1.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap','pg.id as pgid','pg1.nippns',
                'sp.alamat','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.nokontrakspk','sp.noorderrfq','sp.keteranganorder','rkn.namarekanan','rkn.id as rknid',
                'sp.namarekanansales','sp.totalhargasatuan','sp.objectpetugasfk','pg1.namalengkap as mengetahui','pg1.nippns',
                'rkn1.namarekanan as namarekanansales','sp.objectrekanansalesfk','rkn.alamatlengkap as alamatrekanan',
                'rkn1.alamatlengkap as alamatrekanansales','rkn.faksimile as faxrekanan','rkn.telepon as tlprekanan',
                'rkn1.faksimile as faxrekanansales','rkn1.telepon as tlprekanansales','sr.norealisasi','sr.norec as norecrealisasi',
                'rr.norec as norecrrusulan','sr2.norec as norecrealisasisppb','rr2.norec as norecrrsppb','sr.objectmataanggaranfk as mataanggranid','ma.mataanggaran',
                'sr2.objectmataanggaranfk as mataanggranfk','ma1.mataanggaran as mataanggaransppb','ru.id as idunitpengusul','ru.namaruangan as unitpengusul',
                'ru1.id as idunittujuan','ru1.namaruangan as unittujuan','sp.jenisusulanfk',
                \DB::raw('format(sp.tglorder, \'YYYY\') as tahunusulan'))
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norecOrder']);
        }
        $dataStruk = $dataStruk->first();

        $detail = array(
            'tglorder' => $dataStruk->tglorder,
            'noorder' => $dataStruk->noorder,
            'norec' => $dataStruk->norec,
            'petugasid' => $dataStruk->pgid,
            'petugas' => $dataStruk->namalengkap,
            'petugasmengetahui'=> $dataStruk->mengetahui,
            'petugasmengetahuiid' => $dataStruk->objectpetugasfk,
            'nippns' => $dataStruk->nippns,
            'keterangan' => $dataStruk->keteranganorder,
            'alamat' => $dataStruk->alamat,
            'telp' => $dataStruk->alamattempattujuan,
            'koordinator' => $dataStruk->keteranganlainnya,
            'tglusulan' => $dataStruk->tglvalidasi,
            'nousulan' => $dataStruk->noorderintern,
            'namapengadaan' => $dataStruk->keterangankeperluan,
            'nokontrak' => $dataStruk->nokontrakspk,
            'tahunusulan' => $dataStruk->tahunusulan,
            'namarekananid' => $dataStruk->rknid,
            'namarekanan' => $dataStruk->namarekanan,
            'alamatrekanan' => $dataStruk->alamatrekanan,
            'faxrekanan'=> $dataStruk->faxrekanan,
            'tlprekanan'=> $dataStruk->tlprekanan,
            'rekanansalesfk'=> $dataStruk->objectrekanansalesfk,
            'namarekanansales'=> $dataStruk->namarekanansales,
            'alamatrekanansales'=>$dataStruk->alamatrekanansales,
            'faxrekanansales'=> $dataStruk->faxrekanansales,
            'tlprekanansales'=> $dataStruk->tlprekanansales,
            'totalhargasatuan' => $dataStruk->totalhargasatuan,
            'norealisasi'=>$dataStruk->norealisasi,
            'norecrealisasi'=>$dataStruk->norecrealisasi,
            'norecrealisasisppb'=>$dataStruk->norecrealisasisppb,
            'norecrrusulan'=>$dataStruk->norecrrusulan,
            'norecrrsppb'=>$dataStruk->norecrrsppb,
            'norecrrsppb'=>$dataStruk->norecrrsppb,
            'mataanggranid'=>$dataStruk->mataanggranid,
            'mataanggaran'=>$dataStruk->mataanggaran,
            'mataanggranfk'=>$dataStruk->mataanggranfk,
            'mataanggaransppb'=>$dataStruk->mataanggaransppb,
            'idunitpengusul' =>$dataStruk->idunitpengusul,
            'unitpengusul' =>$dataStruk->unitpengusul,
            'idunittujuan' =>$dataStruk->idunittujuan,
            'unittujuan' =>$dataStruk->unittujuan,
            'jenisusulanfk' =>$dataStruk->jenisusulanfk
        );

        $i = 0;
        $dataStok = $details = DB::select(DB::raw("
                    select spd.norec as norec_op, pr.id as produkfk,pr.namaproduk,spd.objectrekananfk,rek.namarekanan,pr.kdproduk,
                    ss.satuanstandar,ss.id as ssid,spd.qtyproduk,spd.qtyterimalast,spd.hargasatuan,spd.deskripsiprodukquo,
                    spd.hargasatuanquo,spd.qtyprodukkonfirmasi,spd.hargadiscountquo,spd.hargappnquo,spd.hargadiscount,spd.hargappn,sb.name as statusbarang,
                    spd.qtyproduk*spd.hargasatuan as subtotal,
                    (spd.qtyproduk*(spd.hargasatuan+spd.hargappn-spd.hargadiscount)) as total,
                    (spd.qtyproduk*(spd.hargasatuanquo-spd.hargadiscountquo+spd.hargappnquo)) totalkonfirmasi,
                    (spd.qtyprodukkonfirmasi*(spd.hargasatuanquo)) as totalkonfirmasiss,
                    spd.hasilkonversi,spd.noorderfk,spd.objectasalprodukfk,ap.id as apid,ap.asalproduk,
                    spd.tglpelayananakhir as tglkebutuhan,spd.qtyterimalast
                    from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                    left JOIN rekanan_m as rek on rek.id=spd.objectrekananfk
                    left JOIN status_barang_m as sb on sb.id = spd.objectstatusbarang
                    where spd.kdprofile = $idProfile and spd.noorderfk=:norec"),
            array(
                'norec' => $request['norecOrder'],
            )
        );
        $jmlstok=0;
        $details=[];
        foreach ($dataStok as $item){
            $i = $i+1;
            if ($item->qtyterimalast == null){
                $qtyterima = 0;
            }else{
                $qtyterima = (float)$item->qtyterimalast;
            }
            if ((float)$item->qtyproduk - $qtyterima > 0){
                $details[] = array(
                    'no' => $i,
                    'kdproduk' => $item->kdproduk,
                    'produkfk' => $item->produkfk,
                    'norec_op' => $item->norec_op,
                    'namaproduk' => $item->namaproduk,
                    'namarekanan' => $item->namarekanan,
                    'rekananfk' =>$item->objectrekananfk,
                    'nilaikonversi' => $item->hasilkonversi,
                    'satuanstandarfk' => $item->ssid,
                    'satuanstandar' => $item->satuanstandar,
                    'satuanviewfk' => $item->ssid,
                    'satuanview' => $item->satuanstandar,
                    'spesifikasi' => $item->deskripsiprodukquo,
                    'jmlstok' => $jmlstok,
                    'jumlahsppb' =>(float)$item->qtyproduk,
                    'jumlahterima' => $qtyterima,
                    'jumlah' => (float)$item->qtyproduk - $qtyterima,
                    'hargasatuan' => $item->hargasatuan,
                    'hargasatuanquo' => $item->hargasatuanquo,
                    'hargadiscountquo' => $item->hargadiscountquo,
                    'hargappnquo' => $item->hargappnquo,
                    'qtyprodukkonfirmasi' => $item->qtyprodukkonfirmasi,
                    'qtyterima' => $item->qtyterimalast,
                    'hargasatuankonfirmasi' => $item->hargasatuanquo,
                    'totalkonfirmasi' => $item->totalkonfirmasi,
                    'totalkonfirmasiss'=> $item->totalkonfirmasiss,
                    'hargadiscount' => $item->hargadiscount,
                    'ppn' => $item->hargappn,
                    'subtotal' => $item->subtotal ,
                    'total' => $item->total ,
                    'ruanganfk'=> 50 ,
                    'asalprodukfk'=> $item->apid ,
                    'asalproduk'=> $item->asalproduk ,
                    'persendiscount'=> 0 ,
                    'persenppn'=> 0 ,
                    'keterangan'=> '',
                    'nobatch'=> '',
                    'statusbarang'=> $item->statusbarang,
                    'tglkadaluarsa'=> null,
                    'tglkebutuhan'=> $item->tglkebutuhan,
                );
            }
        }

        $result = array(
            'detail' => $detail,
            'details' => $details,
            'datalogin' => $dataReq,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getHargaTerakhir(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $produkfk = $request['produkfk'];
        $ruanganfk = $request['ruanganfk'];
        $maxHarga = \DB::select(DB::raw("select distinct spd.tglpelayanan,spd.harganetto1 as harga,spdl.harganetto,pr.id,pr.kdproduk,pr.namaproduk,ss.satuanstandar
                from stokprodukdetail_t as spd
                inner JOIN produk_m as pr on pr.id=spd.objectprodukfk
                INNER JOIN satuanstandar_m as ss on ss.id=pr.objectsatuanstandarfk
                left join detailjenisproduk_m as djp on djp.id = pr.objectdetailjenisprodukfk
                inner join strukpelayanandetail_t as spdl on spdl.nostrukfk = spd.nostrukterimafk
                where spd.kdprofile = $idProfile and spd.objectprodukfk='$produkfk' 
                -- and spd.objectruanganfk='$ruanganfk' 
                and djp.objectjenisprodukfk = 97 order by spd.tglpelayanan desc;")
        );
        $dataNyawaTerakhir = [];
        $samateu = false;
        $tgl = date('2000-01-01 00:00');
        foreach ($maxHarga as $item){
            $samateu = false;
            foreach ($dataNyawaTerakhir as $itemsss){
                if ($item->id == $itemsss['id']){
                    $samateu = true;
                    if ($item->tglpelayanan > date($itemsss['tglpelayanan'])){
                        $itemsss['harga'] = $item->harga;
                        $itemsss['hargapenerimaan'] = $item->harganetto;
                        $itemsss['tglpelayanan'] = $item->tglpelayanan;
                        break;
                    }
                }
            }
            if ($samateu == false){
                $dataNyawaTerakhir[] = array(
                    'id' => $item->id,
                    'tglpelayanan' => $item->tglpelayanan,
                    'harga' => $item->harga,
                    'hargapenerimaan' => $item->harganetto,
                    'kdproduk' => $item->kdproduk,
                    'namaproduk' => $item->namaproduk,
                    'satuanstandar' => $item->satuanstandar,
                );
            }
        }


        $result= array(
            'detail' => $dataNyawaTerakhir,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function saveUsulanPermintaan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try{
            if ($request['strukorder']['norec'] == '') {
                //###1###
                //CARI KODE USULAN BERDASARKAN RUANGAN PENG-USUL
                $dataRuanganLogin = \DB::table('maploginusertoruangan_s as mlu')
                    ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
                    ->select('ru.id','ru.namaruangan','ru.website')
                    ->where('mlu.kdprofile',$idProfile)
                    ->where('ru.id',$request['strukorder']['ruanganfkPengusul'])
                    ->first();

                $prefix = '/'. $dataRuanganLogin->website . '/' . $this->KonDecRomawi($this->getDateTime()->format('m')) . '/' . $this->getDateTime()->format('y');
                $resultr = StrukOrder::where('noorder', 'ILIKE', '%' . $prefix)->max('noorder');
                $subPrefix = str_replace($prefix, '', $resultr);
                $noOrder = (str_pad((int)$subPrefix + 1, 3, "0", STR_PAD_LEFT)) . '' . $prefix;
                //####1####

                $dataSO = new StrukOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->noorder = $request['strukorder']['noUsulan'];
            }else {
                $dataSO = StrukOrder::where('norec', $request['strukorder']['norec'])->first();
                $noStruk = $dataSO->nostruk;

                $delSPD = OrderPelayanan::where('strukorderfk', $request['strukorder']['norec'])
                    ->delete();
            }
            $dataSO->isdelivered = 0;
            $dataSO->objectkelompoktransaksifk = 89;
            $dataSO->keteranganorder = $request['strukorder']['keteranganorder'];
            $dataSO->qtyjenisproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->qtyproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->tglorder = $request['strukorder']['tglUsulan'];
            $dataSO->tglvalidasi = $request['strukorder']['tglDibutuhkan'];
            $dataSO->keteranganlainnya = $request['strukorder']['koordinator'];
            $dataSO->noorderintern = $request['strukorder']['nousulan'];
            $dataSO->objectruanganfk = $request['strukorder']['ruanganfkPengusul'];
            $dataSO->objectruangantujuanfk = $request['strukorder']['ruanganfkTujuan'];
            $dataSO->objectpegawaiorderfk = $request['strukorder']['penanggungjawabfk'];
            $dataSO->objectpetugasfk = $request['strukorder']['mengetahuifk'];
            $dataSO->noorderintern = $request['strukorder']['nousulan'];
            $dataSO->statusorder = 0;
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim = 0;
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = $request['strukorder']['total'];
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn =  $request['strukorder']['ppn'];

            $dataSO->save();
            $SO = array(
                "norec"  => $dataSO->norec,
                "objectkelompoktransaksifk" => $dataSO->objectkelompoktransaksifk,

            );

            foreach ($request['details'] as $item) {
                $dataOP = new OrderPelayanan();
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = $idProfile;
                $dataOP->statusenabled = true;
                $dataOP->hasilkonversi = $item['nilaikonversi'];
                $dataOP->iscito = 0;
                $dataOP->noorderfk =$SO['norec'];
                $dataOP->objectprodukfk = $item['produkfk'];
                $dataOP->objectasalprodukfk = $request['strukorder']['asalproduk'];
                $dataOP->qtyproduk = $item['jumlah'];
                $dataOP->qtyprodukretur = 0;
                $dataOP->objectsatuanstandarfk = $item['satuanviewfk'];
                $dataOP->strukorderfk = $SO['norec'];
                $dataOP->tglpelayanan = $request['strukorder']['tglUsulan'];
                $dataOP->hargasatuan = $item['hargasatuan'];
                $dataOP->hargadiscount = $item['hargadiscount'];
                $dataOP->hargappn = $item['ppn'];
                $dataOP->deskripsiprodukquo = $item['spesifikasi'];//$item['spesifikasi'];
                $dataOP->tglpelayananakhir = $item['tglkebutuhan'];
                $dataOP->save();
            }

            //***** Struk Realisasi *****
            $datanorecSR='';
            if ($request['strukorder']['norecrealisasi'] == '') {
                $dataSR= new StrukRealisasi();
                $norealisasi = $this->generateCode(new StrukRealisasi(),'norealisasi',10,'RA-'.$this->getDateTime()->format('ym'), $idProfile);
                $dataSR->norec = $dataSR->generateNewId();
                $dataSR->kdprofile = $idProfile;
                $dataSR->statusenabled = true;
                $dataSR->norealisasi = $norealisasi;
                $dataSR->tglrealisasi = $request['strukorder']['tglUsulan'];
                $dataSR->totalbelanja = $request['strukorder']['total'];
                $dataSR->save();
                if ($request['strukorder']['norecrealisasi'] == '') {
                    $datanorecSR = $dataSR->norec;
                }else{
                    $datanorecSR = $request['strukorder']['norecrealisasi'];
                }
            }else {
                $dataSR = StrukRealisasi::where('norec', $request['strukorder']['norecrealisasi'])->first();
                $dataSR->tglrealisasi = $request['strukorder']['tglUsulan'];
                $dataSR->objectmataanggaranfk = $request['strukorder']['objectmataanggaranfk'];
                $dataSR->totalbelanja = $request['strukorder']['total'];
                $dataSR->save();
                if ($request['strukorder']['norecrealisasi'] == '') {
                    $datanorecSR = $dataSR->norec;
                }else{
                    $datanorecSR = $request['strukorder']['norecrealisasi'];
                }
            }



            //***** Riwayat Realisasi *****
            if ($request['strukorder']['norecrealisasi'] == '') {
                $dataRR= new RiwayatRealisasi();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = $idProfile;
                $dataRR->statusenabled = true;
            }else {

                $dataRR = RiwayatRealisasi::where('objectstrukrealisasifk', $request['strukorder']['norecrealisasi'])->first();

            }
            $dataRR->objectstrukrealisasifk = $datanorecSR;
            $dataRR->objectstrukfk = $SO['norec'];
            $dataRR->objectkelompoktransaksifk = $SO['objectkelompoktransaksifk'];
            $dataRR->tglrealisasi =$request['strukorder']['tglUsulan'];
            $dataRR->objectpetugasfk = $request['strukorder']['penanggungjawabfk'];
            $dataRR->noorderintern = $request['strukorder']['nousulan'];
            $dataRR->keteranganlainnya = $request['strukorder']['keteranganorder'];
            $dataRR->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Usulan Permintaan Barang";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Usulan Permintaan Barang Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSO,
                "data" => $datanorecSR,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Usulan Permintaan Barang Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO,
                "data" => $datanorecSR,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarPermintaanBarangRuangan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $data = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','sp.norec')
//            ->JOIN('strukverifikasi_t as sv','sv.norec','=','sp.objectsrukverifikasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
//            ->LEFTJOIN('strukverifikasianggaran_t as sk','sk.norec','=','sp.strukverifikasianggaranfk')
            ->LEFTJOIN('mataanggaran_t as ma','ma.norec','=','sp.objectmataanggaranfk')
            ->LEFTJOIN('asalproduk_m as ap','ap.id','=','op.objectasalprodukfk')
            ->select(DB::raw("sp.norec,sp.tglorder,sp.noorder,pg.namalengkap AS penanggungjawab,pg2.namalengkap AS mengetahui,
                    sp.tglvalidasi AS tglkebutuhan,sp.alamattempattujuan,sp.keteranganlainnya,sp.tglvalidasi,sp.noorderintern,
                    sp.keterangankeperluan,sp.keteranganorder,ru.namaruangan AS ruangan,ru.id AS ruid,ru2.namaruangan AS ruangantujuan,
                    ru2.id AS ruidtujuan,ma.norec AS mataanggaranfk,ma.mataanggaran,ap.id AS apid,ap.asalproduk,sp.totalhargasatuan"))
//                'sp.norec','sp.tglorder','sp.noorder','pg.namalengkap as penanggungjawab','pg2.namalengkap as mengetahui',
//                'sp.tglvalidasi as tglkebutuhan','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
//                'sp.keterangankeperluan','sp.keteranganorder','ru.namaruangan as ruangan','ru.id as ruid',
//                'ru2.namaruangan as ruangantujuan','ru2.id as ruidtujuan',
//                'sp.totalhargasatuan','sp.status','pg2.nippns',
////                ,'sv.noverifikasi','sv.tglverifikasi','sp.noorderhps','sk.noverifikasi',
////                'sk.tglverifikasi','sk.statusterima as statusverifikasi','sk.keteranganlainnya as keteranganlainnya1',
//                'ma.norec as mataanggaranfk','ma.mataanggaran','ap.id as apid','ap.asalproduk'
//                \DB::raw("Case when sk.statusterima = 1 then  'Disetujui' else 'Tidak Disetujui' end as keteranganverifikasi"));
            ->where('sp.kdprofile',$idProfile);
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
        if(isset($request['noKontrak']) && $request['noKontrak']!="" && $request['noKontrak']!="undefined"){
            $data = $data->where('sp.nokontrakspk','ILIKE','%'. $request['noKontrak']);
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganorder','ILIKE','%'. $request['keterangan']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }

        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
//        $data = $data->where('sk.statusterima',1);
        $data = $data->where('sp.objectkelompoktransaksifk',89);
        $data = $data->orderBy('sp.tglorder');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,spd.hargappnquo,spd.hargadiscountquo,
                    (spd.qtyproduk*(spd.hargasatuan)) as total,
                    (spd.qtyprodukkonfirmasi*(spd.hargasatuanquo + hargappnquo - hargadiscountquo)) as totalkonfirmasi,
                    spd.tglpelayananakhir as tglkebutuhan,spd.deskripsiprodukquo as spesifikasi,pr.id as prid,
                    spd.hargasatuanquo,spd.qtyprodukkonfirmasi
                     from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and strukorderfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'penanggungjawab' => $item->penanggungjawab,
                'keterangan' => $item->keteranganorder,
                'koordinator' => $item->keteranganlainnya,
                'tglkebutuhan' => $item->tglkebutuhan,
                'tglusulan' => $item->tglorder,
                'nousulan' => $item->noorderintern,
                'namapengadaan' => $item->keterangankeperluan,
                'mengetahui' => $item->mengetahui,
                'ruangan' => $item->ruangan,
                'ruangantujuan' => $item->ruangantujuan,
                'totalhargasatuan' => $item->totalhargasatuan,
//                'status' => $item->status,
//                'noverifikasi' => $item->noverifikasi,
//                'noorderhps' => $item->noorderhps,
//                'tglverifikasi' => $item->tglverifikasi,
//                'statusverifikasi' => $item->statusverifikasi,
//                'keteranganlainnya1' => $item->keteranganlainnya1,
//                'keteranganverifikasi' => $item->keteranganverifikasi,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarSPPB(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $data = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
//            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap',
                'sp.alamat','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.nokontrakspk','sp.noorderrfq','sp.keteranganorder','rkn.namarekanan','rkn.id as rknid',
                'sp.namarekanansales','sp.totalhargasatuan','sp.status','ru.namaruangan as ruangan','ru.id as ruid',
                'ru2.namaruangan as ruangantujuan','ru2.id as ruidtujuan','pg2.namalengkap as mengetahui','sp.qtyproduk'
            )
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
        if(isset($request['noKontrak']) && $request['noKontrak']!="" && $request['noKontrak']!="undefined"){
            $data = $data->where('sp.nokontrakspk','ILIKE','%'. $request['noKontrak']);
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganorder','ILIKE','%'. $request['keterangan']);
        }
        if(isset($request['rekanan']) && $request['rekanan']!="" && $request['rekanan']!="undefined"){
            $data = $data->where('sp.namarekanansales','ILIKE','%'. $request['rekanan']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }

        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',89);
        $data = $data->orderBy('sp.noorder');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = \DB::select(DB::raw("select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.qtyterimalast,spd.hargasatuan,spd.hargadiscount,spd.hargappn,
                    (spd.qtyproduk*(spd.hargasatuan)) as total,
                    (spd.qtyprodukkonfirmasi*(spd.hargasatuanquo)) as totalkonfirmasi,
                    spd.tglpelayananakhir as tglkebutuhan,spd.deskripsiprodukquo as spesifikasi,pr.id as prid,
                    spd.hargasatuanquo,spd.qtyprodukkonfirmasi
                     from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and strukorderfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganorder,
                'alamat' => $item->alamat,
                'telp' => $item->alamattempattujuan,
                'koordinator' => $item->keteranganlainnya,
                'tglusulan' => $item->tglvalidasi,
                'nousulan' => $item->noorderintern,
                'namapengadaan' => $item->keterangankeperluan,
                'nokontrak' => $item->nokontrakspk,
                'tahunusulan' => $item->noorderrfq,
                'namarekanan' => $item->namarekanansales,
                'totalhargasatuan' => $item->totalhargasatuan,
                'ruangan' => $item->ruangan,
                'ruangantujuan' => $item->ruangantujuan,
                'mengetahui' => $item->mengetahui,
                'status' => $item->status,
                'jmlitem' => $item->qtyproduk,
                'details' => $details,

            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function savePerbaikiKartuStok(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $ruanganfk = $request['ruanganfk'];
        $produkfk = $request['produkfk'];;
        $tglawal = $request['tglawal'];;
        $tglakhir = $request['tglakhir'];

        try {
            $data = KartuStok::where('ruanganfk', $ruanganfk)
                ->where('kdprofile', $idProfile)
                ->where('produkfk',$produkfk)
                ->where('tglkejadian','>=',$tglawal)
//                ->where('tglkejadian','<',$tglakhir)
                ->orderby('tglkejadian')
                ->get();
            foreach ($data as $item){
                $saldoAwal = (float)0 ;
                $qtyMasuk = (float)0;
                $qtyKeluar = (float)0;
                $saldoAkhir = (float)0;
                if($item->status == true){
                    $saldoAwal = (float)$item->saldoawal -  (float)$item->jumlah;
                    $qtyMasuk = (float)$item->jumlah;
                    $qtyKeluar = (float)0;
                    $saldoAkhir = (float)$item->saldoawal;
                }else{
                    $saldoAwal = (float)$item->saldoawal + (float)$item->jumlah ;
                    $qtyMasuk = (float)0;
                    $qtyKeluar = (float)$item->jumlah;
                    $saldoAkhir = (float)$item->saldoawal;
                }
                $datas[] = array(
                    'norec'=> $item->norec,
                    'statusenabled'=> $item->statusenabled,
                    'jumlah'=> $item->jumlah,
                    'keterangan'=> $item->keterangan,
                    'produkfk'=> $item->produkfk,
                    'ruanganfk'=> $item->ruanganfk,
                    'saldoawal'=> $saldoAwal,
                    'saldomasuk'=> $qtyMasuk,
                    'saldokeluar'=> $qtyKeluar,
                    'saldoakhir'=> $saldoAkhir,
                    'status'=> $item->status,
                    'tglinput'=> $item->tglinput,
                    'tglkejadian'=> $item->tglkejadian,
                    'nostrukterimafk'=> $item->nostrukterimafk,
                );
            }
            $i = 0;
            $j = 1;
            $dt = 0;
            foreach ($datas as $itm){
                if ($j >= count($datas)){
                    $datas[$i]['saldoawal'] = $datas[$i-1]['saldoakhir'];
                    $datas[$i]['saldoakhir'] = (float)$datas[$i]['saldoawal'] + ((float)$datas[$i]['saldomasuk'] - (float)$datas[$i]['saldokeluar']);
                    break;
                }
                if ($datas[$i]['saldoakhir'] != $datas[$j]['saldoawal']){
                    $datas[$j]['saldoawal'] = (float)$datas[$i]['saldoakhir'];
                    $datas[$j]['saldoakhir'] = (float)$datas[$j]['saldoawal'] + ((float)$datas[$j]['saldomasuk'] - (float)$datas[$j]['saldokeluar']);
                }

                $i = $i + 1 ;
                $j = $j + 1;
            }
            $jumlah = 0;
            foreach ($datas as $itm){
                KartuStok::where('norec',$itm['norec'])
                    ->update([
                        'saldoawal' => (float)$itm['saldoakhir']
                    ]);
                $jumlah = (float)$itm['saldoakhir'];
            }

            $dataStokNow = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk "),
                array(
                    'ruanganfk' => $ruanganfk,
                    'produkfk' => $produkfk,
                )
            );
            $jumlah = $jumlah - (float)$dataStokNow[0]->qty;

            //STOK MINUS MENYEBALKAN//
            $dataStokMinus = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk < 0 "),
                array(
                    'ruanganfk' => $ruanganfk,
                    'produkfk' => $produkfk,
                )
            );
            StokProdukDetail::where('objectruanganfk', $ruanganfk)
                ->where('kdprofile', $idProfile)
                ->where('objectprodukfk', $produkfk)
                ->where('qtyproduk', '<', 0)
                ->update([
                    'qtyproduk' => 0
                ]);
            if (count($dataStokMinus) != 0) {
                foreach ($dataStokMinus as $items) {
                    $stokMinus = (float)$items->qty;
                }
            }
            //######################//


            $saldoAwal = 0;
            $jumlah = (float)$jumlah + (float)$stokMinus;

            if ($jumlah > 0) {
                $dataStok = DB::select(DB::raw("select 
                        top 1
                        qtyproduk as qty,norec,nostrukterimafk from stokprodukdetail_t 
                        where kdprofile = $idProfile and  objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk>0 
                        -- limit 1
                        "),
                    array(
                        'ruanganfk' => $ruanganfk,
                        'produkfk' => $produkfk,
                    )
                );
                if (count($dataStok) == 0) {
                    $dataStok = DB::select(DB::raw("select
                                  top 1
                                  qtyproduk as qty,norec,nostrukterimafk from stokprodukdetail_t 
                                  where kdprofile = $idProfile and  objectruanganfk=:ruanganfk and objectprodukfk=:produkfk  
                                  -- limit 1
                                  "),
                        array(
                            'ruanganfk' => $ruanganfk,
                            'produkfk' => $produkfk,
                        )
                    );
                }
                foreach ($dataStok as $items) {
                    StokProdukDetail::where('norec', $items->norec)
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'qtyproduk' => (float)$items->qty + (float)$jumlah]
                        );
                }

            } else {
                $jumlah = $jumlah * (-1);
                $dataStok = DB::select(DB::raw("select qtyproduk as qty,norec,nostrukterimafk from stokprodukdetail_t 
                        where kdprofile = $idProfile and  objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $ruanganfk,
                        'produkfk' => $produkfk,
                    )
                );
                foreach ($dataStok as $items) {
                    if ((float)$items->qty < $jumlah) {
                        $jumlah = $jumlah - (float)$items->qty;
                        StokProdukDetail::where('norec', $items->norec)
                            ->where('kdprofile', $idProfile)
                            ->update([
                                    'qtyproduk' => 0]
                            );
                    } else {
                        $saldoakhir = (float)$items->qty - $jumlah;
                        $jumlah = 0;
                        StokProdukDetail::where('norec', $items->norec)
                            ->where('kdprofile', $idProfile)
                            ->update([
                                    'qtyproduk' => (float)$saldoakhir]
                            );
                    }
                }
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Perbaiki data ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $datas,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $datas,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function DeletePenerimaanSuplier(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            StrukPelayanan::where('norec', $request['norec_sp'])
                ->where('kdprofile', $idProfile)
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
            \DB::commit();
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

    public function getDataRekananPart(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataProduk=[];
        $dataProduk  = \DB::table('rekanan_m as st')
            ->select('st.id','st.namarekanan','st.alamatlengkap','st.telepon','st.faksimile')
            ->where('st.statusenabled',true)
            ->where('st.kdprofile', $idProfile)
            ->orderBy('st.namarekanan');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('st.namarekanan','ILIKE','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();
        return $this->respond($dataProduk);
    }

    public function getDetailDataSPPB(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataReq = $request->all();
        $dataStruk = \DB::table('strukorder_t as sp')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.objectstrukfk','=','sp.norec')
            ->LEFTJOIN('riwayatrealisasi_t as rr2','rr2.sppbfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('strukrealisasi_t as sr2','sr2.norec','=','rr2.objectstrukrealisasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg1','pg1.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('rekanan_m as rkn1','rkn1.id','=','sp.objectrekanansalesfk')
            ->LEFTJOIN('mataanggaran_t as ma','ma.norec','=','sr.objectmataanggaranfk')
            ->LEFTJOIN('mataanggaran_t as ma1','ma1.norec','=','sr2.objectmataanggaranfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru1','ru1.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap','pg.id as pgid','pg1.nippns',
                'sp.alamat','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.nokontrakspk','sp.noorderrfq','sp.keteranganorder','rkn.namarekanan','rkn.id as rknid',
                'sp.namarekanansales','sp.totalhargasatuan','sp.objectpetugasfk','pg1.namalengkap as mengetahui','pg1.nippns',
                'rkn1.namarekanan as namarekanansales','sp.objectrekanansalesfk','rkn.alamatlengkap as alamatrekanan',
                'rkn1.alamatlengkap as alamatrekanansales','rkn.faksimile as faxrekanan','rkn.telepon as tlprekanan',
                'rkn1.faksimile as faxrekanansales','rkn1.telepon as tlprekanansales','sr.norealisasi','sr.norec as norecrealisasi',
                'rr.norec as norecrrusulan','sr2.norec as norecrealisasisppb','rr2.norec as norecrrsppb','sr.objectmataanggaranfk as mataanggranid','ma.mataanggaran',
                'sr2.objectmataanggaranfk as mataanggranfk','ma1.mataanggaran as mataanggaransppb','ru.id as idunitpengusul','ru.namaruangan as unitpengusul',
                'ru1.id as idunittujuan','ru1.namaruangan as unittujuan','sp.jenisusulanfk',
                \DB::raw('EXTRACT (YEAR from sp.tglorder) AS tahunusulan'))
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norecOrder']);
        }
        $dataStruk = $dataStruk->first();

        $detail = array(
            'tglorder' => $dataStruk->tglorder,
            'noorder' => $dataStruk->noorder,
            'norec' => $dataStruk->norec,
            'petugasid' => $dataStruk->pgid,
            'petugas' => $dataStruk->namalengkap,
            'petugasmengetahui'=> $dataStruk->mengetahui,
            'petugasmengetahuiid' => $dataStruk->objectpetugasfk,
            'nippns' => $dataStruk->nippns,
            'keterangan' => $dataStruk->keteranganorder,
            'alamat' => $dataStruk->alamat,
            'telp' => $dataStruk->alamattempattujuan,
            'koordinator' => $dataStruk->keteranganlainnya,
            'tglusulan' => $dataStruk->tglvalidasi,
            'nousulan' => $dataStruk->noorderintern,
            'namapengadaan' => $dataStruk->keterangankeperluan,
            'nokontrak' => $dataStruk->nokontrakspk,
            'tahunusulan' => $dataStruk->tahunusulan,
            'namarekananid' => $dataStruk->rknid,
            'namarekanan' => $dataStruk->namarekanan,
            'alamatrekanan' => $dataStruk->alamatrekanan,
            'faxrekanan'=> $dataStruk->faxrekanan,
            'tlprekanan'=> $dataStruk->tlprekanan,
            'rekanansalesfk'=> $dataStruk->objectrekanansalesfk,
            'namarekanansales'=> $dataStruk->namarekanansales,
            'alamatrekanansales'=>$dataStruk->alamatrekanansales,
            'faxrekanansales'=> $dataStruk->faxrekanansales,
            'tlprekanansales'=> $dataStruk->tlprekanansales,
            'totalhargasatuan' => $dataStruk->totalhargasatuan,
            'norealisasi'=>$dataStruk->norealisasi,
            'norecrealisasi'=>$dataStruk->norecrealisasi,
            'norecrealisasisppb'=>$dataStruk->norecrealisasisppb,
            'norecrrusulan'=>$dataStruk->norecrrusulan,
            'norecrrsppb'=>$dataStruk->norecrrsppb,
            'norecrrsppb'=>$dataStruk->norecrrsppb,
            'mataanggranid'=>$dataStruk->mataanggranid,
            'mataanggaran'=>$dataStruk->mataanggaran,
            'mataanggranfk'=>$dataStruk->mataanggranfk,
            'mataanggaransppb'=>$dataStruk->mataanggaransppb,
            'idunitpengusul' =>$dataStruk->idunitpengusul,
            'unitpengusul' =>$dataStruk->unitpengusul,
            'idunittujuan' =>$dataStruk->idunittujuan,
            'unittujuan' =>$dataStruk->unittujuan,
            'jenisusulanfk' =>$dataStruk->jenisusulanfk
        );

        $i = 0;
        $dataStok = $details = DB::select(DB::raw("
                    select spd.norec as norec_op, pr.id as produkfk,pr.namaproduk,spd.objectrekananfk,rek.namarekanan,pr.kdproduk,
                    ss.satuanstandar,ss.id as ssid,spd.qtyproduk,spd.qtyterimalast,spd.hargasatuan,spd.deskripsiprodukquo,
                    spd.hargasatuanquo,spd.qtyprodukkonfirmasi,spd.hargadiscountquo,spd.hargappnquo,spd.hargadiscount,spd.hargappn,sb.name as statusbarang,
                    spd.qtyproduk*spd.hargasatuan as subtotal,
                    (spd.qtyproduk*(spd.hargasatuan+spd.hargappn-spd.hargadiscount)) as total,
                    (spd.qtyproduk*(spd.hargasatuanquo-spd.hargadiscountquo+spd.hargappnquo)) totalkonfirmasi,
                    (spd.qtyprodukkonfirmasi*(spd.hargasatuanquo)) as totalkonfirmasiss,
                    spd.hasilkonversi,spd.noorderfk,spd.objectasalprodukfk,ap.id as apid,ap.asalproduk,
                    spd.tglpelayananakhir as tglkebutuhan,spd.qtyterimalast
                    from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                    left JOIN rekanan_m as rek on rek.id=spd.objectrekananfk
                    left JOIN status_barang_m as sb on sb.id = spd.objectstatusbarang
                    where spd.kdprofile = $idProfile and spd.noorderfk=:norec"),
            array(
                'norec' => $request['norecOrder'],
            )
        );
        $jmlstok=0;
        $details=[];
        foreach ($dataStok as $item){
            $i = $i+1;
            if ($item->qtyterimalast == null){
                $qtyterima = 0;
            }else{
                $qtyterima = (float)$item->qtyterimalast;
            }
            if ((float)$item->qtyproduk - $qtyterima > 0){
                $details[] = array(
                    'no' => $i,
                    'kdproduk' => $item->kdproduk,
                    'produkfk' => $item->produkfk,
                    'norec_op' => $item->norec_op,
                    'namaproduk' => $item->namaproduk,
                    'namarekanan' => $item->namarekanan,
                    'rekananfk' =>$item->objectrekananfk,
                    'nilaikonversi' => $item->hasilkonversi,
                    'satuanstandarfk' => $item->ssid,
                    'satuanstandar' => $item->satuanstandar,
                    'satuanviewfk' => $item->ssid,
                    'satuanview' => $item->satuanstandar,
                    'spesifikasi' => $item->deskripsiprodukquo,
                    'jmlstok' => $jmlstok,
                    'jumlahsppb' =>(float)$item->qtyproduk,
                    'jumlahterima' => $qtyterima,
                    'jumlah' => (float)$item->qtyproduk - $qtyterima,
                    'hargasatuan' => $item->hargasatuan,
                    'hargasatuanquo' => $item->hargasatuanquo,
                    'hargadiscountquo' => $item->hargadiscountquo,
                    'hargappnquo' => $item->hargappnquo,
                    'qtyprodukkonfirmasi' => $item->qtyprodukkonfirmasi,
                    'qtyterima' => $item->qtyterimalast,
                    'hargasatuankonfirmasi' => $item->hargasatuanquo,
                    'totalkonfirmasi' => $item->totalkonfirmasi,
                    'totalkonfirmasiss'=> $item->totalkonfirmasiss,
                    'hargadiscount' => $item->hargadiscount,
                    'ppn' => $item->hargappn,
                    'subtotal' => $item->subtotal ,
                    'total' => $item->total ,
                    'ruanganfk'=> 50 ,
                    'asalprodukfk'=> $item->apid ,
                    'asalproduk'=> $item->asalproduk ,
                    'persendiscount'=> 0 ,
                    'persenppn'=> 0 ,
                    'keterangan'=> '',
                    'nobatch'=> '',
                    'statusbarang'=> $item->statusbarang,
                    'tglkadaluarsa'=> null,
                    'tglkebutuhan'=> $item->tglkebutuhan,
                );
            }
        }

        $result = array(
            'detail' => $detail,
            'details' => $details,
            'datalogin' => $dataReq,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDataDetailRekanan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data  = \DB::table('rekanan_m as st')
            ->select('st.id','st.namarekanan','st.alamatlengkap','st.telepon','st.faksimile')
            ->where('st.statusenabled',true)
            ->where('st.kdprofile',$idProfile)
            ->orderBy('st.namarekanan');

        if (isset($request['rekananid']) && $request['rekananid'] != "" && $request['rekananid'] != "undefined") {
            $data = $data->where('st.id', '=', $request['rekananid']);
        }
        $data = $data->get();

        return $this->respond($data);
    }

    public function getNomorSPPB (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukorder_t as so')
            ->where('kdprofile',$idProfile)
            ->select('so.noorder');

        if(isset($request['NoSPPB']) && $request['NoSPPB']!="" && $request['NoSPPB']!="undefined"){
            $data = $data->where('so.noorder', $request['NoSPPB']);
        }

        $data = $data->where('so.statusenabled',true);
        $data = $data->get();
        return $this->respond($data);
    }

    public function SaveSPPB(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $noOrder =  $request['strukorder']['noorder'];
        $alamat='';
        if ($request['strukorder']['alamat'] != ''){
            $alamat=$request['strukorder']['alamat'];
        }
        try{
            foreach ($request['details'] as $item) {
                if($item['norec_op'] != null) {
                    $dataUpSO = OrderPelayanan::where('norec', $item['norec_op'])
                        ->update([
                            'qtyprodukkonfirmasi' => $item['qtyprodukkonfirmasi'],
                            'hargasatuanquo'=> $item['hargasatuan'],
                            'hargadiscountquo' => $item['hargadiscount'],
                            'hargappnquo' => $item['ppn']
                        ]);
                }
            }
            if ($request['strukorder']['norec'] == '') {
                $prefix = '/RSAB-SPPB' . '/' . $this->KonDecRomawi($this->getDateTime()->format('m')) . '/' . $this->getDateTime()->format('y');
                $resultr = StrukOrder::where('noorder', 'ILIKE', '%' . $prefix)->max('noorder');
                $subPrefix = str_replace($prefix, '', $resultr);
                //$noOrder = (str_pad((int)$subPrefix + 1, 3, "0", STR_PAD_LEFT)) . '' . $prefix;
                $dataSO = new StrukOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->isdelivered = 0;
            }else {
                $dataSO = StrukOrder::where('norec', $request['strukorder']['norec'])->first();
                $noStruk = $dataSO->nostruk;

                $delSPD = OrderPelayanan::where('strukorderfk', $request['strukorder']['norec'])
                    ->delete();
            }
            $dataSO->noorder = $noOrder;
            $dataSO->keteranganorder = $request['strukorder']['keteranganorder'];
            $dataSO->objectpegawaiorderfk = $request['strukorder']['pegawaiorderfk'];
            $dataSO->qtyjenisproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->qtyproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->namarekanansales = $request['strukorder']['namarekanansales'];
            $dataSO->objectrekanansalesfk = $request['strukorder']['objectrekananfk'];
            $dataSO->tglorder = $request['strukorder']['tglorder'];
            $dataSO->statusorder = 0;
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim = 0;
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = $request['strukorder']['total'];
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn = 0;
            $dataSO->totalhargasatuan = $request['strukorder']['total'];
            $dataSO->alamat = $alamat;
            $dataSO->alamattempattujuan = $request['strukorder']['notelpmobile'];
            $dataSO->keteranganlainnya = $request['strukorder']['koordinator'];
            $dataSO->jenisusulanfk = $request['strukorder']['koordinatorid'];
            $dataSO->tglvalidasi = $request['strukorder']['tglusulan'];
            $dataSO->noorderintern = $request['strukorder']['nousulan'];
            $dataSO->keterangankeperluan = $request['strukorder']['namapengadaan'];
            $dataSO->nokontrakspk = $request['strukorder']['nokontrak'];
            $dataSO->noorderrfq = $request['strukorder']['tahunusulan'];
            $dataSO->nourutlogin = $request['strukorder']['jmlHari'];
            if (isset($request['strukorder']['objectmataanggaranfk']) || $request['strukorder']['objectmataanggaranfk'] != ""){
                $dataSO->objectmataanggaranfk = $request['strukorder']['objectmataanggaranfk'];
            }
            $dataSO->objectkelompoktransaksifk = 88;
            $dataSO->save();
            $dataSO = $dataSO->norec;

            foreach ($request['details'] as $item) {
                $dataOP = new OrderPelayanan();
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = $idProfile;
                $dataOP->statusenabled = true;
                $dataOP->hasilkonversi = $item['nilaikonversi'];
                $dataOP->iscito = 0;
                $dataOP->noorderfk = $dataSO;
                $dataOP->objectprodukfk = $item['produkfk'];
                $dataOP->objectasalprodukfk = $request['strukorder']['asalprodukfk'];
                $dataOP->qtyproduk=$item['jumlah'];
                $dataOP->qtyprodukkonfirmasi = $item['qtyprodukkonfirmasi'];
                $dataOP->qtyprodukretur = 0;
                $dataOP->objectsatuanstandarfk = $item['satuanviewfk'];
                $dataOP->strukorderfk = $dataSO;
                $dataOP->tglpelayanan = $request['strukorder']['tglorder'];
                $dataOP->hargasatuan = $item['hargasatuan'];
                $dataOP->hargadiscount = $item['hargadiscount'];
                $dataOP->hargappn = $item['ppn'];
                $dataOP->save();
            }

            //***** Struk Realisasi *****
            $norecSR = '';
            $norecso='';
            if ($request['strukorder']['norecrealisasi'] == '') {
                $dataSR= new StrukRealisasi();
                $norealisasi = $this->generateCode(new StrukRealisasi(),'norealisasi',10,'RA-'.$this->getDateTime()->format('ym'), $idProfile);
                $dataSR->norec = $dataSR->generateNewId();
                $dataSR->kdprofile = $idProfile;
                $dataSR->statusenabled = true;
                $dataSR->norealisasi = $norealisasi;
                $dataSR->tglrealisasi = $request['strukorder']['tglorder'];
                $dataSR->totalbelanja = $request['strukorder']['total'];
                if (isset($request['strukorder']['objectmataanggaranfk']) || $request['strukorder']['objectmataanggaranfk'] != ""){
                    $dataSR->objectmataanggaranfk = $request['strukorder']['objectmataanggaranfk'];
                }
                $dataSR->save();
                $dataSR=$dataSR->norec;

                if ($request['strukorder']['norecrealisasi'] == null) {
                    $norecSR = $dataSR;
                }else{
                    $norecSR = $request['strukorder']['norecrealisasi'];
                }

            }else {
                $dataSR = StrukRealisasi::where('norec', $request['strukorder']['norecrealisasi'])->first();
                $dataSR->tglrealisasi = $request['strukorder']['tglorder'];
                $dataSR->totalbelanja = $request['strukorder']['total'];
                $dataSR->save();

                if ($request['strukorder']['norecrealisasi'] == null) {
                    $norecSR = $dataSR;
                }else{
                    $norecSR = $request['strukorder']['norecrealisasi'];
                }
            }

            if ($request['strukorder']['norec'] == null) {
                $norecso = $dataSO;
            }else{
                $norecso = $request['strukorder']['norec'];
            }

            //***** Riwayat Realisasi *****
            if ($request['strukorder']['norecrealisasi'] != '' || $dataSR != '') {
                $dataRR= new RiwayatRealisasi();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = $idProfile;
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 88;
            }else {
                $dataRR = RiwayatRealisasi::where('objectstrukrealisasifk', $request['strukorder']['norecrealisasi'])->first();
            }
            $dataRR->objectstrukrealisasifk = $norecSR;
            $dataRR->objectstrukfk = $norecso;
            $dataRR->sppbfk = $dataSO;
            $dataRR->tglrealisasi = $request['strukorder']['tglorder'];
            $dataRR->objectpetugasfk = $request['strukorder']['pegawaiorderfk'];
            $dataRR->noorderintern = $request['strukorder']['nousulan'];
            $dataRR->keteranganlainnya = $request['strukorder']['keteranganorder'];
            $dataRR->save();

            $transStatus = 'true';
        } catch (Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Order SPPB";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan SPPB Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSO,
                "norecSR" => $norecSR,
                "norecso"=> $norecso,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan SPPB Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO,
                "norecSR" => $norecSR,
                "norecso"=> $norecso,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getNoTerimaGenerate(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noStruk = '';
        $noBuktiKK = '';
        if ($request['asalproduk'] == 7) {
            $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 13, 'RS/' . $this->getDateTime()->format('ym/'), $idProfile);
            $noBuktiKK = $this->generateCode(new StrukPelayanan, 'nostruk_intern', 13, 'KK/' . $this->getDateTime()->format('ym/'), $idProfile);
        } else {
            $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 13, 'RS/' . $this->getDateTime()->format('ym/'), $idProfile);
            $noBuktiKK = '';
        }
        $result = array(
            'noStruk' => $noStruk,
            'noBuktiKK' => $noBuktiKK,
            'message' => 'Cepot'
        );
        return $this->respond($result);
    }

    public function getDetailPenerimaanBarang(Request $request){
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
            ->where('sr.kdprofile',$idProfile);
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
            ->where('sp.kdprofile',$idProfile);

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
                'hargadiscount' => $item->hargadiscount,
                'persendiscount' => $item->persendiscount,
                'ppn' => $item->hargappn,
                'persenppn' => $item->persenppn,
                'total' =>  ((float)$item->hargasatuan *  (float) $item->jumlah )- (float)$item->hargadiscount + (float)$item->hargappn,
                'keterangan' => $item->keteranganlainnya,
                'nobatch' => $item->nobatch,
                'tglkadaluarsa' => $item->tglkadaluarsa,
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

    public function getDetailDataSPk(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataReq = $request->all();
        $dataStruk = \DB::table('strukorder_t as sp')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.objectstrukfk','=','sp.norec')
            ->LEFTJOIN('riwayatrealisasi_t as rr2','rr2.kontrakfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('strukrealisasi_t as sr2','sr2.norec','=','rr2.objectstrukrealisasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('pegawai_m as pg3','pg3.id','=','sp.objectpegawaispkfk')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('mataanggaran_t as ma','ma.norec','=','sr.objectmataanggaranfk')
            ->LEFTJOIN('mataanggaran_t as ma1','ma1.norec','=','sr2.objectmataanggaranfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru1','ru1.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('jenisusulan_m as ju','ju.jenisusulan','=','sp.keteranganlainnya')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap','pg.id as pgid',
                'pg2.id as pegawaimengetahuiid','pg2.namalengkap as pegawaimengetahui','pg2.nippns',
                'sp.alamat','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.nokontrakspk','sp.noorderrfq','sp.keteranganorder','rkn.namarekanan',
                'rkn.id as rknid','sp.namarekanansales','sp.totalhargasatuan','sr.norealisasi','sr.norec as norecrealisasi',
                'sr.objectmataanggaranfk','ma.mataanggaran','ma.saldoawalblu','rr.norec as norecrr','rr.objectkelompoktransaksifk as keltransaksi',
                'sp.objectsrukverifikasifk','rr.objectstrukfk','rr2.objectstrukrealisasifk as norecrealisasikontrak','rr2.norec as rrnoreckontrak',
                'sr2.objectmataanggaranfk as objectmataanggaranfkkontrak','ma1.mataanggaran as mataanggarankontrak','ma1.saldoawalblu as saldoawalbluawalkontrak',
                'sp.tglhps','sp.noorderhps','ru.id as idunitpengusul','ru.namaruangan as unitpengusul',
                'ru1.id as idunittujuan','ru1.namaruangan as unittujuan','sp.nokontrak as kontrak','pg3.id as idpegawaispk','pg3.namalengkap as pegawaispk',
                'sp.totalbiayakirim','sp.tglkontrak','ju.id as jenisusulanid'
            )
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norecOrder']);
        }
        $dataStruk = $dataStruk->first();

        $detail = array(
            'tglorder' => $dataStruk->tglorder,
            'noorder' => $dataStruk->noorder,
            'norec' => $dataStruk->norec,
            'penanggungjawabid' => $dataStruk->pgid,
            'penanggungjawab' => $dataStruk->namalengkap,
            'pegawaimengetahuiid' => $dataStruk->pegawaimengetahuiid,
            'nippns' => $dataStruk->nippns,
            'pegawaimengetahui' => $dataStruk->pegawaimengetahui,
            'keterangan' => $dataStruk->keteranganorder,
            'keteranganlainnya' => $dataStruk->keteranganlainnya,
            'jenisusulanid' => $dataStruk->jenisusulanid,
            'alamat' => $dataStruk->alamat,
            'telp' => $dataStruk->alamattempattujuan,
            'koordinator' => $dataStruk->keteranganlainnya,
            'tglusulan' => $dataStruk->tglvalidasi,
            'nousulan' => $dataStruk->noorderintern,
            'namapengadaan' => $dataStruk->keterangankeperluan,
            'nokontrak' => $dataStruk->nokontrakspk,
            'tahunusulan' => $dataStruk->noorderrfq,
            'rekananid' => $dataStruk->rknid,
            'namarekanan' => $dataStruk->namarekanan,
            'totalhargasatuan' => $dataStruk->totalhargasatuan,
            'norealisasi'=>$dataStruk->norealisasi,
            'norecrealisasi'=>$dataStruk->norecrealisasi,
            'objectmataanggaranfk'=>$dataStruk->objectmataanggaranfk,
            'mataanggaran'=>$dataStruk->mataanggaran,
            'saldoawalblu'=>$dataStruk->saldoawalblu,
            'norecrr'=>$dataStruk->norecrr,
            'keltransaksi'=>$dataStruk->keltransaksi,
            'objectsrukverifikasifk'=>$dataStruk->objectsrukverifikasifk,
            'objectstrukfk'=>$dataStruk->objectstrukfk,
            'norecrealisasikontrak'=>$dataStruk->norecrealisasikontrak,
            'rrnoreckontrak'=>$dataStruk->rrnoreckontrak,
            'objectmataanggaranfkkontrak'=>$dataStruk->objectmataanggaranfkkontrak,
            'mataanggarankontrak'=>$dataStruk->mataanggarankontrak,
            'saldoawalbluawalkontrak'=>$dataStruk->saldoawalbluawalkontrak,
            'tglhps'=>$dataStruk->tglhps,
            'noorderhps'=>$dataStruk->noorderhps,
            'idunitpengusul' =>$dataStruk->idunitpengusul,
            'unitpengusul' =>$dataStruk->unitpengusul,
            'idunittujuan' =>$dataStruk->idunittujuan,
            'unittujuan' =>$dataStruk->unittujuan,
            'kontrak' => $dataStruk->kontrak,
            'idpegawaispk' => $dataStruk->idpegawaispk,
            'pegawaispk' => $dataStruk->pegawaispk,
            'totalbiayakirim' => $dataStruk->totalbiayakirim,
            'tglkontrak' => $dataStruk->tglkontrak,
        );

        $i = 0;
        $dataStok = $details = \DB::select(DB::raw("select spd.norec as norec_op, pr.id as produkfk,pr.namaproduk,spd.objectrekananfk,rek.namarekanan,
                    ss.satuanstandar,ss.id as ssid,spd.qtyproduk,spd.qtyterimalast,spd.hargasatuan,spd.hargappn,
					spd.hargadiscount,spd.qtyprodukkonfirmasi,spd.deskripsiprodukquo,spd.hargasatuanquo,spd.hargappnquo,
					spd.hargadiscountquo,sb.name as statusbarang,
                    (spd.qtyproduk*(spd.hargasatuan+spd.hargappn-spd.hargadiscount)) as total,
                    (spd.qtyprodukkonfirmasi*(spd.hargasatuanquo+spd.hargappnquo-spd.hargadiscountquo)) as totalkonfirmasiss,
                    spd.hasilkonversi,spd.noorderfk,spd.objectasalprodukfk,ap.id as apid,ap.asalproduk,
                    spd.tglpelayananakhir as tglkebutuhan,spd.qtyterimalast
                    from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                    left JOIN rekanan_m as rek on rek.id=spd.objectrekananfk
                    left JOIN status_barang_m as sb on sb.id = spd.objectstatusbarang
                    where spd.kdprofile = $idProfile and spd.noorderfk=:norec"),
            array(
                'norec' => $request['norecOrder'],
            )
        );
        $jmlstok=0;
        $details=[];
        foreach ($dataStok as $item){
            $i = $i+1;
            if ($item->qtyterimalast == null){
                $qtyterima = 0;
            }else{
                $qtyterima = (float)$item->qtyterimalast;
            }
            if ((float)$item->qtyproduk != $qtyterima || $qtyterima == 0){
                $details[] = array(
                    'no' => $i,
                    'produkfk' => $item->produkfk,
                    'norec_op' => $item->norec_op,
                    'namaproduk' => $item->namaproduk,
                    'namarekanan' => $item->namarekanan,
                    'rekananfk' =>$item->objectrekananfk,
                    'nilaikonversi' => $item->hasilkonversi,
                    'satuanstandarfk' => $item->ssid,
                    'satuanstandar' => $item->satuanstandar,
                    'satuanviewfk' => $item->ssid,
                    'satuanview' => $item->satuanstandar,
                    'spesifikasi' => $item->deskripsiprodukquo,
                    'jmlstok' => $jmlstok,
                    'jumlahspk' =>(float)$item->qtyproduk,
                    'jumlahterima' => $qtyterima,
                    'jumlah' => (float)$item->qtyproduk - $qtyterima,
                    'hargasatuan' => $item->hargasatuan,
                    'hargasatuanquo' => $item->hargasatuanquo,
                    'hargappnquo' => $item->hargappnquo,
                    'hargadiscountquo' => $item->hargadiscountquo,
                    'qtyprodukkonfirmasi' => $item->qtyprodukkonfirmasi,
                    'qtyterima' => $item->qtyterimalast,
                    'hargasatuankonfirmasi' => $item->hargasatuanquo,
//                    'totalkonfirmasi' => $item->totalkonfirmasi,
                    'totalkonfirmasiss'=> $item->totalkonfirmasiss,
                    'hargadiscount' => $item->hargadiscount,
                    'ppn' => $item->hargappn,
                    'total' => $item->total ,
                    'ruanganfk'=> 50 ,
                    'asalprodukfk'=> $item->apid ,
                    'asalproduk'=> $item->asalproduk ,
                    'persendiscount'=> 0 ,
                    'persenppn'=> 0 ,
                    'keterangan'=> '' ,
                    'nobatch'=> '' ,
                    'statusbarang'=> $item->statusbarang,
                    'tglkadaluarsa'=> null,
                    'tglkebutuhan'=> $item->tglkebutuhan,
                );
            }
        }

        $result = array(
            'detail' => $detail,
            'details' => $details,
            'datalogin' => $dataReq,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDetailSPPBPerItem(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataReq = $request->all();
        $dataStruk = \DB::table('strukorder_t as sp')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.objectstrukfk','=','sp.norec')
            ->LEFTJOIN('riwayatrealisasi_t as rr2','rr2.sppbfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('strukrealisasi_t as sr2','sr2.norec','=','rr2.objectstrukrealisasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg1','pg1.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('rekanan_m as rkn1','rkn1.id','=','sp.objectrekanansalesfk')
            ->LEFTJOIN('mataanggaran_t as ma','ma.norec','=','sr.objectmataanggaranfk')
            ->LEFTJOIN('mataanggaran_t as ma1','ma1.norec','=','sr2.objectmataanggaranfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru1','ru1.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap','pg.id as pgid','pg1.nippns',
                'sp.alamat','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.nokontrakspk','sp.noorderrfq','sp.keteranganorder','rkn.namarekanan','rkn.id as rknid',
                'sp.namarekanansales','sp.totalhargasatuan','sp.objectpetugasfk','pg1.namalengkap as mengetahui','pg1.nippns',
                'rkn1.namarekanan as namarekanansales','sp.objectrekanansalesfk','rkn.alamatlengkap as alamatrekanan',
                'rkn1.alamatlengkap as alamatrekanansales','rkn.faksimile as faxrekanan','rkn.telepon as tlprekanan',
                'rkn1.faksimile as faxrekanansales','rkn1.telepon as tlprekanansales','sr.norealisasi','sr.norec as norecrealisasi',
                'rr.norec as norecrrusulan','sr2.norec as norecrealisasisppb','rr2.norec as norecrrsppb','sr.objectmataanggaranfk as mataanggranid','ma.mataanggaran',
                'sr2.objectmataanggaranfk as mataanggranfk','ma1.mataanggaran as mataanggaransppb','ru.id as idunitpengusul','ru.namaruangan as unitpengusul',
                'ru1.id as idunittujuan','ru1.namaruangan as unittujuan',
                \DB::raw('EXTRACT (YEAR from sp.tglorder) AS tahunusulan'))
            ->where('sp.kdprofile', $idProfile);
        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norecOrder']);
        }
        $dataStruk = $dataStruk->first();

        $detail = array(
            'tglorder' => $dataStruk->tglorder,
            'noorder' => $dataStruk->noorder,
            'norec' => $dataStruk->norec,
            'petugasid' => $dataStruk->pgid,
            'petugas' => $dataStruk->namalengkap,
            'petugasmengetahui'=> $dataStruk->mengetahui,
            'petugasmengetahuiid' => $dataStruk->objectpetugasfk,
            'nippns' => $dataStruk->nippns,
            'keterangan' => $dataStruk->keteranganorder,
            'alamat' => $dataStruk->alamat,
            'telp' => $dataStruk->alamattempattujuan,
            'koordinator' => $dataStruk->keteranganlainnya,
            'tglusulan' => $dataStruk->tglvalidasi,
            'nousulan' => $dataStruk->noorderintern,
            'namapengadaan' => $dataStruk->keterangankeperluan,
            'nokontrak' => $dataStruk->nokontrakspk,
            'tahunusulan' => $dataStruk->tahunusulan,
            'namarekananid' => $dataStruk->rknid,
            'namarekanan' => $dataStruk->namarekanan,
            'alamatrekanan' => $dataStruk->alamatrekanan,
            'faxrekanan'=> $dataStruk->faxrekanan,
            'tlprekanan'=> $dataStruk->tlprekanan,
            'rekanansalesfk'=> $dataStruk->objectrekanansalesfk,
            'namarekanansales'=> $dataStruk->namarekanansales,
            'alamatrekanansales'=>$dataStruk->alamatrekanansales,
            'faxrekanansales'=> $dataStruk->faxrekanansales,
            'tlprekanansales'=> $dataStruk->tlprekanansales,
            'totalhargasatuan' => $dataStruk->totalhargasatuan,
            'norealisasi'=>$dataStruk->norealisasi,
            'norecrealisasi'=>$dataStruk->norecrealisasi,
            'norecrealisasisppb'=>$dataStruk->norecrealisasisppb,
            'norecrrusulan'=>$dataStruk->norecrrusulan,
            'norecrrsppb'=>$dataStruk->norecrrsppb,
            'norecrrsppb'=>$dataStruk->norecrrsppb,
            'mataanggranid'=>$dataStruk->mataanggranid,
            'mataanggaran'=>$dataStruk->mataanggaran,
            'mataanggranfk'=>$dataStruk->mataanggranfk,
            'mataanggaransppb'=>$dataStruk->mataanggaransppb,
            'idunitpengusul' =>$dataStruk->idunitpengusul,
            'unitpengusul' =>$dataStruk->unitpengusul,
            'idunittujuan' =>$dataStruk->idunittujuan,
            'unittujuan' =>$dataStruk->unittujuan,
        );

        $i = 0;
        $str = explode(',',$request['produkfk']);
        for ($i = 0; $i < count($str); $i++){
            $arr = (int)$str[$i];
            $str[$i] = $arr;
        }
        $produkfk = implode(',',$str);
        $dataStok = $details = DB::select(DB::raw("
                    select spd.norec as norec_op, pr.id as produkfk,pr.namaproduk,spd.objectrekananfk,rek.namarekanan,pr.kdproduk,
                    ss.satuanstandar,ss.id as ssid,spd.qtyproduk,spd.qtyterimalast,spd.hargasatuan,spd.deskripsiprodukquo,
                    spd.hargasatuanquo,spd.qtyprodukkonfirmasi,spd.hargadiscountquo,spd.hargappnquo,spd.hargadiscount,spd.hargappn,sb.name as statusbarang,
                    (spd.qtyproduk*(spd.hargasatuan+spd.hargappn-spd.hargadiscount)) as total,
                    (spd.qtyproduk*(spd.hargasatuanquo-spd.hargadiscountquo+spd.hargappnquo)) totalkonfirmasi,
                    (spd.qtyprodukkonfirmasi*(spd.hargasatuanquo)) as totalkonfirmasiss,
                    spd.hasilkonversi,spd.noorderfk,spd.objectasalprodukfk,ap.id as apid,ap.asalproduk,
                    spd.tglpelayananakhir as tglkebutuhan,spd.qtyterimalast
                    from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    left JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                    left JOIN rekanan_m as rek on rek.id=spd.objectrekananfk
                    left JOIN status_barang_m as sb on sb.id = spd.objectstatusbarang
                    where spd.kdprofile = $idProfile and spd.noorderfk=:norec and pr.id in ($produkfk) "),
            array(
                'norec' => $request['norecOrder'],
            )
        );
        $jmlstok=0;
        $details=[];
        foreach ($dataStok as $item){
            $i = $i+1;
            if ($item->qtyterimalast == null){
                $qtyterima = 0;
            }else{
                $qtyterima = (float)$item->qtyterimalast;
            }
            if ((float)$item->qtyproduk - $qtyterima > 0){
                $details[] = array(
                    'no' => $i,
                    'kdproduk' => $item->kdproduk,
                    'produkfk' => $item->produkfk,
                    'norec_op' => $item->norec_op,
                    'namaproduk' => $item->namaproduk,
                    'namarekanan' => $item->namarekanan,
                    'rekananfk' =>$item->objectrekananfk,
                    'nilaikonversi' => $item->hasilkonversi,
                    'satuanstandarfk' => $item->ssid,
                    'satuanstandar' => $item->satuanstandar,
                    'satuanviewfk' => $item->ssid,
                    'satuanview' => $item->satuanstandar,
                    'spesifikasi' => $item->deskripsiprodukquo,
                    'jmlstok' => $jmlstok,
                    'jumlahsppb' =>(float)$item->qtyproduk,
                    'jumlahterima' => $qtyterima,
                    'jumlah' => (float)$item->qtyproduk - $qtyterima,
                    'hargasatuan' => $item->hargasatuan,
                    'hargasatuanquo' => $item->hargasatuanquo,
                    'hargadiscountquo' => $item->hargadiscountquo,
                    'hargappnquo' => $item->hargappnquo,
                    'qtyprodukkonfirmasi' => $item->qtyprodukkonfirmasi,
                    'qtyterima' => $item->qtyterimalast,
                    'hargasatuankonfirmasi' => $item->hargasatuanquo,
                    'totalkonfirmasi' => $item->totalkonfirmasi,
                    'totalkonfirmasiss'=> $item->totalkonfirmasiss,
                    'hargadiscount' => $item->hargadiscount,
                    'ppn' => $item->hargappn,
                    'total' => $item->total ,
                    'ruanganfk'=> 50 ,
                    'asalprodukfk'=> $item->apid ,
                    'asalproduk'=> $item->asalproduk ,
                    'persendiscount'=> 0 ,
                    'persenppn'=> 0 ,
                    'keterangan'=> '',
                    'nobatch'=> '',
                    'statusbarang'=> $item->statusbarang,
                    'tglkadaluarsa'=> null,
                    'tglkebutuhan'=> $item->tglkebutuhan,
                );
            }
        }

        $result = array(
            'detail' => $detail,
            'details' => $details,
            'datalogin' => $dataReq,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function saveTerimaBarangSuplier(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try {
            $req = $request;
            $status = "";
            $noStruk = "";
            if ($request['struk']['nostruk'] == '') {
                $SP = new StrukPelayanan();
                $norecSP = $SP->generateNewId();
                $noStruk = $req['struk']['noterima']; //$request['struk']['noterima'];//$this->generateCode(new StrukPelayanan, 'nostruk', 13, 'RS/'.$this->getDateTime()->format('ym/'));
                $SP->norec = $norecSP;
                $SP->kdprofile = $idProfile;
                $SP->statusenabled = true;
                $SP->noorderfk = $req['struk']['norecOrder'];
//                $SP->nostruk = $noStruk;
                if ($request['struk']['asalproduk'] == 7) {
                    $noStrukKasKecil = $req['struk']['noBuktiKK'];//$request['struk']['noBuktiKK'];//$this->generateCode(new StrukPelayanan, 'nostruk_intern', 13, 'KK/'.$this->getDateTime()->format('ym/'));
                    $SP->nostruk_intern = $noStrukKasKecil;
                    $SP->objectruanganasalfk = $request['struk']['ruanganfkKK'];
                    $SP->tglspk = $request['struk']['tglKK'];
                    $SP->objectpegawaipenanggungjawabfk = $request['struk']['pegawaifkKK'];
                    $SP->nosppb = $req['struk']['noorder'];
                }
                if ($request['struk']['norecsppb'] != '') {
                    $data = StrukOrder::where('norec', $request['struk']['norecsppb'])->first();
//                    $SP->nosppb = $data->noorder;
                    $SP->nosppb = $req['struk']['noorder'];
                    foreach ($req['details'] as $item) {
                        $dataOP = OrderPelayanan::where('noorderfk', $request['struk']['norecsppb'])
                            ->where('kdprofile', $idProfile)
                            ->where('objectprodukfk', $item['produkfk'])
                            ->update([
                                    'qtyterimalast' => (float)$item['jumlah'] + (float)$item['jumlahterima']]
                            );

                    }
                }
                $SP->noterima = $noStruk;

            } else {
                $dataKS =  KartuStok::where('keterangan',  'Penerimaan Barang Suplier. No Terima. ' . $req['struk']['noterima'] . ' Faktur No.' . $req['struk']['nofaktur'] . ' ' . $req['struk']['namarekanan'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                        'flagfk' => null
                    ]);
//                    ->first();

//                return $this->respond($dataKS);

                //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                $dataKembaliStok = DB::select(DB::raw("select sp.norec,spd.qtyproduk,spd.hasilkonversi,sp.objectruanganfk,spd.objectprodukfk,
                          sp.nostruk
                                from strukpelayanandetail_t as spd
                                INNER JOIN strukpelayanan_t sp on sp.norec=spd.nostrukfk
                                where sp.kdprofile = $idProfile and sp.norec=:norec"),
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
                            if ($request['struk']['norecOrder'] != '') {
//                    $data = StrukOrder::where('norec', $request['struk']['norecOrder'])->first();
//                    $noordeFk = $data->norec;
                                foreach ($req['details'] as $item) {
                                    $dataOP = OrderPelayanan::where('noorderfk', $request['struk']['norecOrder'])
                                        ->where('kdprofile', $idProfile)
                                        ->where('objectprodukfk', $item['produkfk'])
                                        ->update([
                                                'qtyterimalast' => (float)$item['jumlah']]
                                        );

                                }

                            }

                            $tglnow =  date('Y-m-d H:i:s');
                            $tglUbah = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow)));

                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $idProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                            $newKS->keterangan = 'Ubah Penerimaan No. ' . $item5->nostruk;
                            $newKS->produkfk = $item5->objectprodukfk;
                            $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
                            $newKS->saldoawal = (float)$saldoAwal - (float)$TambahStok;
                            $newKS->status = 0;
                            $newKS->tglinput = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->tglkejadian = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->nostrukterimafk = $request['struk']['nostruk'];
                            $newKS->norectransaksi = $request['struk']['norecOrder'];
                            $newKS->tabletransaksi = 'orderpelayanan_t';
                            $newKS->save();

                            //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                            $SP = StrukPelayanan::where('norec', $request['struk']['nostruk'])->first();
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



                            if ($request['struk']['norecOrder'] != '') {
                                //$data = StrukOrder::where('norec', $request['struk']['norecOrder'])->first();
                                //$noordeFk = $data->norec;
                                foreach ($req['details'] as $item) {
                                    $dataOP = OrderPelayanan::where('noorderfk', $request['struk']['norecOrder'])
                                        ->where('kdprofile', $idProfile)
                                        ->where('objectprodukfk', $item['produkfk'])
                                        ->update([
                                                'qtyterimalast' => (float)$item['jumlah']]
                                        );
                                }
                            }
                            $hasil = 0;
                            $penamBahan = (float)$saldoAwal - (float)$TambahStok;
                            if ($penamBahan < 0){
                                $hasil = 0;
                            }else{
                                $hasil = (float)$saldoAwal - (float)$TambahStok;
                            }

                            $tglnow1 =  date('Y-m-d H:i:s');
                            $tglUbah1 = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow1)));

                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $idProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                            $newKS->keterangan = 'Ubah Penerimaan No. ' . $item5->nostruk;
                            $newKS->produkfk = $item5->objectprodukfk;
                            $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
                            $newKS->saldoawal = $hasil;//(float)$saldoAwal - (float)$TambahStok;
                            $newKS->status = 0;
                            $newKS->tglinput = $tglUbah1; //date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->tglkejadian = $tglUbah1; //date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->nostrukterimafk = $request['struk']['nostruk'];
                            $newKS->norectransaksi = $request['struk']['norecOrder'];
                            $newKS->tabletransaksi = 'orderpelayanan_t';
                            $newKS->save();

                            //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                            $SP = StrukPelayanan::where('norec', $request['struk']['nostruk'])->first();
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

                        }
                    }
                }
            }
            $SP->nostruk = $noStruk;
            $SP->objectkelompoktransaksifk = $req['struk']['kelompoktranskasi'];
            $SP->objectrekananfk = $req['struk']['rekananfk'];
            $SP->namarekanan = $req['struk']['namarekanan'];
            $SP->objectruanganfk = $req['struk']['ruanganfk'];
            $SP->keteranganlainnya = 'Penerimaan Barang Dari Supplier';
            $SP->nokontrak = $req['struk']['nokontrak'];;
            $SP->nofaktur = $req['struk']['nofaktur'];
            $SP->tglfaktur = date('Y-m-d H:i:s', strtotime($req['struk']['tglfaktur']));//$req['struk']['tglfaktur'];
            $SP->tglstruk = date('Y-m-d H:i:s', strtotime($req['struk']['tglstruk']));//$req['struk']['tglstruk'];
            $SP->objectpegawaipenerimafk = $req['struk']['pegawaimenerimafk'];
            $SP->objectpegawaimenerimafk = $req['struk']['pegawaimenerimafk'];
            $SP->objectpegawaipenanggungjawabfk = $req['struk']['objectpegawaipenanggungjawabfk'];
            $SP->namapegawaipenerima = $req['struk']['namapegawaipenerima'];
            $SP->qtyproduk = $req['struk']['qtyproduk'];
            $SP->totalharusdibayar = $req['struk']['totalharusdibayar'];
            $SP->totalppn = $req['struk']['totalppn'];
            $SP->totaldiscount = $req['struk']['totaldiscount'];
            $SP->totalhargasatuan = $req['struk']['totalhargasatuan'];
            $SP->keteranganambil = $req['struk']['ketterima'];
            $SP->tgldokumen = $req['struk']['tglorder'];
            $SP->tglkontrak = $req['struk']['tglkontrak'];
            $SP->namapengadaan = $req['struk']['namapengadaan'];
            $SP->tgljatuhtempo = date('Y-m-d H:i:s', strtotime($req['struk']['tgljatuhtempo']));//$req['struk']['tgljatuhtempo'];
            $SP->save();


            foreach ($req['details'] as $item) {
                $qtyJumlah = (float)$item['jumlah'] * (float)$item['nilaikonversi'];

                $SPD = new StrukPelayananDetail();
                $norecKS = $SPD->generateNewId();
                $SPD->norec = $norecKS;
                $SPD->kdprofile = $idProfile;
                $SPD->statusenabled = true;
                $SPD->nostrukfk = $SP->norec;
                $SPD->objectasalprodukfk = $request['struk']['asalproduk'];//$item['asalprodukfk'];
                $SPD->objectprodukfk = $item['produkfk'];
                $SPD->objectruanganfk = $req['struk']['ruanganfk'];
                $SPD->objectruanganstokfk = $req['struk']['ruanganfk'];
                $SPD->objectsatuanstandarfk = $item['satuanstandarfk'];
                $SPD->hargadiscount = $item['hargadiscount'];
                $SPD->hargadiscountgive = 0;
                $SPD->hargadiscountsave = 0;
                $SPD->harganetto = $item['hargasatuan'];
                $SPD->hargapph = 0;
                $SPD->hargappn = $item['ppn'];
                $SPD->hargasatuan = $item['hargasatuan'];
                $SPD->hasilkonversi = $item['nilaikonversi'];
                $SPD->namaproduk = $item['namaproduk'];
                $SPD->keteranganlainnya = $item['keterangan'];
                $SPD->hargasatuandijamin = 0;
                $SPD->hargasatuanppenjamin = 0;
                $SPD->hargatambahan = 0;
                $SPD->hargasatuanpprofile = 0;
                $SPD->isonsiteservice = 0;
                $SPD->kdpenjaminpasien = 0;
                $SPD->persendiscount = $item['persendiscount'];
                $SPD->persenppn = $item['persenppn'];
                $SPD->qtyproduk = $item['jumlah'];
                $SPD->qtyprodukoutext = 0;
                $SPD->qtyprodukoutint = 0;
                $SPD->qtyprodukretur = 0;
                $SPD->satuan = '-';//$item['satuanstandar'];;
                $SPD->satuanstandar = $item['satuanviewfk'];
                $SPD->tglpelayanan = date('Y-m-d H:i:s', strtotime($req['struk']['tglstruk']));//$req['struk']['tglstruk'];
                $SPD->is_terbayar = 0;
                $SPD->linetotal = 0;
                $SPD->tglkadaluarsa = $item['tglkadaluarsa'];
                $SPD->nobatch = $item['nobatch'];
                $SPD->save();

                //## StokProdukDetail
                $StokPD = new StokProdukDetail();
                $norecStokPD = $StokPD->generateNewId();
                $StokPD->norec = $norecKS;
                $StokPD->kdprofile = $idProfile;
                $StokPD->statusenabled = true;

                $StokPD->objectasalprodukfk = $request['struk']['asalproduk'];//$item['asalprodukfk'];
                $StokPD->hargadiscount = 0;
                $diskon = (((float)$item['persendiscount']) * (float)$item['hargasatuan'])/100;
                $hargaStlhDiskon = (float)$item['hargasatuan']-$diskon;
                $ppn = ((float) $item['persenppn'] * $hargaStlhDiskon )/100;
                $StokPD->harganetto1 = ($hargaStlhDiskon + $ppn) / (float)$item['nilaikonversi'];
                // $StokPD->harganetto1 = ((float)$item['hargasatuan'] + (float)$item['ppn']) / (float)$item['nilaikonversi'];
                $StokPD->harganetto2 = ((float)$item['hargasatuan']) / (float)$item['nilaikonversi'];
                $StokPD->persendiscount = 0;
                $StokPD->objectprodukfk = $item['produkfk'];
                $StokPD->qtyproduk = $qtyJumlah;
                $StokPD->qtyprodukonhand = 0;
                $StokPD->qtyprodukoutext = 0;
                $StokPD->qtyprodukoutint = 0;

                $StokPD->objectruanganfk = $req['struk']['ruanganfk'];
                $StokPD->nostrukterimafk = $SP->norec;
                $StokPD->nobatch = $item['nobatch'];
                $StokPD->objectstrukpelayanandetail = $SPD->norec;
                $StokPD->tglkadaluarsa = $item['tglkadaluarsa'];
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
                $newKS->keterangan = 'Penerimaan Barang Suplier. No Terima. ' . $noStruk . ' Faktur No.' . $req['struk']['nofaktur'] . ' ' . $req['struk']['namarekanan'];
                $newKS->produkfk = $item['produkfk'];
                $newKS->ruanganfk = $req['struk']['ruanganfk'];
                $newKS->saldoawal = (float)$saldoAwal;//- (float)$qtyJumlah;
                $newKS->status = 1;
                $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $SP->norec;
                $newKS->norectransaksi = $SP->norec;
                $newKS->tabletransaksi = 'strukpelayanan_t';
                $newKS->flagfk = 1;
                $newKS->save();

            }
            //***** Struk Realisasi *****
            $datanorecSR = '';
            if ($req['struk']['norecrealisasi'] == '') {
                $dataSR = new StrukRealisasi();
                $norealisasi = $this->generateCode(new StrukRealisasi(), 'norealisasi', 10, 'RA-' . 
                    $this->getDateTime()->format('ym'),$idProfile);
                $dataSR->norec = $dataSR->generateNewId();
                $dataSR->kdprofile = $idProfile;
                $dataSR->statusenabled = true;
                $dataSR->norealisasi = $norealisasi;
                $dataSR->tglrealisasi = date('Y-m-d H:i:s', strtotime($req['struk']['tglrealisasi']));//$req['struk']['tglrealisasi'];
                $dataSR->objectmataanggaranfk = $req['struk']['objectmataanggaranfk'];
                $dataSR->totalbelanja = $req['struk']['totalharusdibayar'];
                $dataSR->save();
                //            if (is_null($req['struk']['norecrealisasi'])) {
                $datanorecSR = $dataSR->norec;

                //            }else{
                //                $datanorecSR = $req['struk']['norecrealisasi'];
                //            }
            } else {
                $dataSR = StrukRealisasi::where('norec', $req['struk']['norecrealisasi'])->first();
                $dataSR->tglrealisasi = date('Y-m-d H:i:s', strtotime($req['struk']['tglstruk']));//$req['struk']['tglstruk'];
                $dataSR->objectmataanggaranfk = $req['struk']['objectmataanggaranfk'];
                $dataSR->totalbelanja = $req['struk']['totalharusdibayar'];
                $dataSR->save();
                //            if (is_null($req['struk']['norecrealisasi'])) {
                //                $datanorecSR = $dataSR->norec;
                //            }else{
                $datanorecSR = $req['struk']['norecrealisasi'];
                //            }
            }

            //***** Riwayat Realisasi *****
//            if ($req['struk']['norecrealisasi'] == '') {
            $dataRR = new RiwayatRealisasi();
            $dataRR->norec = $dataRR->generateNewId();
            $dataRR->kdprofile = $idProfile;
            $dataRR->statusenabled = true;
//            }else {
//                $dataRR = RiwayatRealisasi::where('objectstrukrealisasifk', $req['struk']['norecrealisasi'])->first();
//            }
            $dataRR->objectkelompoktransaksifk = $req['struk']['kelompoktranskasi'];
            $dataRR->objectstrukrealisasifk = $datanorecSR;
            $dataRR->objectstrukfk = $req['struk']['norecOrder'];
            $dataRR->penerimaanfk = $SP->norec;;
            $dataRR->tglrealisasi = date('Y-m-d H:i:s', strtotime($req['struk']['tglrealisasi']));//$req['struk']['tglrealisasi'];
            $dataRR->objectpetugasfk = $req['struk']['pegawaimenerimafk'];
            $dataRR->noorderintern = $req['struk']['nousulan'];
            $dataRR->keteranganlainnya = 'Penerimaan Barang Dari Supplier';
            $dataRR->save();

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

    public function getDaftarSPPBDetail (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
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
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('produk_m as pr','pr.id','=','op.objectprodukfk')
            ->LEFTJOIN('satuanstandar_m as ss','ss.id','=','op.objectsatuanstandarfk')
            ->select(\DB::raw('sp.norec,sp.tglorder,sp.noorder,pg.namalengkap,sp.alamat,sp.alamattempattujuan,sp.keteranganlainnya,sp.tglvalidasi,sp.noorderintern,
                              sp.keterangankeperluan,sp.nokontrakspk,sp.noorderrfq,sp.keteranganorder,rkn.namarekanan,rkn.id as rknid,sp.namarekanansales,
                              sp.totalhargasatuan,sp.status,
                              pr.id as produkfk,pr.namaproduk,ss.satuanstandar,op.qtyproduk,op.qtyterimalast,op.hargasatuan,op.hargadiscount,op.hargappn,
                             (op.qtyproduk*(op.hargasatuan)) as total,
                             (op.qtyprodukkonfirmasi*(op.hargasatuanquo)) as totalkonfirmasi,
                             op.tglpelayananakhir as tglkebutuhan,op.deskripsiprodukquo as spesifikasi,pr.id as prid,
                             op.hargasatuanquo,op.qtyprodukkonfirmasi'))
            ->where('sp.kdprofile',$idProfile);
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
        if(isset($request['noKontrak']) && $request['noKontrak']!="" && $request['noKontrak']!="undefined"){
            $data = $data->where('sp.nokontrakspk','ILIKE','%'. $request['noKontrak']);
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganorder','ILIKE','%'. $request['keterangan']);
        }
        if(isset($request['rekanan']) && $request['rekanan']!="" && $request['rekanan']!="undefined"){
            $data = $data->where('sp.namarekanansales','ILIKE','%'. $request['rekanan']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',88);
        $data = $data->orderBy('sp.noorder');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganorder,
                'alamat' => $item->alamat,
                'telp' => $item->alamattempattujuan,
                'koordinator' => $item->keteranganlainnya,
                'tglusulan' => $item->tglvalidasi,
                'nousulan' => $item->noorderintern,
                'namapengadaan' => $item->keterangankeperluan,
                'nokontrak' => $item->nokontrakspk,
                'tahunusulan' => $item->noorderrfq,
                'namarekanan' => $item->namarekanansales,
                'produkfk' => $item->produkfk,
                'namaproduk' => $item->namaproduk,
                'satuanstandar' => $item->satuanstandar,
                'qtyproduk' => $item->qtyproduk,
                'qtyterimalast' => $item->qtyterimalast,
                'hargasatuan' =>$item->hargasatuan,
                'total' => $item->total,
                'status' => $item->status,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarSPK(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
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
            ->JOIN('orderpelayanan_t as op','op.nokontrakspk','=','sp.nokontrakspk')
            ->LEFTJOIN('strukpelayanan_t as spn','spn.noorderfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('rekanan_m as rk','rk.id','=','sp.objectrekananfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap as penanggungjawab','pg2.namalengkap as mengetahui',
                'sp.tglvalidasi as tglkebutuhan','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.keteranganorder','ru.namaruangan as ruangan','ru.id as ruid',
                'ru2.namaruangan as ruangantujuan','ru2.id as ruidtujuan','sp.qtyproduk',
                'sp.totalhargasatuan','sp.status','pg2.nippns',
                'op.nokontrakspk','sp.tglkontrak','sp.objectrekananfk','rk.namarekanan','spn.norec as norecpenerimaan')
            ->where('sp.kdprofile', $idProfile)
            ->whereNotNull('sp.nokontrakspk');

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
        if(isset($request['noKontrak']) && $request['noKontrak']!="" && $request['noKontrak']!="undefined"){
            $data = $data->where('sp.nokontrakspk','ILIKE','%'. $request['noKontrak'].'%');
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganorder','ILIKE','%'. $request['keterangan']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('op.objectprodukfk','=',$request['produkfk']);
        }

        $data = $data->distinct();
        $data = $data->whereNotNull('op.noorderfk');
        $data = $data->where('sp.statusenabled',true);
        $data = $data->Where('sp.objectkelompoktransaksifk',90);
        $data = $data->orderBy('sp.noorder');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = \DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.qtyterimalast,spd.hargasatuan,spd.hargadiscount,spd.hargappn,
                    (spd.qtyproduk*(spd.hargasatuan+spd.hargappn-hargadiscount)) as total,
                    (spd.qtyprodukkonfirmasi*(spd.hargasatuanquo+spd.hargappn-hargadiscountquo)) as totalkonfirmasi,
                    spd.tglpelayananakhir as tglkebutuhan,spd.deskripsiprodukquo as spesifikasi,pr.id as prid,
                    spd.hargasatuanquo,spd.qtyprodukkonfirmasi
                     from orderpelayanan_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and strukorderfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'penanggungjawab' => $item->penanggungjawab,
                'keterangan' => $item->keteranganorder,
                'koordinator' => $item->keteranganlainnya,
                'tglkebutuhan' => $item->tglkebutuhan,
                'tglusulan' => $item->tglorder,
                'nousulan' => $item->noorderintern,
                'namapengadaan' => $item->keterangankeperluan,
                'mengetahui' => $item->mengetahui,
                'ruangan' => $item->ruangan,
                'ruangantujuan' => $item->ruangantujuan,
                'totalhargasatuan' => $item->totalhargasatuan,
                'status' => $item->status,
                'nospk' => $item->nokontrakspk,
                'tglkontrak' => $item->tglkontrak,
                'supplier' => $item->namarekanan,
                'rekananfk' => $item->objectrekananfk,
                'jmlitem' => $item->qtyproduk,
                'norecpenerimaan' => $item->norecpenerimaan,
                'details' => $details,
            );
        }
        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarUPKKeSPK(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
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

        $data = \DB::table('strukorder_t as so')
            ->leftJoin('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->leftJoin('status_barang_m as sb','sb.id','=','so.objectstatusbarang')
            ->leftJoin('rekanan_m as rk','rk.id','=','so.objectrekananfk')
            ->select('so.keteranganorder','so.norec as norec_so','op.norec as norec_op',
                'so.objectrekananfk','rk.namarekanan',
                'op.objectstatusbarang','sb.name'
            )
            ->where('so.kdprofile', $idProfile)
            ->where('op.objectstatusbarang',2);

        if(isset($request['rekananfk']) && $request['rekananfk']!="" && $request['rekananfk']!="undefined"){
            $data = $data->where('op.objectrekananfk','=',$request['rekananfk']);
        }
        if(isset($request['ketOrder']) && $request['ketOrder']!="" && $request['ketOrder']!="undefined"){
            $data = $data->where('so.keteranganorder','=',$request['ketOrder']);
        }
        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $data = $data->where('so.norec','=',$request['norec']);
        }

        $data = $data->distinct();
        $data = $data->where('so.statusenabled',true);
        $data = $data->get();

        return $this->respond($data);
    }

    public function UpdateStatusUPK(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $data=$request['data'];
        try {
            foreach ($data as $item) {
                $dataOP = OrderPelayanan::where('norec', $item['norec_op'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                        'objectrekananfk' => null,
                        'objectstatusbarang'=> null,
                    ]);
            }

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "";
        }

        if ($transStatus == 'true') {
            $transMessage = "";
            \DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function DeleteSPK(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try {
            $dataSO = StrukOrder::where('norec', $request['norec_so'])
                ->where('kdprofile', $idProfile)
                ->update([
                    'statusenabled'=> 'f',
                ]);

            $dataOP = OrderPelayanan::where('noorderfk', $request['norec_so'])
                ->where('kdprofile', $idProfile)
                ->update([
                    'statusenabled'=> 'f',
                ]);

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Delete SPK";
        }


        if ($transStatus == 'true') {
            $transMessage = "Delete SPK Berhasil";
            \DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Delete SPK Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function SaveSPK(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try{
            if ($request['strukorder']['norec'] == '') {
                //###1###
                //CARI KODE USULAN BERDASARKAN RUANGAN PENG-USUL
                $dataRuanganLogin = \DB::table('maploginusertoruangan_s as mlu')
                    ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
                    ->select('ru.id','ru.namaruangan','ru.website')
                    ->where('ru.id',$request['strukorder']['ruanganfkPengusul'])
                    ->where('mlu.kdprofile', $idProfile)
                    ->first();

                $prefix = '/'. $dataRuanganLogin->website . '/' . $this->KonDecRomawi($this->getDateTime()->format('m')) . '/' . $this->getDateTime()->format('y');
                $resultr = StrukOrder::where('noorder', 'ILIKE', '%' . $prefix)->max('noorder');
                $subPrefix = str_replace($prefix, '', $resultr);
                $noOrder = (str_pad((int)$subPrefix + 1, 3, "0", STR_PAD_LEFT)) . '' . $prefix;
//                $noSPK = $this->generateCode(new StrukOrder(),'nokontrakspk',10,'SK'.$this->getDateTime()->format('ym'));


                $noSPK =  $request['strukorder']['nokontrakspk'];
                //####1####

                $dataSO = new StrukOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
//                $dataSO->noorder = $noSPK;
//                $dataSO->nokontrakspk =$noSPK;

            }else {
                $dataSO = StrukOrder::where('norec', $request['strukorder']['norec'])->first();
                $noStruk = $dataSO->nostruk;

                $delSPD = OrderPelayanan::where('strukorderfk', $request['strukorder']['norec'])
                    ->delete();
            }
            $dataSO->nokontrakspk= $request['strukorder']['nokontrakspk'];
            $dataSO->noorder = $request['strukorder']['nokontrakspk'];
            $dataSO->isdelivered = 0;
            $dataSO->objectkelompoktransaksifk = 90;
            $dataSO->nokontrak = $request['strukorder']['kontrak'];
            $dataSO->keteranganorder = $request['strukorder']['keteranganorder'];
            $dataSO->qtyjenisproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->qtyproduk = $request['strukorder']['qtyjenisproduk'];
            $dataSO->tglorder = $request['strukorder']['tglUsulan'];
            $dataSO->tglkontrak = $request['strukorder']['tglkontrak'];
            $dataSO->tglvalidasi = $request['strukorder']['tglDibutuhkan'];
            $dataSO->keteranganlainnya = $request['strukorder']['koordinator'];
            $dataSO->noorderintern = $request['strukorder']['nousulan'];
            $dataSO->objectruanganfk = $request['strukorder']['ruanganfkPengusul'];
            $dataSO->objectruangantujuanfk = $request['strukorder']['ruanganfkTujuan'];
            $dataSO->objectpegawaiorderfk = $request['strukorder']['penanggungjawabfk'];
            $dataSO->objectpetugasfk = $request['strukorder']['mengetahuifk'];
            $dataSO->objectpegawaispkfk =$request['strukorder']['objectpegawaispkfk'];
            $dataSO->objectrekananfk = $request['strukorder']['rekananfk'];
            $dataSO->noorderintern = $request['strukorder']['nousulan'];
            $dataSO->statusorder = 0;
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim =  $request['strukorder']['biayakirim'];
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = $request['strukorder']['total'];
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn =  $request['strukorder']['ppn'];

            $dataSO->save();

            $SO = array(
                "norec"  => $dataSO->norec,
                "nospk" => $dataSO->nokontrakspk,
                "keltransaksi" =>$dataSO->objectkelompoktransaksifk,
            );

            foreach ($request['details'] as $item) {

                $dataOP = new OrderPelayanan();
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = $idProfile;
                $dataOP->statusenabled = true;
                $dataOP->hasilkonversi = $item['nilaikonversi'];
                $dataOP->iscito = 0;
                $dataOP->noorderfk = $SO['norec'];
                $dataOP->nokontrakspk = $request['strukorder']['nokontrakspk'];
                $dataOP->objectprodukfk = $item['produkfk'];
                $dataOP->qtyproduk = $item['jumlah'];
                $dataOP->qtyprodukretur = 0;
                $dataOP->objectsatuanstandarfk = $item['satuanviewfk'];
                $dataOP->strukorderfk = $SO['norec'];
                $dataOP->tglkontrak = $request['strukorder']['tglkontrak'];
                $dataOP->objectrekananfk = $request['strukorder']['rekananfk'];
                $dataOP->tglpelayanan = $request['strukorder']['tglUsulan'];
                $dataOP->hargasatuan = $item['hargasatuan'];
                $dataOP->hargadiscount = $item['hargadiscount'];
                $dataOP->hargappn = $item['ppn'];
                $dataOP->deskripsiprodukquo = $item['spesifikasi'];
                $dataOP->tglpelayananakhir = $item['tglkebutuhan'];
                $dataOP->save();
            }

            //***** Struk Realisasi *****
//            if ($request['strukorder']['norec'] == '') {
//                $dataSR= new StrukRealisasi();
//                $norealisasi = $this->generateCode(new StrukRealisasi(),'norealisasi',10,'RA-'.$this->getDateTime()->format('ym'));
//                $dataSR->norec = $dataSR->generateNewId();
//                $dataSR->kdprofile = 0;
//                $dataSR->statusenabled = true;
//                $dataSR->norealisasi = $norealisasi;
//            }else {
//                $dataSR = StrukRealisasi::where('norec', $request['strukorder']['norecrealisasi'])->first();
//            }
//            $dataSR->tglrealisasi = $request['strukorder']['tglUsulan'];
//            $dataSR->totalbelanja = $request['strukorder']['total'];
//            $dataSR->objectmataanggaranfk = $request['strukorder']['objectmataanggaranfk'];
//            $dataSR->status = 1;
//            $dataSR->save();
//            $datanorecSR = $dataSR->norec;

            //***** Riwayat Realisasi *****
//            if ($request['strukorder']['norec'] == '') {
//                $dataRR= new RiwayatRealisasi();
//                $dataRR->norec = $dataRR->generateNewId();
//                $dataRR->kdprofile = 0;
//                $dataRR->statusenabled = true;
//            }else {
//                $dataRR = RiwayatRealisasi::where('objectstrukrealisasifk', $request['strukorder']['norecrealisasi'])->first();
//            }
//            $dataRR->objectkelompoktransaksifk = $SO['keltransaksi'];
//            $dataRR->objectstrukrealisasifk = $datanorecSR;
//            $dataRR->objectstrukfk = $SO['norec'];
//            $dataRR->tglrealisasi =  $request['strukorder']['tglkontrak'];
//            $dataRR->objectpetugasfk = $request['strukorder']['objectpegawaispkfk'];
//            $dataRR->noorderintern = $request['strukorder']['nousulan'];
//            $dataRR->keteranganlainnya = $request['strukorder']['keteranganorder'];
//            $dataRR->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan SPK";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan SPK";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSO->norec,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan SPK Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO->norec,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
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
            ->select(DB::raw("
                sp.tglstruk, sp.nostruk, rkn.namarekanan, pg.namalengkap, sp.nokontrak,
                ru.namaruangan, sp.norec, sp.nofaktur, sp.tglfaktur, CAST(sp.totalharusdibayar AS FLOAT), sbk.nosbk,
                sp.nosppb, sp.noorderfk, sp.qtyproduk
            ")
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
            $data = $data->where('sp.nostruk', 'ILIKE', '%' . $request['nostruk']);
        }
        if (isset($request['namarekanan']) && $request['namarekanan'] != "" && $request['namarekanan'] != "undefined") {
            $data = $data->where('rkn.namarekanan', 'ILIKE', '%' . $request['namarekanan'] . '%');
        }
        if (isset($request['nofaktur']) && $request['nofaktur'] != "" && $request['nofaktur'] != "undefined") {
            $data = $data->where('sp.nofaktur', 'ILIKE', '%' . $request['nofaktur'] . '%');
        }
        if (isset($request['produkfk']) && $request['produkfk'] != "" && $request['produkfk'] != "undefined") {
            $data = $data->where('spd.objectprodukfk', '=', $request['produkfk']);
        }
        if (isset($request['noSppb']) && $request['noSppb'] != "" && $request['noSppb'] != "undefined") {
            $data = $data->where('sp.nosppb', 'ILIKE', '%' . $request['noSppb'] . '%');
        }
//        $data = $data->distinct();
        $data = $data->where('sp.statusenabled', true);
        $data = $data->where('sp.objectkelompoktransaksifk', 35);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();

        foreach ($data as $item) {
            $details = \DB::select(DB::raw("select  pr.namaproduk,ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.hargasatuan,spd.hargadiscount,
                    --spd.hargappn,((spd.hargasatuan-spd.hargadiscount+spd.hargappn)*spd.qtyproduk) as total,spd.tglkadaluarsa,spd.nobatch
                    --spd.hargappn,((spd.hargasatuan * spd.qtyproduk)-spd.hargadiscount+spd.hargappn) as total,spd.tglkadaluarsa,spd.nobatch
                    spd.hargappn,CAST(((spd.qtyproduk*spd.hargasatuan)-(((spd.persendiscount*spd.hargasatuan)/100)*spd.qtyproduk))+(spd.persenppn*((spd.qtyproduk*spd.hargasatuan)-(((spd.persendiscount*spd.hargasatuan)/100)*spd.qtyproduk))/100) AS FLOAT) AS total,
                    spd.tglkadaluarsa,spd.nobatch
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

    public function DeletePenerimaanBarangSupplier(Request $request){
        \DB::beginTransaction();
        $transMessage = '';
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            $dataKembaliStok = DB::select(DB::raw("select sp.norec,spd.qtyproduk,spd.hasilkonversi,sp.objectruanganfk,spd.objectprodukfk,
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

                    $dataKS = KartuStok::where('keterangan',  'Penerimaan Barang Suplier. No Terima. ' . $dataPenerimaan->nostruk . ' Faktur No.' . $dataPenerimaan->nofaktur . ' ' . $dataPenerimaan->namarekanan)
                        ->where('kdprofile', $idProfile)
                        ->update([
                            'flagfk' => null
                        ]);


                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                    $newKS->keterangan = 'Batal Penerimaan No. ' . $item5->nostruk;
                    $newKS->produkfk = $item5->objectprodukfk;
                    $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
                    $newKS->saldoawal = (float)$saldoAwal - (float)$TambahStok;
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->nostrukterimafk = $request['nostruk'];

                    $newKS->save();

                    OrderPelayanan::where('noorderfk', $request['noorderfk'])
                        ->where('kdprofile', $idProfile)
                        ->where('objectprodukfk', $item5->objectprodukfk)
                        ->update([
                            'qtyterimalast' => 0
                        ]);

                }
                $SP = StrukPelayanan::where('norec', $request['nostruk'])->where('kdprofile', $idProfile)->first();
                $SP->statusenabled = false;
                $SP->save();


                $delSPD = StokProdukDetail::where('nostrukterimafk', $request['nostruk'])
                    ->where('kdprofile', $idProfile)
                    ->delete();
//            $delSPD = StrukPelayananDetail::where('nostrukfk',$request['struk']['nostruk'])
//                ->delete();

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
                $transMessage = "Hapus Penerimaan";
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
                "as" => 'as@epic',
            );
        } else {
//            $transMessage = "Hapus Penerimaan Gagal!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
//                "data" => $SP,
//                "kartustok" => $kartuStok,
//                "stokdetail" => $stokdetail,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarPenerimaanSuplierPerUnit(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdSirs1 = $request['KdSirs1'];
        $kdSirs2= $request['KdSirs2'];
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk', $dataLogin['userData']['id'])
            ->get();

        $data = \DB::table('strukpelayanan_t as sp')
            ->JOIN('strukpelayanandetail_t as spd', 'spd.nostrukfk', '=', 'sp.norec')
            ->LEFTJOIN('rekanan_m as rkn', 'rkn.id', '=', 'sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'sp.objectpegawaipenerimafk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'sp.objectruanganfk')
            ->LEFTJOIN('produk_m as pr', 'pr.id', '=', 'spd.objectprodukfk')
            ->LEFTJOIN('satuanstandar_m as ss', 'ss.id', '=', 'spd.objectsatuanstandarfk')
            ->LEFTJOIN('asalproduk_m as asp', 'asp.id', '=', 'spd.objectasalprodukfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk', 'sbk.norec', '=', 'sp.nosbklastfk')
            ->select('sp.tglstruk', 'sp.nostruk', 'sp.nofaktur', 'sp.tglfaktur', 'pr.id as kdproduk', 'pr.kdproduk as kdsirs', 'pr.namaproduk', 'ss.satuanstandar',
                              'rkn.namarekanan', 'pg.namalengkap as namapenerima', 'ru.namaruangan', 'spd.hargasatuan', 'sbk.nosbk','sp.nosppb', 'asp.asalproduk',
                \DB::raw("CAST(spd.qtyproduk AS FLOAT) AS qtyproduk,CAST(spd.qtyprodukretur AS FLOAT) AS qtyprodukretur,CAST (spd.hargasatuan AS FLOAT) AS hargasatuan,
                                CAST (spd.qtyproduk*spd.hargasatuan AS FLOAT) AS subtotal,CAST (spd.hargadiscount AS FLOAT) AS hargadiscount,CAST (spd.hargappn AS FLOAT) AS hargappn,
                                CAST (((spd.hargasatuan*spd.qtyproduk) - spd.hargadiscount) + spd.hargappn AS FLOAT) AS total,CAST(spd.tglkadaluarsa as varchar) as  tglkadaluarsa")
            )
            ->where('sp.kdprofile', $idProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sp.tglfaktur', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('sp.tglfaktur', '<=', $tgl);
        }
        if (isset($request['nostruk']) && $request['nostruk'] != "" && $request['nostruk'] != "undefined") {
            $data = $data->where('sp.nostruk', 'ILIKE', '%' . $request['nostruk']);
        }
        if (isset($request['namarekanan']) && $request['namarekanan'] != "" && $request['namarekanan'] != "undefined") {
            $data = $data->where('rkn.namarekanan', 'ILIKE', '%' . $request['namarekanan'] . '%');
        }
        if (isset($request['nofaktur']) && $request['nofaktur'] != "" && $request['nofaktur'] != "undefined") {
            $data = $data->where('sp.nofaktur', 'ILIKE', '%' . $request['nofaktur'] . '%');
        }
        if (isset($request['produkfk']) && $request['produkfk'] != "" && $request['produkfk'] != "undefined") {
            $data = $data->where('spd.objectprodukfk', '=', $request['produkfk']);
        }
        if(isset( $request['KdSirs1'])&&  $request['KdSirs1']!=''){
            if($request['KdSirs2'] != null &&  $request['KdSirs2']!='' && $request['KdSirs1'] != null &&  $request['KdSirs1']!= ''){
                $data = $data->whereRaw (" (pr.kdproduk BETWEEN '".$request['KdSirs1']."' and '".$request['KdSirs2']."') ");
            }elseif ($request['KdSirs2'] &&  $request['KdSirs2']!= '' && $request['KdSirs1'] == '' ||  $request['KdSirs1'] == null){
                $data = $data->whereRaw = (" pr.kdproduk ILIKE '".$request['KdSirs2']."%'");
            }elseif ($request['KdSirs1'] &&  $request['KdSirs1']!= '' && $request['KdSirs2'] == '' ||  $request['KdSirs2'] == null){
                $data = $data->whereRaw = (" pr.kdproduk ILIKE '".$request['KdSirs1']."%'");
            }
        }

        $data = $data->where('sp.statusenabled', true);
        $data = $data->where('sp.objectkelompoktransaksifk', 35);
//        $data = $data->whereRaw($kdSirs1 . ' ' . $kdSirs2);
        $data = $data->orderBy('pr.kdproduk','asc');
        $data = $data->get();
        $result = array(
            'datalogin' => $dataLogin,
            'data' => $data,
            'message' => 'Cepot'
        );
        return $this->respond($result);
    }

    public function getDaftarPemakaianStokRuangan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }
        $data = \DB::table('strukpelayanan_t as sp')
            ->leftJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipenanggungjawabfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
//            ->LEFTJOIN('strukbuktipengeluaran_t as sbk','sbk.norec','=','sp.nosbklastfk')
//            ->LEFTJOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
//            ->LEFTJOIN('jeniskemasan_m as jkm','jkm.id','=','spd.objectjeniskemasanfk')
            ->select('sp.tglstruk','sp.nostruk','pg.namalengkap',
                'ru.namaruangan','sp.norec','sp.nofaktur','sp.tglfaktur','sp.totalhargasatuan as total','sp.keteranganlainnya'
//                'spd.hargasatuan','spd.hargadiscount','spd.qtyproduk','spd.hargatambahan' ,
//                'pr.namaproduk as namaprodukstandar',
//                'spd.resepke as rke','jkm.jeniskemasan'
            )
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglstruk','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglstruk','<=', $tgl);
        }
        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('sp.nostruk','ILIKE','%'. $request['nostruk']);
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganlainnya','ILIKE','%'. $request['keterangan']);
        }
        if(isset($request['ruanganid']) && $request['ruanganid']!="" && $request['ruanganid']!="undefined"){
            $data = $data->where('sp.objectruanganfk','=', $request['ruanganid']);
        }
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',45);
        $data = $data->wherein('sp.objectruanganfk',$strRuangan);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();
        $result =[];
        foreach ($data as $item){
            $details = \DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,
                    ((spd.hargasatuan-spd.hargadiscount)*spd.qtyproduk) as total
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
//                'nofaktur' => $item->nofaktur,
//                'tglfaktur' => $item->tglfaktur,
//                'namarekanan' => $item->namarekanan,
                'norec' => $item->norec,
                'namaruangan' => $item->namaruangan,
                'namapegawai' => $item->namalengkap,
                'keterangan' => $item->keteranganlainnya,
                'total' => $item->total,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $result,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function hapusPemakaianStokRuangan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $transMessage='';

        try {
            $dataKembaliStok = DB::select(DB::raw("select sp.norec,spd.qtyproduk,spd.hasilkonversi,sp.objectruanganfk,spd.objectprodukfk,
                      sp.nostruk
                            from strukpelayanandetail_t as spd
                            INNER JOIN strukpelayanan_t sp on sp.norec=spd.nostrukfk
                            where sp.kdprofile = $idProfile and sp.norec=:norec"),
                array(
                    'norec' => $request['nostruk'],
                )
            );

            $dataStokSudahKirim = StokProdukDetail::where('nostrukterimafk',$request['nostruk'])
                ->where('kdprofile', $idProfile)
                ->whereNotIn('objectruanganfk',[$dataKembaliStok[0]->objectruanganfk])
                ->where('qtyproduk','>',0)
                ->get();

            if (count($dataStokSudahKirim) == 0){
                foreach ($dataKembaliStok as $item5){
                    $TambahStok = (float)$item5->qtyproduk*(float)$item5->hasilkonversi;

                    $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
                            where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                        array(
                            'ruanganfk' => $item5->objectruanganfk,
                            'produkfk' => $item5->objectprodukfk,
                        )
                    );
                    $saldoAwal=0;
                    foreach ($dataSaldoAwal as $itemss){
                        $saldoAwal = (float)$itemss->qty;
                    }

                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                    $newKS->keterangan = 'Hapus Pemakaian Ruangan No. ' . $item5->nostruk;
                    $newKS->produkfk = $item5->objectprodukfk;
                    $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
                    $newKS->saldoawal = (float)$saldoAwal + (float)$TambahStok;
                    $newKS->status = 1;
                    $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $request['nostruk'];

                    $newKS->save();

//                    OrderPelayanan::where('noorderfk', $request['noorderfk'])
//                        ->where('objectprodukfk',$item5->objectprodukfk)
//                        ->update([
//                            'qtyterimalast' => 0
//                        ]);

                }
                $SP = StrukPelayanan::where('norec',$request['nostruk'])->where('kdprofile',$idProfile)->first();
                $SP->statusenabled = false;
                $SP->save();
                //<editor-fold desc="Tambah STok Produk Detail">
                $dataSPDK = StokProdukDetail::where('objectprodukfk', $item5->objectprodukfk)
//                where('nostrukterimafk', $item['nostrukterimafk'])
                    ->where('kdprofile',$idProfile)
                    ->where('qtyproduk', '>', 0)
                    ->where('objectruanganfk', $item5->objectruanganfk)
                    ->first();

                StokProdukDetail::where('norec', $dataSPDK->norec)
                    ->where('kdprofile',$idProfile)
                    ->update([
                            'qtyproduk' => (float)$dataSPDK->qtyproduk +( (float)$item5->qtyproduk*(float)$item5->hasilkonversi)]
                    );
                //</editor-fold>

                $delSPD = StrukPelayananDetail::where('nostrukfk',$request['nostruk'])
                    ->where('kdprofile',$idProfile)
                    ->delete();
//            $delSPD = StrukPelayananDetail::where('nostrukfk',$request['struk']['nostruk'])
//                ->delete();

                $kirim = KartuStok::where('ruanganfk',$item5->objectruanganfk)
                    ->where('kdprofile',$idProfile)
                    ->where('produkfk',$item5->objectprodukfk)
                    ->get();

                $kartuStok[] = $kirim;

                $dataSTOKDETAIL[] = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec from stokprodukdetail_t 
                        where kddprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $item5->objectruanganfk,
                        'produkfk' => $item5->objectprodukfk,
                    )
                );

                $stokdetail[]=$dataSTOKDETAIL;

                $transStatus = 'true';
                $transMessage = "Hapus Pemakaian Ruangan";
            }else{
                $transStatus = 'false';
                $transMessage = "Hapus Pemakaian Ruangan Gagal";
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
                "as" => 'as@epic',
            );
        } else {
//            $transMessage = "Hapus Pemakaian Ruangan Gagal!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "data" => $SP,
//                "kartustok" => $kartuStok,
//                "stokdetail" => $stokdetail,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function savePemakaianStokRuangan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $transMessage='';
        $req = $request;
        try {
            //<editor-fold desc="Save Struk Pelayanan & Detail & Kartu Stok  ">
            if ($request['struk']['nostruk'] == '') {
                $SP = new StrukPelayanan();
                $norecSP = $SP->generateNewId();
                $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 13, 'PS/' . $this->getDateTime()->format('ym/'), $idProfile);
                $SP->norec = $norecSP;
                $SP->kdprofile = $idProfile;
                $SP->statusenabled = true;
                $SP->nostruk = $noStruk;
                $SP->noterima = $noStruk;

            }else{
                //<editor-fold desc="Penambahan Kembali STOK ">
                $SP = StrukPelayanan::where('norec',$request['struk']['nostruk'] )->first();
                $noStruk = $SP->nostruk;
                $norecSP = $SP->norec;

                $dataKembaliStok = DB::select(DB::raw("select spd.qtyproduk ,spd.hasilkonversi  ,spd.objectruanganfk ,
                    spd.objectprodukfk,spd.harganetto
                    from strukpelayanandetail_t as spd
                    where spd.kdprofile = $idProfile and spd.nostrukfk=:strukfk"),
                    array(
                        'strukfk' => $norecSP,
                    )
                );
                foreach ($dataKembaliStok as $item5){
                    $TambahStok = (float)$item5->qtyproduk*(float)$item5->hasilkonversi;
                    $newSPD = StokProdukDetail::where('objectruanganfk',$item5->objectruanganfk)
                        ->where('kdprofile', $idProfile)
                        ->where('objectprodukfk',$item5->objectprodukfk)
//                    ->where('harganetto1',$item5->harganetto)
                        ->orderby('tglkadaluarsa','desc')
                        ->first();
                    $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
                    $newSPD->save();

                    $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                        array(
                            'ruanganfk' => $item5->objectruanganfk,
                            'produkfk' => $item5->objectprodukfk,
                        )
                    );
                    $saldoAwal=0;
                    foreach ($dataSaldoAwal as $itemss){
                        $saldoAwal = (float)$itemss->qty;
                    }

                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                    $newKS->keterangan = 'Ubah Pemakaian Stok Ruangan  No. ' . $noStruk;
                    $newKS->produkfk = $item5->objectprodukfk;
                    $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
                    $newKS->saldoawal = (float)$saldoAwal ;//- (float)$qtyJumlah;
                    $newKS->status = 1;
                    $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
                    $newKS->norectransaksi = $norecSP;
                    $newKS->tabletransaksi = 'strukpelayanan_t';
                    $newKS->save();
                }

                $delSPD = StrukPelayananDetail::where('nostrukfk',$request['struk']['nostruk'])
                    ->where('kdprofile', $idProfile)
                    ->delete();
                //</editor-fold>
            }

            $SP->objectkelompoktransaksifk = 45;
            $SP->objectruanganfk = $req['struk']['ruanganfk'];
            $SP->keteranganlainnya = $req['struk']['keterangan'];
            $SP->tglstruk = $req['struk']['tglstruk'];
            $SP->objectpegawaipenanggungjawabfk = $req['struk']['pegawaimenerimafk'];
            $SP->qtyproduk = $req['struk']['qtyproduk'];

            $SP->totalhargasatuan = $req['struk']['total'];
            $SP->save();

            foreach ($req['details'] as $item) {
                $qtyJumlah = (float)$item['jumlah'] * (float)$item['nilaikonversi'];
                $SPD = new StrukPelayananDetail();
                $norecKS = $SPD->generateNewId();
                $SPD->norec = $norecKS;
                $SPD->kdprofile = $idProfile;
                $SPD->statusenabled = true;
                $SPD->nostrukfk = $SP->norec;

                $SPD->objectasalprodukfk = $item['asalprodukfk'];
                $SPD->objectprodukfk = $item['produkfk'];
                $SPD->objectruanganfk = $item['ruanganfk'];
                $SPD->objectruanganstokfk = $item['ruanganfk'];
                $SPD->objectsatuanstandarfk = $item['satuanstandarfk'];
                $SPD->hargadiscount = 0;
                $SPD->hargadiscountgive = 0;
                $SPD->hargadiscountsave = 0;
                $SPD->harganetto = $item['hargasatuan'];
                $SPD->hargapph = 0;
                $SPD->hargappn = 0;
                $SPD->hargasatuan = $item['hargasatuan'];
                $SPD->hasilkonversi = $item['nilaikonversi'];
                $SPD->namaproduk = $item['namaproduk'];
//            $SPD->keteranganlainnya = $item['keterangan'];
                $SPD->hargasatuandijamin = 0;
                $SPD->hargasatuanppenjamin = 0;
                $SPD->hargatambahan = 0;
                $SPD->hargasatuanpprofile = 0;
                $SPD->isonsiteservice = 0;
                $SPD->kdpenjaminpasien = 0;
                $SPD->persendiscount = 0;
                $SPD->persenppn = 0;
                $SPD->qtyproduk = $item['jumlah'];
                $SPD->qtyprodukoutext = 0;
                $SPD->qtyprodukoutint = 0;
                $SPD->qtyprodukretur = 0;
                $SPD->satuan = '-';//$item['satuanstandar'];;
                $SPD->satuanstandar = $item['satuanviewfk'];
                $SPD->tglpelayanan = $req['struk']['tglstruk'];
                $SPD->is_terbayar = 0;
                $SPD->linetotal = 0;
                $SPD->save();
                //<editor-fold desc="Update STOK StokProdukDetails ">
                $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $item['ruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $saldoAwalPengirim = 0;
                foreach ($dataSaldoAwalK as $items) {
                    $saldoAwalPengirim = (float)$items->qty;
                }

                $dataSPDK = StokProdukDetail::where('nostrukterimafk', $item['nostrukterimafk'])
                    ->where('kdprofile', $idProfile)
                    ->where('objectprodukfk', $item['produkfk'])
                    ->where('qtyproduk', '>', 0)
                    ->where('objectruanganfk', $item['ruanganfk'])
                    ->first();

                StokProdukDetail::where('norec', $dataSPDK->norec)
                    ->where('kdprofile', $idProfile)
                    ->update([
                            'qtyproduk' => (float)$dataSPDK->qtyproduk - ((float)$item['jumlah'] * (float)$item['nilaikonversi'])]
                    );
                //</editor-fold >
                //## KartuStok
                $newKS = new KartuStok();
                $norecKS = $newKS->generateNewId();
                $newKS->norec = $norecKS;
                $newKS->kdprofile = $idProfile;
                $newKS->statusenabled = true;
                $newKS->jumlah = ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                $newKS->keterangan = 'Pemakaian Stok Ruangan ' . $req['struk']['namaruangan'].' '.$noStruk;
                $newKS->produkfk = $item['produkfk'];
                $newKS->ruanganfk = $req['struk']['ruanganfk'];
                $newKS->saldoawal = (float)$saldoAwalPengirim - ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                $newKS->status = 0;
                $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $item['nostrukterimafk'];
                $newKS->norectransaksi = $dataSPDK->norec;
                $newKS->tabletransaksi = 'stokprodukdetail_t';
                $newKS->save();
            }
            //</editor-fold>
            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Pemakaian Ruangan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $SP,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Pemakaian Ruangan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPemakaianStokRuanganByNorec(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataStruk = \DB::table('strukpelayanan_t as sp')
            ->leftJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipenanggungjawabfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
//            ->LEFTJOIN('strukbuktipengeluaran_t as sbk','sbk.norec','=','sp.nosbklastfk')
//            ->LEFTJOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
//            ->LEFTJOIN('jeniskemasan_m as jkm','jkm.id','=','spd.objectjeniskemasanfk')
            ->select('sp.tglstruk','sp.nostruk','pg.namalengkap','pg.id as pgid','sp.objectruanganfk',
                'ru.namaruangan','sp.norec','sp.nofaktur','sp.tglfaktur','sp.totalhargasatuan as total','sp.keteranganlainnya'
//                'spd.hargasatuan','spd.hargadiscount','spd.qtyproduk','spd.hargatambahan' ,
//                'pr.namaproduk as namaprodukstandar',
//                'spd.resepke as rke','jkm.jeniskemasan'
            )
            ->where('sp.kdprofile', $idProfile);
//        $dataStruk = \DB::table('strukpelayanan_t as sr')
//            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.penerimaanfk','=','sr.norec')
//            ->LEFTJOIN('strukrealisasi_t as srr','srr.norec','=','rr.objectstrukrealisasifk')
//            ->LEFTJOIN('mataanggaran_m as ma','ma.norec','=','srr.objectmataanggaranfk')
//            ->leftJOIN('pegawai_m as pg','pg.id','=','sr.objectpegawaipenerimafk')
//            ->leftJOIN('pegawai_m as pg1','pg1.id','=','sr.objectpegawaipenanggungjawabfk')
//            ->JOIN('ruangan_m as ru','ru.id','=','sr.objectruanganfk')
//            ->select('sr.tglstruk','sr.nostruk','rr.noorderintern as nousulan','sr.nokontrak','pg.id as pgid','pg.namalengkap','ru.id','ru.namaruangan','sr.nofaktur',
//                'sr.tglfaktur','sr.namarekanan','sr.objectrekananfk','sr.nosppb','srr.norealisasi','srr.norec as norecrealisasi','sr.tglkontrak','sr.tgldokumen',
//                'rr.objectstrukfk','srr.objectmataanggaranfk as mataanggranid','ma.namamataanggaran','rr.tglrealisasi','sr.keteranganlainnya','sr.keteranganambil',
//                'pg.id as pgid','pg.namalengkap','sr.objectpegawaipenanggungjawabfk','pg1.namalengkap as penanggungjawab','sr.namapengadaan');

        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norec']);
        }

        $dataStruk = $dataStruk->first();

        $data = \DB::table('strukpelayanan_t as sp')
            ->JOIN('strukpelayanandetail_t as spd','spd.nostrukfk','=','sp.norec')
            ->JOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->JOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
            ->leftJOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','spd.objectsatuanstandarfk')
            ->leftJOIN('asalproduk_m as ap','ap.id','=','spd.objectasalprodukfk')
            ->select('sp.nostruk','spd.hargasatuan','spd.qtyproduk','sp.objectruanganfk','ru.namaruangan',
                'spd.objectprodukfk as produkfk','pr.namaproduk','spd.hasilkonversi as nilaikonversi',
                'spd.objectsatuanstandarfk','ss.satuanstandar','spd.satuanstandar as satuanviewfk','ss.satuanstandar as ssview',
                'spd.qtyproduk as jumlah','spd.hargadiscount','spd.hargappn','spd.hargasatuan','spd.objectasalprodukfk',
                'ap.asalproduk','spd.persendiscount','spd.persenppn','spd.keteranganlainnya','spd.nobatch','spd.tglkadaluarsa',
                'kp.id as kpid','kp.kelompokproduk')
            ->where('sp.kdprofile', $idProfile);

        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norec']);
        }
        $data = $data->get();

        $pelayananPasien=[];
        $i = 0;
        foreach ($data as $item){
            $i = $i+1;
            $pelayananPasien[] = array(
                'no' => $i,
                'hargasatuan' => $item->hargasatuan,
                'ruanganfk' => $item->objectruanganfk,
                'asalprodukfk' =>  $item->objectasalprodukfk,
                'asalproduk' =>  $item->asalproduk,
                'produkfk' => $item->produkfk,
                'namaproduk' => $item->namaproduk,
                'nilaikonversi' => $item->nilaikonversi,
                'satuanstandarfk' => $item->satuanviewfk,
                'satuanstandar' => $item->ssview,
                'satuanviewfk' => $item->satuanviewfk,
                'satuanview' => $item->ssview,
                'jumlah' => $item->jumlah,
                'hargadiscount' => $item->hargadiscount,
                'persendiscount' =>$item->persendiscount,
                'ppn' => $item->hargappn,
                'persenppn' => $item->persenppn,
                'total'=> ((float)$item->hargasatuan-(float)$item->hargadiscount+(float)$item->hargappn)*$item->jumlah,
                'keterangan'=> $item->keteranganlainnya,
                'nobatch'=> $item->nobatch,
                'tglkadaluarsa'=> $item->tglkadaluarsa,
                'kpid' => $item->kpid,
                'kelompokproduk' => $item->kelompokproduk,
            );
        }

        $result = array(
            'struk' => $dataStruk,
            'details' => $pelayananPasien,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDetailOrderBarang(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->select('ap.id','ap.asalproduk')
            ->get();

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
            ->where('sp.kdprofile', $idProfile);;

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $dataStruk = $dataStruk->where('sp.norec','=', $request['norecOrder']);
        }

        $dataStruk = $dataStruk->where('sp.statusenabled',true);
        $dataStruk = $dataStruk->where('sp.objectkelompoktransaksifk',34);
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
            ->where('so.kdprofile', $idProfile);;
        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('so.norec','=', $request['norecOrder']);
        }
        $data = $data->get();
        $details = [];
        $i = 0;
        $dataStok = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                    spd.harganetto2 as hargajual,spd.harganetto2 as harganetto,spd.hargadiscount,ap.asalproduk,
                    sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk
                    from stokprodukdetail_t as spd
                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                    inner JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                    where spd.kdprofile = $idProfile and spd.objectruanganfk =:ruanganid
                    group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2,spd.hargadiscount,ap.asalproduk,
                    spd.objectruanganfk
                    order By sk.tglstruk"),
            array(
                'ruanganid' => $dataStruk->objectruangantujuanfk
            )
        );
        $hargajual=0;
        $harganetto=0;
        $nostrukterimafk='';
        $asalprodukfk=0;
        $asalproduk='';
        $jmlstok=0;
        $hargasatuan=0;
        $hargadiscount=0;
        $total=0;
        $aturanpakaifk=0;
        foreach ($data as $item){
            if ($item->jumlah > 0 ){
                $i = $i+1;
                foreach ($dataStok as $item2){
                    if ($item2->objectprodukfk == $item->produkfk){
                        if ($item2->qtyproduk >= $item->jumlah*$item->nilaikonversi){
                            $nostrukterimafk = $item2->norec;
                            $asalprodukfk = $item2->objectasalprodukfk;
                            $jmlstok = $item2->qtyproduk;
                            break;
                        }
                    }
                }
                foreach ($dataAsalProduk as $item3){
                    if ($asalprodukfk == $item3->id){
                        $asalproduk = $item3->asalproduk;
                    }
                }
                $details[] = array(
                    'no' => $i,
                    'noregistrasifk' => '',
                    'tglregistrasi' => '',
                    'generik' => null,
                    'hargajual' => $hargajual,
                    'jenisobatfk' => '',
                    'kelasfk' => '',
                    'stock' => $jmlstok,
                    'harganetto' => $harganetto,
                    'nostrukterimafk' => $nostrukterimafk,
                    'ruanganfk' => $item->objectruanganfk,
                    'rke' => 0,
                    'jeniskemasanfk' => 0,
                    'jeniskemasan' => '',
                    'aturanpakaifk' => $aturanpakaifk,
                    'aturanpakai' => '',
                    'routefk' => 0,
                    'route' => '',
                    'asalprodukfk' => $asalprodukfk,
                    'asalproduk' => $asalproduk,
                    'produkfk' => $item->produkfk,
                    'kdproduk' => $item->kdproduk,
                    'namaproduk' => $item->namaproduk,
                    'nilaikonversi' => $item->nilaikonversi,///$item->jumlah,
                    'satuanstandarfk' => $item->satuanviewfk,//objectsatuanstandarfk,
                    'satuanstandar' => $item->ssview,//satuanstandar,
                    'satuanviewfk' => $item->satuanviewfk,
                    'satuanview' => $item->ssview,
                    'jmlstok' => $jmlstok,
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

    public function getProdukDetail(Request $request) {
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
            $result = \DB::select(DB::raw("select sk.norec,spd.objectprodukfk, $strMSHT as tgl,spd.objectasalprodukfk,$strHN as harganetto ,
                      spd.hargadiscount,
                sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk,ap.asalproduk,spd.nostrukterimafk
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                inner JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
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
                inner JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
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
                inner JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
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


        $result= array(
            'detail' => $results,
            'jmlstok'=> $jmlstok,
            'sistemharganetto' => $SistemHargaNetto,
            'metodeambilharganetto' => $MetodeAmbilHargaNetto,
            'metodestokharganetto' => $MetodeStokHargaNetto,
            'consis' => count($cekConsis),
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function saveOrderBarang(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try{
            if ($request['strukorder']['norecorder'] == '') {
                if ($request['strukorder']['jenispermintaanfk'] != 1) {
                    $noOrder = $this->generateCode(new StrukOrder, 'noorder', 14, 'OTRF-' . $this->getDateTime()->format('ym'), $idProfile);
                } else {
                    $noOrder = $this->generateCode(new StrukOrder, 'noorder', 14, 'OAMP-' . $this->getDateTime()->format('ym'), $idProfile);
                }
                $dataSO = new StrukOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->isdelivered = 0;
                $dataSO->noorder = $noOrder;
            }else {
                $dataSO = StrukOrder::where('norec',$request['strukorder']['norecorder'])->first();
                OrderPelayanan::where('noorderfk',$request['strukorder']['norecorder'])->delete();
            }
            $dataSO->jenispermintaanfk = $request['strukorder']['jenispermintaanfk'];
            $dataSO->objectkelompoktransaksifk = 34;
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
                $dataOP->hasilkonversi = $item['nilaikonversi'];
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
            $transMessage = "simpan Order";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Order Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSO,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Order Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarOrderBarang(Request $request) {
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

    public function saveBatalOrderBarang(Request $request) {
        \DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try{
            $dataAing = StrukOrder::where('norec',$request['norecorder'])
                ->where('kdprofile',$idProfile)
                ->update([
                        'statusenabled' => false]
                );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        $transMessage = "Batal Order Barang Berhasil";

        if ($transStatus == 'true') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Batal Order Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDetailKirimBarang(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->select('ap.id','ap.asalproduk')
            ->get();
        $dataSigna = \DB::table('stigma as st')
            ->select('st.id','st.name')
            ->get();
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
        $dataStok = \DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2 as hargajual,spd.harganetto2 as harganetto,spd.hargadiscount,
                    sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk
                    from stokprodukdetail_t as spd
                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                    where spd.kdprofile = $idProfile and spd.objectruanganfk =:ruanganid
                    group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2,spd.hargadiscount,
                    spd.objectruanganfk
                    order By sk.tglstruk"),
            array(
                'ruanganid' => $dataStruk->id
            )
        );
        $hargajual=0;
        $harganetto=0;
        $nostrukterimafk='';
        $asalprodukfk=0;
        $asalproduk='';
        $jmlstok=0;
        $hargasatuan=0;
        $hargadiscount=0;
        $total=0;
        $aturanpakaifk=0;
        foreach ($data as $item){
            if ($item->jumlah > 0 ){
                $i = $i+1;

                foreach ($dataStok as $item2){
                    if ($item2->objectprodukfk == $item->produkfk){
                        if ($item2->qtyproduk > $item->jumlah*$item->nilaikonversi){
//                        $hargajual = $item2->hargajual+(($item2->hargajual*25)/100);
//                        $harganetto = $item2->harganetto+(($item2->harganetto*25)/100);

                            $hargajual = $item->hargajual;
                            $harganetto = $item->harganetto;

                            $nostrukterimafk = $item2->norec;
                            $asalprodukfk = $item2->objectasalprodukfk;
//                        $asalproduk = $item2->objectasalprodukfk;
                            $jmlstok = $item2->qtyproduk;
                            $hargasatuan = $harganetto;//$item2->harganetto;
                            $hargadiscount = $item->hargadiscount;
//                        $hargadiscount = $item2->hargadiscount;
                            $total =(((float)$item->jumlah * ((float)$hargasatuan-(float)$hargadiscount))) ;
                            break;
                        }
                    }
                }
                foreach ($dataAsalProduk as $item3){
                    if ($asalprodukfk == $item3->id){
                        $asalproduk = $item3->asalproduk;
                    }
                }
                $pelayananPasien[] = array(
                    'no' => $i,
                    'noregistrasifk' => '',
                    'tglregistrasi' => '',
                    'generik' => null,
                    'hargajual' => $hargajual,
                    'jenisobatfk' => '',
                    'kelasfk' => '',
                    'stock' => $jmlstok,
                    'harganetto' => $harganetto,
                    'nostrukterimafk' => $nostrukterimafk,
                    'ruanganfk' => $item->objectruanganfk,
                    'rke' => 0,
                    'jeniskemasanfk' => 0,
                    'jeniskemasan' => '',
                    'aturanpakaifk' => $aturanpakaifk,
                    'aturanpakai' => '',
                    'routefk' => 0,
                    'route' => '',
                    'asalprodukfk' => $asalprodukfk,
                    'asalproduk' => $asalproduk,
                    'produkfk' => $item->produkfk,
                    'kdproduk' => $item->kdproduk,
                    'namaproduk' => $item->namaproduk,
                    'nilaikonversi' => $item->nilaikonversi/$item->jumlah,
                    'satuanstandarfk' => $item->satuanviewfk,//objectsatuanstandarfk,
                    'satuanstandar' => $item->ssview,//satuanstandar,
                    'satuanviewfk' => $item->satuanviewfk,
                    'satuanview' => $item->ssview,
                    'jmlstok' => $jmlstok,
                    'jumlah' => $item->jumlah,
                    'qtyorder' => $item->jumlah,
                    'qtyretur' => $item->qtyprodukretur,
                    'dosis' => 1,
                    'hargasatuan' => $hargasatuan,
                    'hargadiscount' => $hargadiscount,
                    'total' => $total +$item->jasa,
                    'jmldosis' => (String)($item->jumlah/$item->nilaikonversi)/1 . '/' . (String)1,
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

    public function getDetailOrderBarangForKirim(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->select('ap.id','ap.asalproduk')
            ->get();
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
        $dataStok = \DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                    sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk
                    from stokprodukdetail_t as spd
                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                    where spd.kdprofile = $idProfile and spd.objectruanganfk =:ruanganid
                    group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2,spd.hargadiscount,
                    spd.objectruanganfk
                    order By sk.tglstruk"),
            array(
                'ruanganid' => $dataStruk->ruidtujuan
            )
        );
        $hargajual=0;
        $harganetto=0;
        $nostrukterimafk='';
        $asalprodukfk=0;
        $asalproduk='';
        $jmlstok=0;
        $hargasatuan=0;
        $hargadiscount=0;
        $total=0;
        $aturanpakaifk=0;
        foreach ($data as $item){
            $i = $i+1;
            foreach ($dataStok as $item2){
                if ($item2->objectprodukfk == $item->produkfk){
                    $jmlstok =0;
                    if ((float)$item2->qtyproduk >= (float)$item->jumlah*(float)$item->nilaikonversi){
//                        $hargajual = $item2->hargajual+(($item2->hargajual*25)/100);
//                        $harganetto = $item2->harganetto+(($item2->harganetto*25)/100);

//                        $hargajual = $item->hargajual;
//                        $harganetto = $item->hargasatuan;

                        $nostrukterimafk = $item2->norec;
                        $asalprodukfk = $item2->objectasalprodukfk;
                        $jmlstok = (float)$item2->qtyproduk/(float)$item->nilaikonversi;
                        break;
                    }
                }
            }
            foreach ($dataAsalProduk as $item3){
                if ($asalprodukfk == $item3->id){
                    $asalproduk = $item3->asalproduk;
                }
            }

            $details[] = array(
                'no' => $i,
                'hargajual' => $hargajual,
                'stock' => $jmlstok,
                'harganetto' => $harganetto,
                'nostrukterimafk' => $nostrukterimafk,
                'ruanganfk' => $item->objectruanganfk,
                'asalprodukfk' => $asalprodukfk,
                'asalproduk' => $asalproduk,
                'produkfk' => $item->produkfk,
                'kdproduk' => $item->kdproduk,
                'namaproduk' => $item->namaproduk,
                'nilaikonversi' => $item->nilaikonversi,
                'satuanstandarfk' => $item->satuanviewfk,//objectsatuanstandarfk,
                'satuanstandar' => $item->ssview,//satuanstandar,
                'satuanviewfk' => $item->satuanviewfk,
                'satuanview' => $item->ssview,
                'jmlstok' => $jmlstok,
                'jumlah' => $item->jumlah,
                'qtyorder' => $item->jumlah,
                'hargasatuan' => $hargasatuan,
                'hargadiscount' => $hargadiscount,
                'total' => $total ,//+$item->jasa,
            );
        }

        $result = array(
            'detail' => $detail,
            'details' => $details,
            'data' => $data,
            'data2' => $dataStok,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function saveKirimBarangRuangan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        return $this->respond(array(date($request['strukkirim']['tglkirim'])));
        if ($request['strukkirim']['jenispermintaanfk'] == 2) {
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 14, 'TRF-' . $this->getDateTime()->format('ym'), $idProfile);
        }else{
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 14, 'AMP-' . $this->getDateTime()->format('ym'), $idProfile);
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
                    select  ru.namaruangan
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
            if ($request['strukkirim']['noreckirim'] == ''){
                if ($request['strukkirim']['norecOrder'] != ''){
                    $dataAing = StrukOrder::where('norec',$request['strukkirim']['norecOrder'])
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'statusorder' => 1]
                        );
                }
//                if ($request['strukkirim']['jenispermintaanfk'] == 2) {
//                    $noKirim = $this->generateCode(new StrukKirim, 'nokirim', 14, 'TRF-' . $this->getDateTime()->format('ym'));
//                }else{
//                    $noKirim = $this->generateCode(new StrukKirim, 'nokirim', 14, 'AMP-' . $this->getDateTime()->format('ym'));
//                }
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
                $dataSK = StrukKirim::where('norec',$request['strukkirim']['noreckirim'])->where('kdprofile',$idProfile)->first();
                $strukKirimOld = StrukKirim::where('norec', $request['strukkirim']['noreckirim'])->where('kdprofile',$idProfile)->first();
                KartuStok::where('keterangan',  'Kirim Amprahan, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strRuanganTujuan . ' No Kirim: ' .  $dataSK->nokirim)
                    ->update([
                        'flagfk' => null
                    ]);
//                return $this->respond($strukKirimOld->jenispermintaanfk);
                if ($request['strukkirim']['objectruanganfk'] == $strukKirimOld->objectruanganfk){

                    $getDetails = KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])
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
                            ->where('kdprofile',$idProfile)
                            ->where('objectruanganfk',$request['strukkirim']['objectruanganfk'])
                            ->where('objectprodukfk',$item->objectprodukfk)
                            ->first();
                        StokProdukDetail::where('norec', $tambah->norec)
                            ->where('kdprofile',$idProfile)
                            ->update([
                                    'qtyproduk' => (float)$tambah->qtyproduk + (float)$item->qtyproduk]
                            );

                        $tglnow =  date('Y-m-d H:i:s');
                        $tglUbah = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow)));

                        //## KartuStok
                        $newKS = new KartuStok();
                        $norecKS = $newKS->generateNewId();
                        $newKS->norec = $norecKS;
                        $newKS->kdprofile = $idProfile;
                        $newKS->statusenabled = true;
                        $newKS->jumlah = (float)$item->qtyproduk;
                        $newKS->keterangan = 'Ubah Kirim Barang, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strNmRuanganStrukKirimSebelumnya . ' No Kirim: ' .  $dataSK->nokirim;
                        $newKS->produkfk = $item->objectprodukfk;
                        $newKS->ruanganfk = $request['strukkirim']['objectruanganfk'];
                        $newKS->saldoawal = (float)$saldoAwalPengirim + (float)$item->qtyproduk;
                        $newKS->status = 1;
                        $newKS->tglinput = $tglUbah;//date('Y-m-d H:i:s');
                        $newKS->tglkejadian = $tglUbah;//date('Y-m-d H:i:s');
                        $newKS->nostrukterimafk =  $item->nostrukterimafk;
                        $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                        $newKS->tabletransaksi = 'strukkirim_t';
                        $newKS->save();

                        if ($request['strukkirim']['jenispermintaanfk'] == 2) {
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
                            if ($dataSK->jenispermintaanfk == 2){
                                $kurang = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
                                    ->where('kdprofile',$idProfile)
                                    ->where('objectruanganfk', $strIdRuanganStrukKirimSebelumnya)
                                    ->where('objectprodukfk', $item->objectprodukfk)
//                              ->where('qtyproduk','>',0)
                                    ->first();
                                StokProdukDetail::where('norec', $kurang->norec)
                                    ->where('kdprofile',$idProfile)
                                    ->update([
                                            'qtyproduk' => (float)$kurang->qtyproduk - (float)$item->qtyproduk]
                                    );
//                            return $this->respond((float)$saldoAwalPenerima);

                                $tglnow1 =  date('Y-m-d H:i:s');
                                $tglUbah1 = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow1)));

                                //## KartuStok
                                $newKS = new KartuStok();
                                $norecKS = $newKS->generateNewId();
                                $newKS->norec = $norecKS;
                                $newKS->kdprofile = $idProfile;
                                $newKS->statusenabled = true;
                                $newKS->jumlah = (float)$item->qtyproduk;
                                $newKS->keterangan = 'Ubah Terima Barang, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strNmRuanganStrukKirimSebelumnya . ' No Kirim: ' .  $dataSK->nokirim;
                                $newKS->produkfk = $item->objectprodukfk;
                                $newKS->ruanganfk = $strIdRuanganStrukKirimSebelumnya;//$request['strukkirim']['objectruangantujuanfk'];
                                $newKS->saldoawal = (float)$saldoAwalPenerima - (float)$item->qtyproduk;
                                $newKS->status = 0;
                                $newKS->tglinput = $tglUbah1;//date('Y-m-d H:i:s');
                                $newKS->tglkejadian = $tglUbah1;//date('Y-m-d H:i:s');
                                $newKS->nostrukterimafk =  $item->nostrukterimafk;
                                $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                                $newKS->tabletransaksi = 'strukkirim_t';
                                $newKS->save();
                            }else{

                            }
                        }elseif($strukKirimOld->jenispermintaanfk == 2 && $request['strukkirim']['jenispermintaanfk'] == 1) {
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
                                ->where('kdprofile',$idProfile)
                                ->where('objectruanganfk', $strIdRuanganStrukKirimSebelumnya)
                                ->where('objectprodukfk', $item->objectprodukfk)
//                              ->where('qtyproduk','>',0)
                                ->first();
                            StokProdukDetail::where('norec', $kurang->norec)
                                ->where('kdprofile',$idProfile)
                                ->update([
                                        'qtyproduk' => (float)$kurang->qtyproduk - (float)$item->qtyproduk]
                                );
//                            return $this->respond((float)$saldoAwalPenerima);

                            $tglnow1 =  date('Y-m-d H:i:s');
                            $tglUbah1 = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow1)));

                            //## KartuStok
                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $idProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = (float)$item->qtyproduk;
                            $newKS->keterangan = 'Ubah Terima Barang, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strNmRuanganStrukKirimSebelumnya . ' No Kirim: ' .  $dataSK->nokirim;
                            $newKS->produkfk = $item->objectprodukfk;
                            $newKS->ruanganfk = $strIdRuanganStrukKirimSebelumnya;//$request['strukkirim']['objectruangantujuanfk'];
                            $newKS->saldoawal = (float)$saldoAwalPenerima - (float)$item->qtyproduk;
                            $newKS->status = 0;
                            $newKS->tglinput = $tglUbah1;//date('Y-m-d H:i:s');
                            $newKS->tglkejadian = $tglUbah1;//date('Y-m-d H:i:s');
                            $newKS->nostrukterimafk =  $item->nostrukterimafk;
                            $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                            $newKS->tabletransaksi = 'strukkirim_t';
                            $newKS->save();
                        } else{
//                             $kurangin = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
//                                        ->where('objectruanganfk', $request['strukkirim']['objectruangantujuanfk'])
//                                        ->where('objectprodukfk', $item->objectprodukfk)
//                                        ->first();
//                             StokProdukDetail::where('norec', $kurangin->norec)
//                                ->update([
//                                        'qtyproduk' => (float)$kurangin->qtyproduk - (float)$item->qtyproduk]
//                                );
                        }
                    }
                    KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])->where('kdprofile',$idProfile)->delete();

                }else{

                    $ruanganAsal = Ruangan::where('id', $strukKirimOld->objectruanganfk)->where('kdprofile',$idProfile)->first();
                    $ruanganTujuan = Ruangan::where('id', $strukKirimOld->objectruangantujuanfk)->where('kdprofile',$idProfile)->first();
                    $getDetails = KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])
                        ->where('kdprofile',$idProfile)
                        ->where('qtyproduk','>',0)
                        ->get();

                    foreach ($getDetails as $item){
                        //PENGIRIM
                        $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                            array(
                                'ruanganfk' => $strukKirimOld->objectruanganfk,
                                'produkfk' => $item->objectprodukfk,
                            )
                        );
                        $saldoAwalPengirim = 0;
                        foreach ($dataSaldoAwalK as $items) {
                            $saldoAwalPengirim = (float)$items->qty;
                        }
                        $tambah = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
                            ->where('kdprofile',$idProfile)
                            ->where('objectruanganfk',$strukKirimOld->objectruanganfk)
                            ->where('objectprodukfk',$item->objectprodukfk)
                            ->first();
                        StokProdukDetail::where('norec', $tambah->norec)
                            ->where('kdprofile',$idProfile)
                            ->update([
                                    'qtyproduk' => (float)$tambah->qtyproduk + (float)$item->qtyproduk]
                            );

                        $tglnow =  date('Y-m-d H:i:s');
                        $tglUbah = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow)));

                        //## KartuStok
                        $newKS = new KartuStok();
                        $norecKS = $newKS->generateNewId();
                        $newKS->norec = $norecKS;
                        $newKS->kdprofile = $idProfile;
                        $newKS->statusenabled = true;
                        $newKS->jumlah = (float)$item->qtyproduk;
                        $newKS->keterangan = 'Ubah Kirim Barang, dari Ruangan '. $ruanganAsal->namaruangan .' ke Ruangan ' . $ruanganTujuan->namaruangan . ' No Kirim: ' .  $dataSK->nokirim;
                        $newKS->produkfk = $item->objectprodukfk;
                        $newKS->ruanganfk = $strukKirimOld->objectruanganfk;//$request['strukkirim']['objectruanganfk'];
                        $newKS->saldoawal = (float)$saldoAwalPengirim + (float)$item->qtyproduk;
                        $newKS->status = 1;
                        $newKS->tglinput = $tglUbah;//date('Y-m-d H:i:s');
                        $newKS->tglkejadian = $tglUbah;//date('Y-m-d H:i:s');
                        $newKS->nostrukterimafk =  $item->nostrukterimafk;
                        $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                        $newKS->tabletransaksi = 'strukkirim_t';
                        $newKS->save();

                        if ($request['strukkirim']['jenispermintaanfk'] == 2) {
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
                            if ($dataSK->jenispermintaanfk == 2){
                                $kurang = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
                                    ->where('kdprofile',$idProfile)
                                    ->where('objectruanganfk', $strIdRuanganStrukKirimSebelumnya)
                                    ->where('objectprodukfk', $item->objectprodukfk)
//                              ->where('qtyproduk','>',0)
                                    ->first();
                                StokProdukDetail::where('norec', $kurang->norec)
                                    ->where('kdprofile',$idProfile)
                                    ->update([
                                            'qtyproduk' => (float)$kurang->qtyproduk - (float)$item->qtyproduk]
                                    );
//                            return $this->respond((float)$saldoAwalPenerima);

                                $tglnow1 =  date('Y-m-d H:i:s');
                                $tglUbah1 = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow1)));

                                //## KartuStok
                                $newKS = new KartuStok();
                                $norecKS = $newKS->generateNewId();
                                $newKS->norec = $norecKS;
                                $newKS->kdprofile = $idProfile;
                                $newKS->statusenabled = true;
                                $newKS->jumlah = (float)$item->qtyproduk;
                                $newKS->keterangan = 'Ubah Terima Barang, dari Ruangan '. $ruanganAsal->namaruangan .' ke Ruangan ' . $ruanganTujuan->namaruangan . ' No Kirim: ' .  $dataSK->nokirim;
                                $newKS->produkfk = $item->objectprodukfk;
                                $newKS->ruanganfk = $strukKirimOld->objectruangantujuanfk;//$strIdRuanganStrukKirimSebelumnya;//$request['strukkirim']['objectruangantujuanfk'];
                                $newKS->saldoawal = (float)$saldoAwalPenerima - (float)$item->qtyproduk;
                                $newKS->status = 0;
                                $newKS->tglinput = $tglUbah1;//date('Y-m-d H:i:s');
                                $newKS->tglkejadian = $tglUbah1;//date('Y-m-d H:i:s');
                                $newKS->nostrukterimafk =  $item->nostrukterimafk;
                                $newKS->norectransaksi = $request['strukkirim']['noreckirim'];
                                $newKS->tabletransaksi = 'strukkirim_t';
                                $newKS->save();
                            }else{

                            }
                        }else{
//                             $kurangin = StokProdukDetail::where('nostrukterimafk', $item->nostrukterimafk)
//                                        ->where('objectruanganfk', $request['strukkirim']['objectruangantujuanfk'])
//                                        ->where('objectprodukfk', $item->objectprodukfk)
//                                        ->first();
//                             StokProdukDetail::where('norec', $kurangin->norec)
//                                ->update([
//                                        'qtyproduk' => (float)$kurangin->qtyproduk - (float)$item->qtyproduk]
//                                );
                        }
                    }
                    KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])->where('kdprofile',$idProfile)->delete();
                }
            }

            $dataSK->kdprofile = $idProfile;
            $dataSK->statusenabled = true;
            $dataSK->objectpegawaipengirimfk = $request['strukkirim']['objectpegawaipengirimfk'];
            $dataSK->objectruanganasalfk = $request['strukkirim']['objectruanganfk'];
            $dataSK->objectruanganfk = $request['strukkirim']['objectruanganfk'];
            $dataSK->objectruangantujuanfk = $request['strukkirim']['objectruangantujuanfk'];
            $dataSK->jenispermintaanfk = $request['strukkirim']['jenispermintaanfk'];
            $dataSK->objectkelompoktransaksifk = 34;
            $dataSK->keteranganlainnyakirim =$request['strukkirim']['keteranganlainnyakirim'];
            $dataSK->qtydetailjenisproduk = 0;
            $dataSK->qtyproduk = $request['strukkirim']['qtyproduk'];
            $dataSK->tglkirim = date($request['strukkirim']['tglkirim']);
            $dataSK->totalbeamaterai = 0;
            $dataSK->totalbiayakirim = 0;
            $dataSK->totalbiayatambahan = 0;
            $dataSK->totaldiscount = 0;
            $dataSK->totalhargasatuan = $request['strukkirim']['totalhargasatuan'];
            $dataSK->totalharusdibayar = 0;
            $dataSK->totalpph =0;
            $dataSK->totalppn = 0;
            $dataSK->noregistrasifk = $request['strukkirim']['norec_apd'];
            $dataSK->noorderfk = $request['strukkirim']['norecOrder'];
            if (isset($request['strukkirim']['statuskirim'])){
                $dataSK->statuskirim = $request['strukkirim']['statuskirim'];
            }
            $dataSK->save();

            $norecSK = $dataSK->norec;

            foreach ($request['details'] as $item) {
                //cari satuan standar
                $satuanstandar = DB::select(DB::raw("
                     select  ru.objectsatuanstandarfk
                     from produk_m as ru 
                    where ru.id=:id"),
                    array(
                        'id' => $item['produkfk'],
                    )
                );
                $satuanstandarfk = $satuanstandar[0]->objectsatuanstandarfk;

                if ($request['strukkirim']['jenispermintaanfk'] == 2) {
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
                                $dataKP->hargadiscount = $items->hargadiscount;
                                $dataKP->harganetto = $items->harganetto;
                                $dataKP->hargapph = 0;
                                $dataKP->hargappn = 0;
                                $dataKP->hargasatuan = $items->hargasatuan;
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
                                $dataNewSPD->hargadiscount = $dataStok->hargadiscount;
                                $dataNewSPD->harganetto1 = $dataStok->harganetto1;
                                $dataNewSPD->harganetto2 = $dataStok->harganetto2;
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
                                $dataKP->hargadiscount = $items->hargadiscount;
                                $dataKP->harganetto = $items->harganetto;
                                $dataKP->hargapph = 0;
                                $dataKP->hargappn = 0;
                                $dataKP->hargasatuan = $items->hargasatuan;
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
                                $dataNewSPD->hargadiscount = $dataStok->hargadiscount;
                                $dataNewSPD->harganetto1 = $dataStok->harganetto1;
                                $dataNewSPD->harganetto2 = $dataStok->harganetto2;
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
                    $newKS->keterangan = 'Kirim Barang, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strRuanganTujuan . ' No Kirim: ' .  $dataSK->nokirim;
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
                    $newKS2->keterangan = 'Terima Barang, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strRuanganTujuan . ' No Kirim: ' .  $dataSK->nokirim;
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
                }
                if ($request['strukkirim']['jenispermintaanfk'] == 1) {
                    //PENGIRIM AMPRAHAN
//                    $dataSaldoAwalK = DB::select(DB::raw("select qtyproduk as qty,norec from stokprodukdetail_t
//                        where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
//                        array(
//                            'ruanganfk' => $request['strukkirim']['objectruanganfk'],
//                            'produkfk' => $item['produkfk'],
//                        )
//                    );
                    $dataSaldoAwalK = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec,objectasalprodukfk as asalprodukfk,
                        hargadiscount,harganetto1 as harganetto,harganetto1 as hargasatuan
                        from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk > 0 "),
                        array(
                            'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                            'produkfk' => $item['produkfk'],
                        )
                    );
                    $saldoAwalPengirim = 0;
                    $jumlah=(float)$item['jumlah'] * (float)$item['nilaikonversi'];
                    foreach ($dataSaldoAwalK as $items) {
                        $saldoAwalPengirim =$saldoAwalPengirim + (float)$items->qty;
                        if ((float)$items->qty <= $jumlah){
                            $dataKP = new KirimProduk;
                            $dataKP->norec = $dataKP->generateNewId();
                            $dataKP->kdprofile = $idProfile;
                            $dataKP->statusenabled = true;
                            $dataKP->objectasalprodukfk = $items->asalprodukfk;
                            $dataKP->hargadiscount = $items->hargadiscount;
                            $dataKP->harganetto = $items->harganetto;
                            $dataKP->hargapph = 0;
                            $dataKP->hargappn = 0;
                            $dataKP->hargasatuan = $items->hargasatuan;
                            $dataKP->hargatambahan = 0;
                            $dataKP->hasilkonversi = (float)$items->qty;
                            $dataKP->objectprodukfk = $item['produkfk'];
                            $dataKP->objectprodukkirimfk = $item['produkfk'];
                            $dataKP->nokirimfk = $norecSK;
                            $dataKP->persendiscount = 0;
                            $dataKP->qtyproduk = (float)$items->qty;
                            $dataKP->qtyprodukkonfirmasi = (float)$items->qty;
                            $dataKP->qtyprodukretur = 0;
                            $dataKP->qtyorder = $item['qtyorder'];
                            $dataKP->qtyprodukterima = (float)$items->qty;
                            $dataKP->nostrukterimafk = $items->nostrukterimafk;
                            $dataKP->objectruanganfk =$request['strukkirim']['objectruangantujuanfk'];
                            $dataKP->objectruanganpengirimfk =  $request['strukkirim']['objectruanganfk'];
                            $dataKP->satuan = '-';
                            $dataKP->objectsatuanstandarfk = $satuanstandarfk;//$item['satuanstandarfk'];
                            $dataKP->satuanviewfk = $item['satuanviewfk'];
                            $dataKP->tglpelayanan = date($request['strukkirim']['tglkirim']);
                            $dataKP->qtyprodukterimakonversi = (float)$items->qty;
                            $dataKP->save();

                            $jumlah = $jumlah - (float)$items->qty;
                            StokProdukDetail::where('norec', $items->norec)
                                ->where('kdprofile', $idProfile)
                                ->update([
                                        'qtyproduk' => 0]
                                );
                        }else{

                            $dataKP = new KirimProduk;
                            $dataKP->norec = $dataKP->generateNewId();
                            $dataKP->kdprofile = $idProfile;
                            $dataKP->statusenabled = true;
                            $dataKP->objectasalprodukfk = $items->asalprodukfk;
                            $dataKP->hargadiscount = $items->hargadiscount;
                            $dataKP->harganetto = $items->harganetto;
                            $dataKP->hargapph = 0;
                            $dataKP->hargappn = 0;
                            $dataKP->hargasatuan = $items->hargasatuan;
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
                            $dataKP->objectruanganfk =$request['strukkirim']['objectruangantujuanfk'];
                            $dataKP->objectruanganpengirimfk =  $request['strukkirim']['objectruanganfk'];
                            $dataKP->satuan = '-';
                            $dataKP->objectsatuanstandarfk = $satuanstandarfk;//$item['satuanstandarfk'];
                            $dataKP->satuanviewfk = $item['satuanviewfk'];
                            $dataKP->tglpelayanan = date($request['strukkirim']['tglkirim']);
                            $dataKP->qtyprodukterimakonversi = $jumlah;
                            $dataKP->save();

                            $saldoakhir =(float)$items->qty - $jumlah;
                            $jumlah=0;
                            StokProdukDetail::where('norec', $items->norec)
                                ->where('kdprofile', $idProfile)
                                ->update([
                                        'qtyproduk' => (float)$saldoakhir]
                                );
                        }
                    }

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->keterangan = 'Kirim Amprahan, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strRuanganTujuan . ' No Kirim: ' .  $dataSK->nokirim;
                    $newKS->produkfk = $item['produkfk'];
                    $newKS->ruanganfk = $request['strukkirim']['objectruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalPengirim - ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $items->norec;
                    $newKS->norectransaksi = $norecSK;
                    $newKS->tabletransaksi = 'strukkirim_t';
                    $newKS->flagfk = 2;
                    $newKS->save();
                }
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
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Kirim Barang";

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
                "stokdetail" => $dataSTOKDETAIL,
                "stokdetailTujuan" => $dataSTOKDETAIL2,
                "req" => $request->all(),
                "kartuStok" => $kirim,
                "kartuStokTujuan" => $terima,
                "kirimproduk" => $dataKP,
//                "saldoawalTujuan" => $dataSaldoAwalT,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function CekProdukKirim(Request $request) {
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
            ->where('spd.objectruanganfk',$request['objectruanganfk'])
            ->where('spd.kdprofile',$idProfile)
            ->groupBy('pr.id','pr.namaproduk')
            ->get();

        $result= array(
            'data' => $details,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getDaftarStokRuanganSO(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();
        $detailjenisprodukfk = '';
        $jenisprodukfk='';
        $namaproduk='';
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId='';

        if(isset( $request['jeniskprodukid'])&&  $request['jeniskprodukid']!=''){
            $jenisprodukfk = "and djp.objectjenisprodukfk in (".$request['jeniskprodukid'].")";
        }
        if(isset( $request['namaproduk'])&&  $request['namaproduk']!=''){
            $namaproduk = "and pr.namaproduk  ILIKE '%". $request['namaproduk']."%'";
        }
        if(isset( $request['detailjenisprodukfk'])&&  $request['detailjenisprodukfk']!=''){
            $detailjenisprodukfk = "and djp.id in (".$request['detailjenisprodukfk'].")";
        }
        if(isset( $request['ruanganfk'])&&  $request['ruanganfk']!=''){
            $ruanganId = "and ru.id =".$request['ruanganfk'];
        }

        $data = DB::select(DB::raw("
				SELECT
					x.kdproduk,
					x.tglclosing,
					x.namaproduk,
					x.satuanstandar,
					x.namaruangan,
					x.tglkadaluarsa,
					SUM (x.qtyprodukreal) AS qtyprodukreal,
					SUM (x.harganetto1) AS harganetto1,
					SUM (x.total) AS total
				FROM
					(
						SELECT DISTINCT
							pr.id AS kdproduk,
							sp.tglstruk,
							sc.tglclosing,
							pr.namaproduk,
							ss.satuanstandar,
							spd.qtyprodukreal,
							spd.harganetto1,
							spd.qtyprodukreal * spd.harganetto1 AS total,
							ru.namaruangan,
							spdt.tglkadaluarsa
						FROM
							strukclosing_t sc
						LEFT JOIN stokprodukdetailopname_t spd ON spd.noclosingfk = sc.norec
						LEFT JOIN strukpelayanan_t sp ON sp.norec = spd.nostrukterimafk
						LEFT JOIN strukpelayanandetail_t spdt ON spdt.noclosingfk = sc.norec
						LEFT JOIN produk_m pr ON pr.id = spd.objectprodukfk
						LEFT JOIN detailjenisproduk_m djp ON djp.id = pr.objectdetailjenisprodukfk
						LEFT JOIN satuanstandar_m ss ON ss.id = pr.objectsatuanstandarfk
						LEFT JOIN ruangan_m ru ON ru.id = spd.objectruanganfk
						WHERE sc.kdprofile =$idProfile and
							sc.tglclosing BETWEEN '$tglAwal'
						AND '$tglAkhir'
						$ruanganId
					$namaproduk
					$detailjenisprodukfk
					$jenisprodukfk
					
					
					) AS x
				GROUP BY
					x.kdproduk,
					x.tglclosing,
					x.namaproduk,
					x.satuanstandar,
					x.namaruangan,
					x.tglkadaluarsa
			"));
        $samateu = false;
        $arrayFix = [];
        foreach ($data as $item){
            $samateu = false;
            foreach ($arrayFix as $itemsss){
                if ($item->kdproduk == $itemsss['kdproduk']){
                    $samateu = true;
                    if ($item->tglclosing > date($itemsss['tglclosing'] )){
                        $itemsss['qtyprodukreal'] =(float) $item->qtyprodukreal;
                        $itemsss['tglclosing'] = $item->tglclosing;
                        break;
                    }
                }
            }
            if ($samateu == false){
                $arrayFix[] = array(
                    'kdproduk' => $item->kdproduk,
                    'tglclosing'=> $item->tglclosing,
                    'namaproduk' => $item->namaproduk,
                    'satuanstandar' => $item->satuanstandar,
                    'namaruangan' => $item->namaruangan,
                    'tglkadaluarsa' => $item->tglkadaluarsa,
                    'qtyprodukreal' => (float)$item->qtyprodukreal,
                    'harga' => (float)$item->harganetto1,
                    'total' => (float) $item->total,

                );
            }
        }
        $result= array(
            'data' => $arrayFix,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarDistribusiBarang(Request $request) {
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
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk',
                'ru.id as ruasalid','ru.namaruangan as ruanganasal','ru2.id as rutujuanid','ru2.namaruangan as ruangantujuan','sp.keteranganlainnyakirim',
                DB::raw('count(kp.objectprodukfk) as jmlitem')
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim');

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
            $data = $data->where('ru2.namaruangan','ILIKE', '%'.$request['ruangantujuanfk'].'%');
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
            );
        }
        $data2 = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap',
                'ru.namaruangan as ruanganasal','ru.id as ruasalid','ru2.namaruangan as ruangantujuan','ru2.id as rutujuanid','sp.keteranganlainnyakirim',
                DB::raw('count(kp.objectprodukfk) as jmlitem')
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim');

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
            $data = $data->where('ru2.namaruangan','ILIKE', '%'.$request['ruangantujuanfk'].'%');
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

    public function BatalKirimTerima (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {
            if($request['strukkirim']['noorderfk'] != ''){
                $dataAing = StrukOrder::where('norec',$request['strukkirim']['noorderfk'])
                    ->update([
                        'statusorder' => 2
                    ]);
            }
            $dataSK = StrukKirim::where('norec', $request['strukkirim']['noreckirim'])->where('kdprofile',$idProfile)->first();
            $getDetails = KirimProduk::where('nokirimfk', $request['strukkirim']['noreckirim'])
                ->where('kdprofile',$idProfile)
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
                    ->where('kdprofile',$idProfile)
                    ->where('objectruanganfk', $request['strukkirim']['objectruanganasal'])
                    ->where('objectprodukfk', $item->objectprodukfk)
                    ->first();
                StokProdukDetail::where('norec', $tambah->norec)
                    ->where('kdprofile',$idProfile)
                    ->update([
                            'qtyproduk' => (float)$tambah->qtyproduk + (float)$item->qtyproduk]
                    );

                $dataKs = KartuStok::where('keterangan',  'Kirim Amprahan, dari Ruangan '. $request['strukkirim']['ruanganasal'] .' ke Ruangan '. $request['strukkirim']['ruangantujuan'] .' No Kirim: '. $dataSK->nokirim)
                    ->where('kdprofile', $kdProfile)
                    ->update([
                        'flagfk' => null
                    ]);
//                    ->first();

//                return $this->respond($dataKs);

                //## KartuStok
                $newKS = new KartuStok();
                $norecKS = $newKS->generateNewId();
                $newKS->norec = $norecKS;
                $newKS->kdprofile = $idProfile;
                $newKS->statusenabled = true;
                $newKS->jumlah = (float)$item->qtyproduk;
                $newKS->keterangan = 'Batal Kirim Barang ke Ruangan ' . $request['strukkirim']['ruangantujuan'] . ' No Kirim: ' . $dataSK->nokirim;
                $newKS->produkfk = $item->objectprodukfk;
                $newKS->ruanganfk = $request['strukkirim']['objectruanganasal'];
                $newKS->saldoawal = (float)$saldoAwalPengirim + (float)$item->qtyproduk;
                $newKS->status = 1;
                $newKS->tglinput = date('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $item->nostrukterimafk;
                $newKS->save();

                if ($request['strukkirim']['jenispermintaanfk'] == 2) {
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
                        ->where('objectruanganfk', $request['strukkirim']['obejectruangantujuan'])
                        ->where('objectprodukfk', $item->objectprodukfk)
//                        ->where('qtyproduk','>',0)
                        ->first();
                    StokProdukDetail::where('norec', $kurang->norec)
                        ->where('kdprofile',$idProfile)
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
                }
            }
            StrukKirim::where('norec', $request['strukkirim']['noreckirim'])
                ->where('kdprofile',$idProfile)
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
            $logUser->jenislog = 'Batal Kirim';
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
                "datalogin" => $dataLogin['userData'],
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "saldoAwal" => $saldoAwalPengirim,
                "data" => $dataSK,
                "datalogin" => $dataLogin['userData'],
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarProdukToBatal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $noKirim=$request['nokirimfk'];
        $ruanganFk=$request['ruanganfk'];

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

        $details =  \DB::select(DB::raw("select pr.id as kdeproduk,pr.namaproduk,ss.satuanstandar,
                                  kp.nostrukterimafk,spd.tglpelayanan,SUM(spd.qtyproduk) as qtyproduk
                       from kirimproduk_t as kp
                       left join strukkirim_t as sk on sk.norec = kp.nokirimfk
                       left join produk_m as pr on pr.id=kp.objectprodukfk
                       left join stokprodukdetail_t as spd on spd.nostrukterimafk = kp.nostrukterimafk  and spd.objectprodukfk=kp.objectprodukfk
                       left join satuanstandar_m as ss on ss.id=kp.objectsatuanstandarfk
                       where kp.kdprofile = $idProfile and kp.nokirimfk='$noKirim' and sk.objectruangantujuanfk=$ruanganFk and spd.objectruanganfk=$ruanganFk
                            $idProduk
                       group by pr.id,pr.namaproduk,ss.satuanstandar,kp.nostrukterimafk,
                                spd.tglpelayanan,spd.qtyproduk")
        );
        return $this->respond($details);
    }

    public function BatalKirimTerimaPerItem (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {

            $i = 0;
            $str = explode(',',$request['strukkirim']['dataproduk']);
            for ($i = 0; $i < count($str); $i++){
                $arr = (int)$str[$i];
                $str[$i] = $arr;
            }
            $produkfk = implode(',',$str);
            $dataSK = StrukKirim::where('norec', $request['strukkirim']['noreckirim'])->where('kdprofile', $idProfile)->first();
            $getDetails = KirimProduk::where('nokirimfk', $request['strukkirim']['noreckirim'])
                ->where('kdprofile', $idProfile)
                ->whereRaw('objectprodukfk in ('.$produkfk.')')
//                ->where('qtyproduk', '>', 0)
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
                $newKS->keterangan = 'Batal Kirim Barang ke Ruangan ' . $request['strukkirim']['ruangantujuan'] . ' No Kirim: ' . $dataSK->nokirim;
                $newKS->produkfk = $item->objectprodukfk;
                $newKS->ruanganfk = $request['strukkirim']['objectruanganasal'];
                $newKS->saldoawal = (float)$saldoAwalPengirim + (float)$item->qtyproduk;
                $newKS->status = 1;
                $newKS->tglinput = date('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $item->nostrukterimafk;
                $newKS->save();

                if ($request['strukkirim']['jenispermintaanfk'] == 2) {
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
                        ->where('kdprofile', $idProfile)
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
                }

                KirimProduk::where('nokirimfk', $request['strukkirim']['noreckirim'])
                    ->where('kdprofile', $idProfile)
                    ->where('objectprodukfk',$item->objectprodukfk)
                    ->where('qtyproduk',$item->qtyproduk)
                    ->delete();
                $kirProd = KirimProduk::where('nokirimfk', $request['strukkirim']['noreckirim'])->first();
                if ($kirProd == '' || $kirProd == null){
                    StrukKirim::where('norec', $request['strukkirim']['noreckirim'])->first()
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'statusenabled' => 'f' ]
                        );
                }
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Kirim';
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
                "datalogin" => $dataLogin['userData'],
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "saldoAwal" => $saldoAwalPengirim,
                "data" => $dataSK,
                "datalogin" => $dataLogin['userData'],
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarDistribusiBarangPerUnit(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdSirs1 = $request['KdSirs1'];
        $kdSirs2= $request['KdSirs2'];
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
            ->where('ru.kdprofile', $idProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
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
            $data = $data->where('sp.nokirim','ILIKE','%'. $request['nokirim']);
        }
        if(isset($request['ruanganasalfk']) && $request['ruanganasalfk']!="" && $request['ruanganasalfk']!="undefined"){
            $data = $data->where('ru.id','=', $request['ruanganasalfk']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru2.id','=', $request['ruangantujuanfk']);
        }
        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $data = $data->where('pr.namaproduk','ILIKE','%'. $request['namaproduk']);
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
                $data = $data->whereRaw = (" pr.kdproduk ILIKE '".$request['KdSirs2']."%'");
            }elseif ($request['KdSirs1'] &&  $request['KdSirs1']!= '' && $request['KdSirs2'] == '' ||  $request['KdSirs2'] == null){
                $data = $data->whereRaw = (" pr.kdproduk ILIKE '".$request['KdSirs1']."%'");
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

    public function getDaftarMutasiBarangKadaluarsa(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
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
        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk',
                'ru.id as ruasalid','ru.namaruangan as ruanganasal','ru2.id as rutujuanid','ru2.namaruangan as ruangantujuan','sp.keteranganlainnyakirim'
            )
            ->where('sp.kdprofile',$idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ILIKE','%'. $request['nokirim']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru2.id','=', $request['ruangantujuanfk']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('kp.objectprodukfk','=', $request['produkfk']);
        }
        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',100);
        $data = $data->wherein('sp.objectruanganasalfk',$strRuangan);
        $data = $data->where('sp.noregistrasifk','=',0);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = \DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk
                     from kirimbarangkadaluarsa_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nokirimfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $results[] = array(
                'status' => 'Kirim Barang',
                'tglstruk' => $item->tglkirim,
                'nostruk' => $item->nokirim,
                'noorderfk' => $item->noorderfk,
                'jenispermintaanfk' => $item->jenispermintaanfk,
                'norec' => $item->norec,
                'ruasalid'=> $item->ruasalid,
                'namaruanganasal' => $item->ruanganasal,
                'rutujuanid'=> $item->rutujuanid,
                'namaruangantujuan' => $item->ruangantujuan,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganlainnyakirim,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'cepot',
            'str' => $strRuangan,
        );

        return $this->respond($result);
    }

    public function getDaftarAmprahanHpp(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdSirs1 = $request['KdSirs1'];
        $kdSirs2= $request['KdSirs2'];
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
            ->where('mlu.kdprofile', $idProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();

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
            $data = $data->where('sp.nokirim','ILIKE','%'. $request['nokirim']);
        }
        if(isset($request['ruanganasalfk']) && $request['ruanganasalfk']!="" && $request['ruanganasalfk']!="undefined"){
            $data = $data->where('ru.id','=', $request['ruanganasalfk']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru2.id','=', $request['ruangantujuanfk']);
        }
        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $data = $data->where('pr.namaproduk','ILIKE','%'. $request['namaproduk']);
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
                $data = $data->whereRaw = (" pr.kdproduk ILIKE '".$request['KdSirs2']."%'");
            }elseif ($request['KdSirs1'] &&  $request['KdSirs1']!= '' && $request['KdSirs2'] == '' ||  $request['KdSirs2'] == null){
                $data = $data->whereRaw = (" pr.kdproduk ILIKE '".$request['KdSirs1']."%'");
            }
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',34);
        $data = $data->where('kp.qtyproduk','>', 0);
        $data = $data->where('sp.jenispermintaanfk','1');
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();
        $result = array(
            'datalogin' => $dataLogin,
            'data' => $data,
            'message' => 'ea@epic'
        );
        return $this->respond($result);
    }

    public function getDetailBarang(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('produk_m as pr')
            ->leftJOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select('pr.id as kodeproduk','pr.kodebmn','pr.namaproduk','djp.id as djpid','djp.detailjenisproduk','jp.id as jpid','jp.jenisproduk','ss.id as ssid','ss.satuanstandar',
                'kp.id as kpid','kp.kelompokproduk')
            ->where('pr.kdprofile', $idProfile);

        if (isset($request['kodeproduk']) && $request['kodeproduk'] != "" && $request['kodeproduk'] != "undefined") {
            $data = $data->where('pr.id', '=', $request['kodeproduk']);
        }
        $data = $data->where('pr.statusenabled',true);
        $data = $data->orderBy('pr.id');
        $data = $data->limit(1);
        $data = $data->get();

        $result = array(
            'datas' => $data,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDetailBarangRegisterAset(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('registrasiaset_t as ra')
            ->leftJOIN('strukpelayanan_t as sp','sp.norec','=','ra.nostrukterimafk')
            ->leftJOIN('strukpelayanandetail_t as spd','spd.norec','=','ra.nostrukterimadetailfk')
            ->JOIN('produk_m as pr','pr.id','=','ra.objectprodukfk')
            ->leftJOIN('detailjenisproduk_m as djp','djp.id','=','ra.objectdetailjenisproduk')
            ->leftJOIN('jenisproduk_m as jp','jp.id','=','ra.objectjenisproduk')
//            ->leftJOIN('kelompokproduk_m as kp','kp.id','=','ra.objectkelompokasetfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','ra.objectsatuan')
            ->leftJOIN('asalproduk_m as ap','ap.id','=','ra.objectasalprodukfk')
            ->leftJOIN('ruangan_m as ru','ru.id','=','ra.objectruanganfk')
            ->leftJOIN('ruangan_m as ru1','ru1.id','=','ra.objectruanganposisicurrentfk')
            ->leftJOIN('rekanan_m as rek','rek.id','=','ra.objectsupplier')
            ->leftJOIN('kelompokaset_m as ka','ka.id','=','ra.objectkelompokasetfk')
            ->leftJOIN('merkproduk_m as mp','mp.id','=','ra.objectmerkprodukfk')
            ->leftJOIN('typeproduk_m as tp','tp.id','=','ra.objecttypeprodukfk')
            ->select('ra.norec','ra.noregisteraset','ra.objectprodukfk as idproduk','pr.namaproduk','jp.id as jpid','jp.jenisproduk','ra.kdbmn as kodebmn',
                'pr.kodeexternal','pr.kdproduk','pr.tglproduksi','ru.id as ruanganasalfk','ru.namaruangan as namaruanganasal','ru1.id as ruangancurrenfk',
                'ru1.namaruangan as ruangancurrent','ap.id as apid','ap.asalproduk','ka.id as kaid','ka.kelompokaset','ra.tglregisteraset',
                'ra.tglstrukterima','ra.tahunperolehan','djp.id as djpid','djp.detailjenisproduk','ra.qtyprodukaset','ra.hargaperolehan','ra.tglregisteraset',
                'ra.tglstrukterima','rek.id as idsupplier','rek.namarekanan as namasupplier','rek.alamatlengkap as almSupplier','sp.norec as norecsp',
                'ss.id as ssid','ss.satuanstandar','ra.qtyprodukaset','spd.norec as norecspd','ap.asalproduk','ra.noregisteraset_int',
                'ra.masaberlakusertifikat','ra.sisaumur','ra.noseri','ra.tahunperolehan','ra.objectmerkprodukfk as merkid','mp.merkproduk',
                'ra.tahunperolehan','tp.typeproduk','ra.nilaisisa','ra.umurasset')
            ->where('ra.kdprofile',$idProfile);

        if (isset($request['norecAsset']) && $request['norecAsset'] != "" && $request['norecAsset'] != "undefined") {
            $data = $data->where('ra.norec', '=', $request['norecAsset']);
        }
//        $data = $data->where('ra.statusenabled',true);
        $data = $data->orderBy('ra.noregisteraset');
        $data = $data->limit(1);
        $data = $data->get();

        $result = array(
            'datas' => $data,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function SimpanDetailRegisterAset (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {
            //## RegisterAsset
            if ($request['regAset']['noRegisaset'] == '') {
                $noasset = $this->generateCode(new RegistrasiAset(),'noregisteraset',12,'AST'.$this->getDateTime()->format('ym'), $idProfile);
                $dataRegAset = new RegistrasiAset();
                $dataRegAset->norec = $dataRegAset->generateNewId();
                $dataRegAset->kdprofile = $idProfile;
                $dataRegAset->statusenabled = true;
                $dataRegAset->noregisteraset = $noasset;
                $dataRegAset->tglregisteraset =$request['regAset']['tglregisteraset'];
                $dataRegAset->objectprodukfk=$request['regAset']['objectprodukfk'];
                $dataRegAset->qtyprodukaset=1;
            }else{
                $dataRegAset =  RegistrasiAset::where('noregisteraset',$request['regAset']['noRegisaset'])->first();
            }
            $dataRegAset->alamatlengkap = $request['regAset']['alamatlengkap'];
            $dataRegAset->objectruanganfk = $request['regAset']['objectruanganfk'];
            $dataRegAset->objectruanganposisicurrentfk =$request['regAset']['objectruanganposisicurrentfk'];
            $dataRegAset->objectasalprodukfk = $request['regAset']['objectasalprodukfk'];
            $dataRegAset->objectbahanprodukfk = $request['regAset']['objectbahanprodukfk'];
            $dataRegAset->bpkb_atasnama =$request['regAset']['bpkb_atasnama'];
            $dataRegAset->objectdesakelurahanfk=$request['regAset']['objectdesakelurahanfk'];
            $dataRegAset->kdbmn=$request['regAset']['kdbmn'];
            $dataRegAset->kdjenissertifikat=$request['regAset']['kdjenissertifikat'];
//            $dataRegAset->kdrsabhk=$request['regAset']['kdrsabhk'];
            $dataRegAset->objectkecamatanfk=$request['regAset']['objectkecamatanfk'];
            $dataRegAset->objectkelompokasetfk=$request['regAset']['objectkelompokasetfk'];
            $dataRegAset->kodepos=$request['regAset']['kodepos'];
            $dataRegAset->objectkotakabupatenfk=$request['regAset']['objectkotakabupatenfk'];
            $dataRegAset->lb_lebar=$request['regAset']['lb_lebar'];
            $dataRegAset->lb_panjang=$request['regAset']['lb_panjang'];
            $dataRegAset->lb_tinggi=$request['regAset']['lb_tinggi'];
            $dataRegAset->masaberlakusertifikat=$request['regAset']['masaberlakusertifikat'];
            $dataRegAset->sertifikat_atasnama=$request['regAset']['sertifikat_atasnama'];
            $dataRegAset->kdjenissertifikat=$request['regAset']['kdjenissertifikat'];
            $dataRegAset->objectmerkprodukfk=$request['regAset']['objectmerkprodukfk'];
            $dataRegAset->desakelurahan=$request['regAset']['desakelurahan'];
            $dataRegAset->kecamatan=$request['regAset']['kecamatan'];
            $dataRegAset->kotakabupaten=$request['regAset']['kotakabupaten'];
            $dataRegAset->nobpkb=$request['regAset']['nobpkb'];
            $dataRegAset->nomesin=$request['regAset']['nomesin'];
            $dataRegAset->nomodel=$request['regAset']['nomodel'];
            $dataRegAset->nopolisi=$request['regAset']['nopolisi'];
            $dataRegAset->norangka=$request['regAset']['norangka'];
            $dataRegAset->noseri=$request['regAset']['noseri'];
            $dataRegAset->nosertifikat=$request['regAset']['nosertifikat'];
            $dataRegAset->objectprodusenprodukfk=$request['regAset']['objectprodusenprodukfk'];
            $dataRegAset->objectpropinsifk=$request['regAset']['objectpropinsifk'];
            $dataRegAset->objectwarnaprodukfk=$request['regAset']['objectwarnaprodukfk'];
            $dataRegAset->dayalistrik=$request['regAset']['dayalistrik'];
            $dataRegAset->objectdetailjenisproduk=$request['regAset']['objectdetailjenisproduk'];
            $dataRegAset->objectjenisproduk=$request['regAset']['objectjenisproduk'];
            $dataRegAset->klasifikasiteknologi=$request['regAset']['klasifikasiteknologi'];
            $dataRegAset->objectsatuan=$request['regAset']['objectsatuan'];
            $dataRegAset->sisaumur=$request['regAset']['sisaumur'];
            $dataRegAset->objectsupplier=$request['regAset']['objectsupplier'];
            $dataRegAset->tahunperolehan=$request['regAset']['tahunperolehan'];
            $dataRegAset->usiapakai=$request['regAset']['usiapakai'];
            $dataRegAset->usiateknis=$request['regAset']['usiateknis'];
            $dataRegAset->fungsikegunaan=$request['regAset']['fungsikegunaan'];
//            $dataRegAset->objecttypeprodukfk=$request['regAset']['objecttypeprodukfk'];
            $dataRegAset->tglproduksi=$request['regAset']['tglproduksi'];
            $dataRegAset->noseri=$request['regAset']['noseri'];
            $dataRegAset->hargaperolehan=$request['regAset']['hargaperolehan'];
            $dataRegAset->noregisteraset_int=$request['regAset']['noaset'];
            $dataRegAset->judul=$request['regAset']['judul'];
            $dataRegAset->spesifikasi=$request['regAset']['spesifikasi'];
            $dataRegAset->jenisaset=$request['regAset']['jenisaset'];
            $dataRegAset->nilaisisa=$request['regAset']['nilaisisa'];
            $dataRegAset->umurasset=$request['regAset']['umurasset'];
            $dataRegAset->save();
            //## END RegisterAsset

            $norecdataRegAset = $dataRegAset->norec;

            //* STOKPRODUK DETAIL
                $StokPD = new StokProdukDetail();
                $norecStokPD = $StokPD->generateNewId();
                $StokPD->norec = $norecStokPD;
                $StokPD->kdprofile = $idProfile;
                $StokPD->statusenabled = true;
                $StokPD->objectasalprodukfk = $request['regAset']['objectasalprodukfk'];
                $StokPD->hargadiscount = 0;
                $StokPD->harganetto1 = ((float)$request['regAset']['hargaperolehan']); //+(float)$request['regAset']['ppn'])/(float)$request['regAset']['nilaikonversi'];
                $StokPD->harganetto2 = ((float)$request['regAset']['hargaperolehan']); // /(float)$request['regAset']['nilaikonversi'];
                $StokPD->persendiscount = 0;
                $StokPD->objectprodukfk = $request['regAset']['objectprodukfk'];
                $StokPD->qtyproduk = $request['regAset']['qtyprodukaset'];;
                $StokPD->qtyprodukonhand = 0;
                $StokPD->qtyprodukoutext = 0;
                $StokPD->qtyprodukoutint = 0;
                $StokPD->objectruanganfk = $request['regAset']['objectruanganposisicurrentfk'];
                if(isset($request['regAset']['nostruk']) &&
                    $request['regAset']['nostruk']!="" &&
                    $request['regAset']['nostruk']!="undefined"){
                    $StokPD->nostrukterimafk = $request['regAset']['nostruk'];
                }else{
                    $StokPD->nostrukterimafk = "INITASSET-0001";
                }
//                $StokPD->nobatch = $request['regAset']['nobatch'];
                if(isset($request['regAset']['nostrukterimadetailfk']) &&
                    $request['regAset']['nostrukterimadetailfk']!="" &&
                    $request['regAset']['nostrukterimadetailfk']!="undefined"){
                    $StokPD->nostrukterimafk = $request['regAset']['nostrukterimadetailfk'];
                }else{
                    $StokPD->nostrukterimafk = "INITASSET-0001";
                }
//                $StokPD->tglkadaluarsa = $request['regAset']['tglkadaluarsa'];
                $StokPD->tglpelayanan = date('Y-m-d H:i:s');//$request['regAset']['tglregisteraset'];
                $StokPD->save();
            //* END STOKPRODUK DETAIL



            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Detail Register Asset";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "regAset" => $dataRegAset,
                "as" => 'cepot',
            );
        } else {
            $transMessage = "Simpan Detail Register Asset Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "regAset" => $dataRegAset,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function SimpanPenyusutanAset(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
//        $dataLogin = $request->all();
        try {
            $StokPD = new PenyusutanAsset();
            $norecStokPD = $StokPD->generateNewId();
            $StokPD->norec = $norecStokPD;
            $StokPD->kdprofile = $idProfile;
            $StokPD->statusenabled = true;
            $StokPD->noregistrasifk = $request['norecAsset'];
            $StokPD->eoy = $request['datasimpan']['eoy'];
            $StokPD->tahun = $request['datasimpan']['inttahun'];;
            $StokPD->hargaperolehan = $request['datasimpan']['hargaperolehan'];;
            $StokPD->nilaisisa = $request['datasimpan']['nilaisisa'];;
            $StokPD->lifetime = $request['datasimpan']['lifetime'];;
            $StokPD->penyusutan = $request['datasimpan']['nilaipenyusutan'];;
            $StokPD->akumulasipenyusutan = $request['datasimpan']['akumpenyusutan'];;
            $StokPD->nilaibuku = $request['datasimpan']['nilaibuku'];;
            $StokPD->status = 'posted';
            $StokPD->save();


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Penyusutan Asset";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Detail Register Asset Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function SimpanDeletePenyusutanAset(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
//        $dataLogin = $request->all();
        try {
            $StokPD = PenyusutanAsset::where('norec','=',$request['datasimpan']['norec'])->where('kdprofile',$idProfile)->delete();


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Penyusutan Asset";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Detail Register Asset Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataBarangRegisterAset(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('registrasiaset_t as ra')
            ->leftJOIN('strukpelayanan_t as sp','sp.norec','=','ra.nostrukterimafk')
            ->leftJOIN('strukpelayanandetail_t as spd','spd.norec','=','ra.nostrukterimadetailfk')
            ->JOIN('produk_m as pr','pr.id','=','ra.objectprodukfk')
            ->leftJOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->leftJOIN('asalproduk_m as ap','ap.id','=','ra.objectasalprodukfk')
            ->leftJOIN('ruangan_m as ru','ru.id','=','ra.objectruanganfk')
            ->leftJOIN('ruangan_m as ru1','ru1.id','=','ra.objectruanganposisicurrentfk')
            ->leftJOIN('rekanan_m as rek','rek.id','=','ra.objectsupplier')
            ->select('ra.norec','ra.noregisteraset','ra.objectprodukfk as kdproduk','pr.namaproduk','jp.jenisproduk',
                     'ru.id as ruanganasalfk','ru.namaruangan as namaruanganasal','ru1.id as ruangancurrenfk','ru1.namaruangan as ruangancurrent',
                     'ra.tglregisteraset','ra.tglstrukterima','ra.qtyprodukaset','ra.hargaperolehan','ra.tglregisteraset','ra.tglstrukterima',
                     'rek.namarekanan as namasupplier','rek.alamatlengkap as almSupplier','sp.norec as norecsp',
                     'spd.norec as norecspd','ap.asalproduk','ra.keteranganlainnya','ra.spesifikasi','ra.jenisaset','ra.judul','ra.kdbmn')
            ->where('ra.kdprofile', $idProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('ra.tglregisteraset', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('ra.tglregisteraset', '<=', $request['tglAkhir']);
        }
        if(isset($request['kdproduk']) && $request['kdproduk']!="" && $request['kdproduk']!="undefined"){
            $data = $data->where('ra.objectprodukfk','=', $request['kdproduk']);
        }
        if(isset($request['kdDetailJenis']) && $request['kdDetailJenis']!="" && $request['kdDetailJenis']!="undefined"){
            $data = $data->where('djp.id','=', $request['kdDetailJenis']);
        }
        if(isset($request['ruangancurrenfk']) && $request['ruangancurrenfk']!="" && $request['ruangancurrenfk']!="undefined"){
            $data = $data->where('ru1.id','=', $request['ruangancurrenfk']);
        }
        if (isset($request['norecAsset']) && $request['norecAsset'] != "" && $request['norecAsset'] != "undefined") {
            $data = $data->where('ra.norec', '=', $request['norecAsset']);
        }
        if (isset($request['namaproduk']) && $request['namaproduk'] != "" && $request['namaproduk'] != "undefined") {
            $data = $data->where('pr.namaproduk', 'ILIKE', '%'.$request['namaproduk'].'%');
        }
//        $data = $data->where('ra.statusenabled',true);
        $data = $data->orderBy('ra.noregisteraset');
        $data = $data->take(100);
        $data = $data->get();

        $result = array(
            'datas' => $data,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDataProdukKirim(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //todo : raw query sum
        $req=$request->all();
        $dataProduk = \DB::table('registrasiaset_t as ra')
            ->JOIN('produk_m as pr','pr.id','=','ra.objectprodukfk')
            ->leftJOIN('detailjenisproduk_m as djp','djp.id','=','ra.objectdetailjenisproduk')
            ->leftJOIN('jenisproduk_m as jp','jp.id','=','ra.objectjenisproduk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','ra.objectsatuan')
            ->leftJOIN('ruangan_m as ru','ru.id','=','ra.objectruanganposisicurrentfk')
            ->select('ra.noregisteraset','pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar','pr.kdproduk','ra.qtyprodukaset','ru.namaruangan')
            ->where('ra.qtyprodukaset','>',0)
            ->where('ra.kdprofile', $idProfile)
//            ->groupBy('pr.id','ss.id','ra.qtyprodukaset','ru.namaruangan','noregisteraset')
            ->orderBy('pr.namaproduk');
        if(isset($request['ruanganId']) && $request['ruanganId']!="" && $request['ruanganId']!="undefined"){
            $dataProduk = $dataProduk->where('ru.id','=', $request['ruanganId']);
        }
        $dataProduk = $dataProduk->get();

        $result[]=array(
            'data' => $dataProduk,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }
    public function getDataPenyusutan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //todo : raw query sum
        $req=$request->all();
        $dataProduk = \DB::table('penyusutanasset_t as pa')
            ->select('pa.*')
            ->where('pa.kdprofile',$idProfile);
        if(isset($request['norecAsset']) && $request['norecAsset']!="" && $request['norecAsset']!="undefined"){
            $dataProduk = $dataProduk->where('pa.noregistrasifk','=', $request['norecAsset']);
        }
        $dataProduk = $dataProduk->get();

        $result[]=array(
            'data' => $dataProduk,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getNoAsset(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $results = \DB::select(DB::raw("select ra.norec,ra.noregisteraset, pr.id as kdproduk, pr.namaproduk,ru.namaruangan as ruanganasal ,ru.id as ruanganasalfk,
                                       ru1.id as ruangancurrentfk, ru1.namaruangan as ruangancurrent, ra.qtyprodukaset
                                       from registrasiaset_t as ra
                                       inner JOIN produk_m as pr on pr.id = ra.objectprodukfk
                                       inner JOIN ruangan_m as ru on ru.id = ra.objectruanganfk
                                       INNER JOIN ruangan_m as ru1 on ru1.id = ra.objectruanganposisicurrentfk
                                       where ra.kdprofile = $idProfile and ra.statusenabled=1 and pr.id=:produkId and ru1.id=:ruanganid"),
            array(
                'produkId' => $request['produkfk'],
                'ruanganid' => $request['ruanganfk'],
            )
        );

        $data = DB::select(DB::raw("select ra.norec,ra.noregisteraset, pr.id as kdproduk, pr.namaproduk,ru.namaruangan as ruanganasal ,ru.id as ruanganasalfk,
                                       ru1.id as ruangancurrentfk, ru1.namaruangan as ruangancurrent, ra.qtyprodukaset
                                       from registrasiaset_t as ra
                                       inner JOIN produk_m as pr on pr.id = ra.objectprodukfk
                                       inner JOIN ruangan_m as ru on ru.id = ra.objectruanganfk
                                       INNER JOIN ruangan_m as ru1 on ru1.id = ra.objectruanganposisicurrentfk
                                       where ra.kdprofile = $idProfile and ra.statusenabled=1 and ra.noregisteraset=:noregisteraset"),
            array(
                'noregisteraset' => $request['noregisteraset'],
            )
        );

        $result= array(
            'result'=> $results,
            'dataaset' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveKirimBarangAsset(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
//        $dataLogin = $request->all();
        $nilaikonversi = 1;
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        try {
            //## StrukKirim
            if ($request['strukkirimaset']['noreckirim'] == ''){
//                if ($request['strukkirimaset']['jenispermintaanfk'] == 2) {
//                    $noKirim = $this->generateCode(new StrukKirim, 'nokirim', 14, 'TRFA-' . $this->getDateTime()->format('ym'));
//                    $jenispermintaan = 2;
//                }else{
            $noKirim = $this->generateCode(new StrukKirim, 'nokirim', 14, 'TRFA-' . $this->getDateTime()->format('ym'), $idProfile);
            $jenispermintaan = 1;
//                }
                $dataSK = new StrukKirim();
                $dataSK->norec = $dataSK->generateNewId();
                $dataSK->nokirim = $noKirim;
                $dataSK->kdprofile = $idProfile;
                $dataSK->statusenabled = true;
            }else{
                $dataSK =  StrukKirim::where('norec',$request['strukkirimaset']['noreckirim'])->first();
            }
            $dataSK->qtyproduk=0;
            $dataSK->tglkirim=$request['strukkirimaset']['tglkirim'];
            $dataSK->totalbeamaterai=0;
            $dataSK->totalbiayakirim=0;
            $dataSK->totalbeamaterai=0;
            $dataSK->totalbiayatambahan=0;
            $dataSK->totaldiscount=0;
            $dataSK->totalhargasatuan=0;
            $dataSK->totalharusdibayar=0;
            $dataSK->totalpph=0;
            $dataSK->totalppn=0;
            $dataSK->qtydetailjenisproduk=0;
            $dataSK->objectpegawaipengirimfk=$dataPegawaiUser[0]->id;
            $dataSK->objectruanganasalfk=$request['strukkirimaset']['objectruanganfk'];
            $dataSK->objectruangantujuanfk=$request['strukkirimaset']['objectruangantujuanfk'];
            $dataSK->jenispermintaanfk=$jenispermintaan;
            $dataSK->objectkelompoktransaksifk = 95;
            $dataSK->save();
            //## END StrukKirim

            $SK = array(
                "norec"  => $dataSK->norec,
            );

            foreach ($request['details'] as $item){
                $dataSaldoAwalK = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec,objectasalprodukfk as asalprodukfk,
                        hargadiscount,harganetto1 as harganetto,harganetto1 as hargasatuan
                        from stokprodukdetail_t 
                        where kdprofile = $idProfile and  objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk > 0 "),
                    array(
                        'ruanganfk' => $request['strukkirimaset']['objectruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $saldoAwalPengirim = 0;
                $jumlah=(float)$item['jumlah'] * (float)$nilaikonversi;
                foreach ($dataSaldoAwalK as $items) {
                    $saldoAwalPengirim =$saldoAwalPengirim + (float)$items->qty;
                }
                if ((float)$items->qty <= $jumlah){
                    //## KirimProdukAset
                    $dataKPA = new KirimProdukAset();
                    $dataKPA->norec = $dataKPA->generateNewId();
                    $dataKPA->kdprofile = $idProfile;
                    $dataKPA->statusenabled = true;
                    $dataKPA->objectkondisiprodukfk = $item['kondisiprodukfk'];
                    $dataKPA->nokirimfk =$SK['norec'];
                    $dataKPA->noregisterasetfk = $request['strukkirimaset']['norecAsset'];
                    $dataKPA->qtyproduk = $item['jumlah'];
                    $dataKPA->produkfk =  $item['produkfk'];
                    $dataKPA->save();
                    //## END KirimProdukAset

                    $jumlah = $jumlah - (float)$items->qty;
                    StokProdukDetail::where('norec', $items->norec)
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'qtyproduk' => 0]
                        );
                }else{
                    //## KirimProdukAset
                    $dataKPA = new KirimProdukAset();
                    $dataKPA->norec = $dataKPA->generateNewId();
                    $dataKPA->kdprofile = $idProfile;
                    $dataKPA->statusenabled = true;
                    $dataKPA->objectkondisiprodukfk = $item['kondisiprodukfk'];
                    $dataKPA->nokirimfk =$SK['norec'];
                    $dataKPA->noregisterasetfk = $request['strukkirimaset']['norecAsset'];
                    $dataKPA->qtyproduk = $item['jumlah'];
                    $dataKPA->produkfk =  $item['produkfk'];
                    $dataKPA->save();
                    //## END KirimProdukAset

                    $saldoakhir =(float)$items->qty - $jumlah;
                    $jumlah=0;
                    StokProdukDetail::where('norec', $items->norec)
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'qtyproduk' => (float)$saldoakhir]
                        );
                }

                //## RegisterAsset
                $dataRA = RegistrasiAset::where('norec', $request['strukkirimaset']['norecAsset'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                        'objectruanganposisicurrentfk' => $request['strukkirimaset']['objectruangantujuanfk'],
//                    'qtyprodukaset'=> $item['jumlah']
                    ]);
                //## END RegisterAsset
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Kirim Asset";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "strukkirim" => $dataSK,
//                "kirimaset" => $dataKPA,
//                "dataRA" => $dataRA->objectruanganposisicurrentfk,
                'message' => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Kirim Asset Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "strukkirim" => $dataSK,
//                "kirimaset" => $dataKPA,
//                 'dataRA' => $dataRA->objectruanganposisicurrentfk,
                'message' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function SaveReturDistribusi (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();
        try {
            $jeniskirim ='';
            if($request['strukkirim']['jenispermintaanfk'] == 2){
                $jeniskirim = 'Transfer';
            }else{
                $jeniskirim = 'Amprahan';
            }
            $dataSK = StrukKirim::where('norec', $request['strukkirim']['noreckirim'])->first();
            $getDetails = KirimProduk::where('nokirimfk', $request['strukkirim']['noreckirim'])
                ->where('kdprofile', $idProfile)
                ->where('qtyproduk', '>', 0)
                ->get();
            if($request['strukkirim']['norecRetur'] == ''){
                $newSRetur = new StrukRetur();
                $norecSRetur = $newSRetur->generateNewId();
                $noRetur = $this->generateCode(new StrukRetur, 'noretur', 12, 'Ret/' . $this->getDateTime()->format('ym') . '/', $idProfile);
                $newSRetur->norec = $norecSRetur;
                $newSRetur->kdprofile = $idProfile;
                $newSRetur->statusenabled = true;
                $newSRetur->objectkelompoktransaksifk = 50;
            }else{
                $newSRetur =  StrukRetur::where('norec',$request['strukkirim']['norecRetur'])->where('kdprofile', $idProfile)->first();
                StrukReturDetail::where('strukreturfk',$request['strukkirim']['norecRetur'])->where('kdprofile', $idProfile)->delete();
            }
                $newSRetur->keteranganalasan =$request['strukkirim']['keteranganlainnyakirim'];
                $newSRetur->keteranganlainnya = 'Retur  ' .$jeniskirim. '  Dari Ruangan  '.$request['strukkirim']['ruangantujuan'] . '  Ke Ruangan  '.$request['strukkirim']['ruangan']. '  No Kirim:  ' . $dataSK->nokirim;;
                $newSRetur->noretur = $noRetur;
                $newSRetur->objectruanganfk = $request['strukkirim']['objectruanganfk'];
                $newSRetur->objectruangantujuanfk = $request['strukkirim']['objectruangantujuanfk'];
                $newSRetur->objectpegawaifk = $request['strukkirim']['objectpegawaipengirimfk'];
                $newSRetur->tglretur = $this->getDateTime()->format('Y-m-d H:i:s');
                $newSRetur->strukkirimfk = $request['strukkirim']['noreckirim'];
                $newSRetur->save();
                $norecRetur = $newSRetur->norec;

            foreach ($request['details'] as $item) {
                $norecKirim="";
                $IdProduk="";
                if ((float)$item['qtyretur'] != 0){
                    //PENGIRIM
                    $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                        array(
                            'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                            'produkfk' => $item['produkfk'],
                        )
                    );
                    $saldoAwalPengirim = 0;
                    foreach ($dataSaldoAwalK as $items) {
                        $saldoAwalPengirim = (float)$items->qty;
                    }

                    $norecKirim=$request['strukkirim']['noreckirim'];
                    $IdProduk=$item['produkfk'];
                    $nostrukterima = DB::select(DB::raw("select top 1 nostrukterimafk from kirimproduk_t 
                        where kdprofile = $idProfile and nokirimfk='$norecKirim' and objectprodukfk=$IdProduk")
//                        ,
//                        array(
//                            'nokirimfk' => ,
//                            'produkfk' => $item['produkfk'],
//                        )
                    );
                    $noTarimakeun = '';
                    foreach ($nostrukterima as $items) {
                        $noTarimakeun = $items->nostrukterimafk;
                    }

                    $tambah = StokProdukDetail::where('nostrukterimafk', $noTarimakeun)
                        ->where('kdprofile', $idProfile)
                        ->where('objectruanganfk', $request['strukkirim']['objectruanganfk'])
                        ->where('objectprodukfk', $item['produkfk'])
                        ->first();

                    //## StrukReturDetail
                    $StokPD = new StrukReturDetail();
                    $norecStokPD = $StokPD->generateNewId();
                    $StokPD->norec = $norecStokPD;
                    $StokPD->kdprofile = $idProfile;
                    $StokPD->statusenabled = true;
                    $StokPD->objectasalprodukfk = $item['asalprodukfk'];
                    $StokPD->hargadiscount = 0;
                    $StokPD->harganetto1 = (float)$tambah->harganetto1;
                    $StokPD->harganetto2 = (float)$tambah->harganetto2;
                    $StokPD->persendiscount = 0;
                    $StokPD->objectprodukfk = $item['produkfk'];
                    $StokPD->qtyproduk = (float)$item['qtyretur'];
                    $StokPD->qtyprodukonhand = 0;
                    $StokPD->qtyprodukoutext = 0;
                    $StokPD->qtyprodukoutint = 0;
                    $StokPD->nostrukterimafk =$noTarimakeun;
                    $StokPD->strukreturfk =$norecRetur;
                    $StokPD->tglkadaluarsa = $tambah->tglkadaluarsa;
                    $StokPD->save();

                    StokProdukDetail::where('norec', $tambah->norec)
                        ->where('kdprofile', $idProfile)
                        ->update([
                            'qtyproduk' => (float)$tambah->qtyproduk + (float)$item['qtyretur']
                        ]);

                    KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])
                        ->where('kdprofile', $idProfile)
                        ->where ('objectprodukfk', $item['produkfk'])
                        ->update([
                            'qtyprodukretur' => (float)$item['qtyretur'],
                            'qtyproduk' => (float)$item['jumlah'],
                        ]);

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = (float)$item['qtyretur'];
                    $newKS->keterangan = 'Retur  ' .$jeniskirim. '  Dari Ruangan  '.$request['strukkirim']['ruangantujuan'] . '  Ke Ruangan  '.$request['strukkirim']['ruangan']. '  No Kirim:  ' . $dataSK->nokirim;
                    $newKS->produkfk = $item['produkfk'];
                    $newKS->ruanganfk = $request['strukkirim']['objectruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalPengirim + (float)$item['qtyretur'];
                    $newKS->status = 1;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk = $noTarimakeun;
                    $newKS->flagfk = 9;
                    $newKS->save();

                    if ($request['strukkirim']['jenispermintaanfk'] == 2) {
                        //PENERIMA
                        $dataSaldoAwalT = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                            array(
                                'ruanganfk' => $request['strukkirim']['objectruangantujuanfk'],
                                'produkfk' => $item['produkfk'],
                            )
                        );
                        $saldoAwalPenerima = 0;
                        foreach ($dataSaldoAwalT as $items) {
                            $saldoAwalPenerima = (float)$items->qty;
                        }

                        $kurang = StokProdukDetail::where('nostrukterimafk', $noTarimakeun)
                            ->where('kdprofile', $idProfile)
                            ->where('objectruanganfk', $request['strukkirim']['objectruangantujuanfk'])
                            ->where('objectprodukfk', $item['produkfk'])
                            ->first();
                        StokProdukDetail::where('norec', $kurang->norec)
                            ->where('kdprofile', $idProfile)
                            ->update([
                                'qtyproduk' => (float)$kurang->qtyproduk - (float)$item['qtyretur']
                            ]);
                        //## KartuStok
                        $newKS = new KartuStok();
                        $norecKS = $newKS->generateNewId();
                        $newKS->norec = $norecKS;
                        $newKS->kdprofile = $idProfile;
                        $newKS->statusenabled = true;
                        $newKS->jumlah = (float)$item['qtyretur'];
                        $newKS->keterangan = 'Retur  ' .$jeniskirim. '  Dari Ruangan  '.$request['strukkirim']['ruangantujuan'] . '  Ke Ruangan  '.$request['strukkirim']['ruangan']. '  No Kirim:  ' . $dataSK->nokirim;
                        $newKS->produkfk = $item['produkfk'];
                        $newKS->ruanganfk = $request['strukkirim']['objectruangantujuanfk'];
                        $newKS->saldoawal = (float)$saldoAwalPenerima - (float)$item['qtyretur'];
                        $newKS->status = 0;
                        $newKS->tglinput = date('Y-m-d H:i:s');
                        $newKS->tglkejadian = date('Y-m-d H:i:s');
                        $newKS->nostrukterimafk =$noTarimakeun;
                        $newKS->save();
                    }
                }
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Kirim';
            $logUser->noreff = $request['strukkirim']['noreckirim'];
            $logUser->referensi='norec Struk Kirim';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            $logUser->keterangan ='Retur ' .$jeniskirim. ' Dari Ruangan '.$request['strukkirim']['ruangantujuan'] . ' Ke Ruangan '.$request['strukkirim']['ruangan']. ' No Kirim: ' . $dataSK->nokirim;
            $logUser->save();

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Simpan Retur Barang";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "strukkirim" => $dataSK,
//                "kirimaset" => $dataKPA,
//                "dataRA" => $dataRA->objectruanganposisicurrentfk,
                'message' => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Retur Barang Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "strukkirim" => $dataSK,
//                "kirimaset" => $dataKPA,
//                 'dataRA' => $dataRA->objectruanganposisicurrentfk,
                'message' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarReturDistribusiBarang(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
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
        $data = \DB::table('strukretur_t as sr')
            ->JOIN ('strukkirim_t as sk','sk.norec','=','sr.strukkirimfk')
            ->LEFTJOIN ('strukreturdetail_t as srd','srd.strukreturfk','=','sr.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sk.objectpegawaipengirimfk')
            ->LEFTJOIN('pegawai_m as pg1','pg1.id','=','sr.objectpegawaifk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sk.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sk.objectruangantujuanfk')
            ->select(\DB::raw('sr.norec,sk.tglkirim,sr.tglretur,sk.nokirim,sr.noretur,ru.id as ruasalid,ru.namaruangan as ruanganasal, 
                             ru2.id as rutujuanid,ru2.namaruangan as ruangantujuan,sk.keteranganlainnyakirim,sr.keteranganalasan,pg.namalengkap,
                             COUNT(srd.objectprodukfk) as jmlitem,pg1.id as idpegwairetur,pg1.namalengkap as pegawairetur,sk.jenispermintaanfk')
            )
            ->where('mlu.kdprofile', $idProfile)
            ->groupBy('sr.norec','sk.tglkirim','sr.tglretur','sk.nokirim','sr.noretur','sr.keteranganalasan',
                'sk.jenispermintaanfk','pg.namalengkap','ru.id','ru.namaruangan','ru2.id','ru2.namaruangan',
                'sk.keteranganlainnyakirim','pg1.id','pg1.namalengkap','sk.jenispermintaanfk');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sr.tglretur','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sr.tglretur','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ILIKE','%'. $request['nokirim']);
        }
        if(isset($request['noretur']) && $request['noretur']!="" && $request['noretur']!="undefined"){
            $data = $data->where('sr.noretur','ILIKE','%'. $request['noretur']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru2.namaruangan','ILIKE', '%'.$request['ruangantujuanfk'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('srd.objectprodukfk','=', $request['produkfk']);
        }
        $data = $data->where('sr.statusenabled',true);
        $data = $data->where('sr.objectkelompoktransaksifk',50);
        $data = $data->wherein('sk.objectruanganasalfk',$strRuangan);
        $data = $data->where('sk.noregistrasifk','=',0);
        $data = $data->orderBy('sk.nokirim');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.objectprodukfk
                     from strukreturdetail_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=pr.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and spd.strukreturfk=:norec"),
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
                'tglretur' => $item->tglretur,
                'tglstruk' => $item->tglkirim,
                'noretur' => $item->noretur,
                'nostruk' => $item->nokirim,
//                'noorderfk' => $item->noorderfk,
                'jenispermintaanfk' => $item->jenispermintaanfk,
                'jeniskirim' => $jeniskirim,
                'norec' => $item->norec,
                'ruasalid'=> $item->ruasalid,
                'namaruanganasal' => $item->ruanganasal,
                'rutujuanid'=> $item->rutujuanid,
                'namaruangantujuan' => $item->ruangantujuan,
                'petugas' => $item->namalengkap,
                'petugasretur' => $item->pegawairetur,
                'keterangan' => $item->keteranganlainnyakirim,
                'keteranganretur' => $item->keteranganalasan,
                'jmlitem' => $item->jmlitem,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
            'str' => $strRuangan,
        );

        return $this->respond($result);
    }

    public function getDaftarReturPenerimaanSuplier(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
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
        $data = \DB::table('strukretur_t as sr')
            ->JOIN('strukpelayanan_t as sp','sp.norec','=','sr.strukterimafk')
            ->LEFTJOIN('strukreturdetail_t as srd','srd.strukreturfk','=','sr.norec')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipenerimafk')
            ->LEFTJOIN('pegawai_m as pg1','pg1.id','=','sr.objectpegawaifk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk','sbk.norec','=','sp.nosbklastfk')

            ->select(\DB::raw('sr.norec,sr.tglretur,sr.noretur,sp.nostruk,sp.nosppb,sp.nokontrak,sp.nofaktur,sp.tglfaktur,
                                    sp.objectruanganfk,ru.namaruangan,rkn.namarekanan,pg.namalengkap,pg1.namalengkap as pegawairetur,
                                    COUNT(srd.objectprodukfk) as jmlitem'))
            ->where('sr.kdprofile', $idProfile)
            ->groupBy('sr.norec','sr.tglretur','sr.noretur','sp.nostruk','sp.nosppb','sp.nokontrak','sp.nofaktur','sp.tglfaktur',
                'rkn.namarekanan','pg.namalengkap','pg1.namalengkap','sp.objectruanganfk','ru.namaruangan');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sr.tglretur','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sr.tglretur','<=', $tgl);
        }
        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('sp.nostruk','ILIKE','%'. $request['nostruk']);
        }
        if(isset($request['namarekanan']) && $request['namarekanan']!="" && $request['namarekanan']!="undefined"){
            $data = $data->where('rkn.namarekanan','ILIKE','%'. $request['namarekanan'].'%');
        }
        if(isset($request['nofaktur']) && $request['nofaktur']!="" && $request['nofaktur']!="undefined"){
            $data = $data->where('sp.nofaktur','ILIKE','%'. $request['nofaktur'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('srd.objectprodukfk','=',$request['produkfk']);
        }
        if(isset($request['noSppb']) && $request['noSppb']!="" && $request['noSppb']!="undefined"){
            $data = $data->where('sp.nosppb','ILIKE','%'. $request['noSppb'].'%');
        }
        if(isset($request['noretur']) && $request['noretur']!="" && $request['noretur']!="undefined"){
            $data = $data->where('sr.noretur','ILIKE','%'. $request['noretur'].'%');
        }

        $data = $data->wherein('sp.objectruanganfk',$strRuangan);
        $data = $data->where('sr.statusenabled',true);
        $data = $data->where('sr.objectkelompoktransaksifk',50);
        $data = $data->orderBy('sr.noretur');
        $data = $data->get();

        foreach ($data as $item){
            $details = DB::select(DB::raw("
                    select  pr.namaproduk,ss.satuanstandar,spd.qtyproduk,spd.harganetto1,spd.hargadiscount,
							((spd.harganetto1-spd.hargadiscount)*spd.qtyproduk) as total,spd.tglkadaluarsa,spd.nobatch
                    from strukreturdetail_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=pr.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and spd.strukreturfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'tglretur' => $item->tglretur,
                'tglfaktur' => $item->tglfaktur,
                'nostruk' => $item->nostruk,
                'nofaktur' => $item->nofaktur,
                'noretur' => $item->noretur,
                'namarekanan' => $item->namarekanan,
                'norec' => $item->norec,
                'nosppb' => $item->nosppb,
                'namaruangan' => $item->namaruangan,
                'namapenerima' => $item->namalengkap,
                'nokontrak' => $item->nokontrak,
                'jmlitem' => $item->jmlitem,
                'details' => $details,
            );
        }
        if (count($data) == 0){
            $result=[];
        }

        $result = array(
            'daftar' => $result,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getNoFaktur(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukpelayanan_t as so')
            ->where('kdprofile', $idProfile)
            ->select('so.nofaktur');

        if (isset($request['NoSPK']) && $request['NoSPK'] != "" && $request['NoSPK'] != "undefined") {
            $data = $data->where('so.nofaktur', $request['NoSPK']);
        }

        $data = $data->where('so.statusenabled', true);
        $data = $data->get();
        return $this->respond($data);
    }

    public function getNoTerima(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukpelayanan_t as so')
            ->where('kdprofile', $idProfile)
            ->select('so.nostruk');

        if (isset($request['NoSPK']) && $request['NoSPK'] != "" && $request['NoSPK'] != "undefined") {
            $data = $data->where('so.nostruk', $request['NoSPK']);
        }

        $data = $data->where('so.statusenabled', true);
        $data = $data->get();
        return $this->respond($data);
    }

    public function getDaftarProdukToReturPenerimaan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $i = 0;
        $str = explode(',', $request['objectprodukfk']);
        for ($i = 0; $i < count($str); $i++) {
            $arr = (int)$str[$i];
            $str[$i] = $arr;
        }
        $produkfk = implode(',', $str);
        $idProduk = ' ';
        if (isset($request['objectprodukfk']) && $request['objectprodukfk'] != "" && $request['objectprodukfk'] != "undefined") {
            $idProduk = '  and  pr.id in (' . $produkfk . ')';
        }
        $details = \DB::select(DB::raw("
                  select  pr.id as kdeproduk,pr.namaproduk, ss.satuanstandar, sum(spd.qtyproduk) as qtyproduk,sp.nostrukfk,
                  spd.tglpelayanan
                  from strukpelayanandetail_t as sp 
                  left join strukpelayanan_t as spl on spl.norec = sp.nostrukfk
                  left join produk_m as pr on pr.id=sp.objectprodukfk
                  left join stokprodukdetail_t as spd on spd.nostrukterimafk = sp.nostrukfk  and spd.objectprodukfk=sp.objectprodukfk
                  left join satuanstandar_m as ss on ss.id=sp.objectsatuanstandarfk
                  where sp.kdprofile = $idProfile and sp.nostrukfk=:norec and sp.objectruanganfk=:ruanganfk and spd.objectruanganfk=:ruanganfk
                        $idProduk
                  group by pr.id ,pr.namaproduk, ss.satuanstandar,sp.nostrukfk,
                  spd.tglpelayanan;"),
            array(
                'norec' => $request['noTerima'],
                'ruanganfk' => $request['ruanganfk'],
            )
        );
        return $this->respond($details);
    }

    public function SaveReturPenerimaan (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        try {

            if($request['struk']['norecRetur'] == ''){
                $newSRetur = new StrukRetur();
                $norecSRetur = $newSRetur->generateNewId();
                $noRetur = $this->generateCode(new StrukRetur, 'noretur', 12, 'Ret/' . $this->getDateTime()->format('ym') . '/', $idProfile);
                $newSRetur->norec = $norecSRetur;
                $newSRetur->kdprofile = $idProfile;
                $newSRetur->statusenabled = true;
                $newSRetur->objectkelompoktransaksifk = 50;
            }else{
                $newSRetur =  StrukRetur::where('norec',$request['struk']['norecRetur'])->where('kdprofile', $idProfile)->first();
                StrukReturDetail::where('strukreturfk',$request['struk']['norecRetur'])->where('kdprofile', $idProfile)->delete();
            }
            $newSRetur->keteranganalasan =$request['struk']['keterangan'];
            $newSRetur->keteranganlainnya = 'Retur  Penerimaan '.' Dari Ruangan  '. $request['struk']['namaruangan']  .' Dengan No Terima:  '.$request['struk']['noterima'] . '  Ke Supplier  '.$request['struk']['namarekanan'];
            $newSRetur->noretur = $noRetur;
            $newSRetur->objectruanganfk = $request['struk']['ruanganfk'];
            $newSRetur->objectpegawaifk = $request['struk']['pegawaimenerimafk'];
            $newSRetur->tglretur = $this->getDateTime()->format('Y-m-d H:i:s');
            $newSRetur->strukterimafk = $request['struk']['nostruk'];
            $newSRetur->save();
            $norecRetur = $newSRetur->norec;

            foreach ($request['details'] as $item) {
                if ((float)$item['qtyprodukretur'] != 0){
                    $noTarimakeun =  $request['struk']['nostruk'];

                    $tambah = StokProdukDetail::where('nostrukterimafk', $noTarimakeun)
                        ->where('kdprofile', $idProfile)
                        ->where('objectruanganfk', $request['struk']['ruanganfk'])
                        ->where('objectprodukfk', $item['produkfk'])
                        ->first();

                    //## StrukReturDetail
                    $StokPD = new StrukReturDetail();
                    $norecStokPD = $StokPD->generateNewId();
                    $StokPD->norec = $norecStokPD;
                    $StokPD->kdprofile = $idProfile;
                    $StokPD->statusenabled = true;
                    $StokPD->objectasalprodukfk = $item['asalprodukfk'];
                    $StokPD->hargadiscount = 0;
                    $StokPD->harganetto1 = (float)$tambah->harganetto1;
                    $StokPD->harganetto2 = (float)$tambah->harganetto2;
                    $StokPD->persendiscount = 0;
                    $StokPD->objectprodukfk = $item['produkfk'];
                    $StokPD->qtyproduk = (float)$item['qtyprodukretur'];
                    $StokPD->qtyprodukonhand = 0;
                    $StokPD->qtyprodukoutext = 0;
                    $StokPD->qtyprodukoutint = 0;
                    $StokPD->nostrukterimafk =$noTarimakeun;
                    $StokPD->strukreturfk =$norecRetur;
                    $StokPD->tglkadaluarsa = $tambah->tglkadaluarsa;
                    $StokPD->save();

                    //PENERIMA
                    $dataSaldoAwalT = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                        array(
                            'ruanganfk' => $request['struk']['ruanganfk'],
                            'produkfk' => $item['produkfk'],
                        )
                    );

                    $saldoAwalPenerima = 0;
                    foreach ($dataSaldoAwalT as $items) {
                        $saldoAwalPenerima = (float)$items->qty;
                    }

                    $kurang = StokProdukDetail::where('nostrukterimafk', $noTarimakeun)
                        ->where('kdprofile', $idProfile)
                        ->where('objectruanganfk', $request['struk']['ruanganfk'])
                        ->where('objectprodukfk', $item['produkfk'])
                        ->first();

                    StokProdukDetail::where('norec', $kurang->norec)
                        ->where('kdprofile', $idProfile)
                        ->update([
                            'qtyproduk' => (float)$kurang->qtyproduk - (float)$item['qtyprodukretur']
                        ]);

                    StrukPelayananDetail::where('nostrukfk',$request['struk']['nostruk'])
                        ->where('kdprofile', $idProfile)
                        ->where('objectprodukfk',$item['produkfk'])
                        ->update([
                            'qtyproduk' => (float)$kurang->qtyproduk - (float)$item['qtyprodukretur'],
                            'qtyprodukretur' => (float)$item['qtyprodukretur']
                        ]);

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = (float)$item['qtyprodukretur'];
                    $newKS->keterangan = 'Retur  Penerimaan '.' Dari Ruangan  '. $request['struk']['namaruangan']  .' Dengan No Terima:  '.$request['struk']['noterima'] . '  Ke Supplier  '.$request['struk']['namarekanan'];
                    $newKS->produkfk = $item['produkfk'];
                    $newKS->ruanganfk = $request['struk']['ruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalPenerima - (float)$item['qtyprodukretur'];
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =$noTarimakeun;
                    $newKS->flagfk = 10;
                    $newKS->save();
                }
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Kirim';
            $logUser->noreff = $request['struk']['nostruk'];
            $logUser->referensi='norec Struk Terima';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            $logUser->keterangan ='Retur  Penerimaan '.' Dari Ruangan  '. $request['struk']['namaruangan']  .' Dengan No Terima:  '.$request['struk']['noterima'] . '  Ke Supplier  '.$request['struk']['namarekanan'];
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
                "noretur" => $norecRetur,
                "data" => $newSRetur,
                "datalogin" => $dataLogin['userData'],
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $newSRetur,
                "datalogin" => $dataLogin['userData'],
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarStockFlowDetailRev1(Request $request) {
//        ini_set('max_execution_time', 1000); //6 minutes
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglawal = $request['tglawal'];
        $tglakhir = $request['tglakhir'];
        $ruanganfk=$request['ruanganfk'];
        $jenisprodukfk ='';
        $detailjenisprodukfk = '';
        $kdSirs1 = '';
        $kdSirs2 = '';
        $IdProduk='';
        if(isset( $request['jenisprodukfk'])&&  $request['jenisprodukfk']!=''){
            $jenisprodukfk = "and djp.objectjenisprodukfk in (".$request['jenisprodukfk'].")";
            $jenisprodukfkSaldoAwal = "and objectjenisprodukfk in (".$request['jenisprodukfk'].")";
        }
        if(isset( $request['IdProduk'])&&  $request['IdProduk']!=''){
            $IdProduk = 'and pr.id  = '. $request['IdProduk'];
        }
        if(isset( $request['detailjenisprodukfk'])&&  $request['detailjenisprodukfk']!=''){
            $detailjenisprodukfk = "and djp.id in (".$request['detailjenisprodukfk'].")";
            $detailjenisprodukfkSaldoAwal = "and objectdetailjenisprodukfk in (".$request['detailjenisprodukfk'].")";
        }
        if(isset( $request['KdSirs1']) &&  $request['KdSirs1']!=''){
            if(isset( $request['KdSirs2'])&&  $request['KdSirs2']!=''){
                $kdSirs1 = " and (pr.kdproduk BETWEEN '".$request['KdSirs1']."' and '".$request['KdSirs2']."')";
            }else{
                $kdSirs1 = " and pr.kdproduk ILIKE '".$request['KdSirs1']."%'";
//                $kdSirs2 = " and '%'";
            }
        }

        if(isset( $request['KdSirs2'])&&  $request['KdSirs2']!=''){
            if(isset( $request['KdSirs1'])&&  $request['KdSirs1']!=''){
                $kdSirs2 = " and (pr.kdproduk BETWEEN '".$request['KdSirs1']."' and '".$request['KdSirs2']."')";
                $kdSirs1 ='';
            }else{
                $kdSirs2 = " and pr.kdproduk ILIKE '".$request['KdSirs2']."%'";
//                $kdSirs1 = " and '%'";
            }
        }
        $result=[];
        $hrga = 1000;

        $mydate = $tglawal;
        $daystosum = '1';

        $datesum = date('d-m-Y', strtotime($mydate.' - '.$daystosum.' months'));
        $datadata = date('Ym', strtotime($datesum));
        $strDataTglSaldoawal = (string)$datadata;

        $checkSaldoAwal = \DB::select(DB::raw("
          select * from persediaansaldoawal_t where kdprofile = $idProfile and ym='$strDataTglSaldoawal' and objectruanganfk= $ruanganfk " )
        );

        if (count($checkSaldoAwal) == 0){
            $data = DB::select(DB::raw("
                select x.ruanganfk,x.produkfk, x.kodeexternal,x.namaproduk,x.satuanstandar,x.harga,
                sum(x.penerimaan) as terima,sum(x.pengeluaran) as keluar ,
                x.ruid,x.namaruangan,x.id,x.kdproduk,x.objectdetailjenisprodukfk
                from
                (select ks.ruanganfk,ks.produkfk, pr.kodeexternal, pr.namaproduk, ss.satuanstandar,$hrga as harga,
                case when ks.status = 1 then ks.jumlah else 0 end as penerimaan,
                case when ks.status =0 then ks.jumlah else 0 end as pengeluaran,
                ru.id as ruid, ru.namaruangan,
                pr.id,pr.kdproduk,djp.id as objectdetailjenisprodukfk
                 from kartustok_t as ks
                INNER JOIN produk_m as pr on pr.id=ks.produkfk
                LEFT JOIN detailjenisproduk_m as djp on pr.objectdetailjenisprodukfk=djp.id
                INNER JOIN satuanstandar_m as ss on ss.id=pr.objectsatuanstandarfk
                INNER JOIN ruangan_m as ru on ru.id=ks.ruanganfk
                where ks.tglinput BETWEEN '$tglawal' and '$tglakhir'
                and ks.kdprofile = $idProfile and ks.ruanganfk=$ruanganfk
                $jenisprodukfk
                $detailjenisprodukfk
                $IdProduk
                $kdSirs1
                $kdSirs2
                and pr.statusenabled =1 and ks.jumlah >=0 --and pr.id in (18876,1002116929)
                ) as x
                group by x.ruanganfk,x.produkfk,x.kodeexternal,x.namaproduk,x.satuanstandar,x.harga,
                x.ruid,x.namaruangan,x.id,x.kdproduk,x.objectdetailjenisprodukfk
                order by x.namaproduk;")
            );

            $dataPengurangUbahTransaksiTea = DB::select(DB::raw("
                select x.produkfk,
                sum(x.penerimaan) as terima,sum(x.pengeluaran) as keluar 
                from
                (select ks.produkfk,
                case when ks.status =1 then ks.jumlah else 0 end as penerimaan,
                case when ks.status =0 then ks.jumlah else 0 end as pengeluaran
                from kartustok_t as ks
                where ks.kdprofile = $idProfile and ks.tglinput BETWEEN '$tglawal' and '$tglakhir'
                and ks.ruanganfk=$ruanganfk
                and  ks.jumlah >0 and 
                (ks.keterangan ILIKE 'Hapus Penerimaan%' 
                or ks.keterangan ILIKE 'Ubah Penerimaan%' 
                or ks.keterangan ILIKE 'Batal Penerimaa%' 
                or ks.keterangan ILIKE 'Hapus Resep Oba%' 
                or ks.keterangan ILIKE 'Hapus Resep Pel%' 
                or ks.keterangan ILIKE 'Ubah Resep Obat%' 
                or ks.keterangan ILIKE 'Ubah Resep No%'
                or ks.keterangan ILIKE 'Batal Terima Barang%'
                or ks.keterangan ILIKE 'Ubah Terima Bar%'
                or ks.keterangan ILIKE 'Batal Kirim Bar%'
                or ks.keterangan ILIKE 'Ubah Kirim Bara%')
                ) as x group by x.produkfk")
            );
            $tglnow = date('Y-m-d H:i:s');
            $dataElseStok = DB::select(DB::raw("
                select spd.objectruanganfk as  ruanganfk,spd.objectprodukfk as  produkfk, 
                    pr.kodeexternal,pr.namaproduk,ss.satuanstandar,1000 as harga,
                    sum(spd.qtyproduk) as qtystok,
                    case when y.terima is null then 0 else y.terima end as terima,
                    case when y.keluar is null then 0 else y.keluar end as keluar,
                    pr.kdproduk,ru.namaruangan,pr.objectdetailjenisprodukfk
                    from stokprodukdetail_t as spd
                    left JOIN 
                    (select ks.ruanganfk,ks.produkfk,
                    sum(case when ks.status =1 then ks.jumlah else 0 end) as terima,
                    sum(case when ks.status =0 then ks.jumlah else 0 end) as keluar
                    from kartustok_t as ks
                    INNER JOIN produk_m as pr on pr.id=ks.produkfk
                    where ks.kdprofile = $idProfile and  ks.tglinput BETWEEN '$tglakhir' and '$tglnow'
                    and ks.ruanganfk=$ruanganfk
                    and pr.statusenabled=1 
                    group by ks.ruanganfk,ks.produkfk
                    ) as y on y.produkfk=spd.objectprodukfk and y.ruanganfk=spd.objectruanganfk
                    INNER JOIN produk_m as pr on pr.id=spd.objectprodukfk
                     LEFT JOIN detailjenisproduk_m as djp on pr.objectdetailjenisprodukfk=djp.id
                    INNER JOIN satuanstandar_m as ss on ss.id=pr.objectsatuanstandarfk
                    INNER JOIN ruangan_m as ru on ru.id=spd.objectruanganfk
                    where spd.kdprofile = $idProfile and spd.objectruanganfk=$ruanganfk and spd.qtyproduk>0 and pr.statusenabled= 1 
                    $jenisprodukfk
                    $detailjenisprodukfk
                    $IdProduk
                    $kdSirs1
                    $kdSirs2
                    group by ru.objectruanganfk ,spd.objectprodukfk , 
                    pr.kodeexternal,pr.namaproduk,ss.satuanstandar,
                    pr.namaproduk,spd.objectprodukfk ,spd.objectruanganfk,y.produkfk,y.ruanganfk,y.terima,y.keluar ,
                    pr.kdproduk,ru.namaruangan,pr.objectdetailjenisprodukfk
                    order by pr.namaproduk;")
            );

            $dataHargaAkhir = DB::select(DB::raw("select * from (
                    select sp.tglstruk,spd.objectprodukfk,spd.harganetto1 as harganetto, row_number() over (partition by objectprodukfk order by tglstruk desc) as rownum
                    from strukpelayanan_t as sp
                    INNER JOIN stokprodukdetail_t as spd on spd.nostrukterimafk=sp.norec
                    where sp.kdprofile = $idProfile and  sp.tglstruk BETWEEN '2016-01-01 00:00' and '$tglakhir' and sp.statusenabled=1 and sp.objectkelompoktransaksifk=35
                    ) tmp where rownum =1 order by objectprodukfk;;
                ")
            );
            $dataAwal = DB::select(DB::raw("select * from 
                (select ks.produkfk,
                case when ks.status =0 then ks.saldoawal+ks.jumlah else ks.saldoawal-ks.jumlah end as saldoawal,ks.tglinput, row_number() over (partition by ks.produkfk order by ks.tglinput) as rownum 
                from kartustok_t as ks
                where ks.kdprofile = $idProfile and  ks.tglinput BETWEEN '$tglawal' and '$tglakhir'
                and ks.ruanganfk=$ruanganfk ) as x where rownum=1;
                    ")
            );
//            $dataAkhir = DB::select(DB::raw("select * from
//                (select ks.produkfk,
//                ks.saldoawal as saldoakhir,ks.tglinput, row_number() over (partition by ks.produkfk order by ks.tglinput desc) as rownum
//                from kartustok_t as ks
//                where ks.tglinput BETWEEN '$tglawal' and '$tglakhir'
//                and ks.ruanganfk=$ruanganfk ) as x where rownum=1;
//                    ")
//            );
            $dataAkhir2 = DB::select(DB::raw("select ks.produkfk,
                ks.saldoawal as saldoakhir,ks.tglinput,cast( row_number() over (partition by ks.produkfk order by ks.tglinput asc) as int) as rownum
                from kartustok_t as ks
                where ks.kdprofile = $idProfile and ks.tglinput BETWEEN '$tglawal' and '$tglakhir'
                and ks.ruanganfk=$ruanganfk ")
            );
            $sm = false;
            $dataAkhir =[];

            foreach ($dataAkhir2 as $ddt){
                $sm = false;
                $i=0;
                foreach ($dataAkhir as $rss){
                    if ($dataAkhir[$i]['produkfk'] == $ddt->produkfk){
                        $sm = true;
                        if ((int)$ddt->rownum > (int)$dataAkhir[$i]['rownum']){
                            $dataAkhir[$i]['saldoakhir'] = $ddt->saldoakhir;
                            $dataAkhir[$i]['rownum'] = $ddt->rownum;
                            break;
                        }
                    }
                    $i = $i +1;
                }

                if($sm == false ){
                    $dataAkhir[] = array(
                        'produkfk' => $ddt->produkfk,
                        'saldoakhir' => $ddt->saldoakhir,
                        'tglinput' => $ddt->tglinput,
                        'rownum' => $ddt->rownum,
                    );
                }

            }


            foreach ($data as $item){
                $jmlAsup=0;
                $idproduk = $item->produkfk;
                //############AWAL

                $jmlAwal =0;
                foreach ($dataAwal as $xx){
                    if ($xx->produkfk == $idproduk){
                        $jmlAwal = $xx->saldoawal;
                    }
                }

                //############AKHIR

                $jmlAkhir =0;
                foreach ($dataAkhir as $yy){
                    if ($yy['produkfk'] == $idproduk){
                        $jmlAkhir = $yy['saldoakhir'];
                    }
                }


                $hrgAkhir =0;
                foreach ($dataHargaAkhir as $xxx){
                    if ($xxx->objectprodukfk == $idproduk){
                        $hrgAkhir = $xxx->harganetto;
                    }
                }

                $jmlAsup = $item->terima;
                $jmlKaluar = $item->keluar;
                //##################MUTASI2 edit transaksi
                foreach ($dataPengurangUbahTransaksiTea as $xxxxxxx){
                    if ($xxxxxxx->produkfk == $idproduk){
                        $jmlAsup = $jmlAsup - ($xxxxxxx->terima + $xxxxxxx->keluar);
                        $jmlKaluar = $jmlKaluar - ($xxxxxxx->terima + $xxxxxxx->keluar);
                    }
                }


//                if ($jmlKaluar > 0  or $jmlAsup > 0){
//                    $jmlAwal = (float)$item->qtyakhir + (float)$jmlKaluar - (float)$jmlAsup;
                $result[] = array(
//                        'nostrukterimafk' =>   $item->nostrukterimafk,
                    'namaproduk' => $item->namaproduk,
                    'satuanstandar' => $item->satuanstandar,
//                        'asalproduk' => $item->asalproduk,
                    'objectruanganfk' =>   $item->ruanganfk,
                    'namaruangan' => $item->namaruangan,
                    'objectprodukfk' =>   $item->produkfk,
                    'kodeproduk' => $item->kdproduk,
                    'kodebmn' => $item->id,
                    'harga' => $hrgAkhir,
                    'qtyawal' =>   $jmlAwal,
                    'ttlawal' =>   $jmlAwal*$hrgAkhir,
                    'qtymasuk' =>   $jmlAsup,
                    'ttlmasuk' =>   $jmlAsup*$hrgAkhir,
                    'qtykeluar' =>   $jmlKaluar,
                    'ttlkeluar' =>   $jmlKaluar*$hrgAkhir,
                    'qtyakhir' =>   $jmlAkhir,
                    'ttlakhir' =>   $jmlAkhir*$hrgAkhir,
                    'objectdetailjenisprodukfk' => $item->objectdetailjenisprodukfk,
//                        'tglstruk' =>$item->tglstruk,
//                        'tglkadaluarsa' =>$item->tglkadaluarsa,
                );
//                }
            }
            $tosAya = false;
            $tandaaja = '';
            foreach ($dataElseStok as $arem){
                $tosAya = false;
                foreach ($result as $itm){
                    if ($arem->produkfk == $itm['objectprodukfk']){
                        $tosAya = true;
                    }

                }
                $hrgAkhir2 =0;
                foreach ($dataHargaAkhir as $xxx){
                    if ($xxx->objectprodukfk == $arem->produkfk){
                        $hrgAkhir2 = $xxx->harganetto;
                    }
                }
                if ($tosAya == false){
                    $jmlAwal = ((float)$arem->qtystok- (float)$arem->terima) + (float)$arem->keluar;
                    $tandaaja = '*';
                    if ($jmlAwal <> (float)$arem->qtystok){
                        $tandaaja = '**';
                    }
                    if ($jmlAwal > 0){
                        $result[] = array(
                            'namaproduk' => $arem->namaproduk . $tandaaja,
                            'satuanstandar' => $arem->satuanstandar,
                            'objectruanganfk' =>   $arem->ruanganfk,
                            'namaruangan' => $arem->namaruangan,
                            'objectprodukfk' =>   $arem->produkfk,
                            'kodeproduk' => $arem->kdproduk,
                            'kodebmn' => $arem->produkfk,
                            'harga' => $hrgAkhir2,
                            'qtyawal' =>   $jmlAwal,
                            'ttlawal' =>   $jmlAwal*$hrgAkhir2,
                            'qtymasuk' =>   0,
                            'ttlmasuk' =>   0*$hrgAkhir2,
                            'qtykeluar' =>   0,
                            'ttlkeluar' =>   0*$hrgAkhir2,
                            'qtyakhir' =>   $jmlAwal,
                            'ttlakhir' =>   $jmlAwal*$hrgAkhir2,
                            'objectdetailjenisprodukfk' => $arem->objectdetailjenisprodukfk,
                        );
                    }
                }
            }
            foreach ($result as $key => $row) {
                $count[$key] = $row['namaproduk'];
            }
            if(count($result)>0){
                array_multisort($count, SORT_ASC, $result);
            }
        }else{
            $data = DB::select(DB::raw("
                select x.produkfk,
                sum(x.penerimaan) as terima,sum(x.pengeluaran) as keluar 
                from
                (select ks.produkfk,
                case when ks.status =1 then ks.jumlah else 0 end as penerimaan,
                case when ks.status =0 then ks.jumlah else 0 end as pengeluaran
                from kartustok_t as ks
                where ks.kdprofile = $idProfile and ks.tglinput BETWEEN '$tglawal' and '$tglakhir'
                and ks.ruanganfk=$ruanganfk
                and  ks.jumlah >0
                ) as x group by x.produkfk")
            );
            $dataPengurangUbahTransaksiTea = DB::select(DB::raw("
                select x.produkfk,
                sum(x.penerimaan) as terima,sum(x.pengeluaran) as keluar 
                from
                (select ks.produkfk,
                case when ks.status =1 then ks.jumlah else 0 end as penerimaan,
                case when ks.status =0 then ks.jumlah else 0 end as pengeluaran
                from kartustok_t as ks
                where ks.kdprofile = $idProfile and ks.tglinput BETWEEN '$tglawal' and '$tglakhir'
                and ks.ruanganfk=$ruanganfk
                and  ks.jumlah >0 and 
                (ks.keterangan ILIKE 'Hapus Penerimaan%' 
                or ks.keterangan ILIKE 'Ubah Penerimaan%' 
                or ks.keterangan ILIKE 'Batal Penerimaa%' 
                or ks.keterangan ILIKE 'Hapus Resep Oba%' 
                or ks.keterangan ILIKE 'Hapus Resep Pel%' 
                or ks.keterangan ILIKE 'Ubah Resep Obat%' 
                or ks.keterangan ILIKE 'Ubah Resep No%'
                or ks.keterangan ILIKE 'Batal Terima Barang%'
                or ks.keterangan ILIKE 'Ubah Terima Bar%'
                or ks.keterangan ILIKE 'Batal Kirim Bar%'
                or ks.keterangan ILIKE 'Ubah Kirim Bara%')
                ) as x group by x.produkfk")
            );
            $tglnow = date('Y-m-d H:i:s');
            $dataHargaAkhir = DB::select(DB::raw("select * from (
                    select sp.tglstruk,spd.objectprodukfk,spd.harganetto1 as harganetto, row_number() over (partition by objectprodukfk order by tglstruk desc) as rownum
                    from strukpelayanan_t as sp
                    INNER JOIN stokprodukdetail_t as spd on spd.nostrukterimafk=sp.norec
                    where sp.kdprofile = $idProfile and  sp.tglstruk BETWEEN '2016-01-01 00:00' and '$tglakhir' and sp.statusenabled=1 and sp.objectkelompoktransaksifk=35
                    ) tmp where rownum =1 order by objectprodukfk;;
                ")
            );
            //TODO : Saldo awal persediaan
            $dataAwal = DB::select(DB::raw("select psa.*, pr.kodeexternal, pr.namaproduk, ss.satuanstandar ,ru.id as ruid, ru.namaruangan,
                pr.id,pr.kdproduk,pr.objectdetailjenisprodukfk
                from persediaansaldoawal_t as psa
                INNER JOIN produk_m as pr on pr.id=psa.objectprodukfk
                LEFT JOIN detailjenisproduk_m as djp on pr.objectdetailjenisprodukfk=djp.id
                INNER JOIN satuanstandar_m as ss on ss.id=pr.objectsatuanstandarfk
                INNER JOIN ruangan_m as ru on ru.id=psa.objectruanganfk
                where psa.kdprofile = $idProfile and  psa.ym='$strDataTglSaldoawal' and psa.objectruanganfk = $ruanganfk
                $jenisprodukfk
                $detailjenisprodukfk;
                    ")
            );

            $dataAkhir = DB::select(DB::raw("select * from 
                (select ks.produkfk,
                ks.saldoawal as saldoakhir,ks.tglinput, row_number() over (partition by ks.produkfk order by ks.tglinput desc) as rownum 
                from kartustok_t as ks
                where  ks.kdprofile = $idProfile and ks.tglinput BETWEEN '$tglawal' and '$tglakhir'
                and ks.ruanganfk=$ruanganfk ) as x where rownum=1;
                    ")
            );


            $jmlAwal =0;
            $ttlAwal =0;
            foreach ($dataAwal as $item){
                $jmlAsup=0;
                $idproduk = $item->objectprodukfk;
                //############AWAL
                $jmlAwal = $item->qty;
                $ttlAwal = $item->total;



                //##################MUTASI2
                $jmlAsup = 0;
                $jmlKaluar = 0;
                foreach ($data as $xx){
                    if ($xx->produkfk == $idproduk){
                        $jmlAsup = $xx->terima;
                        $jmlKaluar = $xx->keluar;
                    }
                }

                //##################MUTASI2 edit transaksi
                foreach ($dataPengurangUbahTransaksiTea as $xxxxxxx){
                    if ($xxxxxxx->produkfk == $idproduk){
                        $jmlAsup = $jmlAsup - ($xxxxxxx->terima + $xxxxxxx->keluar);
                        $jmlKaluar = $jmlKaluar - ($xxxxxxx->terima + $xxxxxxx->keluar);
                    }
                }
                //############AKHIR

                $jmlAkhir =0;
                foreach ($dataAkhir as $yy){
                    if ($yy->produkfk == $idproduk){
                        $jmlAkhir = $yy->saldoakhir;
                    }
                }


                $hrgAkhir =0;
                foreach ($dataHargaAkhir as $xxx){
                    if ($xxx->objectprodukfk == $idproduk){
                        $hrgAkhir = $xxx->harganetto;
                    }
                }


//                if ($jmlKaluar > 0  or $jmlAsup > 0){
//                    $jmlAwal = (float)$item->qtyakhir + (float)$jmlKaluar - (float)$jmlAsup;
                $result[] = array(
//                        'nostrukterimafk' =>   $item->nostrukterimafk,
                    'namaproduk' => $item->namaproduk,
                    'satuanstandar' => $item->satuanstandar,
//                        'asalproduk' => $item->asalproduk,
                    'objectruanganfk' =>   $item->objectruanganfk,
                    'namaruangan' => $item->namaruangan,
                    'objectprodukfk' =>   $item->objectprodukfk,
                    'kodeproduk' => $item->kdproduk,
                    'kodebmn' => $item->id,
                    'harga' => $hrgAkhir,
                    'qtyawal' =>   $jmlAwal,//$jmlAwal,
                    'ttlawal' =>   $ttlAwal,//$jmlAwal*$hrgAkhir,
                    'qtymasuk' =>   $jmlAsup,
                    'ttlmasuk' =>   $jmlAsup*$hrgAkhir,
                    'qtykeluar' =>   $jmlKaluar,
                    'ttlkeluar' =>   $jmlKaluar*$hrgAkhir,
                    'qtyakhir' =>   ($jmlAwal+$jmlAsup)-$jmlKaluar,
                    'ttlakhir' =>   (($jmlAwal+$jmlAsup)-$jmlKaluar)*$hrgAkhir,
                    'objectdetailjenisprodukfk' => $item->objectdetailjenisprodukfk,
//                        'tglstruk' =>$item->tglstruk,
//                        'tglkadaluarsa' =>$item->tglkadaluarsa,
                );
//                }
            }
            $tosAya = false;
            $tandaaja = '';
            foreach ($result as $key => $row) {
                $count[$key] = $row['namaproduk'];
            }
            if(count($result)>0){
                array_multisort($count, SORT_ASC, $result);
            }
        }
        $asepic = array(
            'data' =>  $result,
//            'dtakhir' => $dataAkhir,
//            'datatransaksibatal' => $dataPengurangUbahTransaksiTea,
        );
        return $this->respond($asepic);

        if($ruanganfk != null){

        }else{
        }
    }

    public function SaveClosingPersediaan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        ini_set('max_execution_time', 200);
        \DB::beginTransaction();
        $dataReq = $request->all();
        $data = $dataReq['data'];

        try {
            $sama = false;
            $data2 = [];
            foreach ($data as $item) {
                $sama = false;
                foreach ($data2 as $item2){
                    if ($item['objectruanganfk'] == $item2['objectruanganfk']){
                        if($item['objectdetailjenisprodukfk'] == $item2['objectdetailjenisprodukfk']){
                            $sama = true;
                        }
                    }
                }
                if ($sama == false){
                    $data2[] = array(
                        'objectruanganfk' => $item['objectruanganfk'],
                        'objectdetailjenisprodukfk' => $item['objectdetailjenisprodukfk'],
                    );
                }
            }
            foreach ($data2 as $item){
                $postingSA = PersediaanSaldoAwal::where('ym', $dataReq['ym'])
                    ->where('objectruanganfk', $item['objectruanganfk'])
                    ->where('objectdetailjenisprodukfk', $item['objectdetailjenisprodukfk'])
                    ->delete();
            }

            foreach ($data as $item){
                $postingSA = new PersediaanSaldoAwal();
                $norecHead = $postingSA->generateNewId();
                $postingSA->norec = $norecHead;
                $postingSA->kdprofile = $idProfile;
                $postingSA->statusenabled = 1;

                $postingSA->objectruanganfk = $item['objectruanganfk'];
                $postingSA->objectprodukfk = $item['objectprodukfk'];
                $postingSA->objectdetailjenisprodukfk = $item['objectdetailjenisprodukfk'];
                $postingSA->qty = $item['qtyakhir'];
                $postingSA->total = $item['ttlakhir'];
                $postingSA->ym = $dataReq['ym'];
                $postingSA->save();
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Closing Persediaan ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $data2,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $data2,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataComboKadaluarsa(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataProduk =  \DB::select(DB::raw("select * from
                        (select pr.id,pr.kdproduk as kdsirs,pr.namaproduk,sa.id as ssid,sa.satuanstandar,SUM(spd.qtyproduk) as qtyproduk			
                        from stokprodukdetail_t as spd
                        INNER JOIN produk_m as pr on pr.id = spd.objectprodukfk
                        INNER JOIN detailjenisproduk_m as djp on djp.id = pr.objectdetailjenisprodukfk
                        INNER JOIN jenisproduk_m as jp on jp.id = djp.objectjenisprodukfk
                        INNER JOIN satuanstandar_m as sa on sa.id = pr.objectsatuanstandarfk
                        where spd.kdprofile = $idProfile and pr.statusenabled=1 and spd.objectruanganfk=:objectruangan 
                        GROUP BY pr.id,pr.kdproduk,pr.namaproduk,sa.id,sa.satuanstandar) as x
                        where x.qtyproduk > 0
                        order by x.namaproduk asc"),
            array(
                'objectruangan' => $request['objectruangan'],
            )
        );

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kdprofile', $idProfile)
            ->where('ks.statusenabled',true)
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
                'qtyproduk' => $item->qtyproduk,
            );
        }

        $result = array(
            'produk' => $dataProdukResult,
        );

        return $this->respond($result);
    }

    public function saveBarangKadaluarsa (Request $request) {
        \DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $ruanganAsal = DB::select(DB::raw("select ru.namaruangan from ruangan_m as ru where ru.kdprofile = $idProfile and ru.id=:id"),
            array(
                'id' => $request['strukkirim']['objectruanganfk'],
            )
        );
        $strRuanganAsal='';
        $norecSpd='';
        $strRuanganAsal = $ruanganAsal[0]->namaruangan;
        $ruanganTujuan = DB::select(DB::raw("select ru.namaruangan from ruangan_m as ru where ru.kdprofile = $idProfile and ru.id=:id"),
            array(
                'id' => $request['strukkirim']['objectruanganfk'],
            )
        );
        $strRuanganTujuan='';
        $strRuanganTujuan = $ruanganTujuan[0]->namaruangan;
        $noStrukTerima = '';
        $norecSpd = '';
        $dataSPD = '';
        try{
            foreach ($request['details'] as $item) {
                $dataSaldoAwalK = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec,objectasalprodukfk as asalprodukfk,
                            hargadiscount,harganetto1 as harganetto,harganetto1 as hargasatuan
                            from stokprodukdetail_t 
                            where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk>0"),
                    array(
                        'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );

                $saldoAwalPengirim = 0;
                $jumlah=(float)$item['jumlah'] * (float)$item['nilaikonversi'];
                foreach ($dataSaldoAwalK as $items) {
                    $saldoAwalPengirim = (float)$items->qty;
                    $dataStok = StokProdukDetail::where('norec', $items->norec)
                        ->where('kdprofile',$idProfile)
                        ->orderBy('tglpelayanan','desc')
                        ->first();
                    $noStrukTerima = $dataStok->nostrukterimafk;
                    if ($request['strukkirim']['norec'] == ''){
                        $dataNewSPD = new StokProdukKadaluarsa();
                        $dataNewSPD->norec = $dataNewSPD->generateNewId();
                        $dataNewSPD->kdprofile = $idProfile;
                        $dataNewSPD->statusenabled = true;
                        $norecSpd = $dataNewSPD->norec;
                    }else{
                        $dataNewSPD = StokProdukKadaluarsa::where('norec', $request['strukkirim']['norec'])->where('kdprofile',$idProfile)->first();
                    }
                        if ((float)$items->qty <= $jumlah){
                            $dataNewSPD->objectasalprodukfk = $item['asalprodukfk'];
                            $dataNewSPD->hargadiscount =0;
                            $dataNewSPD->harganetto1 = $dataStok->harganetto1;
                            $dataNewSPD->harganetto2 = $dataStok->harganetto2;
                            $dataNewSPD->persendiscount = 0;
                            $dataNewSPD->objectprodukfk = $item['produkfk'];
                            $dataNewSPD->qtyproduk = (float)$items->qty;
                            $dataNewSPD->qtyprodukonhand = 0;
                            $dataNewSPD->qtyprodukoutext = 0;
                            $dataNewSPD->qtyprodukoutint = 0;
                            $dataNewSPD->objectruanganfk = $request['strukkirim']['objectruanganfk'];
                            $dataNewSPD->nostrukterimafk = $dataStok->nostrukterimafk;
                            $dataNewSPD->tglkadaluarsa =$request['strukkirim']['tglkadaluarsa'];
                            $dataNewSPD->tglpelayanankadaluarsa =  $this->getDateTime()->format('Y-m-d H:i:s');
                            $dataNewSPD->keteranganlainnya = $request['strukkirim']['keteranganlainnya'];
                            $dataNewSPD->save();
                            $dataSPD = $dataNewSPD;
                            $jumlah = $jumlah - (float)$items->qty;
                            StokProdukDetail::where('norec', $items->norec)
                                ->where('kdprofile',$idProfile)
                                ->update([
                                        'qtyproduk' => 0]
                                );
                        }else{
                            $dataNewSPD->objectasalprodukfk = $item['asalprodukfk'];
                            $dataNewSPD->hargadiscount =0;
                            $dataNewSPD->harganetto1 = $dataStok->harganetto1;
                            $dataNewSPD->harganetto2 = $dataStok->harganetto2;
                            $dataNewSPD->persendiscount = 0;
                            $dataNewSPD->objectprodukfk = $item['produkfk'];
                            $dataNewSPD->qtyproduk = $jumlah;
                            $dataNewSPD->qtyprodukonhand = 0;
                            $dataNewSPD->qtyprodukoutext = 0;
                            $dataNewSPD->qtyprodukoutint = 0;
                            $dataNewSPD->objectruanganfk = $request['strukkirim']['objectruanganfk'];
                            $dataNewSPD->nostrukterimafk = $dataStok->nostrukterimafk;
                            $dataNewSPD->tglkadaluarsa =$request['strukkirim']['tglkadaluarsa'];
                            $dataNewSPD->tglpelayanankadaluarsa =  $this->getDateTime()->format('Y-m-d H:i:s');
                            $dataNewSPD->keteranganlainnya = $request['strukkirim']['keteranganlainnya'];
                            $dataNewSPD->save();
                            $dataSPD = $dataNewSPD;
                            $saldoakhir =(float)$items->qty - $jumlah;
                            $jumlah=0;
                            $dataStok = StokProdukDetail::where('norec', $items->norec)
                                ->where('kdprofile',$idProfile)
                                ->where('objectprodukfk',$item['produkfk'])
//                                ->first()
                                ->update(['qtyproduk' => (float)$saldoakhir]);
                        }

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->keterangan = 'Barang Kadaluarsa, dari Ruangan '. $strRuanganAsal;
                    $newKS->produkfk = $item['produkfk'];
                    $newKS->ruanganfk = $request['strukkirim']['objectruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalPengirim - ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $noStrukTerima;
                    $newKS->norectransaksi = $norecSpd;
                    $newKS->tabletransaksi = 'stokprodukkadaluarsa_t';
                    $newKS->save();
                }

                //## Logging User
                $newId = LoggingUser::max('id');
                $newId = $newId +1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile= $idProfile;
                $logUser->statusenabled=true;
                $logUser->jenislog = 'Input Barang Kadaluarsa';
                $logUser->noreff = $norecSpd;
                $logUser->referensi='norec Stok Produk Kadaluarsa';
                $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
                $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
                $logUser->keterangan = 'Input Barang Kadaluarsa tanggal ' . $request['strukkirim']['tglkadaluarsa'];
                $logUser->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Barang Kadaluarsa";
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Barang Kadaluarsa Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $dataSPD,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Barang Kadaluarsa Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "data" => $dataSPD,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getBarangKadaluarsa (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();
        $tglkadaluarsa=$request['tglkadaluarsa'];
        $dataRuangan='';
        if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
            $dataRuangan = ' and ru.id = '. $request['idruangan'];
        }
        $kdproduk='';
        if(isset($request['kdproduk']) && $request['kdproduk']!="" && $request['kdproduk']!="undefined"){
            $kdproduk = ' and pr.id = '. $request['kdproduk'];
        }
        $tglAyeuna = date('Y-m-d H:i:s');
        $data = \DB::select(DB::raw("select * from
                        (select pr.id,pr.kdproduk as kdsirs,pr.namaproduk,SUM(spd.qtyproduk) as qtyproduk,spd.tglkadaluarsa			
                        from stokprodukkadaluarsa_t as spd
                        INNER JOIN produk_m as pr on pr.id = spd.objectprodukfk
                        INNER JOIN detailjenisproduk_m as djp on djp.id = pr.objectdetailjenisprodukfk
                        INNER JOIN jenisproduk_m as jp on jp.id = djp.objectjenisprodukfk
                        INNER JOIN satuanstandar_m as sa on sa.id = pr.objectsatuanstandarfk
                        INNER JOIN ruangan_m as ru on ru.id = spd.objectruanganfk
                        where spd.kdprofile = $idProfile and pr.statusenabled=1 and spd.tglkadaluarsa >= '$tglkadaluarsa' 
                        $dataRuangan
                        $kdproduk
                        GROUP BY pr.id,pr.kdproduk,pr.namaproduk,spd.tglkadaluarsa	) as x
                        where x.qtyproduk > 0
                        order by x.namaproduk asc"));

        $result= array(
            'data' => $data,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function saveStockOpname(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        ini_set('max_execution_time', 300); //6 minutes
        $dataReq = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataReq['userData']['id'])
            ->first();
        $datas=array();
        \DB::beginTransaction();
        try{
            $noClosing = $this->generateCode(new StrukClosing, 'noclosing', 10, 'PN/' . $this->getDateTime()->format('ym'), $idProfile);

            $dataSC = new StrukClosing();
            $dataSC->norec = $dataSC->generateNewId();
            $dataSC->kdprofile = $idProfile;
            $dataSC->statusenabled = true;
            $dataSC->objectpegawaidiclosefk = $dataPegawai->objectpegawaifk;
            $dataSC->objectkelompoktransaksifk = 12;
            $dataSC->keteranganlainnya = 'Stock Opname '.$request['namaRuangan'] ;
            $dataSC->noclosing = $noClosing;
            $dataSC->objectruangandiclosefk = $request['ruanganId'];
            $dataSC->objectruanganfk = $request['ruanganId'];
            $dataSC->tglclosing = $request['tglClosing'];
            $dataSC->save();

            $norecSC = $dataSC->norec;


            foreach ($request['stokProduk'] as $item) {


                $dataSPDK =StokProdukDetail::where('objectprodukfk', $item['produkfk'])
                    ->where('kdprofile',$idProfile)
                    ->where('objectruanganfk', $request['ruanganId'])
//                    ->where('tglkadaluarsa','>', $request['tglClosing'])
                    ->orderby('tglkadaluarsa')
                    ->first();
//                return $this->respond(count($dataSPDK));
//                if (count($dataSPDK) == 0 || $dataSPDK == null){
                if ($dataSPDK == null){
                    $dataSPDK2 =StokProdukDetail::where('objectprodukfk', $item['produkfk'])
                        ->where('kdprofile',$idProfile)
                        ->orderby('tglkadaluarsa')
                        ->first();

                    if(count($dataSPDK2) != 0 || $dataSPDK2 != null){

                        $dataSPD = new StokProdukDetailOpname();
                        $dataSPD->norec = $dataSPD->generateNewId();
                        $dataSPD->kdprofile = $idProfile;
                        $dataSPD->statusenabled = true;
                        $dataSPD->objectasalprodukfk = $dataSPDK2->objectasalprodukfk;
                        $dataSPD->hargadiscount = 0;
                        $dataSPD->harganetto1 = $dataSPDK2->harganetto1;
                        $dataSPD->harganetto2 = $dataSPDK2->harganetto2;
                        $dataSPD->persendiscount = 0;
                        $dataSPD->objectprodukfk = $item['produkfk'];
                        $dataSPD->qtyprodukreal = $item['stokReal'];
                        $dataSPD->qtyproduksystem = $item['stokSistem'];
                        $dataSPD->qtyprodukinext = $item['selisih'];
                        $dataSPD->objectruanganfk = $request['ruanganId'];
                        $dataSPD->noclosingfk = $norecSC;
                        $dataSPD->nostrukterimafk = $dataSPDK2->nostrukterimafk;
                        $dataSPD->save();

                        $dataNewSPD = new StokProdukDetail;
                        $dataNewSPD->norec = $dataNewSPD->generateNewId();
                        $dataNewSPD->kdprofile = $idProfile;
                        $dataNewSPD->statusenabled = true;
                        $dataNewSPD->objectasalprodukfk = $dataSPDK2->objectasalprodukfk;
                        $dataNewSPD->hargadiscount = $dataSPDK2->hargadiscount;
                        $dataNewSPD->harganetto1 = $dataSPDK2->harganetto1;
                        $dataNewSPD->harganetto2 = $dataSPDK2->harganetto2;
                        $dataNewSPD->persendiscount = 0;
                        $dataNewSPD->objectprodukfk = $dataSPDK2->objectprodukfk;
                        $dataNewSPD->qtyproduk = (float)$item['selisih'];
                        $dataNewSPD->qtyprodukonhand = 0;
                        $dataNewSPD->qtyprodukoutext = 0;
                        $dataNewSPD->qtyprodukoutint = 0;
                        $dataNewSPD->objectruanganfk = $request['ruanganId'];
                        $dataNewSPD->nostrukterimafk = $dataSPDK2->nostrukterimafk;
                        $dataNewSPD->noverifikasifk = $dataSPDK2->noverifikasifk;
                        $dataNewSPD->nobatch = $dataSPDK2->nobatch;
                        $dataNewSPD->tglkadaluarsa = $dataSPDK2->tglkadaluarsa;
                        $dataNewSPD->tglpelayanan = $dataSPDK2->tglpelayanan;
                        $dataNewSPD->tglproduksi = $dataSPDK2->tglproduksi;
                        $dataNewSPD->save();

                        $dataSTOKDETAIL[] = DB::select(DB::raw("select qtyproduk as qty,norec from stokprodukdetail_t 
                            where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                            array(
                                'ruanganfk' => $request['ruanganId'],
                                'produkfk' => $item['produkfk'],
                            )
                        );

                        //## KartuStok
                        $newKS = new KartuStok();
                        $norecKS = $newKS->generateNewId();
                        $newKS->norec = $norecKS;
                        $newKS->kdprofile = $idProfile;
                        $newKS->statusenabled = true;
                        $newKS->jumlah = (float)$item['selisih'];
                        $newKS->keterangan = 'Stock Opname Ruangan ' . $request['namaRuangan'];
                        $newKS->produkfk = $item['produkfk'];
                        $newKS->ruanganfk = $request['ruanganId'];
                        $newKS->saldoawal = (float)$item['selisih'];
                        $newKS->status = 1;
                        $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                        $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                        $newKS->nostrukterimafk =  $dataNewSPD->nostrukterimafk;
                        $newKS->norectransaksi = $dataNewSPD->norec;
                        $newKS->tabletransaksi = 'stokprodukdetail_t';
                        $newKS->flagfk = 5;
                        $newKS->save();
                    }else{
                        $dataBarang = DB::select(DB::raw("select * from produk_m where kdprofile = $idProfile and id=:produkfk"),
                            array(
                                'produkfk' => $item['produkfk'],
                            )
                        );
                        foreach ($dataBarang as $poek) {
                            $datas[] = array(
                                "kdproduk" => $item['produkfk'],
                                "namaproduk" => $poek->namaproduk,
                                "stokSistem" => $item['stokSistem'],
                                "stokReal" => $item['stokReal'],
                                "selisih" => $item['selisih'],
                            );
                        }
                    }
                }else{
                    $dataSPD = new StokProdukDetailOpname();
                    $dataSPD->norec = $dataSPD->generateNewId();
                    $dataSPD->kdprofile = $idProfile;
                    $dataSPD->statusenabled = true;
                    $dataSPD->objectasalprodukfk = $dataSPDK->objectasalprodukfk;
                    $dataSPD->hargadiscount = 0;
                    $dataSPD->harganetto1 = $dataSPDK->harganetto1;
                    $dataSPD->harganetto2 = $dataSPDK->harganetto2;
                    $dataSPD->persendiscount = 0;
                    $dataSPD->objectprodukfk = $item['produkfk'];
                    $dataSPD->qtyprodukreal = $item['stokReal'];
                    $dataSPD->qtyproduksystem = $item['stokSistem'];
                    $dataSPD->qtyprodukinext = $item['selisih'];
                    $dataSPD->objectruanganfk = $request['ruanganId'];
                    $dataSPD->noclosingfk = $norecSC;
                    $dataSPD->nostrukterimafk = $dataSPDK->nostrukterimafk;
                    $dataSPD->save();

                    //STOK MINUS MENYEBALKAN//
                    $dataStokMinus = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk < 0 "),
                        array(
                            'ruanganfk' => $request['ruanganId'],
                            'produkfk' => $item['produkfk'],
                        )
                    );
                    StokProdukDetail::where('objectruanganfk', $request['ruanganId'])
                        ->where('kdprofile',$idProfile)
                        ->where('objectprodukfk', $item['produkfk'])
                        ->where('qtyproduk', '<', 0)
                        ->update([
                            'qtyproduk' => 0
                        ]);
                    if (count($dataStokMinus) != 0) {
                        foreach ($dataStokMinus as $items) {
                            $stokMinus = (float)$items->qty;
                        }
                    }
                    //######################//


                    $saldoAwal = 0;
                    $jumlah = (float)$item['selisih'] + (float)$stokMinus;

                    if ($jumlah > 0) {
                        $dataStok = DB::select(DB::raw("select qtyproduk as qty,norec,nostrukterimafk from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk>0 limit 1"),
                            array(
                                'ruanganfk' => $request['ruanganId'],
                                'produkfk' => $item['produkfk'],
                            )
                        );
                        if (count($dataStok) == 0) {
                            $dataStok = DB::select(DB::raw("select qtyproduk as qty,norec,nostrukterimafk from stokprodukdetail_t 
                                  where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk limit 1"),
                                array(
                                    'ruanganfk' => $request['ruanganId'],
                                    'produkfk' => $item['produkfk'],
                                )
                            );
                        }
                        foreach ($dataStok as $items) {
                            StokProdukDetail::where('norec', $items->norec)
                                ->where('kdprofile',$idProfile)
                                ->update([
                                        'qtyproduk' => (float)$items->qty + (float)$jumlah]
                                );
                        }

                        $dataSTOKDETAIL[] = DB::select(DB::raw("select qtyproduk as qty,norec from stokprodukdetail_t 
                            where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk "),
                            array(
                                'ruanganfk' => $request['ruanganId'],
                                'produkfk' => $item['produkfk'],
                            )
                        );

                    } else {
                        $jumlah = $jumlah * (-1);
                        $dataStok = DB::select(DB::raw("select qtyproduk as qty,norec,nostrukterimafk from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                            array(
                                'ruanganfk' => $request['ruanganId'],
                                'produkfk' => $item['produkfk'],
                            )
                        );
                        foreach ($dataStok as $items) {
                            if ((float)$items->qty < $jumlah) {
                                $jumlah = $jumlah - (float)$items->qty;
                                StokProdukDetail::where('norec', $items->norec)
                                    ->where('kdprofile',$idProfile)
                                    ->update([
                                            'qtyproduk' => 0]
                                    );
                            } else {
                                $saldoakhir = (float)$items->qty - $jumlah;
                                $jumlah = 0;
                                StokProdukDetail::where('norec', $items->norec)
                                    ->where('kdprofile',$idProfile)
                                    ->update([
                                            'qtyproduk' => (float)$saldoakhir]
                                    );
                            }
                        }

                        $dataSTOKDETAIL[] = DB::select(DB::raw("select qtyproduk as qty,norec,nostrukterimafk from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                            array(
                                'ruanganfk' => $request['ruanganId'],
                                'produkfk' => $item['produkfk'],
                            )
                        );
                    }

                    $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
                  where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                        array(
                            'ruanganfk' => $request['ruanganId'],
                            'produkfk' => $item['produkfk'],
                        )
                    );
                    $saldoAwal = 0;
                    foreach ($dataSaldoAwalK as $items) {
                        $saldoAwal = (float)$items->qty;
                    }
                    $statusssss = 0;
                    $flagfk = 0;
                    if ($item['selisih'] < 0) {
                        $statusssss = 0;
                        $selisih = (float)$item['selisih'] * (-1);
                        $flagfk = 5;
                    } else {
                        $statusssss = 1;
                        $selisih = (float)$item['selisih'];
                        $flagfk = 4;
                    }

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = $selisih;
                    $newKS->keterangan = 'Stock Opname Ruangan ' . $request['namaRuangan'];
                    $newKS->produkfk = $item['produkfk'];
                    $newKS->ruanganfk = $request['ruanganId'];
                    $newKS->saldoawal = (float)$saldoAwal;
                    $newKS->status = $statusssss;
                    $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->nostrukterimafk = $dataSPDK->nostrukterimafk;
                    $newKS->norectransaksi = $dataSPDK->norec;
                    $newKS->tabletransaksi = 'stokprodukdetail_t';
                    $newKS->flagfk = $flagfk;
                    $newKS->save();

                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Stock Opname";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "databarangtaktersave" => $datas,
//                "message" => $transMessage,
                "noSO" => $dataSC,
                "detailstok" => $dataSTOKDETAIL,
                "dataSPDK" => $dataSPDK,
//                "dataSPDK2" => $dataSPDK2,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "message"  => $transMessage,
                "databarangtaktersave" => $datas,
                "noSO" => $dataSC,
                "detailstok" => $dataSTOKDETAIL,
                "dataSPDK" => $dataSPDK,
//                "dataSPDK2" => $dataSPDK2,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function DeleteBarangKadaluarsa(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
        $dataKembaliStok = DB::select(DB::raw("select spd.qtyproduk ,spd.hasilkonversi  ,spd.objectruanganfk ,
                spd.objectprodukfk,spd.harganetto,sp.nostruk
                from strukpelayanandetail_t as spd inner join strukpelayanan_t as sp on sp.norec=spd.nostrukfk
                where kdprofile = $idProfile and spd.nostrukfk=:strukfk"),
            array(
                'strukfk' => $request['norec_sp'],
            )
        );
        foreach ($dataKembaliStok as $item5){
            $TambahStok = (float)$item5->qtyproduk*(float)$item5->hasilkonversi;
            $newSPD = StokProdukDetail::where('objectruanganfk',$item5->objectruanganfk)
                ->where('kdprofile', $idProfile)
                ->where('objectprodukfk',$item5->objectprodukfk)
//                ->where('harganetto1',$item5->harganetto)
                ->orderby('tglkadaluarsa','desc')
                ->first();
            $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
            try {
                $newSPD->save();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
                $transMessage = "update Stok obat";
            }

            $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                    where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                array(
                    'ruanganfk' => $item5->objectruanganfk,
                    'produkfk' => $item5->objectprodukfk,
                )
            );
            $saldoAwal=0;
            foreach ($dataSaldoAwal as $itemss){
                $saldoAwal = (float)$itemss->qty;
            }

            $newKS = new KartuStok();
            $norecKS = $newKS->generateNewId();
            $newKS->norec = $norecKS;
            $newKS->kdprofile = $idProfile;
            $newKS->statusenabled = true;
            $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
            $newKS->keterangan = 'Hapus Resep Obat Bebas No. ' . $item5->nostruk;
            $newKS->produkfk = $item5->objectprodukfk;
            $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
            $newKS->saldoawal = (float)$saldoAwal ;//- (float)$qtyJumlah;
            $newKS->status = 1;
            $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
            $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
            $newKS->nostrukterimafk =  $newSPD['nostrukterimafk'];
            $newKS->norectransaksi = $newSPD->norec;
            $newKS->tabletransaksi = 'stokprodukdetail_t';
            try {
                $newKS->save();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
                $transMessage = "Kartu Stok Ubah Resep";
            }
        }
        //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
        try {
            $datadel = StrukPelayananDetail::where('nostrukfk',$request['norec_sp'])->where('kdprofile', $idProfile)->delete();
            $datadel2 = StrukPelayanan::where('norec', $request['norec_sp'])->where('kdprofile', $idProfile)->delete();
//                ->update([
//                        'statusenabled' => 'f']
//                );
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "gagal Pelayanan Pasien";
        }

        ##KartuStok
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
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarReturPenerimaanSuplierDetail(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukretur_t as sr')
            ->JOIN('strukpelayanan_t as sp','sp.norec','=','sr.strukterimafk')
            ->LEFTJOIN('strukreturdetail_t as srd','srd.strukreturfk','=','sr.norec')
            ->LEFTJOIN('produk_m as pr','pr.id','=','srd.objectprodukfk')
            ->LEFTJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->LEFTJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipenerimafk')
            ->LEFTJOIN('pegawai_m as pg1','pg1.id','=','sr.objectpegawaifk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk','sbk.norec','=','sp.nosbklastfk')
            ->select(DB::raw("sr.norec,sr.tglretur,sr.noretur,sp.nostruk,sp.nosppb,sp.nokontrak,sp.nofaktur,sp.tglfaktur,
                                    sp.objectruanganfk,ru.namaruangan,rkn.namarekanan,pg.namalengkap,pg1.namalengkap as pegawairetur,
                                    pr.namaproduk,ss.satuanstandar,srd.qtyproduk,srd.harganetto1,srd.hargadiscount,
							        ((srd.harganetto1-srd.hargadiscount)*srd.qtyproduk) as total,srd.tglkadaluarsa,srd.nobatch"))
            ->where('sr.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sr.tglretur','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sr.tglretur','<=', $tgl);
        }
        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('sp.nostruk','ILIKE','%'. $request['nostruk']);
        }
        if(isset($request['namarekanan']) && $request['namarekanan']!="" && $request['namarekanan']!="undefined"){
            $data = $data->where('rkn.namarekanan','ILIKE','%'. $request['namarekanan'].'%');
        }
        if(isset($request['nofaktur']) && $request['nofaktur']!="" && $request['nofaktur']!="undefined"){
            $data = $data->where('sp.nofaktur','ILIKE','%'. $request['nofaktur'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('srd.objectprodukfk','=',$request['produkfk']);
        }
        if(isset($request['noSppb']) && $request['noSppb']!="" && $request['noSppb']!="undefined"){
            $data = $data->where('sp.nosppb','ILIKE','%'. $request['noSppb'].'%');
        }
        if(isset($request['noretur']) && $request['noretur']!="" && $request['noretur']!="undefined"){
            $data = $data->where('sr.noretur','ILIKE','%'. $request['noretur'].'%');
        }

//        $data = $data->wherein('sp.objectruanganfk',$strRuangan);
        $data = $data->where('sr.statusenabled',true);
        $data = $data->where('sr.objectkelompoktransaksifk',50);
        $data = $data->orderBy('sr.noretur');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function saveVerifikasiAnggaran (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try{
            if ($request['strukverifikasi']['norec'] != '') {
//                //#struk Verifikasi
                $noVerifikasi = $this->generateCode(new StrukKonfirmasi(),
                    'nokonfirmasi', 10, 'CN'.$this->getDateTime()->format('Y'), $idProfile);
                $dataSV = new StrukKonfirmasi();
                $dataSV->norec = $dataSV->generateNewId();
                $dataSV->nokonfirmasi = $noVerifikasi;
                $dataSV->kdprofile = $idProfile;
                $dataSV->statusenabled = true;
                $dataSV->objectkelompoktransaksifk = 102;
                $dataSV->keteranganlainnya = 'CONFIRM USULAN PERMINTAAN BARANG/JASA';
                $dataSV->objectpegawaifk = $request['strukverifikasi']['objectpegawaipjawabfk'];
                $dataSV->namakonfirmasi = 'Confirm Anggaran';
//            $dataSV->objectruanganfk = $request['strukverifikasi']['objectruanganfk'];
                $dataSV->tglkonfirmasi = $request['strukverifikasi']['tglconfirm'];
                $dataSV->save();
                $dataSV = $dataSV->norec;

                $dataSO = StrukOrder::where('norec', $request['strukverifikasi']['norec'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                            'objectkonfirmasifk' => $dataSV,
                        ]
                    );


            }

//            $dataSO = StrukOrder::where('norec', $request['strukverifikasi']['norec'])
//                ->where('kdprofile', $idProfile)
//                ->update([
//                        'objectsrukverifikasifk' => $dataSV,
//                        'keteranganlainnya' =>$request['strukorder']['koordinator'],
//                        'totalhargasatuan' => $request['strukorder']['total'],
//                        'totalppn' => $request['strukorder']['ppn']
//                    ]
//                );
//


//          //***** Riwayat Realisasi *****
//        if ($request['strukverifikasi']['norecrealisasi'] != null) {
            $dataRR= new RiwayatRealisasi();
//          $norealisasi = $this->generateCode(new StrukRealisasi(),'norealisasi',10,'RC-'.$this->getDateTime()->format('ym'));
            $dataRR->norec = $dataRR->generateNewId();
            $dataRR->kdprofile = $idProfile;
            $dataRR->statusenabled = true;
            $dataRR->objectkelompoktransaksifk = 102;
//        }else {
//            $dataRR = RiwayatRealisasi::where('objectstrukrealisasifk', $request['strukverifikasi']['norecrealisasi'])->first();
//        }
            $dataRR->objectstrukrealisasifk = $request['strukverifikasi']['norecrealisasi'];
            $dataRR->objectstrukfk = $request['strukverifikasi']['norec'];
            $dataRR->tglrealisasi = $request['strukverifikasi']['tglconfirm'];
            $dataRR->objectpetugasfk = $request['strukverifikasi']['objectpegawaipjawabfk'];
            if (isset($request['strukverifikasi']['nousulan'])){
                $dataRR->noorderintern = $request['strukverifikasi']['nousulan'];
            }
            $dataRR->keteranganlainnya = 'CONFIRM USULAN PERMINTAAN BARANG/JASA';
            $dataRR->objectverifikasifk = $dataSV;
            $dataRR->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Verifikasi";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $request['strukverifikasi']['norec'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan VerifikasiGagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $request['strukverifikasi']['norec'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarStokRuanganSODetail(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();
        $detailjenisprodukfk = '';
        $jenisprodukfk='';
        $namaproduk='';
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId='';

        if(isset( $request['jeniskprodukid'])&&  $request['jeniskprodukid']!=''){
            $jenisprodukfk = "and djp.objectjenisprodukfk in (".$request['jeniskprodukid'].")";
        }
        if(isset( $request['namaproduk'])&&  $request['namaproduk']!=''){
            $namaproduk = "and pr.namaproduk  ILIKE '%". $request['namaproduk']."%'";
        }
        if(isset( $request['detailjenisprodukfk'])&&  $request['detailjenisprodukfk']!=''){
            $detailjenisprodukfk = "and djp.id in (".$request['detailjenisprodukfk'].")";
        }
        if(isset( $request['ruanganfk'])&&  $request['ruanganfk']!=''){
            $ruanganId = "and ru.id =".$request['ruanganfk'];
        }

        $data = DB::select(DB::raw("SELECT x.tglclosing,x.kdproduk,x.namaproduk,x.satuanstandar,
                    SUM (x.farmasi) AS farmasi, SUM (x.deporajal) AS deporajal, SUM (x.depoinap) AS depoinap, SUM (x.depoigd) AS depoigd,
                    SUM (x.depoibs) AS depoibs, SUM (x.qtyprodukreal) AS qtyseluruh,SUM (x.harganetto1) AS harganetto1,SUM(x.total) as total
                    FROM (SELECT convert(varchar, sc.tglclosing, 105) as tglclosing, pr.id AS kdproduk,pr.namaproduk,ss.satuanstandar,
                            CASE WHEN ru.id = 452 then spd.qtyprodukreal else 0 end as farmasi,
                            CASE WHEN ru.id = 553 then spd.qtyprodukreal else 0 end as deporajal,
                            CASE WHEN ru.id = 554 then spd.qtyprodukreal else 0 end as depoinap,
                            CASE WHEN ru.id = 555 then spd.qtyprodukreal else 0 end as depoigd,
                            CASE WHEN ru.id = 556 then spd.qtyprodukreal else 0 end as depoibs,
                            --CASE WHEN ru.id = 558 then spd.qtyprodukreal else 0 end as graha,
                            spd.qtyprodukreal,spd.harganetto1,spd.qtyprodukreal * spd.harganetto1 AS total
                        FROM
                        strukclosing_t sc
                        LEFT JOIN stokprodukdetailopname_t spd ON spd.noclosingfk = sc.norec
                        LEFT JOIN strukpelayanan_t sp ON sp.norec = spd.nostrukterimafk
                        LEFT JOIN strukpelayanandetail_t spdt ON spdt.noclosingfk = sc.norec
                        LEFT JOIN produk_m pr ON pr.id = spd.objectprodukfk
                        LEFT JOIN detailjenisproduk_m djp ON djp.id = pr.objectdetailjenisprodukfk
                        LEFT JOIN satuanstandar_m ss ON ss.id = pr.objectsatuanstandarfk
                        LEFT JOIN ruangan_m ru ON ru.id = spd.objectruanganfk
                        WHERE sc.kdprofile = $idProfile and sc.tglclosing BETWEEN '$tglAwal' AND '$tglAkhir' and pr.id is not null and spd.qtyprodukreal > 0					
                        $namaproduk
                        $detailjenisprodukfk
                        $jenisprodukfk										
					) AS x
				 GROUP BY x.tglclosing,x.kdproduk,x.namaproduk,x.satuanstandar
                 ORDER BY x.namaproduk asc
			"));

        $result= array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDaftarHistoryAsset(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        if(isset( $request['noregisterasetfk'])&&  $request['noregisterasetfk']!=''){
            $noregisterasetfk = "and kpa.noregisterasetfk " . $request['noregisterasetfk'] . ")";
        }

        $data = DB::select(DB::raw("
                select sk.nokirim,sk.tglkirim,sk.objectruanganasalfk,
                ruasal.namaruangan as ruanganasal,sk.objectruangantujuanfk ,rutujuan.namaruangan as ruangantujuan
                from strukkirim_t as sk 
                INNER JOIN kirimprodukaset_t as kpa on kpa.nokirimfk=sk.norec
                INNER JOIN ruangan_m as ruasal on ruasal.id=sk.objectruanganasalfk
                INNER JOIN ruangan_m as rutujuan on rutujuan.id=sk.objectruangantujuanfk
                where sk.kdprofile = $idProfile and sk.objectkelompoktransaksifk=95
                $noregisterasetfk
			"));

        $result= array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
	
 	public function getStokMinimumGlobal(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;

        $tglawal = $request['tglawal'];
        $tglakhir = $request['tglakhir'];
        $jmlharipesan = (int)$request['jmlharipesan'];
        $leadtime = (int)$request['leadtime'];

        $data = DB::select(DB::raw("
                select x.produkfk,pr.namaproduk,ss.satuanstandar,
                (x.total1*$leadtime,2) as stokminimun,y.stok,
                (x.total1*($jmlharipesan+$leadtime),2) as qtypengadaan ,
                x.total1 as rata30hari
                from 
                (select produkfk,sum(jumlah)/30 as total1
                from pelayananpasien_t
                where tglpelayanan  between '$tglawal' and '$tglakhir'
                and kdprofile = $idProfile
                and isobat=true
                GROUP BY produkfk) as x
                INNER JOIN 
                (select objectprodukfk,sum(qtyproduk) as stok from stokprodukdetail_t
                 where  kdprofile = $idProfile
                group by objectprodukfk) as y 
                on x.produkfk=y.objectprodukfk
                INNER JOIN produk_m as pr on pr.id=x.produkfk
                INNER JOIN satuanstandar_m as ss on ss.id=pr.objectsatuanstandarfk
                where x.total1*$leadtime > y.stok;
            "));
        $data2 = DB::select(DB::raw("
                select * from profile_m where statusenabled=true;
            "));
        $stok = DB::select(DB::raw("select sum(spd.qtyproduk) as qtyproduk,prd.id,prd.namaproduk,
                ru.namaruangan,ss.satuanstandar
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                inner JOIN ruangan_m as ru on ru.id=spd.objectruanganfk
                inner JOIN produk_m as prd on prd.id=spd.objectprodukfk
                left JOIN satuanstandar_m as ss on ss.id=prd.objectsatuanstandarfk
                inner JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                where spd.qtyproduk > 0 
                and prd.statusenabled=1 
                and ru.statusenabled=1  
                and spd.kdprofile = $idProfile
                group by prd.namaproduk,ru.namaruangan,ss.satuanstandar ,prd.id
                order by prd.namaproduk"));
        if(count($stok) > 0){
            foreach ($stok as $key => $row) {
                $count[$key] = $row->qtyproduk;
            }
            array_multisort($count, SORT_DESC, $stok);
        }

        $result= array(
            'data' => $data,
            'profile' => $data2,
            'stok' => $stok,
            'copyright' => 'transmedic',
            'by' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getDataComboMataAnggaran(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAyeuna = date('Y');
        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        $dataAnggaranT = \DB::table('mataanggaran_t as ma')
            ->select('ma.norec','ma.mataanggaran','ma.saldoawalblu','ma.saldoawalrm')
            ->where('ma.statusenabled',true)
            ->where('ma.kdprofile',$idProfile)
            ->where('ma.tahun', 'ILIKE', '%'.$tglAyeuna.'%')
            ->orderBy('ma.mataanggaran')
            ->get();

        $dataAnggaranH1 = \DB::table('kelompokanggaranpertama_m as ma')
            ->select('ma.id','ma.childpertama')
            ->where('ma.statusenabled',true)
            ->where('ma.kdprofile',$idProfile)
            ->orderBy('ma.childpertama')
            ->get();

        $dataAnggaranH2 = \DB::table('kelompokanggarankedua_m as ma')
            ->select('ma.id','ma.childkedua')
            ->where('ma.kdprofile',$idProfile)
            ->where('ma.statusenabled',true)
            ->orderBy('ma.childkedua')
            ->get();

        $dataAnggaranH3 = \DB::table('kelompokanggaranketiga_m as ma')
            ->select('ma.id','ma.childketiga')
            ->where('ma.kdprofile',$idProfile)
            ->where('ma.statusenabled',true)
            ->orderBy('ma.childketiga')
            ->get();

        $dataAnggaranH4 = \DB::table('kelompokanggarankeempat_m as ma')
            ->select('ma.id','ma.childkeempat')
            ->where('ma.kdprofile',$idProfile)
            ->where('ma.statusenabled',true)
            ->orderBy('ma.childkeempat')
            ->get();

        $Pengendali = \DB::table('pengendali_m as ma')
            ->select('ma.id','ma.pengendali')
            ->where('ma.kdprofile',$idProfile)
            ->where('ma.statusenabled',true)
            ->orderBy('ma.pengendali')
            ->get();

        $AslProduk = \DB::table('asalproduk_m as ma')
            ->select('ma.id','ma.asalproduk')
            ->where('ma.statusenabled',true)
            ->orderBy('ma.asalproduk')
            ->get();

        $result = array(
            'mataanggaran' => $dataAnggaranT,
            'headsatu' => $dataAnggaranH1,
            'headdua' => $dataAnggaranH2,
            'headtiga' => $dataAnggaranH3,
            'headempat' => $dataAnggaranH4,
            'pengendali' => $Pengendali,
            'asalproduk' => $AslProduk,
            'user' => $dataPegawaiUser,
            'message' => 'cepot@epic',
        );
        return $this->respond($result);
    }

    public function getTrendPemakaianObat (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal = $request['tglawal'];
        $tglAkhir = $request['tglakhir'];
        $data = DB::select(DB::raw("select  * from
                (
                select sum(pp.jumlah) as jumlah,prd.namaproduk  ,prd.id
                from pelayananpasien_t  as pp 
                join produk_m as prd on pp.produkfk= prd.id
                where pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN '$tglAwal' and  '$tglAkhir'
                and pp.strukresepfk is not null 
                GROUP BY prd.namaproduk,prd.id

                UNION ALL
                SELECT  sum(spd.qtyproduk) as jumlah,pr.namaproduk,pr.id
               FROM strukpelayanan_t as sp  
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN '$tglAwal' and  '$tglAkhir'
                AND sp.nostruk_intern='-' AND substring(sp.nostruk,1,2)='OB'  
                and sp.statusenabled != false
                GROUP BY pr.namaproduk,pr.id
                UNION ALL    
                 SELECT sum  (spd.qtyproduk) as jumlah,pr.namaproduk,pr.id
               FROM strukpelayanan_t as sp  
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN '$tglAwal' and  '$tglAkhir'
              AND sp.nostruk_intern not in ('-') AND substring(sp.nostruk,1,2)='OB'  
                and sp.statusenabled != false
                        GROUP BY pr.namaproduk,pr.id
                ) as x
                order by x.jumlah desc
                limit 10")
        );

        $result = array(
            'chart' => $data,
            'copyright' => 'transmedic',
            'by' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function getProdukAvailable (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        // return $idProfile;
           $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
               ->where('pr.kdprofile', $idProfile)
            ->where('pr.statusenabled',true)
            ->whereIn('jp.id',[97,283])
            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk');

            if(isset($request['namaProduk']) &&$request['namaProduk']!='' ){
             $dataProduk =$dataProduk->where('pr.namaproduk','ILIKE','%'.$request['namaProduk'].'%');
            }
            $dataProduk =$dataProduk->get();
        $result = array(
            'data' => $dataProduk,
            'copyright' => 'transmedic',
            'by' => 'er@epic',
        );
        return $this->respond($result);
     }

    public function updateBarangKadaluarsa(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
//        return $this->respond($request->all());
        try {
            $stokProdukDetail = StokProdukDetail::where('norec',$request['norec_spd'])
                                ->where('kdprofile', $idProfile)
                                ->where('objectprodukfk',$request['produkfk'])
//                                ->where('nostrukterimafk',$request['nostruterimafk'])
                                ->update([
                                    'tglkadaluarsa' => $request['tanggal']
                                ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $stokProdukDetail,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "data" => $stokProdukDetail,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveAdjustmentStok(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$request['userData']['id'])
            ->first();
        $KelTransAddjusment = (int) $this->settingDataFixed('KelTransAddjusment', $idProfile);
        \DB::beginTransaction();
        try {
                $newSPD = StokProdukDetail::where('norec',$request['norec_spd'])
                    ->where('kdprofile', $idProfile)
                    ->where('nostrukterimafk',$request['nostruterimafk'])
                    ->where('objectruanganfk',$request['ruanganfk'])
                    ->where('objectprodukfk',$request['produkfk'])
                    ->first();

                $noClosing = $this->generateCode(new StrukClosing, 'noclosing', 10, 'AS/' . $this->getDateTime()->format('ym'),$idProfile);
                $dataSC = new StrukClosing();
                $dataSC->norec = $dataSC->generateNewId();
                $dataSC->kdprofile = $idProfile;
                $dataSC->statusenabled = true;
                $dataSC->objectpegawaidiclosefk = $dataPegawai->objectpegawaifk;
                $dataSC->objectkelompoktransaksifk = $KelTransAddjusment;
                $dataSC->keteranganlainnya = 'Adjusment Stok '.$request['namaRuangan'] ;
                $dataSC->noclosing = $noClosing;
                $dataSC->objectruangandiclosefk = $request['ruanganfk'];
                $dataSC->objectruanganfk = $request['ruanganfk'];
                $dataSC->tglclosing = date('Y-m-d H:i:s');
                $dataSC->save();
                $norecSC = $dataSC->norec;

                $dataSPD = new StokProdukDetailAdjusment();
                $dataSPD->norec = $dataSPD->generateNewId();
                $dataSPD->kdprofile = $idProfile;
                $dataSPD->statusenabled = true;
                $dataSPD->objectasalprodukfk = $newSPD->objectasalprodukfk;
                $dataSPD->hargadiscount = 0;
                $dataSPD->harganetto1 = $newSPD->harganetto1;
                $dataSPD->harganetto2 = $newSPD->harganetto2;
                $dataSPD->persendiscount = 0;
                $dataSPD->objectprodukfk = $request['produkfk'];
                $dataSPD->qtyprodukreal = $request['qtyreal'];
                $dataSPD->qtyproduksystem = $request['qtyad'];
                $dataSPD->qtyprodukadjusment = $request['qtyad'];
                $dataSPD->objectruanganfk = $request['ruanganfk'];
                $dataSPD->noclosingfk = $norecSC;
                $dataSPD->nostrukterimafk = $newSPD->nostrukterimafk;
                $dataSPD->norec_spd = $request['norec_spd'];
                $dataSPD->save();
                $dataSpdAD = $dataSPD->norec;
                $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                            where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $request['ruanganfk'],
                        'produkfk' => $request['produkfk']
                    )
                );

                $saldoAwal=0;
                foreach ($dataSaldoAwal as $itemss){
                    $saldoAwal = (float)$itemss->qty;
                }
                if ($saldoAwal == 0){
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = (float) $request['qtyad'];
                    $newKS->keterangan = 'Adjusment Stok Ruangan ' . $request['namaRuangan'];
                    $newKS->produkfk = $request['produkfk'];
                    $newKS->ruanganfk = $request['ruanganfk'];
                    $newKS->saldoawal = (float) $request['qtyad'];
                    $newKS->status = 1;
                    $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
                    $newKS->norectransaksi = $newSPD->norec;
                    $newKS->tabletransaksi = 'stokprodukdetail_t';
                    $newKS->save();
                }else{
                    $Selisih = (float) $request['qtyad'] - (float) $request['qtyreal'];
                    $statusssss = 0;
                    $hasilSelisih = 0;
                    $saldoAwalR = 0;
                    $jumlahR = 0;
                    if ($Selisih < 0) {
                        $statusssss = 0;
                        $selisih = (float)$Selisih * (-1);
                    } else {
                        $statusssss = 1;
                        $selisih = (float)$Selisih;
                    }
                    if ($statusssss == 0){
                        $jumlahR = $selisih;
                        $saldoAwalR = (float)$saldoAwal - (float)$selisih;
                    }else{
                        $jumlahR = $selisih;
                        $saldoAwalR = (float)$selisih + (float)$saldoAwal;
                    }

//                    return $this->respond($saldoAwalR);
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = 12;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = (float)$jumlahR;//$selisih;
                    $newKS->keterangan = 'Adjusment Stok Ruangan ' . $request['namaRuangan'];
                    $newKS->produkfk = $request['produkfk'];
                    $newKS->ruanganfk = $request['ruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalR;
                    $newKS->status = $statusssss;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
                    $newKS->norectransaksi = $newSPD->norec;
                    $newKS->tabletransaksi = 'stokprodukdetail_t';
                    $newKS->save();
//                    return $this->respond($newKS);
                }

                StokProdukDetail::where('norec', $newSPD->norec)
                    ->where('kdprofile', $idProfile)
                    ->update([
                        'qtyproduk' => $request['qtyad']
                    ]);

                //## Logging User
                $newId = LoggingUser::max('id');
                $newId = $newId +1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile= $kdProfile;
                $logUser->statusenabled=true;
                $logUser->jenislog = "Adjusment Stok Ruangan";
                $logUser->noreff = $dataSpdAD;
                $logUser->referensi='norec stokprodukdetailadjustment_t';
                $logUser->objectloginuserfk =  $request['userData']['id'];
                $logUser->tanggal = date('Y-m-d H:i:s');
                $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "data" => $stokProdukDetail,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "data" => $stokProdukDetail,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    function getDaftarBarangPerBulan (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = DB::select(DB::raw("select x.namaproduk, x.satuanstandar, x.hargasatuan, x.namaruangan,
        sum(x.tgl1) as tgl1,sum(x.tgl2) as tgl2,sum(x.tgl3) as tgl3,sum(x.tgl4) as tgl4,sum(x.tgl5) as tgl5,sum(x.tgl6) as tgl6,sum(x.tgl7) as tgl7,sum(x.tgl8) as tgl8,sum(x.tgl9) as tgl9,
        sum(x.tgl10) as tgl10,sum(x.tgl11) as tgl11,sum(x.tgl12) as tgl12,sum(x.tgl13) as tgl13,sum(x.tgl14) as tgl14,sum(x.tgl15) as tgl15,sum(x.tgl16) as tgl16,sum(x.tgl17) as tgl17,sum(x.tgl18) as tgl18,sum(x.tgl19) as tgl19,
        sum(x.tgl20) as tgl20,sum(x.tgl21) as tgl21,sum(x.tgl22) as tgl22,sum(x.tgl23) as tgl23,sum(x.tgl24) as tgl24,sum(x.tgl25) as tgl25,sum(x.tgl26) as tgl26,sum(x.tgl27) as tgl27,sum(x.tgl28) as tgl28,sum(x.tgl29) as tgl29,
        sum(x.tgl30) as tgl30,sum(x.tgl31) as tgl31
        from
        (select pr.namaproduk,ss.satuanstandar,sp.tglkirim,kp.qtyproduk,kp.hargasatuan,ru.namaruangan,
        case when date_part('day',sp.tglkirim) = 1 then kp.qtyproduk else 0 end as tgl1,
        case when date_part('day',sp.tglkirim) = 2 then kp.qtyproduk else 0 end as tgl2,
        case when date_part('day',sp.tglkirim) = 3 then kp.qtyproduk else 0 end as tgl3,
        case when date_part('day',sp.tglkirim) = 4 then kp.qtyproduk else 0 end as tgl4,
        case when date_part('day',sp.tglkirim) = 5 then kp.qtyproduk else 0 end as tgl5,
        case when date_part('day',sp.tglkirim) = 6 then kp.qtyproduk else 0 end as tgl6,
        case when date_part('day',sp.tglkirim) = 7 then kp.qtyproduk else 0 end as tgl7,
        case when date_part('day',sp.tglkirim) = 8 then kp.qtyproduk else 0 end as tgl8,
        case when date_part('day',sp.tglkirim) = 9 then kp.qtyproduk else 0 end as tgl9,
        case when date_part('day',sp.tglkirim) = 10 then kp.qtyproduk else 0 end as tgl10,
        case when date_part('day',sp.tglkirim) = 11 then kp.qtyproduk else 0 end as tgl11,
        case when date_part('day',sp.tglkirim) = 12 then kp.qtyproduk else 0 end as tgl12,
        case when date_part('day',sp.tglkirim) = 13 then kp.qtyproduk else 0 end as tgl13,
        case when date_part('day',sp.tglkirim) = 14 then kp.qtyproduk else 0 end as tgl14,
        case when date_part('day',sp.tglkirim) = 15 then kp.qtyproduk else 0 end as tgl15,
        case when date_part('day',sp.tglkirim) = 16 then kp.qtyproduk else 0 end as tgl16,
        case when date_part('day',sp.tglkirim) = 17 then kp.qtyproduk else 0 end as tgl17,
        case when date_part('day',sp.tglkirim) = 18 then kp.qtyproduk else 0 end as tgl18,
        case when date_part('day',sp.tglkirim) = 19 then kp.qtyproduk else 0 end as tgl19,
        case when date_part('day',sp.tglkirim) = 20 then kp.qtyproduk else 0 end as tgl20,
        case when date_part('day',sp.tglkirim) = 21 then kp.qtyproduk else 0 end as tgl21,
        case when date_part('day',sp.tglkirim) = 22 then kp.qtyproduk else 0 end as tgl22,
        case when date_part('day',sp.tglkirim) = 23 then kp.qtyproduk else 0 end as tgl23,
        case when date_part('day',sp.tglkirim) = 24 then kp.qtyproduk else 0 end as tgl24,
        case when date_part('day',sp.tglkirim) = 25 then kp.qtyproduk else 0 end as tgl25,
        case when date_part('day',sp.tglkirim) = 26 then kp.qtyproduk else 0 end as tgl26,
        case when date_part('day',sp.tglkirim) = 27 then kp.qtyproduk else 0 end as tgl27,
        case when date_part('day',sp.tglkirim) = 28 then kp.qtyproduk else 0 end as tgl28,
        case when date_part('day',sp.tglkirim) = 29 then kp.qtyproduk else 0 end as tgl29,
        case when date_part('day',sp.tglkirim) = 30 then kp.qtyproduk else 0 end as tgl30,
        case when date_part('day',sp.tglkirim) = 31 then kp.qtyproduk else 0 end as tgl31
        from strukkirim_t as sp 
        left join pegawai_m as pg on pg.id = sp.objectpegawaipengirimfk 
        left join ruangan_m as ru on ru.id = sp.objectruanganasalfk 
        left join ruangan_m as ru2 on ru2.id = sp.objectruangantujuanfk 
        left join kirimproduk_t as kp on kp.nokirimfk = sp.norec 
        left join produk_m as pr on pr.id = kp.objectprodukfk 
        left join detailjenisproduk_m as djp on djp.id = pr.objectdetailjenisprodukfk 
        left join jenisproduk_m as jp on jp.id = djp.objectjenisprodukfk 
        left join kelompokproduk_m as kps on kps.id = jp.objectkelompokprodukfk 
        left join asalproduk_m as ap on ap.id = kp.objectasalprodukfk 
        left join satuanstandar_m as ss on ss.id = kp.objectsatuanstandarfk 
        where sp.kdprofile = $idProfile and sp.tglkirim >= '$tglAwal' and sp.tglkirim <= '$tglAkhir' 
        and sp.statusenabled = true and sp.objectkelompoktransaksifk = 34 and kp.qtyproduk > 0 and ru.objectdepartemenfk = 14
        order by sp.nokirim asc) 
        as x
        group by x.namaproduk, x.satuanstandar, x.hargasatuan, x.namaruangan"));

        $result = array(
            'data' => $data,
            'by' => 'er@epic',
        );
        return $this->respond($data);
    }

    function updateHead(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        try {
            $newSPD = StrukPelayanan::where('norec',$request['norec'])
                    ->update([
                        'namapengadaan' => $request['namapengadaan'],
                        'namarekanan' => $request['rekanan']['namarekanan'],
                        'namapegawaipenerima' => $request['pegawaipenerima']['namalengkap'],
                        'nostruk' => $request['nousulan'],
                        'noterima' => $request['noterima'],
                        'nofaktur' => $request['nofaktur'],
                        'nokontrak' => $request['nokontrak'],
                        'objectkelompoktransaksifk' => 35,
                        'objectpegawaipenerimafk' => $request['pegawaipenerima']['id'],
                        'objectrekananfk' => $request['rekanan']['id'],
                        'objectruanganfk' => $request['ruangterima']['id'],
                        'tglstruk' => $request['tglusulan'],
                        'tgljatuhtempo' => $request['tgljatuhtempo'],
                        'tgldokumen' => $request['tglfaktur'],
                    ]);
                    

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "data" => $stokProdukDetail,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "data" => $stokProdukDetail,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}