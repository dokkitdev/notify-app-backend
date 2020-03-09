<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParsingLog;
use App\Models\PrivateCustomer;
use App\Service\Exceptions\MessageException;
use App\Service\PrivateService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class ParsingLogsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 10;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'desc';
        $logs = ParsingLog::orderBy($sort, $direction)
            ->paginate($limit);
        return view('admin.logs.index', [
            'logs' => $logs,
            'limit' => $limit,
            'title' => 'Private Contract Letters (Annual payment)',
        ]);
    }


}
