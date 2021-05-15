<?php


namespace App\Http\Controllers;


use App\Jobs\AssetReportJob;
use App\Jobs\JobAssetReportJob;
use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function webhookAction(Request $request)
    {
        $data = $request->all();
        $id = $data['ID'];
        if (in_array($id, ['asset.created', 'asset.updated'])) {
            AssetReportJob::dispatch($data);
        } elseif ($id === 'job.updated') {
            JobAssetReportJob::dispatch($data);
        }
        Webhook::create(
            [
                'data' => $data,
            ]
        );
    }
}
