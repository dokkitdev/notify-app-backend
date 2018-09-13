@extends('layouts.app')

@section('content')

    <div class="container">

        <h2>{{$title}}</h2>
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
                                @if($customer['job']->confirm==1&&$customer['job']->delete==0)
                                    Confirmed
                                @elseif($customer['job']->confirm==0&&$customer['job']->delete==1)
                                    Deleted
                                @else
                                    <nobr><button onclick="confirmation({{$customer['job']->id}})">Confirm</button>&nbsp;<button onclick="deletion({{$customer['job']->id}})">Delete</button></nobr>
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

    </div>
    <script>
        function confirmation(id){
            send(id,'update')
        }
        function deletion(id){
            send(id,'delete')
        }
        function send(id,action) {
            var token=document.getElementsByName('csrf-token')[0].getAttribute('content');
            $.ajax({
                url: 'http://bf.loc/jobs/'+action,
                type: 'post',
                data: {'id':id,'_token':token},
                dataType: 'json',
                success: function (_response) {
                    //console.log(_response);
                    if(_response.confirm==1){
                        editTD('action'+_response.id,'Confirmed');
                    }
                    if(_response.delete==1){
                        editTD('action'+_response.id,'Deleted');
                    }
                },
                error: function (_response) {
                    console.log(_response);
                    // Handle error
                }
            });
        }
        function editTD(id,text) {
            document.getElementById(id).innerHTML=text;
        }
    </script>

@endsection