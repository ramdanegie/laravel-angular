<form class="form-horizontal form-label-left"
      autocomplete="off"  action="{!! route("saveRencanaPulang") !!}" method="POST">
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

            <legend> Kondisi</legend>
            <div class="col-lg-12 col-xl-12">
                <div class="table-responsive">
                    <table class="table m-0">
                        <tbody>


                        <tr>
                            <th scope="row">Kondisi</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <select id="cboKondisi" class="form-control cbo-custom" name="kondisi" required>
                                        <option value="">-- Kondisi --</option>
                                        @foreach($cbo['kondisiKeluar']  as $k)
                                            <option
                                                value="{{ $k->id }}" > {{ $k->kondisipasien }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Status Pulang</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <select id="cboStatusPulang" class="form-control cbo-custom" name="statuspulang" required>
                                        <option value="">-- Status Pulang --</option>
                                        @foreach($cbo['statusPulang']  as $k)
                                            <option
                                                value="{{ $k->id }}" > {{ $k->statuspulang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Tgl Keluar</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <input type="text" id="tglpulang" name="tglpulang"
                                           class="datetime-custom form-control" value="{{date('Y-m-d H:i:s')}}" >
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Pembawa Pulang</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <input type="text" id="namapembawa" name="namapembawa"  placeholder="Pembawa Pulang" class=" form-control" >
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Hubungan Keluarga</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                    <select id="cboHubungan" class="form-control cbo-custom" name="hubungankeluarga" >
                                        <option value="">-- Hubungan Keluarga --</option>
                                        @foreach($cbo['hubunganKeluarga']  as $k)
                                            <option
                                                value="{{ $k->id }}" > {{ $k->hubungankeluarga }}</option>
                                        @endforeach
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
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
    </div>
</form>

<script>
    //  $(".cbo-custom").select2();
    $('.date-custom').bootstrapMaterialDatePicker({
        time: false,
        clearButton: false,
        switchOnClick: true,
        nowButton: true,
        // format :'YY MMM YYYY'
    });
    $('.datetime-custom').bootstrapMaterialDatePicker({
        time: true,
        clearButton: false,
        switchOnClick: true,
        nowButton: true,
        format :'YYYY-MM-DD HH:mm:ss'
    });
</script>
