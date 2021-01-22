<form class="form-horizontal form-label-left" id="cust_form"
      autocomplete="off"  action="{!! route("saveBed") !!}" method="POST">
            <div class="modal-body">
                <input type="hidden"  name="id" id="id"  value="{{ isset($valueEdit) ? $valueEdit->norec: ''  }}" class="form-control">
                <fieldset class="mb-4">
                    <legend>Data Ketersediaan Tempat Tidur</legend>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Kelas / Ruang</label>
                        <div class="col-sm-5 col-md-5 col-xs-12">
                            <select id="comboDiagnosa" class="form-control js-example-basic-single" name="kelas" required>
                                <option value="">-- Kelas --</option>
                                @foreach($listKelas  as $k)
                                    <option
                                        {{ isset($valueEdit) && $valueEdit->objectkelasfk ==  $k->id ? 'selected' : ''  }}
                                        value='{{ $k->id }}' > {{ $k->namakelas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Kapasitas </label>
                        <div class="col-sm-5 col-md-5 col-xs-12">
                            <input type="text" placeholder="Kapasitas"  name="kapasitas" id="kapasitas" value="{{ isset($valueEdit) ? $valueEdit->kapasitas : ''  }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Tersedia </label>
                        <div class="col-sm-5 col-md-5 col-xs-12">
                            <input type="text" placeholder="Tersedia"  name="tersedia" id="tersedia" value="{{ isset($valueEdit) ? $valueEdit->tersedia : ''  }}" class="form-control" required>
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
