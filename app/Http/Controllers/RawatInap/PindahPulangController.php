<?php

/**
 * Created by PhpStorm.
 * User: Ramdanegie
 * Date: 01/03/2018
 * Time: 16.20
 */
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 08/08/2019
 * Time: 15:55
 */

namespace App\Http\Controllers\RawatInap;

use App\Http\Controllers\ApiController;
use App\Master\Pasien;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\PasienDaftar;
use App\Transaksi\TempatTidur;
use App\Transaksi\StrukOrder;
use App\Transaksi\RegistrasiPelayananPasien;
use Illuminate\Http\Request;
use App\Http\Requests;

use App\Traits\Valet;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Transaksi\LogAcc;
use App\Traits\SettingDataFixedTrait;
use Carbon\Carbon;

class PindahPulangController extends ApiController
{
    use Valet, PelayananPasienTrait;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getComboPindahPulang(Request $request){

        $dataLogin = $request->all();
        $statusKeluar = \DB::table('statuskeluar_m as st')
            ->select('st.id','st.statuskeluar')
            ->where('st.statusenabled', true)
            ->orderBy('st.statuskeluar')
            ->get();
        $kondisiKeluar = \DB::table('kondisipasien_m as kp')
            ->select('kp.id','kp.kondisipasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kondisipasien')
            ->get();
        $kelas = \DB::table('kelas_m as kls')
            ->select('kls.id','kls.namakelas')
            ->where('kls.statusenabled', true)
            ->orderBy('kls.namakelas')
            ->get();
        $kamar = \DB::table('kamar_m as kmr')
            ->select('kmr.id', 'kmr.namakamar')
            ->where('kmr.statusenabled', true)
            ->orderBy('kmr.namakamar')
            ->get();
        $ruanganInap = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', [16,35,17])
            ->orderBy('ru.namaruangan')
            ->get();
        $statusPulang = \DB::table('statuspulang_m as sp')
            ->select('sp.id', 'sp.statuspulang')
            ->where('sp.statusenabled', true)
            ->orderBy('sp.statuspulang')
            ->get();
        $hubunganKeluarga = \DB::table('hubungankeluarga_m as sp')
            ->select('sp.id', 'sp.hubungankeluarga')
            ->where('sp.statusenabled', true)
            ->orderBy('sp.hubungankeluarga')
            ->get();
        $penyebabKematian = \DB::table('penyebabkematian_m as sp')
            ->select('sp.id', 'sp.penyebabkematian')
            ->where('sp.statusenabled', true)
            ->orderBy('sp.penyebabkematian')
            ->get();
        $pindah = \DB::table('statuspulang_m as sp')
            ->select('sp.id', 'sp.statuspulang')
            ->where('sp.statusenabled', true)
            ->where('sp.id',2)
            ->orderBy('sp.statuspulang')
            ->get();
        $result = array(
            'statuskeluar' => $statusKeluar,
            'kondisipasien' =>$kondisiKeluar,
            'kelas' =>$kelas,
            'ruanganinap' =>$ruanganInap,
            'kamar' =>$kamar,
            'statuspulang' =>$statusPulang,
            'hubungankeluarga'=> $hubunganKeluarga,
            'penyebabkematian' => $penyebabKematian,
            'datalogin' => $dataLogin,
            'pindah' => $pindah,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function getPindahPasienByNoreg($norec_pd,$norec_apd){
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.objectpekerjaanfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftjoin('pegawai_m as peg','peg.id','=','pd.objectpegawaifk')
            ->join('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->LEFTjoin('jenispelayanan_m as jpl','jpl.kodeinternal','=','pd.jenispelayanan')
            ->select('ps.nocm','ps.id as nocmfk','ps.noidentitas','ps.namapasien','pd.noregistrasi', 'pd.tglregistrasi','jk.jeniskelamin',
                'ps.tgllahir','alm.alamatlengkap','pdd.pendidikan','pk.pekerjaan','ps.notelepon','ps.objectjeniskelaminfk',
                'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd','pd.norec as norec_pd',
                'kps.kelompokpasien','kls.namakelas','apd.objectkelasfk','pd.objectkelompokpasienlastfk','pd.objectrekananfk',
                'rk.namarekanan','pd.objectruanganlastfk','jpl.jenispelayanan','apd.objectasalrujukanfk',
                'ru.kdinternal','jpl.kodeinternal as objectjenispelayananfk','pd.objectpegawaifk','pd.statuspasien','pd.objectruanganlastfk',
                'ps.qpasien as id_ibu',
                DB::raw('case when ru.objectdepartemenfk in (16,35,17) then \'true\' else \'false\' end as israwatinap')
            )
            ->where('pd.norec','=',$norec_pd)
            ->where('apd.norec','=',$norec_apd)
//            ->where('pd.objectruanganlastfk',$ruanganlast)
//            ->where('apd.objectruanganfk',$ruanganlast)
            ->whereNull('pd.tglpulang')
            ->get();

        return $this->respond($data);
    }
    public function getKelasByRuangan(Request $request) {
        $data = \DB::table('mapruangantokelas_m as mrk')
            ->join ('ruangan_m as ru','ru.id','=','mrk.objectruanganfk')
            ->join ('kelas_m as kl','kl.id','=','mrk.objectkelasfk')
            ->select('kl.id','kl.namakelas','ru.id as id_ruangan','ru.namaruangan')
            ->where('mrk.objectruanganfk', $request['idRuangan'])
            ->get();

        $result = array(
            'kelas'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getKamarByKelasRuangan(Request $request) {
        $data = \DB::table('kamar_m as kmr')
            ->join ('ruangan_m as ru','ru.id','=','kmr.objectruanganfk')
            ->join ('kelas_m as kl','kl.id','=','kmr.objectkelasfk')
            ->select('kmr.id','kmr.namakamar','kl.id as id_kelas','kl.namakelas','ru.id as id_ruangan',
                'ru.namaruangan','kmr.jumlakamarisi','kmr.qtybed')
            ->where('kmr.objectruanganfk', $request['idRuangan'])
            ->where('kmr.objectkelasfk', $request['idKelas'])
            ->where('kmr.statusenabled',true)
            ->get();

        $result = array(
            'kamar'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getNoBedByKamar(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('tempattidur_m as tt')
            ->join ('statusbed_m as sb','sb.id','=','tt.objectstatusbedfk')
            ->join ('kamar_m as km','km.id','=','tt.objectkamarfk')
            ->select('tt.id','sb.statusbed','tt.reportdisplay')
            ->where('tt.objectkamarfk', $request['idKamar'])
            ->where('km.statusenabled',true)
            ->where('tt.kdprofile', (int)$kdProfile)
            ->get();

        $result = array(
            'bed'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function savePulangPasien(Request $request) {
        $detLogin =$request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $r_NewPD=$request['pasiendaftar'];
        $r_NewAPD=$request['antrianpasiendiperiksa'];
        DB::beginTransaction();
        //##Update Pasiendaftar##
        try{

            if ( $r_NewPD['norec_pd'] != 'undefined' && $r_NewPD['noregistrasi']!= 'undefined' && $r_NewPD['objectstatuskeluarfk']==5) {
                $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                    ->update([
                            'objecthubungankeluargaambilpasienfk' => $r_NewPD['objecthubungankeluargaambilpasienfk'],
                            'objectkondisipasienfk' => $r_NewPD['objectkondisipasienfk'],
                            'namalengkapambilpasien' => $r_NewPD['namalengkapambilpasien'],
                            'objectpenyebabkematianfk' => $r_NewPD['objectpenyebabkematianfk'],
                            'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                            'objectstatuspulangfk' => $r_NewPD['objectstatuspulangfk'],
                            'tglmeninggal' => $r_NewPD['tglmeninggal'],
                            'tglpulang' => $r_NewPD['tglpulang'],
                        ]
                    );
            }
            if ( $r_NewPD['norec_pd'] != 'undefined' && $r_NewPD['noregistrasi']!= 'undefined' && $r_NewPD['objectstatuskeluarfk']==5 && $r_NewPD['objectpenyebabkematianfk'] ==4) {
                $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                    ->update([
                            'objecthubungankeluargaambilpasienfk' => $r_NewPD['objecthubungankeluargaambilpasienfk'],
                            'objectkondisipasienfk' => $r_NewPD['objectkondisipasienfk'],
                            'namalengkapambilpasien' => $r_NewPD['namalengkapambilpasien'],
                            'objectpenyebabkematianfk' => $r_NewPD['objectpenyebabkematianfk'],
                            'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                            'objectstatuspulangfk' => $r_NewPD['objectstatuspulangfk'],
                            'tglmeninggal' => $r_NewPD['tglmeninggal'],
                            'tglpulang' => $r_NewPD['tglpulang'],
                            'keteranganpenyebabkematian' => $r_NewPD['keterangankematian'],
                        ]
                    );
            }
            if ( $r_NewPD['norec_pd'] != 'undefined' && $r_NewPD['noregistrasi']!= 'undefined' && $r_NewPD['objectstatuskeluarfk']!=5) {
                $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                    ->update([
                            'objecthubungankeluargaambilpasienfk' => $r_NewPD['objecthubungankeluargaambilpasienfk'],
                            'objectkondisipasienfk' => $r_NewPD['objectkondisipasienfk'],
                            'namalengkapambilpasien' => $r_NewPD['namalengkapambilpasien'],
//                        'objectpenyebabkematianfk' => $r_NewPD['objectpenyebabkematianfk'],
                            'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                            'objectstatuspulangfk' => $r_NewPD['objectstatuspulangfk'],
//                        'tglmeninggal' => $r_NewPD['tglmeninggal'],
                            'tglpulang' => $r_NewPD['tglpulang'],
                        ]
                    );
            }
            if ($r_NewPD['nocmfk'] != 'undefined' && $r_NewPD['objectstatuskeluarfk']== 5) {
                $updatePS= Pasien::where('id', $r_NewPD['nocmfk'])
                    ->update([
                            'tglmeninggal' => $r_NewPD['tglmeninggal'],
                        ]
                    );

            }
            if ($r_NewAPD['norec_apd'] != 'undefined') {
                $updateAPD= AntrianPasienDiperiksa::where('norec', $r_NewAPD['norec_apd'])
                    ->update([
                            'tglkeluar' => $r_NewPD['tglpulang'],
                        ]
                    );

                $ruangasal = DB::select(DB::raw("select * from antrianpasiendiperiksa_t 
                         where norec=:norec and objectruanganfk=:objectruanganasalfk;" ),
                    array(
                        'norec' => $r_NewAPD['norec_apd'],
                        'objectruanganasalfk'=>$r_NewAPD['objectruanganlastfk'],
                    )
                );

                //update statusbed jadi Kosong
                foreach ($ruangasal as $Hit){
                    TempatTidur::where('id',$Hit->nobed)->update(['objectstatusbedfk'=>2]);
                }

            }

            if ($request['strukorder']['norecorder'] != ''){
                $updateSO= StrukOrder::where('norec', $request['strukorder']['norecorder'])
                    ->update([
                            'statusorder' => 4,
                            'tglvalidasi' => $tglAyeuna
                        ]
                    );
            }

            if ( $r_NewPD['norec_pd'] != 'undefined' && $r_NewPD['noregistrasi']!= 'undefined') {
                // $updateRPP = \DB::table('registrasipelayananpasien_t')
                //     ->select('noregistrasifk','objectruanganfk','tglkeluar')
                //     ->where('objectruanganfk', $r_NewAPD['objectruanganlastfk'])
                //     ->where('noregistrasifk', $r_NewPD['norec_pd'])
                //     ->update([
                //             'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                //             'tglkeluar' => $r_NewPD['tglpulang'],
                //             'tglkeluarrencana' => $r_NewPD['tglpulang'],
                //         ]
                //     );
                $updateRPP =RegistrasiPelayananPasien::where('objectruanganfk', $r_NewAPD['objectruanganlastfk'])
                    ->where('noregistrasifk', $r_NewPD['norec_pd'])
                    ->update([
                            'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                            'tglkeluar' => $r_NewPD['tglpulang'],
                            'tglkeluarrencana' => $r_NewPD['tglpulang'],
                        ]
                    );

            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }



        if ($transStatus == 'true') {
            DB::commit();
            $transMessage = 'SUKSES';
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'dataPD' => $dataPD,
//                'dataAPD' => $dataAPD,
//                'dataRPP'=> $dataRPP,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "GAGAL";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
//                'dataPD' => $dataPD,
//                'dataAPD' => $dataAPD,
//                'dataRPP'=> $dataRPP,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function savePindahPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $detLogin =$request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $r_NewPD=$request['pasiendaftar'];
        $r_NewAPD=$request['antrianpasiendiperiksa'];

        DB::beginTransaction();
        try{
            //##Update Pasiendaftar##
            if ($r_NewPD['norec_pd'] != 'null' || $r_NewPD['norec_pd'] != 'undefined') {
                $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                    ->update([
                            'objectruanganlastfk' => $r_NewPD['objectruanganlastfk'],
                            'objectkelasfk' => $r_NewPD['objectkelasfk'],
                        ]
                    );
            }
            if ($r_NewAPD['norec_apd'] != 'null' || $r_NewAPD['norec_apd'] != 'undefined') {
                $updateAPD= AntrianPasienDiperiksa::where('norec', $r_NewAPD['norec_apd'])
                    ->update([
                            'tglkeluar' => $r_NewAPD['tglmasuk'],
                        ]
                    );


                $ruangasal = DB::select(DB::raw("select * from antrianpasiendiperiksa_t 
                         where noregistrasifk=:noregistrasifk and objectruanganfk=:objectruanganasalfk;" ),
                    array(
                        'noregistrasifk' => $r_NewPD['norec_pd'],
                        'objectruanganasalfk'=>$r_NewPD['objectruanganasalfk'],
                    )
                );

                //update statusbed jadi Kosong
                foreach ($ruangasal as $Hit){
                    TempatTidur::where('id',$Hit->nobed)->update(['objectstatusbedfk'=>2]);
                }
            }

            if ($request['strukorder']['norecorder'] != ''){
                $updateSO= StrukOrder::where('norec', $request['strukorder']['norecorder'])
                    ->update([
                            'statusorder' => 1,
                            'tglvalidasi' => $tglAyeuna
                        ]
                    );
            }

            $countNoAntrian = AntrianPasienDiperiksa::where('objectruanganfk',$r_NewPD['objectruanganlastfk'])
                ->where('tglregistrasi', '>=', $r_NewPD['tglregistrasidate'].' 00:00')
                ->where('tglregistrasi', '<=', $r_NewPD['tglregistrasidate'].' 23:59')
                ->count('norec');
            $noAntrian = $countNoAntrian + 1;
            //##Save Antroan Pasien Diperiksa##
//        try{
            $pd = PasienDaftar::where('norec',$r_NewPD['norec_pd'])->first();
            $dataAPD =new AntrianPasienDiperiksa;
            $dataAPD->norec = $dataAPD->generateNewId();
            $dataAPD->kdprofile = $kdProfile;
            $dataAPD->statusenabled = true;
            $dataAPD->objectruanganfk = $r_NewAPD['objectruanganlastfk'];
            $dataAPD->objectasalrujukanfk =  $r_NewAPD['objectasalrujukanfk'];
            $dataAPD->objectkamarfk = $r_NewAPD['objectkamarfk'];
            $dataAPD->objectkasuspenyakitfk = null;
            $dataAPD->objectkelasfk = $r_NewAPD['objectkelasfk'];
            $dataAPD->noantrian = $noAntrian; //count tgl pasien perruanga
            $dataAPD->nobed = $r_NewAPD['nobed'];
//          $dataAPD->nomasuk = '';
            $dataAPD->noregistrasifk = $r_NewPD['norec_pd'];
//          $dataAPD->objectpegawaifk = $r_NewAPD['objectpegawaifk'];
//          $dataAPD->prefixnoantrian = null;
            $dataAPD->statusantrian = 0;
            $dataAPD->statuskunjungan =$r_NewPD['statuspasien'];
            $dataAPD->statuspasien = 1;
//          $dataAPD->statuspenyakit =null;
//          $dataAPD->objectstrukorderfk = null;
//          $dataAPD->objectstrukreturfk = null;
            $dataAPD->tglregistrasi =  $pd->tglregistrasi;//$r_NewAPD['tglregistrasi'];
//          $dataAPD->tgldipanggildokter = null;
//          $dataAPD->tgldipanggilsuster = null;
            $dataAPD->objectruanganasalfk = $r_NewPD['objectruanganasalfk'];
            $dataAPD->tglkeluar = null;
            $dataAPD->tglmasuk =$r_NewAPD['tglmasuk'];
            $dataAPD->israwatgabung = $r_NewAPD['israwatgabung'];

            $dataAPD->save();

            //update statusbed jadi Isi
            TempatTidur::where('id',$r_NewAPD['nobed'])->update(['objectstatusbedfk'=>1]);

////            $transStatus = 'true';
////        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "simpan Antrian Pasien";
//        }
//      try{
            //##Save Registrasi Pel Pasien##
            $dataRPP = new RegistrasiPelayananPasien();
            $dataRPP->norec = $dataRPP->generateNewId();;
            $dataRPP->kdprofile = $kdProfile;
            $dataRPP->statusenabled = true;
            $dataRPP->objectasalrujukanfk = $r_NewAPD['objectasalrujukanfk'];
//          $dataRPP->objecthasiltriasefk =null;
            $dataRPP->israwatgabung = $r_NewAPD['israwatgabung'];;
            $dataRPP->objectkamarfk =$r_NewAPD['objectkamarfk'];
//          $dataRPP->objectkasuspenyakitfk = null;
//          $dataRPP->kddokter = null;
//          $dataRPP->kddokterperiksanext =  null;
            $dataRPP->objectkelasfk = $r_NewAPD['objectkelasfk'];
            $dataRPP->objectkelaskamarfk = $r_NewAPD['objectkelasfk'];
//          $dataRPP->objectkelaskamarrencanafk =null;
//          $dataRPP->objectkelaskamartujuanfk =null;
//          $dataRPP->objectkelasrencanafk = null;
//          $dataRPP->objectkelastujuanfk = null;
            $dataRPP->kdpenjaminpasien = 0;
//          $dataRPP->objectkeadaanumumfk = null;
            $dataRPP->objectkelompokpasienfk = $r_NewPD['objectkelompokpasienlastfk'];
//          $dataRPP->keteranganlainnyaperiksanext = null;
            $dataRPP->keteranganlainnyarencana = $r_NewAPD['keteranganpindah'];
//          $dataRPP->kodenomorbuktiperjanjian = null;
//          $dataRPP->objectkondisipasienfk = null;
//          $dataRPP->namatempattujuan =null;
//          $dataRPP->noantrian = null;
            $dataRPP->noantrianbydokter = 0;
//          $dataRPP->nobed = null;
//          $dataRPP->nobedtujuan =  null;
            $dataRPP->nocmfk = $r_NewPD['nocmfk'];
            $dataRPP->noregistrasifk = $r_NewPD['norec_pd'];
//          $dataRPP->prefixnoantrian =  '1';
            $dataRPP->objectruanganasalfk = $r_NewAPD['objectruanganasalfk'];
            $dataRPP->objectruanganfk = $r_NewAPD['objectruanganlastfk'];
//          $dataRPP->objectruanganperiksanextfk = $r_NewAPD['objectruanganfk'];
//          $dataRPP->objectruanganrencanafk =  null;
//          $dataRPP->objectruangantujuanfk =  null;
            $dataRPP->objectstatuskeluarfk = $r_NewPD['objectstatuskeluarfk'];
//            $dataRPP->objectstatuskeluarrencanafk =  $r_NewPD['objectstatuskeluarfk'];
//          $dataRPP->statuspasien =  0;
            $dataRPP->objecttempattidurfk = $r_NewAPD['nobed'];
//          $dataRPP->tglkeluar = null;
//          $dataRPP->tglkeluarrencana = null;
            $dataRPP->tglmasuk = $r_NewAPD['tglmasuk'];
//          $dataRPP->tglperiksanext = null;
            $dataRPP->tglpindah = $r_NewAPD['tglmasuk'];
//          $dataRPP->objecttransportasifk = null;
//          $dataRPP->objectdetailkamarfk = null;
            $dataRPP->save();
            $dataNorecRPP=$dataRPP->norec;

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Registrasi Pelayanan Pasien";
        }

        if ($transStatus == 'true') {
            DB::commit();
            $transMessage = 'SUKSES';
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'dataPD' => $dataPD,
                'dataAPD' => $dataAPD,
                'dataRPP'=> $dataRPP,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "GAGAL";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
//                'dataPD' => $dataPD,
                'dataAPD' => $dataAPD,
                'dataRPP'=> $dataRPP,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function getPindahPasienByNoreg2(Request $r){
         $kdProfile = $this->getDataKdProfile($r);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.objectpekerjaanfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftjoin('pegawai_m as peg','peg.id','=','pd.objectpegawaifk')
            ->join('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->LEFTjoin('jenispelayanan_m as jpl','jpl.kodeinternal','=','pd.jenispelayanan')
            ->select('ps.nocm','ps.id as nocmfk','ps.noidentitas','ps.namapasien','pd.noregistrasi', 'pd.tglregistrasi','jk.jeniskelamin',
                'ps.tgllahir','alm.alamatlengkap','pdd.pendidikan','pk.pekerjaan','ps.notelepon','ps.objectjeniskelaminfk',
                'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd','pd.norec as norec_pd',
                'kps.kelompokpasien','kls.namakelas','apd.objectkelasfk','pd.objectkelompokpasienlastfk','pd.objectrekananfk',
                'rk.namarekanan','pd.objectruanganlastfk','jpl.jenispelayanan','apd.objectasalrujukanfk',
                'ru.kdinternal','jpl.kodeinternal as objectjenispelayananfk','pd.objectpegawaifk','pd.statuspasien','pd.objectruanganlastfk',
                'ps.qpasien as id_ibu',
                DB::raw('case when ru.objectdepartemenfk in (16,35,17) then \'true\' else \'false\' end as israwatinap')
            )
            ->where('pd.norec','=',$r['norec_pd'])
            ->where('apd.norec','=',$r['norec_apd'])
           ->where('pd.kdprofile',$kdProfile)
//            ->where('apd.objectruanganfk',$ruanganlast)
            ->whereNull('pd.tglpulang')
            ->get();

        return $this->respond($data);
    }
}