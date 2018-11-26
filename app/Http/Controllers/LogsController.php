<?php

namespace App\Http\Controllers;

use App\Logs;
use App\Service\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LogsController extends Controller
{
    public function index()
    {
        $title = 'System Logs';
        $srv = new LogService();
        $data = $srv->getStandardReport();

        $logs = Logs::all();

        return view('tpl.logs.index', [
            'data' => $data,
            'title' => $title,
            'logs' => $logs,
        ])->with('title', $title);
    }

    public function dailyLettersLog($type, $date)
    {
        $title = $type . ' letters for ' . $date;
        $srv = new LogService();
        $data = $srv->getDailyLog($type, $date);
        return view('tpl.logs.daily', ['data' => $data, 'title' => $title])->with('title', $title);
    }

    public function downloadPdfLetterFromS3(Request $request)
    {
        $link = $request->get('link');
        if (Storage::disk('s3')->exists($link)) {
            $file = Storage::disk('s3')->download($link);
            return $file;
        }
        return response()->view('errors.main', [], 500);
    }
}
