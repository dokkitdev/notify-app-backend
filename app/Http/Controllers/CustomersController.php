<?php

namespace App\Http\Controllers;

use App\Customers;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index()
    {
        $title = 'Customers';

        $customers = Customers::get();
        return view('tpl.customers.index',['customers'=>$customers])->with('title',$title);
    }
}
