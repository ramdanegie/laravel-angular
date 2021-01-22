<?php
namespace App\Traits\Dev;


Trait GenerateClassTemplateController
{
    protected function getTemplateMasterController($attribute){
$string = '<?php

namespace App\Http\Controllers\Master;

use App\Traits\CrudMaster;

class '.$attribute['className'].' extends MasterController
{
    use CrudMaster;

    public function __construct()
    {
        $this->modelName= \''.$attribute['modelName'].'\';

        parent::__construct();
        $this->makeTransform();

        $this->useValidation=false;
        $this->ruleCustomMessages = array();
        $this->rules = array();
    }
}
';

        return $string;
    }
}