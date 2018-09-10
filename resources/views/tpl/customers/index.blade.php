@extends('layouts.app')

@section('content')

    <div class="container">

        <h2>{{$title}}</h2>
        <ul style="list-style: none">
        @foreach($tags as $tag)
            <li style="float: left; padding-right: 20px;"><a href="/customers/{{$tag['id']}}">{{$tag['name']}}</a></li>
        @endforeach
        </ul>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">simpro_id</th>
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

                @foreach ($customers as $customer)
                        <tr>
                            <td scope="row">{{$customer->simpro_id}}</td>
                            <td scope="row"><input type="checkbox" name="customerActive[{{$customer->id}}]"></td>
                            <td>{{$customer->given_name}} {{$customer->family_name}}</td>
                            <td>{{$customer->address}}</td>
                            <td>
                                @if($customer->letter_state==1)
                                    1st Letter
                                @elseif($customer->letter_state==2)
                                    2nd Letter
                                @elseif($customer->letter_state==3)
                                    3rd Letter
                                @endif
                            </td>
                            <td>{{$customer->created_at}}</td>
                            <td>{{$customer->contract_name}} <br><nobr>{{date("Y-m-d",$customer->start_date)}} - {{date("Y-m-d",$customer->end_date)}}</nobr></td>
                            <td>{{$customer->email}}</td>

                        </tr>
                @endforeach

            </tbody>
        </table>

    </div>

@endsection