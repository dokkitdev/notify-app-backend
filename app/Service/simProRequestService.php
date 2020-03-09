<?php
/**
 * Created by PhpStorm.
 * User: OmenWD
 * Date: 04/09/18
 * Time: 14:53
 */

namespace App\Service;


use App\Appointment;
use App\Models\Customer;
use App\Models\HousingJob;
use App\Settings;
use App\Templates;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class simProRequestService
{
    private $token = 'e929be7849b90e553082e592552739082c7614ab';
    const API_URL = 'https://blueflamecornwallltd.simprocloud.com';

    public $result_count;

    public function __construct()
    {
        //$this->getToken();
    }


    protected function request($method, $url, $data = [], &$attemptCount = 0)
    {
        $this->getToken();
        $client = new Client();
        $url = $url . ((strpos($url, '?') != false) ? '&' : '?') . 'access_token=' . $this->token;
        try {
            $res = $client->request($method, $url, $data);
            if ((int)$res->getStatusCode() == 200 || (int)$res->getStatusCode() == 201 || (int)$res->getStatusCode() == 204) {
                return $res;
            }
        } catch (\Exception $e) {
            if ($attemptCount < 3) {
                sleep(2);
                $this->reGenToken();
                $attemptCount++;

                //print 'ac='.$attemptCount.'<br>';
                return $this->request($method, $url, $data, $attemptCount);
            } else {
                Log::error('simPRORequestError ' . $e->getMessage());
            }
        }
        return false;
    }


    public function getRequestPage($method, $url)
    {
        $res = $this->request($method, self::API_URL . $url,
            ['headers' => [
                'Accept' => 'application/json', #todo required
            ]
            ]
        );
        if ($res) {
            $headers = $res->getHeaders();
            $urls[] = $url;
            if (isset($headers['Result-Total'][0])) {
                $this->result_count = $headers['Result-Total'][0];
            }
            if (isset($headers['Result-Pages'][0]) && $headers['Result-Pages'][0] > 1) {
                for ($i = 2; $i < $headers['Result-Pages'][0] + 1; $i++) {

                    $urls[] = $url . ((strpos($url, '?') != false) ? '&' : '?') . 'page=' . $i;
                }
            }
            return $urls;
        } else {
            return false;
        }
    }


    public function getRequest($method, $url)
    {

        $res = $this->request($method, self::API_URL . $url,
            ['headers' => [
                'Accept' => 'application/json', #todo required
            ]
            ]
        );
        if ($res) return json_decode($res->getBody());
        return false;
    }

    public function deleteRequest($url)
    {
        $client = new Client();
        $this->getToken();
        $res = $client->delete(self::API_URL . $url . '?access_token=' . $this->token,
            ['headers' => [
                'Accept' => 'application/json', #todo required
            ]
            ]
        );
        if ($res) return json_decode($res->getBody());
        return false;
    }

    public function patchRequest($method, $url, $data)
    {
        $res = $this->request($method, self::API_URL . $url,
            ['headers' => [
                'Accept' => 'application/json', #todo required
            ],
                'json' => $data
            ]
        );
        if ($res) return json_decode($res->getBody());
        return false;

    }

    private function reGenToken()
    {
        $settings = new Settings();
        $set = $settings->getParam('access_token');
        if ($set->wait == 0) {
            $set->value = '';
            $set->wait = 1;
            $set->save();
            $this->getToken(true);
            return;
        }
        $this->getToken();
    }

    private function getToken($reGet = false, &$count = 0)
    {
        $settings = new Settings();
        $set = $settings->getParam('access_token');

        if (isset($set->wait) && $set->wait == 1 && ($reGet == false)) { # отправка задач в ожидание если происходит перегенерация токена
            if ($count <= 3) {
                sleep(10);
                $count++;
                return $this->getToken(false, $count);
            } else {
                Log::error('simPRO can`t get token');
                return false;
            }
        }

        /**
         * пока что не нужно
         * if (isset($set['value']) && $set['value'] != '') { # возврат токена если он есть в базе
         * $dateS = (strtotime($set['updated_at']) + $set['expires_in']);
         * if (time() < $dateS) {
         * $this->token = $set['value'];
         * return true;
         * }
         * }
         */

        # запрос нового токена
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        $client = new Client();
        /**
         * Пока что не нужно
         * try {
         * $res = $client->request('POST', self::API_URL . '/oauth2/token', ['form_params' => ['client_id' => '3f9f54e78b4adea956c14f6582b5cd', 'client_secret' => 'e6acde1064', 'grant_type' => 'client_credentials']]);
         * $code = (int)$res->getStatusCode();
         * if ($code != 200 && $code != 204) {
         * throw new \Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
         * }
         * } catch (\Exception $E) {
         * die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
         * }
         * $data = json_decode($res->getBody());
         *
         * $this->token=$data->access_token;
         * */
        $set->name = 'access_token';
        $set->value = $this->token;
//        $set->expires_in = $data->expires_in;
        $set->wait = 0;
        $set->save();
        return true;
    }

    function getParam($name)
    {
        $settings = Settings::where(['name' => $name])->get()->toArray();
        if (count($settings) == 0) return new Settings();
        else return Settings::find($settings[0]['id']);
    }

    function uploadAppointment(Appointment $a)
    {
//        dump('upload');
        $today = new \DateTime();
        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder . '/' . $a->pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder . '/' . $a->pdf));
        $file_name = $a->job_id . '.appointment.' . $today->format('Y-m-d') . '.pdf';
        $res = $this->patchRequest('POST', '/api/v1.0/companies/0/jobs/' . $a->job_id . '/attachments/files/',
            [
                'Filename' => $file_name,
                'Base64Data' => $b64Doc,
                'Public' => true,
                'Email' => false,
            ]
        );
    }

    function uploadAppointmentChl(Appointment $a)
    {
        $letter_type = '';
        switch ($a->letter_type) {
            case Templates::APPOINTMENT_LETTER_CHL_1:
                $letter_type = 'appointment';
                break;
            case Templates::APPOINTMENT_LETTER_CHL_2:
                $letter_type = 'no-access-2';
                break;
            case Templates::APPOINTMENT_LETTER_CHL_3:
                $letter_type = 'no-access-3';
                break;
        }
        $today = new \DateTime();
        $file_name = 'Job#' . $a->job_id . '.' . $letter_type . '.' . $today->format('Y-m-d') . '.pdf';

        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder . '/' . $a->pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder . '/' . $a->pdf));
        $res = $this->patchRequest('POST', '/api/v1.0/companies/0/jobs/' . $a->job_id . '/attachments/files/',
            [
                'Filename' => $file_name,
                'Base64Data' => $b64Doc,
                'Public' => true,
                'Email' => false,
            ]
        );
    }

    function uploadHousing(HousingJob $h)
    {
        $today = new \DateTime();
        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder . '/' . $h->pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder . '/' . $h->pdf));
        $tag = $h->tags;
        $tag = str_replace("(Letter)", '', $tag);
        $tag = strtolower($tag);
        $tag = trim($tag);
        $tag = str_replace(' ', '-', $tag);
        $file_name = $h->job_id . '.' . $tag . '.' . $today->format('Y-m-d') . '.pdf';
        $res = $this->patchRequest('POST', '/api/v1.0/companies/0/jobs/' . $h->job_id . '/attachments/files/',
            [
                'Filename' => $file_name,
                'Base64Data' => $b64Doc,
                'Public' => true,
                'Email' => false,
            ]
        );
    }


    function uploadPrivate(Customer $customer, $pdf)
    {
//        /api/v1.0/companies/0/employees/123/attachments/files/
        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder . '/' . $pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder . '/' . $pdf));
        $res = $this->patchRequest('POST', '/api/v1.0/companies/0/customers/' . $customer->company_id . '/attachments/files/', [
                'Filename' => $pdf,
                'Base64Data' => $b64Doc,
            ]
        );
    }

    function uploadNewPrivate($customer, $pdf) {
        $pdf_folder = Config::get('constants.storage_pdf');
        if (!is_file($pdf_folder . '/' . $pdf)) {
            return;
        }
        $b64Doc = base64_encode(file_get_contents($pdf_folder . '/' . $pdf));

//        $res = $this->patchRequest('POST', '/api/v1.0/companies/0/recurringInvoices/'. $customer->recurring_invoice_id . '/attachments/files/', [
            $res = $this->patchRequest('POST', '/api/v1.0/companies/0/customers/' . $customer->customer_id . '/attachments/files/', [
                'Filename' => $pdf,
                'Base64Data' => $b64Doc,
            ]
        );
    }

    public function getAccessToken()
    {
        return $this->token;
    }
}

