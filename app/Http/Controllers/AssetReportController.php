<?php


namespace App\Http\Controllers;


use App\Models\AssetReport;
use App\Models\AssetReportValidation;
use App\Models\ParsingConstant;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class AssetReportController extends Controller
{
    public function index(Request $request)
    {
        $assetConstant = ParsingConstant::firstOrCreate(
            [
                'type' => ParsingConstant::ASSET_TYPE,
            ],
            [
                'is_need_parsing' => false,
            ]
        );

        $sites = AssetReportValidation::query()
            ->select('site_id')
            ->groupBy('site_id')
            ->get();


        $siteId = $request->get('site_id');
        $assetType = $request->get('asset_type');
        $errorSelected = $request->get('error_selected');
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'asc';
        $validations = AssetReportValidation::query();


        if ($siteId) {
            $validations->where('site_id', $siteId);
            $assetTypes = AssetReportValidation::query()
                ->select('asset_type')
                ->where('site_id', $siteId)
                ->groupBy('asset_type')
                ->get();
        } else {
            $assetTypes = AssetReportValidation::query()
                ->select('asset_type')
                ->groupBy('asset_type')
                ->get();
        }

        $errors = [
            'Last service ago',
            'Service due in 11 days',
            'Service due tomorrow',
            'Service complete outside of due date',
            'No UPRN',
            'No Fuel Type found',
            'No Asset Make found',
            'No Model found',
        ];

        if ($errorSelected !== null) {
            if ($errorSelected == 0) {
                $like = 'Last service%ago';
            } else {
                $like = $errors[$errorSelected] ?? '';
                $like .= '%';
            }
            $validations->where('error', 'LIKE', $like);
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
                'asset_constant' => $assetConstant,
                'errors' => $errors,
                'error_selected' => $errorSelected,
            ]
        );
    }

    public function downloadCsv(Request $request)
    {
        $assetReports = AssetReport::get();
        $pdfFolder = Config::get('constants.reports');
        $name = 'CHL_InstalledEquipment_'.Date('YmdHi').'.csv';
        $fp = fopen($pdfFolder.'/'.$name, 'w');
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
                'Service Level Start Date',
                'Job Due Date',
                'Service Level Next Service Date',
                'Job Stage',
                'Service Level Name',
                'Last MOT Date',
                'Service Due',
                'Next Scheduled Appointment Date',
                'No Access Visits',
            ],
            ','
        );
        foreach ($assetReports as $assetReport) {
            $jobDueDate = '';
            $jobStage = $assetReport->job_stage;
            if (in_array($jobStage, ['Progress', 'Pending', 'Complete'])) {
                $jobDueDate = $assetReport->job_due_date;
            }
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
                    $jobDueDate,
                    $assetReport->next_service_date,
                    $jobStage == 'Archived' ? '' : $jobStage,
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
        ReportLog::create(
            [
                'type' => ReportLog::ASSET_REPORT,
                'filename' => $name,
            ]
        );

        return response()->download($pdfFolder.'/'.$name, $name);
    }

    public
    function scheduleValidation(
        Request $request
    ) {
        $assetConstant = ParsingConstant::firstOrCreate(
            [
                'type' => ParsingConstant::ASSET_TYPE,
            ],
            [
                'is_need_parsing' => true,
            ]
        );
        if (!$assetConstant->is_need_parsing) {
            $assetConstant->is_need_parsing = 1;
            $assetConstant->save();
        }

        return redirect()->back()->with(
            [
                'ok' => 'You have successfully scheduled parsing',
            ]
        );
    }
}
