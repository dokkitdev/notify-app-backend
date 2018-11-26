<?php

namespace App\Http\Controllers;

use App\Service\ImportPrivate;
use App\Service\simProService;
use App\Settings;
use Illuminate\Http\Request;

class ImportCustomersController extends Controller
{
    public function index($id)
    {
        if ($id != 'n34u9bf') return false;
        $ps = new simProService();
        $settings = new Settings();
        $settings->setParam('customers_import_start', time());
        $ps->importCustomers();
        $settings->setParam('customers_import_end', time());
        exit;
    }

    public function test()
    {
        (new ImportPrivate())
            ->runCompanies();
    }
}
