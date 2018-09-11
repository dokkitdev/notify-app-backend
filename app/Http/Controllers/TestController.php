<?php

namespace App\Http\Controllers;

use App\Customers;
use App\Jobs\parseCustomers;
use App\Jobs\parseCustomersLinks;
use App\Jobs\parseJobs;
use App\Jobs\parseJobsLinks;
use App\Service\simProRequestService;
use App\Settings;
use App\SimProJobs;
use App\User;
use Aws\Credentials\CredentialProvider;
use Aws\Exception\AwsException;
use Aws\Ses\SesClient;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TestController extends Controller
{
    private $token;
    private $SesClient;
    private $simProRequest;
    public function __construct()
    {
        $this->simProRequest=new simProRequestService();
    }
    private function parseCompanies(){
        $companies=$this->simProRequest->getRequest('GET','/api/v1.0/companies/');
        if(!$companies) {
            print 'err_companies';
            return false;
        }
        return $companies;
    }

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

        $name = 'Template_Name2';
        $html_body = '<h1> {{name}} AWS Amazon Simple Email Service Test Email</h1>'.
            '<p>This email was sent with <a href="https://aws.amazon.com/ses/">'.
            'Amazon SES</a> using the <a href="https://aws.amazon.com/sdk-for-php/">'.
            'AWS SDK for PHP</a>.</p>';
        $subject = 'Amazon SES test (AWS SDK for PHP)';
        $plaintext_body = 'This email was send with Amazon SES using the AWS SDK for PHP.' ;


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
            throw new \Exception($this->getErr($e));

        }
    }

    /**
     * обработка кривого ответа от Amazon
     * @param AwsException $e
     * @return string
     */
    function getErr(AwsException $e){
        try {
            preg_match('~<Code>(.*?)</Code>~', $e->getMessage(), $m);
            return $m[1];
        }catch (\Exception $e){
            return '';
        }

    }
    public function deleteSesTemplate($name){
        $SesClient=$this->CreateSesClient();
        //$name = 'Template_Name';
        try {
            $result = $SesClient->deleteTemplate([
                'TemplateName' => $name,
            ]);
            return $result->toArray()['@metadata']['statusCode'];
        } catch (AwsException $e) {
            throw new \Exception($this->getErr($e));
        }
    }
    public function getSesTemplate(){
        $SesClient=$this->CreateSesClient();
        $name = 'Template_Name';
        try {
            $result = $SesClient->getTemplate([
                'TemplateName' => $name,
            ]);
            return $result->toArray()['Template'];
        } catch (AwsException $e) {
            throw new \Exception($this->getErr($e));
        }
    }
    public function sendSesTemplateEmail(){
        $SesClient=$this->CreateSesClient();
        $template_name = 'Template_Name';
        $sender_email = 'omenpars@gmail.com';
        $recipeint_emails = ['max2225@yandex.ru'];
        try {
            $result = $SesClient->sendTemplatedEmail([
                'Destination' => [
                    'ToAddresses' => $recipeint_emails,
                ],
                'ReplyToAddresses' => [$sender_email],
                'Source' => $sender_email,

                'Template' => $template_name,
                'TemplateData' => json_encode(['name'=>'MMMax'])
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
    public function updateSesTemplate(){
        $SesClient=$this->CreateSesClient();
        $name = 'Template_Name';
        $html_body = '<h1>AWS Amazon Simple Email Service Test Email</h1>'.
            '<p>This email was sent with <a href="https://aws.amazon.com/ses/">'.
            'Amazon SES</a> using the <a href="https://aws.amazon.com/sdk-for-php/">'.
            'AWS SDK for PHP</a>.</p>';
        $subject = 'Amazon SES test (AWS SDK for PHP)';
        $plaintext_body = 'This email was send with Amazon SES using the AWS SDK for PHP.' ;
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
    public function checkSES(){
        try {
            //$rez=$this->updateSesTemplate();
            //$rez=$this->sendSesTemplateEmail();
            //$rez=$this->createSesTemplate();
            //$rez = $this->deleteSesTemplate($v);
            //$rez=$this->getSesTemplate();
            //$rez=$this->listSesTemplates();
        }catch (\Exception $e){

            print $e->getMessage();
            exit;
        }
        //print_r($rez);

        exit;
    }

    public function index(){
        $spJobs=SimProJobs::all()->toArray();
        foreach($spJobs as $v){
            print_r(json_decode($v['parsedData'])->Tags);
        }
exit;
        /**
         * за 3 дня
         */


        //exit;



        dd($this->getRequest('GET','/api/v1.0/companies/2/jobs/'));
        print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/2/customers/companies/13103'),1).'</pre>';
        foreach ($in as $v) {
            print '<pre>'.print_r($this->getRequest('GET',$v),1).'</pre>';
            preg_match('!\D(\/companies\/|\/individuals\/)(.*+)$!',$v,$matches); # get the customer id
            preg_match('!\/companies\/(.*)\/customers\/!',$v,$matchesC); # get the customer id
            $res[] = $this->parseCustomerContractByUrl('/api/v1.0/companies/'.$matchesC[1].'/customers/'.$matches[2].'/contracts/');
        }
        dd($res);

        preg_match('!\/companies\/(.*)\/customers\/!','/api/v1.0/companies/2/customers/companies/13101',$matches); # get the customer id
        dd($matches[1]);
        exit;
        exit;
        print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/0/customers/13102/contracts/'),1).'</pre>';
        print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/0/customers/13102/contracts/13098'),1).'</pre>';
        exit;
        /*$arr=$this->getRequest('GET','/api/v1.0/companies/3/customers/individuals/');
        foreach ($arr as $v){
            print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/3/customers/'.$v->ID.'/contracts/'),1).'</pre>';
        }

        */
        print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/3/customers/2388/contracts/'),1).'</pre>';
        print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/3/customers/individuals/'),1).'</pre>';
        //print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/3/customers/individuals/2184'),1).'</pre>';
        //print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/3/jobs/',2),1).'</pre>';

        exit;
        $this->updateSimProCustomer(['GivenName'=>'TTTname'],Customers::find(10));
        exit;
        $arr[]='/api/v1.0/companies/0/customers/companies/75';
        $arr[]='/api/v1.0/companies/0/customers/individuals/4351';
        foreach ($arr as $v){
            $res[]=$this->getRequest('GET',$v);
        }
        print_r($res);
        dd($res);


        exit;
    }

    private function getToken()
    {

        $settings=$this->getParam('access_token');
        if(isset($settings['value'])&&$settings['value']!=''){
            $dateS=(strtotime($settings['updated_at'])+$settings['expires_in']);
            if(time()<$dateS) {
                $this->token = $settings['value'];
                return true;
            }
        }
        $client = new Client();
        $errors=array(
            301=>'Moved permanently',
            400=>'Bad request',
            401=>'Unauthorized',
            403=>'Forbidden',
            404=>'Not found',
            500=>'Internal server error',
            502=>'Bad gateway',
            503=>'Service unavailable'
        );
        try
        {
            $res = $client->request('POST', 'https://enterprise-sandbox-uk.simprosuite.com/oauth2/token',['form_params'=>['client_id'=>'3f9f54e78b4adea956c14f6582b5cd','client_secret'=>'e6acde1064','grant_type'=>'client_credentials']]);
            $code=(int)$res->getStatusCode();
            if($code!=200 && $code!=204) {
                throw new \Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
            }
        }
        catch(\Exception $E)
        {
            die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
        $data=json_decode($res->getBody());
        $this->token=$data->access_token;

        $settings=$this->getParam('access_token');
        $settings->name='access_token';
        $settings->value=$this->token;
        $settings->expires_in=$data->expires_in;
        $settings->save();
        return true;
    }
    function getParam($name){
        $settings=Settings::where(['name'=>$name])->get()->toArray();
        if(count($settings)==0)return new Settings();
        else return Settings::find($settings[0]['id']);
    }

    public function getRequest($method,$url){
        $client = new Client();
        $res = $client->request($method, 'https://enterprise-sandbox-uk.simprosuite.com'.$url.((strpos($url,'page=')!=false)?'&':'?').'access_token='.$this->token,
            ['headers'=>[
                'Accept'     => 'application/json', #todo required
            ]
            ]
        );
        //print_r($res->getHeader());
        if((int)$res->getStatusCode()==200){
            return json_decode($res->getBody());
        }else{
            return false;
        }
    }


}