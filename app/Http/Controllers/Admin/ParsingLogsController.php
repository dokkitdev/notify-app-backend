<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParsingLog;
use Illuminate\Http\Request;

class ParsingLogsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 10;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'desc';
        $logs = ParsingLog::orderBy($sort, $direction)
            ->paginate($limit);
        return view('admin.parsing_logs.index', [
            'logs' => $logs,
            'limit' => $limit,
            'title' => 'Private Contract Letters (Annual payment)',
        ]);
    }


}
