<?php
/**
 * Created by PhpStorm.
 * User: OmenWD
 * Date: 04/09/18
 * Time: 14:53
 */

namespace App\Service;


use App\Customers;
use App\Jobs\parseCustomers;
use App\Settings;
use GuzzleHttp\Client;

class simProService
{
    private $token;
    public function __construct()
    {
        $this->getToken();
    }
    public function updateSimProCustomer($data,Customers $customer){
        $res=$this->patchRequest('PATCH',$customer->apiurl,$data);
    }
    public function index(){
        $companies=$this->parseCompanies();
        if(!$companies) {
            print 'err_companies';
            return false;
        }
        $customersLinks=$this->parseCustomersLinks($companies);
        $this->parseCustomers($customersLinks);
        exit;
    }
    function parseCustomers($customers=[]){
        foreach($customers as $v) {
            parseCustomers::dispatch($v)->delay(now()->addSecond(5));
        }
        return true;
    }
    function parseCustomerByUrl($url){
        $customer=$this->getRequest('GET',$url);
        return $customer;
    }
    private function parseCompanies(){
        $companies=$this->getRequest('GET','/api/v1.0/companies/');
        if(!$companies) {
            print 'err_companies';
            return false;
        }
        return $companies;
    }
    public function getRequest($method,$url){
        $client = new Client();
        $res = $client->request($method, 'https://enterprise-sandbox-uk.simprosuite.com'.$url.'?access_token='.$this->token,
            ['headers'=>[
                'Accept'     => 'application/json', #todo required
            ]
            ]
        );
        if((int)$res->getStatusCode()==200){
            return json_decode($res->getBody());
        }else{
            return false;
        }
    }
    public function patchRequest($method,$url,$data){
        $client = new Client();
        $res = $client->request($method, 'https://enterprise-sandbox-uk.simprosuite.com'.$url.'?access_token='.$this->token,
            ['headers'=>[
                'Accept'     => 'application/json', #todo required
            ],
                'form_params'=>$data
            ]
        );
        if((int)$res->getStatusCode()==200){
            return $res->getStatusCode();
        }else{
            return false;
        }
    }
    private function parseCustomersLinks($companies){
        $customersLinks=[];
        foreach ($companies as $v){
            $customers=$this->getRequest('GET','/api/v1.0/companies/'.$v->ID.'/customers/');
            if(count($customers)>0){
                foreach ($customers as $val){
                    $customersLinks[]=$val->_href;
                }
            }
        }
        return $customersLinks;
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
        $client = new Client();
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
}