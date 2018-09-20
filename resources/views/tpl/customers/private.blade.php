@extends('layouts.app')

@section('content')
        <h2>Private Contracts</h2>
        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif
        <form action="/contracts/process" method="post">
            @csrf
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Include</th>
                    <th scope="col">Name</th>
                    <th scope="col">Address</th>
                    <th scope="col">State</th>
                    <th scope="col">Import Date</th>
                    <th scope="col">Contract Plan</th>
                    <th scope="col">Letter Type</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($data as $customer)
                            <tr>
                                <td scope="row" id="action{{$customer['contract']->id}}">
                                    <input value="{{$customer['contract']->letter_state}}" name="contract[{{$customer['contract']->id}}]"
                                           onchange="deletion(this,{{$customer['contract']->id}})"
                                           type="checkbox" @if($customer['contract']->delete==1) @else checked=checked @endif>
                                </td>
                                <td>{{$customer['customer']->given_name}} {{$customer['customer']->family_name}}</td>
                                <td>{{$customer['customer']->address}}</td>
                                <td><nobr>
                                    @if($customer['contract']->letter_state==1)
                                        1st Letter
                                    @elseif($customer['contract']->letter_state==2)
                                        2nd Letter
                                    @elseif($customer['contract']->letter_state==3)
                                        3rd Letter
                                    @endif
                                    </nobr>
                                </td>
                                <td>{{$customer['contract']->created_at}}</td>
                                <td>{{$customer['contract']->contract_name}} {{date("Y-m-d",$customer['contract']->end_date)}}</td>
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
        </form>

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
                url: 'http://bf.loc/contracts/'+action,
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