<?php


namespace App\Http\Controllers;


use App\Jobs\AssetReportJob;
use App\Models\AssetReport;
use App\Models\AssetReportMini;
use App\Models\AssetReportValidation;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WebhookController extends Controller
{
    public function webhookAction(Request $request)
    {
        $data = $request->all();
        $id = $data['ID'];

        if (in_array($id, ['asset.created', 'asset.updated', 'asset.deleted'])) {
            $siteId = Arr::get($data, 'reference.siteID');

            if (!empty($siteId)) {
                if ($id === 'asset.deleted') {
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
                } else {
                    AssetReportJob::dispatch($data, true)->onQueue('high');
                }
            }
        }

        Webhook::query()
            ->create(
                [
                    'data' => $data,
                ]
            );
    }
}

