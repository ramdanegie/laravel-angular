<?php
namespace App\Traits\Dev;


Trait GenerateClassTemplateModel
{
    protected function getTemplateMasterModel($attribute){
$string = '<?php
namespace App\Master;

class '.$attribute['className'].' extends MasterModel
{
    protected $table ="'.$attribute['tableName'].'";
    protected $fillable = [];
    public $timestamps = false;
    

    //contoh belongsTo
    //public function {method}(){
    //    return $this->belongsTo({namespacemodel}, {fk});
    //}

    //public function {method}(){
        //return $this->hasMany({namespacemodel}, {fk});
    //}
}
';

        return $string;
    }
}