<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;

use function redirect;
use function view;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'DESC';
        $logs = Logs::query()
            ->orderBy($sort, $direction)
            ->paginate($limit);
        $logs->appends($request->except(['page', '_token']));

        return view('admin.logs.index',
                    [
                        'logs' => $logs,
                    ]
        );
    }

    public function showEmails($id)
    {
        $log = Logs::find($id);
        if (!$log || !$log->emails) {
            return redirect()->back();
        }

        return view('admin.logs.emails', [
            'html' => gzuncompress(base64_decode($log->emails)),
        ]);
    }
}
