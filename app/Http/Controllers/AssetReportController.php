<?php


namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssetReportController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'asc';
        return view(
            'admin.asset_report.asset_report',
            [
                'limit' => $limit,
            ]
        );
    }
}
