<form class="form-horizontal form-label-left" id="cust_form"
      autocomplete="off"  action="{!! route("savePegawai") !!}" method="POST">
            <div class="modal-body">
                <input type="hidden"  name="id" id="id"  value="{{ isset($valueEdit) ? $valueEdit->id: ''  }}" class="form-control">
                <fieldset class="mb-4">
                    <legend>Data Profil Pegawai</legend>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nama Lengkap </label>
                        <div class="col-sm-9 col-md-9 col-xs-12">
                            <input type="text" placeholder="Nama Lengkap"  name="namalengkap" id="namalengkap"
                                   value="{{ isset($valueEdit) ? $valueEdit->namalengkap: ''  }}"
                                   class="form-control" required>
                        </div>

                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">NIP </label>
                        <div class="col-sm-5 col-md-5 col-xs-12">
                            <input type="text" placeholder="NIP"  name="nip" id="nip" value="{{ isset($valueEdit) ? $valueEdit->nip : ''  }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Tempat / Tanggal Lahir</label>
                        <div class="col-sm-5 col-md-5 col-xs-12">
                            <input type="text" placeholder="Tempat Lahir"  name="tempatlahir"
                                   value="{{ isset($valueEdit) ? $valueEdit->tempatlahir : ''  }}"
                                   id="tempatlahir" class="form-control" >
                        </div>
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                <input type="text" id="tgllahir" name="tgllahir"  class="date-custom form-control"
                                       value="{{ isset($valueEdit) ? $valueEdit->tgllahir : ''  }}" >
                            </div>
{{--                            <input type="text" placeholder="Tahun" name="tahunlahir" id="tahunlahir" class="form-control">--}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Jenis Kelamin</label>
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            <select id="comboDiagnosa" class="form-control js-example-basic-single" name="jk" required>
                                <option value="">-- Jenis Kelamin --</option>
                                @foreach($listJk  as $k)
                                    <option
                                        {{ isset($valueEdit) && $valueEdit->objectjeniskelaminfk ==  $k->id ? 'selected' : ''  }}
                                        value='{{ $k->id }}' > {{ $k->jeniskelamin }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            <select  class="form-control js-example-basic-single" name="pendidikan" required>
                                <option value="">-- Pendidikan --</option>
                                @foreach($listPdd as $k)
                                    <option
                                        {{ isset($valueEdit) && $valueEdit->objectpendidikanfk ==  $k->id ? 'selected' : ''  }}
                                        value='{{ $k->id }}' > {{ $k->pendidikan }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Jabatan</label>
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            <select  class="form-control js-example-basic-single" name="jabatan" required>
                                <option value="">-- Jabatan --</option>
                                @foreach($listJB as $k)
                                    <option
                                        {{ isset($valueEdit) && $valueEdit->objectjabatanfk ==  $k->id ? 'selected' : ''  }}
                                        value='{{ $k->id }}' > {{ $k->jabatan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            <select  class="form-control js-example-basic-single" name="jenispegawai" required>
                                <option value="">-- Jenis Pegawai --</option>
                                @foreach($listJP as $k)
                                    <option
                                        {{ isset($valueEdit) && $valueEdit->objectjenispegawaifk  ==  $k->id  ? 'selected' : ''  }}
                                        value='{{ $k->id }}' > {{ $k->jenispegawai }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Pangkat</label>
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            <select  class="form-control js-example-basic-single" name="pangkat" id="pangkat">
                                <option value="">-- Pangkat --</option>
                                @foreach($listPangkat as $k)
                                    <option {{ isset($valueEdit) && $valueEdit->objectpangkatfk  ==  $k->id  ? 'selected' : ''  }}
                                            value='{{ $k->id }}' > {{ $k->pangkat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Tgl Masuk / Tgl Keluar</label>
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                <input type="text" placeholder="Tgl Masuk" id="tglmasuk"
                                       value="{{ isset($valueEdit) ? $valueEdit->tglmasuk : ''  }}"
                                       name="tglmasuk"  class="date-custom form-control" >
                            </div>
                        </div>
                        <div class="col-sm-4 col-md-4 col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                <input type="text"  placeholder="Tgl Keluar" id="tglkeluar" name="tglkeluar"
                                       value="{{ isset($valueEdit) ? $valueEdit->tglkeluar : ''  }}"
                                       class="date-custom form-control"  >
                            </div>
                            {{--                            <input type="text" placeholder="Tahun" name="tahunlahir" id="tahunlahir" class="form-control">--}}
                        </div>
                    </div>
{{--                        <div class="col-sm-6 col-md-6 col-xs-12">--}}
{{--                            <select class="js-example-basic-single col-sm-12" name="asalsekolah" style="line-height:35px !important;">--}}
{{--                                <option value="">Asal Sekolah</option>--}}
{{--                                <?php--}}
{{--                                foreach ($sekolah as $s){--}}
{{--                                ?>--}}
{{--                                <option value="<?php echo $s->nama_sekolah; ?>"><?php echo $s->nama_sekolah; ?></option>--}}
{{--                                <?php--}}
{{--                                }--}}
{{--                                ?>--}}
{{--                            </select>--}}
{{--                        </div>--}}
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
