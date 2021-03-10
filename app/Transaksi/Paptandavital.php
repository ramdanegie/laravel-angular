<?php
namespace App\Transaksi;

class Paptandavital extends Transaksi
{
    protected $table ="paptandavital_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
