@extends('admin.layout')

@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Dashboard</h4>
        </div>
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-primary border-primary border shadow">
                                <i class="fe-mail font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="text-right">
                                <h3 class="mt-1">{{ $chl }}</h3>
                                <p class="text-muted mb-1">CHL</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-primary border-primary border shadow">
                                <i class="fe-mail font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="text-right">
                                <h3 class="mt-1">{{ $appointments }}</h3>
                                <p class="text-muted mb-1">Appointments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-success border-success border shadow">
                                <i class="fe-shield font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="text-right">
                                <h3 class="mt-1">{{ $housing }}</h3>
                                <p class="text-muted mb-1">Housing</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-info border-info border shadow">
                                <i class="fe-lock font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="text-right">
                                <h3 class="mt-1">{{ $private_annual }}</h3>
                                <p class="text-muted mb-1">
                                    Annual payment
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-info border-info border shadow">
                                <i class="fe-lock font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="text-right">
                                <h3 class="mt-1">{{ $private_debit }}</h3>
                                <p class="text-muted mb-1">
                                    Direct Debit
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="widget-rounded-circle card-box">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-warning border-warning border shadow    ">
                                <i class="fe-alert-circle font-22 avatar-title text-white"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="text-right">
                                <h3 class="mt-1">{{ $assets_validation }}</h3>
                                <p class="text-muted mb-1">
                                    Asset Report
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        @if (session('error'))
        $.toast({
            heading: "Notification",
            text: "{{ session('error') }}",
            position: "top-right",
            loaderBg: '#bf441d',
            icon: "error",
        });
        @elseif(session('ok'))
        $.toast({
            heading: "Notification",
            text: "{{ session('ok') }}",
            position: "top-right",
            loaderBg: '#3b98b5',
            icon: "info",
        });

        @endif
    </script>
@endsection
