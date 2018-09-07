<?php

namespace App\Http\Controllers;

use App\Service\collectDataService;
use Illuminate\Http\Request;

class CollectDataController extends Controller
{
    public function index($id){
        if($id!='n34u9bf')return false;
        $cds=new collectDataService();
        $cds->collectData();
    }
}
