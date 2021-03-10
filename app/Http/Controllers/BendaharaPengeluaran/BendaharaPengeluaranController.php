<?php
/**
 * Created by PhpStorm.
 * User: efan (ea@epic)
 * Date: 13/09/2019
 * Time: 13:15
 */
namespace App\Http\Controllers\BendaharaPengeluaran;
use App\Http\Controllers\ApiController;
use App\Traits\Valet;
use App\Transaksi\StrukBuktiPenerimaan;
use App\Transaksi\StrukBuktiPenerimaanCaraBayar;
use App\Transaksi\StrukBuktiPengeluaran;
use App\Transaksi\StrukBuktiPengeluaranCaraBayar;
use App\Transaksi\StrukClosing;
use App\Transaksi\StrukHistori;
use App\Transaksi\StrukPelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BendaharaPengeluaranController extends ApiController {
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
            ->where('cs.kdprofile', $idProfile)
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
                where lu.kdprofile = $idProfile and objectkelompokuserfk=:id and pg.statusenabled = true"),
            array(
                'id' => 20,
            )
        );

        $kelompokTransaksi= \DB::table('mapbkutokelompoktransaksi_m as mk')
            ->join('kelompoktransaksi_m as kt','kt.id','=','mk.kelompoktransaksifk')
            ->select('kt.id','kt.kelompoktransaksi as kelompokTransaksi','mk.bku')
            ->where('mk.kdprofile', $idProfile)
            ->where('mk.statusenabled',true)
            ->orderBy('kt.kelompoktransaksi');

        if (isset($request['keterangan']) && $request['keterangan'] != "" && $request['keterangan'] != "undefined") {
            $kelompokTransaksi = $kelompokTransaksi->where('mk.bku', '=', $request['keterangan']);
        }

        $kelompokTransaksi=$kelompokTransaksi ->get();

        $mataAnggaran= \DB::table('mataanggaran_t as m')
            ->select('m.mataanggaran','m.norec')
            ->where('m.kdprofile', $idProfile)
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

        $RuanganAll = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled',true)
            ->get();

        $asalProduk = \DB::table('asalproduk_m as lu')
            ->select('lu.id','lu.asalproduk as asalProduk')
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.statusenabled', true)
            ->get();

        $dataJabatan = \DB::table('jabatan_m as kp')
            ->select('kp.id','kp.namajabatan')
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled',true)
            ->orderBy('kp.namajabatan')
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
            'ruanganall' => $RuanganAll,
            'asalproduk' => $asalProduk,
            'jabatan' => $dataJabatan,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function GetDaftarPembayaran(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];

//        $Supplier = ' ';
//        if(isset($request['Supplier']) && $request['Supplier']!="" && $request['Supplier']!="undefined"){
//            $Supplier = " and rkn.namarekanan LIKE '%".$request['Supplier']."%'";
//        }
//
        $ScaraBayar = ' ';
        if (isset($request['ScaraBayar']) && $request['ScaraBayar']!="" && $request['ScaraBayar']!="undefined") {
            $ScaraBayar = " and sbc.carabayarfk = ".$request['ScaraBayar'];
        }

        $Skasir = ' ';
        if (isset($request['Skasir']) && $request['Skasir'] != "" && $request['Skasir'] != "undefined") {
            $Skasir = " and lu.objectpegawaifk =" .$request['Skasir'];
        }

        $noSBK = ' ';
        if (isset($request['noSBK']) && $request['noSBK'] != "" && $request['noSBK'] != "undefined") {
            $noSBK = "where sbk.nosbk LIKE '%".$request['noSBK']."%'";
        }

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
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $results =array();
        $data = DB::select(DB::raw("select sbk.norec as norec_sbk,sbk.tglsbk,sbk.nosbk,sbk.totaldibayar,sbk.objectkelompoktransaksifk,
			    kt.kelompoktransaksi,lu.objectpegawaifk,pg.namalengkap,sbc.carabayarfk,cb.carabayar
                from strukbuktipengeluaran_t as sbk
                inner join strukbuktipengeluarancarabayar_t as sbc on sbc.nosbkfk=sbk.norec
                inner join strukpelayanan_t as sp on sp.norec = sbk.nostrukfk
                inner join strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                left join rekanan_m as rkn on rkn.id = sp.objectrekananfk
                inner join kelompoktransaksi_m as kt on kt.id = sbk.objectkelompoktransaksifk
                inner join loginuser_s as lu on lu.id = sbk.objectpegawaipembayarfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = sbk.objectruanganfk
                left join carabayar_m as cb on cb.id = sbc.carabayarfk
                where sbk.kdprofile = $idProfile and sbk.objectkelompoktransaksifk=107 and sbk.tglsbk BETWEEN '$tglAwal' and '$tglAkhir'
                $Skasir
                $ScaraBayar
                $noSBK
                GROUP BY sbk.norec,sbk.nosbk,sbk.tglsbk,sbk.objectkelompoktransaksifk,kt.kelompoktransaksi,
				sbk.totaldibayar,lu.objectpegawaifk,pg.namalengkap,sbc.carabayarfk,cb.carabayar;"));

        $result = array(
            'daftar' => $data,
            'datalogin' => $dataPegawaiUser,
            'message' => 'Cepot',
            'str' => $strRuangan,
        );
        return $this->respond($result);
    }

    public function GetDaftarTagihanSuplier(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];

        $Supplier = ' ';
        if(isset($request['Supplier']) && $request['Supplier']!="" && $request['Supplier']!="undefined"){
            $Supplier = " and rkn.namarekanan LIKE '%".$request['Supplier']."%'";
        }

        $NoFaktur = ' ';
        if (isset($request['NoFaktur']) && $request['NoFaktur']!="" && $request['UserId']!="undefined") {
            $NoFaktur = " and sp.nofaktur LIKE '%".$request['NoFaktur']."%'";
        }

        $NoStruk = ' ';
        if (isset($request['NoStruk']) && $request['NoStruk'] != "" && $request['NoStruk'] != "undefined") {
            $NoStruk = " and sp.nostruk LIKE '%".$request['NoStruk']."%'";
        }

        $status = ' ';
        if (isset($request['status']) && $request['status'] != "" && $request['status'] != "undefined") {
            $statusTea=$request['status'];
            $status =  'where su.status = '."'$statusTea'";
        }

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
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $results = array();
        $data = DB::select(DB::raw("select * from(select xx.norec,xx.tglstruk,xx.tgldokumen,xx.tgljatuhtempo,xx.rknid,xx.namarekanan,xx.nostruk,xx.nodokumen,xx.nopo,
                 xx.nosbk,xx.noverifikasi,xx.total,xx.totalppn,xx.totaldiskon,xx.subtotal,xx.sisautang,
                 (CASE WHEN xx.totalbayar = 0 THEN 'BELUM LUNAS' 
                 WHEN xx.totalbayar <> 0 and xx.totalbayar > 0 and xx.sisautang <> 0 THEN 'BELUM LUNAS' 
                 WHEN xx.totalbayar = xx.subtotal OR xx.sisautang = 0 THEN 'LUNAS' ELSE '' END) as status,xx.tglsbk
                 FROM(SELECT x.norec,x.tglstruk,x.tgldokumen,x.tgljatuhtempo,x.rknid,x.namarekanan,x.nostruk,x.nodokumen,x.nopo,
                 (x.totalppn*x.qty) as totalppn,
                 (x.totaldiskon*x.qty) as totaldiskon,
                 (x.total*x.qty) as total,
                 ((x.total+x.totalppn-x.totaldiskon)*x.qty) as subtotal,
                 x.totaldibayar as totalbayar,
                 (case when x.totalsisahutang is null then ((x.total+x.totalppn-x.totaldiskon)*x.qty) ELSE
				 x.totalsisahutang end) as sisautang,
                 x.nosbk,x.noverifikasi,x.tglsbk
                 FROM (SELECT sp.norec,sp.tglstruk,sp.tglfaktur as tgldokumen,sp.nostruk,sp.nosppb as nopo,sp.nofaktur as nodokumen,
                 SUM(spd.qtyproduk) as qty,SUM(spd.hargasatuan) as total,
                 SUM(spd.hargappn) as totalppn,SUM(spd.hargadiscount) as totaldiskon,
                 CASE WHEN sbk.totaldibayar is null then 0 else sbk.totaldibayar end as totaldibayar,
                 CASE WHEN sbk.totalsudahdibayar is null then 0 else sbk.totalsudahdibayar end as totalsudahdibayar,	
                 sbk.nosbk,sv.noverifikasi,sp.tgljatuhtempo,rkn.id as rknid,rkn.namarekanan,sbk.totalsisahutang,sbk.tglsbk
                 from strukpelayanan_t as sp
                 inner join strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                 left join rekanan_m as rkn on rkn.id = sp.objectrekananfk
                 left join strukbuktipengeluaran_t as sbk on sbk.norec = sp.nosbklastfk
                 left join strukverifikasi_t as sv on sv.norec = spd.noverifikasifk
                 left join ruangan_m as ru on ru.id = sv.objectruanganfk
                 where sp.kdprofile = $idProfile and sp.objectkelompoktransaksifk=35 and sp.tglfaktur BETWEEN '$tglAwal' and '$tglAkhir'
                 $Supplier
                 $NoFaktur
                 $NoStruk      
                 GROUP BY sp.norec,sp.tglstruk,sp.tglfaktur,sp.nostruk,sp.nosppb,sp.nofaktur,sp.nosbklastfk,sbk.totaldibayar,
                          sbk.totalsudahdibayar,sp.tgljatuhtempo,rkn.id,rkn.namarekanan,sbk.nosbk,sv.noverifikasi,sbk.totalsisahutang,sbk.tglsbk) as x) as xx) as su
                 $status;"
        ));

        foreach ($data as $item) {
            $results[] = array(
                'tglstruk' => $item->tglstruk,
                'tgldokumen' => $item->tgldokumen,
                'tgljatuhtempo' => $item->tgljatuhtempo,
                'rknid' => $item->rknid,
                'namarekanan' => $item->namarekanan,
                'nostruk' => $item->nostruk,
                'nodokumen' => $item->nodokumen,
                'nopo' => $item->nopo,
                'nosbk' => $item->nosbk,
                'noverifikasi' => $item->noverifikasi,
                'total' => $item->total,
                'totalppn' => $item->totalppn,
                'totaldiskon' => $item->totaldiskon,
                'subtotal' => $item->subtotal,
                'sisautang' => $item->sisautang,
                'status' => $item->status,
                'norec' => $item->norec,
                'tglsbk' => $item->tglsbk
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataPegawaiUser,
            'message' => 'ea@epic',
            'str' => $strRuangan,
        );
        return $this->respond($result);
    }

    public function GetDetailTagihanSupplier (Request $request) {
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
        $NoStrukFk=$request['NoStrukFk'];

        $data = DB::select(DB::raw("select spd.norec,pro.id as kdproduk,pro.kdproduk as kdsirs,pro.namaproduk,ss.id as ssid,ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargappn,
			    spd.hargadiscount,((spd.hargasatuan+spd.hargappn-spd.hargadiscount)*spd.qtyproduk) as subtotal,asp.id as aspid,asp.asalproduk
                from strukpelayanan_t as sp
                inner join strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                inner join produk_m as pro on pro.id = spd.objectprodukfk
                left join asalproduk_m as asp on asp.id = spd.objectasalprodukfk
                left join satuanstandar_m as ss on ss.id = spd.objectsatuanstandarfk
                left join rekanan_m as rkn on rkn.id = sp.objectrekananfk
                left join strukbuktipengeluaran_t as sbk on sbk.norec = sp.nosbklastfk
                left join strukverifikasi_t as sv on sv.norec = spd.noverifikasifk
                left join ruangan_m as ru on ru.id = sv.objectruanganfk
                where sp.kdprofile = $idProfile and spd.nostrukfk='$NoStrukFk'
                order by pro.namaproduk asc;"
        ));
        $result = array(
            'data' => $data,
            'dataPegawaiUser' => $dataPegawaiUser,
            'message' => 'Mr.Cepot '
        );
        return $this->respond($result);
    }

    public function GetRiwayatPembayaran (Request $request) {
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
            ->where('mlu.kdprofile', $idProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }
        $NoStruk=$request['NoTerima'];
        $NoFaktur=$request['NoFaktur'];

        $data = DB::select(DB::raw("select sp.norec as norec_sp,sbk.norec as norec_sbk,sp.nostruk,sp.nofaktur as nodokumen,sbk.nosbk,
                sp.tglstruk,sp.tglfaktur as tgldokumen,sbk.tglsbk,sbk.pembayaranke,sp.tgljatuhtempo,
                SUM(spd.qtyproduk) as qty,SUM(spd.hargasatuan) as total,
                SUM(spd.hargappn) as totalppn,SUM(spd.hargadiscount) as totaldiskon,
                ((SUM(spd.hargasatuan)+ SUM(spd.hargappn)-SUM(spd.hargadiscount))*SUM(spd.qtyproduk)) as totalharusdibayar,
                coalesce(sbk.totalsisahutang,0) as totalsisahutang,
                coalesce(sbk.totaldibayar,0) as totaldibayar,
                coalesce(sbk.totalsudahdibayar,0) as totalsudahdibayar,
                coalesce(sbk.totaldibayarbefore,0) as totaldibayarbefore
                from strukbuktipengeluaran_t as sbk
                left join strukpelayanan_t as sp on sp.norec = sbk.nostrukfk
                left join strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                left join rekanan_m as rkn on rkn.id = sp.objectrekananfk
                left join kelompoktransaksi_m as kt on kt.id = sbk.objectkelompoktransaksifk
                left join pegawai_m as pg on pg.id = sbk.objectpegawaipembayarfk
                left join ruangan_m as ru on ru.id = sbk.objectruanganfk
                where sbk.kdprofile = $idProfile and sp.nostruk='$NoStruk' and sp.nofaktur='$NoFaktur'
                GROUP BY sp.norec,sbk.norec,sp.nostruk,sp.nofaktur,sbk.nosbk,
                         sp.tglstruk,sp.tglfaktur,sbk.tglsbk,sbk.pembayaranke,
                         sp.tgljatuhtempo,sbk.totalsisahutang,sbk.totaldibayar,
                         sbk.totalsudahdibayar,sbk.totaldibayarbefore;"
        ));

        $result = array(
            'data' => $data,
            'dataPegawaiUser' => $dataPegawaiUser,
            'message' => 'ea@epic'
        );
        return $this->respond($result);
    }

    public function getDaftarPenerimaanKasKecil(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataAll = $request->all();
        $dataLogin = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $data = \DB::table('strukpelayanan_t as sp')
            ->JOIN('strukpelayanandetail_t as spd', 'spd.nostrukfk', '=', 'sp.norec')
            ->leftJOIN('rekanan_m as rkn', 'rkn.id', '=', 'sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'sp.objectpegawaipenerimafk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'sp.objectruanganfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk', 'sbk.norec', '=', 'sp.nosbklastfk')
            ->select('sp.nostruk_intern as nokk','sp.tglstruk','sp.nostruk', 'rkn.namarekanan', 'pg.namalengkap', 'sp.nokontrak',
                'ru.namaruangan', 'sp.norec', 'sp.nofaktur', 'sp.tglfaktur', 'sp.totalharusdibayar', 'sbk.nosbk',
                'sp.nosppb', 'sp.noorderfk', 'sp.qtyproduk'
            )
            ->where('sp.kdprofile',$idProfile)
            ->groupBy('sp.tglstruk','sp.nostruk_intern','sp.nostruk', 'rkn.namarekanan', 'pg.namalengkap', 'sp.nokontrak', 'ru.namaruangan', 'sp.norec', 'sp.nofaktur',
                'sp.tglfaktur', 'sp.totalharusdibayar', 'sbk.nosbk', 'sp.nosppb', 'sp.noorderfk', 'sp.qtyproduk');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sp.tglfaktur', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('sp.tglfaktur', '<=', $tgl);
        }
        if (isset($request['nostruk']) && $request['nostruk'] != "" && $request['nostruk'] != "undefined") {
            $data = $data->where('sp.nostruk_intern', 'ilike', '%' . $request['nostruk']);
        }
        if (isset($request['namarekanan']) && $request['namarekanan'] != "" && $request['namarekanan'] != "undefined") {
            $data = $data->where('rkn.namarekanan', 'ilike', '%' . $request['namarekanan'] . '%');
        }
        if (isset($request['nofaktur']) && $request['nofaktur'] != "" && $request['nofaktur'] != "undefined") {
            $data = $data->where('sp.nofaktur', 'ilike', '%' . $request['nofaktur'] . '%');
        }
        if (isset($request['AsalProduk']) && $request['AsalProduk'] != "" && $request['AsalProduk'] != "undefined") {
            $data = $data->where('spd.objectasalprodukfk', '=', $request['AsalProduk']);
        }
        $data = $data->where('sp.statusenabled', true);
        $data = $data->where('sp.objectkelompoktransaksifk', 35);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();

        foreach ($data as $item) {
            $details = DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.hargasatuan,spd.hargadiscount,
                    spd.hargappn,((spd.hargasatuan-spd.hargadiscount+spd.hargappn)*spd.qtyproduk) as total,spd.tglkadaluarsa,spd.nobatch
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
                'nokk' => $item->nokk,
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

    public function savePembayaranTagihanSuplier(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        \DB::beginTransaction();
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id as pgid,pg.namalengkap,lu.id as luid,lu.namauser from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );

        $datastruk = DB::select(DB::raw("select * from strukpelayanan_t where kdprofile = $idProfile and norec=:nostruk"),
            array(
                'nostruk' => $request['sbk']['nostruk'],
            )
        );
        $totaldibayarbefore=0;
        $totalsudahdibayar=0;
        $sisautang=0;
        try{
            foreach ($datastruk as $items) {
                if ($items->nosbklastfk == null || $items->nosbklastfk == ''){
                    $totaldibayarbefore=0;
                    $totalsudahdibayar=0;
                    $sisautang=(float)$request['sbk']['sisautang'] - (float)$request['sbk']['totalbayar'];
                }else{

                    $datasbk = DB::select(DB::raw("select * from strukbuktipengeluaran_t where kdprofile = $idProfile and norec=:nostruk"),
                        array(
                            'nostruk' => $items->nosbklastfk,
                        )
                    );
                    foreach ($datasbk as $Hits) {
                        $totaldibayarbefore=$Hits->totaldibayar;
                        $totalsudahdibayar=$Hits->totaldibayar;
                        $sisautang=(float)$Hits->totalsisahutang-(float)$request['sbk']['totalbayar'];
                    }
                }
                if($request['sbk']['nosbk'] == '') {
                    $dataSBK = new StrukBuktiPengeluaran();
                    $dataSBK->norec = $dataSBK->generateNewId();
                    $dataSBK->kdprofile = $idProfile;
                    $dataSBK->statusenabled = true;
                    $dataSBK->nosbk = $this->generateCode(new StrukBuktiPengeluaran(), 'nosbk', 14, 'PV-'.$this->getDateTime()->format('ym'),$idProfile);
                }else {
                    $dataSBK = StrukBuktiPengeluaran::where('norec', $request['sbk']['nosbk'])->where('kdprofile', $idProfile)->first();

                    $delSBKCB = StrukBuktiPengeluaranCaraBayar::where('nosbkfk', $request['sbk']['nosbk'])->where('kdprofile', $idProfile)
                        ->delete();
                }
                $dataSBK->keteranganlainnya = "Pembayaran Tagihan Supplier";
                $dataSBK->objectkelompoktransaksifk =  $request['sbk']['kelompoktransaksi'];
//            $dataSBK->objectpegawaipenerimafk  = $this->getCurrentLoginID();
                $dataSBK->tglsbk  = $request['sbk']['tglsbk'];
                $dataSBK->nostrukfk = $request['sbk']['nostruk'];
                $dataSBK->objectpegawaipembayarfk = $request['userData']['id'];
                $dataSBK->namapegawaipenerima = $request['sbk']['pemilikrekanan'];
                $dataSBK->namapegawaipembayar = $request['sbk']['pemilik'];
                $dataSBK->totaldibayar  = $request['sbk']['totalbayar'];
                $dataSBK->keterangan = $request['sbk']['keterangan'];
                $dataSBK->totaldibayarbefore = $totaldibayarbefore;
                $dataSBK->totalsudahdibayar = $totalsudahdibayar;
                $dataSBK->totalsisahutang=$sisautang;
                $dataSBK->save();
                $dataSBKNorec = $dataSBK->norec;
                $dataNoSBK =$dataSBK->nosbk;

                if ($dataSBKNorec != ''){
                    $SBPCB = new StrukBuktiPengeluaranCaraBayar();
                    $SBPCB->norec = $SBPCB->generateNewId();
                    $SBPCB->kdprofile= $idProfile;
                    $SBPCB->statusenabled = true;
                    $SBPCB->nosbkfk = $dataSBKNorec;
                    $SBPCB->namapemilik = $request['sbk']['pemilikrekanan'];
                    $SBPCB->nokartuaccount = $request['sbk']['rekeningrekanan'];
                    $SBPCB->namabank = $request['sbk']['bankrekanan'];
                    $SBPCB->carabayarfk = $request['sbk']['carabayar'];
                    $SBPCB->nourutcb = 0;
                    $SBPCB->pegawaipembayarfk = $request['userData']['id'];
                    $SBPCB->totaldibayar = $request['sbk']['totalbayar'];
                    $SBPCB->totaldibayarcashin = $request['sbk']['totalbayar'];
                    $SBPCB->save();

                    StrukPelayanan::where('norec', $request['sbk']['nostruk'])
                        ->where('kdprofile', $idProfile)
                        ->update([
                                'nosbklastfk' => $dataSBKNorec]
                        );
                }
            }


            $transStatus = 'true';
        } catch (Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Pembayaran Berhasil Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Pembayaran Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "norecsbk" => $dataSBKNorec,
                "nosbk" => $dataNoSBK,
                "as" => 'Cepot',
            );
        } else {
            $transMessage = "Simpan Pembayaran Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "norecsbk" => $dataSBKNorec,
                "nosbk" => $dataNoSBK,
                "as" => 'Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function daftarBKUPengeluaran(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $listTrans =  explode ( ',',$this->settingDataFixed('KdTransaksiBendaharaPengeluaran',$idProfile));
        $kdTrans = [];
        $KdTransak = $this->settingDataFixed('KdTransaksiBendaharaPengeluaran',$idProfile);
        foreach ($listTrans as $itemTrans){
            $kdTrans []=  (int)$itemTrans;
        }
        $kdSetoranBendPengeluaran = $this->settingDataFixed('KdTransaksiSetoranHarianBendaharaPengeluaran',$idProfile);
        $filter = $request->all();
        $dataPenerimaanBank= \DB::table('strukhistori_t as sh')
            ->join('strukclosing_t as sc', 'sc.norec', '=', 'sh.noclosing')
            ->leftjoin('strukbuktipenerimaan_t as spp', 'spp.noclosingfk', '=','sc.norec',
                        \DB::raw("sc.norec and spp.objectkelompoktransaksifk in ($KdTransak)"))
            ->leftjoin('strukbuktipengeluaran_t as sbk', 'sbk.noclosingfk', '=', 'sc.norec')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'spp.objectpegawaipenerimafk')
            ->leftjoin('loginuser_s as lu2', 'lu2.id', '=', 'sbk.objectpegawaipembayarfk')
            ->leftjoin('pegawai_m as p', 'p.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pegawai_m as p2', 'p2.id', '=', 'lu2.objectpegawaifk')
            ->leftjoin('pegawai_m as psetor', 'psetor.id', '=', 'sh.objectpegawaitarikdepositfk')
            ->join('kelompoktransaksi_m as kt', 'kt.id', '=', 'sh.objectkelompoktransaksifk')
            ->join('mapbkutokelompoktransaksi_m as mbk','mbk.kelompoktransaksifk','=','kt.id')
            ->leftjoin('strukbuktipenerimaancarabayar_t as spc', 'spc.nosbmfk', '=', 'spp.norec')
            ->leftjoin('bankaccount_m as ba', 'ba.id', '=', 'spc.objectbankaccountfk')
            ->select('spp.norec', 'spp.tglsbm', 'spp.keteranganlainnya','spp.nosbm_intern',
                'spp.objectpegawaipenerimafk','p.namalengkap', 'kt.kelompoktransaksi','spp.nostrukfk',
                'sc.objectkelompoktransaksifk', 'spc.objectbankaccountfk','ba.bankaccountnama','spc.namabankprovider','spc.namapemilik',
                'sc.noclosing','sh.nonhistori','sbk.objectpegawaipembayarfk','p2.namalengkap as pegawaibayar',
                'sh.ketlainya','sh.norec as norec_sh','sh.kdperkiraan','sh.namaperkiraan','sh.kettransaksi','sh.nobukti','psetor.namalengkap as penyetor',
                DB::raw(" 
                    CASE WHEN sbk.objectkelompoktransaksifk = $kdSetoranBendPengeluaran THEN COALESCE ((sbk.totaldibayar), 0) ELSE COALESCE ((spp.totaldibayar), 0) END AS debit,
 	                CASE WHEN sbk.objectkelompoktransaksifk NOT IN ($kdSetoranBendPengeluaran) THEN COALESCE ((sbk.totaldibayar), 0) ELSE 0 END AS kredit,      
                    case when spp.nosbm is null then sbk.nosbk else spp.nosbm end as notransaksi,
                    cast(sh.tglsetortarikdeposit as date)as tglsetortarikdeposit"))
//        -- coalesce((spp.totaldibayar ), 0) debit ,
//                          -- coalesce((sbk.totaldibayar), 0) kredit ,
            ->groupBy('spp.norec','spp.tglsbm','spp.keteranganlainnya','spp.nosbm_intern','spp.objectpegawaipenerimafk','p.namalengkap',
                      'kt.kelompoktransaksi','spp.nostrukfk','sc.objectkelompoktransaksifk','spc.objectbankaccountfk','ba.bankaccountnama',
                      'spc.namabankprovider','spc.namapemilik','sc.noclosing','sh.nonhistori','sbk.objectpegawaipembayarfk','p2.namalengkap',
                      'sh.ketlainya','sh.norec','sh.kdperkiraan','sh.namaperkiraan','sh.kettransaksi','sh.nobukti','psetor.namalengkap',
                      'spp.totaldibayar','sbk.totaldibayar','spp.nosbm','sbk.nosbk','sh.tglsetortarikdeposit','sc.tglclosing','sbk.objectkelompoktransaksifk')
            ->orderBy('sc.tglclosing','asc')
            ->where('sh.kdprofile',$idProfile)
            ->where('sh.statusenabled',true)
            ->whereIn('sh.objectkelompoktransaksifk',$kdTrans);

        $filter = $request->all();
        if(@$filter['noStruk']!="" && @$filter['noStruk']!="undefined"){
            $dataPenerimaanBank = $dataPenerimaanBank->where('spp.norec', $filter['noStruk']);
        }
        if(@$filter['jenisTransaksi']!="" && @$filter['jenisTransaksi']!="undefined"){
            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.objectkelompoktransaksifk', $filter['jenisTransaksi']);
        }
        if(@$filter['noRekening']!="" && @$filter['noRekening']!="undefined"){
            $dataPenerimaanBank = $dataPenerimaanBank->where('spc.namabankprovider', $filter['noRekening']);
        }
        if(@$filter['tglAwal']!=""){
            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.tglsetortarikdeposit','>=', $filter['tglAwal']." 00:00:00");
        }
        if(@$filter['tglAkhir']!=""){
            $tgl= $filter['tglAkhir']." 23:59:59";
            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.tglsetortarikdeposit','<=', $tgl);
        }
        if(@$filter['namaPerkiraan']!="" && @$filter['namaPerkiraan']!="undefined"){
            $dataPenerimaanBank = $dataPenerimaanBank->where('sh.namaperkiraan', $filter['namaPerkiraan']);
        }
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
        $tglMinSabulan = $arr1 . '-' . $arr2 . '-' . $arr3;
        /** @End_Function */

        $tglAwal= $filter['tglAwal'];
        $dataSaldo= DB::select(DB::raw(" select sh.tglsetortarikdeposit,
                     coalesce((spp.totaldibayar ), 0) debit ,
                     coalesce((sbk.totaldibayar ), 0) kredit
                     from strukhistori_t as sh
                     left join strukclosing_t as sc on sc.noclosing = sh.noclosing
                     left join strukbuktipenerimaan_t as spp on spp.noclosingfk = sc.norec
                     left join strukbuktipengeluaran_t as sbk on sbk.noclosingfk = sc.norec
                     inner join kelompoktransaksi_m as kt on kt.id = sh.objectkelompoktransaksifk
                     inner join mapbkutokelompoktransaksi_m as mbk on mbk.kelompoktransaksifk = kt.id
                     where sh.kdprofile = $idProfile and sh.tglsetortarikdeposit > '$tglMinSabulan' and sh.tglsetortarikdeposit < '$tglAwal'
                       and sh.statusenabled = true
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
            if ($dataPenerimaan->debit == 0) {
                $saldo = $saldo + (float)$dataPenerimaan->debit - (float)$dataPenerimaan->kredit;
            } else {
                $saldo = $saldo + (float)$dataPenerimaan->debit;
            }
            $jumlahD = $jumlahD + (float)$dataPenerimaan->debit;
            $jumlahK = $jumlahK +  (float)$dataPenerimaan->kredit;
            $saldoAkhir = $saldo;

            $result[] = array(
                'norec_sh'  =>$dataPenerimaan->norec_sh,
                'noStruk'  =>$dataPenerimaan->norec,
                'tglStruk'  =>$dataPenerimaan->tglsetortarikdeposit,
                'keterangan'  =>$dataPenerimaan->ketlainya,
                'jenisTransaksi'  =>$dataPenerimaan->kelompoktransaksi,
                'idJenisTransaksi'  =>$dataPenerimaan->objectkelompoktransaksifk,
                'kredit'  => $dataPenerimaan->kredit,
                'debit'  => $dataPenerimaan->debit,
                'saldo'  => $saldo,
                'idPegawai'  =>$dataPenerimaan->objectpegawaipenerimafk,
                'namaPegawai' => $dataPenerimaan->namalengkap,
                'nostrukfk' => $dataPenerimaan->nostrukfk,
                'objectbankaccountfk' => $dataPenerimaan->objectbankaccountfk,
                'bankaccountnama' => $dataPenerimaan->bankaccountnama,
                'namabankprovider' => $dataPenerimaan->namabankprovider,
                'namapemilik' => $dataPenerimaan->namapemilik,
                'nohistori' => $dataPenerimaan->nonhistori,
                'notransaksi' => $dataPenerimaan->notransaksi,
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
//        return $this->respond($dataSaldo);
    }

    public function getDataComboBk(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();
        $dataLog= \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('pg.id','pg.namalengkap')
            ->where('lu.kdprofile',$idProfile)
            ->where('pg.statusenabled',true)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->orderBy('pg.namalengkap')
            ->get();
        $kelompokTransaksi= \DB::table('mapbkutokelompoktransaksi_m as mp')
            ->join('bku_m as bku','bku.id','=','mp.idbku')
            ->join('kelompoktransaksi_m as kt','kt.id','=','mp.kelompoktransaksifk')
            ->select('mp.id','mp.kelompoktransaksifk','mp.idbku','bku.bku','kt.kelompoktransaksi')
            ->where('mp.kdprofile',$idProfile)
            ->where('mp.statusenabled',true);

        if (isset($request['keterangan']) && $request['keterangan'] != "" && $request['keterangan'] != "undefined") {
            $kelompokTransaksi = $kelompokTransaksi->where('bku.bku', '=', $request['keterangan']);
        }
        $kelompokTransaksi=$kelompokTransaksi ->get();


        return $this->respond(array(
            'datalogin'=>$dataLog,
            'mapkelompoktransaksi'=>$kelompokTransaksi,
            'by'=>'as@epic',
        ));
    }

    public function simpanBKUBk(Request $request){
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
                ->where('mlu.kdprofile',$idProfile)
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
            $SC->noclosing = $this->generateCode(new StrukClosing, 'noclosing', 10, 'C-' . $this->getDateTime()->format('ym'),$idProfile);
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
            $nohistori = $this->generateCode(new StrukHistori(), 'nonhistori', 14, 'BKU-' . $this->getDateTime()->format('ym'),$idProfile);
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
                $strukBuktiPenerimanan->nosbm = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-' . $this->getDateTime()->format('ym'),$idProfile);
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
                $SBK->nosbk = $this->generateCode(new StrukBuktiPengeluaran(), 'nosbk', 14, 'PV-' . $this->getDateTime()->format('ym'),$idProfile);
                $SBK->noclosingfk = $SC->norec;
                $SBK->asalprodukfk = $input['sumberdana'];
                $SBK->save();

            }

            $transStatus = true;
        }catch(\Exception $e){
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
}