@extends('layouts.app')

@section('content')


        <h2>{{$title}}</h2>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Date</th>
                <th scope="col">Customer Type</th>
                <th scope="col">Letters Generated</th>
                <th scope="col">Email Generated</th>
            </tr>
            </thead>
            <tbody>

                @foreach ($logs as $log)
                        <tr>
                            <td scope="row">{{$customer->simpro_id}}</td>
                            <td scope="row"><input type="checkbox" name="customerActive[{{$customer->id}}]"></td>
                            <td>{{$customer->given_name}} {{$customer->family_name}}</td>
                            <td>{{$customer->address}}</td>
                        </tr>
                @endforeach

            </tbody>
        </table>


@endsection