@extends('template.template')
@section('css')
<style>
    #mapid { height: 1000px; }
</style>
@endsection
@section('content-body')
<div id="mapid"></div>
@endsection
@section('javascript')
<!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAjQfTEPdvW5C0pJwM-Iep2_DMKQXiUfGI&callback=initialize" async defer></script>-->
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD-mlg5GUBIec3T5617uBS8J5GPK2Vb8Pk&callback=initialize">
</script>

<script>
    // initialize()
    function initialize() {
        var alamat = "PGC 1, Cililitan, Kec. Kramat jati, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta, Indonesia";
        var geocoder = new google.maps.Geocoder;
        geocoder.geocode({'address': alamat}, function(results, status) {
            if (status === 'OK') {
                if (results[0]) {
                    geo = results[0].geometry.location;
                    rs = geo.lat()+", "+geo.lng();
                } else {
                    rs = 'No results found';
                }
            } else {
                rs = 'Geocoder failed due to: ' + status;
            }
            alert(rs);
        });
    }
    var mymapDashboard;
    $(document).ready(function(){
        mymapDashboard = L.map('mapid').setView([-7.2540452, 112.7505882], 8);
        setTimeout(function(){
            L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoicmFtZGFuZWdpZSIsImEiOiJja2N1OG1uYjUyNWtjMnFsYjB3cGxiZ2RvIn0.bIO5MwJKX98q8D2-1lJ8zQ', {
                maxZoom: 15,
                attribution: 'Map data &copy; <a href="https://www.inovamedika.com/">www.inovamedika.com</a> contributors, ' +
                    '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                    'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                id: 'mapbox/streets-v11',
                tileSize: 512,
                zoomOffset: -1
            }).addTo(mymapDashboard);


            L.marker([-7.2540452, 112.7505882]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Surabaya Kota');
            });


            L.marker([-7.4099012, 112.6929866]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Sidoarjo ');
            });


            L.marker([-7.1649211, 112.6354279]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Gresik');
            });


            L.marker([-7.190560, 113.247195]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Sampang');
            });


            L.marker([-7.024420, 112.750636]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Bangkalan');
            });


            L.marker([-7.1548012, 113.4554739]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Pamekasan');
            });


            L.marker([-7.0095355, 113.8495442]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Sumenep ');
            });


            L.marker([-8.3474456, 113.4069761]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Jember');
            });


            L.marker([-7.8424207, 111.9811681]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Kediri');
            });


            L.marker([-7.5904682, 111.8913635]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Nganjuk ');
            });


            L.marker([-7.4191036, 111.4084305]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Ngawi');
            });


            L.marker([-8.094825, 112.1477463]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Blitar Kota');
            });


            // L.marker([NULL, NULL]).addTo(mymapDashboard).on('click', function(e){
            //     setKabupaten(this, 'Gowa');
            // });


            L.marker([-7.6455201, 111.2994343]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Magetan');
            });


            // L.marker([NULL, NULL]).addTo(mymapDashboard).on('click', function(e){
            //     setKabupaten(this, 'Ternate');
            // });


            L.marker([-7.5412522, 112.2008105]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Jombang');
            });


            L.marker([-7.4707422, 112.419833]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Mojokerto');
            });


            L.marker([-7.7505416, 112.6972926]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Pasuruan');
            });


            L.marker([-7.8716621, 111.4516344]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Ponorogo ');
            });


            L.marker([-6.8934518, 112.0253574]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Tuban ');
            });


            L.marker([-6.9052436, 107.3141495]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Bandung Barat');
            });


            L.marker([-7.1560884, 111.8785384]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Bojonegoro');
            });


            L.marker([-6.8223228, 107.1289307]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Cianjur');
            });


            L.marker([-7.1276047, 112.376209]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Lamongan ');
            });


            L.marker([-6.7040571, 110.8969791]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Pati');
            });


            L.marker([-7.0247246, 110.3470252]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Semarang');
            });


            L.marker([-7.6897123, 110.311235]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Sleman');
            });


            L.marker([-7.6828419, 110.761394]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Sukoharjo');
            });


            L.marker([-7.577444, 110.8257805]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Surakarta');
            });


            L.marker([-7.9786439, 112.5967635]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Malang Kota');
            });


            L.marker([-7.7723105, 113.1664562]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Probolinggo Kota');
            });


            L.marker([-7.4714887, 112.4217214]).addTo(mymapDashboard).on('click', function(e){
                setKabupaten(this, 'Mojokerto Kota');
            });
        }, 3000);



//    mymapDashboard.on('click', onMapClick);



    })
    function onMapClick(e) {
        L.popup()
            .setLatLng(e.latlng)
            .setContent("You clicked the map at " + e.latlng.toString())
            .openOn(mymapDashboard);
    }

    function setKabupaten(obj, kabupaten){
        // $.ajax({
        //     type    :'POST',
        //     url     :'https://rsdc.inovamedika.com/index.php?r=sistemInformasiEksekutif/dashboardCovid19/SetKabupaten',
        //     data    : {kabupaten:kabupaten,'Access-Control-Allow-Origin':'*'},
        //     dataType: "json",
        //     success :function(data){
                if(kabupaten == 'Sampang'){
                    var data ={
                        "pasien": 3,
                        "loadpasien": [
                            {
                                "latitude": "-7.065215",
                                "longitude": "113.1815534",
                                "kecamatan_nama": "Kedungdung ",
                                "jumlah": "1"
                            },
                            {
                                "latitude": "-7.1530706",
                                "longitude": "113.1812058",
                                "kecamatan_nama": "Torjun",
                                "jumlah": "1"
                            },
                            {
                                "latitude": "-7.247838",
                                "longitude": "113.1956949",
                                "kecamatan_nama": "Sampang ",
                                "jumlah": "1"
                            }
                        ]
                    }
                }
                if (data.pasien == undefined ){
                    toastr.error("Kecamatan belum di set koordinatnya","Perhatian!");
                    return false;
                }

                $.each(data.loadpasien, function(index, value){
                    var greenIcon = new L.Icon({
                        iconUrl: "{!! asset('js/leaflet/images/marker-icon-2x-red.png') !!}",
                        shadowUrl: "{!! asset('js/leaflet/images/marker-shadow.png') !!}",
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });

                    L.marker([value.latitude, value.longitude],{icon: greenIcon}).addTo(mymapDashboard)
                        .bindPopup("<b>Kecamatan "+ value.kecamatan_nama +"</b><br>"+ value.jumlah +' Pasien').openPopup();
                });
                return true;
            // },
            // error   : function (jqXHR, textStatus, errorThrown) { console.log(errorThrown);}
        // });

    }
</script>
@endsection
