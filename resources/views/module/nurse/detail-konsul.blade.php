<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script> -->
<div class="modal-body">

    <div class="table-responsive">
        <table class="table  table-striped table-sm table-styling" style="width: 100%" id="tabelstatus" >
        <thead>
        <tr class="table-inverse">
            <th>No </th>
            {{--                    <th>Ruang Asal </th>--}}
            <th>Poli Tujuan </th>
            <th>Dokter </th>
            <th>Keterangan </th>
            <th>Status </th>
            <th># </th>
        </tr>
        </thead>
        <tbody>

        @forelse($data as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                {{--                        <td>{{ $d->ruanganasal }}</td>--}}
                <td>{{ $d->ruangantujuan }}</td>
                <td>{{ $d->namalengkap }}</td>
                <td>{{ $d->keteranganorder }}</td>
                <td>{{ $d->norec_apd != null ? 'Verifikasi' : '-' }}</td>
                <td>
                <button data-norec-so="<?php echo $d->norec; ?>" data-norec-apd="<?php echo $d->norec_apd != null ? 'ada' : '-'; ?>" id="btnDel"    type="button"
                        data-toggle="tooltip" title="Hapus" class="btn btn-danger btn-mini waves-effect waves-light">
                    <span class="fa fa-trash"></span>
                </button>
{{--                onclick="deleteKonsul('{{ $d->norec }}','{{ $d->norec_apd != null ? 'ada' : '-'}}')"--}}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center">Data Tidak ada</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
<form class="form-horizontal form-label-left" id="cust_form"
      autocomplete="off"  action="{!! route("saveKonsul") !!}" method="POST">
    <div class="modal-body">
{{--        <fieldset class="mb-4">--}}
            <input type="hidden"  name="norec_pd" id="norec_pd"  value="{{ $norec_pd }}" class="form-control">
            <input type="hidden"  name="namapasienc" id="namapasienc"  value="{{ $paramCari['namapasien'] }}" class="form-control">
            <input type="hidden"  name="objectdepartemenfkc" id="objectdepartemenfkc"  value="{{ $paramCari['objectdepartemenfk'] }}" class="form-control">
            <input type="hidden"  name="ruanganfkc" id="ruanganfkc"  value="{{ $paramCari['ruanganfk'] }}" class="form-control">
            <input type="hidden"  name="lamarawatc" id="lamarawatc"  value="{{ $paramCari['lamarawats'] }}" class="form-control">

        <fieldset class="mb-4">

            <legend> Tambah Rujuk Poli</legend>
            <div class="col-lg-12 col-xl-12">
                <div class="table-responsive">
                    <table class="table m-0">
                        <tbody>
                         <tr>
                            <th scope="row">Pasien</th>
                            <td>{{ $pasien->namapasien .' ( '.$pasien->nocm.' )'  }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Poli Tujuan</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <select id="comboPoli" class="form-control" name="poli" required>
                                        <option value="">-- Pilih Poli --</option>
                                        @foreach($ruangan  as $k)
                                            <option {!! $selectedRuangan == $k->id ? 'selected' : '' !!}
                                                value="{{ $k->id }}" > {{ $k->namaruangan }}</option>
                                            
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                        </tr>
                      <!--   <tr>
                            <th scope="row">Dokter</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <select id="comboDok" class="form-control cbo-cusss" name="dokter" >
                                        <option value="">-- Pilih Dokter --</option>
                                            @foreach($dokter  as $k)
                                                <option
                                                    value="{{ $k->id }}" > {{ $k->namalengkap }}</option>
                                            @endforeach
                                    </select>
                                </div>
                            </td>
                        </tr> -->

                        <tr>
                            <th scope="row">Keterangan </th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <textarea name="keterangan" rows="5" cols="5" class="form-control"
                                              placeholder="Keterangan"></textarea>
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
    $(document).ready(function() {
        // $('.cbo-cusss').select2();
        // $(".cbo-cusss").select2({
        //     dropdownParent: $("#modalkonsul")
        // });
            $("#tabelstatus").dataTable();
   });

    var APP_URL = {!! json_encode(url('/')) !!}

    function deleteKonsul(norec_so,norec_apd){
        if(norec_apd =='ada'){
            alert('Tidak bisa dihapus sudah di verifkasi')
            return
        }

        $.ajax({
            type    : 'POST',
            url     : APP_URL+'/delete-konsul',
            data    : {norec_so:norec_so},
            cache   : false,
            success : function(respond){
                if(respond == 1){
                    $('#tabelstatus tr').remove();
                }
            }
        })
    }
    $("#tabelstatus").on('click', '#btnDel', function () {
        var norec_so = $(this).attr("data-norec-so");
        var apd = $(this).attr("data-norec-apd");
        let thisss =this
        if(apd == 'ada'){
            add_toast("Tidak bisa dihapus sudah di verifkasi","info");
            // alert('Tidak bisa dihapus sudah di verifkasi')
            return
        }
        var APP_URL = {!! json_encode(url('/')) !!}
        $.ajax({
            type    : 'POST',
            url     : APP_URL+'/delete-konsul',
            data    : {norec_so:norec_so},
            cache   : false,
            success : function(respond){
                // debugger
                if(respond == 1){
                    $(thisss).closest('tr').remove();
                }
            }
        })

    });
    $(function(){
        // $("#tabelstatus").dataTable();
    });
</script>

