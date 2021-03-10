<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Traits\InternalList;

class ListGeneric extends ApiController
{
    use InternalList;
    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }

    ///siapapun lu.. jangan sok ngubah-ngubah ya. mending lu bikin fungsi baru aja sono.
    protected function getPrefix($model){
        $folderList = array('Master', 'Transaksi');
        $prefix = 'Transaksi';
        foreach ($folderList as $key => $value) {
            $classCheck = 'App\\'.$value.'\\' . ucfirst($model);
            if(class_exists($classCheck)){
                $prefix = $value;
                break;
            }
        }
        return $prefix;
    }

    ///siapapun lu.. jangan sok ngubah-ngubah ya. mending lu bikin fungsi baru aja sono.
    public function getListGeneric(Request $request)
    {
        $model = $request->input('master');
        if(!$request->input('master')){
            $model = $request->input('view');
        }
        $prefix =$this->getPrefix($model);

        $classModel = 'App\\'.$prefix.'\\' . ucfirst($model);
        $classTransformer = 'App\\Transformers\\'.$prefix.'\\' . ucfirst($model).'Transformer';

        if ($model and class_exists($classModel) && class_exists($classTransformer)) {
            $transformer = new $classTransformer;
            $listdata  = new $classModel;
            return $this->respond($this->getList($listdata, $transformer, $request));

        } else {
            return $this->respondNotFound();
        }
    }

    public function getRekanaByKelompokPasien(Request $request){
        $filter = $request->all();
        $data= \DB::table('rekanan_m as r')
            ->join('mapkelompokpasientopenjamin_m as map', 'r.id', '=', 'map.kdpenjaminpasien')
            ->select('r.id', 'r.reportdisplay');

        if(isset($filter['kdKelompokPasien']) && $filter['kdKelompokPasien']!=""){
            $data = $data->where('map.objectkelompokpasienfk', $filter['kdKelompokPasien']);
        }

        $data =$data->get();

        return $this->respond($data, 'Data Rekanan');

    }

    public function GetHubunganKeluarga(Request $request)
    {
        $hasilKategoriTriage = \DB::table('hubungankeluarga_m as hb')
            ->select('hb.*')
            ->where('statusenabled', 't')
            ->get();


        $result = array(
            'hasilKategoriTriage' => $hasilKategoriTriage,

        );

        return $this->respond($result);
    }

}
