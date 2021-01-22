<?php
namespace App\Traits\Dev;


Trait GenerateClass
{
    use GenerateClassTemplateController;
    use GenerateClassTemplateTransformer;
    use GenerateClassTemplateModel;
    use StructureSchema;


    protected $typeClass= array('controller', 'transformer');
    protected $path = array(
        'controller' => '',
        'transformer' => '',
    );

    protected $pathNamespaceModel="App\\Master\\";

    protected $classModelName;
    protected $className;
    protected $actionStatus='preview';
    protected $messages = array();
    protected $tempMessages = array();

    private function createFile($path, $string, $type){
//        dd($path)
        $createClass= fopen($path, "w") or die("Gagal Membuat Class Type: ".$type." dengan path: ".$path);
        fwrite($createClass, $string);
        fclose($createClass);
        $this->messages[]= "BERHASIL Membuat Class Type: ".$type." dengan path: ".$path;
    }

    protected function showMsg($msg=null){
        if($msg!=null){
            $this->messages[]=$msg;
        }
        return \Response::json($this->messages, 200);
    }

    protected function setMsg($msg){
        $this->messages[]=$msg;
    }

    protected function getTransformList(){
        $classModel = $this->pathNamespaceModel;
        $model = new $classModel;
        $this->table = $model->getTable();
        $tableColumns= $this->getTableColumns();
        $foreignKeys = $this->getListForeignKey();

        $transformList = array();
        $unlistTransform = array();
//        cek modelnya udah ada blum kalo engga.. masukin ke messsage
        
        foreach($tableColumns as $column){

            if (array_key_exists($column, $foreignKeys)){
                $this->tempMessages = array();
                $statusCheck = false;
                $posibleName = $foreignKeys[$column]['possibleName'];
                $posibleMethod = $foreignKeys[$column]['possibleMethod'];
                $tableName = $foreignKeys[$column]['tableFk'];



//                if(method_exists($model, $posibleMethod)){
//                    $this->tempMessages[]= 'Method {'.$posibleMethod.'()} di Model: {'.$this->classModelName.'} tidak ada';
//                    $statusCheck = false;
//                }

//                if($this->isColumnExist('bahanproduk_m', 'namabahanproduk')){
//                    echo 'ada';
//                }else{
//                    echo "tidak ada";
//                }
//                die();
                $arrayTest = array('', 'nama');
                foreach ($arrayTest as $test){
                    if($this->isColumnExist($tableName, $test.$posibleName) ){
                        $statusCheck = true;
                        $posibleName = $test.$posibleName;
                    }
                }
                if($statusCheck==false){
                    $this->tempMessages[]= 'table {'.$tableName.'()} atau column '.$column.' nama'.$column."tidak ada";
                }


//                cek column foreach 2 kali. tambah nama atau engga..
                if($statusCheck){
                    $transformList[$column] = $this->getDesignation(str_replace('object', '', $column));
                    $columnName =  $posibleMethod.'.'.$posibleName;
                    $transformList[$columnName] = $this->getDesignation($posibleName);
                }else{
                    $unlistTransform[$column] = $this->getDesignation(str_replace('object', '', $column));
                    $columnName = $posibleMethod.'.'.$posibleName;
                    $unlistTransform[$columnName] = $this->getDesignation($posibleName);
//                    $unlistTransform['data']['detail'] = $foreignKeys[$column];
//                    $unlistTransform[$columnName.'-data']['message'] = $this->tempMessages;
                }
            }else{
                $transformList[$column] = $this->getDesignation($column);
            }
        }
        $result = array('transform-field' => $transformList, 'unlist-transform' =>$unlistTransform, 'messages' =>$this->messages);
        return $result;
    }

    protected function generateClass(){
        if($this->actionStatus=='commit'){
            foreach ($this->typeClass as $typeClass){
                $namaMethod = 'create'.ucfirst($typeClass).'Class';
                $this->{$namaMethod}($typeClass);
            }
            return $this->showMsg();
        }else{
            $result = $this->getTransformList();
            return \Response::json($result, 200);
        }
    }

    protected function  checkPathExist($path){
        if(file_exists($path)){
            die('file '.$path." =====>sudah ada");
        }
    }

    protected function createControllerClass($typeClass){
        $templateAttribute= array(
            "className" => $this->className.'Controller',
            "modelName" => $this->classModelName
        );
        $string = $this->getTemplateMasterController($templateAttribute);
        $path = $this->path[$typeClass].$this->className."Controller.php";
        $this->checkPathExist($path);
        $this->createFile($path, $string, $typeClass);
    }

    protected function createTransformerClass($typeClass){
        $templateAttribute= array(
            "className" => $this->className.'Transformer',
            "modelName" => $this->classModelName,
            "list" => $this->getTransformList(),
        );

//        dd($templateAttribute);
        $string = $this->getTemplateMasterTransformer($templateAttribute);
        $path = $this->path[$typeClass].$this->className."Transformer.php";
        $this->checkPathExist($path);
        $this->createFile($path, $string, $typeClass);
    }

    protected function generateClassModel($path, $tablename){
        $templateAttribute = array(
            'className' => $this->classModelName,
            'tableName' => $tablename
            );
        
        $string= $this->getTemplateMasterModel($templateAttribute);
        $this->checkPathExist($path);
        $this->createFile($path, $string, 'Model');
        return $this->showMsg();
    }
}
