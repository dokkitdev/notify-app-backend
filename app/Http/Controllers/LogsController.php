<?php

namespace App\Http\Controllers;

use App\Service\LogService;

class LogsController extends Controller
{
    public function index()
    {
        $title = 'System Logs';
        $srv = new LogService();
        $data = $srv->getStandardReport();

        return view('tpl.logs.index', ['data' => $data, 'title' => $title])->with('title', $title);
    }
}
