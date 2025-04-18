
@extends('template.template')
@section('css')

@endsection
@section('content-body')
<div class="page-wrapper" style="padding-top: 0">
    <!-- Page-header start -->
    <div class="page-header m-t-50">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <div class="d-inline">
                        <h4>Pegawai</h4>
                        <span>Form Detail Pegawai</span>
                    </div>
                </div>
            </div>
{{--            <div class="col-lg-4">--}}
{{--                <div class="page-header-breadcrumb">--}}
{{--                    <ul class="breadcrumb-title">--}}
{{--                        <li class="breadcrumb-item">--}}
{{--                            <a href="{!! route("show_page",["role"=> "user","pages"=>'pegawai'])!!}">--}}
{{--                                <i class="icofont icofont-home"></i>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="breadcrumb-item"><a href="#!">Detail Pegawai</a></li>--}}

{{--                    </ul>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
    <!-- Page-header end -->
    <!-- Page body start -->
    <div class="page-body">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-xs-12 col-sm-12">
                <!-- Default card start -->
                <div class="card">
                    <div class="card-header">
                        <h5>Pegawai </h5>
                    </div>
                    <div class="card-block">
                        <form action="{!! route("show_page",["role"=>$_SESSION["role"],"pages"=>"pegawai"]) !!}"  method="get">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">NIP</label>
                                <div class="col-sm-3 col-md-3 col-xs-12">
                                    <input type="text" value="{{request()->get("nip")}}" name="nip" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Nama Pegawai</label>
                                <div class="col-sm-6 col-md-6 col-xs-12">
                                    <input type="text" value="{{request()->get("namalengkap")}}" name="namalengkap"  class="form-control">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label"></label>
                                <div class="col-sm-8 col-md-8 col-xs-12">
                                    <button type="submit" class="btn btn-danger"><i class="ti-search"></i>  Cari </button>
                                </div>
                            </div>
                        </form>
                        <button onclick="show_popup('')" class="btn btn-sm btn-success"><i class="fa fa-user-plus"></i> Input Pegawai</button>
                        <hr>
                        <p>Terdapata [ <b>{{ $listpegawai->total() }}</b> ] Data Pegawai</p>
                        <div class="table-responsive">
                            <table class="table  table-striped table-sm table-styling">
                                <thead>
                                <tr class="table-inverse">
                                    <th>#</th>
                                    <th>NIP</th>
                                    <th>Nama Pegawai</th>
                                    <th>Tgl Lahir</th>
                                    <th>JK</th>
                                    <th>Pendidikan</th>
                                    <th>Jabatan</th>
                                    <th>Pangkat</th>
                                    <th>Jenis Pegawai</th>
                                    <th>Profile</th>
                                    <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($listpegawai as $key => $p)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{ $p->nip }}</td>
                                    <td><span  style="color:#01a9ac">
                                         {{ $p->namalengkap }}</span>
                                    </td>
                                    <td>{{ $p->tgllahir }}</td>
                                    <td>{{ $p->jeniskelamin }}</td>
                                    <td>{{ $p->pendidikan }}</td>
                                    <td>{{ $p->jabatan }}</td>
                                    <td>{{ $p->pangkat }}</td>
                                    <td>{{ $p->jenispegawai }}</td>
                                    <td>{{ $p->namaprofile }}</td>
                                    <td>
                                        <button  onclick="show_popup({{$p->id }})" class="btn btn-mini btn-primary btn-xlg edit"><i class="ti-pencil"></i></button>
                                        <button href="{!! route("hapusPegawai",[ "id" => $p->id ] ) !!}" class="btn btn-mini btn-danger btn-xlg hapus"><i class="ti-trash"></i></button>
                                    </td>
                                </tr>
                                @empty

                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div style='margin-top: 10px;'>
                            @if ($listpegawai->hasMorePages())
                                {!!
                                    $listpegawai->appends(["nip"=>request()->get("nip"),
                                    "namalengkap"=>request()->get("namalengkap")])
                                    ->links()
                                 !!}
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Default card end -->
            </div>

        </div>
    </div>
</div>

<div class="modal fade bs-example" id="modaledit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">PEGAWAI</h4>
            </div>
            <div id="modal-body">
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
    <script>
        function show_popup(id){
            $("#modaledit").modal("show");
            let url ="{!! route("showPegawai", ":id") !!}";
            url = url.replace('?:id', '?id='+id);
            $("#modal-body").load(url);
        }
        $(function(){

            $('.hapus').on('click',function(){
                var getLink = $(this).attr('href');
                swal({
                    title             : 'Yakin di Hapus ?',
                    text              : '',
                    //html              : true,
                    confirmButtonColor: '#d43737',
                    showCancelButton  : true,
                },function(){
                    window.location.href = getLink
                });
                return false;
            });
        });
    </script>

@endsection

