<?php

namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use DB;
use App\Master\ModulAplikasi;
use App\Master\MapObjekModulToKelompokUser;
use App\Master\MapObjekModulAplikasiToModulAplikasi;
use App\Master\MapLoginUsertoRuangan;
use App\Master\ObjekModulAplikasi;

use App\Master\LoginUser;
use App\Master\KelompokUser;
use App\Traits\Valet;
use Illuminate\Support\Facades\Hash;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;

class HakAksesPegawaiController extends ApiController
{

    use Valet;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getData(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        if ($request['jenis'] == 'objekMenuRecursive') {
            $dataRaw = \DB::table('objekmodulaplikasi_s as oma')
                ->join('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'acdc.objekmodulaplikasiid', '=', 'oma.id')
                ->join('modulaplikasi_s as ma', 'ma.id', '=', 'acdc.modulaplikasiid')
                ->where('oma.statusenabled', true)
                ->where('acdc.modulaplikasiid',$request['id'])
//                ->where('oma.kdprofile', $idProfile)
                ->select('oma.id', 'oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi',
                    'acdc.modulaplikasiid','oma.kodeexternal')
                ->orderBy('oma.id');
            $dataRaw = $dataRaw->get();
            foreach ($dataRaw as $dataRaw2) {
//                if ((integer)$dataRaw2->id < 100) {
                if ($dataRaw2->kodeexternal == 'H') {
                    $dataraw3[] = array(
                        'id' => $dataRaw2->id,
                        'parent_id' => 0,
                        'modulaplikasiid'=>$dataRaw2->modulaplikasiid,
                        'subCategoryName' => $dataRaw2->id . '_' . $dataRaw2->objekmodulaplikasi,
                    );
                } else {
//                    if ((integer)$dataRaw2->id > 100) {
                    if ($dataRaw2->kodeexternal != 'H') {
                        $dataraw3[] = array(
                            'id' => $dataRaw2->id,
                            'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                            'subCategoryName' => $dataRaw2->id . '_' . $dataRaw2->objekmodulaplikasi,
                        );
                    } else {
                        if ($dataRaw2->modulaplikasiid == $request['id']) {
                            $dataraw3[] = array(
                                'id' => $dataRaw2->id,
                                'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                                'subCategoryName' => $dataRaw2->id . '_' . $dataRaw2->objekmodulaplikasi,
                            );
                        }
                    }
                }
            }
            $data = $dataraw3;

            function recursiveElements($data)
            {
                $elements = [];
                $tree = [];
                foreach ($data as &$element) {
//                    $element['subCategories'] = [];
                    $id = $element['id'];
                    $parent_id = $element['parent_id'];

                    $elements[$id] = &$element;
                    if (isset($elements[$parent_id])) {
                        $elements[$parent_id]['subCategories'][] = &$element;
                    } else {
                        if ($parent_id <= 10) {
                            $tree[] = &$element;
                        }
                    }
                    //}
                }
                return $tree;
            }

            $data = recursiveElements($data);
        }
        if ($request['jenis'] == 'subsistemRecursive') {
//            $data = [
//                ['id' => 1, 'parent_id' => 0, 'title' => 'food'],
//                ['id' => 2, 'parent_id' => 1, 'title' => 'drinks'],
//                ['id' => 3, 'parent_id' => 2, 'title' => 'juice'],
//                ['id' => 4, 'parent_id' => 0, 'title' => 'furniture'],
//                ['id' => 5, 'parent_id' => 4, 'title' => 'tables']
//            ];

            $dataRaw = $data = \DB::table('modulaplikasi_s as ma')
                ->where('modulaplikasi', '<>', 'Belum Pilih Modul')
//                ->where('ma.kdprofile', (int)$kdProfile)
                ->orderBy('id')
                ->get();

            foreach ($dataRaw as $dataRaw2) {
//                if (isset($dataRaw2->kdmodulaplikasihead)){
                $dataraw3[] = array(
                    'id' => $dataRaw2->id,
                    'parent_id' => $dataRaw2->kdmodulaplikasihead,
                    'title' => $dataRaw2->modulaplikasi,
                );
//                }else{
//                    $dataraw3[] = array(
//                        'id' => $dataRaw2->id,
//                        'parent_id' => 0,
//                        'title' => $dataRaw2->modulaplikasi,
//                    );
//                }
            }

            $data = $dataraw3;

            function recursiveElements($data)
            {
                $elements = [];
                $tree = [];
                foreach ($data as &$element) {
                    $element['children'] = [];
                    $id = $element['id'];
                    $parent_id = $element['parent_id'];
                    $elements[$id] = &$element;
                    if (isset($elements[$parent_id])) {
                        $elements[$parent_id]['children'][] = &$element;
                    } else {
                        $tree[] = &$element;
                    }
                }
                return $tree;
            }

            $data = recursiveElements($data);
        }
        if ($request['jenis'] == 'subsistem') {
            $data = \DB::table('modulaplikasi_s as ma')
                ->whereNull('ma.kdmodulaplikasihead')
                ->where('reportdisplay', '=', 'Modul')
                ->where('ma.statusenabled', true)
//                ->where('ma.kdprofile', (int)$kdProfile)
                ->orderBy('id','desc');
            if (isset($request['id'])) {
                $data = $data->where('ma.id', $request['id']);
            };
            $data = $data->get();
        }
        if ($request['jenis'] == 'modulaplikasi') {
            $data = \DB::table('modulaplikasi_s as ma')
                ->where('ma.statusenabled', true)
//                ->where('ma.kdprofile', (int)$kdProfile)
                ->orderBy('ma.id')
                ->whereNotNull('ma.kdmodulaplikasihead');
            if (isset($request['id'])) {
                $data = $data->where('ma.kdmodulaplikasihead', $request['id']);
            }
            if (isset($request['idhead'])) {
                $data = $data->where('ma.id', $request['idhead']);
            };
            $data = $data->get();
        }
        if ($request['jenis'] == 'objectmodulaplikasi') {
            $dataHead = \DB::table('objekmodulaplikasi_s as oma')
                ->whereNull('kdobjekmodulaplikasihead')
                ->where('statusenabled', true)
//                ->where('oma.kdprofile', (int)$kdProfile)
                ->orderBy('id')
                ->get();

            $dataAnak = \DB::table('objekmodulaplikasi_s as oma')
                ->join('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'acdc.objekmodulaplikasiid', '=', 'oma.id')
                ->whereNotNull('oma.kdobjekmodulaplikasihead')
                ->where('oma.statusenabled', true)
//                ->where('oma.kdprofile', (int)$kdProfile)
                ->select('oma.*');
            if (isset($request['id'])) {
                $dataAnak = $dataAnak->where('acdc.modulaplikasiid', $request['id']);
            }
            $dataAnak = $dataAnak->get();
            foreach ($dataHead as $hulu) {
                //$result[]='';
                $result = null;
                foreach ($dataAnak as $buntut) {
                    if ($hulu->id == $buntut->kdobjekmodulaplikasihead) {
                        $result[] = array(
                            'id' => $buntut->id,
                            'modul' => $buntut->objekmodulaplikasi,
                        );
                    }
                }
                $resultHead[] = array(
                    'id' => $hulu->id,
                    'modul' => $hulu->objekmodulaplikasi,
                    'anak' => $result,
                );
            }
            $data = $resultHead;
        }
        if ($request['jenis'] == 'objekmodultokelompokuser') {
//            $data5 = \DB::table('mapobjekmodultokelompokuser_s as momku')
//                ->join('objekmodulaplikasi_s as oma','momku.objectobjekmodulaplikasifk','=','oma.id')
//                ->join('kelompokuser_s as ku','momku.objectkelompokuserfk','=','ku.id')
//                ->join('mapobjekmodulaplikasitomodulaplikasi_s as acdc','momku.objectobjekmodulaplikasifk','=','acdc.objekmodulaplikasiid')
//                ->select('oma.id as omaid','oma.objekmodulaplikasi',
//                    'momku.simpan','momku.edit','momku.hapus','momku.cetak',
//                    'ku.id as kuid','ku.kelompokuser','acdc.modulaplikasiid','oma.fungsi','oma.keterangan',
//                    'oma.nourut','oma.alamaturlform','oma.kdobjekmodulaplikasihead','momku.id as momkuid')
//                ->where('oma.statusenabled',true);
            $data5 = \DB::table('mapobjekmodultokelompokuser_s as momku')
                ->leftjoin('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'momku.objectobjekmodulaplikasifk', '=', 'acdc.objekmodulaplikasiid')
                ->join('kelompokuser_s as ku', 'momku.objectkelompokuserfk', '=', 'ku.id')
                ->select('momku.simpan', 'momku.edit', 'momku.hapus', 'momku.cetak', 'ku.id as kuid', 'ku.kelompokuser', 'momku.id as momkuid', 'momku.objectobjekmodulaplikasifk as omaid', 'acdc.modulaplikasiid')
                ->where('acdc.statusenabled',true)
                ->where('momku.kdprofile', (int)$kdProfile);
//                ->where('oma.statusenabled',true);
//            if (isset($request['id'])) {
//                $data5 = $data5->where('acdc.modulaplikasiid', $request['id']);
//            }
            if (isset($request['omaid'])) {
                $data5 = $data5->where('momku.objectobjekmodulaplikasifk', $request['omaid']);
            }
            $data5 = $data5->get();
            $data2 = ObjekModulAplikasi::where('id', $request['omaid'])->first();
            if (isset($data2->kdobjekmodulaplikasihead)) {
                $data3 = ObjekModulAplikasi::where('id', $data2->kdobjekmodulaplikasihead)
//                                            ->where('kdprofile', (int)$kdProfile)
                                            ->first();
                $objekmodulaplikasiSTR = $data3->objekmodulaplikasi;
                $kdobjekmodulaplikasihead = $data2->kdobjekmodulaplikasihead;
            } else {
                $objekmodulaplikasiSTR = '';
                $kdobjekmodulaplikasihead = '';
            }
            $data = array(
                'data1' => $data5,
                'data2' => array(
                    'kdobjekmodulaplikasihead' => $kdobjekmodulaplikasihead,
                    'objekmodulaplikasihead' => $objekmodulaplikasiSTR,
                    'objekmodulaplikasi' => $data2->objekmodulaplikasi,
                    'omaid' => $data2->id,
                    'fungsi' => $data2->fungsi,
                    'keterangan' => $data2->keterangan,
                    'nourut' => $data2->nourut,
                    'alamaturlform' => $data2->alamaturlform,
                )
            );
            ///$data=$data3;
        }
        if ($request['jenis'] == 'kelompokuser') {
            $data = KelompokUser::where('statusenabled', true)
                                ->where('kdprofile', (int)$kdProfile)
                                ->get();
        }
        return $this->respond($data);
    }

    public function modulAplikasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        if ($request['id'] == 0) {
            $modulaplikasi = new ModulAplikasi();
            $newID = ModulAplikasi::max('id');
            $newID = $newID + 1;
            $modulaplikasi->id = $newID;
            $modulaplikasi->kdprofile = $kdProfile; // 0;
            $modulaplikasi->statusenabled = true;
//            $modulaplikasi->kodeexternal = $request['kodeexternal'];
//            $modulaplikasi->namaexternal = $request['namaexternal'];
            $modulaplikasi->norec = substr(Uuid::generate(), 0, 32);
            $modulaplikasi->reportdisplay = $request['reportdisplay'];
            $modulaplikasi->kdmodulaplikasi = $newID;
            $modulaplikasi->modulaplikasi = $request['modulaplikasi'];
            $modulaplikasi->iconimage = $request['iconimage'];
            $modulaplikasi->nourut = intval($request['nourut']);
            $modulaplikasi->kdmodulaplikasihead = intval($request['kdmodulaplikasihead']);
//            $modulaplikasi->moduliconimage = $request['moduliconimage'];
//            $modulaplikasi->modulnourut = intval($request['modulnourut']);
        } else {
            $modulaplikasi = ModulAplikasi::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
            //$modulaplikasi->kdmodulaplikasi = $modulaplikasi->id;
            $modulaplikasi->modulaplikasi = $request['modulaplikasi'];
            $modulaplikasi->iconimage = $request['iconimage'];
            $modulaplikasi->nourut = intval($request['nourut']);
            $modulaplikasi->kdmodulaplikasihead = intval($request['kdmodulaplikasihead']);
        }
        try {
            $modulaplikasi->save();
        } catch (\Exception $e) {
            $this->transStatus = false;
            $this->transMessage = "Simpan Modul Aplikasi Gagal";
        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Modul Aplikasi Berhasil";
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

    public function HapusModulAplikasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $modulaplikasi = ModulAplikasi::where('id', $request['id'])
//                ->where('kdprofile', $kdProfile)
                ->first();
            $modulaplikasi->statusenabled = 'f';
            $modulaplikasi->save();
        } catch (\Exception $e) {
            $this->transStatus = false;
            $this->transMessage = "Delete Modul Aplikasi Gagal";
        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Delete Modul Aplikasi Berhasil";
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

    public function objekModulAplikasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
//        try{
        if ($request['id'] == 0) {

                $objekmodulaplikasi = new ObjekModulAplikasi();
                $newID = ObjekModulAplikasi::max('id');
                $newID = $newID + 1;

//                $newId2=1;
//                $newId = ObjekModulAplikasi::where('id','>',(float)1-1)
//                    ->where('id','<',(float)100)
//                    ->count('id');
//                $newId = (float)$newId2 + (float)$newId;

                $objekmodulaplikasi->id = $newID;
                $objekmodulaplikasi->kdprofile = $kdProfile;//0;
                $objekmodulaplikasi->statusenabled = true;
            if(is_null($request['kdobjekmodulaplikasihead'])) {
                $objekmodulaplikasi->kodeexternal = 'H';
            }else {
                $objekmodulaplikasi->kodeexternal = null;
            }
//            $objekmodulaplikasi->namaexternal = $request['namaexternal'];
                $objekmodulaplikasi->norec = substr(Uuid::generate(), 0, 32);
                //$objekmodulaplikasi->reportdisplay = $request['reportdisplay'];
                $objekmodulaplikasi->fungsi = $request['fungsi'];
                $objekmodulaplikasi->kdobjekmodulaplikasi = $newID; // $request['kdobjekmodulaplikasi'];
                $objekmodulaplikasi->keterangan = $request['keterangan'];
                $objekmodulaplikasi->objekmodulaplikasi = $request['objekmodulaplikasi'];
                $objekmodulaplikasi->nourut = intval($request['nourut']);
                $objekmodulaplikasi->kdobjekmodulaplikasihead = $request['kdobjekmodulaplikasihead'];
                $objekmodulaplikasi->alamaturlform = $request['alamaturlform'];

                $objekmodulaplikasi->save();

                //add Map
                $newData = new MapObjekModulAplikasiToModulAplikasi();
                $newId2 = MapObjekModulAplikasiToModulAplikasi::max('id');
                $newId2 = $newId2 + 1;

                $newData->id = $newId2;
                $newData->kdprofile = $kdProfile;//0;
                $newData->statusenabled = true;
                $newData->norec = substr(Uuid::generate(), 0, 32);
                $newData->modulaplikasiid = intval($request['modulaplikasiid']);
                $newData->objekmodulaplikasiid = $newID;

                $newData->save();


////            else{
////                $newId2=101;
////                $newId = ObjekModulAplikasi::where('id','>',(float)101-1)
////                    ->where('id','<',(float)10000)
////                    ->count('id');
////                $newId = (float)$newId2 + (float)$newId;
////
////                $objekmodulaplikasi = new ObjekModulAplikasi();
////                $newID = ObjekModulAplikasi::max('id');
////                $newID = $newID + 1;
//
//                $objekmodulaplikasi->id = $newId;
//                $objekmodulaplikasi->kdprofile = 0;
//                $objekmodulaplikasi->statusenabled = true;
////            $objekmodulaplikasi->kodeexternal = $request['kodeexternal'];
////            $objekmodulaplikasi->namaexternal = $request['namaexternal'];
//                $objekmodulaplikasi->norec = substr(Uuid::generate(), 0, 32);
//                //$objekmodulaplikasi->reportdisplay = $request['reportdisplay'];
//                $objekmodulaplikasi->fungsi = $request['fungsi'];
//                $objekmodulaplikasi->kdobjekmodulaplikasi = $newId; // $request['kdobjekmodulaplikasi'];
//                $objekmodulaplikasi->keterangan = $request['keterangan'];
//                $objekmodulaplikasi->objekmodulaplikasi = $request['objekmodulaplikasi'];
//                $objekmodulaplikasi->nourut = intval($request['nourut']);
//                $objekmodulaplikasi->kdobjekmodulaplikasihead = $request['kdobjekmodulaplikasihead'];
//                $objekmodulaplikasi->alamaturlform = $request['alamaturlform'];
//
//                $objekmodulaplikasi->save();
//
//                //add Map
//                $newData = new MapObjekModulAplikasiToModulAplikasi();
//                $newId2 = MapObjekModulAplikasiToModulAplikasi::max('id');
//                $newId2 = $newId2 + 1;
//
//                $newData->id = $newId2;
//                $newData->kdprofile = 0;
//                $newData->statusenabled = true;
//                $newData->norec = substr(Uuid::generate(), 0, 32);
//                $newData->modulaplikasiid = intval($request['modulaplikasiid']);
//                $newData->objekmodulaplikasiid = $newId;
//
//                $newData->save();
//
//
//            }
        } else {
            if ($request['addMap'] == 1) {
                //add Map
                $newData = new MapObjekModulAplikasiToModulAplikasi();
                $newId2 = MapObjekModulAplikasiToModulAplikasi::max('id');
                $newId2 = $newId2 + 1;

                $newData->id = $newId2;
                $newData->kdprofile = $kdProfile;//0;
                $newData->statusenabled = true;
                $newData->norec = substr(Uuid::generate(), 0, 32);
                $newData->modulaplikasiid = intval($request['modulaplikasiid']);
                $newData->objekmodulaplikasiid = $request['id'];
                try {
                    $newData->save();
                } catch (\Exception $e) {
                    $this->transStatus = false;
                    $this->transMessage = "Map Objek Modul Aplikasi Gagal";
                }
            } else {
                $objekmodulaplikasi = ObjekModulAplikasi::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
                $objekmodulaplikasi->fungsi = $request['fungsi'];
                //$objekmodulaplikasi->kdobjekmodulaplikasi = $request['kdobjekmodulaplikasi'];
                $objekmodulaplikasi->keterangan = $request['keterangan'];
                $objekmodulaplikasi->objekmodulaplikasi = $request['objekmodulaplikasi'];
                //$objekmodulaplikasi->kdobjekmodulaplikasihead = $request['kdobjekmodulaplikasihead'];
                $objekmodulaplikasi->nourut = $request['nourut'];
                $objekmodulaplikasi->alamaturlform = $request['alamaturlform'];

                try {
                    $objekmodulaplikasi->save();
                } catch (\Exception $e) {
                    $this->transStatus = false;
                    $this->transMessage = "update Objek Modul Aplikasi Gagal";
                }
            }
        }
//        } catch (\Exception $e) {
//            $this->transStatus = false;
//            $this->transMessage = "Simpan Gagal";
//        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Simpan Objek Modul Aplikasi Berhasil";
//            return $this->respond($this->transMessage);
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

    public function HapusObjekModulAplikasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {

            $dataDelete = ObjekModulAplikasi::where('id', $request['id'])->update(
                [ 'statusenabled' => 'f' ]
            );

            $dataDelete = MapObjekModulAplikasiToModulAplikasi::where('objekmodulaplikasiid', $request['id'])->update(
                [ 'statusenabled' => 'f' ]
            );
//            $dataDelete->statusenabled = 'f';
//            $dataDelete->save();

//            $Id2 = MapObjekModulAplikasiToModulAplikasi::where('objekmodulaplikasiid', $request['id'])->where('kdprofile', $kdProfile)->delete();
//            $dataDelete = ObjekModulAplikasi::where('id', $request['id'])->where('kdprofile', $kdProfile)->delete();

        } catch (\Exception $e) {
            $this->transStatus = false;
            $this->transMessage = "Delete Objek Modul Aplikasi Gagal";
        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Delete Objek Modul Aplikasi Berhasil";
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

    public function HapusMAPObjekModulAplikasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $Id2 = MapObjekModulAplikasiToModulAplikasi::where('objekmodulaplikasiid', $request['id'])
//                ->where('kdprofile', $kdProfile)
                ->where('modulaplikasiid', $request['idModul'])
                ->delete();
//            $Id2->statusenabled = 'f';
//            $Id2->save();
        } catch (\Exception $e) {
            $this->transStatus = false;
            $this->transMessage = "Delete Map Objek Modul Aplikasi Gagal";
        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Delete Map Objek Modul Aplikasi Berhasil";
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

    public function mapObjekModulToKelompokUser(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
        if ($request['id'] == 0) {
           foreach ($request['array'] as $item) {
               $mapobjekmodultokelompokuser = new MapObjekModulToKelompokUser();
               $newid = MapObjekModulToKelompokUser::max('id');
               $newid = $newid + 1;

               $mapobjekmodultokelompokuser->id = $newid;
               $mapobjekmodultokelompokuser->kdprofile = $kdProfile;//0;
               $mapobjekmodultokelompokuser->statusenabled = true;
//            $mapobjekmodultokelompokuser->kodeexternal = $request['kodeexternal'];
//            $mapobjekmodultokelompokuser->namaexternal = $request['namaexternal'];
               $mapobjekmodultokelompokuser->norec = substr(Uuid::generate(), 0, 32);
//            $mapobjekmodultokelompokuser->reportdisplay = $request['reportdisplay'];
               $mapobjekmodultokelompokuser->objectkelompokuserfk = intval($request['objectkelompokuserfk']);
               $mapobjekmodultokelompokuser->objectobjekmodulaplikasifk = $item['id'];//$request['objectobjekmodulaplikasifk'];
               $mapobjekmodultokelompokuser->cetak = $request['cetak'];
               $mapobjekmodultokelompokuser->edit = $request['edit'];
               $mapobjekmodultokelompokuser->hapus = $request['hapus'];
               $mapobjekmodultokelompokuser->simpan = $request['simpan'];
               $mapobjekmodultokelompokuser->save();
           }
        } else {
            foreach ($request['array'] as $item) {
                $mapobjekmodultokelompokuser = MapObjekModulToKelompokUser::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
                $mapobjekmodultokelompokuser->objectkelompokuserfk = $request['objectkelompokuserfk'];
//            $mapobjekmodultokelompokuser->objectobjekmodulaplikasifk = $request['objectobjekmodulaplikasifk'];
                $mapobjekmodultokelompokuser->objectobjekmodulaplikasifk = $item['id'];//$request['objectobjekmodulaplikasifk'];
                $mapobjekmodultokelompokuser->cetak = $request['cetak'];

                $mapobjekmodultokelompokuser->edit = $request['edit'];
                $mapobjekmodultokelompokuser->hapus = $request['hapus'];
                $mapobjekmodultokelompokuser->simpan = $request['simpan'];
                $mapobjekmodultokelompokuser->save();
            }
        }

//            $mapobjekmodultokelompokuser->save();
        } catch (\Exception $e) {
            $this->transStatus = false;
            $this->transMessage = "Map Objek Modul To Kelompok User Gagal";
        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Map Objek Modul To Kelompok User Berhasil";
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

    public function HapusmapObjekModulToKelompokUser(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $Id2 = MapObjekModulToKelompokUser::where('id', $request['idMap'])
                ->where('kdprofile', $kdProfile)
                ->delete();
//            $Id2->statusenabled = 'f';
//            $Id2->save();
        } catch (\Exception $e) {
            $this->transStatus = false;
            $this->transMessage = "Delete Map Objek Modul Aplikasi to Kelompok User Gagal";
        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Delete Map Objek Modul Aplikasi to Kelompok User Berhasil";
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

//    public function TambahObjekModulAplikasiToModulAplikasi(Request $request) {
//        DB::beginTransaction();
//        $newData = new MapObjekModulAplikasiToModulAplikasi();
//        $newId = MapObjekModulAplikasiToModulAplikasi::max('id');
//        $newId = $newId+1;
//
//        $newData->id = $newId;
//        $newData->kdprofile = 0;
//        $newData->statusenabled = true;
////        $newData->kodeexternal = $request['kodeexternal'];
////        $newData->namaexternal = $request['namaexternal'];
//        $newData->norec = substr(Uuid::generate(), 0, 32);
//        $newData->reportdisplay = $request['reportdisplay'];
//        $newData->modulaplikasiid = intval($request['objectkelompokuserfk']);
//        $newData->objectmodulaplikasiid = intval($request['objectkelompokuserfk']);
//        try {
//            $newData->save();
//        } catch (\Exception $e) {
//            $this->transStatus = false;
//            $this->transMessage = "Map Objek Modul To Kelompok User Gagal";
//        }
//
//        if ($this->transStatus) {
//            DB::commit();
//            $this->transMessage = "Map Objek Modul To Kelompok User Berhasil";
//            return $this->setStatusCode(201)->respond([], $this->transMessage);
//        } else {
//            DB::rollBack();
//            return $this->setStatusCode(400)->respond([], $this->transMessage);
//        }
//    }

    public function getDataDepartemenRuangan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('departemen_m as d')
            ->join('ruangan_m as r', 'd.id', '=', 'r.objectdepartemenfk')
            ->where('d.statusenabled', true)
            ->where('r.statusenabled', true)
            ->where('d.kdprofile', $kdProfile)
            ->select('d.namadepartemen as departemen', 'r.namaruangan as ruangan')->get();
        return $this->respond($data);
    }

    public function getRuangan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('maploginusertoruangan_s')
            ->where('objectloginuserfk','=', $request['id'])
            ->where('kdprofile',$kdProfile)
            ->select('*')->get();
        return $this->respond($data);
    }

    public function getMapLoginUsertoRuangan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        if ($request['jenis'] == 'departemenruangan') {
//            $dataHeads = \DB::table('pegawai_m as p')
//                ->join('loginuser_s as lu', 'lu.objectpegawaifk', '=', 'p.id')
//                ->join('maploginusertoruangan_s as mlutr', 'mlutr.objectloginuserfk', '=', 'lu.id')
//                ->join('ruangan_m as r', 'mlutr.objectruanganfk', '=', 'r.id')
//                ->join('departemen_m as d', 'r.objectdepartemenfk', '=', 'd.id')
//                ->select('d.id as did', 'd.namaexternal AS departemen', 'r.id as rid', 'r.objectdepartemenfk as objectdepartemenfk', 'r.namaexternal AS ruangan')
////                ->where('p.id', '=', 25)
//                ->orderBy('did')->get();
            $departemens = \DB::table('departemen_m as d')->where('d.kdprofile',$kdProfile)->select('*')->orderBy('id')->get();
            $ruangans = \DB::table('ruangan_m as r')
                                ->where('namaexternal','<>','Belum Pilih Ruangan')
                                ->where('r.statusenabled',true)
                                ->where('r.kdprofile',$kdProfile)
                                ->select('*')->orderBy('id')->get();
//            foreach ($dataHeads as $dataHead){
            $child=[];
            foreach ($departemens as $dptmn) {
                $child=[];
                foreach ($ruangans as $rng){
                    if($dptmn->id == $rng->objectdepartemenfk){
                        $child[] = array(
                            'id' =>$rng->id,
//                                'parent_id' => 'd'.$dptmn->id,
                            'title' => $rng->namaruangan,
                        );
                    }
                }
//                    if ($dataHead->did == $dptmn->id){
                    $result[] = array(
                        'id' => 'd'.$dptmn->id,
//                            'parent_id' => 0,
                        'title' => $dptmn->namadepartemen,
                        'child' => $child
                    );

//                    }
            }
//            }


            $data = $result;

//            function recursiveElements($data) {
//                $elements = [];
//                $tree = [];
//                foreach ($data as &$element) {
//                    $element['children'] = [];
//                    $id = $element['id'];
//                    $parent_id = $element['parent_id'];
//                    $elements[$id] =& $element;
//                    if (isset($elements[$parent_id])) {
//                        $elements[$parent_id]['children'][] =& $element;
//                    }
//                    else { $tree[] =& $element; }
//                }
//                return $tree;
//            }
//
//            $recursiveArray = recursiveElements($data);
        }
        return $this->respond($data);
    }

    public function MapLoginUsertoRuangan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        if ($request['id'] == 0) {
            $maploginusertoruangan = new ModulAplikasi();
            $newID = MapLoginUsertoRuangan::max('id');
            $newID = $newID + 1;
            $maploginusertoruangan->id = $newID;
            $maploginusertoruangan->kdprofile = (int)$kdProfile;//0;
            $maploginusertoruangan->statusenabled = true;
            $maploginusertoruangan->kodeexternal = $request['kodeexternal'];
            $maploginusertoruangan->namaexternal = $request['namaexternal'];
            $maploginusertoruangan->norec = substr(Uuid::generate(), 0, 32);
            $maploginusertoruangan->reportdisplay = $request['reportdisplay'];
            $maploginusertoruangan->objectloginuserfk = intval($request['objectloginuserfk']);
            $maploginusertoruangan->objectruanganfk = intval($request['objectruanganfk']);
        } else {
            $maploginusertoruangan = MapLoginUsertoRuangan::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
            $maploginusertoruangan->kodeexternal = $request['kodeexternal'];
            $maploginusertoruangan->namaexternal = $request['namaexternal'];
            $maploginusertoruangan->reportdisplay = $request['reportdisplay'];
            $maploginusertoruangan->objectloginuserfk = intval($request['objectloginuserfk']);
            $maploginusertoruangan->objectruanganfk = intval($request['objectruanganfk']);
        }
        try {
            $maploginusertoruangan->save();
        } catch (\Exception $e) {
            $this->transStatus = false;
            $this->transMessage = "Simpan Map Login User to Ruangan Gagal";
        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Modul Map Login User to Ruangan Berhasil";
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

    public function HapusMapLoginUsertoRuangan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $Id2 = MapLoginUsertoRuangan::where('id', $request['id'])
//                ->where('kdprofile', $kdProfile)
                ->delete();
        } catch (\Exception $e) {
            $this->transStatus = false;
            $this->transMessage = "Delete Map Objek Modul Aplikasi to Kelompok User Gagal";
        }

        if ($this->transStatus) {
            DB::commit();
            $this->transMessage = "Delete Map Objek Modul Aplikasi to Kelompok User Berhasil";
            return $this->setStatusCode(201)->respond([], $this->transMessage);
        } else {
            DB::rollBack();
            return $this->setStatusCode(400)->respond([], $this->transMessage);
        }
    }

    public function getDataSubsitemModulMenu(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        if ($request['jenis'] == 'subsistem') {
            $dataHeads = \DB::table('modulaplikasi_s as ma')
                ->where('modulaplikasi', '<>', 'Belum Pilih Modul')
//                ->where('ma.kdprofile', $kdProfile)
                ->select('*')
                ->take(10)
                ->orderBy('id')->get();
            $dataAnaks = \DB::table('objekmodulaplikasi_s as oma')
                ->leftjoin('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'acdc.objekmodulaplikasiid', '=', 'oma.id')
                ->leftjoin('modulaplikasi_s as ma', 'ma.id', '=', 'acdc.modulaplikasiid')
                ->where('oma.statusenabled', true)
//                ->where('oma.kdprofile', $kdProfile)
                ->select('oma.id', 'oma.kdobjekmodulaplikasihead', 'acdc.objekmodulaplikasiid', 'acdc.modulaplikasiid', 'ma.modulaplikasi', 'oma.objekmodulaplikasi', 'ma.kdmodulaplikasihead')
                ->take(10)->orderBy('oma.id')->get();
            $dataobjectmoduls = \DB::table('objekmodulaplikasi_s')
                ->where('id', '<', 11)
//                ->where('kdprofile', $kdProfile)
                ->select('*')
                ->take(10)->orderBy('id')->get();

            foreach ($dataHeads as $dataHead) {
                $result[] = array(
                    'id' => 'm'.$dataHead->id,
                    'parent_id' => 'm'.$dataHead->kdmodulaplikasihead,
                    'title' => $dataHead->modulaplikasi,
                );
                if ($dataHead->kdmodulaplikasihead !== null){
//                    foreach ($dataobjectmoduls as $dom) {
//                        $result[] = array(
//                            'id' => $dom->id,
//                            'parent_id' => 'm'.$dataHead->id,
//                            'title' => $dom->id . '_' . $dom->objekmodulaplikasi,
//                        );
                    foreach ($dataAnaks as $dataAnak) {
                        if ((integer)$dataAnak->id < 11) {
                            $result[] = array(
                                'id' => $dataAnak->id,
                                'parent_id' => 'm'.$dataHead->id,
                                'title' => $dataAnak->id . '_' . $dataAnak->objekmodulaplikasi,
                            );
                        } else {
                            if ((integer)$dataAnak->kdobjekmodulaplikasihead > 10) {
                                $result[] = array(
                                    'id' => $dataAnak->id,
                                    'parent_id' => $dataAnak->kdobjekmodulaplikasihead,
                                    'title' => $dataAnak->id . '_' . $dataAnak->objekmodulaplikasi,
                                );
                            } else {
                                if ($dataAnak->modulaplikasiid == $request['id']) {
                                    $result[] = array(
                                        'id' => $dataAnak->id,
                                        'parent_id' => $dataAnak->kdobjekmodulaplikasihead,
                                        'title' => $dataAnak->id . '_' . $dataAnak->objekmodulaplikasi,
                                    );
                                }
                            }
                        }
                    }
//                    }
                }
            }

            $data = $result;

            function recursiveElements($data) {
                $elements = [];
                $tree = [];
                foreach ($data as &$element) {
                    $element['children'] = [];
                    $id = $element['id'];
                    $parent_id = $element['parent_id'];
                    $elements[$id] =& $element;
                    if (isset($elements[$parent_id])) {
                        $elements[$parent_id]['children'][] =& $element;
                    }
                    else { $tree[] =& $element; }
                }
                return $tree;
            }

            $recursiveArray = recursiveElements($data);
        }
        return $this->respond($recursiveArray);
    }

    public function getalldata(Request $request){
        $req = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        if (isset($req['get']) && $req['get'] == "pegawai") {
            $pegawai = DB::table('pegawai_m')
                ->select('id as pegawaiId','namalengkap as Name')
                ->where('statusenabled',true)
                ->where('kdprofile', $kdProfile);
            if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
                $pegawai = $pegawai->where('namalengkap','ilike','%'.$req['nama'].'%');
            }
            $pegawai = $pegawai->orderBy('namalengkap')
                ->take(10)
                ->get();

            $loginuser =[];
//            if(isset($req['id'])) {
                $loginuser = DB::table('loginuser_s as lu')
                    ->join('kelompokuser_s as ku', 'ku.id', '=', 'lu.objectkelompokuserfk')
                    ->join('pegawai_m as pg', 'pg.id', '=', 'lu.objectpegawaifk')
                    ->select('lu.id as luid','lu.namauser', 'ku.kelompokuser', 'ku.id as kuid', 'lu.katasandi',
                        'lu.objectpegawaifk')
                    ->where('lu.kdprofile', $kdProfile);
                    if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
                        $loginuser = $loginuser->where('pg.namalengkap','ilike','%'.$req['nama'].'%');
                    }
//                    ->where('lu.objectpegawaifk', (int)$req['id'])
                $loginuser    =$loginuser->where('lu.statusenabled', true)
                    ->get();
//            }
            $log=[];
            foreach ($pegawai as $item){
                foreach ($loginuser as $item2){
                    if ($item->pegawaiId == $item2->objectpegawaifk){
                        $log[]=array(
                            'luid' => $item2->luid,
                            'namauser' => $item2->namauser,
                            'kelompokuser' => $item2->kelompokuser,
                            'kuid' => $item2->kuid,
                        );
                    }
                }
                $data[]=array(
                    'pegawaiId' => $item->pegawaiId,
                    'Name' => $item->Name,
                    'loginuser' => $log,
                );
            }
            $temp=array(
                'pegawai' => $data,
                //'detail' => $detailPegawai,
                'loginuser' => $loginuser,
            ) ;
        }elseif (isset($req['get']) && $req['get'] == "loginuser") {
            $loginuser = DB::table('loginuser_s as lu')
                ->join('kelompokuser_s as ku', 'ku.id', '=', 'lu.objectkelompokuserfk')
                ->select('lu.id as luid','lu.namauser', 'ku.kelompokuser', 'ku.id as kuid', 'lu.katasandi',
                    'lu.objectpegawaifk')
                ->where('lu.kdprofile', $kdProfile);
                if(isset($req['id'])){
                    $loginuser = $loginuser->where('lu.id',$req['id']);
                };
            $loginuser =$loginuser->where('lu.statusenabled', true)
                ->first();

            $mapLu = DB::table('maploginusertoruangan_s as mlu')
                ->join('ruangan_m as ru', 'ru.id', '=', 'mlu.objectruanganfk')
                ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                ->select('mlu.objectloginuserfk','mlu.objectruanganfk','ru.id as ruid','ru.namaruangan',
                    'dp.id as dpid','dp.namadepartemen')
                ->where('mlu.statusenabled', true)
                ->where('mlu.objectloginuserfk', $loginuser->luid)
                ->where('mlu.kdprofile', $kdProfile)
                ->get();

            $data[]=array(
                'luid' => $loginuser->luid,
                'namauser' => $loginuser->namauser,
                'kelompokuser' => $loginuser->kelompokuser,
                'kuid' => $loginuser->kuid,
                'katasandi' => $loginuser->katasandi,
                'data' => $mapLu,
            );

            $temp=array(
                'loginuser' => $data,
            ) ;
        }elseif(isset($req['get']) && $req['get'] == "modul") {
            $subsistem = DB::table('modulaplikasi_s')
                ->select('id as Id','modulaplikasi as Name')
                ->where('modulaplikasi','ilike','Sub Sistem%')
                ->where('kdprofile', $kdProfile)
                ->orderBy('id')
                ->get();

            $subsistem_ = json_decode(json_encode($subsistem),true);

            for ($i=0; $i < count($subsistem_); $i++) {

                $modul = DB::table('modulaplikasi_s')
                    ->select('kdmodulaplikasi as moduleId','modulaplikasi as Name')
                    ->where('kdmodulaplikasihead','=',(int)$subsistem[$i]->Id)
//                    ->where('kdprofile', $kdProfile)
                    ->orderBy('id')
                    ->get();

                if (empty($modul)) {
                    $subsistem[$i]->HasModule = "false";
                }else{
                    $subsistem[$i]->HasModule = "true";
                }

                $subsistem[$i]->Module = $modul;
            }

            $temp = $subsistem;
        }elseif (isset($req['get']) && $req['get'] == "ruangan") {
            $departemen = DB::table('departemen_m')
                ->select('id as dpid','namadepartemen')
                ->where('statusenabled',true)
                ->where('kdprofile', $kdProfile)
                ->orderBy('id')
                ->get();
            $ruangan = DB::table('ruangan_m')
                ->select('id as ruid','namaruangan','objectdepartemenfk')
                ->where('statusenabled',true)
                ->where('kdprofile', $kdProfile)
                ->orderBy('id')
                ->get();
            foreach ($departemen as $item){
                $check2=false;
                foreach ($ruangan as $item2){
                    if ($item->dpid == $item2->objectdepartemenfk){
                        $check=false;
//                        foreach ($map as $item3){
//                            if ($item3->objectruanganfk == $item2->ruid) {
//                                $check = true;
//                                $check2 = true;
//                            }
//                        }
                        $data[]=array(
                            'ruid' => $item2->ruid,
                            'nama' => $item2->namaruangan,
                            'checked' => $check
                        );
                        break;
                    }
                }
                $dataDep[] =array(
                    'dpid' => $item->dpid,
                    'nama' => $item->namadepartemen,
                    'expanded' => $check2,
                    'ruangs' => $data,
                );
            }

            $temp = $dataDep;
        }elseif (isset($req['get']) && $req['get'] == "kelompokuser") {
            $kelompokuser = DB::table('kelompokuser_s')
                ->select('id','kelompokuser')
//                ->where('kdprofile', $kdProfile)
                ->get();
            $temp = $kelompokuser;
        }elseif (isset($req['get']) && $req['get'] == "profile") {
            $kelompokuser = DB::table('profile_m')
                ->select('id','namalengkap')
                ->where('kdprofile', $kdProfile)
                ->get();
            $temp = $kelompokuser;
        }


        if(!empty($req['callback'])){
            $result = $req['callback']."(".json_encode($temp).")";
            return $result;
        }else{
            return $this->respond($temp);
        }
    }

    public function getRecursiveRuangan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $map = DB::table('maploginusertoruangan_s as mlur')
            ->join('loginuser_s as lu', 'lu.id', '=', 'mlur.objectloginuserfk')
            ->select('mlur.objectruanganfk')
            ->where('lu.objectpegawaifk',$request['idpegawai'])
            ->where('mlur.kdprofile', $kdProfile)
            ->orderBy('mlur.objectruanganfk')
            ->get();

        $departemen = DB::table('departemen_m')
            ->select('id as dpid','namadepartemen')
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->orderBy('id')
            ->get();
        $ruangan = DB::table('ruangan_m')
            ->select('id as ruid','namaruangan','objectdepartemenfk')
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->orderBy('id')
            ->get();
        foreach ($departemen as $item){
            $check2=false;
            foreach ($ruangan as $item2){
                if ($item->dpid == $item2->objectdepartemenfk){
                    $check=false;
                    foreach ($map as $item3){
                        if ($item3->objectruanganfk == $item2->ruid) {
                            $check = true;
                            $check2 = true;
                        }
                    }
                    $data[]=array(
                        'ruid' => $item2->ruid,
                        'nama' => $item2->namaruangan,
                        'checked' => $check
                    );
                    break;
                }
            }
            $dataDep[] =array(
                'dpid' => $item->dpid,
                'nama' => $item->namadepartemen,
                'expanded' => $check2,
                'ruangs' => $data,
            );
        }

        return $this->respond($dataDep);
    }
    public function getRecursiveModul(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $map = DB::table('mappegawaitomodulaplikasi_s')
            ->select('objectmodulaplikasifk')
            ->where('objectpegawaifk',$request['idpegawai'])
//            ->where('kdprofile',$kdProfile)
            ->orderBy('objectmodulaplikasifk')
            ->get();
        $subsistem = DB::table('modulaplikasi_s')
            ->select('id as Id','modulaplikasi as Name')
            ->where('modulaplikasi','ilike','Sub Sistem%')
//            ->where('kdprofile',$kdProfile)
            ->orderBy('id')
            ->get();

        $subsistem_ = json_decode(json_encode($subsistem),true);
        $modul2 =[];
        for ($i=0; $i < count($subsistem_); $i++) {

            $modul = DB::table('modulaplikasi_s')
                ->select('kdmodulaplikasi as moduleId','modulaplikasi as Name')
                ->where('kdmodulaplikasihead','=',(int)$subsistem[$i]->Id)
//                ->where('kdprofile',$kdProfile)
                ->orderBy('id')
                ->get();
            $check = false;
            $check2 = false;
            foreach ($modul as $item){
                $check =false;
                foreach ($map as $item2){
                    if ($item2->objectmodulaplikasifk == $item->moduleId){
                        $check = true;
                        break;
                    }
                }
                if ($check == true){
                    $check2 = true;
                }
                $modul2[]=array(
                    'moduleId' => $item->moduleId,
                    'Name' => $item->Name,
                    'checked' => $check,
                );
            }
            $subsistem[$i]->Module = $modul2;
            $subsistem[$i]->expanded = $check2;
        }

        return $this->respond($subsistem);
    }

    public function getMaxId($table){
        $maxId = DB::table($table)->find(DB::table($table)->max('id'));

        $nextId = $maxId->id+1;

        return $nextId;
    }

    public function addAlldata(Request $request){
        $data = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $this->getMaxId('mappegawaitomodulaplikasi_s');
        $cData = count($data);

        for ($i=0; $i < $cData; $i++) {

            $filterD = DB::table('mappegawaitomodulaplikasi_s')
                ->select('objectpegawaifk as pegawaiId','objectmodulaplikasifk as moduleId')
                ->where('objectpegawaifk','=',(int)$data[$i]['pegawaiId'])
//                ->where('kdprofile',$kdProfile)
                ->get();

            $filter[] = DB::table('mappegawaitomodulaplikasi_s')
                ->select(DB::raw('count(*) as jml, id'))
                ->where([
                    ['objectpegawaifk','=',(int)$data[$i]['pegawaiId']],
                    ['objectmodulaplikasifk','=',(int)$data[$i]['moduleId']],
                    ['kdprofile','=',$kdProfile]
                ])
                ->groupBy('id')
                ->get();

            //remove null array vlaue
            $newfilter = array_filter($filter);

            if(!empty($data) && !empty($filterD)){
                //convert obj to array
                $newfilterD = json_decode(json_encode($filterD), true);

                // Compare all values by a json_encode
                $diff = array_diff(array_map('json_encode', $newfilterD), array_map('json_encode', $data));

                // Json decode the result
                $rowDataforDelete = array_map('json_decode', $diff);

                //reset index of array
                $rowDataforDelete_ = array_values($rowDataforDelete);
            }

            if($filter[$i] == null){ //if not exist on table

                $id = $id+$i;

                $rowDataforInsert[] = array(
                    "id" => (int)$id,
                    "kdprofile" => intval(''),
                    "statusenabled" => (bool)"t",
                    "kodeexternal" => "",
                    "namaexternal" => "tes",
                    "norec" => $id,
                    "reportdisplay" => "tes",
                    "objectmodulaplikasifk" => (int)$data[$i]['moduleId'],
                    "objectpegawaifk" => (int)$data[$i]['pegawaiId'],

                );
            }elseif ($newfilter[$i][0]->jml == 1) { //if exist on table
                $rowDataforUpdate[] = array(
                    "id" => (int)$newfilter[$i][0]->id,
                    "statusenabled" => (bool)"t",
                    "namaexternal" => "tes update",
                    "reportdisplay" => "tes update",
                    "objectmodulaplikasifk" => (int)$data[$i]['moduleId'],
                    "objectpegawaifk" => (int)$data[$i]['pegawaiId'],
                );
            }
        }

        if (!empty($rowDataforDelete_)) {
            for ($i=0; $i < count($rowDataforDelete_); $i++) {

                //rename property name
                $rowDataforDelete_[$i]->objectmodulaplikasifk = $rowDataforDelete_[$i]->moduleId;
                unset($rowDataforDelete_[$i]->moduleId);

                $rowDataforDelete_[$i]->objectpegawaifk = $rowDataforDelete_[$i]->pegawaiId;
                unset($rowDataforDelete_[$i]->pegawaiId);

                //convert obj to array
                $rowDataforDeleted_ = json_encode($rowDataforDelete_);
                $data = json_decode($rowDataforDeleted_,true);


                $is_delete[] = DB::table('mappegawaitomodulaplikasi_s')
                    ->where([
                        ['objectmodulaplikasifk','=',(int)$data[$i]['objectmodulaplikasifk']],
                        ['objectpegawaifk','=',(int)$data[$i]['objectpegawaifk']]
                    ])
                    ->delete();
            }
        }

        if(!empty($rowDataforInsert)){
            //insert all data in array
            $is_saved = DB::table('mappegawaitomodulaplikasi_s')
                ->insert($rowDataforInsert);

        }

        if (!empty($rowDataforUpdate)) {

            for ($i=0; $i < count($rowDataforUpdate); $i++) {
                $is_update = DB::table('mappegawaitomodulaplikasi_s')
                    ->where('id',$rowDataforUpdate[$i]['id']);

                unset($rowDataforUpdate[$i]['id']); //remove id, for not update id

                $is_update  = $is_update->update($rowDataforUpdate[$i]);
            }

        }

        if(isset($is_saved) || isset($is_update) || isset($is_delete)){
            $message = "SUKSES";
        }else{
            $message = "GAGAL";
        }

        $result = array(
            "data" => array(
                "message" => array(
                    "label-success" => $message
                )
            )
        );

        return $this->respond($result);
    }

    public function getdataRuangAll(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req = $request->all();
        if (isset($req['get']) && $req['get'] == "user") {

            $user = DB::table('loginuser_s')
                ->select('id as userId','namauser as userName')
                ->orderBy('namauser')
                ->get();

            $user_ = json_decode(json_encode($user),true);

            for ($i=0; $i < count($user_); $i++) {

                $ruang = DB::table('maploginusertoruangan_s as a')
                    ->select('a.objectruanganfk as ruangId','b.namaruangan as Name')
                    ->leftjoin('ruangan_m as b','b.id','=','a.objectruanganfk')
                    ->where('objectloginuserfk','=',(int)$user[$i]->userId)
                    ->where('a.kdprofile', $kdProfile)
                    ->get();

                if (empty($ruang)) {
                    $user[$i]->HasRuang = "false";
                }else{
                    $user[$i]->HasRuang = "true";
                }

                $user[$i]->Ruang = $ruang;
            }

            $temp = $user;

        }elseif (isset($req['get']) && $req['get'] == "ruang") {
            $ruang = DB::table('ruangan_m')
                ->select('id as ruangId','namaruangan as Name')
                ->where('kdprofile', $kdProfile)
                ->get();

            $temp = $ruang;
        }

        return $this->respond($temp);
    }

    public function addMapUserRuang(Request $request){
        /*$uuid = substr(Uuid::generate(), 0, 32);
        $norec = $uuid;*/
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $this->getMaxId('maploginusertoruangan_s');

        $data = $request->all();

        $cData = count($data);

        for ($i=0; $i < $cData; $i++) {

            $filterD = DB::table('maploginusertoruangan_s')
                ->select('objectloginuserfk as userId','objectruanganfk as ruangId')
                ->where('objectloginuserfk','=',(int)$data[$i]['userId'])
                ->where('kdprofile', $kdProfile)
                ->get();

            $filter[] = DB::table('maploginusertoruangan_s')
                ->select(DB::raw('count(*) as jml, id'))
                ->where([
                    ['objectloginuserfk','=',(int)$data[$i]['userId']],
                    ['objectruanganfk','=',(int)$data[$i]['ruangId']],
                    ['kdprofile','=', $kdProfile]
                ])
                ->groupBy('id')
                ->get();

            //remove null array vlaue
            $newfilter = array_filter($filter);

            if(!empty($data) && !empty($filterD)){
                //convert obj to array
                $newfilterD = json_decode(json_encode($filterD), true);

                // Compare all values by a json_encode
                $diff = array_diff(array_map('json_encode', $newfilterD), array_map('json_encode', $data));

                // Json decode the result
                $rowDataforDelete = array_map('json_decode', $diff);

                //reset index of array
                $rowDataforDelete_ = array_values($rowDataforDelete);
            }

            if($filter[$i] == null){ //if not exist on table

                $id = $id+$i;

                $rowDataforInsert[] = array(
                    "id" => (int)$id,
                    "kdprofile" => intval(''),
                    "statusenabled" => (bool)"t",
                    "kodeexternal" => "",
                    "namaexternal" => "tes",
                    "norec" => substr(Uuid::generate(), 0, 32),
                    "reportdisplay" => "tes",
                    "objectloginuserfk" => (int)$data[$i]['userId'],
                    "objectruanganfk" => (int)$data[$i]['ruangId'],

                );
            }elseif ($newfilter[$i][0]->jml == 1) { //if exist on table
                $rowDataforUpdate[] = array(
                    "id" => (int)$newfilter[$i][0]->id,
                    "statusenabled" => (bool)"t",
                    "namaexternal" => "tes update",
                    "reportdisplay" => "tes update",
                    "objectloginuserfk" => (int)$data[$i]['userId'],
                    "objectruanganfk" => (int)$data[$i]['ruangId'],
                );
            }
        }

        if(!empty($rowDataforDelete_)) {
            for ($i=0; $i < count($rowDataforDelete_); $i++) {

                //rename property name
                $rowDataforDelete_[$i]->objectruanganfk = $rowDataforDelete_[$i]->ruangId;
                unset($rowDataforDelete_[$i]->ruangId);

                $rowDataforDelete_[$i]->objectloginuserfk = $rowDataforDelete_[$i]->userId;
                unset($rowDataforDelete_[$i]->userId);

                //convert obj to array
                $rowDataforDeleted_ = json_encode($rowDataforDelete_);
                $data = json_decode($rowDataforDeleted_,true);


                $is_delete[] = DB::table('maploginusertoruangan_s')
                    ->where([
                        ['objectloginuserfk','=',(int)$data[$i]['objectloginuserfk']],
                        ['objectruanganfk','=',(int)$data[$i]['objectruanganfk']]
                    ])
                    ->delete();
            }
        }

        if(!empty($rowDataforInsert)){
            //insert all data in array
            $is_saved = DB::table('maploginusertoruangan_s')
                ->insert($rowDataforInsert);

        }

        if (!empty($rowDataforUpdate)) {

            for ($i=0; $i < count($rowDataforUpdate); $i++) {
                $is_update = DB::table('maploginusertoruangan_s')
                    ->where('id',$rowDataforUpdate[$i]['id']);

                unset($rowDataforUpdate[$i]['id']); //remove id, for not update id

                $is_update  = $is_update->update($rowDataforUpdate[$i]);
            }

        }

        if(isset($is_saved) || isset($is_update) || isset($is_delete)){
            $message = "SUKSES";
        }else{
            $message = "GAGAL";
        }

        $result = array(
            "data" => array(
                "message" => array(
                    "label-success" => $message
                )
            )
        );


        return $this->respond($result);
    }

    public function getallPegawai(Request $request){
        $pegawai = DB::table('pegawai_m')
            ->select('id as pegawaiId','namalengkap as Name')
            ->get();

        return $this->respond($pegawai);
    }
    public function saveMapLoginUser(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $newID = MapLoginUsertoRuangan::max('id');
            $newID = $newID + 1;

            $newKS = new MapLoginUsertoRuangan();
            $norecKS = $newKS->generateNewId();
            $newKS->id = $newID;
            $newKS->norec = $norecKS;
            $newKS->kdprofile =$kdProfile;//0;
            $newKS->statusenabled = true;
            $newKS->objectloginuserfk = $request['loginuserfk'];
            $newKS->objectruanganfk = $request['ruanganfk'];
            $newKS->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "MLU";
        }

        if ($transStatus == 'true') {
            $transMessage = "MLU Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $newKS,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "MLU Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $newKS,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveHapusMapLoginUser(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
//            $newKS =  MapLoginUsertoRuangan::where('id',$request['id'])->delete();
            $delSPD = MapLoginUsertoRuangan::where('objectloginuserfk',$request['loginuserfk'])
                ->where('kdprofile', $kdProfile)
                ->where('objectruanganfk',$request['ruanganfk'])
                ->delete();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "MLU";
        }

        if ($transStatus == 'true') {
            $transMessage = "MLU Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $delSPD,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "MLU Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $delSPD,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getAllObjekModul(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataRaw = \DB::table('objekmodulaplikasi_s as oma')
            ->leftjoin('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'acdc.objekmodulaplikasiid', '=', 'oma.id')
            ->leftjoin('modulaplikasi_s as ma', 'ma.id', '=', 'acdc.modulaplikasiid')
            ->join('mapobjekmodultokelompokuser_s as momku', 'momku.objectobjekmodulaplikasifk', '=',
                'acdc.objekmodulaplikasiid')
            ->leftjoin('kelompokuser_s as ku', 'momku.objectkelompokuserfk', '=', 'ku.id')
            ->where('oma.statusenabled', true)
            ->where('acdc.statusenabled', true)
//            ->where('oma.kdprofile', $kdProfile)
//            ->whereNotNull('oma.kdobjekmodulaplikasihead')
//            ->where('momku.objectkelompokuserfk', $request['idKelompokUser'])
            ->select('oma.id', 'oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi', 'acdc.modulaplikasiid',
                'momku.objectkelompokuserfk','ku.kelompokuser',
                'oma.fungsi', 'oma.kdobjekmodulaplikasi','oma.kdprofile','oma.keterangan','oma.norec','oma.nourut','oma.statusenabled',
                'oma.alamaturlform','oma.kodeexternal')
            ->orderBy('oma.id')
            ->groupBy('oma.id', 'oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi', 'acdc.modulaplikasiid',
                'momku.objectkelompokuserfk','ku.kelompokuser',
                'oma.fungsi', 'oma.kdobjekmodulaplikasi','oma.kdprofile','oma.keterangan','oma.norec','oma.nourut','oma.statusenabled',
                'oma.alamaturlform','oma.kodeexternal');

            $dataRaw = $dataRaw->get();
            //$data =$dataRaw;
            $dataraw3 = [];
            foreach ($dataRaw as $dataRaw2) {
//                if ((integer)$dataRaw2->id < 11) {
//                    $dataraw3[] = array(
//                        'id' => $dataRaw2->id,
//                        'parent_id' => 0,
//                        'objekModulAplikasi' =>  $dataRaw2->objekmodulaplikasi,
//                        'fungsi' =>  $dataRaw2->fungsi,
//                        'kdObjekModulAplikasi' =>  $dataRaw2->kdobjekmodulaplikasi,
//                        'kdObjekModulAplikasiHead' =>  $dataRaw2->kdobjekmodulaplikasihead,
//                        'kdProfile' =>  $dataRaw2->kdprofile,
//                        'keterangan' =>  $dataRaw2->keterangan,
//                        'noRec' =>  $dataRaw2->norec,
//                        'noUrut' =>  $dataRaw2->nourut,
//                        'statusEnabled' =>  $dataRaw2->statusenabled,
//
//
//
//                    );
//                } else {
//                    if ((integer)$dataRaw2->id > 100) {

                if ($dataRaw2->kodeexternal != 'H') {
                        $dataraw3[] = array(
                            'id' => $dataRaw2->id,
                            'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                            'objekModulAplikasi' =>  $dataRaw2->objekmodulaplikasi,
//                            'objectKelompokUserfk' =>  $dataRaw2->objectkelompokuserfk,
//                            'kelompokUser' =>  $dataRaw2->kelompokuser,
                            'fungsi' =>  $dataRaw2->fungsi,
                            'kdObjekModulAplikasi' =>  $dataRaw2->kdobjekmodulaplikasi,
                            'kdObjekModulAplikasiHead' =>  $dataRaw2->kdobjekmodulaplikasihead,
                            'kdProfile' =>  $dataRaw2->kdprofile,
                            'keterangan' =>  $dataRaw2->keterangan,
                            'alamatUrlForm' =>$dataRaw2->alamaturlform,
                            'noRec' =>  $dataRaw2->norec,
                            'noUrut' =>  $dataRaw2->nourut,
                            'statusEnabled' =>  $dataRaw2->statusenabled,
                        );

                    } else {
                        if ($dataRaw2->modulaplikasiid == $request['idModulAplikasi']
                                && $dataRaw2->objectkelompokuserfk  == $request['idKelompokUser']) {
                            $dataraw3[] = array(
                                'id' => $dataRaw2->id,
                                'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                                'objekModulAplikasi' =>  $dataRaw2->objekmodulaplikasi,
                                'objectKelompokUserfk' =>  $dataRaw2->objectkelompokuserfk,
                                'kelompokUser' =>  $dataRaw2->kelompokuser,
                                'fungsi' =>  $dataRaw2->fungsi,
                                'kdObjekModulAplikasi' =>  $dataRaw2->kdobjekmodulaplikasi,
                                'kdObjekModulAplikasiHead' =>  $dataRaw2->kdobjekmodulaplikasihead,
                                'kdProfile' =>  $dataRaw2->kdprofile,
                                'keterangan' =>  $dataRaw2->keterangan,
                                'alamatUrlForm' =>$dataRaw2->alamaturlform,
                                'noRec' =>  $dataRaw2->norec,
                                'noUrut' =>  $dataRaw2->nourut,
                                'statusEnabled' =>  $dataRaw2->statusenabled,
                            );
                        }
//                    }
                }
            }
            $data = $dataraw3;
            function recursiveElements($data)
            {
                $elements = [];
                $tree = [];
                foreach ($data as &$element) {
                    $element['children'] = [];
                    $id = $element['id'];
                    $parent_id = $element['parent_id'];

                    $elements[$id] = &$element;
                    if (isset($elements[$parent_id])) {
                        $elements[$parent_id]['children'][] = &$element;
                    } else {
                        if ($parent_id <= 10) {
                            $tree[] = &$element;
                        }
                    }
                    //}
                }
                return $tree;
            }

            $data = recursiveElements($data);
//        }

        return $this->respond($data);
    }
    public function hasPassword(Request $request){

        $hashed = Hash::make($request['password']);
        return $this->respond($hashed);
    }
    public function getChildIdHead(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $dataRaw = \DB::table('objekmodulaplikasi_s as oma')
            ->where('oma.statusenabled', true)
            ->where('oma.id', $request['idOma'])
            ->orwhere('oma.kdobjekmodulaplikasihead', $request['idOma'])
//            ->where('oma.kdprofile',(int)$kdProfile)
            ->select('oma.id', 'oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi',
                'oma.fungsi', 'oma.kdobjekmodulaplikasi', 'oma.kdprofile', 'oma.keterangan', 'oma.norec', 'oma.nourut', 'oma.statusenabled',
                'oma.alamaturlform')
            ->orderBy('oma.id');

        $dataRaw = $dataRaw->get();
//        foreach ($dataRaw as $item){
//            $dataz=DB::raw(DB::select('select * from objekmodulaplikasi_s where id=:idoma'),
//            array(
//                'idoma' =>$item->id,
//            )
//            );
//
//        }
        $result = array(
            "data" => $dataRaw,
            "as" => 'as@rmdn',
        );
        return $this->respond($result);

    }
    public function getKelompokUser(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $detailLogin = $request->all();
        $data = \DB::table('kelompokuser_s as ku')
            ->join('loginuser_s as lu','lu.objectkelompokuserfk','=','ku.id')
            ->select('ku.id','ku.kelompokuser','lu.namauser')
            ->where('lu.id',$request['luId'])
//            ->where('ku.kdprofile',(int)$kdProfile)
            ->first();
        $result = array(
            "data" => $data,
            "as" => 'as@rmdn',
        );
        return $this->respond($result);

    }


}


