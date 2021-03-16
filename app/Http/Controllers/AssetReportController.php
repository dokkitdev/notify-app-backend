<?php


namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\AssetReport;
use App\Models\AssetReportValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class AssetReportController extends Controller
{
    public function index(Request $request)
    {
        $sites = AssetReportValidation::query()
            ->select('site_id')
            ->groupBy('site_id')
            ->get();


        $siteId = $request->get('site_id');
        $assetType = $request->get('asset_type');
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'asc';
        $validations = AssetReportValidation::query();

        $assetTypes = [];
        if ($siteId) {
            $validations->where('site_id', $siteId);
            $assetTypes = AssetReportValidation::query()
                ->select('asset_type')
                ->where('site_id', $siteId)
                ->groupBy('asset_type')
                ->get();
        } else {
            $assetType = null;
        }

        if ($assetType) {
            $validations->where('asset_type', $assetType);
        }

        $validations = $validations
            ->orderBy($sort, $direction)
            ->paginate($limit);

        return view(
            'admin.asset_report.asset_report',
            [
                'validations' => $validations,
                'limit' => $limit,
                'sort' => $sort,
                'direction' => $direction,
                'sites' => $sites,
                'site_id' => $siteId,
                'asset_types' => $assetTypes,
                'asset_type' => $assetType,
            ]
        );
    }

    public function downloadCsv(Request $request)
    {
        $assetReports = AssetReport::get();
        $pdfFolder = Config::get('constants.reports');
        $fp = fopen($pdfFolder . '/CHL_InstalledEquipment202103141856.csv', 'w');
        fputcsv(
            $fp,
            [
                'Site ID',
                '~UPRN',
                'AssetID',
                'AssetType',
                'Type',
                'Fuel Type',
                'Make',
                'Model',
                'Last Service Date',
                'Service Level – Start Date',
                'Job Due Date',
                'Service Level – Next Service Date',
                'Job Stage',
                'Service Level - Name',
                'Last MOT Date',
                'Service Due',
                'Next Scheduled Appointment Date',
                'No Access Visits',
            ],
            ','
        );
        foreach ($assetReports as $assetReport) {
            fputcsv(
                $fp,
                [
                    $assetReport->site_id,
                    $assetReport->uprn,
                    $assetReport->asset_id,
                    $assetReport->asset_type,
                    $assetReport->type,
                    $assetReport->fuel_type,
                    $assetReport->make,
                    $assetReport->model,
                    $assetReport->last_service_date,
                    $assetReport->service_level_start_date,
                    $assetReport->job_due_date,
                    $assetReport->next_service_date,
                    $assetReport->job_stage,
                    $assetReport->service_level_name,
                    $assetReport->last_MOT_date,
                    $assetReport->service_due,
                    $assetReport->next_scheduled_appointment_date,
                    $assetReport->no_access_visits,
                ],
                ','
            );
        }
        fclose($fp);
        return response()->file($pdfFolder . '/CHL_InstalledEquipment202103141856.csv');

    }
}
