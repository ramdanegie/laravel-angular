<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//if(isset($_SESSION)){
    session_start();
//}

Route::get('/', "Auth\AuthController@show")->name("login");
Route::post('/logins', "Auth\AuthController@loginKeun")->name("login_validation");
Route::get('/logout', "Auth\AuthController@logoutKeun")->name("logout");


Route::group([ 'prefix' => 'view'], function () {
    Route::get('pasien', 'MedicalRecord\GeneralController@getPasien')->name('pasien');
    Route::get('detail-emr', 'MedicalRecord\GeneralController@getDetailEMR')->name('detailemr');
    Route::get('get-pasien', 'MedicalRecord\GeneralController@getPasien');
});

Route::get('/emr', function () {
    return view('form/emr');
});

Route::group(["middleware" => "login_check"], function () {

    Route::get('/detail-pindah', "Auth\AuthController@getPopUpPindah")->name("showDetailPindah");
    Route::get('/get-kamarbyruangankelas', "Auth\AuthController@getKamarByKelasRuangan");
    Route::get('/get-nobedbykamar', "Auth\AuthController@getNoBedByKamar");
    Route::post('/save-pindah-pasien', "Auth\AuthController@savePindahPasien")->name("savePindah");
    Route::get('/detail-pulang', "Auth\AuthController@getDataPulang")->name("showDetailPulang");
    Route::post('/save-rencana-pulang-pasien', "Auth\AuthController@savePulang")->name("saveRencanaPulang");
    Route::get('/detail-pulang-rencana', "Auth\AuthController@getDataPulangRencana")->name("showProsesPulang");

    Route::get('/get-ruangan-by-dept', "Auth\AuthController@getRuanganByDept");
    Route::get('/daftar-pasien', "Auth\AuthController@getDaftarPasiens")->name("daftarPasien");
    Route::post('/bed-save-dt', "Auth\AuthController@saveDataBeds")->name("saveDataBed");
//    Route::get('/bed', "Auth\AuthController@showIndex")->name("home");
    Route::get('/data-harian', "Auth\AuthController@getDataHarian")->name("dataHarian");
    Route::get('/daftar-pasien-aktif', "Auth\AuthController@getDaftarPasienAktif")->name("daftarPasienAktif");
    Route::get('/bed-get', "Auth\AuthController@getDataBed")->name("showBedDetail");

    Route::get('/get-order-konsul', "Auth\AuthController@getOrderKonsul")->name("showPopUpKonsul");
    Route::post('/delete-konsul', "Auth\AuthController@unverifKonsul");
    Route::post('/post-konsul', "Auth\AuthController@saveOrderKonsul")->name("saveKonsul");
    Route::get('/dashboard-nurse', function () {
        return view('module.nurse.dashboard');
    });

    Route::get('/bed-get-byid', "Auth\AuthController@getDataBed");

    Route::get('/get-diagnosa-bykode/{kddiagnosa}', 'DashboardController@geTopTenDiagnosaByKD');
    Route::get('/get-diagnosa-bykode-byrsaddress/{kddiagnosa}', 'DashboardController@geTopTenDiagnosaByRSAddress');
    Route::get('/get-name-prov/{kode}', 'DashboardController@getNameRegionBykode');
    Route::get('/get-name-kota/{kode}/{kddiagnosa}', 'DashboardController@getNameKotaBykode');
    Route::get('/pelayanan-detail/{code}/{nama}/{kddiagnosa}','DashboardController@geTopTenDiagnosaDetail');
    Route::get('/get-detail-table-diagnosa','DashboardController@getDetailTableDiag');
    Route::get('/get-chart-by-rs','DashboardController@getChartByRS');
    Route::get('/get-detail-rs-table','DashboardController@getDetailRS');
    Route::get('/get-data-dashboard','DashboardController@getDataDashboard');
    Route::get('/get-combo-diagnosa','DashboardController@getComboDiagnosa');
    Route::get('/get-data-chart-rs','DashboardController@getDataChartRS');
    Route::get('/get-data-faskes','DashboardController@getDataFaskes');
    Route::get('/get-pasien-bymap', 'DashboardController@getMapDataKabupatenKota');
    Route::get('/get-pasien-by-kotakab', 'DashboardController@getDetailPasienKecamatan');
    Route::get('/get-data-flag','DashboardController@getDataDashboardFlag');
    Route::get('/get-dashboard-pegawai','DashboardController@getDashboardPegawai');
    Route::get('/get-dashboard-persediaan','DashboardController@getDashboardPersediaan');
    Route::get('/get-dashboard-persediaan-stok','DashboardController@getDashboardPersediaanStok');
    Route::get('/get-dashboard-kamar','DashboardController@getKetersediaanKamar');
    Route::get('/get-detail-covid-pasien','DashboardController@getDetailCovid');
    Route::get('/get-detail-kunjungan-pasien','DashboardController@getDetailKun');
    Route::get('/get-detail-bed','DashboardController@getDetailBed');
    Route::get('/get-kec-by-prov','MainController@getKecByProv');
    Route::get('/get-top-diagnosa-by-kec','MainController@getTopDiagByKec');
    Route::get('/get-detail-kunjungan-pasien','MainController@getDetailKun');
    Route::get('/get-detail-pendapatan','MainController@getDetailPendapatan');
    Route::get('/get-detail-pegawai','MainController@getDetailPegawai');
    Route::get('/get-count-pegawai','MainController@getCountPegawai');
    Route::post('/tes-post','MainController@tesPost');

    Route::get('{role}/{pages}', "MainController@show_page")->name("show_page");
//    Route::get('/pegawai-show', "MainController@showDataPegawai")->name("showPegawai");
//    Route::post('/pegawai-save', "MainController@savePegawai")->name("savePegawai");
//    Route::get('/pegawai-delete', "MainController@hapusPegawai")->name("hapusPegawai");
//
//    Route::get('/bed-show', "MainController@showDataBed")->name("showBed");
//    Route::post('/bed-save', "MainController@saveBed")->name("saveBed");
//    Route::get('/bed-delete', "MainController@hapusBed")->name("hapusBed");
//
//    Route::get('/stok-show', "MainController@showDataStok")->name("showStok");
//    Route::post('/stok-save', "MainController@saveStok")->name("saveStok");
//    Route::get('/stok-delete', "MainController@hapusStok")->name("hapusStok");

});

Route::get('/katalog', function () {
    return view('module.katalog.index');
});

Route::post('/ecg','MainController@saveECG');
