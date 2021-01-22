@extends('template.katalog')
@section('content-katalog')
<style>
    .accordion-msg {
        background-color: #f5f5f585;
        border-left: 1px solid #f5f5f5;
    }

    .card {
        border-radius: 10px;
        -webkit-box-shadow: 0 1px 20px 0 rgba(69, 90, 100, .08);
        box-shadow: 5px 10px 20px 0 rgb(69 90 100 / 22%);
        border: none;
        margin-bottom: 30px;
    }

    .card .card-header h5 {
        font-size: 20px;
    }
</style>
<div class="page-wrapper">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <div class="d-inline">
                        <h4 style="font-size: 30px;">Katalog</h4>
                        <span>Web Service RSD Wisma Atlet Tower 5</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">

            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="row">
            <div class="col-sm-12">
                <!-- Basic usage card start -->
                <div class="card">
                    <div class="card-header" style="border-bottom: 1px solid rgba(204,204,204,0.35);">
                        <h5>Signature</h5>
                        <span>Pengambilan token untuk header Web Service</span>
                    </div>
                    <div class="card-block accordion-block">
                        <div class="accordion-panel">
                            <div class="accordion-heading" role="tab" id="heading12">
                                <h3 class="card-title accordion-title">
                                    <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse12" aria-expanded="false" aria-controls="collapse12">
                                        Get Signature Token
                                    </a>
                                </h3>
                            </div>
                            <div id="collapse12" class="panel-collapse in collapse" role="tabpanel" aria-labelledby="heading12">

                                <div class="accordion-content accordion-desc">
                                    <span style="font-size: 20px"><b> {Base URL}sign/generate-token</b></span>

                                    <p> Method :<b> POST </b> </p>
                                    <p> Format :<b> Json </b></p>

                                    <p> Content-Type:<b> application/json </b></p>
                                    <br>
                                    <div class="row">
                                        <div class="col-lg-12 col-xl-12">
                                            <ul class="nav nav-tabs  tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-toggle="tab" href="#tab_a" role="tab" aria-expanded="true">Request</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-toggle="tab" href="#tab_b" role="tab" aria-expanded="false">Response</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content tabs card-block">
                                                <div class="tab-pane active" id="tab_a" role="tabpanel" aria-expanded="true">
                                                    <h6 class="m-t-20 f-w-600">Request</h6>
                                                    <div class="row">
                                                        <div class="col-xl-12 col-md-12">

                                                            <div class="prism-show-language">
                                                                <div class="prism-show-language-label">Markup</div>
                                                            </div>
                                                            <pre class=" language-markup">
                                                             <span class="cp">
    {
        "namaUser" : "{user yag diberikan}",
        "kataSandi" :  "{katasandi yag diberikan}",
    }
                                                                </span>
                                                            </pre>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="tab_b" role="tabpanel" aria-expanded="false">
                                                    <h6 class="m-t-20 f-w-600">Response</h6>
                                                    <div class="prism-show-language">
                                                        <div class="prism-show-language-label">Markup</div>
                                                    </div>
                                                    <pre class=" language-markup">
                                                     <span class="cp">
    {
        "response": {
            "id": "{idUser}",
            "kdProfile": {kdProfile},
            "namaProfile":{namaProfile},
            "namaUser":{namaUser}
        },
        "token": {
            "X-AUTH-TOKEN": "{tokennya}"
        },
        "status": 201,
        "as": "er@epic"
    }
                                                        </span>
                                                    </pre>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <!-- Basic usage card start -->
                <div class="card">
                    <div class="card-header" style="border-bottom: 1px solid rgba(204,204,204,0.35);">
                        <h5>Pasien</h5>
                        <!--                    <div class="sub-title" style="margin-bottom: 0px">Electronic Medical Records</div>-->
                    </div>
                    <div class="card-block accordion-block">
                        <div id="accordion" role="tablist" aria-multiselectable="true">
                            <!--  save pasien-->
                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingOne">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                            Insert Pasien
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapseOne" class="panel-collapse in collapse" role="tabpanel" aria-labelledby="headingOne" style="">

                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}/service/medifirst2000/registrasi/save-pasien-fix</b></span>
                                        <p> Fungsi : Insert Pasien & Alamat </p>
                                        <p> Method :<b> POST </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>
                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_1" role="tab" aria-expanded="true">Request</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-toggle="tab" href="#tab_2" role="tab" aria-expanded="false">Response</a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content tabs card-block">
                                                    <div class="tab-pane active" id="tab_1" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Request</h6>
                                                        <div class="row">
                                                            <div class="col-xl-5 col-md-12">

                                                                <div class="prism-show-language">
                                                                    <div class="prism-show-language-label">Markup</div>
                                                                </div>
                                                                <pre class=" language-markup">
                                                             <span class="cp">
         {
            "isbayi": false,
                "isPenunjang": false,
                "idpasien": "",
                "pasien": {
                    "namaPasien": "EFAN ANDRIAN",
                    "noIdentitas": "3213030509940006",
                    "namaSuamiIstri": null,
                    "noAsuransiLain": null,
                    "noBpjs": null,
                    "noHp": "081322389499",
                    "tempatLahir": "SUBANG",
                    "namaKeluarga": null,
                    "tglLahir": "1994-09-05 00:00",
                    "image": null
                },
                "agama": {
                    "id": 1
                },
                "jenisKelamin": {
                    "id": 1
                },
                "pekerjaan": {
                    "id": 4
                },
                "pendidikan": {
                    "id": 7
                },
                "statusPerkawinan": {
                    "id": 2
                },
                "golonganDarah": {
                    "id": 2
                },
                "suku": {
                    "id": 2
                },
                "namaIbu": null,
                "noTelepon": null,
                "noAditional": null,
                "kebangsaan": {
                    "id": 1
                },
                "negara": {
                    "id": 0
                },
                "namaAyah": null,
                "alamatLengkap": "JLN. CIHANJUANG GG.BAGJA 3 NO.88 RT/RW:003/011",
                "desaKelurahan": {
                    "id": 40301,
                    "namaDesaKelurahan": "CIBABAT"
                },
                "kecamatan": {
                    "id": 2831,
                    "namaKecamatan": "Cimahi Utara"
                },
                "kotaKabupaten": {
                    "id": 185,
                    "namaKotaKabupaten": "Kota Cimahi"
                },
                "propinsi": {
                    "id": 12
                },
                "kodePos": "40513",
                "jenisalamat": 3,
                "alamatLengkaptd": "JLN. CIHANJUANG GG.BAGJA 3 NO.88 RT/RW:003/011",
                "desaKelurahantd": {
                    "id": 40301,
                    "namaDesaKelurahan": "CIBABAT"
                },
                "kecamatantd": {
                    "id": 2831,
                    "namaKecamatan": "Cimahi Utara"
                },
                "kotaKabupatentd": {
                    "id": 185,
                    "namaKotaKabupaten": "Kota Cimahi"
                },
                "propinsitd": {
                    "id": 12
                },
                "kodePostd": "40513",
                "jenisalamattd": 4,
                "penanggungjawab": null,
                "hubungankeluargapj": null,
                "pekerjaanpenangggungjawab": null,
                "ktppenanggungjawab": null,
                "alamatrmh": null,
                "alamatktr": null,
                "teleponpenanggungjawab": null,
                "bahasa": null,
                "jeniskelaminpenanggungjawab": null,
                "umurpenanggungjawab": null,
                "dokterpengirim": null,
                "alamatdokter": null,
                "isAlamatSama": true
            }
                                                                </span>
                                                            </pre>
                                                            </div>
                                                            <div class="col-xl-7 col-md-12">

                                                                <div class="prism-show-language">
                                                                    <div class="prism-show-language-label">Markup</div>
                                                                </div>
                                                                <pre class=" language-markup">
                                                             <span class="cp">
            {
                "isbayi": false,
                "isPenunjang": false,
                "idpasien": "",
                "pasien": {
                    "namaPasien": "{Nama Pasien}" ->wajib diisi,
                    "noIdentitas": "{NIK}",
                    "namaSuamiIstri": {Nama Suami/Istri},
                    "noAsuransiLain": {No Asuransi Selain Bpjs},
                    "noBpjs": {No Asuransi Bpjs},
                    "noHp": {No Hp} ->wajib diisi,
                    "tempatLahir": {tempat lahir} ->wajib diisi,
                    "namaKeluarga": {nama keluarga} ,
                    "tglLahir": {tanggal lahir} ->wajib diisi,,
                    "image": null
                },
                "agama": {
                    { agama -> baca referensi no.3}
                },
                "jenisKelamin": {
                    { jeniskelamin -> baca referensi no.3} ->wajib diisi
                },
                "pekerjaan": {
                    { pekerjaan -> baca referensi no.3}
                },
                "pendidikan": {
                    { pendidikan -> baca referensi no.3}
                },
                "statusPerkawinan": {
                    { status perkawinan -> baca referensi no.3}
                },
                "golonganDarah": {
                    { golongan darah -> baca referensi no.3}
                },
                "suku": {
                    { suku -> baca referensi }
                },
                "namaIbu": {nama ibu},
                "noTelepon": {no telepon},
                "noAditional": {no aditional},
                "kebangsaan": {
                    { kebangsaan -> baca referensi no.2}
                },
                "negara": {
                    { negara -> baca referensi no.2}
                },
                "namaAyah": {nama ayah},
                "alamatLengkap": {alamatLengkap} -> wajib diisi,
                "desaKelurahan": {
                    { desa kelurahan -> baca referensi no.2}
                },
                "kecamatan": {
                    { kecamatan -> baca referensi no.2}
                },
                "kotaKabupaten": {
                    { kota kabupaten -> baca referensi no.2}
                },
                "propinsi": {
                    { propinsi -> baca referensi }
                },
                "kodePos": { kodepos -> baca referensi no.2},
                "jenisalamat": 3,
                "alamatLengkaptd": {alamatLengkap domisili},
                "desaKelurahantd": {
                    {desa kelurahan domisili no.2}
                },
                "kecamatantd": {
                    {kecamatan domisili no.2}
                },
                "kotaKabupatentd": {
                    {kabupaten domisili no.2}
                },
                "propinsitd": {
                    {propinsi domisili no.2}
                },
                "kodePostd": {kodepos domisili no.2},
                "jenisalamattd": 4,
                "penanggungjawab": {penanggung jawab pasien},
                "hubungankeluargapj": {hubungan keluarga pasien},
                "pekerjaanpenangggungjawab": {pekerjaan penanggung jawab },
                "ktppenanggungjawab": {ktp penanggung jawab },
                "alamatrmh": {alamat rumah penanggung jawab },
                "alamatktr": {ktp penanggung jawab pasien},
                "teleponpenanggungjawab": {telepon penanggung jawab pasien},
                "bahasa": {bahasa penanggung jawab pasien},
                "jeniskelaminpenanggungjawab": {jeniskelamin penanggung jawab pasien},
                "umurpenanggungjawab": {umur penanggung jawab pasien},
                "dokterpengirim": {dokter pengirim},
                "alamatdokter": {alamat dokter},
                "isAlamatSama": true
            }
                                                                </span>
                                                            </pre>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" id="tab_2" role="tabpanel" aria-expanded="false">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
                                                     <span class="cp">
            {
                "status": 201,
                "as": "ramdanegie",
                "messages": "Simpan Berhasil"
			}
                                                        </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>



                                </div>
                            </div>

                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingTwo">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            Registrasi Pasien
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/registrasi/save-registrasipasien </b></span>
                                        <p> Fungsi : Insert Registrasi Ke Ruangan </p>
                                        <p> Method :<b> POST </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_3" role="tab" aria-expanded="true">Request</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-toggle="tab" href="#tab_4" role="tab" aria-expanded="false">Response</a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content tabs card-block">
                                                    <div class="tab-pane active" id="tab_3" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Request</h6>
                                                        <div class="row">
                                                            <div class="col-xl-5 col-md-12">
                                                                <div class="prism-show-language">
                                                                    <div class="prism-show-language-label">Markup</div>
                                                                </div>
                                                                <pre class=" language-markup">
                                                     <span class="cp">
    {
        "pasiendaftar": {
            "tglregistrasi": "2020-09-17 21:51:29",
            "tglregistrasidate": "2020-09-17",
            "nocmfk": 56477,
            "objectruanganfk": 659,
            "objectdepartemenfk": 16,
            "objectkelasfk": 6,
            "objectkelompokpasienlastfk": 16,
            "objectrekananfk": null,
            "tipelayanan": "1",
            "objectpegawaifk": null,
            "noregistrasi": "",
            "norec_pd": "",
            "israwatinap": "true",
            "statusschedule": "",
            "statuspasien": "LAMA",
            "statuscovid": "TERKONFIRMASI - ASIMTOMATIK",
            "statuscovidfk": 6
        },
        "antrianpasiendiperiksa": {
            "norec_apd": "",
            "tglregistrasi": "2020-09-17 21:51:29",
            "objectruanganfk": 659,
            "objectkelasfk": 6,
            "objectpegawaifk": null,
            "objectkamarfk": 421,
            "nobed": null,
            "objectdepartemenfk": 16,
            "objectasalrujukanfk": 2,
            "israwatgabung": 0
        }
    }
                                                        </span>
                                                    </pre>
                                                            </div>
                                                            <div class="col-xl-7 col-md-12">
                                                                <div class="prism-show-language">
                                                                    <div class="prism-show-language-label">Markup</div>
                                                                </div>
                                                                <pre class=" language-markup">
    {
        "pasiendaftar": {
            "tglregistrasi": {Tgl Registrasi (YYYY-MM-dd HH:mm:ss)},
            "tglregistrasidate": {Tgl Registrasi (YYYY-MM-dd )},
            "nocmfk": {id pasien},
            "objectruanganfk": {id Ruangan daftar},
            "objectdepartemenfk": {id Departemen Daftar},
            "objectkelasfk": {id Kelas Dirawat},
            "objectkelompokpasienlastfk": {id Tipe Pasien},
            "objectrekananfk": {id rekanan penjamin pasien},
            "tipelayanan": "1",
            "objectpegawaifk": {id dokter penanggung Jawab},
            "noregistrasi": "",
            "norec_pd": "",
            "israwatinap": "true",
            "statusschedule": "",
            "statuspasien": "LAMA",
            "statuscovid": "{status covid},
            "statuscovidfk": {id status covid}
        },
        "antrianpasiendiperiksa": {
            "norec_apd": "",
            "tglregistrasi": {Tgl Registrasi (YYYY-MM-dd HH:mm:ss)},
            "objectruanganfk": {id Ruangan daftar},,
            "objectkelasfk": {id Kelas Dirawat},
            "objectpegawaifk": null,
            "objectkamarfk": {id Kamar Dirawat},
            "nobed": null,
            "objectdepartemenfk": {id Departemen},,
            "objectasalrujukanfk": {id Asal Rujukan},,
            "israwatgabung": 0
        }
    }
                                                        </span>
                                                    </pre>
                                                            </div>
                                                        </div>


                                                    </div>
                                                    <div class="tab-pane" id="tab_4" role="tabpanel" aria-expanded="false">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
                                                     <span class="cp">
    {
    "status": 201,
    "as": "ramdanegie",
    "messages": "Simpan Berhasil"
    }
                                                        </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="heading8">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse8" aria-expanded="false" aria-controls="collapse8">
                                            Referensi Address (Insert Pasien)
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapse8" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading8" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/registrasi/get-combo-address</b></span>
                                        <p> Fungsi : Referensi Master data saat mau Inset Pasien Baru </p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_3" role="tab" aria-expanded="true">Response</a>
                                                    </li>

                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_3" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
                                                     <span class="cp">
        {
            "kebangsaan": [
                {
                    "id": 1,
                    "name": "WNI"
                },
                {
                    "id": 2,
                    "name": "WNA"
                }
            ],
            "negara": [
                {
                    "id": 0,
                    "namanegara": "INDONESIA"
                }
            ],
            "propinsi": [
                {
                    "id": 1,
                    "namapropinsi": "ACEH"
                },
                {
                    "id": 17,
                    "namapropinsi": "BALI"
                }
            ],
            "kotakabupaten": [
                {
                    "id": 5,
                    "namakotakabupaten": "KAB. ACEH BARAT"
                },
                {
                    "id": 12,
                    "namakotakabupaten": "KAB. ACEH BARAT DAYA"
                }
            ],
            "kecamatan": [
                {
                    "id": 1410,
                    "namakecamatan": "ABAB"
                },
                {
                    "id": 1567,
                    "namakecamatan": "ABAB"
                }
             ]
      }


                                            </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headinga">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapsea" aria-expanded="false" aria-controls="collapsea">
                                            Referensi Master Data (Insert Pasien)
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapsea" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headinga" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/registrasi/get-combo-registrasi</b></span>
                                        <p> Fungsi : Referensi Master data saat mau Inset Pasien Baru </p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">

                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_d" role="tab" aria-expanded="true">Response</a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_d" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
                                                     <span class="cp">
                                                     {
    "jeniskelamin": [
        {
            "id": 0,
            "jeniskelamin": "-"
        },
        {
            "id": 1,
            "jeniskelamin": "LAKI-LAKI"
        },
        {
            "id": 2,
            "jeniskelamin": "PEREMPUAN"
        }
    ],
    "agama": [
        {
            "id": 0,
            "agama": "-"
        },
        {
            "id": 1,
            "agama": "ISLAM"
        }

    ],
    "statusperkawinan": [
        {
            "id": 0,
            "statusperkawinan": "-",
            "namadukcapil": "-"
        },
        {
            "id": 2,
            "statusperkawinan": "KAWIN",
            "namadukcapil": "KAWIN"
        },

        {
            "id": 1,
            "statusperkawinan": "BELUM KAWIN",
            "namadukcapil": "BELUM KAWIN"
        }
    ],
    "pendidikan": [

        {
            "id": 8,
            "pendidikan": "DIPLOMA IV",
            "namadukcapil": "DIPLOMA IV"
        },
        {
            "id": 9,
            "pendidikan": "S1",
            "namadukcapil": "DIPLOMA IV/STRATA I"
        },
        {
            "id": 10,
            "pendidikan": "S2",
            "namadukcapil": "STRATA II"
        },
        {
            "id": 11,
            "pendidikan": "S3",
            "namadukcapil": "STRATA III"
        }
    ],
    "pekerjaan": [
        {
            "id": 0,
            "pekerjaan": "-",
            "namadukcapil": "-"
        },
        {
            "id": 1,
            "pekerjaan": "TIDAK BEKERJA",
            "namadukcapil": "BELUM/TIDAK BEKERJA"
        },
        {
            "id": 2,
            "pekerjaan": "MENGURUS RUMAH TANGGA",
            "namadukcapil": "MENGURUS RUMAH TANGGA"
        },
        {
            "id": 3,
            "pekerjaan": "PELAJAR/ MAHASISWA",
            "namadukcapil": "PELAJAR/MAHASISWA"
        }
    ],
    "pegawaiLogin": "-",
    "golongandarah": [
        {
            "id": 0,
            "golongandarah": "-",
            "namadukcapil": "-"
        },
        {
            "id": 1,
            "golongandarah": "A",
            "namadukcapil": "A"
        },
        {
            "id": 2,
            "golongandarah": "B",
            "namadukcapil": "B"
        },
        {
            "id": 3,
            "golongandarah": "O",
            "namadukcapil": "0"
        },
        {
            "id": 4,
            "golongandarah": "AB",
            "namadukcapil": "AB"
        },
        {
            "id": 5,
            "golongandarah": "A-",
            "namadukcapil": "A-"
        }
    ],
    "suku": [
        {
            "id": 0,
            "suku": "-"
        },
        {
            "id": 1,
            "suku": "JAWA"
        },
        {
            "id": 2,
            "suku": "SUNDA"
        },
        {
            "id": 3,
            "suku": "MADURA"
        }
    ],
    "message": "inhuman"
}


                                                     </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headinga">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapsea" aria-expanded="false" aria-controls="collapsea">
                                            Referensi Get Kelurahan
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapsea" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headinga" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/registrasi/get-desa-kelurahan-paging?filter[filters][0][value]=mugarsari</b></span>
                                        <p> Fungsi : Referensi Desa Kelurahan Paging 10  </p>
                                        <p> params : filter[filters][0][value] --> buat searching kelurahan </p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">

                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_d" role="tab" aria-expanded="true">Response</a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_d" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
                                                     <span class="cp">

    [
        {
            "id": 40358,
            "namadesakelurahan": "MUGARSARI",
            "kodepos": "46196",
            "namakecamatan": "Tamansari",
            "namakotakabupaten": "Kota Tasikmalaya",
            "namapropinsi": "Jawa Barat",
            "desa": "MUGARSARI, Tamansari,  Kota Tasikmalaya, Jawa Barat",
            "objectkecamatanfk": 2838,
            "objectkotakabupatenfk": 186,
            "objectpropinsifk": 12
        }
    ]
                                                        </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>


                                </div>
                            </div>
                          
                        </div>


                    </div>
                </div>

            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header" style="border-bottom: 1px solid rgba(204,204,204,0.35);">
                        <h5>Penerimaan Barang Supplier</h5>
                    </div>
                    <div class="card-block accordion-block">
                        <div class="accordion-panel">
                            <div class="accordion-heading" role="tab" id="heading19">
                                <h3 class="card-title accordion-title">
                                    <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse19" aria-expanded="false" aria-controls="collapse19">
                                        Insert Penerimaan
                                    </a>
                                </h3>
                            </div>
                            <div id="collapse19" class="panel-collapse in collapse" role="tabpanel" aria-labelledby="heading19" style="">

                                <div class="accordion-content accordion-desc">
                                    <span style="font-size: 20px"><b> {Base URL}medifirst2000/logistik/save-data-penerimaan</b></span>
                                    <p> Fungsi : Insert Penerimaan Barang Supplier </p>
                                    <p> Method :<b> POST </b> </p>
                                    <p> Format :<b> Json </b></p>
                                    <p> Header :<b> </b></p>
                                    <p> Content-Type:<b> application/json </b></p>
                                    <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>
                                    <br>
                                    <div class="row">
                                        <div class="col-lg-12 col-xl-12">
                                            <ul class="nav nav-tabs  tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-toggle="tab" href="#tab_14" role="tab" aria-expanded="true">Request</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-toggle="tab" href="#tab_15" role="tab" aria-expanded="false">Response</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content tabs card-block">
                                                <div class="tab-pane active" id="tab_14" role="tabpanel" aria-expanded="true">
                                                    <h6 class="m-t-20 f-w-600">Request</h6>
                                                    <div class="row">
                                                        <div class="col-xl-5 col-md-12">

                                                            <div class="prism-show-language">
                                                                <div class="prism-show-language-label">Markup</div>
                                                            </div>
                                                            <pre class=" language-markup">
                                                             <span class="cp">
    {
            "struk": {
            "nostruk": "",
            "noorder": "Tes-00001",
            "rekananfk": 18585370,
            "namarekanan": "PARIT PADANG GLOBAL, PT",
            "ruanganfk": 657,
            "nokontrak": "Tes-00001",
            "nofaktur": "PB/09-20/APT/00001",
            "tglfaktur": "2020-09-17T15:23:48.770Z",
            "tglstruk": "2020-09-17T15:23:48.770Z",
            "tglorder": "2020-09-17 22:23",
            "tglrealisasi": "2020-09-17T15:23:48.770Z",
            "tglkontrak": "2020-09-17 22:23",
            "objectpegawaipenanggungjawabfk": 24377,
            "pegawaimenerimafk": 320261028,
            "namapegawaipenerima": "-",
            "qtyproduk": 2,
            "totalharusdibayar": 29000,
            "totalppn": 0,
            "totaldiscount": 0,
            "totalhargasatuan": 29000,
            "asalproduk": 1,
            "ruanganfkKK": 657,
            "tglKK": "2020-09-17T15:23:48.770Z",
            "pegawaifkKK": null,
            "norecsppb": "",
            "kelompoktranskasi": 35,
            "norecrealisasi": "",
            "nousulan": "Tes-00001",
            "objectmataanggaranfk": "",
            "noterima": "RS/2009/00001",
            "noBuktiKK": "",
            "ketterima": "-",
            "jenisusulan": "Medis",
            "jenisusulanfk": 1,
            "namapengadaan": "Obat Alkes",
            "norecOrder": null,
            "tgljatuhtempo": "2020-09-17T15:23:48.770Z"
            },
            "details": [
            {
                "no": 1,
                "hargasatuan": "1000",
                "ruanganfk": 657,
                "asalprodukfk": 1,
                "asalproduk": "Badan Layanan Umum",
                "produkfk": 28266,
                "namaproduk": "Ambroxol HCl 30 mg Tablet",
                "nilaikonversi": 1,
                "satuanstandarfk": 335,
                "satuanstandar": "TABLET",
                "satuanviewfk": 335,
                "satuanview": "TABLET",
                "jumlah": "20",
                "hargadiscount": "0",
                "persendiscount": "0",
                "ppn": "0",
                "persenppn": "10",
                "total": 20000,
                "keterangan": null,
                "nobatch": "-",
                "tglkadaluarsa": "2020-09-17T15:28:59.092Z"
            },
            {
                "no": 2,
                "hargasatuan": "1500",
                "ruanganfk": 657,
                "asalprodukfk": 1,
                "asalproduk": "Badan Layanan Umum",
                "produkfk": 28181,
                "namaproduk": "Ekacetol (Paracetamol) 120 mg/5 mL Syrup 60mL",
                "nilaikonversi": 1,
                "satuanstandarfk": 339,
                "satuanstandar": "BOTOL",
                "satuanviewfk": 339,
                "satuanview": "BOTOL",
                "jumlah": "6",
                "hargadiscount": "0",
                "persendiscount": "0",
                "ppn": "0",
                "persenppn": "0",
                "total": 9000,
                "keterangan": null,
                "nobatch": "-",
                "tglkadaluarsa": "2020-09-17T15:29:36.524Z"
            }
            ]
        }
                                                                 </span>
                                                            </pre>
                                                        </div>
                                                        <div class="col-xl-7 col-md-12">

                                                            <div class="prism-show-language">
                                                                <div class="prism-show-language-label">Markup</div>
                                                            </div>
                                                            <pre class=" language-markup">
    {
        "struk": {
        "nostruk": "",
        "noorder": {nomo order},
        "rekananfk": {id rekanan },
        "namarekanan": {nama rekanan },
        "ruanganfk": {id ruangan },
        "nokontrak": {no kontrak},
        "nofaktur": {no faktur },
        "tglfaktur": {Tgl tglfaktur (YYYY-MM-dd HH:mm:ss)},
        "tglstruk": {Tgl tglstruk (YYYY-MM-dd HH:mm:ss)},
        "tglorder": {Tgl tglorder (YYYY-MM-dd HH:mm:ss)},
        "tglrealisasi": {Tgl tglrealisasi (YYYY-MM-dd HH:mm:ss)},
        "tglkontrak": {Tgl tglrealisasi (YYYY-MM-dd HH:mm:ss)},
        "objectpegawaipenanggungjawabfk": {id pegawai},
        "pegawaimenerimafk": {id pegawai},
        "namapegawaipenerima": "-",
        "qtyproduk": {jml produk},
        "totalharusdibayar": {totalharusdibayar},
        "totalppn": 0,
        "totaldiscount": 0,
        "totalhargasatuan": {totalhargasatuan},
        "asalproduk": 1,
        "ruanganfkKK": {id ruangan},
        "tglKK": {tglKK (YYYY-MM-dd HH:mm:ss)},
        "pegawaifkKK": null,
        "norecsppb": "",
        "kelompoktranskasi": 35,
        "norecrealisasi": "",
        "nousulan": {nousulan}
        "objectmataanggaranfk": "",
        "noterima": {noterima},
        "noBuktiKK": "",
        "ketterima": "-",
        "jenisusulan": {jenisusulan},
        "jenisusulanfk": 1,
        "namapengadaan": {namapengadaan},
        "norecOrder": null,
        "tgljatuhtempo": {tgljatuhtempo (YYYY-MM-dd HH:mm:ss)}
        },
        "details": [
        {
            "no": 1,
            "hargasatuan": {hargasatuan},
            "ruanganfk": {ruanganfk},
            "asalprodukfk": {asalprodukfk},
            "asalproduk": {asalproduk},
            "produkfk": {produkfk},
            "namaproduk": {namaproduk},
            "nilaikonversi": 1,
            "satuanstandarfk": {satuanstandarfk},
            "satuanstandar": {satuanstandar},
            "satuanviewfk": {satuanviewfk},
            "satuanview": {satuanview},
            "jumlah": {jumlah},
            "hargadiscount": {hargadiscount},
            "persendiscount": {persendiscount},
            "ppn": {ppn},
            "persenppn": {persenppn},
            "total": {total},
            "keterangan": {keterangan},
            "nobatch": {nobatch},
            "tglkadaluarsa": {tglkadaluarsa}
        },
        {
            "no": 2,
            "hargasatuan": {hargasatuan},
            "ruanganfk": {ruanganfk},
            "asalprodukfk": {asalprodukfk},
            "asalproduk": {asalproduk},
            "produkfk": {produkfk},
            "namaproduk": {namaproduk},
            "nilaikonversi": 1,
            "satuanstandarfk": {satuanstandarfk},
            "satuanstandar": {satuanstandar},
            "satuanviewfk": {satuanviewfk},
            "satuanview": {satuanview},
            "jumlah": {jumlah},
            "hargadiscount": {hargadiscount},
            "persendiscount": {persendiscount},
            "ppn": {ppn},
            "persenppn": {persenppn},
            "total": {total},
            "keterangan": {keterangan},
            "nobatch": {nobatch},
            "tglkadaluarsa": {tglkadaluarsa}
        }
        ]
    }
                                                                 </span>
                                                            </pre>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="tab_15" role="tabpanel" aria-expanded="false">
                                                    <h6 class="m-t-20 f-w-600">Response</h6>
                                                    <div class="prism-show-language">
                                                        <div class="prism-show-language-label">Markup</div>
                                                    </div>
                                                    <pre class=" language-markup">
                                                     <span class="cp">
    {
        "status": 201,
        "as": "as@epic",
        "messages": "Simpan Berhasil"
    }
                                                        </span>
                                                    </pre>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header" style="border-bottom: 1px solid rgba(204,204,204,0.35);">
                        <h5>Reservasi Online</h5>
                    </div>
                    <div class="card-block accordion-block">
                        <div class="accordion-panel">
                            <div class="accordion-heading" role="tab" id="headingd">
                                <h3 class="card-title accordion-title">
                                    <a class="accordion-msg scale_active collapsed" data-toggle="collapse"
                                     data-parent="#accordion" href="#collapsed" aria-expanded="false" aria-controls="collapsed">
                                        Insert Reservasi
                                    </a>
                                </h3>
                            </div>
                            <div id="collapsed" class="panel-collapse in collapse" role="tabpanel" aria-labelledby="headingd" style="">

                                <div class="accordion-content accordion-desc">
                                    <span style="font-size: 20px"><b> {Base URL}medifirst2000/reservasionline/save</b></span>
                                    <p> Fungsi : Insert Reservasi Pasien Baru </p>
                                    <p> Method :<b> POST </b> </p>
                                    <p> Format :<b> Json </b></p>
                                    <p> Header :<b> </b></p>
                                    <p> Content-Type:<b> application/json </b></p>
                                    <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>
                                    <br>
                                    <div class="row">
                                        <div class="col-lg-12 col-xl-12">
                                            <ul class="nav nav-tabs  tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-toggle="tab" 
                                                    href="#tab_d" role="tab" aria-expanded="true">Request</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-toggle="tab"
                                                     href="#tab_e" role="tab" aria-expanded="false">Response</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content tabs card-block">
                                                <div class="tab-pane active" id="tab_d" role="tabpanel" aria-expanded="true">
                                                    <h6 class="m-t-20 f-w-600">Request</h6>
                                                    <div class="row">
                                                        <div class="col-xl-5 col-md-12">

                                                            <div class="prism-show-language">
                                                                <div class="prism-show-language-label">Markup</div>
                                                            </div>
                                                            <pre class=" language-markup">
                                                             <span class="cp">
{
  "namaPasien": "RAMDAN",
  "jenisKelamin": {
    "id": 1,
    "jeniskelamin": "LAKI-LAKI"
  },
  "tglLahir": "1995-03-01",
  "noTelpon": "-",
  "noCm": null,
  "tglLahirLama": null,
  "dokter": null,
  "poliKlinik": null,
  "tipePembayaran": {
    "id": 20,
    "kelompokpasien": "Keluarga PNS"
  },
  "noKartuPeserta": null,
  "jamReservasi": null,
  "noRujukan": null,
  "tglReservasiFix": "2020-09-27",
  "isBaru": true,
  "tglAwal": null,
  "tglAkhir": null,
  "nik": "3207070103940001",
  "alamat": "Badung",
  "provinsi": {
    "id": 17,
    "namapropinsi": "Bali"
  },
  "kabKota": {
    "id": 276,
    "namakotakabupaten": "Kab. Badung"
  },
  "kecamatan": {
    "id": 4345,
    "namakecamatan": "Kuta"
  },
  "namaPasienDukcapil": null,
  "asalRujukan": {
    "id": 5,
    "asalrujukan": "Datang Sendiri"
  },
  "reaktif": null,
  "nonReaktif": null,
  "hasil": "Negatif",
  "swab": null,
  "rapid": null,
  "tglPemeriksaan": "2020-09-17",
  "jenisPemeriksaan": "swab",
  "dataPendukung": null,
  "hasilRapid": null,
  "desa": {
    "id": 60399,
    "namadesakelurahan": "Seminyak"
  },
  "tempatLahir": "Badung"
}
                                                                 </span>
                                                            </pre>
                                                        </div>
                                                        <div class="col-xl-7 col-md-12">

                                                            <div class="prism-show-language">
                                                                <div class="prism-show-language-label">Markup</div>
                                                            </div>
                                                            <pre class=" language-markup">
{
  "namaPasien": "RAMDAN",
  "jenisKelamin": {
    "id": {id jenis kelamin diambil dari referensi reservasi},
    "jeniskelamin": "LAKI-LAKI"
  },
  "tglLahir": "1995-03-01",
  "noTelpon": "-",
  "noCm": null,
  "tglLahirLama": null,
  "dokter": null,
  "poliKlinik": null,
  "tipePembayaran": {
    "id": {id jenis kelamin diambil dari referensi kelompok pasien},
    "kelompokpasien": "Keluarga PNS"
  },
  "noKartuPeserta": null,
  "jamReservasi": null,
  "noRujukan": null,
  "tglReservasiFix": "2020-09-27",
  "isBaru": {pasien baru : true , lama false},
  "tglAwal": null,
  "tglAkhir": null,
  "nik": "3207070103940001",
  "alamat": "Badung",
  "provinsi": {
    "id": {id jenis kelamin diambil dari referensi provinsi},
    "namapropinsi": "Bali"
  },
  "kabKota": {
    "id": {id jenis kelamin diambil dari referensi kota},
    "namakotakabupaten": "Kab. Badung"
  },
  "kecamatan": {
    "id": {id jenis kelamin diambil dari referensi kecamatan},
    "namakecamatan": "Kuta"
  },
  "namaPasienDukcapil": null,
  "asalRujukan": {
    "id": {id jenis kelamin diambil dari referensi asal rujukan},
    "asalrujukan": "Datang Sendiri"
  },
  "reaktif": null,
  "nonReaktif": null,
  "hasil": "Negatif",
  "swab": null,
  "rapid": null,
  "tglPemeriksaan": "2020-09-17",
  "jenisPemeriksaan": "swab",
  "dataPendukung": null,
  "hasilRapid": null,
  "desa": {
    "id": {id jenis kelamin diambil dari referensi desa},
    "namadesakelurahan": "Seminyak"
  },
  "tempatLahir": "Badung"
}
                                                                 </span>
                                                            </pre>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="tab_e" role="tabpanel" aria-expanded="false">
                                                    <h6 class="m-t-20 f-w-600">Response</h6>
                                                    <div class="prism-show-language">
                                                        <div class="prism-show-language-label">Markup</div>
                                                    </div>
                                                    <pre class=" language-markup">
                                                     <span class="cp">
    {
        "status": 201,
        "as": "as@epic",
        "messages": "Simpan Berhasil"
    }
                                                        </span>
                                                    </pre>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingf">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" 
                                        href="#collapsef" aria-expanded="false" aria-controls="collapsef">
                                         Get History Reservasi
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapsef" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingf" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/reservasionline/get-history?nocmNama=RAMDAN&tgllahir=01-03-1995</b></span>
                                        <p> Fungsi : Mengambil history reservasi </p>
                                        <p> param  : nocmNama -> no rm atau nama pasien </p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_f" role="tab" aria-expanded="true">Response</a>
                                                    </li>

                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_f" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
                                                     <span class="cp">
 {
    "data": [
        {
            "norec": "e2adf9b0-f8ce-11ea-a027-dbdd11db",
            "nocm": null,
            "noreservasi": "e2adf49",
            "tanggalreservasi": "2020-09-27 00:00:00",
            "objectruanganfk": null,
            "objectpegawaifk": null,
            "namaruangan": null,
            "isconfirm": null,
            "dokter": null,
            "nocmfk": null,
            "namapasien": "RAMDAN",
            "alamatlengkap": null,
            "pekerjaan": null,
            "noasuransilain": null,
            "noidentitas": null,
            "nobpjs": null,
            "nohp": null,
            "pendidikan": null,
            "type": "BARU",
            "kelompokpasien": "Keluarga PNS",
            "objectkelompokpasienfk": 20,
            "objectdepartemenfk": null,
            "asalrujukan": "Datang Sendiri",
            "jenispemeriksaan": "swab",
            "tglpemeriksaan": "2020-09-17 00:00:00",
            "alamat": "Badung",
            "hasilpemeriksaan": "Negatif",
            "datapendukung": null,
            "prefixnoantrian": null,
            "norujukan": null,
            "status": "Reservasi",
            "tempatlahir": "Badung",
            "jeniskelamin": "LAKI-LAKI",
            "tgllahir": "1995-03-01 00:00:00",
            "notelepon": "-"
        }
    ],
    "as": "ramdan@epic"
}


                                            </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                           <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingg">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" 
                                        href="#collapseg" aria-expanded="false" aria-controls="collapseg">
                                         Get List Data
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapseg" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingg" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/reservasionline/get-list-data</b></span>
                                        <p> Fungsi : Referensi Master data reservasi online </p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_g" role="tab" aria-expanded="true">Response</a>
                                                    </li>

                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_g" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
 {
    "jeniskelamin": [],
    "kelompokpasien": [],
    "asalrujukan": []
 }
                                            </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingh">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" 
                                        href="#collapseh" aria-expanded="false" aria-controls="collapseh">
                                        Get Master Provinsi
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapseh" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingh" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/reservasionline/get-provinsi</b></span>
                                        <p> Fungsi : Referensi Master data Provinsi </p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_h" role="tab" aria-expanded="true">Response</a>
                                                    </li>

                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_h" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
[
    {
        "id": 1,
        "namapropinsi": "Aceh"
    }
 ]
                                            </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingi">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" 
                                        href="#collapsei" aria-expanded="false" aria-controls="collapsei">
                                        Get Master Kota Kabupaten
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapsei" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingi" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/reservasionline/get-kota?idprov=12</b></span>
                                        <p> Fungsi : Referensi Master data kota by Id Prov </p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_i" role="tab" aria-expanded="true">Response</a>
                                                    </li>

                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_i" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
[
    {
        "id": 1,
        "namakotakabupaten": "-"
    }
 ]
                                            </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingj">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" 
                                        href="#collapsej" aria-expanded="false" aria-controls="collapsej">
                                        Get Master Kecamatan
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapsej" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingj" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/reservasionline/get-kecamatan?idkot=186&idprop=12</b></span>
                                        <p> Fungsi : Referensi Master data Kecamatan  by Id Prov & Id Kota </p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_j" role="tab" aria-expanded="true">Response</a>
                                                    </li>

                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_j" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
[
    {
        "id": 1,
        "namakecamatan": "-"
    }
 ]
                                            </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingk">
                                    <h3 class="card-title accordion-title">
                                        <a class="accordion-msg scale_active collapsed" data-toggle="collapse" data-parent="#accordion" 
                                        href="#collapsek" aria-expanded="false" aria-controls="collapsek">
                                        Get Master Desa Kelurahan
                                        </a>
                                    </h3>
                                </div>
                                <div id="collapsek" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingk" style="">
                                    <div class="accordion-content accordion-desc">
                                        <span style="font-size: 20px"><b> {Base URL}medifirst2000/reservasionline/get-desa?idkec=2833&idkot=186&idprop=12</b></span>
                                        <p> Fungsi : Referensi Master data Desa  by Id Prov & Id Kota  & Id Kecamatan</p>
                                        <p> Method :<b> GET </b> </p>
                                        <p> Format :<b> Json </b></p>
                                        <p> Header :<b> </b></p>
                                        <p> Content-Type:<b> application/json </b></p>
                                        <p> X-AUTH-TOKEN:<b> {Get Signature/Token} </b></p>

                                        <br>
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <ul class="nav nav-tabs  tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#tab_k" role="tab" aria-expanded="true">Response</a>
                                                    </li>

                                                </ul>
                                                <div class="tab-content tabs card-block">

                                                    <div class="tab-pane active" id="tab_k" role="tabpanel" aria-expanded="true">
                                                        <h6 class="m-t-20 f-w-600">Response</h6>
                                                        <div class="prism-show-language">
                                                            <div class="prism-show-language-label">Markup</div>
                                                        </div>
                                                        <pre class=" language-markup">
[
    {
        "id": 1,
        "namadesakelurahan": "-"
    }
 ]
                                            </span>
                                                    </pre>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>



                    </div>
                </div>
            </div>


            <div class="col-sm-12">
                <!-- Basic usage card start -->
                <div class="card">
                    <div class="card-header" style="border-bottom: 1px solid rgba(204,204,204,0.35);">
                        <h5><i>Base URL : https://www.rsdarurat.com/service/</i></h5>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
</div>
@endsection