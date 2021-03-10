<?php
namespace App\Transaksi;

class FormulisObatDetail extends Transaksi
{
    protected $table = "formulirobatdetail_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}