<?php

namespace App\Http\Controllers\Template;

use App\Appointment;
use App\Http\Controllers\Controller;
use App\Service\simProRequestService;
use App\TemplateParent;
use App\Templates;
use CloudConvert\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;


class TemplateController extends Controller
{

    /** Главная страница всех темплейтов */
    public function all()
    {
        $templateParents = TemplateParent::all();
        return view('admin.templates.new.index', [
            'templateParents' => $templateParents,
        ]);
    }

    /** Редактирование темплейта(метод GET) */
    public function getTemplate($id)
    {
        $template = Templates::find($id);
        if ($template) {
            return view('admin.templates.new.edit', [
                'template' => $template
            ]);
        }
        return redirect()->route('templates.all');
    }

    /** Редактирование темплейта(метод PUT) */
    public function putTemplate(Request $request, $id)
    {
        $template = Templates::find($id);
        $data = $request->all();
        if ($template) {
            $template->subject = $data['subject'] ?? $template->subject;
            $template->html_body = $data['html_body'] ?? $template->html_body;

            if (isset($data['file']) && $data['file']) {
                $html = md5(uniqid('template', true)) . '.html';
                $docx = md5(uniqid('template', true)) . '.docx';

                $html_folder = Config::get('constants.storage_html');
                $docx_folder = Config::get('constants.storage_docx') . '/';
                $pdf_folder = Config::get('constants.storage_pdf');

                $data['file']->move($docx_folder, $docx);
                $template->docx = $docx;

                $docx_file_path = $docx_folder . $docx;

                exec('libreoffice --headless --writer --convert-to pdf ' . $docx_file_path . ' --outdir ' . $pdf_folder);
                $pdf = substr($docx, 0, -4) . 'pdf';
                $template->pdf = $pdf;
            }
            $template->save();
        }
        return redirect()->route('templates.all');
    }

    /** AJAX обновление docx */
    public function uploadDocx(Request $request)
    {
        $req = $request->all();
        if (!array_key_exists('file', $req) ||
            !array_key_exists('alias', $req)
        ) {
            return false;
        }

        $template = Templates::where('alias', '=', $req['alias'])->first();

        if (!$template) {
            return false;
        }

        /** Получение папок */
        $pdf_folder = Config::get('constants.storage_pdf');
        $docx_folder = Config::get('constants.storage_docx') . '/';

        $docx = md5(uniqid('template', true)) . '.docx';
        $docx_file_path = $docx_folder . $docx;

        /** Создаем файл на сервере и конвертируем его в пдф */
        $req['file']->move($docx_folder, $docx);
        exec('libreoffice --headless --writer --convert-to pdf ' . $docx_file_path . ' --outdir ' . $pdf_folder);
        $pdf = substr($docx, 0, -4) . 'pdf';
        $template->pdf = $pdf;
        $template->docx = $docx;
        $template->save();

        return response()->json($template->docx);
    }

    public function test()
    {
        $sim = new simProRequestService();
        $companies = $sim->getRequest('GET', '/api/v1.0/companies/0/customers/companies/');
        foreach ($companies as $companyId) {
            $company = $sim->getRequest('GET', '/api/v1.0/companies/0/customers/companies/' . $companyId->ID);
            if (isset($company->Sites)) {
                foreach ($company->Sites as $siteId) {
                    $assets = $sim->getRequest('GET', '/api/v1.0/companies/0/sites/' . $siteId->ID . '/assets/');
                    foreach ($assets as $assetId) {
                        $asset = $sim->getRequest('GET', '/api/v1.0/companies/0/sites/' . $siteId->ID . '/assets/' . $assetId->ID);
//                        if (sizeOf($asset->CustomerContract) > 0) {
                        dump($asset);
//                        }
                    }
                }
            }
            $contracts = $sim->getRequest('GET', '/api/v1.0/companies/0/customers/' . $companyId->ID . '/contracts/');
            foreach ($contracts as $contractId) {
                $contract = $sim->getRequest('GET', '/api/v1.0/companies/0/customers/' . $companyId->ID . '/contracts/' . $contractId->ID);
//                dump($contract);
            }
        }
        die;

    }

    public function test2()
    {
        $sim = new simProRequestService();
        $jobs = $sim->getRequest('GET', '/api/v1.0/companies/0/schedules/?Type=job');
        foreach ($jobs as $jobSchedule) {
            $date = $jobSchedule->Date;
            $blocks = $jobSchedule->Blocks;
            $time = $blocks[0]->EndTime;
            $jobParse = explode('-', $jobSchedule->Reference);
            $jobId = array_shift($jobParse);

            $job = $sim->getRequest('GET', '/api/v1.0/companies/0/jobs/' . $jobId . '?display=all');
            $siteId = $job->Site->ID ?? null;
            if (!$siteId) {
                continue;
            }
            $site = $sim->getRequest('GET', '/api/v1.0/companies/0/sites/' . $siteId);

            $workType = $job->Sections[0]->CostCenters[0]->CostCenter->Name ?? null;
            $sendDate = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time) ?? null;
            $address = $site->Address->Address ?? null;
            $city = $site->Address->City ?? null;
            $state = $site->Address->State ?? null;
            $postalCode = $site->Address->PostalCode ?? null;
            $country = $site->Address->Country ?? null;
            $title = $site->PrimaryContact->Title ?? '';
            $givenName = $site->PrimaryContact->GivenName ?? '';
            $familyName = $site->PrimaryContact->FamilyName ?? '';

            if (strlen($title) == 0
                && strlen($givenName) == 0
                && strlen($familyName) == 0) {
                $title = 'The Occupier';
            }

            $conn = DB::connection();
            Appointment::create([
                'title' => strlen(trim($title)) > 0 ? $title : 'The Occupier',
                'family_name' => $familyName,
                'given_name' => $givenName,
                'address' => $address,
                'state' => $state,
                'city' => $city,
                'country' => $country,
                'postcode' => $postalCode,
                'job_id' => $jobId,
                'send_date' => $sendDate,
                'work_type' => $workType,
                'appointment_id' => $jobSchedule->ID ?? null,
                'site_id' => $siteId,
                'time' => $time
            ]);
        }
        $this->clearDublicates();
    }

    public function clearDublicates()
    {
        $result = DB::select('SELECT job_id, min(`send_date`) as mtime FROM appointments GROUP BY job_id HAVING COUNT(*) > 1');
        foreach ($result as $r) {
            $finded = DB::select('SELECT id FROM appointments WHERE job_id = :job and `send_date` = :send_date LIMIT 1', [
                $r->job_id,
                $r->mtime
            ]);
            DB::delete('DELETE FROM `appointments` WHERE job_id = :job AND id <> :id;', [
                $r->job_id,
                $finded[0]->id
            ]);
        }
    }

}
