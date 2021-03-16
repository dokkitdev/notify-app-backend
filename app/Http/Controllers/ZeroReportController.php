<?php


namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\AssetReport;
use App\Models\AssetReportValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ZeroReportController extends Controller
{
    public function index(Request $request)
    {
        return view(
            'admin.zero_report.zero_report',
            [
            ]
        );
    }

    public function requestToParseZeroReport(Request $request)
    {
        $dates = $request->request->get('dates');
        $dates = explode('-', preg_replace('/ +/', '', $dates));
        $from = \DateTime::createFromFormat('d/m/Y', array_shift($dates));
        $to = \DateTime::createFromFormat('d/m/Y', array_shift($dates));
        if (!$from || !$to) {
            return redirect()->back()->with(
                [
                    'error' => 'Dates invalid!',
                ]
            );
        }

        dump($from, $to);
    }

}
