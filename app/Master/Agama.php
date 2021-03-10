<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;
//use DB;

class Agama extends MasterModel
{
    protected $table = 'agama_m';
    protected $fillable = [];
    public $timestamps = false;

//    public function getTableColumns() {
//        $result = [];
//        $case =  $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
//        return $case;

//        return DB::connection()->getDoctrineColumn($this->getTable())->getType()->getName();
//        cari disini
//        http://stackoverflow.com/questions/18562684/how-to-get-database-field-type-in-laravel
//    }


}


