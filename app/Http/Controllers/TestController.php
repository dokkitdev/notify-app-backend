<?php

namespace App\Http\Controllers;

use App\Customers;
use App\Jobs\parseCustomers;
use App\Settings;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TestController extends Controller
{
    private $token;
    public function __construct()
    {
        $this->getToken();
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
    public function updateSimProCustomer($data,Customers $customer){
        $res=$this->patchRequest('PATCH',$customer->apiurl,$data);
        $res=$this->getRequest('GET',$customer->apiurl);
        dd($res);
    }
    public function index(){
        dd($this->getRequest('GET','/api/v1.0/customers/'));
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

}
