<?php
namespace App\Traits\Dev;

Trait Designation
{
    protected $spesialWord = array(
        "penghasilan",
        "kualifikasi",
        "karbohidrat",
        "dokumentasi",
        "tanggungan",
        "struktural",
        "sertifikat",
        "registrasi",
        "preskripsi",
        "perkawinan",
        "pendidikan",
        "pembimbing",
        "departemen",
        "teknologi",
        "perolehan",
        "penunjang",
        "panggilan",
        "imunisasi",
        "bertambah",
        "terkecil",
        "terakhir",
        "struktur",
        "rekening",
        "produsen",
        "produksi",
        "kategory",
        "golongan",
        "external",
        "ekonomis",
        "berjalan",
        "ruangan",
        "riwayat",
        "rekanan",
        "protein",
        "profile",
        "periksa",
        "pegawai",
        "panjang",
        "pangkat",
        "panggil",
        "laporan",
        "kelamin",
        "kandung",
        "jurusan",
        "jabatan",
        "dokumen",
        "display",
        "current",
        "berlaku",
        "antrian",
        "account",
        "tujuan",
        "tiroid",
        "tempat",
        "status",
        "satuan",
        "report",
        "produk",
        "object",
        "normal",
        "negara",
        "lantai",
        "intern",
        "finger",
        "eleson",
        "detail",
        "bentuk",
        "akibat",
        "Effect",
        "total",
        "title",
        "tidur",
        "tidak",
        "tahun",
        "surat",
        "struk",
        "score",
        "ruang",
        "range",
        "print",
        "photo",
        "pajak",
        "nomor",
        "nilai",
        "nikah",
        "netto",
        "level",
        "lemak",
        "lebar",
        "kecil",
        "jenis",
        "hidup",
        "hasil",
        "harga",
        "dasar",
        "chart",
        "batas",
        "bahan",
        "akhir",
        "yang",
        "usia",
        "unit",
        "type",
        "suku",
        "nama",
        "last",
        "kode",
        "kena",
        "imun",
        "haid",
        "gizi",
        "diri",
        "diet",
        "bank",
        "awal",
        "anak",
        "tgl",
        "rec",
        "qty",
        "pns",
        "nik",
        "min",
        "map",
        "ibu",
        "add",
        "ada",
        "ya",
        "no",
        "kd",
        "id",
        "fk",
    );

    protected $typeDesignation;

    protected function getStatusDesignation($string, $symbol = "*")
    {
        $cekString = $clipedString = str_replace($symbol, '', $string);
        if ($cekString == "") {
            return true;
        } else {
            return false;
        }
    }

    protected function setDesignationWord($word)
    {
        if ($this->typeDesignation == "method") {
            $word = "_" . $word;
        } else {
            $word = ucfirst($word);
        }
        return $word;

    }

    protected function getDesignation($baseString, $type = 'field')
    {
        $baseString = str_replace(' ', '', $baseString);
        $baseString = str_replace('_', '', $baseString);
        $clipedString = $baseString;
        $indexedString = $baseString;
        $this->typeDesignation = $type;

        $arrayResult = [];
        $i = 0;
        foreach ($this->spesialWord as $word) {
            $checkInString = strrpos($clipedString, $word);
            if (($checkInString) !== FALSE) {
                $strlen = strlen($word);
                $getWord = substr($clipedString, $checkInString, $strlen);
                $clipedString = str_replace($getWord, '*', $clipedString);
                $indexedString = str_replace($getWord, '_' . $i . '_', $indexedString);
//                echo $word."<br>";
                if ((int)$checkInString != 0) {
                    $getWord = $this->setDesignationWord($getWord);
                }
                $arrayResult['_' . $i . '_'] = $getWord;
            }
            $i++;
            if ($this->getStatusDesignation($clipedString)) {
                break;
            }
        }

//        echo $clipedString;
//        die();
        $sisa = explode('*', $clipedString);
//        if($baseString=='kdbarcode'){
//            $m =  strrpos($baseString, 'kdd');
//            if($m!==FALSE){
//                echo 'dapat'.(int)$m.'sss';
//            }else{
//                echo 'tidak ada';
//            }
//            die();
//        }
        if (!$this->getStatusDesignation($clipedString)) {
            foreach ($sisa as $key => $katasisa) {
                if ($katasisa != '') {
                    $clipedString = str_replace($katasisa, '*', $clipedString);
                    $indexedString = str_replace($katasisa, '_' . $i . '_', $indexedString);
                    if ($key != 0) {
                        $katasisa = $this->setDesignationWord($katasisa);
                    }
                    $arrayResult['_' . $i . '_'] = $katasisa;
                }

                $i++;
            }
        }

        $result = $indexedString;
        foreach ($arrayResult as $key => $value) {
            $result = str_replace($key, $value, $result);
        }

        return $result;
    }
}
