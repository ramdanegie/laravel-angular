<?php
namespace App\Traits;

Trait CrudMaster{
    use Crud;

    public $middlePath = 'Master';

    //ini untuk dapetin id dari sequence global
    public function generateId($data){
        if(\DB::connection()->getName() == 'pgsql'){
            $next_id = \DB::select("select nextval('hibernate_sequence')");
            $data['id'] = $next_id['0']->nextval;
        }
        return $data;
    }
    
}
