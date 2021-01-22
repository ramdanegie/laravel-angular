<form class="form-horizontal form-label-left" id="cust_form"
      autocomplete="off"  action="{!! route("saveStok") !!}" method="POST">
    <div class="modal-body">
        <input type="hidden"  name="norec" id="norec"  value="{{ isset($valueEdit) ? $valueEdit->norec: ''  }}" class="form-control">
        <fieldset class="mb-4">
            <legend>Stok RS</legend>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Nama Produk</label>
                <div class="col-sm-5 col-md-5 col-xs-12">
                    <select id="comboDiagnosa" class="form-control js-example-basic-single" name="produk" required>
                        <option value="">-- Produk --</option>
                        @foreach($listProduk  as $k)
                            <option
                                {{ isset($valueEdit) && $valueEdit->produkfk ==  $k->id ? 'selected' : ''  }}
                                value='{{ $k->id }}' > {{ $k->namaproduk }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Satuan</label>
                <div class="col-sm-5 col-md-5 col-xs-12">
                    <select id="comboDiagnosa" class="form-control js-example-basic-single" name="satuanstandar" required>
                        <option value="">-- Satuan --</option>
                        @foreach($listSatuan as $k)
                            <option
                                {{ isset($valueEdit) && $valueEdit->satuanstandarfk ==  $k->id ? 'selected' : ''  }}
                                value='{{ $k->id }}' > {{ $k->satuanstandar }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Total </label>
                <div class="col-sm-5 col-md-5 col-xs-12">
                    <input type="text" placeholder="Total Stok"  name="total" id="total" value="{{ isset($valueEdit) ? $valueEdit->total : ''  }}" class="form-control" required>
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
