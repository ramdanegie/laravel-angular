<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 13/08/2019
 * Time: 4:14 PM
 */


namespace App\Http\Controllers\Registrasi;
use App\Http\Controllers\ApiController;
use App\Transaksi\DaftarPermintaanLabel;
use Illuminate\Http\Request;
use DB;
use App\Traits\Valet;

class OrderLabelController extends  ApiController
{

    use Valet;
    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getPasienDaftarByNoreg( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->Join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->Join('alamat_m as al','al.nocmfk','=','ps.id')
            ->Join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->Join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->select('pd.noregistrasi','ps.nocm','ps.namapasien','al.alamatlengkap',
                'ps.tgllahir','apd.objectruanganfk','ru.namaruangan','apd.norec as norec_apd')
            ->where('pd.kdprofile', $idProfile);
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };
        $data=$data->get();
        $result = array(
            'data' => $data,
            'message' => 'egie@',
        );
        return $this->respond($result);
    }

    public function saveOrderLabel(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
//        try{

//            if ($request['permintaanlabeldetail']['0']['norec_apd']==''){


//            }else{
//                $dataZ =  DaftarPermintaanLabel::where('noregistrasifk',$request['permintaanlabeldetail']['0']['norec_apd'])->first();
//            }

        $r_pl = $request['permintaanlabel']['permintaanlabeldetail'];
        foreach ($r_pl as $item){
//           if ($item['norec_apd']=='') {
               $newDPL = new DaftarPermintaanLabel();
               $norec = $newDPL->generateNewId();
               $newDPL->norec = $norec;
               $newDPL->kdprofile = $idProfile;
//            }else{
//                $newDPL =  DaftarPermintaanLabel::where('noregistrasifk',$item['norec_apd'])->first();
//
//           }
               $newDPL->noregistrasifk = $item['norec_apd'];
               $newDPL->pegawaiorder = $request['permintaanlabel']['pegawaiorderM'];
               $newDPL->objectpegawaiorderfk = $request['permintaanlabel']['pegawaiorderA'];
               $newDPL->tglpermintaan = $request['permintaanlabel']['tglpermintaan'];
               $newDPL->qtyorder = $request['permintaanlabel']['qtyorder'];
               $newDPL->keterangan = $request['permintaanlabel']['keterangan'];
               $newDPL->objectstatusorderfk = '1';//menunggu
               $newDPL->save();
//           }
        }

//        try{
        $transStatus = 'true';

//        } catch (\Exception $e) {
//            $transStatus = 'false';
            $transMessage = "simpan Diagnosa Baru";
//        }

        if ($transStatus == 'true') {
            $transMessage = "Data Tersimpan";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'data' => $newDPL,
                'as' => 'egie@ramdan',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'data' =>$newDPL,
                'as' => 'egie@ramdan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteOrderLabel(Request $request) {
    $kdProfile = $this->getDataKdProfile($request);
    $idProfile = (int) $kdProfile;
    $dataLogin = $request->all();
    DB::beginTransaction();
    $r_pl = $request['permintaanlabel']['permintaanlabeldetail'];
    if ($r_pl['norec_apd'] != ''){
        try{
            $data1 = DaftarPermintaanLabel::where('noregistrasifk', $r_pl['norec_dp'])->where('kdprofile', $idProfile)->delete();
            $transStatus = 'true';
        }
        catch(\Exception $e){
            $transStatus= false;
        }
//        try{
//            $data2 = DiagnosaPasien::where('norec',$request['diagnosa']['norec_dp'])->delete();
//            $transStatus = 'true';
//        }
//        catch(\Exception $e){
//            $transStatus= false;
//        }

    }
    if ($transStatus='true')
    {    DB::commit();
        $transMessage = "Data Terhapus";
    }
    else{
        DB::rollBack();
        $transMessage = "Data Gagal Dihapus";
    }

    return $this->setStatusCode(201)->respond([], $transMessage);

}
    public function getDataCombo(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$idProfile));
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $kdJeniPegawaiDokter = (int) $this->settingDataFixed('KdJenisPegawaiDokter',$idProfile);
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $dataLogin = $request->all();
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile', $idProfile)
            ->whereIn('dp.id', $kdDepartemenRawatPelayanan)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
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
            ->select('kl.id', 'kl.namakelas')
            ->where('kl.statusenabled', true)
            ->where('kl.kdprofile', $idProfile)
            ->orderBy('kl.namakelas')
            ->get();

        $dataKamar = \DB::table('kamar_m as kmr')
            ->select('kmr.id', 'kmr.namakamar')
            ->where('kmr.statusenabled', true)
            ->where('kmr.kdprofile', $idProfile)
            ->orderBy('kmr.namakamar')
            ->get();
        $dataRuanganInap = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.objectdepartemenfk', $idDepRanap)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataPegawai = \DB::table('pegawai_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
//            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('ru.namalengkap')
            ->get();
        $dataStatusOrder = \DB::table('statusorderlabel_m as sto')
            ->select('sto.id','sto.status')
//            ->where('sto.kdprofile', $idProfile)
            ->orderBy('sto.id')
            ->get();

        $dataKasir= DB::select(DB::raw("select pg.id,pg.namalengkap,lu.id as luid from loginuser_s lu
                INNER JOIN pegawai_m pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and objectkelompokuserfk=:id;"),
            array(
                'id' => 20,
            )
        );

        $result = array(
            'departemen' => $dataDepartemen,
            'ruangan' => $dataRuangan,
            'ruanganinap' => $dataRuanganInap,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'datalogin' => $dataLogin,
            'kelas' => $dataKelas,
            'kamar' => $dataKamar,
//            'rekanan' => $dataRekanan,
            'pegawai' => $dataPegawai,
            'kasir'=>$dataKasir,
            'statusorder'=> $dataStatusOrder,
            'message' => 'as@egieramdan',
        );

        return $this->respond($result);
    }

    public function getDaftarPermintaan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
                ->join ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
                ->leftjoin ('pasien_m as ps','ps.id','=','pd.nocmfk')
                ->join ('daftarpermintaanlabel_t as dpl','dpl.noregistrasifk','=','apd.norec')
                ->leftjoin ('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
                ->leftjoin ('pegawai_m as pg','pg.id','=','dpl.objectpegawaiorderfk')
                ->leftjoin ('pegawai_m as pg2','pg2.id','=','dpl.objectpegawaipenerimafk')
                ->leftjoin ('statusorderlabel_m as sol','sol.id','=','dpl.objectstatusorderfk')
                ->select('dpl.norec','dpl.tglpermintaan','pd.noregistrasi','ps.nocm','ps.namapasien',
                    'ru.namaruangan','dpl.pegawaiorder','pg.namalengkap as pegawaiorder1' ,'dpl.pegawaipenerima',
                    'dpl.objectpegawaipenerimafk','dpl.objectpegawaiorderfk',
                    'pg2.namalengkap as pegawaipenerima1','dpl.qtyorder','dpl.qtydikerjakan',
                    'sol.status',
                    'dpl.keterangan')
                ->where('pd.kdprofile', $idProfile);
//            ->where('djp.objectjenisprodukfk', '<>', 97)
//            ->whereNull('sp.statusenabled');
//            ->where('ru.objectdepartemenfk', 18);


        $kelompokpasiens = array('1', '3', '5');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('dpl.tglpermintaan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('dpl.tglpermintaan', '<=', $tgl);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
        if (isset($request['idStatus']) && $request['idStatus'] != "" && $request['idStatus'] != "undefined") {
            $data = $data->where('sol.id', '=', $request['idStatus']);
        }
//        if(isset($filter['noreg']) && $filter['noreg']!="" && $filter['noreg']!="undefined"){
//            $data = $data->where('pd.noregistrasi','ilike','%'. $filter['noreg'].'%');
//        }
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined"){
            $data = $data->where('ps.nocm','ilike','%'. $request['norm']. '%');
        }
        if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
            $data = $data->where('ps.namapasien','ilike','%'. $request['nama'] .'%');
        }
//        $data = $data->orderBy('dpl.tglpermintaan', 'ASC');
//        $data = $data->distinct();
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'egie@epic'
        );
        return $this->respond($result);
    }
    public function simpanUpdatePenerimaLabel(Request $request){
        DB::beginTransaction();
        $transStatus = 'true';
        try{
//            $data= PasienDaftar::where('norec', $request['norec'])
//                ->update([
//                        'objectpegawaifk' => $request['objectpegawaifk']]
//                );
            $data2= DaftarPermintaanLabel::where('norec', $request['norec'])
                ->update([
                        'objectpegawaipenerimafk' => $request['objectpegawaipenerimafk'],
                        'tglambil' => $request['tglambil'],
                        'objectstatusorderfk' => '4' //diambil
                       ]);
            $transMessage = "Update Penerima Label Berhasil";
        }
        catch(\Exception $e){
            $transStatus = 'false';
            $transMessage = "Update Penerima Label Gagal";
        }

        if($transStatus != 'false'){
            DB::commit();
            $result = array(
                "status" => 201,
                "message" =>$transMessage,
            );
        }else{
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

}
