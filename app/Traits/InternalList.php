<?php
namespace App\Traits;


use Illuminate\Http\Request;
use App\Http\Requests;
Trait InternalList
{
    protected $searchList = array();

    /**
     * @return array
     */
    public function getSearchList()
    {
        return $this->searchList;
    }

    public function setSearchList($searchList)
    {
        if(is_array($searchList)){
            foreach ($searchList as $key => $value){
                $this->searchList[$key] = $searchList;
            }
        }
        return $this;
    }

    protected function getList($objectModel, $objectTransform, $request)
    {
        $select = '*';
//        $sort_by = $objectModel->getPrimaryKey();
        $sort_type = 'asc';
        $selectAttribute = $objectTransform->getList();
        if ($request->input('sort') && $request->input('sort') != "") {
            $arraySort = $this->clearEmptyArray(explode(',', $request->input('sort')));
            foreach ($arraySort as $key => $value) {
                $sort = explode(':', $value);
                $sort[0] = trim($sort[0]);
                if(!empty($sort[0]) && $objectTransform->isListed($sort[0])){
                    $sid = (!isset($sort[1]) && empty($sort[1])) ? $sort_type : $sort[1];
                    $objectModel = $objectModel->orderBy($objectTransform->transformSigleField($sort[0]), $sid);
                }
            }
        }
//        else{
//            $objectModel = $objectModel->orderBy($sort_by, $sort_type);
//        }

        foreach ($this->getSearchList() as $key => $value){
            $objectModel = $objectModel->where($key, $value);
        }

        if($request->input('search') && $request->input('search') != ""){
            $arraySearch = $this->clearEmptyArray(explode(',', $request->input('search')));
            foreach ($arraySearch as $key => $value) {
                $search = explode(':', $value);
                $search[0] = trim($search[0]);
                if(isset($search[1]) && !empty($search[1]) && !empty($search[0]) && $objectTransform->isListed($search[0])){
                    $search_type = (strrpos($search[1], '%')!==FALSE) ? 'LIKE' : '=';
                    $objectModel = $objectModel->where($objectTransform->transformSigleField($search[0]), $search_type, $search[1]);
                }
            }
        }

        if ($request->input('select') && $request->input('select') != "*") {
            $arraySelect = $this->clearEmptyArray(explode(',', $request->input('select')));
            $selectAttribute = $objectTransform->unTransformColumn($arraySelect);
            $select =[];
            foreach ($selectAttribute as $key => $value){
                if(count(explode('.', $key))==1){
                    $select = $key;
                }
            }
        }
        if(count($select) == count($selectAttribute)){
            $objectModel = $objectModel->select($select);
        }

        if ($request->input('limit')) {
            $objectModel = $objectModel->paginate($request->input('limit'));
        } else {
            $objectModel = $objectModel->get();
        }
        return $objectTransform->transformColumn($objectModel, $selectAttribute);

    }
}