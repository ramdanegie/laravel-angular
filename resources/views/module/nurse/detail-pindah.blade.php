<form class="form-horizontal form-label-left" id="cust_form"
      autocomplete="off"  action="{!! route("savePindah") !!}" method="POST">
    <div class="modal-body">
    <fieldset class="mb-4">
        <input type="hidden"  name="norec_apd" id="norec_apd"  value="{{ $norec_apd }}" class="form-control">
        <input type="hidden"  name="namapasienc" id="namapasienc"  value="{{ $paramCari['namapasien'] }}" class="form-control">
        <input type="hidden"  name="objectdepartemenfkc" id="objectdepartemenfkc"  value="{{ $paramCari['objectdepartemenfk'] }}" class="form-control">
        <input type="hidden"  name="ruanganfkc" id="ruanganfkc"  value="{{ $paramCari['ruanganfk'] }}" class="form-control">
        <input type="hidden"  name="lamarawatc" id="lamarawatc"  value="{{ $paramCari['lamarawats'] }}" class="form-control">
        <legend>Detail Pasien</legend>
            <div class="table-responsive">
                <table class="table m-0">
                    <tbody>
                    <tr>
                        <th scope="row">No Rekam Medis</th>
                        <td>{{ $pasien->nocm  }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Nama Pasien</th>
                        <td>{{ $pasien->namapasien  }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Kamar</th>
                        <td>{{ $pasien->kamarpasien  }}</td>
                    </tr>

                    </tbody>
                </table>
            </div>

        </fieldset>
        <fieldset class="mb-4">

            <legend> Ruangan Tujuan</legend>
            <div class="col-lg-12 col-xl-12">
                <div class="table-responsive">
                    <table class="table m-0">
                        <tbody>
                        <!-- <tr>
                            <th scope="row">Tgl Pindah</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">

                               </div>
                           </td>
                        </tr> -->
                        <tr>
                            <th scope="row">Instalasi</th>
                            <td>  <div class="col-sm-12 col-md-12 col-xs-12">
                                <select id="comboDepartemen2" class="form-control cbo-custom" name="departemen" required>
                                    <option value="">-- Pilih Instalasi --</option>
                                    @foreach($dataDepartemen  as $k)
                                        <option
                                            value="{{ $k['id'] }}" > {{ $k['namadepartemen'] }}</option>
                                    @endforeach
                                </select>
                            </div></td>
                        </tr>
                       <tr>
                            <th scope="row">Ruangan</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <select id="comboRuangan2" class="form-control cbo-custom" name="ruangan" required>
                                        <option value="">-- Pilih Ruangan --</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Kamar </th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <select id="comboKamar" class="form-control cbo-custom" name="kamar" required>
                                        <option value="">-- Pilih Kamar --</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Bed </th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <select id="comboBed" class="form-control cbo-custom" name="bed" required>
                                        <option value="">-- Pilih Bed --</option>
                                    </select>
                                </div>
                            </td>
                        </tr>


                        </tbody>
                    </table>
                </div>
            </div>

        </fieldset>


    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
    </div>
</form>

<script type="application/javascript">
        //  $(".cbo-custom").select2();
        var APP_URL = {!! json_encode(url('/')) !!}
    $("#comboDepartemen2").change(function(e){
    // debugger
        $.ajax({
            type    : 'GET',
            url     : APP_URL+'/get-ruangan-by-dept',
            data    : {dep: $("#comboDepartemen2").val()},
            cache   : false,
            success : function(respond){

                $("#comboRuangan2").html(respond);
                // $("#comboRuangan").val()

            }
        })
     })
     $("#comboRuangan2").change(function(e){
        $.ajax({
            type    : 'GET',
            url     : APP_URL+'/get-kamarbyruangankelas',
            data    : {idKelas: 6,idRuangan:$("#comboRuangan2").val()},
            cache   : false,
            success : function(respond){
             $("#comboKamar").html(respond);
            }
        })
    })
    $("#comboKamar").change(function(e){
        $.ajax({
            type    : 'GET',
            url     : APP_URL+'/get-nobedbykamar',
            data    : {idKamar:$("#comboKamar").val()},
            cache   : false,
            success : function(respond){
             $("#comboBed").html(respond);
            }
        })
    })
</script>
