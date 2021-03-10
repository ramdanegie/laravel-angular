<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 30/07/2018
 * Time: 15.13
 */
namespace App\Transaksi;

class BibliographyBuku extends Transaksi
{
    protected $table ="sdm_bibliographybuku_t";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";


}