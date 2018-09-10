<?php

namespace App\Service;

use App\Customers;
use App\TemplateGroup;
use Aws\Credentials\CredentialProvider;
use Aws\Exception\AwsException;
use Aws\Ses\SesClient;

class sesTemplatesService
{
    public $name;
    public $html_body;
    public $subject;
    public $plaintext_body;

    public function CreateSesClient():SesClient{
        $provider = CredentialProvider::defaultProvider();
        $SesClient=new SesClient([
            'profile'=>'default',
            'version' => '2010-12-01',
            'region'  => 'us-east-1',
            'credentials' => $provider,
        ]);
        return $SesClient;
    }
    public function createSesTemplate(){
        $SesClient=$this->CreateSesClient();
        $name = $this->name;
        $html_body = $this->html_body;
        $subject = $this->subject;
        $plaintext_body = $this->plaintext_body;


        try {
            $result = $SesClient->createTemplate([
                'Template' => [
                    'HtmlPart' => $html_body,
                    'SubjectPart' => $subject,
                    'TemplateName' => $name,
                    'TextPart' => $plaintext_body,
                ],
            ]);
            print_r($result);
            var_dump($result);
            dd($result);
        } catch (AwsException $e) {
            // output error message if fails
            echo $e->getMessage();
            echo "\n";
        }
    }
    public function deleteSesTemplate(){
        $SesClient=$this->CreateSesClient();
        $name = 'Template_Name';
        try {
            $result = $SesClient->deleteTemplate([
                'TemplateName' => $name,
            ]);
            dd($result);
        } catch (AwsException $e) {
            // output error message if fails
            echo $e->getMessage();
            echo "\n";
        }
    }
    public function getSesTemplate(){
        $SesClient=$this->CreateSesClient();
        $name = 'Template_Name';
        try {
            $result = $SesClient->getTemplate([
                'TemplateName' => $name,
            ]);
            dd($result);
        } catch (AwsException $e) {
            // output error message if fails
            echo $e->getMessage();
            echo "\n";
        }
    }
    public function sendSesTemplateEmail(){
        $SesClient=$this->CreateSesClient();
        $name = 'Template_Name';
        $sender_email = 'omenpars@gmail.com';
        $recipeint_emails = ['max2225@yandex.ru'];
        try {
            $result = $SesClient->sendTemplatedEmail([
                'Destination' => [
                    'ToAddresses' => $recipeint_emails,
                ],
                'ReplyToAddresses' => [$sender_email],
                'Source' => $sender_email,

                'Template' => $name,
                'TemplateData' => json_encode(['name'=>'MMMax'])
            ]);
            dd($result);
        } catch (AwsException $e) {
            // output error message if fails
            echo $e->getMessage();
            echo "\n";
        }
    }
    public function listSesTemplates(){
        $SesClient=$this->CreateSesClient();
        try {
            $result = $SesClient->listTemplates([
                'MaxItems' => 100,
            ]);
            dd($result);
        } catch (AwsException $e) {
            // output error message if fails
            echo $e->getMessage();
            echo "\n";
        }
    }
}