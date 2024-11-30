<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use function redirect;
use function response;
use function view;

class ReportLogsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'DESC';
        $logs = ReportLog::query()
            ->orderBy($sort, $direction)
            ->paginate($limit);
        $logs->appends($request->except(['page', '_token']));
        return view(
            'admin.report_logs.index',
            [
                'logs' => $logs,
            ]
        );
    }

    public function download($id)
    {
        $reportLog = ReportLog::query()->find($id);
        if (!$reportLog) {
            return redirect()->back()->with(
                [
                    'error' => 'Report log is not found!',
                ]
            );
        }
        $pdfFolder = Config::get('constants.reports');

        return response()->file($pdfFolder.'/'.$reportLog->filename);
    }

}
