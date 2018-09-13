<?php

namespace App\Http\Controllers;

use App\Service\simProService;
use App\Settings;
use Illuminate\Http\Request;

class ImportJobsController extends Controller
{
    public function index($id){
        if($id!='n34u9bf')return false;
        $ps=new simProService();
        $settings=new Settings();
        $settings->setParam('jobs_import_start',time());
        $ps->importJobs();
        $settings->setParam('jobs_import_end',time());
        exit;
    }
}
