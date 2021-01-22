<?php
namespace App\Traits\Dev;
use DB;

Trait StructureSchema
{
    use Designation;
    protected $table;
    protected $conn='pgsql';

    private function getTableStructure(){
        return \DB::connection($this->conn)->getDoctrineSchemaManager()->listTableColumns($this->table);
    }

    protected function getTableCulumnAttribute(){
        $table = $this->getTableStructure();
//        return $table;
        $result = array();
        foreach ($table as $column => $attribute){
            $result[$column]['type']= $attribute->getType()->getName();
            $result[$column]['length']= $attribute->getLength();
            $result[$column]['not_null']= $attribute->getNotnull();
        }
        return $result;
    }

    protected function getTableColumns() {
        return \DB::getSchemaBuilder()->getColumnListing($this->table);
    }

    protected function getTableColumnsWithDataDummy(){
        $columns = $this->getTableColumns();
        $result = array();
        foreach ($columns as $column) {
            $dumValue=null;
            $type = DB::connection()->getDoctrineColumn($this->table, $column)->getType()->getName();
            switch ($type) {
                case 'integer':
                    $dumValue = 1;
                    break;
                case 'smallint':
                    $dumValue = 1;
                    break;
                case 'bigint':
                    $dumValue = 1;
                    break;
                case 'boolean':
                    $dumValue = 0;
                    break;
                case "string":
                    $dumValue = "string";
                    break;
                default:
                    $dumValue = "string";
            }
            $result[$column] = $dumValue;

        }
        return $result;
    }


    protected function getListForeignKey(){
        $foreignKeys =  DB::connection()->getDoctrineSchemaManager()->listTableForeignKeys($this->table);
        $result =  array();
        foreach ($foreignKeys as $foreignKey) {
            $fkName = $foreignKey->getLocalColumns();
            if(is_array($fkName)){
                $fkName = $fkName[0];
                $tableFk= $foreignKey->getForeignTableName();
                $fk = $foreignKey->getForeignColumns()[0];

                $possibilityFieldName = $tableFk;
                $arrayPissibility = explode('_',$possibilityFieldName);
                $possibleName = $arrayPissibility[0]; //carilagi buat nama tablenya karna bisa aja pengambilan namanya salah
                $posibleMethod = $this->getDesignation($possibleName, 'method');

                $result[$fkName] = array();
                $result[$fkName]['tableFk'] = $tableFk;
                $result[$fkName]['fk'] = $fk;
                $result[$fkName]['possibleName'] = $possibleName;
                $result[$fkName]['possibleMethod'] = $posibleMethod;
            }
        }

//        return $foreignKeys;
        return $result;
    }

    protected function  isColumnExist($table, $column){
//        return DB::connection()->hasColumn($table, $column);
        return \Schema::hasColumn($table, $column);
    }


    ///test
    protected function getTableStructureTest(){

        $table = $this->getTableStructure();
//        return $table;
//        $result = array();
//        foreach ($table as $column => $attribute){
//            $result[$column]['type']= $attribute->getType()->getName();
//            $result[$column]['length']= $attribute->getLength();
//            $result[$column]['not_null']= $attribute->getNotnull();
//        }
        return $table;
    }



}