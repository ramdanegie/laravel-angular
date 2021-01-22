<?php
namespace App\Traits\Dev;


Trait GenerateClassTemplateTransformer
{
    protected function getTemplateMasterTransformer($attribute){
        $string = '<?php
namespace App\Transformers\Master;

use App\Transformers\Transformer;

class '.$attribute['className'].' extends Transformer{
    protected $list = [
';
        foreach ($attribute['list']['transform-field'] as $key => $item){
            $string .='        "'.$key.'"       => "'.$item.'",
        ';
        }
        foreach ($attribute['list']['unlist-transform'] as $key => $item){
            $string .='//        "'.$key.'"     => "'.$item.'",
        ';
        }

        $string .= '
    ];
 }
 ';
        return $string;
    }
}