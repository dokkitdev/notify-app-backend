<?php


namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\AssetReport;
use App\Models\AssetReportValidation;
use App\Models\ParsingConstant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ZeroReportController extends Controller
{
    public function index(Request $request)
    {
        $zeroConstant = ParsingConstant::firstOrCreate(
            [
                'type' => ParsingConstant::ZERO_TYPE,
            ],
            [
                'is_need_parsing' => false,
            ]
        );
        return view(
            'admin.zero_report.zero_report',
            [
                'zero_constant' => $zeroConstant,
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

        $zeroConstant = ParsingConstant::firstOrCreate(
            [
                'type' => ParsingConstant::ZERO_TYPE,
            ],
            [
                'is_need_parsing' => true,
            ]
        );
        if (!$zeroConstant->is_need_parsing) {
            $zeroConstant->is_need_parsing = true;
            $zeroConstant->save();
        }

        $command = 'php ' . base_path() . '/artisan upload:zero:report ' . $from->format('Y-m-d') . ' ' . $to->format('Y-m-d') . ' > /dev/null 2>&1 &';
        exec($command);

        return redirect()->back()->with(
            [
                'ok' => 'You have successfully started parsing',
            ]
        );
    }

}
