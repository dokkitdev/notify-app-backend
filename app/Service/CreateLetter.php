<?php

namespace App\Service;


use App\Customers;
use App\Helpers\PDFGenerator;
use App\HousingTemplate;
use App\Letter;
use App\SimProContracts;
use App\SimProJobs;
use App\Template;
use Illuminate\Support\Facades\Log;

class CreateLetter
{
    public function createLetter($id)
    {
        $letter = Letter::where('id', $id)->first();
        if($letter == NULL)
            return false;
        try{
            $tdsSrv = new templateDataService();
            if($letter->contract_id > 0){
                // private letter
                $contract = SimProContracts::where('id', $letter->contract_id)->first();
                if($contract == NULL){
                    $this->log('Create private letter warning: contract not found', 'warning', $id);
                    return false;
                }
                $customer = Customers::where('id', $contract->customers_id)->first();
                if($customer == NULL){
                    $this->log('Create private letter warning: customer not found', 'warning', $id);
                    return false;
                }
                $data = $tdsSrv->makeTemplateDataPrivate($contract);
                $template = Template::where('id', $letter->template_id)->first();
                if($template == NULL){
                    $this->log('Create letter warning: private template not found', 'warning', $id);
                    return false;
                }
                return $this->createMessage($data, $customer, $template, $letter);
            }
            if($letter->job_id > 0){
                // housing letter
                $job = SimProJobs::find($letter->job_id);
                if($job == NULL){
                    $this->log('Create private letter warning: contract not found', 'warning', $id);
                    return false;
                }
                $customer = Customers::where('simpro_id','=',$job->simpro_customer_id)->first();
                if($customer == NULL){
                    $this->log('Create housing letter warning: customer not found', 'warning', $id);
                    return false;
                }
                $data = $tdsSrv->makeTemplateDataHousing($job);
                $template = HousingTemplate::where('id', $letter->housing_template_id)->first();
                if($template == NULL){
                    $this->log('Create letter warning: housing template not found', 'warning', $id);
                    return false;
                }
                return $this->createMessage($data, $customer, $template, $letter);
            }
            Log::warning('Create letter warning: letter type not defined');
        }
        catch(\Exception $e){
            $this->log('Create letter exception: ' . $e->getMessage(), 'error', $id);
            throw new \Exception($e);
        }
        return false;
    }

    public function wrapKeys($arr, $wrappers = ['{{', '}}'])
    {
        $result = [];
        foreach ($arr as $key => $value){
            if(strpos($key, $wrappers[0]) === false)
                $result[$wrappers[0] . $key . $wrappers[1]] = $value;
            else
                $result[$key] = $value;
        }
        return $result;
    }

    private function createMessage($data, $customer, $template, $letter){
        $wrappedData = $this->wrapKeys($data);
        $email = $customer->email;
        if(strpos($email, '@') === false){
            // pdf
            $companyId = $customer->company_id;
            if(is_int($companyId)){
                return $this->sendPDF($template, $wrappedData, $letter, $companyId, $customer);
            }
            else{
                $this->log('Create letter warning: incorrect company id', 'warning', $letter->id);
                return false;
            }
        }
        else {
            // email
            $this->sendEmail($email, $letter, $template, $wrappedData);
        }
        return true;
    }

    private function sendPDF($template, $data, $letter, $companyId, $customer){
        $pdfGenerator = new PDFGenerator();
        $pdf = $pdfGenerator->generatePDF($template->html_pdf, $data);
        $letter->generated_at = date('Y-m-d H:i:s', time());
        $letter->letter = 1;
        $letter->save();
        $pi = pathinfo($pdf);
        $srv = new simProService();
        $letter->simpro_attachment_id = $srv->sendAttachment($companyId, $customer->simpro_id, $pdf, $pi['basename']);
        $letter->sended_at = date('Y-m-d H:i:s', time());
        $letter->save();
        unlink($pdf);
        rmdir($pi['dirname']);
        return true;
    }

    private function sendEmail($email, $letter, $template, $data){
        $srv = new sesTemplatesService();
        $sender = env('MAIL_FROM_ADDRESS', 'example@example.com');
        $letter->generated_at = date('Y-m-d H:i:s', time());
        $letter->email = 1;
        $letter->save();
        $srv->sendSesTemplateEmail($template->name, $sender, $email, $data);
        $letter->sended_at = date('Y-m-d H:i:s', time());
        $letter->save();
        return true;
    }

    private function log($text, $level, $id){
        $text .= '. Letter id: ' . $id;
        if($level == 'error')
            Log::error($text);
        if($level == 'warning')
            Log::warning($text);
    }
}