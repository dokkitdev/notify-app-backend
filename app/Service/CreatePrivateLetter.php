<?php

namespace App\Service;


use App\Customers;
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
            if($letter->contract_id > 0){
                // private letter
                $contract = SimProContracts::where('id', $letter->contract_id)->first();
                if($contract == NULL)
                    return false;
                $customer = Customers::where('id', $contract->customers_id)->first();
                if($customer == NULL)
                    return false;
                $email = $customer->email;
                if(strpos($email, '@') === false){
                    // pdf
                }
                else {
                    // email
                    $template = Template::where('id', $letter->template_id)->first();
                    if($template == NULL)
                        return false;
                    $srv = new sesTemplatesService();
                    $sender = env('MAIL_FROM_ADDRESS', 'example@example.com');
                    // #todo: data
                    $data = [];
                    $letter->generated_at = date('Y-m-d H:i:s', time());;
                    $letter->save();
                    $srv->sendSesTemplateEmail($template->name, $sender, $email, $data);
                    $letter->sended_at = date('Y-m-d H:i:s', time());;
                    $letter->save();
                }
            }
        }
        catch(\Exception $e){
            throw new \Exception($e);
        }
    }
}