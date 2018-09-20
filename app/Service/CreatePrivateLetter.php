<?php

namespace App\Service;


use App\Customers;
use App\Helpers\PDFGenerator;
use App\Letter;
use App\SimProContracts;
use App\Template;

class CreatePrivateLetter
{
    public function createPrivateLetter($id)
    {
        $letter = Letter::where('id', $id)->first();
        if($letter == NULL)
            return false;
        try{
            $template = Template::where('id', $letter->template_id)->first();
            if($template == NULL)
                return false;

            $tdsSrv = new templateDataService();
            if($letter->contract_id > 0){
                // private letter
                $contract = SimProContracts::where('id', $letter->contract_id)->first();
                if($contract == NULL)
                    return false;
                $customer = Customers::where('id', $contract->customers_id)->first();
                if($customer == NULL)
                    return false;
                $data = $tdsSrv->makeTemplateDataPrivate($contract);
                $wrappedData = $this->wrapKeys($data);
                $email = $customer->email;
                if(strpos($email, '@') === false){
                    // pdf
                    $companyId = $customer->company_id;
                    if(is_int($companyId)){
                        $pdfGenerator = new PDFGenerator();
                        $pdf = $pdfGenerator->generatePDF($template->html_pdf, $wrappedData);
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
                    }
                }
                else {
                    // email
                    $srv = new sesTemplatesService();
                    $sender = env('MAIL_FROM_ADDRESS', 'example@example.com');
                    $letter->generated_at = date('Y-m-d H:i:s', time());
                    $letter->email = 1;
                    $letter->save();
                    $srv->sendSesTemplateEmail($template->name, $sender, $email, $wrappedData);
                    $letter->sended_at = date('Y-m-d H:i:s', time());
                    $letter->save();
                }
            }
        }
        catch(\Exception $e){
            throw new \Exception($e);
        }
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
}