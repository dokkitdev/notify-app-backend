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
use App\Jobs\parseCustomersLinks;
use App\Settings;
use GuzzleHttp\Client;

class simProRequestService
{
    private $token;
    public function __construct()
    {
        $this->getToken();
    }
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


    public function getRequest($method,$url){
        $client = new Client();
        $res = $client->request($method, 'https://enterprise-sandbox-uk.simprosuite.com'.$url.((strpos($url,'?')!=false)?'&':'?').'access_token='.$this->token,
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
                'json'=>$data
            ]
        );
        if((int)$res->getStatusCode()==200){
            return $res->getStatusCode();
        }else{
            return false;
        }
    }

    private function getToken()
    {

        $settings=new Settings();
        $set=$settings->getParam('access_token');

        if(isset($set['value'])&&$set['value']!=''){
            $dateS=(strtotime($set['updated_at'])+$set['expires_in']);
            if(time()<$dateS) {
                $this->token = $set['value'];
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

        $set->name='access_token';
        $set->value=$this->token;
        $set->expires_in=$data->expires_in;
        $set->save();
        return true;
    }
    function getParam($name){
        $settings=Settings::where(['name'=>$name])->get()->toArray();
        if(count($settings)==0)return new Settings();
        else return Settings::find($settings[0]['id']);
    }
}