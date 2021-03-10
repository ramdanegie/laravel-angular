<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 7/31/2019
 * Time: 4:42 PM
 */

namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\ApiController;
use App\Master\MapLoginUserToModulAplikasi;
use App\Master\MapObjekModulAplikasiToModulAplikasi;
use App\Master\WaktuLogin;
use App\Master\ModulAplikasi;
use App\Master\ObjekModulAplikasi;
use App\Web\LoginUser;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;

class ModulAplikasiController extends ApiController
{

    use Valet;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getMenuDinamis(Request $request){
        $get = $request->all();
        $kdProfile = (int) $request['Profile'];
        $dataraw3 = [];
        $dataRaw = \DB::table('objekmodulaplikasi_s as oma')
            ->join('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'acdc.objekmodulaplikasiid', '=', 'oma.id')
            ->join('maploginusertomodulaplikasi_s as maps',
                function ($join){
                    $join->on('maps.objectmodulaplikasifk', '=', 'acdc.modulaplikasiid');
//					$join->on('maps.objectmodulaplikasifk', '=', 'acdc.modulaplikasiid');
                })
//				'maps.objectmodulaplikasifk', '=', 'acdc.modulaplikasiid')
            ->join('modulaplikasi_s as ma', 'ma.id', '=', 'acdc.modulaplikasiid')
//            ->where('oma.kdprofile', $kdProfile)
            ->where('oma.statusenabled', true)
            ->where('ma.reportdisplay', 'Menu')
            ->where('maps.objectloginuserfk',$request['idUser'] )

            ->select('oma.id', 'oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi','oma.alamaturlform','ma.modulaplikasi',
                'acdc.modulaplikasiid','oma.kodeexternal')
            ->groupBy('oma.id','oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi','oma.alamaturlform','ma.modulaplikasi',
                'acdc.modulaplikasiid','oma.kodeexternal','oma.nourut')
            ->orderBy('oma.nourut');
        $dataRaw = $dataRaw->get();
        foreach ($dataRaw as $dataRaw2) {
//                if ((integer)$dataRaw2->id < 100) {
            if ($dataRaw2->kdobjekmodulaplikasihead == null) {
                if($dataRaw2->alamaturlform != null || $dataRaw2->alamaturlform !=''){
                    $dataraw3[] = array(
                        'id' => $dataRaw2->id,
                        'parent_id' => 0,
//					'modulaplikasiid'=>$dataRaw2->modulaplikasiid,
                        'name' => $dataRaw2->objekmodulaplikasi,
                        'link' =>$dataRaw2->alamaturlform //!= null? $dataRaw2->alamaturlform : '#/',
                    );
                }else{
                    $dataraw3[] = array(
                        'id' => $dataRaw2->id,
                        'parent_id' => 0,
                        'name' => $dataRaw2->objekmodulaplikasi,
                       // 'link' =>$dataRaw2->alamaturlform //!= null? $dataRaw2->alamaturlform : '#/',
                    );
                }

            } else {
                if ($dataRaw2->kdobjekmodulaplikasihead != null ) {
                    if($dataRaw2->alamaturlform != null || $dataRaw2->alamaturlform !='') {
                        $dataraw3[] = array(
                            'id' => $dataRaw2->id,
                            'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                            'name' => $dataRaw2->objekmodulaplikasi,
                            'link' => $dataRaw2->alamaturlform// != null ? $dataRaw2->alamaturlform : '#/',
                        );
                    }else{
                        $dataraw3[] = array(
                            'id' => $dataRaw2->id,
                            'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                            'name' => $dataRaw2->objekmodulaplikasi,
                           // 'link' => $dataRaw2->alamaturlform// != null ? $dataRaw2->alamaturlform : '#/',
                        );
                    }
                } else {
                    if ($dataRaw2->modulaplikasiid == $request['id']) {
                        if ($dataRaw2->alamaturlform != null || $dataRaw2->alamaturlform != '') {
                            $dataraw3[] = array(
                                'id' => $dataRaw2->id,
                                'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                                'name' => $dataRaw2->objekmodulaplikasi,
                                'link' => $dataRaw2->alamaturlform //? $dataRaw2->alamaturlform : '#/',
                            );
                        }else{
                            $dataraw3[] = array(
                                'id' => $dataRaw2->id,
                                'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                                'name' => $dataRaw2->objekmodulaplikasi,
                               // 'link' => $dataRaw2->alamaturlform //? $dataRaw2->alamaturlform : '#/',
                            );
                        }
                    }
                }
            }
        }
        $data = $dataraw3;
//        return $this->respond($data);
        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
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
            }
            return $tree;
        }

        $data = recursiveElements($data);


        return $this->respond($data);
    }

    public function getObjekModulAplikasiStandar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
//		$data = DB::table('modulaplikasi_s')
//			->select('*')
//			->where('statusenabled',true)
//			->orderBy('nourut')
//			->get();
//		$result = array(
//			'data' => $data,
//			'as'=> 'inhuman'
//		);
//		return $this->respond($result);

        $dataraw3 = [];
        $dataRaw = \DB::table('objekmodulaplikasi_s')
//            ->where('kdprofile', $kdProfile)
            ->where('statusenabled', true)
            ->select('id', 'kdobjekmodulaplikasihead', 'objekmodulaplikasi')
//			->whereBetween('id',[1450,1790])
            ->orderBy('id');
        $dataRaw = $dataRaw->get();
        foreach ($dataRaw as $dataRaw2) {
            if ($dataRaw2->kdobjekmodulaplikasihead == null) {
                $dataraw3[] = array(
                    'id' => $dataRaw2->id,
                    'parent_id' => 0,
                    'name' => $dataRaw2->objekmodulaplikasi,
                );
            } else if ($dataRaw2->kdobjekmodulaplikasihead != null ) {
                $dataraw3[] = array(
                    'id' => $dataRaw2->id,
                    'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                    'name' => $dataRaw2->objekmodulaplikasi,
                );
            }

        }
        $data = $dataraw3;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
                $id = $element['id'];
                $parent_id = $element['parent_id'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                } else {
//					if ($parent_id <= 10) {
                    $tree[] = &$element;
//					}
                }
            }
            return $tree;
        }

        $data = recursiveElements($data);

        $modulAplikasi = ModulAplikasi::where('statusenabled',true)
//            ->where('kdprofile', $kdProfile)
            ->where('reportdisplay','=','Menu')->get();
        $result = array(
            'objekmodulaplikasi' => $data,
            'modulaplikasi' => $modulAplikasi
        );
        return $this->respond($result);
    }
    public function getMapModulToObjekModul(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataraw3 = [];
        $dataraw4 = [];
        $dataRaw = \DB::table('objekmodulaplikasi_s as oma')
            ->join('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'acdc.objekmodulaplikasiid', '=', 'oma.id')
            ->join('modulaplikasi_s as ma', 'ma.id', '=', 'acdc.modulaplikasiid')
//            ->where('oma.statusenabled', true)
            ->where('ma.id',$request['idModulAplikasi'] )
            ->select('oma.id', 'oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi','oma.alamaturlform','ma.modulaplikasi',
                'acdc.modulaplikasiid','oma.kodeexternal')
            ->where('oma.kdprofile',$kdProfile)
            ->orderBy('oma.nourut', 'asc');
        $dataRaw = $dataRaw->get();
        foreach ($dataRaw as $dataRaw2) {
            if ($dataRaw2->kdobjekmodulaplikasihead == null) {
                $dataraw3[] = array(
                    'id' => $dataRaw2->id,
                    'parent_id' => 0,
//					'modulaplikasiid'=>$dataRaw2->modulaplikasiid,
                    'name' => $dataRaw2->objekmodulaplikasi,
                    'isChecked' => true
                );
            } else if ($dataRaw2->kdobjekmodulaplikasihead != null ) {
                $dataraw3[] = array(
                    'id' => $dataRaw2->id,
                    'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                    'name' => $dataRaw2->objekmodulaplikasi,
                    'isChecked' => true
                );

            }
            $dataraw4 [] = array(
                'id' => $dataRaw2->id,
                'isChecked' => true
            );
        }
        $data = $dataraw3;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
                $id = $element['id'];
                $parent_id = $element['parent_id'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                } else {
//					if ($parent_id <= 10) {
                    $tree[] = &$element;
//					}
                }
            }
            return $tree;
        }

        $data = recursiveElements($data);
        $result = array(
            'recursive' =>$data,
            'data' => $dataraw4
        );

        return $this->respond($result);
    }
    function buildTree($elements, $parentId = "0") {
        $branch = array();
        foreach ($elements as $element) {
            if ($element->parent_id === $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->items = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
    public function all(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $get_data_modul = DB::table('ModulAplikasi_S as modul')
            ->leftJoin('MapLoginUserToProfile_S as map', 'map.KdModulAplikasi', '=', 'modul.KdModulAplikasi')
            ->where('modul.StatusEnabled','=',1)
            ->where('modul.KdModulAplikasi','=',$request->input('KdModuleAplikasi'))
            ->where('map.KdProfile','=',$request->input('KdProfile'))
            ->where('map.KdRuangan','=',$request->input('KdRuangan'))
            ->where('map.KdUser','=',$request->input('KdUser'))
//            ->where('modul.kdprofile', $kdProfile)
            ->select('modul.KdModulAplikasi', 'modul.KdModulAplikasiHead', 'modul.ReportDisplay')
            ->orderBy('modul.KdModulAplikasiHead','ASC')->get();

        $get_data_menu = DB::table('MapObjekModulToModulAplikasi_S as main')
            ->join('ObjekModulAplikasi_S as objek', function($join) {
                $join->on('main.KdObjekModulAplikasi', '=', 'objek.KdObjekModulAplikasi')
                    ->where('objek.StatusEnabled', '=', 1);
            })
            // ->join('MapObjekModulToModulAplikasi_S as objek', 'main.KdObjekModulAplikasi', '=', 'objek.KdObjekModulAplikasi')
            ->join('MapObjekModulToKelompokUser_S as group',function($kelompokUser){
                $kelompokUser->on('main.KdObjekModulAplikasi', '=', 'group.KdObjekModulAplikasi');
                $kelompokUser->on('main.KdProfile', '=', 'group.KdProfile');
            })
//            ->where('main.kdprofile', $kdProfile)
            ->where('main.StatusEnabled','=',1)
            ->where('main.KdModulAplikasi','=',$request->input('KdModuleAplikasi'))
            ->where('group.KdKelompokUser','=', $request->input('KdKelompokUser'))
            ->where('main.KdProfile', '=', $request->input('KdProfile'))
            ->select('objek.KdObjekModulAplikasi','objek.KdObjekModulAplikasiHead', 'main.KdModulAplikasi', 'objek.ReportDisplay', 'objek.AlamatURLFormObjek',
                'group.Simpan', 'group.Hapus', 'group.Ubah', 'group.Cetak', 'group.Tampil'

            )
            ->orderBy('main.NoUrutObjek','ASC')
            ->orderBy('objek.ReportDisplay','ASC')->get();
        //var_dump($get_data_modul);
        //var_dump($get_data_menu);
        $data_menu = array();
        $data_module = array();
        foreach ($get_data_modul as $key => $value){
            $obj = new \stdClass();
            $obj->id = $value->KdModulAplikasi;
            $obj->parent_id = ($value->KdModulAplikasiHead === NULL)?"0":$value->KdModulAplikasiHead;
            $obj->label = $value->ReportDisplay;
            $obj->icon = 'fa fa-fw fa-sitemap';
            // $obj->routerLink = [''];
            $obj->badge = '';
            $obj->badgeStyleClass = 'orange-badge';
            array_push($data_module,$obj );
        }

        foreach ($get_data_menu as $key => $value){
            $obj = new \stdClass();
            $obj->id = $value->KdObjekModulAplikasi.'_child';
            $obj->parent_id = ($value->KdObjekModulAplikasiHead < 1)? $value->KdModulAplikasi : $value->KdObjekModulAplikasiHead.'_child' ;
            $obj->label = $value->ReportDisplay;
            $obj->icon = 'fa fa-fw fa-sitemap';
            $obj->routerLink = ($value->AlamatURLFormObjek)?['./'.$value->AlamatURLFormObjek]:['./'];
            $obj->badge = '';
            $obj->badgeStyleClass = 'orange-badge';
            array_push($data_menu,$obj );
        }
        $data = array_merge($data_module,$data_menu);
        $results = $this->buildTree($data);

        return response()->json($results);
    }
    public function saveModulAplikasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $newId = ModulAplikasi::max('id') +1 ;
            if ($request['id'] == ''){
                $TP = new ModulAplikasi();
                $TP->id = $newId;
                $TP->kdprofile = $kdProfile;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = ModulAplikasi::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
                $newId = $TP->id;
            }
            $TP->statusenabled =  $request['statusenabled'];
            $TP->kdmodulaplikasi = $newId;
//			$TP->kodeexternal =  $newId;
            $TP->reportdisplay = $request['reportdisplay'];
            $TP->modulaplikasi = $request['modulaplikasi'];
            if($request['modulAplikasiHead']!= ''){
                $TP->kdmodulaplikasihead = $request['modulAplikasiHead'];
            }
//			$TP->nourut = $request['nourut'];
            $TP->save();

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Error';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function saveObjekModulAplikasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $newId = ObjekModulAplikasi::max('id') +1 ;
            if ($request['id'] == ''){
                $TP = new ObjekModulAplikasi();
                $TP->id = $newId;
                $TP->kdprofile = $kdProfile;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = ObjekModulAplikasi::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
                $newId = $TP->id;
            }
            $TP->statusenabled =  $request['statusenabled'];
            $TP->kdobjekmodulaplikasi = $newId;
            $TP->kodeexternal =  $newId;
            $TP->reportdisplay = $request['objekmodulaplikasi'];
            $TP->objekmodulaplikasi = $request['objekmodulaplikasi'];
            $TP->fungsi = $request['fungsi'];
            $TP->keterangan = $request['keterangan'];
            $TP->alamaturlform = $request['alamaturlform'] != '' ? $request['alamaturlform'] : null;
            if($request['objekModulAplikasiHead']!= ''){
                $TP->kdobjekmodulaplikasihead = $request['objekModulAplikasiHead'];
            }
            $TP->nourut = $request['nourut'];
            $TP->save();

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Error';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function getDaftarObjekModulAplikasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::table('objekmodulaplikasi_s')
            ->select('*')
//            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('nourut')
            ->get();
        $result = array(
            'data' =>$data,
            'as'=> 'inhuman'
        );
        return $this->respond($result);

    }

    public function getMapLoginToModulApp(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::table('maploginusertomodulaplikasi_s as maps')
            ->join('loginuser_s as log','maps.objectloginuserfk','=','log.id')
            ->join('modulaplikasi_s as mod','maps.objectmodulaplikasifk','=','mod.id')
            ->select('maps.*','mod.modulaplikasi','log.namauser')
            ->where('maps.kdprofile', $kdProfile)
            ->where('maps.statusenabled',true)
            ->where('log.id',$request['idLogin'])
//			->orderBy('nourut')
            ->get();
        $result = array(
            'data' => $data,
            'as'=> 'inhuman'
        );
        return $this->respond($result);

    }
    public function saveMapLoginToModulApp(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $newId = MapLoginUserToModulAplikasi::max('id') +1 ;

            $TP = new MapLoginUserToModulAplikasi();
            $TP->id = $newId;
            $TP->kdprofile = $kdProfile;
            $TP->norec = $TP->generateNewId();
            $TP->statusenabled =  true;
            $TP->objectmodulaplikasifk = $request['objectmodulaplikasifk'];
            $TP->objectloginuserfk = $request['objectloginuserfk'];
            $TP->save();

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Error';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function deleteMapLoginToModulApp(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            MapLoginUserToModulAplikasi::where('objectloginuserfk',$request['objectloginuserfk'])
                ->where('kdprofile', $kdProfile)
                ->where('objectmodulaplikasifk',$request['objectmodulaplikasifk'])
                ->where('id',$request['id'])
                ->delete();

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//				'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Error';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveMapModultoObjekModulApp(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            MapObjekModulAplikasiToModulAplikasi::where('modulaplikasiid',$request['modulaplikasiid'])->delete();
            foreach ($request['data'] as $item){
                $newId = MapObjekModulAplikasiToModulAplikasi::max('id') +1 ;
                $TP = new MapObjekModulAplikasiToModulAplikasi();
                $TP->id = $newId;
                $TP->kdprofile = $kdProfile;
                $TP->norec = $TP->generateNewId();
                $TP->statusenabled =  true;
                $TP->objekmodulaplikasiid = $item['objekmodulaplikasiid'];
                $TP->modulaplikasiid = $item['modulaplikasiid'];
                $TP->save();
            }


            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Error';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function saveObjekModulAplikasiFromJson(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $newIds = ObjekModulAplikasi::max('id') +1 ;
            $no = ObjekModulAplikasi::max('nourut') +1 ;
            $TPs = new ObjekModulAplikasi();
            $TPs->id = $newIds;
            $TPs->kdprofile = $kdProfile;
            $TPs->norec = $TPs->generateNewId();
            $TPs->statusenabled =  true;
            $TPs->kdobjekmodulaplikasi = $newIds;
            $TPs->kodeexternal =  $newIds;
            $TPs->reportdisplay = 'Eksternal';
            $TPs->objekmodulaplikasi = $request['name'];
            $TPs->fungsi = '';
            $TPs->keterangan = $request['name'];
            if(isset($request['link'])){
                $TPs->alamaturlform = $request['link'];
            }
//
//			$TPs->kdobjekmodulaplikasihead = 1676 ;
            $TPs->nourut = $no;
            $TPs->save();
            $idHead=$TPs->id;

            if(isset( $request ['children'])){
                $dataRaw = $request ['children'];
                foreach ($dataRaw as $item){
                    $newId = ObjekModulAplikasi::max('id') +1 ;
                    $no = ObjekModulAplikasi::max('nourut') +1 ;
                    $TP = new ObjekModulAplikasi();
                    $TP->id = $newId;
                    $TP->kdprofile = $kdProfile;
                    $TP->norec = $TP->generateNewId();
                    $TP->statusenabled =  true;
                    $TP->kdobjekmodulaplikasi = $newId;
                    $TP->kodeexternal =  $newId;
                    $TP->reportdisplay = 'Eksternal';
                    $TP->objekmodulaplikasi = $item['name'];
                    $TP->fungsi = '';
                    $TP->keterangan = $item['name'];
                    $TP->alamaturlform = $item['link'];
                    $TP->kdobjekmodulaplikasihead = $idHead;
                    $TP->nourut = $no;
                    $TP->save();

                }
            }

//
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//				'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Error';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function saveEndWaktuLogin (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
//			$data = WaktuLogin::where('statusenabled',true)
//				->where('objectjenisruanganfk',$request['jenisruanganid'])
//				->get();
//			if(count($data) != 0){
//				$data2 = App\Master\MapRuanganToJenisRuangan::where('statusenabled',true)
//					->where('objectjenisruanganfk',$request['jenisruanganid'])
//					->delete();
//
//			}
            foreach ($request['user'] as $item) {

                $new = New WaktuLogin();
                $new->id = WaktuLogin::max('id') + 1;
                $new->kdprofile = $kdProfile;
                $new->statusenabled = true;
                $new->norec = $new->generateNewId();
//				$new->pegawaifk = $item['jenisruanganid'];
                $new->loginuserfk = $item['luid'];
                $new->expired =  $request['waktuberakhir'];
                $new->status = '';
                $new->save();
            }
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus == true) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getEndWaktuLogin (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::table('waktulogin_m as wl')
            ->join('loginuser_s as lu','lu.id','=','wl.loginuserfk')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('wl.id','wl.loginuserfk','wl.expired','lu.namauser','lu.objectpegawaifk',
                'pg.namalengkap')
            ->where('wl.kdprofile', $kdProfile)
            ->orderBy('wl.expired');
        if(isset($request['idUser']) && $request['idUser']!=''){
            $data = $data->where('lu.id',$request['idUser']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'as' => 'ramdanegie',
        );
        return $this->respond($result);

    }
    public function deleteEndWaktuLogin (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            WaktuLogin::where('id',$request['id'])->where('kdprofile', $kdProfile)->delete();

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus == true) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    protected function encryptSHA1($pass){
        return sha1($pass);
    }
    public function saveNewUser (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            if($request['id'] == ''){
                $user = LoginUser::where('namauser',$request['namauser'])->where('kdprofile', $kdProfile)->get();
                if( count($user)>0 ){
                    $transMessage = 'User sudah ada' ;
                    $result = array(
                        "status" => 400,
                        "message" => 'inhuman@epic'
                    );
                    return $this->setStatusCode($result['status'])->respond($result, $transMessage);
                }
                $new = new LoginUser();
                $new->id = LoginUser::max('id') + 1;
                $new->kdprofile = $kdProfile;//0;
                $new->statusenabled = true;
                $new->norec = $new->generateNewId();
            }else{
                $new = LoginUser::where('id', $request['id'])->where('kdprofile', $kdProfile)->first();
                $cekUser = LoginUser::where('namauser',$request['namauser'])
                    ->where('kdprofile', $kdProfile)
                    ->first();
                $sama = false ;
                if(!empty($cekUser)){
                    if($cekUser->id != $request['id'] && $request['namauser'] == $cekUser->namauser ){
                        $sama = true;
                    }
                }
                if($sama ==  true){
                    $result = array(
                        "status" => 400,
                        "as" => '#Inhuman'
                    );
                    return $this->setStatusCode($result['status'])->respond($result, 'Nama User sudah ada');
                }

            }
            $new->namaexternal = $request['namauser'];
            $new->reportdisplay = $request['namauser'];
            $new->katasandi = $this->encryptSHA1($request['katasandi']);
            $new->objectkelompokuserfk = $request['objectkelompokuserfk'];
            $new->namauser = $request['namauser'];
            $new->objectpegawaifk = $request['objectpegawaifk'];
            $new->statuslogin = 0;
            $new->passcode = $this->encryptSHA1($request['katasandi']);
            $new->save();
            if(isset($request['waktuberakhir']) && $request['waktuberakhir'] != ''){
                $news = New WaktuLogin();
                $news->id = WaktuLogin::max('id') + 1;
                $news->kdprofile = $kdProfile;
                $news->statusenabled = true;
                $news->norec = $news->generateNewId();
                $news->loginuserfk = $new->id;
                $news->expired =  $request['waktuberakhir'];
                $news->status = '';
                $news->save();

            }
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus == true) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'inhuman@epic'
            );
        } else {
            $transMessage = "Failed";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'inhuman@epic'
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getPegawaiPart(Request $request) {
        $req=$request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $datas =[];
        $data  = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap')
            ->where('st.statusenabled',true)
            ->where('st.kdprofile',$kdProfile)
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data->where('st.namalengkap','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        if(isset($req['namalengkap']) && $req['namalengkap']!="" && $req['namalengkap']!="undefined"){
            $data = $data
                ->where('st.namalengkap','ilike','%'.$req['namalengkap'].'%' );
        }
        if(isset($req['idPeg']) && $req['idPeg']!="" && $req['idPeg']!="undefined"){
            $data = $data
                ->where('st.id','=',$req['idPeg'] );
        }
        $data = $data->take(10);
        $data = $data->get();

        foreach ($data as $item){
            $datas[]  =array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }

        return $this->respond($datas);
    }
    public function getDaftarUser(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data  = \DB::table('loginuser_s as lo')
            ->join('pegawai_m as pg','lo.objectpegawaifk','=','pg.id')
            ->join('kelompokuser_s as kl','lo.objectkelompokuserfk','=','kl.id')
            ->select('lo.id','lo.namauser','lo.passcode as katakunci','pg.namalengkap','kl.kelompokuser','lo.objectkelompokuserfk',
                'lo.objectpegawaifk')
            ->where('lo.statusenabled',true)
            ->where('lo.kdprofile', $kdProfile)
            ->orderBy('lo.namauser');

        $data = $data->get();

        $result = array(
            "data" => $data,
            "message" => 'inhuman@epic'
        );

        return $this->respond($result);
    }
    public function deleteNewUser (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        try{
            if($request['id'] != ''){
                WaktuLogin::where('loginuserfk',$request['id'])
                            ->where('kdprofile', $kdProfile)
                            ->delete();
                LoginUser::where('id', $request['id'])
                        ->where('kdprofile', $kdProfile)
                        ->delete();
            }

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'inhuman@epic'
            );
        } else {
            $transMessage = "Failed";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'inhuman@epic'
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getMasterModulAplikasi (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $modulAplikasi = \DB::table('modulaplikasi_s as mad')
            // ->leftJoin('modulaplikasi_s as mods','mods.id','=','mad.kdmodulaplikasihead')
            ->select('mad.*')
            ->where('statusenabled',true)
//            ->where('kdprofile', $kdProfile)
            ->orderBy('id','desc')
            ->get();

        $data2 = [];
        foreach ($modulAplikasi as $item){
            if($item->kdmodulaplikasihead != null ){
                $mod = ModulAplikasi::where('statusenabled',true)
                    ->where('id', $item->kdmodulaplikasihead )
//                    ->where('kdprofile', $kdProfile)
                    ->first();

                $data2 [] = array(
                    'iconimage' =>  $item->iconimage ,
                    'id'  =>  $item->id ,
                    'kdmodulaplikasi' => $item->kdmodulaplikasi ,
                    'kdmodulaplikasihead' =>  $item->kdmodulaplikasihead ,
                    'kdprofile'  => $kdProfile,
                    'kodeexternal' =>  $item->kodeexternal ,
                    'modulaplikasi' =>  $item->modulaplikasi ,
                    'moduliconimage' =>  $item->moduliconimage ,
                    'modulnourut' =>  $item->modulnourut  ,
                    'namaexternal' =>    $item->namaexternal  ,
                    'norec' =>$item->norec ,
                    'nourut' => $item->nourut ,
                    'reportdisplay' => $item->reportdisplay ,
                    'statusenabled' => $item->statusenabled ,
                    'modulAplikasiHead' => array(
                        'modulaplikasi' => $mod['modulaplikasi'],
                        'id' => $mod['id'],
                    )
                );
            }else{
                $data2 [] = array(
                    'iconimage' =>  $item->iconimage ,
                    'id'  =>  $item->id ,
                    'kdmodulaplikasi' => $item->kdmodulaplikasi ,
                    'kdmodulaplikasihead' =>  $item->kdmodulaplikasihead ,
                    'kdprofile'  => $kdProfile,
                    'kodeexternal' =>  $item->kodeexternal ,
                    'modulaplikasi' =>  $item->modulaplikasi ,
                    'moduliconimage' =>  $item->moduliconimage ,
                    'modulnourut' =>  $item->modulnourut  ,
                    'namaexternal' =>    $item->namaexternal  ,
                    'norec' =>$item->norec ,
                    'nourut' => $item->nourut ,
                    'reportdisplay' => $item->reportdisplay ,
                    'statusenabled' => $item->statusenabled ,
                );
            }

        }
        $result = array(
            'data' => $data2,
        );
        return $this->respond($result);
    }
    public static function getMenDB(){
        $dataraw3 = [];
        $dataRaw = \DB::table('objekmodulaplikasi_s as oma')
            ->join('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'acdc.objekmodulaplikasiid', '=', 'oma.id')
            ->join('maploginusertomodulaplikasi_s as maps',
                function ($join){
                    $join->on('maps.objectmodulaplikasifk', '=', 'acdc.modulaplikasiid');
                })
            ->join('modulaplikasi_s as ma', 'ma.id', '=', 'acdc.modulaplikasiid')
//            ->where('oma.kdprofile', $kdProfile)
            ->where('oma.statusenabled', true)
            ->where('ma.reportdisplay', 'Menu')
            ->where('maps.objectloginuserfk',  $_SESSION["id"]  )
            ->select('oma.id', 'oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi','oma.alamaturlform','ma.modulaplikasi',
                'acdc.modulaplikasiid','oma.kodeexternal')
            ->groupBy('oma.id','oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi','oma.alamaturlform','ma.modulaplikasi',
                'acdc.modulaplikasiid','oma.kodeexternal','oma.nourut')
            ->orderBy('oma.nourut');
        $dataRaw = $dataRaw->get();
        foreach ($dataRaw as $dataRaw2) {
//                if ((integer)$dataRaw2->id < 100) {
            if ($dataRaw2->kdobjekmodulaplikasihead == null) {
                if($dataRaw2->alamaturlform != null || $dataRaw2->alamaturlform !=''){
                    $dataraw3[] = array(
                        'id' => $dataRaw2->id,
                        'parent_id' => 0,
                        'name' => $dataRaw2->objekmodulaplikasi,
                        'link' =>str_replace('#/','', $dataRaw2->alamaturlform) 
                    );
                }else{
                    $dataraw3[] = array(
                        'id' => $dataRaw2->id,
                        'parent_id' => 0,
                        'name' => $dataRaw2->objekmodulaplikasi,
                        'link' => '#',
                    );
                }

            } else {
                if ($dataRaw2->kdobjekmodulaplikasihead != null ) {
                    if($dataRaw2->alamaturlform != null || $dataRaw2->alamaturlform !='') {
                        $dataraw3[] = array(
                            'id' => $dataRaw2->id,
                            'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                            'name' => $dataRaw2->objekmodulaplikasi,
                            'link' =>str_replace('#/','', $dataRaw2->alamaturlform) 
                        );
                    }else{
                        $dataraw3[] = array(
                            'id' => $dataRaw2->id,
                            'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                            'name' => $dataRaw2->objekmodulaplikasi,
                            'link' => '#',
                        );
                    }
                } else {
                    if ($dataRaw2->modulaplikasiid == $request['id']) {
                        if ($dataRaw2->alamaturlform != null || $dataRaw2->alamaturlform != '') {
                            $dataraw3[] = array(
                                'id' => $dataRaw2->id,
                                'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                                'name' => $dataRaw2->objekmodulaplikasi,
                                'link' =>str_replace('#/','', $dataRaw2->alamaturlform) 
                            );
                        }else{
                            $dataraw3[] = array(
                                'id' => $dataRaw2->id,
                                'parent_id' => $dataRaw2->kdobjekmodulaplikasihead,
                                'name' => $dataRaw2->objekmodulaplikasi,
                                'link' => '#',
                            );
                        }
                    }
                }
            }
        }
        $data = $dataraw3;
//        return $this->respond($data);
        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
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
            }
            return $tree;
        }

        $data = recursiveElements($data);
        return $data;
    }
    public function getMenuDinamisByName(Request $request){
 
        $kdProfile = (int) $_SESSION['kdProfile'];
        $dataraw3 = [];
        $dataRaw = \DB::table('objekmodulaplikasi_s as oma')
            ->join('mapobjekmodulaplikasitomodulaplikasi_s as acdc', 'acdc.objekmodulaplikasiid', '=', 'oma.id')
            ->join('maploginusertomodulaplikasi_s as maps',
                function ($join){
                    $join->on('maps.objectmodulaplikasifk', '=', 'acdc.modulaplikasiid');
                })
            ->join('modulaplikasi_s as ma', 'ma.id', '=', 'acdc.modulaplikasiid')
            ->where('oma.statusenabled', true)
            ->where('ma.reportdisplay', 'Menu')
            ->where('maps.objectloginuserfk',  $_SESSION["id"])
            ->where('oma.objekmodulaplikasi','ilike', '%'. $request["nama"].'%')
            ->where('oma.alamaturlform','!=', '')
            ->whereNotNull('oma.alamaturlform')
            ->where('oma.alamaturlform','!=', '#')
            ->select('oma.id', 'oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi','oma.alamaturlform','ma.modulaplikasi',
                'acdc.modulaplikasiid','oma.kodeexternal')
            ->groupBy('oma.id','oma.kdobjekmodulaplikasihead', 'oma.objekmodulaplikasi','oma.alamaturlform','ma.modulaplikasi',
                'acdc.modulaplikasiid','oma.kodeexternal','oma.nourut')
            ->orderBy('oma.nourut');
        $dataRaw = $dataRaw->get();

        return $this->respond($dataRaw);
    }
}
