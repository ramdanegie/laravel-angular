@extends('template.template3')
@section('css')
    <style>
        .pcoded .pcoded-inner-content {
            padding: 10px 0 10px 0;
        }

        .class-icon {
            font-size: 50px;

            padding: 5px;
            border-radius: 5px;
            background: #bcbcbc8a;
        }
        .pad{
            padding-top: 3rem;
        }
        @media (min-width: 992px) {
            .pad {
                padding-top: 1.8rem;
            }
        }
        .modal-lg .kons{
            width:1140px;
        }
        @media only screen and (max-width: 575px) {
            .latest-update-card .card-block .latest-update-box .update-meta {
                z-index: 2;
                min-width: 0;
                text-align: left !important;
                margin-bottom: 15px;
                border-top: 1px solid #f1f1f1;
                padding-top: 15px;
            }
        }
    </style>
@endsection
@section('content-body')
    <div class="page-wrapper pad" >
        <div class="page-body">
            <div class="card">
                <div class="card-header">
                    <h5>Dashboard Back Office</h5>
                </div>
                <div class="card-block tab-icon">
                    <form action="{!! route("show_page", ["role" => $_SESSION['role'], "pages" => $r->pages ]) !!}" method="get">
                        <div class="row">
                            <div class="col-lg-7">
                            </div>
                            <div class="col-lg-2" style="margin-top: 5px">
                                <div class="input-group" >
                                    <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                    <input type="text" id="tglawal" name="tglawal" class="date-custom form-control" value="{{request()->get("tglawal")}}" >
                                </div>

                            </div>
                            <div class="col-lg-2" style="margin-top: 5px">
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                    <input type="text" id="tglakhir" name="tglakhir"  class="date-custom form-control" value="{{request()->get("tglakhir")}}" >
                                </div>
                            </div>
                            <div class="col-lg-1" style="margin-top: 5px" >
                                <button class="btn btn-success  btn-outline-success"  type="submit">
                                    <i class="icofont icofont-search"></i>Search</button>
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px ">
                            <div class="col-lg-12 col-md-12">
                                <div class="card">
                                    <div class="panel panel-info">
                                        <div class="panel-heading bg-info">
                                            Dashboard PPI
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-warning">
                                        <div class="panel-heading bg-warning">
                                            Dashboard PMKP
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-primary">
                                        <div class="panel-heading bg-primary">
                                            Dashboard Laundry
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-merah">
                                        <div class="panel-heading bg-c-merah">
                                           <span style="color:white"> Dashboard Sanitasi</span>
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-kuning">
                                        <div class="panel-heading bg-c-kuning">
                                            Dashboard IPSRS
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-purple">
                                        <div class="panel-heading bg-purple">
                                            Dashboard Administrasi Sistem (IT)
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-maroon">
                                        <div class="panel-heading bg-c-maroon">
                                           <span style="color:white"> Dashboard DIKLAT</span>
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div >
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKunjungan" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg"  role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="titleModalKun"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="load_kunjungan">
                </div>

                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
        })
        var APP_URL = {!! json_encode(url('/')) !!}


    </script>

@endsection
