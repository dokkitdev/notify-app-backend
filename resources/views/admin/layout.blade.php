<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="icon" type="image/png" href="{{ asset('/images/favicon-32x32.png') }}">
    <link rel="stylesheet" href="{{ asset('/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
          type="text/css"/>
    <link rel="stylesheet"
          href="{{ asset('/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
          type="text/css"/>
    <link href="{{ asset('/css/bootstrap-material.min.css') }}" rel="stylesheet" type="text/css"
          id="bs-default-stylesheet"/>
    <link href="{{ asset('/css/app-material.min.css') }}" rel="stylesheet" type="text/css"
          id="app-default-stylesheet"/>
    <link href="{{ asset('/css/bootstrap-material-dark.min.css') }}" rel="stylesheet" type="text/css"
          id="bs-dark-stylesheet" disabled/>
    <link href="{{ asset('/css/app-material-dark.min.css') }}" rel="stylesheet" type="text/css"
          id="app-dark-stylesheet" disabled/>
    <link href="{{ asset('/css/icons.min.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/flatpickr.min.css') }}">
    @yield('css')
</head>

<body>

<div id="wrapper">

    <div class="navbar-custom">
        <div class="container-fluid">
            <ul class="list-unstyled topnav-menu float-right mb-0">
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect waves-light" data-toggle="dropdown"
                       href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="../images/users/user-1.jpg" alt="user-image"
                             class="rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                        <a href="{{ route('profile') }}" class="dropdown-item notify-item">
                            <i class="fe-user"></i>
                            <span>Profile</span>
                        </a>
                        <a href="{{ route('logout') }}" class="dropdown-item notify-item">
                            <i class="fe-log-out"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </li>

            </ul>
            <div class="logo-box">
                <a href="index.html" class="logo logo-dark text-center">
                            <span class="logo-sm">
                                <img src="{{ asset('/images/favicon-32x32.png') }}" alt="" height="22">
                            </span>
                    <span class="logo-lg text-white">
                        <strong>Notify</strong>
                    </span>
                </a>

                <a href="index.html" class="logo logo-light">
                            <span class="logo-sm text-center">
                                <img src="{{ asset('/images/favicon-32x32.png') }}" alt="" height="22">
                            </span>
                    <span class="logo-lg text-white" style="padding: 0 20px;">
                        <strong style="font-size:21px;">Notify</strong>
                    </span>
                </a>
            </div>
            <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
                <li>
                    <button class="button-menu-mobile waves-effect waves-light">
                        <i class="fe-menu"></i>
                    </button>
                </li>

                <li>
                    <a class="navbar-toggle nav-link" data-toggle="collapse" data-target="#topnav-menu-content">
                        <div class="lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="left-side-menu">
        <div class="h-100" data-simplebar>
            <div class="user-box text-center">
                <img src="..//images/users/user-1.jpg" alt="user-img" title="Mat Helme"
                     class="rounded-circle avatar-md">
                <div class="dropdown">
                    <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block"
                       data-toggle="dropdown">Geneva Kennedy</a>
                    <div class="dropdown-menu user-pro-dropdown">
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <i class="fe-user mr-1"></i>
                            <span>My Account</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <i class="fe-log-out mr-1"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
                <p class="text-muted">Admin Head</p>
            </div>
            <div id="sidebar-menu">
                <ul id="side-menu">
                    <li class="menu-title">Menu</li>
                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i class="mdi mdi-account-circle"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('chl.appointments.all') }}">
                            <i class="mdi mdi-mail"></i>
                            <span>CHL Letters</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('appointments.all') }}">
                            <i class="mdi mdi-mail"></i>
                            <span>Appointment Letters</span>
                        </a>
                    </li>
                    {{--                    <li>--}}
                    {{--                        <a href="{{ route('housing.all') }}">--}}
                    {{--                            <i class="mdi mdi-shield"></i>--}}
                    {{--                            <span>Housing Customers</span>--}}
                    {{--                        </a>--}}
                    {{--                    </li>--}}
                    <li>
                        <a href="#privates" data-toggle="collapse" class="collapsed" aria-expanded="false">
                            <i class="mdi mdi-lock"></i>
                            <span>Private Contracts<span class="menu-arrow"></span></span>
                        </a>
                        <div class="collapse" id="privates" style="">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('private.all') }}">Annual payment</a>
                                </li>
                                <li>
                                    <a href="{{ route('private.debit') }}">Direct Debit</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="{{ route('reports.all') }}">
                            <i class="mdi mdi-server"></i>
                            <span>Warehouse</span>
                        </a>
                    </li>
                    <li class="{{ Route::current()->getName() == 'asset_report.index' ? 'menuitem-active' : '' }}">
                        <a href="{{ route('asset_report.index') }}?first=1">
                            <i class="mdi mdi-alert-circle"></i>
                            <span>Asset Report</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('zero_report.index') }}">
                            <i class="mdi mdi-clipboard-list-outline"></i>
                            <span>Zero Report</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('report_logs.index') }}">
                            <i class="mdi mdi-clipboard-list"></i>
                            <span>Report Logs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logs.index') }}">
                            <i class="mdi mdi-clipboard-text-multiple"></i>
                            <span>Logs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('templates.all') }}">
                            <i class="mdi mdi-microsoft-word"></i>
                            <span>Templates</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('users.index') }}">
                            <i class="mdi mdi-account-circle"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('parsing_logs.all') }}">
                            <i class="mdi mdi-server-network"></i>
                            <span>Parsing logs</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-toggle="modal" data-target="#support">
                            <i class="mdi mdi-timeline-help"></i>
                            <span>Support</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="content-page">
        @yield('content')
    </div>


    <div id="support" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="supportLabel" class="modal-title text-align-center">Need support?</h4>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <p>We'll get back to you as soon as we can!</p>
                    </div>
                    {!! Form::open(['url' => route('support'), 'method' => 'POST']) !!}
                    <div class="form-group">
                        {!! Form::label('Name', 'Your name:') !!}
                        {!! Form::text('name',Auth::user()->name,['class'=>'form-control','disabled'=>'disabled']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('Email', 'Your email address:') !!}
                        {!! Form::text('email',Auth::user()->email,['class'=>'form-control','disabled'=>'disabled']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('Message', 'Message') !!}
                        {!! Form::textarea('message','',['class'=>'form-control']) !!}
                    </div>
                    {!! Form::submit('Send', ['class'=>'btn btn-primary float-right']) !!}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

</div>

<script src="{{ asset('/js/eventsource.js') }}"></script>
<script src="{{ asset('/js/vendor.min.js') }}"></script>
<script src="{{ asset('/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('/libs/jquery-datatables-checkboxes/js/dataTables.checkboxes.min.js') }}"></script>
<link href="{{ asset('libs/jquery-toast-plugin/jquery.toast.min.css') }}" rel="stylesheet" type="text/css"/>
<script src="{{ asset('/libs/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('/js/app.min.js') }}"></script>
<script src="{{ asset('/js/main.js') }}"></script>
<script src="{{ asset('/libs/jquery-toast-plugin/jquery.toast.min.js') }}"></script>

<script>
    $(document).ready(function () {
        $(".flatpickr-with-time-input").flatpickr({
            enableTime: !0,
            dateFormat: "d.m.Y H:i"
        });
    });
</script>
@yield('js')
</body>
</html>
