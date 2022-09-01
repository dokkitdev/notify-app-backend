<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-02-04
 * Time: 08:01
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function indexReports()
    {
        return view('admin.report.reports', [
        ]);
    }

    public function generateReports(Request $request)
    {
        $log = Logs::where('is_started', '=', 1)
            ->where('customer_type', '=', 'Warehouse report')
            ->get();
        if (count($log) > 0) {
            return redirect()->back()->with([
                'error' => 'Report is generating...',
            ]);
        }
        $log = Logs::create([
            'customer_type' => 'Warehouse report',
            'letters_generated' => 0,
            'email_generated' => 0,
            'is_started' => 1,
        ]);
        $date = $request->get('date');
        $command = 'php ' . base_path() . '/artisan upload:report:own ' . $date . ' ' . $log->id . ' > /dev/null 2>&1 &';
        exec($command);
        return redirect()->back()->with([
            'ok' => 'Your report is being processed and will appear in the logs page shortly.',
        ]);
    }
}
