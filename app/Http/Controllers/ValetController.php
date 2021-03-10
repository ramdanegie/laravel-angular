<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Valet;

class ValetController extends ApiController
{
    use Valet;
    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }

    public function getTerbilang($number){
        $terbilang = $this->makeTerbilang($number);
        return $this->respond(array('terbilang' => $terbilang));
    }

    public function HelloWorld(){
        return $this->respond (array('Hello bang riko... '));
    }



}
