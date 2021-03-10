<?php
namespace App\Transaksi;

class SuratKematianPasienDelete extends Transaksi {
    protected $table = "suratkematianpasiendelete_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}