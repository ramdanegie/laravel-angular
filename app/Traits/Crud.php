<?php
namespace App\Traits;

use Validator;
use App\Exceptions\ExecuteQueryException;
use DB;

Trait Crud
{
    /**
     * @var
     */
    private $modelName;

    /**
     * @return mixed
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

    //for validation
    protected $useValidation = false;
    protected $ruleCustomMessages = null;
    protected $rules = null;

    //for transformer
    protected $transformer;
    protected $transformerClassName = null;

    //for translate message
    protected $modulPath = 'lib.modul';
    protected $modulName = null;

    //untuk nyisipin fungsi
    protected $errorMessage=array();
    protected $extraFunc=array();

    protected function  addErrorMessage($msg){
        $this->errorMessage[] = $msg;
    }

    protected function makeTransform()
    {
        if ($this->transformerClassName == null) {
            $this->transformerClassName = $this->getModelName() . "Transformer";
        }

        $class = 'App\\Transformers\\' . $this->middlePath . '\\' . $this->transformerClassName;
        $this->transformer = new $class();
    }

    protected function getModulLabel()
    {
        $modul = ($this->modulName == null) ? strtolower($this->getModelName()) : $this->modulName;
        return trans($this->modulPath . "." . strtolower($this->middlePath) . "." . $modul);
    }

    protected function getModelNameSpace()
    {
        return 'App\\' . $this->middlePath . '\\' . $this->getModelName();
    }

    protected function setData($model, $data){
        try{
            foreach ($data as $key => $value) {
                if(($value!= null && !empty($value)) || $value=="0" || $value ==0){
                    $model->{$key} = $value;
                }
            }
            return $model;
        }
        catch(\Exception $e){
            throw new Exception($e);
        }
    }

    private function grab_data($id){
        $class = $this->getModelNameSpace();
        $msg = trans('msg.show.failed', ['modul' => $this->getModulLabel()]);
        $data = $class::findOrThrowException($id, $msg);
        return $data;
    }

    protected function listData($request, $isTransformed=true)
    {

        $limit = $request->input('limit') ? $request->input('limit') : null;
        //$sort_by = 'id';
        $sort_type = 'asc';
        $class = $this->getModelNameSpace();
        $listdata = new $class;

        //sort

        if ($request->input('sort') && $request->input('sort') != "") {
            $arraySort = $this->clearEmptyArray(explode(',', $request->input('sort')));
            foreach ($arraySort as $key => $value) {
                $sort = explode(':', $value);
                $sort[0] = trim($sort[0]);
                if(!empty($sort[0]) && $this->transformer->isListed($sort[0])){
                    $sid = (!isset($sort[1]) && empty($sort[1])) ? $sort_type : $sort[1];
                    $listdata = $listdata->orderBy($this->transformer->transformSigleField($sort[0]), $sid);
                }
            }
        }
//        {
//            $listdata = $listdata->orderBy($sort_by, $sort_type);
//        }

        //search
        if($request->input('search') && $request->input('search') != ""){
            $arraySearch = $this->clearEmptyArray(explode(',', $request->input('search')));
            foreach ($arraySearch as $key => $value) {
                $search = explode(':', $value);
                $search[0] = trim($search[0]);
                if(isset($search[1]) && !empty($search[1]) && !empty($search[0]) && $this->transformer->isListed($search[0])){
                    $search_type = (strrpos($search[1], '%')!==FALSE) ? 'LIKE' : '=';
                    $listdata = $listdata->where($this->transformer->transformSigleField($search[0]), $search_type, $search[1]);
                }
            }
        }

        if ($limit) {
            $listdata = $listdata->paginate($limit);
            $page_info = $listdata->toArray();
            $data = array(
//                'data' => $this->transformer->transformCollection($listdata),
                'total' =>$page_info['total'],
                'per_page' => intval($page_info['per_page']),
                'current_page' =>$page_info['current_page'],
                'last_page' =>$page_info['last_page'],
                'from' =>$page_info['from'],
                'to' =>$page_info['to'],
//                'next_page_url' =>$page_info['next_page_url'],
//                'prev_page_url' =>$page_info['prev_page_url'],

            );
            return $this->respond($data);
        } else {
            $listdata = $listdata->get();
        }

        if($isTransformed){
//            $data = array("data" => $this->transformer->transformCollection($listdata));
            return \Response::json($this->transformer->transformCollection($listdata), 200);
        }else{

            $data['data'] = $listdata;
            return $data;
        }



    }

    protected function grab_edit($id){
        return $this->respond($this->transformer->transformToForm($this->grab_data($id)));
    }

    protected function direct($id){
        return $this->grab_show($id);
    }

    protected function grab_show($id)
    {
        return $this->respond($this->transformer->transform($this->grab_data($id)));
    }

    protected function saveCreate($request)
    {
        $class = $this->getModelNameSpace();
        $data = $this->transformer->transformBack($request, $class);

        if($this->useValidation){
            $validator  = ($this->ruleCustomMessages==null) ? Validator::make($data, $this->rules) : Validator::make($data, $this->rules, $this->ruleCustomMessages);

            if ($validator->fails()) {
                return $this->respondValidation($this->transformer->transformValidation($validator->errors()->toArray()), trans('msg.validation_error'));
            }
        }

        $newdata =  new $class;
        if(isset($this->extraFunc['SaveCreate'])){
            if(method_exists($this, $this->extraFunc['SaveCreate'])){
                $data = $this->{$this->extraFunc['SaveCreate']}($data);
                if(count($this->errorMessage)>0){
                    return $this->respondValidation($this->errorMessage, trans('msg.validation_error'));
                }
            }
        }
        $newdata =$this->setData($newdata, $data);
        try{
            $newdata->save();
            return $this->setStatusCode(201)->respond([], trans('msg.insert.success', ['modul' => $this->getModulLabel()]));
        }catch(\Exception $e){

            $msg = trans('msg.insert.failed', ['modul' => $this->getModulLabel()]);
//            throw new ExecuteQueryException($msg);
            throw new ExecuteQueryException($e);
        }

    }

    protected function saveUpdate($request, $id)
    {
        $class = $this->getModelNameSpace();
        $data = $this->transformer->transformBack($request, $class);
        if($this->useValidation){
            $validator  = ($this->ruleCustomMessages==null) ? Validator::make($data, $this->rules) : Validator::make($data, $this->rules, $this->ruleCustomMessages);

            if ($validator->fails()) {
                return $this->respondValidation($this->transformer->transformValidation($validator->errors()->toArray()), trans('msg.validation_error'));
            }
        }

        $msg = trans('msg.show.failed', ['modul' => $this->getModulLabel()]);
        $dataupdate = $class::findOrThrowException($id, $msg);
        $dataupdate = $this->setData($dataupdate, $data);
        try{
            $dataupdate->save();
            return $this->setStatusCode(202)->respond([], trans('msg.update.success', ['modul' => $this->getModulLabel()]));
        }catch(\Exception $e){
            throw new ExecuteQueryException($msg);
        }
    }

    protected function doDestroy($id)
    {
        $class = $this->getModelNameSpace();
        $msg = trans('msg.show.failed', ['modul' => $this->getModulLabel()]);
        $data = $class::findOrThrowException($id, $msg);
        try{
            $data->delete();
            return $this->respond([], trans('msg.delete.success', ['modul' => $this->getModulLabel()]));
        }
        catch(\Exception $e){
            $msg = trans('msg.delete.failed', ['modul' => $this->getModulLabel()]);
            throw new ExecuteQueryException($msg);
        }
    }

}