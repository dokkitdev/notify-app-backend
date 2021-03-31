<?php


namespace App\Http\Controllers;


use App\Jobs\AssetReportJob;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function webhookAction(Request $request)
    {
        $data = $request->all();
        AssetReportJob::dispatch($data);
    }
}
