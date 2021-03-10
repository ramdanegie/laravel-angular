<?php
/**
 * Created by PhpStorm.
 * User: Sany
 * Date: 11/28/2017
 * Time: 8:35 PM
 */

namespace App\Http\Controllers\SysAdmin\Master;
use App\Http\Controllers\ApiController;
use App\Master\MapKelompokPasientoPenjamin;
use App\Master\Rekanan;
use App\Master\SettingDataFixed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Traits\CrudMaster;
use App\Traits\Valet;

use App\Master\ChartOfAccount;
use DB;
use App\Traits\Dev\Designation;
use Date;

class SettingDataFixedController extends ApiController
{
    use CrudMaster;
    use Designation;
    use Valet;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getDataFixed(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataDataFixed = \DB::table('settingdatafixed_m as sf')
            ->select('sf.id', 'sf.keteranganfungsi','sf.namafield' ,'sf.nilaifield', 'sf.tabelrelasi',
                'sf.typefield','sf.statusenabled')
            ->where('sf.kdprofile', $kdProfile)
//            ->where('sf.statusenabled',true)
            ->orderBy('sf.id','desc')
            ->take(50);

        if (isset($request['idDataFixed']) && $request['idDataFixed'] != "" && $request['idDataFixed'] != "undefined") {
            $dataDataFixed = $dataDataFixed->where('sf.id', '=', $request['idDataFixed']);
        }
        if (isset($request['ketFungsi']) && $request['ketFungsi'] != "" && $request['ketFungsi'] != "undefined") {
            $dataDataFixed = $dataDataFixed->where('sf.keteranganfungsi', 'ilike', '%'. $request['ketFungsi'].'%');
        }
        if (isset($request['namaFild']) && $request['namaFild'] != "" && $request['namaFild'] != "undefined") {
            $dataDataFixed = $dataDataFixed->where('sf.namafield', 'ilike','%'. $request['namaFild'].'%');
        }
        if (isset($request['nilaiField']) && $request['nilaiField'] != "" && $request['nilaiField'] != "undefined") {
            $dataDataFixed = $dataDataFixed->where('sf.nilaifield', 'ilike','%'. $request['nilaiField'].'%');
        }

        $dataDataFixed  =$dataDataFixed -> get();
        $result = array(
            'settingdatafixed' => $dataDataFixed,
            'message' => 'Hidayat',
        );

        return $this->respond($result);
    }

    public function getSettingById(Request $request, $id){
        $kdProfile = (int) $this->getDataKdProfile($request);
//        $data = \DB::table('setting as rek')
//            ->select('rek.*','jr.jenisrekanan','mp.id as idmap','mp.objectkelompokpasienfk')
//            ->where('rek.id','=',$id)
//            ->get();

        $datas= DB::select(DB::raw("select * from settingdatafixed_m
              where kdprofile = $kdProfile and id='$id'
              and statusenabled=true"));

        return $this->respond($datas);
    }

    public function SaveSettingDataFixed(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();

        $idDataFixed = SettingDataFixed::max('id');
        $idDataFixed = $idDataFixed + 1;

        if ($request['datafixed']['iddatafixed']==''){
            $newDF = new SettingDataFixed();
            $newDF->id = $idDataFixed;
            $newDF->norec = $idDataFixed;
            $newDF->kodeexternal = $idDataFixed;
            $newDF->kdprofile = $kdProfile;
            $newDF->statusenabled = true;
        }else{
            $newDF =  SettingDataFixed::where('id',$request['datafixed']['iddatafixed'])-where('kdprofile', $kdProfile)->first();
        }
        $newDF->kodeexternal = str_limit( $request['datafixed']['kodeexternal'],10);
        $newDF->namaexternal = $request['datafixed']['namaexternal'];
        $newDF->reportdisplay = $request['datafixed']['reportdisplay'];
        $newDF->fieldkeytabelrelasi = $request['datafixed']['fieldkeytabelrelasi'];
        $newDF->fieldreportdisplaytabelrelasi = $request['datafixed']['fieldreportdisplaytabelrelasi'];
        $newDF->keteranganfungsi = $request['datafixed']['keteranganfungsi'];
        $newDF->namafield = $request['datafixed']['namafield'];
        $newDF->nilaifield = $request['datafixed']['nilai'];
        $newDF->tabelrelasi = $request['datafixed']['tabelrelasi'];
        $newDF->typefield = $request['datafixed']['typefield'];
        if(isset($request['datafixed']['kelompok'])){
            $newDF->kelompok = $request['datafixed']['kelompok'];
        }
        try {
            $newDF->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Data Fixed Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "result" => $newDF,//$noResep,,//$noResep,
                "as" => 'uhman',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "SaveSettingDataFixed" => $newDF,//$noResep,
                "as" => 'uhman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }


    public function HapusSettingDataFixed(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();

        try {
            $newDF = SettingDataFixed::where('id',$request['iddatafixed'])
                ->where('kdprofile', $kdProfile)
                ->update(['statusenabled' => 'f']);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Data Fixed Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "datRekanan" => $newDF,//$noResep,,//$noResep,
                "as" => 'hidayat',
            );
        } else {
            $transMessage = "Hapus Data Fixed Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "datRekanan" => $newDF,//$noResep,
                "as" => 'hidayat',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

	public function deleteSetting(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
		DB::beginTransaction();
		try {
			$newDF = SettingDataFixed::where('id',$request['id'])
                ->where('kdprofile', $kdProfile)
                ->delete();
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';

		}
		if ($transStatus == 'true') {
			$transMessage = "Hapus Data Fixed Berhasil";
			DB::commit();
			$result = array(
				"status" => 201,
				"message" => $transMessage,
				"as" => 'inhuman@epic',
			);
		} else {
			$transMessage = "Hapus Data Fixed Gagal";
			DB::rollBack();
			$result = array(
				"status" => 400,
				"message"  => $transMessage,
				"as" => 'inhuman@epic',
			);
		}
		return $this->setStatusCode($result['status'])->respond($result, $transMessage);
	}
    public function updateStatuEnabled(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $newDF = SettingDataFixed::where('id',$request['id'])
                ->where('kdprofile', $kdProfile)
                ->update(
                ['statusenabled' => $request['statusenabled'] ]
            );
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }
        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'inhuman@epic',
            );
        } else {
            $transMessage = "Terjadi Kesalahan";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'inhuman@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getKelompokSettingDataFix(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::table('settingdatafixed_m')
            ->select(DB::raw("case when kelompok is null then 'Lain-lain' else kelompok end as kelompok"))
            ->where('kdprofile', $kdProfile)
            ->groupBy('kelompok')
            ->orderBy('kelompok')
            ->get();
        $result = array(
          'data' => $data,
          'as' => 'inhuman@epic'
        );
        return $this->respond($result);
    }
    public function getSettingDetail(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        if($request['kelompok'] == 'Lain-lain'){
            $request['kelompok']  = null;
        }
        $dataRaw = \DB::table('settingdatafixed_m')
            ->where('kelompok', $request['kelompok'])
            ->where('kdprofile', $kdProfile)
            ->select('*','keteranganfungsi as caption','nilaifield')
            ->orderBy('id');
        $dataRaw = $dataRaw->get();
        $dataraw3A =[];

        foreach ($dataRaw as $dataRaw2) {
            $head = '';
            $type =  $dataRaw2->typefield;
            if(stripos( $dataRaw2->typefield, 'Str') !== FALSE
                || stripos( $dataRaw2->typefield, 'Int') !== FALSE
                    ||stripos( $dataRaw2->typefield, 'Char') !== FALSE ){
                if($dataRaw2->tabelrelasi != null){
                    $type = 'combobox';
                }else{
                    $type = 'textbox';
                }
            }elseif($dataRaw2->typefield == 'combobox') {
                $type = 'combobox';
            }else{
                $type = 'textbox';
            }

            $dataraw3A[] = array(
                'kdprofile' => $dataRaw2->kdprofile,
                'statusenabled' => $dataRaw2->statusenabled,
                'kodeexternal'=> $dataRaw2->kodeexternal,
                'namaexternal' => $dataRaw2->namaexternal,
                'reportdisplay' => $dataRaw2->reportdisplay,
                'fieldkeytabelrelasi' => $dataRaw2->fieldkeytabelrelasi,
                'caption' => $head . $dataRaw2->caption  ,

                'cbotable' => $dataRaw2->tabelrelasi,
                'fieldreportdisplaytabelrelasi' => $dataRaw2->fieldreportdisplaytabelrelasi,
                'keteranganfungsi' => $dataRaw2->keteranganfungsi,
                'namafield' => $dataRaw2->namafield,
                'id' => $dataRaw2->id ,
                'nilaifield' => $dataRaw2->nilaifield ,
                'tabelrelasi' => $dataRaw2->tabelrelasi,
                'typefield' => $dataRaw2->typefield,
                'type' =>$type ,
                'kelompok' => $dataRaw2->kelompok,
                'value' => $dataRaw2->nilaifield ,
                'text' => $dataRaw2->reportdisplay,
            );
        }

        $result = array(
            'kolom1' => $dataraw3A,
            'message' => 'inhuman@epic',
        );

        return $this->respond($result);
    }
    public function getComboPart(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $request['id'];
        $req= $request->all();
        $setting = DB::table('settingdatafixed_m')
            ->select('*')
            ->where('kdprofile', $kdProfile)
            ->where('id',$id)
            ->get();

        $table =  $setting[0]->tabelrelasi;
        $namaField = strtolower ($setting[0]->fieldreportdisplaytabelrelasi);
        $keyField = strtolower ($setting[0]->fieldkeytabelrelasi);
        $table = strtolower ($table);
        $data  = \DB::table("$table")
            ->select("$namaField as text" ,"$keyField as value")
            ->where('statusenabled',true)
            ->orderBy("$keyField");

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
                $data = $data->where("$namaField",'ilike','%'. $req['filter']['filters'][0]['value'].'%' );

        };
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }
    public function updateSettingDataFix(Request $request) {
        DB::beginTransaction();
        $dataReq = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = $dataReq['data'];

        try {

            $i=0;
            foreach ($data as $item) {
                if (is_array($item['values'])){
                    $value = $item['values']['value'] ;
                    $text = $item['values']['text'] ;
                }else{
                    $value = $item['values'];
                    $text = '';
                }

                $EMRD =  SettingDataFixed::where('id',$item['id'])->where('kdprofile', $kdProfile)->first();
                $EMRD->statusenabled = $dataReq['head'];
                $EMRD->nilaifield = $value;
                $EMRD->reportdisplay = $text;
                $EMRD->save();
                $i = $i + 1;
            }



            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Update Setting ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'inhuman@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getTable(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req= $request->all();
        $data  = \DB::table("information_schema.tables")
            ->select("table_name")
            ->where('table_schema','=','public')
            ->where('table_type','=','BASE TABLE')
            ->orderBy("table_name");

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data->where("table_name",'ilike','%'. $req['filter']['filters'][0]['value'].'%' );

        };
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }
    public function getFieldTable(Request $request){;
        $table = $request['tablename'];
        $data  = DB::select(DB::raw("SELECT
            COLUMN_NAME
            FROM
            information_schema.COLUMNS
            WHERE
            TABLE_NAME = '$table';"));
        $result = array(
            "data" => $data,
            "as" => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getDataFromTable(Request $request){;
        $table = $request['table_name'];
        $column = $request['column_name'];
        $data  = DB::select(DB::raw("
            select $column as name from $table
             "));
        $result = array(
            "data" => $data,
            "as" => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getReportDisplayTable(Request $request){;
        $table = $request['table_name'];
        $column = $request['column_name'];
        $nilai = $request['nilai'];
        $data  = DB::select(DB::raw("
            select $column as id from $table
             "));
        $result = array(
            "data" => $data,
            "as" => 'as@epic',
        );
        return $this->respond($result);
    }
    protected function getSettingDataFixedGeneric($namaField, Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $set = SettingDataFixed::where('namafield', $namaField)->where('kdprofile', $kdProfile)->first();
        return $set->nilaifield;
    }
};