<?php
namespace App\Transaksi;

class IndikatorInformedConsent extends Transaksi
{
    protected $table = "indikator_informedconsent_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}