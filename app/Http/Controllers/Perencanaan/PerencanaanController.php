<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 11/16/2019
 * Time: 3:05 PM
 */

namespace App\Http\Controllers\Perencanaan;

use App\Http\Controllers\ApiController;
use App\Master\KelompokAnggaranHead;
use App\Master\KelompokAnggaranKedua;
use App\Master\KelompokAnggaranKeempat;
use App\Master\KelompokAnggaranKetiga;
use App\Master\KelompokAnggaranPertama;
use App\Master\KelompokAnggaranKelima;
use App\Master\KelompokAnggaranKeenam;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\MataAnggaran;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\RiwayatRealisasi;
use App\Transaksi\StrukRealisasi;
use App\Transaksi\StrukVerifikasi;
use App\Transaksi\StrukVerifikasiAnggaran;
use Illuminate\Http\Request;
use DB;

use App\Transaksi\StrukOrder;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\OrderProduk;
use App\Master\Pegawai;
use App\Traits\Valet;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Parent_;
use Webpatser\Uuid\Uuid;

class PerencanaanController extends ApiController {
    use Valet;
    public function __construct(){
        parent::__construct($skip_authentication=false);
    }


    public function getPokRKAKL(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data= \DB::table('mataanggaran_t as rk')
            ->JOIN ('kelompokanggaranhead_m as k0','k0.id','=','rk.objectheadfk')
            ->JOIN ('kelompokanggaranpertama_m as k1','k1.id','=','rk.objectchildpertamafk')
            ->JOIN ('kelompokanggarankedua_m as k2','k2.id','=','rk.objectchildkeduafk')
            ->JOIN ('kelompokanggaranketiga_m as k3','k3.id','=','rk.objectchildketigafk')
            ->JOIN ('kelompokanggarankeempat_m as k4','k4.id','=','rk.objectchildkeempatfk')
            ->select( 'rk.norec','k0.kdkelompokhead','k0.id as id0','k0.kelompokhead','k1.id as id1','k1.childpertama',
                'k2.id as id2','k2.childkedua','k3.id as id3','k3.childketiga',
                'k4.id as id4','k4.childkeempat','rk.mataanggaran','rk.saldoawalblu','rk.saldoawalrm',
                'k1.kdchildpertama','k2.kdchildkedua','k3.kdchildketiga','k4.kdchildkeempat',
                'rk.tahun','rk.tglanggaran','rk.revisidivake')
            ->where('rk.kdprofile', $idProfile);

        $saldoBlu = $data->sum('rk.saldoawalblu');
        $saldoRm = $data->sum('rk.saldoawalrm');

        $filter = $request->all();
//        if(isset($filter['tglAwal']) && $filter['tglAwal']!="" && $filter['tglAwal']!="undefined"){
//            $data = $data->where('rk.tglanggaran','>=', $filter['tglAwal']);
//        }
//        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!="" && $filter['tglAkhir']!="undefined"){
//            $tgl= $filter['tglAkhir'];//." 23:59:59";
//            $data = $data->where('rk.tglanggaran','<=', $tgl);
//        }
        if(isset($filter['tahun']) && $filter['tahun']!="" && $filter['tahun']!="undefined"){
            $data = $data->where('rk.tahun','=', $filter['tahun']);
        }
        if(isset($filter['revisike']) && $filter['revisike']!="" && $filter['revisike']!="undefined"){
            $data = $data->where('rk.revisidivake','=', $filter['revisike']);
        }

        if(isset($filter['childPertama']) && $filter['childPertama']!="" && $filter['childPertama']!="undefined"){
            $data = $data->where('k1.id','=', $filter['childPertama']);
        }
        if(isset($filter['childKedua']) && $filter['childKedua']!="" && $filter['childKedua']!="undefined"){
            $data = $data->where('k2.id','=', $filter['childKedua']);
        }
        if(isset($filter['childKetiga']) && $filter['childKetiga']!="" && $filter['childKetiga']!="undefined"){
            $data = $data->where('k3.id','=', $filter['childKetiga']);
        }
        if(isset($filter['childKeempat']) && $filter['childKeempat']!="" && $filter['childKeempat']!="undefined"){
            $data = $data->where('k4.id','=', $filter['childKeempat']);
        }
        if(isset($filter['childMataAnggaran']) && $filter['childMataAnggaran']!="" && $filter['childMataAnggaran']!="undefined"){
            $data = $data->where('rk.mataanggaran','ilike', '%'.$filter['childMataAnggaran'].'%');
        }

        $data=$data->take(20);
        $data=$data->get();

        $i0=0;
        $s0=0;
        $s1=0;
        $s2=0;
        $a0=[];
        $a1=[];
        $a2=[];
        $a3=[];
        $a4=[];
        $a5=[];
        $t0=0.0;
//        ini_set('max_execution_time', 1000); //6 minutes
        foreach ($data as $item){
            $s0=0;
            $i0=0;
            foreach ($a0 as $a){
                if ($item->id0 == $a0[$i0]['id0']){
                    $s0=1;
                    $a0[$i0]['totalblu'] = $a0[$i0]['totalblu'] + $item->saldoawalblu;
                    $a0[$i0]['totalrm'] = $a0[$i0]['totalrm'] + $item->saldoawalrm;
                }
                $i0 = $i0 + 1;
            }

            foreach ($data as $item1){
                $s1=0;
                $i1=0;
                if ($item1->id0 == $item->id0){
                    foreach ($a1 as $aa){
                        if ( $item1->id1 ==  $a1[$i1]['id1'] ){
                            $s1=1;

                            $a1[$i1]['totalblu'] = $a1[$i1]['totalblu'] + $item1->saldoawalblu;
                            $a1[$i1]['totalrm'] = $a1[$i1]['totalrm'] + $item1->saldoawalrm;
                        }
                        $i1 = $i1 + 1;
                    }
                    foreach ($data as $item2){
                        $s2=0;
                        $i2=0;
                        if ($item1->id0 == $item2->id0 and $item1->id1 == $item2->id1){
                            foreach ($a2 as $aaa){
                                if ($item2->id2 == $a2[$i2]['id2'] ){
                                    $s2=1;
                                    $a2[$i2]['totalblu'] = $a2[$i2]['totalblu'] + $item2->saldoawalblu;
                                    $a2[$i2]['totalrm'] = $a2[$i2]['totalrm'] + $item2->saldoawalrm;
                                }
                                $i2 = $i2 + 1;
                            }
                            foreach ($data as $item3){
                                $s3=0;
                                $i3=0;
                                if ($item3->id0 == $item2->id0 and $item3->id1 == $item2->id1 and  $item3->id2 == $item2->id2){
                                    foreach ($a3 as $aaaa){
                                        if ($item3->id3 == $a3[$i3]['id3']){//$aaaa['id3']   ){
                                            $s3=1;
                                            $a3[$i3]['totalblu'] = $a3[$i3]['totalblu'] + $item3->saldoawalblu;
                                            $a3[$i3]['totalrm'] = $a3[$i3]['totalrm'] + $item3->saldoawalrm;
                                        }
                                        $i3 = $i3 + 1;
                                    }
                                    foreach ($data as $item4){
                                        $s4=0;
                                        $i4=0;
                                        if ($item4->id3 == $item3->id3 and $item4->id0 == $item3->id0 and $item4->id1 == $item4->id1 and  $item4->id2 == $item3->id2){
                                            foreach ($a4 as $aaaaa){
                                                if ($item4->id4 == $a4[$i4]['id4']){
                                                    $s4=1;
                                                    $a4[$i4]['totalblu'] = $a4[$i4]['totalblu'] + $item4->saldoawalblu;
                                                    $a4[$i4]['totalrm'] = $a4[$i4]['totalrm'] + $item4->saldoawalrm;
                                                }
                                                $i4 = $i4 + 1;
                                            }
                                            foreach ($data as $item5){
                                                if ($item5->id4 == $item4->id4 and $item4->id3 == $item5->id3
                                                    and $item4->id0 == $item5->id0 and $item4->id1 == $item5->id1
                                                    and  $item4->id2 == $item5->id2){
                                                    $a5[]= array(
//                                                            'id' => $item5->norec,
//                                                            'id0' => $item5->id0,
//                                                            'kelompokhead' => $item5->kelompokhead,
//                                                            'id1' => $item5->id1,
//                                                            'childpertama' => $item5->childpertama,
//                                                            'id2' => $item5->id2,
//                                                            'childkedua' => $item5->childkedua,
//                                                            'id3' => $item5->id3,
//                                                            'childketiga' => $item5->childketiga,
//                                                            'id4' => $item5->id4,
                                                        'norec' => $item5->norec,
                                                        'mataanggaran' => $item5->mataanggaran,
                                                        'totalblu' => $item5->saldoawalblu,
                                                        'totalrm' => $item5->saldoawalrm,
                                                    );
                                                }
                                            }
                                            if ($s4 == 0 ){
                                                $a4[]= array(
//                                                    'id' => $item4->norec,
//                                                    'id0' => $item4->id0,
//                                                    'kelompokhead' => $item4->kelompokhead,
//                                                    'id1' => $item4->id1,
//                                                    'childpertama' => $item4->childpertama,
//                                                    'id2' => $item4->id2,
//                                                    'childkedua' => $item4->childkedua,
//                                                    'id3' => $item4->id3,
//                                                    'childketiga' => $item4->childketiga,
                                                    'id4' => $item4->id4,
                                                    'kode' => $item4->kdchildkeempat,
                                                    'mataanggaran' => $item4->childkeempat,
                                                    'totalblu' => $item4->saldoawalblu,
                                                    'totalrm' => $item4->saldoawalrm,
                                                    'child' => $a5,
//                                                    'expanded' =>true,
                                                );
                                            }
                                        }
                                        $a5=[];
                                    }
                                    if ($s3 == 0 ){
                                        $a3[]= array(
//                                            'id' => $item3->norec,
//                                            'id0' => $item3->id0,
//                                            'kelompokhead' => $item3->kelompokhead,
//                                            'id1' => $item3->id1,
//                                            'childpertama' => $item3->childpertama,
//                                            'id2' => $item3->id2,
//                                            'childkedua' => $item3->childkedua,
                                            'id3' => $item3->id3,
//                                            'childketiga' => $item3->childketiga,
                                            'kode' => $item3->kdchildketiga,
                                            'mataanggaran' => $item3->childketiga,
                                            'totalblu' => $item3->saldoawalblu,
                                            'totalrm' => $item3->saldoawalrm,
                                            'child' => $a4,
//                                            'expanded' =>true,
                                        );
                                    }
                                }
                                $a4=[];
                            }
                            if ($s2 == 0 ){
                                $a2[]= array(
//                                    'id' => $item2->norec,
//                                    'id0' => $item2->id0,
//                                    'kelompokhead' => $item2->kelompokhead,
//                                    'id1' => $item2->id1,
//                                    'childpertama' => $item2->childpertama,
                                    'id2' => $item2->id2,
//                                    'childkedua' => $item2->childkedua,
                                    'kode' => $item2->kdchildkedua,
                                    'mataanggaran' => $item2->childkedua,
                                    'totalblu' => $item2->saldoawalblu,
                                    'totalrm' => $item2->saldoawalrm,
                                    'child' => $a3,
//                                    'expanded' =>true,
                                );
                            }
                            $a3=[];
                        }
                    }
                    if ($s1 == 0){
                        $a1[]= array(
//                            'id' => $item1->norec,
//                            'id0' => $item1->id0,
//                            'kelompokhead' => $item1->kelompokhead,
                            'id1' => $item1->id1,
//                            'childpertama' => $item1->childpertama,
                            'kode' => $item1->kdchildpertama,
                            'mataanggaran' =>$item1->childpertama,
                            'totalblu' => $item1->saldoawalblu,
                            'totalrm' => $item1->saldoawalrm,
                            'child' => $a2,
//                            'expanded' =>true,
                        );
                    }
                }
                $a2=[];
            }
            if ($s0 == 0){
                $a0[]= array(
//                    'id' => $item->norec,
                    'id0' => $item->id0,
                    'kode'=> $item->kdkelompokhead,
//                    'kelompokhead' => $item->kelompokhead,
                    'mataanggaran' => $item->kelompokhead,
                    'totalblu' => $item->saldoawalblu,
                    'totalrm' => $item->saldoawalrm,
                    'grandtotalblu' => $saldoBlu,
                    'grandtotalrm' => $saldoRm,
                    'child' => $a1,
//                    'expanded' =>true,
                );
            }
            $a1 =[];
        }


        return $this->respond($a0);
    }

    public function saveChild(Request $request) {
        DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
       try{
            if ($request['norec']==''){
                $MA = new MataAnggaran();
                $MA->norec = $MA->generateNewId();
                $MA->kdprofile = $idProfile;
                $MA->statusenabled = true;
                $MA->objectheadfk =  $request['objectheadfk'];

            }else{
                $MA= MataAnggaran::where('norec',$request['norec'])
                    ->where('kdprofile', $idProfile)
                    ->first();
            }
            $MA->objectchildpertamafk = $request['objectchildpertamafk'];
            $MA->objectchildkeduafk =  $request['objectchildkeduafk'];
            $MA->objectchildketigafk =  $request['objectchildketigafk'];
            $MA->objectchildkeempatfk =  $request['objectchildkeempatfk'];
            $MA->objectchildkelimafk =  $request['objectchildkelimafk'];
            $MA->objectchildkeenamfk =  $request['objectchildkeenamfk'];
            $MA->mataanggaran =  $request['mataanggaran'];
            $MA->revisidivake =  $request['revisidivake'];
            $MA->objectasalprodukfk =  $request['objectasalprodukfk'];
            $MA->saldoawalblu =  $request['saldoawalblu'];
            $MA->saldoawalrm =  $request['saldoawalrm'];
            $MA->tglanggaran = date('Y-m-d H:i:s');
            $MA->tahun =  $request['tahun'];
            $MA->kode =   $request['objectchildpertamafk'].$request['objectchildkeduafk'].$request['objectchildketigafk'].$request['objectchildkeempatfk'].$request['objectchildkelimafk'].$request['objectchildkeenamfk'];
            $MA->objectpengendalifk = $request['pengendali'];

            $MA->save();
           $transStatus = 'true';
       } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Mata Anggaran";
       }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Mata Anggaran Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Mata Anggaran Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond($requestAll);
    }
    public function getDataCombo(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $head = \DB::table('kelompokanggaranhead_m as dp')
            ->select('dp.id','dp.kdkelompokhead','dp.kelompokhead','dp.kodeexternal','dp.namaexternal')
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.kelompokhead')
            ->get();
        $pertama = \DB::table('kelompokanggaranpertama_m as ru')
            ->select('ru.id','ru.kdchildpertama','ru.childpertama','ru.kodeexternal','ru.namaexternal')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.childpertama')
            ->get();
        $kedua = \DB::table('kelompokanggarankedua_m as ru')
            ->select('ru.id','ru.kdchildkedua','ru.childkedua','ru.kodeexternal','ru.namaexternal')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.childkedua')
            ->get();
        $ketiga = \DB::table('kelompokanggaranketiga_m as ru')
            ->select('ru.id','ru.childketiga','ru.kdchildketiga','ru.kodeexternal','ru.namaexternal')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.childketiga')
            ->get();

        $keempat = \DB::table('kelompokanggarankeempat_m as ru')
            ->select('ru.id','ru.kdchildkeempat','ru.childkeempat','ru.kodeexternal','ru.namaexternal')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.childkeempat')
            ->get();
        $kelima = \DB::table('kelompokanggarankelima_m as ru')
            ->select('ru.id','ru.kdchildkelima','ru.childkelima','ru.kodeexternal','ru.namaexternal')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.childkelima')
            ->get();

       $keenam = \DB::table('kelompokanggarankeenam_m as ru')
                ->select('ru.id','ru.kdchildkeenam','ru.childkeenam','ru.kodeexternal','ru.namaexternal')
                ->where('ru.kdprofile', $idProfile)
                ->where('ru.statusenabled', true)
                ->orderBy('ru.childkeenam')
                ->get();

        $Pengendali = \DB::table('pengendali_m as ru')
            ->select('ru.id','ru.pengendali')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.pengendali')
            ->get();


        $result = array(
            'kelompokhead' => $head,
            'kelompokpertama' => $pertama,
            'kelompokkedua' => $kedua,
            'kelompokketiga' => $ketiga,
            'kelompokkeempat' => $keempat,
            'kelompokkelima' => $kelima,
            'kelompokkeenam' => $keenam,
            'datalogin' => $dataLogin,
            'pengendali' => $Pengendali,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function deleteChildAnggaran(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        try{
            if ($request['norec'] != ''){
                $data1 = MataAnggaran::where('norec', $request['norec'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            }
        }
        catch(\Exception $e){
            $transStatus= 'false';
        }
        if ($transStatus=='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }
    public function getKelAnggaranKeempat(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $keempat = \DB::table('kelompokanggarankeempat_m as ru')
            ->select('ru.id','ru.kdchildkeempat','ru.childkeempat')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.childkeempat');

        if(isset($req['childkeempat']) &&
            $req['childkeempat']!="" &&
            $req['childkeempat']!="undefined"){
            $keempat = $keempat->where('ru.childkeempat','ilike','%'. $req['childkeempat'] .'%');
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $keempat = $keempat->where('ru.childkeempat','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        };

        $keempat = $keempat->take(20);
        $keempat = $keempat->get();

        foreach ($keempat as $item){
            $data[]=array(
                'id' => $item->id,
                'kdchildkeempat' => $item->kdchildkeempat,
                'childkeempat' =>$item->kdchildkeempat.' - '.$item->childkeempat,
            );
        }
        return $this->respond($data);
    }
    public function getChildByNorec(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data= \DB::table('mataanggaran_t as rk')
            ->JOIN ('kelompokanggaranhead_m as k0','k0.id','=','rk.objectheadfk')
            ->JOIN ('kelompokanggaranpertama_m as k1','k1.id','=','rk.objectchildpertamafk')
            ->JOIN ('kelompokanggarankedua_m as k2','k2.id','=','rk.objectchildkeduafk')
            ->JOIN ('kelompokanggaranketiga_m as k3','k3.id','=','rk.objectchildketigafk')
            ->JOIN ('kelompokanggarankeempat_m as k4','k4.id','=','rk.objectchildkeempatfk')
            ->select( 'rk.norec','k0.kdkelompokhead','k0.id as id0','k0.kelompokhead','k1.id as id1','k1.childpertama',
                'k2.id as id2','k2.childkedua','k3.id as id3','k3.childketiga',
                'k4.id as id4','k4.childkeempat','rk.mataanggaran','rk.saldoawalblu','rk.saldoawalrm',
                'k1.kdchildpertama','k2.kdchildkedua','k3.kdchildketiga','k4.kdchildkeempat',
                'rk.tahun','rk.tglanggaran','rk.revisidivake')
            ->where('rk.norec',$request['norec'])
            ->where('rk.kdprofile', $idProfile)
            ->get();


        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function getDaftarMataAnggaran(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $Pengendali =  ' ';
        $tahun =  ' ';
        $revisiDiva =  ' ';

        if (isset($request['tahun']) && $request['tahun'] != "" && $request['tahun'] != "undefined") {
            $tahun =  ' where tahun =  ' . $request['tahun'];
        }

        if (isset($request['Pengendali']) && $request['Pengendali'] != "" && $request['Pengendali'] != "undefined") {
            $Pengendali =  ' and objectpengendalifk =  ' . $request['Pengendali'];
        }
      
        if (isset($request['revisike']) && $request['revisike'] != "" && $request['revisike'] != "undefined") {
            $revisiDiva =  ' and revisidivake =  ' . $request['revisike'];
        }

        $data = DB::select(DB::raw("select x.kode, x.kd,x.desk,x.totalblu,x.totalrm,x.tahun,x.revisidivake,x.objectpengendalifk from
                    (
											select left(mat.kode,1) + '00000000000000' as kode, ka.kdkelompokhead as kd, upper(ka.kelompokhead) as desk,
											sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,
											mat.objectpengendalifk
											from mataanggaran_t as mat
											INNER JOIN kelompokanggaranhead_m as ka on ka.id =mat.objectheadfk
											group by left(mat.kode,1),ka.kdkelompokhead,ka.kelompokhead,mat.tahun,mat.revisidivake,
											mat.objectpengendalifk
											--- *** ---
											union all
											--- *** ---
											select left(mat.kode,1)+ '000000000000' as kode,'--' + ka.kdchildpertama as kd,upper(ka.childpertama) as desk,
											sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk
											from mataanggaran_t as mat
											INNER JOIN kelompokanggaranpertama_m ka on ka.id =mat.objectchildpertamafk
											group by left(mat.kode,1),ka.kdchildpertama,ka.childpertama,mat.tahun,mat.revisidivake,mat.objectpengendalifk
										--- *** ---
											union all
											--- *** ---
											select  left(mat.kode,1) + '0000000000'as kode,
											case when ka.kodeexternal is not null and  ka.kdchildkedua is not NULL then '----' + ka.kodeexternal + ' || ' + ka.kdchildkedua 
											when ka.kodeexternal is not null and  ka.kdchildkedua is NULL then '----' + ka.kodeexternal 
											when ka.kodeexternal is  null and  ka.kdchildkedua is not NULL then '----' + ka.kdchildkedua end 
											as kd,

											case when ka.childkedua is not null and  ka.namaexternal is not NULL then  ka.childkedua  + ka.namaexternal 
											when ka.childkedua is not null and  ka.namaexternal is NULL then  ka.childkedua
											when ka.childkedua is  null and  ka.namaexternal is not NULL then  ka.namaexternal end 
											as desk,
									
											sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk
											from mataanggaran_t as mat
											INNER JOIN kelompokanggarankedua_m ka on ka.id =mat.objectchildkeduafk
											group by left(mat.kode,1),ka.kdchildkedua,ka.childkedua,mat.tahun,mat.revisidivake,ka.kodeexternal,mat.objectpengendalifk,ka.namaexternal
											--- *** ---
										 union all
											--- *** ---
											 select  left(mat.kode,1) + '00000000'as kode,
											case when ka.kodeexternal is not null and  ka.kdchildketiga is not NULL then '------' + ka.kodeexternal + ' || ' + ka.kdchildketiga 
											when ka.kodeexternal is not null and  ka.kdchildketiga is NULL then '------' + ka.kodeexternal 
											when ka.kodeexternal is  null and  ka.kdchildketiga is not NULL then '------' + ka.kdchildketiga end 
											as kd,

											case when ka.childketiga is not null and  ka.namaexternal is not NULL then  ka.childketiga  + ka.namaexternal 
											when ka.childketiga is not null and  ka.namaexternal is NULL then  ka.childketiga
											when ka.childketiga is  null and  ka.namaexternal is not NULL then  ka.namaexternal end 
											as desk,
											
											sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk
											from mataanggaran_t as mat
											INNER JOIN kelompokanggaranketiga_m ka on ka.id =mat.objectchildketigafk
											group by left(mat.kode,1),ka.kdchildketiga,ka.childketiga,mat.tahun,mat.revisidivake,ka.kodeexternal,mat.objectpengendalifk,ka.namaexternal

									 union all
					
											select  left(mat.kode,1) + '000000'as kode,
										  case when ka.kodeexternal is not null and  ka.kdchildkeempat is not NULL then '----------' + ka.kodeexternal + ' || ' + ka.kdchildkeempat 
											when ka.kodeexternal is not null and  ka.kdchildkeempat is NULL then '----------' + ka.kodeexternal 
											when ka.kodeexternal is  null and  ka.kdchildkeempat is not NULL then '----------' + ka.kdchildkeempat end 
											as kd,

											case when ka.childkeempat is not null and  ka.namaexternal is not NULL then  ka.childkeempat  + ka.namaexternal 
											when ka.childkeempat is not null and  ka.namaexternal is NULL then  ka.childkeempat
											when ka.childkeempat is  null and  ka.namaexternal is not NULL then  ka.namaexternal end 
											as desk,
				
											sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk
											from mataanggaran_t as mat
											INNER JOIN kelompokanggarankeempat_m ka on ka.id =mat.objectchildkeempatfk
											group by left(mat.kode,1),ka.kdchildkeempat,ka.childkeempat,mat.tahun,mat.revisidivake,ka.kodeexternal,mat.objectpengendalifk,ka.namaexternal
											
										union ALL

											select  left(mat.kode,1) + '0000'as kode,
									   	case when ka.kodeexternal is not null and  ka.kdchildkelima is not NULL then '------------' + ka.kodeexternal + ' || ' + ka.kdchildkelima 
											when ka.kodeexternal is not null and  ka.kdchildkelima is NULL then '------------' + ka.kodeexternal 
											when ka.kodeexternal is  null and  ka.kdchildkelima is not NULL then '------------' + ka.kdchildkelima end 
											as kd,

											case when ka.childkelima is not null and  ka.namaexternal is not NULL then  ka.childkelima  + ka.namaexternal 
											when ka.childkelima is not null and  ka.namaexternal is NULL then  ka.childkelima
											when ka.childkelima is  null and  ka.namaexternal is not NULL then  ka.namaexternal end 
											as desk,
														--'--------' + ka.kodeexternal + ' || ' + ka.kdchildkelima as kd,ka.childkelima + ka.namaexternal as desk,
											sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk
											from mataanggaran_t as mat
											INNER JOIN kelompokanggarankelima_m ka on ka.id =mat.objectchildkelimafk
											group by left(mat.kode,1),ka.kdchildkelima,ka.childkelima,mat.tahun,mat.revisidivake,ka.kodeexternal,mat.objectpengendalifk,ka.namaexternal

										union ALL

											 select  left(mat.kode,1) + '00'as kode,
										case when ka.kodeexternal is not null and  ka.kdchildkeenam is not NULL then '--------------' + ka.kodeexternal + ' || ' + ka.kdchildkeenam 
											when ka.kodeexternal is not null and  ka.kdchildkeenam is NULL then '--------------' + ka.kodeexternal 
											when ka.kodeexternal is  null and  ka.kdchildkeenam is not NULL then '--------------' + ka.kdchildkeenam end 
											as kd,

											case when ka.childkeenam is not null and  ka.namaexternal is not NULL then  ka.childkeenam  + ka.namaexternal 
											when ka.childkeenam is not null and  ka.namaexternal is NULL then  ka.childkeenam
											when ka.childkeenam is  null and  ka.namaexternal is not NULL then  ka.namaexternal end 
											as desk,
									--'----------' + ka.kodeexternal + ' || ' + ka.kdchildkeenam as kd,ka.childkeenam + ka.namaexternal as desk,
											sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk
											from mataanggaran_t as mat
											INNER JOIN kelompokanggarankeenam_m ka on ka.id =mat.objectchildkeenamfk
											group by left(mat.kode,1),ka.kdchildkeenam,ka.childkeenam,mat.tahun,mat.revisidivake,ka.kodeexternal,mat.objectpengendalifk,ka.namaexternal  
								union ALL
										select left(mat.kode,9) + '~' + norec as kode,'---------------'  as kd,mat.mataanggaran as desk,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk
                    from mataanggaran_t as mat
                    group by left(mat.kode,9) + '~' +  norec,mat.kode,mat.mataanggaran,mat.tahun,mat.revisidivake,mat.objectpengendalifk
                  ) as x
                    $tahun
                    $revisiDiva
                    $Pengendali      
                GROUP BY x.kode,x.kd,x.desk,x.totalblu,x.totalrm,x.tahun,x.revisidivake,x.objectpengendalifk
            order by x.kd asc;
            
            "));

        foreach ($data as $key => $row) {
            $count[$key] = $row->kd;
        }

//        if ($count[$key] != ''){
            array_multisort($count, SORT_DESC, $data);
//        }
        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function saveKelHead(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if ($request['id']==''){
                $newId = KelompokAnggaranHead::max('id');
                $newId= $newId+1;
                $MA = new KelompokAnggaranHead();
                $MA->id = $newId;
                $MA->kdprofile = $idProfile;
                $MA->statusenabled = true;
            }else{
                $MA= KelompokAnggaranHead::where('id',$request['id'])
                    ->where('kdprofile', $idProfile)
                    ->first();
            }
            $MA->kdkelompokhead = $request['kdkelompokhead'];
            $MA->kelompokhead =  $request['kelompokhead'];
            $MA->kodeexternal =  $request['kodeexternal'];
            $MA->namaexternal =  $request['namaexternal'];

            $MA->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Mata Anggaran";
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveKelPertama(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if ($request['id']==''){
                $newId = KelompokAnggaranPertama::max('id');
                $newId= $newId+1;
                $MA = new KelompokAnggaranPertama();
                $MA->id = $newId;
                $MA->kdprofile = $idProfile;
                $MA->statusenabled = true;
            }else{
                $MA= KelompokAnggaranPertama::where('id',$request['id'])
                    ->where('kdprofile', $idProfile)
                    ->first();
            }
            $MA->kdchildpertama = $request['kdchildpertama'];
            $MA->childpertama =  $request['childpertama'];
            $MA->kodeexternal =  $request['kodeexternal'];
            $MA->namaexternal =  $request['namaexternal'];

            $MA->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Mata Anggaran";
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveKelKedua(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{

            if ($request['id']==''){
                $newId = KelompokAnggaranKedua::max('id');
                $newId= $newId+1;
                $MA = new KelompokAnggaranKedua();
                $MA->id = $newId;
                $MA->kdprofile = $idProfile;
                $MA->statusenabled = true;
            }else{
                $MA= KelompokAnggaranKedua::where('id',$request['id'])
                    ->where('kdprofile', $idProfile)
                    ->first();
            }
            $MA->kdchildkedua = $request['kdchildkedua'];
            $MA->childkedua =  $request['childkedua'];
            $MA->kodeexternal =  $request['kodeexternal'];
            $MA->namaexternal =  $request['namaexternal'];

            $MA->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Mata Anggaran";
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveKelKetiga(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if ($request['id']==''){
                $newId = KelompokAnggaranKetiga::max('id');
                $newId= $newId+1;
                $MA = new KelompokAnggaranKetiga();
                $MA->id = $newId;
                $MA->kdprofile = 0;
                $MA->statusenabled = true;
            }else{
                $MA= KelompokAnggaranKetiga::where('id',$request['id'])
                    ->where('kdprofile', $idProfile)
                    ->first();
            }
            $MA->kdchildketiga = $request['kdchildketiga'];
            $MA->childketiga =  $request['childketiga'];
            $MA->kodeexternal =  $request['kodeexternal'];
            $MA->namaexternal =  $request['namaexternal'];

            $MA->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Mata Anggaran";
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveKelKeempat(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            foreach ($request['data'] as $item){


            if ($item['id']==''){
                $newId = KelompokAnggaranKeempat::max('id');
                $newId= $newId+1;
                $MA = new KelompokAnggaranKeempat();
                $MA->id = $newId;
                $MA->kdprofile = $idProfile;
                $MA->statusenabled = true;
            }else{
                $MA= KelompokAnggaranKeempat::where('id',$item['id'])
                    ->where('kdprofile', $idProfile)
                    ->first();
            }
            $MA->kdchildkeempat = $item['kdchildkeempat'];
            $MA->childkeempat =  $item['childkeempat'];
            $MA->kodeexternal =  $item['kodeexternal'];
            $MA->namaexternal =  $item['namaexternal'];

            $MA->save();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Mata Anggaran";
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteKelompokHead(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        try{
            if ($request['id'] != ''){
                $data1 = KelompokAnggaranHead::where('id', $request['id'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            }
        }
        catch(\Exception $e){
            $transStatus= 'false';
        }
        if ($transStatus=='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }
    public function deleteKelompokPertama(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        try{
            if ($request['id'] != ''){
                $data1 = KelompokAnggaranPertama::where('id', $request['id'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            }
        }
        catch(\Exception $e){
            $transStatus= 'false';
        }
        if ($transStatus=='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }
    public function deleteKelompokKedua(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        try{
            if ($request['id'] != ''){
                $data1 = KelompokAnggaranKedua::where('id', $request['id'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            }
        }
        catch(\Exception $e){
            $transStatus= 'false';
        }
        if ($transStatus=='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }
    public function deleteKelompokKetiga(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        try{
            if ($request['id'] != ''){
                $data1 = KelompokAnggaranKetiga::where('id', $request['id'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            }
        }
        catch(\Exception $e){
            $transStatus= 'false';
        }
        if ($transStatus=='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }
    public function deleteKelompokKeempat(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        try{
            if ($request['id'] != ''){
                $data1 = KelompokAnggaranKeempat::where('id', $request['id'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            }
        }
        catch(\Exception $e){
            $transStatus= 'false';
        }
        if ($transStatus=='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }

    public function getDaftarMonitoringAnggaran(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("select x.kode,x.kd,x.descc,x.totalblu,x.totalrm,x.tahun,x.revisidivake,x.totalbelanja,((x.totalrm + x.totalblu)- x.totalbelanja) as sisabelanja from
                    (select kode,kd,descc,totalblu,totalrm,tahun,revisidivake,sum(totalbelanja)as totalbelanja from (
                    select left(mat.kode,1)|| '00000000' as kode, ka.kdkelompokhead as kd,upper(ka.kelompokhead) as descc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm,mat.tahun as tahun,mat.revisidivake,
                    (case when sr.totalbelanja <> 0 then sum(sr.totalbelanja)
					 else 0 end)as totalbelanja
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggaranhead_m as ka on cast(ka.id as text)=left(mat.kode,1)
										LEFT JOIN strukrealisasi_t as sr on sr.objectmataanggaranfk = mat.norec
										 where mat.kdprofile = $idProfile
                    group by left(mat.kode,1),ka.kdkelompokhead,ka.kelompokhead,mat.tahun,mat.revisidivake,sr.totalbelanja
                    union all
                    select left(mat.kode,3)|| '000000' as kode,'---' || ka.kdchildpertama as kd,upper(ka.childpertama) as descc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun ,mat.revisidivake,
                    (case when sr.totalbelanja <> 0 then sum(sr.totalbelanja)
					else 0 end)as totalbelanja
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggaranpertama_m ka on cast(ka.id as text)=substring(mat.kode,1,2)
										LEFT JOIN strukrealisasi_t as sr on sr.objectmataanggaranfk = mat.norec
										 where mat.kdprofile = $idProfile
                    group by left(mat.kode,3),ka.kdchildpertama,ka.childpertama,mat.tahun,mat.revisidivake,sr.totalbelanja
                    union all
                    select  left(mat.kode,5) || '0000'as kode,'------' || ka.kdchildkedua as kd,ka.childkedua as descc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,
                    (case when sr.totalbelanja <> 0 then sum(sr.totalbelanja)
					 else 0 end)as totalbelanja
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggarankedua_m ka on cast(ka.id as text)=substring(mat.kode,3,2)
										LEFT JOIN strukrealisasi_t as sr on sr.objectmataanggaranfk = mat.norec
										 where mat.kdprofile = $idProfile
                    group by left(mat.kode,5),ka.kdchildkedua,ka.childkedua,mat.tahun,mat.revisidivake,sr.totalbelanja
                    union all
                    select left(mat.kode,7) || '00'as kode,'---------' || ka.kdchildketiga as kd,ka.childketiga as descc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm,mat.tahun as tahun,mat.revisidivake,
                    (case when sr.totalbelanja <> 0 then sum(sr.totalbelanja)
					 else 0 end)as totalbelanja
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggaranketiga_m as ka on cast(ka.id as text)=substring(mat.kode,5,2)
										LEFT JOIN strukrealisasi_t as sr on sr.objectmataanggaranfk = mat.norec
										 where mat.kdprofile = $idProfile
                    group by left(mat.kode,7),ka.kdchildketiga,ka.childketiga,mat.tahun,mat.revisidivake,sr.totalbelanja
                    union all
                    select  left(mat.kode,9) as kode,'------------' || ka.kdchildkeempat as kd,ka.childkeempat as descc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm,mat.tahun as tahun,mat.revisidivake,
                    (case when sr.totalbelanja <> 0 then sum(sr.totalbelanja)
					 else 0 end)as totalbelanja
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggarankeempat_m as ka on cast(ka.id as text)=substring(mat.kode,7,3)
										LEFT JOIN strukrealisasi_t as sr on sr.objectmataanggaranfk = mat.norec
										 where mat.kdprofile = $idProfile
                    group by left(mat.kode,9),ka.kdchildkeempat,ka.childkeempat,mat.tahun,mat.revisidivake,sr.totalbelanja
                    union all
                    select left(mat.kode,9) || '~' || mat.norec as kode,'---------------'  as kd,mat.mataanggaran as descc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,
                    (case when sr.totalbelanja <> 0 then sum(sr.totalbelanja)
					 else 0 end)as totalbelanja
                    from mataanggaran_t as mat
										LEFT JOIN strukrealisasi_t as sr on sr.objectmataanggaranfk = mat.norec
										 where mat.kdprofile = $idProfile
                    group by left(mat.kode,9)|| '~' || mat.norec,mat.kode,mat.mataanggaran,mat.tahun,mat.revisidivake,sr.totalbelanja
                    ) as x WHERE tahun=:tahun 
                    and revisidivake=:revisidivake
                    group by kode,kd,descc,totalblu,totalrm,tahun,revisidivake
                    order by kode) as x"),
            array(
                'tahun' => $request['tahun'],
                'revisidivake' => $request['revisike'],
            )
        );

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function getDaftarRiwayatRealisasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('strukpelayanan_t as sp')
            ->LEFTJOIN('riwayatrealisasi_t as rr','rr.penerimaanfk','=','sp.norec')
            ->LEFTJOIN('strukrealisasi_t as sr','sr.norec','=','rr.objectstrukrealisasifk')
            ->LEFTJOIN('mataanggaran_t as ma','ma.norec','=','sr.objectmataanggaranfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk', 'sbk.norec', '=', 'sp.nosbklastfk')
            ->LEFTJOIN('rekanan_m as rkn', 'rkn.id', '=', 'sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'sp.objectpegawaipenerimafk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'sp.objectruanganfk')
            ->select('sp.tglstruk', 'sp.nostruk', 'rkn.namarekanan', 'pg.namalengkap',
                'ru.namaruangan','sp.norec','sp.nofaktur','sp.tglfaktur','sp.totalharusdibayar','sbk.nosbk',
                'ma.mataanggaran','sr.totalbelanja','sp.keteranganlainnya'
            )
            ->where('sp.kdprofile', $idProfile);
        if (isset($request['tahun']) && $request['tahun'] != "" && $request['tahun'] != "undefined") {
            $data = $data->where('ma.tahun','=',$request['tahun']);
        }
        $data = $data->where('sp.statusenabled', true);
        $data = $data->whereIn('sp.objectkelompoktransaksifk', [35,92]);
//        $data = $data->whereNotIn('sp.kdprofile',[7]);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();

        foreach ($data as $item) {
            $details = DB::select(DB::raw("select  pr.namaproduk,ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,
                                          spd.hargappn,((spd.hargasatuan-spd.hargadiscount+spd.hargappn)*spd.qtyproduk) as total,spd.tglkadaluarsa,spd.nobatch
                                          from strukpelayanandetail_t as spd 
                                          left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                                          left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                                          where spd.kdprofile = $idProfile and spd.kdprofile = $idProfile and nostrukfk=:norec"),
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
                'keteranganlainnya' => $item->keteranganlainnya,
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

    public function getDetailVerifikasiUsulan (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $noKonfirmasi =  $request['noKonfirmasi'];
        $data = DB::select(DB::raw("select sk.*,pg.namalengkap
                from strukkonfirmasi_t as sk
                INNER JOIN pegawai_m as pg on pg.id = sk.objectpegawaifk
                where sk.kdprofile = $idProfile and sk.nokonfirmasi='$noKonfirmasi';"));
        $result = array(
            'data' =>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarUsulanAnggaran(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('kdprofile', $idProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $data = \DB::table('strukorder_t as sp')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','sp.norec')
            ->JOIN('strukverifikasi_t as sv','sv.norec','=','sp.objectsrukverifikasifk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaiorderfk')
            ->LEFTJOIN('pegawai_m as pg2','pg2.id','=','sp.objectpetugasfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('strukverifikasianggaran_t as sk','sk.norec','=','sp.strukverifikasianggaranfk')
            ->LEFTJOIN('mataanggaran_t as ma','ma.norec','=','sp.objectmataanggaranfk')
            ->LEFTJOIN('asalproduk_m as ap','ap.id','=','op.objectasalprodukfk')
            ->select('sp.norec','sp.tglorder','sp.noorder','pg.namalengkap as penanggungjawab','pg2.namalengkap as mengetahui',
                'sp.tglvalidasi as tglkebutuhan','sp.alamattempattujuan','sp.keteranganlainnya','sp.tglvalidasi','sp.noorderintern',
                'sp.keterangankeperluan','sp.keteranganorder','ru.namaruangan as ruangan','ru.id as ruid',
                'ru2.namaruangan as ruangantujuan','ru2.id as ruidtujuan',
                'sp.totalhargasatuan','sp.status','pg2.nippns','sv.noverifikasi','sv.tglverifikasi','sp.noorderhps','sk.noverifikasi',
                'sk.tglverifikasi','sk.statusterima as statusverifikasi','sk.keteranganlainnya as keteranganlainnya1',
                'ma.norec as mataanggaranfk','ma.mataanggaran','ap.id as apid','ap.asalproduk'
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
            $data = $data->where('sp.noorder','ilike','%'. $request['noorder']);
        }
        if(isset($request['noKontrak']) && $request['noKontrak']!="" && $request['noKontrak']!="undefined"){
            $data = $data->where('sp.nokontrakspk','ilike','%'. $request['noKontrak']);
        }
        if(isset($request['keterangan']) && $request['keterangan']!="" && $request['keterangan']!="undefined"){
            $data = $data->where('sp.keteranganorder','ilike','%'. $request['keterangan']);
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
                'status' => $item->status,
//                'noverifikasi' => $item->noverifikasi,
                'noorderhps' => $item->noorderhps,
                'tglverifikasi' => $item->tglverifikasi,
                'noverifikasianggaran' => $item->noverifikasi,
                'statusverifikasi' => $item->statusverifikasi,
                'keteranganverifikasi' => $item->keteranganlainnya1,
//                'keteranganverifikasi' => $item->keteranganverifikasi,
//                'tglkonfirmasi' => $item->tglkonfirmasi,
                'mataanggaranfk' => $item->mataanggaranfk,
                'mataanggaran' => $item->mataanggaran,
                'apid' => $item->apid,
                'asalproduk' => $item->asalproduk,
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

    public function getDaftarMataAnggaranForUpk(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $Pengendali =  ' ';
        if (isset($request['Pengendali']) && $request['Pengendali'] != "" && $request['Pengendali'] != "undefined") {
            $Pengendali =  ' and objectpengendalifk =  ' . $request['Pengendali'];
        }
        $data = DB::select(DB::raw("select * from (
                    select left(mat.kode,1)|| '00000000' as kode, ka.kdkelompokhead as kd, upper(ka.kelompokhead) as desc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,
                    mat.objectpengendalifk,mat.norec
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggaranhead_m as ka on cast(ka.id as text)=left(mat.kode,1)
                    where mat.kdprofile = $idProfile
                    group by left(mat.kode,1),ka.kdkelompokhead,ka.kelompokhead,mat.tahun,mat.revisidivake,
                    mat.objectpengendalifk,mat.norec
                    union all
                    select left(mat.kode,3)|| '000000' as kode,'---' || ka.kdchildpertama as kd,upper(ka.childpertama) as desc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggaranpertama_m ka on cast(ka.id as text)=substring(mat.kode,1,2)
                    where mat.kdprofile = $idProfile
                    group by left(mat.kode,3),ka.kdchildpertama,ka.childpertama,mat.tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    union all
                    select  left(mat.kode,5) || '0000'as kode,'------' || ka.kdchildkedua as kd,ka.childkedua as desc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggarankedua_m ka on cast(ka.id as text)=substring(mat.kode,3,2)
                    where mat.kdprofile = $idProfile
                    group by left(mat.kode,5),ka.kdchildkedua,ka.childkedua,mat.tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    union all
                    select left(mat.kode,7) || '00'as kode,'---------' || ka.kdchildketiga as kd,ka.childketiga as desc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggaranketiga_m as ka on cast(ka.id as text)=substring(mat.kode,5,2)
                    where mat.kdprofile = $idProfile
                    group by left(mat.kode,7),ka.kdchildketiga,ka.childketiga,mat.tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    union all
                    select  left(mat.kode,9) as kode,'------------' || ka.kdchildkeempat as kd,ka.childkeempat as desc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    from mataanggaran_t as mat
                    INNER JOIN kelompokanggarankeempat_m as ka on cast(ka.id as text)=substring(mat.kode,7,3)
                    where mat.kdprofile = $idProfile
                    group by left(mat.kode,9),ka.kdchildkeempat,ka.childkeempat,mat.tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    union all
                    select left(mat.kode,9) || '~' || norec as kode,'---------------'  as kd,mat.mataanggaran as desc,
                    sum(mat.saldoawalblu) as totalblu,sum(mat.saldoawalrm) as totalrm ,mat.tahun as tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    from mataanggaran_t as mat
                    group by left(mat.kode,9)|| '~' || norec,mat.kode,mat.mataanggaran,mat.tahun,mat.revisidivake,mat.objectpengendalifk,mat.norec
                    where mat.kdprofile = $idProfile
                    ) as x WHERE tahun=:tahun 
                    and revisidivake=:revisidivake
                    $Pengendali 
                    order by kode;"),
            array(
                'tahun' => $request['tahun'],
                'revisidivake' => $request['revisike'],
            )
        );

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function saveVerifikasiAnggaran (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $tglAyeuna = date('Y-m-d H:i:s');
        $noveriF='';
        DB::beginTransaction();
        try{
            if ($request['strukverifikasi']['norec'] != '') {

                //#struk Verifikasi
                $noVerifikasi = $this->generateCode(new StrukVerifikasi(),
                    'noverifikasi', 10, 'CA'.$this->getDateTime()->format('Y'));
                $dataSV = new StrukVerifikasiAnggaran();
                $dataSV->norec = $dataSV->generateNewId();
                $dataSV->noverifikasi = $noVerifikasi;
                $dataSV->kdprofile = $idProfile;
                $dataSV->statusenabled = true;
                $dataSV->objectkelompoktransaksifk = 102;
                $dataSV->keteranganlainnya = $request['strukverifikasi']['keterangan'];
                $dataSV->objectpegawaipjawabfk = $dataPegawai->objectpegawaifk;
                $dataSV->namaverifikasi = 'Confirm Anggaran';
                $dataSV->tglverifikasi = $tglAyeuna;
                $dataSV->tgleksekusi = $tglAyeuna;
                $dataSV->statusterima =  $request['strukverifikasi']['status'];
                $dataSV->totalpengajuan = $request['strukverifikasi']['totalpengajuan'];
                $dataSV->totalppn = $request['strukverifikasi']['totalppn'];
                $dataSV->totaldiskon = $request['strukverifikasi']['totaldiskon'];
                $dataSV->save();
                $dataSV = $dataSV->norec;
                $noveriF = $noVerifikasi;
                $dataSO = StrukOrder::where('norec', $request['strukverifikasi']['norec'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                            'strukverifikasianggaranfk' => $dataSV,
                        ]
                    );
            }
//                return $this->respond($noveriF);
            //***** Struk Realisasi *****
            $datanorecSR = '';
            if ($request['strukverifikasi']['norecrealisasi'] == '') {
                $dataSR= new StrukRealisasi();
                $norealisasi = $this->generateCode(new StrukRealisasi(),'norealisasi',10,'RA-'.$this->getDateTime()->format('ym'));
                $dataSR->norec = $dataSR->generateNewId();
                $dataSR->kdprofile = $idProfile;
                $dataSR->statusenabled = true;
                $dataSR->norealisasi = $norealisasi;
                $dataSR->tglrealisasi = $tglAyeuna;
                $dataSR->objectmataanggaranfk = $request['strukverifikasi']['mataanggaran'];
//                $dataSR->status = 1;
                $dataSR->save();
                if($datanorecSR == null){
                    $datanorecSR = $request['strukverifikasi']['norecrealisasi'];
                }else{
                    $datanorecSR = $dataSR->norec;
                }
            }else {
                $dataSR = StrukRealisasi::where('norec', $request['strukverifikasi']['norecrealisasi'])->where('kdprofile', $idProfile)->first();
                $dataSR->tglrealisasi = $tglAyeuna;
                $dataSR->objectmataanggaranfk = $request['strukverifikasi']['mataanggaran'];
                $dataSR->save();
                if($datanorecSR == null){
                    $datanorecSR = $request['strukverifikasi']['norecrealisasi'];
                }else{
                    $datanorecSR = $dataSR->norec;
                }
            }

            //***** Riwayat Realisasi *****
            if ($request['strukverifikasi']['norecrealisasi'] != null) {
                $dataRR= new RiwayatRealisasi();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = $idProfile;
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 102;
            }else {
                $dataRR = RiwayatRealisasi::where('objectstrukrealisasifk', $request['strukverifikasi']['norecrealisasi'])->where('kdprofile', $idProfile)->first();
            }
            $dataRR->objectstrukrealisasifk = $datanorecSR;
            $dataRR->objectstrukfk = $request['strukverifikasi']['norec'];
            $dataRR->tglrealisasi = $tglAyeuna;
            $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
            $dataRR->noorderintern = $request['strukverifikasi']['nousulan'];
            $dataRR->keteranganlainnya = 'Confirm Anggaran';
            $dataRR->objectverifikasifk = $dataSV;
            $dataRR->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Verifikasi Anggaran";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Verifikasi Anggaran Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nousulan" => $request['strukverifikasi']['nousulan'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Verifikasi Anggaran Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "norec" => $request['strukverifikasi']['norec'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getKelAnggaranKeTiga(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $keempat = \DB::table('kelompokanggaranketiga_m as ru')
            ->select('ru.id','ru.kdchildketiga','ru.childketiga')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.childketiga');

        if(isset($req['childketiga']) &&
            $req['childketiga']!="" &&
            $req['childketiga']!="undefined"){
            $keempat = $keempat->where('ru.childketiga','ilike','%'. $req['childketiga'] .'%');
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $keempat = $keempat->where('ru.childketiga','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        };

        $keempat = $keempat->take(20);
        $keempat = $keempat->get();

        foreach ($keempat as $item){
            $data[]=array(
                'id' => $item->id,
                'kdchildketiga' => $item->kdchildketiga,
                'childketiga' =>$item->kdchildketiga.' - '.$item->childketiga,
            );
        }
        return $this->respond($data);
    }

    public function getKelAnggaranKedua(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $keempat = \DB::table('kelompokanggarankedua_m as ru')
            ->select('ru.id','ru.kodeexternal','ru.kdchildkedua','ru.childkedua','ru.namaexternal')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.childkedua');

        if(isset($req['childkedua']) &&
            $req['childkedua']!="" &&
            $req['childkedua']!="undefined"){
            $keempat = $keempat->where('ru.childkedua','ilike','%'. $req['childkedua'] .'%');
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $keempat = $keempat->where('ru.childkedua','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        };

        $keempat = $keempat->take(20);
        $keempat = $keempat->get();

        foreach ($keempat as $item){
            $data[]=array(
                'id' => $item->id,
                'kdchildkedua' => $item->kdchildkedua,
                'childkedua' =>$item->kodeexternal.' - '.$item->kdchildkedua.' - '.$item->childkedua.' - '.$item->namaexternal,
            );
        }
        return $this->respond($data);
    }
     public function deleteKelompokAnggaran(Request $request) {
         $kdProfile = $this->getDataKdProfile($request);
         $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        try{
            if($request['kelompok'] == 5){
                  if ($request['id'] != ''){
                    $data1 = KelompokAnggaranKelima::where('id', $request['id'])
                        ->where('ru.kdprofile', $idProfile)
                        ->delete();
                    $transStatus = 'true';
                }
            }
            if($request['kelompok'] == 6){
                  if ($request['id'] != ''){
                    $data1 = KelompokAnggaranKeenam::where('id', $request['id'])
                        ->where('ru.kdprofile', $idProfile)
                        ->delete();
                    $transStatus = 'true';
                }
            }
          
        }
        catch(\Exception $e){
            $transStatus= 'false';
        }
        if ($transStatus=='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }
    public function saveKelompokAnggaran(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if($request['kelompok'] == 5){
                if ($request['id']==''){
                    $newId = KelompokAnggaranKelima::max('id');
                    $newId= $newId+1;
                    $MA = new KelompokAnggaranKelima();
                    $MA->id = $newId;
                    $MA->kdprofile = $idProfile;
                    $MA->statusenabled = true;
                }else{
                    $MA= KelompokAnggaranKelima::where('id',$request['id'])
                        ->where('kdprofile', $idProfile)
                        ->first();
                }
                $MA->kdchildkelima = $request['kdkelompok'];
                $MA->childkelima =  $request['namakelompok'];
                $MA->kodeexternal =  $request['kodeexternal'];
                $MA->namaexternal =  $request['namaexternal'];

                $MA->save();
            }
             if($request['kelompok'] == 6){
                if ($request['id']==''){
                    $newId = KelompokAnggaranKeenam::max('id');
                    $newId= $newId+1;
                    $MA = new KelompokAnggaranKeenam();
                    $MA->id = $newId;
                    $MA->kdprofile = $idProfile;
                    $MA->statusenabled = true;
                }else{
                    $MA= KelompokAnggaranKeenam::where('id',$request['id'])
                        ->where('kdprofile', $idProfile)
                        ->first();
                }
                $MA->kdchildkeenam = $request['kdkelompok'];
                $MA->childkeenam =  $request['namakelompok'];
                $MA->kodeexternal =  $request['kodeexternal'];
                $MA->namaexternal =  $request['namaexternal'];

                $MA->save();
            }
           
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Mata Anggaran";
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $MA,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
     public function getKelAnggaranKelima(Request $request) {
         $kdProfile = $this->getDataKdProfile($request);
         $idProfile = (int) $kdProfile;
        $req=$request->all();
        $keempat = \DB::table('kelompokanggarankelima_m as ru')
            ->select('ru.id','ru.kdchildkelima','ru.childkelima')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.childkelima');

        if(isset($req['childkelima']) &&
            $req['childkelima']!="" &&
            $req['childkelima']!="undefined"){
            $keempat = $keempat->where('ru.childkelima','ilike','%'. $req['childkelima'] .'%');
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $keempat = $keempat->where('ru.childkelima','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        };

        $keempat = $keempat->take(20);
        $keempat = $keempat->get();
         $data=[];
        foreach ($keempat as $item){
            $data[]=array(
                'id' => $item->id,
                'kdchildkelima' => $item->kdchildkelima,
                'childkelima' =>$item->kdchildkelima.' - '.$item->childkelima,
            );
        }
        return $this->respond($data);
    }
     public function getKelAnggaranKeenam(Request $request) {
         $kdProfile = $this->getDataKdProfile($request);
         $idProfile = (int) $kdProfile;
        $req=$request->all();
        $keempat = \DB::table('kelompokanggarankeenam_m as ru')
            ->select('ru.id','ru.kdchildkeenam','ru.childkeenam')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.childkeenam');

        if(isset($req['childkeenam']) &&
            $req['childkeenam']!="" &&
            $req['childkeenam']!="undefined"){
            $keempat = $keempat->where('ru.childkeenam','ilike','%'. $req['childkeenam'] .'%');
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $keempat = $keempat->where('ru.childkeenam','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        };

        $keempat = $keempat->take(20);
        $keempat = $keempat->get();
         $data =[];
        foreach ($keempat as $item){
            $data[]=array(
                'id' => $item->id,
                'kdchildkeenam' => $item->kdchildkeenam,
                'childkeenam' =>$item->kdchildkeenam.' - '.$item->childkeenam,
            );
        }
        return $this->respond($data);
    }
}