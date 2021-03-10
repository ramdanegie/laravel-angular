<?php
/**
 * StokLogistikController
 * Created by PhpStorm.
 * User: as@epic
 * Date: 29/08/2017
 * Time: 12.09
 */
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 09/08/2019
 * Time: 15:06
 */
namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;

use App\Master\Pegawai;
use App\Transaksi\StrukKirim;
use App\Transaksi\KirimProduk;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\KartuStok;



class PaketObatController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getDataComboTransfer(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $dataPenulis = Pegawai::where('statusenabled',true)
            ->where('kpdrofile', $kdProfile)
            ->where('objectjenispegawaifk',1)
            ->get();
        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }

        $dataSatuan = \DB::table('satuanstandar_m as ss')
            ->where('ss.kpdrofile', $kdProfile)
            ->where('ss.statusenabled', true)
            ->orderBy('ss.satuanstandar')
            ->get();

        $dataKondisi = \DB::table('kondisiproduk_m as ss')
            ->where('ss.kpdrofile', $kdProfile)
            ->where('ss.statusenabled', true)
            ->orderBy('ss.kondisiproduk')
            ->get();

//        $dataSigna = \DB::table('stigma as st')
//            ->select('st.id','st.name')
//            ->orderBy('st.name')
//            ->get();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id','ru.namaruangan')
            ->where('mlu.kpdrofile', $kdProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $dataRuanganall = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
//            ->where('ru.objectdepartemenfk',14)
            ->where('ru.kpdrofile', $kdProfile)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();

//        $dataJenisKemasan = \DB::table('jeniskemasan_m as jk')
//            ->select('jk.id','jk.jeniskemasan')
//            ->where('jk.statusenabled',true)
//            ->get();
//        $dataJenisRacikan = \DB::table('jenisracikan_m as jk')
//            ->select('jk.id','jk.jenisracikan')
//            ->where('jk.statusenabled',true)
//            ->get();

        foreach ($dataRuangan as $lalala){
            $ruFilter[]=array($lalala->id);
        }
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.kdproduk as kdsirs','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.kpdrofile', $kdProfile)
            ->where('pr.statusenabled',true)
//            ->where('jp.id',97)
            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','pr.kdproduk','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->JOIN('stokprodukdetail_t as spd','spd.objectasalprodukfk','=','ap.id')
            ->select('ap.id','ap.asalproduk')
            ->where('ap.kpdrofile', $kdProfile)
            ->where('ap.statusenabled',true)
            ->orderBy('ap.id')
            ->groupBy('ap.id','ap.asalproduk')
            ->get();

//        $dataRoute = \DB::table('routefarmasi as rt')
//            ->select('rt.id','rt.name')
//            ->where('rt.statusenabled',true)
//            ->orderBy('rt.id')
//            ->get();
//        $dataKelas = \DB::table('kelas_m as rt')
//            ->select('rt.id','rt.namakelas')
//            ->where('rt.statusenabled',true)
//            ->orderBy('rt.id')
//            ->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kpdrofile', $kdProfile)
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
            );
        }

        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = :kdprofile and lu.id=:idLoginUser"),
            array(
                'kdprofile' => $kdProfile,
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        $result = array(
            'produk' => $dataProdukResult,
            'penulisresep' =>   $dataPenulis2,
            'ruangan' => $dataRuangan,
            'ruanganall' => $dataRuanganall,
//            'jeniskemasan' => $dataJenisKemasan,
            'asalproduk' => $dataAsalProduk,
            'satuan' => $dataSatuan,
//            'route' => $dataRoute,
//            'kelas' => $dataKelas,
//            'signa' => $dataSigna,
//            'jenisracikan' => $dataJenisRacikan,
            'kondisiproduk' =>$dataKondisi,
            'detaillogin' => $dataPegawaiUser,
            'message' => 'as@epic',
//            'asdas' => $ruFilter,
        );

        return $this->respond($result);
    }
    public function getRuanganFromAPD(Request $request) {
//        $dataLogin = $request->all();
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataRuangan = \DB::table('antrianpasiendiperiksa_t as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id','ru.namaruangan')
            ->where('mlu.kdprofile', $kdProfile)
            ->where('mlu.norec',$request['norec_apd'])
            ->get();

        $result = array(
//            'datalogin' => $dataLogin,
            'data' => $dataRuangan[0],
            'message' => 'as@epic'
        );

        return $this->respond($result);
    }
    public function getDetailKirimBarang(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->select('ap.id','ap.asalproduk')
            ->where('ap.kdprofile', $kdProfile)
            ->get();
        $dataSigna = \DB::table('stigma as st')
            ->select('st.id','st.name')
            ->where('st.kdprofile', $kdProfile)
            ->get();
        $dataStruk = \DB::table('strukkirim_t as sr')
            ->JOIN('pegawai_m as pg','pg.id','=','sr.objectpegawaipengirimfk')
            ->JOIN('ruangan_m as ru','ru.id','=','sr.objectruanganfk')
            ->JOIN('ruangan_m as ru2','ru2.id','=','sr.objectruangantujuanfk')
            ->select('sr.nokirim','pg.id as pgid','pg.namalengkap','ru.id','ru.namaruangan','ru2.id as ruid2','ru2.namaruangan as namaruangan2',
                'sr.jenispermintaanfk','sr.tglkirim','sr.keteranganlainnyakirim as keterangan')
            ->where('sr.kdprofile', $kdProfile);

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
            ->where('sp.kdprofile', $kdProfile);

        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norec']);
        }
        $data = $data->get();
//        return $this->respond($data);

        $pelayananPasien=[];
        $i = 0;
        $dataStok = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2 as hargajual,spd.harganetto2 as harganetto,spd.hargadiscount,
                    sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk
                    from stokprodukdetail_t as spd
                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                    where spd.kdprofile = :kdProfile and spd.objectruanganfk =:ruanganid
                    group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2,spd.hargadiscount,
                    spd.objectruanganfk
                    order By sk.tglstruk"),
            array(
                'ruanganid' => $dataStruk->id,
                'kdProfile' => $kdProfile
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
        $kdProfile = (int)$this->getDataKdProfile($request);
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
                'ru2.id as ruidtujuan','ru2.namaruangan as ruangantujuan','sr.tglorder','sr.jenispermintaanfk')
            ->where('sr.kdprofile', $kdProfile);

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
            ->where('sp.kdprofile', $kdProfile);;

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norecOrder']);
        }
        $data = $data->get();

        $details=[];
        $i = 0;
        $dataStok = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                    sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk
                    from stokprodukdetail_t as spd
                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                    where spd.kdprofile = :kdProfile and spd.objectruanganfk =:ruanganid
                    group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2,spd.hargadiscount,
                    spd.objectruanganfk
                    order By sk.tglstruk"),
            array(
                'kdProfile' => $kdProfile,
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
//                        $asalproduk = $item2->objectasalprodukfk;
                        $jmlstok = (float)$item2->qtyproduk/(float)$item->nilaikonversi;
//                        $hargasatuan = $harganetto;//$item2->harganetto;
//                        $hargadiscount = $item->hargadiscount;
//                        $hargadiscount = $item2->hargadiscount;
//                        $total =(((float)$item->jumlah * ((float)$hargasatuan-(float)$hargadiscount))) ;
                        break;
                    }
                }
            }
            foreach ($dataAsalProduk as $item3){
                if ($asalprodukfk == $item3->id){
                    $asalproduk = $item3->asalproduk;
                }
            }
//            foreach ($dataSigna as $item4){
//                if ($item->aturanpakai == $item4->id){
//                    $aturanpakaifk = $item4->id;
//                }
//            }
            $details[] = array(
                'no' => $i,
//                'noregistrasifk' => '',
//                'tglregistrasi' => '',
//                'generik' => null,
                'hargajual' => $hargajual,
//                'jenisobatfk' => '',
//                'kelasfk' => '',
                'stock' => $jmlstok,
                'harganetto' => $harganetto,
                'nostrukterimafk' => $nostrukterimafk,
                'ruanganfk' => $item->objectruanganfk,
//                'rke' => $item->resepke,
//                'jeniskemasanfk' => $item->jkid,
//                'jeniskemasan' => $item->jeniskemasan,
//                'aturanpakaifk' => $aturanpakaifk,
//                'aturanpakai' => $item->aturanpakai,
//                'routefk' => 0,
//                'route' => '',
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
//                'dosis' => 1,
                'hargasatuan' => $hargasatuan,
                'hargadiscount' => $hargadiscount,
                'total' => $total ,//+$item->jasa,
//                'jmldosis' => (String)($item->jumlah/$item->nilaikonversi)/1 . '/' . (String)1,
//                'jasa' => $item->jasa,
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
    public function saveKirimBarang(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
//        return $this->respond(array(date($request['strukkirim']['tglkirim'])));
        if ($request['strukkirim']['jenispermintaanfk'] == 2) {
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 14, 'TRF-' . $this->getDateTime()->format('ym'), $kdProfile);
        }else{
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 14, 'AMP-' . $this->getDateTime()->format('ym'), $kdProfile);
        }
        if ($noKirim == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "NOKIRIM" => $noKirim,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }
        DB::beginTransaction();

        try{
            $ruanganAsal = DB::select(DB::raw("
                         select  ru.namaruangan
                         from ruangan_m as ru 
                        where ru.kdprofile = $kdProfile and ru.id=:id"),
                array(
                    'id' => $request['strukkirim']['objectruanganfk'],
                )
            );
            $strRuanganAsal ='';
            $strRuanganAsal = $ruanganAsal[0]->namaruangan;

            $ruanganTujuan = DB::select(DB::raw("
                         select  ru.namaruangan
                         from ruangan_m as ru 
                        where ru.kdprofile = $kdProfile and ru.id=:id"),
                array(
                    'id' => $request['strukkirim']['objectruangantujuanfk'],
                )
            );
            $strRuanganTujuan='';
            $strRuanganTujuan = $ruanganTujuan[0]->namaruangan;

            if ($request['strukkirim']['noreckirim'] == ''){
                if ($request['strukkirim']['norecOrder'] != ''){
                    $dataAing = StrukOrder::where('norec',$request['strukkirim']['norecOrder'])
                        ->where('kdprofile', $kdProfile)
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
                         where ru.kdprofile = $kdProfile and ru.id=(select objectruangantujuanfk from strukkirim_t where kdprofile = $kdProfile and norec = :norec)"),
                    array(
                        'norec' => $request['strukkirim']['noreckirim'],
                    )
                );
                $strNmRuanganStrukKirimSebelumnya='';
                $strIdRuanganStrukKirimSebelumnya='';
                $strNmRuanganStrukKirimSebelumnya = $ruanganStrukKirimSebelumnya[0]->namaruangan;
                $strIdRuanganStrukKirimSebelumnya = $ruanganStrukKirimSebelumnya[0]->id;
                //#1

                $dataSK = StrukKirim::where('norec',$request['strukkirim']['noreckirim'])->where('kdprofile', $kdProfile)->first();
                $getDetails = KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])
                    ->where('kdprofile', $kdProfile)
                    ->where('qtyproduk','>',0)
                    ->get();

                foreach ($getDetails as $item){
                    //PENGIRIM
                    $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
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
                        ->where('kdprofile', $kdProfile)
                        ->where('objectruanganfk',$request['strukkirim']['objectruanganfk'])
                        ->where('objectprodukfk',$item->objectprodukfk)
                        ->first();
                    StokProdukDetail::where('norec', $tambah->norec)
                        ->where('kdprofile', $kdProfile)
                        ->update([
                                'qtyproduk' => (float)$tambah->qtyproduk + (float)$item->qtyproduk]
                        );

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $kdProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = (float)$item->qtyproduk;
                    $newKS->keterangan = 'Ubah Kirim Barang, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strNmRuanganStrukKirimSebelumnya . ' No Kirim: ' .  $dataSK->nokirim;
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

                    if ($request['strukkirim']['jenispermintaanfk'] == 2) {
                        //PENERIMA

                        $dataSaldoAwalT = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
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
                                ->where('kdprofile', $kdProfile)
                                ->where('objectruanganfk', $strIdRuanganStrukKirimSebelumnya)
                                ->where('objectprodukfk', $item->objectprodukfk)
//                              ->where('qtyproduk','>',0)
                                ->first();
                            StokProdukDetail::where('norec', $kurang->norec)
                                ->where('kdprofile', $kdProfile)
                                ->update([
                                        'qtyproduk' => (float)$kurang->qtyproduk - (float)$item->qtyproduk]
                                );
//                            return $this->respond((float)$saldoAwalPenerima);
                            //## KartuStok
                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $kdProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = (float)$item->qtyproduk;
                            $newKS->keterangan = 'Ubah Terima Barang, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strNmRuanganStrukKirimSebelumnya . ' No Kirim: ' .  $dataSK->nokirim;
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
                KirimProduk::where('nokirimfk',$request['strukkirim']['noreckirim'])->where('kdprofile', $kdProfile)->delete();
            }

            $dataSK->kdprofile = $kdProfile;
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
            $dataSK->save();

            $norecSK = $dataSK->norec;

            foreach ($request['details'] as $item) {
                //cari satuan standar
                $satuanstandar = DB::select(DB::raw("
                     select  ru.objectsatuanstandarfk
                     from produk_m as ru 
                    where ru.kdprofile = $kdProfile and ru.id=:id"),
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
                        where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk 
                        --and qtyproduk > 0
                        "),
                        array(
                            'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                            'produkfk' => $item['produkfk'],
                        )
                    );
                    //PENERIMA
                    $dataSaldoAwalT = DB::select("select sum(qtyproduk) as qty from stokprodukdetail_t 
                        where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk",
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
                                $dataKP->kdprofile = $kdProfile;
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
                                    ->where('kdprofile', $kdProfile)
                                    ->update([
                                            'qtyproduk' => 0]
                                    );
                                $dataStok = StokProdukDetail::where('norec', $items->norec)
                                    ->where('kdprofile', $kdProfile)
                                    ->first();

                                $dataNewSPD = new StokProdukDetail;
                                $dataNewSPD->norec = $dataNewSPD->generateNewId();
                                $dataNewSPD->kdprofile = $kdProfile;
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
                                $dataKP->kdprofile = $kdProfile;
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
                                    ->where('kdprofile', $kdProfile)
                                    ->update([
                                            'qtyproduk' => (float)$saldoakhir]
                                    );

                                $dataStok = StokProdukDetail::where('norec', $items->norec)
                                    ->where('kdprofile', $kdProfile)
                                    ->first();

                                $dataNewSPD = new StokProdukDetail;
                                $dataNewSPD->norec = $dataNewSPD->generateNewId();
                                $dataNewSPD->kdprofile = $kdProfile;
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
                    $newKS->kdprofile = $kdProfile;
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
                    $newKS2->kdprofile = $kdProfile;
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
                        where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk > 0 "),
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
                            $dataKP->kdprofile = $kdProfile;
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
                                ->where('kdprofile', $kdProfile)
                                ->update([
                                        'qtyproduk' => 0]
                                );
                        }else{

                            $dataKP = new KirimProduk;
                            $dataKP->norec = $dataKP->generateNewId();
                            $dataKP->kdprofile = $kdProfile;
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
                            if(isset($item['qtyorder']) && $item['qtyorder']!="" && $item['qtyorder']!="undefined"){
                                $dataKP->qtyorder = $item['qtyorder'];
                            }
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
                                ->where('kdprofile', $kdProfile)
                                ->update([
                                        'qtyproduk' => (float)$saldoakhir]
                                );
                        }
                    }

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $kdProfile;
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
                    $newKS->save();
                }
                $dataSTOKDETAIL2[] = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec from stokprodukdetail_t 
                        where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk   "),
                    array(
                        'ruanganfk' => $request['strukkirim']['objectruangantujuanfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $dataSTOKDETAIL[] = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec from stokprodukdetail_t 
                        where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $kirim = KartuStok::where('ruanganfk',$request['strukkirim']['objectruanganfk'])
                    ->where('kdprofile', $kdProfile)
                    ->where('produkfk',$item['produkfk'])
                    ->get();
                $terima = KartuStok::where('ruanganfk',$request['strukkirim']['objectruangantujuanfk'])
                    ->where('kdprofile', $kdProfile)
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

}