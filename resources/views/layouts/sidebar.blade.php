<!-- Sidebar -->
<ul class="sidebar navbar-nav custom-sidebar">
    <li class="nav-item">
        <a class="nav-link" href="/">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item {!! Route::current()->getName() == 'private.all' ? 'active' : '' !!}">
        <a class="nav-link" href="{{ route('private.all') }}">
            <i class="fas fa-fw fa-folder"></i>
            <span>Private Contracts</span>
        </a>
    </li>
    <li class="nav-item {!! Route::current()->getName() == 'housing.all' ? 'active' : '' !!}">
        <a class="nav-link" href="{{ route('housing.all') }}">
            <i class="fas fa-fw fa-folder"></i>
            <span>Housing Customers</span>
        </a>
    </li>

    <li class="nav-item {!! Route::current()->getName() == 'appointments.all' ? 'active' : '' !!}">
        <a class="nav-link" href="{{ route('appointments.all') }}">
            <i class="fas fa-fw fa-folder"></i>
            <span>Appointment Letters</span>
        </a>
    </li>
    @if(Auth::user()->role=='admin')
        <li class="nav-item
                        @if(Request::is('admin/template*')||Request::is('admin/housing*'))
                                        active
                        @endif
                        ">
            <a class="nav-link" href="/admin/templates">
                <i class="fas fa-fw fa-envelope"></i>
                <span>Templates</span>
            </a>
        </li>
        <li class="nav-item
                        @if(Request::is('admin/users*'))
                                        active
                        @endif
                        ">
            <a class="nav-link" href="/admin/users">
                <i class="fas fa-fw fa-users-cog"></i>
                <span>Users</span>
            </a>
        </li>
    @endif

    <li class="nav-item
                    @if(Request::path() === 'logs')
                                active
                    @endif
                    ">
        <a class="nav-link" href="/logs">
            <i class="fas fa-fw fa-table"></i>
            <span>Logs</span>
        </a>
    </li>

    <li class="nav-item">
        <a href="#!" data-toggle="modal" data-target="#support" class="nav-link">
            <i class="fas fa-headset"></i>
            <span>Support</span>
        </a>
    </li>


    {{--<li class="nav-item dropdown">



        <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-fw fa-folder"></i>
            <span>Pages</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="pagesDropdown">
            <h6 class="dropdown-header">Login Screens:</h6>
            <a class="dropdown-item" href="login.html">Login</a>
            <a class="dropdown-item" href="register.html">Register</a>
            <a class="dropdown-item" href="forgot-password.html">Forgot Password</a>
            <div class="dropdown-divider"></div>
            <h6 class="dropdown-header">Other Pages:</h6>
            <a class="dropdown-item" href="404.html">404 Page</a>
            <a class="dropdown-item" href="blank.html">Blank Page</a>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="charts.html">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Charts</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="tables.html">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables</span></a>
    </li>--}}
</ul>