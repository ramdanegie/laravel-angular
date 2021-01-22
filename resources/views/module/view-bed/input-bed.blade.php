<form class="form-horizontal form-label-left" id="cust_form"
      autocomplete="off"  action="{!! route("saveDataBed") !!}" method="POST">
    <div class="modal-body">
        <fieldset class="mb-4">
            <input type="hidden"  name="id_ruangan" id="id_ruangan"  value="{{ $valueEdit->id_ruangan}}" class="form-control">
            <input type="hidden"  name="tt_id" id="tt_id"  value="{{ $valueEdit->tt_id}}" class="form-control">

            <legend>Update Status Bed</legend>
            <div class="col-lg-12 col-xl-12">
                <div class="table-responsive">
                    <table class="table m-0">
                        <tbody>
                        <tr>
                            <th scope="row">Ruangan</th>
                            <td>{{ $valueEdit->namaruangan  }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Nama Kamar</th>
                            <td>{{ $valueEdit->namakamar  }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Nama Bed</th>
                            <td>{{ $valueEdit->namabed  }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Status Bed</th>
                            <td>
                                <div class="col-sm-12 col-md-12 col-xs-12">
                                <select id="comboDiagnosa" class="form-control js-example-basic-single" name="statusbeds" required>
                                    <option value="">-- Status --</option>
                                    @foreach($listStatus  as $k)
                                        <option
                                            {{$valueEdit->sbid ==  $k->id ? 'selected' : ''  }}
                                            value='{{ $k->id }}' > {{ $k->statusbed }}</option>
                                    @endforeach
                                </select>
                            </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
{{--            <div class="form-group row">--}}
{{--                <label class="col-sm-3 col-form-label">Nama Ruangan </label>--}}
{{--                <div class="col-sm-5 col-md-5 col-xs-12">--}}
{{--                    <input type="text" placeholder="Ruangan"  name="namaruangan" id="namaruangan"--}}
{{--                           value="{{ isset($valueEdit) ? $valueEdit->namaruangan : ''  }}" class="form-control" disabled="">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="form-group row">--}}
{{--                <label class="col-sm-3 col-form-label">Nama Kamaar </label>--}}
{{--                <div class="col-sm-5 col-md-5 col-xs-12">--}}
{{--                    <input type="text" placeholder="Kamar"  name="namakamar" id="namakamar"--}}
{{--                           value="{{ isset($valueEdit) ? $valueEdit->namakamar : ''  }}" class="form-control" disabled="">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="form-group row">--}}
{{--                <label class="col-sm-3 col-form-label">Nama Bed </label>--}}
{{--                <div class="col-sm-5 col-md-5 col-xs-12">--}}
{{--                    <input type="text" placeholder="Bed"  name="namabed" id="namabed"--}}
{{--                           value="{{ isset($valueEdit) ? $valueEdit->namabed : ''  }}" class="form-control" disabled="">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="form-group row">--}}
{{--                <label class="col-sm-3 col-form-label">Status Bed</label>--}}
{{--                <div class="col-sm-5 col-md-5 col-xs-12">--}}
{{--                    <select id="comboDiagnosa" class="form-control js-example-basic-single" name="statusbed" required>--}}
{{--                        <option value="">-- Status --</option>--}}
{{--                        @foreach($listStatus  as $k)--}}
{{--                            <option--}}
{{--                                {{ isset($valueEdit) && $valueEdit->tt_id ==  $k->id ? 'selected' : ''  }}--}}
{{--                                value='{{ $k->id }}' > {{ $k->statusbed }}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div>--}}
        </fieldset>
        <fieldset class="mb-4">
            <legend>Detail Pasien</legend>
            <div class="table-responsive">
                <table class="table m-0">
                    <tbody>
                    <tr>
                        <th scope="row">No Rekam Medis</th>
                        <td>{{ $valueEdit->nocm  }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Nama Pasien</th>
                        <td>{{ $valueEdit->namapasien  }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Jenis Kelamin</th>
                        <td>{{ $valueEdit->jeniskelamin  }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Umur</th>
                        <td>{{ $valueEdit->umur_string  }}</td>
                    </tr>
                    <tr>
                        <th scope="row">No Registrasi</th>
                        <td>{{ $valueEdit->noregistrasi  }}</td>
                    </tr>
                    <tr>
                        <th scope="row">No HP/Telpon</th>
                        <td>{{ $valueEdit->nohp  }}</td>
                    </tr>

                    </tbody>
                </table>
            </div>
{{--            <div class="form-group row">--}}
{{--                <label class="col-sm-3 col-form-label">No Rekam Medis </label>--}}
{{--                <div class="col-sm-5 col-md-5 col-xs-12">--}}
{{--                    <input type="text" placeholder="No Rekam Medis"  name="nocm" id="nocm"--}}
{{--                           value="{{ isset($valueEdit) ? $valueEdit->nocm : ''  }}" class="form-control" disabled="">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="form-group row">--}}
{{--                <label class="col-sm-3 col-form-label">Nama Pasien </label>--}}
{{--                <div class="col-sm-5 col-md-5 col-xs-12">--}}
{{--                    <input type="text" placeholder="Nama Pasien"  name="namapasien" id="namapasien"--}}
{{--                           value="{{ isset($valueEdit) ? $valueEdit->namapasien : ''  }}" class="form-control" disabled="">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="form-group row">--}}
{{--                <label class="col-sm-3 col-form-label">Jenis Kelamin </label>--}}
{{--                <div class="col-sm-5 col-md-5 col-xs-12">--}}
{{--                    <input type="text" placeholder="Jenis Kelamin"  name="jeniskelamin" id="jeniskelamin"--}}
{{--                           value="{{ isset($valueEdit) ? $valueEdit->jeniskelamin : ''  }}" class="form-control" disabled="">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="form-group row">--}}
{{--                <label class="col-sm-3 col-form-label">Umur </label>--}}
{{--                <div class="col-sm-5 col-md-5 col-xs-12">--}}
{{--                    <input type="text" placeholder="Umur"  name="umur_string" id="umur_string"--}}
{{--                           value="{{ isset($valueEdit) ? $valueEdit->umur_string : ''  }}" class="form-control" disabled="">--}}
{{--                </div>--}}
{{--            </div>--}}
        </fieldset>

    </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
    </div>
</form>

<script>
    $('.js-example-basic-single').select2();
    $('.date-custom').bootstrapMaterialDatePicker
    ({
        time: false,
        clearButton: false,
        switchOnClick:true,
        nowButton:true,
        // format :'YY MMM YYYY'
    });
</script>
