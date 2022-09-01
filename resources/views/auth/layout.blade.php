<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description"/>
    <meta content="Coderthemes" name="author"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <link rel="icon" type="image/png" href="{{ asset('/images/favicon-32x32.png') }}">
    <link href="{{ asset('/css/bootstrap-material.css') }}" rel="stylesheet" type="text/css"
          id="bs-default-stylesheet"/>
    <link href="{{ asset('/css/app-material.css') }}" rel="stylesheet" type="text/css"
          id="app-default-stylesheet"/>
    <link href="{{ asset('/css/icons.min.css') }}" rel="stylesheet" type="text/css"/>
    @yield('css')
</head>
<body class="@yield('body-class')">
@yield('content')
<script src="{{ asset('/js/vendor.min.js') }}"></script>
@yield('js')
</body>
</html>
