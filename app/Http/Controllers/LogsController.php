<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index()
    {
        $title = 'System Logs';
        //$contracts=SimProContracts::where(['active',1])->get();
        $res=[];

        return view('tpl.logs.index',['logs'=>$res,'title'=>$title])->with('title',$title);
    }
}
