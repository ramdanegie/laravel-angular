<?php
/**
 * Created by PhpStorm.
 * SumberDayaManusiaController
 * User: Efan Andrian (ea@epic)
 * Date: 12/11/2019
 * Time: 09:30 PM
 */

namespace App\Http\Controllers\SDM;
use App\Master\JamKerja;
use App\Master\Paket;
use App\Master\Agama;
use App\Master\DiklatJurusan;
use App\Master\DiklatKategory;
use App\Master\Eselon;
use App\Master\GolonganPegawai;
use App\Master\HubunganKeluarga;
use App\Master\Jabatan;
use App\Master\JenisJabatan;
use App\Master\JenisKelamin;
use App\Master\JenisPegawai;
use App\Master\JenisProvider;
use App\Master\JurusanPeminatan;
use App\Master\KategoryPegawai;
use App\Master\KelompokJabatan;
use App\Master\KelompokUser;
use App\Master\KeluargaPegawai;
use App\Master\Pegawai;
use App\Master\PegawaiJadwalKerja;
use App\Master\Pekerjaan;
use App\Master\Pendidikan;
use App\Master\Ruangan;
use App\Master\SdmKedudukan;
use App\Master\SdmShiftKerja;
use App\Master\SettingDataFixed;
use App\Master\ShiftKerja;
use App\Master\StatusPegawai;
use App\Master\StatuspPerkawinanPegawai;
use App\Master\SubUnitKerja;
use App\Master\SuratKeputusan;
use App\Master\Tanggungan;
use App\Master\UnitKerjaPegawai;
use App\Transaksi\KegiatanPendidikan;
use App\Transaksi\ListTanggalCuti;
use App\Transaksi\MasaBerlakuSipStr;
use App\Transaksi\NomorTelphonePegawai;
use App\Transaksi\PesertaDidik;
use App\Transaksi\PlanningPegawaiStatus;
use App\Transaksi\RiwayatJabatan;
use App\Transaksi\RiwayatPelatihan;
use App\Transaksi\RiwayatPendidikan;
use App\Transaksi\SDM_AbsensiPegawai;
use App\Transaksi\TenagaPengajar;
use App\Transaksi\JadwalKerjaPegawai;
use Illuminate\Http\Request;
use DB;
use App\Transaksi\LoggingUser;
use Illuminate\Support\Facades\Storage;
use Response;
use App\Http\Controllers\ApiController;
use App\Traits\Valet;
use Carbon\Carbon;
use App\Master\MapPegawaiToUnit;

class SumberDayaManusiaController extends ApiController {

    use Valet;
    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getComboPegawaiSdm (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
//        $dataPegawai = \DB::table('loginuser_s as lu')
//            ->select('lu.objectpegawaifk','lu.objectkelompokuserfk')
//            ->where('lu.id',$dataLogin['userData']['id'])
//            ->first();
        $agama = Agama::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $jeniskelamin = JenisKelamin::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $UnitKerja = UnitKerjaPegawai::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $SubUnitKerja = SubUnitKerja::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $Ruangan = Ruangan::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $StatusPegawai = StatusPegawai::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $KategoryPegawai = KategoryPegawai::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $jabatan = Jabatan::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $jenisJabatan = JenisJabatan::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $GolonganPegawai = GolonganPegawai::where('statusenabled', true)
                            ->select(DB::raw("id, namaexternal || ' - ' || reportdisplay as golongan"))
                            ->where('kdprofile', $kdProfile)
                            ->get();
        $KelompokJabatan = KelompokJabatan::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $Pendidikan = Pendidikan::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $Eselon = Eselon::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $ShiftKerja = SdmShiftKerja::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $StatusKawin = StatuspPerkawinanPegawai::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $HubunganKeluarga = HubunganKeluarga::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $Pekerjaan = Pekerjaan::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $Tanggungan = Tanggungan::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $Kedudukan = SdmKedudukan::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $KelompokUser = KelompokUser::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $JenisPegawai = JenisPegawai::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $JenisProvider = JenisProvider::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();

        $PangkatPegawai = \DB::table('pangkat_m as pk')
            ->where('pk.kdprofile', $kdProfile)
            ->where('pk.statusenabled', true)
            ->orderBy('pk.namapangkat')
            ->get();
        $Shift = \DB::table('shiftkerja_m as pk')
            ->where('pk.kdprofile', $kdProfile)
            ->where('pk.statusenabled', true)
            ->orderBy('pk.namashift')
            ->get();

        $SettingUsiaPensiun = SettingDataFixed::where('statusenabled', true)
            ->select('nilaifield')
            ->where('kdprofile', $kdProfile)
            ->where('keteranganfungsi', 'ilike','%'.'Setting Usia Pensiun'.'%')
            ->first();
        $SettingPasswordAwal = SettingDataFixed::where('statusenabled', true)
            ->select('nilaifield')
            ->where('kdprofile', $kdProfile)
            ->where('namafield', '=','passworddefault')
            ->first();
        $DataUnitkerja=[];
        foreach ($UnitKerja as $item) {
            $detail = [];
            foreach ($SubUnitKerja as $item2) {
                if ($item->id == $item2->objectunitkerjapegawaifk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'name' => $item2->name,
                    );
                }
            }

            $DataUnitkerja[] = array(
                'id' => $item->id,
                'name' => $item->name,
                'subunit' => $detail,
            );
        }
        $DataJabatan=[];
        foreach ($jenisJabatan as $datas){
            $details=[];
            foreach ($jabatan as $data){
                if ($datas->id = $data->objectjenisjabatanfk){
                    $details[]=array(
                        'id' => $data->id,
                        'namajabatan' => $data->namajabatan
                    );
                }
            }
            $DataJabatan[] = array(
                'id' => $datas->id,
                'jenisjabatan' => $datas->jenisjabatan,
                'jabatan' => $details
            );
        }
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$kdProfile));
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id',$kdDepartemenRawatPelayanan)
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', $kdProfile)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $kdProfile)
            ->orderBy('ru.namaruangan')
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
            ->where('kp.kdprofile', $kdProfile)
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataJenispetugaspe = \DB::table('jenispetugaspelaksana_m as jpp')
            ->select('jpp.id', 'jpp.jenispetugaspe')
//            ->where('jpp.kdprofile', $kdProfile)
            ->where('jpp.statusenabled', true)
            ->orderBy('jpp.jenispetugaspe')
            ->get();

        $result = array(
            'datalogin'=> $dataLogin,
            'unitkerjapegawai' => $UnitKerja,
            'subunitkerja' => $SubUnitKerja,
            'dataunitkerja' => $DataUnitkerja,
            'statuspegawai' => $StatusPegawai,
            'kategorypegawai' => $KategoryPegawai,
            'jeniskelamin' => $jeniskelamin,
            'ruangan' => $Ruangan,
            'agama' => $agama,
            'jabatan' => $jabatan,
            'golonganpegawai' => $GolonganPegawai,
            'kelompokjabatan' => $KelompokJabatan,
            'pendidikan' =>$Pendidikan,
            'eselon' => $Eselon,
            'shiftkerja' => $ShiftKerja,
            'statuskawin' => $StatusKawin,
            'usiapensiun' => $SettingUsiaPensiun,
            'hubungankeluarga' => $HubunganKeluarga,
            'pekerjaan' => $Pekerjaan,
            'tanggungan' => $Tanggungan,
            'datajabatan' => $DataJabatan,
            'kedudukan' => $Kedudukan,
            'kelompokuser' => $KelompokUser,
            'passwordawal' => $SettingPasswordAwal,
            'jenispegawai' => $JenisPegawai,
            'pangkatpegawai' => $PangkatPegawai,
            'shiftpegawai' => $Shift,
            'jenisprovider' => $JenisProvider,
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'jenispetugaspe' => $dataJenispetugaspe,
            'by' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarPegawai (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->leftJoin('agama_m as ag','ag.id','=','pg.objectagamafk')
            ->leftJoin('detailkategorypegawai_m as dkp','dkp.id','=','pg.objectdetailkategorypegawaifk ')
            ->leftJoin('golongandarah_m as gd','gd.id','=', 'pg.objectgolongandarahfk')
            ->leftJoin('jabatan_m as jb','jb.id','=','pg.objectjabatanfungsionalfk')
            ->leftJoin('jabatan_m as jb1','jb1.id', '=', 'pg.objectjabatanstrukturalfk')
            ->leftJoin('golonganpegawai_m as gp','gp.id','=', 'pg.objectgolonganpegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk','uk.id','=','pg.objectunitkerjapegawaifk')
            ->leftJoin('statuspegawai_m as sp','sp.id','=','pg.objectstatuspegawaifk')
            ->leftJoin('kelompokjabatan_m as kj','kj.id','=','pg.objectkelompokjabatanfk')
            ->leftJoin('statusperkawinanpegawai_m as spp','spp.id','=','pg.objectstatusperkawinanpegawaifk')
            ->leftJoin('kategorypegawai_m as kp','kp.id','=','pg.kategorypegawai')
            ->leftJoin('eselon_m as ese','ese.id','=','pg.objecteselonfk')
            ->leftJoin('pendidikan_m as pend','pend.id', '=','pg.objectpendidikanterakhirfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','pg.objectjeniskelaminfk')
            ->leftJoin('sdm_golongan_m as gol','gol.id','=','pg.objectgolonganfk')
            ->leftJoin('nilaikelompokjabatan_m as nkj','nkj.id','=','pg.objectkelompokjabatanfk')
            ->leftJoin('sdm_kelompokshift_m as ks','ks.id','=','pg.objectshiftkerja')
            ->leftJoin('sdm_kedudukan_m as kdd','kdd.id','=','pg.kedudukanfk')
            ->select(\DB::raw("pg.*,ag.agama,dkp.detailkategorypegawai,gd.golongandarah,jb.namajabatan as jbfungsional,
			                        jb1.namajabatan as jbstruktural,gp.golonganpegawai,uk.name as unitkerja,sp.statuspegawai,
			                        kj.namakelompokjabatan,spp.statusperkawinan,ese.eselon,kp.kategorypegawai as namakategorypegawai,pend.pendidikan,jk.jeniskelamin,
			                        gol.name as golongan,nkj.detailkelompokjabatan,nkj.grade,ks.kelompokshiftkerja,kdd.name as kedudukan"))
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('pg.namalengkap');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pg.id','=',$request['idPegawai']);
        }
        $data = $data->get();
        return $this->respond($data);
    }

    public function updateDataIdFingerPrint(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();

        try {

            $Kel = Pegawai::where('id', $request['idpegawai'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'fingerprintid' => $request['idfinger'],
                ]);

            /*Logging User*/
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Update Id Finger Print Pegawai';
            $logUser->noreff =$request['idpegawai'];
            $logUser->referensi='id pegawai';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();
            /*End Logging User*/

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
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDetailPegawai (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->leftJoin('agama_m as ag','ag.id','=','pg.objectagamafk')
            ->leftJoin('detailkategorypegawai_m as dkp','dkp.id','=','pg.objectdetailkategorypegawaifk ')
            ->leftJoin('golongandarah_m as gd','gd.id','=', 'pg.objectgolongandarahfk')
            ->leftJoin('jabatan_m as jb','jb.id','=','pg.objectjabatanfungsionalfk')
            ->leftJoin('jabatan_m as jb1','jb1.id', '=', 'pg.objectjabatanstrukturalfk')
            ->leftJoin('golonganpegawai_m as gp','gp.id','=', 'pg.objectgolonganpegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk','uk.id','=','pg.objectunitkerjapegawaifk')
            ->leftJoin('statuspegawai_m as sp','sp.id','=','pg.objectstatuspegawaifk')
            ->leftJoin('kelompokjabatan_m as kj','kj.id','=','pg.objectkelompokjabatanfk')
            ->leftJoin('statusperkawinanpegawai_m as spp','spp.id','=','pg.objectstatusperkawinanpegawaifk')
            ->leftJoin('eselon_m as ese','ese.id','=','pg.objecteselonfk')
            ->leftJoin('pendidikan_m as pend','pend.id', '=','pg.objectpendidikanterakhirfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','pg.objectjeniskelaminfk')
            ->leftJoin('sdm_golongan_m as gol','gol.id','=','pg.objectgolonganfk')
            ->leftJoin('nilaikelompokjabatan_m as nkj','nkj.id','=','pg.objectkelompokjabatanfk')
            ->leftJoin('sdm_kelompokshift_m as ks','ks.id','=','pg.objectshiftkerja')
            ->leftJoin('sdm_kedudukan_m as kdd','kdd.id','=','pg.kedudukanfk')
            ->leftJoin('kategorypegawai_m as kp','kp.id','=','pg.kategorypegawai')
            ->leftJoin('jenispegawai_m as jp','jp.id','=','pg.objectjenispegawaifk')
            ->select(\DB::raw("pg.*,ag.agama,dkp.detailkategorypegawai,gd.golongandarah,jb.namajabatan as jbfungsional,
			                        jb1.namajabatan as jbstruktural,gp.golonganpegawai,uk.name as unitkerja,sp.statuspegawai,
			                        kj.namakelompokjabatan,spp.statusperkawinan,ese.eselon,pend.pendidikan,jk.jeniskelamin,
			                        gol.name as golongan,nkj.detailkelompokjabatan,nkj.grade,ks.kelompokshiftkerja,
			                        kp.kategorypegawai as namakategorypegawai,kdd.name as kedudukan,pg.objectjenispegawaifk,jp.jenispegawai"))
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('pg.namalengkap');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pg.id','=',$request['idPegawai']);
        }
        $data = $data->get();

        $dataKeluarga=\DB::table('keluargapegawai_m as kp')
            ->leftJoin('pegawai_m as pg','pg.id','=','kp.objectpegawaifk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','kp.objectjeniskelaminfk')
            ->leftJoin('hubungankeluarga_m as hk','hk.id','=','kp.objectkdhubunganfk')
            ->leftJoin('statusperkawinanpegawai_m as spp','spp.id','=','kp.objectstatusperkawinanpegawaifk')
            ->leftJoin('pendidikan_m as pend','pend.id','=','kp.objectpendidikanterakhirfk')
            ->leftJoin('pekerjaan_m as pe','pe.id','=','kp.objectpekerjaanfk')
            ->select(DB::raw("kp.*,pg.namalengkap as namapegawai,jk.jeniskelamin,hk.reportdisplay as hubungankeluarga,
	                          spp.statusperkawinan,pend.pendidikan,pe.pekerjaan"))
            ->where('kp.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('pg.namalengkap');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataKeluarga = $dataKeluarga->where('pg.id','=',$request['idPegawai']);
        }
        $dataKeluarga = $dataKeluarga->get();

        $dataPendidikan=\DB::table('riwayatpendidikan_t as rp')
            ->leftJoin('pegawai_m as pg','pg.id','=','rp.objectpegawaifk')
            ->leftJoin('pendidikan_m as pend','pend.id','=','rp.objectpendidikanfk')
            ->select(DB::raw("rp.*,pend.pendidikan,pg.namalengkap"))
            ->where('rp.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('rp.tgllulus');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataPendidikan = $dataPendidikan->where('pg.id','=',$request['idPegawai']);
        }
        $dataPendidikan = $dataPendidikan->get();

        $dataPelatihan=\DB::table('riwayatpelatihan_t as rpl')
            ->leftJoin('pegawai_m as pg','pg.id','=','rpl.objectpegawaifk')
            ->select(DB::raw("rpl.*,pg.namalengkap"))
            ->where('rpl.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('rpl.tglmulai');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataPelatihan = $dataPelatihan->where('pg.id','=',$request['idPegawai']);
        }
        $dataPelatihan = $dataPelatihan->get();

        $dataJabatan=\DB::table('riwayatjabatan_t as rj')
            ->leftJoin('jenisjabatan_m as jb','jb.id','=','rj.objectjenisjabatanfk')
            ->leftJoin('jabatan_m as jab','jab.id','=','rj.objectjabatanttdfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','rj.objectpegawaifk')
            ->leftJoin('pegawai_m as pg1','pg1.id','=','rj.objectpegawaittdfk')
            ->select(DB::raw("rj.*,jb.jenisjabatan,jab.namajabatan as namajabatanttd,
			                  pg.namalengkap as namapegawai,pg1.namalengkap as pegawaittd,
			                  pg1.namalengkap || ' / ' || jab.namajabatan as pegawaipenanggungjawab"))
            ->where('pg.statusenabled',true)
            ->where('rj.kdprofile', $kdProfile)
            ->orderBy('rj.tglsk');
        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataJabatan = $dataJabatan->where('pg.id','=',$request['idPegawai']);
        }
        $dataJabatan = $dataJabatan->get();

        $dataSip=\DB::table('masaberlakusipstr_t as mbs')
            ->leftJoin('jenismasaberlakustrsip_m as jmb','jmb.id','=','mbs.jenismasaberlakufk')
            ->leftJoin('pegawai_m as pg','pg.id','=','mbs.pegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk','uk.id','=','mbs.unitkerjafk')
            ->leftJoin('unitkerjapegawai_m as uk2','uk2.id','=','mbs.subunitkerjafk')
            ->select(DB::raw("mbs.*,jmb.jenismasaberlaku, pg.namalengkap as namapegawai, uk.name as unitkerja,
			                  uk2.name as subunit"))
            ->where('mbs.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->where('mbs.jenismasaberlakufk',1)
            ->orderBy('mbs.tglberakhir');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataSip = $dataSip->where('pg.id','=',$request['idPegawai']);
        }
        $dataSip = $dataSip->get();

        $dataStr=\DB::table('masaberlakusipstr_t as mbs')
            ->leftJoin('jenismasaberlakustrsip_m as jmb','jmb.id','=','mbs.jenismasaberlakufk')
            ->leftJoin('pegawai_m as pg','pg.id','=','mbs.pegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk','uk.id','=','mbs.unitkerjafk')
            ->leftJoin('unitkerjapegawai_m as uk2','uk2.id','=','mbs.subunitkerjafk')
            ->select(DB::raw("mbs.*,jmb.jenismasaberlaku, pg.namalengkap as namapegawai, uk.name as unitkerja,
			                  uk2.name as subunit"))
            ->where('pg.statusenabled',true)
            ->where('mbs.kdprofile', $kdProfile)
            ->where('mbs.jenismasaberlakufk',2)
            ->orderBy('mbs.tglberakhir');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataStr = $dataStr->where('pg.id','=',$request['idPegawai']);
        }
        $dataStr = $dataStr->get();

        $dataTelp=\DB::table('nomortelphone_t as nt')
            ->leftJoin('jenisprovider_m as jp','jp.id','=','nt.providerfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','nt.pegawaifk')
            ->select(DB::raw("nt.*,jp.namaprovider"))
            ->where('nt.kdprofile', $kdProfile)
            ->where('nt.statusenabled',true);
//            ->orderBy('mbs.tglberakhir');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataTelp = $dataTelp->where('pg.id','=',$request['idPegawai']);
        }
        $dataTelp = $dataTelp->get();

        $dataLogin=\DB::table('riwayatjabatan_t as rj')
            ->leftJoin('jenisjabatan_m as jb','jb.id','=','rj.objectjenisjabatanfk')
            ->leftJoin('jabatan_m as jab','jab.id','=','rj.objectjabatanttdfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','rj.objectpegawaifk')
            ->leftJoin('pegawai_m as pg1','pg1.id','=','rj.objectpegawaittdfk')
            ->select(DB::raw("rj.*,jb.jenisjabatan,jab.namajabatan as namajabatanttd,
			                  pg.namalengkap as namapegawai,pg1.namalengkap as pegawaittd,
			                  pg1.namalengkap || ' / ' || jab.namajabatan as pegawaipenanggungjawab"))
            ->where('rj.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('rj.tglsk');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataLogin = $dataLogin->where('pg.id','=',$request['idPegawai']);
        }
        $dataLogin = $dataLogin->get();

        $result = array(
            'datapegawai' => $data,
            'datakeluarga' => $dataKeluarga,
            'datapendidikan' => $dataPendidikan,
            'datapelatihan' => $dataPelatihan,
            'datajabatan' => $dataJabatan,
            'datasip'=> $dataSip,
            'datastr'=> $dataStr,
            'datatelp'=> $dataTelp,
            "createby : " => 'ea@epic',

        );
        return $this->respond($result);
    }

    public function deleteDataPelatihan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            RiwayatPelatihan::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->delete();
            $transMessage = "Sukses ";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Hapus Data Pelatihan Gagal";
        }

        if ($transStatus != 'false') {

            \DB::commit();
            $result = array(

                "status" => 201,
                "message" => $transMessage,
                "as" => 'ridwan',
            );
        } else {
            DB::rollBack();
            $result = array(

                "status" => 400,
                "message" => $transMessage,
                "as" => 'ridwan',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveDataRekamDataPegawai (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
//        $dataLogin = $request->all();
//        $dataPegawai = \DB::table('loginuser_s as lu')
//            ->select('lu.objectpegawaifk')
//            ->where('lu.id',$dataLogin['userData']['id'])
//            ->first();
        try{
            #region savePegawai
            $dataPegawai=$request['datapegawai'];
            $dataKeluargaPegawai=$request['datakeluarga'];
            $dataRiwayatPendidikan=$request['riwayatpendidikan'];
            $dataRiwayatPelatihan=$request['riwayatpelatihan'];
            $dataRIwayatJabatan=$request['riwayatjabatan'];
            $dataSip=$request['simpansip'];
            $dataStr=$request['simpanstr'];
            $dataTelp=$request['simpantelp'];
//            return $this->respond($dataKeluargaPegawai);


            if ($dataPegawai['id'] == '') {
                $idPegawai = Pegawai::max('id') +1 ;
                $dataSavePegawai = new Pegawai();
                $dataSavePegawai->id = $idPegawai;
                $dataSavePegawai->kdprofile = $kdProfile;
                $dataSavePegawai->statusenabled=true;
                $dataSavePegawai->norec = $dataSavePegawai->generateNewId();
                $dataSavePegawai->qpegawai=0;
            }else{
                $dataSavePegawai = Pegawai::where('id', $dataPegawai['id'])->where('kdprofile', $kdProfile)->first();
            }
            $dataSavePegawai->namalengkap = $dataPegawai['namalengkap'];
            $dataSavePegawai->nama = $dataPegawai['nama'];
            $dataSavePegawai->gelardepan = $dataPegawai['gelardepan'];
            $dataSavePegawai->gelarbelakang = $dataPegawai['gelarbelakang'];
            $dataSavePegawai->tempatlahir = $dataPegawai['tempatlahir'];
            $dataSavePegawai->bankrekeningnomor = $dataPegawai['nomorrekening'];
            $dataSavePegawai->bankrekeningatasnama = $dataPegawai['namarekening'];
            $dataSavePegawai->bankrekeningnama = $dataPegawai['namabank'];
            $dataSavePegawai->npwp = $dataPegawai['npwp'];
            if (isset($dataPegawai['kodepos'])){
                $dataSavePegawai->kodepos = $dataPegawai['kodepos'];
            }
            $dataSavePegawai->alamat = $dataPegawai['alamat'];
            $dataSavePegawai->idfinger = $dataPegawai['idfinger'];
            $dataSavePegawai->nippns = $dataPegawai['nippns'];
            $dataSavePegawai->pensiun =  $dataPegawai['pensiun'];
            $dataSavePegawai->tgllahir = $dataPegawai['tgllahir'];
            $dataSavePegawai->tglmasuk = $dataPegawai['tglmasuk'];
            $dataSavePegawai->objectagamafk = $dataPegawai['agama'];
            $dataSavePegawai->objectjeniskelaminfk= $dataPegawai['jeniskelamin'];
            $dataSavePegawai->kategorypegawai =  $dataPegawai['statuspegawai'];
            $dataSavePegawai->kedudukanfk=  $dataPegawai['kedudukan'];
            $dataSavePegawai->objectgolonganfk=  $dataPegawai['golongan'];
            $dataSavePegawai->objectjabatanfungsionalfk= $dataPegawai['jabatan'];
            $dataSavePegawai->objectpendidikanterakhirfk=  $dataPegawai['pendidikan'];
            $dataSavePegawai->objectkelompokjabatanfk=  $dataPegawai['kelompokjabatan'];
            $dataSavePegawai->objectstatusperkawinanpegawaifk= $dataPegawai['statusperkawinan'];
            $dataSavePegawai->objecteselonfk=  $dataPegawai['eselon'];
            $dataSavePegawai->objectshiftkerja=  $dataPegawai['shiftkerja'];
            $dataSavePegawai->pensiun = $dataPegawai['pensiun'];
            $dataSavePegawai->nilaijabatan=  $dataPegawai['nilaijabatan'];
            $dataSavePegawai->grade = $dataPegawai['grade'];
            $dataSavePegawai->objectjenispegawaifk = $dataPegawai['jenispegawai'];
            $dataSavePegawai->objectunitkerjapegawaifk = $dataPegawai['unitkerjafk'];
            if (isset($dataPegawai['tglmeninggal']) && $dataPegawai['tglmeninggal'] != 'Invalid date'){
                $dataSavePegawai->tanggalmeninggal = $dataPegawai['tglmeninggal'];
            }
            if (isset($dataPegawai['tglpensiun']) && $dataPegawai['tglpensiun'] != 'Invalid date'){
                $dataSavePegawai->tglpensiun = $dataPegawai['tglpensiun'];
            }
            if (isset($dataPegawai['tglkeluar']) && $dataPegawai['tglkeluar'] != 'Invalid date'){
                $dataSavePegawai->tglkeluar = $dataPegawai['tglkeluar'];
            }
            $dataSavePegawai->save();

            $IdPegawais=$dataPegawai['id'];
            if ($IdPegawais == ""){
                $IdPegawais = $idPegawai;
            }

            if ($dataKeluargaPegawai != ""){

                $dataSaveKeluarga = KeluargaPegawai::where('objectpegawaifk',$IdPegawais)
                    ->where('kdprofile', $kdProfile)
                    ->delete();

                foreach ($dataKeluargaPegawai as $item){

                    $idKeluarga = KeluargaPegawai::max('id') + 1;
                    $dataSaveKeluarga = new KeluargaPegawai();
                    $dataSaveKeluarga->id = $idKeluarga;
                    $dataSaveKeluarga->kdprofile = $kdProfile;
                    $dataSaveKeluarga->statusenabled=true;
                    $dataSaveKeluarga->norec = $dataSaveKeluarga->generateNewId();
                    $dataSaveKeluarga->objectpegawaifk = $IdPegawais;

                    $dataSaveKeluarga->alamat = $item['alamat'];
                    $dataSaveKeluarga->objectjeniskelaminfk = $item['objectjeniskelaminfk'];
                    $dataSaveKeluarga->keterangan = $item['keterangan'];
                    $dataSaveKeluarga->namaayah = $item['namaayah'];
                    $dataSaveKeluarga->namaibu = $item['namaibu'];
                    $dataSaveKeluarga->namalengkap = $item['namalengkap'];
                    $dataSaveKeluarga->nipistrisuami = $item['nipistrisuami'];
                    $dataSaveKeluarga->nosuratkuliah = $item['nosuratkuliah'];
                    $dataSaveKeluarga->objectkdhubunganfk = $item['objectkdhubunganfk'];
                    $dataSaveKeluarga->objectpekerjaanfk = $item['objectpekerjaanfk'];
                    $dataSaveKeluarga->objectstatusperkawinanpegawaifk = $item['objectstatusperkawinanpegawaifk'];
                    $dataSaveKeluarga->statustanggungan = $item['statustanggungan'];
                    $dataSaveKeluarga->tgllahir = $item['tgllahir'];
                    if (isset($item['tglsuratkuliah']) && $item['tglsuratkuliah'] != 'Invalid date'){
                        $dataSaveKeluarga->tglsuratkuliah = $item['tglsuratkuliah'];
                    }
                    $dataSaveKeluarga->statustanggunganfk = $item['statustanggunganfk'];
                    $dataSaveKeluarga->objectpendidikanterakhirfk = $item['objectpendidikanterakhirfk'];
                    $dataSaveKeluarga->save();
                }
            }

            if ($dataRiwayatPendidikan != ""){
                foreach ($dataRiwayatPendidikan as $items){
                    if ($items['norec'] == ""){
                        $dataSavePendidikan = new RiwayatPendidikan();
                        $dataSavePendidikan->kdprofile = $kdProfile;
                        $dataSavePendidikan->statusenabled=true;
                        $dataSavePendidikan->norec = $dataSavePendidikan->generateNewId();
                        $dataSavePendidikan->objectpegawaifk = $IdPegawais;
                    }else{
                        $dataSavePendidikan = RiwayatPendidikan::where('norec', $items['norec'])
                            ->where('kdprofile', $kdProfile)
                            ->where('objectpegawaifk',$IdPegawais)
                            ->first();
                    }
                    $dataSavePendidikan->namatempatpendidikan = $items['namatempatpendidikan'];
                    $dataSavePendidikan->alamattempatpendidikan = $items['alamattempatpendidikan'];
                    $dataSavePendidikan->objectpendidikanfk =$items['objectpendidikanfk'];
                    $dataSavePendidikan->jurusan = $items['jurusan'];
                    $dataSavePendidikan->tglmasuk = $items['tglmasuk'];
                    $dataSavePendidikan->tgllulus = $items['tgllulus'];
                    $dataSavePendidikan->nilaiipk = $items['nilaiipk'];
                    $dataSavePendidikan->noijazah = $items['noijazah'];
                    if (isset($items['tglijazah'])){
                        $dataSavePendidikan->tglijazah = $items['tglijazah'];
                    }
                    $dataSavePendidikan->save();
                }
            }

            if ($dataRiwayatPelatihan != ""){
                foreach ($dataRiwayatPelatihan as $itempend){
                    if ($itempend['norec'] == ""){
                        $dataSavePelatihan = new RiwayatPelatihan();
                        $dataSavePelatihan->kdprofile = $kdProfile;
                        $dataSavePelatihan->statusenabled=true;
                        $dataSavePelatihan->norec = $dataSavePelatihan->generateNewId();
                        $dataSavePelatihan->objectpegawaifk = $IdPegawais;
                    }else{
                        $dataSavePelatihan = RiwayatPelatihan::where('norec', $itempend['norec'])
                            ->where('kdprofile', $kdProfile)
                            ->where('objectpegawaifk',$IdPegawais)
                            ->first();
                    }
                    $dataSavePelatihan->instansipenyelenggara = $itempend['instansipenyelenggara'];
                    $dataSavePelatihan->namapelatihan = $itempend['namapelatihan'];
                    $dataSavePelatihan->lokasipelatihan =$itempend['lokasipelatihan'];
                    $dataSavePelatihan->tglmulai = $itempend['tglmulai'];
                    $dataSavePelatihan->tglakhir = $itempend['tglakhir'];
                    $dataSavePelatihan->durasi = $itempend['durasi'];
                    if (isset($itempend['nosertifikat'])){
                        $dataSavePelatihan->nosertifikat = $itempend['nosertifikat'];
                    }
                    if (isset($itempend['keterangan'])){
                        $dataSavePelatihan->keterangan = $itempend['keterangan'];
                    }
                    $dataSavePelatihan->save();
                }
            }

            if ($dataRIwayatJabatan != ""){
                foreach ($dataRIwayatJabatan as $hideung){
                    if ($hideung['norec'] == ""){
                        $dataSaveJabatan = new RiwayatJabatan();
                        $dataSaveJabatan->kdprofile = $kdProfile;
                        $dataSaveJabatan->statusenabled=true;
                        $dataSaveJabatan->norec = $dataSaveJabatan->generateNewId();
                        $dataSaveJabatan->objectpegawaifk = $IdPegawais;
                    }else{
                        $dataSaveJabatan = RiwayatJabatan::where('norec', $hideung['norec'])
                            ->where('kdprofile', $kdProfile)
                            ->where('objectpegawaifk',$IdPegawais)
                            ->first();
                    }
                    $dataSaveJabatan->objectjenisjabatanfk = $hideung['objectjenisjabatanfk'];
                    $dataSaveJabatan->objectjabatanfk = $hideung['objectjabatanfk'];
                    $dataSaveJabatan->namajabatan =$hideung['namajabatan'];
                    $dataSaveJabatan->nosk = $hideung['nosk'];
                    $dataSaveJabatan->tglsk = $hideung['tglsk'];
                    $dataSaveJabatan->objectpegawaittdfk = $hideung['objectpegawaittdfk'];
                    $dataSaveJabatan->objectjabatanttdfk = $hideung['objectjabatanttdfk'];
                    $dataSaveJabatan->save();
                }
            }

            if ($dataSip != ""){
                foreach ($dataSip as $hideungs){
                    if ($hideungs['norec'] == ""){
                        $dataSaveSip = new MasaBerlakuSipStr();
                        $dataSaveSip->kdprofile = $kdProfile;
                        $dataSaveSip->statusenabled=true;
                        $dataSaveSip->jenismasaberlakufk=1;
                        $dataSaveSip->norec = $dataSaveSip->generateNewId();
                        $dataSaveSip->pegawaifk = $IdPegawais;
                    }else{
                        $dataSaveSip = MasaBerlakuSipStr::where('norec', $hideungs['norec'])
                            ->where('kdprofile', $kdProfile)
                            ->where('pegawaifk',$IdPegawais)
                            ->first();
                    }
                    $dataSaveSip->unitkerjafk = $hideungs['unitkerjafk'];
                    $dataSaveSip->subunitkerjafk = $hideungs['subunitkerjafk'];
                    $dataSaveSip->nomor =$hideungs['nomor'];
                    $dataSaveSip->tglberakhir = $hideungs['tglberakhir'];
                    $dataSaveSip->save();
                }
            }

            if ($dataStr != ""){
                foreach ($dataStr as $hideungx){
                    if ($hideungx['norec'] == ""){
                        $dataSaveStr = new MasaBerlakuSipStr();
                        $dataSaveStr->kdprofile = $kdProfile;
                        $dataSaveStr->statusenabled=true;
                        $dataSaveStr->jenismasaberlakufk=2;
                        $dataSaveStr->norec = $dataSaveStr->generateNewId();
                        $dataSaveStr->pegawaifk = $IdPegawais;
                    }else{
                        $dataSaveStr = MasaBerlakuSipStr::where('norec', $hideungx['norec'])
                            ->where('kdprofile', $kdProfile)
                            ->where('pegawaifk',$IdPegawais)
                            ->first();
                    }
                    $dataSaveStr->unitkerjafk = $hideungx['unitkerjafk'];
                    $dataSaveStr->subunitkerjafk = $hideungx['subunitkerjafk'];
                    $dataSaveStr->nomor =$hideungx['nomor'];
                    $dataSaveStr->tglberakhir = $hideungx['tglberakhir'];
                    $dataSaveStr->save();
                }
            }

            if ($dataTelp != ""){
                foreach ($dataTelp as $hideungtelp){
                    if ($hideungtelp['norec'] == ""){
                        $dataSaveTelp = new NomorTelphonePegawai();
                        $dataSaveTelp->kdprofile = $kdProfile;
                        $dataSaveTelp->statusenabled=true;
                        $dataSaveTelp->norec = $dataSaveTelp->generateNewId();
                        $dataSaveTelp->pegawaifk = $IdPegawais;
                    }else{
                        $dataSaveTelp = NomorTelphonePegawai::where('norec', $hideungtelp['norec'])
                            ->where('kdprofile', $kdProfile)
                            ->where('pegawaifk',$IdPegawais)
                            ->first();
                    }
                    $dataSaveTelp->nomor = $hideungtelp['noTelp'];
                    $dataSaveTelp->providerfk = $hideungtelp['providerfk'];
                    $dataSaveTelp->save();
                }
            }

            //## Logging User
//                if ($dataPegawai['id'] == ""){
//                    $newId = LoggingUser::max('id');
//                    $newId = $newId +1;
//                    $logUser = new LoggingUser();
//                    $logUser->id = $newId;
//                    $logUser->norec = $logUser->generateNewId();
//                    $logUser->kdprofile= 0;
//                    $logUser->statusenabled=true;
//                    $logUser->jenislog = 'Tambah Master Pegawai';
//                    $logUser->noreff =$dataSavePegawai->id;
//                    $logUser->referensi='norec Verifikasi Direktur Terkait';
//                    $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
//                    $logUser->tanggal = $tglAyeuna;
//                    $logUser->save();
//                }else{
//                    $newId = LoggingUser::max('id');
//                    $newId = $newId +1;
//                    $logUser = new LoggingUser();
//                    $logUser->id = $newId;
//                    $logUser->norec = $logUser->generateNewId();
//                    $logUser->kdprofile= 0;
//                    $logUser->statusenabled=true;
//                    $logUser->jenislog = 'Edit Master Pegawai';
//                    $logUser->noreff =$dataSavePegawai->id;
//                    $logUser->referensi='norec Verifikasi Direktur Terkait';
//                    $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
//                    $logUser->tanggal = $tglAyeuna;
//                    $logUser->save();
//                }

            #endregion

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
                "idpegawai" => $IdPegawais,
                "datapegawai" => $dataSavePegawai,
                "datakeluarga" => $dataKeluargaPegawai,
                "datapendidian" => $dataRiwayatPendidikan,
                "datapelatihan" => $dataRiwayatPelatihan,
                "datajabatan" => $dataRIwayatJabatan,
                'datasip' => $dataSip,
                'datastr' => $dataStr,
                'datatelp' =>$dataTelp,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "idpegawai" => $IdPegawais,
                "datapegawai" => $dataSavePegawai,
                "datakeluarga" => $dataKeluargaPegawai,
                "datapendidian" => $dataRiwayatPendidikan,
                "datapelatihan" => $dataRiwayatPelatihan,
                "datajabatan" => $dataRIwayatJabatan,
                'datasip' => $dataSip,
                'datastr' => $dataStr,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataDaftarUrutKepangkatan (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->leftJoin('jenispegawai_m as jp','jp.id','=','pg.objectjenispegawaifk')
            ->leftJoin('pangkat_m as pk','pk.id','=','pg.objectpangkatfk')
            ->leftJoin('jabatan_m as jb','jb.id','=','pg.objectjabatanfungsionalfk')
            ->leftJoin('sdm_golongan_m as sg','sg.id','=','pg.objectgolonganfk')
            ->select(\DB::raw("pg.id as pegawaiid,pg.namalengkap,pg.tempatlahir,pg.tgllahir,pg.nippns,
                             pg.objectjenispegawaifk,jp.jenispegawai,pg.objectpangkatfk,
                             pk.namapangkat,pg.objectjabatanfungsionalfk,jb.namajabatan,
                             pg.objectgolonganfk,sg.reportdisplay as golongan"))
            ->where('pg.statusenabled',true)
            ->where('pg.kdprofile',$kdProfile);

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pg.id','=',$request['idPegawai']);
        }

        if(isset($request['idJenisPegawai']) && $request['idJenisPegawai']!="" && $request['idJenisPegawai']!="undefined"){
            $data = $data->where('pg.objectjenispegawaifk','=',$request['idJenisPegawai']);
        }

        if(isset($request['idPangkatPe']) && $request['idPangkatPe']!="" && $request['idPangkatPe']!="undefined"){
            $data = $data->where('pg.objectpangkatfk','=',$request['idPangkatPe']);
        }

        $data = $data->orderBy('pg.namalengkap');
        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataJadwalKerjaRuangan (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawaijadwalkerja_m as pjk')
            ->leftJoin('pegawai_m as pg','pg.id','=','pjk.objectpegawaifk')
            ->leftJoin('ruangan_m as ru','ru.id','=','pjk.objectruanganfk')
            ->leftJoin('shiftkerja_m as sf','sf.id','=','pjk.objectshiftfk')
            ->leftJoin('kalender_s as kln','kln.id','=','pjk.objecttanggalfk')
            ->select(\DB::raw("pjk.*,pg.namalengkap,ru.namaruangan,sf.namashift,sf.jammasuk,sf.jampulang,kln.reportdisplay"))
            ->where('pg.statusenabled',true)
            ->where('pg.kdprofile',$kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pjk.tgljadwal', '>=', $request['tglAwal']);
        }

        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pjk.tgljadwal', '<=', $tgl);
        }

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pjk.objectpegawaifk','=',$request['idPegawai']);
        }

        if(isset($request['idRuangan']) && $request['idRuangan']!="" && $request['idRuangan']!="undefined"){
            $data = $data->where('pjk.objectruanganfk','=',$request['idRuangan']);
        }

        $data = $data->orderBy('pg.namalengkap');
        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataSip (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('masaberlakusipstr_t as mbs')
            ->leftJoin('jenismasaberlakustrsip_m as jmb','jmb.id','=','mbs.jenismasaberlakufk')
            ->leftJoin('pegawai_m as pg','pg.id','=','mbs.pegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk','uk.id','=','pg.objectunitkerjapegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk2','uk2.id','=','mbs.subunitkerjafk')
            ->select(\DB::raw("mbs.*,jmb.jenismasaberlaku, pg.namalengkap as namapegawai,pg.nip_pns as nipPns, uk.name as unitkerja,
			                  uk2.name as subunit"))
            ->where('pg.statusenabled',true)
            ->where('mbs.kdprofile', $kdProfile)
//            ->where('mbs.jenismasaberlakufk',1)
            ->orderBy('mbs.tglberakhir');

//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('pjk.tgljadwal', '>=', $request['tglAwal']);
//        }
//
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];//." 23:59:59";
//            $data = $data->where('pjk.tgljadwal', '<=', $tgl);
//        }
//
//        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
//            $data = $data->where('pjk.objectpegawaifk','=',$request['idPegawai']);
//        }

        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ridwan',
        );
        return $this->respond($result);
    }

    public function getDataStr (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('masaberlakusipstr_t as mbs')
            ->leftJoin('jenismasaberlakustrsip_m as jmb','jmb.id','=','mbs.jenismasaberlakufk')
            ->leftJoin('pegawai_m as pg','pg.id','=','mbs.pegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk','uk.id','=','mbs.unitkerjafk')
            ->leftJoin('unitkerjapegawai_m as uk2','uk2.id','=','mbs.subunitkerjafk')
            ->select(\DB::raw("mbs.*,jmb.jenismasaberlaku, pg.namalengkap as namapegawai,pg.nip_pns as nipPns, uk.name as unitkerja,
			                  uk2.name as subunit"))
            ->where('mbs.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->where('mbs.jenismasaberlakufk',2)
            ->orderBy('mbs.tglberakhir');

//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('pjk.tgljadwal', '>=', $request['tglAwal']);
//        }
//
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];//." 23:59:59";
//            $data = $data->where('pjk.tgljadwal', '<=', $tgl);
//        }
//
//        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
//            $data = $data->where('pjk.objectpegawaifk','=',$request['idPegawai']);
//        }

        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ridwan',
        );
        return $this->respond($result);
    }

    public function simpanSipStr(Request $request){
         \DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ""){
                $dataSaveSipStr = new MasaBerlakuSipStr();
                $dataSaveSipStr->kdprofile = $kdProfile;
                $dataSaveSipStr->statusenabled=true;
                $dataSaveSipStr->norec = $dataSaveSipStr->generateNewId();

            }else{
                $dataSaveSipStr = MasaBerlakuSipStr::where('norec', $request['norec'])
                    ->where('kdprofile',$kdProfile)
                    ->where('pegawaifk',$request['idPegawai'])
                    ->first();
            }

//            if ($request['jenismasaberlakufk'] == 1){
//                $filename = 'SIP-'.$request['idPegawai'].'.'.$extension;
//            }elseif ($request['jenismasaberlakufk'] == 2) {
//                $filename = 'STR-'.$request['idPegawai'].'.'.$extension;
//            }
//
//            Storage::disk('local')->put('SDM/FileSipStr/'.$filename,file_get_contents($request->file('file')));
//            // $path = $uploadedFile->move('photos',$filename);
//
//            $dataSaveSipStr->pegawaifk=$request['idPegawai'];
//            $dataSaveSipStr->jenismasaberlakufk=$request['jenismasaberlakufk'];
//            $dataSaveSipStr->nomor =$request['nomorSurat'];
//            $dataSaveSipStr->tglberakhir = $request['tglBerakhir'];
//            $dataSaveSipStr->namafileupload = $filename;

            $uploadedFileSip = $request->file('filesip');
            $uploadedFileStr = $request->file('filestr');
            $pegawai = Pegawai::where('id',$request['idPegawai'])->first();
            if(!empty($uploadedFileSip)){
                $extensionSip = $uploadedFileSip->getClientOriginalExtension();
                $filenameSip = 'SIP-'.$pegawai->namalengkap.'.'.$extensionSip;

//                Storage::disk('local')->put('SDM/FileSipStr/'.$filenameSip,file_get_contents($request->file('filesip')));
                $dataSaveSipStr->namafilesip = $filenameSip;
            }
            if(!empty($uploadedFileStr)){
                $extensionStr = $uploadedFileStr->getClientOriginalExtension();
                $filenameStr = 'STR-' .$pegawai->namalengkap . '.' . $extensionStr;
//                Storage::disk('local')->put('SDM/FileSipStr/'.$filenameStr,file_get_contents($request->file('filestr')));
                $dataSaveSipStr->namafilestr =$filenameStr;
            }

            $dataSaveSipStr->pegawaifk=$request['idPegawai'];
            $dataSaveSipStr->nosip=$request['nomorsip'];
            $dataSaveSipStr->terbitsip =$request['terbitsip'];
            $dataSaveSipStr->berakhirsip = $request['berakhirsip'];
            $dataSaveSipStr->nostr = $request['nomorstr'];
            $dataSaveSipStr->terbitstr = $request['terbitstr'];
            $dataSaveSipStr->berakhirstr = $request['berakhirstr'];
            $dataSaveSipStr->save();
            $norec =  $dataSaveSipStr->norec ;
            if(!empty($uploadedFileSip)) {
                $request->file('filesip')->move('SDM/FileSipStr/'.$norec,
                    $filenameSip);
            }
            if(!empty($uploadedFileStr)) {
                $request->file('filestr')->move('SDM/FileSipStr/'.$norec,
                    $filenameStr);
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus = 'true') {
            $transMessage = "Sukses ";
            \DB::commit();
            $result = array(
                "status" => 201,
                "norec" => $dataSaveSipStr,
                "as" => 'ridwan',
                "by" => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Sip/Str Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'ridwan',
                "by" => 'er@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function createZipSipStr(Request $request){

        $zip_file = $request['name'].'.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $path = public_path('SDM/FileSipStr'.'/'.$request['norec']);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $name => $file)
        {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();

                // extracting filename with substr/strlen
                $relativePath =  substr($filePath, strlen($path) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return response()->download($zip_file);
    }
    public function deleteDataSipStr(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            $filename = $request['namafile'];
            $path = public_path('SDM/FileSipStr/'.$request['norec'].'/');

            if (!\File::exists($path)) {
//                abort(404);
            }else{
                $file = \File::deleteDirectory($path);
            }


            Storage::disk('local')->delete('SDM/FileSipStr/'.$filename);
            MasaBerlakuSipStr::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->delete();
            $transMessage = "Sukses ";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Hapus Data Sip/Str Gagal";
        }

        if ($transStatus != 'false') {

            \DB::commit();
            $result = array(

                "status" => 201,
                "message" => $transMessage,
                "as" => 'ridwan',
                "by" => 'er@epic',
            );
        } else {
            DB::rollBack();
            $result = array(

                "status" => 400,
                "message" => $transMessage,
                "as" => 'ridwan',
                "by" => 'er@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPegawaiSudahPensiun (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->leftJoin('agama_m as ag','ag.id','=','pg.objectagamafk')
            ->leftJoin('golonganpegawai_m as gp','gp.id','=', 'pg.objectgolonganpegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk','uk.id','=','pg.objectunitkerjafk')
            ->leftJoin('unitkerjapegawai_m as uk2','uk2.id','=','pg.objectsubunitkerjapegawaifk')
            ->leftJoin('sdm_golongan_m as gol','gol.id','=','pg.objectgolonganfk')
//            ->select(DB::raw("pg.id,pg.namalengkap, pg.nippns,pg.tgllahir, pg.pensiun, pg.tglpensiun,pg.objectgolonganfk,
//                    gp.golonganpegawai,uk.name as unitkerja, uk2.name as subunit"))
            ->select(\DB::raw("pg.*,gp.golonganpegawai,uk.name as unitkerja, uk2.name as subunit"))
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.statusenabled',false)
            ->orderBy('pg.namalengkap');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pg.tglpensiun', '>=', $request['tglAwal']);
        }

        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pg.tglpensiun', '<=', $request['tglAkhir']);
        }
//
        if(isset($request['namaPeg']) && $request['namaPeg']!="" && $request['namaPeg']!="undefined"){
            $data = $data->where('pg.namalengkap','ilike','%'.$request['namaPeg'].'%');
        }

        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ridwan',
        );
        return $this->respond($result);
    }

    public function getPegawaiPensiun (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->leftJoin('agama_m as ag','ag.id','=','pg.objectagamafk')
            ->leftJoin('golonganpegawai_m as gp','gp.id','=', 'pg.objectgolonganpegawaifk')
            ->leftJoin('unitkerjapegawai_m as uk','uk.id','=','pg.objectunitkerjafk')
            ->leftJoin('unitkerjapegawai_m as uk2','uk2.id','=','pg.objectsubunitkerjapegawaifk')
            ->leftJoin('sdm_golongan_m as gol','gol.id','=','pg.objectgolonganfk')
//            ->select(DB::raw("pg.id,pg.namalengkap, pg.nippns,pg.tgllahir, pg.pensiun, pg.tglpensiun,pg.objectgolonganfk,
//                    gp.golonganpegawai,uk.name as unitkerja, uk2.name as subunit"))
            ->select(\DB::raw("pg.*,gp.golonganpegawai,uk.name as unitkerja, uk2.name as subunit"))
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('pg.namalengkap');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pg.tglpensiun', '>=', $request['tglAwal']);
        }

        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pg.tglpensiun', '<=', $request['tglAkhir']);
        }
//
        if(isset($request['namaPeg']) && $request['namaPeg']!="" && $request['namaPeg']!="undefined"){
            $data = $data->where('pg.namalengkap','ilike','%'.$request['namaPeg'].'%');
        }

        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ridwan',
        );
        return $this->respond($result);
    }

    public function updateDataPegawaiFormPensiun (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try{
            #region savePegawaiFormPensiun

//            $dataPegawai=$request['datapegawai'];
            $updatePegFormPensiun = Pegawai::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
            $updatePegFormPensiun->nippns = $request['nippns'];
            $updatePegFormPensiun->nip_pns = $request['nip_pns'];
            $updatePegFormPensiun->pensiun =  $request['pensiun'];
            $updatePegFormPensiun->objectunitkerjapegawaifk =  $request['objectunitkerjapegawaifk'];
            $updatePegFormPensiun->objectunitkerjafk =  $request['objectunitkerjafk'];
            $updatePegFormPensiun->objectsubunitkerjapegawaifk =  $request['objectsubunitkerjapegawaifk'];
            $updatePegFormPensiun->tgllahir = $request['tgllahir'];
            $updatePegFormPensiun->objectgolonganfk=  $request['objectgolonganfk'];
            $updatePegFormPensiun->objectgolonganpegawaifk=  $request['objectgolonganpegawaifk'];
            $updatePegFormPensiun->tglpensiun = $request['tglpensiun'];

            $updatePegFormPensiun->save();

            #endregion savePegawaiFormPensiun
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
                "datapegawai" => $updatePegFormPensiun,
                "as" => 'ridwan',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "datapegawai" => $updatePegFormPensiun,
                "as" => 'ridwan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getComboPensiun(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $UnitKerja = UnitKerjaPegawai::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $SubUnitKerja = SubUnitKerja::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $GolonganPegawai = GolonganPegawai::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();

        $DataUnitkerja=[];
        foreach ($UnitKerja as $item) {
            $detail = [];
            foreach ($SubUnitKerja as $item2) {
                if ($item->id == $item2->objectunitkerjapegawaifk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'name' => $item2->name,
                    );
                }
            }

            $DataUnitkerja[] = array(
                'id' => $item->id,
                'name' => $item->name,
                'subunit' => $detail,
            );
        }
        $result = array(
            'datalogin'=> $dataLogin,
            'dataunitkerja' => $DataUnitkerja,
            'unitkerjapegawai' => $UnitKerja,
            'subunitkerja' => $SubUnitKerja,
            'golonganpegawai' => $GolonganPegawai,
            'by' => 'ridwan',
        );

        return $this->respond($result);
    }

    public function SavePensiunPegawai (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try{
            #region savePegawaiFormPensiun

//            $dataPegawai=$request['datapegawai'];
            $pensiunkanPegawai = Pegawai::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
            $pensiunkanPegawai->statusenabled = $request['statusenabled'];

            $pensiunkanPegawai->save();

            #endregion savePegawaiFormPensiun
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
                "datapegawai" => $pensiunkanPegawai,
                "as" => 'ridwan',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "datapegawai" => $pensiunkanPegawai,
                "as" => 'ridwan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveJadwalBulananPegawai(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try {
            foreach ($request['data'] as $item) {
                if ($request['id'] == '') {
                    $id = PegawaiJadwalKerja::max('id');
                    $dataJadwal = new PegawaiJadwalKerja();
                    $dataJadwal->id = $id + 1;
                    $dataJadwal->norec = $dataJadwal->generateNewId();
                    $dataJadwal->kdprofile = $kdProfile;
                    $dataJadwal->statusenabled = true;

                } else {
                    $dataJadwal = PegawaiJadwalKerja::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
                }

                $dataJadwal->objectpegawaifk = $item['idpegawai'];
//                $dataJadwal->objectruanganfk = $request['ruanganfk'];
                $dataJadwal->objectunitkerjapegawaifk = $request['ruanganfk'];
                $dataJadwal->objectshiftfk = $request['shiftkerja'];
                $dataJadwal->tgljadwal = $item['tglmulai'];
                $dataJadwal->jammulai = $request['jammulai'];
                $dataJadwal->jamselesai = $request['jamselesai'];
                $dataJadwal->keteranganalasan = $request['keterangan'];
//                $dataJadwal->objectstatushadirfk = 1;//hadir $request['objectstatushadirfk'];
                $dataJadwal->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Jadwal Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "res" => $dataJadwal,
                "as" => 'ramdanegie@epic',
            );
        } else {
            $transMessage = "Simpan Jadwal Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ramdanegie@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getJadwalBulananPegawai(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawaijadwalkerja_m AS pjk')
            ->join('pegawai_m as pg','pg.id','=','pjk.objectpegawaifk')
            ->leftjoin('ruangan_m as ru','ru.id','=','pjk.objectruanganfk')
            ->join('unitkerjapegawai_m as uk','uk.id','=','pjk.objectunitkerjapegawaifk')
            ->select('pjk.*','pg.namalengkap','ru.namaruangan','uk.name as unitkerja')
            ->where('pjk.kdprofile', $kdProfile)
            ->where('pjk.statusenabled', true)
            ->orderByRaw('pg.namalengkap,pjk.tgljadwal desc');


        if(isset($request['bulan']) &&
            $request['bulan']!="" &&
            $request['bulan']!="undefined"){
            $tgl = $request['bulan']  ;
            $data = $data->whereRaw("STUFF(CONVERT(varchar(10), pjk.tgljadwal,104),1,3,'')  ='$tgl' " );
        };
        if(isset($request['namalengkap']) &&
            $request['namalengkap']!="" &&
            $request['namalengkap']!="undefined"){
            $data = $data->where('pg.namalengkap','ilike','%'. $request['namalengkap'] .'%' );
        };
        if(isset($request['idRuangan']) &&
            $request['idRuangan']!="" &&
            $request['idRuangan']!="undefined"){
            $data = $data->where('uk.id','=', $request['idRuangan'] );
        };
        if(isset($request['idPegawai']) &&
            $request['idPegawai']!="" &&
            $request['idPegawai']!="undefined"){
            $idPegawai =$request['idPegawai'];
            $data = $data->whereRaw("pg.id in ( $idPegawai )");
        };

        $data = $data->get();
        return $this->respond($data);
    }

    public function hapusJadwalBulananPegawai(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try {
            foreach ($request['data'] as $item) {

                $dataJadwal = PegawaiJadwalKerja::where('id', $item['id'])
                    ->where('kdprofile', $kdProfile)
                    ->update(
                    [ 'statusenabled' => false ]
                );

            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Jadwal Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "res" => $dataJadwal,
                "as" => 'ramdanegie@epic',
            );
        } else {
            $transMessage = "Hapus Jadwal Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ramdanegie@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getComboPegawaiJadwal(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
//        $kdJenisPegawaiDokter = $this->settingDataFixed('kdJenisPegawaiDokter');
        $req = $request->all();
        $data = \DB::table('pegawai_m')
            ->select('id','namalengkap')
            ->where('statusenabled', true)
            ->where('kdprofile', $kdProfile)
//            ->where('objectjenispegawaifk',$kdJenisPegawaiDokter)
            ->orderBy('namalengkap');

        if(isset($req['namalengkap']) &&
            $req['namalengkap']!="" &&
            $req['namalengkap']!="undefined"){
            $data = $data->where('namalengkap','ilike','%'. $req['namalengkap'] .'%' );
        };
        if(isset($req['idpegawai']) &&
            $req['idpegawai']!="" &&
            $req['idpegawai']!="undefined"){
            $data = $data->where('id', $req['idpegawai'] );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data
                ->where('namalengkap','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }

//        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getMonitoringAbsensiPegawai (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->Join('sdm_absensipegawai_t as abn','abn.pegawaifk','=','pg.id')
            ->select(\DB::raw("abn.pegawaifk,pg.namalengkap,abn.jammasuk,abn.jamkeluar,'-' as namaruangan"))
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->where('abn.statusenabled',true);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('abn.jammasuk', '>=', $request['tglAwal']);
        }

        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('abn.jammasuk', '<=', $tgl);
        }

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pg.id','=',$request['idPegawai']);
        }
        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $data = $data->where('abn.norec','=',$request['norec']);
        }

        $data = $data->orderBy('pg.namalengkap');
        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function deleteDataKeluarga(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            KeluargaPegawai::where('id',$request['id'])->where('kdprofile', $kdProfile)->delete();
            $transMessage = "Sukses ";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Hapus Data Keluarga Gagal";
        }

        if ($transStatus != 'false') {

            \DB::commit();
            $result = array(

                "status" => 201,
                "message" => $transMessage,
                "as" => 'ridwan',
            );
        } else {
            DB::rollBack();
            $result = array(

                "status" => 400,
                "message" => $transMessage,
                "as" => 'ridwan',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataKeluarga (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataKeluarga=\DB::table('keluargapegawai_m as kp')
            ->leftJoin('pegawai_m as pg','pg.id','=','kp.objectpegawaifk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','kp.objectjeniskelaminfk')
            ->leftJoin('hubungankeluarga_m as hk','hk.id','=','kp.objectkdhubunganfk')
            ->leftJoin('statusperkawinanpegawai_m as spp','spp.id','=','kp.objectstatusperkawinanpegawaifk')
            ->leftJoin('pendidikan_m as pend','pend.id','=','kp.objectpendidikanterakhirfk')
            ->leftJoin('pekerjaan_m as pe','pe.id','=','kp.objectpekerjaanfk')
            ->select(\DB::raw("kp.*,pg.namalengkap as namapegawai,jk.jeniskelamin,hk.reportdisplay as hubungankeluarga,
	                          spp.statusperkawinan,pend.pendidikan,pe.pekerjaan"))
            ->where('kp.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('pg.namalengkap');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $dataKeluarga = $dataKeluarga->where('pg.id','=',$request['idPegawai']);
        }
        $dataKeluarga = $dataKeluarga->get();

        $result = array(
            'data'  => $dataKeluarga,
            'as' => 'ridwan',
        );
        return $this->respond($result);
    }

    public function saveDataKeluarga (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try{
            #region savePegawaiFormKeluarga

            $dataKeluargaPegawai=$request['datakeluarga'];
            $IdPegawais=$request['idPegawai'];

            if ($dataKeluargaPegawai != ""){
                foreach ($dataKeluargaPegawai as $item){
                    if ($item['id'] == ""){
                        $idKeluarga = KeluargaPegawai::max('id') + 1;
                        $dataSaveKeluarga = new KeluargaPegawai();
                        $dataSaveKeluarga->id = $idKeluarga;
                        $dataSaveKeluarga->kdprofile = $kdProfile;
                        $dataSaveKeluarga->statusenabled=true;
                        $dataSaveKeluarga->norec = $dataSaveKeluarga->generateNewId();
                        $dataSaveKeluarga->objectpegawaifk = $IdPegawais;
                    }else{
                        $dataSaveKeluarga = KeluargaPegawai::where('id', $item['id'])
                            ->where('kdprofile', $kdProfile)
                            ->where('objectpegawaifk',$IdPegawais)
                            ->first();
                    }
                    $dataSaveKeluarga->alamat = $item['alamat'];
                    $dataSaveKeluarga->objectjeniskelaminfk = $item['objectjeniskelaminfk'];
                    $dataSaveKeluarga->keterangan = $item['keterangan'];
                    $dataSaveKeluarga->namaayah = $item['namaayah'];
                    $dataSaveKeluarga->namaibu = $item['namaibu'];
                    $dataSaveKeluarga->namalengkap = $item['namalengkap'];
                    $dataSaveKeluarga->nipistrisuami = $item['nipistrisuami'];
                    $dataSaveKeluarga->nosuratkuliah = $item['nosuratkuliah'];
                    $dataSaveKeluarga->objectkdhubunganfk = $item['objectkdhubunganfk'];
                    $dataSaveKeluarga->objectpekerjaanfk = $item['objectpekerjaanfk'];
                    $dataSaveKeluarga->objectstatusperkawinanpegawaifk = $item['objectstatusperkawinanpegawaifk'];
                    $dataSaveKeluarga->statustanggungan = $item['statustanggungan'];
                    $dataSaveKeluarga->tgllahir = $item['tgllahir'];
                    if (isset($item['tglsuratkuliah']) && $item['tglsuratkuliah'] != 'Invalid date'){
                        $dataSaveKeluarga->tglsuratkuliah = $item['tglsuratkuliah'];
                    }
                    $dataSaveKeluarga->statustanggunganfk = $item['statustanggunganfk'];
                    $dataSaveKeluarga->objectpendidikanterakhirfk = $item['objectpendidikanterakhirfk'];
                    $dataSaveKeluarga->save();
                }
            }

            #endregion savePegawaiFormPensiun
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
                "datapegawai" => $dataSaveKeluarga,
                "as" => 'ridwan',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "datapegawai" => $dataSaveKeluarga,
                "as" => 'ridwan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarDiklatKategory(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DiklatKategory::get();

        $result = array(
            'data' => $data,
            'by' => 'asepic',
        );
        return $result;
    }

    public function saveDiklatKategory(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $newId = DiklatKategory::max('id') +1 ;
            if ($request['id'] == ''){
                $TP = new DiklatKategory();
                $TP->id = $newId;
                $TP->kdprofile = $kdProfile;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = DiklatKategory::where('id', $request['id'])->first();
            }
            $TP->statusenabled =  $request['statusenabled'];
            $TP->kodeexternal = $newId;
            $TP->reportdisplay = $request['diklatkategori'];
            $TP->diklatkategori = $request['diklatkategori'];
            $TP->kddiklatkategori = $newId;
            $TP->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Diklat Kategory";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'asepic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'asepic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarDiklatJurusan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DiklatJurusan::get();

        $result = array(
            'data' => $data,
            'by' => 'asepic',
        );
        return $result;
    }
    public function saveDiklatJurusan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $newId = DiklatJurusan::max('id') +1 ;
            if ($request['id'] == ''){
                $TP = new DiklatJurusan();
                $TP->id = $newId;
                $TP->kdprofile = $kdProfile;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = DiklatJurusan::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
            }
            $TP->statusenabled =  $request['statusenabled'];
            $TP->kodeexternal = $newId;
            $TP->reportdisplay = $request['diklatkategori'];
            $TP->diklatjurusan = $request['diklatkategori'];
            $TP->kddiklatjurusan = $newId;
            $TP->qtysksmin = 0;
            $TP->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Diklat Jurusan";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'asepic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'asepic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataComboMapKategoriPendidikanToProgramPendidikan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data1 = DiklatJurusan::where('kdprofile',$kdProfile)->get();
        // $data2 = Pendidikan::get();
        $data2 = DB::select(DB::raw("select * from diklatkategori_m
                where kdprofile = $kdProfile and statusenabled=true"));
        $data3 = JurusanPeminatan::where('kdprofile',$kdProfile)->get();

        $result = array(
            'data1' => $data1,
            'data2' => $data2,
            'data3' => $data3,
            'by' => 'asepic',
        );
        return $result;
    }
    public function getDataComboPesertaDidik(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataJenisKelamin = DB::select(DB::raw("select * from jeniskelamin_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $dataAgama = DB::select(DB::raw("select * from agama_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $dataInstitusipPendidikan = DB::select(DB::raw("select * from sdm_institusipendidikan_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $dataJurusan = DB::select(DB::raw("select * from sdm_jurusanpeminatan_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $datafakultas = DB::select(DB::raw("select * from sdm_fakultas_m
                where kdprofile = $kdProfile and statusenabled=true")
        );

        $result = array(
            'dataJenisKelamin' => $dataJenisKelamin,
            'dataAgama' => $dataAgama,
            'dataInstitusipPendidikan' => $dataInstitusipPendidikan,
            'dataJurusan' => $dataJurusan,
            'datafakultas' => $datafakultas,
            'by' => 'asepic',
        );
        return $result;
    }
    public function getDaftarPesertaDidik(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $nama = '';
        if ($request['nama'] != ''){
            $nama = " and a.nama ilike '%" . $request['nama'] . "%'";
        }
        $insts ='';
        if ($request['institusipendidikanfk'] != ''){
            $insts = ' and a.institusipendidikanfk = ' . $request['institusipendidikanfk'];
        }

        $data = DB::select(DB::raw("select a.*,ag.agama,jk.jeniskelamin,sins.institusipendidikan,sjur.jurusanpeminatan,sfak.fakultas ,
            a.tempatlahir || ', ' || to_char(a.tanggallahir,'yyyy-MM-dd')  as ttl
            from sdm_pesertadidik_t as a
            INNER JOIN agama_m as ag on a.agamafk=ag.id
            INNER JOIN jeniskelamin_m as jk on jk.id=a.jeniskelaminfk
            left JOIN sdm_institusipendidikan_m as sins on sins.id=a.institusipendidikanfk
            left JOIN sdm_jurusanpeminatan_m as sjur on sjur.id=a.jurusanpeminatanfk
            left JOIN sdm_fakultas_m as sfak on sfak.id=a.fakultasfk
            where a.kdprofile = $kdProfile and  a.statusenabled=true $nama $insts")
        );
        $result = array(
            'data' => $data,
            'by' => 'asepic',
        );
        return $result;
    }
    public function savePesertaDidik(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
//            $newId = PesertaDidik::max('id') +1 ;
            if ($request['norec'] == ''){
                $TP = new PesertaDidik();
//                $TP->id = $newId;
                $TP->kdprofile = $kdProfile;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = PesertaDidik::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $TP->statusenabled =  true;
            $TP->agamafk = $request['agamafk'];
            $TP->alamat = $request['alamat'];
            $TP->fakultasfk = $request['fakultasfk'];
            $TP->institusipendidikanfk = $request['institusipendidikanfk'];
            $TP->jeniskelaminfk = $request['jeniskelaminfk'];
            $TP->jurusanpeminatanfk = $request['jurusanpeminatanfk'];
            $TP->nama = $request['nama'];
            $TP->nim = $request['nim'];
            $TP->nomorhp = $request['nomorhp'];
            $TP->tanggallahir = $request['tanggallahir'];
            $TP->tempatlahir = $request['tempatlahir'];
            $TP->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Peserta Didik";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'asepic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'asepic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function hapusPesertaDidik(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $TP = PesertaDidik::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->update([
                'statusenabled' => false
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'asepic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'asepic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataComboTenagaPengajar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data1 = DB::select(DB::raw("select id,namalengkap from pegawai_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $data2= DB::select(DB::raw("select id,fakultas from sdm_fakultas_m
                where kdprofile = $kdProfile and statusenabled=true")
        );

        $result = array(
            'dataPegawai' => $data1,
            'dataProgramStudi' => $data2,
            'by' => 'asepic',
        );
        return $result;
    }
    public function getDaftarTenagaPengajar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::select(DB::raw("select a.*,sfak.fakultas,pg.namalengkap from tenagapengajar_m as a
            INNER JOIN sdm_fakultas_m as sfak on sfak.id=a.jurusanpeminatanfk
            INNER JOIN pegawai_m as pg on pg.id=a.pegawaifk
            where a.kdprofile = $kdProfile and a.statusenabled=true")
        );
        $result = array(
            'data' => $data,
            'by' => 'asepic',
        );
        return $result;
    }

    public function getDetailTenagaPengajar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idpegawai = $request['id'];
        $data = DB::select(DB::raw("select pg.id,pg.namalengkap,jk.jeniskelamin,ag.agama,pg.nohandphone,pg.tempatlahir + ', ' + format(pg.tgllahir ,'yyyy-MM-dd') as tempatlahir,
                jb.namajabatan,pg.tglmasuk,ru.namaruangan,pg.alamat
                 from  pegawai_m as pg 
                left JOIN jeniskelamin_m as jk on jk.id=pg.objectjeniskelaminfk
                left JOIN agama_m as ag on ag.id=pg.objectagamafk
                left JOIN jabatan_m as jb on jb.id=pg.objectjabatanfungsionalfk
                left JOIN ruangan_m as ru on ru.id=pg.objectruangankerjafk
            where pg.kdprofile = $kdProfile and pg.statusenabled=true and pg.id= $idpegawai")
        );
        $result = array(
            'data' => $data,
            'by' => 'asepic',
        );
        return $result;
    }
    public function saveTenagaPengajar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $newId = TenagaPengajar::max('id') +1 ;
            if ($request['norec'] == ''){
                $TP = new TenagaPengajar();
                $TP->id = $newId;
                $TP->kdprofile = $kdProfile;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = TenagaPengajar::where('id', $request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $TP->statusenabled =  true;
            $TP->jurusanpeminatanfk = $request['fakultasfk'];
            $TP->pegawaifk = $request['pegawaifk'];
            $TP->unit_kerja = $request['namaruangan'];
            $TP->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Tenaga Pengajar";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'asepic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'asepic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function hapusTenagaPengajar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $TP = TenagaPengajar::where('id', $request['id'])
                ->where('kdprofile', $kdProfile)
                ->update(
                [ 'statusenabled' => false]
            );
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'asepic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'asepic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataComboMOUPKS(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data1 = DB::select(DB::raw("select id,namalengkap from pegawai_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $data2= DB::select(DB::raw("select id,fakultas from sdm_fakultas_m
                where kdprofile = $kdProfile and statusenabled=true")
        );

        $result = array(
            'dataPegawai' => $data1,
            'dataProgramStudi' => $data2,
            'by' => 'asepic',
        );
        return $result;
    }
    public function getDaftarKegiatanPendidikan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::select(DB::raw("select a.*,spes.nama,dkat.diklatkategori,sfak.fakultas,djur.diklatjurusan,pg.namalengkap from sdm_kegiatanpendidikan_t as a
            INNER JOIN sdm_pesertadidik_t as spes on spes.norec=a.pesertafk
            INNER JOIN diklatkategori_m as dkat on dkat.id=a.diklatkatregorifk
            INNER JOIN sdm_fakultas_m as sfak on sfak.id=a.fakultasfk
            INNER JOIN diklatjurusan_m as djur on djur.id=a.diklatjurusanfk
            left JOIN tenagapengajar_m as tpeng on tpeng.id=a.tenagapengajarfk
            left JOIN pegawai_m as pg on pg.id=tpeng.pegawaifk
            where a.kdprofile = $kdProfile")
        );
        $result = array(
            'data' => $data,
            'by' => 'asepic',
        );
        return $result;
    }

    public function getDataComboKegiatanPendidikan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataInstitusipPendidikan = DB::select(DB::raw("select id,institusipendidikan from sdm_institusipendidikan_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $dataDiklatKategori = DB::select(DB::raw("select id,diklatkategori from diklatkategori_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $datafakultas = DB::select(DB::raw("select id,fakultas from sdm_fakultas_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $datadiklatjurusan = DB::select(DB::raw("select id,diklatjurusan from diklatjurusan_m
                where kdprofile = $kdProfile and statusenabled=true")
        );
        $dataTenagaPengajar = DB::select(DB::raw("select pg.namalengkap,a.id from tenagapengajar_m as a
            INNER JOIN sdm_fakultas_m as sfak on sfak.id=a.jurusanpeminatanfk
            INNER JOIN pegawai_m as pg on pg.id=a.pegawaifk
            where a.kdprofile = $kdProfile and a.statusenabled=true")
        );

        $result = array(
            'dataInstitusipPendidikan' => $dataInstitusipPendidikan,
            'dataDiklatKategori' => $dataDiklatKategori,
            'datafakultas' => $datafakultas,
            'datadiklatjurusan' => $datadiklatjurusan,
            'dataTenagaPengajar' => $dataTenagaPengajar,
            'by' => 'asepic',
        );
        return $result;
    }

    public function saveKegiatanPendidikan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
//            $newId = KegiatanPendidikan::max('id') +1 ;
            if ($request['norec'] == ''){
                $TP = new KegiatanPendidikan();
//                $TP->id = $newId;
                $TP->kdprofile = $kdProfile;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = KegiatanPendidikan::where('id', $request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $TP->statusenabled =  true;
            $TP->nokegiatan = '0001';
            $TP->pesertafk = $request['pesertafk'];
            $TP->diklatkatregorifk = $request['diklatkatregorifk'];
            $TP->fakultasfk = $request['fakultasfk'];
            $TP->diklatjurusanfk = $request['diklatjurusanfk'];
            $TP->tenagapengajarfk = $request['tenagapengajarfk'];
            $TP->tglmulai = $request['tglmulai'];
            $TP->nilaipraktek = $request['nilaipraktek'];
            $TP->nilaiujian = $request['nilaiujian'];
            $TP->surveykepuasan = $request['surveykepuasan'];
            $TP->biayapendidikan = $request['biayapendidikan'];
            $TP->tglpembayaran = $request['tglpembayaran'];
            $TP->nokwitansi = $request['nokwitansi'];
            $TP->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Kegiatan Pendidikan";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'asepic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'asepic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getRekapPegawai(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $paramkategId = '';
        $paramjenisPegId = '';
        $paramstatusPegId = '';
        
        if(isset($request['kategId']) && $request['kategId']!=''){
            $paramkategId = ' and pg.kategorypegawai ='.$request['kategId'];
        }
        if(isset($request['statusPegId']) && $request['statusPegId']!=''){
            $paramstatusPegId = ' and pg.objectstatuspegawaifk ='.$request['statusPegId'];
        }
        if(isset($request['jenisPegId']) && $request['jenisPegId']!=''){
            $paramjenisPegId = ' and pg.objectjenispegawaifk ='.$request['jenisPegId'];
        }

        $data = DB::select(DB::raw("
            select * from (
                select  'Jenis Kelamin' as kategory , x.tipe,count ( x.id) as total 
                from (
                select jp.jeniskelamin as tipe ,pg.namalengkap ,pg.id
                from pegawai_m  as pg
                left JOIN jeniskelamin_m as jp on jp.id =pg.objectjeniskelaminfk
                where pg.kdprofile = $kdProfile and pg.statusenabled=true
                $paramkategId
                $paramstatusPegId 
                $paramjenisPegId 
                )as x GROUP BY x.tipe

                union all

                select 'Jenis Kepegawaian' as kategory  ,x.tipe ,count ( x.id) as total from (
                select jp.jenispegawai as tipe ,pg.namalengkap ,pg.id
                from pegawai_m  as pg
                left JOIN jenispegawai_m as jp on jp.id =pg.objectjenispegawaifk
                where pg.kdprofile = $kdProfile and pg.statusenabled=true
                 $paramkategId
                $paramstatusPegId 
                $paramjenisPegId 
                )as x GROUP BY x.tipe

                union all 

                select 'Agama' as kategory  ,x.tipe ,count ( x.id) as total from (
                select case when jp.agama is null then '-' else jp.agama end as tipe ,pg.namalengkap ,pg.id
                from pegawai_m  as pg
                left JOIN agama_m as jp on jp.id =pg.objectagamafk
                where pg.statusenabled=true
                 $paramkategId
                $paramstatusPegId 
                $paramjenisPegId 
                )as x GROUP BY x.tipe

                union all 

                select 'Pendidikan' as kategory  ,x.tipe ,count ( x.id) as total from (
                select case when jp.pendidikan is null then '-' else jp.pendidikan end as tipe ,pg.namalengkap ,pg.id
                from pegawai_m  as pg
                left JOIN pendidikan_m as jp on jp.id =pg.objectpendidikanterakhirfk
                where pg.kdprofile = $kdProfile and pg.statusenabled=true
                 $paramkategId
                $paramstatusPegId 
                $paramjenisPegId 
                )as x GROUP BY x.tipe
                union all 


                select 'Jenis Usia' as kategory  ,z.tipe ,count (z.tipe) as total from (
                select x.id,x.namalengkap, x.tgllahir,case when x.umur is null then '-' 
                when x.umur <= 20 then 'dibawah 20 Tahun' 
                when x.umur >  20 and x.umur <= 30 then '21 s/d 30 Tahun'
                when x.umur > 30 and x.umur <= 40 then '31 s/d 40 Tahun'
                when x.umur > 40 and x.umur <= 50 then '41 s/d 50 Tahun'
                when x.umur > 50 then 'diatas 51 Tahun'
                end as  tipe from (
                select pg.id,pg.namalengkap,pg.tgllahir ,
                --CONVERT(int,ROUND(DATEDIFF(hour,pg.tgllahir,GETDATE())/8766.0,0)) AS umur
                date_part('year',age(pg.tgllahir))  AS umur
                from pegawai_m  as pg
                where pg.kdprofile = $kdProfile and pg.statusenabled=true
                 $paramkategId
                $paramstatusPegId 
                $paramjenisPegId 
                ) as x
                ) as z
                GROUP BY z.tipe
                 ) as xx order by xx.kategory,xx.total desc"));
        
        $result = array(
           
            'data' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }

    public function getDataInformasijabatanStruktural (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->leftJoin('jabatan_m as jb','jb.id','=','pg.objectjabatanstrukturalfk')
            ->select(\DB::raw("pg.id as pegawaiid,pg.namalengkap,pg.tempatlahir,pg.tgllahir,pg.nippns,pg.objectjabatanstrukturalfk,jb.namajabatan"))
            ->where('pg.statusenabled',true)
            ->where('pg.kdprofile',$kdProfile);

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pg.id','=',$request['idPegawai']);
        }

        if(isset($request['JabatanStrukturalfk']) && $request['JabatanStrukturalfk']!="" && $request['JabatanStrukturalfk']!="undefined"){
            $data = $data->where('pg.objectjabatanstrukturalfk','=',$request['JabatanStrukturalfk']);
        }

        $data = $data->orderBy('pg.namalengkap');
        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'ea@epic',
        );
        return $this->respond($result);
    }
     public function getPegawaiAll (Request $request) {
         $kdProfile = (int) $this->getDataKdProfile($request);
        $pegawai = \DB::table('pegawai_m as pg')
            ->leftJoin('jabatan_m as jb','jb.id','=','pg.objectjabatanfungsionalfk')
            ->leftJoin('subunitkerja_m as sub','sub.id','=','pg.objectsubunitkerjapegawaifk')
            
            ->select('pg.*','jb.namajabatan','sub.name as subunitkerja')
            ->where('pg.statusenabled',true)
            ->where('pg.kdprofile', $kdProfile)
            ->orderBy('pg.namalengkap')
            ->get();
        $jeniscuti = \DB::table('sdm_jeniscuti_m as pg')
            ->select('pg.*')
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('pg.name')
            ->get();

         $arrayName = array(
            'pegawai' => $pegawai,
            'jeniscuti' => $jeniscuti,
         );
        return $this->respond($arrayName);
    }
    public function savePermohonanCuti(Request $request){
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $noPlanning = $this->generateCodeBySeqTable(new PlanningPegawaiStatus, 'noplanning', 11, 'P'.date('ym'), $kdProfile);
        if ($noPlanning == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }

        try{

            if ($request['norec'] == ''){
                $TP = new PlanningPegawaiStatus();
                $TP->kdprofile = $kdProfile;
                $TP->statusenabled =true;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = PlanningPegawaiStatus::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->first();
                $noPlanning =  $TP->noplanning ;
                $detailss = ListTanggalCuti::where('objectplanningpegawaistatusfk',$TP->norec)->where('kdprofile', $kdProfile)->delete();
            }
            $TP->noplanning = $noPlanning;
            $TP->objectpegawaifk =   $request['objectpegawaifk'];
            $TP->objectstatuspegawaiplanfk = $request['statuspegawaiplan'];
            $TP->tglpengajuan = $request['tglpengajuan'];
            $TP->jumlahhari = $request['jumlah'];
            $TP->keterangan = $request['keteranganlain'];
            $TP->deskripsistatuspegawaiplan = $request['deskripsi'];

            $TP->save();
            $details22 = [];
            foreach ($request['listtanggal'] as $item ){
                $details = new ListTanggalCuti();
                $details->norec = $details->generateNewId();
                $details->kdprofile = $kdProfile;
                $details->statusenabled =true;
                $details->approvalstatus = null;
                $details->objectplanningpegawaistatusfk =$TP->norec;
                $details->tgl = $item['tgl'];
                $details->save();
                $details22 []= $details;
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'detail' => $details22,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPermohonanCuti (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data =\DB::table('planningpegawaistatus_t as pps')
            ->leftJoin('pegawai_m as pg','pg.id','=','pps.objectpegawaifk')
            ->leftJoin('sdm_jeniscuti_m as jc','jc.id','=','pps.objectstatuspegawaiplanfk')
            ->leftJoin('jabatan_m as jb','jb.id','=','pg.objectjabatanstrukturalfk')
            ->leftJoin('unitkerja_m as ukp','ukp.id','=','pg.objectunitkerjafk')
            ->select( 'pps.norec','pps.noplanning','pps.tglpengajuan','pps.objectpegawaifk','pps.objectstatuspegawaiplanfk',
                'pps.deskripsistatuspegawaiplan as desk','pps.keterangan','pg.namalengkap','jc.name as statuspermohonan',
                'jb.namajabatan','ukp.unitkerja','pps.approvalstatus')
            ->where('pps.kdprofile', $kdProfile)
            ->where('pps.statusenabled',true)
            ->orderBy('pps.tglpengajuan');

        if(isset($request['namaLengkap']) && $request['namaLengkap']!="" && $request['namaLengkap']!="undefined"){
            $data = $data->where('pg.namalengkap','ilike','%'.$request['namaLengkap'].'%');
        }
        if(isset($request['pgId']) && $request['pgId']!="" && $request['pgId']!="undefined"){
            $data = $data->where('pg.id','=',$request['pgId']);
        }
        if(isset($request['unitKerjaId']) && $request['unitKerjaId']!="" && $request['unitKerjaId']!="undefined"){
            $data = $data->where('ukp.id','=',$request['unitKerjaId']);
        }
        if(isset($request['subUnitId']) && $request['subUnitId']!="" && $request['subUnitId']!="undefined"){
            $data = $data->where('pg.objectsubunitkerjapegawaifk','=',$request['subUnitId']);
        }
        if(isset($request['kedudukanId']) && $request['kedudukanId']!="" && $request['kedudukanId']!="undefined"){
            $data = $data->where('pg.kedudukanfk','=',$request['kedudukanId']);
        }
        if(isset($request['statusPegawaiId']) && $request['statusPegawaiId']!="" && $request['statusPegawaiId']!="undefined"){
            $data = $data->where('pg.objectstatuspegawaifk','=',$request['statusPegawaiId']);
        }
        if(isset($request['qstatusApprove']) && $request['qstatusApprove']!="" && $request['qstatusApprove']!="undefined" ){
            if($request['qstatusApprove'] == 0){
                $data = $data->whereNull('pps.approvalstatus');
            }else{
                $data = $data->where('pps.approvalstatus','=',$request['qstatusApprove']);
            }
        }
        if(isset($request['qPermohonan']) && $request['qPermohonan']!="" && $request['qPermohonan']!="undefined"){
            $data = $data->where('jc.id','=',$request['qPermohonan']);
        }

        if(isset($request['tglMasuk']) && $request['tglMasuk']!="" && $request['tglMasuk']!="undefined"){
            $tgl = $request['tglMasuk'];
            $data = $data->whereRaw("format(pg.tglmasuk,'yyyy-MM') = '$tgl'");
        }
        if(isset($request['rows']) && $request['rows']!="" && $request['rows']!="undefined"){
            $data = $data->take($request['rows']);
        }
        $data = $data->get();
        $data2 =[];

        foreach ($data as $item){
            $norec= $item->norec;
            $details = DB::select(DB::raw("select * from listtanggalcuti_t where objectplanningpegawaistatusfk='$norec'"));
            $tglVerif = [];
            foreach ($details as $itmde){
                if( $itmde-> approvalstatus == 1){
                    $tglVerif [] = array(
                        'norec' => $itmde->norec,
                        'tgl' => $itmde->tgl,
                        'approvalstatus' => $itmde->approvalstatus,
                        'objectplanningpegawaistatusfk' => $itmde->objectplanningpegawaistatusfk
                    );
                }

            }

            $data2 [] =  array(
                'norec' => $item->norec,
                'noplanning' => $item->noplanning,
                'tglpengajuan' => $item->tglpengajuan,
                'objectpegawaifk' => $item->objectpegawaifk,
                'objectstatuspegawaiplanfk' => $item->objectstatuspegawaiplanfk,
                'desk' => $item->desk,
                'keterangan' => $item->keterangan,
                'namalengkap' => $item->namalengkap,
                'statuspermohonan' => $item->statuspermohonan,
                'namajabatan' => $item->namajabatan,
                'unitkerja' => $item->unitkerja,
                'approvalstatus' => $item->approvalstatus,
                'listtanggal' => $details,
                'listtanggalapprove' => $tglVerif,

            );
        }
        $result = array(
            'data'  => $data2,
            'as' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function deletePermohonanCuti(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            PlanningPegawaiStatus::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->update(
                ['statusenabled' =>  false]
            );


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function verifCuti(Request $request){
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try{

            foreach ($request['data'] as $item ){
                $data = ListTanggalCuti::where('norec', $item['norec'])->where('kdprofile', $kdProfile)->first();
                $details = ListTanggalCuti::where('norec', $item['norec'])->where('kdprofile', $kdProfile)->update(
                    [ 'approvalstatus' => 1 ]
                );

                $head = PlanningPegawaiStatus::where('norec', $data->objectplanningpegawaistatusfk )
                    ->where('kdprofile', $kdProfile)
                    ->update(
                    [ 'approvalstatus' => 1 ]
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
                'status' => 201,
                'message' => $transMessage,
//                'result' => $TP,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function unverifCuti(Request $request){
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try{

            $data = PlanningPegawaiStatus::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update(
                [ 'approvalstatus' => null ]
            );
            $detail =  ListTanggalCuti::where('objectplanningpegawaistatusfk',$request['norec'] )
                ->where('kdprofile', $kdProfile)
                ->update(
                [ 'approvalstatus' => null ]
            );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'result' => $TP,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function savePermohonanCutiBersama(Request $request){
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try{

            $pegawai = Pegawai::where('statusenabled',true)->get();
            $tanggals = $request['listtanggal'];
            $arrayTgl = [];
            foreach ($tanggals as $tgl){
                $arrayTgl [] = $tgl['tgl'];
            }
            $delete =\DB::table('listtanggalcuti_t as list')
                ->select('pps.norec')
                ->Join('planningpegawaistatus_t as pps','pps.norec','=','list.objectplanningpegawaistatusfk')
                ->where('list.kdprofile', $kdProfile)
                ->where('pps.objectstatuspegawaiplanfk',7)
                ->whereIn('list.tgl',$arrayTgl)
                ->get();
            if(count($delete) > 0){
                $arrNorec=[];
                foreach ($delete as $del){
                    $arrNorec[] = $del->norec;
                }
                $update = PlanningPegawaiStatus::whereIn('norec',$arrNorec)->delete();

            }

//            return $this->respond($delete);

            foreach ($pegawai as $pegawais){
                $noPlanning = $this->generateCodeBySeqTable(new PlanningPegawaiStatus, 'noplanning', 11, 'P'.date('ym'), $kdProfile);
                if ($noPlanning == ''){
                    $transMessage = "Gagal mengumpukan data, Coba lagi.!";
                    DB::rollBack();
                    $result = array(
                        "status" => 400,
                        "message"  => $transMessage,
                        "as" => 'as@epic',
                    );
                    return $this->setStatusCode($result['status'])->respond($result, $transMessage);
                }


                $TP = new PlanningPegawaiStatus();
                $TP->kdprofile = $kdProfile;
                $TP->statusenabled =true;
                $TP->norec = $TP->generateNewId();
                $TP->noplanning = $noPlanning;
                $TP->objectpegawaifk =  $pegawais->id;
                $TP->objectstatuspegawaiplanfk = 7;
                $TP->tglpengajuan = date('Y-m-d H:i:s');
//                $TP->jumlahhari = $request['jumlah'];
                $TP->keterangan = 'Cuti Bersama';
                $TP->approvalstatus = 1;
//                $TP->deskripsistatuspegawaiplan = $request['deskripsi'];

                $TP->save();

                foreach ($request['listtanggal'] as $item ){
                    $details = new ListTanggalCuti();
                    $details->norec = $details->generateNewId();
                    $details->kdprofile = $kdProfile;
                    $details->statusenabled =true;
                    $details->approvalstatus = 1;
                    $details->objectplanningpegawaistatusfk =$TP->norec;
                    $details->tgl = $item['tgl'];
                    $details->save();
                }
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getAbsesnsiPegawai (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->Join('sdm_absensipegawai_t as abn','abn.pegawaifk','=','pg.id')
            ->select('pg.namalengkap','abn.*')
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->where('abn.statusenabled',true);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('abn.jammasuk', '>=', $request['tglAwal']);
        }

        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('abn.jammasuk', '<=', $tgl);
        }

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pg.id','=',$request['idPegawai']);
        }
        if(isset($request['bulan']) && $request['bulan']!="" && $request['bulan']!="undefined"){
            $bln = $request['bulan'];
            $data = $data->whereRaw("to_char(abn.jammasuk,'yyyy-MM')= '$bln'");
        }

        $data = $data->orderBy('abn.jammasuk');
        $data = $data->get();



        $result = array(
            'data'  => $data,
            'as' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function saveAbsensi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{

            $pegawaifk = $request['pegawaifk']['id'];
            $tglAbsen = substr( $request['absen'][0]['tglabsen'],0,7);
//            return $this->respond($tglAbsen);
//            return $this->respond($pegawaifk);
            $delete = DB::delete("delete from sdm_absensipegawai_t where kdprofille = $kdProfile and pegawaifk='$pegawaifk' and  bulan='$tglAbsen' ");

            foreach ($request['absen'] as $item){
                $TP = new SDM_AbsensiPegawai();
                $TP->kdprofile = $kdProfile;
                $TP->statusenabled =true;
                $TP->norec = $TP->generateNewId();
                $TP->jamkeluar =$item['jampulang']!=null? $item['tglabsen']. ' '.$item['jampulang']: null;
                $TP->jammasuk = $item['jammasuk']!= null ? $item['tglabsen']. ' '. $item['jammasuk'] : null;
                $TP->pegawaifk = $request['pegawaifk']['id'];
                $TP->tglhistori = date('Y-m-d H:i:s');
                $TP->mesinmasuk =$item['mesinmasuk'];
                $TP->mesinpulang = $item['mesinpulang'];
                $TP->bulan = substr($item['tglabsen'],0,7);
                $TP->status = $item['status'];
                $TP->kwk = $item['kwk'];

                $TP->save();

            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getPegawaiByUnitKerja (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('mapunitkerjatopegawai_m as mpkp')
            ->Join('pegawai_m as pg','pg.id','=','mpkp.pegawaifk')
            ->Join('unitkerjapegawai_m as uk','uk.id','=','mpkp.unitkerjafk')
//            ->leftJoin('sdm_kelompokshift_m as ks','ks.id','=','pg.objectshiftkerja')
//            ->select(\DB::raw("pg.id,pg.namalengkap,pg.objectshiftkerja,pg.objectunitkerjapegawaifk,uk.name as unitkerja,
//			                         ks.kelompokshiftkerja"))
            ->select(\DB::raw("pg.id,pg.namalengkap,mpkp.unitkerjafk,uk.name AS unitkerja"))
            ->where('mpkp.kdprofile', $kdProfile)
            ->where('pg.statusenabled',true)
            ->orderBy('pg.namalengkap');

        if(isset($request['idPegawai']) && $request['idPegawai']!="" && $request['idPegawai']!="undefined"){
            $data = $data->where('pg.id','=',$request['idPegawai']);
        }
        if(isset($request['unitkerjaid']) && $request['unitkerjaid']!="" && $request['unitkerjaid']!="undefined"){
            $data = $data->where('uk.id','=',$request['unitkerjaid']);
        }
        $data = $data->get();
        $result = [];
        $bulanTahun = $request['bulantahun'];
        foreach ($data as $key => $value) {
            $idPegawai= $value->id;
            $details = \DB::select(\DB::raw("select jd.*,jk.kode
                    from jadwalkerjapegawai_t as jd 
                    join jamkerja_m as jk on jk.id=jd.jadwalkerjafk
                    where jd.kdprofile = $kdProfile and to_char(tanggal,'yyyy-MM') = '$bulanTahun'
                    and pegawaifk='$idPegawai'
                    and jd.statusenabled=true
                    "));
            $result [] = array(
                'id' => $value->id,
                'namalengkap' => $value->namalengkap,
                'unitkerjafk' => $value->unitkerjafk,
                'unitkerja' => $value->unitkerja,
                'jadwalkerja' => $details,
              
            );
        }
        return $this->respond($result);
    }
    public function getJadwalAbasensiCbo (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $UnitKerja = UnitKerjaPegawai::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $SubUnitKerja = SubUnitKerja::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $DataUnitkerja=[];
        foreach ($UnitKerja as $item) {
            $detail = [];
            foreach ($SubUnitKerja as $item2) {
                if ($item->id == $item2->objectunitkerjapegawaifk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'name' => $item2->name,
                    );
                }
            }

            $DataUnitkerja[] = array(
                'id' => $item->id,
                'name' => $item->name,
                'subunit' => $detail,
            );
        }


        $Shift = \DB::table('shiftkerja_m as pk')
            ->where('pk.kdprofile', $kdProfile)
            ->where('pk.statusenabled', true)
            ->orderBy('pk.namashift')
            ->get();

        $jenisSK = \DB::table('jeniskeputusan_m as pk')
            ->where('pk.kdprofile', $kdProfile)
            ->where('pk.statusenabled', true)
            ->orderBy('pk.jeniskeputusan')
            ->get();

        $UnitKerjaPegawai = \DB::table('unitkerjapegawai_m as pk')
            ->where('pk.kdprofile', $kdProfile)
            ->where('pk.statusenabled', true)
//            ->where('pk.kdprofile', 12)
            ->orderBy('pk.name')
            ->get();

        $ShiftKerja = SdmShiftKerja::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $JamKerja = JamKerja::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        $result = array(
            'unitkerjapegawai' => $UnitKerjaPegawai,
            'subunitkerja' => $SubUnitKerja,
            'dataunitkerja' => $DataUnitkerja,
            'shiftkerja' => $ShiftKerja,
            'shiftpegawai' => $Shift,
            'jeniskeputusan' => $jenisSK,
            'jamkerja' => $JamKerja,
            'by' => 'er@epic',
        );

        return $this->respond($result);
    }
    public function getSuratKeputusan (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('suratkeputusan_m as sk')
            ->Join('jeniskeputusan_m as jk','jk.id','=','sk.objectjeniskeputusanfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','sk.objectpegawaiobjekskfk')
            ->select('sk.id','sk.namask','sk.keteranganlainnya','sk.nosk','sk.nosk_intern','sk.tglttsk as tgl','jk.jeniskeputusan',
                'sk.objectjeniskeputusanfk','sk.objectpegawaiobjekskfk as pegawaifk','pg.namalengkap','sk.namaexternal as filename' )
            ->where('sk.kdprofile', $kdProfile)
            ->where('sk.statusenabled',true)
            ->where('sk.nosk_intern','SDM')
            ->orderBy('sk.namask');

        $data = $data->get();
        $result = array(
            'data'  => $data,
            'as' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function saveSuratKeputusan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try {
            $newID = SuratKeputusan::max('id') + 1;
            if ($request['id'] == ""){
                $new = new SuratKeputusan();
                $new->id = $newID ;
                $new->kdprofile = $kdProfile;
                $new->statusenabled=true;
                $new->norec = $new->generateNewId();

            }else{
                $new = SuratKeputusan::where('id', $request['id'])
                    ->where('kdprofile', $kdProfile)
                    ->first();
                $newID = $new->id;
            }

            $upload = $request->file('file');
            if(!empty($upload)){
                $ext = $upload->getClientOriginalExtension();
                $filename = $request['nosk'] .'-' . $request['namask'].'.'.$ext;
                $new->namaexternal = $filename;
            }

            $new->objectjeniskeputusanfk=$request['jenissk'];
            $new->keteranganlainnya=$request['keterangan'];
            $new->namask =$request['namask'];
            $new->nosk = $request['nosk'];
            $new->nosk_intern = 'SDM';
            $new->objectpegawaiobjekskfk =$this->getCurrentUserID();
            $new->tglttsk = $request['tgl'];
            $new->save();
            $norec =  $new->id ;
            if(!empty($upload)) {
                $request->file('file')->move('SDM/SuratKeputusan/'.$newID,
                    $filename);
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus = 'true') {
            $transMessage = "Sukses ";
            \DB::commit();
            $result = array(
                "status" => 201,
                "norec" => $new,
                "by" => 'er@epic',
            );
        } else {
            $transMessage = "Simpan SK Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'er@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteSuratKeputusan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            $filename = $request['namafile'];
            $path = public_path('SDM/SuratKeputusan/'.$request['id'].'/');

            if (!\File::exists($path)) {
//                abort(404);
            }else{
                $file = \File::deleteDirectory($path);
            }

            SuratKeputusan::where('id', $request['id'])->where('kdprofile', $kdProfile)->delete();
            $transMessage = "Sukses ";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Hapus Gagal";
        }

        if ($transStatus != 'false') {

            \DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "by" => 'er@epic',
            );
        } else {
            DB::rollBack();
            $result = array(

                "status" => 400,
                "message" => $transMessage,
                "by" => 'er@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getShiftKerja (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::select(DB::raw("SELECT sh.id,sh.kodeexternal,sh.namashift,sh.jammasuk,sh.jampulang,
            sh.factorrate,ks.kelompokshiftkerja,sh.objectkelompokshiftfk,sh.waktuistirahat
             FROM shiftkerja_m as sh
            join sdm_kelompokshift_m as ks on sh.objectkelompokshiftfk=ks.id
            where sh.kdprofile = $kdProfile and sh.statusenabled=TRUE
            "));


        $result = array(
            'data'  => $data,
            'as' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function saveShiftKerja(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            if($request['id'] == ''){
                $newId = ShiftKerja::max('id') + 1;
                $newMod = new ShiftKerja();
                $newMod->id = $newId;
                $newMod->norec = $newMod->generateNewId();
                $newMod->kdprofile = $kdProfile;
            }else{
                $newMod = ShiftKerja::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();
            }
            if($request['statusenabled'] == 'true' ){
                $newMod->statusenabled = $request['statusenabled'];
                $newMod->kodeexternal = $request['kodeexternal'];
                $newMod->namashift = $request['namashift'];
                $newMod->jammasuk = $request['jammasuk'];
                $newMod->jampulang = $request['jampulang'];
                $newMod->factorrate = $request['factorrate'];
                $newMod->objectkelompokshiftfk = $request['kelompokshift']['id'];
                $newMod->waktuistirahat = $request['waktuistirahat'];
            }else{
                $newMod->statusenabled = $request['statusenabled'];
            }
            $newMod->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }

    public function getComboPegawai (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $paket = Pegawai::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        //$produk = Produk::where('statusenabled',true)->get();
        $produk = DB::table('pegawai_m as pg')
//            ->join('produk_m as prd','prd.id','=','hnp.objectprodukfk')
            ->select('pg.id','pg.nama')
            ->where('pg.statusenabled',true)
//            ->where('prd.statusenabled',true)
            ->orderBy('pg.nama')
            ->where('pg.kdprofile', $kdProfile)
            ->whereNotIn('pg.id',[2,890,3,1])
            ->distinct()
            ->get();
        $result = array(
            'paket' => $paket,
            'produk' => $produk,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }

    public function getMappingPegawai (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $paket = DB::table('mapunitkerjatopegawai_m as mukp')
            ->join('pegawai_m as pg','pg.id','=','mukp.pegawaifk')
            ->join('unitkerjapegawai_m as ukp','ukp.id','=','mukp.unitkerjafk')
            ->select('mukp.*','pg.nama','ukp.name')
            ->where('mukp.kdprofile', $kdProfile)
            ->where('mukp.statusenabled',true);

        if(isset($request['paketId']) && $request['paketId'] !='' ){
            $paket = $paket->where('mukp.unitkerjafk',$request['paketId']);
        }
        if(isset($request['namaProduk']) && $request['namaProduk'] !='' ){
            $paket = $paket->where('pg.nama','ilike','%'.$request['namaProduk'].'%');
        }
        $paket = $paket->get();
        $result = array(
            'data' => $paket,
            'as' => 'khris@epic'
        );
        return $this->respond($result);
    }

    public function saveMapPegawaiToUnit(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ( $request['details'] as $item){
                $kode[] = (double) $item['id'];

            }

            $hapus = MapPegawaiToUnit::where('statusenabled',true)
                ->where('kdprofile', $kdProfile)
                ->where('unitkerjafk',$request['paketId'])
                ->whereIn('pegawaifk',$kode)
                ->delete();
            foreach ( $request['details'] as $item){
                $map = new MapPegawaiToUnit();
                $map->id = MapPegawaiToUnit::max('id') + 1;
                $map->kdprofile = $kdProfile;
                $map->statusenabled = true;
                $map->norec =  substr(\Webpatser\Uuid\Uuid::generate(), 0, 32);
                $map->unitkerjafk = $request['paketId'];
                $map->pegawaifk = $item['id'];
                $map->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();

            $result = array(
                'status' => 201,
                'data' => $map,
                'as' => 'khris@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'khris@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function DeleteMapPegawaiToUnit(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item){
                MapPegawaiToUnit::where('id',$item['id'])->where('kdprofile', $kdProfile)->delete();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();

            $result = array(
                'status' => 201,
//                'data' => $map,
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
      public function saveJadwalKerjaPegawai(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            
            foreach ( $request['data'] as $item){
                $pegawaiId[] = (int) $item['pegawai']['id'];
                $jadwal[] = (int) $item['shift']['id'];
                $tanggal[] = $item['tanggal']['dateformat'];
            }
            // return $this->respond($pegawaiId);

            $hapus = JadwalKerjaPegawai::where('statusenabled',true)
                ->where('kdprofile', $kdProfile)
                ->where('unitkerjafk',$request['unitkerjafk'])
                ->whereIn('pegawaifk',$pegawaiId)
                ->whereIn('jadwalkerjafk',$jadwal)
                ->whereIn('tanggal',$tanggal)
                ->delete();

            foreach ($request['data'] as $key) {
                $new = new JadwalKerjaPegawai();
                $new->kdprofile = $kdProfile;
                $new->statusenabled = true;
                $new->norec =  $new->generateNewId();
                $new->pegawaifk = $key['pegawai']['id'];
                $new->jadwalkerjafk = $key['shift']['id'];
                $new->tanggal = $key['tanggal']['dateformat'];
                $new->unitkerjafk = $request['unitkerjafk'];
                $new->save();
            }


           $transStatus = 'true';
        } catch (\Exception $e) {
          $transStatus = 'false';
        }
        if ($transStatus = 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "norec" => $new,
                "by" => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'er@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataPelayananPetugas(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t AS apd','apd.noregistrasifk','=','pd.norec')
            ->join('pelayananpasien_t AS pp','pp.noregistrasifk','=','apd.norec')
            ->join('pelayananpasienpetugas_t AS ppp','ppp.pelayananpasien','=','pp.norec')
            ->leftJoin('jenispetugaspelaksana_m AS jpp','jpp.id','=','ppp.objectjenispetugaspefk')
            ->leftjoin('pasien_m AS pm','pm.id','=','pd.nocmfk')
            ->leftjoin('pegawai_m AS pg','pg.id','=','ppp.objectpegawaifk')
            ->leftjoin('produk_m AS pr','pr.id','=','pp.produkfk')
            ->leftJoin('detailjenisproduk_m AS djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftjoin('jenisproduk_m AS jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJoin('kelompokproduk_m AS kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftJoin('kelompokpasien_m AS kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('rekanan_m AS rk','rk.id','=','pd.objectrekananfk')
            ->leftJoin('ruangan_m AS ru','ru.id','=','apd.objectruanganfk')
            ->leftJoin('kelas_m AS kls','kls.id','=','apd.objectkelasfk')
            ->select(DB::raw("
                 pp.norec AS norec_pp,pd.tglregistrasi,pm.nocm,pm.namapasien,pd.noregistrasi,kps.kelompokpasien,rk.namarekanan,
                 ru.namaruangan,kls.namakelas,pp.tglpelayanan,jp.jenisproduk,pr.namaproduk,pp.jumlah,
                 pp.hargajual,pd.tglpulang,jpp.jenispetugaspe AS jenispelaksana,pg.namalengkap
            "))
            ->where('pd.kdprofile', $kdProfile)
            ->where('pd.statusenabled', true)
            ->whereNotNull('pd.tglpulang');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '<=', $request['tglAkhir']);
        }
        if(isset($request['jenisPetugasPe']) && $request['jenisPetugasPe'] != "" && $request['jenisPetugasPe'] != "undefined") {
            $data = $data->where('ppp.objectjenispetugaspefk', $request['jenisPetugasPe']);
        }
        if(isset($request['pgkArr']) && $request['pgkArr']!="" && $request['pgkArr']!="undefined"){
            $arrRuang = explode(',',$request['pgkArr']) ;
            $kodeRuang = [];
            foreach ( $arrRuang as $item){
                $kodeRuang[] = (int) $item;
            }
            $data = $data->whereIn('ppp.objectpegawaifk',$kodeRuang);
        }
        $data = $data->get();
        $norecPp = '';
        $details = [];
        foreach ($data as $ob){
//            return $this->respond($ob->norec_pp);
            $norecPp = $norecPp.",'".$ob->norec_pp . "'";
        }
        $norecPp = substr($norecPp, 1, strlen($norecPp)-1);

        if($norecPp!= ''){
            $PelayananPasienDetail = DB::select(DB::raw("
               SELECT ppd.pelayananpasien AS norec_pp,kh.komponenharga,ppd.jumlah,ppd.hargasatuan,ppd.hargadiscount,ppd.jasa
               FROM pelayananpasiendetail_t AS ppd
               INNER JOIN komponenharga_m AS kh ON kh.id = ppd.komponenhargafk
               WHERE ppd.kdprofile = $kdProfile and ppd.statusenabled = true AND ppd.pelayananpasien in ($norecPp) "));
            $i = 0;
            foreach ($data as $h){
                $data[$i]->details = [];
                foreach ($PelayananPasienDetail as $d){
                    if($data[$i]->norec_pp == $d->norec_pp){
                        $data[$i]->details[] = $d;
                    }
                }
                $i++;
            }
        }

//        foreach ($data as $item) {
//            $details = \DB::select(DB::raw("select kh.komponenharga,ppd.jumlah,ppd.hargasatuan,ppd.hargadiscount,ppd.jasa
//                        from pelayananpasiendetail_t AS ppd
//                        INNER JOIN komponenharga_m AS kh ON kh.id = ppd.komponenhargafk
//                        WHERE ppd.kdprofile = $kdProfile and ppd.statusenabled = true AND ppd.pelayananpasien =:norec"),
//                array(
//                    'norec' => $item->norec_pp,
//                )
//            );
//
//            $result[] = array(
//                'norec' => $item->norec_pp,
//                'tglregistrasi' => $item->tglregistrasi,
//                'nocm' => $item->nocm,
//                'namapasien' => $item->namapasien,
//                'noregistrasi' => $item->noregistrasi,
//                'kelompokpasien' => $item->kelompokpasien,
//                'namarekanan' => $item->namarekanan,
//                'namaruangan' => $item->namaruangan,
//                'namakelas' => $item->namakelas,
//                'tglpelayanan' => $item->tglpelayanan,
//                'jenisproduk' => $item->jenisproduk,
//                'namaproduk' => $item->namaproduk,
//                'jumlah' => $item->jumlah,
//                'hargajual' => $item->hargajual,
//                'jenispelaksana' => $item->jenispelaksana,
//                'namalengkap' => $item->namalengkap,
//                'tglpulang' => $item->tglpulang,
//                'details' => $details,
//            );
//        }

        if (count($data) == 0) {
            $result = [];
        }

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function deleteDataPegawai(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();

        $pegawai = \DB::table('pegawai_m')
                ->where('id', $request['idPegawai'])
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->first();

        try{
            if($request['idPegawai']!='') {
                $dataPG = Pegawai::where('id', $request['idPegawai'])
                    ->where('kdprofile', (int)$kdProfile)
                    ->update([
                            'statusenabled' => false,
                        ]
                    );

                /*Logging User*/
                $newId = LoggingUser::max('id');
                $newId = $newId +1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile= $kdProfile;
                $logUser->statusenabled=true;
                $logUser->jenislog = 'Hapus Pegawai';
                $logUser->noreff =$request['idPegawai'];
                $logUser->referensi='id pegawai';
                $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
                $logUser->tanggal = $tglAyeuna;
                $logUser->keterangan = 'Hapus Pegawai dengan Nama : '. $pegawai->namalengkap;
                $logUser->save();
                /*End Logging User*/
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "update status enabled ";
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}