<?php
namespace App\Transaksi;

class HistoriCetakDokumen extends Transaksi
{
    protected $table = "historicetakdokumen_t";
    protected $fillable = ['norec', 'kdprofile', 'nohistori', 'kdobjeckmodulaplikasi', 'nobuktitransaksi', 'cetakke',
        'tglcetak', 'keteranganlainnya','statusenabled'];
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = "norec";
}