<?php

namespace App\Http\Controllers;


class DashboardController extends \Illuminate\Routing\Controller
{
    public function getDashboard()
    {
        return view('dashboard.dashboard');
    }
}