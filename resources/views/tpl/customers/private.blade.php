@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Private Contracts</h2>
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
                            <td scope="row"><input type="checkbox" name="customerActive[{{$customer['customer']->id}}]"></td>
                            <td>{{$customer['customer']->given_name}} {{$customer['customer']->family_name}}</td>
                            <td>{{$customer['customer']->address}}</td>
                            <td>
                                @if($customer['contract']->letter_state==1)
                                    1st Letter
                                @elseif($customer['contract']->letter_state==2)
                                    2nd Letter
                                @elseif($customer['contract']->letter_state==3)
                                    3rd Letter
                                @endif
                            </td>
                            <td>{{$customer['contract']->created_at}}</td>
                            <td>{{$customer['contract']->contract_name}} <br><nobr>{{date("Y-m-d",$customer['contract']->start_date)}} - {{date("Y-m-d",$customer['contract']->end_date)}}</nobr></td>
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
@endsection