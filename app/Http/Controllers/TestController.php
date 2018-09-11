<?php

namespace App\Http\Controllers;

use App\Customers;
use App\Jobs\parseCustomers;
use App\Jobs\parseCustomersLinks;
use App\Settings;
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
    public function __construct()
    {
        $this->getToken();
    }
    private function parseCompanies(){
        $companies=$this->getRequest('GET','/api/v1.0/companies/');
        if(!$companies) {
            print 'err_companies';
            return false;
        }
        return $companies;
    }
    public function importCustomers(){


        $companies=$this->parseCompanies();
        if(!$companies) {
            print 'err_companies';
            return false;
        }
        $customersLinks=$this->parseCustomersCompaniesPg($companies);
        exit;
    }
    function parseCustomersCompaniesPg($companies){
        $customersLinks=[];
        foreach ($companies as $v){
            $customers=$this->getRequestPage('GET','/api/v1.0/companies/'.$v->ID.'/customers/');
            if(count($customers)>0){
                foreach ($customers as $val){
                    //$customersLinks[]=$val;
                    parseCustomersLinks::dispatch($val)->delay(now()->addSecond(5));
                }
            }
        }
        return $customersLinks;
    }
    private function parseCustomersLinks($companies){
        $customersLinks=[];
        foreach ($companies as $v){
            $customers=$this->getRequestPage('GET','/api/v1.0/companies/'.$v->ID.'/customers/');
            if(count($customers)>0){
                foreach ($customers as $val){
                    $customersLinks[]=$val->_href;
                }
            }
        }
        return $customersLinks;
    }
    private function parseCustomerLinks($link){
        $customers=$this->getRequest('GET',$link);
        if(count($customers)>0){
            foreach ($customers as $val){
//                $customersLinks[]=$val->_href;
                parseCustomers::dispatch($val->_href)->delay(now()->addSecond(5));
            }
        }

        //       return $customersLinks;
    }

    /**
     * @param $method
     * @param $url
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRequestPage($method,$url){
        $client = new Client();
        $res = $client->request($method, 'https://enterprise-sandbox-uk.simprosuite.com'.$url.'?access_token='.$this->token,
            ['headers'=>[
                'Accept'     => 'application/json', #todo required
            ]
            ]
        );
        if((int)$res->getStatusCode()==200){
            $headers=$res->getHeaders();
            $urls[]=$url;
            if(isset($headers['Result-Pages'][0])&&$headers['Result-Pages'][0]>1){
                for($i=2;$i<$headers['Result-Pages'][0];$i++){
                    $urls[]=$url.'?page='.$i;
                }
            }
            return $urls;
        }else{
            return false;
        }
    }

    function parseCustomerContractByUrl($url){
        $parsedContracts=false;
        $contracts=$this->getRequest('GET',$url); # забираем краткую информацию по контрактам
        if(count($contracts)>0){
            foreach ($contracts as $contract) { # забираем подробную информацию по контрактам
                $parsedContracts[]=$this->getRequest('GET',$url.$contract->ID);
            }

        }

        $res=false;
        if(is_array($parsedContracts)&&count($parsedContracts)>1){ #если несколько контрактов ищем первый НЕ Архивный
            foreach($parsedContracts as $v){
                if($v->Archived==false){
                    $res=$v;
                    break;
                }
            }
            return $res;
        }else {
            return isset($parsedContracts[0])?$parsedContracts[0]:false;
        }
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

    public function index(){
        //dd(\App\Http\Middleware\CheckLogin::class);
        //$this->createSesTemplate();
        #$this->deleteSesTemplate();
        //$this->sendSesTemplateEmail();
        //$this->getSesTemplate();
        try {
            //$rez=$this->updateSesTemplate();
            //$rez=$this->sendSesTemplateEmail();
            //$rez=$this->createSesTemplate();
            $arr=[
'Template1',
'Template2',
'Template3',
'Template_Name',
'Template_Name1',
'Template_Name111',
'Template_Name2'];
            foreach($arr as $v) {
                $rez = $this->deleteSesTemplate($v);
            }
            //$rez=$this->getSesTemplate();
            //$rez=$this->listSesTemplates();
        }catch (\Exception $e){

            print $e->getMessage();
            exit;
        }
        print_r($rez);

        exit;
        /**
         * за 3 дня
         */


        //exit;

        $in[]='/api/v1.0/companies/2/customers/individuals/13102';
        $in[]='/api/v1.0/companies/2/customers/companies/13103';
        $in[]='/api/v1.0/companies/0/customers/companies/75';
        /*
       $in[]='/api/v1.0/companies/0/customers/companies/74';
       $in[]='/api/v1.0/companies/0/customers/companies/4386';
       $in[]='/api/v1.0/companies/0/customers/companies/4413';
       $in[]='/api/v1.0/companies/0/customers/companies/4387';
       $in[]='/api/v1.0/companies/0/customers/companies/4388';
        $in[]='/api/v1.0/companies/0/customers/companies/4411';
        $in[]='/api/v1.0/companies/0/customers/companies/4389';
        $in[]='/api/v1.0/companies/0/customers/companies/4404';
        $in[]='/api/v1.0/companies/0/customers/individuals/4405';
        /*$in[]='/api/v1.0/companies/0/customers/companies/4390';
        $in[]='/api/v1.0/companies/0/customers/individuals/4406';
        $in[]='/api/v1.0/companies/0/customers/individuals/4407';
        $in[]='/api/v1.0/companies/0/customers/companies/4391';
        $in[]='/api/v1.0/companies/0/customers/companies/4392';
        $in[]='/api/v1.0/companies/0/customers/companies/4393';
        $in[]='/api/v1.0/companies/0/customers/companies/4394';
        $in[]='/api/v1.0/companies/0/customers/individuals/4412';
        $in[]='/api/v1.0/companies/0/customers/companies/4414';
        $in[]='/api/v1.0/companies/0/customers/companies/4395';
        $in[]='/api/v1.0/companies/0/customers/companies/4396';
        $in[]='/api/v1.0/companies/0/customers/companies/4397';
        $in[]='/api/v1.0/companies/0/customers/companies/4398';
        $in[]='/api/v1.0/companies/0/customers/companies/4410';
        $in[]='/api/v1.0/companies/0/customers/companies/4399';
        $in[]='/api/v1.0/companies/0/customers/companies/4401';
        $in[]='/api/v1.0/companies/0/customers/companies/4400';
        $in[]='/api/v1.0/companies/0/customers/individuals/4349';
        $in[]='/api/v1.0/companies/2/customers/companies/4890';
        $in[]='/api/v1.0/companies/2/customers/individuals/9310';
        $in[]='/api/v1.0/companies/2/customers/individuals/2917';
        $in[]='/api/v1.0/companies/2/customers/individuals/3449';
        $in[]='/api/v1.0/companies/2/customers/individuals/2857';
        $in[]='/api/v1.0/companies/2/customers/individuals/3647';
        $in[]='/api/v1.0/companies/2/customers/individuals/2863';
        $in[]='/api/v1.0/companies/2/customers/companies/2420';
        $in[]='/api/v1.0/companies/2/customers/companies/2423';
        $in[]='/api/v1.0/companies/2/customers/companies/2424';
        $in[]='/api/v1.0/companies/2/customers/companies/4943';
        $in[]='/api/v1.0/companies/2/customers/individuals/4897';
        $in[]='/api/v1.0/companies/2/customers/companies/13091';
        $in[]='/api/v1.0/companies/2/customers/companies/2425';
        $in[]='/api/v1.0/companies/2/customers/companies/5861';
        $in[]='/api/v1.0/companies/2/customers/individuals/9352';
        $in[]='/api/v1.0/companies/2/customers/companies/4420';
        $in[]='/api/v1.0/companies/2/customers/companies/4369';
        $in[]='/api/v1.0/companies/2/customers/companies/2453';
        $in[]='/api/v1.0/companies/2/customers/individuals/9349';
        $in[]='/api/v1.0/companies/2/customers/companies/13066';
        $in[]='/api/v1.0/companies/2/customers/individuals/9341';
        $in[]='/api/v1.0/companies/2/customers/individuals/2416';
        $in[]='/api/v1.0/companies/2/customers/companies/2426';
        $in[]='/api/v1.0/companies/2/customers/companies/3017';
        $in[]='/api/v1.0/companies/2/customers/companies/4416';
        $in[]='/api/v1.0/companies/2/customers/companies/4944';
        $in[]='/api/v1.0/companies/2/customers/individuals/4895';
        $in[]='/api/v1.0/companies/2/customers/companies/2427';
        $in[]='/api/v1.0/companies/2/customers/individuals/4934';
        $in[]='/api/v1.0/companies/2/customers/companies/2851';
        $in[]='/api/v1.0/companies/2/customers/companies/2852';
        $in[]='/api/v1.0/companies/2/customers/companies/3569';
        $in[]='/api/v1.0/companies/2/customers/companies/4959';
        $in[]='/api/v1.0/companies/2/customers/companies/2976';
        $in[]='/api/v1.0/companies/2/customers/companies/2977';
        $in[]='/api/v1.0/companies/2/customers/companies/2978';
        $in[]='/api/v1.0/companies/2/customers/companies/3644';
        $in[]='/api/v1.0/companies/2/customers/companies/3621';
        $in[]='/api/v1.0/companies/2/customers/companies/2947';
        $in[]='/api/v1.0/companies/2/customers/individuals/2864';
        $in[]='/api/v1.0/companies/2/customers/individuals/2868';
        $in[]='/api/v1.0/companies/2/customers/companies/2979';
        $in[]='/api/v1.0/companies/2/customers/companies/2971';
        $in[]='/api/v1.0/companies/2/customers/companies/2972';
        $in[]='/api/v1.0/companies/2/customers/companies/2973';
        $in[]='/api/v1.0/companies/2/customers/companies/2974';
        $in[]='/api/v1.0/companies/2/customers/companies/2975';
        $in[]='/api/v1.0/companies/2/customers/individuals/2414';
        $in[]='/api/v1.0/companies/2/customers/companies/2534';
        $in[]='/api/v1.0/companies/2/customers/companies/2542';
        $in[]='/api/v1.0/companies/2/customers/companies/2535';
        $in[]='/api/v1.0/companies/2/customers/companies/2536';
        $in[]='/api/v1.0/companies/2/customers/companies/2537';
        $in[]='/api/v1.0/companies/2/customers/companies/2538';
        $in[]='/api/v1.0/companies/2/customers/companies/2539';
        $in[]='/api/v1.0/companies/2/customers/companies/2540';
        $in[]='/api/v1.0/companies/2/customers/companies/2541';
        $in[]='/api/v1.0/companies/2/customers/companies/7';
        $in[]='/api/v1.0/companies/2/customers/companies/2543';
        $in[]='/api/v1.0/companies/2/customers/companies/2544';
        $in[]='/api/v1.0/companies/2/customers/companies/2545';
        $in[]='/api/v1.0/companies/2/customers/companies/2546';
        $in[]='/api/v1.0/companies/2/customers/companies/2547';
        $in[]='/api/v1.0/companies/2/customers/companies/13041';
        $in[]='/api/v1.0/companies/2/customers/companies/9411';
        $in[]='/api/v1.0/companies/2/customers/companies/2548';
        $in[]='/api/v1.0/companies/2/customers/companies/2549';
        $in[]='/api/v1.0/companies/2/customers/companies/4214';
        $in[]='/api/v1.0/companies/2/customers/companies/544';
        $in[]='/api/v1.0/companies/2/customers/companies/4419';
        $in[]='/api/v1.0/companies/2/customers/companies/5855';
        $in[]='/api/v1.0/companies/2/customers/companies/2551';
        $in[]='/api/v1.0/companies/2/customers/individuals/13063';
        $in[]='/api/v1.0/companies/2/customers/individuals/13077';
        $in[]='/api/v1.0/companies/2/customers/individuals/13092';
        $in[]='/api/v1.0/companies/2/customers/individuals/13093';
        $in[]='/api/v1.0/companies/2/customers/individuals/13095';
        $in[]='/api/v1.0/companies/2/customers/individuals/13094';
        $in[]='/api/v1.0/companies/2/customers/individuals/4870';
        $in[]='/api/v1.0/companies/2/customers/companies/2550';
        $in[]='/api/v1.0/companies/2/customers/companies/2552';
        $in[]='/api/v1.0/companies/2/customers/companies/2553';
        $in[]='/api/v1.0/companies/2/customers/companies/2554';
        $in[]='/api/v1.0/companies/2/customers/companies/2555';
        $in[]='/api/v1.0/companies/2/customers/individuals/9311';
        $in[]='/api/v1.0/companies/2/customers/companies/2556';
        $in[]='/api/v1.0/companies/2/customers/individuals/4171';
        $in[]='/api/v1.0/companies/2/customers/individuals/9500';
        $in[]='/api/v1.0/companies/2/customers/companies/2558';
        $in[]='/api/v1.0/companies/2/customers/companies/4378';
        $in[]='/api/v1.0/companies/2/customers/companies/2398';
        $in[]='/api/v1.0/companies/2/customers/companies/2559';
        $in[]='/api/v1.0/companies/2/customers/individuals/9455';
        $in[]='/api/v1.0/companies/2/customers/individuals/9472';
        $in[]='/api/v1.0/companies/2/customers/companies/2560';
        $in[]='/api/v1.0/companies/2/customers/companies/2561';
        $in[]='/api/v1.0/companies/2/customers/companies/5863';
        $in[]='/api/v1.0/companies/2/customers/companies/2562';
        $in[]='/api/v1.0/companies/2/customers/individuals/4172';
        $in[]='/api/v1.0/companies/2/customers/companies/2563';
        $in[]='/api/v1.0/companies/2/customers/companies/2564';
        $in[]='/api/v1.0/companies/2/customers/companies/2565';
        $in[]='/api/v1.0/companies/2/customers/companies/2566';
        $in[]='/api/v1.0/companies/2/customers/companies/2567';
        $in[]='/api/v1.0/companies/2/customers/companies/2568';
        $in[]='/api/v1.0/companies/2/customers/companies/2569';
        $in[]='/api/v1.0/companies/2/customers/companies/2570';
        $in[]='/api/v1.0/companies/2/customers/individuals/9328';
        $in[]='/api/v1.0/companies/2/customers/companies/4955';
        $in[]='/api/v1.0/companies/2/customers/companies/2571';
        $in[]='/api/v1.0/companies/2/customers/companies/4939';
        $in[]='/api/v1.0/companies/2/customers/companies/2572';
        $in[]='/api/v1.0/companies/2/customers/companies/2573';
        $in[]='/api/v1.0/companies/2/customers/companies/549';
        $in[]='/api/v1.0/companies/2/customers/companies/2574';
        $in[]='/api/v1.0/companies/2/customers/companies/2575';
        $in[]='/api/v1.0/companies/2/customers/companies/545';
        $in[]='/api/v1.0/companies/2/customers/companies/2576';
        $in[]='/api/v1.0/companies/2/customers/companies/2577';
        $in[]='/api/v1.0/companies/2/customers/companies/2578';
        $in[]='/api/v1.0/companies/2/customers/individuals/493';
        $in[]='/api/v1.0/companies/2/customers/companies/569';
        $in[]='/api/v1.0/companies/2/customers/companies/2579';
        $in[]='/api/v1.0/companies/2/customers/companies/2580';
        $in[]='/api/v1.0/companies/2/customers/companies/2581';
        $in[]='/api/v1.0/companies/2/customers/companies/2582';
        $in[]='/api/v1.0/companies/2/customers/companies/2392';
        $in[]='/api/v1.0/companies/2/customers/companies/2583';
        $in[]='/api/v1.0/companies/2/customers/companies/2584';
        $in[]='/api/v1.0/companies/2/customers/companies/2585';
        $in[]='/api/v1.0/companies/2/customers/companies/2586';
        $in[]='/api/v1.0/companies/2/customers/companies/2393';
        $in[]='/api/v1.0/companies/2/customers/companies/2587';
        $in[]='/api/v1.0/companies/2/customers/companies/2588';
        $in[]='/api/v1.0/companies/2/customers/companies/2589';
        $in[]='/api/v1.0/companies/2/customers/companies/2590';
        $in[]='/api/v1.0/companies/2/customers/companies/4918';
        $in[]='/api/v1.0/companies/2/customers/companies/2591';
        $in[]='/api/v1.0/companies/2/customers/companies/2592';
        $in[]='/api/v1.0/companies/2/customers/companies/2602';
        $in[]='/api/v1.0/companies/2/customers/companies/2593';
        $in[]='/api/v1.0/companies/2/customers/companies/2594';
        $in[]='/api/v1.0/companies/2/customers/companies/546';
        $in[]='/api/v1.0/companies/2/customers/companies/2595';
        $in[]='/api/v1.0/companies/2/customers/companies/5864';
        $in[]='/api/v1.0/companies/2/customers/companies/18';
        $in[]='/api/v1.0/companies/2/customers/individuals/2399';
        $in[]='/api/v1.0/companies/2/customers/companies/2596';
        $in[]='/api/v1.0/companies/2/customers/companies/2597';
        $in[]='/api/v1.0/companies/2/customers/companies/2598';
        $in[]='/api/v1.0/companies/2/customers/companies/2599';
        $in[]='/api/v1.0/companies/2/customers/companies/2600';
        $in[]='/api/v1.0/companies/2/customers/companies/2601';
        $in[]='/api/v1.0/companies/2/customers/individuals/4177';
        $in[]='/api/v1.0/companies/2/customers/companies/2603';
        $in[]='/api/v1.0/companies/2/customers/companies/2604';
        $in[]='/api/v1.0/companies/2/customers/companies/2605';
        $in[]='/api/v1.0/companies/2/customers/companies/2606';
        $in[]='/api/v1.0/companies/2/customers/companies/547';
        $in[]='/api/v1.0/companies/2/customers/companies/2607';
        $in[]='/api/v1.0/companies/2/customers/companies/2608';
        $in[]='/api/v1.0/companies/2/customers/companies/2609';
        $in[]='/api/v1.0/companies/2/customers/companies/2610';
        $in[]='/api/v1.0/companies/2/customers/individuals/9356';
        $in[]='/api/v1.0/companies/2/customers/individuals/4886';
        $in[]='/api/v1.0/companies/2/customers/companies/2611';*/
        ///api/v1.0/companies/{companyID}/jobs/{jobID}/sections/{sectionID}/costCenters/{costCenterID}/contractorJobs/{contractorJobID}
        //dd($this->getRequest('GET','/api/v1.0/companies/2/jobs/4220/sections/2413/costCenters/2689/contractorJobs/'));
        //dd($this->getRequest('GET','/api/v1.0/companies/0/customers/13103/contracts/13100'));
        dd($this->getRequest('GET','/api/v1.0/companies/2/jobs/4220'));
        dd($this->getRequest('GET','/api/v1.0/companies/2/setup/tags/projects/'));
        dd($this->getRequest('GET','/api/v1.0/companies/2/setup/tags/customers/'));
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
        print '<pre>'.print_r($this->getRequest('GET','/api/v1.0/companies/2/customers/companies/13101'),1).'</pre>';
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