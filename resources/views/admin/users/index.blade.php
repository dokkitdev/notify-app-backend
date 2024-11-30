@extends('admin.layout')

@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="page-title-right">
                <a class="btn btn-primary" href="{{route('users.create')}}">Create user</a>
            </div>
            <h4 class="page-title">Users</h4>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    @if (session('error'))
                        <div class="alert alert-danger mt-1">
                            <strong>{{ session('error') }}</strong>
                        </div>
                    @elseif(session('ok'))
                        <div class="alert alert-success mt-1">
                            <strong>{{ session('ok') }}</strong>
                        </div>
                    @endif
                    <div class="dataTables_wrapper">
                        <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table table-centered table-striped dt-responsive nowrap w-100"
                                       id="user-table">
                                    <thead>
                                    <tr>
                                        <th>
                                            {!! \App\Service\Sorting::order('Name', 'name') !!}
                                        </th>
                                        <th>
                                            {!! \App\Service\Sorting::order('E-mail', 'email') !!}
                                        </th>
                                        <th>
                                            {!! \App\Service\Sorting::order('Role', 'role') !!}
                                        </th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{$user->name}}</td>
                                            <td>{{$user->email}}</td>
                                            <td>
                                                {{$user->role}}
                                            </td>
                                            <td>
                                                <a href="{{route('users.edit',$user->id)}}" class="action-icon">
                                                    <i class="mdi mdi-square-edit-outline"></i>
                                                </a>
                                                <a href="{{route('users.destroy',$user->id)}}" class="action-icon">
                                                    <i class="mdi mdi-delete"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group  clearfix">
                            {{ $users->links('admin.pagination.default') }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
