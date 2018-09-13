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
                            <td scope="row"><input type="checkbox" name="jobActive[{{$customer['job']->id}}]"></td>
                            <td></td>
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

@endsection