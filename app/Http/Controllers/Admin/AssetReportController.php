<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Jobs\AssetReportMiniExportJob;
use App\Models\AssetReport;
use App\Models\AssetReportFilter;
use App\Models\AssetReportValidation;
use App\Models\ParsingConstant;
use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

use function redirect;
use function response;
use function view;


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
        $assetType = $request->get('asset_type') ?: [];
        $stagesSelected = $request->get('stages') ?: [];
        $serviceLevelName = $request->get('service_level_name') ?: [];
        $errorSelected = $request->get('error_selected') ?: [];

        if ($request->has('first')) {
            $filter = AssetReportFilter::query()->firstOrCreate([]);
            $siteId = $filter->site_id;
            $assetType = $filter->asset_types ?: [];
            $stagesSelected = $filter->stages ?: [];
            $serviceLevelName = $filter->service_levels ?: [];
            $errorSelected = $filter->errors ?: [];
        }

        if ($request->has('save_filter')) {
            $filter = AssetReportFilter::query()->firstOrCreate([]);
            $filter->site_id = $siteId;
            $filter->asset_types = $assetType;
            $filter->stages = $stagesSelected;
            $filter->service_levels = $serviceLevelName;
            $filter->errors = $errorSelected;
            $filter->save();
        }

        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'asc';
        $validations = AssetReportValidation::query();


        if ($siteId) {
            $validations->where('site_id', $siteId);
            $assetTypes = AssetReportValidation::query()
                ->select('asset_type')
                ->whereNotNull('asset_type')
                ->where('site_id', $siteId)
                ->groupBy('asset_type')
                ->get();

            $serviceLevelNames = AssetReportValidation::query()
                ->select('service_level_name')
                ->where('site_id', $siteId)
                ->whereNotNull('service_level_name')
                ->groupBy('service_level_name')
                ->get();

            $stages = AssetReportValidation::query()
                ->select('job_stage')
                ->where('site_id', $siteId)
                ->whereNotNull('job_stage')
                ->groupBy('job_stage')
                ->get();
        } else {
            $assetTypes = AssetReportValidation::query()
                ->select('asset_type')
                ->whereNotNull('asset_type')
                ->groupBy('asset_type')
                ->get();

            $serviceLevelNames = AssetReportValidation::query()
                ->whereNotNull('service_level_name')
                ->select('service_level_name')
                ->groupBy('service_level_name')
                ->get();

            $stages = AssetReportValidation::query()
                ->select('job_stage')
                ->whereNotNull('job_stage')
                ->groupBy('job_stage')
                ->get();
        }

        $errors = [
            'Last Service over 14mths',
            'Service due in 30 days',
            'Service due tomorrow',
            'Service complete outside of due date',
            'No UPRN',
            'No Fuel Type found',
            'No Asset Make found',
            'No Model found',
        ];

        if ($errorSelected !== null) {
            $validations->where(
                function ($q) use ($errors, $errorSelected) {
                    foreach ($errorSelected as $e) {
                        $e = (int)$e;
                        if ($e == 0) {
                            $like = 'Last service%ago';
                        } else {
                            if ($e == 1) {
                                $like = 'Service due within%days';
                            } else {
                                $like = $errors[$e] ?? '';
                                $like .= '%';
                            }
                        }
                        if ($e != 3) {
                            $q->orWhere('error', 'LIKE', $like);
                        } else {
                            $q
                                ->orWhere('error', 'LIKE', 'Service complete outside of due date %')
                                ->orWhere('error', 'LIKE', 'Service complete outside of due date %');
                        }
                    }
                }
            );
        }

        if ($assetType) {
            $validations->whereIn('asset_type', $assetType);
        }

        if ($serviceLevelName) {
            $validations->whereIn('service_level_name', $serviceLevelName);
        }

        if ($stagesSelected) {
            $validations->whereIn('job_stage', $stagesSelected);
        }

        /** @var LengthAwarePaginator $validations */
        $validations = $validations
            ->orderBy($sort, $direction)
            ->paginate($limit);

        $validations->appends($request->except(['page', '_token']));

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
                'service_level_names' => $serviceLevelNames,
                'service_level_name' => $serviceLevelName,
                'stages' => $stages,
                'stages_selected' => $stagesSelected,
            ]
        );
    }

    public function downloadCsv(Request $request)
    {
        ini_set('memory_limit', '2048M');
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
                'Location',
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
                'Cancellations',
            ],
            ','
        );
        $now = Date('Y-m-d');
        $today = (new \DateTime('+1 day'))->format('Y-m-d');
        foreach ($assetReports as $assetReport) {
            $jobDueDate = '';
            $jobStage = $assetReport->job_stage;
            if (in_array($jobStage, ['Progress', 'Pending'])) {
                $jobDueDate = $assetReport->job_due_date;
            }
            $d = null;
            if ($assetReport->next_scheduled_appointment_date) {
                list($d) = explode(' ', $assetReport->next_scheduled_appointment_date);
            }

            if (!$jobDueDate || ($jobDueDate < $today)) {
                $serviceDue = $assetReport->next_service_date;
            } else {
                $serviceDue = $jobDueDate;
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
                    $assetReport->location ?? '',
                    $assetReport->last_service_date,
                    $assetReport->service_level_start_date,
                    $jobDueDate,
                    $assetReport->next_service_date,
                    $jobStage == 'Archived' ? '' : $jobStage,
                    $assetReport->service_level_name,
                    $assetReport->last_MOT_date,
                    $serviceDue,
                    $assetReport->next_scheduled_appointment_date && $d >= $now ? $assetReport->next_scheduled_appointment_date : '',
                    $assetReport->no_access_visits,
                    $jobStage == 'Archived' ? '' : ($assetReport->cancellation ?? ''),
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

        AssetReportMiniExportJob::dispatch();

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
