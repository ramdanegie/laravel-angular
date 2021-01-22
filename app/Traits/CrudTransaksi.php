<?php
namespace App\Traits;

use Illuminate\Http\Request;
use App\Http\Requests;
use Validator;


Trait CrudTransaksi{
    use Crud;

    public $middlePath = 'Transaksi';
    
}
