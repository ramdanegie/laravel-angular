<?php
namespace App\Transaksi;

class FormulisObat extends Transaksi
{
    protected $table = "formulirobat_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}