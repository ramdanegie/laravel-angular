<nav class="navbar header-navbar pcoded-header">
    <div class="navbar-wrapper">

        <div class="navbar-logo">
            <a class="mobile-menu" id="mobile-collapse" href="#!">
                <i class="feather icon-menu"></i>
            </a>

            <a href="{!! route('home') !!}" >
                 <span style="font-size: 20px; margin-left: 13px;">
                     TRANS<span style="color: red">M</span>EDIC
                 </span>
<!--                <img class="img-fluid" src="{!! asset('images/trans_his.png') !!}" alt="Theme-Logo" style="width:150px">-->
            </a>
            <a class="mobile-options">
                <i class="feather icon-more-horizontal"></i>
            </a>
        </div>

        <div class="navbar-container container-fluid">
            <ul class="nav-left">
                <li class="header-search">
                    <div class="main-search morphsearch-search">
                        <div class="input-group">
                            <span class="input-group-addon search-close"><i class="feather icon-x"></i></span>
                            <input type="text" class="form-control">
                            <span class="input-group-addon search-btn"><i class="feather icon-search"></i></span>
                        </div>
                    </div>
                </li>
                <li>
                    <a href="#!" onclick="javascript:toggleFullScreen()">
                        <i class="feather icon-maximize full-screen"></i>
                    </a>
                </li>
            </ul>
            <ul class="nav-right">
<!--                <li class="header-notifiation">-->
<!--                    <div class="dropdown-primary dropdown">-->
<!--                        <div class="dropdown-toggle" data-toggle="dropdown">-->
<!--                            <i class="feather icon-bell"></i>-->
<!--                            <span class="badge bg-c-pink">5</span>-->
<!--                        </div>-->
<!--                        <ul class="show-notification notification-view dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">-->
<!--                            <li>-->
<!--                                <h6>Notifications</h6>-->
<!--                                <label class="label label-danger">New</label>-->
<!--                            </li>-->
<!--                            <li>-->
<!--                                <div class="media">-->
<!--                                    <img class="d-flex align-self-center img-radius"-->
<!--                                         src="{!! asset('adminty/files/assets/images/avatar-4.jpg') !!}" alt="Generic placeholder image">-->
<!--                                    <div class="media-body">-->
<!--                                        <h5 class="notification-user">John Doe</h5>-->
<!--                                        <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>-->
<!--                                        <span class="notification-time">30 minutes ago</span>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </li>-->
<!--                            <li>-->
<!--                                <div class="media">-->
<!--                                    <img class="d-flex align-self-center img-radius"-->
<!--                                         src="{!! asset('adminty\files\assets\images\avatar-3.jpg') !!}" alt="Generic placeholder image">-->
<!--                                    <div class="media-body">-->
<!--                                        <h5 class="notification-user">Joseph William</h5>-->
<!--                                        <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>-->
<!--                                        <span class="notification-time">30 minutes ago</span>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </li>-->
<!--                            <li>-->
<!--                                <div class="media">-->
<!--                                    <img class="d-flex align-self-center img-radius" src="{!! asset('adminty\files\assets\images\avatar-4.jpg') !!}" alt="Generic placeholder image">-->
<!--                                    <div class="media-body">-->
<!--                                        <h5 class="notification-user">Sara Soudein</h5>-->
<!--                                        <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>-->
<!--                                        <span class="notification-time">30 minutes ago</span>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </li>-->
<!--                        </ul>-->
<!--                    </div>-->
<!--                </li>-->
<!--                <li class="header-notification">-->
<!--                    <div class="dropdown-primary dropdown">-->
<!--                        <div class="displayChatbox dropdown-toggle" data-toggle="dropdown">-->
<!--                            <i class="feather icon-message-square"></i>-->
<!--                            <span class="badge bg-c-green">3</span>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </li>c-->
                <li class="user-profile header-notification">
                    <div class="dropdown-primary dropdown">
                        <div class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset('adminty\files\assets\images\avatar2.png') }}" class="img-radius" alt="User-Profile-Image">
{{--                            <i class="fa fa-clock-o" style="font-size: 16px; margin-right: 5px"></i>--}}
{{--                            <span style="font-style:normal;" id="timer"></span>--}}
                            <span>{{ isset($_SESSION['namaLengkap']) ?$_SESSION['namaLengkap'] : 'Administrator' }}</span>

                        </div>
                        <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
<!--                            <li>-->
<!--                                <a href="#!">-->
<!--                                    <i class="feather icon-settings"></i> Settings-->
<!--                                </a>-->
<!--                            </li>-->
<!--                            <li>-->
<!--                                <a href="user-profile.htm">-->
<!--                                    <i class="feather icon-user"></i> Profile-->
<!--                                </a>-->
<!--                            </li>-->
<!--                            <li>-->
<!--                                <a href="email-inbox.htm">-->
<!--                                    <i class="feather icon-mail"></i> My Messages-->
<!--                                </a>-->
<!--                            </li>-->
<!--                            <li>-->
<!--                                <a href="auth-lock-screen.htm">-->
<!--                                    <i class="feather icon-lock"></i> Lock Screen-->
<!--                                </a>-->
<!--                            </li>-->
                            <li>
                                <a href="{{ route('logout') }}">
                                    <i class="feather icon-log-out"></i> Log Out
                                </a>
                            </li>
                        </ul>

                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>

    $(document).ready(function () {
        // getdate();
    });
    function getdate() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        if (h < 10) {
            h = "0" + h;
        }
        if (m < 10) {
            m = "0" + m;
        }
        if (s < 10) {
            s = "0" + s;
        }

        var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum&#39;at', 'Sabtu'];
        var date = new Date();
        var day = date.getDate();
        var month = date.getMonth();
        var thisDay = date.getDay(),
            thisDay = myDays[thisDay];
        var yy = date.getYear();
        var year = (yy < 1000) ? yy + 1900 : yy;

        var tgl = ( thisDay + ', ' + day + ' ' + months[month] + ' ' + year);
        var jam = (h + ":" + m + ":" + s + " wib");
        $("#timer").html(tgl + ' ' + jam);
        setTimeout(function () { getdate() }, 1000);
    }



</script>
