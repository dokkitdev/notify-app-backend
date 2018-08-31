<?php

namespace App\Http\Controllers\Admin;


use App\User;
use App\Http\Controllers\Controller;

class MainController extends Controller
{
    public function index(){
        $title = 'Admin panel';

        return view('admin.index',[])->with('title',$title);
    }
}
