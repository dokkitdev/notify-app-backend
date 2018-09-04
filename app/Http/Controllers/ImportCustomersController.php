<?php

namespace App\Http\Controllers;

use App\Service\simProService;
use Illuminate\Http\Request;

class ImportCustomersController extends Controller
{
    public function index($id){
        if($id!='n34u9bf')return false;
        $ps=new simProService();
        $ps->index();
    }
}
