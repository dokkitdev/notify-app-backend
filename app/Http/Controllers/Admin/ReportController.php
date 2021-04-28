<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-02-04
 * Time: 08:01
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Logs;
use App\Models\ReportRow;
use App\Service\TemplateGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use LynX39\LaraPdfMerger\PdfManage;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = ReportRow::groupBy('job_id')->get();
        return view('admin.report.index', [
            'reports' => $reports,
        ]);

    }

    public function report()
    {
        set_time_limit(0);


        $jobs = DB::table('report_row')
            ->select('job_id')
            ->groupBy('job_id')
            ->get();

        $path = __DIR__ . '/../../../../public/images/logo.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $imageContent = file_get_contents($base64);
        $path = tempnam(sys_get_temp_dir(), 'prefix');

        file_put_contents($path, $imageContent);

        $html_folder = Config::get('constants.storage_html');
        $pdf_folder = Config::get('constants.storage_pdf');
        $pdfs = [];

        $generate = [];
        foreach ($jobs as $job) {
            $generate[] = [
                'job_id' => $job->job_id,
                'rows' => ReportRow::where('job_id', '=', $job->job_id)->get()
            ];
        }
        $content = View::make('admin.report.report', [
            'generate' => $generate,
            'logo' => $path,
        ])->render();
        file_put_contents('testik.html', $content);

    }


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
