<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 31/07/2018
 * Time: 13.33
 */
namespace App\Transaksi;

class BibliographyJurnal extends Transaksi
{
    protected $table ="sdm_bibliographyjurnal_t";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";


}