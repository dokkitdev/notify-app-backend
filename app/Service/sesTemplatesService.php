<?php

namespace App\Service;

use Aws\Credentials\CredentialProvider;
use Aws\Exception\AwsException;
use Aws\Ses\SesClient;

class sesTemplatesService
{
    public $name;
    public $html_body;
    public $subject;
    public $plaintext_body;

    private function CreateSesClient():SesClient{
        $provider = CredentialProvider::defaultProvider();
        $SesClient=new SesClient([
            'profile'=>'default',
            'version' => '2010-12-01',
            'region'  => 'us-east-1',
            'credentials' => $provider,
        ]);
        return $SesClient;
    }
    /**
     * обработка кривого ответа от Amazon
     * @param AwsException $e
     * @return string
     */
    private function getErr(AwsException $e){
        try {
            preg_match('~<Code>(.*?)</Code>~', $e->getMessage(), $m);
            return $m[1];
        }catch (\Exception $e){
            return '';
        }

    }
    public function createSesTemplate($name,$html_body,$subject,$plaintext_body=''){
        $SesClient=$this->CreateSesClient();

        try {
            $result = $SesClient->createTemplate([
                'Template' => [
                    'HtmlPart' => $html_body,
                    'SubjectPart' => $subject,
                    'TemplateName' => $name,
                    'TextPart' => $plaintext_body,
                ],
            ]);
            return $result->toArray()['@metadata']['statusCode'];
        } catch (AwsException $e) {
            return $this->getErr($e);

        }
    }
    public function updateSesTemplate($name,$html_body,$subject,$plaintext_body=''){
        $SesClient=$this->CreateSesClient();
        try {
            $result = $SesClient->updateTemplate([
                'Template' => [
                    'HtmlPart' => $html_body,
                    'SubjectPart' => $subject,
                    'TemplateName' => $name,
                    'TextPart' => $plaintext_body,
                ],
            ]);
            return $result->toArray()['@metadata']['statusCode'];
        } catch (AwsException $e) {
            throw new \Exception($this->getErr($e));
        }
    }
    public function deleteSesTemplate($name){
        $SesClient=$this->CreateSesClient();
        try {
            $result = $SesClient->deleteTemplate([
                'TemplateName' => $name,
            ]);
            return $result->toArray()['@metadata']['statusCode'];
        } catch (AwsException $e) {
            throw new \Exception($this->getErr($e));
        }
    }
    public function getSesTemplate($name){
        $SesClient=$this->CreateSesClient();
        try {
            $result = $SesClient->getTemplate([
                'TemplateName' => $name,
            ]);
            return $result->toArray()['Template'];
        } catch (AwsException $e) {
            return $this->getErr($e);
        }
    }
    public function sendSesTemplateEmail($name,$sender_email,$recipeint_emails,$data=[]){
        $SesClient=$this->CreateSesClient();
        try {
            $result = $SesClient->sendTemplatedEmail([
                'Destination' => [
                    'ToAddresses' => [$recipeint_emails],
                ],
                'ReplyToAddresses' => [$sender_email],
                'Source' => $sender_email,

                'Template' => $name,
                'TemplateData' => json_encode($data)
            ]);
            $result=$result->toArray();
            return ['statusCode'=>$result['@metadata']['statusCode'],'MessageId'=>$result['MessageId']];
        } catch (AwsException $e) {
            throw new \Exception($this->getErr($e));
        }
    }
    public function listSesTemplates(){
        $SesClient=$this->CreateSesClient();
        try {
            $result = $SesClient->listTemplates([
                'MaxItems' => 100,
            ]);
            return $result->toArray()['TemplatesMetadata'];
        } catch (AwsException $e) {
            throw new \Exception($this->getErr($e));
        }
    }

}