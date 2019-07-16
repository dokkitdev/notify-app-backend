<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/4/18
 * Time: 1:22 PM
 */

namespace App\Service\Upload;


use App\Models\HousingJob;
use App\Service\simProRequestService;
use Illuminate\Support\Facades\DB;

class HousingUpload
{
    const TAG_NO_ACCESSS_1 = 'No Access 1 (Letter)';
    const TAG_NO_ACCESSS_2 = 'No Access 2 (Letter)';
    const TAG_NO_ACCESSS_3 = 'No Access 3 (Letter)';

    /** @var simProRequestService */
    private $simpro;

    private $yesterday;

    public function __construct($yesterday)
    {
        $this->simpro = new simProRequestService();
        $this->yesterday = $yesterday;
    }

    public function run()
    {
//        DB::delete('TRUNCATE `n_housing_job`;');
        $result = $this->simpro->getRequestPage('get', '/api/v1.0/companies/0/jobs/?display=all');
        if ($result) {
            foreach ($result as $url) {
                dump('getPageByUrl');
                $this->getPageByUrl($url);
            }
        }
    }

    public function getPageByUrl($url)
    {
        $jobs = $this->simpro->getRequest('get', $url);
        if ($jobs) {
            foreach ($jobs as $job_info) {
                dump('parseJob');
                $this->parseJob($job_info);
            }
        }
    }

    public function parseJob($job_info)
    {
        $job = $this->simpro->getRequest('get', '/api/v1.0/companies/0/jobs/' . $job_info->ID . '?display=all');
        if ($job && $job->Customer->ID != 11514 && $job->Customer->ID != 11851) {
            dump("Customer id not in (11514, 11851) " . $job->Customer->ID);
            return;
        }
        if (($job->Stage == 'Progress' || $job->Stage == 'Pending')) {
            dump('parseSite');
            $this->parseSite($job);
        } else {
            $housings = HousingJob::where('job_id', '=', $job->ID)->get();
            if ($housings) {
                foreach ($housings as $h) {
                    $h->delete();
                }
            }
        }
    }


    public function parseSite($job)
    {
        $site_id = $job->Site->ID ?? null;
        if (!$site_id) {
            dump('noo site id');
            return;
        }
        $tag_selected = null;
        foreach ($job->Tags as $tags) {
            if ($tags->Name == self::TAG_NO_ACCESSS_3
                || $tags->Name == self::TAG_NO_ACCESSS_2
                || $tags->Name == self::TAG_NO_ACCESSS_1) {
                if (!$tag_selected) {
                    $tag_selected = $tags->Name;
                } else {
                    if ($tag_selected == 'No Access 3 (Letter)') {
                        continue;
                    }

                    if ($tag_selected == 'No Access 2 (Letter)' && $tags->Name == 'No Access 3 (Letter)') {
                        $tag_selected = $tags->Name;
                        continue;
                    }
                    $tag_selected = $tags->Name;
                }
            }
        }

        if (!$tag_selected) {
            dump('noo tag selected');
            return;
        }

        if ($tag_selected == self::TAG_NO_ACCESSS_2 && $job->Customer->ID == 11851) {
            $site = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/' . $site_id);
            $housingJob = \App\Models\HousingJob::where('job_id', '=', $job->ID)->get()->first();
            $schedule = new \stdClass();
            $schedule->Date = $housingJob ? $housingJob->schedule_date : Date('Y-m-d');
            $schedule->Is11851 = true;
            $this->createHousing($site, $job, $tag_selected, [$schedule]);
            return;
        }
        $costCenterId = $job->Sections[0]->CostCenters[0]->ID ?? null;
        $schedule = $this->simpro->getRequest('get', '/api/v1.0/companies/0/schedules/?Type=job&Reference=' . $job->ID . '-' . $costCenterId . '&Date=' . $this->yesterday);
        if (sizeOf($schedule) < 1) {
            return;
        }
        $site = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/' . $site_id);
        dump('create housing');
        $this->createHousing($site, $job, $tag_selected, $schedule);
    }

    public function createHousing($site, $job, $tag_selected, $schedule)
    {
        $housingJob = \App\Models\HousingJob::where('job_id', '=', $job->ID)->get()->first();
        $schedule = array_shift($schedule);
        dump($job->ID . ' need to add');
        $scheduleDate = $schedule->Date ? (\DateTime::createFromFormat('Y-m-d', $schedule->Date) ?? null) : null;
        if ($housingJob) {
            if (($scheduleDate && $scheduleDate->format('Y-m-d') <= $this->yesterday) || !empty($schedule->Is11851)) {
                $housingJob->job_id = $job->ID;
                $housingJob->order_no = $job->OrderNo;
                $housingJob->company_name = $job->Customer->CompanyName;
                $housingJob->due_date = $job->DueDate ? (\DateTime::createFromFormat('Y-m-d', $job->DueDate) ?? $housingJob->due_date) : $housingJob->due_date;
                if ($housingJob->tags != $tag_selected) {
                    $housingJob->is_proccessed = null;
                }
                $housingJob->tags = $tag_selected;
                $housingJob->stage = $job->Stage;
                $housingJob->job_name = $job->Sections[0]->CostCenters[0]->Name ?? $housingJob->job_name;
                $housingJob->site_id = $site->ID;
                $housingJob->address = $site->Address->Address ?? $housingJob->address;
                $housingJob->city = $site->Address->City ?? $housingJob->city;
                $housingJob->state = $site->Address->State ?? $housingJob->state;
                $housingJob->postal_code = $site->Address->PostalCode ?? $housingJob->postal_code;
                $housingJob->given_name = $site->PrimaryContact->GivenName ?? $housingJob->given_name;
                $housingJob->family_name = $site->PrimaryContact->FamilyName ?? $housingJob->family_name;
                $housingJob->email = $site->PrimaryContact->Email ?? $housingJob->email;
                $housingJob->work_phone = $site->PrimaryContact->WorkPhone ?? $housingJob->work_phone;
                $housingJob->cell_phone = $site->PrimaryContact->CellPhone ?? $housingJob->cell_phone;
                $housingJob->schedule_date = $scheduleDate;
                $housingJob->customer_id = $job->Customer->ID;
                $housingJob->save();
            }
        } else {
            if (($scheduleDate && $scheduleDate->format('Y-m-d') <= $this->yesterday) || !empty($schedule->Is11851)) {
                $housingJob = \App\Models\HousingJob::create([
                    'job_id' => $job->ID,
                    'order_no' => $job->OrderNo,
                    'company_name' => $job->Customer->CompanyName,
                    'due_date' => $job->DueDate ? (\DateTime::createFromFormat('Y-m-d', $job->DueDate) ?? null) : null,
                    'tags' => $tag_selected,
                    'stage' => $job->Stage,
                    'job_name' => $job->Sections[0]->CostCenters[0]->CostCenter->Name ?? null,
                    'site_id' => $site->ID,
                    'address' => $site->Address->Address ?? null,
                    'city' => $site->Address->City ?? null,
                    'state' => $site->Address->State ?? null,
                    'postal_code' => $site->Address->PostalCode ?? null,
                    'given_name' => $site->PrimaryContact->GivenName ?? null,
                    'family_name' => $site->PrimaryContact->FamilyName ?? null,
                    'email' => $site->PrimaryContact->Email ?? null,
                    'work_phone' => $site->PrimaryContact->WorkPhone ?? null,
                    'cell_phone' => $site->PrimaryContact->CellPhone ?? null,
                    'schedule_date' => $scheduleDate,
                    'customer_id' => $job->Customer->ID
                ]);
            }
        }
    }

}










