<?php


namespace App\Http\Controllers;


use App\Jobs\AssetReportJob;
use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function webhookAction(Request $request)
    {
        $data = $request->all();
        AssetReportJob::dispatch($data);
        Webhook::create(
            [
                'data' => $data,
            ]
        );
    }
}
