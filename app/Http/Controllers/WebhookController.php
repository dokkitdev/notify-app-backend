<?php


namespace App\Http\Controllers;


use App\Jobs\AssetReportJob;
use App\Models\AssetReport;
use App\Models\AssetReportMini;
use App\Models\AssetReportValidation;
use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function webhookAction(Request $request)
    {
        $data = $request->all();
        $id = $data['ID'];
                if (in_array($id, ['asset.created', 'asset.updated', 'job.asset.tested'])) {
            AssetReportJob::dispatch($data, true)->onQueue('high');
        } elseif ($id == 'asset.deleted') {
            $siteId = $data['reference']['siteID'];
            $assetId = $data['reference']['assetID'];
            AssetReport::query()
                ->where('site_id', $siteId)
                ->where('asset_id', $assetId)
                ->delete();

            AssetReportMini::query()
                ->where('site_id', $siteId)
                ->where('asset_id', $assetId)
                ->delete();

            AssetReportValidation::query()
                ->where('site_id', $siteId)
                ->where('asset_id', $assetId)
                ->delete();
        }
        Webhook::query()
            ->create(
                [
                    'data' => $data,
                ]
            );
    }
}
