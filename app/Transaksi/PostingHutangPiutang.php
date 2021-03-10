<?php

namespace App\Transaksi;

class PostingHutangPiutang extends Transaksi
{
    protected $table = 'postinghutangpiutang_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}