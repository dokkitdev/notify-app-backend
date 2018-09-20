@extends('layouts.app')

@section('content')
        <h2>{{$title}}</h2>
        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif
        <form action="/jobs/process" method="post">
            @csrf
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Include</th>
                <th scope="col">Authority</th>
                <th scope="col">Name</th>
                <th scope="col">Address</th>
                <th scope="col">State</th>
                <th scope="col">Import Date</th>
                <th scope="col">Letter Type</th>
            </tr>
            </thead>
            <tbody>

                @foreach ($data as $customer)
                        <tr>
                            <td scope="row" id="action{{$customer['job']->id}}">
                                @if($customer['customer']->tpl==true)
                                    <input value="{{$customer['job']->letter_template}}" name="jobs[{{$customer['job']->id}}]"
                                           onchange="deletion(this,{{$customer['job']->id}})"
                                           type="checkbox" @if($customer['job']->delete==1) @else checked=checked @endif>
                                @else
                                    <span style="color:red">No Templates Found</span>
                                @endif
                            </td>
                            <td>{{$customer['customer']->company_name}}</td>
                            <td>{{$customer['customer']->given_name}} {{$customer['customer']->family_name}}</td>
                            <td>{{$customer['customer']->address}}</td>
                            <td>
                                {{$customer['job']->status}}
                            </td>
                            <td>{{$customer['job']->created_at}}</td>
                            <td>
                                @if($customer['customer']->email!='')
                                    Email
                                @else
                                    Letter
                                @endif
                            </td>

                        </tr>
                @endforeach

            </tbody>
        </table>
        <input class="btn btn-primary float-right" type="submit" value="Process All">

    <script>
        function deletion(checkbox,id){
            if(checkbox.checked==true){
                send(id,'undelete')
            }else{
                send(id,'delete')
            }
        }
        function send(id,action) {
            var token=document.getElementsByName('csrf-token')[0].getAttribute('content');
            $.ajax({
                url: 'http://bf.loc/jobs/'+action,
                type: 'post',
                data: {'id':id,'_token':token},
                dataType: 'json',
                success: function (_response) {
                },
                error: function (_response) {
                    console.log(_response);
                    // Handle error
                }
            });
        }
    </script>

@endsection